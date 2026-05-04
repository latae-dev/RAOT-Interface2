<?php
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

// -------------------- โหลดโมเดล --------------------
require __DIR__ . '/../../configs/database.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_model.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_list_model.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_group_model.php';

mb_internal_encoding('UTF-8');

// -------------------- Connect DB --------------------
$database = new Database();
$db = $database->connect('raot_db_dev');

$expensesModel        = new ExpensesModel($db);
$expensesListModel    = new ExpensesListModel($db);
$expensesGroupModel   = new ExpensesGroupModel($db);

$main_data            = $expensesModel->readExpensesOne($_GET['id']);
$child_data           = $expensesListModel->readListDetail($_GET['id']);
$traveling_group_data = $expensesGroupModel->readGroupOne($_GET['id']);

// -------------------- เตรียมตัวแปรจากข้อมูล --------------------
$main = $main_data;

$plan_number       = arabicToThaiNumber($main['plan_number']);
$plan_total        = arabicToThaiNumber($main['plan_total']);
$expenses_number       = arabicToThaiNumber($main['expenses_number']);
$full_name         = $main['full_name'];
$position_name     = $main['position_name'];
$level_name        = $main['level_name'];
$affiliation       = $main['affiliation_name'];
$expenses_learn       = $main['expenses_learn'];
$depart_from       = $main['expenses_start_location'];
$expenses_vehicle  = $main['expenses_vehicle'];
$expenses_group    = ($main['expenses_group'] == 1 || $main['expenses_group'] == 3) ? '1' : '2';
$expenses_action   = $main['expenses_action'];
$arrive_to         = $main['expenses_end_location'];
$text_approve      = $main['text_approve'];
$position_withdraw      = $main['position_withdraw'];
if (count($traveling_group_data) > 2) {
  $text_approve = $traveling_group_data[0]['fullname'] . ' ตำแหน่ง' . $traveling_group_data[0]['position'] . ' และคณะ';
} else if (count($traveling_group_data) > 1) {
  $text_approve = $traveling_group_data[0]['fullname'] . ' ตำแหน่ง' . $traveling_group_data[0]['position'] . ' และ ' . $traveling_group_data[1]['fullname'] . ' ตำแหน่ง' . $traveling_group_data[1]['position'];
} else if (count($traveling_group_data) == 1) {
  $text_approve = $traveling_group_data[0]['fullname'] . ' ตำแหน่ง' . $traveling_group_data[0]['position'];
}
$group_count       = arabicToThaiNumber($main['expenses_group_count']);
$expenses_date     = arabicToThaiNumber(formatDateThaiLong($main['expenses_date']));

$expense_day = date("d", strtotime($main['expenses_date']));
$expense_month = date("m", strtotime($main['expenses_date']));
$expense_year = date("Y", strtotime($main['expenses_date'])) + 543;

$start_date  = date("d", strtotime($main['expenses_start_date']));
$start_month = date("m", strtotime($main['expenses_start_date']));
$start_year  = date("Y", strtotime($main['expenses_start_date'])) + 543;
$start_time  = date("H:i", strtotime($main['expenses_start_date']));

$end_date    = date("d", strtotime($main['expenses_end_date']));
$end_month   = date("m", strtotime($main['expenses_end_date']));
$end_year    = date("Y", strtotime($main['expenses_end_date'])) + 543;
$end_time    = date("H:i", strtotime($main['expenses_end_date']));

$total_allowance      = 0;
$total_stay           = 0;
$total_transport      = 0;
$total_sum            = 0;
$total_other_expenses = 0;
$total_meal           = 0;

foreach ($traveling_group_data as $r) {
  $total_allowance      += $r['total_allowance'];
  $total_stay           += $r['accommodation_total'];
  $total_transport      += $r['transport'];
  $total_other_expenses += $r['other_expenses'];
  $total_meal           += $r['meal_total'];
  $total_sum            += $r['sum'];
}

