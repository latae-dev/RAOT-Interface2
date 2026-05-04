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
            // ถ้ามีข้อผิดพลาดในการ query ให้ log error
            // error_log("UserModel::readUser() error: " . pg_last_error($this->conn));
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
        // ใช้ query ที่ง่ายที่สุดก่อน เพื่อหลีกเลี่ยงปัญหา
        try {
            $query = "SELECT 
                US.id,
                US.user_code,
                US.user_fname,
                US.user_lname,
                US.user_email,
                US.user_status,
                US.created_at,
                US.updated_at,
                PS.position_code, 
                PS.position_name, 
                DP.depart_code, 
                DP.depart_name, 
                LV.level_code, 
                LV.level_name, 
                RL.role_code, 
                PS.position_name AS role_name
            FROM users.tb_users AS US 
            LEFT JOIN users.tb_positions AS PS ON PS.id = US.position_id
            LEFT JOIN users.tb_departs AS DP ON DP.id = US.depart_id
            LEFT JOIN users.tb_levels AS LV ON LV.id = US.level_id
            LEFT JOIN users.tb_roles AS RL ON RL.id = US.role_id
            ORDER BY US.user_fname, US.user_lname";

            $result = pg_query($this->conn, $query);

            if (!$result) {
                $error = pg_last_error($this->conn);
                // error_log("UserModel::readUserAll() query error: " . $error);
                // Fallback: ลองใช้ query ที่ง่ายกว่า (ไม่ JOIN กับ tables ที่อาจไม่มี)
                return $this->readUserAllSimple();
            }

            // ดึงข้อมูลทั้งหมด
            $data = [];
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }

            return $data; // คืนค่าเป็น array ของ users ทั้งหมด
        } catch (Exception $e) {
            // error_log("UserModel::readUserAll() exception: " . $e->getMessage());
            // Fallback: ลองใช้ query ที่ง่ายกว่า
            return $this->readUserAllSimple();
        }
    }

    /**
     * Fallback method - ใช้ query ที่ง่ายที่สุด
     */
    private function readUserAllSimple()
    {
        try {
            $query = "SELECT 
                US.id,
                US.user_code,
                US.user_fname,
                US.user_lname,
                US.user_email,
                US.user_status,
                US.created_at,
                US.updated_at,
                PS.position_code, 
                PS.position_name, 
                DP.depart_code, 
                DP.depart_name
            FROM users.tb_users AS US 
            LEFT JOIN users.tb_positions AS PS ON PS.id = US.position_id
            LEFT JOIN users.tb_departs AS DP ON DP.id = US.depart_id
            ORDER BY US.user_fname, US.user_lname";

            $result = pg_query($this->conn, $query);

            if (!$result) {
                $error = pg_last_error($this->conn);
                // error_log("UserModel::readUserAllSimple() query error: " . $error);
                return false;
            }

            // ดึงข้อมูลทั้งหมด
            $data = [];
            while ($row = pg_fetch_assoc($result)) {
                // เพิ่มค่า default สำหรับ fields ที่ไม่มี
                $row['level_code'] = null;
                $row['level_name'] = null;
                $row['role_code'] = null;
                $row['role_name'] = null;
                $data[] = $row;
            }

            return $data;
        } catch (Exception $e) {
            // error_log("UserModel::readUserAllSimple() exception: " . $e->getMessage());
            return false;
        }
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
        NULL AS branch_type,  -- ไม่มีใน VIEW v_branchs แล้ว
        PV.id AS province_id,
        PV.depart_code AS province_code, 
        PV.depart_name AS province_name,
        NULL AS province_type,  -- ไม่มีใน tb_departs
        AR.id AS area_id,
        AR.depart_code AS area_code,
        AR.depart_name AS area_name,
        NULL AS area_type,  -- ไม่มีใน tb_departs
        HO.id AS head_office_id,
        HO.depart_code AS head_office_code,
        HO.depart_name AS head_office_name,
        NULL AS head_office_type  -- ไม่มีใน tb_departs
        FROM users.tb_users AS US 
        LEFT JOIN users.v_branchs AS BR ON BR.id = US.branch_id 
        LEFT JOIN users.tb_positions AS PS ON PS.id = US.position_id
        LEFT JOIN users.tb_departs AS DP ON DP.id = US.depart_id
        LEFT JOIN users.tb_levels AS LV ON LV.id = US.level_id
        -- ใช้ hierarchy จาก tb_departs แทนการ JOIN หลายตาราง
        -- หา province
        LEFT JOIN users.tb_departs AS PV ON (
            (DP.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'branch') AND PV.id = DP.parent_id AND PV.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'province'))
            OR
            (DP.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'province') AND PV.id = DP.id)
            OR
            (BR.id IS NOT NULL AND PV.id = BR.parent_id AND PV.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'province'))
        )
        -- หา area จาก parent ของ province
        LEFT JOIN users.tb_departs AS AR ON AR.id = PV.parent_id
            AND AR.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'area')
        -- หา head_office จาก parent ของ area
        LEFT JOIN users.tb_departs AS HO ON HO.id = AR.parent_id
            AND HO.depart_type_id = (SELECT id FROM users.tb_depart_types WHERE depart_type_name = 'hq')
        WHERE US.id=$1"; // ใช้ placeholder $1

        // เตรียมการ query
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // ถ้ามีข้อผิดพลาดในการ query ให้ log error
            // error_log("UserModel::readUserOne() error: " . pg_last_error($this->conn));
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
            // error_log("UserModel::createUser() error: The email is already taken.");
            return false;
        }

        // ตรวจสอบว่ามี user_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_users WHERE user_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($user_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            // error_log("UserModel::createUser() error: The user code is already taken.");
            return false;
        }

        // หากไม่มีการซ้ำก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_users (branch_id, depart_id, role_id, position_id, user_code, user_pass, user_fname, user_lname, user_email, user_status) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
        $result = pg_query_params($this->conn, $query, array($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status));

        if (!$result) {
            // error_log("UserModel::createUser() error: " . pg_last_error($this->conn));
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
            // error_log("UserModel::updateUser() error: The email is already taken by another user.");
            return false;
        }

        // ตรวจสอบว่า user_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_users WHERE user_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($user_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            // error_log("UserModel::updateUser() error: The user code is already taken by another user.");
            return false;
        }

        // ถ้า password ไม่ว่าง ให้ update password ด้วย
        if (!empty($user_pass)) {
            $query = "UPDATE users.tb_users 
                      SET branch_id = $1, depart_id = $2, role_id = $3, position_id = $4, user_code = $5, user_pass = $6, 
                          user_fname = $7, user_lname = $8, user_email = $9, user_status = $10
                      WHERE id = $11";
            $result = pg_query_params($this->conn, $query, array($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_pass, $user_fname, $user_lname, $user_email, $user_status, $id));
        } else {
            // ถ้า password ว่าง ไม่ต้อง update password
            $query = "UPDATE users.tb_users 
                      SET branch_id = $1, depart_id = $2, role_id = $3, position_id = $4, user_code = $5, 
                          user_fname = $6, user_lname = $7, user_email = $8, user_status = $9
                      WHERE id = $10";
            $result = pg_query_params($this->conn, $query, array($branch_id, $depart_id, $role_id, $position_id, $user_code, $user_fname, $user_lname, $user_email, $user_status, $id));
        }

        if (!$result) {
            // error_log("UserModel::updateUser() error: " . pg_last_error($this->conn));
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }

    // ฟังก์ชันสำหรับการลบข้อมูลของ user
    public function deleteUser($id)
    {
        // ตรวจสอบว่ามีการใช้งาน user นี้อยู่หรือไม่ (เช่น ในตารางอื่นๆ)
        // ถ้ามีการใช้งานอยู่ อาจจะไม่ควรลบ หรือต้องลบแบบ cascade

        // ถ้าไม่มีการใช้งาน ก็ทำการ delete ข้อมูล
        $query = "DELETE FROM users.tb_users WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            // error_log("UserModel::deleteUser() error: " . pg_last_error($this->conn));
            return false;
        }

        return true; // คืนค่า true ถ้าการ delete สำเร็จ
    }
}
