<?php
/**
 * Authentication Middleware (Unified)
 * ใช้ร่วมกันทั้งระบบ - ไม่มี authMiddleware_ib.php แยกแล้ว
 */

require_once __DIR__ . '/session_bootstrap.php';

/**
 * ตรวจสอบการ authentication แบบมาตรฐาน
 * ใช้สำหรับ module ทั่วไป
 */
function checkAuth() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "status" => "error",
            "message" => "User not authenticated"
        ]);
        exit;
    }
}

/**
 * ดึง user_id จาก session
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * ตรวจสอบว่า user login หรือไม่
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * ตรวจสอบว่ามี session data ที่จำเป็นสำหรับ Investment Budget หรือไม่
 */
function hasRequiredSessionData()
{
    $required = ['user_id', 'branch_id', 'province_id', 'area_id', 'head_office_id'];

    foreach ($required as $key) {
        if (!isset($_SESSION[$key]) || empty($_SESSION[$key])) {
            return false;
        }
    }

    return true;
}

// Backward compatibility aliases (เพื่อไม่ให้กระทบโค้ดเดิม)
function checkAuthIB() { return checkAuth(); }
function getCurrentUserIdIB() { return getCurrentUserId(); }
function isAuthenticatedIB() { return isAuthenticated(); }
function hasRequiredSessionDataIB() { return hasRequiredSessionData(); }