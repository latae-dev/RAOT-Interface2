<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
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
        $result = $this->positionModel->createPosition($data['position_code'], $data['position_name']);
        if ($result) {
            echo json_encode(['message' => 'Position created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating position']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูล position
    public function updatePosition($id, $data)
    {
        $result = $this->positionModel->updatePosition($id, $data['position_code'], $data['position_name']);
        if ($result) {
            echo json_encode(['message' => 'Position updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating position']);
        }
    }

}

$dataController = new PositionController();
$dataController->processRequest();