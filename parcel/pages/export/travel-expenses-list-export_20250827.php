<?php
require __DIR__ . '/../../vendor/autoload.php';

use mikehaertl\wkhtmlto\Pdf;
use mikehaertl\pdftk\Pdf as Pdftk;

header("Content-Type: application/json");
require __DIR__ . '/../../configs/database.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_model.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_list_model.php';
require __DIR__ . '/../../models/withdraws/wrd_expenses_group_model.php';

$database = new Database();
$db = $database->connect('raot_db_dev');

$expensesModel = new ExpensesModel($db);
$expensesListModel = new ExpensesListModel($db);
$expensesGroupModel = new ExpensesGroupModel($db);

$main_data  = $expensesModel->readExpensesOne($_GET['id']);
$child_data  = $expensesListModel->readListDetail($_GET['id']);
$child_total = 0;
foreach ($child_data as $key => $value) {
  $child_total += $value['compensation'];
}
$child_total = arabicToThaiNumber($child_total);
$traveling_group_data  = $expensesGroupModel->readGroupOne($_GET['id']);

// echo json_encode([
//     'main_data' => $main_data,
//     // 'child_data' => $child_data,
//     // 'traveling_group_data' => $traveling_group_data
// ]);
// exit;

$main = $main_data;

$plan_number   = arabicToThaiNumber($main['plan_number']);
$plan_total    = arabicToThaiNumber($main['plan_total']);
$full_name     = $main['full_name'];
$position_name = $main['position_name'];
$level_name    = $main['level_name'];
$affiliation   = $main['affiliation_name'];
$depart_from   = $main['expenses_start_location'];
$expenses_vehicle   = $main['expenses_vehicle'];
$expenses_group   = $main['expenses_group'];
$expenses_action   = $main['expenses_action'];
$arrive_to   = $main['expenses_end_location'];
$group_count   = arabicToThaiNumber($main['expenses_group_count']);
$expenses_date   = arabicToThaiNumber(formatDateThaiLong($main['expenses_date']));
$start_date    = date("d", strtotime($main['expenses_start']));
$start_month   = date("m", strtotime($main['expenses_start']));
$start_year    = date("Y", strtotime($main['expenses_start'])) + 543;
$start_time    = date("H:i", strtotime($main['expenses_start']));
$end_date      = date("d", strtotime($main['expenses_end']));
$end_month     = date("m", strtotime($main['expenses_end']));
$end_year      = date("Y", strtotime($main['expenses_end'])) + 543;
$end_time      = date("H:i", strtotime($main['expenses_end']));

$expenses_code   = arabicToThaiNumber($main['expenses_code']);
$expenses_allowance   = arabicToThaiNumber($main['expenses_allowance']);
$expenses_allowance_days   = arabicToThaiNumber($main['expenses_allowance_days']);
$allowance_total   = arabicToThaiNumber($main['expenses_allowance_total']);
$expenses_accommodation   = arabicToThaiNumber($main['expenses_accommodation']);
$expenses_accommodation_days   = arabicToThaiNumber($main['expenses_accommodation_days']);
$accommodation_total = arabicToThaiNumber($main['expenses_accommodation_total']);
$expenses_transportation   = arabicToThaiNumber($main['expenses_transportation']);
$transport_total   = arabicToThaiNumber($main['expenses_transportation_total']);
$expenses_moving   = arabicToThaiNumber($main['expenses_moving']);
$expenses_moving_total   = arabicToThaiNumber($main['expenses_moving_total']);
$expenses_vehicle_number   = arabicToThaiNumber($main['expenses_vehicle_number']);
$expenses_other   = arabicToThaiNumber($main['expenses_other']);
$other_total       = arabicToThaiNumber($main['expenses_other_total']);
$expenses_food_per_meal       = arabicToThaiNumber($main['expenses_food_per_meal']);
$expenses_food_meals       = arabicToThaiNumber($main['expenses_food_meals']);
$food_total        = arabicToThaiNumber($main['expenses_food_total']);
$total_expenses    = arabicToThaiNumber($main['expenses_total']);
$plan_total    = arabicToThaiNumber($main['plan_total']);
$expenses_amount    = arabicToThaiNumber($main['expenses_amount']);

$total_text = convert_number_to_text($main['expenses_total']);

