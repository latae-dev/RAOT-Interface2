<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/warehouse_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class WarehouseController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new WarehouseModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getWarehouses();
                        break;
                    case 'one':
                        $this->getWarehouse();
                        break;
                    case 'search':
                        $this->searchWarehouses();
                        break;
                    case 'dropdown':
                        $this->getWarehouseDropdown();
                        break;
                    case 'count':
                        $this->getTotalWarehouses();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createWarehouse();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateWarehouse();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteWarehouse();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getWarehouses() {
        try {
            $data = $this->model->readWarehouses();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch warehouses'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getWarehouses: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getWarehouse() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readWarehouseOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Warehouse not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getWarehouse: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchWarehouses() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchWarehouses($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search warehouses'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchWarehouses: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getWarehouseDropdown() {
        try {
            $data = $this->model->getForDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch warehouse dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getWarehouseDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalWarehouses() {
        try {
            $total = $this->model->countAll();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalWarehouses: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createWarehouse() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['warehouse_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists
        if ($this->model->isNameExists($input['warehouse_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Warehouse name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->createWarehouse($input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create warehouse'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Warehouse created successfully']);
        } catch (Exception $e) {
            error_log("Error in createWarehouse: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateWarehouse() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['warehouse_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists (excluding current record)
        if ($this->model->isNameExists($input['warehouse_name'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Warehouse name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateWarehouse($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update warehouse'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Warehouse updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateWarehouse: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteWarehouse() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteWarehouse($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete warehouse'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Warehouse deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteWarehouse: " . $e->getMessage());
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
$controller = new WarehouseController();
$controller->processRequest();
