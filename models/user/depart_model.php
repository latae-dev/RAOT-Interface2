<?php

/**
 * Depart Model - จัดการข้อมูลหน่วยงาน (tb_departs)
 * Migration: Phase 2 - รองรับ hierarchy และ depart_type_id
 * วันที่: 2025-01-27
 */
class DepartModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * ดึงข้อมูลทั้งหมดจาก tb_departs พร้อม hierarchy และ filters
     * 
     * @param array $filters ตัวกรองข้อมูล (depart_code, depart_name, depart_type_id, is_active)
     * @param int $page หน้าที่ต้องการ
     * @param int $pageSize จำนวนรายการต่อหน้า
     * @return array|false ข้อมูล departments พร้อม pagination หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function readDepart($filters = [], $page = 1, $pageSize = 10)
    {
        $whereConditions = [];
        $params = [];
        $paramIndex = 1;

        // Build WHERE conditions
        if (!empty($filters['depart_code'])) {
            $whereConditions[] = "d.depart_code ILIKE $" . $paramIndex;
            $params[] = '%' . $filters['depart_code'] . '%';
            $paramIndex++;
        }

        if (!empty($filters['depart_name'])) {
            $whereConditions[] = "d.depart_name ILIKE $" . $paramIndex;
            $params[] = '%' . $filters['depart_name'] . '%';
            $paramIndex++;
        }

        if (!empty($filters['depart_type_id'])) {
            $whereConditions[] = "d.depart_type_id = $" . $paramIndex;
            $params[] = $filters['depart_type_id'];
            $paramIndex++;
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereConditions[] = "d.is_active = $" . $paramIndex;
            $params[] = $filters['is_active'] === 'true' || $filters['is_active'] === true;
            $paramIndex++;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Count total records
        $countQuery = "SELECT COUNT(*) as total 
                      FROM users.tb_departs d 
                      $whereClause";
        $countResult = pg_query_params($this->conn, $countQuery, $params);
        $totalRecords = 0;
        if ($countResult) {
            $countRow = pg_fetch_assoc($countResult);
            $totalRecords = (int)$countRow['total'];
        }

        // Calculate offset
        $offset = ($page - 1) * $pageSize;

        // Main query with hierarchy and user count
        $query = "SELECT 
                    d.id,
                    d.depart_code,
                    d.depart_name,
                    d.depart_type_id,
                    dt.depart_type_name,
                    dt.depart_type_level,
                    d.parent_id,
                    p.depart_code as parent_depart_code,
                    p.depart_name as parent_depart_name,
                    d.is_active,
                    d.created_at,
                    d.updated_at,
                    COALESCE(uc.user_count, 0)::integer as user_count
                  FROM users.tb_departs d
                  INNER JOIN users.tb_depart_types dt ON dt.id = d.depart_type_id
                  LEFT JOIN users.tb_departs p ON p.id = d.parent_id
                  LEFT JOIN (
                      SELECT depart_id, COUNT(*)::integer as user_count
                      FROM users.tb_users
                      WHERE depart_id IS NOT NULL
                      GROUP BY depart_id
                  ) uc ON uc.depart_id = d.id
                  $whereClause
                  ORDER BY dt.depart_type_level, d.depart_code
                  LIMIT $" . $paramIndex . " OFFSET $" . ($paramIndex + 1);

        $params[] = $pageSize;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            // error_log("Error in readDepart: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            // Ensure user_count is integer
            if (isset($row['user_count'])) {
                $row['user_count'] = (int)$row['user_count'];
            }
            $datas[] = $row;
        }

        return [
            'data' => $datas,
            'total' => $totalRecords,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($totalRecords / $pageSize)
        ];
    }

    /**
     * ดึงข้อมูล depart หนึ่งรายการพร้อม hierarchy
     * 
     * @param int $id ID ของ department
     * @return array|false ข้อมูล department หรือ false ถ้าไม่พบ
     */
    public function readDepartOne($id)
    {
        $query = "SELECT 
                    d.id,
                    d.depart_code,
                    d.depart_name,
                    d.depart_type_id,
                    dt.depart_type_name,
                    dt.depart_type_level,
                    d.parent_id,
                    p.depart_code as parent_depart_code,
                    p.depart_name as parent_depart_name,
                    d.is_active,
                    d.created_at,
                    d.updated_at
                  FROM users.tb_departs d
                  INNER JOIN users.tb_depart_types dt ON dt.id = d.depart_type_id
                  LEFT JOIN users.tb_departs p ON p.id = d.parent_id
                  WHERE d.id = $1";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("Error in readDepartOne: " . pg_last_error($this->conn));
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    /**
     * ดึงข้อมูล departments ตาม type
     * 
     * @param int $departTypeId ID ของ depart type
     * @param bool $isActiveOnly แสดงเฉพาะที่ใช้งานหรือไม่
     * @return array|false ข้อมูล departments หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function readDepartByType($departTypeId, $isActiveOnly = false)
    {
        $whereClause = "WHERE d.depart_type_id = $1";
        $params = [$departTypeId];

        if ($isActiveOnly) {
            $whereClause .= " AND d.is_active = $2";
            $params[] = true;
        }

        $query = "SELECT 
                    d.id,
                    d.depart_code,
                    d.depart_name,
                    d.depart_type_id,
                    dt.depart_type_name,
                    dt.depart_type_level,
                    d.parent_id,
                    d.is_active
                  FROM users.tb_departs d
                  INNER JOIN users.tb_depart_types dt ON dt.id = d.depart_type_id
                  $whereClause
                  ORDER BY d.depart_code";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            // error_log("Error in readDepartByType: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    /**
     * ดึงข้อมูล hierarchy ทั้งหมดของ department (parent + children)
     * ใช้ function get_depart_hierarchy จาก PostgreSQL
     * 
     * @param int $departId ID ของ department
     * @return array|false ข้อมูล hierarchy หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function readDepartHierarchy($departId)
    {
        $query = "SELECT * FROM users.get_depart_hierarchy($1)";
        $result = pg_query_params($this->conn, $query, array($departId));

        if (!$result) {
            // error_log("Error in readDepartHierarchy: " . pg_last_error($this->conn));
            return false;
        }

        $hierarchy = [];
        while ($row = pg_fetch_assoc($result)) {
            $hierarchy[] = $row;
        }

        return $hierarchy;
    }

    /**
     * ดึงข้อมูล children ของ parent
     * 
     * @param int $parentId ID ของ parent department
     * @return array|false ข้อมูล children หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function readDepartChildren($parentId)
    {
        $query = "SELECT 
                    d.id,
                    d.depart_code,
                    d.depart_name,
                    d.depart_type_id,
                    dt.depart_type_name,
                    dt.depart_type_level,
                    d.parent_id,
                    d.is_active
                  FROM users.tb_departs d
                  INNER JOIN users.tb_depart_types dt ON dt.id = d.depart_type_id
                  WHERE d.parent_id = $1
                  ORDER BY d.depart_code";

        $result = pg_query_params($this->conn, $query, array($parentId));

        if (!$result) {
            // error_log("Error in readDepartChildren: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    /**
     * สร้าง depart ใหม่
     * 
     * @param string $departCode รหัสหน่วยงาน
     * @param string $departName ชื่อหน่วยงาน
     * @param int $departTypeId ID ของ depart type
     * @param int|null $parentId ID ของ parent (null ถ้าไม่มี)
     * @param bool $isActive สถานะการใช้งาน
     * @return array|false ข้อมูลที่สร้างใหม่หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function createDepart($departCode, $departName, $departTypeId, $parentId = null, $isActive = true)
    {
        // Validate parent type
        if ($parentId !== null) {
            $parentValidation = $this->validateParentType($departTypeId, $parentId);
            if (!$parentValidation['valid']) {
                // error_log("Error: " . $parentValidation['message']);
                return ['error' => $parentValidation['message']];
            }
        }

        // ตรวจสอบว่า depart_code ซ้ำใน type เดียวกันหรือไม่
        $queryCheckCode = "SELECT COUNT(*) as count 
                          FROM users.tb_departs 
                          WHERE depart_code = $1 AND depart_type_id = $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($departCode, $departTypeId));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            return ['error' => 'รหัสหน่วยงานนี้ถูกใช้แล้วในประเภทนี้'];
        }

        // Get depart_type_name for type column (backward compatibility)
        $queryType = "SELECT depart_type_name FROM users.tb_depart_types WHERE id = $1";
        $resultType = pg_query_params($this->conn, $queryType, array($departTypeId));
        if (!$resultType || pg_num_rows($resultType) == 0) {
            return ['error' => 'ไม่พบประเภทหน่วยงาน'];
        }
        $rowType = pg_fetch_assoc($resultType);
        $departTypeName = $rowType['depart_type_name'];

        // Insert ข้อมูลใหม่ (include type for backward compatibility)
        $query = "INSERT INTO users.tb_departs 
                  (depart_code, depart_name, depart_type_id, type, parent_id, is_active, created_at, updated_at) 
                  VALUES ($1, $2, $3, $4, $5, $6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                  RETURNING id";

        $result = pg_query_params($this->conn, $query, array(
            $departCode,
            $departName,
            $departTypeId,
            $departTypeName, // Set type for backward compatibility
            $parentId,
            $isActive
        ));

        if (!$result) {
            // error_log("Error in createDepart: " . pg_last_error($this->conn));
            return ['error' => 'เกิดข้อผิดพลาดในการสร้างข้อมูล'];
        }

        $row = pg_fetch_assoc($result);
        $newId = $row['id'];

        // ดึงข้อมูลที่สร้างใหม่
        return $this->readDepartOne($newId);
    }

    /**
     * อัปเดตข้อมูล depart
     * 
     * @param int $id ID ของ department
     * @param string $departCode รหัสหน่วยงาน
     * @param string $departName ชื่อหน่วยงาน
     * @param int $departTypeId ID ของ depart type
     * @param int|null $parentId ID ของ parent (null ถ้าไม่มี)
     * @param bool $isActive สถานะการใช้งาน
     * @return array|false ข้อมูลที่อัปเดตหรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function updateDepart($id, $departCode, $departName, $departTypeId, $parentId = null, $isActive = true)
    {
        // ตรวจสอบว่าไม่ให้ parent เป็นตัวเอง
        if ($parentId == $id) {
            return ['error' => 'ไม่สามารถตั้งหน่วยงานแม่เป็นตัวเองได้'];
        }

        // Validate parent type
        if ($parentId !== null) {
            $parentValidation = $this->validateParentType($departTypeId, $parentId);
            if (!$parentValidation['valid']) {
                // error_log("Error: " . $parentValidation['message']);
                return ['error' => $parentValidation['message']];
            }
        }

        // ตรวจสอบว่า depart_code ซ้ำใน type เดียวกัน (ยกเว้นตัวเอง)
        $queryCheckCode = "SELECT COUNT(*) as count 
                          FROM users.tb_departs 
                          WHERE depart_code = $1 AND depart_type_id = $2 AND id != $3";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($departCode, $departTypeId, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            return ['error' => 'รหัสหน่วยงานนี้ถูกใช้แล้วในประเภทนี้'];
        }

        // Get depart_type_name for type column (backward compatibility)
        $queryType = "SELECT depart_type_name FROM users.tb_depart_types WHERE id = $1";
        $resultType = pg_query_params($this->conn, $queryType, array($departTypeId));
        if (!$resultType || pg_num_rows($resultType) == 0) {
            return ['error' => 'ไม่พบประเภทหน่วยงาน'];
        }
        $rowType = pg_fetch_assoc($resultType);
        $departTypeName = $rowType['depart_type_name'];

        // Update ข้อมูล (include type for backward compatibility)
        $query = "UPDATE users.tb_departs 
                  SET depart_code = $1, 
                      depart_name = $2, 
                      depart_type_id = $3,
                      type = $4,
                      parent_id = $5, 
                      is_active = $6, 
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = $7";

        $result = pg_query_params($this->conn, $query, array(
            $departCode,
            $departName,
            $departTypeId,
            $departTypeName, // Set type for backward compatibility
            $parentId,
            $isActive,
            $id
        ));

        if (!$result) {
            // error_log("Error in updateDepart: " . pg_last_error($this->conn));
            return ['error' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล'];
        }

        // ดึงข้อมูลที่อัปเดต
        return $this->readDepartOne($id);
    }

    /**
     * ลบ depart
     * 
     * @param int $id ID ของ department
     * @return bool true ถ้าสำเร็จ, false ถ้าเกิดข้อผิดพลาด
     */
    public function deleteDepart($id)
    {
        // ตรวจสอบว่ามี children หรือไม่
        $queryCheckChildren = "SELECT COUNT(*) as count FROM users.tb_departs WHERE parent_id = $1";
        $resultChildren = pg_query_params($this->conn, $queryCheckChildren, array($id));
        $rowChildren = pg_fetch_assoc($resultChildren);

        if ($rowChildren['count'] > 0) {
            return ['error' => 'ไม่สามารถลบได้ เนื่องจากมีหน่วยงานย่อยอยู่'];
        }

        // ตรวจสอบว่ามี users ใช้หรือไม่
        $queryCheckUsers = "SELECT COUNT(*) as count FROM users.tb_users WHERE depart_id = $1";
        $resultUsers = pg_query_params($this->conn, $queryCheckUsers, array($id));
        $rowUsers = pg_fetch_assoc($resultUsers);

        if ($rowUsers['count'] > 0) {
            return ['error' => 'ไม่สามารถลบได้ เนื่องจากมีผู้ใช้อยู่ในหน่วยงานนี้'];
        }

        // ลบข้อมูล
        $query = "DELETE FROM users.tb_departs WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("Error in deleteDepart: " . pg_last_error($this->conn));
            return ['error' => 'เกิดข้อผิดพลาดในการลบข้อมูล'];
        }

        return true;
    }

    /**
     * ตรวจสอบว่า parent type ถูกต้องหรือไม่
     * 
     * @param int $departTypeId Type ของ department ที่จะสร้าง/แก้ไข
     * @param int $parentId ID ของ parent
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateParentType($departTypeId, $parentId)
    {
        // ดึงข้อมูล parent
        $queryParent = "SELECT d.depart_type_id, dt.depart_type_level 
                       FROM users.tb_departs d
                       INNER JOIN users.tb_depart_types dt ON dt.id = d.depart_type_id
                       WHERE d.id = $1";
        $resultParent = pg_query_params($this->conn, $queryParent, array($parentId));

        if (!$resultParent || pg_num_rows($resultParent) == 0) {
            return ['valid' => false, 'message' => 'ไม่พบหน่วยงานแม่'];
        }

        $parentRow = pg_fetch_assoc($resultParent);
        $parentTypeId = $parentRow['depart_type_id'];
        $parentLevel = (int)$parentRow['depart_type_level'];

        // ดึงข้อมูล type ของ department ที่จะสร้าง/แก้ไข
        $queryType = "SELECT depart_type_level FROM users.tb_depart_types WHERE id = $1";
        $resultType = pg_query_params($this->conn, $queryType, array($departTypeId));
        $typeRow = pg_fetch_assoc($resultType);
        $currentLevel = (int)$typeRow['depart_type_level'];

        // Validate hierarchy: HQ(1) -> Area(2) -> Province(3) -> Branch(4)
        if ($currentLevel == 1) {
            // HQ ไม่มี parent
            return ['valid' => false, 'message' => 'สำนักงานใหญ่ไม่สามารถมีหน่วยงานแม่ได้'];
        } elseif ($currentLevel == 2) {
            // Area ต้องมี parent เป็น HQ (level 1)
            if ($parentLevel != 1) {
                return ['valid' => false, 'message' => 'เขตต้องมีหน่วยงานแม่เป็นสำนักงานใหญ่'];
            }
        } elseif ($currentLevel == 3) {
            // Province ต้องมี parent เป็น Area (level 2)
            if ($parentLevel != 2) {
                return ['valid' => false, 'message' => 'จังหวัดต้องมีหน่วยงานแม่เป็นเขต'];
            }
        } elseif ($currentLevel == 4) {
            // Branch ต้องมี parent เป็น Province (level 3)
            if ($parentLevel != 3) {
                return ['valid' => false, 'message' => 'สาขาต้องมีหน่วยงานแม่เป็นจังหวัด'];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * นับจำนวน users ใน department
     * 
     * @param int $departId ID ของ department
     * @return int จำนวน users
     */
    public function countUsersByDepart($departId)
    {
        $query = "SELECT COUNT(*) as count 
                  FROM users.tb_users 
                  WHERE depart_id = $1";
        $result = pg_query_params($this->conn, $query, array($departId));

        if (!$result) {
            // error_log("Error in countUsersByDepart: " . pg_last_error($this->conn));
            return 0;
        }

        $row = pg_fetch_assoc($result);
        return (int)$row['count'];
    }

    /**
     * ดึงรายชื่อ users ใน department
     * 
     * @param int $departId ID ของ department
     * @return array|false ข้อมูล users หรือ false ถ้าเกิดข้อผิดพลาด
     */
    public function getUsersByDepart($departId)
    {
        // First try with position_name for role (as used in user_model.php)
        // If that fails, try with role_name
        $query = "SELECT 
                    u.id,
                    u.user_code,
                    u.user_fname,
                    u.user_lname,
                    u.user_email,
                    u.user_status,
                    p.position_code,
                    p.position_name,
                    r.role_code,
                    p.position_name AS role_name,
                    u.created_at,
                    u.updated_at
                  FROM users.tb_users u
                  LEFT JOIN users.tb_positions p ON p.id = u.position_id
                  LEFT JOIN users.tb_roles r ON r.id = u.role_id
                  WHERE u.depart_id = $1
                  ORDER BY u.user_fname, u.user_lname";

        $result = pg_query_params($this->conn, $query, array($departId));

        if (!$result) {
            $error = pg_last_error($this->conn);
            // error_log("Error in getUsersByDepart (with position_name): " . $error);

            // Try with role_name instead
            $query2 = "SELECT 
                    u.id,
                    u.user_code,
                    u.user_fname,
                    u.user_lname,
                    u.user_email,
                    u.user_status,
                    p.position_code,
                    p.position_name,
                    r.role_code,
                    r.role_name,
                    u.created_at,
                    u.updated_at
                  FROM users.tb_users u
                  LEFT JOIN users.tb_positions p ON p.id = u.position_id
                  LEFT JOIN users.tb_roles r ON r.id = u.role_id
                  WHERE u.depart_id = $1
                  ORDER BY u.user_fname, u.user_lname";

            $result2 = pg_query_params($this->conn, $query2, array($departId));

            if (!$result2) {
                $error2 = pg_last_error($this->conn);
                // error_log("Error in getUsersByDepart (with role_name): " . $error2);

                // Try without role join at all
                $query3 = "SELECT 
                        u.id,
                        u.user_code,
                        u.user_fname,
                        u.user_lname,
                        u.user_email,
                        u.user_status,
                        p.position_code,
                        p.position_name,
                        u.role_id,
                        u.created_at,
                        u.updated_at
                      FROM users.tb_users u
                      LEFT JOIN users.tb_positions p ON p.id = u.position_id
                      WHERE u.depart_id = $1
                      ORDER BY u.user_fname, u.user_lname";

                $result3 = pg_query_params($this->conn, $query3, array($departId));

                if (!$result3) {
                    // error_log("Error in getUsersByDepart (without role): " . pg_last_error($this->conn));
                    return false;
                }

                $result = $result3;
            } else {
                $result = $result2;
            }
        }

        $users = [];
        while ($row = pg_fetch_assoc($result)) {
            // Ensure role_name exists even if null
            if (!isset($row['role_name'])) {
                $row['role_name'] = null;
            }
            $users[] = $row;
        }

        return $users;
    }
}
