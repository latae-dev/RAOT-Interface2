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

    
}

$dataController = new ParcelReportController();
$dataController->processRequest();
