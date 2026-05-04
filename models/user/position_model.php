<?php

class PositionModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_positions
    public function readPosition()
    {
        $query = "SELECT * FROM users.tb_positions";
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

    // ฟังก์ชันในการดึงข้อมูลของ position หนึ่งรายการ
    public function readPositionOne($id)
    {
        $query = "SELECT * FROM users.tb_positions WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง position ใหม่
    public function createPosition($data)
    {
        $position_code = $data['position_code'];
        $position_name = $data['position_name'];
        
        // ตรวจสอบว่า position_code ซ้ำหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_positions WHERE position_code = $1";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($position_code));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The position code is already taken.\n";
            return false;
        }

        // เตรียมข้อมูล approve fields (default เป็น 'nonactive' ถ้าไม่ระบุ)
        $approve_branch = isset($data['approve_branch']) ? $data['approve_branch'] : 'nonactive';
        $approve_province = isset($data['approve_province']) ? $data['approve_province'] : 'nonactive';
        $approve_airea = isset($data['approve_airea']) ? $data['approve_airea'] : 'nonactive';
        $approve_head_office = isset($data['approve_head_office']) ? $data['approve_head_office'] : 'nonactive';
        $approve_finance = isset($data['approve_finance']) ? $data['approve_finance'] : 'nonactive';
        $approve_payment = isset($data['approve_payment']) ? $data['approve_payment'] : 'nonactive';
        $approve_area = isset($data['approve_area']) ? $data['approve_area'] : null;
        $approve_operating_primary = isset($data['approve_operating_primary']) ? $data['approve_operating_primary'] : null;
        $approve_operating_strategic = isset($data['approve_operating_strategic']) ? $data['approve_operating_strategic'] : null;
        $approve_investment = isset($data['approve_investment']) ? $data['approve_investment'] : null;
        $approve_pacels = isset($data['approve_pacels']) ? $data['approve_pacels'] : null;
        $approve_proposal = isset($data['approve_proposal']) ? $data['approve_proposal'] : null;

        // ถ้าไม่มีการซ้ำ ก็ทำการ insert ข้อมูลใหม่
        $query = "INSERT INTO users.tb_positions (
            position_code, position_name, 
            approve_branch, approve_province, approve_airea, approve_head_office, 
            approve_finance, approve_payment, approve_area, 
            approve_operating_primary, approve_operating_strategic, approve_investment, 
            approve_pacels, approve_proposal
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)";
        
        $result = pg_query_params($this->conn, $query, array(
            $position_code, $position_name,
            $approve_branch, $approve_province, $approve_airea, $approve_head_office,
            $approve_finance, $approve_payment, $approve_area,
            $approve_operating_primary, $approve_operating_strategic, $approve_investment,
            $approve_pacels, $approve_proposal
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ insert สำเร็จ
    }

    // ฟังก์ชันสำหรับการอัปเดตข้อมูลของ position
    public function updatePosition($id, $data)
    {
        $position_code = $data['position_code'];
        $position_name = $data['position_name'];
        
        // ตรวจสอบว่า position_code ซ้ำกับ id อื่นหรือไม่
        $queryCheckCode = "SELECT COUNT(*) FROM users.tb_positions WHERE position_code = $1 AND id != $2";
        $resultCode = pg_query_params($this->conn, $queryCheckCode, array($position_code, $id));
        $rowCode = pg_fetch_assoc($resultCode);

        if ($rowCode['count'] > 0) {
            echo "Error: The position code is already taken by another position.\n";
            return false;
        }

        // เตรียมข้อมูล approve fields
        $approve_branch = isset($data['approve_branch']) ? $data['approve_branch'] : 'nonactive';
        $approve_province = isset($data['approve_province']) ? $data['approve_province'] : 'nonactive';
        $approve_airea = isset($data['approve_airea']) ? $data['approve_airea'] : 'nonactive';
        $approve_head_office = isset($data['approve_head_office']) ? $data['approve_head_office'] : 'nonactive';
        $approve_finance = isset($data['approve_finance']) ? $data['approve_finance'] : 'nonactive';
        $approve_payment = isset($data['approve_payment']) ? $data['approve_payment'] : 'nonactive';
        $approve_area = isset($data['approve_area']) ? $data['approve_area'] : null;
        $approve_operating_primary = isset($data['approve_operating_primary']) ? $data['approve_operating_primary'] : null;
        $approve_operating_strategic = isset($data['approve_operating_strategic']) ? $data['approve_operating_strategic'] : null;
        $approve_investment = isset($data['approve_investment']) ? $data['approve_investment'] : null;
        $approve_pacels = isset($data['approve_pacels']) ? $data['approve_pacels'] : null;
        $approve_proposal = isset($data['approve_proposal']) ? $data['approve_proposal'] : null;

        // ถ้าทุกอย่างถูกต้อง ก็ทำการ update ข้อมูล
        $query = "UPDATE users.tb_positions SET 
            position_code = $1, 
            position_name = $2,
            approve_branch = $4,
            approve_province = $5,
            approve_airea = $6,
            approve_head_office = $7,
            approve_finance = $8,
            approve_payment = $9,
            approve_area = $10,
            approve_operating_primary = $11,
            approve_operating_strategic = $12,
            approve_investment = $13,
            approve_pacels = $14,
            approve_proposal = $15
            WHERE id = $3";
        
        $result = pg_query_params($this->conn, $query, array(
            $position_code, $position_name, $id,
            $approve_branch, $approve_province, $approve_airea, $approve_head_office,
            $approve_finance, $approve_payment, $approve_area,
            $approve_operating_primary, $approve_operating_strategic, $approve_investment,
            $approve_pacels, $approve_proposal
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true; // คืนค่า true ถ้าการ update สำเร็จ
    }

    // ฟังก์ชันสำหรับการลบข้อมูลของ position
    public function deletePosition($id)
    {
        // ตรวจสอบว่ามีการใช้งาน position นี้อยู่หรือไม่ (เช่น ใน tb_users หรือ tb_menu_permissions)
        // ถ้ามีการใช้งานอยู่ อาจจะไม่ควรลบ หรือต้องลบแบบ cascade
        
        // ตรวจสอบใน tb_users
        $queryCheckUsers = "SELECT COUNT(*) FROM users.tb_users WHERE position_id = $1";
        $resultUsers = pg_query_params($this->conn, $queryCheckUsers, array($id));
        $rowUsers = pg_fetch_assoc($resultUsers);
        
        if ($rowUsers['count'] > 0) {
            echo "Error: Cannot delete position. It is being used by " . $rowUsers['count'] . " user(s).\n";
            return false;
        }
        
        // ตรวจสอบใน tb_menu_permissions
        $queryCheckPermissions = "SELECT COUNT(*) FROM users.tb_menu_permissions WHERE position_id = $1";
        $resultPermissions = pg_query_params($this->conn, $queryCheckPermissions, array($id));
        $rowPermissions = pg_fetch_assoc($resultPermissions);
        
        if ($rowPermissions['count'] > 0) {
            echo "Error: Cannot delete position. It has " . $rowPermissions['count'] . " menu permission(s).\n";
            return false;
        }
        
        // ถ้าไม่มีการใช้งาน ก็ทำการ delete ข้อมูล
        $query = "DELETE FROM users.tb_positions WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        return true; // คืนค่า true ถ้าการ delete สำเร็จ
    }
}
