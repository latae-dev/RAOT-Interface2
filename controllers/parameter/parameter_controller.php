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
include_once '../../models/parameter/expen_model.php';
include_once '../../models/parameter/matlist_model.php';



class ParameterController
{
    private $countUnitModel;
    private $expenModel;
    private $matlistModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->countUnitModel = new CountUnitModel($db);
        $this->expenModel = new ExpenModel($db);
        $this->matlistModel = new MatlistModel($db);
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
                    $this->getParameters();
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

    public function getParameters()
    {
            $count_units = $this->countUnitModel->readCountUnit();
            //$expens = $this->expenModel->readExpen();
            $matlists = $this->matlistModel->readMatlist();
            
            session_regenerate_id(true);
            echo json_encode([
                'status' => 'success', 
                'count_units' => $count_units, 
                //'expens' => $expens, 
                'matlists' => $matlists
            ]);

    }
}

$dataController = new ParameterController();
$dataController->processRequest();