$expenses_code                 = arabicToThaiNumber($main['expenses_code']);
$expenses_allowance            = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_allowance'], 2)) : '';
$expenses_allowance_days       = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_allowance_days'], 1)) : '';
$allowance_total               = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_allowance_total'], 2)) : arabicToThaiNumber(number_format($total_allowance, 2));
$expenses_accommodation        = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_accommodation'], 2)) : '';
$expenses_accommodation_days   = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_accommodation_days'], 1)) : '';
$accommodation_total           = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_accommodation_total'], 2)) : arabicToThaiNumber(number_format($total_stay, 2));
$expenses_transportation       = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_transportation'], 2)) : 'รายละเอียดตามเอกสารแนบ';
$transport_total               = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_transportation_total'], 2)) : arabicToThaiNumber(number_format($total_transport, 2));
if (!empty($main['expenses_moving'])) {
  $expenses_moving               = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_moving'], 1)) : '';
} else {
  $expenses_moving               = '';
}
$expenses_moving_total         = !empty($main['expenses_moving_total']) ? arabicToThaiNumber(number_format($main['expenses_moving_total'], 2)) : '';
$expenses_vehicle_number       = !empty($main['expenses_vehicle_number']) ? arabicToThaiNumber($main['expenses_vehicle_number']) : '';
$expenses_other                = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_other'], 2)) : 'รายละเอียดตามเอกสารแนบ';
$expenses_other_remark         = ($expenses_group == 1) ? $main['expenses_other_remark'] : '';
$expenses_transportation_remark = ($expenses_group == 1) ? $main['expenses_transportation_remark'] : '';
$other_total                   = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_other_total'], 2)) : arabicToThaiNumber(number_format($total_other_expenses, 2));
$expenses_food_per_meal        = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_food_per_meal'], 2)) : '-';
$expenses_food_meals           = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_food_meals'], 1)) : '-';
$food_total                    = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main['expenses_food_total'], 2)) : arabicToThaiNumber(number_format($total_meal, 2));
$total_expenses                = arabicToThaiNumber(number_format($main['expenses_total'], 2));
$plan_total                    = arabicToThaiNumber($main['plan_total']);
$expenses_amount               = !empty($main['expenses_amount']) ? arabicToThaiNumber(number_format($main['expenses_amount'], 2)) : '';
$total_text                    = convert_number_to_text($main['expenses_total']);

$child_total_val = 0;
foreach ($child_data as $v) {
  $child_total_val += $v['compensation'];
}
$child_total = arabicToThaiNumber(number_format($child_total_val, 2));

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
  $checked = filter_var($checked, FILTER_VALIDATE_BOOLEAN);

  return '<style>
    .radio {
    display:inline-block;
    position:relative;
    width:12px;
    height:12px;
    border:1.2px solid #000;
    border-radius:50%;
    vertical-align:middle;
    top:6px;
    margin-right:-2px;
  }

  .radio .dot {
    position:absolute;
    top:50%;
    left:50%;
    width:8px;
    height:8px;
    background:#000;
    border-radius:50%;
    transform:translate(-50%, -50%);
  }
  </style>
  <span class="radio">
    '.($checked ? '<span class="dot"></span>' : '').'
  </span>
  '.$label;
}

// -------------------- เตรียมข้อมูล --------------------
$full_name      = $main_data['full_name'] ?? '';
$position_name  = $main_data['position_name'] ?? '';
$total_expenses = ($expenses_group == 1) ? arabicToThaiNumber(number_format($main_data['expenses_total'], 2)) ?? 0 : arabicToThaiNumber(number_format($total_sum + $child_total_val, 2));
$total_text     = ($expenses_group == 1) ? convert_number_to_text($main_data['expenses_total']) : convert_number_to_text($total_sum + $child_total_val);

// -------------------- HTML หน้า 1 --------------------
$html1 = '
<div class="right bold" style="font-size:18pt;line-height:0.9;">
  <p style="font-size:16pt;">' . $expenses_number . '</p>
  ส่วนที่ 1
</div>

<div class="center bold" style="font-size:18pt;">
  ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน บุคคลภายนอก (เทียบตำแหน่ง)
</div>

<p class="right">การยางแห่งประเทศไทย ' . dottedLine($affiliation, 280, 'center', 'dotted') . ' </p>

<p class="right">
วันที่ ' . dottedLine(arabicToThaiNumber($expense_day), 50, 'center', 'dotted') . ' เดือน ' . dottedLine(thaiMonth($expense_month), 90, 'center', 'dotted') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($expense_year), 50, 'center', 'dotted') . '
</p>

