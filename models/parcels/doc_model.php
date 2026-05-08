<?php
class DocModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAllDoc($doc_type, $limit = 10, $offset = 0, $doc_number = null, $start_date = null, $end_date = null, $status_doc = null)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_docs WHERE doc_type = $1 AND status_doc != 'delete'";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query_count .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query_count .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query_count .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
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
        $query = "SELECT D.id, D.doc_number, D.doc_form_name, D.status_doc, D.doc_start, D.doc_end, D.created_at, D.user_requ_name, DM.quarter,
        D.user_requ, D.branch_id, D.province_id, D.area_id, D.head_office_id,
        D.user_branch,D.date_branch,D.status_branch,
        D.user_province,D.date_province,D.status_province,
        D.user_area,D.date_area,D.status_area,
        D.user_head_office,D.date_head_office,D.status_head_office
        FROM parcels.tb_docs AS D
        LEFT JOIN (
            SELECT DISTINCT ON (doc_id) doc_id, quarter
            FROM parcels.tb_doc_mats
            ORDER BY doc_id, id ASC
        ) AS DM ON DM.doc_id = D.id
        WHERE doc_type = $1 AND status_doc != 'delete'";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
        }
        if (!empty($doc_number) && !empty($status_doc) && empty($start_date) && empty($end_date)) {
            $query .= " AND doc_number = $" . (count($params) + 1) . " AND status_doc = $" . (count($params) + 2);
            $params[] = $doc_number;
            $params[] = $status_doc;
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
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }

    public function readAllSearchlist($doc_type, $limit = 10, $offset = 0, $doc_number = null, $start_date = null, $end_date = null, $status_doc = null, $user_data = null)
    {
        $or = "user_requ = '".$user_data['user_code']."' ";

        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_docs WHERE doc_type = $1 AND status_doc != 'delete' AND $or ";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query_count .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query_count .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query_count .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
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
        $query = "SELECT D.id, D.doc_number, D.doc_form_name, D.status_doc, D.doc_start, D.doc_end, D.created_at, D.user_requ_name, DM.quarter,
        D.user_requ, D.branch_id, D.province_id, D.area_id, D.head_office_id,
        D.user_branch,D.date_branch,D.status_branch,
        D.user_province,D.date_province,D.status_province,
        D.user_area,D.date_area,D.status_area,
        D.user_head_office,D.date_head_office,D.status_head_office
        FROM parcels.tb_docs AS D
        LEFT JOIN (
            SELECT DISTINCT ON (doc_id) doc_id, quarter
            FROM parcels.tb_doc_mats
            ORDER BY doc_id, id ASC
        ) AS DM ON DM.doc_id = D.id
        WHERE doc_type = $1 AND status_doc != 'delete' AND $or ";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
        }
        if (!empty($doc_number) && !empty($status_doc) && empty($start_date) && empty($end_date)) {
            $query .= " AND doc_number = $" . (count($params) + 1) . " AND status_doc = $" . (count($params) + 2);
            $params[] = $doc_number;
            $params[] = $status_doc;
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
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }

    // ฟังก์ชันในการดึงข้อมูลของ doc หนึ่งรายการ
    public function readDocOne($id)
    {
        $query = "SELECT * FROM parcels.tb_docs WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง doc ใหม่
    public function createDoc($data)
    {
        // ตรวจสอบค่าที่ส่งมาว่ามีหรือไม่ ถ้าไม่มีให้ใช้ค่า NULL แทน
        $doc_number = isset($data['tb_docs']['doc_number']) ? $data['tb_docs']['doc_number'] : null;
        $doc_number_first = isset($data['tb_docs']['doc_number_first']) ? $data['tb_docs']['doc_number_first'] : null;
        $doc_requ_level = isset($data['tb_docs']['doc_requ_level']) ? $data['tb_docs']['doc_requ_level'] : null;
        $doc_requ_all = isset($data['tb_docs']['doc_requ_all']) ? $data['tb_docs']['doc_requ_all'] : '[]';
        $doc_form_name = isset($data['tb_docs']['doc_form_name']) ? $data['tb_docs']['doc_form_name'] : null;
        $doc_start = isset($data['tb_docs']['doc_start']) ? $data['tb_docs']['doc_start'] : null;
        $doc_end = isset($data['tb_docs']['doc_end']) ? $data['tb_docs']['doc_end'] : null;
        $depart_code = isset($data['tb_docs']['depart_code']) ? $data['tb_docs']['depart_code'] : null;
        $quantity_total = isset($data['tb_docs']['quantity_total']) ? $data['tb_docs']['quantity_total'] : 0;
        $quantity_order = isset($data['tb_docs']['quantity_order']) ? $data['tb_docs']['quantity_order'] : 0;
        $quantity_outst = isset($data['tb_docs']['quantity_outst']) ? $data['tb_docs']['quantity_outst'] : 0;
        $date_deli = isset($data['tb_docs']['date_deli']) ? $data['tb_docs']['date_deli'] : null;
        $date_requ = isset($data['tb_docs']['date_requ']) ? $data['tb_docs']['date_requ'] : null;
        $date_approv = isset($data['tb_docs']['date_approv']) ? $data['tb_docs']['date_approv'] : null;
        $time_deli_doc = isset($data['tb_docs']['time_deli_doc']) ? $data['tb_docs']['time_deli_doc'] : null;
        $time_pick_doc = isset($data['tb_docs']['time_pick_doc']) ? $data['tb_docs']['time_pick_doc'] : null;
        $note_1 = isset($data['tb_docs']['note_1']) ? $data['tb_docs']['note_1'] : null;
        $note_2 = isset($data['tb_docs']['note_2']) ? $data['tb_docs']['note_2'] : null;
        $note_3 = isset($data['tb_docs']['note_3']) ? $data['tb_docs']['note_3'] : null;
        $status_merge = isset($data['tb_docs']['status_merge']) ? $data['tb_docs']['status_merge'] : 'none';
        $branch_type = isset($data['tb_docs']['branch_type']) ? $data['tb_docs']['branch_type'] : 'สาขา';
        $status_doc = isset($data['tb_docs']['status_doc']) ? $data['tb_docs']['status_doc'] : 'pending';
        $user_requ = isset($data['tb_docs']['user_requ']) ? $data['tb_docs']['user_requ'] : null;
        $user_requ_name = isset($data['tb_docs']['user_requ_name']) ? $data['tb_docs']['user_requ_name'] : null;
        $user_res_1 = isset($data['tb_docs']['user_res_1']) ? $data['tb_docs']['user_res_1'] : null;
        $user_res_2 = isset($data['tb_docs']['user_res_2']) ? $data['tb_docs']['user_res_2'] : null;
        $date_res_1 = isset($data['tb_docs']['date_res_1']) ? $data['tb_docs']['date_res_1'] : null;
        $date_res_2 = isset($data['tb_docs']['date_res_2']) ? $data['tb_docs']['date_res_2'] : null;
        $branch_id = isset($data['tb_docs']['branch_id']) ? $data['tb_docs']['branch_id'] : null;
        $province_id = isset($data['tb_docs']['province_id']) ? $data['tb_docs']['province_id'] : null;
        $area_id = isset($data['tb_docs']['area_id']) ? $data['tb_docs']['area_id'] : null;
        $head_office_id = isset($data['tb_docs']['head_office_id']) ? $data['tb_docs']['head_office_id'] : null;
        $at_close = isset($data['tb_docs']['at_close']) && $data['tb_docs']['at_close'] !== ''
            ? filter_var($data['tb_docs']['at_close'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $const_id = isset($data['tb_docs']['const_id']) && $data['tb_docs']['const_id'] !== ''
            ? filter_var($data['tb_docs']['const_id'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $doc_type = isset($data['tb_docs']['doc_type']) ? $data['tb_docs']['doc_type'] : 'master';
        $plan_id = isset($data['tb_docs']['plan_id']) ? $data['tb_docs']['plan_id'] : null;

        // สร้างคำสั่ง SQL INSERT พร้อม RETURNING
        $query = "INSERT INTO parcels.tb_docs (
        doc_number,
        doc_number_first,
        doc_requ_level,
        doc_requ_all,
        doc_form_name,
        doc_start,
        doc_end,
        depart_code,
        quantity_total,
        quantity_order,
        quantity_outst,
        date_deli,
        date_requ,
        date_approv,
        time_deli_doc,
        time_pick_doc,
        note_1,
        note_2,
        note_3,
        status_merge,
        branch_type,
        status_doc,
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
        plan_id
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, $35, $36
    ) RETURNING id";

        // ใช้ pg_query_params เพื่อเตรียมคำสั่ง SQL และส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $doc_number,
            $doc_number_first,
            $doc_requ_level,
            $doc_requ_all,
            $doc_form_name,
            $doc_start,
            $doc_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_doc,
            $time_pick_doc,
            $note_1,
            $note_2,
            $note_3,
            $status_merge,
            $branch_type,
            $status_doc,
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
            $plan_id
        ));

        // ตรวจสอบผลลัพธ์ของการทำงาน
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงค่า doc_number ที่เพิ่ง insert
        $return_id = pg_fetch_result($result, 0, 'id');

        return $return_id; // คืนค่า doc_number
    }

    public function updateStatusDocs($data, $id)
    {
        // ตรวจสอบว่ามีคีย์เพียงพอ
        if (count($data) < 4) {
            return json_encode(['status' => 'error', 'message' => 'Invalid data keys.']);
        }

        // ดึงคีย์และกำหนดให้ปลอดภัย
        $keys = array_keys($data);
        $status_doc_key = $keys[0] ?? null;
        $user_key = $keys[1] ?? null;
        $status_key = $keys[2] ?? null;
        $status_date_key = $keys[3] ?? null;

        // ตรวจสอบว่าคีย์ทั้งหมดมีค่าจริง
        if (!$status_doc_key || !$user_key || !$status_key || !$status_date_key) {
            return json_encode(['status' => 'error', 'message' => 'Missing required keys.']);
        }

        // สร้าง SQL Query
        $queryUpdate = "UPDATE parcels.tb_docs 
                    SET $status_doc_key = $1, 
                        $user_key = $2, 
                        $status_key = $3, 
                        $status_date_key = $4  
                    WHERE id = $5";

        // ป้องกันค่า null
        $params = [
            $data[$status_doc_key] ?? '',
            $data[$user_key] ?? '',
            $data[$status_key] ?? '',
            date("Y-m-d"),
            $id
        ];

        // ทำการอัปเดตข้อมูล
        $resultUpdate = pg_query_params($this->conn, $queryUpdate, $params);

        // ตรวจสอบผลลัพธ์
        if (!$resultUpdate) {
            return json_encode(['status' => 'error', 'message' => 'Failed to update status plan: ' . pg_last_error($this->conn)]);
        }

        // หากไม่อนุติต้องคืน stock
        if ($data[$status_key] == 'reject') {
            // คืน stock
            $queryCheck = "SELECT doc_mats.quarter, doc_mats.quantity, doc_mats.mat_code, docs.plan_id 
                FROM parcels.tb_doc_mats AS doc_mats 
                RIGHT JOIN parcels.tb_docs AS docs ON doc_mats.doc_id = docs.id
                WHERE doc_mats.doc_id = $1";
        
            $resultCheck = pg_query_params($this->conn, $queryCheck, array($id));
        
            if ($resultCheck && pg_num_rows($resultCheck) > 0) {
                while ($row = pg_fetch_assoc($resultCheck)) {
                    if ($row) {
                        $quarter = 'quarter_' . $row['quarter'] . '_use';
                        $quantity = $row['quantity'];
                        $mat_code = $row['mat_code'];
        
                        $queryUpdate = "UPDATE parcels.tb_plan_mats SET $quarter = $quarter - $1 WHERE mat_code = $2";
                        $resultUpdate = pg_query_params($this->conn, $queryUpdate, array($quantity, $mat_code));
                    }
                }
            }
        }

        return json_encode(['status' => 'success', 'message' => 'Status plan updated successfully.']);
    }

    public function deleteDoc($data, $id)
    {
        // ตรวจสอบสถานะเดิมก่อนว่ามีค่าเป็น 'pending' หรือไม่
        $queryCheck = "SELECT status_doc FROM parcels.tb_docs WHERE id = $1";
        $resultCheck = pg_query_params($this->conn, $queryCheck, array($id));

        if (!$resultCheck) {
            // หากเกิดข้อผิดพลาดในการดึงข้อมูล
            return json_encode(['status' => 'error', 'message' => 'Error querying the database: ' . pg_last_error($this->conn)]);
        }

        $row = pg_fetch_assoc($resultCheck);

        // ตรวจสอบว่า status_doc เดิมเป็น 'pending' หรือไม่
        if ($row['status_doc'] != 'pending') {
            // ถ้าสถานะไม่ใช่ 'pending'
            return json_encode(['status' => 'warning', 'message' => 'รายการนี้ถูกอัพเดทสถานะไปก่อนหน้านี้แล้ว.']);
        }

        // ถ้าสถานะเดิมเป็น 'pending', อัปเดต status_doc ใหม่
        $queryUpdate = "UPDATE parcels.tb_docs SET status_doc = $1  WHERE id = $2";
        $resultUpdate = pg_query_params($this->conn, $queryUpdate, array($data['status_doc'], $id));

        if (!$resultUpdate) {
            // หากเกิดข้อผิดพลาดในการอัปเดต
            return json_encode(['status' => 'error', 'message' => 'Failed to update status doc: ' . pg_last_error($this->conn)]);
        }

        // หากอัปเดตสำเร็จ
        return json_encode(['status' => 'success', 'message' => 'Status doc updated successfully.']);
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ doc
    public function updateDoc($data, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return false; // ป้องกันข้อผิดพลาดหากไม่มี ID
        }

        // กำหนดค่าข้อมูล
        $doc_start = $data['tb_docs']['doc_start'] ?? null;
        $doc_end = $data['tb_docs']['doc_end'] ?? null;
        $depart_code = $data['tb_docs']['depart_code'] ?? null;
        $quantity_total = is_numeric($data['tb_docs']['quantity_total'] ?? 0) ? (int)$data['tb_docs']['quantity_total'] : 0;
        $quantity_order = is_numeric($data['tb_docs']['quantity_order'] ?? 0) ? (int)$data['tb_docs']['quantity_order'] : 0;
        $quantity_outst = is_numeric($data['tb_docs']['quantity_outst'] ?? 0) ? (int)$data['tb_docs']['quantity_outst'] : 0;
        $date_deli = $data['tb_docs']['date_deli'] ?? null;
        $date_requ = $data['tb_docs']['date_requ'] ?? null;
        $date_approv = $data['tb_docs']['date_approv'] ?? null;
        $time_deli_doc = $data['tb_docs']['time_deli_doc'] ?? null;
        $time_pick_doc = $data['tb_docs']['time_pick_doc'] ?? null;
        $note_1 = $data['tb_docs']['note_1'] ?? null;
        $note_2 = $data['tb_docs']['note_2'] ?? null;
        $note_3 = $data['tb_docs']['note_3'] ?? null;
        $branch_type = $data['tb_docs']['branch_type'] ?? 'สาขา';
        $user_requ = $data['tb_docs']['user_requ'] ?? null;
        $user_requ_name = $data['tb_docs']['user_requ_name'] ?? null;
        $at_close = filter_var($data['tb_docs']['at_close'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $const_id = filter_var($data['tb_docs']['const_id'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';

        // คำสั่ง SQL UPDATE
        $query = "UPDATE parcels.tb_docs SET 
        doc_start = $1,
        doc_end = $2,
        depart_code = $3,
        quantity_total = $4,
        quantity_order = $5,
        quantity_outst = $6,
        date_deli = $7,
        date_requ = $8,
        date_approv = $9,
        time_deli_doc = $10,
        time_pick_doc = $11,
        note_1 = $12,
        note_2 = $13,
        note_3 = $14,
        branch_type = $15,
        user_requ = $16,
        user_requ_name = $17,
        at_close = $18,
        const_id = $19
        WHERE id = $20 AND status_doc = $21";

        // ใช้ pg_query_params() เพื่อส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $doc_start,
            $doc_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_doc,
            $time_pick_doc,
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

    public function updateDocDoctype($data, $id)
    {
        if (empty($id) || !is_numeric($id)) {
            return false; // ป้องกันข้อผิดพลาดหากไม่มี ID
        }

        // กำหนดค่าข้อมูล
        $doc_type = 'master';
        $doc_start = $data['tb_docs']['doc_start'] ?? null;
        $doc_end = $data['tb_docs']['doc_end'] ?? null;
        $depart_code = $data['tb_docs']['depart_code'] ?? null;
        $quantity_total = isset($data['tb_docs']['quantity_total']) && is_numeric($data['tb_docs']['quantity_total']) ? (int)$data['tb_docs']['quantity_total'] : 0;
        $quantity_order = isset($data['tb_docs']['quantity_order']) && is_numeric($data['tb_docs']['quantity_order']) ? (int)$data['tb_docs']['quantity_order'] : 0;
        $quantity_outst = isset($data['tb_docs']['quantity_outst']) && is_numeric($data['tb_docs']['quantity_outst']) ? (int)$data['tb_docs']['quantity_outst'] : 0;
        $date_deli = $data['tb_docs']['date_deli'] ?? null;
        $date_requ = $data['tb_docs']['date_requ'] ?? null;
        $date_approv = $data['tb_docs']['date_approv'] ?? null;
        $time_deli_doc = $data['tb_docs']['time_deli_doc'] ?? null;
        $time_pick_doc = $data['tb_docs']['time_pick_doc'] ?? null;
        $note_1 = $data['tb_docs']['note_1'] ?? null;
        $note_2 = $data['tb_docs']['note_2'] ?? null;
        $note_3 = $data['tb_docs']['note_3'] ?? null;
        $branch_type = $data['tb_docs']['branch_type'] ?? 'สาขา';
        $user_requ = $data['tb_docs']['user_requ'] ?? null;
        $user_requ_name = $data['tb_docs']['user_requ_name'] ?? null;
        $at_close = filter_var($data['tb_docs']['at_close'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $const_id = filter_var($data['tb_docs']['const_id'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $status_doc = 'pending';

        // คำสั่ง SQL UPDATE (แก้ไข syntax)
        $query = "UPDATE parcels.tb_docs SET 
        doc_type = $1,
        doc_start = $2,
        doc_end = $3,
        depart_code = $4,
        quantity_total = $5,
        quantity_order = $6,
        quantity_outst = $7,
        date_deli = $8,
        date_requ = $9,
        date_approv = $10,
        time_deli_doc = $11,
        time_pick_doc = $12,
        note_1 = $13,
        note_2 = $14,
        note_3 = $15,
        branch_type = $16,
        user_requ = $17,
        user_requ_name = $18,
        at_close = $19,
        const_id = $20
        WHERE id = $21 AND status_doc = $22";

        // ใช้ pg_query_params() เพื่อส่งข้อมูล
        $result = pg_query_params($this->conn, $query, array(
            $doc_type,
            $doc_start,
            $doc_end,
            $depart_code,
            $quantity_total,
            $quantity_order,
            $quantity_outst,
            $date_deli,
            $date_requ,
            $date_approv,
            $time_deli_doc,
            $time_pick_doc,
            $note_1,
            $note_2,
            $note_3,
            $branch_type,
            $user_requ,
            $user_requ_name,
            $at_close,
            $const_id,
            $id,
            $status_doc
        ));

        return $result !== false;
    }

    public function getDocByPlanAndQuarter($quarter, $plan_id)
    {
        $query = "SELECT docs.doc_number, doc_mats.quarter, docs.plan_id
                FROM parcels.tb_doc_mats AS doc_mats
                RIGHT JOIN parcels.tb_docs AS docs ON doc_mats.doc_id = docs.id
                WHERE doc_mats.quarter = $1 AND docs.plan_id = $2";

        $result = pg_query_params($this->conn, $query, array($quarter, $plan_id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
