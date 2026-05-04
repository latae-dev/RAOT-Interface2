<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/asset_category_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class AssetCategoryController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new AssetCategoryModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getAssetCategories();
                        break;
                    case 'one':
                        $this->getAssetCategory();
                        break;
                    case 'search':
                        $this->searchAssetCategories();
                        break;
                    case 'dropdown':
                        $this->getAssetCategoryDropdown();
                        break;
                    case 'count':
                        $this->getTotalAssetCategories();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createAssetCategory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateAssetCategory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteAssetCategory();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getAssetCategories() {
        try {
            $data = $this->model->readAssetCategories();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch asset categories'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAssetCategories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getAssetCategory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readAssetCategoryOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Asset category not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAssetCategory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchAssetCategories() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchAssetCategories($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search asset categories'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchAssetCategories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getAssetCategoryDropdown() {
        try {
            $data = $this->model->getAssetCategoryDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch asset category dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAssetCategoryDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalAssetCategories() {
        try {
            $total = $this->model->getTotalAssetCategories();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalAssetCategories: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createAssetCategory() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['code']) || !isset($input['name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkAssetCategoryCodeExists($input['code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Asset category code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createAssetCategory($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create asset category'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Asset category created successfully']);
        } catch (Exception $e) {
            error_log("Error in createAssetCategory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateAssetCategory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['code']) || !isset($input['name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkAssetCategoryCodeExists($input['code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Asset category code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateAssetCategory($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update asset category'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Asset category updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateAssetCategory: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteAssetCategory() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteAssetCategory($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete asset category'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Asset category deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteAssetCategory: " . $e->getMessage());
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
$controller = new AssetCategoryController();
$controller->processRequest();


