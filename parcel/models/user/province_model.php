<?php
class ProvinceModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_provinces
    public function readProvince()
    {
        $query = "SELECT * FROM users.tb_provinces";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // ฟังก์ชันในการดึงข้อมูลของ province หนึ่งรายการ
    public function readProvinceOne($id)
    {
        $query = "SELECT * FROM users.tb_provinces WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง province ใหม่
    public function createProvince($province_code, $province_name)
    {
        // ตรวจสอบว่า province_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_provinces WHERE province_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($province_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The province code is already taken.\n";
            return false;
        }

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_provinces (province_code, province_name) VALUES ($1, $2)";
        $result = pg_query_params($this->conn, $query, array($province_code, $province_name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ province
    public function updateProvince($id, $province_code, $province_name)
    {
        // ตรวจสอบว่า province_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_provinces WHERE province_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($province_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The province code is already taken by another province.\n";
            return false;
        }

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_provinces SET province_code = $1, province_name = $2 WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($province_code, $province_name, $id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
