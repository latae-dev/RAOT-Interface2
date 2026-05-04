<?php
header('Content-Type: application/json; charset=utf-8');

// ตั้งค่า logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

// include database connection
require_once '../../configs/database.php';

// include model
require_once '../../models/proposal/request_proposal_model.php';

// include auth middleware (commented out for testing)
// include_once '../../configs/authMiddleware.php';

class RequestProposalController {
    private $model;
    private $database;

    public function __construct() {
        try {
            $this->database = new Database();
            $db = $this->database->connect('raot_db_qas');
            if (!$db) {
                throw new Exception("Database connection failed");
            }
            $this->model = new RequestProposalModel($db);
        } catch (Exception $e) {
            error_log("Controller: Constructor error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function processRequest()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
            $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($requestUri[2]) ? intval($requestUri[2]) : null);
            $data = json_decode(file_get_contents("php://input"), true);
            
            
            switch ($method) {
            case 'GET':
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'all':
                            $this->getProposals();
                            break;
                        case 'get':
                            if ($id) {
                                $this->getProposal($id);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'ID required']);
                            }
                            break;
                        case 'get_with_details':
                            if ($id) {
                                $this->getProposalWithDetails($id);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'ID required']);
                            }
                            break;
                        case 'get_with_details_all':
                            $this->getProposalsWithDetails();
                            break;
                        case 'stats':
                            $this->getProposalStats();
                            break;
                        case 'search':
                            $this->searchProposals();
                            break;
                        case 'get_approval_history':
                            if ($id) {
                                $this->getApprovalHistory($id);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'ID required']);
                            }
                            break;
                        default:
                            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
                            break;
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Action required']);
                }
                break;
                
            case 'POST':
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'create':
                            $this->createProposal($data);
                            break;
                        case 'update_status':
                            if ($id && isset($data['status'])) {
                                $this->updateStatus($id, $data['status'], $data['updated_by'] ?? null);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'ID and status required']);
                            }
                            break;
                        case 'update_sap_status':
                            if ($id && isset($data['sap_status'])) {
                                $this->updateSapStatus($id, $data['sap_status']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'ID and sap_status required']);
                            }
                            break;
                        case 'approve':
                            if (isset($data['proposal_id']) && isset($data['approval_status'])) {
                                $this->approveProposal($data);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Proposal ID and approval status required']);
                            }
                            break;
                        default:
                            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
                            break;
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Action required']);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    $this->updateProposal($id, $data);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'ID required']);
                }
                break;
                
            case 'DELETE':
                $deleteId = isset($_GET['id']) ? intval($_GET['id']) : $id;
                if ($deleteId) {
                    $this->deleteProposal($deleteId);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'ID required']);
                }
                break;
                
            default:
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
        } catch (Exception $e) {
            error_log("Controller: Exception in processRequest: " . $e->getMessage());
            error_log("Controller: Exception trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * สร้างคำขอใหม่
     */
    public function createProposal($data)
    {
        try {
            // ตรวจสอบข้อมูลที่จำเป็น
            $requiredFields = ['form_type_id', 'account_category', 'start_date', 'end_date', 'department_code'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    echo json_encode(['status' => 'error', 'message' => "Field {$field} is required"]);
                    return;
                }
            }
            
            $result = $this->model->createProposal($data);
            
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Proposal created successfully',
                    'data' => [
                        'proposal_id' => $result['proposal_id'],
                        'proposal_number' => $result['proposal_number']
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ดึงรายการคำขอทั้งหมด
     */
    public function getProposals()
    {
        try {
            $filters = [];
            
            // รับ filters จาก query parameters
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['sap_status'])) {
                $filters['sap_status'] = $_GET['sap_status'];
            }
            if (isset($_GET['account_category'])) {
                $filters['account_category'] = $_GET['account_category'];
            }
            if (isset($_GET['department_code'])) {
                $filters['department_code'] = $_GET['department_code'];
            }
            
            $proposals = $this->model->getProposals($filters);
            
            if ($proposals !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $proposals
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch proposals'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ดึงข้อมูลคำขอเดียว (แบบปลอดภัย)
     */
    public function getProposal($id)
    {
        try {
            // ตรวจสอบว่า id เป็นตัวเลข
            if (!is_numeric($id) || $id <= 0) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Invalid ID'
                ));
                return;
            }
            
            $proposal = $this->model->getProposal($id);
            
            if ($proposal) {
                echo json_encode(array(
                    'status' => 'success',
                    'data' => $proposal
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Proposal not found'
                ));
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * อัปเดตสถานะ
     */
    public function updateStatus($id, $status, $updatedBy = null)
    {
        try {
            $result = $this->model->updateStatus($id, $status, $updatedBy);
            
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * อัปเดต SAP status
     */
    public function updateSapStatus($id, $sapStatus)
    {
        try {
            $result = $this->model->updateSapStatus($id, $sapStatus);
            
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'SAP status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * อัปเดตคำขอ
     */
    public function updateProposal($id, $data)
    {
        try {
            $result = $this->model->updateProposal($id, $data);
            
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Proposal updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * อนุมัติคำขอ
     */
    public function approveProposal($data)
    {
        try {
            $proposalId = $data['proposal_id'];
            $approvalStatus = $data['approval_status']; // 'approved' หรือ 'rejected'
            $remark = $data['remark'] ?? '';
            $approvedBy = $data['approved_by'] ?? null;
            $approvedAt = $data['approved_at'] ?? null;
            
            // ดึงข้อมูลผู้อนุมัติ
            $approverName = $this->getUserName($approvedBy);
            
            // กำหนด approval level (สำหรับตอนนี้ใช้ level 1)
            $approvalLevel = 1;
            
            // บันทึกประวัติการอนุมัติในตาราง request_proposal_approvals
            $approvalResult = $this->model->addApprovalRecord(
                $proposalId,
                $approvalLevel,
                $approvedBy,
                $approverName,
                $approvalStatus,
                $remark
            );
            
            if (!$approvalResult['success']) {
                throw new Exception($approvalResult['error']);
            }
            
            // อัปเดตสถานะของ proposal
            $newStatus = ($approvalStatus === 'approved') ? 'approved' : 'rejected';
            $statusResult = $this->model->updateStatus($proposalId, $newStatus, $approvedBy);
            
            if (!$statusResult['success']) {
                throw new Exception($statusResult['error']);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Proposal ' . ($approvalStatus === 'approved' ? 'approved' : 'rejected') . ' successfully',
                'data' => [
                    'proposal_id' => $proposalId,
                    'approval_status' => $approvalStatus,
                    'approved_by' => $approvedBy,
                    'approver_name' => $approverName,
                    'approval_level' => $approvalLevel,
                    'remark' => $remark,
                    'approved_at' => $approvedAt
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ดึงชื่อผู้ใช้จากรหัสผู้ใช้
     */
    private function getUserName($userCode)
    {
        // สำหรับตอนนี้ return user code
        // ในอนาคตอาจจะดึงจากตาราง users หรือ API อื่น
        return $userCode;
    }
    
    /**
     * ดึงประวัติการอนุมัติ
     */
    public function getApprovalHistory($id)
    {
        try {
            $history = $this->model->getApprovalHistory($id);
            
            echo json_encode([
                'status' => 'success',
                'data' => $history
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ลบคำขอ
     */
    public function deleteProposal($id)
    {
        try {
            // รับข้อมูล updated_by จาก request body หรือ query parameter
            $data = json_decode(file_get_contents("php://input"), true);
            $updatedBy = $data['updated_by'] ?? $_GET['updated_by'] ?? null;
            
            $result = $this->model->deleteProposal($id, $updatedBy);
            
            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Proposal deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ดึงข้อมูลคำขอพร้อมรายละเอียด (แบบปลอดภัย)
     */
    public function getProposalWithDetails($id)
    {
        try {
            // ตรวจสอบว่า id เป็นตัวเลข
            if (!is_numeric($id) || $id <= 0) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Invalid ID'
                ));
                return;
            }
            
            $proposal = $this->model->getProposalWithDetails($id);
            
            if ($proposal) {
                echo json_encode(array(
                    'status' => 'success',
                    'data' => $proposal
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Proposal not found'
                ));
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * ดึงรายการคำขอพร้อมรายละเอียด (แบบง่าย)
     */
    public function getProposalsWithDetails()
    {
        try {
            $filters = array();
            
            // รับ filters จาก query parameters
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['sap_status']) && !empty($_GET['sap_status'])) {
                $filters['sap_status'] = $_GET['sap_status'];
            }
            if (isset($_GET['account_category']) && !empty($_GET['account_category'])) {
                $filters['account_category'] = $_GET['account_category'];
            }
            if (isset($_GET['department_code']) && !empty($_GET['department_code'])) {
                $filters['department_code'] = $_GET['department_code'];
            }
            if (isset($_GET['form_type_id']) && !empty($_GET['form_type_id'])) {
                $filters['form_type_id'] = $_GET['form_type_id'];
            }
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            
            // ตรวจสอบผู้ใช้ปัจจุบัน
            $currentUser = isset($_GET['current_user']) ? $_GET['current_user'] : '';
            if (!empty($currentUser)) {
                try {
                    $userInfo = $this->model->getUserInfo($currentUser);
                    if ($userInfo && isset($userInfo['position_id'])) {
                        $positionId = intval($userInfo['position_id']);
                        
                        // ถ้าเป็นผู้อนุมัติ (position_id = 11)
                        if ($positionId == 11) {
                            $proposals = $this->model->getProposalsForApprover($currentUser, $filters);
                        } else {
                            // ถ้าไม่ใช่ admin ให้แสดงเฉพาะของตัวเอง
                            if ($positionId != 3) {
                                $filters['created_by'] = $currentUser;
                            }
                            $proposals = $this->model->getProposalsWithDetails($filters);
                        }
                    } else {
                        // ถ้าไม่พบข้อมูลผู้ใช้ ให้แสดงข้อมูลทั้งหมด
                        $proposals = $this->model->getProposalsWithDetails($filters);
                    }
                } catch (Exception $e) {
                    // ถ้าเกิด error ในการดึงข้อมูลผู้ใช้ ให้แสดงข้อมูลทั้งหมด
                    $proposals = $this->model->getProposalsWithDetails($filters);
                }
            } else {
                $proposals = $this->model->getProposalsWithDetails($filters);
            }
            
            if ($proposals !== false) {
                echo json_encode(array(
                    'status' => 'success',
                    'data' => $proposals
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Failed to fetch proposals'
                ));
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * ดึงสถิติคำขอ
     */
    public function getProposalStats()
    {
        try {
            $stats = $this->model->getProposalStats();
            
            if ($stats !== false) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $stats
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to fetch stats'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ค้นหาคำขอ (แบบง่าย)
     */
    public function searchProposals()
    {
        try {
            $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
            $filters = array();
            
            // รับ filters จาก query parameters
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['sap_status']) && !empty($_GET['sap_status'])) {
                $filters['sap_status'] = $_GET['sap_status'];
            }
            if (isset($_GET['account_category']) && !empty($_GET['account_category'])) {
                $filters['account_category'] = $_GET['account_category'];
            }
            if (isset($_GET['form_type_id']) && !empty($_GET['form_type_id'])) {
                $filters['form_type_id'] = $_GET['form_type_id'];
            }
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            
            // ตรวจสอบผู้ใช้ปัจจุบัน
            $currentUser = isset($_GET['current_user']) ? $_GET['current_user'] : '';
            if (!empty($currentUser)) {
                try {
                    $userInfo = $this->model->getUserInfo($currentUser);
                    if ($userInfo && isset($userInfo['position_id'])) {
                        $positionId = intval($userInfo['position_id']);
                        
                        // ถ้าเป็นผู้อนุมัติ (position_id = 11)
                        if ($positionId == 11) {
                            $proposals = $this->model->searchProposalsForApprover($currentUser, $keyword, $filters);
                        } else {
                            // ถ้าไม่ใช่ admin ให้แสดงเฉพาะของตัวเอง
                            if ($positionId != 3) {
                                $filters['created_by'] = $currentUser;
                            }
                            $proposals = $this->model->searchProposals($keyword, $filters);
                        }
                    } else {
                        // ถ้าไม่พบข้อมูลผู้ใช้ ให้แสดงข้อมูลทั้งหมด
                        $proposals = $this->model->searchProposals($keyword, $filters);
                    }
                } catch (Exception $e) {
                    // ถ้าเกิด error ในการดึงข้อมูลผู้ใช้ ให้แสดงข้อมูลทั้งหมด
                    $proposals = $this->model->searchProposals($keyword, $filters);
                }
            } else {
                $proposals = $this->model->searchProposals($keyword, $filters);
            }
            
            if ($proposals !== false) {
                echo json_encode(array(
                    'status' => 'success',
                    'data' => $proposals
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Failed to search proposals'
                ));
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ));
        }
    }

    private function sendResponse($data, $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$controller = new RequestProposalController();
$controller->processRequest();


