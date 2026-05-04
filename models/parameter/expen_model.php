<?php

class ExpenModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readExpen()
    {
        $query = "SELECT PC.*, PCC.* FROM parameters.tb_expens AS PC 
                  LEFT JOIN parameters.tb_expen_cats AS PCC 
                  ON PC.expen_cat_id = PCC.id";
        
        $result = pg_query($this->conn, $query);
    
        // ตรวจสอบผลลัพธ์ว่าได้ข้อมูลมาหรือไม่
        if ($result) {
            // ดึงข้อมูลทั้งหมดในฐานะอาร์เรย์
            $rows = pg_fetch_all($result);
    
            if ($rows) {
                // สร้างอาร์เรย์ที่จัดกลุ่มตาม PCC.expen_name
                $groupedData = [];
    
                foreach ($rows as $row) {
                    $expenName = $row['expen_name']; // PCC.expen_name
    
                    // ตรวจสอบว่า expen_name นี้มีอยู่ในกลุ่มหรือยัง
                    if (!isset($groupedData[$expenName])) {
                        $groupedData[$expenName] = [
                            'expen_cat' => $expenName,
                            'expens' => []
                        ]; // ถ้ายังไม่มี ให้สร้าง array ใหม่
                    }
    
                    // ใส่รายละเอียดของ expen_cat_id เข้าไปในกลุ่ม
                    $groupedData[$expenName]['expens'][] = [
                        'expen_cat_id' => $row['expen_cat_id'],
                        'expen_cat_name' => $row['expen_cat_name'], // ข้อมูลจาก PCC
                        'expen_id' => $row['expen_id'], // ข้อมูลจาก PC
                        'expen_description' => $row['expen_description'], // ข้อมูลจาก PC
                    ];
                }
    
                // แปลงข้อมูลเป็น JSON และส่งกลับ
                return json_encode([
                    'status' => 'success',
                    'data' => array_values($groupedData) // แปลง array ให้เป็น index-based array
                ]);
            } else {
                return json_encode(["status" => "error", "message" => "No data found"]);
            }
        } else {
            return json_encode(["status" => "error", "message" => "Query failed"]);
        }
    }

    public function readExpenOne($id)
    {
        $query = "SELECT * FROM parameters.tb_expens WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}