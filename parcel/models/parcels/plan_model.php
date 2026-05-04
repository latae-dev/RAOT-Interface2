<?php
class PlanModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function quarterCheck()
    {
        $month = date('n'); // ดึงค่าของเดือนปัจจุบัน (1-12)
        $quarter = ceil($month / 3); // หาร 3 แล้วปัดขึ้น
        return $quarter;
    }

    public function readAll($doc_type, $limit = 10, $offset = 0)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_plans WHERE doc_type = $1 AND status_plan != $2";
        $result_count = pg_query_params($this->conn, $query_count, [$doc_type, 'delete']);

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ดึงข้อมูลหลักพร้อมแบ่งหน้า
        $query = "
        SELECT id, plan_number, 
               plan_form_name, 
               status_plan, 
               plan_start, 
               plan_end, 
               created_at, 
               user_requ_name 
        FROM parcels.tb_plans 
        WHERE doc_type = $1 
        AND status_plan != $2
        ORDER BY created_at DESC
        LIMIT $3 OFFSET $4";

        $result = pg_query_params($this->conn, $query, [$doc_type, 'delete', $limit, $offset]);

        if ($result) {
            $rows = pg_fetch_all($result) ?: []; // ถ้าไม่มีข้อมูลให้คืนค่าเป็นอาร์เรย์ว่าง
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "quarter" => $this->quarterCheck(),
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }

    public function readAllSearch($doc_type, $limit = 10, $offset = 0, $plan_number = null, $start_date = null, $end_date = null, $status_plan = null)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_plans WHERE doc_type = $1 AND status_plan != 'delete'";
        $params = [$doc_type];

        if (!empty($plan_number)) {
            $query_count .= " AND plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query_count .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_plan)) {
            $query_count .= " AND status_plan = $" . (count($params) + 1);
            $params[] = $status_plan;
        }

        $result_count = pg_query_params($this->conn, $query_count, $params);

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ดึงข้อมูลหลักพร้อมแบ่งหน้า
        $query = "SELECT id, plan_number, plan_form_name, status_plan, plan_start, plan_end, created_at, user_requ, user_requ_name, 
        branch_id, province_id, area_id, head_office_id,
        user_branch,date_branch,status_branch,
        user_province,date_province,status_province,
        user_area,date_area,status_area,
        user_head_office,date_head_office,status_head_office,
        status_merge, plan_requ_all, branch_type, merge_provice
        FROM parcels.tb_plans WHERE doc_type = $1 AND status_plan != 'delete'";
        $params = [$doc_type];

        if (!empty($plan_number)) {
            $query .= " AND plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_plan)) {
            $query .= " AND status_plan = $" . (count($params) + 1);
            $params[] = $status_plan;
        }
        if (!empty($plan_number) && !empty($status_plan) && empty($start_date) && empty($end_date)) {
            $query .= " AND plan_number = $" . (count($params) + 1) . " AND status_plan = $" . (count($params) + 2);
            $params[] = $plan_number;
            $params[] = $status_plan;
        }

        $query .= " ORDER BY created_at DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if ($result) {
            $rows = pg_fetch_all($result) ?: [];
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "quarter" => $this->quarterCheck(),
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }

    // ฟังก์ชันในการดึงข้อมูลของ plan หนึ่งรายการ
    public function readPlanOne($id)
    {
        $query = "SELECT * FROM parcels.tb_plans WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function createPlanMerc($data)
    {

        $plan_number = isset($data['tb_plans']['plan_number']) ? $data['tb_plans']['plan_number'] : null;
        $doc_type = isset($data['tb_plans']['doc_type']) ? $data['tb_plans']['doc_type'] : null;
        $plan_requ_level = isset($data['tb_plans']['plan_requ_level']) ? $data['tb_plans']['plan_requ_level'] : null;
        $plan_requ_all = isset($data['tb_plans']['plan_requ_all']) ? $data['tb_plans']['plan_requ_all'] : null;
        $branch_type = isset($data['tb_plans']['branch_type']) ? $data['tb_plans']['branch_type'] : null;
        $status_merge = isset($data['tb_plans']['status_merge']) ? $data['tb_plans']['status_merge'] : null;
        $status_plan = isset($data['tb_plans']['status_plan']) ? $data['tb_plans']['status_plan'] : null;
        $user_requ = isset($data['tb_plans']['user_requ']) ? $data['tb_plans']['user_requ'] : null;
        $user_requ_name = isset($data['tb_plans']['user_requ_name']) ? $data['tb_plans']['user_requ_name'] : null;
        $branch_id = isset($data['tb_plans']['branch_id']) ? $data['tb_plans']['branch_id'] : null;
        $province_id = isset($data['tb_plans']['province_id']) ? $data['tb_plans']['province_id'] : null;
        $area_id = isset($data['tb_plans']['area_id']) ? $data['tb_plans']['area_id'] : null;
        $head_office_id = isset($data['tb_plans']['head_office_id']) ? $data['tb_plans']['head_office_id'] : null;
        $plan_form_name = isset($data['tb_plans']['plan_form_name']) ? $data['tb_plans']['plan_form_name'] : null;
        $depart_code = isset($data['tb_plans']['depart_code']) ? $data['tb_plans']['depart_code'] : null;
        $merge_provice = isset($data['tb_plans']['merge_provice']) ? $data['tb_plans']['merge_provice'] : 'no';
        $province_name = isset($data['tb_plans']['province_name']) ? $data['tb_plans']['province_name'] : '-';
        $status_province = 'approve';
        $status_branch = 'approve';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO parcels.tb_plans (
        plan_number,
        doc_type,
        plan_requ_level,
        plan_requ_all,
        branch_type,
        status_merge,
        status_plan,
        user_requ,
        user_requ_name,
        branch_id,
        province_id,
        area_id,
        head_office_id,
        plan_form_name,
        depart_code,
        merge_provice,
        province_name,
        status_province,
        status_branch
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19
    ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $doc_type,
            $plan_requ_level,
            $plan_requ_all,
            $branch_type,
            $status_merge,
            $status_plan,
            $user_requ,
            $user_requ_name,
            $branch_id,
            $province_id,
            $area_id,
            $head_office_id,
            $plan_form_name,
            $depart_code,
            $merge_provice,
            $province_name,
            $status_province,
            $status_branch
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงค่า plan_number ที่เพิ่ง insert
        $return_id = pg_fetch_result($result, 0, 'id');

        return $return_id; // คืนค่า plan_number
    }

    // ฟังก์ชันสำหรับการสร้าง plan ใหม่
    public function createPlan($data)
    {
        // ตรวจสอบค่าที่ส่งมาว่ามีหรือไม่ ถ้าไม่มีให้ใช้ค่า NULL แทน
        $plan_number = isset($data['tb_plans']['plan_number']) ? $data['tb_plans']['plan_number'] : null;
        $plan_number_first = isset($data['tb_plans']['plan_number_first']) ? $data['tb_plans']['plan_number_first'] : null;
        $plan_requ_level = isset($data['tb_plans']['plan_requ_level']) ? $data['tb_plans']['plan_requ_level'] : null;
        $plan_requ_all = isset($data['tb_plans']['plan_requ_all']) ? $data['tb_plans']['plan_requ_all'] : '[]';
        $plan_form_name = isset($data['tb_plans']['plan_form_name']) ? $data['tb_plans']['plan_form_name'] : null;
        $plan_start = isset($data['tb_plans']['plan_start']) ? $data['tb_plans']['plan_start'] : null;
        $plan_end = isset($data['tb_plans']['plan_end']) ? $data['tb_plans']['plan_end'] : null;
        $depart_code = isset($data['tb_plans']['depart_code']) ? $data['tb_plans']['depart_code'] : null;
        $quantity_total = isset($data['tb_plans']['quantity_total']) ? $data['tb_plans']['quantity_total'] : 0;
        $quantity_order = isset($data['tb_plans']['quantity_order']) ? $data['tb_plans']['quantity_order'] : 0;
        $quantity_outst = isset($data['tb_plans']['quantity_outst']) ? $data['tb_plans']['quantity_outst'] : 0;
        $date_deli = isset($data['tb_plans']['date_deli']) ? $data['tb_plans']['date_deli'] : null;
        $date_requ = isset($data['tb_plans']['date_requ']) ? $data['tb_plans']['date_requ'] : null;
        $date_approv = isset($data['tb_plans']['date_approv']) ? $data['tb_plans']['date_approv'] : null;
        $time_deli_plan = isset($data['tb_plans']['time_deli_plan']) ? $data['tb_plans']['time_deli_plan'] : null;
        $time_pick_plan = isset($data['tb_plans']['time_pick_plan']) ? $data['tb_plans']['time_pick_plan'] : null;
        $note_1 = isset($data['tb_plans']['note_1']) ? $data['tb_plans']['note_1'] : null;
        $note_2 = isset($data['tb_plans']['note_2']) ? $data['tb_plans']['note_2'] : null;
        $note_3 = isset($data['tb_plans']['note_3']) ? $data['tb_plans']['note_3'] : null;
        $status_merge = isset($data['tb_plans']['status_merge']) ? $data['tb_plans']['status_merge'] : 'none';
        $branch_type = isset($data['tb_plans']['branch_type']) ? $data['tb_plans']['branch_type'] : 'สาขา';
        $status_plan = isset($data['tb_plans']['status_plan']) ? $data['tb_plans']['status_plan'] : 'pending';
        $user_requ = isset($data['tb_plans']['user_requ']) ? $data['tb_plans']['user_requ'] : null;
        $user_requ_name = isset($data['tb_plans']['user_requ_name']) ? $data['tb_plans']['user_requ_name'] : null;
        $user_res_1 = isset($data['tb_plans']['user_res_1']) ? $data['tb_plans']['user_res_1'] : null;
        $user_res_2 = isset($data['tb_plans']['user_res_2']) ? $data['tb_plans']['user_res_2'] : null;
        $date_res_1 = isset($data['tb_plans']['date_res_1']) ? $data['tb_plans']['date_res_1'] : null;
        $date_res_2 = isset($data['tb_plans']['date_res_2']) ? $data['tb_plans']['date_res_2'] : null;
        $branch_id = isset($data['tb_plans']['branch_id']) ? $data['tb_plans']['branch_id'] : null;
        $province_id = isset($data['tb_plans']['province_id']) ? $data['tb_plans']['province_id'] : null;
        $area_id = isset($data['tb_plans']['area_id']) ? $data['tb_plans']['area_id'] : null;
        $head_office_id = isset($data['tb_plans']['head_office_id']) ? $data['tb_plans']['head_office_id'] : null;
        $at_close = isset($data['tb_plans']['at_close']) && $data['tb_plans']['at_close'] !== ''
            ? filter_var($data['tb_plans']['at_close'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $const_id = isset($data['tb_plans']['const_id']) && $data['tb_plans']['const_id'] !== ''
            ? filter_var($data['tb_plans']['const_id'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $doc_type = isset($data['tb_plans']['doc_type']) ? $data['tb_plans']['doc_type'] : 'master';

        $province_name = isset($data['tb_plans']['province_name']) ? $data['tb_plans']['province_name'] : '-';

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO parcels.tb_plans (
        plan_number,
        plan_number_first,
        plan_requ_level,
        plan_requ_all,
        plan_form_name,
        plan_start,
        plan_end,
        depart_code,
        quantity_total,
        quantity_order,
        quantity_outst,
        date_deli,
        date_requ,
        date_approv,
        time_deli_plan,
        time_pick_plan,
        note_1,
        note_2,
        note_3,
        status_merge,
        branch_type,
        status_plan,
        user_requ,
        user_requ_name,
        user_res_1,
        user_res_2,
        date_res_1,
        date_res_2,
        branch_id,
        province_id,
        area_id,
        head_office_id,
        at_close,
        const_id,
        doc_type,
        province_name
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36
    ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $plan_number,
            $plan_number_first,
            $plan_requ_level,
            $plan_requ_all,
            $plan_form_name,
            $plan_start,
            $plan_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_plan,
            $time_pick_plan,
            $note_1,
            $note_2,
            $note_3,
            $status_merge,
            $branch_type,
            $status_plan,
            $user_requ,
            $user_requ_name,
            $user_res_1,
            $user_res_2,
            $date_res_1,
            $date_res_2,
            $branch_id,
            $province_id,
            $area_id,
            $head_office_id,
            $at_close === null ? 'NULL' : ($at_close ? 'TRUE' : 'FALSE'),
            $const_id === null ? 'NULL' : ($const_id ? 'TRUE' : 'FALSE'),
            $doc_type,
            $province_name
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงค่า plan_number ที่เพิ่ง insert
        $return_id = pg_fetch_result($result, 0, 'id');

        return $return_id; // คืนค่า plan_number
    }

    public function updateStatusPlans($data, $id)
    {
        if (!is_array($data)) {
            return json_encode(['status' => 'error', 'message' => 'Invalid payload.']);
        }

        if ($id === null || $id === '' || !is_numeric($id)) {
            return json_encode(['status' => 'error', 'message' => 'Invalid id.']);
        }
        $id = (int) $id;
        if ($id <= 0) {
            return json_encode(['status' => 'error', 'message' => 'Invalid id.']);
        }

        // ลบ — sync กับ models/parcels/plan_model.php ที่ราก (parcel/controller โหลดไฟล์นี้)
        $statusPlanNorm = isset($data['status_plan']) ? strtolower(trim((string) $data['status_plan'])) : '';
        if ($statusPlanNorm === 'delete') {
            $userVal = $data['user_requ'] ?? $data['user_branch'] ?? '';
            $branchStatus = $data['status_branch'] ?? 'delete';
            $queryUpdate = 'UPDATE parcels.tb_plans SET status_plan = $1, user_requ = $2, status_branch = $3, date_requ = $4 WHERE id = $5';
            $params = ['delete', $userVal, $branchStatus, date('Y-m-d'), $id];
            $resultUpdate = pg_query_params($this->conn, $queryUpdate, $params);
            if (!$resultUpdate) {
                return json_encode(['status' => 'error', 'message' => 'Failed to update status plan: ' . pg_last_error($this->conn)]);
            }
            return json_encode(['status' => 'success', 'message' => 'Status plan updated successfully.']);
        }

        if (isset($data['status_plan']) && count($data) < 4) {
            $status = $data['status_plan'];
            $userVal = $data['user_requ'] ?? $data['user_branch'] ?? '';
            $branchStatus = $data['status_branch'] ?? $status;
            $queryUpdate = 'UPDATE parcels.tb_plans SET status_plan = $1, user_requ = $2, status_branch = $3, date_requ = $4 WHERE id = $5';
            $params = [$status, $userVal, $branchStatus, date('Y-m-d'), $id];
            $resultUpdate = pg_query_params($this->conn, $queryUpdate, $params);
            if (!$resultUpdate) {
                return json_encode(['status' => 'error', 'message' => 'Failed to update status plan: ' . pg_last_error($this->conn)]);
            }
            return json_encode(['status' => 'success', 'message' => 'Status plan updated successfully.']);
        }

        if (count($data) < 4) {
            return json_encode(['status' => 'error', 'message' => 'Invalid data keys.']);
        }

        $keys = array_keys($data);
        $status_plan_key = $keys[0] ?? null;
        $user_key = $keys[1] ?? null;
        $status_key = $keys[2] ?? null;
        $status_date_key = $keys[3] ?? null;

        if (!$status_plan_key || !$user_key || !$status_key || !$status_date_key) {
            return json_encode(['status' => 'error', 'message' => 'Missing required keys.']);
        }

        $queryUpdate = "UPDATE parcels.tb_plans 
                    SET $status_plan_key = $1, 
                        $user_key = $2, 
                        $status_key = $3, 
                        $status_date_key = $4  
                    WHERE id = $5";

        $params = [
            $data[$status_plan_key] ?? '',
            $data[$user_key] ?? '',
            $data[$status_key] ?? '',
            date("Y-m-d"),
            $id
        ];

        $resultUpdate = pg_query_params($this->conn, $queryUpdate, $params);

        if (!$resultUpdate) {
            return json_encode(['status' => 'error', 'message' => 'Failed to update status plan: ' . pg_last_error($this->conn)]);
        }

        return json_encode(['status' => 'success', 'message' => 'Status plan updated successfully.']);
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ plan
    public function updatePlan($data, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return false; // ป้องกันข้อผิดพลาดหากไม่มี ID
        }

        // กำหนดค่าข้อมูล
        $plan_start = $data['tb_plans']['plan_start'] ?? null;
        $plan_end = $data['tb_plans']['plan_end'] ?? null;
        $depart_code = $data['tb_plans']['depart_code'] ?? null;
        $quantity_total = is_numeric($data['tb_plans']['quantity_total'] ?? 0) ? (int)$data['tb_plans']['quantity_total'] : 0;
        $quantity_order = is_numeric($data['tb_plans']['quantity_order'] ?? 0) ? (int)$data['tb_plans']['quantity_order'] : 0;
        $quantity_outst = is_numeric($data['tb_plans']['quantity_outst'] ?? 0) ? (int)$data['tb_plans']['quantity_outst'] : 0;
        $date_deli = $data['tb_plans']['date_deli'] ?? null;
        $date_requ = $data['tb_plans']['date_requ'] ?? null;
        $date_approv = $data['tb_plans']['date_approv'] ?? null;
        $time_deli_plan = $data['tb_plans']['time_deli_plan'] ?? null;
        $time_pick_plan = $data['tb_plans']['time_pick_plan'] ?? null;
        $note_1 = $data['tb_plans']['note_1'] ?? null;
        $note_2 = $data['tb_plans']['note_2'] ?? null;
        $note_3 = $data['tb_plans']['note_3'] ?? null;
        $branch_type = $data['tb_plans']['branch_type'] ?? 'สาขา';
        $user_requ = $data['tb_plans']['user_requ'] ?? null;
        $user_requ_name = $data['tb_plans']['user_requ_name'] ?? null;
        $at_close = filter_var($data['tb_plans']['at_close'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $const_id = filter_var($data['tb_plans']['const_id'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';

        // คำสั่ง SQL UPDATE
        $query = "UPDATE parcels.tb_plans SET 
        plan_start = $1,
        plan_end = $2,
        depart_code = $3,
        quantity_total = $4,
        quantity_order = $5,
        quantity_outst = $6,
        date_deli = $7,
        date_requ = $8,
        date_approv = $9,
        time_deli_plan = $10,
        time_pick_plan = $11,
        note_1 = $12,
        note_2 = $13,
        note_3 = $14,
        branch_type = $15,
        user_requ = $16,
        user_requ_name = $17,
        at_close = $18,
        const_id = $19
        WHERE id = $20 AND status_plan = $21";

        // ใช้ pg_query_params() เพื่อส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $plan_start,
            $plan_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_plan,
            $time_pick_plan,
            $note_1,
            $note_2,
            $note_3,
            $branch_type,
            $user_requ,
            $user_requ_name,
            $at_close,
            $const_id,
            $id,
            'pending'
        ));

        return $result !== false;
    }

    public function updatePlanDoctype($data, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return false; // ป้องกันข้อผิดพลาดหากไม่มี ID
        }

        // กำหนดค่าข้อมูล
        $doc_type = 'master';
        $plan_start = $data['tb_plans']['plan_start'] ?? null;
        $plan_end = $data['tb_plans']['plan_end'] ?? null;
        $depart_code = $data['tb_plans']['depart_code'] ?? null;
        $quantity_total = isset($data['tb_plans']['quantity_total']) && is_numeric($data['tb_plans']['quantity_total']) ? (int)$data['tb_plans']['quantity_total'] : 0;
        $quantity_order = isset($data['tb_plans']['quantity_order']) && is_numeric($data['tb_plans']['quantity_order']) ? (int)$data['tb_plans']['quantity_order'] : 0;
        $quantity_outst = isset($data['tb_plans']['quantity_outst']) && is_numeric($data['tb_plans']['quantity_outst']) ? (int)$data['tb_plans']['quantity_outst'] : 0;
        $date_deli = $data['tb_plans']['date_deli'] ?? null;
        $date_requ = $data['tb_plans']['date_requ'] ?? null;
        $date_approv = $data['tb_plans']['date_approv'] ?? null;
        $time_deli_plan = $data['tb_plans']['time_deli_plan'] ?? null;
        $time_pick_plan = $data['tb_plans']['time_pick_plan'] ?? null;
        $note_1 = $data['tb_plans']['note_1'] ?? null;
        $note_2 = $data['tb_plans']['note_2'] ?? null;
        $note_3 = $data['tb_plans']['note_3'] ?? null;
        $branch_type = $data['tb_plans']['branch_type'] ?? 'สาขา';
        $user_requ = $data['tb_plans']['user_requ'] ?? null;
        $user_requ_name = $data['tb_plans']['user_requ_name'] ?? null;
        $at_close = filter_var($data['tb_plans']['at_close'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $const_id = filter_var($data['tb_plans']['const_id'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $status_plan = 'pending';

        // คำสั่ง SQL UPDATE (แก้ไข syntax)
        $query = "UPDATE parcels.tb_plans SET 
        doc_type = $1,
        plan_start = $2,
        plan_end = $3,
        depart_code = $4,
        quantity_total = $5,
        quantity_order = $6,
        quantity_outst = $7,
        date_deli = $8,
        date_requ = $9,
        date_approv = $10,
        time_deli_plan = $11,
        time_pick_plan = $12,
        note_1 = $13,
        note_2 = $14,
        note_3 = $15,
        branch_type = $16,
        user_requ = $17,
        user_requ_name = $18,
        at_close = $19,
        const_id = $20
        WHERE id = $21 AND status_plan = $22";

        // ใช้ pg_query_params() เพื่อส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $doc_type,
            $plan_start,
            $plan_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_plan,
            $time_pick_plan,
            $note_1,
            $note_2,
            $note_3,
            $branch_type,
            $user_requ,
            $user_requ_name,
            $at_close,
            $const_id,
            $id,
            $status_plan
        ));

        return $result !== false;
    }

    public function updateStatusMerge($data, $status)
    {

        $ids = array_map('intval', $data['tb_plan_mats']);

        // คำสั่ง SQL สำหรับ update status
        $query = "UPDATE parcels.tb_plans SET status_merge = $1 WHERE id = ANY($2)";
        $result = pg_query_params($this->conn, $query, array($status, '{' . implode(',', $ids) . '}'));

        return $result;
    }

    public function updateStatusPlanReguAll($data)
    {
        // แปลง JSON เป็น array ของ string เช่น ["68P0001", "68P0002"]
        $list = json_decode($data['plan_requ_all'], true);

        // ตรวจสอบข้อมูลเบื้องต้น
        if (!is_array($list) || !isset($data['status_head_office'])) {
            return false;
        }

        /*  */

        foreach ($list as $value) {
            $query = "UPDATE parcels.tb_plans SET status_head_office = $1 WHERE plan_number = $2";
            $params = array($data['status_head_office'], $value); // ส่งพารามิเตอร์ให้ครบ 2 ตัว
            $result = pg_query_params($this->conn, $query, $params);

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    public function updateStatusPlanReguAll_userArea($data)
    {
        // แปลง JSON เป็น array ของ string เช่น ["68P0001", "68P0002"]
        $list = json_decode($data['plan_requ_all'], true);

        // ตรวจสอบข้อมูลเบื้องต้น
        if (!is_array($list) || !isset($data['status_area'])) {
            return false;
        }

        /*  */

        foreach ($list as $value) {
            $query = "UPDATE parcels.tb_plans SET status_area = $1 WHERE plan_number = $2";
            $params = array($data['status_area'], $value); // ส่งพารามิเตอร์ให้ครบ 2 ตัว
            $result = pg_query_params($this->conn, $query, $params);

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    public function readApproveStatuslist($doc_type, $limit = 10, $offset = 0, $plan_number = null, $start_date = null, $end_date = null, $status_plan = null)
    {
        // สร้างคำสั่ง SQL พื้นฐานสำหรับการนับจำนวนทั้งหมด
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_plans WHERE 1=1";
        $query_data = "SELECT id, created_at, plan_number, plan_requ_level, status_merge, merge_provice, 
                           status_branch, status_province, status_area, status_head_office, branch_type, plan_requ_all, province_name 
                    FROM parcels.tb_plans 
                    WHERE 1=1";
        $params = array();
        $param_count = 1;

        // เพิ่มเงื่อนไขการค้นหาตาม doc_type
        if ($doc_type) {
            $query_count .= " AND doc_type = $" . $param_count;
            $query_data .= " AND doc_type = $" . $param_count;
            $params[] = $doc_type;
            $param_count++;
        }

        // ค้นหาตาม plan_number
        if ($plan_number) {
            $query_count .= " AND plan_number LIKE $" . $param_count;
            $query_data .= " AND plan_number LIKE $" . $param_count;
            $params[] = '%' . $plan_number . '%';
            $param_count++;
        }

        // ค้นหาตามช่วงวันที่
        if ($start_date && $end_date) {
            $query_count .= " AND created_at >= $" . $param_count;
            $query_data .= " AND created_at >= $" . $param_count;
            $params[] = $start_date;
            $param_count++;

            $query_count .= " AND created_at <= $" . $param_count;
            $query_data .= " AND created_at <= $" . $param_count;
            $params[] = $end_date;
            $param_count++;
        }

        // ค้นหาตามสถานะ
        if ($status_plan) {
            $query_count .= " AND status_plan = $" . $param_count;
            $query_data .= " AND status_plan = $" . $param_count;
            $params[] = $status_plan;
            $param_count++;
        }

        // ดำเนินการนับจำนวนทั้งหมด
        $result_count = pg_query_params($this->conn, $query_count, $params);

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // เพิ่มการเรียงลำดับและ limit ให้กับ query ดึงข้อมูลหลัก
        $query_data .= " ORDER BY created_at DESC LIMIT $" . $param_count . " OFFSET $" . ($param_count + 1);

        // เพิ่มพารามิเตอร์สำหรับ limit และ offset
        $data_params = $params; // ใช้พารามิเตอร์เดิมสำหรับเงื่อนไข
        $data_params[] = $limit;
        $data_params[] = $offset;

        // ดำเนินการ query ดึงข้อมูลหลัก
        $result_data = pg_query_params($this->conn, $query_data, $data_params);

        if ($result_data) {
            $rows = pg_fetch_all($result_data) ?: [];
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "quarter" => $this->quarterCheck(),
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }
}
