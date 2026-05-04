<?php
/**
 * Menu Helper Functions
 * 
 * Helper functions สำหรับจัดการเมนูและ permissions
 */

require_once __DIR__ . '/../configs/database.php';
require_once __DIR__ . '/../models/user/menu_model.php';
require_once __DIR__ . '/../models/user/menu_permission_model.php';

/**
 * ดึงเมนูสำหรับ user ปัจจุบัน
 * 
 * @param array|null $positionIds Array ของ position IDs (ถ้า null จะดึงจาก session)
 * @param bool $asTree ต้องการ tree structure หรือไม่
 * @return array|false Menu array or false on error
 */
function getMenuForUser($positionIds = null, $asTree = true)
{
    $database = new Database();
    $db = $database->connect('raot_db_qas');
    
    if (!$db) {
        // error_log("Menu Helper: Database connection failed");
        return false;
    }

    $menuModel = new MenuModel($db);

    // ถ้าไม่ระบุ positionIds ให้ดึงจาก session
    if ($positionIds === null) {
        $positionIds = getUserPositionIds();
    }

    // ถ้าไม่มี positionIds ให้แสดงเมนูทั้งหมด (แต่ log warning)
    if (empty($positionIds)) {
        // error_log("Menu Helper Warning: No position IDs found in session, showing all menus. Session data: " . json_encode($_SESSION['user_data'] ?? []));
        // ยังคงแสดงเมนูทั้งหมดเพื่อไม่ให้หน้าเว็บ error
        return $asTree ? $menuModel->readMenuTree() : $menuModel->readMenu();
    }

    // ดึงเมนูตาม positionIds
    $result = $asTree 
        ? $menuModel->readMenuTreeByPositionIds($positionIds)
        : $menuModel->readMenuByPositionIds($positionIds);
    
    // Debug: Log result
    if ($result === false) {
        // error_log("Menu Helper: Failed to read menu from database");
    } elseif (empty($result)) {
        // error_log("Menu Helper: Menu result is empty for position IDs: " . json_encode($positionIds));
    } else {
        // error_log("Menu Helper: Successfully loaded " . count($result) . " menu items for position IDs: " . json_encode($positionIds));
    }
    
    return $result;
}

/**
 * ดึง position IDs จาก session
 * 
 * @return array Array of position IDs
 */
