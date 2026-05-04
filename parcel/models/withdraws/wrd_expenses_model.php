<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class ExpensesModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllSearch($key, $limit = 10, $offset = 0, $expenses_number = '', $start_date = '', $end_date = '', $status = '', $req_user_code = '', $req_position_code = '', $req_depart_code = '', $search_user_code = '', $action = '', $is_type = '')
    {
        $base_query = "FROM withdraws.tb_wrd_expenses ";
        if ($_SESSION['user_data']['withdraws']['head_office_id'] != 'N/A') {
            $base_query .= "INNER JOIN users.tb_users ON users.tb_users.user_code = withdraws.tb_wrd_expenses.req_user_code and tb_users.head_office_id = '" . $_SESSION['user_data']['withdraws']['head_office_id'] . "' ";
        }
        $base_query .= "WHERE tb_wrd_expenses.status_expenses != 'delete' AND parent_id IS NULL";
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

        // ✅ plan_number
        if ($expenses_number !== '') {
            $where_clauses[] = "expenses_number = $" . (count($params) + 1);
            $params[] = $expenses_number;
        }

        if ($action != 'expenses_approvel_search') {
            if ($is_type !== '') {
                if ($is_type == 1) {
                    $where_clauses[] = "plan_id is NOT NULL";
                } else if ($is_type == 2) {
                    $where_clauses[] = "plan_id is NULL";
                }
            }
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
            if ($action != 'expenses_approvel_search') {
                if (!array_intersect($_SESSION['user_data']['position_list_id'], [14])) {
                    $where_clauses[] = "req_user_code = $" . (count($params) + 1);
                    $params[] = $req_user_code;
                }
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
        $count_sql = "SELECT COUNT(tb_wrd_expenses.*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_expenses.*,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM withdraws.tb_wrd_compensations e 
                            WHERE tb_wrd_expenses.id = e.expenses_id AND e.status_compensation = 'active'
                        ) THEN 1 ELSE 0 
                    END AS has_compensation,
                    COALESCE(
                        (
                            SELECT e.id
                            FROM withdraws.tb_wrd_compensations e
                            WHERE tb_wrd_expenses.id = e.expenses_id AND e.status_compensation = 'active'
                            LIMIT 1
                        ), 0
                    ) AS compensation_id,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM withdraws.tb_wrd_expenses_transfer e 
                            WHERE tb_wrd_expenses.id = e.expenses_id
                        ) THEN 1 ELSE 0 
                    END AS has_transfer  " . $base_query;
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

    public function readExpensesOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_expenses WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง wrd_plan ใหม่
    public function createExpenses($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = $data['main_data']['expenses_allowance'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = $data['main_data']['expenses_allowance_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = $data['main_data']['expenses_allowance_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = $data['main_data']['expenses_accommodation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = $data['main_data']['expenses_accommodation_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = $data['main_data']['expenses_accommodation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = $data['main_data']['expenses_transportation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = $data['main_data']['expenses_transportation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = $data['main_data']['expenses_other'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = $data['main_data']['expenses_other_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = $data['main_data']['expenses_food_per_meal'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = $data['main_data']['expenses_food_meals'] !== '' ? (float) $data['main_data']['expenses_food_meals'] : 0;
        $expenses_food_total = $data['main_data']['expenses_food_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $plan_id = $data['main_data']['plan_id'];
        $plan_number = $data['main_data']['plan_number'];
        $plan_total = $data['main_data']['plan_total'];
        if ($data['main_data']['expenses_return'] == 1) {
            $expenses_action = 'return';
        } else if ($data['main_data']['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }
        $expenses_amount = $data['main_data']['expenses_amount'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_amount']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $expenses_moving_km = $data['main_data']['expenses_moving_km'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_km']) : 0;
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_moving,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_moving_total,
            expenses_transportation_remark,
            expenses_learn,
            req_user_date,
            expenses_moving_km,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, $54, $55, NOW(), $56, $57, $58, $59, $60
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_moving,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id,
            $plan_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_moving_total,
            $expenses_transportation_remark,
            $expenses_learn,
            $expenses_moving_km,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    public function createExpensesNoPlan($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = $data['main_data']['expenses_allowance'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = $data['main_data']['expenses_allowance_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = $data['main_data']['expenses_allowance_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = $data['main_data']['expenses_accommodation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = $data['main_data']['expenses_accommodation_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = $data['main_data']['expenses_accommodation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = $data['main_data']['expenses_transportation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = $data['main_data']['expenses_transportation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = $data['main_data']['expenses_other'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = $data['main_data']['expenses_other_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = $data['main_data']['expenses_food_per_meal'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = $data['main_data']['expenses_food_meals'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_meals']) : 0;
        $expenses_food_total = $data['main_data']['expenses_food_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = $data['main_data']['fund'] ?? '';
        $fund_id = $data['main_data']['fund_id'] ?? '';
        $project = $data['main_data']['project'] ?? '';
        $project_id = $data['main_data']['project_id'] ?? '';
        $expenses_moving_km = $data['main_data']['expenses_moving_km'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_km']) : 0;
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_moving,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_moving_total,
            expenses_transportation_remark,
            expenses_learn,
            fund,
            fund_id,
            project,
            project_id,
            req_user_date,
            expenses_moving_km,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, $54, $55, $56, $57, $58, $59, NOW(), $60, $61, $62, $63, $64
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number = null,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_moving,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id = null,
            $plan_total = null,
            $expenses_action = null,
            $expenses_amount = null,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_moving_total,
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $expenses_moving_km,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    public function editExpenses($data, $id)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = !empty($data['main_data']['expenses_allowance']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = !empty($data['main_data']['expenses_allowance_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = !empty($data['main_data']['expenses_allowance_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = !empty($data['main_data']['expenses_accommodation']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = !empty($data['main_data']['expenses_accommodation_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = !empty($data['main_data']['expenses_accommodation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = !empty($data['main_data']['expenses_transportation']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = !empty($data['main_data']['expenses_transportation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        $expenses_moving = !empty($data['main_data']['expenses_moving']) ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = !empty($data['main_data']['expenses_other']) ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = !empty($data['main_data']['expenses_other_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = !empty($data['main_data']['expenses_food_per_meal']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = !empty($data['main_data']['expenses_food_meals']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_meals']) : 0;
        $expenses_food_total = !empty($data['main_data']['expenses_food_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = !empty($data['main_data']['expenses_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        if ($data['main_data']['expenses_return'] == 1) {
            $expenses_action = 'return';
        } else if ($data['main_data']['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }
        $expenses_amount = !empty($data['main_data']['expenses_amount']) ? (float) str_replace(',', '', $data['main_data']['expenses_amount']) : 0;
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $req_user_code = $data['main_data']['req_user_code'];
        $expenses_moving_total = !empty($data['main_data']['expenses_moving_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = empty($data['main_data']['fund_id']) ? null : $data['main_data']['fund'];
        $fund_id = empty($data['main_data']['fund_id']) ? null : $data['main_data']['fund_id'];
        $project = empty($data['main_data']['project_id']) ? null : $data['main_data']['project'];
        $project_id = empty($data['main_data']['project_id']) ? null : $data['main_data']['project_id'];
        $text_approve = $data['main_data']['text_approve'] ?? '';
        $position_withdraw = $data['main_data']['position_withdraw'] ?? '';
        $expenses_moving_km = !empty($data['main_data']['expenses_moving_km']) ? (float) str_replace(',', '', $data['main_data']['expenses_moving_km']) : 0;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL UPDATE
        $query = "UPDATE withdraws.tb_wrd_expenses SET
            expenses_form_name = $1,
            expenses_start = $2,
            expenses_end = $3,
            depart_code = $4,
            full_name = $5,
            position_name = $6,
            level_name = $7,
            affiliation_name = $8,
            expenses_code = $9,
            expenses_date = $10,
            expenses_group = $11,
            expenses_group_count = $12,
            expenses_start_location = $13,
            expenses_start_date = $14,
            expenses_start_address = $15,
            expenses_end_location = $16,
            expenses_end_date = $17,
            expenses_end_address = $18,
            expenses_allowance = $19,
            expenses_allowance_days = $20,
            expenses_allowance_total = $21,
            expenses_accommodation = $22,
            expenses_accommodation_days = $23,
            expenses_accommodation_total = $24,
            expenses_transportation = $25,
            expenses_transportation_total = $26,
            expenses_vehicle = $27,
            expenses_vehicle_number = $28,
            expenses_other = $29,
            expenses_other_total = $30,
            expenses_food_per_meal = $31,
            expenses_food_meals = $32,
            expenses_food_total = $33,
            expenses_note = $34,
            expenses_total = $35,
            expenses_action = $36,
            expenses_amount = $37,
            expenses_confirm = $38,
            approve_status = $39,
            approve_finance_user_code = $40,
            approve_finance_datetime = $41,
            approve_audit_user_code = $42,
            approve_audit_datetime = $43,
            updated_at = NOW(),
            status_expenses = $44,
            expenses_transportation_remark = $45,
            expenses_learn = $46,
            fund = $47,
            fund_id = $48,
            project = $49,
            project_id = $50,
            req_user_date = NOW(),
            text_approve = $51,
            position_withdraw = $52,
            expenses_moving_km = $53,
            expenses_moving = $54,
            expenses_moving_total = $55,
            is_foreign = $56,
            expenses_other_remark = $57
        WHERE id = $58";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $status_expenses = 'active',
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $text_approve,
            $position_withdraw,
            $expenses_moving_km,
            $expenses_moving,
            $expenses_moving_total,
            $is_foreign,
            $expenses_other_remark,
            $id // ตัวสุดท้ายคือ id สำหรับ WHERE id = $58
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return true;
    }

    public function deleteExpensesStatus($id)
    {
        // สถานะที่ต้องการอัปเดต
        $status = 'delete';

        // SQL ที่ใช้ parameterized query อย่างถูกต้อง
        $queryUpdate = "UPDATE withdraws.tb_wrd_expenses SET status_expenses = $1, updated_at = $2 WHERE id = $3";

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
        if (empty($date)) {
            return null;
        }

        $date = trim($date);
        if ($date === '') {
            return null;
        }

        // รองรับทั้งตัวคั่น "-" หรือ "/" และมี/ไม่มีเวลา
        if (!preg_match('/^(\d{1,4})[-\/](\d{1,2})[-\/](\d{1,4})(?:\s+(\d{1,2}:\d{2}(?::\d{2})?))?$/', $date, $matches)) {
            return null;
        }

        $part1 = (int)$matches[1];
        $part2 = (int)$matches[2];
        $part3 = (int)$matches[3];
        $timePart = $matches[4] ?? '00:00:00';

        if (strlen($timePart) === 5) {
            $timePart .= ':00';
        }

        // ตรวจสอบว่าส่วนไหนคือปี เดือน วัน
        if ($part1 > 1900) {
            // รูปแบบ Y-m-d
            $year = $part1;
            $month = $part2;
            $day = $part3;
        } elseif ($part3 > 1900) {
            // รูปแบบ d-m-Y
            $day = $part1;
            $month = $part2;
            $year = $part3;
        } else {
            // กรณีอื่น (เช่น 31-12-64) ให้ถือว่า part3 เป็นปีแบบสองหลัก
            $day = $part1;
            $month = $part2;
            $year = $part3 + ($part3 >= 100 ? 0 : 2000);
        }

        if ($year > 2400) {
            $year -= 543;
        }

        // ป้องกันค่าวัน/เดือนที่เกินขอบเขต ให้ DateTime จัดการ normalize
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', sprintf('%04d-%02d-%02d %s', $year, $month, $day, $timePart));
        if (!$dateTime) {
            return null;
        }

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

    public function updateWrdPlanApprove($data, $id, $position_code, $info)
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
                $checkQuery = "UPDATE withdraws.tb_wrd_expenses SET 
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

        if ($approve_status == 'finance_approve') {
            $ExpenseCat = !empty($data['ExpenseCat']) ? $data['ExpenseCat'] : null;
        } else {
            $ExpenseCat = null;
        }

        $query = "UPDATE withdraws.tb_wrd_expenses SET 
                    approve_status = $1,
                    {$user_field} = $2,
                    {$date_field} = NOW(),
                    updated_at = NOW(),
                    expense_cat = $3
                WHERE id = $4";

        $result = pg_query_params($this->conn, $query, [
            $approve_status,
            $user_code,
            $ExpenseCat,
            $id
        ]);

        return $result ? true : false;
    }

    public function updateWrdPlanApproveMulti($data, $id, $position_code, $info)
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
                $checkQuery = "UPDATE withdraws.tb_wrd_expenses SET 
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
        $ExpenseCat = !empty($data['ExpenseCat']) ? $data['ExpenseCat'] : null;

        $query = "UPDATE withdraws.tb_wrd_expenses SET 
                    approve_status = $1,
                    {$user_field} = $2,
                    {$date_field} = NOW(),
                    updated_at = NOW(),
                    expense_cat = $3
                WHERE id = $4";

        $result = pg_query_params($this->conn, $query, [
            $approve_status,
            $user_code,
            $ExpenseCat,
            $id
        ]);

        return $result ? true : false;
    }

    public function readAllPersons($depart_code)
    {
        $query = "SELECT req_user_code,full_name 
            FROM withdraws.tb_wrd_expenses
            where status_expenses = 'active'
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
            FROM withdraws.tb_wrd_expenses
            where status_expenses = 'active'";
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

    public function createExpensesOutside($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = $data['main_data']['expenses_allowance'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = $data['main_data']['expenses_allowance_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = $data['main_data']['expenses_allowance_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = $data['main_data']['expenses_accommodation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = $data['main_data']['expenses_accommodation_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = $data['main_data']['expenses_accommodation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = $data['main_data']['expenses_transportation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = $data['main_data']['expenses_transportation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        // $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = $data['main_data']['expenses_other'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = $data['main_data']['expenses_other_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = $data['main_data']['expenses_food_per_meal'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = $data['main_data']['expenses_food_meals'] !== '' ? (float) $data['main_data']['expenses_food_meals'] : 0;
        $expenses_food_total = $data['main_data']['expenses_food_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $plan_id = $data['main_data']['plan_id'];
        $plan_number = $data['main_data']['plan_number'];
        $plan_total = $data['main_data']['plan_total'];
        if ($data['main_data']['expenses_return'] == 1) {
            $expenses_action = 'return';
        } else if ($data['main_data']['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }
        $expenses_amount = $data['main_data']['expenses_amount'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_amount']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        // $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $text_approve = $data['main_data']['text_approve'] ?? '';
        $is_type_req = 2;
        $position_withdraw = $data['main_data']['position_withdraw'] ?? '';
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_transportation_remark,
            expenses_learn,
            req_user_date,
            text_approve,
            is_type_req,
            position_withdraw,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, NOW(), $54, $55, $56, $57, $58, $59, $60
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id,
            $plan_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_transportation_remark,
            $expenses_learn,
            $text_approve,
            $is_type_req,
            $position_withdraw,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    public function createExpensesNoPlanOutside($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = $data['main_data']['expenses_allowance'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = $data['main_data']['expenses_allowance_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = $data['main_data']['expenses_allowance_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = $data['main_data']['expenses_accommodation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = $data['main_data']['expenses_accommodation_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = $data['main_data']['expenses_accommodation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = $data['main_data']['expenses_transportation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = $data['main_data']['expenses_transportation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        // $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = $data['main_data']['expenses_other'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = $data['main_data']['expenses_other_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = $data['main_data']['expenses_food_per_meal'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = $data['main_data']['expenses_food_meals'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_meals']) : 0;
        $expenses_food_total = $data['main_data']['expenses_food_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        // $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = $data['main_data']['fund'] ?? '';
        $fund_id = $data['main_data']['fund_id'] ?? '';
        $project = $data['main_data']['project'] ?? '';
        $project_id = $data['main_data']['project_id'] ?? '';
        $text_approve = $data['main_data']['text_approve'] ?? '';
        $is_type_req = 2;
        $position_withdraw = $data['main_data']['position_withdraw'] ?? '';
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_transportation_remark,
            expenses_learn,
            fund,
            fund_id,
            project,
            project_id,
            req_user_date,
            text_approve,
            is_type_req,
            position_withdraw,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, $54, $55, $56, $57, NOW(), $58, $59, $60, $61, $62, $63, $64
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number = null,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id = null,
            $plan_total = null,
            $expenses_action = null,
            $expenses_amount = null,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $text_approve,
            $is_type_req,
            $position_withdraw,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    // ฟังก์ชันสำหรับการสร้าง wrd_plan ใหม่
    public function createExpensesMulti($data)
    {
        $m = $data['main_data'] ?? [];

        $expenses_form_name = $m['expenses_form_name'] ?? '';
        $expenses_start = $this->format_date_to_db($m['expenses_start'] ?? null);
        $expenses_end = $this->format_date_to_db($m['expenses_end'] ?? null);
        $depart_code = $m['depart_code'] ?? '';
        $full_name = $m['full_name'] ?? '';
        $position_name = $m['position_name'] ?? '';
        $level_name = $m['level_name'] ?? '';
        $affiliation_name = $m['affiliation_name'] ?? '';
        $expenses_number = $m['expenses_number'] ?? null;
        $expenses_code = $m['expenses_code'] ?? '';
        $expenses_date = $this->format_date_to_db($m['expenses_date'] ?? null);
        $expenses_group = $m['expenses_group'] ?? null;
        $expenses_group_count = isset($m['expenses_group_count']) && $m['expenses_group_count'] !== '' ? $m['expenses_group_count'] : 0;
        $expenses_start_location = $m['expenses_start_location'] ?? '';
        $expenses_start_date = $this->format_date_to_db($m['expenses_start_date'] ?? null);
        $expenses_start_address = $m['expenses_start_address'] ?? '';
        $expenses_end_location = $m['expenses_end_location'] ?? '';
        $expenses_end_date = $this->format_date_to_db($m['expenses_end_date'] ?? null);
        $expenses_end_address = $m['expenses_end_address'] ?? '';

        $expenses_allowance = !empty($m['expenses_allowance']) ? (float) str_replace(',', '', $m['expenses_allowance']) : 0;
        $expenses_allowance_days = !empty($m['expenses_allowance_days']) ? (float) str_replace(',', '', $m['expenses_allowance_days']) : 0;
        $expenses_allowance_total = !empty($m['expenses_allowance_total']) ? (float) str_replace(',', '', $m['expenses_allowance_total']) : 0;
        $expenses_accommodation = !empty($m['expenses_accommodation']) ? (float) str_replace(',', '', $m['expenses_accommodation']) : 0;
        $expenses_accommodation_days = !empty($m['expenses_accommodation_days']) ? (float) str_replace(',', '', $m['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = !empty($m['expenses_accommodation_total']) ? (float) str_replace(',', '', $m['expenses_accommodation_total']) : 0;
        $expenses_transportation = !empty($m['expenses_transportation']) ? (float) str_replace(',', '', $m['expenses_transportation']) : 0;
        $expenses_transportation_total = !empty($m['expenses_transportation_total']) ? (float) str_replace(',', '', $m['expenses_transportation_total']) : 0;
        $expenses_moving = !empty($m['expenses_moving']) ? (float) str_replace(',', '', $m['expenses_moving']) : 0;

        // if (!empty($m['expenses_vehicle']) && $m['expenses_vehicle'] == 1) {
        //     $expenses_vehicle = 'car';
        // } elseif (!empty($m['expenses_motorcycle']) && $m['expenses_motorcycle'] == 1) {
        //     $expenses_vehicle = 'motorcycle';
        // } else {
        //     $expenses_vehicle = '';
        // }
        $expenses_vehicle = $m['expenses_vehicle'] ?? '';
        $expenses_vehicle_number = $m['expenses_vehicle_number'] ?? '';

        $expenses_other = !empty($m['expenses_other']) ? (float) str_replace(',', '', $m['expenses_other']) : 0;
        $expenses_other_total = !empty($m['expenses_other_total']) ? (float) str_replace(',', '', $m['expenses_other_total']) : 0;
        $expenses_food_per_meal = !empty($m['expenses_food_per_meal']) ? (float) str_replace(',', '', $m['expenses_food_per_meal']) : 0;
        $expenses_food_meals = !empty($m['expenses_food_meals']) ? (float) $m['expenses_food_meals'] : 0;
        $expenses_food_total = !empty($m['expenses_food_total']) ? (float) str_replace(',', '', $m['expenses_food_total']) : 0;

        $expenses_note = $m['expenses_note'] ?? '';
        $expenses_total = !empty($m['expenses_total']) ? (float) str_replace(',', '', $m['expenses_total']) : 0;
        $plan_id = $m['plan_id'] ?? null;
        $plan_number = $m['plan_number'] ?? null;
        $plan_total = $m['plan_total'] ?? null;

        if (!empty($m['expenses_return']) && $m['expenses_return'] == 1) {
            $expenses_action = 'return';
        } elseif (!empty($m['expenses_withdraw']) && $m['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }

        $expenses_amount = !empty($m['expenses_amount']) ? (float) str_replace(',', '', $m['expenses_amount']) : 0;
        $expenses_confirm = ($m['expenses_confirm'] == 'on') ? 1 : 0;
        $req_user_code = $m['req_user_code'] ?? null;
        $expenses_moving_total = !empty($m['expenses_moving_total']) ? (float) str_replace(',', '', $m['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $m['expenses_transportation_remark'] ?? '';
        $expenses_learn = $m['expenses_learn'] ?? '';
        $parent_id = $m['parent_id'] ?? null;
        $expenses_moving_km = !empty($m['expenses_moving_km']) ? (float) str_replace(',', '', $m['expenses_moving_km']) : 0;

        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // --- SQL INSERT ---
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_moving,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_moving_total,
            expenses_transportation_remark,
            expenses_learn,
            parent_id,
            req_user_date,
            expenses_moving_km,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10,
            $11, $12, $13, $14, $15, $16, $17, $18, $19, $20,
            $21, $22, $23, $24, $25, $26, $27, $28, $29, $30,
            $31, $32, $33, $34, $35, $36, $37, $38, $39, $40,
            $41, $42, $43, $44, $45, $46, $47, $48, $49, $50,
            $51, $52, $53, $54, $55, $56, NOW(), $57, $58, $59,
            $60, $61
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_moving,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id,
            $plan_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_moving_total,
            $expenses_transportation_remark,
            $expenses_learn,
            $parent_id,
            $expenses_moving_km,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $return_id = pg_fetch_result($result, 0, 'id');
        return $return_id;
    }


    public function createExpensesMultiNoPlan($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        if (empty($expenses_date)) {
            $expenses_date = $data['main_data']['expenses_date'];
        }
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = (float) str_replace(',', '', $data['main_data']['expenses_allowance'] ?? 0);
        $expenses_allowance_days = (float) str_replace(',', '', $data['main_data']['expenses_allowance_days'] ?? 0);
        $expenses_allowance_total = (float) str_replace(',', '', $data['main_data']['expenses_allowance_total'] ?? 0);
        $expenses_accommodation      = (float) str_replace(',', '', $data['main_data']['expenses_accommodation']      ?? 0);
        $expenses_accommodation_days = (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days'] ?? 0);
        $expenses_accommodation_total = (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total'] ?? 0);
        $expenses_transportation     = (float) str_replace(',', '', $data['main_data']['expenses_transportation']     ?? 0);
        $expenses_transportation_total = (float) str_replace(',', '', $data['main_data']['expenses_transportation_total'] ?? 0);
        $expenses_moving             = (float) str_replace(',', '', $data['main_data']['expenses_moving']             ?? 0);

        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = $data['main_data']['fund'] ?? '';
        $fund_id = $data['main_data']['fund_id'] ?? null;
        $project = $data['main_data']['project'] ?? '';
        $project_id = $data['main_data']['project_id'] ?? null;
        $parent_id = $data['main_data']['parent_id'] ?? null;
        $req_user_date = $data['main_data']['req_user_date'] ?? date('Y-m-d'); // ถ้าไม่มีมาให้ใช้วันนี้

        $expenses_moving_total       = (float) str_replace(',', '', $data['main_data']['expenses_moving_total']       ?? 0);
        $expenses_other              = (float) str_replace(',', '', $data['main_data']['expenses_other']              ?? 0);
        $expenses_other_total        = (float) str_replace(',', '', $data['main_data']['expenses_other_total']        ?? 0);
        $expenses_food_per_meal      = (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']      ?? 0);
        $expenses_food_meals         = (float) str_replace(',', '', $data['main_data']['expenses_food_meals']         ?? 0);
        $expenses_food_total         = (float) str_replace(',', '', $data['main_data']['expenses_food_total']         ?? 0);
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // SQL INSERT (61 fields)
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_moving,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_moving_total,
            expenses_transportation_remark,
            expenses_learn,
            fund,
            fund_id,
            project,
            project_id,
            parent_id,
            req_user_date,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,
            $11,$12,$13,$14,$15,$16,$17,$18,$19,$20,
            $21,$22,$23,$24,$25,$26,$27,$28,$29,$30,
            $31,$32,$33,$34,$35,$36,$37,$38,$39,$40,
            $41,$42,$43,$44,$45,$46,$47,$48,$49,$50,
            $51,$52,$53,$54,$55,$56,$57,$58,$59,$60,
            NOW(),$61,$62,$63,$64
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number = null,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_moving,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id = null,
            $plan_total = null,
            $expenses_action = null,
            $expenses_amount = null,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_moving_total,
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $parent_id,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $return_id = pg_fetch_result($result, 0, 'id');
        return $return_id;
    }



    public function readExpensesOneMulti($id)
    {
        $numericId = (int)$id;
        if ($numericId <= 0) {
            return false;
        }

        $query = "SELECT * FROM withdraws.tb_wrd_expenses WHERE id = $1 AND status_expenses = 'active'";
        $result = pg_query_params($this->conn, $query, array($numericId));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readExpensesFamily($id)
    {
        $current = $this->readExpensesOneMulti($id);
        if (!$current) {
            return [
                'root_id' => null,
                'records' => [],
            ];
        }

        $parentRaw = $current['parent_id'] ?? null;
        $rootId = filter_var(
            $parentRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($rootId === false) {
            $rootId = filter_var(
                $current['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
        }

        if ($rootId === false) {
            return [
                'root_id' => null,
                'records' => [],
            ];
        }

        $rootId = (int)$rootId;
        if ($rootId <= 0) {
            return [
                'root_id' => null,
                'records' => [],
            ];
        }

        $query = "SELECT * 
                FROM withdraws.tb_wrd_expenses 
                WHERE (id = $1 OR parent_id = $1) AND status_expenses = 'active' 
                ORDER BY CASE WHEN COALESCE(expenses_group, 0) = 3 THEN 0 ELSE 1 END, id";
        $result = pg_query_params($this->conn, $query, array($rootId));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return [
                'root_id' => $rootId,
                'records' => [],
            ];
        }

        $records = pg_fetch_all($result);
        if (!$records) {
            $records = [];
        }

        return [
            'root_id' => $rootId,
            'records' => $records,
        ];
    }

    public function editExpensesMulti($data, $id)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_to_db($data['main_data']['expenses_date']);
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = $data['main_data']['expenses_group_count'] !== '' ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = $data['main_data']['expenses_allowance'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = $data['main_data']['expenses_allowance_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = $data['main_data']['expenses_allowance_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = $data['main_data']['expenses_accommodation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = $data['main_data']['expenses_accommodation_days'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = $data['main_data']['expenses_accommodation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = $data['main_data']['expenses_transportation'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = $data['main_data']['expenses_transportation_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        // if ($data['main_data']['expenses_vehicle'] == 1) {
        //     $expenses_vehicle = 'car';
        // } else if ($data['main_data']['expenses_motorcycle'] == 1) {
        //     $expenses_vehicle = 'motorcycle';
        // } else {
        //     $expenses_vehicle = '';
        // }
        $expenses_vehicle = $data['main_data']['expenses_vehicle'];
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = $data['main_data']['expenses_other'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = $data['main_data']['expenses_other_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = $data['main_data']['expenses_food_per_meal'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = $data['main_data']['expenses_food_meals'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_meals']) : 0;
        $expenses_food_total = $data['main_data']['expenses_food_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = $data['main_data']['expenses_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        if ($data['main_data']['expenses_return'] == 1) {
            $expenses_action = 'return';
        } else if ($data['main_data']['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }
        $expenses_amount = $data['main_data']['expenses_amount'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_amount']) : 0;
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $req_user_code = $data['main_data']['req_user_code'];
        $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = empty($data['main_data']['fund_id']) ? null : $data['main_data']['fund'];
        $fund_id = empty($data['main_data']['fund_id']) ? null : $data['main_data']['fund_id'];
        $project = empty($data['main_data']['project_id']) ? null : $data['main_data']['project'];
        $project_id = empty($data['main_data']['project_id']) ? null : $data['main_data']['project_id'];
        $text_approve = $data['main_data']['text_approve'] ?? '';
        $position_withdraw = $data['main_data']['position_withdraw'] ?? '';
        $expenses_moving_km = $data['main_data']['expenses_moving_km'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_km']) : 0;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL UPDATE
        $query = "UPDATE withdraws.tb_wrd_expenses SET
            expenses_form_name = $1,
            expenses_start = $2,
            expenses_end = $3,
            depart_code = $4,
            full_name = $5,
            position_name = $6,
            level_name = $7,
            affiliation_name = $8,
            expenses_code = $9,
            expenses_date = $10,
            -- expenses_group ไม่อัปเดตแล้ว
            expenses_group_count = $11,
            expenses_start_location = $12,
            expenses_start_date = $13,
            expenses_start_address = $14,
            expenses_end_location = $15,
            expenses_end_date = $16,
            expenses_end_address = $17,
            expenses_allowance = $18,
            expenses_allowance_days = $19,
            expenses_allowance_total = $20,
            expenses_accommodation = $21,
            expenses_accommodation_days = $22,
            expenses_accommodation_total = $23,
            expenses_transportation = $24,
            expenses_transportation_total = $25,
            expenses_vehicle = $26,
            expenses_vehicle_number = $27,
            expenses_other = $28,
            expenses_other_total = $29,
            expenses_food_per_meal = $30,
            expenses_food_meals = $31,
            expenses_food_total = $32,
            expenses_note = $33,
            expenses_total = $34,
            expenses_action = $35,
            expenses_amount = $36,
            expenses_confirm = $37,
            approve_status = $38,
            approve_finance_user_code = $39,
            approve_finance_datetime = $40,
            approve_audit_user_code = $41,
            approve_audit_datetime = $42,
            updated_at = NOW(),
            status_expenses = $43,
            expenses_transportation_remark = $44,
            expenses_learn = $45,
            fund = $46,
            fund_id = $47,
            project = $48,
            project_id = $49,
            req_user_date = NOW(),
            text_approve = $50,
            position_withdraw = $51,
            expenses_moving_km = $52,
            expenses_moving = $53,
            expenses_moving_total = $54,
            is_foreign = $55,
            expenses_other_remark = $56
        WHERE id = $57";


        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_code,
            $expenses_date,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $status_expenses = 'active',
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $text_approve,
            $position_withdraw,
            $expenses_moving_km,
            $expenses_moving,
            $expenses_moving_total,
            $is_foreign,
            $expenses_other_remark,
            $id
        ));


        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            return false;
        }

        return true;
    }

    public function createExpensesOutsideMulti($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        if (empty($expenses_date)) {
            $expenses_date = $data['main_data']['expenses_date'];
        }
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = isset($data['main_data']['expenses_group_count']) ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = isset($data['main_data']['expenses_allowance']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = isset($data['main_data']['expenses_allowance_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = isset($data['main_data']['expenses_allowance_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = isset($data['main_data']['expenses_accommodation']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = isset($data['main_data']['expenses_accommodation_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = isset($data['main_data']['expenses_accommodation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = isset($data['main_data']['expenses_transportation']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = isset($data['main_data']['expenses_transportation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        // $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = isset($data['main_data']['expenses_other']) ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = isset($data['main_data']['expenses_other_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = isset($data['main_data']['expenses_food_per_meal']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = isset($data['main_data']['expenses_food_meals']) ? (float) $data['main_data']['expenses_food_meals'] : 0;
        $expenses_food_total = isset($data['main_data']['expenses_food_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = isset($data['main_data']['expenses_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $plan_id = $data['main_data']['plan_id'];
        $plan_number = $data['main_data']['plan_number'];
        $plan_total = $data['main_data']['plan_total'];
        if ($data['main_data']['expenses_return'] == 1) {
            $expenses_action = 'return';
        } else if ($data['main_data']['expenses_withdraw'] == 1) {
            $expenses_action = 'withdraw';
        } else {
            $expenses_action = '';
        }
        $expenses_amount = isset($data['main_data']['expenses_amount']) ? (float) str_replace(',', '', $data['main_data']['expenses_amount']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        // $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $text_approve = $data['main_data']['text_approve'] ?? '';
        $is_type_req = 2;
        $position_withdraw = $data['main_data']['position_withdraw'] ?? '';
        $parent_id = $data['main_data']['parent_id'] ?? null;
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_transportation_remark,
            expenses_learn,
            req_user_date,
            text_approve,
            is_type_req,
            position_withdraw,
            parent_id,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, NOW(), $54, $55, $56, $57, $58, $59, $60, $61
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id,
            $plan_total,
            $expenses_action,
            $expenses_amount,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_transportation_remark,
            $expenses_learn,
            $text_approve,
            $is_type_req,
            $position_withdraw,
            $parent_id,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    public function createExpensesNoPlanOutsideMulti($data)
    {
        $expenses_form_name = $data['main_data']['expenses_form_name'];
        $expenses_start = $this->format_date_to_db($data['main_data']['expenses_start']);
        $expenses_end = $this->format_date_to_db($data['main_data']['expenses_end']);
        $depart_code = $data['main_data']['depart_code'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_code = $data['main_data']['expenses_code'];
        $expenses_date = $this->format_date_notime_to_db($data['main_data']['expenses_date']);
        if (empty($expenses_date)) {
            $expenses_date = $data['main_data']['expenses_date'];
        }
        $expenses_group = $data['main_data']['expenses_group'];
        $expenses_group_count = isset($data['main_data']['expenses_group_count']) ? $data['main_data']['expenses_group_count'] : 0;
        $expenses_start_location = $data['main_data']['expenses_start_location'];
        $expenses_start_date = $this->format_date_to_db($data['main_data']['expenses_start_date']);
        $expenses_start_address = $data['main_data']['expenses_start_address'];
        $expenses_end_location = $data['main_data']['expenses_end_location'];
        $expenses_end_date = $this->format_date_to_db($data['main_data']['expenses_end_date']);
        $expenses_end_address = $data['main_data']['expenses_end_address'];
        $expenses_allowance = isset($data['main_data']['expenses_allowance']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance']) : 0;
        $expenses_allowance_days = isset($data['main_data']['expenses_allowance_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_days']) : 0;
        $expenses_allowance_total = isset($data['main_data']['expenses_allowance_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_allowance_total']) : 0;
        $expenses_accommodation = isset($data['main_data']['expenses_accommodation']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation']) : 0;
        $expenses_accommodation_days = isset($data['main_data']['expenses_accommodation_days']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_days']) : 0;
        $expenses_accommodation_total = isset($data['main_data']['expenses_accommodation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_accommodation_total']) : 0;
        $expenses_transportation = isset($data['main_data']['expenses_transportation']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation']) : 0;
        $expenses_transportation_total = isset($data['main_data']['expenses_transportation_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_transportation_total']) : 0;
        // $expenses_moving = $data['main_data']['expenses_moving'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving']) : 0;
        if ($data['main_data']['expenses_vehicle'] == 1) {
            $expenses_vehicle = 'car';
        } else if ($data['main_data']['expenses_motorcycle'] == 1) {
            $expenses_vehicle = 'motorcycle';
        } else {
            $expenses_vehicle = '';
        }
        $expenses_vehicle_number = $data['main_data']['expenses_vehicle_number'] ?? '';
        $expenses_other = isset($data['main_data']['expenses_other']) ? (float) str_replace(',', '', $data['main_data']['expenses_other']) : 0;
        $expenses_other_total = isset($data['main_data']['expenses_other_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_other_total']) : 0;
        $expenses_food_per_meal = isset($data['main_data']['expenses_food_per_meal']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_per_meal']) : 0;
        $expenses_food_meals = isset($data['main_data']['expenses_food_meals']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_meals']) : 0;
        $expenses_food_total = isset($data['main_data']['expenses_food_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_food_total']) : 0;
        $expenses_note = $data['main_data']['expenses_note'];
        $expenses_total = isset($data['main_data']['expenses_total']) ? (float) str_replace(',', '', $data['main_data']['expenses_total']) : 0;
        $expenses_number = $data['main_data']['expenses_number'];
        $expenses_confirm = $data['main_data']['expenses_confirm'];
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        $req_user_code = $data['main_data']['req_user_code'];
        // $expenses_moving_total = $data['main_data']['expenses_moving_total'] !== '' ? (float) str_replace(',', '', $data['main_data']['expenses_moving_total']) : 0;
        $expenses_transportation_remark = $data['main_data']['expenses_transportation_remark'] ?? '';
        $expenses_learn = $data['main_data']['expenses_learn'] ?? '';
        $fund = isset($data['main_data']['fund']) ? $data['main_data']['fund'] : null;
        $fund_id = isset($data['main_data']['fund_id']) ? $data['main_data']['fund_id'] : null;
        $project = isset($data['main_data']['project']) ? $data['main_data']['project'] : null;
        $project_id = isset($data['main_data']['project_id']) ? $data['main_data']['project_id'] : null;
        $text_approve = isset($data['main_data']['text_approve']) ? $data['main_data']['text_approve'] : null;
        $is_type_req = 2;
        $position_withdraw = $data['main_data']['position_withdraw'] ?? null;
        $parent_id = $data['main_data']['parent_id'] ?? null;
        $area_id = ($_SESSION['user_data']['area_id'] !== 'N/A') ? $_SESSION['user_data']['area_id'] : null;
        $province_id = ($_SESSION['user_data']['province_id'] !== 'N/A') ? $_SESSION['user_data']['province_id'] : null;
        $is_foreign = $data['main_data']['is_foreign'] ?? null;
        $expenses_other_remark = $data['main_data']['expenses_other_remark'] ?? '';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO withdraws.tb_wrd_expenses (
            plan_number,
            expenses_form_name,
            expenses_start,
            expenses_end,
            depart_code,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            expenses_number,
            expenses_code,
            expenses_date,
            expenses_group,
            expenses_group_count,
            expenses_start_location,
            expenses_start_date,
            expenses_start_address,
            expenses_end_location,
            expenses_end_date,
            expenses_end_address,
            expenses_allowance,
            expenses_allowance_days,
            expenses_allowance_total,
            expenses_accommodation,
            expenses_accommodation_days,
            expenses_accommodation_total,
            expenses_transportation,
            expenses_transportation_total,
            expenses_vehicle,
            expenses_vehicle_number,
            expenses_other,
            expenses_other_total,
            expenses_food_per_meal,
            expenses_food_meals,
            expenses_food_total,
            expenses_note,
            expenses_total,
            plan_id,
            plan_total,
            expenses_action,
            expenses_amount,
            expenses_confirm,
            approve_status,
            approve_finance_user_code,
            approve_finance_datetime,
            approve_audit_user_code,
            approve_audit_datetime,
            created_at,
            updated_at,
            status_expenses,
            req_user_code,
            expenses_transportation_remark,
            expenses_learn,
            fund,
            fund_id,
            project,
            project_id,
            req_user_date,
            text_approve,
            is_type_req,
            position_withdraw,
            parent_id,
            areas_id,
            province_id,
            is_foreign,
            expenses_other_remark
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36, $37, $38, $39, $40, $41, $42, $43, $44, $45, $46, $47, $48, $49, $50, $51, $52, $53, $54, $55, $56, $57, NOW(), $58, $59, $60, $61, $62, $63, $64, $65
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $plan_number = null,
            $expenses_form_name,
            $expenses_start,
            $expenses_end,
            $depart_code,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $expenses_number,
            $expenses_code,
            $expenses_date,
            $expenses_group,
            $expenses_group_count,
            $expenses_start_location,
            $expenses_start_date,
            $expenses_start_address,
            $expenses_end_location,
            $expenses_end_date,
            $expenses_end_address,
            $expenses_allowance,
            $expenses_allowance_days,
            $expenses_allowance_total,
            $expenses_accommodation,
            $expenses_accommodation_days,
            $expenses_accommodation_total,
            $expenses_transportation,
            $expenses_transportation_total,
            $expenses_vehicle,
            $expenses_vehicle_number,
            $expenses_other,
            $expenses_other_total,
            $expenses_food_per_meal,
            $expenses_food_meals,
            $expenses_food_total,
            $expenses_note,
            $expenses_total,
            $plan_id = null,
            $plan_total = null,
            $expenses_action = null,
            $expenses_amount = null,
            $expenses_confirm,
            $approve_status = 'waiting',
            $approve_finance_user_code = null,
            $approve_finance_datetime = null,
            $approve_audit_user_code = null,
            $approve_audit_datetime = null,
            $created_at,
            $updated_at,
            $status_expenses = 'active',
            $req_user_code,
            $expenses_transportation_remark,
            $expenses_learn,
            $fund,
            $fund_id,
            $project,
            $project_id,
            $text_approve,
            $is_type_req,
            $position_withdraw,
            $parent_id,
            $area_id,
            $province_id,
            $is_foreign,
            $expenses_other_remark
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

    function getExpensesByParentId($parent_id = null, $is_type_req = '', $is_foreign = '', $expense_cat = '')
    {
        if (empty($parent_id)) {
            $query = "SELECT e.id,
            e.expenses_start,
            e.updated_at,
            e.expenses_number,
            e.req_user_code,
            e.depart_code,
            fl.fund_code as fund_code,
            fl.fund_rcode as fund_rcode,
            prol.project_code as project_code,
            f.fund_code as fund_code_2,
            f.fund_rcode as fund_rcode_2,
            p.project_code as project_code_2,
            e.plan_number,
            e.expenses_allowance_total,
            e.expenses_accommodation_total,
            e.expenses_transportation_total,
            e.expenses_other_total,
            e.expenses_food_total,
            e.expenses_moving_total,
            e.is_foreign,
            (select sum(compensation) as amount from withdraws.tb_wrd_expenses_lists g where g.expenses_id=e.id) as total_compensation
            FROM withdraws.tb_wrd_expenses e
            left join parameters.tb_wrd_fund f on f.id=e.fund_id
            left join parameters.tb_wrd_project p on p.id=e.project_id
            left join withdraws.tb_wrd_plans pl on pl.id=e.plan_id
            left join parameters.tb_wrd_fund fl on fl.id=pl.fund_id
            left join parameters.tb_wrd_project prol on prol.id=pl.project_id
            WHERE e.parent_id IS NULL and e.expenses_group = 3 and e.sap_status = 'not_synced' and approve_status = 'finance_approve'";
            if ($is_type_req != '') {
                $query .= " and e.is_type_req = $is_type_req";
            }
            if ($expense_cat != '') {
                $query .= " and e.expense_cat = $expense_cat";
            }
            $result = pg_query_params($this->conn, $query, array());
        } else {
            $query = "SELECT *, 
            (select sum(total_allowance) as amount from withdraws.tb_wrd_expenses_groups g where g.expenses_id=e.id) as total_allowance_group,
            (select sum(accommodation_total) as amount from withdraws.tb_wrd_expenses_groups g where g.expenses_id=e.id) as total_accommodation_group,
            (select sum(transport) as amount from withdraws.tb_wrd_expenses_groups g where g.expenses_id=e.id) as total_transport_group,
            (select sum(other_expenses) as amount from withdraws.tb_wrd_expenses_groups g where g.expenses_id=e.id) as total_other_group,
            (select sum(meal_total) as amount from withdraws.tb_wrd_expenses_groups g where g.expenses_id=e.id) as total_meal_group,
            (select sum(compensation) as amount from withdraws.tb_wrd_expenses_lists g where g.expenses_id=e.id) as total_compensation_group
            FROM withdraws.tb_wrd_expenses e
            WHERE parent_id = $1";
            if ($is_foreign != '') {
                $query .= " and e.is_foreign = $is_foreign";
            }
            if ($expense_cat != '') {
                $query .= " and e.expense_cat = $expense_cat";
            }
            $result = pg_query_params($this->conn, $query, array($parent_id));
        }

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $expenses = array();
        while ($row = pg_fetch_assoc($result)) {
            $expenses[] = $row;
        }

        return $expenses;
    }

    public function readAllReport($key, $limit = 10, $offset = 0, $expenses_number = '', $start_date = '', $end_date = '', $status = '', $req_user_code = '', $req_position_code = '', $req_depart_code = '', $search_user_code = '')
    {
        $base_query = "FROM withdraws.tb_wrd_expenses ";
        $base_query .= "WHERE tb_wrd_expenses.status_expenses != 'delete' AND plan_id IS NULL";
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

        // ✅ plan_number
        if ($expenses_number !== '') {
            $where_clauses[] = "expenses_number = $" . (count($params) + 1);
            $params[] = $expenses_number;
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
        $count_sql = "SELECT COUNT(tb_wrd_expenses.*) AS total " . $base_query;
        $result_count = pg_query_params($this->conn, $count_sql, $params);
        if (!$result_count) {
            return ["status" => "error", "message" => "Count query failed: " . pg_last_error($this->conn)];
        }

        $total_count = (int)pg_fetch_result($result_count, 0, 'total');
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ✅ Query ดึงข้อมูล
        $data_sql = "SELECT tb_wrd_expenses.*,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM withdraws.tb_wrd_compensations e 
                            WHERE tb_wrd_expenses.id = e.expenses_id AND e.status_compensation = 'active'
                        ) THEN 1 ELSE 0 
                    END AS has_compensation,
                    COALESCE(
                        (
                            SELECT e.id
                            FROM withdraws.tb_wrd_compensations e
                            WHERE tb_wrd_expenses.id = e.expenses_id AND e.status_compensation = 'active'
                            LIMIT 1
                        ), 0
                    ) AS compensation_id " . $base_query;
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

    public function getTransferByExpensesId($expenses_id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_expenses_transfer WHERE expenses_id = $1 ORDER BY created_at DESC";
        $result = pg_query_params($this->conn, $query, array($expenses_id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $transfers = array();
        while ($row = pg_fetch_assoc($result)) {
            $transfers[] = $row;
        }

        return $transfers;
    }

    public function getOneTransferByExpensesId($expenses_id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_expenses_transfer WHERE expenses_id = $1 ORDER BY created_at DESC LIMIT 1";
        $result = pg_query_params($this->conn, $query, array($expenses_id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        $data = pg_fetch_assoc($result);

        return $data;
    }

    public function saveTransfer($data)
    {
        $expenses_id = $data['main_data']['expenses_id'];
        $full_name = $data['main_data']['full_name'];
        $position_name = $data['main_data']['position_name'];
        $level_name = $data['main_data']['level_name'];
        $affiliation_name = $data['main_data']['affiliation_name'];
        $transfer_number = $data['main_data']['transfer_number'];
        $transfer_date = $this->format_date_to_db($data['main_data']['transfer_date']);
        $transfer_user_id = $data['main_data']['transfer_user_id'];

        $query = "INSERT INTO withdraws.tb_wrd_expenses_transfer (
            expenses_id,
            full_name,
            position_name,
            level_name,
            affiliation_name,
            transfer_number,
            transfer_date,
            transfer_user_id,
            created_at,
            updated_at
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW(), NOW()
        ) RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $expenses_id,
            $full_name,
            $position_name,
            $level_name,
            $affiliation_name,
            $transfer_number,
            $transfer_date,
            $transfer_user_id
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $return_id = pg_fetch_result($result, 0, 'id');
        return $return_id;
    }
}
