<?php

/**
 * Asset Types Model (โมเดลจัดการประเภทข้อมูล)
 * หน้าที่:
 * - จัดการข้อมูลประเภทข้อมูลในงบลงทุน (Asset Types)
 * - เชื่อมต่อกับฐานข้อมูล PostgreSQL
 * - รองรับการ CRUD (Create, Read, Update, Delete)
 * - ใช้ prepared statements เพื่อความปลอดภัย
 
 */
class AssetTypesModel
{
    /**
     * การเชื่อมต่อฐานข้อมูล
     */
    private $conn;

    /**
     * ชื่อตารางในฐานข้อมูล
     */
    private $table = 'parameters.tb_asset_type';

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
     * ดึงข้อมูลประเภทข้อมูลทั้งหมด
     * @return array|false รายการประเภทข้อมูลทั้งหมดที่ยังใช้งานอยู่ หรือ false หากเกิดข้อผิดพลาด
     */
    public function readAssetTypes()
    {
        $query = "SELECT id, code, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE is_active = true 
                  ORDER BY id ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Database error in readAssetTypes: " . pg_last_error($this->conn));
            return false;
        }

        $asset_types = [];
        while ($row = pg_fetch_assoc($result)) {
            $asset_types[] = [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'is_active' => $row['is_active']
            ];
        }

        return $asset_types;
    }

    /**
     * ดึงข้อมูลประเภทข้อมูลรายการเดียวตาม ID
     * @param int $id รหัสประเภทข้อมูล
     * @return array|false ข้อมูลประเภทข้อมูล หรือ false หากไม่พบหรือเกิดข้อผิดพลาด
     */
    public function readAssetTypeOne($id)
    {
        $query = "SELECT id, code, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE id = $1 AND is_active = true";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in readAssetTypeOne: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);

        if ($row) {
            return [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'is_active' => $row['is_active']
            ];
        }

        return false;
    }

    /**
     * ดึงข้อมูลประเภทข้อมูลตามรหัส (Code)
     * @param string $code รหัสประเภทข้อมูล (เช่น '1091030000')
     * @return array|false ข้อมูลประเภทข้อมูล หรือ false หากไม่พบหรือเกิดข้อผิดพลาด
     */
    public function readAssetTypeByCode($code)
    {
        $query = "SELECT id, code, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE code = $1 AND is_active = true";

        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            error_log("Database error in readAssetTypeByCode: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);

        if ($row) {
            return [
                'id' => $row['id'],
                'code' => $row['code'],
                'name' => $row['name'],
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
     * สร้างประเภทข้อมูลใหม่
     * @param array $data ข้อมูลประเภทข้อมูล ['code' => 'รหัส', 'name' => 'ชื่อ']
     * @return int|false ID ของประเภทข้อมูลที่สร้างขึ้น หรือ false หากเกิดข้อผิดพลาด
     */
    public function createAssetType($data)
    {
        $query = "INSERT INTO " . $this->table . " 
                  (code, name, created_at, updated_at, is_active) 
                  VALUES ($1, $2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, true) 
                  RETURNING id";

        $result = pg_query_params($this->conn, $query, [
            $data['code'],
            $data['name']
        ]);

        if (!$result) {
            error_log("Database error in createAssetType: " . pg_last_error($this->conn));
            return false;
        }

        $row = pg_fetch_assoc($result);
        return $row['id'] ?? false;
    }

    // ========================================
    // UPDATE OPERATIONS (การปรับปรุงข้อมูล)
    // ========================================

    /**
     * ปรับปรุงข้อมูลประเภทข้อมูล
     * @param int $id รหัสประเภทข้อมูลที่ต้องการปรับปรุง
     * @param array $data ข้อมูลใหม่ ['code' => 'รหัส', 'name' => 'ชื่อ']
     * @return bool true หากปรับปรุงสำเร็จ, false หากเกิดข้อผิดพลาด
     */
    public function updateAssetType($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
                  SET code = $1, name = $2, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $3 AND is_active = true";

        $result = pg_query_params($this->conn, $query, [
            $data['code'],
            $data['name'],
            $id
        ]);

        if (!$result) {
            error_log("Database error in updateAssetType: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }

    // ========================================
    // DELETE OPERATIONS (การลบข้อมูล)
    // ========================================

    /**
     * ลบประเภทข้อมูล (Soft Delete)
     * ไม่ลบข้อมูลจริง แต่เปลี่ยน is_active เป็น false
     * @param int $id รหัสประเภทข้อมูลที่ต้องการลบ
     * @return bool true หากลบสำเร็จ, false หากเกิดข้อผิดพลาด
     */
    public function deleteAssetType($id)
    {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = false, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $1";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in deleteAssetType: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }
}
