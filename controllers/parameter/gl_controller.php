<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/gl_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class GLController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new GLModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getGLAccounts();
                        break;
                    case 'one':
                        $this->getGLAccount();
                        break;
                    case 'search':
                        $this->searchGLAccounts();
                        break;
                    case 'dropdown':
                        $this->getGLAccountDropdown();
                        break;
                    case 'count':
                        $this->getTotalGLAccounts();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createGLAccount();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateGLAccount();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteGLAccount();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getGLAccounts() {
        try {
            $data = $this->model->readGLAccounts();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch GL accounts'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getGLAccounts: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getGLAccount() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->getGLAccountById($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'GL Account not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getGLAccount: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchGLAccounts() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchGLAccounts($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search GL accounts'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchGLAccounts: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getGLAccountDropdown() {
        try {
            $data = $this->model->getGLAccountDropdownData();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch GL account dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getGLAccountDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalGLAccounts() {
        try {
            $total = $this->model->getTotalGLAccounts();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalGLAccounts: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createGLAccount() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['gl_account_code']) || !isset($input['gl_account_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkGLAccountCodeExists($input['gl_account_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'GL Account code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createGLAccount($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create GL account'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'GL Account created successfully']);
        } catch (Exception $e) {
            error_log("Error in createGLAccount: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateGLAccount() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['gl_account_code']) || !isset($input['gl_account_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkGLAccountCodeExists($input['gl_account_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'GL Account code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateGLAccount($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update GL account'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'GL Account updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateGLAccount: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteGLAccount() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteGLAccount($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete GL account'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'GL Account deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteGLAccount: " . $e->getMessage());
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
$controller = new GLController();
$controller->processRequest();
