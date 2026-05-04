<?php
class HeadOfficeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_head_offices
    public function readHeadOffice()
    {
        $query = "SELECT * FROM users.tb_head_offices";
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

    // ฟังก์ชันในการดึงข้อมูลของ head_office หนึ่งรายการ
    public function readHeadOfficeOne($id)
    {
        $query = "SELECT * FROM users.tb_head_offices WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง head_office ใหม่
    public function createHeadOffice($head_office_code, $head_office_name)
    {
        // ตรวจสอบว่า head_office_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_head_offices WHERE head_office_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($head_office_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The head_office code is already taken.\n";
            return false;
        }

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_head_offices (head_office_code, head_office_name) VALUES ($1, $2)";
        $result = pg_query_params($this->conn, $query, array($head_office_code, $head_office_name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ head_office
    public function updateHeadOffice($id, $head_office_code, $head_office_name)
    {
        // ตรวจสอบว่า head_office_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_head_offices WHERE head_office_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($head_office_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The head_office code is already taken by another head_office.\n";
            return false;
        }

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_head_offices SET head_office_code = $1, head_office_name = $2 WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($head_office_code, $head_office_name, $id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
