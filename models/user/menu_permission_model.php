<?php

class MenuPermissionModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูล permissions ทั้งหมด
    public function readPermissions()
    {
        $query = "SELECT mp.*, m.menu_code, m.menu_name, p.position_code, p.position_name 
                  FROM users.tb_menu_permissions mp
                  JOIN users.tb_menus m ON m.id = mp.menu_id
                  JOIN users.tb_positions p ON p.id = mp.position_id
                  ORDER BY p.position_code, m.menu_code";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Permission query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูล permission ตาม position_id
    public function readPermissionsByPositionId($positionId)
    {
        $query = "SELECT mp.*, m.menu_code, m.menu_name, m.menu_path, m.icon, m.parent_id, m.sort_order, m.category
                  FROM users.tb_menu_permissions mp
                  JOIN users.tb_menus m ON m.id = mp.menu_id
                  WHERE mp.position_id = $1 AND mp.is_granted = true
                  ORDER BY m.sort_order, m.id";
        $result = pg_query_params($this->conn, $query, array($positionId));

        if (!$result) {
            error_log("Permission by position query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูล permission ตาม menu_id
    public function readPermissionsByMenuId($menuId)
    {
        $query = "SELECT mp.*, p.position_code, p.position_name
                  FROM users.tb_menu_permissions mp
                  JOIN users.tb_positions p ON p.id = mp.position_id
                  WHERE mp.menu_id = $1
                  ORDER BY p.position_code";
        $result = pg_query_params($this->conn, $query, array($menuId));

        if (!$result) {
            error_log("Permission by menu query error: " . pg_last_error($this->conn));
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการตรวจสอบว่า position มีสิทธิ์เข้าถึง menu หรือไม่
    public function checkPermission($positionId, $menuId)
    {
        // ตรวจสอบว่ามี permission record หรือไม่
        $query = "SELECT is_granted FROM users.tb_menu_permissions 
                  WHERE position_id = $1 AND menu_id = $2";
        $result = pg_query_params($this->conn, $query, array($positionId, $menuId));

        if (!$result) {
            error_log("Permission check error: " . pg_last_error($this->conn));
            return null; // null = ไม่มี permission record (แสดงให้ทุกคน)
        }

        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            return null; // ไม่มี permission record = แสดงให้ทุกคน
        }

        return $row['is_granted'] === 't' || $row['is_granted'] === true; // true/false
    }

    // ฟังก์ชันสำหรับการสร้าง permission
    public function createPermission($positionId, $menuId, $isGranted = true)
    {
        // ตรวจสอบว่ามี permission อยู่แล้วหรือไม่
        $queryCheck = "SELECT id FROM users.tb_menu_permissions 
                       WHERE position_id = $1 AND menu_id = $2";
        $resultCheck = pg_query_params($this->conn, $queryCheck, array($positionId, $menuId));
        $rowCheck = pg_fetch_assoc($resultCheck);

        if ($rowCheck) {
            // Update existing permission
            return $this->updatePermission($rowCheck['id'], $isGranted);
        }

        // Insert permission ใหม่
        $query = "INSERT INTO users.tb_menu_permissions (position_id, menu_id, is_granted) 
                  VALUES ($1, $2, $3) RETURNING id";
        $result = pg_query_params($this->conn, $query, array($positionId, $menuId, $isGranted ? 'true' : 'false'));

        if (!$result) {
            error_log("Permission insert error: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    // ฟังก์ชันสำหรับการอัปเดต permission
    public function updatePermission($id, $isGranted)
    {
        $query = "UPDATE users.tb_menu_permissions 
                  SET is_granted = $1 
                  WHERE id = $2";
        $result = pg_query_params($this->conn, $query, array($isGranted ? 'true' : 'false', $id));

        if (!$result) {
            error_log("Permission update error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับการลบ permission
    public function deletePermission($id)
    {
        $query = "DELETE FROM users.tb_menu_permissions WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Permission delete error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับการลบ permission ตาม position_id และ menu_id
    public function deletePermissionByPositionAndMenu($positionId, $menuId)
    {
        $query = "DELETE FROM users.tb_menu_permissions 
                  WHERE position_id = $1 AND menu_id = $2";
        $result = pg_query_params($this->conn, $query, array($positionId, $menuId));

        if (!$result) {
            error_log("Permission delete error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }

    // ฟังก์ชันสำหรับ bulk update permissions สำหรับ position
    public function updatePermissionsForPosition($positionId, $permissions)
    {
        // permissions format: [['menu_id' => 1, 'is_granted' => true], ...]
        // หมายเหตุ: ถ้า menu_id ไม่ได้ส่งมา = ไม่มีสิทธิ์ (จะลบ permission record)
        
        // เริ่ม transaction
        pg_query($this->conn, "BEGIN");

        try {
            // ดึง menu_ids ทั้งหมดที่ควรมี permission record
            // รวมทั้ง checked (is_granted = true) และ unchecked (is_granted = false)
            $allMenuIds = [];
            $grantedMenuIds = [];
            
            foreach ($permissions as $perm) {
                if (!isset($perm['menu_id'])) {
                    continue;
                }
                $menuId = (int)$perm['menu_id'];
                $allMenuIds[] = $menuId;
                if (isset($perm['is_granted']) && $perm['is_granted']) {
                    $grantedMenuIds[] = $menuId;
                }
            }
            
            // ดึง menu_ids ทั้งหมดจาก database (เพื่อหาว่ามี menu อะไรบ้าง)
            $queryAllMenus = "SELECT id FROM users.tb_menus WHERE is_active = true";
            $resultAllMenus = pg_query($this->conn, $queryAllMenus);
            $allMenuIdsInDb = [];
            if ($resultAllMenus) {
                while ($row = pg_fetch_assoc($resultAllMenus)) {
                    $allMenuIdsInDb[] = (int)$row['id'];
                }
            }
            
            // ลบ permission records เก่าทั้งหมดของ position นี้
            $queryDelete = "DELETE FROM users.tb_menu_permissions WHERE position_id = $1";
            $resultDelete = pg_query_params($this->conn, $queryDelete, array($positionId));
            if (!$resultDelete) {
                throw new Exception("Failed to delete old permissions: " . pg_last_error($this->conn));
            }
            
            // สร้าง permission records ใหม่
            // - checked menus: is_granted = true
            // - unchecked menus: is_granted = false (เพื่อซ่อนเมนู)
            foreach ($allMenuIdsInDb as $menuId) {
                $isGranted = in_array($menuId, $grantedMenuIds);
                $this->createPermission($positionId, $menuId, $isGranted);
            }

            pg_query($this->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log("Bulk update permissions error: " . $e->getMessage());
            return false;
        }
    }

    // ฟังก์ชันสำหรับ copy permissions จาก position หนึ่งไปยังอีก position หนึ่ง
    public function copyPermissions($fromPositionId, $toPositionId)
    {
        $query = "INSERT INTO users.tb_menu_permissions (position_id, menu_id, is_granted)
                  SELECT $1, menu_id, is_granted
                  FROM users.tb_menu_permissions
                  WHERE position_id = $2
                  ON CONFLICT (position_id, menu_id) DO UPDATE SET
                      is_granted = EXCLUDED.is_granted,
                      updated_at = CURRENT_TIMESTAMP";
        
        $result = pg_query_params($this->conn, $query, array($toPositionId, $fromPositionId));

        if (!$result) {
            error_log("Copy permissions error: " . pg_last_error($this->conn));
            return false;
        }

        return true;
    }
}

