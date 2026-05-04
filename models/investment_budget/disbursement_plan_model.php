<?php

class DisbursementPlanModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readDisbursementPlan()
    {
        $query = "SELECT * FROM parameters.tb_disbursement_plan ORDER BY id";
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

    public function readDisbursementPlanOne($id)
    {
        $query = "SELECT * FROM parameters.tb_disbursement_plan WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
