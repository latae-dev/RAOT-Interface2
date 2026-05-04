<?php
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

// -------------------- โหลดโมเดล --------------------
require __DIR__ . '/../../configs/database.php';
require __DIR__ . '/../../models/withdraws/wrd_compensation_model.php';
require __DIR__ . '/../../models/withdraws/wrd_compensation_list_model.php';

mb_internal_encoding('UTF-8');

// -------------------- Connect DB --------------------
$database = new Database();
$db = $database->connect('raot_db_qas');

$compensationModel        = new WrdCompensationModel($db);
$compensationListModel    = new WrdCompensationListModel($db);

$main_data             = $compensationModel->readWrdCompensationOne($_GET['id']);
$detail_data           = $compensationListModel->readCompensationListOne($_GET['id']);

// -------------------- เตรียมตัวแปรจากข้อมูล --------------------
$main = $main_data;
$compensation_date          = formatDateThaiLong($main['compensation_date']);
$req_number_withdraw        = $main['req_number_withdraw'];
$req_number_pay             = $main['req_number_pay'];
$req_payee_name             = $main['req_payee_name'];
$req_position_name          = $main['req_position_name'];
$req_affiliation_name       = $main['req_affiliation_name'];
$req_address                = $main['req_address'];
$fund_id                    = $main['fund_id'];

// -------------------- Helper --------------------
function formatDateThaiLong($date)
{
    if (empty($date) || $date == '0000-00-00') return '';
    $ts = strtotime($date);
    $d = date('d', $ts);
    $m = thaiMonth(date('m', $ts));
    $y = date('Y', $ts) + 543;
    return "$d $m $y";
}
function formatDateThaiShort($date)
{
    if (empty($date) || $date == '0000-00-00') return '';
    $ts = strtotime($date);
    $d = date('d', $ts);
    $m = date('m', $ts);
    $y = (date('Y', $ts) + 543);
    return "$d-$m-" . substr($y, -2);
}
function convert_number_to_text($number)
{
    if ($number == 0 || $number === null || $number === '') return '';
    $number = number_format($number, 2, '.', '');
    $txt1 = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า', 'สิบ'];
    $txt2 = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
    $number = str_replace(",", "", $number);
    $split = explode(".", $number);
    $baht = $split[0];
    $satang = isset($split[1]) ? substr(str_pad($split[1], 2, '0', STR_PAD_RIGHT), 0, 2) : '00';
    $len = strlen($baht);
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $n = substr($baht, $i, 1);
        if ($n != 0) {
            if ($i == ($len - 1) && $n == 1) $out .= 'เอ็ด';
            elseif ($i == ($len - 2) && $n == 2) $out .= 'ยี่';
            elseif ($i == ($len - 2) && $n == 1) $out .= '';
            else $out .= $txt1[$n];
            $out .= $txt2[$len - $i - 1];
        }
    }
    $out .= 'บาท';
    if ($satang == '00') {
        $out .= 'ถ้วน';
    } else {
        $len_s = strlen($satang);
        for ($i = 0; $i < $len_s; $i++) {
            $n = substr($satang, $i, 1);
            if ($n != 0) {
                if ($i == ($len_s - 1) && $n == 1) $out .= 'เอ็ด';
                elseif ($i == ($len_s - 2) && $n == 2) $out .= 'ยี่';
                elseif ($i == ($len_s - 2) && $n == 1) $out .= '';
                else $out .= $txt1[$n];
                $out .= $txt2[$len_s - $i - 1];
            }
        }
        $out .= 'สตางค์';
    }
    return $out;
}
function arabicToThaiNumber($input)
{
    // $a = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    // $t = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
    // return str_replace($a, $t, $input);
    return $input;
}
function thaiMonth($m)
{
    $months = ["01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม", "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"];
    return $months[$m];
}
function dottedLine($text = '', $width = 300, $align = 'center', $style = 'dashed')
{
    return '<span class="dotted-line" style="width:' . $width . 'px; text-align:' . $align . '; border-bottom:1px ' . $style . ' #000; line-height:0.5;">' . $text . '</span>';
}
function radioChecked($v, $cur)
{
    return $v === $cur ? 'checked' : '';
}
function radioMark($checked, $label)
{
    return '<span style="display:inline-block;width:12px;height:12px;border:1.2px solid #333;text-align:center;line-height:12px;font-size:16px;font-family:DejaVu Sans, Sarabun;margin-right:3px;">'
        . ($checked ? '✔' : '&nbsp;') .
        '</span> ' . $label;
}
// -------------------- HTML หน้า 1 --------------------
$html1 = '
<div style="position: relative; min-height: 90px;">
    <div style="position: absolute; left: 0; top: 0;text-align: center;">
        <img src="/assets/images/brand-logos/logo-raot.png" width="55">
        <div style="margin-top: 2px; line-height: 1;">
            <p style="font-size: 10pt; font-weight: bold; color: #006400;">
                การยางแห่งประเทศไทย
            </p>
            <p style="font-size: 8pt; color: #006400; line-height: 0.7;">
                Rubber Authority of Thailand
            </p>
        </div>
    </div>
    <div class="right">
    (๑) เลขที่เบิก ' . dottedLine($req_number_withdraw, 100, 'center', 'dotted') . ' /เลขที่จ่าย ' . dottedLine($req_number_pay, 100, 'center', 'dotted') . '
    </div>
    <div style="text-align: center;">
        <div style="font-size:18pt; font-weight:bold;">ใบสั่งจ่าย</div>
        <div style="font-size:18pt; font-weight:bold;line-height:0.9;">การยางแห่งประเทศไทย</div>
    </div>
