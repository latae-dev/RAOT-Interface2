<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class WrdPlanModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllSearch($key, $limit = 10, $offset = 0, $plan_number = '', $start_date = '', $end_date = '', $status = '', $req_user_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_plans WHERE status_plan != 'delete'";
        $params = [];
        $where_clauses = [];

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

        // ✅ req_user_code
        if ($req_user_code !== '') {
            $where_clauses[] = "req_user_code = $" . (count($params) + 1);
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
        $data_sql = "SELECT 
            id, 
            created_at, 
            updated_at, 
            plan_requ_level, 
            plan_number, 
            st_br, 
            st_pv, 
            st_ar, 
            st_hf, 
            req_user_code, 
            province_name, 
            full_name, 
            plan_form_name, 
            st_hf_user_code, 
            st_ar_user_code, 
            st_pv_user_code, 
            st_br_user_code,
            req_user_date,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM withdraws.tb_wrd_expenses e 
                    WHERE e.plan_id = tb_wrd_plans.id AND e.status_expenses = 'active'
                    AND is_type_req = 1 and parent_id IS NULL
                ) THEN 1 ELSE 0 
            END AS has_expenses,
            COALESCE(
                (
                    SELECT e.id
                    FROM withdraws.tb_wrd_expenses e
                    WHERE e.plan_id = tb_wrd_plans.id AND e.status_expenses = 'active'
                    AND is_type_req = 1 and parent_id IS NULL
                    LIMIT 1
                ), 0
            ) AS expenses_id,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM withdraws.tb_wrd_expenses e 
                    WHERE e.plan_id = tb_wrd_plans.id AND e.status_expenses = 'active'
                    AND is_type_req = 2 and parent_id IS NULL
                ) THEN 1 ELSE 0 
            END AS has_expenses_outside,
            COALESCE(
                (
                    SELECT e.id
                    FROM withdraws.tb_wrd_expenses e
                    WHERE e.plan_id = tb_wrd_plans.id AND e.status_expenses = 'active'
                    AND is_type_req = 2 and parent_id IS NULL
                    LIMIT 1
                ), 0
            ) AS out_side_expenses_id,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM withdraws.tb_wrd_compensations e 
                    WHERE e.plan_id = tb_wrd_plans.id AND e.status_compensation = 'active'
                ) THEN 1 ELSE 0 
            END AS has_compensations,
            COALESCE(
                (
                    SELECT c.id
                    FROM withdraws.tb_wrd_compensations c
                    WHERE c.plan_id = tb_wrd_plans.id AND c.status_compensation = 'active'
                    LIMIT 1
                ), 0
            ) AS compensations_id
            " . $base_query;
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

    public function readPlanOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_plans WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง wrd_plan ใหม่
    public function createWrdPlan($data)
    {
        $plan_number = $data['main_data']['plan_number'];
        $plan_requ_level = $data['main_data']['plan_requ_level'];
        $plan_requ_all = $data['main_data']['plan_requ_all'];
        $plan_form_name = $data['main_data']['plan_form_name'];
        $plan_start = $this->format_date_to_db($data['main_data']['plan_start']);
        $plan_end = $this->format_date_to_db($data['main_data']['plan_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $fund = $data['main_data']['fund'];
        $fund_id = $data['main_data']['fund_id']; // Add 31/7/2025 2:34 PM
        if (!empty($data['main_data']['project_id'])) {
            $project = $data['main_data']['project'];
            $project_id = $data['main_data']['project_id'];
        } else {
            $project = null;
            $project_id = null;
        }
        $st_br = $data['main_data']['st_br'];
        $st_br_date = $data['main_data']['st_br_date'];
        $st_br_user_code = $data['main_data']['st_br_user_code'];
        $st_pv = $data['main_data']['st_pv'];
        $st_pv_date = $data['main_data']['st_pv_date'];
        $st_pv_user_code = $data['main_data']['st_pv_user_code'];
        $st_ar = $data['main_data']['st_ar'];
        $st_ar_date = $data['main_data']['st_ar_date'];
        $st_ar_user_code = $data['main_data']['st_ar_user_code'];
        $st_hf = $data['main_data']['st_hf'];
        $st_hf_date = $data['main_data']['st_hf_date'];
        $st_hf_user_code = $data['main_data']['st_hf_user_code'];
        $user_request_code = $data['main_data']['user_request_code'];
        $draft = isset($data['main_data']['draft']) && $data['main_data']['draft'] !== ''
            ? filter_var($data['main_data']['draft'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $day_return = isset($data['main_data']['day_return']) && $data['main_data']['day_return'] !== ''
            ? filter_var($data['main_data']['day_return'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $money_receipt = isset($data['main_data']['money_receipt']) && $data['main_data']['money_receipt'] !== ''
            ? filter_var($data['main_data']['money_receipt'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $note = $data['main_data']['note'];
        $req_user_code = $data['main_data']['req_user_code'];
        $province_name = $data['main_data']['province_name'];
        $status_plan = 'active'; // ค่าเริ่มต้นของ status_plan
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_plans (
                plan_number ,
                plan_requ_level,
                plan_requ_all,
                plan_form_name,
                plan_start,
                plan_end,
                depart_code,
                full_name,
                position_name,
                level_name,
                affiliation_name,
                fund,
                fund_id,
                project,
                project_id,
                st_br,
                st_br_date,
                st_br_user_code,
                st_pv,
                st_pv_date,
                st_pv_user_code,
                st_ar,
                st_ar_date,
                st_ar_user_code,
                st_hf,
                st_hf_date,
                st_hf_user_code,
                user_request_code,
                draft,
                day_return,
                money_receipt,
                note,
                req_user_code,
                province_name,
                status_plan,
                req_user_date,
                areas_id,
                province_id
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32 ,$33 ,$34, $35, NOW(), $36, $37
    ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $plan_requ_level,
            $plan_requ_all,
            $plan_form_name,
            $plan_start,
            $plan_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $st_br,
            $st_br_date,
            $st_br_user_code,
            $st_pv,
            $st_pv_date,
            $st_pv_user_code,
            $st_ar,
            $st_ar_date,
            $st_ar_user_code,
            $st_hf,
            $st_hf_date,
            $st_hf_user_code,
            $user_request_code,
            $draft === null ? 'NULL' : ($draft ? 'TRUE' : 'FALSE'),
            $day_return === null ? 'NULL' : ($day_return ? 'TRUE' : 'FALSE'),
            $money_receipt === null ? 'NULL' : ($money_receipt ? 'TRUE' : 'FALSE'),
            $note,
            $req_user_code,
            $province_name,
            $status_plan,
            $area_id,
            $province_id
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

    public function editWrdPlan($data, $id)
    {
        $plan_start = $this->format_date_to_db($data['main_data']['plan_start']);
        $plan_end = $this->format_date_to_db($data['main_data']['plan_end']);
        $fund = $data['main_data']['fund'];
        $fund_id = $data['main_data']['fund_id'];
        if (!empty($data['main_data']['project_id'])) {
            $project = $data['main_data']['project'];
            $project_id = $data['main_data']['project_id'];
        } else {
            $project = null;
            $project_id = null;
        }
        $day_return = isset($data['main_data']['day_return']) && $data['main_data']['day_return'] !== ''
            ? filter_var($data['main_data']['day_return'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $money_receipt = isset($data['main_data']['money_receipt']) && $data['main_data']['money_receipt'] !== ''
            ? filter_var($data['main_data']['money_receipt'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $note = $data['main_data']['note'];
        $st_hf = 'waiting';

        // สร้างคำสั่ง SQL UPDATE
        $query = "UPDATE withdraws.tb_wrd_plans SET 
                plan_start = $1,
                plan_end = $2,
                fund = $3,
                fund_id = $4,
                project = $5,
                project_id = $6,
                day_return = $7,
                money_receipt = $8,
                note = $9,
                st_hf = $10,
                updated_at = NOW(),
                req_user_date = NOW()
                WHERE id = $11";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $plan_start,
            $plan_end,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $day_return === null ? 'NULL' : ($day_return ? 'TRUE' : 'FALSE'),
            $money_receipt === null ? 'NULL' : ($money_receipt ? 'TRUE' : 'FALSE'),
            $note,
            $st_hf,
            $id
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return true;
    }

    public function deleteWrdStatus($id)
    {
        // สถานะที่ต้องการอัปเดต
        $status = 'delete';

        // SQL ที่ใช้ parameterized query อย่างถูกต้อง
        $queryUpdate = "UPDATE withdraws.tb_wrd_plans SET status_plan = $1, updated_at = $2 WHERE id = $3";

        // ค่าพารามิเตอร์
        $params = [
            $status,
            date("Y-m-d H:i:s"), // หรือ date("Y-m-d H:i:s") ถ้าเป็น timestamp
            $id
        ];

        // ทำการอัปเดตข้อมูล
        $resultUpdate = pg_query_params($this->conn, $queryUpdate, $params);

        // ตรวจสอบผลลัพธ์
        if (!$resultUpdate) {
            return json_encode([
                'status' => 'error',
                'message' => 'Failed to update status plan: ' . pg_last_error($this->conn)
            ]);
        }

        return json_encode([
            'status' => 'success',
            'message' => 'Status plan updated successfully.'
        ]);
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

    public function updateWrdPlanApprove($data, $id)
    {
        if ($data['main_data']['approve'] == 1) {
            $st_hf = 'approve';
        }
        if ($data['main_data']['reject'] == 1) {
            $st_hf = 'reject';
        }
        if ($data['main_data']['check'] == 1) {
            $st_hf = 'finance_check';
        }
        if ($data['main_data']['reject_check'] == 1) {
            $st_hf = 'cho_reject';
        }
        if ($data['main_data']['check_and_approve'] == 1) {
            $st_hf = 'approve';
        }
        $st_hf_user_code = $data['main_data']['user_code'];

        // สร้างคำสั่ง SQL UPDATE
        $query = "UPDATE withdraws.tb_wrd_plans SET 
                st_hf = $1,
                st_hf_user_code = $2,
                st_hf_date = NOW(),
                updated_at = NOW()
                WHERE id = $3";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $st_hf,
            $st_hf_user_code,
            $id
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return true;
    }

    public function readAllPersons($depart_code)
    {
        $query = "SELECT req_user_code,full_name 
            FROM withdraws.tb_wrd_plans
            where status_plan = 'active'
            and depart_code = $1
            group by req_user_code,full_name";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array($depart_code));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return pg_fetch_all($result);
    }

    public function readAllPersonsReport($depart_code)
    {
        $query = "SELECT req_user_code,full_name 
            FROM withdraws.tb_wrd_plans
            where status_plan = 'active'";
        if ($_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            $query .= " and depart_code = $1";
            $data = [$depart_code];
        } else {
            $data = [];
        }
        $query .= " group by req_user_code,full_name";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, $data);

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return pg_fetch_all($result);
    }
}
