<?php

class ProjectPeriodModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readProjectPeriod()
    {
        $query = "SELECT * FROM parameters.tb_project_period ORDER BY name ASC";
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

    public function readProjectPeriodOne($id)
    {
        $query = "SELECT * FROM parameters.tb_project_period WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readProjectPeriodByName($name)
    {
        $query = "SELECT * FROM parameters.tb_project_period WHERE name = $1";
        $result = pg_query_params($this->conn, $query, array($name));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
