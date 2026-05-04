<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include_once '../../configs/database.php';

// ===== MODEL =====
class LiabilityCloneModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * เช็คว่า id มีอยู่แล้วหรือไม่
     */
    public function checkIdExists($id)
    {
        $sql = "SELECT id FROM parameters.tb_liabilities_clone WHERE id = $1";
        $result = pg_query_params($this->conn, $sql, array($id));
        
        if (!$result) {
            return false;
        }
        
        return pg_num_rows($result) > 0;
    }

    /**
     * เช็คว่า liability_code มีอยู่แล้วหรือไม่
     */
    public function checkLiabilityCodeExists($liability_code)
    {
        $sql = "SELECT id FROM parameters.tb_liabilities_clone WHERE liability_code = $1";
        $result = pg_query_params($this->conn, $sql, array($liability_code));
        
        if (!$result) {
            return false;
        }
        
        return pg_num_rows($result) > 0;
    }

    /**
     * เพิ่มข้อมูลใหม่ (พร้อม id)
     */
    public function insertLiability($id, $liability_code, $liability_name)
    {
        $sql = "INSERT INTO parameters.tb_liabilities_clone 
                (id, liability_code, liability_name, liability_description, status, created_at, updated_at) 
                VALUES ($1, $2, $3, NULL, 'active', NOW(), NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->conn, $sql, array($id, $liability_code, $liability_name));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    /**
     * ดึง id สูงสุด
     */
    public function getMaxId()
    {
        $sql = "SELECT COALESCE(MAX(id), 0) as max_id FROM parameters.tb_liabilities_clone";
        $result = pg_query($this->conn, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['max_id'];
    }

    /**
     * นำเข้าข้อมูลหลายรายการ
     */
    public function importLiabilities($data)
    {
        $results = [
            'success' => [],
            'skipped' => [],
            'errors' => []
        ];

        // ดึง id สูงสุดเพื่อใช้เป็นค่าเริ่มต้น
        $currentMaxId = $this->getMaxId();
        $nextId = $currentMaxId + 1;

        foreach ($data as $index => $item) {
            // ตรวจสอบว่ามี liability_code และ liability_name หรือไม่
            if (!isset($item['liability_code']) || !isset($item['liability_name'])) {
                $results['errors'][] = [
                    'index' => $index,
                    'message' => 'Missing liability_code or liability_name',
                    'data' => $item
                ];
                continue;
            }

            $liability_code = trim($item['liability_code']);
            $liability_name = trim($item['liability_name']);

            // ตรวจสอบว่าค่าว่างหรือไม่
            if (empty($liability_code) || empty($liability_name)) {
                $results['errors'][] = [
                    'index' => $index,
                    'message' => 'liability_code or liability_name is empty',
                    'data' => $item
                ];
                continue;
            }

            // กำหนด id (ถ้ามีใน input ใช้ค่านั้น ถ้าไม่มีให้ใช้ nextId)
            $id = null;
            if (isset($item['id']) && !empty($item['id'])) {
                $id = (int)$item['id'];
            } else {
                $id = $nextId;
                $nextId++;
            }

            // เช็คว่ามี id อยู่แล้วหรือไม่
            if ($this->checkIdExists($id)) {
                $results['skipped'][] = [
                    'index' => $index,
                    'id' => $id,
                    'liability_code' => $liability_code,
                    'message' => 'ID already exists'
                ];
                continue;
            }

            // เช็คว่ามี liability_code อยู่แล้วหรือไม่
            if ($this->checkLiabilityCodeExists($liability_code)) {
                $results['skipped'][] = [
                    'index' => $index,
                    'id' => $id,
                    'liability_code' => $liability_code,
                    'message' => 'Liability code already exists'
                ];
                continue;
            }

            // เพิ่มข้อมูลใหม่
            $insertedId = $this->insertLiability($id, $liability_code, $liability_name);
            
            if ($insertedId) {
                $results['success'][] = [
                    'index' => $index,
                    'id' => $insertedId,
                    'liability_code' => $liability_code,
                    'liability_name' => $liability_name
                ];
            } else {
                $results['errors'][] = [
                    'index' => $index,
                    'message' => 'Failed to insert data: ' . pg_last_error($this->conn),
                    'data' => $item
                ];
            }
        }

        return $results;
    }
}

// ===== CONTROLLER =====
class LiabilityCloneApiController
{
    private $liabilityCloneModel;

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
        
        $this->liabilityCloneModel = new LiabilityCloneModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                $this->handlePost();
                break;

            default:
                http_response_code(405);
                echo json_encode([
                    "error" => "Method not allowed"
                ]);
                break;
        }
    }

    private function handlePost()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "error" => "Invalid JSON data"
            ]);
            return;
        }

        // รองรับทั้ง array และ object เดียว
        if (!is_array($data)) {
            $data = [$data];
        }

        // ตรวจสอบว่าเป็น array ที่มีข้อมูลหรือไม่
        if (empty($data)) {
            http_response_code(400);
            echo json_encode([
                "error" => "No data provided"
            ]);
            return;
        }

        // นำเข้าข้อมูล
        $results = $this->liabilityCloneModel->importLiabilities($data);

        // สรุปผลลัพธ์
        $summary = [
            'total' => count($data),
            'success' => count($results['success']),
            'skipped' => count($results['skipped']),
            'errors' => count($results['errors']),
            'details' => $results
        ];

        http_response_code(200);
        echo json_encode($summary, JSON_UNESCAPED_UNICODE);
    }
}

// ===== เรียกใช้งาน =====
$controller = new LiabilityCloneApiController();
$controller->processRequest();
?>
