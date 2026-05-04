<?php
/**
 * Authentication Middleware (Unified)
 * ใช้ร่วมกันทั้งระบบ - ไม่มี authMiddleware_ib.php แยกแล้ว
 */

// เริ่มต้น Session (ตรวจสอบว่ายังไม่เริ่มต้น)
if (session_status() === PHP_SESSION_NONE) {
    // Set session configuration
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_lifetime', '86400'); // 24 hours
    ini_set('session.gc_maxlifetime', '86400'); // 24 hours
    ini_set('session.cookie_samesite', 'Lax'); // Prevent CSRF but allow navigation

    session_start();

    // Track session ID changes
    // $currentSessionId = session_id();
    // if (isset($_SESSION['_session_id']) && $_SESSION['_session_id'] !== $currentSessionId) {
    //     error_log('🔄 Session ID changed! Old: ' . $_SESSION['_session_id'] . ' -> New: ' . $currentSessionId);
    //     error_log('Session data before change: ' . print_r($_SESSION, true));
    // }
    // $_SESSION['_session_id'] = $currentSessionId;

    // // Log session info for debugging
    // if (empty($_SESSION['user_id'])) {
    //     error_log('⚠️ Session started but no user_id. Session ID: ' . session_id());
    // }
}

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