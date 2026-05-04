<?php

class BusinessTypeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readBusinessTypes()
    {
        $query = "SELECT id, busa_code, busa_description FROM parameters.tb_busa_master ORDER BY busa_code ASC";
        $result = pg_query($this->conn, $query);

        // ตรวจสอบผลลัพธ์ว่าได้ข้อมูลมาหรือไม่
        if ($result) {
            // ดึงข้อมูลทั้งหมดในฐานะอาร์เรย์
            $rows = pg_fetch_all($result);

            if ($rows) {
                return $rows; // คืนค่าข้อมูลทั้งหมดที่ได้
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    public function readBusinessTypeOne($id)
    {
        $query = "SELECT * FROM parameters.tb_busa_master WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function getForDropdown()
    {
        $query = "SELECT id, busa_code, busa_description FROM parameters.tb_busa_master 
                  ORDER BY busa_code ASC";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result);
        return $data;
    }

    public function countAll()
    {
        $query = "SELECT COUNT(*) FROM parameters.tb_busa_master";
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        $count = pg_fetch_result($result, 0, 0);
        return $count;
    }
}
