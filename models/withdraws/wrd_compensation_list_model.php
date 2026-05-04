<?php
class WrdCompensationListModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readCompensationListOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_compensation_lists WHERE compensation_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createCompensationList($data, $return_id)
    {
        $wrd_compensation_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO withdraws.tb_wrd_compensation_lists (
            compensation_id,
            detail,
            bath,
            stang,
            created_at,
            updated_at,
            mat_id,
            mat_name,
            mat_code
        ) VALUES ($1, $2, $3, $4, NOW(), NOW(), $5, $6, $7)";

        foreach ($data['detail_data'] as $mat) {

            $compensation_id = $wrd_compensation_id;
            $detail = $mat['detail_list'];
            $bath = $mat['detail_bath'];
            $stang = $mat['detail_stang'];

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $compensation_id,
                $detail,
                $bath,
                $stang,
                $mat['mat_id'],
                $mat['mat_name'],
                $mat['select_mat_code']
            ));


            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function editCompensationList($data, $id)
    {
        // ลบข้อมูลเก่าทั้งหมดที่เกี่ยวข้องกับ wrd_compensation_id
        $delete_query = "DELETE FROM withdraws.tb_wrd_compensation_lists WHERE compensation_id = $1";
        $delete_result = pg_query_params($this->conn, $delete_query, array($id));

        if (!$delete_result) {
            return false;
        }

        // เพิ่มข้อมูลใหม่
        $insert_query = "INSERT INTO withdraws.tb_wrd_compensation_lists (
            compensation_id,
            detail,
            bath,
            stang,
            created_at,
            updated_at,
            mat_id,
            mat_name,
            mat_code
        ) VALUES ($1, $2, $3, $4, NOW(), NOW(), $5, $6, $7)";

        foreach ($data['detail_data'] as $item) {
            $params = array(
                $id,
                $item['detail_list'],
                $item['detail_bath'],
                $item['detail_stang'],
                $item['mat_id'],
                $item['mat_name'],
                $item['select_mat_code']
            );

            $insert_result = pg_query_params($this->conn, $insert_query, $params);

            if (!$insert_result) {
                return false;
            }
        }

        return true;
    }
}
