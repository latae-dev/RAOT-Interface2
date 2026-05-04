<?php

class InstallmentWorkModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readInstallmentWork()
    {
        $query = "SELECT * FROM parameters.tb_installment_work ORDER BY code";
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

    public function readInstallmentWorkOne($id)
    {
        $query = "SELECT * FROM parameters.tb_installment_work WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readInstallmentWorkByCode($code)
    {
        $query = "SELECT * FROM parameters.tb_installment_work WHERE code = $1";
        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
