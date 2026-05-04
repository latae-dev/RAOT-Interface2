<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/role_model.php';
class RoleController
{
    private $roleModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->roleModel = new RoleModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':

                break;
            case 'POST':

                break;
            case 'PUT':

                break;
            case 'DELETE':

                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูล role ทั้งหมด
    public function getRoles()
    {
        $roles = $this->roleModel->readRole();
        if ($roles) {
            echo json_encode($roles); // ส่งข้อมูล role ทั้งหมดกลับในรูปแบบ JSON
        } else {
            echo json_encode(['message' => 'No roles found']);
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูล role หนึ่งตัว
    public function getRole($id)
    {
        $role = $this->roleModel->readRoleOne($id);
        if ($role) {
            echo json_encode($role); // ส่งข้อมูล role ที่มี id ที่ระบุ
        } else {
            echo json_encode(['message' => 'Role not found']);
        }
    }

    // ฟังก์ชันเพื่อสร้าง role ใหม่
    public function createRole($data)
    {
        $result = $this->roleModel->createRole($data['role_code'], $data['position_name']);
        if ($result) {
            echo json_encode(['message' => 'Role created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating role']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูล role
    public function updateRole($id, $data)
    {
        $result = $this->roleModel->updateRole($id, $data['role_code'], $data['position_name']);
        if ($result) {
            echo json_encode(['message' => 'Role updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating role']);
        }
    }
}

$dataController = new RoleController();
$dataController->processRequest();