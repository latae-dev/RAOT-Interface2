<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/liability_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class LiabilityController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new LiabilityModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getLiabilities();
                        break;
                    case 'one':
                        $this->getLiability();
                        break;
                    case 'search':
                        $this->searchLiabilities();
                        break;
                    case 'dropdown':
                        $this->getLiabilityDropdown();
                        break;
                    case 'count':
                        $this->getTotalLiabilities();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createLiability();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateLiability();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteLiability();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getLiabilities() {
        try {
            $data = $this->model->readLiabilities();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch liabilities'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getLiabilities: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getLiability() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readLiabilityOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Liability not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getLiability: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchLiabilities() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchLiabilities($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search liabilities'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchLiabilities: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getLiabilityDropdown() {
        try {
            $data = $this->model->getLiabilityDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch liability dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getLiabilityDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalLiabilities() {
        try {
            $total = $this->model->getTotalLiabilities();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalLiabilities: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createLiability() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['liability_code']) || !isset($input['liability_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkLiabilityCodeExists($input['liability_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Liability code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createLiability($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create liability'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Liability created successfully']);
        } catch (Exception $e) {
            error_log("Error in createLiability: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateLiability() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['liability_code']) || !isset($input['liability_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkLiabilityCodeExists($input['liability_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Liability code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateLiability($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update liability'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Liability updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateLiability: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteLiability() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteLiability($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete liability'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Liability deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteLiability: " . $e->getMessage());
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
$controller = new LiabilityController();
$controller->processRequest();
