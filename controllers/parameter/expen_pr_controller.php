<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/expen_pr_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class ExpenPrController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new ExpenPrModel($db);
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
                    $this->getExpenPrs();
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

    private function getExpenPrs() {
        try {
            $data = $this->model->readExpenPr();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch expense PRs'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getExpenPrs: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function sendResponse($data, $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// สร้าง instance และเรียกใช้
$controller = new ExpenPrController();
$controller->processRequest();

