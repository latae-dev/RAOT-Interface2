<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class WrdExpensesApproveHistoryModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllSearch($key, $limit = 10, $offset = 0, $plan_number = '', $start_date = '', $end_date = '', $status = '', $depart_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_plans WHERE status_plan != 'delete'";
        $params = [];
        $where_clauses = [];

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
            $where_clauses[] = "updated_at::date >= $" . (count($params) + 1) . " AND updated_at::date <= $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        } else {
            if ($start_date !== '') {
                $where_clauses[] = "updated_at::date >= $" . (count($params) + 1);
                $params[] = $start_date;
            }
            if ($end_date !== '') {
                $where_clauses[] = "updated_at::date <= $" . (count($params) + 1);
                $params[] = $end_date;
            }
        }

        // ✅ depart_code
        if ($depart_code !== '') {
            $where_clauses[] = "depart_code = $" . (count($params) + 1);
            $params[] = $depart_code;
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
        $data_sql = "SELECT id, created_at, updated_at, plan_requ_level, plan_number, st_br, st_pv, st_ar, st_hf, req_user_code, province_name, full_name, plan_form_name, st_hf_user_code, st_ar_user_code, st_pv_user_code, st_br_user_code " . $base_query;
        $data_sql .= " ORDER BY updated_at DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
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
        $query = "SELECT * FROM withdraws.tb_wrd_expenses_approve_history approve
        JOIN users.tb_users as users ON users.user_code = approve.user_code
        WHERE expenses_id = $1 
        order by approve.approve_date asc";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง Logs History
    public function createApproveLogs($data, $id, $position_code)
    {
        $map = [
            'APHO' => [
                'approve' => 'finance_approve',
                'reject'  => 'finance_reject'
            ],
            'APHOP' => [
                'approve' => 'audit_approve',
                'reject'  => 'audit_reject'
            ],
        ];

        if (!isset($map[$position_code])) {
            return false; // ถ้าไม่ใช่ 2 ตำแหน่งนี้ return false เลย
        }

        $status = 'waiting';
        if (!empty($data['main_data']['approve'])) {
            $status = $map[$position_code]['approve'];
        } elseif (!empty($data['main_data']['reject'])) {
            $status = $map[$position_code]['reject'];
        }

        $user_code   = $data['main_data']['user_code'] ?? null;
        $remark      = $data['main_data']['remark'] ?? null;
        $approve_date = date('Y-m-d H:i:s');
        $now = date('Y-m-d H:i:s');

        $query = "INSERT INTO withdraws.tb_wrd_expenses_approve_history (
            expenses_id,
            user_code,
            approve_date,
            approve_status,
            created_at,
            updated_at,
            remark
        ) VALUES ($1, $2, now(), $3, now(), now(), $4)
        RETURNING id";

        $result = pg_query_params($this->conn, $query, [
            $id,
            $user_code,
            $status,
            $remark
        ]);

        if (!$result) {
            error_log("createApproveLogs failed: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_result($result, 0, 'id');
    }
    
    public function createApproveLogsMulti($data, $id, $position_code)
    {
        $map = [
            'APHO' => [
                'approve' => 'finance_approve',
                'reject'  => 'finance_reject'
            ],
            'APHOP' => [
                'approve' => 'audit_approve',
                'reject'  => 'audit_reject'
            ],
        ];

        if (!isset($map[$position_code])) {
            return false; // ถ้าไม่ใช่ 2 ตำแหน่งนี้ return false เลย
        }

        $status = 'waiting';
        if (!empty($data['main_data']['approve'])) {
            $status = $map[$position_code]['approve'];
        } elseif (!empty($data['main_data']['reject'])) {
            $status = $map[$position_code]['reject'];
        }

        $user_code   = $data['main_data']['user_code'] ?? null;
        $remark      = $data['main_data']['remark'] ?? null;
        $approve_date = date('Y-m-d H:i:s');
        $now = date('Y-m-d H:i:s');

        $query = "INSERT INTO withdraws.tb_wrd_expenses_approve_history (
            expenses_id,
            user_code,
            approve_date,
            approve_status,
            created_at,
            updated_at,
            remark
        ) VALUES ($1, $2, now(), $3, now(), now(), $4)
        RETURNING id";

        $result = pg_query_params($this->conn, $query, [
            $id,
            $user_code,
            $status,
            $remark
        ]);

        if (!$result) {
            error_log("createApproveLogs failed: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_result($result, 0, 'id');
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
}
