<?php
class UserModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readUser()
    {
        $query = "SELECT * FROM users.tb_users";

        // ใช้ pg_query สำหรับการดึงข้อมูล
        $result = pg_query($this->conn, $query);

        if (!$result) {
            // ถ้ามีข้อผิดพลาดในการ query ให้แสดงข้อความ
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas; // คืนค่าเป็น array ของข้อมูล
    }

    public function readUserAll()
    {
        $query = "SELECT 
            US.id,
            US.user_code,
            US.user_fname,
            US.user_lname,
            PS.position_code, PS.position_name, PS.approve_branch, PS.approve_province, PS.approve_airea, PS.approve_head_office, PS.approve_finance, PS.approve_payment, 
            DP.depart_code, DP.depart_name, 
            LV.level_code, LV.level_name, 
            BR.branch_code, BR.branch_name, 
            PV.province_code, PV.province_name 
        FROM users.tb_users AS US 
        LEFT JOIN users.tb_branchs AS BR ON BR.id = US.branch_id 
        LEFT JOIN users.tb_positions AS PS ON PS.id = US.position_id
        LEFT JOIN users.tb_departs AS DP ON DP.id = US.depart_id
        LEFT JOIN users.tb_levels AS LV ON LV.id = US.level_id
        LEFT JOIN users.tb_provinces AS PV ON PV.id = BR.province_id
        ORDER BY US.user_fname, US.user_lname";

        // รัน query โดยไม่ต้องส่ง parameter
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงข้อมูลทั้งหมด
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data; // คืนค่าเป็น array ของ users ทั้งหมด
    }


    public function readUserOne($id)
    {
        // ป้องกัน SQL Injection โดยการใช้ parameterized query
        $query = "SELECT 
        US.id AS user_id,
        US.user_code,
        US.user_fname, 
        US.user_lname, 
        US.user_email,
        US.user_status,
        US.branch_id AS user_branch_id,
        US.position_id AS user_position_id,
        US.depart_id AS user_depart_id,
        US.role_id AS user_role_id,
        PS.id AS position_id,
        PS.position_code, 
        PS.position_name, 
        PS.approve_branch, 
        PS.approve_province, 
        PS.approve_airea, 
        PS.approve_head_office, 
        PS.approve_finance, 
        PS.approve_payment, 
        PS.approve_operating_primary,
        PS.approve_operating_strategic,
        PS.approve_investment,
        PS.approve_pacels,
        PS.approve_proposal,
        DP.id AS depart_id,
        DP.depart_code, 
        DP.depart_name, 
        LV.id AS level_id,
        LV.level_code, 
        LV.level_name, 
        BR.id AS branch_id,
        BR.branch_code, 
        BR.branch_name, 
        BR.branch_type,
        PV.id AS province_id,
        PV.province_code, 
        PV.province_name,
        PV.province_type,
        AR.id AS area_id,
        AR.area_code,
        AR.area_name,
        AR.area_type,
        HO.id AS head_office_id,
        HO.head_office_code,
        HO.head_office_name,
        HO.head_office_type
        FROM users.tb_users AS US 
        LEFT JOIN users.tb_branchs AS BR ON BR.id = US.branch_id 
        LEFT JOIN users.tb_positions AS PS ON PS.id = US.position_id
        LEFT JOIN users.tb_departs AS DP ON DP.id = US.depart_id
        LEFT JOIN users.tb_levels AS LV ON LV.id = US.level_id
        LEFT JOIN users.tb_provinces AS PV ON PV.id = BR.province_id
        LEFT JOIN users.tb_areas AS AR ON AR.id = PV.area_id
        LEFT JOIN users.tb_head_offices AS HO ON HO.id = AR.head_office_id
        WHERE US.id=$1"; // ใช้ placeholder $1

        // เตรียมการ query
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // ถ้ามีข้อผิดพลาดในการ query ให้แสดงข้อความ
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ใช้ pg_fetch_assoc เพื่อดึงแค่รายการเดียว
        $data = pg_fetch_assoc($result);

        // ถ้าพบข้อมูลคืนค่าแค่รายการเดียว
        return $data ? $data : null; // คืนค่าข้อมูล (หรือ null ถ้าไม่มีข้อมูล)
    }

    public function createUser($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status)
    {
        // ตรวจสอบว่ามี user_email ซ้ำหรือไม่
        $queryCheckEmail = "SELECT COUNT(*) FROM users.tb_users WHERE user_email = $1";
        $resultEmail = pg_query_params($this->conn, $queryCheckEmail, array($user_email));
        $rowEmail = pg_fetch_assoc($resultEmail);
    
        if ($rowEmail['count'] > 0) {
            echo "Error: The email is already taken.\n";
            return false;
        }
    
        // ตรวจสอบว่ามี user_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_users WHERE user_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($user_code));
        $rowCode = pg_fetch_assoc($resultCode);
    
        if ($rowCode['count'] > 0) {
            echo "Error: The user code is already taken.\n";
            return false;
        }
    
        // หากไม่มีการซ้ำก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_users (branch_id, depart_id, role_id, position_id, user_code, user_pass, user_fname, user_lname, user_email, user_status) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
        $result = pg_query_params($this->conn, $query, array($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status));
    
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
    
        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    public function updateUser($id, $branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status)
    {
        // ตรวจสอบว่า user_email ซ้ำกับ id อื่นหรือไม่
        $queryCheckEmail = "SELECT COUNT(*) FROM users.tb_users WHERE user_email = $1 AND id != $2";
        $resultEmail = pg_query_params($this->conn, $queryCheckEmail, array($user_email, $id));
        $rowEmail = pg_fetch_assoc($resultEmail);
    
        if ($rowEmail['count'] > 0) {
            echo "Error: The email is already taken by another user.\n";
            return false;
        }
    
        // ตรวจสอบว่า user_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_users WHERE user_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($user_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);
    
        if ($rowCode['count'] > 0) {
            echo "Error: The user code is already taken by another user.\n";
            return false;
        }
    
        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_users 
                  SET branch_id = $1, depart_id = $2, role_id = $3, position_id = $4, user_code = $5, user_pass = $6, 
                      user_fname = $7, user_lname = $8, user_email = $9, user_status = $10
                  WHERE id = $11";
        $result = pg_query_params($this->conn, $query, array($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status, $id));
    
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
    
        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }
}
