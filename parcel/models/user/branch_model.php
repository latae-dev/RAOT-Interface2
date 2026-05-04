<?php
class BranchModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_branchs
    public function readBranch()
    {
        $query = "SELECT * FROM users.tb_branchs";
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

    // ฟังก์ชันในการดึงข้อมูลของ branch หนึ่งรายการ
    public function readBranchOne($id)
    {
        $query = "SELECT * FROM users.tb_branchs WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง branch ใหม่
    public function createBranch($branch_code, $branch_name)
    {
        // ตรวจสอบว่า branch_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_branchs WHERE branch_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($branch_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The branch code is already taken.\n";
            return false;
        }

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_branchs (branch_code, branch_name) VALUES ($1, $2)";
        $result = pg_query_params($this->conn, $query, array($branch_code, $branch_name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ branch
    public function updateBranch($id, $branch_code, $branch_name)
    {
        // ตรวจสอบว่า branch_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_branchs WHERE branch_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($branch_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The branch code is already taken by another branch.\n";
            return false;
        }

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_branchs SET branch_code = $1, branch_name = $2 WHERE id = $3";
        $result = pg_query_params($this->conn, $query, array($branch_code, $branch_name, $id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