// ---------- HTML ----------
$html1 = '<style>
  body {
    font-family: "Sarabun", sans-serif;
  }
  .bold {
    font-weight: bold;
  }
  table { border-collapse: collapse; width: 100%; }
  td, th { padding: 4px; vertical-align: top; height: 35px; }
  p { margin: 15px 0; }
  .center { text-align: center; }
  .right { text-align: right; }
  .bold { font-weight: bold; }
  .signature-box { border: 1px solid #000; padding: 20px; vertical-align: top; height: 150px; }
  .dotted-line {
    border-bottom: 1px dashed #000;
    display: inline-block;
    text-align: center;
  }
  input[type="radio"] {
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
    top: 6px;
  }

  input[type="radio"]:checked::after {
    content: "✔";
    font-size: 25px;
    color: black;
    position: absolute;
    top: -6px;
    left: 1px;
  }
</style>

<div class="right bold" style="font-size:18pt;">
  ส่วนที่ 1
</div>

<div class="center bold" style="font-size:18pt;">
  ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน
</div>

<p class="right">การยางแห่งประเทศไทย ' . dottedLine($affiliation, 350, 'center', 'dashed') . ' </p>

<p class="right">
วันที่ ' . dottedLine(arabicToThaiNumber($start_date), 80, 'center', 'dashed') . ' เดือน ' . dottedLine(thaiMonth($start_month), 120, 'center', 'dashed') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($start_year), 80, 'center', 'dashed') . '
</p>

<p>เรื่อง ขออนุมัติเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>
<p>เรียน ' . dottedLine($affiliation, 400, 'center', 'dashed') . '</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ตามคำสั่ง/บันทึก ที่ ' . dottedLine($expenses_code, 360, 'center', 'dashed') . ' ลงวันที่ ' . dottedLine($expenses_date, 170, 'center', 'dashed') . ' ได้อนุมัติให้
</p>

<p>
ข้าพเจ้า ' . dottedLine($full_name, 400, 'center', 'dashed') . ' ตำแหน่ง ' . dottedLine($position_name, 170, 'center', 'dashed') . ' ระดับ ' . dottedLine($level_name, 130, 'center', 'dashed') . '
</p>
<p>
สังกัด ' . dottedLine($affiliation, 250, 'center', 'dashed') . ' พร้อมด้วยคณะ ' . dottedLine($group_count, 50, 'center', 'dashed') . ' คน &nbsp; เดินทางไปปฏิบัติงานตามที่ได้รับอนุมัติโดยออกเดินทางจาก
</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp; 
<label><input type="radio" name="depart_from" value="address" ' . radioChecked("address", $depart_from) . '> ที่อยู่</label>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="office" ' . radioChecked("office", $depart_from) . '> สำนักงาน</label>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="thailand" ' . radioChecked("thailand", $depart_from) . '> ประเทศไทย</label>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
วันที่ ' . dottedLine(arabicToThaiNumber($start_date), 60, 'center', 'dashed') . ' เดือน ' . dottedLine(thaiMonth($start_month), 115, 'center', 'dashed') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($start_year), 80, 'center', 'dashed') . ' เวลา ' . dottedLine(arabicToThaiNumber($start_time), 80, 'center', 'dashed') . ' น.
</p>

<p>
และกลับถึง  
<label><input type="radio" name="arrive_to" value="address" ' . radioChecked("address", $arrive_to) . '> ที่อยู่</label>
&nbsp;&nbsp;
<label><input type="radio" name="arrive_to" value="office" ' . radioChecked("office", $arrive_to) . '> สำนักงาน</label>
&nbsp;&nbsp;
<label><input type="radio" name="arrive_to" value="thailand" ' . radioChecked("thailand", $arrive_to) . '> ประเทศไทย</label>
&nbsp;
วันที่ ' . dottedLine(arabicToThaiNumber($end_date), 60, 'center', 'dashed') . ' เดือน ' . dottedLine(thaiMonth($end_month), 115, 'center', 'dashed') . ' พ.ศ. ' . dottedLine(arabicToThaiNumber($end_year), 80, 'center', 'dashed') . ' เวลา ' . dottedLine(arabicToThaiNumber($end_time), 80, 'center', 'dashed') . ' น.
</p>

<p class="center">
ข้าพเจ้าขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ 
<label><input type="radio" name="expenses_group" value="1" ' . radioChecked("1", $expenses_group) . '> ข้าพเจ้า</label>
<label><input type="radio" name="expenses_group" value="2" ' . radioChecked("2", $expenses_group) . '> คณะเดินทาง</label>
ดังนี้
</p>

<table width="100%" border="0">
  <tr>
    <td style="width: 70%;">ค่าเบี้ยเลี้ยง ' . dottedLine($expenses_allowance, 390, 'center', 'dashed') . ' จำนวน ' . dottedLine($expenses_allowance_days, 70, 'center', 'dashed') . ' วัน</td>
    <td style="width: 30%;">รวม ' . dottedLine($allowance_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าเช่าที่พักประเภท ' . dottedLine($expenses_accommodation, 343, 'center', 'dashed') . ' จำนวน ' . dottedLine($expenses_accommodation_days, 70, 'center', 'dashed') . ' วัน</td>
    <td>รวม ' . dottedLine($accommodation_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าพาหนะ ' . dottedLine($expenses_transportation, 530, 'center', 'dashed') . ' </td>
    <td>รวม ' . dottedLine($transport_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
  <tr>
    <td>ค่าขนย้ายสิ่งของส่วนตัว ระยะทาง ' . dottedLine($expenses_moving, 300, 'center', 'dashed') . ' กิโลเมตร</td>
    <td>รวม ' . dottedLine($expenses_moving_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
  <tr>
    <td colspan="2">
    ค่าพาหนะ
    &nbsp;
    กรณีใช้ยานพาหนะส่วนตัว
    &nbsp;&nbsp;
    <label style="vertical-align: top;">
      <input type="radio" name="expenses_vehicle" value="car" ' . radioChecked("car", $expenses_vehicle) . ' style="vertical-align: middle; position: relative; top: -4px;">
      รถยนต์
    </label>
    &nbsp;&nbsp;
    <label style="vertical-align: top;">
      <input type="radio" name="expenses_vehicle" value="motorcycle" ' . radioChecked("motorcycle", $expenses_vehicle) . ' style="vertical-align: middle; position: relative; top: -4px;">
      รถจักรยานยนต์
    </label>
    &nbsp;&nbsp;
    หมายเลขทะเบียน ' . dottedLine($expenses_vehicle_number, 250, 'center', 'dashed') . '
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เงินชดเชย (ตามรายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน)</td>
    <td>รวม ' . dottedLine($child_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>


  <tr>
    <td>ค่าใช้จ่ายอื่น ๆ ' . dottedLine($expenses_other, 510, 'center', 'dashed') . ' </td>
    <td>รวม ' . dottedLine($other_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
  <tr>
    <td>หักค่าอาหารระหว่างการฝึกอบรม จำนวน ' . dottedLine($expenses_food_meals, 100, 'center', 'dashed') . ' มื้อ มื้อละ ' . dottedLine($expenses_food_per_meal, 100, 'center', 'dashed') . ' บาท</td>
    <td>รวม ' . dottedLine($food_total, 190, 'center', 'dashed') . ' บาท</td>
  </tr>
</table>

<p style="text-align:right; font-weight:bold">
รวมทั้งสิ้น ' . dottedLine($total_expenses, 250, 'center', 'dashed') . ' บาท
</p>

<p>
จำนวนเงิน (ตัวอักษร) ' . dottedLine($total_text, 450, 'center', 'dashed') . '
</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
อนึ่ง การเดินทางไปปฏิบัติงานครั้งนี้ ได้รับเงินทดรองจำนวนเงิน ' . dottedLine($plan_total, 200, 'center', 'dashed') . ' บาท<br>
</p>

<p>
จะต้อง 
&nbsp;&nbsp;
<label><input type="radio" name="action" value="return" ' . radioChecked("return", $expenses_action) . '> ส่งคืน</label>
&nbsp;&nbsp;
<label><input type="radio" name="action" value="withdraw" ' . radioChecked("withdraw", $expenses_action) . '> เบิกเพิ่ม</label>
&nbsp;&nbsp;
จำนวนเงิน ' . dottedLine($expenses_amount, 200, 'center', 'dashed') . ' บาท
</p/>

<p style="font-weight:bold">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง พร้อมเอกสารประกอบการเบิกจ่าย
</p>

<p style="font-weight:bold">
ที่ส่งมาด้วยจำนวน ' . dottedLine("", 100, 'center', 'dashed') . ' ฉบับ รวมทั้งจำนวนเงินที่ขอเบิกถูกต้องตามข้อบังคับทุกประการ
</p>

<table width="100%">
  <tr>
    <td class="signature-box center" width="50%">
      <br>
      <p>ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้ขอรับเงิน </p>
      <p>( ' . dottedLine($full_name, 250, 'center', 'dashed') . ' ) </p>
      <p>ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dashed') . ' </p>
      <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>
    </td>
    <td class="signature-box center" width="50%">
      <p style="font-weight:bold">เห็นควรอนุมัติให้เบิกจ่ายได้</p>
      <p>ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้บังคับบัญชา </p>
      <p>( ' . dottedLine("", 250, 'center', 'dashed') . ' ) </p>
      <p>ตำแหน่ง ' . dottedLine("", 250, 'center', 'dashed') . ' </p>
      <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>
    </td>
  </tr>
</table>

<p style="text-align:right; font-size:10pt; margin-top:20px;">
กยท.-ฝกค.-กตจ./๒๕๕๙-๐๒
</p>';


$html2 = '<style>
  body {
    font-family: "Sarabun", sans-serif;
  }
  .bold {
    font-weight: bold;
  }
  .signature-box { border: 1px solid #000; padding: 15px; vertical-align: top; height: 120px; }
  table { border-collapse: collapse; width: 100%; }
  td, th { border:1px solid #000; padding:4px; }
  .center { text-align:center; }
  .right { text-align:right; }
  .left { text-align:left; }
  .bold { font-weight:bold; }
  .dotted-line {
    border-bottom: 1px dashed #000;
    display: inline-block;
    text-align: center;
  }
</style>

<table width="100%">
  <tr>
    <td class="signature-box" width="50%">
      <p class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ได้ตรวจสอบหลักฐานการเบิกจ่ายเงินที่แนบถูกต้องแล้ว</p>
      <p class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เห็นควรอนุมัติให้เบิกจ่ายได้</p>
      <p>' . dottedLine("", 250, 'center', 'dashed') . ' ผู้ตรวจ ' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 110, 'center', 'dashed') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dashed') . ' )</p>
      <p> ' . dottedLine("", 250, 'center', 'dashed') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 110, 'center', 'dashed') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dashed') . ' )</p>
      <p> ' . dottedLine("", 250, 'center', 'dashed') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 110, 'center', 'dashed') . ' </p>
      <p>( ' . dottedLine("", 300, 'center', 'dashed') . ' )</p>
    </td>
    <td class="signature-box center" width="50%">
      <p class="bold">อนุมัติให้จ่ายได้</p>
      <p class="bold">&nbsp;</p>
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้รับผิดชอบบัญชีฯ </p>
      <p>( ' . dottedLine("", 250, 'center', 'dashed') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine("", 250, 'center', 'dashed') . ' </p>
      <p> ' . dottedLine("", 250, 'center', 'dashed') . ' &nbsp;</p>
      <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>
    </td>
  </tr>
</table>

<p class="center">ได้รับเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน จำนวน ' . dottedLine($total_expenses, 400, 'center', 'dashed') . ' บาท</p>
<p class="">( ' . dottedLine($total_text, 400, 'center', 'dashed') . ' ) ไว้เป็นการถูกต้องแล้ว</p>

<br>

<table width="100%" class="center" style="border:0px">
  <tr style="border:0px">
    <td class="center" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้รับเงิน </p>
      <p>( ' . dottedLine($full_name, 250, 'center', 'dashed') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dashed') . ' </p>
      <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>
    </td>
    <td class="center" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้จ่ายเงิน </p>
      <p>( ' . dottedLine("", 250, 'center', 'dashed') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine("", 250, 'center', 'dashed') . ' </p>
      <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>
    </td>
  </tr>
</table>

<br>
<p class="bold">รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>

<table border="1" width="100%" style="border-collapse:collapse; text-align:center;">
  ';

$html2 .= '
  <tr>
    <th width="10%" rowspan="2">วัน เดือน ปี</th>
    <th width="20%" colspan="2">ออกจาก</th>
    <th width="20%" colspan="2">กลับถึง</th>
    <th width="20%" colspan="2">ค่าพาหนะส่วนตัว</th>
    <th width="40%" rowspan="2">หมายเหตุ</th>
  </tr>
  <tr>
    <th>ที่อยู่/สำนักงาน</th>
    <th>ที่พักแรม</th>
    <th>ที่อยู่/สำนักงาน</th>
    <th>ที่พักแรม</th>
    <th>ระยะทาง<br>(กม.)</th>
    <th>เงินชดเชย<br>(บาท)</th>
  </tr>';

$total_compensation = 0;
$rows = 13;

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
            <td height="25"></td>
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
    <td colspan="8" class="right bold">รวมเงิน ' . number_format($total_compensation, 2) . ' บาท</td>
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
    <td width="50%" class="left" style="border:0px">
      <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้ขอรับเงิน </p>
      <p>( ' . dottedLine($full_name, 250, 'center', 'dashed') . ' ) </p>
      <p> ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dashed') . ' </p>
    </td>
  </tr>
</table>';

// mockup data
// $rows = [
//     ['ลำดับ'=>1, 'ชื่อ'=>'นางสาวกิติวรรณ อิ่มอ่อง', 'ตำแหน่ง'=>'พ.ทชส.3', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>500, 'รวม'=>17550, 'ผู้รับเงิน'=>'ลายเซ็น A', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
//     ['ลำดับ'=>2, 'ชื่อ'=>'นางสาวกาญจนา บุษบง', 'ตำแหน่ง'=>'นชส.6', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>490, 'รวม'=>17520, 'ผู้รับเงิน'=>'ลายเซ็น B', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
//     ['ลำดับ'=>3, 'ชื่อ'=>'นางสาวธัญญ์ญ์ ลัดดา', 'ตำแหน่ง'=>'นชส.C', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>150, 'รวม'=>17200, 'ผู้รับเงิน'=>'ลายเซ็น C', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
//     ['ลำดับ'=>4, 'ชื่อ'=>'นายสมชาย หมื่นแก้ว', 'ตำแหน่ง'=>'พ.ทชส.3', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>600, 'รวม'=>17650, 'ผู้รับเงิน'=>'ลายเซ็น D', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
// ];

$html3 = '<style>
  body {
    font-family: "Sarabun", sans-serif;
  }
  .bold {
    font-weight: bold;
  }
  table { border-collapse: collapse; width: 100%; }
  td, th { border:1px solid #000; padding:4px; }
  .center { text-align:center; }
  .bold { font-weight:bold; }
  .center-middle {
    text-align: center;
    vertical-align: middle;
  }
  .right-middle {
    text-align: right;
    vertical-align: middle;
  }
  .dotted-line {
    border-bottom: 1px dashed #000;
    display: inline-block;
    text-align: center;
  }
</style>

<h4 class="center bold">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเดินทางเป็นหมู่คณะ</h4>

<p class="center">
ประกอบใบเบิกค่าใช้จ่ายในการเดินทาง' . dottedLine("", 240, 'center', 'dashed') . ' สังกัด ' . dottedLine("", 240, 'center', 'dashed') . ' ลงวันที่ ' . dottedLine("", 100, 'center', 'dashed') . ' เดือน ' . dottedLine("(3)" . "", 150, 'center', 'dashed') . ' พ.ศ. ' . dottedLine("", 100, 'center', 'dashed') . '
</p>

<table>
  <tr class="center bold">
    <th class="center-middle" rowspan="2" width="10%">ลำดับที่</th>
    <th class="center-middle" rowspan="2" width="15%">ชื่อ (4)</th>
    <th class="center-middle" rowspan="2" width="10%">ตำแหน่ง/ระดับ (5)</th>
    <th class="center-middle" colspan="5" width="25%">ค่าใช้จ่าย (บาท) (6)</th>
    <th class="center-middle" rowspan="2" width="10%">รวม (บาท) (7)</th>
    <th class="center-middle" rowspan="2" width="10%">ลายมือชื่อผู้รับเงิน (8)</th>
    <th class="center-middle" rowspan="2" width="20%">หมายเหตุ</th>
  </tr>
  <tr class="center bold">
    <th>ค่าเบี้ยเลี้ยง</th>
    <th>ค่าที่พัก</th>
    <th>ค่าพาหนะ</th>
    <th>ค่าใช้จ่ายอื่นๆ</th>
    <th>หักค่าอาหารฝึกอบรม</th>
  </tr>';

$total_allowance = 0;
$total_stay = 0;
$total_transport = 0;
$total_sum = 0;
$i = 0;

$minRows = 6;
$rowCount = count($traveling_group_data);

foreach ($traveling_group_data as $r) {
  $i++;
  $html3 .= '
    <tr>
      <td class="center">' . $i . '</td>
      <td>' . $r['fullname'] . '</td>
      <td class="center">' . $r['position'] . '</td>
      <td class="right">' . number_format($r['total_allowance']) . '</td>
      <td class="right">' . number_format($r['accommodation_total']) . '</td>
      <td class="right">' . number_format($r['transport']) . '</td>
      <td class="right">' . number_format($r['other_expenses']) . '</td>
      <td class="right">' . number_format($r['meal_total']) . '</td>
      <td class="right">' . number_format($r['sum']) . '</td>
      <td class="center"></td>
      <td></td>
    </tr>';

  $total_allowance += $r['total_allowance'];
  $total_stay      += $r['accommodation_total'];
  $total_transport += $r['transport'];
  $total_sum       += $r['sum'];
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
    <td colspan="3" class="center">รวมเงิน (9)</td>
    <td class="right">' . number_format($total_allowance) . '</td>
    <td class="right">' . number_format($total_stay) . '</td>
    <td class="right">' . number_format($total_transport) . '</td>
    <td class="right">-</td>
    <td class="right">-</td>
    <td class="right">' . number_format($total_sum) . '</td>
    <td class="center" colspan="2"> ตามสัญญาเงินยืมฯเลขที่ ' . dottedLine("(11)" . "", 70, 'center', 'dashed') . ' วันที่ ' . dottedLine("", 70, 'center', 'dashed') . ' </td>
  </tr>
</table>

<br>

<table style="width: 100%; border: none; font-size: 12pt;">
  <tr>
    <td style="width: 72%; border: none;">
        &nbsp;&nbsp;
        จำนวนเงินรวมทั้งสิ้น (ตัวอักษร) ' . dottedLine(convert_number_to_text($total_sum), 600, 'center', 'dashed') . ' <br>
        <p class="bold">คำชี้แจง</p>
        <p> 1. ค่าเบี้ยเลี้ยงและค่าที่พักให้ระบุอัตราวันละ และจำนวนวันที่ขอเบิกของแต่ละบุคคลในช่องหมายเหตุ </p>
        <p> 2. ให้ผู้มีสิทธิเบิกลงนามเป็นผู้ลงลายมือชื่อผู้รับเงิน </p>
        <p> 3. ผู้จ่ายเงินหมายถึงผู้ซึ่งอยู่ในตำแหน่งผู้จัดทำใบค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน
        และจ่ายเงินให้แก่ผู้เดินทางแต่ละคน เป็นผู้ลงลายมือชื่อจ่ายเงิน </p>
    </td>
    <td style="width: 28%; border: none; text-align: center;">
        <p> ลงชื่อ ' . dottedLine("", 250, 'center', 'dashed') . ' ผู้ขอรับเงิน </p>
        <p>( ' . dottedLine($full_name, 250, 'center', 'dashed') . ' ) </p>
        <p> ตำแหน่ง ' . dottedLine($position_name, 250, 'center', 'dashed') . ' </p>
        <p>' . dottedLine("&nbsp&nbsp/&nbsp&nbsp/&nbsp&nbsp", 250, 'center', 'dashed') . ' </p>

    </td>
  </tr>
</table>

<p style="text-align: right; font-size: 12pt;">กยท.-ฝกค.-กกจ./2565/04</p>
';

// ---------- ฟังก์ชันสร้าง PDF ----------

function formatDateThaiLong($date)
{
  if (empty($date) || $date == '0000-00-00') {
    return '';
  }
  $timestamp = strtotime($date);
  $day   = date('d', $timestamp);
  $month = thaiMonth(date('m', $timestamp));
  $year  = date('Y', $timestamp) + 543; // แปลงเป็น พ.ศ.

  return "$day $month $year";
}

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
  // ถ้าเป็น 0 หรือไม่มีค่า → คืนค่าว่าง
  if ($number == 0 || $number === null || $number === '') {
    return '';
  }

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
      $convert .= $txtnum2[$strlen - $i - 1];
    }
  }
  $convert .= 'บาทถ้วน';
  return $convert;
}

function arabicToThaiNumber($input)
{
  $arabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
  $thai   = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
  return str_replace($arabic, $thai, $input);
}

// echo arabicToThaiNumber('2568');
// echo arabicToThaiNumber(date('d/m/Y'));
// $sentence = 'วันนี้วันที่ 19 สิงหาคม 2568';
// echo arabicToThaiNumber($sentence);
// exit();

function thaiMonth($monthNum)
{
  $months = [
    "01" => "มกราคม",
    "02" => "กุมภาพันธ์",
    "03" => "มีนาคม",
    "04" => "เมษายน",
    "05" => "พฤษภาคม",
    "06" => "มิถุนายน",
    "07" => "กรกฎาคม",
    "08" => "สิงหาคม",
    "09" => "กันยายน",
    "10" => "ตุลาคม",
    "11" => "พฤศจิกายน",
    "12" => "ธันวาคม"
  ];
  return $months[$monthNum];
}

function dottedLine($text = '', $width = 300, $align = 'center', $style = 'dashed')
{
  return '<span class="dotted-line" style="width:' . $width . 'px; text-align:' . $align . '; border-bottom:1px ' . $style . ' #000;">' . $text . '</span>';
}

function radioChecked($fieldValue, $currentValue)
{
  return $fieldValue === $currentValue ? 'checked' : '';
}

function getWkhtmltopdfPath()
{
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows
    return getenv('WKHTMLTOPDF_BIN') ?: 'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe';
  } else {
    // Linux/Unix
    return getenv('WKHTMLTOPDF_BIN') ?: '/usr/bin/wkhtmltopdf';
  }
}

function makePdf($html, $file, $orientation = 'Portrait')
{
  $pdf = new \mikehaertl\wkhtmlto\Pdf([
    'binary' => getWkhtmltopdfPath(),
    'encoding' => 'UTF-8',
    'page-size' => 'A4',
    'orientation' => $orientation,
    'margin-top'    => 10,
    'margin-right'  => 10,
    'margin-bottom' => 10,
    'margin-left'   => 10,
  ]);

  $pdf->addPage($html);
  if (!$pdf->saveAs($file)) {
    die("Error creating $file: " . $pdf->getError());
  }
}


// ---------- สร้างแต่ละหน้า ----------
$file1 = __DIR__ . '/p1.pdf';
makePdf($html1, $file1, 'Portrait');
$file2 = __DIR__ . '/p2.pdf';
makePdf($html2, $file2, 'Portrait');
$file3 = __DIR__ . '/p3.pdf';
makePdf($html3, $file3, 'Landscape');

// ---------- รวมไฟล์ ----------
$output = __DIR__ . '/document.pdf';

// ดึง path จาก .env หรือใช้ default ตาม OS
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $pdftkBin = getenv('PDFTK_BIN') ?: 'C:\\Program Files (x86)\\PDFtk Server\\bin\\pdftk.exe';
} else {
  $pdftkBin = getenv('PDFTK_BIN') ?: '/usr/bin/pdftk';
}

$pdftk = new \mikehaertl\pdftk\Pdf($file1, [
  'command' => $pdftkBin
]);
$pdftk->cat()->addFile($file2)->addFile($file3);

if (!$pdftk->saveAs($output)) {
  die("Error merging: " . $pdftk->getError());
}

$pdftk = new Pdftk($file1, [
  'command' => $pdftkBin
]);
$pdftk->cat()->addFile($file2)->addFile($file3);
if (!$pdftk->saveAs($output)) {
  die("Error merging: " . $pdftk->getError());
}


// ---------- ส่งออกไปยัง Browser ----------
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document.pdf"');
header('Content-Length: ' . filesize($output));
readfile($output);

// ---------- ลบไฟล์ชั่วคราว ----------
@unlink($file1);
@unlink($file2);
@unlink($file3);
@unlink($output);
exit;
