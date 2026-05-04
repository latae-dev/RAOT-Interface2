<?php
/**
 * Dynamic Sidebar Component
 * 
 * Sidebar ที่ดึงข้อมูลเมนูจาก database แบบ dynamic
 * แทนที่ hardcoded menu ใน sidebar.php เดิม
 * 
 * หมายเหตุ: Session ควรจะ start ที่ไฟล์หลัก (เช่น index.php) ก่อนที่จะ include sidebar
 * ไม่ควรเรียก session_start() ในไฟล์นี้เพราะ headers อาจถูกส่งไปแล้ว
 */

// Include dependencies
require_once __DIR__ . '/../../../configs/database.php';
require_once __DIR__ . '/../../../models/user/menu_model.php';
require_once __DIR__ . '/../../../helper/menu_helper.php';

// กำหนด URL paths
if (strpos($_SERVER['REQUEST_URI'], 'parcel/')) {
        $url_parcel = '';
        $url_vendor = '../../pages/';
    } else {
        $url_parcel = '../parcel/pages/';
        $url_vendor = '';
}

// ตรวจสอบว่า session ถูก start หรือไม่
// ไม่ควรเรียก session_start() ที่นี่เพราะ headers อาจถูกส่งไปแล้ว
// Session ควรจะ start ที่ไฟล์หลัก (เช่น index.php, menu-list.php) ก่อนที่จะ include sidebar
// ถ้า session ยังไม่ start จะใช้ @session_start() เพื่อหลีกเลี่ยง error
if (session_id() === '' && !headers_sent()) {
    @session_start();
}

// ดึงเมนูสำหรับ user ปัจจุบัน
try {
    $positionIds = getUserPositionIds();

    if (empty($positionIds)) {
        // error_log("Sidebar Warning: No position IDs found in session. Showing all menus. Please check session setup.");
        // แสดงเมนูทั้งหมดถ้าไม่มี position IDs (ใช้ readMenuTree แทน getMenuForUser([], true))
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        if ($db) {
            $menuModel = new MenuModel($db);
            $menuTree = $menuModel->readMenuTree();
        } else {
            $menuTree = [];
        }
    } else {
        // error_log("Sidebar Info: Position IDs from session: " . json_encode($positionIds));
        // ดึงเมนูตาม position IDs
        $menuTree = getMenuForUser($positionIds, true);
        
        // Debug: Log raw menu tree data
        if ($menuTree !== false && !empty($menuTree)) {
            // error_log("Sidebar Debug: Menu tree type: " . gettype($menuTree));
            // error_log("Sidebar Debug: Menu tree count: " . count($menuTree));
            // error_log("Sidebar Debug: First menu item: " . json_encode($menuTree[0] ?? null, JSON_UNESCAPED_UNICODE));
        }
    }
    
    // ถ้าดึงเมนูไม่สำเร็จ ให้ใช้ fallback
    if ($menuTree === false || empty($menuTree)) {
        // error_log("Warning: Failed to load menu from database, using fallback");
        // error_log("Sidebar Debug: menuTree value: " . var_export($menuTree, true));
        $menuTree = [];
    } else {
        // Log menu count for debugging
        $menuCount = count($menuTree);
        $totalMenuItems = 0;
        $menuDetails = [];
        foreach ($menuTree as $menu) {
            $menuDetails[] = [
                'id' => $menu['id'] ?? null,
                'menu_code' => $menu['menu_code'] ?? null,
                'menu_name' => $menu['menu_name'] ?? null,
                'category' => $menu['category'] ?? null,
                'children_count' => isset($menu['children']) ? count($menu['children']) : 0
            ];
            $totalMenuItems += countMenuItems($menu);
        }
        // error_log("Sidebar Info: Loaded $menuCount menu categories, $totalMenuItems total menu items");
        // error_log("Sidebar Debug: Menu details: " . json_encode($menuDetails, JSON_UNESCAPED_UNICODE));
    }
} catch (Exception $e) {
    // error_log("Error loading menu: " . $e->getMessage());
    // error_log("Error stack trace: " . $e->getTraceAsString());
    $menuTree = [];
}

// Helper function to count menu items recursively
function countMenuItems($menu) {
    $count = 1; // Count self
    if (isset($menu['children']) && is_array($menu['children'])) {
        foreach ($menu['children'] as $child) {
            $count += countMenuItems($child);
        }
    }
    return $count;
}

// Note: renderMenuItem() และ renderMenuWithCategories() ถูกย้ายไปที่ helper/menu_helper.php แล้ว
// เพื่อหลีกเลี่ยง function redeclaration error
?>

<aside class="app-sidebar sticky" id="sidebar">
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="<?= $url_vendor ?>index.php" class="header-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
        </a>
    </div>
    <!-- End::main-sidebar-header -->
    
    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">
                <?php
                // Render menu จาก database
                if (!empty($menuTree)) {
                    // Debug: Log before rendering
                    // error_log("Sidebar: About to render menu with " . count($menuTree) . " top-level items");
                    $renderedHtml = renderMenuWithCategories($menuTree, $url_vendor, $url_parcel);
                    // error_log("Sidebar: Rendered HTML length: " . strlen($renderedHtml) . " characters");
                    echo $renderedHtml;
                } else {
                    // Fallback: แสดงข้อความถ้าไม่มีเมนู
                    // error_log("Sidebar Warning: menuTree is empty, showing fallback message");
                    echo '<li class="slide"><span class="side-menu__label text-muted">กำลังโหลดเมนู...</span></li>';
                }
                ?>
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
        <script>
            function logOut() {
                // ลบข้อมูล session
                sessionStorage.removeItem("raot_user_session");
                // เปลี่ยนหน้าไปยังหน้าล็อกอิน
                window.location.href = "login.php";
            }
        </script>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let userData = null;
        try {
            userData = JSON.parse(sessionStorage.getItem("raot_user_session") || "null");
        } catch (e) {
            userData = null;
        }
        const hasMatchAPHOP = Array.isArray(userData?.position_list_id)
            && userData.position_list_id.some(id => ['6'].includes(String(id)));

        if (hasMatchAPHOP) {
            document.querySelectorAll('.side-menu__label').forEach(function(el) {
                if (el.textContent.trim() === 'ระบบเงินยืมทดรอง') {
                    // พบ element ที่ตรงเงื่อนไข
                    el.textContent = 'ระบบเงินสดย่อย';
                    // สามารถทำงานต่อได้ที่นี่
                }
            });
        }
    });
</script>
