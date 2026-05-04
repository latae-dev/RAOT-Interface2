<?php

class BudgetSourceModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readBudgetSource()
    {
        $query = "SELECT id, CONCAT(code, ' - ', name) AS name FROM parameters.tb_ib_budget_source WHERE type_budget_source = 'GR2' ORDER BY code";
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

    public function readBudgetSourceOne($id)
    {
        $query = "SELECT * FROM parameters.tb_ib_budget_source WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readBudgetSourceByType($type)
    {
        $query = "SELECT * FROM parameters.tb_ib_budget_source WHERE type_budget_source = $1 ORDER BY code";
        $result = pg_query_params($this->conn, $query, array($type));

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
}
