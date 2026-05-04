<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/funding_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class FundingController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new FundingModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getFundings();
                        break;
                    case 'one':
                        $this->getFunding();
                        break;
                    case 'search':
                        $this->searchFundings();
                        break;
                    case 'dropdown':
                        $this->getFundingDropdown();
                        break;
                    case 'count':
                        $this->getTotalFundings();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createFunding();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateFunding();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteFunding();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getFundings() {
        try {
            $data = $this->model->readFundings();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch fundings'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFundings: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFunding() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readFundingOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Funding not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFunding: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchFundings() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchFundings($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search fundings'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchFundings: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFundingDropdown() {
        try {
            $data = $this->model->getFundingDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch funding dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFundingDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalFundings() {
        try {
            $total = $this->model->getTotalFundings();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalFundings: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createFunding() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['funding_code']) || !isset($input['funding_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists
        if ($this->model->checkFundingCodeExists($input['funding_code'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Funding code already exists'], 409);
            return;
        }

        try {
            $id = $this->model->createFunding($input);
            if ($id === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create funding'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'id' => $id, 'message' => 'Funding created successfully']);
        } catch (Exception $e) {
            error_log("Error in createFunding: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateFunding() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['funding_code']) || !isset($input['funding_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if code already exists (excluding current record)
        if ($this->model->checkFundingCodeExists($input['funding_code'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Funding code already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateFunding($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update funding'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Funding updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateFunding: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteFunding() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteFunding($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete funding'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Funding deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteFunding: " . $e->getMessage());
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
$controller = new FundingController();
$controller->processRequest();
