<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include_once '../../configs/database.php';

// ===== MODEL (ใช้ pg_* แทน PDO) =====
class PlanMatModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getPending()
    {
        $sql = "
            SELECT * FROM parcels.tb_plan_mats AS M 
            LEFT JOIN parcels.tb_plans AS O
            ON M.plan_id = O.id
            WHERE O.sap_action = 'pending'
            AND O.status_head_office = 'approve'
        ";

        $result = pg_query($this->conn, $sql);
        $rows = [];

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $newRow = [];
                foreach ($row as $key => $value) {
                    // ตรวจ prefix ตามชื่อ field
                    if (strpos($key, '.') !== false) {
                        // ถ้ามีชื่อ schema.table.field เช่น "tb_plans.id"
                        [$table, $field] = explode('.', $key);
                        $prefix = $table . '_';
                    } else {
                        // ถ้าไม่มี ให้แยกจากชื่อฟิลด์เอง (โดยดูจาก field list)
                        $prefix = (array_key_exists('plan_id', $row) ? 'm_' : '');
                    }

                    // ตรวจสอบว่ามาจากตารางไหน (แบบ manual)
                    if (array_key_exists($key, pg_meta_data($this->conn, 'parcels.tb_plan_mats'))) {
                        $prefix = 'pm_';
                    } elseif (array_key_exists($key, pg_meta_data($this->conn, 'parcels.tb_plans'))) {
                        $prefix = 'p_';
                    }

                    $newRow[$prefix . $key] = $value;
                }
                $rows[] = $newRow;
            }
        }
        return $rows;
    }

    public function updateStatus($id, $status)
    {
        $id = intval($id);
        $status = pg_escape_string($this->conn, $status);
        $status_head_office = pg_escape_string($this->conn, 'approve');
    
        $sql = "UPDATE parcels.tb_plans 
                SET sap_action = '$status' 
                WHERE id = $id AND status_head_office = '$status_head_office'";
    
        $result = pg_query($this->conn, $sql);
    
        if (!$result) {
            return false; // query ล้มเหลว
        }
    
        $affected = pg_affected_rows($result);
        return $affected > 0; // สำเร็จเมื่อมีแถวถูกอัปเดต
    }
}

// ===== CONTROLLER (โครงสร้างคุณเดิม) =====
class PlanMatApiController
{
    private $planMatModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev'); // <-- คืนค่าจาก pg_connect()
        $this->planMatModel = new PlanMatModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        $id = isset($requestUri[2]) ? intval($requestUri[2]) : null;
        $data = json_decode(file_get_contents("php://input"), true);

        switch ($method) {
            case 'GET':
                if ($_GET['action'] == 'pending') {
                    $result = $this->planMatModel->getPending();
                }
                echo json_encode($result);
                break;

            case 'PUT':
                if ($_GET['action'] == 'complete') {
                    $success = $this->planMatModel->updateStatus($_GET['id'], $_GET['action']);
                    echo json_encode(["updated" => $success]);
                } else {
                    echo json_encode(["message" => "Missing data"]);
                }
                break;

            default:
                http_response_code(405);
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }
}

// ===== เรียกใช้งาน =====
$dataController = new PlanMatApiController();
$dataController->processRequest();
