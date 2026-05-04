<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/monetary_unit_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class MonetaryUnitController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new MonetaryUnitModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getMonetaryUnits();
                        break;
                    case 'one':
                        $this->getMonetaryUnit();
                        break;
                    case 'search':
                        $this->searchMonetaryUnits();
                        break;
                    case 'dropdown':
                        $this->getMonetaryUnitDropdown();
                        break;
                    case 'count':
                        $this->getTotalMonetaryUnits();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createMonetaryUnit();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateMonetaryUnit();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteMonetaryUnit();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getMonetaryUnits() {
        try {
            $data = $this->model->readMonetaryUnits();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch monetary units'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getMonetaryUnits: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getMonetaryUnit() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->getMonetaryUnitById($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Monetary unit not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getMonetaryUnit: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchMonetaryUnits() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchMonetaryUnits($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search monetary units'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchMonetaryUnits: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getMonetaryUnitDropdown() {
        try {
            $data = $this->model->getMonetaryUnitDropdownData();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch monetary unit dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getMonetaryUnitDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalMonetaryUnits() {
        try {
            $total = $this->model->getTotalMonetaryUnits();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalMonetaryUnits: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createMonetaryUnit() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['monetary_unit_name_en']) || !isset($input['monetary_unit_name_th'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists
        if ($this->model->checkMonetaryUnitNameExists($input['monetary_unit_name_en'], $input['monetary_unit_name_th'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Monetary unit name already exists'], 409);
            return;
        }

        try {
            $result = $this->model->createMonetaryUnit($input);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'id' => $result['id'] ?? null, 'message' => 'Monetary unit created successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create monetary unit'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in createMonetaryUnit: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateMonetaryUnit() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['monetary_unit_name_en']) || !isset($input['monetary_unit_name_th'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists (excluding current record)
        if ($this->model->checkMonetaryUnitNameExists($input['monetary_unit_name_en'], $input['monetary_unit_name_th'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Monetary unit name already exists'], 409);
            return;
        }

        try {
            $result = $this->model->updateMonetaryUnit($id, $input);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'message' => 'Monetary unit updated successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update monetary unit'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in updateMonetaryUnit: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteMonetaryUnit() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $result = $this->model->deleteMonetaryUnit($id);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'message' => 'Monetary unit deleted successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete monetary unit'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in deleteMonetaryUnit: " . $e->getMessage());
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
$controller = new MonetaryUnitController();
$controller->processRequest();
