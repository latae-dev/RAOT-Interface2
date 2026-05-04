<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/parameter/form_type_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class FormTypeController {
    private $model;
    private $database;

    public function __construct() {
        $this->database = new Database();
        $db = $this->database->connect('raot_db_qas');
        $this->model = new FormTypeModel($db);
    }

    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        $this->getFormTypes();
                        break;
                    case 'one':
                        $this->getFormType();
                        break;
                    case 'search':
                        $this->searchFormTypes();
                        break;
                    case 'dropdown':
                        $this->getFormTypeDropdown();
                        break;
                    case 'count':
                        $this->getTotalFormTypes();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'POST':
                switch ($action) {
                    case 'create':
                        $this->createFormType();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $this->updateFormType();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $this->deleteFormType();
                        break;
                    default:
                        $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
                }
                break;
            default:
                $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    private function getFormTypes() {
        try {
            $data = $this->model->readFormTypes();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch form types'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFormTypes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFormType() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $data = $this->model->readFormTypeOne($id);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Form type not found'], 404);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFormType: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function searchFormTypes() {
        $searchTerm = $_GET['q'] ?? '';
        if (empty($searchTerm)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Search term is required'], 400);
            return;
        }

        try {
            $data = $this->model->searchFormTypes($searchTerm);
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to search form types'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in searchFormTypes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getFormTypeDropdown() {
        try {
            $data = $this->model->getForDropdown();
            if ($data === false) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to fetch form type dropdown'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error in getFormTypeDropdown: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function getTotalFormTypes() {
        try {
            $total = $this->model->countAll();
            $this->sendResponse(['status' => 'success', 'total' => $total]);
        } catch (Exception $e) {
            error_log("Error in getTotalFormTypes: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function createFormType() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['form_type_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists
        if ($this->model->isNameExists($input['form_type_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Form type name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->createFormType($input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to create form type'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Form type created successfully']);
        } catch (Exception $e) {
            error_log("Error in createFormType: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function updateFormType() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['form_type_name'])) {
            $this->sendResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
            return;
        }

        // Check if name already exists (excluding current record)
        if ($this->model->isNameExists($input['form_type_name'], $id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'Form type name already exists'], 409);
            return;
        }

        try {
            $success = $this->model->updateFormType($id, $input);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to update form type'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Form type updated successfully']);
        } catch (Exception $e) {
            error_log("Error in updateFormType: " . $e->getMessage());
            $this->sendResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function deleteFormType() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        try {
            $success = $this->model->deleteFormType($id);
            if (!$success) {
                $this->sendResponse(['status' => 'error', 'message' => 'Failed to delete form type'], 500);
                return;
            }
            $this->sendResponse(['status' => 'success', 'message' => 'Form type deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in deleteFormType: " . $e->getMessage());
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
$controller = new FormTypeController();
$controller->processRequest();
