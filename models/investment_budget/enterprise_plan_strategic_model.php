<?php

class EnterprisePlanStrategicModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readEnterprisePlanStrategic()
    {
        $query = "SELECT * FROM parameters.tb_enterprise_plan_strategic ORDER BY code";
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

    public function readEnterprisePlanStrategicOne($id)
    {
        $query = "SELECT * FROM parameters.tb_enterprise_plan_strategic WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readEnterprisePlanStrategicByCode($code)
    {
        $query = "SELECT * FROM parameters.tb_enterprise_plan_strategic WHERE code = $1";
        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
