<?php

/**
 * Investment Budget Requests Model (โมเดลจัดการคำขอลงทุน)
 *
 * หน้าที่:
 * - จัดการข้อมูลคำขอลงทุน (1 request = 1 item)
 * - รองรับการสร้าง แก้ไข อ่าน และลบข้อมูล
 * - จัดการ transaction เพื่อความสมบูรณ์ของข้อมูล
 * - สร้างเลขที่คำขออัตโนมัติ
 * - รองรับระบบ workflow และ approval
 *
 * @author Claude Code
 * @version 2.0 - Refactored to merge items into requests (1:1 relationship)
 */
class IbRequestsModel
{
    /**
     * การเชื่อมต่อฐานข้อมูล
     */
    private $conn;

    /**
     * ตารางหลักสำหรับคำขอลงทุน
     */
    private $table = 'investment_budget.tb_ib_requests';

    private $reportFieldsChecked = false;
    private $reportFieldsAvailable = false;


    /**
     * สร้างอินสแตนซ์ของโมเดล
     * @param resource $db การเชื่อมต่อฐานข้อมูล PostgreSQL
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function hasReportFieldColumns()
    {
        return $this->ensureReportFieldSupport();
    }

    private function ensureReportFieldSupport()
    {
        if ($this->reportFieldsChecked) {
            return $this->reportFieldsAvailable;
        }

        $this->reportFieldsChecked = true;

        $columns = [
            'equipment_code_type_id',
            'equipment_type_id',
            'count_unit_id',
            'account_code'
        ];

        $placeholders = implode("','", array_map(function ($col) {
            return pg_escape_string($this->conn, $col);
        }, $columns));

        $query = "SELECT column_name FROM information_schema.columns WHERE table_schema = 'investment_budget' AND table_name = 'tb_ib_requests' AND column_name IN ('{$placeholders}')";

        $result = pg_query($this->conn, $query);
        if (!$result) {
            // error_log('Database error while checking report fields: ' . pg_last_error($this->conn));
            $this->reportFieldsAvailable = false;
            return false;
        }

        $found = [];
        while ($row = pg_fetch_assoc($result)) {
            $found[] = $row['column_name'];
        }

        $this->reportFieldsAvailable = count(array_intersect($columns, $found)) === count($columns);
        return $this->reportFieldsAvailable;
    }

    // ========================================
    // CREATE OPERATIONS (การสร้างข้อมูล)
    // ========================================

    /**
     * สร้างคำขอลงทุนใหม่ (Create new investment budget request)
     *
     * @param array $data ข้อมูลคำขอลงทุน (รวมข้อมูล item เนื่องจาก 1 request = 1 item)
     *                    - fiscal_year_id (int): รหัสปีงบประมาณ
     *                    - version_plan_id (int): รหัสเวอร์ชั่นแผน
     *                    - asset_type_id (int): รหัสประเภทสินทรัพย์
     *                    - budget_source_id (int): รหัสแหล่งงบประมาณ
     *                    - item_name (string): ชื่อรายการ
     *                    - description (string): รายละเอียด
     *                    - quantity (numeric): จำนวน
     *                    - unit_price (numeric): ราคาต่อหน่วย
     *                    - total_amount (numeric): จำนวนเงินรวม
     *                    - replacement_asset_numbers (array): หมายเลขครุภัณฑ์ทดแทน
     *                    - และอื่นๆ ตามโครงสร้างตาราง tb_ib_requests
     * @return array|false ข้อมูลคำขอที่สร้างขึ้น ['id' => int, 'request_number' => string] หรือ false หากเกิดข้อผิดพลาด
     */
    public function createRequest($data)
    {
        try {
            // เริ่มต้น transaction เพื่อความสมบูรณ์ของข้อมูล
            pg_query($this->conn, "BEGIN");

            $status = $data['status'] ?? 'draft';

            // สร้างเลขที่คำขออัตโนมัติ (เช่น 68B0001) เฉพาะเมื่อไม่ใช่ draft
            $request_number = null;
            if ($status !== 'draft') {
                $request_number = $this->generateRequestNumber($data['fiscal_year_id'] ?? null);
            }

            // แปลง PHP array เป็น PostgreSQL array format สำหรับ replacement_asset_numbers
            // จาก ['ABC001', 'ABC002'] เป็น '{"ABC001","ABC002"}'
            $replacement_asset_numbers = null;
            if (isset($data['replacement_asset_numbers']) && is_array($data['replacement_asset_numbers'])) {
                $replacement_asset_numbers = '{' . implode(',', array_map(function ($v) {
                    return '"' . pg_escape_string($this->conn, $v) . '"';
                }, $data['replacement_asset_numbers'])) . '}';
            }

            // คำนวณ functional_area จาก equipment_type + equipment_code_type
            $functional_area = $this->calculateFunctionalArea($data);

            // บันทึกข้อมูลคำขอพร้อมข้อมูล item
            $query = "INSERT INTO " . $this->table . "
                     (request_number, fiscal_year_id, version_plan_id, asset_type_id,
                      asset_type_code, status,
                      budget_source_id, item_name, description, reason,
                      quantity, unit_price, total_amount, has_standard, asset_nature,
                      asset_category, replacement_asset_numbers, area_rai,
                      project_start_period, project_end_period, operation_year,
                      strategy, project_program, expected_disbursement_date,
                      multi_year_disbursement, work_period, attachment_files,
                      functional_area, created_by, created_at, updated_at)
                     VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, NOW(), NOW())
                     RETURNING id";

            $result = pg_query_params($this->conn, $query, [
                $request_number,
                $data['fiscal_year_id'],
                $data['version_plan_id'],
                $data['asset_type_id'],
                $data['asset_type_code'] ?? '',
                $status,
                $data['budget_source_id'] ?? null,
                $data['item_name'] ?? null,
                $data['description'] ?? null,
                $data['reason'] ?? null,
                $data['quantity'] ?? null,
                $data['unit_price'] ?? null,
                $data['total_amount'] ?? 0,
                // has_standard เป็น integer (foreign key ไปที่ tb_equipment_standards)
                $data['has_standard'] ?? null,
                $data['asset_nature'] ?? null,
                $data['asset_category'] ?? null,
                $replacement_asset_numbers,
                $data['area_rai'] ?? null,
                $data['project_start_period'] ?? null,
                $data['project_end_period'] ?? null,
                $data['operation_year'] ?? null,
                $data['strategy'] ?? null,
                $data['project_program'] ?? null,
                $data['expected_disbursement_date'] ?? null,
                // multi_year_disbursement เป็น integer (foreign key ไปที่ tb_disbursement_plan)
                $data['multi_year_disbursement'] ?? null,
                $data['work_period'] ?? null,
                isset($data['attachment_files']) ? json_encode($data['attachment_files']) : null,
                $functional_area,
                $data['created_by']
            ]);

            if (!$result) {
                throw new Exception("Failed to create request: " . pg_last_error($this->conn));
            }

            $request_row = pg_fetch_assoc($result);
            $request_id = $request_row['id'];

            // ยืนยันการบันทึกข้อมูล
            pg_query($this->conn, "COMMIT");

            // error_log("✅ สร้างคำขอสำเร็จ ID: {$request_id}, Number: {$request_number}");

            return [
                'id' => $request_id,
                'request_number' => $request_number
            ];
        } catch (Exception $e) {
            // ยกเลิกการบันทึกหากเกิดข้อผิดพลาด
            pg_query($this->conn, "ROLLBACK");
            // error_log("❌ Error in createRequest: " . $e->getMessage());
            // error_log("❌ Stack trace: " . $e->getTraceAsString());
            // error_log("❌ Data sent: " . json_encode($data));
            return false;
        }
    }

    public function getAttachmentFilePaths($requestId)
    {
        $query = "SELECT attachment_files FROM {$this->table} WHERE id = $1";
        $result = pg_query_params($this->conn, $query, [$requestId]);

        if (!$result) {
            // error_log('❌ Failed to fetch attachment_files: ' . pg_last_error($this->conn));
            return [];
        }

        $row = pg_fetch_assoc($result);
        if (!$row || empty($row['attachment_files'])) {
            return [];
        }

        $decoded = json_decode($row['attachment_files'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function updateAttachmentFilesColumn($requestId, array $attachments)
    {
        $query = "UPDATE {$this->table}
                  SET attachment_files = $2, updated_at = NOW()
                  WHERE id = $1";

        $paths = array_values(array_filter($attachments, function ($item) {
            return is_string($item) && $item !== '';
        }));

        $json = !empty($paths) ? json_encode($paths) : null;

        $result = pg_query_params($this->conn, $query, [$requestId, $json]);

        if (!$result) {
            // error_log('❌ Failed to update attachment_files column: ' . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ========================================
    // UPDATE OPERATIONS (การปรับปรุงข้อมูล)
    // ========================================

    /**
     * ปรับปรุงข้อมูลคำขอลงทุน (Update investment budget request)
     *
     * @param int $id รหัสคำขอลงทุนที่ต้องการแก้ไข
     * @param array $data ข้อมูลใหม่ (รวมข้อมูล item เนื่องจาก 1 request = 1 item)
     *                    - ฟิลด์เดียวกับ createRequest()
     *                    - ต้องมี updated_by สำหรับ audit trail
     * @return bool true หากปรับปรุงสำเร็จ, false หากเกิดข้อผิดพลาด
     */
    public function updateRequest($id, $data)
    {
        try {
            // เริ่มต้น transaction เพื่อความสมบูรณ์ของข้อมูล
            pg_query($this->conn, "BEGIN");

            // แปลง PHP array เป็น PostgreSQL array format สำหรับ replacement_asset_numbers
            $replacement_asset_numbers = null;
            if (isset($data['replacement_asset_numbers']) && is_array($data['replacement_asset_numbers'])) {
                $replacement_asset_numbers = '{' . implode(',', array_map(function ($v) {
                    return '"' . pg_escape_string($this->conn, $v) . '"';
                }, $data['replacement_asset_numbers'])) . '}';
            }

            // คำนวณ functional_area จาก equipment_type + equipment_code_type
            $functional_area = $this->calculateFunctionalArea($data);

            // ปรับปรุงข้อมูลคำขอพร้อมข้อมูล item
            $query = "UPDATE " . $this->table . "
                     SET fiscal_year_id = $1, version_plan_id = $2, asset_type_id = $3,
                         asset_type_code = $4, status = $5,
                         budget_source_id = $6, item_name = $7, description = $8, reason = $9,
                         quantity = $10, unit_price = $11, total_amount = $12, has_standard = $13,
                         asset_nature = $14, asset_category = $15, replacement_asset_numbers = $16,
                         area_rai = $17, project_start_period = $18, project_end_period = $19,
                         operation_year = $20, strategy = $21, project_program = $22,
                         expected_disbursement_date = $23, multi_year_disbursement = $24,
                         work_period = $25, attachment_files = $26,
                         request_number = COALESCE($27, request_number),
                         functional_area = $28,
                         updated_by = $29, updated_at = NOW()
                     WHERE id = $30";

            $existingAttachments = $this->getAttachmentFilePaths($id);
            $attachmentJson = isset($data['attachment_files'])
                ? json_encode($data['attachment_files'])
                : (!empty($existingAttachments) ? json_encode($existingAttachments) : null);

            $result = pg_query_params($this->conn, $query, [
                $data['fiscal_year_id'],
                $data['version_plan_id'],
                $data['asset_type_id'],
                $data['asset_type_code'] ?? '',
                $data['status'] ?? 'draft',
                $data['budget_source_id'] ?? null,
                $data['item_name'] ?? null,
                $data['description'] ?? null,
                $data['reason'] ?? null,
                $data['quantity'] ?? null,
                $data['unit_price'] ?? null,
                $data['total_amount'] ?? 0,
                // has_standard เป็น integer (foreign key ไปที่ tb_equipment_standards)
                $data['has_standard'] ?? null,
                $data['asset_nature'] ?? null,
                $data['asset_category'] ?? null,
                $replacement_asset_numbers,
                $data['area_rai'] ?? null,
                $data['project_start_period'] ?? null,
                $data['project_end_period'] ?? null,
                $data['operation_year'] ?? null,
                $data['strategy'] ?? null,
                $data['project_program'] ?? null,
                $data['expected_disbursement_date'] ?? null,
                // multi_year_disbursement เป็น integer (foreign key ไปที่ tb_disbursement_plan)
                $data['multi_year_disbursement'] ?? null,
                $data['work_period'] ?? null,
                $attachmentJson,
                $data['request_number'] ?? null,
                $functional_area,
                $data['updated_by'],
                $id
            ]);

            if (!$result) {
                throw new Exception("Failed to update request: " . pg_last_error($this->conn));
            }

            // ยืนยันการปรับปรุง
            pg_query($this->conn, "COMMIT");

            // error_log("✅ อัพเดตคำขอสำเร็จ ID: {$id}");

            return true;
        } catch (Exception $e) {
            // ยกเลิกการปรับปรุงหากเกิดข้อผิดพลาด
            pg_query($this->conn, "ROLLBACK");
            // error_log("❌ Error in updateRequest: " . $e->getMessage());
            return false;
        }
    }

    public function updateReportFields($id, array $fields, $updatedBy = null)
    {
        if (!$this->ensureReportFieldSupport()) {
            // error_log('Report field update attempted but columns are unavailable.');
            return false;
        }

        $allowed = ['equipment_code_type_id', 'equipment_type_id', 'count_unit_id', 'account_code'];
        $setClauses = [];
        $params = [];
        $index = 1;

        // ถ้ามีการแก้ไข equipment_type_id หรือ equipment_code_type_id ให้คำนวณ functional_area ใหม่
        $needsRecalculateFunctionalArea = isset($fields['equipment_type_id']) || isset($fields['equipment_code_type_id']);

        // ถ้ามีการแก้ไข equipment fields (ชนิด, ประเภท, หน่วยนับ) ให้ reset sap_status เป็น not_synced
        $needsResetSapStatus = isset($fields['equipment_code_type_id']) || isset($fields['equipment_type_id']) || isset($fields['count_unit_id']);

        foreach ($allowed as $field) {
            if (array_key_exists($field, $fields)) {
                $setClauses[] = "$field = $" . $index;
                $params[] = $fields[$field];
                $index++;
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        // คำนวณ functional_area ใหม่ถ้ามีการแก้ไข equipment fields
        if ($needsRecalculateFunctionalArea) {
            // ดึงข้อมูลปัจจุบันเพื่อเอา equipment_type_id และ equipment_code_type_id
            $currentData = $this->readRequest($id);
            if ($currentData) {
                // Merge ข้อมูลเดิมกับข้อมูลใหม่
                $mergedData = array_merge($currentData, $fields);
                $functional_area = $this->calculateFunctionalArea($mergedData);

                if ($functional_area !== null) {
                    $setClauses[] = "functional_area = $" . $index;
                    $params[] = $functional_area;
                    $index++;
                }
            }
        }

        // Reset SAP status เป็น not_synced ถ้ามีการแก้ไข equipment fields
        if ($needsResetSapStatus) {
            $setClauses[] = "sap_status = $" . $index;
            $params[] = 'not_synced';
            $index++;
        }

        if ($updatedBy !== null) {
            $setClauses[] = "updated_by = $" . $index;
            $params[] = $updatedBy;
            $index++;
        }

        $setClauses[] = "updated_at = NOW()";

        $params[] = $id;

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $setClauses) . " WHERE id = $" . $index;

        $result = pg_query_params($this->conn, $query, $params);

        return $result !== false;
    }

    // ========================================
    // READ OPERATIONS (การอ่านข้อมูล)
    // ========================================

    /**
     * อ่านคำขอลงทุนตาม ID พร้อมข้อมูลที่เกี่ยวข้อง
     *
     * @param int $id รหัสคำขอลงทุน
     * @return array|false ข้อมูลคำขอหรือ false หากเกิดข้อผิดพลาด
     */
    public function readRequest($id)
    {
        $reportSelect = '';
        $reportJoins = '';
        if ($this->ensureReportFieldSupport()) {
            $reportSelect = ",
                         r.equipment_code_type_id,
                         r.equipment_type_id,
                         r.count_unit_id,
                         r.account_code,
                         ect.code AS equipment_code_type_code,
                         ect.name AS equipment_code_type_name,
                         et.code AS equipment_type_code,
                         et.name AS equipment_type_name,
                         cu.count_unit_name AS count_unit_name,
                         cu.count_unit_code AS count_unit_code";

            $reportJoins = "
                  LEFT JOIN parameters.tb_equipment_code_type ect ON r.equipment_code_type_id = ect.id
                  LEFT JOIN parameters.tb_equipment_type et ON r.equipment_type_id = et.id
                  LEFT JOIN parameters.tb_count_units cu ON r.count_unit_id = cu.id";
        } else {
            $reportSelect = ",
                         NULL::INTEGER AS equipment_code_type_id,
                         NULL::INTEGER AS equipment_type_id,
                         NULL::INTEGER AS count_unit_id,
                         NULL::VARCHAR AS account_code,
                         NULL::VARCHAR AS equipment_code_type_code,
                         NULL::VARCHAR AS equipment_code_type_name,
                         NULL::VARCHAR AS equipment_type_code,
                         NULL::VARCHAR AS equipment_type_name,
                         NULL::VARCHAR AS count_unit_name,
                         NULL::VARCHAR AS count_unit_code";
        }

        $query = "SELECT r.*,
                         fy.name AS fiscal_year_name,
                         vp.name AS version_plan_name,
                         vp.code AS version_plan_code,
                         at.name AS asset_type_name,
                         bs.name AS budget_source_name,
                         es.name AS asset_nature_name,
                         ac.name AS asset_category_name,
                         strat.name AS strategy_name,
                         dp.name AS multi_year_disbursement_name,
                         CONCAT(u.user_fname, ' ', u.user_lname) AS created_by_username,
                         CONCAT(u2.user_fname, ' ', u2.user_lname) AS approved_by_username,
                         d.id AS department_id,
                         d.depart_code AS department_code,
                         d.depart_name AS department_name,
                         b.id AS branch_id,
                         b.branch_code,
                         b.branch_name,
                         pv.id AS province_id,
                         pv.province_code,
                         pv.province_name,
                         ar.id AS area_id,
                         ar.area_code,
                         ar.area_name,
                         ho.id AS head_office_id,
                         ho.head_office_code,
                         ho.head_office_name" . $reportSelect . "
                  FROM " . $this->table . " r
                  LEFT JOIN parameters.tb_fiscal_year fy ON r.fiscal_year_id = fy.id
                  LEFT JOIN parameters.tb_ib_version_plan vp ON r.version_plan_id = vp.id
                  LEFT JOIN parameters.tb_asset_type at ON r.asset_type_id = at.id
                  LEFT JOIN parameters.tb_ib_budget_source bs ON r.budget_source_id = bs.id
                  LEFT JOIN parameters.tb_equipment_setup es ON r.asset_nature::integer = es.id
                  LEFT JOIN parameters.tb_asset_category ac ON r.asset_category::integer = ac.id
                  LEFT JOIN parameters.tb_enterprise_plan_strategic strat ON r.strategy::integer = strat.id
                  LEFT JOIN parameters.tb_disbursement_plan dp ON r.multi_year_disbursement::integer = dp.id" . $reportJoins . "
                  LEFT JOIN users.tb_users u ON r.created_by::text = u.id::text
                  LEFT JOIN users.tb_departs d ON d.id = u.depart_id
                  LEFT JOIN users.tb_branchs b ON b.id = u.branch_id
                  LEFT JOIN users.tb_provinces pv ON pv.id = b.province_id
                  LEFT JOIN users.tb_areas ar ON ar.id = pv.area_id
                  LEFT JOIN users.tb_head_offices ho ON ho.id = ar.head_office_id
                  LEFT JOIN users.tb_users u2 ON r.approved_by::text = u2.id::text
                  WHERE r.id = $1 AND r.is_deleted = false";

        $result = pg_query_params($this->conn, $query, [$id]);

        if (!$result) {
            // error_log("Database error in readRequest: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);

        if ($row) {
            if ($row['replacement_asset_numbers']) {
                $row['replacement_asset_numbers'] = $this->pgArrayParse($row['replacement_asset_numbers']);
            }

            if ($row['attachment_files']) {
                $decoded = json_decode($row['attachment_files'], true);
                $row['attachment_files'] = is_array($decoded) ? $decoded : [];
            }

            $attachmentPaths = $this->getAttachmentFilePaths($id);
            $row['attachments'] = $attachmentPaths;

            if (empty($row['attachment_files'])) {
                $row['attachment_files'] = $attachmentPaths;
            }

            $row['approvals_history'] = $this->getApprovalHistory($id);
        }

        return $row;
    }


    /**
     * อ่านคำขอลงทุนทั้งหมดพร้อมตัวกรอง
     *
     * @param array $filters ตัวกรองข้อมูล (status, created_by, fiscal_year_id)
     * @return array|false รายการคำขอหรือ false หากเกิดข้อผิดพลาด
     */
    public function readRequests($filters = [], $pagination = null)
    {
        $approvalScope = $filters['approval_scope'] ?? null;
        $includeScopeAnnotations = !empty($filters['include_scope_annotations']);
        $currentUserId = $filters['current_user_id'] ?? null;

        unset($filters['approval_scope'], $filters['include_scope_annotations'], $filters['current_user_id']);

        $where_conditions = ["r.is_deleted = false"];
        $params = [];
        $param_count = 0;

        // Add filters
        if (isset($filters['status']) && $filters['status'] !== '') {
            $param_count++;
            $where_conditions[] = "r.status = $" . $param_count;
            $params[] = $filters['status'];
        }

        if (!empty($filters['status_prefix'])) {
            $param_count++;
            $where_conditions[] = "r.status LIKE $" . $param_count;
            $params[] = $filters['status_prefix'] . '%';
        }

        // กรองตาม visible_statuses (status ที่ user เห็นได้ตามระดับ)
        if (!empty($filters['visible_statuses']) && is_array($filters['visible_statuses'])) {
            $statusPlaceholders = [];
            foreach ($filters['visible_statuses'] as $status) {
                $param_count++;
                $statusPlaceholders[] = "$" . $param_count;
                $params[] = $status;
            }
            $where_conditions[] = "(r.status IN (" . implode(',', $statusPlaceholders) . "))";
            // error_log('🔍 Status filter: r.status IN (' . implode(',', $filters['visible_statuses']) . ')');
        }

        if (isset($filters['created_by'])) {
            $param_count++;
            $where_conditions[] = "r.created_by = $" . $param_count;
            $params[] = $filters['created_by'];
        }

        if (isset($filters['fiscal_year_id'])) {
            $param_count++;
            $where_conditions[] = "r.fiscal_year_id = $" . $param_count;
            $params[] = $filters['fiscal_year_id'];
        }

        if (!empty($filters['request_number'])) {
            $param_count++;
            $where_conditions[] = "r.request_number ILIKE $" . $param_count;
            $params[] = '%' . $filters['request_number'] . '%';
        }

        if (!empty($filters['keyword'])) {
            $param_count++;
            $where_conditions[] = "(r.item_name ILIKE $" . $param_count . " OR r.description ILIKE $" . $param_count . ")";
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['start_date'])) {
            $param_count++;
            $where_conditions[] = "r.created_at >= $" . $param_count;
            $params[] = $filters['start_date'] . ' 00:00:00';
        }

        if (!empty($filters['end_date'])) {
            $param_count++;
            $where_conditions[] = "r.created_at <= $" . $param_count;
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        // สำหรับรายงาน: ไม่รวม draft
        if (!empty($filters['exclude_draft']) && $filters['exclude_draft'] === true) {
            $where_conditions[] = "r.status != 'draft'";
        }

        // department filter
        if (isset($filters['department_id']) && $filters['department_id'] !== '') {
            $param_count++;
            $where_conditions[] = "d.id = $" . $param_count;
            $params[] = $filters['department_id'];
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
            $param_count++;
            $where_conditions[] = "b.id = $" . $param_count;
            $params[] = $filters['branch_id'];
        }

        // budget source filter
        if (isset($filters['budget_source_id']) && $filters['budget_source_id'] !== '') {
            $param_count++;
            $where_conditions[] = "r.budget_source_id = $" . $param_count;
            $params[] = $filters['budget_source_id'];
        }

        // asset type filter
        if (isset($filters['asset_type_id']) && $filters['asset_type_id'] !== '') {
            $param_count++;
            $where_conditions[] = "r.asset_type_id = $" . $param_count;
            $params[] = $filters['asset_type_id'];
        }

        // SAP status filter
        if (isset($filters['sap_status']) && $filters['sap_status'] !== '') {
            $param_count++;
            $where_conditions[] = "r.sap_status = $" . $param_count;
            $params[] = $filters['sap_status'];
        }

        if ($currentUserId !== null && $currentUserId !== '') {
            $param_count++;
            $where_conditions[] = "r.created_by::text IS DISTINCT FROM $" . $param_count;
            $params[] = (string) $currentUserId;
        }

        if ($currentUserId !== null && $approvalScope === null) {
            // error_log('⚠️ No approval scope available for user ' . $currentUserId . ' - returning empty approvals list');

            if ($pagination !== null) {
                return [
                    'items' => [],
                    'meta' => [
                        'total' => 0,
                        'page' => max(1, (int) ($pagination['page'] ?? 1)),
                        'per_page' => max(1, (int) ($pagination['per_page'] ?? 10)),
                        'total_pages' => 1,
                        'filters' => 'NO_SCOPE',
                    ]
                ];
            }

            return [];
        }

        // Filter by approval scope (hierarchical)
        if ($approvalScope !== null && is_array($approvalScope)) {
            $scopeConditions = [];
            $scopeMap = [
                'branch_ids' => 'b.id',
                'province_ids' => 'pv.id',
                'area_ids' => 'ar.id',
                'head_office_ids' => 'ho.id',
            ];

            foreach ($scopeMap as $idsKey => $column) {
                $rawIds = array_filter($approvalScope[$idsKey] ?? [], static function ($value) {
                    return $value !== null && $value !== '';
                });

                if (empty($rawIds)) {
                    continue;
                }

                $hasWildcard = in_array('*', $rawIds, true);
                $filteredIds = array_values(array_filter($rawIds, static function ($value) {
                    return $value !== '*';
                }));

                if ($hasWildcard) {
                    $scopeConditions[] = 'TRUE';
                    continue;
                }

                if (!empty($filteredIds)) {
                    $placeholders = [];
                    foreach ($filteredIds as $value) {
                        $param_count++;
                        $placeholders[] = "$" . $param_count;
                        $params[] = (string) $value;
                    }
                    $scopeConditions[] = '(' . $column . "::text IN (" . implode(',', $placeholders) . "))";
                }
            }

            // เพิ่ม depart_ids filter (AND condition สำหรับ branch level)
            $departIds = array_filter($approvalScope['depart_ids'] ?? [], static function ($value) {
                return $value !== null && $value !== '';
            });

            if (!empty($departIds)) {
                $hasWildcard = in_array('*', $departIds, true);
                $filteredDepartIds = array_values(array_filter($departIds, static function ($value) {
                    return $value !== '*';
                }));

                // ถ้าไม่ใช่ wildcard ต้องกรอง department
                if (!$hasWildcard && !empty($filteredDepartIds)) {
                    $departPlaceholders = [];
                    foreach ($filteredDepartIds as $value) {
                        $param_count++;
                        $departPlaceholders[] = "$" . $param_count;
                        $params[] = (string) $value;
                    }
                    // กรองด้วย d.id (department ของผู้สร้าง)
                    $where_conditions[] = '(d.id::text IN (' . implode(',', $departPlaceholders) . '))';
                    // error_log('🔍 Department filter: d.id IN (' . implode(',', $filteredDepartIds) . ')');
                }
            }

            if (!empty($scopeConditions)) {
                $where_conditions[] = '(' . implode(' OR ', $scopeConditions) . ')';
                // error_log('🔍 Approval scope conditions: ' . implode(' OR ', $scopeConditions));
            } else {
                // error_log('⚠️ No scope conditions built from approval_scope');
            }
        }

        $where_clause = implode(' AND ', $where_conditions);
        // error_log('📋 WHERE clause: ' . ($where_clause ?: '(none)'));
        // error_log('📋 Parameters: ' . json_encode($params));

        $selectParts = [
            'r.*',
            'fy.name as fiscal_year_name',
            'vp.name as version_plan_name',
            'vp.code as version_plan_code',
            'at.name as asset_type_name',
            'bs.name as budget_source_name',
            'bs.code as budget_source_code',
            'es.name as asset_nature_name',
            'ac.name as asset_category_name',
            'ac.code as asset_category_code',
            "CONCAT(u.user_fname, ' ', u.user_lname) as created_by_username",
            'b.id as branch_id',
            'b.branch_code',
            'b.branch_name',
            'd.id as department_id',
            'd.depart_code as department_code',
            'd.depart_name as department_name',
            'pv.id as province_id',
            'pv.province_code',
            'pv.province_name',
            'ar.id as area_id',
            'ar.area_code',
            'ar.area_name',
            'ho.id as head_office_id',
            'ho.head_office_code',
            'ho.head_office_name'
        ];

        $joinParts = [
            "LEFT JOIN parameters.tb_fiscal_year fy ON r.fiscal_year_id = fy.id",
            "LEFT JOIN parameters.tb_ib_version_plan vp ON r.version_plan_id = vp.id",
            "LEFT JOIN parameters.tb_asset_type at ON r.asset_type_id = at.id",
            "LEFT JOIN parameters.tb_ib_budget_source bs ON r.budget_source_id = bs.id",
            "LEFT JOIN parameters.tb_equipment_setup es ON r.asset_nature::integer = es.id",
            "LEFT JOIN parameters.tb_asset_category ac ON r.asset_category::integer = ac.id",
            "LEFT JOIN users.tb_users u ON r.created_by::text = u.id::text",
            "LEFT JOIN users.tb_branchs b ON b.id = u.branch_id",
            "LEFT JOIN users.tb_provinces pv ON pv.id = b.province_id",
            "LEFT JOIN users.tb_areas ar ON ar.id = pv.area_id",
            "LEFT JOIN users.tb_head_offices ho ON ho.id = ar.head_office_id",
            "LEFT JOIN users.tb_departs d ON d.id = u.depart_id"
        ];

        if ($this->ensureReportFieldSupport()) {
            $selectParts = array_merge($selectParts, [
                'r.equipment_code_type_id',
                'r.equipment_type_id',
                'r.count_unit_id',
                'r.account_code',
                'ect.code as equipment_code_type_code',
                'ect.name as equipment_code_type_name',
                'et.code as equipment_type_code',
                'et.name as equipment_type_name',
                'cu.count_unit_name as count_unit_name',
                'cu.count_unit_code as count_unit_code'
            ]);

            $joinParts = array_merge($joinParts, [
                "LEFT JOIN parameters.tb_equipment_code_type ect ON r.equipment_code_type_id = ect.id",
                "LEFT JOIN parameters.tb_equipment_type et ON r.equipment_type_id = et.id",
                "LEFT JOIN parameters.tb_count_units cu ON r.count_unit_id = cu.id"
            ]);
        } else {
            $selectParts = array_merge($selectParts, [
                'NULL::INTEGER AS equipment_code_type_id',
                'NULL::INTEGER AS equipment_type_id',
                'NULL::INTEGER AS count_unit_id',
                'NULL::VARCHAR AS account_code',
                'NULL::VARCHAR AS equipment_code_type_code',
                'NULL::VARCHAR AS equipment_code_type_name',
                'NULL::VARCHAR AS equipment_type_code',
                'NULL::VARCHAR AS equipment_type_name',
                'NULL::VARCHAR AS count_unit_name',
                'NULL::VARCHAR AS count_unit_code'
            ]);
        }

        $selectClause = 'SELECT ' . implode(",\n                         ", $selectParts);
        $fromClause = " FROM " . $this->table . " r\n                  " . implode("\n                  ", $joinParts) . "\n                  WHERE " . $where_clause;

        $orderClause = " ORDER BY r.created_at DESC";

        $requests = [];

        if ($pagination !== null) {
            $page = isset($pagination['page']) ? max(1, (int) $pagination['page']) : 1;
            $perPage = isset($pagination['per_page']) ? (int) $pagination['per_page'] : 10;
            $perPage = max(1, min($perPage, 100));
            $offset = ($page - 1) * $perPage;

            $countQuery = "SELECT COUNT(*)" . $fromClause;
            $countResult = pg_query_params($this->conn, $countQuery, $params);

            if (!$countResult) {
                // error_log("Database error in readRequests count: " . pg_last_error($this->conn));
                return false;
            }

            $total = (int) pg_fetch_result($countResult, 0, 0);
            // error_log('📊 Found ' . $total . ' requests matching approval scope');

            $dataQuery = $selectClause . $fromClause . $orderClause . " LIMIT " . $perPage . " OFFSET " . $offset;
            $result = pg_query_params($this->conn, $dataQuery, $params);

            if (!$result) {
                // error_log("Database error in readRequests: " . pg_last_error($this->conn));
                return false;
            }

            while ($row = pg_fetch_assoc($result)) {
                $requests[] = $row;
            }

            $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
            if ($totalPages === 0) {
                $totalPages = 1;
            }

            return [
                'items' => $requests,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => $totalPages,
                    'filters' => $where_clause,
                ]
            ];
        }

        $dataQuery = $selectClause . $fromClause . $orderClause;
        $result = pg_query_params($this->conn, $dataQuery, $params);

        if (!$result) {
            // error_log("Database error in readRequests: " . pg_last_error($this->conn));
            return false;
        }

        while ($row = pg_fetch_assoc($result)) {
            $requests[] = $row;
        }

        return $requests;
    }

    public function getApprovalHistory($requestId)
    {
        $query = "SELECT a.id,
                         a.request_id,
                         a.approval_level,
                         a.action,
                         a.comments,
                         a.approved_at,
                         CONCAT(u.user_fname, ' ', u.user_lname) AS approver_name
                  FROM investment_budget.tb_ib_requests_approvals a
                  LEFT JOIN users.tb_users u ON a.approver_id::text = u.id::text
                  WHERE a.request_id = $1
                  ORDER BY a.approved_at ASC, a.id ASC";

        $result = pg_query_params($this->conn, $query, [$requestId]);

        if (!$result) {
            // error_log('Database error in getApprovalHistory: ' . pg_last_error($this->conn));
            return [];
        }

        $history = [];
        while ($row = pg_fetch_assoc($result)) {
            $row['stage'] = $this->levelToStage($row['approval_level'] ?? null);
            $row['stage_label'] = $this->getStageLabel($row['stage']);
            $row['action_label'] = $this->formatApprovalActionLabel($row['action']);
            $history[] = $row;
        }

        return $history;
    }

    public function addApprovalHistory($requestId, $stage, $action, $comments, $approverId)
    {
        $stage = strtolower((string) $stage);
        $level = $this->stageToLevel($stage);
        $action = strtolower($action);

        $nextId = $this->resolveNextApprovalHistoryId();

        if ($nextId !== null) {
            $query = "INSERT INTO investment_budget.tb_ib_requests_approvals
                      (id, request_id, approver_id, approval_level, action, comments, approved_at)
                      VALUES ($1, $2, $3, $4, $5, $6, NOW())";

            $params = [
                $nextId,
                $requestId,
                $approverId,
                $level,
                $action,
                $comments
            ];
        } else {
            $query = "INSERT INTO investment_budget.tb_ib_requests_approvals
                      (request_id, approver_id, approval_level, action, comments, approved_at)
                      VALUES ($1, $2, $3, $4, $5, NOW())";

            $params = [
                $requestId,
                $approverId,
                $level,
                $action,
                $comments
            ];
        }

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            // error_log('Database error in addApprovalHistory: ' . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    private function resolveNextApprovalHistoryId()
    {
        $sequenceCandidates = [
            "SELECT nextval('investment_budget.tb_ib_requests_approvals_id_seq') AS seq",
            "SELECT nextval('tb_ib_requests_approvals_id_seq') AS seq"
        ];

        foreach ($sequenceCandidates as $sequenceSql) {
            $result = @pg_query($this->conn, $sequenceSql);
            if ($result && pg_num_rows($result) > 0) {
                $value = pg_fetch_result($result, 0, 'seq');
                if ($value !== false && $value !== null) {
                    return (int) $value;
                }
            }
        }

        $result = pg_query($this->conn, "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM investment_budget.tb_ib_requests_approvals");
        if ($result && pg_num_rows($result) > 0) {
            $value = pg_fetch_result($result, 0, 'next_id');
            if ($value !== false && $value !== null) {
                return (int) $value;
            }
        }

        return null;
    }

    private function stageToLevel($stage)
    {
        $map = [
            'branch' => 1,
            'province' => 2,
            'area' => 3,
            'head_office' => 4,
        ];

        return $map[strtolower((string) $stage)] ?? null;
    }

    private function levelToStage($level)
    {
        $map = [
            1 => 'branch',
            2 => 'province',
            3 => 'area',
            4 => 'head_office',
        ];

        $level = (int) $level;
        return $map[$level] ?? null;
    }

    private function getStageLabel($stage)
    {
        switch (strtolower((string) $stage)) {
            case 'branch':
                return 'ระดับสาขา';
            case 'province':
                return 'ระดับจังหวัด/กอง';
            case 'area':
                return 'ระดับเขต/ฝ่าย';
            case 'head_office':
                return 'ระดับสำนักงานใหญ่';
            default:
                return null;
        }
    }

    private function formatApprovalActionLabel($action)
    {
        $action = strtolower((string) $action);
        switch ($action) {
            case 'approved':
            case 'approve':
                return 'อนุมัติ';
            case 'rejected':
            case 'reject':
                return 'ไม่อนุมัติ';
            default:
                return $action;
        }
    }

    // ========================================
    // HELPER METHODS (เมธอดช่วยเหลือ)
    // ========================================

    /**
     * Generate unique request number
     */
    public function generateNextRequestNumber($fiscalYearId = null)
    {
        return $this->generateRequestNumber($fiscalYearId);
    }

    private function generateRequestNumber($fiscalYearId = null)
    {
        $numericYear = null;

        if ($fiscalYearId) {
            $fiscalYearQuery = "SELECT name FROM parameters.tb_fiscal_year WHERE id = $1 LIMIT 1";
            $fiscalYearResult = pg_query_params($this->conn, $fiscalYearQuery, [$fiscalYearId]);

            if ($fiscalYearResult && pg_num_rows($fiscalYearResult) > 0) {
                $fiscalYearRow = pg_fetch_assoc($fiscalYearResult);
                if ($fiscalYearRow && !empty($fiscalYearRow['name'])) {
                    $numericName = preg_replace('/[^0-9]/', '', $fiscalYearRow['name']);
                    if ($numericName !== '') {
                        $numericYear = (int) $numericName;
                    }
                }
            }
        }

        if (!$numericYear) {
            $numericYear = (int) date('Y') + 543;
        }

        $yearSuffix = substr(str_pad((string) $numericYear, 2, '0', STR_PAD_LEFT), -2);
        $prefix = $yearSuffix . 'B';

        // Get the last request number for this fiscal year prefix
        $query = "SELECT request_number FROM " . $this->table . " 
                  WHERE request_number LIKE $1 
                  ORDER BY request_number DESC LIMIT 1";

        $result = pg_query_params($this->conn, $query, [$prefix . '%']);

        $sequence = 1;
        if ($result && pg_num_rows($result) > 0) {
            $last_request = pg_fetch_assoc($result);
            $last_number_part = substr($last_request['request_number'], strlen($prefix));
            if (is_numeric($last_number_part)) {
                $sequence = (int) $last_number_part + 1;
            }
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Parse PostgreSQL array format to PHP array
     * Converts {value1,value2} to ['value1', 'value2']
     */
    private function pgArrayParse($pgArray)
    {
        if (empty($pgArray) || $pgArray === '{}') {
            return [];
        }

        // Remove outer braces
        $pgArray = trim($pgArray, '{}');

        // Split by comma, handling quoted values
        $items = [];
        $current = '';
        $inQuotes = false;

        for ($i = 0; $i < strlen($pgArray); $i++) {
            $char = $pgArray[$i];

            if ($char === '"' && ($i === 0 || $pgArray[$i - 1] !== '\\')) {
                $inQuotes = !$inQuotes;
            } elseif ($char === ',' && !$inQuotes) {
                $items[] = trim($current, '"');
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $items[] = trim($current, '"');
        }

        return $items;
    }

    /**
     * Update request status
     */
    public function updateStatus($id, $status, $updated_by)
    {
        $query = "UPDATE " . $this->table . " 
                  SET status = $1, updated_by = $2, updated_at = NOW()";

        $params = [$status, $updated_by, $id];

        if ($status === 'submitted' || strpos($status, 'pending_') === 0) {
            $query .= ", submitted_at = NOW()";
        } elseif ($status === 'approved') {
            $query .= ", approved_at = NOW(), approved_by = $2";
        }

        $query .= " WHERE id = $3";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            // error_log("Database error in updateStatus: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Soft delete request
     */
    public function deleteRequest($id, $updated_by)
    {
        $query = "UPDATE " . $this->table . " 
                  SET is_deleted = true, updated_by = $1, updated_at = NOW()
                  WHERE id = $2";

        $result = pg_query_params($this->conn, $query, [$updated_by, $id]);

        if (!$result) {
            // error_log("Database error in deleteRequest: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }

    public function getVersionPlanCodeById($versionPlanId)
    {
        if (!$versionPlanId) {
            return null;
        }

        $query = "SELECT code FROM parameters.tb_ib_version_plan WHERE id = $1";
        $result = pg_query_params($this->conn, $query, [$versionPlanId]);

        if (!$result) {
            // error_log("Database error in getVersionPlanCodeById: " . pg_last_error($this->conn));
            return null;
        }

        $row = pg_fetch_assoc($result);
        return $row['code'] ?? null;
    }

    /**
     * คำนวณ Functional Area (ขอบเขตหน้าที่) จาก equipment_type + equipment_code_type
     * Format: 2 + ประเภทครุภัณฑ์ 4 ตัวหน้า + ชนิดครุภัณฑ์ 3 ตัวหน้า
     *
     * @param array $data ข้อมูลคำขอที่มี equipment_type_id และ equipment_code_type_id
     * @return string|null Functional Area Code
     */
    private function calculateFunctionalArea($data)
    {
        // ถ้าไม่มี equipment_type_id หรือ equipment_code_type_id ให้ return null
        if (empty($data['equipment_type_id']) || empty($data['equipment_code_type_id'])) {
            return null;
        }

        // ดึง code จาก equipment_type
        $equipmentTypeCode = null;
        $queryType = "SELECT code FROM parameters.tb_equipment_type WHERE id = $1";
        $resultType = pg_query_params($this->conn, $queryType, [$data['equipment_type_id']]);
        if ($resultType && $rowType = pg_fetch_assoc($resultType)) {
            $equipmentTypeCode = $rowType['code'];
        }

        // ดึง code จาก equipment_code_type
        $equipmentCodeTypeCode = null;
        $queryCodeType = "SELECT code FROM parameters.tb_equipment_code_type WHERE id = $1";
        $resultCodeType = pg_query_params($this->conn, $queryCodeType, [$data['equipment_code_type_id']]);
        if ($resultCodeType && $rowCodeType = pg_fetch_assoc($resultCodeType)) {
            $equipmentCodeTypeCode = $rowCodeType['code'];
        }

        // ถ้าได้ code ทั้ง 2 ตัว ให้สร้าง functional_area
        if ($equipmentTypeCode && $equipmentCodeTypeCode) {
            // ตัด equipment_type เอา 4 ตัวหน้า
            $equipmentTypePart = substr($equipmentTypeCode, 0, 4);

            // ตัด equipment_code_type เอา 3 ตัวหน้า
            $equipmentCodeTypePart = substr($equipmentCodeTypeCode, 0, 3);

            // Format: 2 + 4 ตัวหน้า + 3 ตัวหน้า
            // ตัวอย่าง: 2 + 1401 + 212 (จาก 21201011) = 21401212
            return '2' . $equipmentTypePart . $equipmentCodeTypePart;
        }

        return null;
    }

    /**
     * Batch update SAP status for multiple requests
     *
     * @param array $requestIds Array of request IDs to update
     * @param string $status Status to set (default: 'success')
     * @return bool true if successful, false otherwise
     */
    public function updateSapStatus($requestIds, $status = 'success')
    {
        if (empty($requestIds) || !is_array($requestIds)) {
            return false;
        }

        try {
            // สร้าง placeholder สำหรับ IN clause
            $placeholders = [];
            $params = [];
            $paramIndex = 1;

            foreach ($requestIds as $id) {
                $placeholders[] = '$' . $paramIndex;
                $params[] = $id;
                $paramIndex++;
            }

            // เพิ่ม status parameter
            $params[] = $status;
            $statusPlaceholder = '$' . $paramIndex;

            $query = "UPDATE " . $this->table . "
                      SET sap_status = {$statusPlaceholder}, updated_at = NOW()
                      WHERE id IN (" . implode(', ', $placeholders) . ")";

            $result = @pg_query_params($this->conn, $query, $params);

            if (!$result) {
                // error_log("❌ Database error in updateSapStatus: " . pg_last_error($this->conn));
                return false;
            }

            $affected = pg_affected_rows($result);
            // error_log("✅ Updated SAP status for {$affected} requests to '{$status}'");

            return true;
        } catch (Exception $e) {
            // error_log("❌ Error in updateSapStatus: " . $e->getMessage());
            return false;
        }
    }
}
