<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('// // // error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json; charset=utf-8");
include_once '../../configs/database.php';
include_once '../../models/user/menu_model.php';
include_once '../../models/user/menu_permission_model.php';

class MenuController
{
    private $menuModel;
    private $menuPermissionModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->menuModel = new MenuModel($db);
        $this->menuPermissionModel = new MenuPermissionModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                $this->handleGet($action);
                break;
            case 'POST':
                $this->handlePost($action);
                break;
            case 'PUT':
                $this->handlePut($action);
                break;
            case 'DELETE':
                $this->handleDelete($action);
                break;
            default:
                http_response_code(405);
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    private function handleGet($action)
    {
        switch ($action) {
            case 'list':
                $this->getMenuList();
                break;
            case 'tree':
                $this->getMenuTree();
                break;
            case 'by-position':
                $this->getMenuByPosition();
                break;
            case 'tree-by-position':
                $this->getMenuTreeByPosition();
                break;
            case 'categories':
                $this->getCategories();
                break;
            case 'one':
                $this->getMenuOne();
                break;
            case 'permissions':
                $this->getPermissions();
                break;
            case 'permissions-by-menu':
                $this->getPermissionsByMenu();
                break;
            case 'permissions-by-position':
                $this->getPermissionsByPosition();
                break;
            default:
                // Default: return menu tree for current user
                $this->getMenuTreeForCurrentUser();
                break;
        }
    }

    private function handlePost($action)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        switch ($action) {
            case 'create':
                $this->createMenu($data);
                break;
            case 'create-permission':
                $this->createPermission($data);
                break;
            case 'bulk-permissions':
                $this->bulkUpdatePermissions($data);
                break;
            case 'copy-permissions':
                $this->copyPermissions($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(["message" => "Invalid action"]);
                break;
        }
    }

    private function handlePut($action)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        switch ($action) {
            case 'update':
                $this->updateMenu($data);
                break;
            case 'update-permission':
                $this->updatePermission($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(["message" => "Invalid action"]);
                break;
        }
    }

    private function handleDelete($action)
    {
        switch ($action) {
            case 'delete':
                $this->deleteMenu();
                break;
            case 'delete-permission':
                $this->deletePermission();
                break;
            default:
                http_response_code(400);
                echo json_encode(["message" => "Invalid action"]);
                break;
        }
    }

    // GET: ดึงเมนูทั้งหมด
    private function getMenuList()
    {
        $menus = $this->menuModel->readMenu();
        if ($menus !== false) {
            echo json_encode($menus, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching menus"]);
        }
    }

    // GET: ดึงเมนูแบบ tree structure
    private function getMenuTree()
    {
        $menuTree = $this->menuModel->readMenuTree();
        if ($menuTree !== false) {
            echo json_encode($menuTree, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching menu tree"]);
        }
    }

    // GET: ดึงเมนูตาม position_ids
    private function getMenuByPosition()
    {
        $positionIds = $_GET['position_ids'] ?? '';
        
        if (empty($positionIds)) {
            http_response_code(400);
            echo json_encode(["message" => "position_ids parameter is required"]);
            return;
        }

        $positionIdsArray = is_array($positionIds) ? $positionIds : explode(',', $positionIds);
        $positionIdsArray = array_map('intval', $positionIdsArray);
        $positionIdsArray = array_filter($positionIdsArray);

        $menus = $this->menuModel->readMenuByPositionIds($positionIdsArray);
        if ($menus !== false) {
            echo json_encode($menus, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching menus by position"]);
        }
    }

    // GET: ดึงเมนูแบบ tree ตาม position_ids
    private function getMenuTreeByPosition()
    {
        $positionIds = $_GET['position_ids'] ?? '';
        
        if (empty($positionIds)) {
            http_response_code(400);
            echo json_encode(["message" => "position_ids parameter is required"]);
            return;
        }

        $positionIdsArray = is_array($positionIds) ? $positionIds : explode(',', $positionIds);
        $positionIdsArray = array_map('intval', $positionIdsArray);
        $positionIdsArray = array_filter($positionIdsArray);

        $menuTree = $this->menuModel->readMenuTreeByPositionIds($positionIdsArray);
        if ($menuTree !== false) {
            echo json_encode($menuTree, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching menu tree by position"]);
        }
    }

    // GET: ดึงเมนูสำหรับ user ปัจจุบัน (จาก session)
    private function getMenuTreeForCurrentUser()
    {
        // ดึง position_ids จาก session
        $positionIds = $this->getUserPositionIds();

        if (empty($positionIds)) {
            // ถ้าไม่มี position ให้แสดงเมนูทั้งหมด (ไม่มี permission records)
            $menuTree = $this->menuModel->readMenuTree();
        } else {
            $menuTree = $this->menuModel->readMenuTreeByPositionIds($positionIds);
        }

        if ($menuTree !== false) {
            echo json_encode($menuTree, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching menu tree"]);
        }
    }

    // GET: ดึง categories
    private function getCategories()
    {
        $categories = $this->menuModel->readCategories();
        if ($categories !== false) {
            echo json_encode($categories, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching categories"]);
        }
    }

    // GET: ดึงเมนูหนึ่งรายการ
    private function getMenuOne()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "id parameter is required"]);
            return;
        }

        $menu = $this->menuModel->readMenuOne($id);
        if ($menu) {
            echo json_encode($menu, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Menu not found"]);
        }
    }

    // POST: สร้างเมนูใหม่
    private function createMenu($data)
    {
        $required = ['menu_code', 'menu_name'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["message" => "$field is required"]);
                return;
            }
        }

        $result = $this->menuModel->createMenu(
            $data['menu_code'],
            $data['menu_name'],
            $data['menu_path'] ?? null,
            $data['parent_id'] ?? null,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0,
            $data['category'] ?? null,
            $data['is_active'] ?? true
        );

        if ($result !== false) {
            http_response_code(201);
            echo json_encode([
                "message" => "Menu created successfully",
                "id" => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error creating menu"]);
        }
    }

    // PUT: อัปเดตเมนู
    private function updateMenu($data)
    {
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "id is required"]);
            return;
        }

        $required = ['menu_code', 'menu_name'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["message" => "$field is required"]);
                return;
            }
        }

        $result = $this->menuModel->updateMenu(
            $data['id'],
            $data['menu_code'],
            $data['menu_name'],
            $data['menu_path'] ?? null,
            $data['parent_id'] ?? null,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0,
            $data['category'] ?? null,
            $data['is_active'] ?? true
        );

        if ($result) {
            echo json_encode(["message" => "Menu updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating menu"]);
        }
    }

    // DELETE: ลบเมนู
    private function deleteMenu()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "id parameter is required"]);
            return;
        }

        $result = $this->menuModel->deleteMenu($id);
        if ($result) {
            echo json_encode(["message" => "Menu deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error deleting menu"]);
        }
    }

    // GET: ดึง permissions ทั้งหมด
    private function getPermissions()
    {
        $permissions = $this->menuPermissionModel->readPermissions();
        if ($permissions !== false) {
            echo json_encode($permissions, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching permissions"]);
        }
    }

    // GET: ดึง permissions ตาม menu_id
    private function getPermissionsByMenu()
    {
        $menuId = $_GET['menu_id'] ?? null;
        
        if (!$menuId) {
            http_response_code(400);
            echo json_encode(["message" => "menu_id parameter is required"]);
            return;
        }

        $permissions = $this->menuPermissionModel->readPermissionsByMenuId($menuId);
        if ($permissions !== false) {
            echo json_encode($permissions, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching permissions"]);
        }
    }

    // GET: ดึง permissions ตาม position_id
    private function getPermissionsByPosition()
    {
        $positionId = $_GET['position_id'] ?? null;
        
        if (!$positionId) {
            http_response_code(400);
            echo json_encode(["message" => "position_id parameter is required"]);
            return;
        }

        $permissions = $this->menuPermissionModel->readPermissionsByPositionId($positionId);
        if ($permissions !== false) {
            echo json_encode($permissions, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error fetching permissions"]);
        }
    }

    // POST: สร้าง permission
    private function createPermission($data)
    {
        $required = ['position_id', 'menu_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["message" => "$field is required"]);
                return;
            }
        }

        $result = $this->menuPermissionModel->createPermission(
            $data['position_id'],
            $data['menu_id'],
            $data['is_granted'] ?? true
        );

        if ($result !== false) {
            http_response_code(201);
            echo json_encode([
                "message" => "Permission created successfully",
                "id" => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error creating permission"]);
        }
    }

    // PUT: อัปเดต permission
    private function updatePermission($data)
    {
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "id is required"]);
            return;
        }

        $result = $this->menuPermissionModel->updatePermission(
            $data['id'],
            $data['is_granted'] ?? true
        );

        if ($result) {
            echo json_encode(["message" => "Permission updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating permission"]);
        }
    }

    // DELETE: ลบ permission
    private function deletePermission()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "id parameter is required"]);
            return;
        }

        $result = $this->menuPermissionModel->deletePermission($id);
        if ($result) {
            echo json_encode(["message" => "Permission deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error deleting permission"]);
        }
    }

    // POST: Bulk update permissions
    private function bulkUpdatePermissions($data)
    {
        if (!isset($data['position_id']) || !isset($data['permissions'])) {
            http_response_code(400);
            echo json_encode(["message" => "position_id and permissions are required"]);
            return;
        }

        $result = $this->menuPermissionModel->updatePermissionsForPosition(
            $data['position_id'],
            $data['permissions']
        );

        if ($result) {
            echo json_encode(["message" => "Permissions updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating permissions"]);
        }
    }

    // POST: Copy permissions
    private function copyPermissions($data)
    {
        if (!isset($data['from_position_id']) || !isset($data['to_position_id'])) {
            http_response_code(400);
            echo json_encode(["message" => "from_position_id and to_position_id are required"]);
            return;
        }

        $result = $this->menuPermissionModel->copyPermissions(
            $data['from_position_id'],
            $data['to_position_id']
        );

        if ($result) {
            echo json_encode(["message" => "Permissions copied successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error copying permissions"]);
        }
    }

    // Helper: ดึง position_ids จาก session
    private function getUserPositionIds()
    {
        $positionIds = [];

        // ตรวจสอบจาก session user_data.position_list (multiple positions)
        if (isset($_SESSION['user_data']['position_list']) && is_array($_SESSION['user_data']['position_list'])) {
            foreach ($_SESSION['user_data']['position_list'] as $position) {
                if (isset($position['id'])) {
                    $positionIds[] = (int)$position['id'];
                }
            }
        }

        // ถ้าไม่มี position_list ให้ใช้ position_id จาก user_data
        if (empty($positionIds) && isset($_SESSION['user_data']['position_id']) && $_SESSION['user_data']['position_id'] !== null) {
            $positionIds[] = (int)$_SESSION['user_data']['position_id'];
        }

        // Fallback: ใช้ position_id จาก root session (เพื่อ backward compatibility)
        if (empty($positionIds) && isset($_SESSION['position_id']) && $_SESSION['position_id'] !== null) {
            $positionIds[] = (int)$_SESSION['position_id'];
        }

        return $positionIds;
    }
}

$menuController = new MenuController();
$menuController->processRequest();