function getUserPositionIds()
{
    // ตรวจสอบว่า session ถูก start หรือไม่
    if (session_id() === '') {
        @session_start();
    }
    
    $positionIds = [];

    // ตรวจสอบจาก session user_data.position_list (multiple positions)
    if (isset($_SESSION['user_data']['position_list']) && is_array($_SESSION['user_data']['position_list'])) {
        foreach ($_SESSION['user_data']['position_list'] as $position) {
            if (isset($position['id'])) {
                $positionIds[] = (int)$position['id'];
            } elseif (is_numeric($position)) {
                // ถ้า position_list เป็น array ของ IDs โดยตรง
                $positionIds[] = (int)$position;
            }
        }
    }

    // ถ้าไม่มี position_list ให้ใช้ position_id จาก user_data
    if (empty($positionIds) && isset($_SESSION['user_data']['position_id']) && $_SESSION['user_data']['position_id'] !== null) {
        $positionIds[] = (int)$_SESSION['user_data']['position_id'];
    }

    // Fallback: ใช้ position_id จาก root session (เพื่อ backward compatibility)
    // authen_controller ตั้งค่า $_SESSION['position_id'] ที่นี่
    if (empty($positionIds)) {
        // ลองดึงจาก root session ก่อน (authen_controller ตั้งค่าที่นี่)
        if (isset($_SESSION['position_id']) && $_SESSION['position_id'] !== null && $_SESSION['position_id'] !== '' && $_SESSION['position_id'] !== 0) {
            $positionIds[] = (int)$_SESSION['position_id'];
        }
        // ถ้ายังไม่มี ลองดึงจาก user_data อีกครั้ง (กรณีที่ไม่ได้อยู่ใน nested array)
        if (empty($positionIds) && isset($_SESSION['user_data']) && is_array($_SESSION['user_data'])) {
            if (isset($_SESSION['user_data']['position_id']) && $_SESSION['user_data']['position_id'] !== null && $_SESSION['user_data']['position_id'] !== '' && $_SESSION['user_data']['position_id'] !== 0) {
                $positionIds[] = (int)$_SESSION['user_data']['position_id'];
            }
        }
    }
    
    // Debug logging (เพิ่มรายละเอียดเพื่อ debug)
    $debugInfo = [
        'session_id' => session_id(),
        'has_user_data' => isset($_SESSION['user_data']),
        'user_data_position_id' => $_SESSION['user_data']['position_id'] ?? null,
        'user_data_position_list' => $_SESSION['user_data']['position_list'] ?? null,
        'root_position_id' => $_SESSION['position_id'] ?? null,
        'result_position_ids' => $positionIds
    ];
    
    if (empty($positionIds)) {
        // error_log("getUserPositionIds: ❌ No position IDs found. Debug: " . json_encode($debugInfo, JSON_UNESCAPED_UNICODE));
    } else {
        $source = 'unknown';
        if (isset($_SESSION['user_data']['position_list']) && is_array($_SESSION['user_data']['position_list'])) {
            $source = 'user_data.position_list';
        } elseif (isset($_SESSION['user_data']['position_id'])) {
            $source = 'user_data.position_id';
        } elseif (isset($_SESSION['position_id'])) {
            $source = 'root.position_id';
        }
        // error_log("getUserPositionIds: ✅ Found position IDs: " . json_encode($positionIds) . " (from " . $source . ")");
        // error_log("getUserPositionIds: Debug details: " . json_encode($debugInfo, JSON_UNESCAPED_UNICODE));
    }

    return $positionIds;
}

/**
 * ตรวจสอบว่า user มีสิทธิ์เข้าถึง menu หรือไม่
 * 
 * @param int $menuId Menu ID
 * @param array|null $positionIds Array ของ position IDs (ถ้า null จะดึงจาก session)
 * @return bool|null true = มีสิทธิ์, false = ไม่มีสิทธิ์, null = ไม่มี permission record (แสดงให้ทุกคน)
 */
function checkMenuPermission($menuId, $positionIds = null)
{
    // ถ้าไม่ระบุ positionIds ให้ดึงจาก session
    if ($positionIds === null) {
        $positionIds = getUserPositionIds();
    }

    // ถ้าไม่มี positionIds ให้แสดงเมนู
    if (empty($positionIds)) {
        return null; // null = ไม่มี permission record (แสดงให้ทุกคน)
    }

    $database = new Database();
    $db = $database->connect('raot_db_qas');
    
    if (!$db) {
        // error_log("Menu Helper: Database connection failed");
        return false;
    }

    $menuPermissionModel = new MenuPermissionModel($db);

    // ตรวจสอบว่ามี permission record หรือไม่
    $hasPermission = false;
    foreach ($positionIds as $positionId) {
        $result = $menuPermissionModel->checkPermission($positionId, $menuId);
        
        if ($result === true) {
            // มีสิทธิ์
            return true;
        } elseif ($result === false) {
            // ไม่มีสิทธิ์
            $hasPermission = false;
            break;
        } elseif ($result === null) {
            // ไม่มี permission record (แสดงให้ทุกคน)
            $hasPermission = null;
        }
    }

    return $hasPermission;
}

/**
 * สร้าง HTML สำหรับเมนู sidebar
 * 
 * @param array $menuTree Menu tree structure
 * @param string $baseUrl Base URL สำหรับ menu paths
 * @param int $level Current level (for indentation)
 * @return string HTML string
 */
