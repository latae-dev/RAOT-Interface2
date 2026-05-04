<?php
header('Content-Type: application/json; charset=utf-8');


error_reporting(E_ALL);
ini_set('display_errors', 1); // ปิดการแสดงผล

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/quarterlist_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class QuarterlistController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new QuarterlistModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'all') {
                    $this->getQuarterlists();
                } elseif ($_GET['action'] == 'quarter_search') {
                    $this->readAll( $_GET['limit'], $_GET['offset']);
                }
                break;
            case 'POST':

                break;
            case 'PUT':
                if ($_GET['action'] == 'update_quarter') {
                    $this->updateQuarter($data, $_GET['id']);
                } 
                break;
            case 'DELETE':

                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    private function readAll($limit, $offset)
    {
        $respond = $this->model->readAll($limit, $offset);
        echo  json_encode($respond);
    }

    private function getQuarterlists() {
        try {
            $data = $this->model->readQuarterlist();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch quarterlist'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getQuarterlists: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateQuarter($data, $id)
    {
        $respond = $this->model->updateQuarter($data, $id);
        // ตรวจสอบผลลัพธ์จากการอัพเดท
        if ($respond) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success']);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed materials']);
        }
    }

    private function sendResponse($data, $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// สร้าง instance และเรียกใช้
$controller = new QuarterlistController();
$controller->processRequest();
