<?php
class PlanListModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readPlanListOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_plan_lists WHERE wrd_plan_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));
    
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
    
        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createPlanList($data, $return_id)
    {

        $wrd_plan_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO withdraws.tb_wrd_plan_lists (
            wrd_plan_id,
            wrd_mat_id,
            wrd_mat_code,
            wrd_mat_name,
            qty,
            count_unit_id,
            count_unit_code,
            count_unit_name,
            price,
            total,
            days
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";

        foreach ($data['child_data'] as $mat) {

            $wrd_mat_id = $mat['wrd_mat_id'];
            $wrd_mat_code = $mat['wrd_mat_code'];
            $wrd_mat_name = $mat['wrd_mat_name'];
            $qty = $mat['qty'];
            $count_unit_id = $mat['count_unit_id'];
            $count_unit_code = $mat['count_unit_code'];
            $count_unit_name = $mat['count_unit_name'];
            $price = $mat['price'];
            $total = $mat['total'];
            $days = $mat['days'];

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $wrd_plan_id,
                $wrd_mat_id,
                $wrd_mat_code,
                $wrd_mat_name,
                $qty,
                $count_unit_id,
                $count_unit_code,
                $count_unit_name,
                $price,
                $total,
                $days
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function editPlanList($data, $id)
    {
        // ลบข้อมูลเก่าทั้งหมดที่เกี่ยวข้องกับ wrd_plan_id
        $delete_query = "DELETE FROM withdraws.tb_wrd_plan_lists WHERE wrd_plan_id = $1";
        $delete_result = pg_query_params($this->conn, $delete_query, array($id));
        
        if (!$delete_result) {
            return false;
        }

        // เพิ่มข้อมูลใหม่
        $insert_query = "INSERT INTO withdraws.tb_wrd_plan_lists (
            wrd_plan_id,
            wrd_mat_id,
            wrd_mat_code,
            wrd_mat_name,
            qty,
            count_unit_id,
            count_unit_code,
            count_unit_name,
            price,
            total,
            days
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";

        foreach ($data['child_data'] as $item) {
            $params = array(
                $id,
                $item['wrd_mat_id'],
                $item['wrd_mat_code'],
                $item['wrd_mat_name'],
                $item['qty'],
                $item['count_unit_id'],
                $item['count_unit_code'],
                $item['count_unit_name'],
                $item['price'],
                $item['total'],
                $item['days']
            );

            $insert_result = pg_query_params($this->conn, $insert_query, $params);
            
            if (!$insert_result) {
                return false;
            }
        }

        return true;
    }

}
