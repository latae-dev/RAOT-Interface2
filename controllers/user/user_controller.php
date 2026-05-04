<?php
// ini_set('log_errors', 1);
// $logFile = '../../logs/' . date('Y-m-d') . '.log';
// ini_set('error_log', $logFile);
// error_reporting(E_ALL);
// ini_set('display_errors', 0); // ปิดการแสดงผล
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
        $db = $database->connect('raot_db_qas');
        $this->userModel = new UserModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Parse ID from URL path (e.g., /controllers/user/user_controller.php/1)
        $id = null;
        $pathParts = explode("/", trim($requestUri, "/"));
        $controllerIndex = -1;
        foreach ($pathParts as $index => $part) {
            if (strpos($part, 'user_controller.php') !== false) {
                $controllerIndex = $index;
                break;
            }
        }
        if ($controllerIndex >= 0 && isset($pathParts[$controllerIndex + 1])) {
            $id = intval($pathParts[$controllerIndex + 1]);
        }

        // Also check query string as fallback
        if (!$id && isset($_GET['id'])) {
            $id = intval($_GET['id']);
        }

        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                $action = $_GET['action'] ?? null;
                if ($action === 'read_all') {
                    $this->getUsersAll();
                } elseif ($id) {
                    $this->getUser($id);
                } else {
                    $this->getUsers();
                }
                break;
            case 'POST':
                $this->createUser($data);
                break;
            case 'PUT':
                if ($id) {
                    $this->updateUser($id, $data);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "ID is required for update"]);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $this->deleteUser($id);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "ID is required for delete"]);
                }
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
        if ($users !== false) {
            if (is_array($users) && count($users) > 0) {
                echo json_encode($users); // ส่งข้อมูลผู้ใช้ทั้งหมดกลับไปในรูปแบบ JSON
            } else {
                echo json_encode([]); // Return empty array if no users
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve users']);
        }
    }

    public function getUsersAll()
    {
        $users = $this->userModel->readUserAll();
        if ($users !== false && $users !== null) {
            if (is_array($users)) {
                if (count($users) > 0) {
                    echo json_encode(['status' => 'success', 'data' => $users]);
                } else {
                    echo json_encode(['status' => 'success', 'data' => []]); // Return empty array
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Invalid data format returned']);
            }
        } else {
            // Fallback: ลองใช้ readUser() แทน
            $users = $this->userModel->readUser();
            if ($users !== false && $users !== null && is_array($users)) {
                echo json_encode(['status' => 'success', 'data' => $users]);
            } else {
                http_response_code(500);
                $error = error_get_last();
                $errorMsg = $error ? $error['message'] : 'Unable to retrieve users';
                echo json_encode(['status' => 'error', 'message' => $errorMsg]);
            }
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
        $branch_id = isset($data['branch_id']) && $data['branch_id'] !== '' ? intval($data['branch_id']) : null;
        $depart_id = isset($data['depart_id']) && $data['depart_id'] !== '' ? intval($data['depart_id']) : null;
        $role_id = isset($data['role_id']) && $data['role_id'] !== '' ? intval($data['role_id']) : null;
        $position_id = isset($data['position_id']) && $data['position_id'] !== '' ? intval($data['position_id']) : null;

        $result = $this->userModel->createUser(
            $branch_id,
            $depart_id,
            $role_id,
            $position_id,
            $data['user_code'],
            $data['user_pass'],
            $data['user_fname'],
            $data['user_lname'],
            $data['user_email'],
            $data['user_status']
        );

        if ($result) {
            echo json_encode(['message' => 'User created successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Error creating user']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูลผู้ใช้
    public function updateUser($id, $data)
    {
        $branch_id = isset($data['branch_id']) && $data['branch_id'] !== '' ? intval($data['branch_id']) : null;
        $depart_id = isset($data['depart_id']) && $data['depart_id'] !== '' ? intval($data['depart_id']) : null;
        $role_id = isset($data['role_id']) && $data['role_id'] !== '' ? intval($data['role_id']) : null;
        $position_id = isset($data['position_id']) && $data['position_id'] !== '' ? intval($data['position_id']) : null;

        $result = $this->userModel->updateUser(
            $id,
            $branch_id,
            $depart_id,
            $role_id,
            $position_id,
            $data['user_code'],
            $data['user_pass'],
            $data['user_fname'],
            $data['user_lname'],
            $data['user_email'],
            $data['user_status']
        );

        if ($result) {
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Error updating user']);
        }
    }

    // ฟังก์ชันเพื่อลบข้อมูลผู้ใช้
    public function deleteUser($id)
    {
        $result = $this->userModel->deleteUser($id);
        if ($result) {
            echo json_encode(['message' => 'User deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Error deleting user']);
        }
    }
}

$dataController = new UserController();
$dataController->processRequest();