</div>
<div class="right" style="line-height:0.5;">
    (๒) วันที่ ' . dottedLine($compensation_date, 230, 'center', 'dotted') . '
</div>
<div class="left">
    (๓) จ่ายให้ ' . dottedLine($req_payee_name, 220, 'center', 'dotted') . ' ตำแหน่ง ' . dottedLine($req_position_name, 115, 'center', 'dotted') . 'ส่วนงาน/หน่วยงาน ' . dottedLine($req_affiliation_name, 179, 'center', 'dotted') . '
</div>
<div class="left">
    (๔) ที่อยู่ ' . dottedLine($req_address, 671, 'left', 'dotted') . '
</div>
<div class="left">
    (๕) เบิกจ่ายจาก กองทุนฯ/เงินทุนฯใด ให้ใส่เครื่องหมาย √ ในช่อง <input type="checkbox" style="width:14px; height:14px; vertical-align:middle; margin-right:3px;">
</div>
<table border="0">
    <tbody style="line-height:0.9;">
        <tr>
            <td width="50%" style="padding-left: 50px;">' . radioMark($fund_id === "1", "เงินทุน-เพื่อการบริหาร ๔๙ (๑)") . '</td>
            <td width="50%" style="padding-left: 10px;">' . radioMark($fund_id === "2", "เงินทุน-เพื่อสนับสนุนการปลูกแทน ๔๙ (๒)") . '</td>
        </tr>
        <tr>
            <td width="50%" style="padding-left: 50px;">' . radioMark($fund_id === "3", "เงินทุน-เพื่อการสนับสนุนเกษตรกร ๔๙ (๓)") . '</td>
            <td width="50%" style="padding-left: 10px;">' . radioMark($fund_id === "4", "เงินทุน-เพื่อการศึกษาวิจัยยางพารา ๔๙ (๔)") . '</td>
        </tr>
        <tr>
            <td width="50%" style="padding-left: 50px;">' . radioMark($fund_id === "5", "เงินทุน-เพื่อสวัสดิการเกษตร ๔๙ (๕)") . '</td>
            <td width="50%" style="padding-left: 10px;">' . radioMark($fund_id === "6", "เงินทุน-เพื่อสนับสนุนสถาบันเกษตรกร ๔๙ (๖)") . '</td>
        </tr>
        <tr>
            <td width="50%" style="padding-left: 50px;">' . radioMark($fund_id === "7", "กองทุนพัฒนายางพารา") . '</td>
            <td width="50%" style="padding-left: 10px;">' . radioMark($fund_id === "8", "อื่นๆ (ระบุ)") . dottedLine('', 270, 'center', 'dotted') . '</td>
        </tr>
    </tbody>
</table>
<table border="1" style="border:1px solid #000;margin-top:10px;">
    <thead>
        <tr>
            <th width="10%" rowspan="2" class="th-vertical-center">(๖) ลำดับที่</th>
            <th width="50%" rowspan="2" class="th-vertical-center">(๗) รายการ</th>
            <th width="40%" colspan="2" class="th-vertical-center">(๘) จำนวนเงิน (บาท)</th>
        </tr>
        <tr>
            <th class="th-vertical-center" width="30%">บาท</th>
            <th class="th-vertical-center" width="10%">สต.</th>
        </tr>
    </thead>
    <tbody>';