<p>เรื่อง ขออนุมัติเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>
<p>เรียน ' . dottedLine($expenses_learn, 280, 'center', 'dotted') . '</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ตามคำสั่ง/บันทึก ที่ ' . dottedLine($expenses_code, 260, 'center', 'dotted') . ' ลงวันที่ ' . dottedLine($expenses_date, 195, 'center', 'dotted') . ' ได้อนุมัติให้
</p>
<p style="margin-top: 3px;">' . dottedLine('&nbsp;&nbsp;&nbsp;' . $text_approve, 715, 'left', 'dotted') . '</p>
<p>' . dottedLine('', 255, 'center', 'dotted') . ' จำนวน ' . dottedLine($group_count, 50, 'center', 'dotted') . ' คน &nbsp; เดินทางไปปฏิบัติงานตามที่ได้รับอนุมัติโดยออกเดินทางจาก
</p>

<p>
' . radioMark($depart_from === "address", "ที่อยู่") . '
' . radioMark($depart_from === "office", "สำนักงาน") . '
' . radioMark($depart_from === "thailand", "ประเทศไทย") . '
วันที่ ' . dottedLine(arabicToThaiNumber($start_date), 50, 'center', 'dotted') . ' เดือน ' . dottedLine(thaiMonth($start_month), 125, 'center', 'dotted') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($start_year), 80, 'center', 'dotted') . ' เวลา ' . dottedLine(arabicToThaiNumber($start_time), 80, 'center', 'dotted') . ' น.
</p>

<p>
และกลับถึง  
' . radioMark($arrive_to === "address", "ที่อยู่") . '
' . radioMark($arrive_to === "office", "สำนักงาน") . '
' . radioMark($arrive_to === "thailand", "ประเทศไทย") . '
วันที่ ' . dottedLine(arabicToThaiNumber($end_date), 30, 'center', 'dotted') . ' เดือน ' . dottedLine(thaiMonth($end_month), 100, 'center', 'dotted') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($end_year), 65, 'center', 'dotted') . ' เวลา ' . dottedLine(arabicToThaiNumber($end_time), 70, 'center', 'dotted') . ' น.
</p>

<p class="center">
ข้าพเจ้า' . dottedLine($full_name, 200, 'center', 'dotted') . ' ตำแหน่ง ' . dottedLine($position_name, 150, 'center', 'dotted') . ' ระดับ ' . dottedLine($level_name, 130, 'center', 'dotted') . '
</p>
<p>
ในฐานะ' . dottedLine($position_withdraw, 310, 'center', 'dotted') . 'ขอเบิกค่าใช้จ่ายในการเดินทางให้กับคณะเดินทาง ดังนี้
</p>

<table width="100%" border="0">
  <tr>
    <td style="width: 70%;">ค่าเบี้ยเลี้ยง ' . dottedLine($expenses_allowance, 300, 'center', 'dotted') . ' จำนวน ' . dottedLine($expenses_allowance_days, 50, 'center', 'dotted') . ' วัน</td>
    <td style="width: 30%;">รวม ' . dottedLine($allowance_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าเช่าที่พักประเภท ' . dottedLine($expenses_accommodation, 256, 'center', 'dotted') . ' จำนวน ' . dottedLine($expenses_accommodation_days, 50, 'center', 'dotted') . ' วัน</td>
    <td>รวม ' . dottedLine($accommodation_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าพาหนะ ' . dottedLine($expenses_transportation_remark, 424, 'center', 'dotted') . ' </td>
    <td>รวม ' . dottedLine($transport_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าขนย้ายสิ่งของส่วนตัว ระยะทาง ' . dottedLine($expenses_moving, 243, 'center', 'dotted') . ' กิโลเมตร</td>
    <td>รวม ' . dottedLine($expenses_moving_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
  <tr>
    <td colspan="2">
      ค่าพาหนะ
      &nbsp;
      กรณีใช้ยานพาหนะส่วนตัว
      ' . radioMark($expenses_vehicle === "car", "รถยนต์") . '
      ' . radioMark($expenses_vehicle === "motorcycle", "รถจักรยานยนต์") . '
      หมายเลขทะเบียน ' . dottedLine($expenses_vehicle_number, 193, 'center', 'dotted') . '
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เงินชดเชย (ตามรายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน)</td>
    <td>รวม ' . dottedLine($child_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>


  <tr>
    <td>ค่าใช้จ่ายอื่น ๆ ' . dottedLine($expenses_other_remark, 405, 'center', 'dotted') . ' </td>
    <td>รวม ' . dottedLine($other_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
  <tr>
    <td>หักค่าอาหารระหว่างการฝึกอบรม จำนวน ' . dottedLine($expenses_food_meals, 83, 'center', 'dotted') . ' มื้อ มื้อละ ' . dottedLine($expenses_food_per_meal, 83, 'center', 'dotted') . ' บาท</td>
    <td>รวม ' . dottedLine($food_total, 140, 'center', 'dotted') . ' บาท</td>
  </tr>
