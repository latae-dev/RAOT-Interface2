<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parameter/wrd_fund_model.php';;

class WrdFundController
{
    private $wrdFundModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->wrdFundModel = new WrdFundModel($db);
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
                    $this->getAll();
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

    public function getAll()
    {
        $lists = $this->wrdFundModel->readAll();
        echo json_encode([
            'status' => 'success',
            'fund_lists' => $lists
        ]);
    }
}

$dataController = new WrdFundController();
$dataController->processRequest();