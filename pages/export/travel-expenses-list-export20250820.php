<?php
require __DIR__ . '/../../vendor/autoload.php';

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

function arabicToThaiNumber($input) {
    $arabic = ['0','1','2','3','4','5','6','7','8','9'];
    $thai   = ['๐','๑','๒','๓','๔','๕','๖','๗','๘','๙'];
    return str_replace($arabic, $thai, $input);
}

// echo arabicToThaiNumber('2568');
// echo arabicToThaiNumber(date('d/m/Y'));
// $sentence = 'วันนี้วันที่ 19 สิงหาคม 2568';
// echo arabicToThaiNumber($sentence);
// exit();

function dottedLineWithText($text = '', $widthPx = 300) {
    $displayText = htmlspecialchars($text ?: str_repeat('.', 50));

    return '
    <span style="
      display: inline-block;
      width: ' . $widthPx . 'px;
      border-bottom: 1px dotted #000;
      text-align: center;
      line-height: 1.2em;
    ">' . $displayText . '</span>';
}


$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'tempDir' => __DIR__ . '/../../tmp',
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_left' => 10,
    'margin_right' => 10,
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

$recipientName = 'นายสมชาย ตัวอย่าง';
$recipientName2 = 'ดก';

$html = '
<style>
  body { font-family: "thsarabun"; font-size: 14pt; }
  table { border-collapse: collapse; width: 100%; }
  td, th { padding: 4px; vertical-align: top; }
  p { margin: 2px 0; }
  .center { text-align: center; }
  .right { text-align: right; }
  .bold { font-weight: bold; }
  .signature-box { border: 1px solid #000; padding: 20px; vertical-align: top; height: 150px; }
</style>

<div class="right bold" style="font-size:18pt;">
  ส่วนที่ 1
</div>
<div class="center bold" style="font-size:18pt;">
  ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน
</div>
<div class="right">การยางแห่งประเทศไทย .......................................................</div>
<p class="right">
วันที่ ............ เดือน ................................. พ.ศ. ..............
</p>

<p>เรื่อง ขออนุมัติเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>
<p>เรียน ....................................................................</p>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ตามคำสั่ง/บันทึก ที่ ..................................................................................................... ลงวันที่ ............................................... ได้อนุมัติให้<br>
ข้าพเจ้า .............................................................................................................. ตำแหน่ง ......................................................... ระดับ ............................<br>
สังกัด ............................................................................... พร้อมด้วยคณะ ................ คน &nbsp; เดินทางไปปฏิบัติงานตามที่ได้รับอนุมัติโดยออกเดินทางจาก
</p>

<p>  
&nbsp;
<label><input type="radio" name="depart_from" value="ที่อยู่"> ที่อยู่</label>
&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="สำนักงาน"> สำนักงาน</label>
&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="ประเทศไทย"> ประเทศไทย</label>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
วันที่ .................. เดือน .................................... พ.ศ. ....................... เวลา ................. น.
</p>

<p>
และกลับถึง  
<label><input type="radio" name="arrive_to" value="ที่อยู่"> ที่อยู่</label>
&nbsp;
<label><input type="radio" name="arrive_to" value="สำนักงาน"> สำนักงาน</label>
&nbsp;
<label><input type="radio" name="arrive_to" value="ประเทศไทย"> ประเทศไทย</label>
&nbsp;&nbsp;&nbsp;
วันที่ .................. เดือน .................................... พ.ศ. ....................... เวลา ................. น.
</p>

<p class="center">
ข้าพเจ้าขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ 
<label><input type="radio" name="depart_from" value="ข้าพเจ้า"> ข้าพเจ้า</label>
<label><input type="radio" name="depart_from" value="คณะเดินทาง"> คณะเดินทาง</label>
ดังนี้
</p>

<table border="0">
  <tr>
    <td style="width: 70%;">ค่าเบี้ยเลี้ยง ................................................................................................. จำนวน ........................ วัน</td>
    <td style="width: 30%;">รวม ................................................... บาท</td>
  </tr>
  <tr>
    <td>ค่าเช่าที่พักประเภท .................................................................................... จำนวน ........................ วัน</td>
    <td>รวม ................................................... บาท</td>
  </tr>
  <tr>
    <td>ค่าพาหนะ ..............................................................................................................................................</td>
    <td>รวม ................................................... บาท</td>
  </tr>
  <tr>
    <td>ค่าขนย้ายสิ่งของส่วนตัว ระยะทาง ........................................................................ กิโลเมตร</td>
    <td>รวม ................................................... บาท</td>
  </tr>
  <tr>
    <td colspan="2">
    ค่าพาหนะ
    &nbsp;
    กรณีใช้ยานพาหนะส่วนตัว
    &nbsp;&nbsp;
    <label><input type="radio" name="depart_from" value="รถยนต์"> รถยนต์</label>
    &nbsp;&nbsp;
    <label><input type="radio" name="depart_from" value="รถจักรยานยนต์"> รถจักรยานยนต์</label>
    &nbsp;&nbsp;
    หมายเลขทะเบียน .....................................................................
    </td>
  </tr>
  <tr>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เงินชดเชย (ตามรายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน)</td>
    <td>รวม ................................................... บาท</td>
  </tr>


  <tr>
    <td>ค่าใช้จ่ายอื่น ๆ ....................................................................................................................................... </td>
    <td>รวม ................................................... บาท</td>
  </tr>
  <tr>
    <td>หักค่าอาหารระหว่างการฝึกอบรม จำนวน ........................................ มื้อ มื้อละ ........................................ บาท</td>
    <td>รวม ................................................... บาท</td>
  </tr>
</table>

<p style="text-align:right; font-weight:bold">
รวมทั้งสิ้น ......................................................... บาท
</p>

<p>
จำนวนเงิน (ตัวอักษร) ..............................................................................................................................................................
</p>

<p class="right">
อนึ่ง การเดินทางไปปฏิบัติงานครั้งนี้ ได้รับเงินทดรองจำนวนเงิน ............................................................................. บาท<br>
</p>

<p>
จะต้อง 
&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="ส่งคืน"> ส่งคืน</label>
&nbsp;&nbsp;
<label><input type="radio" name="depart_from" value="เบิกเพิ่ม"> เบิกเพิ่ม</label>
&nbsp;&nbsp;
จำนวนเงิน .............................................................. บาท
</p/>

<p style="text-align:right; font-weight:bold">
ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง พร้อมเอกสารประกอบการเบิกจ่าย
</p>

<p style="font-weight:bold">
ที่ส่งมาด้วยจำนวน ........ ฉบับ รวมทั้งจำนวนเงินที่ขอเบิกถูกต้องตามข้อบังคับทุกประการ
</p>

<table width="100%">
  <tr>
    <td class="signature-box center" width="50%">
    <br><br>
      ลงชื่อ .................................................................. ผู้ขอรับเงิน <br>
      ( .................................................................. ) <br>
      ตำแหน่ง .................................................................. <br>
      ................../................../..................
    </td>
    <td class="signature-box center" width="50%">
      <p style="font-weight:bold">เห็นควรอนุมัติให้เบิกจ่ายได้</p> <br>
      ลงชื่อ .................................................................. ผู้บังคับบัญชา <br>
      ( .................................................................. ) <br>
      ตำแหน่ง .................................................................. <br>
      ................../................../..................
    </td>
  </tr>
</table>

<p style="text-align:right; font-size:10pt; margin-top:20px;">
กยท.-ฝกค.-กตจ./๒๕๕๙-๐๒
</p>
';

$html2 = '
<style>
  .signature-box { border: 1px solid #000; padding: 15px; vertical-align: top; height: 120px; }
  table { border-collapse: collapse; width: 100%; }
  td, th { border:1px solid #000; padding:4px; }
  .center { text-align:center; }
  .right { text-align:right; }
  .left { text-align:left; }
  .bold { font-weight:bold; }
</style>

<table width="100%">
  <tr>
    <td class="signature-box" width="50%">
      <p class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ได้ตรวจสอบหลักฐานการเบิกจ่ายเงินที่แนบถูกต้องแล้ว</p>
      <p class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เห็นควรอนุมัติให้เบิกจ่ายได้</p>
      <p>........................................................ ผู้ตรวจ ........../........../..........</p>
      <p>( .................................................................. )</p>
      <p>........................................................ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........../........../..........</p>
      <p>( .................................................................. )</p>
      <p>........................................................ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........../........../..........</p>
      <p>( .................................................................. )</p>
    </td>
    <td class="signature-box center" width="50%">
      <p class="bold">อนุมัติให้จ่ายได้</p><br>
      ลงชื่อ .................................................................. ผู้รับผิดชอบบัญชีฯ<br>
      ( .................................................................. )<br>
      ตำแหน่ง ..................................................................<br>
      ..................................................................<br>
      ................../................../..................
    </td>
  </tr>
</table>

<p class="center">ได้รับเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน จำนวน ........................................................................................ บาท</p>
<p class="">( ............................................................................................................................. ) ไว้เป็นการถูกต้องแล้ว</p>

<br>

<table width="100%" class="center" style="border:0px">
  <tr style="border:0px">
    <td class="center" style="border:0px">
      ลงชื่อ .................................................................. ผู้รับเงิน<br>
      ( .................................................................. )<br>
      ตำแหน่ง ..................................................................<br>
      ................../................../..................
    </td>
    <td class="center" style="border:0px">
      ลงชื่อ .................................................................. ผู้จ่ายเงิน<br>
      ( .................................................................. )<br>
      ตำแหน่ง ..................................................................<br>
      ................../................../..................
    </td>
  </tr>
</table>

<br>
<p class="bold">รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</p>

<table border="1" width="100%" style="border-collapse:collapse; text-align:center;">
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
  </tr>
  <tr>
    <td></td>
    <td>เวลา</td>
    <td>เวลา</td>
    <td>เวลา</td>
    <td>เวลา</td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  ';
  for ($i = 0; $i < 12; $i++) {
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

$html2 .= '
  <tr>
    <td colspan="8" class="right bold">รวมเงิน ................................................ บาท</td>
  </tr>
</table>

<p class="center">
ข้าพเจ้าขอรับรองว่ารายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานข้างต้นเป็นความจริงทุกประการ
</p>

<table width="100%" class="center" style="border:0px">
  <tr style="border:0px">
    <td width="50%" class="center" style="border:0px">
    &nbsp;
    </td>
    <td width="50%" class="left" style="border:0px">
      ลงชื่อ .................................................................. ผู้ขอรับเงิน<br>
      &nbsp;&nbsp;( .................................................................. )<br>
      ตำแหน่ง ..................................................................<br>
    </td>
  </tr>
</table>
';

// mockup data
$rows = [
    ['ลำดับ'=>1, 'ชื่อ'=>'นางสาวกิติวรรณ อิ่มอ่อง', 'ตำแหน่ง'=>'พ.ทชส.3', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>500, 'รวม'=>17550, 'ผู้รับเงิน'=>'ลายเซ็น A', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
    ['ลำดับ'=>2, 'ชื่อ'=>'นางสาวกาญจนา บุษบง', 'ตำแหน่ง'=>'นชส.6', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>490, 'รวม'=>17520, 'ผู้รับเงิน'=>'ลายเซ็น B', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
    ['ลำดับ'=>3, 'ชื่อ'=>'นางสาวธัญญ์ญ์ ลัดดา', 'ตำแหน่ง'=>'นชส.C', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>150, 'รวม'=>17200, 'ผู้รับเงิน'=>'ลายเซ็น C', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
    ['ลำดับ'=>4, 'ชื่อ'=>'นายสมชาย หมื่นแก้ว', 'ตำแหน่ง'=>'พ.ทชส.3', 'ค่าเบี้ยเลี้ยง'=>4050, 'ค่าที่พัก'=>13000, 'ค่าพาหนะ'=>600, 'รวม'=>17650, 'ผู้รับเงิน'=>'ลายเซ็น D', 'หมายเหตุ'=>'ค่าเบี้ยเลี้ยง 13.5 วัน วันละ 300 บาท'],
];

$html3 = '
<style>
  body { font-family: "thsarabun"; font-size: 14pt; }
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

</style>

<h4 class="center bold">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเดินทางเป็นหมู่คณะ</h4>

<p class="center">
ประกอบใบเบิกค่าใช้จ่ายในการเดินทาง............................................................สังกัด............................................................ ลงวันที่ .............. เดือน ...(3).............................. พ.ศ. .............
</p>

<table>
  <tr class="center bold">
    <th class="center-middle" rowspan="2" width="10%">ลำดับที่</th>
    <th class="center-middle" rowspan="2" width="15%">ชื่อ (4)</th>
    <th class="center-middle" rowspan="2" width="10%">ตำแหน่ง/ระดับ (5)</th>
    <th class="center-middle" colspan="5" width="25%">ค่าใช้จ่าย (บาท) ()6</th>
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

foreach ($rows as $r) {
    $html3 .= '
    <tr>
      <td class="center">'.$r['ลำดับ'].'</td>
      <td>'.$r['ชื่อ'].'</td>
      <td class="center">'.$r['ตำแหน่ง'].'</td>
      <td class="right">'.number_format($r['ค่าเบี้ยเลี้ยง']).'</td>
      <td class="right">'.number_format($r['ค่าที่พัก']).'</td>
      <td class="right">'.number_format($r['ค่าพาหนะ']).'</td>
      <td class="right">-</td>
      <td class="right">-</td>
      <td class="right">'.number_format($r['รวม']).'</td>
      <td class="center">'.$r['ผู้รับเงิน'].'</td>
      <td>'.$r['หมายเหตุ'].'</td>
    </tr>';

    $total_allowance += $r['ค่าเบี้ยเลี้ยง'];
    $total_stay += $r['ค่าที่พัก'];
    $total_transport += $r['ค่าพาหนะ'];
    $total_sum += $r['รวม'];
}

$html3 .= '
  <tr class="bold">
    <td colspan="3" class="center">รวมเงิน (9)</td>
    <td class="right">'.number_format($total_allowance).'</td>
    <td class="right">'.number_format($total_stay).'</td>
    <td class="right">'.number_format($total_transport).'</td>
    <td class="right">-</td>
    <td class="right">-</td>
    <td class="right">'.number_format($total_sum).'</td>
    <td class="center" colspan="2"> ตามสัญญาเงินยืมฯเลขที่......(11)...... วันที่ .......................... </td>
  </tr>
</table>

<br>

<table style="width: 100%; border: none; font-size: 14pt;">
  <tr>
    <td style="width: 70%; border: none;">
        &nbsp;&nbsp;
        จำนวนเงินรวมทั้งสิ้น (ตัวอักษร) .................................................................................................................................<br>
        <p class="bold">คำชี้แจง</p>
        1. ค่าเบี้ยเลี้ยงและค่าที่พักให้ระบุอัตราวันละ และจำนวนวันที่ขอเบิกของแต่ละบุคคลในช่องหมายเหตุ<br>
        2. ให้ผู้มีสิทธิเบิกลงนามเป็นผู้ลงลายมือชื่อผู้รับเงิน<br>
        3. ผู้จ่ายเงินหมายถึงผู้ซึ่งอยู่ในตำแหน่งผู้จัดทำใบค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน
        และจ่ายเงินให้แก่ผู้เดินทางแต่ละคน เป็นผู้ลงลายมือชื่อจ่ายเงิน
    </td>
    <td style="width: 30%; border: none; text-align: center;">
        ลงชื่อ .................................................................. ผู้ขอรับเงิน<br>
        &nbsp;&nbsp;( .................................................................. )<br>
        ตำแหน่ง ..................................................................<br>
        ................../................../..................
    </td>
  </tr>
</table>

<p style="text-align: right; font-size: 12pt;">กยท.-ฝกค.-กกจ./2565/04</p>
';

$mpdf->WriteHTML($html);
$mpdf->WriteHTML('<pagebreak />'); // ขึ้นหน้าใหม่
$mpdf->WriteHTML($html2);
// หน้า 3 Landscape
$mpdf->WriteHTML('<pagebreak orientation="L" />');
$mpdf->WriteHTML($html3);

$mpdf->Output('ใบเบิกค่าใช้จ่าย.pdf', 'I');
