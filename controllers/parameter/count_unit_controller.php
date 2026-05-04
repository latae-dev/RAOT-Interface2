<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parameter/count_unit_model.php';

class CountUnitController
{
    private $countUnitModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->countUnitModel = new CountUnitModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'all') {
                    $this->getCountUnits();
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

    public function getCountUnits()
    {
        $result = $this->countUnitModel->readCountUnit();
        if ($result) {
            session_regenerate_id(true);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } else {
            echo json_encode(['status' => 'Error creating user']);
        }
    }
}

$dataController = new CountUnitController();
$dataController->processRequest();
