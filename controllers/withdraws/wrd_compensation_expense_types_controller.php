<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parameter/wrd_compensation_expense_types_model.php';

class WrdCompensationExpenseTypesController
{
    private $wrdCompensationExpenseTypesModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->wrdCompensationExpenseTypesModel = new WrdCompensationExpenseTypesModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'getAll') {
                    $this->getAll();
                } else if ($_GET['action'] == 'getFirst') {
                    $this->getFirst();
                }else if ($_GET['action'] == 'getParent') {
                    $this->getParent($_GET['id']);
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
        $lists = $this->wrdCompensationExpenseTypesModel->readAll();
        echo json_encode([
            'status' => 'success',
            'compensation_expense_types' => $lists
        ]);
    }

    public function getFirst()
    {
        $lists = $this->wrdCompensationExpenseTypesModel->getFirst();
        echo json_encode([
            'status' => 'success',
            'compensation_expense_types' => $lists
        ]);
    }

    public function getParent($id = null)
    {
        $lists = $this->wrdCompensationExpenseTypesModel->getParent($id);
        echo json_encode([
            'status' => 'success',
            'compensation_expense_types' => $lists
        ]);
    }
}

$dataController = new WrdCompensationExpenseTypesController();
$dataController->processRequest();