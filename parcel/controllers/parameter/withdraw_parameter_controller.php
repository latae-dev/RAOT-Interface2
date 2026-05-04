<?php

use BcMath\Number;

ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/user/user_model.php';
include_once '../../models/parameter/wrd_mat_model.php';
include_once '../../models/parameter/wrd_taxpayer_info_model.php';
include_once '../../models/parameter/count_unit_model.php';
include_once '../../models/user/depart_model.php';

class ParameterController
{
    private $userModel;
    private $wrdMatModel;
    private $countUnitModel;
    private $wrdTaxpayerInfoModel;
    private $departModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->userModel = new UserModel($db);
        $this->wrdMatModel = new WrdMatModel($db);
        $this->wrdTaxpayerInfoModel = new WrdTaxpayerInfoModel($db);
        $this->countUnitModel = new CountUnitModel($db);
        $this->departModel = new DepartModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'getAllParams') {
                    $this->getAllParams((int)$_GET['user_id']);
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

    public function getAllParams($user_id)
    {
        $current_user = $this->userModel->readUserOne($user_id);
        $wrds = $this->wrdMatModel->readAll();
        $wrds_mat_compensation = $this->wrdMatModel->readType([2, 3]);
        $unit = $this->countUnitModel->readCountUnit();
        $usersAll = $this->userModel->readUserAll();
        $taxpayer_info = $this->wrdTaxpayerInfoModel->readAll();
        $departs = $this->departModel->readDepart();
        $position_list_id = $_SESSION['user_data']['position_list_id'];
        $position_list_code = $_SESSION['user_data']['position_list_code'];

        session_regenerate_id(true);
        echo json_encode([
            'status' => 'success',
            'current_user' => $current_user,
            'users_all' => $usersAll ?: [],
            'mat_lists' => $wrds,
            'unit' => $unit,
            'taxpayer_info' => $taxpayer_info,
            'wrds_mat_compensation' => $wrds_mat_compensation,
            'departs' => $departs,
            'position_list_id' => $position_list_id,
            'position_list_code' => $position_list_code,
        ]);
    }
}

$dataController = new ParameterController();
$dataController->processRequest();
