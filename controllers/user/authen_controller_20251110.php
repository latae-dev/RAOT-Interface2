<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('// error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 1); // ปิดการแสดงผล
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
include_once '../../models/user/position_user_model.php';

 
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
    private $positionUserModel;

    public function __construct()
    {
        $database = new Database();
       $db = $database->connect('raot_db_qas');
        $this->authenModel = new AuthenModel($db);
        $this->branchModel = new BranchModel($db);
        $this->departModel = new DepartModel($db);
        $this->positionModel = new PositionModel($db);
        $this->roleModel = new RoleModel($db);
        $this->provinceModel = new ProvinceModel($db);
        $this->areaModel = new AreaModel($db);
        $this->head_officeModel = new HeadOfficeModel($db);
        $this->levelModel = new LevelModel($db);
        $this->positionUserModel = new PositionUserModel($db);
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



            $role = ($result['role_id'] ?? null) ? ($this->roleModel->readRoleOne($result['role_id']) ?: []) : [];
            $position = ($result['position_id'] ?? null) ? ($this->positionModel->readPositionOne($result['position_id']) ?: []) : [];
            $depart = ($result['depart_id'] ?? null) ? ($this->departModel->readDepartOne($result['depart_id']) ?: []) : [];
            $branch = ($result['branch_id'] ?? null) ? ($this->branchModel->readBranchOne($result['branch_id']) ?: []) : [];

            if(!isset($branch['province_id'])){
                $branch['province_id'] = $result['province_id'];
            }
            $province = ($branch['province_id'] ?? null) ? ($this->provinceModel->readProvinceOne($branch['province_id']) ?: []) : [];

            if(!isset($province['area_id'])){
                $province['area_id'] = $result['area_id'];
            }

            $area = ($province['area_id'] ?? null) ? ($this->areaModel->readAreaOne($province['area_id']) ?: []) : [];

            if(!isset($area['head_office_id'])){
                $area['head_office_id'] = $result['head_office_id'];
            }

            $head_office = ($area['head_office_id'] ?? null) ? ($this->head_officeModel->readHeadOfficeOne($area['head_office_id']) ?: []) : [];
            $level = ($result['id'] ?? null) ? ($this->levelModel->readLevelOne($result['id']) ?: null) : null;

            // $role = $this->roleModel->readRoleOne($result['role_id']) ?? [];
            // $position = $this->positionModel->readPositionOne($result['position_id']) ?? [];
            // $depart = $this->departModel->readDepartOne($result['depart_id']) ?? [];
            // $branch = $this->branchModel->readBranchOne($result['branch_id']) ?? [];

            // $province = $this->provinceModel->readProvinceOne($branch['province_id']) ?? [];
            // $area = $this->areaModel->readAreaOne($province['area_id']) ?? [];
            // $head_office = $this->head_officeModel->readHeadOfficeOne($area['head_office_id']) ?? [];
            // $level =  $this->levelModel->readLevelOne($result['id']) ?? null;

            $positionList = $this->positionUserModel->readPositionList($result['id']) ?? [];


            $res = [
                /* users */
                "id" => $result['id'] ?? null,
                "user_id" => $result['id'] ?? null,
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
                "approve_operating_primary" => $position["approve_operating_primary"] ?? "N/A",
                "approve_operating_strategic" => $position["approve_operating_strategic"] ?? "N/A",
                "approve_investment" => $position["approve_investment"] ?? "N/A",
                "approve_pacels" => $position["approve_pacels"] ?? "N/A",
                "approve_proposal" => $position["approve_proposal"] ?? "N/A",
                "position_list" => $positionList ?? [],

                // depart
                "depart_id" => $depart['id'] ?? 'N/A',
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

            $_SESSION['user_data'] = $res;

            // ป้องกัน Session Fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['is_authenticated'] = true;
            $_SESSION['user_id'] = $result['id'] ?? null;
            $_SESSION['user_code'] = $result['user_code'] ?? null;
            $_SESSION['user_fname'] = $result['user_fname'] ?? null;
            $_SESSION['user_lname'] = $result['user_lname'] ?? null;
            $_SESSION['role_code'] = $role['role_code'] ?? null;
            $_SESSION['role_name'] = $role['role_name'] ?? null;
            $_SESSION['position_id'] = $position['id'] ?? ($result['position_id'] ?? null);
            $_SESSION['position_code'] = $position['position_code'] ?? null;
            $_SESSION['position_name'] = $position['position_name'] ?? null;
            $_SESSION['approve_branch'] = $position['approve_branch'] ?? null;
            $_SESSION['approve_province'] = $position['approve_province'] ?? null;
            $_SESSION['approve_area'] = $position['approve_airea'] ?? null;
            $_SESSION['approve_airea'] = $position['approve_airea'] ?? null;
            $_SESSION['approve_head_office'] = $position['approve_head_office'] ?? null;
            $_SESSION['approve_operating_primary'] = $position['approve_operating_primary'] ?? null;
            $_SESSION['approve_operating_strategic'] = $result['approve_operating_strategic'] ?? null;
            $_SESSION['approve_investment'] = $position['approve_investment'] ?? null;
            $_SESSION['approve_pacels'] = $result['approve_pacels'] ?? null;
            $_SESSION['approve_proposal'] = $position['approve_proposal'] ?? null;
            $_SESSION['depart_id'] = $depart['id'] ?? null;
            $_SESSION['depart_code'] = $depart['depart_code'] ?? null;
            $_SESSION['depart_name'] = $depart['depart_name'] ?? null;
            $_SESSION['branch_id'] = $branch['id'] ?? null;
            $_SESSION['branch_code'] = $branch['branch_code'] ?? null;
            $_SESSION['branch_name'] = $branch['branch_name'] ?? null;
            $_SESSION['province_id'] = $province['id'] ?? null;
            $_SESSION['province_code'] = $province['province_code'] ?? null;
            $_SESSION['province_name'] = $province['province_name'] ?? null;
            $_SESSION['area_id'] = $area['id'] ?? null;
            $_SESSION['area_code'] = $area['area_code'] ?? null;
            $_SESSION['area_name'] = $area['area_name'] ?? null;
            $_SESSION['head_office_id'] = $head_office['id'] ?? null;
            $_SESSION['head_office_code'] = $head_office['head_office_code'] ?? null;
            $_SESSION['head_office_name'] = $head_office['head_office_name'] ?? null;
            $_SESSION['level_code'] = $level['level_code'] ?? null;
            $_SESSION['level_name'] = $level['level_name'] ?? null;

            // Debug logging
            // error_log('=== LOGIN SUCCESS ===');
            // error_log('Session ID after login: ' . session_id());
            // error_log('user_id set to: ' . $_SESSION['user_id']);
            // error_log('Session keys: ' . implode(', ', array_keys($_SESSION)));

            echo json_encode(['status' => 'success', 'data' => $res]);
        } else {
            echo json_encode(['status' => 'Error creating user']);
        }
    }
}

$dataController = new AuthenController();
$dataController->processRequest();
