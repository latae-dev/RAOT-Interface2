<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class WrdCompensationModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllSearch($key, $limit = 10, $offset = 0, $compensation_number = '', $start_date = '', $end_date = '', $status = '', $req_user_code = '', $req_position_code = '', $req_depart_code = '', $search_user_code = '', $action = '', $is_type = '')
    {
        $base_query = "FROM withdraws.tb_wrd_compensations ";
        if ($_SESSION['user_data']['withdraws']['head_office_id'] != 'N/A') {
            $base_query .= "INNER JOIN users.tb_users ON users.tb_users.user_code = withdraws.tb_wrd_compensations.req_user_code and tb_users.head_office_id = '" . $_SESSION['user_data']['withdraws']['head_office_id'] . "' ";
        }
        $base_query .= " WHERE status_compensation != 'delete'";
        $params = [];
        $where_clauses = [];

        // ✅ ตรวจสอบ key และ status
        $allowed_keys = ['approve_status'];
        if ($key !== '' && $status !== '' && in_array($key, $allowed_keys)) {
            $status = explode(",", $status);
            $text = '';
            $text = "(";
            foreach ($status as $i => $value) {
                if ($value == 'audit_approve') {
                    if ($i == 0) {
                        $text .= "$key = $" . (count($params) + 1) . " OR $key = 'finance_check'";
                    } else {
                        $text .= " OR $key = $" . (count($params) + 1) . " OR $key = 'finance_check'";
                    }
                } else if ($value == 'audit_reject') {
                    if ($i == 0) {
                        $text .= "$key = $" . (count($params) + 1) . " OR $key = 'cho_reject'";
                    } else {
                        $text .= " OR $key = $" . (count($params) + 1) . " OR $key = 'cho_reject'";
                    }
                } else {
                    if ($i == 0) {
                        $text .= "$key = $" . (count($params) + 1) . "";
                    } else {
                        $text .= " OR $key = $" . (count($params) + 1) . "";
                    }
                }
                $params[] = $value;
            }
            $text .= ")";
            $where_clauses[] = $text;
        }

        if ($action != 'compensation_approvel_search') {
            if ($is_type !== '') {
                if ($is_type == 1) {
                    $where_clauses[] = "plan_id is NOT NULL";
                } else if ($is_type == 2) {
                    $where_clauses[] = "plan_id is NULL";
                }
            }
        }

        // ✅ compensation_number
        if ($compensation_number !== '') {
            $where_clauses[] = "compensation_number = $" . (count($params) + 1);
            $params[] = $compensation_number;
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
            if ($action != 'compensation_approvel_search') {
                $where_clauses[] = "req_user_code = $" . (count($params) + 1);
                $params[] = $req_user_code;
            } else {
                if (!array_intersect($_SESSION['user_data']['position_list_id'], [5, 6, 12, 13])) {
                    $where_clauses[] = "req_user_code = $" . (count($params) + 1);
                    $params[] = $req_user_code;
                }
            }
        }

        if ($req_depart_code !== '' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            if ($req_position_code == 'APHOP' && $req_position_code == 'APHO') {
                $where_clauses[] = "req_depart_code = $" . (count($params) + 1);
                $params[] = $req_depart_code;
            }
            if ($_SESSION['user_data']['withdraws']['area_id'] != 'N/A') {
                $where_clauses[] = "areas_id = $" . (count($params) + 1);
                $params[] = $_SESSION['user_data']['withdraws']['area_id'];
            }

            if ($_SESSION['user_data']['withdraws']['province_id'] != 'N/A') {
                $where_clauses[] = "province_id = $" . (count($params) + 1);
                $params[] = $_SESSION['user_data']['withdraws']['province_id'];
            }
        }

        if ($search_user_code !== '') {
            $where_clauses[] = "req_user_code = $" . (count($params) + 1);
            $params[] = $search_user_code;
        }

        // รวมเงื่อนไข WHERE
        if (!empty($where_clauses)) {
            $base_query .= " AND " . implode(" AND ", $where_clauses);
        }

        // ✅ Query นับจำนวน
        $count_sql = "SELECT COUNT(tb_wrd_compensations.*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_compensations.* ,
                        COALESCE(
                            (
                                SELECT e.expenses_number
                                FROM withdraws.tb_wrd_expenses e
                                WHERE e.id = tb_wrd_compensations.expenses_id AND e.status_expenses = 'active'
                                LIMIT 1
                            )
                        ) AS expenses_number
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

    public function readWrdCompensationOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_compensations WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง wrd_compensation ใหม่
    public function createWrdCompensation($data)
    {
        $fund = $data['main_data']['fund'] ?? null;
        $fund_id = !empty($data['main_data']['fund_id']) ? $data['main_data']['fund_id'] : null;
        $project = $data['main_data']['project'] ?? null;
        $project_id = !empty($data['main_data']['project_id']) ? $data['main_data']['project_id'] : null;
        $plan_number = $data['main_data']['plan_number'] ?? null;
        $plan_id = !empty($data['main_data']['plan_id']) ? $data['main_data']['plan_id'] : null;
        $plan_total = $data['main_data']['plan_total'] ?? null;
        $start_date = $this->format_date_to_db($data['main_data']['start_date'] ?? null);
        $end_date = $this->format_date_to_db($data['main_data']['end_date'] ?? null);
        $compensation_date = $this->format_date_notime_to_db($data['main_data']['compensation_date'] ?? null);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $compensation_number = $data['main_data']['compensation_number'] ?? null;
        $req_user_code = $data['main_data']['req_user_code'];
        $status_compensation = 'active';
        $approve_status = 'waiting';
        $compensation_type = $data['main_data']['compensation_id'] ?? null;
        $compensation_type_name = $data['main_data']['compensation'] ?? null;
        $compensation_parent_type = $data['main_data']['compensation_parent_id'] ?? null;
        $compensation_parent_type_name = $data['main_data']['compensation_parent'] ?? null;
        $req_number_withdraw = $data['main_data']['req_number_withdraw'] ?? null;
        $req_number_pay = $data['main_data']['req_number_pay'] ?? null;
        $req_payee_name = $data['main_data']['req_payee_name'] ?? null;
        $req_position_name = $data['main_data']['req_position_name'] ?? null;
        $expenses_number = $data['main_data']['expenses_number'] ?? null;
        $req_address = $data['main_data']['req_address'] ?? null;
        $compensation_amount = $data['main_data']['compensation_amount'] !== '' ? (float) str_replace(',', '', $data['main_data']['compensation_amount']) : 0;
        if ($data['main_data']['compensation_return'] == 1) {
            $compensation_action = 'return';
        } else if ($data['main_data']['compensation_withdraw'] == 1) {
            $compensation_action = 'withdraw';
        } else {
            $compensation_action = '';
        }
        $is_confirm = $data['main_data']['is_confirm'];
        $compensation_form_name = $data['main_data']['compensation_form_name'] ?? null;
        $req_affiliation_name = $data['main_data']['req_affiliation_name'] ?? null;
        $plan_date = $this->format_date_notime_to_db($data['main_data']['plan_date'] ?? null);
        $expenses_id = !empty($data['main_data']['expenses_id']) ? $data['main_data']['expenses_id'] : null;
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_compensations (
                created_at,
                updated_at,
                fund_id,
                fund,
                project_id,
                project,
                plan_number,
                plan_id,
                plan_total,
                start_date,
                end_date,
                depart_code,
                full_name,
                position_name,
                level_name,
                affiliation_name,
                compensation_number,
                req_user_code,
                status_compensation,
                req_user_date,
                approve_status,
                compensation_type,
                compensation_type_name,
                parent_compensation_type,
                parent_compensation_type_name,
                req_number_withdraw,
                req_number_pay,
                req_payee_name,
                req_position_name,
                req_address,
                compensation_amount,
                compensation_action,
                is_confirm,
                compensation_form_name,
                req_affiliation_name,
                compensation_date,
                plan_date,
                expenses_id,
                areas_id,
                province_id
        ) VALUES (
            NOW(), NOW(), $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, NOW(), $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37
        ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $fund_id,
            $fund,
            $project_id,
            $project,
            $plan_number,
            $plan_id,
            $plan_total,
            $start_date,
            $end_date,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $compensation_number,
            $req_user_code,
            $status_compensation,
            $approve_status,
            $compensation_type,
            $compensation_type_name,
            $compensation_parent_type,
            $compensation_parent_type_name,
            $req_number_withdraw,
            $req_number_pay,
            $req_payee_name,
            $req_position_name,
            $req_address,
            $compensation_amount,
            $compensation_action,
            $is_confirm,
            $compensation_form_name,
            $req_affiliation_name,
            $compensation_date,
            $plan_date,
            $expenses_id,
            $area_id,
            $province_id
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงค่า wrd_compensation_id ที่เพิ่ง insert
        $return_id = pg_fetch_result($result, 0, 'id');

        return $return_id; // คืนค่า wrd_compensation_id
    }

    public function editWrdCompensation($data, $id)
    {
        $fund = $data['main_data']['fund'] ?? null;
        $fund_id = !empty($data['main_data']['fund_id']) ? $data['main_data']['fund_id'] : null;
        $project = $data['main_data']['project'] ?? null;
        $project_id = !empty($data['main_data']['project_id']) ? $data['main_data']['project_id'] : null;
        $start_date = $this->format_date_to_db($data['main_data']['start_date'] ?? null);
        $end_date = $this->format_date_to_db($data['main_data']['end_date'] ?? null);
        $compensation_date = $this->format_date_notime_to_db($data['main_data']['compensation_date'] ?? null);
        $approve_status = 'waiting';
        $compensation_type = $data['main_data']['compensation_id'] ?? null;
        $compensation_type_name = $data['main_data']['compensation'] ?? null;
        $compensation_parent_type = $data['main_data']['compensation_parent_id'] ?? null;
        $compensation_parent_type_name = $data['main_data']['compensation_parent'] ?? null;
        $req_number_withdraw = $data['main_data']['req_number_withdraw'] ?? null;
        $req_number_pay = $data['main_data']['req_number_pay'] ?? null;
        $req_payee_name = $data['main_data']['req_payee_name'] ?? null;
        $req_position_name = $data['main_data']['req_position_name'] ?? null;
        $req_address = $data['main_data']['req_address'] ?? null;
        $compensation_amount = $data['main_data']['compensation_amount'] !== '' ? (float) str_replace(',', '', $data['main_data']['compensation_amount']) : 0;
        if ($data['main_data']['compensation_return'] == 1) {
            $compensation_action = 'return';
        } else if ($data['main_data']['compensation_withdraw'] == 1) {
            $compensation_action = 'withdraw';
        } else {
            $compensation_action = '';
        }
        $is_confirm = $data['main_data']['is_confirm'];
        $compensation_form_name = $data['main_data']['compensation_form_name'] ?? null;
        $req_affiliation_name = $data['main_data']['req_affiliation_name'] ?? null;

        // สร้างคำสั่ง SQL UPDATE
        $query = "UPDATE withdraws.tb_wrd_compensations SET 
                fund = $1,
                fund_id = $2,
                project = $3,
                project_id = $4,
                start_date = $5,
                end_date = $6,
                req_user_date = NOW(),
                approve_status = $7,
                compensation_type = $8,
                compensation_type_name = $9,
                parent_compensation_type = $10,
                parent_compensation_type_name = $11,
                req_number_withdraw = $12,
                req_number_pay = $13,
                req_payee_name = $14,
                req_position_name = $15,
                req_address = $16,
                compensation_amount = $17,
                compensation_action = $18,
                is_confirm = $19,
                compensation_form_name = $20,
                req_affiliation_name = $21,
                compensation_date = $22
                WHERE id = $23";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $fund,
            $fund_id,
            $project,
            $project_id,
            $start_date,
            $end_date,
            $approve_status,
            $compensation_type,
            $compensation_type_name,
            $compensation_parent_type,
            $compensation_parent_type_name,
            $req_number_withdraw,
            $req_number_pay,
            $req_payee_name,
            $req_position_name,
            $req_address,
            $compensation_amount,
            $compensation_action,
            $is_confirm,
            $compensation_form_name,
            $req_affiliation_name,
            $compensation_date,
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
        $queryUpdate = "UPDATE withdraws.tb_wrd_compensations SET status_compensation = $1, updated_at = $2 WHERE id = $3";

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

    public function format_date_notime_to_db($date)
    {
        if (empty($date)) return null;

        // แยกวันที่และเวลา
        $parts = explode(' ', $date);
        $dmy = $parts[0];
        $dmy_parts = explode('-', $dmy);
        if (count($dmy_parts) !== 3) return null;
        $day = (int)$dmy_parts[0];
        $month = (int)$dmy_parts[1];
        $year = (int)$dmy_parts[2];
        if ($year > 2400) {
            $year -= 543;
        }

        // สร้าง DateTime เฉพาะวันที่
        $dateTime = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
        if (!$dateTime) return null;

        return $dateTime->format('Y-m-d');
    }

    public function readAllPersons($depart_code)
    {
        $query = "SELECT req_user_code,full_name 
            FROM withdraws.tb_wrd_compensations
            where status_compensation = 'active'
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

    public function updateWrdCompensationApprove($data, $id, $position_code, $info)
    {
        $map = [
            'APHO'  => ['approve' => 'finance_approve', 'reject' => 'finance_reject', 'user_field' => 'approve_finance_user_code', 'date_field' => 'approve_finance_datetime'],
            'APHOP' => ['approve' => 'audit_approve',   'reject' => 'audit_reject',   'user_field' => 'approve_audit_user_code',   'date_field' => 'approve_audit_datetime'],
        ];

        if (!isset($map[$position_code])) {
            // return false; // ถ้าไม่ใช่ 2 ตำแหน่งนี้ return false เลย
        }

        $user_code = $data['main_data']['user_code'] ?? null;
        if (!empty($data['main_data']['approve'])) {
            if ($position_code == 'APHO' || $position_code == 'APHOP') {
                $approve_status = $map[$position_code]['approve'];
                $user_field = $map[$position_code]['user_field'];
                $date_field = $map[$position_code]['date_field'];
            } else {
                $approve_status = 'finance_approve';
                $user_field = 'approve_finance_user_code';
                $date_field = 'approve_finance_datetime';
            }
        } else if (!empty($data['main_data']['reject'])) {
            if ($position_code == 'APHO' || $position_code == 'APHOP') {
                $approve_status = $map[$position_code]['reject'];
                $user_field = $map[$position_code]['user_field'];
                $date_field = $map[$position_code]['date_field'];
            } else {
                $approve_status = 'finance_reject';
                $user_field = 'approve_finance_user_code';
                $date_field = 'approve_finance_datetime';
            }
        } else if (!empty($data['main_data']['check'])) {
            $approve_status = 'finance_check';
            $user_field = 'approve_audit_user_code';
            $date_field = 'approve_audit_datetime';
        } else if (!empty($data['main_data']['reject_check'])) {
            $approve_status = 'cho_reject';
            $user_field = 'approve_audit_user_code';
            $date_field = 'approve_audit_datetime';
        } else if (!empty($data['main_data']['check_and_approve'])) {
            $approve_status = 'finance_approve';
            $user_field = 'approve_finance_user_code';
            $date_field = 'approve_finance_datetime';

            if ($info['approve_status'] != 'finance_check') {
                $checkQuery = "UPDATE withdraws.tb_wrd_compensations SET 
                    approve_status = $1,
                    approve_audit_user_code = $2,
                    approve_audit_datetime = NOW(),
                    updated_at = NOW()
                WHERE id = $3";
                $checkResult = pg_query_params($this->conn, $checkQuery, [
                    'finance_check',
                    $user_code,
                    $id
                ]);
            }
        } else {
            $approve_status = 'waiting';
        }

        $query = "UPDATE withdraws.tb_wrd_compensations SET 
                    approve_status = $1,
                    {$user_field} = $2,
                    {$date_field} = NOW(),
                    updated_at = NOW()
                WHERE id = $3";

        $result = pg_query_params($this->conn, $query, [
            $approve_status,
            $user_code,
            $id
        ]);

        return $result ? true : false;
    }

    public function readAllReport($key, $limit = 10, $offset = 0, $compensation_number = '', $start_date = '', $end_date = '', $status = '', $req_user_code = '', $req_position_code = '', $req_depart_code = '', $search_user_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_compensations ";
        $base_query .= " WHERE status_compensation != 'delete' AND plan_id IS NULL";
        $params = [];
        $where_clauses = [];

        // ✅ ตรวจสอบ key และ status
        $allowed_keys = ['approve_status'];
        if ($key !== '' && $status !== '' && in_array($key, $allowed_keys)) {
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

        // ✅ compensation_number
        if ($compensation_number !== '') {
            $where_clauses[] = "compensation_number = $" . (count($params) + 1);
            $params[] = $compensation_number;
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
        if ($req_user_code !== '' && $_SESSION['user_data']['withdraws']['head_office_id'] == 'N/A') {
            if ($req_position_code != 'APHOP' && $req_position_code != 'APHO') {
                $where_clauses[] = "req_user_code = $" . (count($params) + 1);
                $params[] = $req_user_code;
            }
        }

        if ($req_depart_code != '') {
            $where_clauses[] = "depart_code = $" . (count($params) + 1);
            $params[] = $req_depart_code;
            if ($_SESSION['user_data']['withdraws']['area_id'] != 'N/A') {
                $where_clauses[] = "areas_id = $" . (count($params) + 1);
                $params[] = $_SESSION['user_data']['withdraws']['area_id'];
            }

            if ($_SESSION['user_data']['withdraws']['province_id'] != 'N/A') {
                $where_clauses[] = "province_id = $" . (count($params) + 1);
                $params[] = $_SESSION['user_data']['withdraws']['province_id'];
            }
        }

        if ($search_user_code !== '') {
            $where_clauses[] = "req_user_code = $" . (count($params) + 1);
            $params[] = $search_user_code;
        }

        // รวมเงื่อนไข WHERE
        if (!empty($where_clauses)) {
            $base_query .= " AND " . implode(" AND ", $where_clauses);
        }

        // ✅ Query นับจำนวน
        $count_sql = "SELECT COUNT(tb_wrd_compensations.*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_compensations.* ,
                        COALESCE(
                            (
                                SELECT e.expenses_number
                                FROM withdraws.tb_wrd_expenses e
                                WHERE e.id = tb_wrd_compensations.expenses_id AND e.status_expenses = 'active'
                                LIMIT 1
                            )
                        ) AS expenses_number
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


    public function readAllPersonsReport($depart_code)
    {
        $query = "SELECT req_user_code,full_name 
            FROM withdraws.tb_wrd_compensations
            where status_compensation = 'active'";
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
