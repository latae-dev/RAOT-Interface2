<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 1); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parcels/plan_model.php';
include_once '../../models/parcels/plan_mat_model.php';
include_once '../../models/parcels/gen_number_model.php';
include_once '../../models/parcels/doc_model.php';

class PlanController
{
    private $planModel;
    private $planMatModel;
    private $genNumberModel;
    private $docModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->planModel = new PlanModel($db);
        $this->planMatModel = new PlanMatModel($db);
        $this->genNumberModel = new GenNumberModel($db);
        $this->docModel = new DocModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'plan_all') {
                    $this->readAll($_GET['doc_type'], $_GET['limit'], $_GET['offset']);
                } elseif ($_GET['action'] == 'plan_search') {
                    $this->readAllSearch($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_plan']);
                } elseif ($_GET['action'] == 'plan_approve_status') {
                    $this->getApproveStatuslist($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['plan_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_plan']);
                } elseif ($_GET['action'] == 'plan_one') {
                    $this->readPlanOne($_GET['id']);
                } elseif ($_GET['action'] == 'plan_one_and_check_doc') {
                    $this->readPlanOneAndCheckDoc($_GET['id'],$_GET['quarter'],$_GET['user_code']);
                }
                break;
            case 'POST':
                if ($_GET['action'] == 'create_plan') {
                    $this->createPlans($data);
                } elseif ($_GET['action'] == 'merc_plan') {
                    $this->createMercPlan($data);
                }
                break;
            case 'PUT':
                if ($_GET['action'] == 'update_status') {
                    $this->updateStatusPlans($data, $_GET['id']);
                } elseif ($_GET['action'] == 'update_plan') {
                    $this->updatePlans($data, $_GET['id']);
                }elseif ($_GET['action'] == 'update_plan_doctype') {
                    $this->updatePlanDoctypes($data, $_GET['id']);
                }
                break;
            case 'DELETE':

                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    public function createPlans($data)
    {
        $plan_number = $this->genNumberModel->createPlanNumber();  // สร้าง plan_number ใหม่
        $data['tb_plans']['plan_number'] = $plan_number;  // เพิ่ม plan_number ลงในข้อมูล
        $return_id = $this->planModel->createPlan($data);  // สร้างแผนและได้ ID ของแผน

        // สร้างวัสดุในแผน
        $respond = $this->planMatModel->createPlanMat($data, $return_id);

        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($respond) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success', 'data' => $plan_number]);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create materials']);
        }
    }

    public function createMercPlan($data)
    {

        $plan_number = $this->genNumberModel->createPlanNumberMerc();  // สร้าง plan_number ใหม่
        $data['tb_plans']['plan_number'] = $plan_number;  // เพิ่ม plan_number ลงในข้อมูล
        $return_id = $this->planModel->createPlanMerc($data);  // สร้างแผนและได้ ID ของแผน

        // สร้างวัสดุในแผน
        $respond = $this->planMatModel->createPlanMatMerc($data, $return_id);

        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($respond) {

            // เปลี่ยน status_merge เป็น active
            $respond_status = $this->planModel->updateStatusMerge($data, 'active');

            if ($respond_status) {
                echo json_encode(['status' => 'success', 'data' => $plan_number]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status merge']);
            }
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed to create materials']);
        }
    }

    public function updatePlans($data, $id)
    {
        $status = $this->planModel->updatePlan($data, $id);  // สร้างแผนและได้ ID ของแผน
        if ($status == true) {
            $respond = $this->planMatModel->updatePlanMat($data, $id);
            // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
            if ($respond) {
                // ถ้าการสร้างวัสดุสำเร็จ
                echo json_encode(['status' => 'success']);
            } else {
                // ถ้าการสร้างวัสดุไม่สำเร็จ
                echo json_encode(['status' => 'error', 'message' => 'Failed materials']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed updatePlans']);
        }
    }

    public function updatePlanDoctypes($data, $id)
    {
        $status = $this->planModel->updatePlanDoctype($data, $id);  // สร้างแผนและได้ ID ของแผน
        if ($status == true) {
            $respond = $this->planMatModel->updatePlanMat($data, $id);
            // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
            if ($respond) {
                // ถ้าการสร้างวัสดุสำเร็จ
                echo json_encode(['status' => 'success']);
            } else {
                // ถ้าการสร้างวัสดุไม่สำเร็จ
                echo json_encode(['status' => 'error', 'message' => 'Failed materials']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed updatePlans']);
        }
    }

    public function readAll($doc_type, $limit, $offset)
    {
        $respond = $this->planModel->readAll($doc_type, $limit, $offset);
        echo  json_encode($respond);
    }

    public function readAllSearch($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan)
    {
        $respond = $this->planModel->readAllSearch($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan);
        echo  json_encode($respond);
    }

    public function getApproveStatuslist($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan)
    {
        $respond = $this->planModel->readApproveStatuslist($doc_type, $limit, $offset, $plan_number, $start_date, $end_date, $status_plan);
        echo  json_encode($respond);
    }

    public function readPlanOne($id)
    {
        $tb_plans = $this->planModel->readPlanOne($id);
        $tb_plan_mats = $this->planMatModel->readPlanMatOne($id);
        echo json_encode(['status' => 'success', "tb_plans" => $tb_plans, "tb_plan_mats" => $tb_plan_mats]);
    }

    public function readPlanOneAndCheckDoc($id,$quarter,$user_code)
    {
        $tb_plans      = $this->planModel->readPlanOne($id);
        $tb_plan_mats  = $this->planMatModel->readPlanMatOne($id);
        $tb_doc_byuser = $this->docModel->getDocByUserCode($quarter,$user_code);
        echo json_encode(['status' => 'success', "tb_plans" => $tb_plans, "tb_plan_mats" => $tb_plan_mats, "tb_doc_byuser" => $tb_doc_byuser]);
    }

    public function updateStatusPlans($data, $id)
    {
        $respond = $this->planModel->updateStatusPlans($data, $id);

        // หากเป็นระดับสูงสุด ให้อัพเดท plan_requ_all ที่เป็น array ด้วย 
        // ตรวจสอบจาก btanch  key = user_branch_key = user_head_office
        if (array_key_exists('user_head_office', $data)) {
            // มีคีย์ user_head_office อยู่ใน $data
            $res2 = $this->planModel->updateStatusPlanReguAll($data);
        }
        if (array_key_exists('user_area', $data)) {
            // มีคีย์ user_head_office อยู่ใน $data
            $res3 = $this->planModel->updateStatusPlanReguAll_userArea($data);
        }
        
        
        echo $respond;
    }
}

$dataController = new PlanController();
$dataController->processRequest();