$item = 0;
$total_bath = 0;
$total_stang = 0;
$total_all = 0;
foreach ($detail_data as $key => $value) {
    $html1 .= '<tr>
                <td class="hide-top-bottom" style="text-align:center;">' . ($key + 1) . '</td>
                <td class="hide-top-bottom">' . $value['detail'] . '</td>
                <td class="hide-top-bottom" style="text-align:center;">' . number_format($value['bath']) . '</td>
                <td class="hide-top-bottom" style="text-align:center;">' . $value['stang'] . '</td>
            </tr>';
    $item++;
    $total_bath += $value['bath'];
    $total_stang += $value['stang'];
    if ($total_stang >= 100) {
        $total_bath += floor($total_stang / 100);
        $total_stang = $total_stang % 100;
    }
    $total_all += $value['bath'] + ($value['stang'] / 100);
}
if (count($detail_data) < 6) {
    for ($i = count($detail_data); $i < 6; $i++) {
        $html1 .= '<tr>
                    <td class="hide-top-bottom" style="text-align:center;">&nbsp;</td>
                    <td class="hide-top-bottom">&nbsp;</td>
                    <td class="hide-top-bottom" style="text-align:center;">&nbsp;</td>
                    <td class="hide-top-bottom" style="text-align:center;">&nbsp;</td>
                </tr>';
    }
}
$html1 .= '</tbody>
    <tbody>
        <tr>
            <td colspan="2" style="position: relative;">
                <div style="float:left;">&nbsp;&nbsp;(๙) (ตัวอักษร) ' . convert_number_to_text($total_all) . '</div>
                <div style="float:right;">รวม&nbsp;&nbsp;</div>
            </td>
            <td style="text-align:center;">' . number_format($total_bath) . '</td>
            <td style="text-align:center;">' . $total_stang . '</td>
        </tr>
        <tr>
            <td colspan="2" style="border-right:none;line-height:0.9;">
                <p style="margin-top:5px">&nbsp;(ส่วนผู้เบิก)&nbsp;&nbsp;&nbsp;&nbsp;(๑๐) เอกสารประกอบการเบิกเงิน จำนวน' . dottedLine('', 100, 'center', 'dotted') . 'ฉบับ</p>
                <p>&nbsp;(๑๑)' . dottedLine('', 200, 'center', 'dotted') . ' ผู้เบิก (เลขประจำตัว)</p>
                <p>
                    &nbsp;&nbsp;&nbsp;&nbsp;(' . dottedLine('', 200, 'center', 'dotted') . ')&nbsp;&nbsp;&nbsp;&nbsp;
                    <span style="display:inline-block;width:24px;height:24px;border:1px solid #000;margin-right:-5px;margin-top:7px;"></span>
                    <span style="display:inline-block;width:24px;height:24px;border:1px solid #000;margin-right:-5px;margin-top:7px;"></span>
                    <span style="display:inline-block;width:24px;height:24px;border:1px solid #000;margin-right:-5px;margin-top:7px;"></span>
                    <span style="display:inline-block;width:24px;height:24px;border:1px solid #000;margin-right:-5px;margin-top:7px;"></span>
                </p>
                <p>&nbsp;ตำแหน่ง' . dottedLine('', 175, 'center', 'dotted') . '</p>
                <p style="margin-bottom:7px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
            <td colspan="2" style="border-left:none;line-height:0.9;text-align:center;">
                <p style="margin-top:5px;">(๑๒)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เห็นควรอนุมัติเบิก</p>
                <p style="margin-top:5px;">' . dottedLine('&nbsp;', 220, 'center', 'dotted') . '</p>
                <p style="margin-top:7px;">(' . dottedLine('&nbsp;', 220, 'center', 'dotted') . ')</p>
                <p>ตำแหน่ง' . dottedLine('&nbsp;', 180, 'center', 'dotted') . '</p>
                <p style="margin-top:3px;margin-bottom:5px;">' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-right:none;line-height:0.9;">
                <p style="margin-top:5px">&nbsp;(ส่วนผู้ตรวจ)&nbsp;&nbsp;&nbsp;&nbsp;ได้ตรวจสอบหลักฐานการเบิกจ่ายเงินที่แนบถูกต้องแล้ว</p>
                <p>&nbsp;(๑๓)' . dottedLine('', 200, 'center', 'dotted') . ' ผู้ตรวจ</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;(' . dottedLine('', 200, 'center', 'dotted') . ')&nbsp;&nbsp;&nbsp;&nbsp;</p>
                <p>&nbsp;ตำแหน่ง' . dottedLine('', 175, 'center', 'dotted') . '</p>
                <p style="margin-bottom:7px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
            <td colspan="2" style="border-left:none;line-height:0.9;text-align:center;">
                <p style="margin-top:5px;">(๑๔)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เห็นควรอนุมัติให้เบิกจ่ายได้</p>
                <p style="margin-top:5px;">' . dottedLine('&nbsp;', 220, 'center', 'dotted') . '</p>
                <p style="margin-top:7px;">(' . dottedLine('&nbsp;', 220, 'center', 'dotted') . ')</p>
                <p>ตำแหน่ง' . dottedLine('&nbsp;', 180, 'center', 'dotted') . '</p>
                <p style="margin-top:3px;margin-bottom:5px;">' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
        </tr>
    </tbody>
