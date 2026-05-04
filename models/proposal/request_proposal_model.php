<?php

class RequestProposalModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    
    /**
     * ดึง connection สำหรับ debug
     */
    public function getConnection()
    {
        return $this->conn;
    }
    
    /**
     * แปลง busa_area เป็น business_type_id ในข้อมูล proposal
     */
    private function convertBusaAreaToBusinessTypeId($proposal)
    {
        if (is_array($proposal) && isset($proposal['busa_area'])) {
            $proposal['business_type_id'] = $proposal['busa_area'];
        }
        return $proposal;
    }
    
    /**
     * แปลง busa_area เป็น business_type_id ใน array ของ proposals
     */
    private function convertBusaAreaInProposals($proposals)
    {
        if (is_array($proposals)) {
            foreach ($proposals as &$proposal) {
                if (isset($proposal['busa_area'])) {
                    $proposal['business_type_id'] = $proposal['busa_area'];
                }
            }
        }
        return $proposals;
    }
    
    /**
     * แปลงวันที่จาก dd-mm-yyyy เป็น yyyy-mm-dd
     */
    private function convertDateFormat($date)
    {
        if (empty($date)) {
            return null;
        }
        
        // ถ้าเป็นรูปแบบ dd-mm-yyyy ให้แปลงเป็น yyyy-mm-dd
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1]; // yyyy-mm-dd
        }
        
        // ถ้าเป็นรูปแบบ yyyy-mm-dd อยู่แล้ว ให้ return ตามเดิม
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        // ลองใช้ DateTime เพื่อแปลงรูปแบบอื่นๆ
        $dateObj = DateTime::createFromFormat('d-m-Y', $date);
        if ($dateObj !== false) {
            return $dateObj->format('Y-m-d');
        }
        
        return $date;
    }
    
    /**
     * สร้างคำขอใหม่
     */
    public function createProposal($data)
    {
        try {
            // เริ่ม transaction
            pg_query($this->conn, "BEGIN");
            
            // สร้าง proposal number
            $proposalNumber = $this->generateProposalNumber();
            
            // ข้อมูลหลัก
            $query = "INSERT INTO \"request-proposal\".request_proposals (
                proposal_number, form_type_id, account_category, start_date, end_date,
                department_code, header_note, description, factory_id, warehouse_id,
                purchase_group_id, monetary_unit_en, monetary_unit_th, tax_id,
                delivery_date, gl_account_id, cost_center_id, fund_id, scope_id,
                funds_center_id, liability_id, purchase_text, item_note, delivery_text,
                material_order_text, quotation_text, status, sap_status, total_quantity,
                total_amount, created_by, created_at, is_deleted, busa_area
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, NOW(), $32, $33
            ) RETURNING id";
            
            // รับ business_type_id จากข้อมูลที่ส่งมา (อาจจะส่งมาเป็น business_type_id หรือ busa_area)
            $busaArea = $data['busa_area'] ?? $data['business_type_id'] ?? null;
            
            $result = pg_query_params($this->conn, $query, array(
                $proposalNumber,
                $data['form_type_id'],
                $data['account_category'],
                $this->convertDateFormat($data['start_date']),
                $this->convertDateFormat($data['end_date']),
                $data['department_code'],
                $data['header_note'] ?? null,
                $data['description'] ?? null,
                $data['factory_id'],
                $data['warehouse_id'],
                $data['purchase_group_id'],
                $data['monetary_unit_en'] ?? null,
                $data['monetary_unit_th'] ?? null,
                (!empty($data['tax_id']) ? $data['tax_id'] : null),
                $this->convertDateFormat($data['delivery_date']),
                // TYPE N (ไม่เลือก) และ TYPE A (สินทรัพย์) สามารถมี GL Account เป็นค่าว่างได้
                (!empty($data['gl_account_id']) ? $data['gl_account_id'] : null),
                $data['cost_center_id'],
                $data['fund_id'],
                $data['scope_id'],
                $data['funds_center_id'],
                $data['liability_id'],
                $data['purchase_text'] ?? null,
                $data['item_note'] ?? null,
                $data['delivery_text'] ?? null,
                $data['material_order_text'] ?? null,
                $data['quotation_text'] ?? null,
                'pending', // status
                'not_synced', // sap_status
                $data['total_quantity'] ?? 0,
                $data['total_amount'] ?? 0,
                $data['created_by'],
                "F", // is_deleted
                (!empty($busaArea) ? $busaArea : null) // busa_area
            ));
            
            if (!$result) {
                throw new Exception("Error creating proposal: " . pg_last_error($this->conn));
            }
            
            $proposalId = pg_fetch_result($result, 0, 0);
            
            // ใช้ยอดรวมที่ส่งมาจาก JavaScript (รวมเป็นเงินทั้งสิ้น) - แปลงเป็น string
            $totalQuantity = number_format(floatval($data['total_quantity'] ?? 0), 2, '.', '');
            $totalAmount = number_format(floatval($data['total_amount'] ?? 0), 2, '.', '');
            
            // บันทึกรายการ
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->createProposalItem($proposalId, $item);
                }
            }
            
            // อัปเดตยอดรวมในตารางหลัก
            $updateQuery = "UPDATE \"request-proposal\".request_proposals SET 
                total_quantity = $1, total_amount = $2 
                WHERE id = $3";
            pg_query_params($this->conn, $updateQuery, array(
                $totalQuantity,
                $totalAmount,
                $proposalId
            ));
            
            // commit transaction
            pg_query($this->conn, "COMMIT");
            
            return [
                'success' => true,
                'proposal_id' => $proposalId,
                'proposal_number' => $proposalNumber
            ];
            
        } catch (Exception $e) {
            // rollback transaction
            pg_query($this->conn, "ROLLBACK");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * สร้างรายการในคำขอ
     */
    private function createProposalItem($proposalId, $itemData)
    {
        $query = "INSERT INTO \"request-proposal\".request_proposal_items (
            proposal_id, material_code, material_name, short_text, group_name,
            quantity, unit_code, unit_price, total_price, created_at
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, NOW()
        )";
        
        // เลือก group_name จากหลายแหล่ง: 
        // - group_name (ส่งมาตรงๆ)
        // - expen_pr_id (TYPE K - ศูนย์ต้นทุน)
        // - asset_category_id (TYPE A - สินทรัพย์)
        // - mrd_group_id (TYPE N - ไม่เลือก)
        $groupName = $itemData['group_name'] ?? $itemData['expen_pr_id'] ?? $itemData['asset_category_id'] ?? $itemData['mrd_group_id'] ?? null;
        
        $result = pg_query_params($this->conn, $query, array(
            $proposalId,
            $itemData['material_code'] ?? null,
            $itemData['material_name'] ?? null,
            $itemData['short_text'] ?? null,
            $groupName, // เก็บ ID ของกลุ่มวัสดุ/Asset Category/Expen PR
            number_format(floatval($itemData['quantity'] ?? 0), 2, '.', ''), // แปลงเป็น string
            $itemData['unit_code'] ?? null,
            number_format(floatval($itemData['unit_price'] ?? 0), 2, '.', ''), // แปลงเป็น string
            number_format(floatval($itemData['total_price'] ?? 0), 2, '.', '') // แปลงเป็น string
        ));
        
        if (!$result) {
            throw new Exception("Error creating proposal item: " . pg_last_error($this->conn));
        }
        
        return true;
    }
    
    /**
     * สร้าง proposal number
     */
    private function generateProposalNumber()
    {
        $year = date('Y');
        $query = "SELECT COUNT(*) FROM \"request-proposal\".request_proposals WHERE proposal_number LIKE $1";
        $result = pg_query_params($this->conn, $query, array("RP-{$year}-%"));
        
        if ($result) {
            $count = pg_fetch_result($result, 0, 0);
            $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            return "RP-{$year}-{$nextNumber}";
        }
        
        return "RP-{$year}-001";
    }
    
    /**
     * อัปเดตสถานะ
     */
    public function updateStatus($id, $status, $updatedBy = null)
    {
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }
        
        $query = "UPDATE \"request-proposal\".request_proposals SET status = $1, updated_by = $2, updated_at = NOW() WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($status, $updatedBy, $id));
        
        if (!$result) {
            return ['success' => false, 'error' => pg_last_error($this->conn)];
        }
        
        return ['success' => true];
    }
    
    /**
     * อัปเดต SAP status
     */
    public function updateSapStatus($id, $sapStatus)
    {
        $validSapStatuses = ['not_synced', 'synced'];
        if (!in_array($sapStatus, $validSapStatuses)) {
            return ['success' => false, 'error' => 'Invalid SAP status'];
        }
        
        $query = "UPDATE \"request-proposal\".request_proposals SET sap_status = $1, updated_at = NOW() WHERE id = $2";
        $result = pg_query_params($this->conn, $query, array($sapStatus, $id));
        
        if (!$result) {
            return ['success' => false, 'error' => pg_last_error($this->conn)];
        }
        
        return ['success' => true];
    }
    
    /**
     * ดึงข้อมูลคำขอ (แบบปลอดภัย)
     */
    public function getProposal($id)
    {
        try {
            // ตรวจสอบว่า id เป็นตัวเลข
            if (!is_numeric($id) || $id <= 0) {
                return false;
            }
            
            $query = "SELECT * FROM \"request-proposal\".request_proposals WHERE id = $1 AND is_deleted = FALSE";
            $result = pg_query_params($this->conn, $query, array($id));
            
            if (!$result) {
                return false;
            }
            
            $proposal = pg_fetch_assoc($result);
            
            if ($proposal) {
                // แปลง busa_area เป็น business_type_id เพื่อให้ JavaScript ใช้งานได้
                if (isset($proposal['busa_area'])) {
                    $proposal['business_type_id'] = $proposal['busa_area'];
                }
                
                // ดึงรายการ
                $itemsQuery = "SELECT * FROM \"request-proposal\".request_proposal_items WHERE proposal_id = $1 ORDER BY id";
                $itemsResult = pg_query_params($this->conn, $itemsQuery, array($id));
                
                if ($itemsResult) {
                    $items = pg_fetch_all($itemsResult);
                    $proposal['items'] = $items ? $items : array();
                } else {
                    $proposal['items'] = array();
                }
            }
            
            return $proposal;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * ดึงรายการคำขอทั้งหมด
     */
    public function getProposals($filters = [])
    {
        $whereConditions = [];
        $params = [];
        $paramCount = 0;
        
        if (isset($filters['status'])) {
            $paramCount++;
            $whereConditions[] = "status = $" . $paramCount;
            $params[] = $filters['status'];
        }
        
        if (isset($filters['sap_status'])) {
            $paramCount++;
            $whereConditions[] = "sap_status = $" . $paramCount;
            $params[] = $filters['sap_status'];
        }
        
        if (isset($filters['account_category'])) {
            $paramCount++;
            $whereConditions[] = "account_category = $" . $paramCount;
            $params[] = $filters['account_category'];
        }
        
        if (isset($filters['department_code'])) {
            $paramCount++;
            $whereConditions[] = "department_code = $" . $paramCount;
            $params[] = $filters['department_code'];
        }
        
        // เพิ่มเงื่อนไข is_deleted = FALSE เสมอ (ไม่แสดงข้อมูลที่ถูกลบ)
        $whereConditions[] = "is_deleted = FALSE";
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "SELECT * FROM \"request-proposal\".request_proposals {$whereClause} ORDER BY created_at DESC";
        
        if (!empty($params)) {
            $result = pg_query_params($this->conn, $query, $params);
        } else {
            $result = pg_query($this->conn, $query);
        }
        
        if (!$result) {
            return false;
        }
        
        $proposals = pg_fetch_all($result);
        return $this->convertBusaAreaInProposals($proposals);
    }
    
    /**
     * อัปเดตคำขอ
     */
    public function updateProposal($id, $data)
    {
        try {
            // เริ่ม transaction
            pg_query($this->conn, "BEGIN");
            
            // อัปเดตข้อมูลหลัก
            $updateFields = [];
            $updateValues = [];
            $paramCount = 0;
            
            // ฟิลด์ที่สามารถอัปเดตได้
            $updatableFields = [
                'form_type_id', 'account_category', 'start_date', 'end_date',
                'department_code', 'header_note', 'description', 'factory_id',
                'warehouse_id', 'purchase_group_id', 'monetary_unit_en',
                'monetary_unit_th', 'tax_id', 'delivery_date', 'gl_account_id',
                'cost_center_id', 'fund_id', 'scope_id', 'funds_center_id',
                'liability_id', 'purchase_text', 'item_note', 'delivery_text',
                'material_order_text', 'quotation_text', 'total_quantity', 'total_amount',
                'is_deleted', 'updated_by', 'busa_area'
            ];
            
            foreach ($updatableFields as $field) {
                // รองรับทั้ง busa_area และ business_type_id (แปลง business_type_id เป็น busa_area)
                if ($field === 'busa_area' && isset($data['business_type_id'])) {
                    $paramCount++;
                    $updateFields[] = "busa_area = $" . $paramCount;
                    $updateValues[] = (!empty($data['business_type_id']) ? $data['business_type_id'] : null);
                } else if (isset($data[$field])) {
                    $paramCount++;
                    $updateFields[] = "{$field} = $" . $paramCount;
                    
                    // แปลง empty string เป็น null สำหรับ tax_id
                    if ($field === 'tax_id' && empty($data[$field])) {
                        $updateValues[] = null;
                    }
                    // TYPE N (ไม่เลือก) สามารถมี GL Account เป็นค่าว่างได้
                    else if ($field === 'gl_account_id' && empty($data[$field])) {
                        $updateValues[] = null;
                    }
                    // แปลงรูปแบบวันที่สำหรับฟิลด์วันที่
                    else if (in_array($field, ['start_date', 'end_date', 'delivery_date'])) {
                        $updateValues[] = $this->convertDateFormat($data[$field]);
                    }
                    else {
                        $updateValues[] = $data[$field];
                    }
                }
            }
            
            if (!empty($updateFields)) {
                $updateFields[] = "updated_at = NOW()";
                $updateValues[] = $id;
                
                $query = "UPDATE \"request-proposal\".request_proposals SET " . implode(', ', $updateFields) . " WHERE id = $" . (count($updateValues));
                $result = pg_query_params($this->conn, $query, $updateValues);
                
                if (!$result) {
                    throw new Exception("Error updating proposal: " . pg_last_error($this->conn));
                }
            }
            
            // อัปเดตรายการ (ถ้ามี)
            if (isset($data['items']) && is_array($data['items'])) {
                // ลบรายการเก่า
                $deleteItemsQuery = "DELETE FROM \"request-proposal\".request_proposal_items WHERE proposal_id = $1";
                $deleteResult = pg_query_params($this->conn, $deleteItemsQuery, array($id));
                
                if (!$deleteResult) {
                    throw new Exception("Error deleting old items: " . pg_last_error($this->conn));
                }
                
                // ใช้ยอดรวมที่ส่งมาจาก JavaScript (รวมเป็นเงินทั้งสิ้น) - แปลงเป็น string
                $totalQuantity = number_format(floatval($data['total_quantity'] ?? 0), 2, '.', '');
                $totalAmount = number_format(floatval($data['total_amount'] ?? 0), 2, '.', '');
                
                // เพิ่มรายการใหม่
                foreach ($data['items'] as $item) {
                    $this->createProposalItem($id, $item);
                }
                
                // อัปเดตยอดรวมในตารางหลัก
                $updateTotalsQuery = "UPDATE \"request-proposal\".request_proposals SET 
                    total_quantity = $1, total_amount = $2, updated_at = NOW()
                    WHERE id = $3";
                pg_query_params($this->conn, $updateTotalsQuery, array(
                    $totalQuantity,
                    $totalAmount,
                    $id
                ));
            }
            
            // commit transaction
            pg_query($this->conn, "COMMIT");
            
            return ['success' => true];
            
        } catch (Exception $e) {
            // rollback transaction
            pg_query($this->conn, "ROLLBACK");
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ดึงข้อมูลคำขอพร้อมรายละเอียด (แบบปลอดภัย)
     */
    public function getProposalWithDetails($id)
    {
        try {
            // ตรวจสอบว่า id เป็นตัวเลข
            if (!is_numeric($id) || $id <= 0) {
                return false;
            }
            
            $query = "
                SELECT 
                    rp.*,
                    ft.form_type_name,
                    f.factory_name,
                    w.warehouse_name,
                    pg.purchase_group_name,
                    t.tax_name,
                    gl.gl_account_name,
                    cc.cost_center_name,
                    fund.funding_name,
                    s.scope_name,
                    wf.fund_name as wrd_fund_name,
                    l.liability_name
                FROM \"request-proposal\".request_proposals rp
                LEFT JOIN parameters.tb_form_types ft ON rp.form_type_id = ft.id
                LEFT JOIN parameters.tb_factories f ON rp.factory_id = f.id
                LEFT JOIN parameters.tb_warehouses w ON rp.warehouse_id = w.id
                LEFT JOIN parameters.tb_purchase_groups pg ON rp.purchase_group_id = pg.id
                LEFT JOIN parameters.tb_taxes t ON rp.tax_id = t.id
                LEFT JOIN parameters.tb_gl_accounts gl ON rp.gl_account_id = gl.id
                LEFT JOIN parameters.tb_cost_centers cc ON rp.cost_center_id = cc.id
                LEFT JOIN parameters.tb_fundings fund ON rp.fund_id = fund.id
                LEFT JOIN parameters.tb_scopes s ON rp.scope_id = s.id
                LEFT JOIN parameters.tb_wrd_fund wf ON rp.funds_center_id = wf.id
                LEFT JOIN parameters.tb_liabilities l ON rp.liability_id = l.id
                WHERE rp.id = $1 AND rp.is_deleted = FALSE
            ";
            
            $result = pg_query_params($this->conn, $query, array($id));
            
            if (!$result) {
                return false;
            }
            
            $proposal = pg_fetch_assoc($result);
            
            if ($proposal) {
                // แปลง busa_area เป็น business_type_id เพื่อให้ JavaScript ใช้งานได้
                if (isset($proposal['busa_area'])) {
                    $proposal['business_type_id'] = $proposal['busa_area'];
                }
                
                // ดึงรายการ
                $itemsQuery = "SELECT * FROM \"request-proposal\".request_proposal_items WHERE proposal_id = $1 ORDER BY id";
                $itemsResult = pg_query_params($this->conn, $itemsQuery, array($id));
                
                if ($itemsResult) {
                    $items = pg_fetch_all($itemsResult);
                    $proposal['items'] = $items ? $items : array();
                } else {
                    $proposal['items'] = array();
                }
            }
            
            return $proposal;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * ตรวจสอบ position_id ของผู้ใช้
     */
    public function getUserPositionId($userCode)
    {
        if (empty($userCode)) {
            return null;
        }
        
        $query = "SELECT position_id FROM users.tb_users WHERE user_code = $1";
        $result = pg_query_params($this->conn, $query, array($userCode));
        
        if (!$result) {
            return null;
        }
        
        $row = pg_fetch_assoc($result);
        return $row ? $row['position_id'] : null;
    }
    
    /**
     * ดึงข้อมูลผู้ใช้ (position_id, depart_id, branch_id, province_id, areas_id, head_office_id) - แบบปลอดภัย
     */
    public function getUserInfo($userCode)
    {
        if (empty($userCode)) {
            return null;
        }
        
        try {
            $query = "SELECT user_code, position_id, depart_id, branch_id, province_id, areas_id, head_office_id FROM users.tb_users WHERE user_code = $1";
            $result = pg_query_params($this->conn, $query, array($userCode));
            
            if (!$result) {
                // ถ้า query ไม่สำเร็จ ให้ return null แทนที่จะ error
                return null;
            }
            
            $row = pg_fetch_assoc($result);
            return $row;
            
        } catch (Exception $e) {
            // ถ้าเกิด error ให้ return null
            return null;
        }
    }
    
    /**
     * ดึง area_id ของผู้อนุมัติ
     * ตรวจสอบตามลำดับ: branch_id → province_id → areas_id → head_office_id
     */
    private function getApproverAreaId($userInfo)
    {
        if (!$userInfo) {
            return null;
        }
        
        // กรณีที่ 1: ถ้ามี branch_id ให้ดึง area_id จาก branch → province → area
        if (!empty($userInfo['branch_id'])) {
            try {
                $query = "
                    SELECT p.area_id 
                    FROM users.tb_branchs b
                    INNER JOIN users.tb_provinces p ON b.province_id = p.id
                    WHERE b.id = $1
                ";
                $result = pg_query_params($this->conn, $query, array($userInfo['branch_id']));
                
                if ($result) {
                    $row = pg_fetch_assoc($result);
                    if ($row && !empty($row['area_id'])) {
                        return $row['area_id'];
                    }
                }
            } catch (Exception $e) {
                // ถ้าเกิด error ให้ลองวิธีอื่น
            }
        }
        
        // กรณีที่ 2: ถ้า branch_id เป็น null แต่มี province_id ให้ดึง area_id จาก province
        if (empty($userInfo['branch_id']) && !empty($userInfo['province_id'])) {
            try {
                $query = "SELECT area_id FROM users.tb_provinces WHERE id = $1";
                $result = pg_query_params($this->conn, $query, array($userInfo['province_id']));
                
                if ($result) {
                    $row = pg_fetch_assoc($result);
                    if ($row && !empty($row['area_id'])) {
                        return $row['area_id'];
                    }
                }
            } catch (Exception $e) {
                // ถ้าเกิด error ให้ลองวิธีอื่น
            }
        }
        
        // กรณีที่ 3: ถ้า branch_id และ province_id เป็น null แต่มี areas_id โดยตรง (จาก tb_users)
        if (empty($userInfo['branch_id']) && empty($userInfo['province_id']) && !empty($userInfo['areas_id'])) {
            return $userInfo['areas_id'];
        }
        
        // กรณีที่ 4: ถ้า branch_id, province_id, areas_id เป็น null แต่มี head_office_id
        // ใช้ head_office_id เป็นเงื่อนไขพิเศษ (return 'head_office' เพื่อระบุว่าใช้ head_office_id)
        if (empty($userInfo['branch_id']) && empty($userInfo['province_id']) && empty($userInfo['areas_id']) && !empty($userInfo['head_office_id'])) {
            return 'head_office_' . $userInfo['head_office_id'];
        }
        
        return null;
    }
    
    /**
     * ดึงรายการคำขอสำหรับผู้อนุมัติ (position_id = 11) - แบบปลอดภัย
     * แสดงเฉพาะใบขอเสนอที่มี area_id ตรงกับผู้อนุมัติ
     */
    public function getProposalsForApprover($approverUserCode, $filters = array())
    {
        try {
            // ดึงข้อมูลผู้อนุมัติ
            $approverInfo = $this->getUserInfo($approverUserCode);
            
            if (!$approverInfo) {
                // ถ้าไม่พบข้อมูลผู้ใช้ ให้ return array ว่าง
                return array();
            }
            
            // ตรวจสอบว่าเป็นผู้อนุมัติ (position_id = 11)
            $positionId = isset($approverInfo['position_id']) ? intval($approverInfo['position_id']) : null;
            
            if (!$positionId || $positionId != 11) {
                // ถ้าไม่ใช่ผู้อนุมัติ ให้ return array ว่าง
                return array();
            }
            
            // ดึง area_id ของผู้อนุมัติ (ตรวจสอบจาก branch_id, province_id, areas_id, head_office_id)
            $approverAreaId = $this->getApproverAreaId($approverInfo);
            
            if (!$approverAreaId) {
                // ถ้าไม่มี area_id ให้ return array ว่าง
                return array();
            }
            
            // สร้าง WHERE conditions
            $whereConditions = array();
            $params = array();
            $paramCount = 0;
            
            // เพิ่มเงื่อนไข is_deleted = FALSE เสมอ
            $whereConditions[] = "rp.is_deleted = FALSE";
            
            // เงื่อนไขหลัก: ตรวจสอบ area_id ของผู้สร้าง
            // รองรับกรณีที่ creator.branch_id เป็น null หรือไม่เป็น null
            // และรองรับกรณีที่ใช้ head_office_id
            if (strpos($approverAreaId, 'head_office_') === 0) {
                // กรณีที่ใช้ head_office_id
                $approverHeadOfficeId = str_replace('head_office_', '', $approverAreaId);
                $paramCount++;
                $whereConditions[] = "(
                    (creator.branch_id IS NOT NULL AND creator_area.area_id IS NULL) OR
                    (creator.branch_id IS NULL AND creator.province_id IS NOT NULL AND creator_province.area_id IS NULL) OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id IS NULL AND creator.head_office_id = $" . $paramCount . ")
                )";
                $params[] = $approverHeadOfficeId;
            } else {
                // กรณีปกติที่ใช้ area_id
                $paramCount++;
                $whereConditions[] = "(
                    (creator.branch_id IS NOT NULL AND creator_area.area_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NOT NULL AND creator_province.area_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id IS NULL AND creator.head_office_id = $" . $paramCount . ")
                )";
                $params[] = $approverAreaId;
            }
            
            // เพิ่ม filters อื่นๆ
            if (isset($filters['status']) && !empty($filters['status'])) {
                $paramCount++;
                $whereConditions[] = "rp.status = $" . $paramCount;
                $params[] = $filters['status'];
            }
            
            if (isset($filters['sap_status']) && !empty($filters['sap_status'])) {
                $paramCount++;
                $whereConditions[] = "rp.sap_status = $" . $paramCount;
                $params[] = $filters['sap_status'];
            }
            
            if (isset($filters['account_category']) && !empty($filters['account_category'])) {
                $paramCount++;
                $whereConditions[] = "rp.account_category = $" . $paramCount;
                $params[] = $filters['account_category'];
            }
            
            if (isset($filters['form_type_id']) && !empty($filters['form_type_id'])) {
                $paramCount++;
                $whereConditions[] = "rp.form_type_id = $" . $paramCount;
                $params[] = $filters['form_type_id'];
            }
            
            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $paramCount++;
                $whereConditions[] = "rp.created_at::date >= $" . $paramCount;
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $paramCount++;
                $whereConditions[] = "rp.created_at::date <= $" . $paramCount;
                $params[] = $filters['date_to'];
            }
            
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
            
            $query = "
                SELECT 
                    rp.*,
                    ft.form_type_name,
                    f.factory_name,
                    w.warehouse_name,
                    pg.purchase_group_name,
                    t.tax_name,
                    gl.gl_account_name,
                    cc.cost_center_name,
                    fund.funding_name,
                    s.scope_name,
                    wf.fund_name as wrd_fund_name,
                    l.liability_name,
                    creator.depart_id as creator_depart_id,
                    COALESCE(creator_area.area_id, creator_province.area_id, creator.areas_id) as creator_area_id
                FROM \"request-proposal\".request_proposals rp
                INNER JOIN users.tb_users creator ON rp.created_by = creator.user_code
                LEFT JOIN users.tb_branchs creator_branch ON creator.branch_id = creator_branch.id
                LEFT JOIN users.tb_provinces creator_area ON creator_branch.province_id = creator_area.id
                LEFT JOIN users.tb_provinces creator_province ON creator.province_id = creator_province.id
                LEFT JOIN parameters.tb_form_types ft ON rp.form_type_id = ft.id
                LEFT JOIN parameters.tb_factories f ON rp.factory_id = f.id
                LEFT JOIN parameters.tb_warehouses w ON rp.warehouse_id = w.id
                LEFT JOIN parameters.tb_purchase_groups pg ON rp.purchase_group_id = pg.id
                LEFT JOIN parameters.tb_taxes t ON rp.tax_id = t.id
                LEFT JOIN parameters.tb_gl_accounts gl ON rp.gl_account_id = gl.id
                LEFT JOIN parameters.tb_cost_centers cc ON rp.cost_center_id = cc.id
                LEFT JOIN parameters.tb_fundings fund ON rp.fund_id = fund.id
                LEFT JOIN parameters.tb_scopes s ON rp.scope_id = s.id
                LEFT JOIN parameters.tb_wrd_fund wf ON rp.funds_center_id = wf.id
                LEFT JOIN parameters.tb_liabilities l ON rp.liability_id = l.id
                {$whereClause}
                ORDER BY rp.created_at DESC
            ";
            
            $result = pg_query_params($this->conn, $query, $params);
            
            if (!$result) {
                // ถ้า query ไม่สำเร็จ ให้ return array ว่าง
                return array();
            }
            
            $data = pg_fetch_all($result);
            $data = $data ? $data : array();
            return $this->convertBusaAreaInProposals($data);
            
        } catch (Exception $e) {
            // ถ้าเกิด error ให้ return array ว่าง
            return array();
        }
    }
    
    /**
     * ค้นหาคำขอสำหรับผู้อนุมัติ (position_id = 11) - แบบปลอดภัย
     * แสดงเฉพาะใบขอเสนอที่มี area_id ตรงกับผู้อนุมัติ
     */
    public function searchProposalsForApprover($approverUserCode, $keyword, $filters = array())
    {
        try {
            // ดึงข้อมูลผู้อนุมัติ
            $approverInfo = $this->getUserInfo($approverUserCode);
            
            if (!$approverInfo) {
                // ถ้าไม่พบข้อมูลผู้ใช้ ให้ return array ว่าง
                return array();
            }
            
            // ตรวจสอบว่าเป็นผู้อนุมัติ (position_id = 11)
            $positionId = isset($approverInfo['position_id']) ? intval($approverInfo['position_id']) : null;
            
            if (!$positionId || $positionId != 11) {
                // ถ้าไม่ใช่ผู้อนุมัติ ให้ return array ว่าง
                return array();
            }
            
            // ดึง area_id ของผู้อนุมัติ (ตรวจสอบจาก branch_id, province_id, areas_id, head_office_id)
            $approverAreaId = $this->getApproverAreaId($approverInfo);
            
            if (!$approverAreaId) {
                // ถ้าไม่มี area_id ให้ return array ว่าง
                return array();
            }
            
            // สร้าง WHERE conditions
            $whereConditions = array();
            $params = array();
            $paramCount = 0;
            
            // เพิ่มเงื่อนไข is_deleted = FALSE เสมอ
            $whereConditions[] = "rp.is_deleted = FALSE";
            
            // เงื่อนไขหลัก: ตรวจสอบ area_id ของผู้สร้าง
            // รองรับกรณีที่ creator.branch_id เป็น null หรือไม่เป็น null
            // และรองรับกรณีที่ใช้ head_office_id
            if (strpos($approverAreaId, 'head_office_') === 0) {
                // กรณีที่ใช้ head_office_id
                $approverHeadOfficeId = str_replace('head_office_', '', $approverAreaId);
                $paramCount++;
                $whereConditions[] = "(
                    (creator.branch_id IS NOT NULL AND creator_area.area_id IS NULL) OR
                    (creator.branch_id IS NULL AND creator.province_id IS NOT NULL AND creator_province.area_id IS NULL) OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id IS NULL AND creator.head_office_id = $" . $paramCount . ")
                )";
                $params[] = $approverHeadOfficeId;
            } else {
                // กรณีปกติที่ใช้ area_id
                $paramCount++;
                $whereConditions[] = "(
                    (creator.branch_id IS NOT NULL AND creator_area.area_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NOT NULL AND creator_province.area_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id = $" . $paramCount . ") OR
                    (creator.branch_id IS NULL AND creator.province_id IS NULL AND creator.areas_id IS NULL AND creator.head_office_id = $" . $paramCount . ")
                )";
                $params[] = $approverAreaId;
            }
            
            // ค้นหาจาก keyword
            if (!empty($keyword)) {
                $paramCount++;
                $whereConditions[] = "(rp.proposal_number ILIKE $" . $paramCount . " OR rp.department_code ILIKE $" . $paramCount . " OR rp.header_note ILIKE $" . $paramCount . " OR rp.description ILIKE $" . $paramCount . ")";
                $params[] = "%{$keyword}%";
            }
            
            // เพิ่ม filters อื่นๆ
            if (isset($filters['status']) && !empty($filters['status'])) {
                $paramCount++;
                $whereConditions[] = "rp.status = $" . $paramCount;
                $params[] = $filters['status'];
            }
            
            if (isset($filters['sap_status']) && !empty($filters['sap_status'])) {
                $paramCount++;
                $whereConditions[] = "rp.sap_status = $" . $paramCount;
                $params[] = $filters['sap_status'];
            }
            
            if (isset($filters['account_category']) && !empty($filters['account_category'])) {
                $paramCount++;
                $whereConditions[] = "rp.account_category = $" . $paramCount;
                $params[] = $filters['account_category'];
            }
            
            if (isset($filters['form_type_id']) && !empty($filters['form_type_id'])) {
                $paramCount++;
                $whereConditions[] = "rp.form_type_id = $" . $paramCount;
                $params[] = $filters['form_type_id'];
            }
            
            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $paramCount++;
                $whereConditions[] = "rp.created_at::date >= $" . $paramCount;
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $paramCount++;
                $whereConditions[] = "rp.created_at::date <= $" . $paramCount;
                $params[] = $filters['date_to'];
            }
            
            $whereClause = "WHERE " . implode(" AND ", $whereConditions);
            
            $query = "
                SELECT 
                    rp.*,
                    ft.form_type_name,
                    f.factory_name,
                    w.warehouse_name,
                    pg.purchase_group_name,
                    t.tax_name,
                    gl.gl_account_name,
                    cc.cost_center_name,
                    fund.funding_name,
                    s.scope_name,
                    wf.fund_name as wrd_fund_name,
                    l.liability_name,
                    creator.depart_id as creator_depart_id,
                    COALESCE(creator_area.area_id, creator_province.area_id, creator.areas_id) as creator_area_id
                FROM \"request-proposal\".request_proposals rp
                INNER JOIN users.tb_users creator ON rp.created_by = creator.user_code
                LEFT JOIN users.tb_branchs creator_branch ON creator.branch_id = creator_branch.id
                LEFT JOIN users.tb_provinces creator_area ON creator_branch.province_id = creator_area.id
                LEFT JOIN users.tb_provinces creator_province ON creator.province_id = creator_province.id
                LEFT JOIN parameters.tb_form_types ft ON rp.form_type_id = ft.id
                LEFT JOIN parameters.tb_factories f ON rp.factory_id = f.id
                LEFT JOIN parameters.tb_warehouses w ON rp.warehouse_id = w.id
                LEFT JOIN parameters.tb_purchase_groups pg ON rp.purchase_group_id = pg.id
                LEFT JOIN parameters.tb_taxes t ON rp.tax_id = t.id
                LEFT JOIN parameters.tb_gl_accounts gl ON rp.gl_account_id = gl.id
                LEFT JOIN parameters.tb_cost_centers cc ON rp.cost_center_id = cc.id
                LEFT JOIN parameters.tb_fundings fund ON rp.fund_id = fund.id
                LEFT JOIN parameters.tb_scopes s ON rp.scope_id = s.id
                LEFT JOIN parameters.tb_wrd_fund wf ON rp.funds_center_id = wf.id
                LEFT JOIN parameters.tb_liabilities l ON rp.liability_id = l.id
                {$whereClause}
                ORDER BY rp.created_at DESC
            ";
            
            $result = pg_query_params($this->conn, $query, $params);
            
            if (!$result) {
                // ถ้า query ไม่สำเร็จ ให้ return array ว่าง
                return array();
            }
            
            $data = pg_fetch_all($result);
            $data = $data ? $data : array();
            return $this->convertBusaAreaInProposals($data);
            
        } catch (Exception $e) {
            // ถ้าเกิด error ให้ return array ว่าง
            return array();
        }
    }
    
    /**
     * ดึงรายการคำขอพร้อมรายละเอียด (แบบง่าย)
     */
    public function getProposalsWithDetails($filters = array())
    {
        try {
            $whereConditions = array();
        $params = array();
        $paramCount = 0;
        
        // เพิ่มเงื่อนไข is_deleted = FALSE เสมอ
        $whereConditions[] = "rp.is_deleted = FALSE";
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $paramCount++;
            $whereConditions[] = "rp.status = $" . $paramCount;
            $params[] = $filters['status'];
        }
        
        if (isset($filters['sap_status']) && !empty($filters['sap_status'])) {
            $paramCount++;
            $whereConditions[] = "rp.sap_status = $" . $paramCount;
            $params[] = $filters['sap_status'];
        }
        
        if (isset($filters['account_category']) && !empty($filters['account_category'])) {
            $paramCount++;
            $whereConditions[] = "rp.account_category = $" . $paramCount;
            $params[] = $filters['account_category'];
        }
        
        if (isset($filters['department_code']) && !empty($filters['department_code'])) {
            $paramCount++;
            $whereConditions[] = "rp.department_code = $" . $paramCount;
            $params[] = $filters['department_code'];
        }
        
        if (isset($filters['form_type_id']) && !empty($filters['form_type_id'])) {
            $paramCount++;
            $whereConditions[] = "rp.form_type_id = $" . $paramCount;
            $params[] = $filters['form_type_id'];
        }
        
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_at::date >= $" . $paramCount;
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_at::date <= $" . $paramCount;
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['created_by']) && !empty($filters['created_by'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_by = $" . $paramCount;
            $params[] = $filters['created_by'];
        }
        
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
        
        $query = "
            SELECT 
                rp.*,
                ft.form_type_name,
                f.factory_name,
                w.warehouse_name,
                pg.purchase_group_name,
                t.tax_name,
                gl.gl_account_name,
                cc.cost_center_name,
                fund.funding_name,
                s.scope_name,
                wf.fund_name as wrd_fund_name,
                l.liability_name
            FROM \"request-proposal\".request_proposals rp
            LEFT JOIN parameters.tb_form_types ft ON rp.form_type_id = ft.id
            LEFT JOIN parameters.tb_factories f ON rp.factory_id = f.id
            LEFT JOIN parameters.tb_warehouses w ON rp.warehouse_id = w.id
            LEFT JOIN parameters.tb_purchase_groups pg ON rp.purchase_group_id = pg.id
            LEFT JOIN parameters.tb_taxes t ON rp.tax_id = t.id
            LEFT JOIN parameters.tb_gl_accounts gl ON rp.gl_account_id = gl.id
            LEFT JOIN parameters.tb_cost_centers cc ON rp.cost_center_id = cc.id
            LEFT JOIN parameters.tb_fundings fund ON rp.fund_id = fund.id
            LEFT JOIN parameters.tb_scopes s ON rp.scope_id = s.id
            LEFT JOIN parameters.tb_wrd_fund wf ON rp.funds_center_id = wf.id
            LEFT JOIN parameters.tb_liabilities l ON rp.liability_id = l.id
            {$whereClause}
            ORDER BY rp.created_at DESC
        ";
        
        if (!empty($params)) {
            $result = pg_query_params($this->conn, $query, $params);
        } else {
            $result = pg_query($this->conn, $query);
        }
        
        if (!$result) {
            $error = pg_last_error($this->conn);
            error_log("RequestProposalModel::getProposalsWithDetails - Query error: " . $error);
            error_log("RequestProposalModel::getProposalsWithDetails - Query: " . $query);
            return false;
        }
        
        $data = pg_fetch_all($result);
        $data = $data ? $data : array();
        return $this->convertBusaAreaInProposals($data);
        } catch (Exception $e) {
            error_log("RequestProposalModel::getProposalsWithDetails - Exception: " . $e->getMessage());
            error_log("RequestProposalModel::getProposalsWithDetails - Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * ดึงสถิติคำขอ
     */
    public function getProposalStats()
    {
        $query = "
            SELECT 
                COUNT(*) as total_proposals,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN sap_status = 'not_synced' THEN 1 END) as not_synced_count,
                COUNT(CASE WHEN sap_status = 'synced' THEN 1 END) as synced_count,
                COUNT(CASE WHEN account_category = 'A' THEN 1 END) as asset_count,
                COUNT(CASE WHEN account_category = 'K' THEN 1 END) as cost_center_count,
                COUNT(CASE WHEN account_category IS NULL OR account_category = '' THEN 1 END) as no_category_count
            FROM \"request-proposal\".request_proposals
            WHERE is_deleted = FALSE
        ";
        
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }
    
    /**
     * ค้นหาคำขอ (แบบง่าย)
     */
    public function searchProposals($keyword, $filters = array())
    {
        $whereConditions = array();
        $params = array();
        $paramCount = 0;
        
        // เพิ่มเงื่อนไข is_deleted = FALSE เสมอ
        $whereConditions[] = "rp.is_deleted = FALSE";
        
        // ค้นหาจาก keyword
        if (!empty($keyword)) {
            $paramCount++;
            $whereConditions[] = "(rp.proposal_number ILIKE $" . $paramCount . " OR rp.department_code ILIKE $" . $paramCount . " OR rp.header_note ILIKE $" . $paramCount . " OR rp.description ILIKE $" . $paramCount . ")";
            $params[] = "%{$keyword}%";
        }
        
        // เพิ่ม filters อื่นๆ
        if (isset($filters['status']) && !empty($filters['status'])) {
            $paramCount++;
            $whereConditions[] = "rp.status = $" . $paramCount;
            $params[] = $filters['status'];
        }
        
        if (isset($filters['sap_status']) && !empty($filters['sap_status'])) {
            $paramCount++;
            $whereConditions[] = "rp.sap_status = $" . $paramCount;
            $params[] = $filters['sap_status'];
        }
        
        if (isset($filters['account_category']) && !empty($filters['account_category'])) {
            $paramCount++;
            $whereConditions[] = "rp.account_category = $" . $paramCount;
            $params[] = $filters['account_category'];
        }
        
        if (isset($filters['form_type_id']) && !empty($filters['form_type_id'])) {
            $paramCount++;
            $whereConditions[] = "rp.form_type_id = $" . $paramCount;
            $params[] = $filters['form_type_id'];
        }
        
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_at::date >= $" . $paramCount;
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_at::date <= $" . $paramCount;
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['created_by']) && !empty($filters['created_by'])) {
            $paramCount++;
            $whereConditions[] = "rp.created_by = $" . $paramCount;
            $params[] = $filters['created_by'];
        }
        
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
        
        $query = "
            SELECT 
                rp.*,
                ft.form_type_name,
                f.factory_name,
                w.warehouse_name,
                pg.purchase_group_name,
                t.tax_name,
                gl.gl_account_name,
                cc.cost_center_name,
                fund.funding_name,
                s.scope_name,
                wf.fund_name as wrd_fund_name,
                l.liability_name
            FROM \"request-proposal\".request_proposals rp
            LEFT JOIN parameters.tb_form_types ft ON rp.form_type_id = ft.id
            LEFT JOIN parameters.tb_factories f ON rp.factory_id = f.id
            LEFT JOIN parameters.tb_warehouses w ON rp.warehouse_id = w.id
            LEFT JOIN parameters.tb_purchase_groups pg ON rp.purchase_group_id = pg.id
            LEFT JOIN parameters.tb_taxes t ON rp.tax_id = t.id
            LEFT JOIN parameters.tb_gl_accounts gl ON rp.gl_account_id = gl.id
            LEFT JOIN parameters.tb_cost_centers cc ON rp.cost_center_id = cc.id
            LEFT JOIN parameters.tb_fundings fund ON rp.fund_id = fund.id
            LEFT JOIN parameters.tb_scopes s ON rp.scope_id = s.id
            LEFT JOIN parameters.tb_wrd_fund wf ON rp.funds_center_id = wf.id
            LEFT JOIN parameters.tb_liabilities l ON rp.liability_id = l.id
            {$whereClause}
            ORDER BY rp.created_at DESC
        ";
        
        if (!empty($params)) {
            $result = pg_query_params($this->conn, $query, $params);
        } else {
            $result = pg_query($this->conn, $query);
        }
        
        if (!$result) {
            return false;
        }
        
        $proposals = pg_fetch_all($result);
        return $this->convertBusaAreaInProposals($proposals);
    }
    
    /**
     * ดึงประวัติการอนุมัติ
     */
    public function getApprovalHistory($proposalId)
    {
        try {
            // ดึงข้อมูลจากตาราง request_proposal_approvals
            $query = "SELECT 
                        rpa.approval_level,
                        rpa.approver_code,
                        rpa.approver_name,
                        rpa.approval_status,
                        rpa.approval_note,
                        rpa.approved_at,
                        rpa.created_at
                      FROM \"request-proposal\".request_proposal_approvals rpa 
                      WHERE rpa.proposal_id = $1
                      ORDER BY rpa.approval_level ASC, rpa.created_at ASC";
            
            $result = pg_query_params($this->conn, $query, array($proposalId));
            
            if (!$result) {
                throw new Exception('Database query failed: ' . pg_last_error($this->conn));
            }
            
            $history = [];
            
            while ($row = pg_fetch_assoc($result)) {
                $history[] = [
                    'approval_level' => $row['approval_level'],
                    'status' => $row['approval_status'],
                    'approved_by' => $row['approver_name'] ?: $row['approver_code'],
                    'approved_at' => $row['approved_at'] ?: $row['created_at'],
                    'remark' => $row['approval_note'] ?: $this->getStatusText($row['approval_status'])
                ];
            }
            
            // ถ้าไม่มีข้อมูลในตาราง approvals ให้ดึงจากตารางหลัก
            if (empty($history)) {
                return $this->getApprovalHistoryFromMainTable($proposalId);
            }
            
            return $history;
            
        } catch (Exception $e) {
            error_log("Error in getApprovalHistory: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ดึงประวัติการอนุมัติจากตารางหลัก (fallback)
     */
    private function getApprovalHistoryFromMainTable($proposalId)
    {
        try {
            $query = "SELECT 
                        rp.status,
                        rp.updated_by,
                        rp.updated_at,
                        rp.created_by,
                        rp.created_at
                      FROM \"request-proposal\".request_proposals rp 
                      WHERE rp.id = $1 AND rp.is_deleted = FALSE";
            
            $result = pg_query_params($this->conn, $query, array($proposalId));
            
            if (!$result) {
                return [];
            }
            
            $history = [];
            $row = pg_fetch_assoc($result);
            
            if ($row) {
                // เพิ่มข้อมูลการสร้าง
                if ($row['created_by']) {
                    $history[] = [
                        'approval_level' => 1,
                        'status' => 'pending',
                        'approved_by' => $row['created_by'],
                        'approved_at' => $row['created_at'],
                        'remark' => 'สร้างใบขอเสนอ'
                    ];
                }
                
                // เพิ่มข้อมูลการอัปเดตสถานะ (ถ้ามี)
                if ($row['status'] !== 'pending' && $row['updated_by']) {
                    $history[] = [
                        'approval_level' => 2,
                        'status' => $row['status'],
                        'approved_by' => $row['updated_by'],
                        'approved_at' => $row['updated_at'],
                        'remark' => $this->getStatusText($row['status'])
                    ];
                }
            }
            
            return $history;
            
        } catch (Exception $e) {
            error_log("Error in getApprovalHistoryFromMainTable: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * แปลงสถานะเป็นข้อความ
     */
    private function getStatusText($status)
    {
        switch ($status) {
            case 'approved':
                return 'อนุมัติ';
            case 'rejected':
                return 'ไม่อนุมัติ';
            case 'pending':
                return 'รออนุมัติ';
            default:
                return $status;
        }
    }
    
    /**
     * เพิ่มรายการอนุมัติ
     */
    public function addApprovalRecord($proposalId, $approvalLevel, $approverCode, $approverName, $status, $note = '')
    {
        try {
            if ($status === 'approved' || $status === 'rejected') {
                $query = "INSERT INTO \"request-proposal\".request_proposal_approvals 
                          (proposal_id, approval_level, approver_code, approver_name, approval_status, approval_note, approved_at) 
                          VALUES ($1, $2, $3, $4, $5, $6, NOW())
                          RETURNING id";
                
                $result = pg_query_params($this->conn, $query, [
                    $proposalId,
                    $approvalLevel,
                    $approverCode,
                    $approverName,
                    $status,
                    $note
                ]);
            } else {
                $query = "INSERT INTO \"request-proposal\".request_proposal_approvals 
                          (proposal_id, approval_level, approver_code, approver_name, approval_status, approval_note) 
                          VALUES ($1, $2, $3, $4, $5, $6)
                          RETURNING id";
                
                $result = pg_query_params($this->conn, $query, [
                    $proposalId,
                    $approvalLevel,
                    $approverCode,
                    $approverName,
                    $status,
                    $note
                ]);
            }
            
            if (!$result) {
                throw new Exception('Database query failed: ' . pg_last_error($this->conn));
            }
            
            $row = pg_fetch_assoc($result);
            return ['success' => true, 'id' => $row['id']];
            
        } catch (Exception $e) {
            error_log("Error in addApprovalRecord: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * ลบคำขอ (soft delete)
     */
    public function deleteProposal($id, $updatedBy = null)
    {
        $query = "UPDATE \"request-proposal\".request_proposals SET is_deleted = TRUE, updated_by = $2, updated_at = NOW() WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id, $updatedBy));
        
        if (!$result) {
            return ['success' => false, 'error' => pg_last_error($this->conn)];
        }
        
        return ['success' => true];
    }
}


