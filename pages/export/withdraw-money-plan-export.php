<?php
require __DIR__ . '/../../vendor/autoload.php';

header("Content-Type: application/json");
require __DIR__ . '/../../configs/database.php';
require __DIR__ . '/../../models/withdraws/wrd_plan_model.php';
require __DIR__ . '/../../models/withdraws/wrd_plan_list_model.php';
require __DIR__ . '/../../models/withdraws/wrd_number_model.php';

$database = new Database();
$db = $database->connect('raot_db_qas');

$wrdPlanModel   = new WrdPlanModel($db);
$wrdNumberModel = new WrdNumberModel($db);
$planListModel  = new PlanListModel($db);

$main_data  = $wrdPlanModel->readPlanOne($_GET['id']);       
$child_data = $planListModel->readPlanListOne($_GET['id']); 

function formatDateThaiShort($date) {
    if (empty($date) || $date == '0000-00-00') {
        return '';
    }
    $timestamp = strtotime($date);
    $day   = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year  = date('Y', $timestamp) + 543; // แปลงเป็น พ.ศ.
    $year_short = substr($year, -2); // เอาแค่สองหลัก

    return "$day-$month-$year_short";
}

function convert_number_to_text($number) {
    $number = number_format($number, 2, '.', '');
    $txtnum1 = ['ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า','สิบ'];
    $txtnum2 = ['','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน'];
    $number = str_replace(",", "", $number);
    $split_number = explode(".", $number);
    $number = $split_number[0];
    $strlen = strlen($number);
    $convert = '';
    for ($i = 0; $i < $strlen; $i++) {
        $n = substr($number, $i, 1);
        if ($n != 0) {
            if ($i == ($strlen - 1) && $n == 1) {
                $convert .= 'เอ็ด';
            } elseif ($i == ($strlen - 2) && $n == 2) {
                $convert .= 'ยี่';
            } elseif ($i == ($strlen - 2) && $n == 1) {
                $convert .= '';
            } else {
                $convert .= $txtnum1[$n];
            }
            $convert .= $txtnum2[$strlen - $i - 1];
        }
    }
    $convert .= 'บาทถ้วน';
    return $convert;
}

// $result = [
//     'status'     => 'success',
//     'main_data'  => $main_data,
//     'child_data' => $child_data
// ];

// // ส่งออกเป็น JSON
// echo json_encode($result);
// exit();

$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'tempDir' => __DIR__ . '/../../tmp',
    'margin_top' => 5,
    'margin_bottom' => 5,
    'margin_left' => 5,
    'margin_right' => 5,
    'fontDir' => [
        __DIR__ . '/../../vendor/mpdf/mpdf/ttfonts',
        __DIR__ . '/../../assets/fonts/Prompt'
    ],
    'fontdata' => [
        'thsarabun' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'I' => 'THSarabunNew Italic.ttf',
            'BI' => 'THSarabunNew BoldItalic.ttf',
        ],
    ],
    'default_font' => 'thsarabun'
]);

$html = '

<style>
    body { font-family: "thsarabun"; font-size: 16pt; }
    table { border-collapse: collapse; width: 100%; }
    .df-table td, th {
        border: 1px solid black;
        padding: 6px;
        vertical-align: top;
    }
    td, th {
        padding: 6px;
        vertical-align: top;
    }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .table-header {
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
    }
    .signature-section {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #000; /* เส้นรอบนอก */
    }
    .signature-section td {
        padding: 5px;
        font-size: 12pt;
    }
</style>

<table class="df-table">
    <tr>
        <td style="width: 20%; height: 90px; text-align:center;">
            <img src="' . realpath(__DIR__ . '/../../assets/images/brand-logos/logo-raot.png') . '" width="50">
            <div style="margin-top: 2px; line-height: 1.2;">
                <span style="font-family: prompt; font-size: 8pt; font-weight: bold; color: #006400;">
                    การยางแห่งประเทศไทย
                </span><br>
                <span style="font-size: 4pt; color: #006400;">
                    Rubber Authority of Thailand
                </span>
            </div>
        </td>

        <td class="center bold" style="width: 60%; font-size: 20pt; vertical-align: middle;">
            การยางแห่งประเทศไทย<br>
            สัญญาการยืมเงินทดรอง
        </td>
        <td style="width: 20%; font-size: 12pt; vertical-align: middle;">
            เลขที่: '.$main_data['plan_number'].'<br>
            วันครบกำหนด: '.formatDateThaiShort($main_data['plan_end']).'
        </td>
    </tr>
    <tr>
        <td colspan="3" style="font-size: 12pt; vertical-align: middle;">
            ข้าพเจ้า '.$main_data['full_name'].' ขออนุมัติ ทดรองจ่าย ตำแหน่ง '.$main_data['position_name'].' ระดับ '.$main_data['level_name'].' สังกัด '.$main_data['affiliation_name'].'
            มีความประสงค์ที่จะขอยืมเงินทดรองจากการยางแห่งประเทศไทย เพื่อใช้จ่ายใน '.$main_data['note'].' มีรายละเอียดดังนี้
        </td>
    </tr>
