<?php

/**
 * Investment Budget Requests Controller (คอนโทรลเลอร์จัดการคำขอลงทุน)
 * หน้าที่:
 * - จัดการ API สำหรับคำขอลงทุนและรายการในคำขอ
 * - รองรับ RESTful API (GET, POST, PUT, DELETE)
 * - จัดการระบบ workflow และ approval
 * - ตรวจสอบสิทธิ์การเข้าถึงและเจ้าของข้อมูล
 * - ส่งคืนข้อมูลในรูปแบบ JSON

 */

// ตั้งค่าการบันทึก error logs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/investment_budget_' . date('Y-m-d') . '.log';
ini_set('// error_log', $logFile);

// เรียกใช้ middleware และ dependencies
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json; charset=utf-8");
include_once '../../configs/database.php';
include_once '../../models/investment_budget/ib_requests_model.php';
include_once '../../models/user/user_model.php';

class IbRequestsController
{
    /**
     * โมเดลสำหรับจัดการข้อมูลคำขอลงทุน
     */
    private $ibRequestsModel;
    private $userModel;

    private const APPROVAL_STAGES = ['branch', 'province', 'area', 'head_office'];

    private const VERSION_PLAN_START_STAGE = [
        's-1' => 0,
        's-2' => 1,
        's-3' => 2,
        's-4' => 3,
    ];

