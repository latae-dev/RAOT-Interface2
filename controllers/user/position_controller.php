<?php
// ini_set('log_errors', 1);
// $logFile = '../../logs/' . date('Y-m-d') . '.log';
// ini_set('// error_log', $logFile);
// error_reporting(E_ALL);
// ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/position_model.php';
class PositionController
{
    private $positionModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->positionModel = new PositionModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Parse ID from URL path (e.g., /controllers/user/position_controller.php/1)
        $id = null;
        $pathParts = explode("/", trim($requestUri, "/"));
        $controllerIndex = -1;
        foreach ($pathParts as $index => $part) {
            if (strpos($part, 'position_controller.php') !== false) {
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
                if ($id) {
                    $this->getPosition($id);
                } else {
                    $this->getPositions();
                }
                break;
            case 'POST':
                $this->createPosition($data);
                break;
            case 'PUT':
                if ($id) {
                    $this->updatePosition($id, $data);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "ID is required for update"]);
                }
                break;
            case 'DELETE':
                if ($id) {
                    $this->deletePosition($id);
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

    // ฟังก์ชันเพื่อดึงข้อมูล position ทั้งหมด
    public function getPositions()
    {
        $positions = $this->positionModel->readPosition();
        if ($positions) {
            echo json_encode($positions); // ส่งข้อมูล position ทั้งหมดกลับในรูปแบบ JSON
        } else {
            echo json_encode(['message' => 'No positions found']);
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูล position หนึ่งตัว
    public function getPosition($id)
    {
        $position = $this->positionModel->readPositionOne($id);
        if ($position) {
            echo json_encode($position); // ส่งข้อมูล position ที่มี id ที่ระบุ
        } else {
            echo json_encode(['message' => 'Position not found']);
        }
    }

    // ฟังก์ชันเพื่อสร้าง position ใหม่
    public function createPosition($data)
    {
        $result = $this->positionModel->createPosition($data);
        if ($result) {
            echo json_encode(['message' => 'Position created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating position']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูล position
    public function updatePosition($id, $data)
    {
        $result = $this->positionModel->updatePosition($id, $data);
        if ($result) {
            echo json_encode(['message' => 'Position updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating position']);
        }
    }

    // ฟังก์ชันเพื่อลบข้อมูล position
    public function deletePosition($id)
    {
        $result = $this->positionModel->deletePosition($id);
        if ($result) {
            echo json_encode(['message' => 'Position deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Error deleting position. Position may be in use.']);
        }
    }
}

$dataController = new PositionController();
$dataController->processRequest();
