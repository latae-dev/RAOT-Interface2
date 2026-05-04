<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/user/authen_model.php';
include_once '../../models/user/branch_model.php';
include_once '../../models/user/depart_model.php';
include_once '../../models/user/position_model.php';
include_once '../../models/user/role_model.php';
include_once '../../models/user/province_model.php';
include_once '../../models/user/area_model.php';
include_once '../../models/user/head_office_model.php';
include_once '../../models/user/user_model.php';
include_once '../../models/user/level_model.php';


class AuthenController
{
    private $authenModel;
    private $branchModel;
    private $departModel;
    private $positionModel;
    private $roleModel;
    private $provinceModel;
    private $areaModel;
    private $head_officeModel;
    private $levelModel;

    public function __construct()
    {
       $database = new Database();
       $db = $database->connect('raot_db_dev');
        $this->authenModel = new AuthenModel($db);
        $this->branchModel = new BranchModel($db);
        $this->departModel = new DepartModel($db);
        $this->positionModel = new PositionModel($db);
        $this->roleModel = new RoleModel($db);
        $this->provinceModel = new ProvinceModel($db);
        $this->areaModel = new AreaModel($db);
        $this->head_officeModel = new HeadOfficeModel($db);
        $this->levelModel = new LevelModel($db);
    }

    public function processRequest()
    {
     
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
       
        switch ($method) {
            case 'GET':

                break;
            case 'POST':
                if ($_GET['action'] == 'login') {
                    //   echo json_encode(['status' => '200','message'=> "OK"]);
                    
                   $this->loginUser($data);
                }
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


    public function loginUser($data)
    {
        $result = $this->authenModel->loginUser(
            $data['user_code'],
            $data['user_pass']
        );
        if ($result) {
            


            $role = $this->roleModel->readRoleOne($result['role_id']) ?? [];
            $position = $this->positionModel->readPositionOne($result['position_id']) ?? [];
            $depart = $this->departModel->readDepartOne($result['depart_id']) ?? [];
            $branch = $this->branchModel->readBranchOne($result['branch_id']) ?? [];

            $province = $this->provinceModel->readProvinceOne($branch['province_id']) ?? [];
            $area = $this->areaModel->readAreaOne($province['area_id']) ?? [];
            $head_office = $this->head_officeModel->readHeadOfficeOne($area['head_office_id']) ?? [];
            $level =  $this->levelModel->readLevelOne($result['id']) ?? null;


            $res = [
                /* users */
                "id" => $result['id'] ?? null, 
                "user_code" => $result['user_code'] ?? null, 
                "user_fname" => $result['user_fname'] ?? null, 
                "user_lname" => $result['user_lname'] ?? null, 
                "user_email" => $result['user_email'] ?? null, 
                "user_status" => $result['user_status'] ?? null, 

                // role
                "role_code" => $role['role_code'] ?? 'N/A', 
                "role_name" => $role['role_name'] ?? 'N/A', 

                // level
                "level_name" => $level['level_name'] ?? 'N/A',
                "level_code" => $level['level_code'] ?? 'N/A',


                // position
                "position_id" => $position['id'] ?? 'N/A', 
                "position_code" => $position['position_code'] ?? 'N/A', 
                "position_name" => $position['position_name'] ?? 'N/A', 
                "approve_branch" => $position['approve_branch'] ?? 'N/A', 
                "approve_province" => $position['approve_province'] ?? 'N/A', 
                "approve_airea" => $position['approve_airea'] ?? 'N/A', 
                "approve_head_office" => $position['approve_head_office'] ?? 'N/A', 

                // depart
                "depart_code" => $depart['depart_code'] ?? 'N/A', 
                "depart_name" => $depart['depart_name'] ?? 'N/A', 

                // branch
                "branch_id" => $branch['id'] ?? 'N/A', 
                "branch_code" => $branch['branch_code'] ?? 'N/A', 
                "branch_name" => $branch['branch_name'] ?? 'N/A',
                "branch_type" => $branch['branch_type'] ?? 'N/A',

                // province
                "province_id" => $province['id'] ?? 'N/A', 
                "province_code" => $province['province_code'] ?? 'N/A', 
                "province_name" => $province['province_name'] ?? 'N/A',
                "province_type" => $province['province_type'] ?? 'N/A',

                // area
                "area_id" => $area['id'] ?? 'N/A', 
                "area_code" => $area['area_code'] ?? 'N/A', 
                "area_name" => $area['area_name'] ?? 'N/A',
                "area_type" => $area['area_type'] ?? 'N/A',

                // head office
                "head_office_id" => $head_office['id'] ?? 'N/A', 
                "head_office_code" => $head_office['head_office_code'] ?? 'N/A', 
                "head_office_name" => $head_office['head_office_name'] ?? 'N/A',
                "head_office_type" => $head_office['head_office_type'] ?? 'N/A'
            ];



            // ป้องกัน Session Fixation
            session_regenerate_id(true);
            echo json_encode(['status' => 'success', 'data' => $res]);
        } else {
            echo json_encode(['status' => 'Error creating user']);
        }
    }
}

$dataController = new AuthenController();
$dataController->processRequest();