</table>

<p style="text-align:right; font-weight:bold;margin: right 17px;">
รวมทั้งสิ้น ' . dottedLine($total_expenses, 250, 'center', 'dotted') . ' บาท
</p>

<p>
จำนวนเงิน (ตัวอักษร) ' . dottedLine($total_text, 450, 'center', 'dotted') . '
</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
อนึ่ง การเดินทางไปปฏิบัติงานครั้งนี้ ได้รับเงินทดรองจำนวนเงิน ' . dottedLine($plan_total, 200, 'center', 'dotted') . ' บาท<br>
</p>

<p>
จะต้อง 
&nbsp;&nbsp;
' . radioMark($expenses_action === "return", "ส่งคืน") . '
&nbsp;&nbsp;
' . radioMark($expenses_action === "withdraw", "เบิกเพิ่ม") . '
&nbsp;&nbsp;
จำนวนเงิน ' . dottedLine($expenses_amount, 200, 'center', 'dotted') . ' บาท
</p>

<p style="font-weight:bold">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง พร้อมเอกสารประกอบการเบิกจ่าย
</p>

<p style="font-weight:bold">
ที่ส่งมาด้วยจำนวน ' . dottedLine("", 100, 'center', 'dotted') . ' ฉบับ รวมทั้งจำนวนเงินที่ขอเบิกถูกต้องตามข้อบังคับทุกประการ
</p>
<table width="100%">
  <tr>
    <td class="signature-box center" width="50%">
      <br>
      <p>ลงชื่อ ' . dottedLine("", 215, 'center', 'dotted') . ' ผู้ขอรับเงิน </p>
      <p>( ' . dottedLine($full_name, 250, 'center', 'dotted') . ' ) </p>
      <p>ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dotted') . ' </p>
      <p>' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
    <td class="signature-box center" width="50%">
      <p style="font-weight:bold">เห็นควรอนุมัติให้เบิกจ่ายได้</p>
      <p>ลงชื่อ ' . dottedLine("", 200, 'center', 'dotted') . ' ผู้บังคับบัญชา </p>
      <p>( ' . dottedLine("", 250, 'center', 'dotted') . ' ) </p>
      <p>ตำแหน่ง ' . dottedLine("", 250, 'center', 'dotted') . ' </p>
      <p>' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
  </tr>
</table>

<p style="text-align:right; font-size:14pt; margin-top:10px;">
กยท.-ฝกค.-กตจ./๒๕๕๙-๐๒
</p>
';

