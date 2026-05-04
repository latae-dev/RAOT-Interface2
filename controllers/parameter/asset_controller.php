<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/asset_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class AssetController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new AssetModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getAssets();
                        break;
                    case 'one':
                        $this->getAsset();
                        break;
                    case 'search':
                        $this->searchAssets();
                        break;
                    case 'dropdown':
                        $this->getAssetDropdown();
                        break;
                    case 'count':
                        $this->getTotalAssets();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createAsset();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateAsset();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteAsset();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getAssets() {
        try {
            $data = $this->model->readAssets();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch assets'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAssets: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getAsset() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readAssetOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Asset not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAsset: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchAssets() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchAssets($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search assets'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchAssets: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getAssetDropdown() {
        try {
            $data = $this->model->getAssetDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch asset dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getAssetDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalAssets() {
        try {
            $total = $this->model->getTotalAssets();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalAssets: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createAsset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['asset_code']) || !isset($input['asset_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkAssetCodeExists($input['asset_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Asset code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createAsset($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create asset'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Asset created successfully']);
        } catch (Exception $e) {
            error_log("Error in createAsset: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateAsset() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['asset_code']) || !isset($input['asset_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkAssetCodeExists($input['asset_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Asset code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateAsset($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update asset'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Asset updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateAsset: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteAsset() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteAsset($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete asset'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Asset deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteAsset: " . $e->getMessage());
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
$controller = new AssetController();
$controller->processRequest();

