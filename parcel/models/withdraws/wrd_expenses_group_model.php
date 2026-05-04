<?php
class ExpensesGroupModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readGroupOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_expenses_groups WHERE expenses_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createGroup($data, $return_id)
    {

        $expenses_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO withdraws.tb_wrd_expenses_groups (
            expenses_id,
            fullname,
            position,
            allowance,
            allowance_days,
            total_allowance,
            accommodation,
            accommodation_days,
            accommodation_total,
            transport,
            other_expenses,
            meal,
            meal_count,
            meal_total,
            sum,
            created_at,
            updated_at,
            id_card
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12,$13, $14, $15, $16, $17, $18)";

        foreach ($data['group_data'] as $mat) {

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $expenses_id,
                empty($mat['name']) ? null : $mat['name'],
                empty($mat['position']) ? null : $mat['position'],
                empty($mat['allowance']) ? null : $mat['allowance'],
                empty($mat['days']) ? null : $mat['days'],
                empty($mat['total_allowance']) ? null : $mat['total_allowance'],
                empty($mat['accommodation']) ? null : $mat['accommodation'],
                empty($mat['accommodation_days']) ? null : $mat['accommodation_days'],
                empty($mat['accommodation_total']) ? null : $mat['accommodation_total'],
                empty($mat['transport']) ? null : $mat['transport'],
                empty($mat['other_expenses']) ? null : $mat['other_expenses'],
                empty($mat['meal']) ? null : $mat['meal'],
                empty($mat['meal_count']) ? null : $mat['meal_count'],
                empty($mat['meal_total']) ? null : $mat['meal_total'],
                empty($mat['sum']) ? null : $mat['sum'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                empty($mat['group_id_card']) ? null : $mat['group_id_card']
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function editGroup($data, $id)
    {
        // ลบข้อมูลเก่าทั้งหมดที่เกี่ยวข้องกับ expenses_id
        $delete_query = "DELETE FROM withdraws.tb_wrd_expenses_groups WHERE expenses_id = $1";
        $delete_result = pg_query_params($this->conn, $delete_query, array($id));

        if (!$delete_result) {
            return false;
        }

        // เพิ่มข้อมูลใหม่
        $query = "INSERT INTO withdraws.tb_wrd_expenses_groups (
            expenses_id,
            fullname,
            position,
            allowance,
            allowance_days,
            total_allowance,
            accommodation,
            accommodation_days,
            accommodation_total,
            transport,
            other_expenses,
            meal,
            meal_count,
            meal_total,
            sum,
            created_at,
            updated_at,
            id_card
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12,$13, $14, $15, $16, $17, $18)";

        foreach ($data['group_data'] as $mat) {

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $id,
                empty($mat['name']) ? null : $mat['name'],
                empty($mat['position']) ? null : $mat['position'],
                empty($mat['allowance']) ? null : $mat['allowance'],
                empty($mat['days']) ? null : $mat['days'],
                empty($mat['total_allowance']) ? null : $mat['total_allowance'],
                empty($mat['accommodation']) ? null : $mat['accommodation'],
                empty($mat['accommodation_days']) ? null : $mat['accommodation_days'],
                empty($mat['accommodation_total']) ? null : $mat['accommodation_total'],
                empty($mat['transport']) ? null : $mat['transport'],
                empty($mat['other_expenses']) ? null : $mat['other_expenses'],
                empty($mat['meal']) ? null : $mat['meal'],
                empty($mat['meal_count']) ? null : $mat['meal_count'],
                empty($mat['meal_total']) ? null : $mat['meal_total'],
                empty($mat['sum']) ? null : $mat['sum'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                empty($mat['group_id_card']) ? null : $mat['group_id_card']
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;
    }
}
