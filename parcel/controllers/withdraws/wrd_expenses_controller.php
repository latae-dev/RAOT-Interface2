<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/withdraws/wrd_expenses_model.php';
include_once '../../models/withdraws/wrd_expenses_list_model.php';
include_once '../../models/withdraws/wrd_expenses_group_model.php';
include_once '../../models/withdraws/wrd_expenses_number_model.php';
include_once '../../models/withdraws/wrd_expenses_approve_history.php';
include_once '../../models/withdraws/wrd_plan_model.php';
include_once '../../models/withdraws/wrd_plan_list_model.php';

class ExpensesController
{
    private $expensesModel;
    private $expensesNumberModel;
    private $expensesListModel;
    private $expensesGroupModel;
    private $WrdExpensesApproveHistoryModel;
    private $wrdPlanModel;
    private $planListModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->expensesModel = new ExpensesModel($db);
        $this->expensesNumberModel = new ExpensesNumberModel($db);
        $this->expensesListModel = new ExpensesListModel($db);
        $this->expensesGroupModel = new ExpensesGroupModel($db);
        $this->WrdExpensesApproveHistoryModel = new WrdExpensesApproveHistoryModel($db);
        $this->wrdPlanModel = new WrdPlanModel($db);
        $this->planListModel = new PlanListModel($db);
    }

    private function resolveGroupCount($groupValue, $groupData)
    {
        $groupValue = (string)($groupValue ?? '');

        if ($groupValue === '1' || $groupValue === '3') {
            return 1;
        }

        if ($groupValue === '2') {
            if (!is_array($groupData)) {
                return 0;
            }

            $validRows = array_filter($groupData, function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                foreach ($row as $value) {
                    if ($value !== null && $value !== '') {
                        return true;
                    }
                }

                return false;
            });

            return count($validRows);
        }

        return isset($groupData) && !is_array($groupData)
            ? (int)$groupData
            : 0;
    }

    private function normalizeDateTimeString($value)
    {
        if (empty($value) || !is_string($value)) {
            return $value;
        }

        $formats = ['d-m-Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);
            if ($date instanceof DateTime) {
                return $date->format('d-m-Y H:i');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('d-m-Y H:i', $timestamp);
        }

        return $value;
    }

    private function applyGroupOverrides(array &$data)
    {
        $group = isset($data['main_data']['expenses_group'])
            ? (string)$data['main_data']['expenses_group']
            : '';

        if ($group === '') {
            return;
        }

        $parentId = $data['main_data']['parent_id'] ?? null;
        $parentData = null;
        if ($parentId) {
            $parentData = $this->expensesModel->readExpensesOne($parentId) ?: null;
        }

        // กรณี group 2 → ใช้ค่า field ตรง ๆ
        if ($group === '2') {
            foreach (['depart_code', 'full_name', 'position_name', 'level_name', 'affiliation_name'] as $field) {
                // ✅ ใช้ค่าจาก frontend ก่อน
                if (!empty($data['main_data'][$field])) {
                    continue; // ถ้ามีแล้ว ไม่ต้องทับ
                }

                // fallback ใช้ parentData ถ้า frontend ไม่ส่งมา
                if (is_array($parentData) && !empty($parentData[$field])) {
                    $data['main_data'][$field] = $parentData[$field];
                }
            }
        }

        // กรณี group 3 → ใช้ *_expenses mapping
        elseif ($group === '3' || $group === '1') {
            $mapping = [
                'depart_code'      => 'depart_code_expenses',
                'full_name'        => 'full_name_text_expenses',   // ✅ ใช้ full_name_text_expenses
                'position_name'    => 'position_name_expenses',
                'level_name'       => 'level_name_expenses',
                'affiliation_name' => 'affiliation_name_expenses',
            ];

            foreach ($mapping as $target => $source) {
                if (isset($data['main_data'][$source]) && $data['main_data'][$source] !== '') {
                    $data['main_data'][$target] = $data['main_data'][$source];
                } elseif (
                    !isset($data['main_data'][$target]) &&
                    is_array($parentData) &&
                    isset($parentData[$target])
                ) {
                    $data['main_data'][$target] = $parentData[$target];
                }
            }
        }

        // ลบฟิลด์ *_expenses ออกหลังจาก map เสร็จ
        $cleanupFields = [
            'depart_code_expenses',
            'full_name_text_expenses',
            'position_name_expenses',
            'level_name_expenses',
            'affiliation_name_expenses',
        ];

        foreach ($cleanupFields as $field) {
            if (isset($data['main_data'][$field])) {
                unset($data['main_data'][$field]);
            }
        }
    }



    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'expenses_search') {
                    $this->readSearch($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['expenses_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], '', '', '', '', $_GET['is_type']);
                } else if ($_GET['action'] == 'get_one') {
                    $this->readOne($_GET['id']);
                } else if ($_GET['action'] == 'get_one_multi') {
                    $this->readOneMulti($_GET['id']);
                } else if ($_GET['action'] == 'expenses_approvel_search') {
                    $this->readSearch($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['expenses_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], $_GET['req_position_code'], $_GET['req_depart_code'], $_GET['search_user_code'], $_GET['action'], $_GET['is_type']);
                } else if ($_GET['action'] == 'get_persons') {
                    $this->getPersons($_GET['depart_code']);
                } else if ($_GET['action'] == 'report') {
                    $this->readReport($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['expenses_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], $_GET['req_position_code'], $_GET['req_depart_code'], $_GET['search_user_code']);
                } else if ($_GET['action'] == 'get_persons_report') {
                    $this->getPersonsReport($_GET['depart_code']);
                } else if ($_GET['action'] == 'get_transfer') {
                    $this->getTransfer($_GET['id']);
                }
                break;
            case 'POST':
                if ($_GET['action'] == 'create_plan') {
                    $this->createExpenses($data);
                } else if ($_GET['action'] == 'create_plan_outside') {
                    $this->createExpensesOutside($data);
                } else if ($_GET['action'] == 'create_plan_multi') {
                    $this->createExpensesMulti($data);
                } else if ($_GET['action'] == 'create_plan_outside_multi') {
                    $this->createExpensesOutsideMulti($data);
                } else if ($_GET['action'] == 'save_transfer') {
                    $this->saveTransfer($data);
                }
                break;
            case 'PUT':
                if ($_GET['action'] == 'edit_one') {
                    $this->editExpenses($data, $_GET['id']);
                } else if ($_GET['action'] == 'approve_one') {
                    $this->approveExpenses($data, $_GET['id'], $_GET['position_code']);
                } else if ($_GET['action'] == 'approve_multi') {
                    $this->approveExpensesMulti($data, $_GET['id'], $_GET['position_code']);
                } else if ($_GET['action'] == 'edit_one_multi') {
                    $this->editExpensesMulti($data, $_GET['id']);
                }
                break;
            case 'DELETE':
                if ($_GET['action'] == 'delete_status') {
                    $this->deleteStatus($_GET['id']);
                }
                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    public function createExpenses($data)
    {
        $expenses_number = $this->expensesNumberModel->createExpensesNumber();  // สร้าง expenses_number ใหม่
        $data['main_data']['expenses_number'] = $expenses_number;  // เพิ่ม plan_number ลงในข้อมูล
        if (isset($data['main_data']['plan_id'])) {
            $return_id = $this->expensesModel->createExpenses($data);  // สร้างแผนและได้ ID ของแผน
        } else {
            $return_id = $this->expensesModel->createExpensesNoPlan($data);  // สร้างแผนและได้ ID ของแผน
        }

        // สร้างวัสดุในแผน
        if (!empty($data['list_data'])) {
            $list = $this->expensesListModel->createListDetail($data, $return_id);
        }
        if (!empty($data['group_data'])) {
            $group = $this->expensesGroupModel->createGroup($data, $return_id);
        }
        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($return_id) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success', 'data' => $expenses_number]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function createExpensesMulti($data)
    {
        $parent_id = $data['main_data']['parent_id'] ?? null;
        $expenses_number = $data['main_data']['expenses_number'] ?? null;
        if (empty($expenses_number)) {
            $expenses_number = $this->expensesNumberModel->createExpensesNumber();  // สร้าง expenses_number ใหม่
        }

        $this->applyGroupOverrides($data);
        //  $this->applyGroupOverrides($data);
        //      echo json_encode([
        //         'status' => 'success',
        //         'data' => $data,
        //     ]);
        $data['main_data']['expenses_number'] = $expenses_number;  // เพิ่ม expenses_number ลงในข้อมูล
        $data['main_data']['expenses_group_count'] = $this->resolveGroupCount(
            $data['main_data']['expenses_group'] ?? null,
            $data['group_data'] ?? []
        );
        $data['main_data']['parent_id'] = $parent_id ?: null;
        if (!empty($data['main_data']['plan_id'])) {
            $return_id = $this->expensesModel->createExpensesMulti($data);  // สร้างแผนและได้ ID ของแผน
        } else {
            $return_id = $this->expensesModel->createExpensesMultiNoPlan($data);  // สร้างแผนและได้ ID ของแผน
        }

        // สร้างวัสดุในแผน
        if (!empty($data['list_data'])) {
            $list = $this->expensesListModel->createListDetail($data, $return_id);
        }
        if (!empty($data['group_data'])) {
            $group = $this->expensesGroupModel->createGroup($data, $return_id);
        }
        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($return_id) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => $return_id,
                    'expenses_number' => $expenses_number,
                    'parent_id' => $parent_id,
                ],
                'expenses_number' => $expenses_number,
            ]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function editExpenses($data, $id)
    {
        $edit_main_state = $this->expensesModel->editExpenses($data, $id);  // สร้างแผนและได้ ID ของแผน
        $edit_child_state = $this->expensesListModel->editListDetail($data, $id);
        $edit_group_state = $this->expensesGroupModel->editGroup($data, $id);

        if ($edit_main_state & $edit_child_state & $edit_group_state) {
            echo json_encode(['status' => 'success', 'data']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function editExpensesMulti($data, $id)
    {
        // 🔹 เหมือน create: apply overrides & resolve group count
        $this->applyGroupOverrides($data);
        $data['main_data']['expenses_group_count'] = $this->resolveGroupCount(
            $data['main_data']['expenses_group'] ?? null,
            $data['group_data'] ?? []
        );

        $edit_main_state  = $this->expensesModel->editExpensesMulti($data, $id);
        $edit_child_state = $this->expensesListModel->editListDetail($data, $id);
        $edit_group_state = $this->expensesGroupModel->editGroup($data, $id);

        if ($edit_main_state && $edit_child_state && $edit_group_state) {
            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'id'                => $id,
                    'expenses_group'    => $data['main_data']['expenses_group'] ?? null,
                    'expenses_group_count' => $data['main_data']['expenses_group_count'] ?? 0,
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update child']);
        }
    }


    public function approveExpenses($data, $id, $position_code)
    {
        $main_data = $this->expensesModel->readExpensesOne($id);       // ดึงข้อมูลหลัก
        $edit_main_state = $this->expensesModel->updateWrdPlanApprove($data, $id, $position_code, $main_data);
        $return_id = $this->WrdExpensesApproveHistoryModel->createApproveLogs($data, $id, $position_code, $main_data);

        if ($edit_main_state && $return_id) {
            echo json_encode(['status' => 'success', 'data' => $return_id]);
        } else {
            // ตรวจสอบว่าใคร fail
            $errorSource = [];
            if (!$edit_main_state) {
                $errorSource[] = 'updateWrdPlanApprove';
            }
            if (!$return_id) {
                $errorSource[] = 'createApproveLogs';
            }

            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to create child',
                'error_source' => $errorSource
            ]);
        }
    }

    public function approveExpensesMulti($data, $id, $position_code)
    {
        if (empty($data['main_data']['approve']) && empty($data['main_data']['check_and_approve'])) {
            $data['main_data']['cat_text'] = '';
            $data['ExpenseCat'] = null;
        }
        $family = $this->expensesModel->readExpensesFamily($id);
        $records = $family['records'] ?? [];

        if (empty($records)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลสำหรับอนุมัติ',
            ]);
            return;
        }

        $updateFailed = [];
        $logFailed = [];
        $logIds = [];

        foreach ($records as $record) {
            $targetId = (int)($record['id'] ?? 0);
            if ($targetId <= 0) {
                $updateFailed[] = $targetId;
                continue;
            }

            $updated = $this->expensesModel->updateWrdPlanApproveMulti($data, $targetId, $position_code, $record);
            if (!$updated) {
                $updateFailed[] = $targetId;
                continue;
            }

            $logId = $this->WrdExpensesApproveHistoryModel->createApproveLogsMulti($data, $targetId, $position_code, $record);
            if (!$logId) {
                $logFailed[] = $targetId;
                continue;
            }

            $logIds[] = $logId;
        }

        if (!empty($updateFailed) || !empty($logFailed)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'อนุมัติไม่สำเร็จ',
                'update_failed' => $updateFailed,
                'log_failed' => $logFailed,
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $logIds,
        ]);
    }


    public function readSearch($key, $limit, $offset, $expenses_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code, $action = '', $is_type = '')
    {
        $respond = $this->expensesModel->readAllSearch($key, $limit, $offset, $expenses_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code, $action, $is_type);
        echo json_encode($respond);
    }

    public function readReport($key, $limit, $offset, $expenses_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code)
    {
        $respond = $this->expensesModel->readAllReport($key, $limit, $offset, $expenses_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code);
        foreach ($respond['data'] as &$item) {
            $child_data = $this->expensesListModel->readListDetail($item['id']);
            $traveling_group_data = $this->expensesGroupModel->readGroupOne($item['id']); // จำนวนคณะเดินทาง
            if (!empty($traveling_group_data)) {
                foreach ($traveling_group_data as $rec) {
                    $item['expenses_allowance_total'] += $rec['total_allowance'];
                    $item['expenses_accommodation_total'] += $rec['accommodation_total'];
                    $item['expenses_transportation_total'] += $rec['transport'];
                    $item['expenses_other_total'] += $rec['other_expenses'];
                    $item['expenses_food_total'] += $rec['meal_total'];
                }
            }
            $compensation = 0;
            if (!empty($child_data)) {
                foreach ($child_data as $rec) {
                    $compensation += $rec['compensation'];
                }
            }
            $audit = $this->WrdExpensesApproveHistoryModel->readApproveOneReport($item['id'], 'audit');
            $finance = $this->WrdExpensesApproveHistoryModel->readApproveOneReport($item['id'], 'finance');
            $item['compensation_total'] = $compensation;
            if (!empty($audit)) {
                $item['audit_status'] = $audit['approve_status'];
                $item['audit_full_name'] = $audit['user_fname'] . ' ' . $audit['user_lname'];
                $item['audit_date'] = $audit['approve_date'];
            }
            if (!empty($finance)) {
                $item['finance_status'] = $finance['approve_status'];
                $item['finance_full_name'] = $finance['user_fname'] . ' ' . $finance['user_lname'];
                $item['finance_date'] = $finance['approve_date'];
            }
            $transfer = $this->expensesModel->getOneTransferByExpensesId($item['id']);
            $item['transfer'] = $transfer;
        }
        echo json_encode($respond);
    }

    public function readOne($id)
    {
        $main_data = $this->expensesModel->readExpensesOne($id);       // ดึงข้อมูลหลัก
        $child_data = $this->expensesListModel->readListDetail($id); // ดึงข้อมูลย่อย (เช่น รายการแผนย่อย)
        $traveling_group_data = $this->expensesGroupModel->readGroupOne($id); // จำนวนคณะเดินทาง
        $approve_data = $this->WrdExpensesApproveHistoryModel->readApproveOne($id); // ดึงข้อมูลประวัติการอนุมัติ

        $main_data_plan = $this->wrdPlanModel->readPlanOne($main_data["plan_id"]);  // ดึงข้อมูลหลักคำขอเบิกเงินทดรองจ่าย
        $child_data_plan = $this->planListModel->readPlanListOne($main_data["plan_id"]); // ดึงข้อมูลย่อยคำขอเบิกเงินทดรองจ่าย

        $result = [
            'status' => 'success',
            'main_data' => $main_data,
            'child_data' => $child_data,
            'traveling_group_data' => $traveling_group_data,
            'approve_data' => $approve_data,
            'main_data_plan' => $main_data_plan,
            'child_data_plan' => $child_data_plan
        ];

        // ✅ ถ้าต้องการให้ echo เป็น JSON (สำหรับ frontend)
        echo json_encode($result);

        // ✅ หรือถ้าจะ return เป็น array (สำหรับใช้ภายในระบบ)
        return $result;
    }

    public function readOneMulti($id)
    {
        $family = $this->expensesModel->readExpensesFamily($id);

        if (empty($family['records'])) {
            $result = [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูล',
            ];
            echo json_encode($result);
            return $result;
        }

        $forms = [];
        $formNumber = 1;

        foreach ($family['records'] as $record) {
            $recordId = (int)$record['id'];
            $planId = $record['plan_id'] ?? null;

            $forms[] = [
                'form_number' => $formNumber,
                'main_data' => $record,
                'child_data' => $this->expensesListModel->readListDetail($recordId),
                'traveling_group_data' => $this->expensesGroupModel->readGroupOne($recordId),
                'approve_data' => $this->WrdExpensesApproveHistoryModel->readApproveOne($recordId),
                'main_data_plan' => $planId ? $this->wrdPlanModel->readPlanOne($planId) : null,
                'child_data_plan' => $planId ? $this->planListModel->readPlanListOne($planId) : [],
            ];

            $formNumber += 1;
        }

        $result = [
            'status' => 'success',
            'root_id' => $family['root_id'],
            'current_id' => (int)$id,
            'forms' => $forms,
        ];

        echo json_encode($result);

        return $result;
    }

    public function deleteStatus($id)
    {
        $respond = $this->expensesModel->deleteExpensesStatus($id);

        // แปลง JSON string ที่ได้กลับมาให้เป็น array
        $result = json_decode($respond, true);

        if ($result && $result['status'] === 'success') {
            echo json_encode(['status' => 'success']);
        } else {
            // ดึงข้อความ error จากฟังก์ชัน deleteWrdStatus มาแสดง
            $message = $result['message'] ?? 'Unknown error occurred';
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
    }

    public function getPersons($depart_code)
    {
        $respond = $this->expensesModel->readAllPersons($depart_code);
        echo json_encode($respond);
        return $respond;
    }

    public function getPersonsReport($depart_code)
    {
        $respond = $this->expensesModel->readAllPersonsReport($depart_code);
        echo json_encode($respond);
        return $respond;
    }

    public function createExpensesOutside($data)
    {
        $expenses_number = $this->expensesNumberModel->createExpensesNumber();  // สร้าง expenses_number ใหม่
        $data['main_data']['expenses_number'] = $expenses_number;  // เพิ่ม plan_number ลงในข้อมูล
        if (isset($data['main_data']['plan_id'])) {
            $return_id = $this->expensesModel->createExpensesOutside($data);  // สร้างแผนและได้ ID ของแผน
        } else {
            $return_id = $this->expensesModel->createExpensesNoPlanOutside($data);  // สร้างแผนและได้ ID ของแผน
        }

        // สร้างวัสดุในแผน
        if (!empty($data['list_data'])) {
            $list = $this->expensesListModel->createListDetail($data, $return_id);
        }
        if (!empty($data['group_data'])) {
            $group = $this->expensesGroupModel->createGroup($data, $return_id);
        }
        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($return_id) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success', 'data' => $expenses_number]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function createExpensesOutsideMulti($data)
    {
        $parent_id = $data['main_data']['parent_id'] ?? null;
        $expenses_number = $data['main_data']['expenses_number'] ?? null;
        if (empty($expenses_number)) {
            $expenses_number = $this->expensesNumberModel->createExpensesNumber();  // สร้าง expenses_number ใหม่
        }

        $this->applyGroupOverrides($data);
        $data['main_data']['expenses_number'] = $expenses_number;  // เพิ่ม expenses_number ลงในข้อมูล
        $data['main_data']['expenses_group_count'] = $this->resolveGroupCount(
            $data['main_data']['expenses_group'] ?? null,
            $data['group_data'] ?? []
        );
        $data['main_data']['parent_id'] = $parent_id ?: null;
        if (!empty($data['main_data']['plan_id'])) {
            $return_id = $this->expensesModel->createExpensesOutsideMulti($data);  // สร้างแผนและได้ ID ของแผน
        } else {
            $return_id = $this->expensesModel->createExpensesNoPlanOutsideMulti($data);  // สร้างแผนและได้ ID ของแผน
        }

        // สร้างวัสดุในแผน
        if (!empty($data['list_data'])) {
            $list = $this->expensesListModel->createListDetail($data, $return_id);
        }
        if (!empty($data['group_data'])) {
            $group = $this->expensesGroupModel->createGroup($data, $return_id);
        }
        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($return_id) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => $return_id,
                    'expenses_number' => $expenses_number,
                    'parent_id' => $parent_id,
                ],
                'expenses_number' => $expenses_number,
            ]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function getTransfer($id)
    {
        $respond = $this->expensesModel->getTransferByExpensesId($id);

        echo json_encode(['status' => 'success', 'data' => $respond]);
    }

    public function saveTransfer($data)
    {
        $return_id = $this->expensesModel->saveTransfer($data);

        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($return_id) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success', 'data' => $return_id]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }
}

$dataController = new ExpensesController();
$dataController->processRequest();
