<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/cost_center_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class CostCenterController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new CostCenterModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getCostCenters();
                        break;
                    case 'one':
                        $this->getCostCenter();
                        break;
                    case 'search':
                        $this->searchCostCenters();
                        break;
                    case 'dropdown':
                        $this->getCostCenterDropdown();
                        break;
                    case 'count':
                        $this->getTotalCostCenters();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createCostCenter();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateCostCenter();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteCostCenter();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getCostCenters() {
        try {
            $data = $this->model->readCostCenters();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch cost centers'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getCostCenters: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getCostCenter() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readCostCenterOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Cost center not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getCostCenter: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchCostCenters() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchCostCenters($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search cost centers'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchCostCenters: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getCostCenterDropdown() {
        try {
            $data = $this->model->getCostCenterDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch cost center dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getCostCenterDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalCostCenters() {
        try {
            $total = $this->model->getTotalCostCenters();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalCostCenters: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createCostCenter() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['cost_center_code']) || !isset($input['cost_center_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkCostCenterCodeExists($input['cost_center_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Cost center code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createCostCenter($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create cost center'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Cost center created successfully']);
        } catch (Exception $e) {
            error_log("Error in createCostCenter: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateCostCenter() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['cost_center_code']) || !isset($input['cost_center_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkCostCenterCodeExists($input['cost_center_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Cost center code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateCostCenter($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update cost center'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Cost center updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateCostCenter: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteCostCenter() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteCostCenter($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete cost center'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Cost center deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteCostCenter: " . $e->getMessage());
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
$controller = new CostCenterController();
$controller->processRequest();
