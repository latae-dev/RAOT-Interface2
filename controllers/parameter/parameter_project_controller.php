<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parameter/wrd_project_model.php';;

class WrdProjectController
{
    private $wrdProjectModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->wrdProjectModel = new WrdProjectModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        // $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        // $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        // $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'getAll') {
                    $this->getAll($_GET['fund_id']);
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

    public function getAll($fund_id = null)
    {
        $lists = $this->wrdProjectModel->readAll($fund_id);
        echo json_encode([
            'status' => 'success',
            'project_lists' => $lists
        ]);
    }
}

$dataController = new WrdProjectController();
$dataController->processRequest();