<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/purchase_group_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class PurchaseGroupController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new PurchaseGroupModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getPurchaseGroups();
                        break;
                    case 'one':
                        $this->getPurchaseGroup();
                        break;
                    case 'search':
                        $this->searchPurchaseGroups();
                        break;
                    case 'dropdown':
                        $this->getPurchaseGroupDropdown();
                        break;
                    case 'count':
                        $this->getTotalPurchaseGroups();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createPurchaseGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updatePurchaseGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deletePurchaseGroup();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getPurchaseGroups() {
        try {
            $data = $this->model->readPurchaseGroups();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch purchase groups'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getPurchaseGroups: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getPurchaseGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->getPurchaseGroupById($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Purchase group not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getPurchaseGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchPurchaseGroups() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchPurchaseGroups($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search purchase groups'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchPurchaseGroups: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getPurchaseGroupDropdown() {
        try {
            $data = $this->model->getPurchaseGroupDropdownData();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch purchase group dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getPurchaseGroupDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalPurchaseGroups() {
        try {
            $total = $this->model->getTotalPurchaseGroups();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalPurchaseGroups: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createPurchaseGroup() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['purchase_group_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists
        if ($this->model->checkPurchaseGroupNameExists($input['purchase_group_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Purchase group name already exists'], 409);
            return;
        }

        try {
            $result = $this->model->createPurchaseGroup($input);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'id' => $result['id'] ?? null, 'message' => 'Purchase group created successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create purchase group'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in createPurchaseGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updatePurchaseGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['purchase_group_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists (excluding current record)
        if ($this->model->checkPurchaseGroupNameExists($input['purchase_group_name'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Purchase group name already exists'], 409);
            return;
        }

        try {
            $result = $this->model->updatePurchaseGroup($id, $input);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'message' => 'Purchase group updated successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update purchase group'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in updatePurchaseGroup: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deletePurchaseGroup() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $result = $this->model->deletePurchaseGroup($id);
            if ($result && isset($result['status']) && $result['status'] === 'success') {
                $this->sendResponse(['status' => 'success', 'message' => 'Purchase group deleted successfully']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete purchase group'], 500);
            }
        } catch (Exception $e) {
            error_log("Error in deletePurchaseGroup: " . $e->getMessage());
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
$controller = new PurchaseGroupController();
$controller->processRequest();
