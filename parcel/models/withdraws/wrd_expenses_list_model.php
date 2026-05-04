<?php
class ExpensesListModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readListDetail($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_expenses_lists WHERE expenses_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createListDetail($data, $return_id)
    {

        $expenses_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO withdraws.tb_wrd_expenses_lists (
            list_date,
            time_start,
            time_end,
            time_depart,
            time_arrive,
            distance,
            compensation,
            expenses_id,
            remark,
            created_at,
            updated_at,
            compensation_per_km
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";

        foreach ($data['list_data'] as $mat) {

            $date = $this->format_date_to_db($mat['date']);
            $time_start = empty($mat['time_start']) ? null : $mat['time_start'];
            $time_end = empty($mat['time_end']) ? null : $mat['time_end'];
            $time_depart = empty($mat['time_depart']) ? null : $mat['time_depart'];
            $time_arrive = empty($mat['time_arrive']) ? null : $mat['time_arrive'];
            $distance = empty($mat['distance']) ? null : $mat['distance'];
            $compensation = empty($mat['compensation']) ? null : $mat['compensation'];
            $remark = empty($mat['remark']) ? null : $mat['remark'];
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');
            $compensation_per_km = empty($mat['compensation_per_km']) ? null : $mat['compensation_per_km'];

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $date,
                $time_start,
                $time_end,
                $time_depart,
                $time_arrive,
                $distance,
                $compensation,
                $expenses_id,
                $remark,
                $created_at,
                $updated_at,
                $compensation_per_km
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function editListDetail($data, $id)
    {
        // ลบข้อมูลเก่าทั้งหมดที่เกี่ยวข้องกับ expenses_id
        $delete_query = "DELETE FROM withdraws.tb_wrd_expenses_lists WHERE expenses_id = $1";
        $delete_result = pg_query_params($this->conn, $delete_query, array($id));

        if (!$delete_result) {
            return false;
        }

        // เพิ่มข้อมูลใหม่
        $query = "INSERT INTO withdraws.tb_wrd_expenses_lists (
            list_date,
            time_start,
            time_end,
            time_depart,
            time_arrive,
            distance,
            compensation,
            expenses_id,
            remark,
            created_at,
            updated_at,
            compensation_per_km
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";

        foreach ($data['list_data'] as $mat) {

            $date = $this->format_date_to_db($mat['date']);
            $time_start = empty($mat['time_start']) ? null : $mat['time_start'];
            $time_end = empty($mat['time_end']) ? null : $mat['time_end'];
            $time_depart = empty($mat['time_depart']) ? null : $mat['time_depart'];
            $time_arrive = empty($mat['time_arrive']) ? null : $mat['time_arrive'];
            $distance = empty($mat['distance']) ? null : $mat['distance'];
            $compensation = empty($mat['compensation']) ? null : $mat['compensation'];
            $remark = empty($mat['remark']) ? null : $mat['remark'];
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');
            $compensation_per_km = empty($mat['compensation_per_km']) ? null : $mat['compensation_per_km'];

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $date,
                $time_start,
                $time_end,
                $time_depart,
                $time_arrive,
                $distance,
                $compensation,
                $id,
                $remark,
                $created_at,
                $updated_at,
                $compensation_per_km
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;
    }

    public function format_date_to_db($date)
    {
        if (empty($date)) return null;

        // ถ้ามาแบบ YYYY-MM-DD (ISO)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // แยกวันที่และเวลา
        $parts = explode(' ', $date);
        $dmy = $parts[0];
        $dmy_parts = explode('-', $dmy);
        if (count($dmy_parts) !== 3) return null;

        $day   = (int)$dmy_parts[0];
        $month = (int)$dmy_parts[1];
        $year  = (int)$dmy_parts[2];
        if ($year > 2400) {
            $year -= 543;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
        return $dateTime ? $dateTime->format('Y-m-d') : null;
    }

}
