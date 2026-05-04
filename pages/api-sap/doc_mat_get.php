<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include_once '../../configs/database.php';

// ===== MODEL (ใช้ pg_* แทน PDO) =====
class DocMatModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getPending()
    {
        $sql = "
            SELECT * FROM parcels.tb_doc_mats AS M 
            LEFT JOIN parcels.tb_docs AS O
            ON M.doc_id = O.id
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
                        // ถ้ามีชื่อ schema.table.field เช่น "tb_docs.id"
                        [$table, $field] = explode('.', $key);
                        $prefix = $table . '_';
                    } else {
                        // ถ้าไม่มี ให้แยกจากชื่อฟิลด์เอง (โดยดูจาก field list)
                        $prefix = (array_key_exists('doc_id', $row) ? 'm_' : '');
                    }

                    // ตรวจสอบว่ามาจากตารางไหน (แบบ manual)
                    if (array_key_exists($key, pg_meta_data($this->conn, 'parcels.tb_doc_mats'))) {
                        $prefix = 'dm_';
                    } elseif (array_key_exists($key, pg_meta_data($this->conn, 'parcels.tb_docs'))) {
                        $prefix = 'd_';
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
    
        $sql = "UPDATE parcels.tb_docs SET sap_action = '$status' WHERE id = $id AND status_head_office = '$status_head_office'";
        $result = pg_query($this->conn, $sql);
    
        if (!$result) {
            return false; // query ผิดหรือ db error
        }
    
        $affected = pg_affected_rows($result);
        return $affected > 0; // สำเร็จถ้ามี row ถูกอัปเดต
    }

    public function updateStatusMultiple($ids, $status)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $query = "
            UPDATE parcels.tb_docs 
            SET sap_action = $1 
            WHERE id = ANY ($2::int[])
            AND status_head_office = $3
        ";

        try {
            $status_head_office = 'approve';
            $result = pg_query_params($this->conn, $query, [$status, '{' . implode(',', $ids) . '}', $status_head_office]);

            if (!$result) {
                throw new Exception(pg_last_error($this->conn));
            }

            $rows_affected = pg_affected_rows($result);
            return $rows_affected > 0;

        } catch (Exception $e) {
            error_log("Error in updateStatusMultiple: " . $e->getMessage());
            return false;
        }
    }
}

// ===== CONTROLLER (โครงสร้างคุณเดิม) =====
class DocMatApiController
{
    private $docMatModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        
        if (!$db) {
            http_response_code(500);
            echo json_encode([
                "error" => "Database connection failed"
            ]);
            exit;
        }
        
        $this->docMatModel = new DocMatModel($db);
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
                    $result = $this->docMatModel->getPending();
                }
                echo json_encode($result);
                break;

            case 'PUT':
                if ($_GET['action'] == 'complete') {
                    // รองรับทั้ง query string และ request body
                    $ids = [];
                    
                    // รับจาก query string - parse เองเพื่อรองรับ ?id=1&id=2&id=3
                    if (isset($_SERVER['QUERY_STRING'])) {
                        // Parse query string เพื่อรับหลาย id
                        $queryString = $_SERVER['QUERY_STRING'];
                        preg_match_all('/[&?]id=(\d+)/', $queryString, $matches);
                        if (!empty($matches[1])) {
                            $ids = array_map('intval', $matches[1]);
                        } elseif (isset($_GET['id'])) {
                            // Fallback ถ้าไม่มีหลาย id
                            $ids = [intval($_GET['id'])];
                        }
                    }
                    
                    // รับจาก request body (ถ้ามี)
                    if (empty($ids) && isset($data['id'])) {
                        if (is_array($data['id'])) {
                            $ids = array_map('intval', $data['id']);
                        } else {
                            $ids = [intval($data['id'])];
                        }
                    }
                    
                    if (empty($ids)) {
                        echo json_encode(["message" => "Missing id"]);
                        break;
                    }
                    
                    // อัปเดตหลาย id พร้อมกัน
                    $success = $this->docMatModel->updateStatusMultiple($ids, $_GET['action']);
                    
                    if ($success) {
                        echo json_encode([
                            "updated" => true,
                            "rows_affected" => count($ids)
                        ]);
                    } else {
                        echo json_encode(["updated" => false]);
                    }
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
$dataController = new DocMatApiController();
$dataController->processRequest();