</table>
<table border="1" style="border:1px solid #000;border-top:0px;">
    <tbody>
        <tr>
            <td colspan="2" style="border-top:0px;line-height:0.9;width:300px;">
                <p>&nbsp;(ส่วนผู้มีอำนวจอนุมัติ) (๑๕) อนุมัติให้จ่ายได้</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 200, 'center', 'dotted') . '</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;(' . dottedLine('', 200, 'center', 'dotted') . ')&nbsp;&nbsp;&nbsp;&nbsp;</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;ผู้ว่าการ กยท./ผู้ที่ผู้ว่าการ กยท.มอบหมาย</p>
                <p style="margin-bottom:7px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
            <td colspan="2" style="border-top:0px;line-height:0.9;text-align:center;">
                <div style="position: relative;">
                    <span>(๑๖) ได้รับเงินถูกต้องแล้ว</span>
                    <div style="float:left;">&nbsp;(ส่วนผู้รับเงิน)</div>
                </div>
                <br>
                <p style="line-height:0.4;">' . dottedLine('', 200, 'center', 'dotted') . ' ผู้รับเงิน</p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;(' . dottedLine('', 250, 'center', 'dotted') . ')&nbsp;&nbsp;&nbsp;&nbsp;</p>
                <p style="margin-top:7px;margin-bottom:7px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
        </tr>
    </tbody>
</table>
<table border="1" style="border:1px solid #000;border-top:0px;">
    <tbody>
        <tr>
            <td colspan="2" style="border-top:0px;border-right:0px;line-height:0.9;width:350px;">
                <p>&nbsp;(ส่วนผู้จ่ายเงิน) (๑๗)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จำนวนเงินตามใบสำคัญที่เบิก</p>
                <p style="text-align:center;">&nbsp;<ins>หัก</ins> ภาษี ณ ที่จ่าย</p>
                <p style="text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                อื่นๆ (ระบุ)' . dottedLine('&nbsp;', 150, 'center', 'dotted') . '</p>
                <p style="text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                รวมจำนวนเงินสุทธิที่จ่าย เงินสด
                </p>
                <p style="text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ใบถอน/เช็ค เลขที่' . dottedLine('&nbsp;', 115, 'center', 'dotted') . '</p>
                </p>
                <p style="margin-top:7px;">
                &nbsp;(๑๙)&nbsp;&nbsp;บันทึกบัญชีแล้ว<input type="checkbox" style="width:14px; height:14px; vertical-align:middle; margin-right:3px;">&nbsp;&nbsp;&nbsp;
                ' . dottedLine('', 180, 'center', 'dotted') . ' ผู้บันทึก
                </p>
                <p style="text-align:center;">(' . dottedLine('', 220, 'center', 'dotted') . ')</p>
                <p style="text-align:center;">ตำแหน่ง' . dottedLine('', 180, 'center', 'dotted') . '</p>
                <p style="margin-bottom:7px;text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
            <td colspan="2" style="border-top:0px;border-left:0px;line-height:0.9;text-align:center;">
                <p style="text-align:center;">' . dottedLine('', 200, 'center', 'dotted') . 'บาท</p>
                <p style="text-align:center;">' . dottedLine('', 200, 'center', 'dotted') . 'บาท</p>
                <p style="text-align:center;">' . dottedLine('', 200, 'center', 'dotted') . 'บาท</p>
                <p style="text-align:center;">' . dottedLine('', 200, 'center', 'dotted') . 'บาท</p>
                <p style="text-align:center;">' . dottedLine('', 200, 'center', 'dotted') . 'บาท</p>
                <p style="text-align:center;">(๑๘)' . dottedLine('', 200, 'center', 'dotted') . 'ผู้จ่ายเงิน</p>
                <p style="text-align:center;">(' . dottedLine('', 200, 'center', 'dotted') . ')</p>
                <p style="text-align:center;">ตำแหน่ง' . dottedLine('', 200, 'center', 'dotted') . '</p>
                <p style="margin-bottom:7px;text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '/' . dottedLine('', 50, 'center', 'dotted') . '</p>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:right;">
