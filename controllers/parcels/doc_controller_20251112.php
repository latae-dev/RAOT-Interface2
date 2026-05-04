<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';

include_once '../../models/parcels/doc_model.php';
include_once '../../models/parcels/plan_mat_model.php';
include_once '../../models/parcels/doc_mat_model.php';
include_once '../../models/parcels/gen_number_model.php';

class DocController
{
    private $docModel;
    private $docMatModel;
    private $planMatModel;
    private $genNumberModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->docModel = new DocModel($db);
        $this->docMatModel = new DocMatModel($db);
        $this->planMatModel = new PlanMatModel($db);
        $this->genNumberModel = new GenNumberModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'doc_all') {
                } elseif ($_GET['action'] == 'doc_search') {
                    $this->readAll($_GET['doc_type'], $_GET['limit'], $_GET['offset'], $_GET['doc_number'], $_GET['start_date'], $_GET['end_date'], $_GET['status_doc']);
                } elseif ($_GET['action'] == 'doc_one') {
                    $this->readDocOne($_GET['id']);
                }
                break;
            case 'POST':
                if ($_GET['action'] == 'create_doc') {
                    $this->createDocs($data);
                }
                break;
            case 'PUT':
                if ($_GET['action'] == 'update_status') {
                    $this->updateStatusDocs($data, $_GET['id']);
                } elseif ($_GET['action'] == 'update_doc') {
                    $this->updateDoc($data, $_GET['id']);
                } elseif ($_GET['action'] == 'update_doc_doctype') {
                }
                break;
            case 'DELETE':
                if ($_GET['action'] == 'delete_doc') {
                    $this->deleteDoc($data, $_GET['id']);
                }
                break;
            default:
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    public function createDocs($data)
    {
        try {
            // ตัดวัสดุออกจากแผน
            $cutResult = $this->planMatModel->cutQuantityPlanMat($data);

            if ($cutResult !== true) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'มีวัสดุอย่างน้อย 1 รายการที่มีจำนวนที่ใช้เกินจำนวนที่มีอยู่'
                ]);
                return;
            }

            // สร้างเลขที่เอกสาร
            $doc_number = $this->genNumberModel->createDocNumber();
            $data['tb_docs']['doc_number'] = $doc_number;

            // สร้างเอกสาร
            $return_id = $this->docModel->createDoc($data);

            // สร้างวัสดุในเอกสาร
            $respond = $this->docMatModel->createDocMat($data, $return_id);

            if (!$respond) {
                throw new Exception("ไม่สามารถสร้างวัสดุในเอกสารได้");
            }

            echo json_encode(['status' => 'success', 'data' => $doc_number]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateDoc($data, $id)
    {
        $respond = $this->docMatModel->updateDocMat($data, $id);
        // ตรวจสอบผลลัพธ์จากการสร้างวัสดุ
        if ($respond) {
            // ถ้าการสร้างวัสดุสำเร็จ
            echo json_encode(['status' => 'success']);
        } else {
            // ถ้าการสร้างวัสดุไม่สำเร็จ
            echo json_encode(['status' => 'error', 'message' => 'Failed materials']);
        }

        /* $status = $this->docModel->updateDoc($data, $id);  // สร้างแผนและได้ ID ของแผน
        if ($status == true) {
            $respond = $this->docMatModel->updateDocMat($data, $id);
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
        } */
    }

    public function readAll($doc_type, $limit, $offset, $doc_number, $start_date, $end_date, $status_doc)
    {
        $respond = $this->docModel->readAllDoc($doc_type, $limit, $offset, $doc_number, $start_date, $end_date, $status_doc);
        echo  json_encode($respond);
    }

    public function readDocOne($id)
    {
        $tb_docs = $this->docModel->readDocOne($id);
        $tb_doc_mats = $this->docMatModel->readDocMatOne($id);
        $tb_doc_mats_new = $this->planMatModel->readPlanMatCode($tb_doc_mats);
        echo json_encode(['status' => 'success', "tb_docs" => $tb_docs, "tb_doc_mats" => $tb_doc_mats_new]);
    }

    public function updateStatusDocs($data, $id)
    {
        $respond = $this->docModel->updateStatusDocs($data, $id);
        echo $respond;
    }

    public function deleteDoc($data, $id)
    {
        $respond = $this->docModel->deleteDoc($data, $id);
        echo $respond;
    }
}

$dataController = new DocController();
$dataController->processRequest();
