<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/user_model.php';
class UserController
{
    private $userModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->userModel = new UserModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                $action = $_GET['action'] ?? null;
                if ($action === 'read_all') {
                    $this->getUsersAll();
                } elseif ($action === 'get_one' && isset($_GET['id'])) {
                    $this->getUser($_GET['id']);
                } else {
                    $this->getUsers();
                }
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

    // ฟังก์ชันเพื่อดึงข้อมูลผู้ใช้ทั้งหมด
    public function getUsers()
    {
        $users = $this->userModel->readUser();
        if ($users) {
            echo json_encode($users); // ส่งข้อมูลผู้ใช้ทั้งหมดกลับไปในรูปแบบ JSON
        } else {
            echo json_encode(['message' => 'No users found']);
        }
    }

    public function getUsersAll()
    {
        $users = $this->userModel->readUserAll();
        if ($users !== false) {
            echo json_encode(['status' => 'success', 'data' => $users]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve users']);
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูลผู้ใช้หนึ่งคน
    public function getUser($id)
    {
        $user = $this->userModel->readUserOne($id);
        if ($user) {
            echo json_encode($user); // ส่งข้อมูลของผู้ใช้ที่มี id ที่ระบุ
        } else {
            echo json_encode(['message' => 'User not found']);
        }
    }

    // ฟังก์ชันเพื่อสร้างผู้ใช้ใหม่
    public function createUser($data)
    {
        $result = $this->userModel->createUser(
            $data['user_code'],
            $data['user_pass'],
            $data['user_fname'],
            $data['user_lname'],
            $data['user_email'],
            $data['user_status'],
            $data['branch_id'],
            $data['depart_id'],
            $data['role_id'],
            $data['position_id']
        );

        if ($result) {
            echo json_encode(['message' => 'User created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating user']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูลผู้ใช้
    public function updateUser($id, $data)
    {
        $result = $this->userModel->updateUser(
            $id,
            $data['user_code'],
            $data['user_pass'],
            $data['user_fname'],
            $data['user_lname'],
            $data['user_email'],
            $data['user_status'],
            $data['branch_id'],
            $data['depart_id'],
            $data['role_id'],
            $data['position_id']
        );

        if ($result) {
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating user']);
        }
    }
}

$dataController = new UserController();
$dataController->processRequest();
