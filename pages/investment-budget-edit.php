<?php

/**
 * หน้าแก้ไขคำขอตั้งงบลงทุนถูกผสานเข้ากับหน้า form หลักแล้ว
 * ไฟล์นี้ทำหน้าที่ redirect ไปยังหน้า form พร้อมพารามิเตอร์ id
 */

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$target = 'investment-budget-form.php';

if ($requestId > 0) {
    $target .= '?id=' . $requestId;
}

header('Location: ' . $target);
exit;

