<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/withdraws/wrd_compensation_model.php';
include_once '../../models/withdraws/wrd_compensation_list_model.php';
include_once '../../models/withdraws/wrd_compensation_personal_model.php';
include_once '../../models/withdraws/wrd_number_model.php';
include_once '../../models/withdraws/wrd_compensation_approve_history.php';
include_once '../../models/withdraws/wrd_plan_model.php';
include_once '../../models/withdraws/wrd_plan_list_model.php';

class CompensationController
{
    private $wrdCompensationModel;
    private $wrdNumberModel;
    private $wrdCompensationListModel;
    private $wrdCompensationPersonalModel;
    private $wrdCompensationApproveHistoryModel;
    private $wrdPlanModel;
    private $planListModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->wrdCompensationModel = new WrdCompensationModel($db);
        $this->wrdNumberModel = new WrdNumberModel($db);
        $this->wrdCompensationListModel = new WrdCompensationListModel($db);
        $this->wrdCompensationPersonalModel = new WrdCompensationPersonalModel($db);
        $this->wrdCompensationApproveHistoryModel = new WrdCompensationApproveHistoryModel($db);
        $this->wrdPlanModel = new WrdPlanModel($db);
        $this->planListModel = new PlanListModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'compensation_search') {
                    $this->readSearch($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['compensation_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], '', '', '', '', $_GET['is_type']);
                } else if ($_GET['action'] == 'get_one') {
                    $this->readOne($_GET['id']);
                } else if ($_GET['action'] == 'compensation_approvel_search') {
                    $this->readSearch($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['compensation_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], $_GET['req_position_code'], $_GET['req_depart_code'], $_GET['search_user_code'], $_GET['action'], $_GET['is_type']);
                } else if ($_GET['action'] == 'get_persons') {
                    $this->getPersons($_GET['depart_code']);
                } else if ($_GET['action'] == 'report') {
                    $this->readSearchReport($_GET['key'], $_GET['limit'], $_GET['offset'], $_GET['compensation_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status'], $_GET['req_user_code'], $_GET['req_position_code'], $_GET['req_depart_code'], $_GET['search_user_code']);
                } else if ($_GET['action'] == 'get_persons_report') {
                    $this->getPersonsReport($_GET['depart_code']);
                }
                break;
            case 'POST':
                if ($_GET['action'] == 'create_compensation') {
                    $this->createCompensation($data);
                }
                break;
            case 'PUT':
                if ($_GET['action'] == 'edit_one') {
                    $this->editCompensation($data, $_GET['id']);
                } else if ($_GET['action'] == 'approve_one') {
                    $this->approveExpenses($data, $_GET['id'], $_GET['position_code']);
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

    public function createCompensation($data)
    {
        $compensation_number = $this->wrdNumberModel->createCompensationNumber();
        $data['main_data']['compensation_number'] = $compensation_number;
        $return_id = $this->wrdCompensationModel->createWrdCompensation($data);

        if ($return_id) {
            $respond = $this->wrdCompensationListModel->createCompensationList($data, $return_id);
            $respond_personal = $this->wrdCompensationPersonalModel->createCompensationPersonal($data, $return_id);
            echo json_encode(['status' => 'success', 'data' => $compensation_number]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function editCompensation($data, $id)
    {
        $edit_main_state = $this->wrdCompensationModel->editWrdCompensation($data, $id);
        $edit_child_state = $this->wrdCompensationListModel->editCompensationList($data, $id);
        $edit_personal_state = $this->wrdCompensationPersonalModel->editCompensationPersonal($data, $id);

        if ($edit_main_state & $edit_child_state & $edit_personal_state) {
            echo json_encode(['status' => 'success', 'data']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create child']);
        }
    }

    public function readSearch($key, $limit, $offset, $compensation_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code, $action = '', $is_type = '')
    {
        $respond = $this->wrdCompensationModel->readAllSearch($key, $limit, $offset, $compensation_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code, $action, $is_type);
        echo json_encode($respond);
    }

    public function readOne($id)
    {
        $main_data = $this->wrdCompensationModel->readWrdCompensationOne($id);
        $detail_data = $this->wrdCompensationListModel->readCompensationListOne($id);
        $gl_detail_data = $this->wrdCompensationListModel->readCompensationGLListOne($id);
        $personal_data = $this->wrdCompensationPersonalModel->readCompensationPersonalOne($id);
        $approve_data = $this->wrdCompensationApproveHistoryModel->readApproveOne($id);

        $main_data_plan = $this->wrdPlanModel->readPlanOne($main_data["plan_id"]);
        $child_data_plan = $this->planListModel->readPlanListOne($main_data["plan_id"]);

        $result = [
            'status' => 'success',
            'main_data' => $main_data,
            'detail_data' => $detail_data,
            'personal_data' => $personal_data,
            'approve_data' => $approve_data,
            'main_data_plan' => $main_data_plan,
            'child_data_plan' => $child_data_plan,
            'gl_detail_data' => $gl_detail_data
        ];

        echo json_encode($result);

        return $result;
    }

    public function deleteStatus($id)
    {
        $respond = $this->wrdCompensationModel->deleteWrdStatus($id);

        $result = json_decode($respond, true);

        if ($result && $result['status'] === 'success') {
            echo json_encode(['status' => 'success']);
        } else {
            $message = $result['message'] ?? 'Unknown error occurred';
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
    }

    public function getPersons($depart_code)
    {
        $respond = $this->wrdCompensationModel->readAllPersons($depart_code);
        echo json_encode($respond);
        return $respond;
    }

    public function approveExpenses($data, $id, $position_code)
    {
        $main_data = $this->wrdCompensationModel->readWrdCompensationOne($id);
        $edit_main_state = $this->wrdCompensationModel->updateWrdCompensationApprove($data, $id, $position_code, $main_data);
        $return_id = $this->wrdCompensationApproveHistoryModel->createApproveLogs($data, $id, $position_code, $main_data);
        if (!empty($data['gl_detail_data'])) {
            $saveGL = $this->wrdCompensationListModel->insertCompensationGLList($data, $id);
        }

        if ($edit_main_state && $return_id) {
            echo json_encode(['status' => 'success', 'data' => $return_id]);
        } else {
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

    public function readSearchReport($key, $limit, $offset, $compensation_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code)
    {
        $respond = $this->wrdCompensationModel->readAllReport($key, $limit, $offset, $compensation_number, $start_date, $end_date, $status, $req_user_code, $req_position_code, $req_depart_code, $search_user_code);
        foreach ($respond['data'] as &$item) {
            $detail_data = $this->wrdCompensationListModel->readCompensationListOne($item['id']);
            $text = [];
            if (!empty($detail_data)) {
                foreach ($detail_data as $rec) {
                    $total = number_format($rec['bath'] + ($rec['stang'] / 100), 2);
                    $text[] = $rec['mat_name'] . ' (' . $total . ' บาท)';
                }
            }
            $item['compenstaion_items'] = implode('<br>', $text);
            $audit = $this->wrdCompensationApproveHistoryModel->readApproveOneReport($item['id'], 'audit');
            $finance = $this->wrdCompensationApproveHistoryModel->readApproveOneReport($item['id'], 'finance');
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
        }
        echo json_encode($respond);
    }

    public function getPersonsReport($depart_code)
    {
        $respond = $this->wrdCompensationModel->readAllPersonsReport($depart_code);
        echo json_encode($respond);
        return $respond;
    }
}

$dataController = new CompensationController();
$dataController->processRequest();
