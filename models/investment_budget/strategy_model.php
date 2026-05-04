<?php

/**
 * Strategy Model (โมเดลจัดการยุทธศาสตร์)
 * หน้าที่:
 * - จัดการข้อมูลยุทธศาสตร์ในงบลงทุน (Strategy)
 * - เชื่อมต่อกับฐานข้อมูล PostgreSQL
 * - รองรับการ CRUD (Create, Read, Update, Delete)
 * - ใช้ prepared statements เพื่อความปลอดภัย
 
 */
class StrategyModel
{
    /**
     * การเชื่อมต่อฐานข้อมูล
     */
    private $conn;

    /**
     * ชื่อตารางในฐานข้อมูล
     */
    private $table = 'parameters.tb_strategy';

    /**
     * สร้างอินสแตนซ์ของโมเดล
     * @param resource $db การเชื่อมต่อฐานข้อมูล PostgreSQL
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ========================================
    // READ OPERATIONS (การดึงข้อมูล)
    // ========================================

    /**
     * ดึงข้อมูลยุทธศาสตร์ทั้งหมด
     * @return array|false รายการยุทธศาสตร์ทั้งหมดที่ยังใช้งานอยู่ หรือ false หากเกิดข้อผิดพลาด
     */
    public function readStrategies()
    {
        $query = "SELECT id, code, name, description, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE is_active = true 
                  ORDER BY code ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Database error in readStrategies: " . pg_last_error($this->conn));
            return false;
        }

        $strategies = [];
        while ($row = pg_fetch_assoc($result)) {
            $strategies[] = [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'is_active' => $row['is_active']
            ];
        }

        return $strategies;
    }

    /**
     * ดึงข้อมูลยุทธศาสตร์รายการเดียวตาม ID
     * @param int $id รหัสยุทธศาสตร์
     * @return array|false ข้อมูลยุทธศาสตร์ หรือ false หากไม่พบหรือเกิดข้อผิดพลาด
     */
    public function readStrategyOne($id)
    {
        $query = "SELECT id, code, name, description, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE id = $1 AND is_active = true";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in readStrategyOne: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);

        if ($row) {
            return [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'is_active' => $row['is_active']
            ];
        }

        return false;
    }

    /**
     * ดึงข้อมูลยุทธศาสตร์ตามรหัส (Code)
     * @param string $code รหัสยุทธศาสตร์ (เช่น 'S01')
     * @return array|false ข้อมูลยุทธศาสตร์ หรือ false หากไม่พบหรือเกิดข้อผิดพลาด
     */
    public function readStrategyByCode($code)
    {
        $query = "SELECT id, code, name, description, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE code = $1 AND is_active = true";

        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            error_log("Database error in readStrategyByCode: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);

        if ($row) {
            return [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'is_active' => $row['is_active']
            ];
        }

        return false;
    }

    // ========================================
    // CREATE OPERATIONS (การสร้างข้อมูล)
    // ========================================

    /**
     * สร้างยุทธศาสตร์ใหม่
     * @param array $data ข้อมูลยุทธศาสตร์ ['code' => 'รหัส', 'name' => 'ชื่อ', 'description' => 'รายละเอียด']
     * @return int|false ID ของยุทธศาสตร์ที่สร้างขึ้น หรือ false หากเกิดข้อผิดพลาด
     */
    public function createStrategy($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (code, name, description, created_at, updated_at, is_active) 
                  VALUES ($1, $2, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, true) 
                  RETURNING id";

        $result = pg_query_params($this->conn, $query, [
            $data['code'],
            $data['name'],
            $data['description'] ?? ''
        ]);

        if (!$result) {
            error_log("Database error in createStrategy: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);
        return $row['id'] ?? false;
    }

    // ========================================
    // UPDATE OPERATIONS (การปรับปรุงข้อมูล)
    // ========================================

    /**
     * ปรับปรุงข้อมูลยุทธศาสตร์
     * @param int $id รหัสยุทธศาสตร์ที่ต้องการปรับปรุง
     * @param array $data ข้อมูลใหม่ ['code' => 'รหัส', 'name' => 'ชื่อ', 'description' => 'รายละเอียด']
     * @return bool true หากปรับปรุงสำเร็จ, false หากเกิดข้อผิดพลาด
     */
    public function updateStrategy($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET code = $1, name = $2, description = $3, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $4 AND is_active = true";

        $result = pg_query_params($this->conn, $query, [
            $data['code'],
            $data['name'],
            $data['description'] ?? '',
            $id
        ]);

        if (!$result) {
            error_log("Database error in updateStrategy: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }

    // ========================================
    // DELETE OPERATIONS (การลบข้อมูล)
    // ========================================

    /**
     * ลบยุทธศาสตร์ (Soft Delete)
     * ไม่ลบข้อมูลจริง แต่เปลี่ยน is_active เป็น false
     * @param int $id รหัสยุทธศาสตร์ที่ต้องการลบ
     * @return bool true หากลบสำเร็จ, false หากเกิดข้อผิดพลาด
     */
    public function deleteStrategy($id)
    {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = false, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $1";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in deleteStrategy: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }
}