// -------------------- HTML หน้า 2 --------------------
$html2 = '
<table width="100%">
  <tr>
    <td class="signature-box" width="50%">
      <p class="bold center">ได้ตรวจสอบหลักฐานการเบิกจ่ายเงินที่แนบถูกต้องแล้ว</p>
      <p class="bold center">เห็นควรอนุมัติให้เบิกจ่ายได้</p>
      <p>' . dottedLine("", 170, 'center', 'dotted') . '&nbsp; ผู้ตรวจ &nbsp;' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;", 90, 'center', 'dotted') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dotted') . ' )</p>
      <p> ' . dottedLine("", 170, 'center', 'dotted') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;", 90, 'center', 'dotted') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dotted') . ' )</p>
      <p> ' . dottedLine("", 170, 'center', 'dotted') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;", 90, 'center', 'dotted') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dotted') . ' )</p>
    </td>
    <td class="signature-box center" width="50%">
      <p class="bold">อนุมัติให้จ่ายได้</p>
      <p class="bold">&nbsp;</p>
      <p> ลงชื่อ ' . dottedLine("", 173, 'center', 'dotted') . ' ผู้รับผิดชอบบัญชีฯ </p>
      <p>( ' . dottedLine("", 250, 'center', 'dotted') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine("", 250, 'center', 'dotted') . ' </p>
      <p> ' . dottedLine("", 250, 'center', 'dotted') . ' &nbsp;</p>
      <p>' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
  </tr>
</table>
<p class="center">ได้รับเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน จำนวน ' . dottedLine($total_expenses, 385, 'center', 'dotted') . ' บาท</p>
<p class="">( ' . dottedLine($total_text, 400, 'center', 'dotted') . ' ) ไว้เป็นการถูกต้องแล้ว</p>

<table width="100%" class="center" style="border:0px">
  <tr style="border:0px">
    <td class="center" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dotted') . ' ผู้รับเงิน </p>
      <p>( ' . dottedLine($full_name, 250, 'center', 'dotted') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dotted') . ' </p>
      <p>' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
    <td class="center" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dotted') . ' ผู้จ่ายเงิน </p>
      <p>( ' . dottedLine("", 250, 'center', 'dotted') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine("", 250, 'center', 'dotted') . ' </p>
      <p>' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
  </tr>
</table>

<hr style="border:0; border-top:0.5px solid #000; margin:10px 0;">
<p class="center bold" style="font-size:16pt;">รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>

<table border="1" width="100%" style="border-collapse:collapse; text-align:center;">
  ';

$html2 .= '
  <tr>
    <th class= "th-vertical-center" width="10%" rowspan="3" style="height:20px;font-size:14pt">วัน เดือน ปี</th>
    <th class= "th-vertical-center" width="20%" colspan="2" style="font-size:14pt">ออกจาก</th>
    <th class= "th-vertical-center" width="20%" colspan="2" style="font-size:14pt">กลับถึง</th>
    <th class= "th-vertical-center" width="30%" colspan="2" style="font-size:14pt">ค่าพาหนะส่วนตัว</th>
    <th class= "th-vertical-center" width="30%" rowspan="3" style="font-size:14pt">หมายเหตุ</th>
  </tr>
  <tr>
    <th style="height:20px;font-size:14pt">ที่อยู่/สำนักงาน</th>
    <th style="font-size:14pt">ที่พักแรม</th>
    <th style="font-size:14pt">ที่อยู่/สำนักงาน</th>
    <th style="font-size:14pt">ที่พักแรม</th>
    <th style="font-size:14pt">ระยะทาง</th>
    <th style="font-size:14pt">เงินชดเชย</th>
  </tr>
  <tr>
    <th style="font-size:14pt">เวลา</th>
    <th style="font-size:14pt">เวลา</th>
    <th style="font-size:14pt">เวลา</th>
    <th style="font-size:14pt">เวลา</th>
    <th style="font-size:14pt">(กม.)</th>
    <th style="font-size:14pt">(บาท)</th>
  </tr>
  ';

$total_compensation = 0;
$rows = 10;

for ($i = 0; $i < $rows; $i++) {
  if (isset($child_data[$i])) {
    $c = $child_data[$i];
    $html2 .= '
        <tr>
            <td>' . date("d/m/Y", strtotime($c['list_date'])) . '</td>
            <td>' . $c['time_start'] . '</td>
            <td>' . $c['time_end'] . '</td>
            <td>' . $c['time_depart'] . '</td>
            <td>' . $c['time_arrive'] . '</td>
            <td class="right">' . number_format((float)($c['distance'] ?? 0), 2) . '</td>
            <td class="right">' . number_format((float)($c['compensation'] ?? 0), 2) . '</td>
            <td>' . $c['remark'] . '</td>
        </tr>';
    $total_compensation += $c['compensation'];
  } else {
    $html2 .= '
        <tr>
            <td height="20"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>';
  }
}

$html2 .= '
  <tr>
    <td colspan="6" class="bold center">รวมเงิน</td>
    <td class="right bold" width="15%">' . number_format($total_compensation, 2) . '</td>
    <td class="right bold"></td>
  </tr>
</table>';


$html2 .= '

<p class="center">
ข้าพเจ้าขอรับรองว่ารายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานข้างต้นเป็นความจริงทุกประการ
</p>

<table width="100%" class="center" style="border:0px">
  <tr style="border:0px">
    <td width="50%" class="center" style="border:0px">
    &nbsp;
    </td>
    <td width="50%" class="center" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dotted') . ' ผู้ขอรับเงิน </p>
      <p>( ' . dottedLine($full_name, 300, 'center', 'dotted') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dotted') . ' </p>
    </td>
  </tr>
</table>';

// -------------------- HTML หน้า 3 --------------------
$html3 = '
<p class="center bold" style="font-size:18pt;">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเดินทางเป็นหมู่คณะ</p>
<p class="center" style="font-size:16pt;line-height:1.5;">
ประกอบใบเบิกค่าใช้จ่ายในการเดินทางของคณะ' . dottedLine('', 450, 'center', 'dotted') . 'ลงวันที่ ' . dottedLine("", 50, 'center', 'dotted') . ' เดือน ' . dottedLine("" . "", 100, 'center', 'dotted') . ' พ.ศ. ' . dottedLine("", 50, 'center', 'dotted') . '
</p>

<table border="1">
  <tr class="center bold">
    <th class="center-middle" style="font-size:14pt" rowspan="2" width="5%">ลำดับ<br>ที่</th>
    <th class="center-middle" style="font-size:14pt" rowspan="2" width="15%">ชื่อ</th>
    <th class="center-middle" style="font-size:14pt" rowspan="2" width="10%">เทียบตำแหน่ง<br>ระดับ</th>
    <th class="center-middle" style="font-size:14pt" colspan="5" width="25%">ค่าใช้จ่าย (บาท)</th>
    <th class="center-middle" style="font-size:14pt" rowspan="2" width="10%">รวม (บาท)</th>
    <th class="center-middle" style="font-size:14pt" rowspan="2" width="10%">ลายมือชื่อผู้รับเงิน</th>
    <th class="center-middle" style="font-size:14pt;min-width:240px" rowspan="2" width="25%">หมายเหตุ</th>
  </tr>
  <tr class="center bold">
    <th style="font-size:12pt">ค่าเบี้ยเลี้ยง</th>
    <th style="font-size:12pt">ค่าที่พัก</th>
    <th style="font-size:12pt">ค่าพาหนะ</th>
    <th style="font-size:12pt">ค่าใช้จ่าย<br>อื่นๆ</th>
    <th style="font-size:12pt">หักค่าอาหาร<br>ฝึกอบรม</th>
  </tr>';

$i        = 0;
$minRows  = 6;
$rowCount = count($traveling_group_data);

foreach ($traveling_group_data as $r) {
  $i++;
  $html3 .= '
    <tr>
      <td class="center">' . $i . '</td>
      <td style="font-size:14pt;line-height:0.8;">' . $r['fullname'] . '<br><label style="font-size:12pt;">หมายเลขบัตรประจำตัวประชาชน</label><br><label style="font-size:12pt;">' . $r['id_card'] . '</label></td>
      <td class="center" style="font-size:14pt;">' . $r['position'] . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['total_allowance'], 2) . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['accommodation_total'], 2) . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['transport'], 2) . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['other_expenses'], 2) . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['meal_total'], 2) . '</td>
      <td class="right" style="font-size:14pt;">' . @number_format($r['sum'], 2) . '</td>
      <td class="center"></td>
      <td>
        <p style="font-size:14pt;">ค่าเบี้ยเลี้ยง' . dottedLine($r['allowance_days'], 50, 'center', 'dotted') . 'วัน&nbsp;วันละ' . dottedLine($r['allowance'], 60, 'center', 'dotted') . 'บาท</p>
        <p style="font-size:14pt;margin: 0px 0;line-height:1;">ค่าเช่าที่พัก' . dottedLine($r['accommodation_days'], 50, 'center', 'dotted') . 'วัน&nbsp;วันละ' . dottedLine($r['accommodation'], 60, 'center', 'dotted') . 'บาท</p>
      </td>
    </tr>';
}

// ถ้ามีข้อมูลไม่ถึง 6 → เติมบรรทัดว่างให้ครบ
for ($j = $rowCount + 1; $j <= $minRows; $j++) {
  $html3 .= '
    <tr>
      <td class="center">' . $j . '</td>
      <td>&nbsp;</td>
      <td class="center">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="right">&nbsp;</td>
      <td class="center">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>';
}

$html3 .= '
  <tr class="bold">
    <td colspan="3" class="center" style="font-size:14pt">รวมเงิน</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_allowance, 2) . '</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_stay, 2) . '</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_transport, 2) . '</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_other_expenses, 2) . '</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_meal, 2) . '</td>
    <td class="right" style="font-size:14pt">' . @number_format($total_sum, 2) . '</td>
    <td class="center" colspan="2" style="font-size:14pt"> ตามสัญญาเงินยืมฯเลขที่ ' . dottedLine("" . "", 70, 'center', 'dotted') . ' วันที่ ' . dottedLine("", 90, 'center', 'dotted') . ' </td>
  </tr>
