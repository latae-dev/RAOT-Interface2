<?php

class QuarterlistModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readQuarterlist()
    {
        $query = "SELECT * FROM parameters.tb_quarter_conf ORDER BY quarter_code";
        $result = pg_query($this->conn, $query);

        // ตรวจสอบผลลัพธ์ว่าได้ข้อมูลมาหรือไม่
        if ($result) {
            // ดึงข้อมูลทั้งหมดในฐานะอาร์เรย์
            $rows = pg_fetch_all($result);

            if ($rows) {
                return $rows; // คืนค่าข้อมูลทั้งหมดที่ได้
            } else {
                return ["status" => "error", "message" => "No data found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function readQuarterlistOne($id)
    {
        $query = "SELECT * FROM parameters.tb_quarter_conf WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