กยท.๙๑-๐๑-๕๓/๖๕
</p>
';
// -------------------- HTML หน้า 2   --------------------
$html2 = '
<div style="position: relative; min-height: 60px;">
    <div style="position: absolute; left: 0; top: 0;text-align: center;">
        <div style="font-size:16pt; font-weight:bold;">ช่องที่</div>
    </div>
    <div style="text-align: center;">
        <div style="font-size:18pt; font-weight:bold;">คำอธิบาย</div>
    </div>
</div>
<table border="0" style="font-size:14pt;line-height:1;">
    <tr>
        <td>
            (๑)
        </td>
        <td style="padding-left:5px;">
            <b>"เลขที่เบิก"</b> ให้เจ้าหน้าที่ผู้เบิกเป็นผู้กรอกตามเลขที่กำหนดจากระบบพัสดุ และ <b>"เลขที่จ่าย"</b> ให้เจ้าหน้าที่ผู้จ่ายเงินเป็นผู้กรอก ตามเลขที่<br>กำหนดจากระบบเงินสด โดยจะเรียงตามลำดับจนสิ้นปีงบประมาณ
        </td>
    </tr>
    <tr>
        <td>
            (๒)
        </td>
        <td style="padding-left:5px;">
            <b>"วันที่"</b> ให้กรอกวัน เดือน ปี ที่จัดทำใบสั่งจ่าย
        </td>
    </tr>
    <tr>
        <td>
            (๓)
        </td>
        <td style="padding-left:5px;">
            <b>"จ่ายให้"</b> ให้กรอกชื่อผู้มีสิทธิรับเงิน อาจเป็นคนละคนกับผู้เบิกก็ได้ (เฉพาะพนักงานให้กรอกตำแหน่งและสังกัดด้วย)
        </td>
    </tr>
    <tr>
        <td>
            (๔)
        </td>
        <td style="padding-left:5px;">
            <b>"ที่อยู่"</b> ให้กรอกที่อยู่ผู้มีสิทธิรับเงินตามสังกัดของพนักงาน สำหรับบุคคลภายนอกให้กรอกที่อยู่ให้ครบถ้วน
        </td>
    </tr>
    <tr>
        <td>
            (๕)
        </td>
        <td style="padding-left:5px;">
            <b>"เบิกจ่ายจาก"</b> กองทุนฯ/เงินทุนฯใด ให้ใส่เครื่องหมาย √ ในช่อง <input type="checkbox" style="width:14px; height:14px; vertical-align:middle; margin-right:3px;">
        </td>
    </tr>
    <tr>
        <td>
            (๖)
        </td>
        <td style="padding-left:5px;">
            <b>"ลำดับที่"</b> ให้กรอกลำดับที่ของรายการที่เบิก
    </tr>
    <tr>
        <td>
            (๗)
        </td>
        <td style="padding-left:5px;">
            <b>"รายการ"</b> ให้กรอกชื่อรายการที่ต้องการเบิกและเลขเอกสารที่ขอรับเงินให้ชัดเจน กรณี <b>"จ่ายเพื่อทดแทน"</b> (คือ การเบิกที่ได้ทดรองจ่ายเงิน<br>ของตนไปก่อนแล้ว) เอกสารขอรับเงินเช่น ใบเสร็จรับเงินค่ารักษาพยาบาล หรือ ค่าเล่าเรียนบุตร เป็นต้น <br>กรณี <b>"จ่ายเพื่อชำระหนี้"</b> (คือการเบิกเพื่อชำระค่าสินค้าและบริการแก่ร้านค้าบุคคลภายนอก) เอกสารขอรับเงิน เป็นเอกสารที่ถูกต้องตาม<br>ความนิยมทางธุรกิจ เช่น บิลเงินสด อินวอยซ์ใบแจ้งหนี้ หรือใบส่งของ เป็นต้น
        </td>
    </tr>
    <tr>
        <td>
            (๘)
        </td>
        <td style="padding-left:5px;">
            <b>"จำนวนเงิน"</b> ให้กรอกจำนวนเงินที่เบิก และยอดเงินรวม ถ้ามีมากกว่าหนึ่งรายการ ให้กรอกทีละรายการและยอดเงินรวม
        </td>
    </tr>
    <tr>
        <td>
            (๙)
        </td>
        <td style="padding-left:5px;">
            <b>"ตัวอักษร"</b> ให้กรอกจำนวนเงินรวมเป็นตัวอักษร
        </td>
    </tr>
    <tr>
        <td>
            (๑๐)
        </td>
        <td style="padding-left:5px;">
            <b>"เอกสารประกอบการเบิกเงิน"</b> ให้กรอกจำนวนเอกสารที่เบิกโดยนับแผ่นละ ๑ ฉบับ
        </td>
    </tr>
    <tr>
        <td>
            (๑๑)
        </td>
        <td style="padding-left:5px;">
            <b>"ผู้เบิก"</b> ให้ผู้เบิกลงลายมือชื่อ และชื่อ-สกุลตำแหน่งวัน เดือน ปี เลขประจำตัวผู้เบิก กรณี <b>"จ่ายเพื่อทดแทน"</b> ให้พนักงานที่ได้ทดรองจ่าย<br>เงินของตนไปก่อน ลงลายมือชื่อผู้เบิก กรณี <b>"จ่ายเพื่อชำระหนี้"</b> ให้พนักงานผู้มีหน้าที่ในงานนั้นๆ ลงลายมือชื่อผู้เบิกเช่น เจ้าหน้าที่พัสดุ
        </td>
    </tr>
    <tr>
        <td>
            (๑๒)
        </td>
        <td style="padding-left:5px;">
            <b>"เห็นควรอนุมัติเบิก"</b> <b>กรณีจ่ายเพื่อทดแทน</b> ให้ผู้บังคับบัญชาระดับต้นของผู้เบิกลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี กรณี หัวหน้า<br>กองหรือเทียบเท่าขึ้นไปเป็นผู้เบิก ให้ผู้เบิกลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี ได้เลย <b>กรณีจ่ายเพื่อชำระหนี้</b> ให้ผู้บังคับบัญชา<br>ระดับต้นของผู้มีหน้าที่ในงานนั้นๆลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี ยกเว้น ในส่วนกลางของ ฝ่ายบริหารทรัพย์สิน ให้ ผอ.ฝ่าย ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี
        </td>
    </tr>
    <tr>
        <td>
            (๑๓)
        </td>
        <td style="padding-left:5px;">
            <b>"ผู้ตรวจ"</b> ให้พนักงานผู้ตรวจสอบใบสั่งจ่าย ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือนปี เมื่อได้ทำการตรวจใบสั่งจ่ายนั้นและเห็นว่าถูกต้อง
        </td>
    </tr>
    <tr>
        <td>
            (๑๔)
        </td>
        <td style="padding-left:5px;">
            <b>"เห็นควรอนุมัติให้เบิกจ่ายได้"</b> ให้ผู้บังคับบัญชาของผู้ตรวจสอบใบสั่งจ่ายระดับกองหรือเทียบเท่า ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี สำหรับหน่วยงานระดับสาขา ให้หัวหน้าแผนกลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี สำหรับส่วนงานหรือหน่วยงาน<br>ที่ไม่มีหัวหน้ากองหรือเทียบเท่าหรือหัวหน้าแผนก ให้ผู้บังคับบัญชาเหนือกว่าขึ้นไปเป็นผู้ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี หรือผู้ที่บุคคลดังกล่าวมอบหมายลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี
        </td>
    </tr>
    <tr>
        <td>
            (๑๕)
        </td>
        <td style="padding-left:5px;">
            <b>"อนุมัติให้จ่ายได้"</b> ให้ผู้ว่าการการยางแห่งประเทศไทยหรือผู้ที่ผู้ว่าการการยางแห่งประเทศไทยมอบหมายให้อนุมัติสั่งจ่ายเงินได้ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี ที่อนุมัติ
        </td>
    </tr>
    <tr>
        <td>
            (๑๖)
        </td>
        <td style="padding-left:5px;">
            <b>"ได้รับเงินถูกต้องแล้ว"</b> สำหรับการ <b>"จ่ายเพื่อทดแทน"</b> ให้พนักงานที่ได้ทดรองจ่ายเงินของตนไปก่อน ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง สำหรับ <b>"จ่ายเพื่อชำระหนี้"</b> ให้บุคคลภายนอกโดยโอนเงินเข้าบัญชีไม่ต้องลงลายมือชื่อรับเงินก็ได้ และให้ผู้รับเงินออกใบเสร็จรับเงินด้วย
        </td>
    </tr>
    <tr>
        <td>
            (๑๗)
        </td>
        <td style="padding-left:5px;">
            <b>"ส่วนผู้จ่ายเงิน"</b> ให้เจ้าหน้าที่ผู้จ่ายเงิน กรอกจำนวนเงินตามใบสำคัญที่เบิกและกรอกรายการที่หัก หากไม่มีรายการหักใดๆ ให้ขีด' . dottedLine('"-"', 50, 'center', 'dotted') . '
