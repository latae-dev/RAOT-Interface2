<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/factory_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class FactoryController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new FactoryModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getFactories();
                        break;
                    case 'one':
                        $this->getFactory();
                        break;
                    case 'search':
                        $this->searchFactories();
                        break;
                    case 'dropdown':
                        $this->getFactoryDropdown();
                        break;
                    case 'count':
                        $this->getTotalFactories();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createFactory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateFactory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteFactory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getFactories() {
        try {
            $data = $this->model->readFactories();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch factories'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFactories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFactory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readFactoryOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Factory not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFactory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchFactories() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchFactories($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search factories'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchFactories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFactoryDropdown() {
        try {
            $data = $this->model->getForDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch factory dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFactoryDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalFactories() {
        try {
            $total = $this->model->countAll();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalFactories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createFactory() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['factory_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists
        if ($this->model->isNameExists($input['factory_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Factory name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->createFactory($input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create factory'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Factory created successfully']);
        } catch (Exception $e) {
            error_log("Error in createFactory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateFactory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['factory_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists (excluding current record)
        if ($this->model->isNameExists($input['factory_name'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Factory name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateFactory($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update factory'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Factory updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateFactory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteFactory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteFactory($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete factory'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Factory deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteFactory: " . $e->getMessage());
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
$controller = new FactoryController();
$controller->processRequest();
