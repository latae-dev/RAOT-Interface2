<?php
$basePath = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../../';

require $basePath . '/vendor/composer/ClassLoader.php';

$autoloader = new Composer\Autoload\ClassLoader();

$psr4 = require $basePath . '/vendor/composer/autoload_psr4.php';
foreach ($psr4 as $prefix => $paths) {
    $autoloader->setPsr4($prefix, $paths);
}

$psr0 = require $basePath . '/vendor/composer/autoload_namespaces.php';
foreach ($psr0 as $prefix => $paths) {
    $autoloader->set($prefix, $paths);
}

$classMap = require $basePath . '/vendor/composer/autoload_classmap.php';
if ($classMap) {
    $autoloader->addClassMap($classMap);
}

$autoloader->register();

$files = require $basePath . '/vendor/composer/autoload_files.php';
foreach ($files as $fileIdentifier => $file) {
    if (!isset($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
        require $file;
    }
}

use Dompdf\Dompdf;
use Dompdf\Options;

header("Content-Type: application/pdf");
require __DIR__ . '/../../configs/database.php';
require __DIR__ . '/../../models/withdraws/wrd_plan_model.php';
require __DIR__ . '/../../models/withdraws/wrd_plan_list_model.php';
require __DIR__ . '/../../models/withdraws/wrd_number_model.php';

$database = new Database();
$db = $database->connect('raot_db_dev');

$wrdPlanModel   = new WrdPlanModel($db);
$wrdNumberModel = new WrdNumberModel($db);
$planListModel  = new PlanListModel($db);

$main_data  = $wrdPlanModel->readPlanOne($_GET['id']);
$child_data = $planListModel->readPlanListOne($_GET['id']);
$child_data = is_array($child_data) ? $child_data : [];
$count_page_detail = ceil(count($child_data) / 6);

function formatDateThaiShort($date)
{
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

function convert_number_to_text($number)
{
    $number = number_format($number, 2, '.', '');
    $txtnum1 = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า', 'สิบ'];
    $txtnum2 = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
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
            $txtnum2_index = $strlen - $i - 1;
            if (isset($txtnum2[$txtnum2_index])) {
                $convert .= $txtnum2[$txtnum2_index];
            }
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

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->setChroot($basePath);
$options->setDefaultFont('THSarabunNew');
$options->setTempDir(__DIR__ . '/../../tmp');

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4');

$fontPath = realpath(__DIR__ . '/../../assets/fonts/Prompt') ?: __DIR__ . '/../../assets/fonts/Prompt';
$logoPath = realpath(__DIR__ . '/../../assets/images/brand-logos/logo-raot.png') ?: __DIR__ . '/../../assets/images/brand-logos/logo-raot.png';
$fontPath = str_replace('\\', '/', $fontPath);
$logoPath = str_replace('\\', '/', $logoPath);

$planNumber = htmlspecialchars($main_data['plan_number'] ?? '', ENT_QUOTES, 'UTF-8');
$planEnd    = htmlspecialchars(formatDateThaiShort($main_data['plan_end'] ?? ''), ENT_QUOTES, 'UTF-8');
$fullName   = htmlspecialchars($main_data['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
$position   = htmlspecialchars($main_data['position_name'] ?? '', ENT_QUOTES, 'UTF-8');
$level      = htmlspecialchars($main_data['level_name'] ?? '', ENT_QUOTES, 'UTF-8');
$affiliation = htmlspecialchars($main_data['affiliation_name'] ?? '', ENT_QUOTES, 'UTF-8');
$note       = htmlspecialchars($main_data['note'] ?? '', ENT_QUOTES, 'UTF-8');
$html = '';
$total = 0;
$sumTotal = 0;
foreach ($child_data as $item) {
    $sumTotal += $item['total'];
}
for ($page = 0; $page < $count_page_detail; $page++) {
    $html .= <<<HTML
<style>
    @page {
        margin-top: 5mm;
        margin-right: 5mm;
        margin-bottom: 15mm;
        margin-left: 5mm;
    }
    @font-face {
        font-family: "THSarabunNew";
        src: url("file://{$fontPath}/THSarabunNew.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: "THSarabunNew";
        src: url("file://{$fontPath}/THSarabunNew Bold.ttf") format("truetype");
        font-weight: bold;
        font-style: normal;
    }
    @font-face {
        font-family: "Prompt";
        src: url("file://{$fontPath}/Prompt-Regular.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: "Prompt";
        src: url("file://{$fontPath}/Prompt-Bold.ttf") format("truetype");
        font-weight: bold;
        font-style: normal;
    }
    body { font-family: "THSarabunNew"; font-size: 16pt; margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    .df-table td, th {
        border: 1px solid black;
        padding: 2px;
        vertical-align: middle;
    }
    td, th {
        padding: 2px;
        vertical-align: middle;
    }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .table-header {
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }
    .df-table tr.data-row td,
    .df-table tr.blank-row td {
        height: 25px;
        vertical-align: middle;
    }
    .df-table tr.total-row td {
        height: 25px;
        vertical-align: middle;
    }
    .signature-section {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #000;
    }
    .signature-section td {
        padding: 5px;
        font-size: 12pt;
        vertical-align: top;
        min-height: 10px;
    }
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 12pt;
    }
    .page-number:before {
        content: "หน้า " counter(page) "/" counter(pages);
    }
</style>

<table class="df-table">
    <tr>
        <td style="width: 20%; height: 90px; text-align:center;">
            <img src="file://{$logoPath}" width="50">
            <div style="margin-top: 2px; line-height: 0.4;">
                <span style="font-family: 'Prompt'; font-size: 8pt; font-weight: bold; color: #006400;">
                    การยางแห่งประเทศไทย
                </span><br>
                <span style="font-size: 4pt; color: #006400;">
                    Rubber Authority of Thailand
                </span>
            </div>
        </td>
        <td class="center bold" style="width: 60%; font-size: 20pt; vertical-align: middle;">
            <div style="line-height: 0.8;">
                การยางแห่งประเทศไทย<br>
                สัญญาการยืมเงินทดรอง
            </div>
        </td>
        <td style="width: 20%; font-size: 12pt; vertical-align: middle;">
            <div style="line-height: 0.8;">
                เลขที่: {$planNumber}<br>
                วันครบกำหนด: ......................
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="font-size: 12pt; vertical-align: middle; line-height: 0.9;">
            ข้าพเจ้า {$fullName} ขออนุมัติ ทดรองจ่าย ตำแหน่ง {$position} ระดับ {$level} สังกัด {$affiliation} <br>
            มีความประสงค์ที่จะขอยืมเงินทดรองจากการยางแห่งประเทศไทย เพื่อใช้จ่ายใน {$note} มีรายละเอียดดังนี้
        </td>
    </tr>
</table>
HTML;

    $html .= <<<HTML
<table class="df-table">
    <tr class="center bold">
        <td class="table-header" style="width: 80%; font-size: 12pt; vertical-align: middle;">รายการ</td>
        <td class="table-header" style="width: 20%; font-size: 12pt; vertical-align: middle;">จำนวนเงิน (บาท)</td>
    </tr>
HTML;
    $start = $page * 6;
    $end = min($start + 6, count($child_data));
    for ($i = $start; $i < $end; $i++) {
        $item = $child_data[$i];
        $total += $item['total'];
        $itemLabel = htmlspecialchars(($item['wrd_mat_code'] ?? '') . ' - ' . ($item['wrd_mat_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $itemAmount = number_format($item['total'], 2);
        $html .= <<<HTML
    <tr class="data-row">
        <td style="width: 80%; font-size: 12pt; vertical-align: middle;">{$itemLabel}</td>
        <td style="width: 20%; font-size: 12pt; text-align: right; vertical-align: middle;">{$itemAmount}</td>
    </tr>
HTML;
    }
    // เติม blank-row ถ้าข้อมูลไม่ครบ 6 รายการ
    for ($j = $end; $j < $start + 6; $j++) {
        $html .= <<<HTML
    <tr class="blank-row">
        <td style="width: 80%; font-size: 12pt; vertical-align: middle;"></td>
        <td style="width: 20%; font-size: 12pt; text-align: right; vertical-align: middle;"></td>
    </tr>
HTML;
    }
    // เฉพาะหน้าสุดท้ายเท่านั้นที่แสดงผลรวม
    if ($page == $count_page_detail - 1) {
        $totalFormatted = number_format($total, 2);
        $totalText = htmlspecialchars(convert_number_to_text($total), ENT_QUOTES, 'UTF-8');
        $html .= <<<HTML
    <tr class="total-row">
        <td style="width: 80%; font-size: 12pt; font-weight: bold; text-align: center;">(ตัวอักษร) {$totalText}</td>
        <td style="width: 20%; font-size: 12pt; font-weight: bold; text-align: right;">{$totalFormatted}</td>
    </tr>
HTML;
    } else {
        $html .= <<<HTML
    <tr class="total-row">
        <td style="width: 80%; font-size: 12pt; font-weight: bold; text-align: center;"></td>
        <td style="width: 20%; font-size: 12pt; font-weight: bold; text-align: right;"></td>
    </tr>
HTML;
    }
    $html .= "</table>";
    // ส่วนลายเซ็นและใบรับเงิน (ทุกหน้า)
    $totalFormatted = number_format($sumTotal, 2);
    $totalText = htmlspecialchars(convert_number_to_text($sumTotal), ENT_QUOTES, 'UTF-8');
    $html .= <<<HTML
<table class="signature-section" width="100%" style="line-height: 0.8;">
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
            <div style="text-align: center;">
                ...................................................... ผู้ยืม<br>
                <div style="text-align: center;">({$fullName})</div>
                <div style="text-align: center;">........................../......................../......................</div>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">เห็นควรให้ {$fullName} ยืมเงินทดรองจ่ายเป็นเงิน {$totalFormatted} บาท<br>
            ( {$totalText} )
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: center;">
                ...................................................... ผู้บังคับบัญชาผู้ยืม<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%" style="line-height: 0.7;">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">เสนอ ผอ.กยท.ส. .....................................................................................................................................<br>
            ชื่อ {$fullName}...................................................................................................................<br>
            ..................................................................................................................................................................<br>
            ..................................................................................................................................................................<br>
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: center;">
                ...................................................... ผู้พิจารณาเสนอ<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%" style="line-height: 0.7;">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">อนุมัติให้ยืมเงินทดรองตามเงื่อนไขข้างต้นได้เป็นเงิน {$totalFormatted} บาท ({$totalText})
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;">
        </td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: center;">
                ......................................................................... ผู้อนุมัติ<br>
                (.........................................................................................)<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table class="signature-section" width="100%" style="line-height: 0.7;">
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt; font-weight: bold; text-align: center;">
            <div style="text-align: center;">ใบรับเงิน</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" width="100%" style="font-size: 12pt;">
            <div style="text-align: left;">ได้รับเงินยืมทดรองจำนวน {$totalFormatted} บาท ไปเป็นการถูกต้องแล้ว<br>
            (ตัวอักษร) {$totalText}
            </div>
        </td>
    </tr>
    <tr>
        <td width="70%" style="font-size: 12pt; text-align: right;"></td>
        <td width="30%" style="height: 50px; font-size: 12pt; text-align: center;">
            <div style="text-align: center;">
                ......................................................................... ผู้ยืม<br>
                ........................../......................../......................
            </div>
        </td>
    </tr>
</table>

<table width="100%" style="border: none; border-collapse: collapse; font-size: 12pt; line-height: 0.8;">
    <tr>
        <td style="border: none; text-align: left;">
            ทะเบียนหน้า.................... ผู้จดทะเบียน.............................
        </td>
        <td style="border: none; text-align: right;">
            กยท. ๙๑-๐๑-๑๓/๖๓
        </td>
    </tr>
</table>
<div class="footer"><span class="page-number"></span></div>
HTML;
}

$dompdf->loadHtml($html);
$dompdf->render();

$filename = 'สัญญาการยืมเงินทดรอง_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
