<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/scope_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class ScopeController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new ScopeModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getScopes();
                        break;
                    case 'one':
                        $this->getScope();
                        break;
                    case 'search':
                        $this->searchScopes();
                        break;
                    case 'dropdown':
                        $this->getScopeDropdown();
                        break;
                    case 'count':
                        $this->getTotalScopes();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createScope();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateScope();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteScope();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getScopes() {
        try {
            $data = $this->model->readScopes();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch scopes'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getScopes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getScope() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readScopeOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Scope not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getScope: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchScopes() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchScopes($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search scopes'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchScopes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getScopeDropdown() {
        try {
            $data = $this->model->getScopeDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch scope dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getScopeDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalScopes() {
        try {
            $total = $this->model->getTotalScopes();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalScopes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createScope() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['scope_code']) || !isset($input['scope_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkScopeCodeExists($input['scope_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Scope code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createScope($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create scope'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Scope created successfully']);
        } catch (Exception $e) {
            error_log("Error in createScope: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateScope() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['scope_code']) || !isset($input['scope_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkScopeCodeExists($input['scope_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Scope code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateScope($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update scope'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Scope updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateScope: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteScope() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteScope($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete scope'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Scope deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteScope: " . $e->getMessage());
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
$controller = new ScopeController();
$controller->processRequest();