function renderMenuHTML($menuTree, $baseUrl = '', $level = 0)
{
    $html = '';
    $indent = str_repeat('  ', $level);

    foreach ($menuTree as $menu) {
        $hasChildren = !empty($menu['children']);
        $menuPath = $menu['menu_path'] ?? '#';
        $icon = $menu['icon'] ?? '';
        $menuName = htmlspecialchars($menu['menu_name'], ENT_QUOTES, 'UTF-8');
        
        // ถ้ามี children ให้สร้าง submenu
        if ($hasChildren) {
            $html .= $indent . '<li class="slide has-sub">' . "\n";
            $html .= $indent . '  <a href="javascript:void(0);" class="side-menu__item">' . "\n";
            if ($icon) {
                $html .= $indent . '    <i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' side-menu__icon"></i>' . "\n";
            }
            $html .= $indent . '    <span class="side-menu__label">' . $menuName . '</span>' . "\n";
            $html .= $indent . '    <i class="fe fe-chevron-right side-menu__angle"></i>' . "\n";
            $html .= $indent . '  </a>' . "\n";
            $html .= $indent . '  <ul class="slide-menu child' . ($level + 1) . '">' . "\n";
            
            // Render children
            $html .= renderMenuHTML($menu['children'], $baseUrl, $level + 1);
            
            $html .= $indent . '  </ul>' . "\n";
            $html .= $indent . '</li>' . "\n";
        } else {
            // Single menu item
            $html .= $indent . '<li class="slide">' . "\n";
            $html .= $indent . '  <a href="' . htmlspecialchars($baseUrl . $menuPath, ENT_QUOTES, 'UTF-8') . '" class="side-menu__item">' . "\n";
            if ($icon) {
                $html .= $indent . '    <i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' side-menu__icon"></i>' . "\n";
            }
            $html .= $indent . '    <span class="side-menu__label">' . $menuName . '</span>' . "\n";
            $html .= $indent . '  </a>' . "\n";
            $html .= $indent . '</li>' . "\n";
        }
    }

    return $html;
}

/**
 * Render menu item
 * 
 * @param array $menu Menu item array
 * @param string $urlVendor URL vendor prefix
 * @param string $urlParcel URL parcel prefix
 * @param int $level Current level (for indentation)
 * @return string HTML string
 */
