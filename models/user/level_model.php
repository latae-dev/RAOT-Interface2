<?php

class LevelModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_levels
    public function readLevel()
    {
        $query = "SELECT * FROM users.tb_levels";
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

    // ฟังก์ชันในการดึงข้อมูลของ level หนึ่งรายการ
    public function readLevelOne($id)
    {
        $query = "SELECT * FROM users.tb_levels WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง level ใหม่
    public function createLevel($level_code, $level_name)
    {
        // ตรวจสอบว่า level_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_levels WHERE level_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($level_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The level code is already taken.\n";
            return false;
        }

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_levels (level_code, level_name) VALUES ($1, $2)";
        $result = pg_query_params($this->conn, $query, array($level_code, $level_name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ level
    public function updateLevel($id, $level_code, $level_name)
    {
        // ตรวจสอบว่า level_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_levels WHERE level_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($level_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The level code is already taken by another level.\n";
            return false;
        }

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_levels SET level_code = $1, level_name = $2 WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($level_code, $level_name, $id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
