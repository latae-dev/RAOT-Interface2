<?php

class QuarterlistModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll($limit = 10, $offset = 0)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parameters.tb_quarter_conf WHERE 1=1";
        $result_count = pg_query_params($this->conn, $query_count, array());

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ดึงข้อมูลหลักพร้อมแบ่งหน้า
        $query = "
        SELECT * 
        FROM parameters.tb_quarter_conf 
        WHERE 1=1
        ORDER BY quarter_code ASC
        LIMIT $1 OFFSET $2";

        $result = pg_query_params($this->conn, $query, [$limit, $offset]);

        if ($result) {
            $rows = pg_fetch_all($result) ?: []; // ถ้าไม่มีข้อมูลให้คืนค่าเป็นอาร์เรย์ว่าง
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
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

    public function updateQuarter($data, $id)
    {

        // คำสั่ง SQL สำหรับ update status
        $query = "UPDATE parameters.tb_quarter_conf SET quarter_date_start = $2 , quarter_date_end = $3 WHERE quarter_code = $1";
        $result = pg_query_params($this->conn, $query, [$id,$data['quarter_date_start'],$data['quarter_date_end']]);

        return $result;
    }
}
