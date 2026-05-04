<?php

/**
 * Depart Controller - API สำหรับจัดการข้อมูลหน่วยงาน
 * Migration: Phase 2 - เพิ่ม API endpoints ครบถ้วน
 * วันที่: 2025-01-27
 */
// ini_set('log_errors', 1);
// $logFile = '../../logs/' . date('Y-m-d') . '.log';
// ini_set('// // error_log', $logFile);
// error_reporting(E_ALL);
// ini_set('display_errors', 0);
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/depart_model.php';

class DepartController
{
    private $departModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->departModel = new DepartModel($db);
    }

    /**
     * Process HTTP requests
     */
    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        // Handle GET requests
        if ($method === 'GET') {
            if ($action === 'get' && $id) {
                $this->getDepartment($id);
            } elseif ($action === 'list') {
                $this->listDepartments();
            } elseif ($action === 'types') {
                $this->getDepartTypes();
            } elseif ($action === 'hierarchy' && $id) {
                $this->getDepartmentHierarchy($id);
            } elseif ($action === 'users' && $id) {
                $this->getDepartmentUsers($id);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
        }
        // Handle POST requests (Create)
        elseif ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $this->createDepartment($data);
        }
        // Handle PUT requests (Update)
        elseif ($method === 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['id'])) {
                $this->updateDepartment($data['id'], $data);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            }
        }
        // Handle DELETE requests
        elseif ($method === 'DELETE') {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['id'])) {
                $this->deleteDepartment($data['id']);
            } else {
                $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            }
        } else {
            $this->sendResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }

    /**
     * List departments with filters and pagination
     */
    private function listDepartments()
    {
        // Get filters from query parameters
        $filters = [
            'depart_code' => isset($_GET['depart_code']) ? trim($_GET['depart_code']) : '',
            'depart_name' => isset($_GET['depart_name']) ? trim($_GET['depart_name']) : '',
            'depart_type_id' => isset($_GET['depart_type_id']) ? intval($_GET['depart_type_id']) : '',
            'is_active' => isset($_GET['is_active']) ? $_GET['is_active'] : ''
        ];

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== '' && $value !== null;
        });

        // Get pagination
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $pageSize = isset($_GET['page_size']) ? max(1, min(1000, intval($_GET['page_size']))) : 10;

        $result = $this->departModel->readDepart($filters, $page, $pageSize);

        if ($result === false) {
            $this->sendResponse(['status' => 'error', 'message' => 'Error loading departments'], 500);
            return;
        }

        $this->sendResponse([
            'status' => 'success',
            'data' => $result['data'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'page_size' => $result['page_size'],
                'total_pages' => $result['total_pages']
            ]
        ]);
    }

    /**
     * Get single department by ID
     */
    private function getDepartment($id)
    {
        if (!$id) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $result = $this->departModel->readDepartOne($id);

        if ($result === false) {
            $this->sendResponse(['status' => 'error', 'message' => 'Department not found'], 404);
            return;
        }

        $this->sendResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Get department types
     */
    private function getDepartTypes()
    {
        $query = "SELECT id, depart_type_name, depart_type_level, description 
                  FROM users.tb_depart_types 
                  WHERE is_active = true 
                  ORDER BY depart_type_level";

        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $result = pg_query($db, $query);

        if (!$result) {
            $this->sendResponse(['status' => 'error', 'message' => 'Error loading department types'], 500);
            return;
        }

        $types = [];
        while ($row = pg_fetch_assoc($result)) {
            $types[] = $row;
        }

        $this->sendResponse([
            'status' => 'success',
            'data' => $types
        ]);
    }

    /**
     * Get department hierarchy (parent + children)
     */
    private function getDepartmentHierarchy($id)
    {
        $hierarchy = $this->departModel->readDepartHierarchy($id);
        if ($hierarchy !== false) {
            $this->sendResponse(['status' => 'success', 'data' => $hierarchy]);
        } else {
            $this->sendResponse(['status' => 'error', 'message' => 'ไม่สามารถดึงข้อมูล hierarchy ได้'], 500);
        }
    }

    /**
     * Get users in department
     */
    private function getDepartmentUsers($id)
    {
        try {
            if (!$id) {
                $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
                return;
            }

            // Get department info first
            $department = $this->departModel->readDepartOne($id);
            if (!$department) {
                $this->sendResponse(['status' => 'error', 'message' => 'Department not found'], 404);
                return;
            }

            // Get users
            $users = $this->departModel->getUsersByDepart($id);
            if ($users === false) {
                $errorMsg = 'ไม่สามารถดึงข้อมูล users ได้';
                // // error_log("Error in getDepartmentUsers: Failed to get users for department ID: " . $id);

                $this->sendResponse([
                    'status' => 'error',
                    'message' => $errorMsg,
                    'debug' => 'Check server logs for details'
                ], 500);
                return;
            }

            $this->sendResponse([
                'status' => 'success',
                'data' => [
                    'department' => $department,
                    'users' => $users,
                    'count' => count($users)
                ]
            ]);
        } catch (Exception $e) {
            // // error_log("Exception in getDepartmentUsers: " . $e->getMessage());
            // // error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendResponse([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new department
     */
    private function createDepartment($data)
    {
        // Validate required fields
        if (empty($data['depart_code']) || empty($data['depart_name']) || empty($data['depart_type_id'])) {
            $this->sendResponse([
                'status' => 'error',
                'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน (รหัสหน่วยงาน, ชื่อหน่วยงาน, ประเภทหน่วยงาน)'
            ], 400);
            return;
        }

        $departCode = trim($data['depart_code']);
        $departName = trim($data['depart_name']);
        $departTypeId = intval($data['depart_type_id']);
        $parentId = isset($data['parent_id']) && $data['parent_id'] ? intval($data['parent_id']) : null;
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        $result = $this->departModel->createDepart($departCode, $departName, $departTypeId, $parentId, $isActive);

        if (isset($result['error'])) {
            $this->sendResponse([
                'status' => 'error',
                'message' => $result['error']
            ], 400);
            return;
        }

        if ($result === false) {
            $this->sendResponse([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการสร้างข้อมูล'
            ], 500);
            return;
        }

        $this->sendResponse([
            'status' => 'success',
            'message' => 'สร้างข้อมูลสำเร็จ',
            'data' => $result
        ], 201);
    }

    /**
     * Update department
     */
    private function updateDepartment($id, $data)
    {
        if (!$id) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        // Validate required fields
        if (empty($data['depart_code']) || empty($data['depart_name']) || empty($data['depart_type_id'])) {
            $this->sendResponse([
                'status' => 'error',
                'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน (รหัสหน่วยงาน, ชื่อหน่วยงาน, ประเภทหน่วยงาน)'
            ], 400);
            return;
        }

        $departCode = trim($data['depart_code']);
        $departName = trim($data['depart_name']);
        $departTypeId = intval($data['depart_type_id']);
        $parentId = isset($data['parent_id']) && $data['parent_id'] ? intval($data['parent_id']) : null;
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        $result = $this->departModel->updateDepart($id, $departCode, $departName, $departTypeId, $parentId, $isActive);

        if (isset($result['error'])) {
            $this->sendResponse([
                'status' => 'error',
                'message' => $result['error']
            ], 400);
            return;
        }

        if ($result === false) {
            $this->sendResponse([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล'
            ], 500);
            return;
        }

        $this->sendResponse([
            'status' => 'success',
            'message' => 'อัปเดตข้อมูลสำเร็จ',
            'data' => $result
        ]);
    }

    /**
     * Delete department
     */
    private function deleteDepartment($id)
    {
        if (!$id) {
            $this->sendResponse(['status' => 'error', 'message' => 'ID is required'], 400);
            return;
        }

        $result = $this->departModel->deleteDepart($id);

        if (isset($result['error'])) {
            $this->sendResponse([
                'status' => 'error',
                'message' => $result['error']
            ], 400);
            return;
        }

        if ($result === false) {
            $this->sendResponse([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'
            ], 500);
            return;
        }

        $this->sendResponse([
            'status' => 'success',
            'message' => 'ลบข้อมูลสำเร็จ'
        ]);
    }

    /**
     * Send JSON response
     */
    private function sendResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Initialize and process request
$controller = new DepartController();
$controller->processRequest();
