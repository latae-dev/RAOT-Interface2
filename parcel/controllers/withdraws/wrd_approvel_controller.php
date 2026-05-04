<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/withdraws/wrd_plan_model.php';
include_once '../../models/withdraws/wrd_plan_list_model.php';
include_once '../../models/withdraws/wrd_number_model.php';
include_once '../../models/withdraws/wrd_approve_history.php';

class PlanController
{
    private $wrdPlanModel;
    private $wrdNumberModel;
    private $planListModel;
    private $wrdApproveHistoryModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->wrdPlanModel = new WrdPlanModel($db);
        $this->wrdNumberModel = new WrdNumberModel($db);
        $this->planListModel = new PlanListModel($db);
        $this->wrdApproveHistoryModel = new WrdApproveHistoryModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'plan_search') {
                    $this->readSearch($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['depart_code'], $_GET['req_user_code']);
                } else if ($_GET['action'] == 'get_one') {
                    $this->readOne($_GET['id']);
                } else if ($_GET['action'] == 'get_persons') {
                    $this->getPersons($_GET['depart_code']);
                } else if ($_GET['action'] == 'get_persons_report') {
                    $this->getPersonsReport($_GET['depart_code']);
                }
                break;
            case 'POST':
                if ($_GET['action'] == 'create_plan') {
                    $this->createPlans($data);
                }
                break;
            case 'PUT':
                if ($_GET['action'] == 'edit_one') {
                    $this->editPlans($data, $_GET['id']);
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

    public function createPlans($data)
    {
        $plan_number = $this->wrdNumberModel->createPlanNumber();  // สร้าง plan_number ใหม่
        $data['main_data']['plan_number'] = $plan_number;  // เพิ่ม plan_number ลงในข้อมูล
        $return_id = $this->wrdPlanModel->createWrdPlan($data);  // สร้างแผนและได้ ID ของแผน

        // สร้างวัสดุในแผน
        $respond = $this->planListModel->createPlanList($data, $return_id);

        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($respond) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success', 'data' => $plan_number]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function editPlans($data, $id)
    {
        $main_data = $this->wrdPlanModel->readPlanOne($id);       // ดึงข้อมูลหลัก
        $edit_main_state = $this->wrdPlanModel->updateWrdPlanApprove($data, $id);  // สร้างแผนและได้ ID ของแผน

        if ($edit_main_state) {
            $logs = $this->LogsApprove($data, $id, $main_data);  // สร้าง logs
            echo json_encode(['status' => 'success', 'data']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function readSearch($key, $limit, $offset, $plan_number, $start_date, $end_date, $status, $depart_code, $req_user_code)
    {
        $respond = $this->wrdApproveHistoryModel->readAllSearch($key, $limit, $offset, $plan_number, $start_date, $end_date, $status, $depart_code, $req_user_code);
        echo json_encode($respond);
    }

    public function readOne($id)
    {
        $main_data = $this->wrdPlanModel->readPlanOne($id);       // ดึงข้อมูลหลัก
        $child_data = $this->planListModel->readPlanListOne($id); // ดึงข้อมูลย่อย (เช่น รายการแผนย่อย)
        $approve_data = $this->wrdApproveHistoryModel->readApproveOne($id); // ดึงข้อมูลประวัติการอนุมัติ

        $result = [
            'status' => 'success',
            'main_data' => $main_data,
            'child_data' => $child_data,
            'approve_data' => $approve_data
        ];

        // ✅ ถ้าต้องการให้ echo เป็น JSON (สำหรับ frontend)
        echo json_encode($result);

        // ✅ หรือถ้าจะ return เป็น array (สำหรับใช้ภายในระบบ)
        return $result;
    }

    public function deleteStatus($id)
    {
        $respond = $this->wrdPlanModel->deleteWrdStatus($id);

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

    public function LogsApprove($data, $id, $info)
    {
        $return_id = $this->wrdApproveHistoryModel->createApproveLogs($data, $id, $info);
        return $return_id;
    }

    public function getPersons($depart_code)
    {
        $respond = $this->wrdPlanModel->readAllPersons($depart_code);
        echo json_encode($respond);
        return $respond;
    }

    public function getPersonsReport($depart_code)
    {
        $respond = $this->wrdPlanModel->readAllPersonsReport($depart_code);
        echo json_encode($respond);
        return $respond;
    }
}

$dataController = new PlanController();
$dataController->processRequest();