</table>';

$total = 0;
$html .= '<table class="df-table">
    <tr class="center bold">
        <td class="table-header" style="width: 80%; font-size: 12pt;">รายการ</td>
        <td class="table-header" style="width: 20%; font-size: 12pt;">จำนวนเงิน (บาท)</td>
    </tr>';

// loop child_data
foreach ($child_data as $item) {
    $total += $item['total'];
    $html .= '<tr>
        <td style="width: 80%; font-size: 12pt; height: 30px;">'
            . htmlspecialchars($item['wrd_mat_code'] . ' - ' . $item['wrd_mat_name']) .
        '</td>
        <td style="width: 20%; font-size: 12pt; height: 30px; text-align: right;">'
            . number_format($item['total'], 2) .
        '</td>
    </tr>';
}

for ($i = count($child_data); $i < 6; $i++) {
    $html .= '<tr>
        <td style="width: 80%; font-size: 12pt; height: 30px;"></td>
        <td style="width: 20%; font-size: 12pt; height: 30px; text-align: right;"></td>
    </tr>';
}

$html .= '<tr>
    <td style="width: 80%; font-size: 12pt; font-weight: bold; height: 30px; text-align: center;">
        (ตัวอักษร) ' . convert_number_to_text($total) . '
    </td>
    <td style="width: 20%; font-size: 12pt; font-weight: bold; height: 30px; text-align: right;">'
        . number_format($total, 2) .
    '</td>
</tr>';

$html .= '</table>';

$html .= '
<table class="signature-section" width="100%">
    <tr>
        <td colspan="2" style="font-size: 12pt;">
            ข้าพเจ้าสัญญาว่าจะปฏิบัติตามข้อบังคับ ระเบียบหรือคำสั่งของการยางแห่งประเทศไทยทุกประการ และจะนำใบสำคัญคู่จ่ายที่ถูกต้อง รวมทั้งเงินเหลือจ่าย <br> (ถ้ามี) ส่งใช้ภายใน ................. วัน นับจากวันที่กลับมาถึง หรือ นับแต่วันที่รับเงิน ถ้าข้าพเจ้าไม่ส่งใช้ภายในที่กำหนด <br>
            ข้าพเจ้ายินยอมให้การยางแห่งประเทศไทยหักเงินเดือน ค่าจ้าง บำเหน็จ หรือเงินอื่นใด อันพึงได้รับจากการยางแห่งประเทศไทย ชดใช้เงินที่ยืมไปนี้จนครบถ้วนได้ทันที
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="font-size: 12pt; text-align: center;">
            <div style="text-align: right;">...................................................... ผู้ยืม<br>
            ('.$main_data['full_name'].')<br>
            ........................../......................../......................
            </div>
        </td>
        
    </tr>
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">เห็นควรให้ '.$main_data['full_name'].' ยืมเงินทดรองจ่ายเป็นเงิน '.number_format($total, 2).' บาท<br>
            ( '.convert_number_to_text($total).' )
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: right;">
                ...................................................... ผู้บังคับบัญชาผู้ยืม<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">เสนอ ผอ.กยท.ส. ..................<br>
            ชื่อ '.$main_data['full_name'].' ไม่มีเงินยืมค้างชำระ เห็นสมควรให้ยืมได้เป็นจำนวนเงิน '.number_format($total, 2).' บาท
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: right;">
                ...................................................... ผู้พิจารณาเสนอ<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">อนุมัติให้ยืมเงินทดรองตามเงื่อนไขข้างต้นได้เป็นเงิน '.number_format($total, 2).' บาท ('.convert_number_to_text($total).')
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: right;">
                ......................................................................... ผู้อนุมัติ<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt; font-weight: bold; text-align: center;">
            <div style="text-align: center;">ใบรับเงิน</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">ได้รับเงินยืมทดรองจำนวน '.number_format($total, 2).' บาท ไปเป็นการถูกต้องแล้ว<br>
            (ตัวอักษร) '.convert_number_to_text($total).'
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: right;">
                ......................................................................... ผู้ยืม<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table width="100%" style="border: none; border-collapse: collapse; font-size: 12pt;">
    <tr>
        <td style="border: none; text-align: left;">
            ทะเบียนหน้า.................... ผู้จดทะเบียน.............................
        </td>
        <td style="border: none; text-align: right;">
            กยท. ๙๑-๐๑-๑๓/๖๓
        </td>
    </tr>
</table>

';

$mpdf->SetHTMLFooter('
    <div style="text-align: center; font-size: 12pt;">
        หน้า {PAGENO}/{nb}
    </div>
');

$mpdf->WriteHTML($html);
// $mpdf->Output();

$filename = 'สัญญาการยืมเงินทดรอง_' . date('Y-m-d_H-i-s') . '.pdf';
$mpdf->Output($filename, 'D');
exit;