</table>

<br>

<table style="width: 100%; border: none; font-size: 12pt;">
  <tr>
    <td style="width: 72%; border: none;">
        <p style="font-size:14pt;">&nbsp;&nbsp;จำนวนเงินรวมทั้งสิ้น (ตัวอักษร) ' . dottedLine(convert_number_to_text($total_sum), 500, 'center', 'dotted') . '<p>
        <p class="bold" style="font-size:14pt;">คำชี้แจง</p>
        <p style="font-size:14pt;">1. ค่าเบี้ยเลี้ยงและค่าเช่าที่พักให้ระบุอัตราวันละ และจำนวนวันที่ของเบิกของแต่ละบุคคลในช่องหมายเหตุ</p>
        <p style="font-size:14pt;">2. ให้บุคคลภายนอก (เทียบตำแหน่ง) แต่ละคนเป็นผู้ลงลายมือชื่อผู้ขอรับเงิน</p>
        <p style="font-size:14pt;">3. ผู้ขอเบิก หมายถึง ผู้ที่ได้รับอนุมัติให้เบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน และจ่ายเงินให้แก่บุคคลภายนอก (เทียบตำแหน่ง) กรณีมียืมเงินทดรอง</p>
        <p style="font-size:14pt;">4. ห้ามทำการดัดแปลง (ตัดแต่ง/ต่อเติม) หลักฐานการจ่ายเงินฯ หากเอกสารมีหลายหน้าให้ใส่ยอดรวมและลงชื่อผู้ขอเบิกในหน้าสุดท้าย</p>
    </td>
    <td style="width: 28%; border: none; text-align: center;">
        <p style="font-size:14pt;"> ลงชื่อ ' . dottedLine("", 200, 'center', 'dotted') . ' ผู้จ่ายเงิน </p>
        <p style="font-size:14pt;">( ' . dottedLine("", 250, 'center', 'dotted') . ' ) </p>
        <p style="font-size:14pt;"> ตำแหน่ง ' . dottedLine("", 230, 'center', 'dotted') . ' </p>
        <p style="font-size:14pt;">' . dottedLine("&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;", 250, 'center', 'dotted') . ' </p>
    </td>
  </tr>
