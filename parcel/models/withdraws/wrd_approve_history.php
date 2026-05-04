<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class WrdApproveHistoryModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllSearch($key, $limit = 10, $offset = 0, $plan_number = '', $start_date = '', $end_date = '', $status = '', $depart_code = '', $req_user_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_plans ";
        if ($_SESSION['user_data']['withdraws']['head_office_id'] != 'N/A') {
            $base_query .= "INNER JOIN users.tb_users ON users.tb_users.user_code = withdraws.tb_wrd_plans.req_user_code and tb_users.head_office_id = '" . $_SESSION['user_data']['withdraws']['head_office_id'] . "' ";
        }
        $base_query .= "WHERE status_plan != 'delete'";
        $params = [];
        $where_clauses = [];

        if ($_SESSION['user_data']['withdraws']['area_id'] != 'N/A' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $where_clauses[] = "tb_wrd_plans.areas_id = $" . (count($params) + 1);
            $params[] = $_SESSION['user_data']['withdraws']['area_id'];
        }

        if ($_SESSION['user_data']['withdraws']['province_id'] != 'N/A' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $where_clauses[] = "tb_wrd_plans.province_id = $" . (count($params) + 1);
            $params[] = $_SESSION['user_data']['withdraws']['province_id'];
        }

        // ✅ ตรวจสอบ key และ status
        $allowed_keys = ['st_br', 'st_pv', 'st_ar', 'st_hf'];
        if ($key !== '' && $status !== '' && in_array($key, $allowed_keys)) {
            // $where_clauses[] = "$key = $" . (count($params) + 1);
            // $params[] = $status;
            $status = explode(",", $status);
            $text = '';
            $text = "(";
            foreach ($status as $i => $value) {
                if ($i == 0) {
                    $text .= "$key = $" . (count($params) + 1) . "";
                } else {
                    $text .= " OR $key = $" . (count($params) + 1) . "";
                }
                $params[] = $value;
            }
            $text .= ")";
            $where_clauses[] = $text;
        }

        // ✅ plan_number
        if ($plan_number !== '') {
            $where_clauses[] = "plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }

        // ✅ วันที่
        if ($start_date !== '' && $end_date !== '') {
            // $where_clauses[] = "created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $where_clauses[] = "req_user_date::date >= $" . (count($params) + 1) . " AND req_user_date::date <= $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        } else {
            if ($start_date !== '') {
                $where_clauses[] = "req_user_date::date >= $" . (count($params) + 1);
                $params[] = $start_date;
            }
            if ($end_date !== '') {
                $where_clauses[] = "req_user_date::date <= $" . (count($params) + 1);
                $params[] = $end_date;
            }
        }

        // ✅ depart_code
        if ($depart_code !== '' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $where_clauses[] = "tb_wrd_plans.depart_code = $" . (count($params) + 1);
            $params[] = $depart_code;
        }

        // ✅ req_user_code
        if ($req_user_code !== '') {
            $where_clauses[] = "tb_wrd_plans.req_user_code = $" . (count($params) + 1);
            $params[] = $req_user_code;
        }

        // รวมเงื่อนไข WHERE
        if (!empty($where_clauses)) {
            $base_query .= " AND " . implode(" AND ", $where_clauses);
        }

        // ✅ Query นับจำนวน
        $count_sql = "SELECT COUNT(*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_plans.id, tb_wrd_plans.created_at, tb_wrd_plans.updated_at, plan_requ_level, plan_number, st_br, st_pv, st_ar, st_hf, req_user_code, province_name, full_name, plan_form_name, st_hf_user_code, st_ar_user_code, st_pv_user_code, st_br_user_code, req_user_date " . $base_query;
        $data_sql .= " ORDER BY req_user_date DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $data_sql, $params);
        if (!$result) {
            return ["status" => "error", "message" => "Data query failed: " . pg_last_error($this->conn)];
        }

        $rows = pg_fetch_all($result) ?: [];

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
    }

    public function readApproveOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_approve_history approve
        JOIN users.tb_users as users ON users.user_code = approve.user_code
        WHERE plan_id = $1 
        order by approve.approve_date asc";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง wrd_plan ใหม่
    public function createApproveLogs($data, $id, $info)
    {
        if ($data['main_data']['approve'] == 1) {
            $status = 'approve';
        }
        if ($data['main_data']['reject'] == 1) {
            $status = 'reject';
        }
        if ($data['main_data']['check'] == 1) {
            $status = 'finance_check';
        }
        if ($data['main_data']['reject_check'] == 1) {
            $status = 'cho_reject';
        }
        if ($data['main_data']['check_and_approve'] == 1) {
            $status = 'approve';
        }
        $user_code = $data['main_data']['user_code'];
        $remark = $data['main_data']['remark'];
        if ($data['main_data']['check_and_approve'] == 1) {
            if ($info['st_hf'] != 'finance_check') {
                $checkQuery = "INSERT INTO withdraws.tb_wrd_approve_history (
                plan_id,
                user_code,
                approve_date,
                approve_status,
                created_at,
                updated_at,
                remark
            ) VALUES (
                $1, $2, NOW(), $3, NOW(), NOW(), $4
            ) RETURNING id";
                $checkResult = pg_query_params($this->conn, $checkQuery, array(
                    $id,
                    $user_code,
                    'finance_check',
                    $remark
                ));
                if (!$checkResult) {
                    echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                    return false;
                }
            }
        }

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_approve_history (
            plan_id,
            user_code,
            approve_date,
            approve_status,
            created_at,
            updated_at,
            remark
        ) VALUES (
            $1, $2, NOW(), $3, NOW(), NOW(), $4
        ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $id,
            $user_code,
            $status,
            $remark
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงค่า wrd_plan_number ที่เพิ่ง insert
        $return_id = pg_fetch_result($result, 0, 'id');

        return $return_id; // คืนค่า wrd_plan_number
    }

    // แปลงวันที่จากรูปแบบ 'd-m-Y H:i' เป็น 'Y-m-d H:i:s'
    public function format_date_to_db($date)
    {
        $parts = explode(' ', $date);
        if (count($parts) !== 2) return null;
        list($dmy, $hm) = $parts;
        $dmy_parts = explode('-', $dmy);
        if (count($dmy_parts) !== 3) return null;
        $day = (int)$dmy_parts[0];
        $month = (int)$dmy_parts[1];
        $year = (int)$dmy_parts[2];
        if ($year > 2400) {
            $year -= 543;
        }
        $dateTime = DateTime::createFromFormat('Y-m-d H:i', sprintf('%04d-%02d-%02d %s', $year, $month, $day, $hm));
        if (!$dateTime) return null;

        return $dateTime->format('Y-m-d H:i:s');
    }

    public function readReportSearch($key, $limit = 10, $offset = 0, $plan_number = '', $start_date = '', $end_date = '', $status = '', $depart_code = '', $req_user_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_plans ";
        if ($_SESSION['user_data']['withdraws']['head_office_id'] != 'N/A') {
            $base_query .= "LEFT JOIN users.tb_users ON users.tb_users.user_code = withdraws.tb_wrd_plans.req_user_code and tb_users.head_office_id = '" . $_SESSION['user_data']['withdraws']['head_office_id'] . "' ";
        }
        $base_query .= "WHERE status_plan != 'delete'";
        $params = [];
        $where_clauses = [];

        if ($_SESSION['user_data']['withdraws']['area_id'] != 'N/A' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $where_clauses[] = "tb_wrd_plans.areas_id = $" . (count($params) + 1);
            $params[] = $_SESSION['user_data']['withdraws']['area_id'];
        }

        if ($_SESSION['user_data']['withdraws']['province_id'] != 'N/A' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $where_clauses[] = "tb_wrd_plans.province_id = $" . (count($params) + 1);
            $params[] = $_SESSION['user_data']['withdraws']['province_id'];
        }

        // ✅ ตรวจสอบ key และ status
        $allowed_keys = ['st_br', 'st_pv', 'st_ar', 'st_hf'];
        if ($key !== '' && $status !== '' && in_array($key, $allowed_keys)) {
            $where_clauses[] = "$key = $" . (count($params) + 1);
            $params[] = $status;
        }

        // ✅ plan_number
        if ($plan_number !== '') {
            $where_clauses[] = "plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }

        // ✅ วันที่
        if ($start_date !== '' && $end_date !== '') {
            // $where_clauses[] = "created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $where_clauses[] = "req_user_date::date >= $" . (count($params) + 1) . " AND req_user_date::date <= $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        } else {
            if ($start_date !== '') {
                $where_clauses[] = "req_user_date::date >= $" . (count($params) + 1);
                $params[] = $start_date;
            }
            if ($end_date !== '') {
                $where_clauses[] = "req_user_date::date <= $" . (count($params) + 1);
                $params[] = $end_date;
            }
        }

        // ✅ depart_code
        if ($depart_code !== '') {
            $where_clauses[] = "tb_wrd_plans.depart_code = $" . (count($params) + 1);
            $params[] = $depart_code;
        }

        // ✅ req_user_code
        if ($req_user_code !== '') {
            $where_clauses[] = "tb_wrd_plans.req_user_code = $" . (count($params) + 1);
            $params[] = $req_user_code;
        }

        // รวมเงื่อนไข WHERE
        if (!empty($where_clauses)) {
            $base_query .= " AND " . implode(" AND ", $where_clauses);
        }

        // ✅ Query นับจำนวน
        $count_sql = "SELECT COUNT(*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_plans.*,
            (
                SELECT COALESCE((
                    SELECT sum(e.expenses_total)
                    FROM withdraws.tb_wrd_expenses e
                    WHERE e.plan_id = tb_wrd_plans.id
                    AND e.is_type_req = 1
                    AND e.status_expenses = 'active'
                    LIMIT 1
                ), 0)
            ) AS expenses_inside_total,
            (
                SELECT COALESCE((
                    SELECT sum(e.expenses_total)
                    FROM withdraws.tb_wrd_expenses e
                    WHERE e.plan_id = tb_wrd_plans.id
                    AND e.is_type_req = 2
                    AND e.status_expenses = 'active'
                    LIMIT 1
                ), 0)
            ) AS expenses_outside_total,
            (
                SELECT COALESCE((
                    SELECT c.id
                    FROM withdraws.tb_wrd_compensations c
                    WHERE c.plan_id = tb_wrd_plans.id
                    AND c.status_compensation = 'active'
                    LIMIT 1
                ), 0)
            ) AS compensations_id " . $base_query;
        $data_sql .= " ORDER BY req_user_date DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $data_sql, $params);
        if (!$result) {
            return ["status" => "error", "message" => "Data query failed: " . pg_last_error($this->conn)];
        }

        $rows = pg_fetch_all($result) ?: [];

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
    }
}
