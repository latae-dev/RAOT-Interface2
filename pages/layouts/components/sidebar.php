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
    // Debug: ตรวจสอบ position IDs จาก session
    $positionIds = getUserPositionIds();
    
    // Debug: Log session data
    $sessionDebug = [
        'session_id' => session_id(),
        'has_user_data' => isset($_SESSION['user_data']),
        'user_data_keys' => isset($_SESSION['user_data']) ? array_keys($_SESSION['user_data']) : [],
        'position_ids' => $positionIds,
        'position_id_from_user_data' => $_SESSION['user_data']['position_id'] ?? null,
        'position_list_from_user_data' => $_SESSION['user_data']['position_list'] ?? null,
        'position_id_from_root' => $_SESSION['position_id'] ?? null
    ];
    // error_log("Sidebar Debug: " . json_encode($sessionDebug, JSON_UNESCAPED_UNICODE));
    
    // ถ้าไม่มี position IDs ใน session ให้แสดงเมนูทั้งหมด (แต่ log warning)
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
        // Script สำหรับจัดการ dynamic menu permissions (ถ้าต้องการ)
        // เนื่องจากตอนนี้ permissions จัดการที่ server-side แล้ว
        // แต่ถ้ามี menu ที่ต้องการซ่อนด้วย JavaScript ก็สามารถทำได้ที่นี่
        
        // Debug: Log position_id information
        const positionIds = <?php echo json_encode($positionIds ?? []); ?>;
        const sessionDebug = <?php echo json_encode($sessionDebug ?? [], JSON_UNESCAPED_UNICODE); ?>;
        const menuTree = <?php echo json_encode($menuTree ?? [], JSON_UNESCAPED_UNICODE); ?>;
        
        console.log("=== Sidebar Position ID Debug ===");
        console.log("Position IDs:", positionIds);
        console.log("Position IDs count:", positionIds.length);
        console.log("Session Debug:", sessionDebug);
        console.log("Position ID from user_data:", sessionDebug.position_id_from_user_data);
        console.log("Position ID from root session:", sessionDebug.position_id_from_root);
        console.log("Position list from user_data:", sessionDebug.position_list_from_user_data);
        console.log("Has user_data:", sessionDebug.has_user_data);
        console.log("User data keys:", sessionDebug.user_data_keys);
        console.log("Menu categories loaded:", <?php echo count($menuTree ?? []); ?>);
        
        // Debug: Count menus per position
        if (positionIds.length > 0) {
            console.log("\n=== Menu Count Analysis ===");
            positionIds.forEach((posId, index) => {
                console.log(`Position ID ${posId}:`, {
                    index: index,
                    is_primary: index === 0 ? 'Yes (from user_data.position_id)' : 'No'
                });
            });
            
            // Count total menu items
            let totalMenuItems = 0;
            let menuItemsByCategory = {};
            
            function countMenuItems(menu, category = null) {
                const cat = menu.category || category || 'Uncategorized';
                if (!menuItemsByCategory[cat]) {
                    menuItemsByCategory[cat] = 0;
                }
                menuItemsByCategory[cat]++;
                totalMenuItems++;
                
                if (menu.children && menu.children.length > 0) {
                    menu.children.forEach(child => {
                        countMenuItems(child, cat);
                    });
                }
            }
            
            menuTree.forEach(menu => {
                countMenuItems(menu);
            });
            
            console.log("Total menu items:", totalMenuItems);
            console.log("Menu items by category:", menuItemsByCategory);
            console.log("\n💡 Note: If position_id 2 has fewer menus, check:");
            console.log("   1. Menu permissions in tb_menu_permissions table");
            console.log("   2. If position_id 2 has is_granted=false for some menus");
            console.log("   3. If position_id 1 has more permissions than position_id 2");
        }
        
        if (positionIds.length === 0) {
            console.warn("⚠️ No position IDs found in session! Menus may not be filtered correctly.");
        } else {
            console.log("✅ Position IDs found:", positionIds);
        }
        
        console.log("Dynamic sidebar loaded successfully");

        const userData = JSON.parse(sessionStorage.getItem("raot_user_session"));
        const hasMatchAPHOP = userData?.position_list_id.some(id => ['6'].includes(id));

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