</table>

<p style="text-align:right; font-size:14pt; margin-top:20px;">
กยท.-ฝกค.-กตจ./๒๕๕๙-๐๒
</p>';

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
    font-size:16pt;
    line-height:1.1;     
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

// -------------------- Render หน้า 3 (Landscape) --------------------
$dompdf2 = new Dompdf($options);
$dompdf2->setPaper('A4', 'landscape');
$dompdf2->loadHtml('<html><head><style>' . $globalCss . '</style></head><body>' . $html3 . '</body></html>');
$dompdf2->render();
$pdf2 = $dompdf2->output();

// -------------------- รวม PDF ด้วย FPDI --------------------
if ($expenses_group == 1) {
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
} else {
  $tmp1 = tempnam(sys_get_temp_dir(), 'p1');
  $tmp2 = tempnam(sys_get_temp_dir(), 'p2');
  file_put_contents($tmp1, $pdf1);
  file_put_contents($tmp2, $pdf2);

  $final = new Fpdi();
  foreach ([$tmp1, $tmp2] as $file) {
    $pageCount = $final->setSourceFile($file);
    for ($i = 1; $i <= $pageCount; $i++) {
      $tpl = $final->importPage($i);
      $size = $final->getTemplateSize($tpl);
      $final->AddPage($size['orientation'], [$size['width'], $size['height']]);
      $final->useTemplate($tpl);
    }
  }
}

// -------------------- Output --------------------
if (ob_get_level()) {
  ob_end_clean();
}
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก).pdf"');
$final->SetTitle("ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)", true);
$final->Output('I');
exit;
