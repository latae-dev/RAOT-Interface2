<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
include_once '../../configs/authMiddleware.php';
header("Content-Type: application/json");
include_once '../../configs/database.php';
include_once '../../models/user/branch_model.php';
class BranchController
{
    private $branchModel;

    public function __construct()
    {
        checkAuth();
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->branchModel = new BranchModel($db);
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

    // ฟังก์ชันเพื่อดึงข้อมูล branch ทั้งหมด
    public function getBranches()
    {
        $branches = $this->branchModel->readBranch();
        if ($branches) {
            echo json_encode($branches); // ส่งข้อมูล branch ทั้งหมดกลับในรูปแบบ JSON
        } else {
            echo json_encode(['message' => 'No branches found']);
        }
    }

    // ฟังก์ชันเพื่อดึงข้อมูล branch หนึ่งตัว
    public function getBranch($id)
    {
        $branch = $this->branchModel->readBranchOne($id);
        if ($branch) {
            echo json_encode($branch); // ส่งข้อมูล branch ที่มี id ที่ระบุ
        } else {
            echo json_encode(['message' => 'Branch not found']);
        }
    }

    // ฟังก์ชันเพื่อสร้าง branch ใหม่
    public function createBranch($data)
    {
        $result = $this->branchModel->createBranch($data['branch_code'], $data['branch_name']);
        if ($result) {
            echo json_encode(['message' => 'Branch created successfully']);
        } else {
            echo json_encode(['message' => 'Error creating branch']);
        }
    }

    // ฟังก์ชันเพื่ออัปเดตข้อมูล branch
    public function updateBranch($id, $data)
    {
        $result = $this->branchModel->updateBranch($id, $data['branch_code'], $data['branch_name']);
        if ($result) {
            echo json_encode(['message' => 'Branch updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating branch']);
        }
    }
}

$dataController = new BranchController();
$dataController->processRequest();

