<?php
class WrdFundModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_wrd_fund
    public function readAll()
    {
        $query = "SELECT 
                    f.id,
                    f.fund_code,
                    f.fund_name,
                    f.is_active,
                    CASE WHEN COUNT(p.id) > 0 THEN 1 ELSE 0 END AS has_project
                FROM parameters.tb_wrd_fund f
                LEFT JOIN parameters.tb_wrd_project_lists p ON p.fund_id = f.id
                WHERE f.is_active = '1'
                GROUP BY 
                    f.id,
                    f.fund_code,
                    f.fund_name,
                    f.is_active
                ORDER BY f.id ASC";
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

    public function readById($id)
    {
        $query = "SELECT * FROM parameters.tb_wrd_fund WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    
}