และกรอกรวมจำนวนเงินสุทธิที่จ่าย (อาจจะจ่ายเป็นเงินสด ใบถอนเช็คแล้วแต่กรณี)
        </td>
    </tr>
    <tr>
        <td>
            (๑๘)
        </td>
        <td style="padding-left:5px;">
            <b>"ผู้จ่ายเงิน"</b> ให้เจ้าหน้าที่การเงินผู้จ่ายเงินลงลายมือชื่อ และชื่อ-สกุลตำแหน่ง วันเดือนปี ที่จ่ายเงิน
        </td>
    </tr>
    <tr>
        <td>
            (๑๙)
        </td>
        <td style="padding-left:5px;">
            <b>"บันทึกบัญชีแล้ว"</b> ให้ใส่เครื่องหมาย √ ในช่อง <input type="checkbox" style="width:14px; height:14px; vertical-align:middle; margin-right:3px;"> เมื่อบันทึกบัญชีเรียบร้อยแล้ว ให้ลงลายมือชื่อ และชื่อ-สกุล ตำแหน่ง วัน เดือน ปี
        </td>
    </tr>
</table>
<table border="0" style="font-size:14pt;line-height:1;">
    <tr>
        <td style="font-size:16pt; font-weight:bold;">
            <ins>หมายเหตุ</ins>
        </td>
        <td style="padding-left:5px;">
            ๑.
        </td>
        <td style="padding-left:5px;">
            ให้เจ้าหน้าที่การเงิน ผู้จ่ายเงิน ประทับตราข้อความว่า <b>"จ่ายเงินแล้ว"</b> โดยลงลายมือชื่อ วัน เดือนปี ไว้ใน หลักฐานการจ่าย เช่น<br>ใบเสร็จรับเงิน หรือใบรับรองการจ่ายเงินทุกฉบับ ซึ่งแสดงว่าได้มีการจ่ายเงินให้แก่เจ้าหนี้ หรือผู้รับเงิน ตามข้อผูกพันแล้ว
        </td>
    </tr>
    <tr>
        <td></td>
        <td style="padding-left:5px;">
            ๒.
        </td>
        <td style="padding-left:5px;">
            ใบเสร็จรับเงินที่ผู้รับเงินออกให้จะต้องมีรายการครบถ้วนตามระเบียบกระทรวงการคลัง
        </td>
    </tr>
    <tr>
        <td></td>
        <td style="padding-left:5px;">
            ๓.
        </td>
        <td style="padding-left:5px;">
            การจ่ายเงินทุกครั้งให้เรียกใบเสร็จรับเงินจากผู้รับเงิน แต่บางลักษณะไม่อาจเรียกได้ ให้ผู้รับเงินลงลายมือชื่อ วัน เดือน ปี รับเงิน<br>ในใบสำคัญรับเงิน ตามแบบที่การยางแห่งประเทศไทยกำหนด เพื่อใช้เป็นหลักฐานการจ่าย
        </td>
    </tr>
