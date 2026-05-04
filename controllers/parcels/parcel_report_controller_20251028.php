<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parcels/parcel_report_model.php';

class ParcelReportController
{
    private $parcelReportModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->parcelReportModel = new ParcelReportModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'read') {
                    $this->readApproveRaot();
                } elseif ($_GET['action'] == 'parcel_withdrawal_plan_approve_report') {
                    $this->readPlanApproveReport($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_plan']);
                } elseif ($_GET['action'] == 'parcel_withdrawal_approve_report') {
                    $this->readWithdrawalApproveReport($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_plan']);
                } elseif ($_GET['action'] == 'parcel_withdrawal_summary_report') {
                    $this->readWithdrawalSummaryReport($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_plan']);
                }
                break;
            case 'POST':

                break;
            case 'PUT':

                break;
            case 'DELETE':

                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    public function readApproveRaot()
    {
        $respondPlan = $this->parcelReportModel->readReportPlan();
        $respondDoc = $this->parcelReportModel->readReportDoc();
        if ($respondPlan && $respondDoc) {
            echo json_encode(['status' => 'success', 'respondPlan' => $respondPlan, 'respondDoc' => $respondDoc]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create materials']);
        }
    }

    public function readPlanApproveReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan)
    {
        $respond = $this->parcelReportModel->readPlanApproveReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan);
        echo  json_encode($respond);
    }

    public function readWithdrawalApproveReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan)
    {
        $respond = $this->parcelReportModel->readWithdrawalApproveReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan);
        echo  json_encode($respond);
    }

    public function readWithdrawalSummaryReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan)
    {
        $respond = $this->parcelReportModel->readWithdrawalSummaryReport($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan);
        echo  json_encode($respond);
    }

    
}

$dataController = new ParcelReportController();
$dataController->processRequest();
