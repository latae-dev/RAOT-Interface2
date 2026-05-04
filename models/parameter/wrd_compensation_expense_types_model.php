<?php
class WrdCompensationExpenseTypesModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_wrd_fund
    public function readAll()
    {
        $query = "SELECT *
                FROM parameters.tb_wrd_compensation_expense_types cet
                WHERE cet.is_active = '1'
                ORDER BY cet.id ASC";
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

    public function getFirst()
    {
        $query = "SELECT *
                FROM parameters.tb_wrd_compensation_expense_types cet
                WHERE cet.is_active = '1' and cet.parent_id IS NOT NULL
                ORDER BY cet.id ASC";
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

    public function getParent($id = null)
    {
        if ($id != 'null' && $id != null) {
            $query = "SELECT *
                FROM parameters.tb_wrd_compensation_expense_types cet
                WHERE cet.is_active = '1' and cet.parent_id = $1
                ORDER BY cet.id ASC";
            $result = pg_query_params($this->conn, $query, array($id));

            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        } else {
            return [];
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }
}