</table>
';
// -------------------- ตั้งค่า Dompdf (ฟอนต์/เพจ/แคช) --------------------
$root = realpath(__DIR__ . '/../../'); // /var/www/html

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Sarabun');
$options->setChroot($root);

@mkdir($root . '/storage/dompdf_font_cache', 0777, true);
@mkdir($root . '/storage/dompdf_tmp', 0777, true);
$options->set('fontCache', $root . '/storage/dompdf_font_cache');
$options->set('tempDir',   $root . '/storage/dompdf_tmp');

// -------------------- พาธฟอนต์ (อยู่ใต้ chroot) --------------------
// $fontRegular = '/assets/fonts/Prompt/THSarabunNew.ttf';
// $fontBold    = '/assets/fonts/Prompt/THSarabunNew Bold.ttf';
// $fontItalic  = '/assets/fonts/Prompt/THSarabunNew Italic.ttf';
// $fontBI      = '/assets/fonts/Prompt/THSarabunNew BoldItalic.ttf';

$fontRegular = '/assets/fonts/Prompt/thsarabunpsk.ttf';
$fontBold    = '/assets/fonts/Prompt/thsarabunpsk Bold.ttf';
$fontItalic  = '/assets/fonts/Prompt/thsarabunpsk Italic.ttf';
$fontBI      = '/assets/fonts/Prompt/thsarabunpsk BoldItalic.ttf';

