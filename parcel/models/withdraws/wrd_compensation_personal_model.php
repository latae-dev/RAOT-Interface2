<?php
class WrdCompensationPersonalModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readCompensationPersonalOne($id)
    {
        $query = "SELECT *
        FROM withdraws.tb_wrd_compensation_personal 
        INNER JOIN parameters.tb_taxpayer_info on tb_taxpayer_info.id = tb_wrd_compensation_personal.personal_id
        WHERE compensation_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createCompensationPersonal($data, $return_id)
    {
        $wrd_compensation_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO withdraws.tb_wrd_compensation_personal (
            compensation_id,
            personal_id,
            personal_bath,
            personal_stang,
            created_at,
            updated_at
        ) VALUES ($1, $2, $3, $4, NOW(), NOW())";

        foreach ($data['personal_data'] as $mat) {

            $compensation_id = $wrd_compensation_id;
            $personal_id = $mat['personal_id'];
            $bath = $mat['personal_bath'];
            $stang = $mat['personal_stang'];

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $compensation_id,
                $personal_id,
                $bath,
                $stang,
            ));


            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function editCompensationPersonal($data, $id)
    {
        // ลบข้อมูลเก่าทั้งหมดที่เกี่ยวข้องกับ wrd_compensation_id
        $delete_query = "DELETE FROM withdraws.tb_wrd_compensation_personal WHERE compensation_id = $1";
        $delete_result = pg_query_params($this->conn, $delete_query, array($id));

        if (!$delete_result) {
            return false;
        }

        // เพิ่มข้อมูลใหม่
        $insert_query = "INSERT INTO withdraws.tb_wrd_compensation_personal (
            compensation_id,
            personal_id,
            personal_bath,
            personal_stang,
            created_at,
            updated_at
        ) VALUES ($1, $2, $3, $4, NOW(), NOW())";

        foreach ($data['personal_data'] as $item) {
            $params = array(
                $id,
                $item['personal_id'],
                $item['personal_bath'],
                $item['personal_stang']
            );

            $insert_result = pg_query_params($this->conn, $insert_query, $params);

            if (!$insert_result) {
                return false;
            }
        }

        return true;
    }
}
