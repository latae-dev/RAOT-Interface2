<?php
class RoleModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_roles
    public function readRole()
    {
        $query = "SELECT * FROM users.tb_roles";
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

    // ฟังก์ชันในการดึงข้อมูลของ role หนึ่งรายการ
    public function readRoleOne($id)
    {
        $query = "SELECT * FROM users.tb_roles WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง role ใหม่
    public function createRole($role_code, $position_name)
    {
        // ตรวจสอบว่า role_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_roles WHERE role_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($role_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The role code is already taken.\n";
            return false;
        }

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_roles (role_code, position_name) VALUES ($1, $2)";
        $result = pg_query_params($this->conn, $query, array($role_code, $position_name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ role
    public function updateRole($id, $role_code, $position_name)
    {
        // ตรวจสอบว่า role_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_roles WHERE role_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($role_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The role code is already taken by another role.\n";
            return false;
        }

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_roles SET role_code = $1, position_name = $2 WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($role_code, $position_name, $id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