// -------------------- CSS รวม --------------------
$globalCss = "
  @font-face {
    font-family:'Sarabun';
    src:url('{$fontRegular}') format('truetype');
    font-weight:400;
    font-style:normal;
  }
  @font-face {
    font-family:'Sarabun';
    src:url('{$fontBold}') format('truetype');
    font-weight:700;
    font-style:normal;
  }
  @font-face {
    font-family:'Sarabun';
    src:url('{$fontItalic}') format('truetype');
    font-weight:400;
    font-style:italic;
  }
  @font-face {
    font-family:'Sarabun';
    src:url('{$fontBI}') format('truetype');
    font-weight:700;
    font-style:italic;
  }

  @page {
    margin-top: 10mm;
    margin-right: 10mm;
    margin-bottom: 10mm;
    margin-left: 10mm;
  }

  html, body {
    font-family:'Sarabun', sans-serif;
    font-size:13pt;
    line-height:1;     
  }

  table { border-collapse:collapse; width:100%; }

  td, th {
    padding:1px;
    vertical-align:top;
    /* ห้าม fix height คงที่ เพราะจะตัดสระวรรณยุกต์ */
    /* height:40px;  <- อย่าใช้ */
    min-height:10px;
  }

  .th-vertical-center {
    vertical-align:middle;
  }

  * { overflow: visible !important; } /* กัน clipping */

  b, strong, .bold { font-weight:700; }

  .dotted-line { border-bottom:1px dashed #000; display:inline-block; text-align:center; }


  /* บล็อกที่จะเริ่มหน้าใหม่และใช้เพจ landscape */
  .landscape {
    display: block;               /* ให้เป็น block แน่ ๆ */
    page: landscape;              /* ใช้เพจที่ตั้งชื่อไว้ */
    page-break-before: always;    /* เริ่มหน้าใหม่ก่อนบล็อกนี้ */
    /* Dompdf-specific fallback (กันบางเวอร์ชันไม่อ่าน named page) */
    -dompdf-page-size: A4 landscape;
    -dompdf-page-orientation: landscape;
  }

  /*table { border-collapse: collapse; width: 100%; }
  td, th { padding: 4px; vertical-align: top; height: 10px; }*/
  p { margin: 0px 0; }
  .center { text-align:center; }
  .right { text-align:right; }
  .left { text-align:left; }
  .signature-box { border: 1px solid #000; padding: 20px; vertical-align: top; height: 150px; }
  /*input[type='radio'] {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 18px;
    height: 18px;
    border: 1px solid #333;
    border-radius: 10px;
    display: inline-block;
    position: relative;
    cursor: pointer;
    top: 12px;
  }

  input[type='radio']:checked::after {
    content: '✔';
    font-size: 25px;
    color: black;
    position: absolute;
    top: -6px;
    left: 1px;
  }*/

  .hide-top-bottom {
    border-top:0px !important;
    border-bottom:0px !important;
  }
";

// -------------------- Dompdf Config --------------------
$root = realpath(__DIR__ . '/../../');
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Sarabun');
$options->setChroot($root);
$options->set('tempDir', $root . '/storage/dompdf_tmp');

// -------------------- Render หน้า 1–2 (Portrait) --------------------
$dompdf1 = new Dompdf($options);
$dompdf1->setPaper('A4', 'portrait');
$dompdf1->loadHtml('<html><head><style>' . $globalCss . '</style></head><body>' . $html1 . '<div style="page-break-before:always"></div>' . $html2 . '</body></html>');
$dompdf1->render();
$pdf1 = $dompdf1->output();
// -------------------- รวม PDF ด้วย FPDI --------------------
$tmp1 = tempnam(sys_get_temp_dir(), 'p1');
file_put_contents($tmp1, $pdf1);

$final = new Fpdi();
foreach ([$tmp1] as $file) {
    $pageCount = $final->setSourceFile($file);
    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $final->importPage($i);
        $size = $final->getTemplateSize($tpl);
        $final->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $final->useTemplate($tpl);
    }
}

// -------------------- Output --------------------
if (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ใบสั่งจ่าย.pdf"');
$final->SetTitle("ใบสั่งจ่าย", true);
$final->Output('I');
exit;
