<?php

class ImplementationYearModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readImplementationYear()
    {
        $query = "SELECT * FROM parameters.tb_implementation_year ORDER BY year DESC";
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

    public function readImplementationYearOne($id)
    {
        $query = "SELECT * FROM parameters.tb_implementation_year WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readImplementationYearByYear($year)
    {
        $query = "SELECT * FROM parameters.tb_implementation_year WHERE year = $1";
        $result = pg_query_params($this->conn, $query, array($year));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