    /**
     * สร้างอินสแตนซ์ของคอนโทรลเลอร์
     */
    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->ibRequestsModel = new IbRequestsModel($db);
        $this->userModel = new UserModel($db);
    }
    private function hydrateSessionFromRequest(array $payload = null)
    {
        $contexts = [];

        if ($payload && isset($payload['user_context'])) {
            $contexts[] = [
                'value' => $payload['user_context'],
                'encoding' => $payload['user_context_encoding'] ?? null
            ];
        }

        if (isset($_POST['user_context'])) {
            $contexts[] = [
                'value' => $_POST['user_context'],
                'encoding' => $_POST['user_context_encoding'] ?? null
            ];
        }

        if (isset($_GET['user_context'])) {
            $contexts[] = [
                'value' => $_GET['user_context'],
                'encoding' => $_GET['user_context_encoding'] ?? null
            ];
        }

        if (isset($_SERVER['HTTP_X_USER_CONTEXT'])) {
            $contexts[] = [
                'value' => $_SERVER['HTTP_X_USER_CONTEXT'],
                'encoding' => $_SERVER['HTTP_X_USER_CONTEXT_ENCODING'] ?? null
            ];
        }

        foreach ($contexts as $contextEntry) {
            $rawContext = is_array($contextEntry) ? ($contextEntry['value'] ?? '') : $contextEntry;
            $encoding = is_array($contextEntry) ? ($contextEntry['encoding'] ?? null) : null;

            $decoded = $this->decodeUserContextString($rawContext, $encoding);
            if (!is_array($decoded)) {
                continue;
            }

            $normalized = $this->normalizeUserContext($decoded);
            if (empty($normalized['user_id'])) {
                continue;
            }

            foreach ($normalized as $key => $value) {
                $_SESSION[$key] = $value;
            }
            $_SESSION['is_authenticated'] = true;
            return true;
        }

        return false;
    }

    private function decodeUserContextString($rawContext, $encoding = null)
    {
        if (!is_string($rawContext) || $rawContext === '') {
            return null;
        }

        $candidates = [];

        if ($encoding === 'base64') {
            $decoded = base64_decode($rawContext, true);
            if ($decoded !== false) {
                $candidates[] = $decoded;
            }
        } elseif ($encoding === 'url') {
            $candidates[] = urldecode($rawContext);
        }

        $candidates[] = $rawContext;

        if ($encoding === null && preg_match('/^[A-Za-z0-9+\/=]+$/', $rawContext) && strlen($rawContext) % 4 === 0) {
            $decoded = base64_decode($rawContext, true);
            if ($decoded !== false) {
                $candidates[] = $decoded;
            }
        }

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }

            $decodedJson = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedJson)) {
                return $decodedJson;
            }
        }

        return null;
    }

    private function normalizeUserContext(array $context)
    {
        $map = [
            'user_id' => ['user_id', 'id'],
            'user_code' => ['user_code'],
            'user_fname' => ['user_fname'],
            'user_lname' => ['user_lname'],
            'branch_id' => ['branch_id', 'branch'],
            'province_id' => ['province_id'],
            'area_id' => ['area_id'],
            'head_office_id' => ['head_office_id'],
            'approve_branch' => ['approve_branch'],
            'approve_province' => ['approve_province'],
            'approve_area' => ['approve_area', 'approve_airea'],
            'approve_head_office' => ['approve_head_office'],
            'approve_investment' => ['approve_investment'],
            'position_id' => ['position_id'],
            'position_code' => ['position_code'],
            'depart_id' => ['depart_id'],
            'role_id' => ['role_id'],
            'branch_code' => ['branch_code'],
            'branch_name' => ['branch_name'],
            'province_code' => ['province_code'],
            'province_name' => ['province_name'],
            'area_code' => ['area_code'],
            'area_name' => ['area_name'],
            'head_office_code' => ['head_office_code'],
            'head_office_name' => ['head_office_name']
        ];

        $normalized = [];
        foreach ($map as $target => $sources) {
            foreach ($sources as $sourceKey) {
                if (isset($context[$sourceKey]) && $context[$sourceKey] !== '') {
                    $normalized[$target] = is_scalar($context[$sourceKey])
                        ? (string) $context[$sourceKey]
                        : $context[$sourceKey];
                    break;
                }
            }
        }

        return $normalized;
    }



    public function processRequest()
    {
        $this->hydrateSessionFromRequest();

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if (isset($_GET['action'])) {
                    if ($_GET['action'] == 'all') {
                        $this->getAllRequests();
                    } elseif ($_GET['action'] == 'one' && isset($_GET['id'])) {
                        $this->getRequestById($_GET['id']);
                    } elseif ($_GET['action'] == 'with_items' && isset($_GET['id'])) {
                        // รองรับ backward compatibility - ส่ง request เดียวกัน
                        $this->getRequestById($_GET['id']);
                    } elseif ($_GET['action'] == 'approvals') {
                        $this->getApprovalRequests();
                    } elseif ($_GET['action'] == 'my_requests') {
                        $this->getMyRequests();
                    } else {
                        $this->responseError('Invalid GET action', 400);
                    }
                } else {
                    $this->responseError('Action parameter required', 400);
                }
                break;

            case 'POST':
                if (isset($_GET['action'])) {
                    if ($_GET['action'] == 'save_form') {
                        $this->saveFormData();
                    } elseif ($_GET['action'] == 'update_form' && isset($_GET['id'])) {
                        $this->updateFormData($_GET['id']);
                    } elseif ($_GET['action'] == 'process_approval' && isset($_GET['id'])) {
                        $this->processApprovalAction($_GET['id']);
                    } elseif ($_GET['action'] == 'update_report_fields' && isset($_GET['id'])) {
                        $this->updateReportFields($_GET['id']);
                    } else {
                        $this->responseError('Invalid POST action', 400);
                    }
                } else {
                    $inputRaw = file_get_contents('php://input');
                    $input = json_decode($inputRaw, true);

                    if (!is_array($input) || !isset($input['action'])) {
                        $this->responseError('Invalid POST action', 400);
                        return;
                    }

                    if ($input['action'] === 'update_sap_status') {
                        $this->updateSapStatus($input);
                    } elseif ($input['action'] === 'save_sap_export') {
                        $this->saveSapExport($input);
                    } else {
                        $this->responseError('Invalid POST action in JSON body', 400);
                    }
                }
                break;

            case 'PUT':
                if (isset($_GET['id'])) {
                    if (isset($_GET['action']) && $_GET['action'] == 'status') {
                        $this->updateRequestStatus($_GET['id']);
                    } else {
                        $this->updateRequest($_GET['id']);
                    }
                } else {
                    $this->responseError('ID parameter required for PUT request', 400);
                }
                break;

            case 'DELETE':
                if (isset($_GET['id'])) {
                    $this->deleteRequest($_GET['id']);
                } else {
                    $this->responseError('ID parameter required for DELETE request', 400);
                }
                break;

            default:
                $this->responseError('Method not allowed', 405);
                break;
        }
    }

    private function getAllRequests()
    {
        $filters = [];

        // print_r($_SESSION);

        // Filter by current user (creator) - เฉพาะผู้สร้างเท่านั้น
        // Debug: Log session data
        // error_log('=== IB getAllRequests - Session Debug ===');
        // error_log('Session ID: ' . session_id());
        // error_log('Session Status: ' . session_status());

        if (empty($_SESSION)) {
            // error_log('ERROR: $_SESSION is empty!');
        } else {
            // error_log('Session Keys: ' . implode(', ', array_keys($_SESSION)));
            // error_log('user_id: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
            // error_log('is_authenticated: ' . ($_SESSION['is_authenticated'] ?? 'NOT SET'));
        }

        // ตรวจสอบ authentication
        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            // error_log('❌ ERROR: No user_id in session! Session dump:');
            // error_log(print_r($_SESSION, true));

            $this->responseError('Session expired. Please login again.', 401);
            return;
        }

        // พบ user_id
        $includeAll = isset($_GET['include_all']) && filter_var($_GET['include_all'], FILTER_VALIDATE_BOOLEAN);

        if (!$includeAll) {
            $filters['created_by'] = $user_id;
            // error_log('✅ Filtering by user_id: ' . $user_id);
        } else {
            // error_log('✅ include_all enabled - skipping created_by filter for user_id: ' . $user_id);
        }

        // Add filters from query parameters
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statusFilter = strtolower(trim($_GET['status']));
            if ($statusFilter !== 'all') {
                if ($statusFilter === 'submitted') {
                    $filters['status_prefix'] = 'pending_';
                } else {
                    $filters['status'] = $statusFilter;
                }
            }
        }
        if (!empty($_GET['fiscal_year_id'])) {
            $filters['fiscal_year_id'] = $_GET['fiscal_year_id'];
        }
        if (!empty($_GET['request_number'])) {
            $filters['request_number'] = trim($_GET['request_number']);
        }
        if (!empty($_GET['keyword'])) {
            $filters['keyword'] = trim($_GET['keyword']);
        }
        if (!empty($_GET['department_id'])) {
            $filters['department_id'] = $_GET['department_id'];
        }
        if (!empty($_GET['branch_id'])) {
            $filters['branch_id'] = $_GET['branch_id'];
        }
        if (!empty($_GET['created_by'])) {
            $filters['created_by'] = $_GET['created_by'];
        }
        if (!empty($_GET['budget_source_id'])) {
            $filters['budget_source_id'] = $_GET['budget_source_id'];
        }
        if (!empty($_GET['asset_type_id'])) {
            $filters['asset_type_id'] = $_GET['asset_type_id'];
        }

        // สำหรับรายงาน: ไม่รวม draft
        if (!empty($_GET['exclude_draft']) && $_GET['exclude_draft'] === 'true') {
            $filters['exclude_draft'] = true;
        }

        $startDate = $_GET['start_date'] ?? '';
        if (!empty($startDate) && $this->isValidDate($startDate)) {
            $filters['start_date'] = $startDate;
        }

        $endDate = $_GET['end_date'] ?? '';
        if (!empty($endDate) && $this->isValidDate($endDate)) {
            $filters['end_date'] = $endDate;
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $perPage = max(1, min($perPage, 100));

        $result = $this->ibRequestsModel->readRequests($filters, [
            'page' => $page,
            'per_page' => $perPage
        ]);

        if ($result !== false) {
            if (!empty($_GET['include_scope_annotations'])) {
                $context = $this->getCurrentUserContext();
                if ($context) {
                    $scope = $this->buildApprovalScope($context);
                    $result = $this->annotateApprovalPermissions($result, $scope, $context);
                }
            }

            $this->responseSuccess('Requests retrieved successfully', $result);
        } else {
            $this->responseError('Failed to retrieve requests', 500);
        }
    }

    private function getMyRequests()
    {
        // Get current user from session or token
        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            $this->responseError('User not authenticated 1', 401);
            return;
        }

        $filters = ['created_by' => $user_id];

        // Add additional filters
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $result = $this->ibRequestsModel->readRequests($filters);

        if ($result !== false) {
            $this->responseSuccess('My requests retrieved successfully', $result);
        } else {
            $this->responseError('Failed to retrieve my requests', 500);
        }
    }

    private function getRequestById($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $result = $this->ibRequestsModel->readRequest($id);

        if ($result !== false) {
            $context = $this->getCurrentUserContext();
            $scope = $this->buildApprovalScope($context ?? []);

            $evaluation = $this->evaluateApprovalScope($scope, $result);
            $currentStage = $evaluation['stage'] ?? $this->getStageFromStatus($result['status'] ?? '', $result);

            $result['current_stage'] = $currentStage;
            $result['current_stage_label'] = $currentStage ? $this->getStageLabel($currentStage) : null;
            $result['can_approve'] = $evaluation['can_approve'];
            $result['approval_scope_level'] = $evaluation['level'];
            $result['approval_scope_level_label'] = $evaluation['level'] ? $this->getStageLabel($evaluation['level']) : null;
            $result['approval_scope_reason'] = $evaluation['reason'];
            $result['next_stage_status'] = $this->getNextStageStatus($result);
            $result['next_stage_label'] = $result['next_stage_status'] ? $this->getStageLabel($this->getStageFromStatus($result['next_stage_status'])) : null;

            if (!empty($result['approvals_history']) && is_array($result['approvals_history'])) {
                foreach ($result['approvals_history'] as &$hist) {
                    $hist['stage_label'] = $hist['stage_label'] ?? $this->getStageLabel($hist['stage'] ?? null);
                    $hist['action_label'] = $hist['action_label'] ?? $this->formatApprovalActionLabel($hist['action'] ?? null);
                    $hist['approved_at_formatted'] = $this->formatDateString($hist['approved_at'] ?? null);
                }
            }

            $this->responseSuccess('Request retrieved successfully', $result);
        } else {
            $this->responseError('Request not found', 404);
        }
    }


    private function createRequest()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $this->hydrateSessionFromRequest($data);

        if (!$data) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        // Validate required fields
        $required_fields = ['fiscal_year_id', 'version_plan_id', 'asset_type_id'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->responseError("Missing required field: $field", 400);
                return;
            }
        }

        // Add current user as creator
        $data['created_by'] = $_SESSION['user_id'] ?? '1'; // Default to user 1 for testing

        $result = $this->ibRequestsModel->createRequest($data);

        if ($result !== false) {
            $this->responseSuccess('Request created successfully', $result);
        } else {
            $this->responseError('Failed to create request', 500);
        }
    }

    private function getApprovalRequests()
    {
        $context = $this->getCurrentUserContext();
        if (!$context) {
            $this->responseError('User not authenticated 2', 401);
            return;
        }

        $scope = $this->buildApprovalScope($context);

        // Debug: Log approval scope
        // error_log('🔍 User ' . ($context['user_id'] ?? 'unknown') . ' approval context:');
        // error_log('  - branch_id: ' . ($context['branch_id'] ?? 'null'));
        // error_log('  - province_id: ' . ($context['province_id'] ?? 'null'));
        // error_log('  - area_id: ' . ($context['area_id'] ?? 'null'));
        // error_log('  - head_office_id: ' . ($context['head_office_id'] ?? 'null'));
        // error_log('  - approve_branch: ' . ($context['approve_branch'] ?? 'null'));
        // error_log('  - approve_province: ' . ($context['approve_province'] ?? 'null'));
        // error_log('  - approve_area: ' . ($context['approve_area'] ?? 'null'));
        // error_log('  - approve_head_office: ' . ($context['approve_head_office'] ?? 'null'));
        // error_log('  - approve_investment: ' . ($context['approve_investment'] ?? 'null'));
        // error_log('📊 Built approval scope: ' . json_encode($scope));

        $filters = [];

        $status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
        if ($status === '' || $status === null || $status === 'submitted') {
            $filters['status_prefix'] = 'pending_';
        } elseif ($status !== 'all') {
            $filters['status'] = $status;
        }

        if (!empty($_GET['request_number'])) {
            $filters['request_number'] = trim($_GET['request_number']);
        }

        $startDate = $_GET['start_date'] ?? '';
        if (!empty($startDate) && $this->isValidDate($startDate)) {
            $filters['start_date'] = $startDate;
        }

        $endDate = $_GET['end_date'] ?? '';
        if (!empty($endDate) && $this->isValidDate($endDate)) {
            $filters['end_date'] = $endDate;
        }

        if ($scope !== null) {
            $filters['approval_scope'] = $scope;
        }

        $filters['include_scope_annotations'] = true;
        $filters['current_user_id'] = $context['user_id'];

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $perPage = max(1, min($perPage, 100));

        $result = $this->ibRequestsModel->readRequests($filters, [
            'page' => $page,
            'per_page' => $perPage
        ]);

        if ($result !== false) {
            if (!empty($_GET['include_scope_annotations'])) {
                $context = $this->getCurrentUserContext();
                if ($context) {
                    $scope = $this->buildApprovalScope($context);
                    $result = $this->annotateApprovalPermissions($result, $scope, $context);
                }
            }

            $this->responseSuccess('Requests retrieved successfully', $result);
        } else {
            $this->responseError('Failed to retrieve requests', 500);
        }
    }

    private function getCurrentUserContext()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $context = [
            'user_id' => $_SESSION['user_id'],
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'province_id' => $_SESSION['province_id'] ?? null,
            'area_id' => $_SESSION['area_id'] ?? null,
            'head_office_id' => $_SESSION['head_office_id'] ?? null,
            'approve_branch' => $_SESSION['approve_branch'] ?? null,
            'approve_province' => $_SESSION['approve_province'] ?? null,
            'approve_area' => $_SESSION['approve_area'] ?? ($_SESSION['approve_airea'] ?? null),
            'approve_head_office' => $_SESSION['approve_head_office'] ?? null,
            'approve_investment' => $_SESSION['approve_investment'] ?? null
        ];

        $needsRefresh = false;
        foreach (['branch_id', 'province_id', 'area_id', 'head_office_id', 'approve_branch', 'approve_province', 'approve_area', 'approve_head_office', 'approve_investment'] as $key) {
            if (!isset($context[$key]) || $context[$key] === null || $context[$key] === '') {
                $needsRefresh = true;
                break;
            }
        }

        if ($needsRefresh) {
            $user = $this->userModel->readUserOne($context['user_id']);
            if ($user) {
                $context['branch_id'] = $user['branch_id'] ?? $context['branch_id'];
                $context['province_id'] = $user['province_id'] ?? $context['province_id'];
                $context['area_id'] = $user['area_id'] ?? $context['area_id'];
                $context['head_office_id'] = $user['head_office_id'] ?? $context['head_office_id'];
                $context['approve_branch'] = $user['approve_branch'] ?? $context['approve_branch'];
                $context['approve_province'] = $user['approve_province'] ?? $context['approve_province'];
                $context['approve_area'] = $user['approve_airea'] ?? $context['approve_area'];
                $context['approve_head_office'] = $user['approve_head_office'] ?? $context['approve_head_office'];
                $context['approve_investment'] = $user['approve_investment'] ?? $context['approve_investment'];

                $_SESSION['branch_id'] = $context['branch_id'];
                $_SESSION['province_id'] = $context['province_id'];
                $_SESSION['area_id'] = $context['area_id'];
                $_SESSION['head_office_id'] = $context['head_office_id'];
                $_SESSION['approve_branch'] = $context['approve_branch'];
                $_SESSION['approve_province'] = $context['approve_province'];
                $_SESSION['approve_area'] = $context['approve_area'];
                $_SESSION['approve_head_office'] = $context['approve_head_office'];
                $_SESSION['approve_investment'] = $context['approve_investment'];
            }
        }

        return $context;
    }

    private function buildApprovalScope(array $context)
    {
        $scope = [
            'branch_ids' => [],
            'province_ids' => [],
            'area_ids' => [],
            'head_office_ids' => [],
        ];

        if ($this->isTruthy($context['approve_branch'] ?? null)) {
            $value = $this->normalizeScopeValue($context['branch_id'] ?? null);
            if ($value !== null && $value !== '') {
                $scope['branch_ids'][] = $value;
            } else {
                $scope['branch_ids'][] = '*';
            }
        }

        if ($this->isTruthy($context['approve_province'] ?? null)) {
            $value = $this->normalizeScopeValue($context['province_id'] ?? null);
            if ($value !== null && $value !== '') {
                $scope['province_ids'][] = $value;
            } else {
                $scope['province_ids'][] = '*';
            }
        }

        if ($this->isTruthy($context['approve_area'] ?? null)) {
            $value = $this->normalizeScopeValue($context['area_id'] ?? null);
            if ($value !== null && $value !== '') {
                $scope['area_ids'][] = $value;
            } else {
                $scope['area_ids'][] = '*';
            }
        }

        if ($this->isTruthy($context['approve_head_office'] ?? null) && $this->isTruthy($context['approve_investment'] ?? null)) {
            $value = $this->normalizeScopeValue($context['head_office_id'] ?? null);
            if ($value !== null && $value !== '') {
                $scope['head_office_ids'][] = $value;
            } else {
                $scope['head_office_ids'][] = '*';
            }
        }

        $hasScope = false;
        foreach ($scope as $values) {
            if (!empty($values)) {
                $hasScope = true;
                break;
            }
        }

        return $hasScope ? $scope : null;
    }

    private function isTruthy($value)
    {
        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return false;
        }

        $truthy = ['y', 'yes', '1', 'true', 'active', 'enable', 'enabled', 'on'];
        $falsy = ['n', 'no', '0', 'false', 'inactive', 'nonactive', 'noactive', 'disabled', 'off'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        return (bool) $normalized;
    }

    private function normalizeScopeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        if (is_numeric($string)) {
            if (ctype_digit($string)) {
                return (string) ((int) $string);
            }

            return (string) ((float) $string);
        }

        return $string;
    }

    private function annotateApprovalPermissions($payload, ?array $scope, array $context)
    {
        $hasMeta = is_array($payload) && array_key_exists('items', $payload) && array_key_exists('meta', $payload);
        $items = $hasMeta ? ($payload['items'] ?? []) : (is_array($payload) ? $payload : []);

        $annotatedItems = [];
        foreach ($items as $item) {
            $evaluation = $this->evaluateApprovalScope($scope, $item);
            $currentStage = $evaluation['stage'] ?? $this->getStageFromStatus($item['status'] ?? '', $item);
            $currentStageLabel = $evaluation['stage_label'] ?? ($currentStage ? $this->getStageLabel($currentStage) : null);

            $item['current_stage'] = $currentStage;
            $item['current_stage_label'] = $currentStageLabel;
            $item['can_approve'] = $evaluation['can_approve'];
            $item['approval_scope_level'] = $evaluation['level'];
            $item['approval_scope_level_label'] = $evaluation['level'] ? $this->getStageLabel($evaluation['level']) : null;
            $item['approval_scope_reason'] = $evaluation['reason'];
            $annotatedItems[] = $item;
        }

        if ($hasMeta) {
            $payload['items'] = $annotatedItems;
            return $payload;
        }

        return $annotatedItems;
    }

    private function evaluateApprovalScope(?array $scope, array $request)
    {
        $status = strtolower($request['status'] ?? '');
        $stage = $this->getStageFromStatus($status, $request);

        if (!$stage) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'not_pending',
                'stage' => null,
                'stage_label' => null,
            ];
        }

        if (!$scope) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'no_scope',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        $stageKeyMap = [
            'branch' => ['branch_ids', 'branch_id'],
            'province' => ['province_ids', 'province_id'],
            'area' => ['area_ids', 'area_id'],
            'head_office' => ['head_office_ids', 'head_office_id'],
        ];

        if (!isset($stageKeyMap[$stage])) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'unknown_stage',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        [$idsKey, $fieldKey] = $stageKeyMap[$stage];
        $ids = array_values(array_filter(array_map(function ($value) {
            return $this->normalizeScopeValue($value);
        }, (array) ($scope[$idsKey] ?? [])), static function ($value) {
            return $value !== null && $value !== '';
        }));

        if (empty($ids)) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'no_scope_stage',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        $value = $this->normalizeScopeValue($request[$fieldKey] ?? null);
        if (in_array('*', $ids, true)) {
            return [
                'can_approve' => true,
                'level' => $stage,
                'reason' => 'wildcard',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        if ($value !== null && $value !== '' && in_array($value, $ids, true)) {
            return [
                'can_approve' => true,
                'level' => $stage,
                'reason' => 'match',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        return [
            'can_approve' => false,
            'level' => null,
            'reason' => 'no_match',
            'stage' => $stage,
            'stage_label' => $this->getStageLabel($stage),
        ];
    }

    private function determineInitialStageStatus(array $requestData)
    {
        $versionPlanCode = $this->resolveVersionPlanCode($requestData);
        $flow = $this->getApprovalFlow($versionPlanCode);

        if (empty($flow)) {
            $flow = ['head_office'];
        }

        $stage = $flow[0];

        return $this->makeStageStatus($stage);
    }

    private function resolveVersionPlanCode(array $data)
    {
        if (!empty($data['version_plan_code'])) {
            return strtolower($data['version_plan_code']);
        }

        if (!empty($data['version_plan_id'])) {
            $rawId = $data['version_plan_id'];

            if (!is_numeric($rawId)) {
                return strtolower((string) $rawId);
            }

            return strtolower((string) ($this->ibRequestsModel->getVersionPlanCodeById($rawId) ?? ''));
        }

        return null;
    }

    private function getApprovalFlow(?string $versionPlanCode)
    {
        $base = self::APPROVAL_STAGES;
        $code = strtolower($versionPlanCode ?? '');
        $startIndex = self::VERSION_PLAN_START_STAGE[$code] ?? 0;

        if ($startIndex >= count($base)) {
            $last = $base[count($base) - 1] ?? 'head_office';
            return [$last];
        }

        return array_slice($base, $startIndex);
    }

    private function makeStageStatus(string $stage)
    {
        return 'pending_' . $stage;
    }

    private function isStageStatus(?string $status)
    {
        if ($status === null) {
            return false;
        }

        return strpos(strtolower($status), 'pending_') === 0;
    }

    private function getStageFromStatus(?string $status)
    {
        if (!$this->isStageStatus($status)) {
            return null;
        }

        return substr(strtolower($status), strlen('pending_')) ?: null;
    }

    private function getNextStageStatus(array $request)
    {
        $currentStatus = strtolower($request['status'] ?? '');
        $currentStage = $this->getStageFromStatus($currentStatus, $request);

        if (!$currentStage) {
            return null;
        }

        $versionPlanCode = $this->resolveVersionPlanCode($request);
        $flow = $this->getApprovalFlow($versionPlanCode);
        $index = array_search($currentStage, $flow, true);

        if ($index === false) {
            return null;
        }

        if ($index >= count($flow) - 1) {
            return null;
        }

        return $this->makeStageStatus($flow[$index + 1]);
    }

    private function getStageLabel(?string $stage)
    {
        switch ($stage) {
            case 'branch':
                return 'ระดับสาขา';
            case 'province':
                return 'ระดับจังหวัด/กอง';
            case 'area':
                return 'ระดับเขต/ฝ่าย';
            case 'head_office':
                return 'ระดับสำนักงานใหญ่';
            default:
                return null;
        }
    }

    private function formatDateString($dateString)
    {
        if (!$dateString) {
            return null;
        }

        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return $dateString;
        }

        $thaiYear = (int) date('Y', $timestamp) + 543;
        $formatted = date('d/m', $timestamp) . '/' . $thaiYear . ' ' . date('H:i', $timestamp);
        return $formatted;
    }

    private function formatApprovalActionLabel(?string $action)
    {
        if (!$action) {
            return null;
        }

        $actionLower = strtolower($action);
        switch ($actionLower) {
            case 'approve':
            case 'approved':
                return 'อนุมัติ';
            case 'reject':
            case 'rejected':
                return 'ไม่อนุมัติ';
            default:
                return $action;
        }
    }

    private function updateRequest($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        // Add current user as updater
        $data['updated_by'] = $_SESSION['user_id'] ?? '1'; // Default to user 1 for testing

        $result = $this->ibRequestsModel->updateRequest($id, $data);

        if ($result) {
            $this->responseSuccess('Request updated successfully', null);
        } else {
            $this->responseError('Failed to update request or request not found', 404);
        }
    }

    private function updateRequestStatus($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['status'])) {
            $this->responseError('Status is required', 400);
            return;
        }

        $statusInput = strtolower(trim($data['status']));
        $updated_by = $_SESSION['user_id'] ?? '1';

        $request = $this->ibRequestsModel->readRequest($id);

        if (!$request) {
            $this->responseError('Request not found', 404);
            return;
        }

        $result = false;

        if (in_array($statusInput, ['approved', 'approve'], true)) {
            $nextStatus = $this->getNextStageStatus($request);

            if ($nextStatus) {
                $result = $this->ibRequestsModel->updateStatus($id, $nextStatus, $updated_by);
            } else {
                $result = $this->ibRequestsModel->updateStatus($id, 'approved', $updated_by);
            }
        } elseif (in_array($statusInput, ['rejected', 'reject'], true)) {
            $result = $this->ibRequestsModel->updateStatus($id, 'rejected', $updated_by);
        } elseif ($this->isStageStatus($statusInput) || in_array($statusInput, ['draft', 'submitted'], true)) {
            $targetStatus = $this->isStageStatus($statusInput)
                ? $statusInput
                : ($statusInput === 'submitted' ? $this->determineInitialStageStatus($request) : $statusInput);

            $result = $this->ibRequestsModel->updateStatus($id, $targetStatus, $updated_by);
        } else {
            $this->responseError('Invalid status value', 400);
            return;
        }

        if ($result) {
            $this->responseSuccess('Request status updated successfully', null);
        } else {
            $this->responseError('Failed to update request status or request not found', 404);
        }
    }

    private function processApprovalAction($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $this->hydrateSessionFromRequest($payload);
        if (!is_array($payload)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        $action = strtolower(trim($payload['action'] ?? ''));
        $comments = trim($payload['comments'] ?? '');

        if (!in_array($action, ['approve', 'approved', 'reject', 'rejected'], true)) {
            $this->responseError('Invalid approval action', 400);
            return;
        }

        if ($action === 'reject' || $action === 'rejected') {
            if ($comments === '') {
                $this->responseError('กรุณากรอกหมายเหตุเมื่อปฏิเสธคำขอ', 400);
                return;
            }
        }

        $request = $this->ibRequestsModel->readRequest($id);

        if (!$request) {
            $this->responseError('Request not found', 404);
            return;
        }

        $context = $this->getCurrentUserContext();
        if (!$context) {
            $this->responseError('User not authenticated 3', 401);
            return;
        }

        $scope = $this->buildApprovalScope($context);
        $evaluation = $this->evaluateApprovalScope($scope, $request);

        if (!$evaluation['stage']) {
            $this->responseError('คำขอนี้ไม่อยู่ในขั้นตอนที่ต้องอนุมัติ', 400);
            return;
        }

        if (!$evaluation['can_approve']) {
            $this->responseError('คุณไม่มีสิทธิ์อนุมัติคำขอนี้', 403);
            return;
        }

        $currentStage = $evaluation['stage'];
        $updated_by = $_SESSION['user_id'] ?? '1';
        $newStatus = null;

        if (in_array($action, ['approve', 'approved'], true)) {
            $nextStatus = $this->getNextStageStatus($request);
            if ($nextStatus) {
                $newStatus = $nextStatus;
            } else {
                $newStatus = 'approved';
            }
        } else {
            $newStatus = 'rejected';
        }

        $updated = $this->ibRequestsModel->updateStatus($id, $newStatus, $updated_by);

        if (!$updated) {
            $this->responseError('ไม่สามารถอัปเดตสถานะได้', 500);
            return;
        }

        $this->ibRequestsModel->addApprovalHistory(
            $id,
            $currentStage,
            $action,
            $comments,
            $updated_by
        );

        $updatedRequest = $this->ibRequestsModel->readRequest($id);

        if ($updatedRequest) {
            $updatedRequest['approvals_history'] = $this->ibRequestsModel->getApprovalHistory($id);
            $evaluationAfter = $this->evaluateApprovalScope($scope, $updatedRequest);
            $updatedRequest['current_stage'] = $evaluationAfter['stage'] ?? $this->getStageFromStatus($updatedRequest['status'], $updatedRequest);
            $updatedRequest['current_stage_label'] = $evaluationAfter['stage_label'] ?? $this->getStageLabel($updatedRequest['current_stage']);
            $updatedRequest['can_approve'] = $evaluationAfter['can_approve'];
            $updatedRequest['approval_scope_level'] = $evaluationAfter['level'];
            $updatedRequest['approval_scope_level_label'] = $evaluationAfter['level'] ? $this->getStageLabel($evaluationAfter['level']) : null;
            $updatedRequest['approval_scope_reason'] = $evaluationAfter['reason'];
            $updatedRequest['next_stage_status'] = $this->getNextStageStatus($updatedRequest);
            $updatedRequest['next_stage_label'] = $updatedRequest['next_stage_status'] ? $this->getStageLabel($this->getStageFromStatus($updatedRequest['next_stage_status'], $updatedRequest)) : null;

            if (!empty($updatedRequest['approvals_history'])) {
                foreach ($updatedRequest['approvals_history'] as &$hist) {
                    $hist['stage_label'] = $hist['stage_label'] ?? $this->getStageLabel($hist['stage'] ?? null);
                    $hist['action_label'] = $hist['action_label'] ?? $this->formatApprovalActionLabel($hist['action'] ?? null);
                    $hist['approved_at_formatted'] = $this->formatDateString($hist['approved_at'] ?? null);
                }
            }
        }

        $this->responseSuccess('ดำเนินการพิจารณาอนุมัติเรียบร้อย', $updatedRequest);
    }

    private function updateReportFields($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $this->hydrateSessionFromRequest($payload);
        if (!is_array($payload)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        $fields = [];
        $allowed = ['equipment_code_type_id', 'equipment_type_id', 'count_unit_id', 'account_code'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $value = $payload[$field];
                if (is_string($value) && strtolower(trim($value)) === 'null') {
                    $value = null;
                }
                if ($value === '' || $value === null) {
                    $fields[$field] = null;
                } else {
                    if ($field === 'account_code') {
                        $fields[$field] = trim((string) $value);
                    } else {
                        $fields[$field] = (int) $value;
                    }
                }
            }
        }

        if (empty($fields)) {
            $this->responseError('No fields to update', 400);
            return;
        }

        $context = $this->getCurrentUserContext();
        if (!$context) {
            $this->responseError('User not authenticated 4', 401);
            return;
        }

        if (!$this->ibRequestsModel->hasReportFieldColumns()) {
            $this->responseError('ระบบยังไม่รองรับการบันทึกข้อมูลเพิ่มเติม กรุณาอัปเดตฐานข้อมูล (add_report_fields_to_ib_requests.sql)', 500);
            return;
        }

        $updated = $this->ibRequestsModel->updateReportFields((int) $id, $fields, $context['user_id'] ?? null);

        if (!$updated) {
            $this->responseError('Failed to update report fields', 500);
            return;
        }

        $record = $this->ibRequestsModel->readRequest($id);
        $this->responseSuccess('Report fields updated successfully', $record);
    }

    private function updateSapStatus($payload)
    {
        if (!is_array($payload)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        if (!isset($payload['request_ids']) || !is_array($payload['request_ids'])) {
            $this->responseError('request_ids array is required', 400);
            return;
        }

        $requestIds = $payload['request_ids'];
        $status = $payload['status'] ?? 'success';

        // Validate status
        if (!in_array($status, ['success', 'not_synced'])) {
            $this->responseError('Invalid status. Must be "success" or "not_synced"', 400);
            return;
        }

        // Validate all IDs are numeric
        foreach ($requestIds as $id) {
            if (!is_numeric($id)) {
                $this->responseError('All request IDs must be numeric', 400);
                return;
            }
        }

        $updated = $this->ibRequestsModel->updateSapStatus($requestIds, $status);

        if (!$updated) {
            $this->responseError('Failed to update SAP status', 500);
            return;
        }

        $this->responseSuccess('SAP status updated successfully', [
            'updated_count' => count($requestIds),
            'status' => $status
        ]);
    }

    private function saveSapExport($payload)
    {
        if (!is_array($payload)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        if (!isset($payload['content']) || !isset($payload['filename'])) {
            $this->responseError('content and filename are required', 400);
            return;
        }

        $content = $payload['content'];
        $filename = $payload['filename'];

        // สร้าง directory path
        $baseDir = __DIR__ . '/../../storage/investment_budget/sap';

        // สร้าง folder ถ้ายังไม่มี
        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0755, true)) {
                $this->responseError('Failed to create directory', 500);
                return;
            }
        }

        // Full path ของไฟล์
        $filepath = $baseDir . '/' . $filename;

        // Log path for debugging
        // error_log("Attempting to save SAP file to: " . $filepath);
        // error_log("Directory exists: " . (is_dir($baseDir) ? 'yes' : 'no'));
        // error_log("Directory writable: " . (is_writable($baseDir) ? 'yes' : 'no'));

        // บันทึกไฟล์
        $result = @file_put_contents($filepath, $content);

        if ($result === false) {
            $lastError = error_get_last();
            $errorMsg = $lastError ? $lastError['message'] : 'Unknown error';
            // error_log("Failed to save SAP file: " . $errorMsg);
            $this->responseError('Failed to save file: ' . $errorMsg, 500);
            return;
        }

        // ส่ง relative path กลับไปแสดงให้ user เห็น
        $relativePath = 'files/investment_budget/sap/' . $filename;

        $this->responseSuccess('File saved successfully', [
            'filepath' => $relativePath,
            'filename' => $filename,
            'size' => $result
        ]);
    }

    private function deleteRequest($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $updated_by = $_SESSION['user_id'] ?? '1'; // Default to user 1 for testing
        $result = $this->ibRequestsModel->deleteRequest($id, $updated_by);

        if ($result) {
            $this->responseSuccess('Request deleted successfully', null);
        } else {
            $this->responseError('Failed to delete request or request not found', 404);
        }
    }

    private function responseSuccess($message, $data = null)
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $this->sendJson($response, 200);
    }

    /**
     * บันทึกข้อมูลจากฟอร์มงบลงทุน (รองรับทั้ง draft และ submit)
     */
    private function saveFormData()
    {
        $formData = $this->getRequestPayload();

        if (!is_array($formData)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        $uploadedFiles = $this->getUploadedPdfFiles();
        $validationError = $this->validatePdfUploads($uploadedFiles);
        if ($validationError) {
            $this->responseError($validationError, 400);
            return;
        }

        try {
            // error_log("📋 Form Data รับมา: " . json_encode($formData));
            // error_log("🔢 Serial Numbers: " . json_encode($formData['serialNumbers'] ?? 'ไม่มี'));
            // error_log("📅 Expected Disbursement Date: " . ($formData['expectedDisbursementDate'] ?? 'ไม่มี'));
            // error_log("📋 Project Program: " . ($formData['projectProgram'] ?? 'ไม่มี'));
            // error_log("🔄 Multi Year Disbursement: " . ($formData['multiYearDisbursement'] ?? 'ไม่มี'));

            $status = $formData['status'] ?? 'draft';
            // error_log("📋 Status: " . $status);

            $isSubmittingForApproval = ($status === 'pending_approval');

            if ($isSubmittingForApproval) {
                $validation = $this->validateForSubmission($formData);

                if (!$validation['isValid']) {
                    $this->responseError('ข้อมูลไม่ครบถ้วน: ' . implode(', ', $validation['errors']), 400);
                    return;
                }
            }

            $requestData = $this->transformFormData($formData);

            if ($isSubmittingForApproval) {
                $status = $this->determineInitialStageStatus($requestData);
            }

            $requestData['status'] = $status;

            // error_log("📋 Request Data ที่จะบันทึก: " . json_encode($requestData));

            if (!$this->ibRequestsModel) {
                throw new Exception("ibRequestsModel ไม่พร้อมใช้งาน");
            }

            $result = $this->ibRequestsModel->createRequest($requestData);
            // error_log("📋 ผลลัพธ์จาก createRequest: " . json_encode($result));

            if ($result !== false) {
                $attachments = [];
                try {
                    $attachments = $this->handleAttachments($result['id'], $result['request_number'], $uploadedFiles);
                } catch (Exception $attachmentException) {
                    // error_log("❌ Attachment handling error: " . $attachmentException->getMessage());
                    $attachments = $this->ibRequestsModel->getAttachmentFilePaths($result['id']);
                }

                if ($this->isStageStatus($status)) {
                    $this->ibRequestsModel->updateStatus($result['id'], $status, $requestData['created_by'] ?? ($requestData['updated_by'] ?? '1'));
                }

                $responseData = $this->ibRequestsModel->readRequest($result['id']);
                if ($responseData) {
                    $responseData['attachments'] = $attachments;
                } else {
                    $responseData = [
                        'id' => $result['id'],
                        'request_number' => $result['request_number'],
                        'attachments' => $attachments
                    ];
                }

                $message = ($status === 'draft') ? 'บันทึกแบบร่างสำเร็จ' : 'ส่งเพื่อขออนุมัติสำเร็จ';
                $this->responseSuccess($message, $responseData);
            } else {
                $this->responseError('ไม่สามารถบันทึกได้ - createRequest คืนค่า false', 500);
            }
        } catch (Exception $e) {
            // error_log("❌ เกิดข้อผิดพลาดในการบันทึกฟอร์ม: " . $e->getMessage());
            // error_log("❌ Stack trace: " . $e->getTraceAsString());
            $this->responseError('เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage(), 500);
        }
    }

    /**
     * อัพเดตข้อมูลจากฟอร์มงบลงทุน (สำหรับหน้า edit)
     */
    private function updateFormData($id)
    {
        if (!is_numeric($id)) {
            $this->responseError('Invalid ID format', 400);
            return;
        }

        $formData = $this->getRequestPayload();

        if (!is_array($formData)) {
            $this->responseError('Invalid JSON data', 400);
            return;
        }

        $uploadedFiles = $this->getUploadedPdfFiles();
        $validationError = $this->validatePdfUploads($uploadedFiles);
        if ($validationError) {
            $this->responseError($validationError, 400);
            return;
        }

        try {
            // ตรวจสอบว่า request นี้มีอยู่และเป็น draft หรือไม่
            $existingRequest = $this->ibRequestsModel->readRequest($id);

            if (!$existingRequest) {
                $this->responseError('Request not found', 404);
                return;
            }

            // ตรวจสอบสิทธิ์ (ต้องเป็นเจ้าของหรือมีสิทธิ์แก้ไข)
            $currentUserId = $_SESSION['user_id'] ?? 1;
            if ($existingRequest['created_by'] != $currentUserId) {
                // TODO: เพิ่มการตรวจสอบสิทธิ์แก้ไขจากผู้อื่น
            }

            // ตรวจสอบสถานะ (เฉพาะ draft เท่านั้นที่แก้ไขได้)
            if ($existingRequest['status'] !== 'draft' && $existingRequest['status'] !== 'rejected') {
                $this->responseError('ไม่สามารถแก้ไขได้ คำขอนี้อยู่ในสถานะ: ' . $existingRequest['status'], 400);
                return;
            }

            // ตรวจสอบประเภทการบันทึก
            $status = $formData['status'] ?? 'draft';

            $isSubmittingForApproval = ($status === 'pending_approval');

            if ($isSubmittingForApproval) {
                // ตรวจสอบข้อมูลสำหรับการส่งอนุมัติ
                $validation = $this->validateForSubmission($formData);

                if (!$validation['isValid']) {
                    $this->responseError('ข้อมูลไม่ครบถ้วน: ' . implode(', ', $validation['errors']), 400);
                    return;
                }
            }

            // Debug: บันทึกข้อมูลที่รับมา
            // error_log("📋 Form Data รับมา (UPDATE): " . json_encode($formData));
            // error_log("📅 Expected Disbursement Date: " . ($formData['expectedDisbursementDate'] ?? 'ไม่มี'));
            // error_log("📋 Project Program: " . ($formData['projectProgram'] ?? 'ไม่มี'));
            // error_log("🔄 Multi Year Disbursement: " . ($formData['multiYearDisbursement'] ?? 'ไม่มี'));

            // แปลงข้อมูลฟอร์มให้เป็นรูปแบบที่ฐานข้อมูลต้องการ
            $requestData = $this->transformFormData($formData);

            if ($isSubmittingForApproval) {
                $status = $this->determineInitialStageStatus($requestData);
            }

            $requestData['status'] = $status;
            $requestData['updated_by'] = $_SESSION['user_id'] ?? 1;

            $requestNumber = $existingRequest['request_number'] ?? null;
            if ($status !== 'draft' && empty($requestNumber)) {
                $requestNumber = $this->ibRequestsModel->generateNextRequestNumber($requestData['fiscal_year_id'] ?? null);
            }

            if (!empty($requestNumber)) {
                $requestData['request_number'] = $requestNumber;
            }

            // error_log("📝 อัพเดต Request ID: $id ด้วยข้อมูล: " . json_encode($requestData));

            // อัพเดตข้อมูลผ่าน model
            $result = $this->ibRequestsModel->updateRequest($id, $requestData);

            if ($result) {
                $attachments = [];
                try {
                    $attachments = $this->handleAttachments($id, $existingRequest['request_number'], $uploadedFiles);
                } catch (Exception $attachmentException) {
                    // error_log('❌ Attachment handling error (update): ' . $attachmentException->getMessage());
                    $attachments = $this->ibRequestsModel->getAttachmentFilePaths($id);
                }

                $updatedRequest = $this->ibRequestsModel->readRequest($id);
                if ($updatedRequest) {
                    $updatedRequest['attachments'] = $attachments;
                } else {
                    $updatedRequest = [
                        'id' => $id,
                        'attachments' => $attachments
                    ];
                }

                if ($this->isStageStatus($status)) {
                    $this->ibRequestsModel->updateStatus($id, $status, $requestData['updated_by']);
                    $updatedRequest = $this->ibRequestsModel->readRequest($id);
                    if ($updatedRequest) {
                        $updatedRequest['attachments'] = $attachments;
                    }
                }

                $message = ($status === 'draft') ? 'อัพเดตแบบร่างสำเร็จ' : 'ส่งเพื่อขออนุมัติสำเร็จ';
                $this->responseSuccess($message, $updatedRequest);
            } else {
                $this->responseError('ไม่สามารถอัพเดตได้', 500);
            }
        } catch (Exception $e) {
            // error_log("❌ เกิดข้อผิดพลาดในการอัพเดตฟอร์ม: " . $e->getMessage());
            // error_log("❌ Stack trace: " . $e->getTraceAsString());
            $this->responseError('เกิดข้อผิดพลาดในการอัพเดต: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ดึง payload จากคำขอ (รองรับ JSON และ multipart/form-data)
     */
    private function getRequestPayload()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');

        if (strpos($contentType, 'multipart/form-data') === 0) {
            $jsonPayload = $_POST['payload'] ?? null;
            if (!$jsonPayload) {
                return null;
            }

            $decoded = json_decode($jsonPayload, true);
            return is_array($decoded) ? $decoded : null;
        }

        $raw = file_get_contents('php://input');
        if (!$raw) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * รวมไฟล์ PDF ที่อัปโหลดให้เป็น array มาตรฐาน
     */
    private function getUploadedPdfFiles()
    {
        $files = [];

        $targets = [];
        if (isset($_FILES['pdf_attachments'])) {
            $targets[] = $_FILES['pdf_attachments'];
        }
        if (isset($_FILES['pdf_attachments_1'])) {
            $targets[] = $_FILES['pdf_attachments_1'];
        }

        foreach ($targets as $target) {
            foreach ($this->normalizeFilesArray($target) as $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * จัดโครงสร้างข้อมูลไฟล์ให้อยู่ในรูปแบบ array ที่ใช้งานง่าย
     */
    private function normalizeFilesArray($files)
    {
        $normalized = [];

        if (is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                $normalized[] = [
                    'name' => $name,
                    'type' => $files['type'][$index] ?? null,
                    'tmp_name' => $files['tmp_name'][$index] ?? null,
                    'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$index] ?? 0
                ];
            }
        } else {
            $normalized[] = $files;
        }

        return $normalized;
    }

    /**
     * ตรวจสอบว่าไฟล์ที่อัปโหลดเป็น PDF ที่ถูกต้องหรือไม่
     */
    private function validatePdfUploads(array $files)
    {
        if (empty($files)) {
            return null;
        }

        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;

        foreach ($files as $file) {
            $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;

            if ($errorCode === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($errorCode !== UPLOAD_ERR_OK) {
                finfo_close($finfo);
                return 'ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง';
            }

            if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                finfo_close($finfo);
                return 'ไฟล์แนบไม่ถูกต้อง';
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;

            $allowedMimes = [
                'application/pdf',
                'application/x-pdf',
                'application/octet-stream'
            ];

            $isPdfMime = $mime ? in_array($mime, $allowedMimes, true) : true;

            if ($extension !== 'pdf' || !$isPdfMime) {
                if ($finfo) {
                    finfo_close($finfo);
                }
                return 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น';
            }
        }

        if ($finfo) {
            finfo_close($finfo);
        }

        return null;
    }

    /**
     * จัดการไฟล์แนบ: บันทึกไฟล์ลงโฟลเดอร์และบันทึกเส้นทางลงฐานข้อมูล
     */
    private function handleAttachments($requestId, $requestNumber = null, array $files = [])
    {
        $existingPaths = $this->ibRequestsModel->getAttachmentFilePaths($requestId);
        $paths = is_array($existingPaths) ? $existingPaths : [];

        if (empty($files)) {
            $this->ibRequestsModel->updateAttachmentFilesColumn($requestId, $paths);
            return $paths;
        }

        $storagePath = $this->getAttachmentStoragePath();

        // สร้างโฟลเดอร์ถ้ายังไม่มี (ใช้ helper function)
        $this->ensureDirectoryExists($storagePath);

        // ตรวจสอบว่าสามารถเขียนได้หรือไม่
        if (!is_writable($storagePath)) {
            // error_log("❌ ไม่สามารถเขียนไฟล์ในโฟลเดอร์: {$storagePath}");
            throw new Exception("โฟลเดอร์ไม่มีสิทธิ์เขียนไฟล์: {$storagePath} (กรุณาตรวจสอบ Permission)");
        }

        $webPrefix = $this->getAttachmentWebPrefix();
        $uploadBy = $_SESSION['user_id'] ?? null;
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;

        foreach ($files as $file) {
            $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;

            if ($errorCode !== UPLOAD_ERR_OK) {
                continue;
            }

            $safeBaseName = preg_replace('/[^a-zA-Z0-9-_]+/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $timestamp = date('Ymd_His');
            $random = bin2hex(random_bytes(4));
            $storedFileNameParts = array_filter([$requestNumber, $safeBaseName, $timestamp, $random]);
            $storedFileName = implode('_', $storedFileNameParts) . '.pdf';

            $targetPath = $storagePath . DIRECTORY_SEPARATOR . $storedFileName;

            // ตรวจสอบก่อน move_uploaded_file
            if (!is_uploaded_file($file['tmp_name'])) {
                //error_log('❌ ไฟล์ไม่ใช่ uploaded file: ' . $file['name']);
                continue;
            }

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $lastError = error_get_last();
                // error_log('❌ ไม่สามารถย้ายไฟล์แนบได้: ' . $file['name']);
                // error_log('   Source: ' . $file['tmp_name']);
                // error_log('   Target: ' . $targetPath);
                // error_log('   Storage Path Writable: ' . (is_writable($storagePath) ? 'Yes' : 'No'));
                if ($lastError) {
                   // error_log('   Error: ' . $lastError['message']);
                }
                continue;
            }

           // error_log('✅ ย้ายไฟล์สำเร็จ: ' . $storedFileName);

            $mime = $finfo ? finfo_file($finfo, $targetPath) : 'application/pdf';
            $webPath = $this->buildAttachmentWebPath($webPrefix, $storedFileName);

            $paths[] = $webPath;
        }

        if ($finfo) {
            finfo_close($finfo);
        }

        $paths = array_values(array_unique(array_filter($paths)));
        $this->ibRequestsModel->updateAttachmentFilesColumn($requestId, $paths);

        return $paths;
    }

    /**
     * คืนค่าพาธสำหรับจัดเก็บไฟล์บนเซิร์ฟเวอร์
     */
    private function getAttachmentStoragePath()
    {
        $projectRoot = dirname(__DIR__, 2);
        return $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'investment_budget';
    }

    /**
     * คืนค่า prefix สำหรับการเข้าถึงไฟล์ผ่านเว็บ
     */
    private function getAttachmentWebPrefix()
    {
        $projectFolder = basename(dirname(__DIR__, 2));
        return '/' . trim($projectFolder, '/') . '/storage/investment_budget';
    }

    /**
     * สร้างเส้นทางเว็บของไฟล์แนบ
     */
    private function buildAttachmentWebPath($prefix, $fileName)
    {
        $cleanPrefix = rtrim($prefix, '/');
        return $cleanPrefix . '/' . ltrim($fileName, '/');
    }

    
    private function ensureDirectoryExists($path, $throwException = true)
    {
        if (is_dir($path)) {
            return true;
        }

        // สร้างโฟลเดอร์
        $oldUmask = umask(0);
        $created = mkdir($path, 0777, true);
        umask($oldUmask);

        if (!$created && !is_dir($path)) {
            $message = 'ไม่สามารถสร้างโฟลเดอร์: ' . $path;
            error_log('❌ ' . $message);

            if ($throwException) {
                throw new Exception($message);
            }
            return false;
        }

        // บน Windows: ตั้งค่า permission เพิ่มเติม
        if (DIRECTORY_SEPARATOR === '\\') {
            @chmod($path, 0777);
        }

        error_log('✅ สร้างโฟลเดอร์สำเร็จ: ' . $path);
        return true;
    }
    /**
     * แปลงข้อมูลฟอร์มให้เป็นรูปแบบที่ฐานข้อมูลต้องการ
     *
     * @param array $formData - ข้อมูลจากฟอร์ม (camelCase format)
     * @return array - ข้อมูลที่พร้อมบันทึกในฐานข้อมูล (snake_case format)
     */
    private function transformFormData($formData)
    {
        // ข้อมูลหลักของคำขอรวมกับข้อมูล item (1 request = 1 item)
        $requestData = [
            // ข้อมูลหลักของคำขอ (Request Main Info)
            'fiscal_year_id' => $formData['fiscalYear'] ?? null,
            'version_plan_id' => $formData['versionPlan'] ?? null,
            'asset_type_id' => $formData['assetType'] ?? null,
            'asset_type_code' => $formData['assetTypeCode'] ?? '',

            // ข้อมูล Item Details
            'budget_source_id' => !empty($formData['budgetSource']) ? $formData['budgetSource'] : null,
            'item_name' => $formData['itemName'] ?? null,
            'description' => $formData['Description'] ?? null,  // Note: ต้องเป็น 'D' ตัวใหญ่ตาม JS
            'reason' => !empty($formData['Reason']) ? $formData['Reason'] : null,  // Note: ต้องเป็น 'R' ตัวใหญ่ตาม JS

            // ข้อมูลจำนวนและราคา (Quantity & Price)
            'quantity' => $this->parseNumber($formData['Quantity'] ?? 0),  // Note: ต้องเป็น 'Q' ตัวใหญ่ตาม JS
            'unit_price' => $this->parseNumber($formData['Price'] ?? 0),  // Note: ต้องเป็น 'P' ตัวใหญ่ตาม JS
            'total_amount' => $this->parseNumber($formData['totalAmount'] ?? ($formData['Total'] ?? 0)),

            // ข้อมูลครุภัณฑ์ (Equipment Info)
            // has_standard เป็น foreign key ไปที่ tb_equipment_standards (integer)
            'has_standard' => !empty($formData['equipmentStandards']) ? (int)$formData['equipmentStandards'] : null,
            'asset_nature' => !empty($formData['equipmentNature']) ? $formData['equipmentNature'] : null,
            'asset_category' => !empty($formData['assetCategory']) ? $formData['assetCategory'] : null,
            'replacement_asset_numbers' => !empty($formData['serialNumbers']) ? $formData['serialNumbers'] : null,  // Array field

            // ข้อมูลยุทธศาสตร์และโครงการ (Strategy & Project)
            'strategy' => !empty($formData['strategy']) ? $formData['strategy'] : null,
            'project_program' => !empty($formData['projectProgram']) ? $formData['projectProgram'] : null,

            // ข้อมูลการเบิกจ่าย (Disbursement Info)
            'expected_disbursement_date' => !empty($formData['expectedDisbursementDate']) ? $formData['expectedDisbursementDate'] : null,
            // multi_year_disbursement เป็น foreign key ไปที่ tb_disbursement_plan (integer)
            // 1 = เป็นแผนเบิกจ่ายภายใน 1 ปี, 2 = เป็นแผนเบิกจ่ายมากกว่า 1 ปี
            'multi_year_disbursement' => !empty($formData['multiYearDisbursement']) ? (int)$formData['multiYearDisbursement'] : null,

            // ข้อมูลเฉพาะ Card (Specific Fields)
            'area_rai' => !empty($formData['areaRai']) ? (float)$formData['areaRai'] : null,
            'project_start_period' => !empty($formData['projectStartPeriod']) ? $formData['projectStartPeriod'] : null,
            'project_end_period' => !empty($formData['projectEndPeriod']) ? $formData['projectEndPeriod'] : null,
            'operation_year' => !empty($formData['operationYear']) ? $formData['operationYear'] : null,
            'work_period' => !empty($formData['workPeriod']) ? $formData['workPeriod'] : null,

            // ข้อมูลผู้สร้างและผู้แก้ไข (Audit Info)
            'created_by' => $_SESSION['user_id'] ?? 1,
            'updated_by' => $_SESSION['user_id'] ?? 1
        ];

        // Log เพื่อ debug
        // error_log("🔄 Transform Form Data:");
        // error_log("  - equipmentStandards: " . ($formData['equipmentStandards'] ?? 'null') . " → has_standard (ID): " . ($requestData['has_standard'] ?? 'null'));
        // error_log("  - serialNumbers: " . json_encode($formData['serialNumbers'] ?? []));
        // error_log("  - Reason: " . substr($formData['Reason'] ?? '', 0, 50));
        // error_log("  - projectProgram: " . ($formData['projectProgram'] ?? 'null'));
        // error_log("  - expectedDisbursementDate: " . ($formData['expectedDisbursementDate'] ?? 'null'));
        // error_log("  - multiYearDisbursement: " . ($formData['multiYearDisbursement'] ?? 'null') . " → (ID): " . ($requestData['multi_year_disbursement'] ?? 'null'));

        if (isset($requestData['quantity']) && $requestData['quantity'] !== null) {
            $requestData['quantity'] = (int) round($requestData['quantity']);
        }

        // คำนวณจำนวนเงินรวมจากจำนวนและราคาต่อหน่วย หากยังไม่ได้รับค่า
        $hasQuantity = isset($requestData['quantity']) && $requestData['quantity'] !== null;
        $hasUnitPrice = isset($requestData['unit_price']) && $requestData['unit_price'] !== null;

        if (
            (!isset($requestData['total_amount']) || $requestData['total_amount'] <= 0) &&
            $hasQuantity && $hasUnitPrice &&
            $requestData['quantity'] > 0 && $requestData['unit_price'] > 0
        ) {
            $requestData['total_amount'] = round($requestData['quantity'] * $requestData['unit_price'], 2);
            // error_log('  → Recomputed total_amount: ' . $requestData['total_amount']);
        }

        return $requestData;
    }


    /**
     * ตรวจสอบข้อมูลสำหรับการส่งอนุมัติ
     */
    private function validateForSubmission($formData)
    {
        $errors = [];

        // ตรวจสอบฟิลด์หลักที่จำเป็น
        if (empty($formData['fiscalYear'])) {
            $errors[] = 'กรุณาเลือกปีงบประมาณ';
        }

        if (empty($formData['versionPlan'])) {
            $errors[] = 'กรุณาเลือกเวอร์ชั่นแผน';
        }

        if (empty($formData['assetType'])) {
            $errors[] = 'กรุณาเลือกประเภทข้อมูล';
        }

        // ตรวจสอบว่ามีรายการอย่างน้อย 1 รายการ
        $hasItems = false;

        $itemFields = [
            $formData['itemName'] ?? '',
            $formData['Description'] ?? '',
            $formData['Reason'] ?? '',
        ];

        foreach ($itemFields as $value) {
            if (is_string($value) && trim($value) !== '') {
                $hasItems = true;
                break;
            }
        }

        if (!$hasItems) {
            $quantity = $this->parseNumber($formData['Quantity'] ?? 0);
            $price = $this->parseNumber($formData['Price'] ?? 0);
            $total = $this->parseNumber($formData['totalAmount'] ?? 0);

            if ($quantity > 0 || $price > 0 || $total > 0) {
                $hasItems = true;
            }
        }

        if (!$hasItems) {
            $errors[] = 'กรุณากรอกรายการอย่างน้อย 1 รายการ';
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function isValidDate($date)
    {
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    /**
     * แปลงข้อความเป็นตัวเลข
     */
    private function parseNumber($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }
            $value = $trimmed;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // ลบ comma และสัญลักษณ์อื่นๆ
        $cleaned = preg_replace('/[^\d.-]/', '', $value);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function responseError($message, $code = 400)
    {
        $this->sendJson([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    private function sendJson($payload, $code = 200)
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

// Create controller and process request
$controller = new IbRequestsController();
$controller->processRequest();
