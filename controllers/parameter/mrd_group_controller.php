<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once __DIR__ . '/../../configs/database.php';

// include model
require_once __DIR__ . '/../../models/parameter/mrd_group_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class MrdGroupController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new MrdGroupModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getMrdGroups();
                        break;
                    case 'one':
                        $this->getMrdGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createMrdGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateMrdGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteMrdGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getMrdGroups() {
        try {
            $data = $this->model->readMrdGroup();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch mrd groups'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getMrdGroups: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getMrdGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readMrdGroupOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Mrd group not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getMrdGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createMrdGroup() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        try {
            // Note: MrdGroupModel doesn't have create method, so this is a placeholder
            $this->sendResponse(['status' => 'error', 'message' => 'Create method not implemented'], 501);
        } catch (Exception $e) {
            error_log("Error in createMrdGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateMrdGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        try {
            // Note: MrdGroupModel doesn't have update method, so this is a placeholder
            $this->sendResponse(['status' => 'error', 'message' => 'Update method not implemented'], 501);
        } catch (Exception $e) {
            error_log("Error in updateMrdGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteMrdGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            // Note: MrdGroupModel doesn't have delete method, so this is a placeholder
            $this->sendResponse(['status' => 'error', 'message' => 'Delete method not implemented'], 501);
        } catch (Exception $e) {
            error_log("Error in deleteMrdGroup: " . $e->getMessage());
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
$controller = new MrdGroupController();
$controller->processRequest();
