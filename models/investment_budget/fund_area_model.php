<?php

class FundAreaModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readFundArea()
    {
        $query = "SELECT id, code, name, is_active, created_at, updated_at
                  FROM parameters.tb_ib_fund_area
                  WHERE is_active = TRUE
                  ORDER BY code";
        $result = pg_query($this->conn, $query);
        if (!$result) {
            return [];
        }

        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function readFundAreaOne($id)
    {
        $query = "SELECT id, code, name, is_active, created_at, updated_at
                  FROM parameters.tb_ib_fund_area
                  WHERE id = $1";
        $result = pg_query_params($this->conn, $query, [$id]);
        if (!$result) {
            return null;
        }
        return pg_fetch_assoc($result) ?: null;
    }
}
