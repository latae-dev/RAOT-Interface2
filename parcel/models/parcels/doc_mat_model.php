<?php
class DocMatModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_doc_mats
    public function readDocMat()
    {
        $query = "SELECT * FROM parcels.tb_doc_mats";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูลของ doc_mat หนึ่งรายการ
    public function readDocMatOne($id)
    {
        $query = "SELECT * FROM parcels.tb_doc_mats WHERE doc_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงข้อมูลทั้งหมดจากผลลัพธ์ในรูปแบบของอาร์เรย์
        $data = [];
        while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $data[] = $row; // เก็บแถวในอาร์เรย์
        }

        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง doc_mat ใหม่
    public function createDocMat($data, $return_id)
    {
        if (!isset($data['tb_doc_mats']) || !is_array($data['tb_doc_mats'])) {
            echo "Invalid data format.";
            return false;
        }

        $doc_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO parcels.tb_doc_mats (
            doc_id, mat_code, mat_name, count_unit_name, quarter, quantity, deli_date, plan_mat_id
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

        foreach ($data['tb_doc_mats'] as $mat) {
            // ดึงค่าของแต่ละรายการ
            $mat_code = isset($mat['mat_code']) ? $mat['mat_code'] : null;
            $mat_name = isset($mat['mat_name']) ? $mat['mat_name'] : null;
            $count_unit_name = isset($mat['count_unit_name']) ? $mat['count_unit_name'] : null;
            $quarter = isset($mat['quarter']) ? $mat['quarter'] : 0;
            $quantity = isset($mat['quantity']) ? $mat['quantity'] : 0;
            $deli_date = isset($mat['deli_date']) ? $mat['deli_date'] : null;
            $plan_mat_id = isset($mat['plan_mat_id']) ? $mat['plan_mat_id'] : 0;

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $doc_id,
                $mat_code,
                $mat_name,
                $count_unit_name,
                $quarter,
                $quantity,
                $deli_date,
                $plan_mat_id
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function updateDocMat($data, $id)
    {
        $tb_doc_mats = $data['tb_doc_mats'];          // ข้อมูลใหม่ทั้งหมด
        $deletedIds = $data['deletedIds'];            // ID ที่ต้องลบ
        $dataSelectsOld = $data['dataSelectsOld'];    // ข้อมูลเดิม

        // === 1. ลบข้อมูลและคืนค่า plan_mat ===
        foreach ($deletedIds as $data_loop) {
            $query_doc_mat = "SELECT id, quarter, quantity, plan_mat_id FROM parcels.tb_doc_mats WHERE id = $1";
            $result_doc_mat = pg_query_params($this->conn, $query_doc_mat, [(int)$data_loop]);
            if (!$result_doc_mat || pg_num_rows($result_doc_mat) == 0) continue;

            $doc = pg_fetch_assoc($result_doc_mat);
            $quarter_field = 'quarter_' . $doc['quarter'] . '_use';
            $plan_mat_id = (int)$doc['plan_mat_id'];

            $plan_query = "SELECT $quarter_field FROM parcels.tb_plan_mats WHERE id = $1";
            $plan_result = pg_query_params($this->conn, $plan_query, [$plan_mat_id]);
            if (!$plan_result || pg_num_rows($plan_result) == 0) continue;

            $plan = pg_fetch_assoc($plan_result);
            $new_use = (int)$plan[$quarter_field] - (int)$doc['quantity'];

            pg_query_params(
                $this->conn,
                "UPDATE parcels.tb_plan_mats SET $quarter_field = $1 WHERE id = $2",
                [$new_use, $plan_mat_id]
            );

            pg_query_params(
                $this->conn,
                "DELETE FROM parcels.tb_doc_mats WHERE id = $1",
                [(int)$doc['id']]
            );
        }

        // === 2. อัปเดตหรือเพิ่ม doc_mat ===
        foreach ($tb_doc_mats as $item) {
            $id = $item['id'];
            $quarter = 'quarter_' . $item['quarter'] . '_use';
            $quantity_new = (int)$item['quantity'];
            $plan_mat_id = (int)$item['plan_mat_id'];

            if ($id) {
                // ค้นหา quantity เดิมจาก dataSelectsOld
                $quantity_old = 0;
                foreach ($dataSelectsOld as $old) {
                    if ($old['id'] == $id) {
                        $quantity_old = (int)$old['quantity'];
                        break;
                    }
                }

                // ดึงค่าปัจจุบันของ plan_mat
                $query_plan = "SELECT $quarter FROM parcels.tb_plan_mats WHERE id = $1";
                $result_plan = pg_query_params($this->conn, $query_plan, [$plan_mat_id]);
                $row_plan = pg_fetch_assoc($result_plan);
                $use_value = (int)$row_plan[$quarter];

                // คำนวณค่าใหม่
                $new_use_value = $use_value - $quantity_old + $quantity_new;

                // DEBUG log
                error_log("UPDATE plan_mat: id=$plan_mat_id, $quarter = $use_value - $quantity_old + $quantity_new = $new_use_value");

                // อัปเดต tb_plan_mats
                $update_plan = "UPDATE parcels.tb_plan_mats SET $quarter = $1 WHERE id = $2";
                pg_query_params($this->conn, $update_plan, [$new_use_value, $plan_mat_id]);

                // อัปเดต tb_doc_mats
                $update_doc = "UPDATE parcels.tb_doc_mats 
                           SET quantity = $1, quarter = $2 
                           WHERE id = $3";
                pg_query_params($this->conn, $update_doc, [$quantity_new, $item['quarter'], $id]);
            }
        }

        return true;
    }
}