if (!function_exists('renderMenuItem')) {
function renderMenuItem($menu, $urlVendor = '', $urlParcel = '', $level = 0) {
    $html = '';
    $hasChildren = !empty($menu['children']) && is_array($menu['children']) && count($menu['children']) > 0;
    $menuPath = $menu['menu_path'] ?? '#';
    $icon = $menu['icon'] ?? '';
    $menuName = htmlspecialchars($menu['menu_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $menuCode = htmlspecialchars($menu['menu_code'] ?? '', ENT_QUOTES, 'UTF-8');
    
    // จัดการ menu_path
    if ($menuPath !== '#' && !empty($menuPath)) {
        // ถ้า path เริ่มด้วย ../parcel/pages/ หรือ parcel/pages/ ให้ใช้ $url_parcel
        if (strpos($menuPath, '../parcel/pages/') === 0 || strpos($menuPath, 'parcel/pages/') === 0) {
            $menuPath = str_replace(['../parcel/pages/', 'parcel/pages/'], $urlParcel, $menuPath);
        } elseif (strpos($menuPath, '../parcel/') === 0) {
            // Handle ../parcel/pages/ pattern
            $menuPath = str_replace('../parcel/', $urlParcel, $menuPath);
        } else {
            // ใช้ $url_vendor สำหรับ paths อื่นๆ
            $menuPath = $urlVendor . $menuPath;
        }
    }
    
    // Special handling สำหรับ logout menu
    if ($menuCode === 'MENU_LOGOUT') {
        $html .= '<li class="slide">' . "\n";
        $html .= '  <a href="#" onclick="logOut()" class="side-menu__item">' . "\n";
        if ($icon) {
            $html .= '    <i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' side-menu__icon"></i>' . "\n";
        }
        $html .= '    <span class="side-menu__label">' . $menuName . '</span>' . "\n";
        $html .= '  </a>' . "\n";
        $html .= '</li>' . "\n";
        return $html;
    }
    
    // ถ้ามี children ให้สร้าง submenu
    if ($hasChildren) {
        $html .= '<li class="slide has-sub">' . "\n";
        $html .= '  <a href="javascript:void(0);" class="side-menu__item">' . "\n";
        if ($icon) {
            $html .= '    <i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' side-menu__icon"></i>' . "\n";
        }
        $html .= '    <span class="side-menu__label">' . $menuName . '</span>' . "\n";
        $html .= '    <i class="fe fe-chevron-right side-menu__angle"></i>' . "\n";
        $html .= '  </a>' . "\n";
        $html .= '  <ul class="slide-menu child' . ($level + 1) . '">' . "\n";
        
        // Render label ถ้าเป็น parent menu (ไม่มี path)
        if (($menu['menu_path'] === null || $menu['menu_path'] === '' || $menu['menu_path'] === '#') && $level === 0) {
            $html .= '    <li class="slide side-menu__label1">' . "\n";
            $html .= '      <a href="javascript:void(0);">' . $menuName . '</a>' . "\n";
            $html .= '    </li>' . "\n";
        }
        
        // Render children
        foreach ($menu['children'] as $child) {
            $html .= renderMenuItem($child, $urlVendor, $urlParcel, $level + 1);
        }
        
        $html .= '  </ul>' . "\n";
        $html .= '</li>' . "\n";
    } else {
        // Single menu item (ไม่มี children)
        $html .= '<li class="slide">' . "\n";
        $html .= '  <a href="' . htmlspecialchars($menuPath, ENT_QUOTES, 'UTF-8') . '" class="side-menu__item">' . "\n";
        if ($icon) {
            $html .= '    <i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' side-menu__icon"></i>' . "\n";
        }
        $html .= '    <span class="side-menu__label">' . $menuName . '</span>' . "\n";
        $html .= '  </a>' . "\n";
        $html .= '</li>' . "\n";
    }
    
    return $html;
}
} // End if !function_exists('renderMenuItem')

/**
 * Render menu with categories
 * 
 * @param array $menuTree Menu tree structure
 * @param string $urlVendor URL vendor prefix
 * @param string $urlParcel URL parcel prefix
 * @return string HTML string
 */
if (!function_exists('renderMenuWithCategories')) {
function renderMenuWithCategories($menuTree, $urlVendor = '', $urlParcel = '') {
    $html = '';
    $currentCategory = null;
    
    // Debug: Log menu tree structure
    // error_log("renderMenuWithCategories: Processing " . count($menuTree) . " menu items");
    
    foreach ($menuTree as $menu) {
        $category = $menu['category'] ?? null;
        $menuCode = $menu['menu_code'] ?? 'N/A';
        $menuName = $menu['menu_name'] ?? 'N/A';
        
        // Debug: Log each menu item
        // error_log("renderMenuWithCategories: Processing menu - code: $menuCode, name: $menuName, category: " . ($category ?? 'null'));
        
        // ถ้ามี category ใหม่ให้สร้าง category label
        if ($category && $category !== $currentCategory) {
            if ($currentCategory !== null) {
                // ปิด category เดิม (ถ้าต้องการ)
            }
            
            $html .= '<!-- Start::slide__' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . ' -->' . "\n";
            $html .= '<li class="slide__category"><span class="category-name">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</span></li>' . "\n";
            $html .= '<!-- End::slide__' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . ' -->' . "\n";
            
            $currentCategory = $category;
        }
        
        // Render menu item
        $menuHtml = renderMenuItem($menu, $urlVendor, $urlParcel, 0);
        $html .= $menuHtml;
    }
    
    // error_log("renderMenuWithCategories: Generated HTML with " . strlen($html) . " characters");
    return $html;
}
} // End if !function_exists('renderMenuWithCategories')

