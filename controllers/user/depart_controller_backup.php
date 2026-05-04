<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/depart_model.php';
class DepartController
{
    private $departModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->departModel = new DepartModel($db);
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

    // ฟังก์ชันเพื่อดึงข้อมูล depart ทั้งหมด
    public function getDepts()
    {
        $departs = $this->departModel->readDepart();
        if ($departs) {
            echo json_encode($departs); // ส่งข้อมูล depart ทั้งหมดกลับในรูปแบบ JSON
        } else {
            echo json_encode(['message' => 'No departments found']);
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูล depart หนึ่งตัว
    public function getDept($id)
    {
        $depart = $this->departModel->readDepartOne($id);
        if ($depart) {
            echo json_encode($depart); // ส่งข้อมูล depart ที่มี id ที่ระบุ
        } else {
            echo json_encode(['message' => 'Department not found']);
        }
    }

    // ฟังก์ชันเพื่อสร้าง depart ใหม่
    public function createDept($data)
    {
        $result = $this->departModel->createDepart($data['depart_code'], $data['depart_name']);
        if ($result) {
            echo json_encode(['message' => 'Department created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating department']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูล depart
    public function updateDept($id, $data)
    {
        $result = $this->departModel->updateDepart($id, $data['depart_code'], $data['depart_name']);
        if ($result) {
            echo json_encode(['message' => 'Department updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating department']);
        }
    }
}

$dataController = new DepartController();
$dataController->processRequest();