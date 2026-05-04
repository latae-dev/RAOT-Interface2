<?php

class EquipmentStandardsModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readEquipmentStandards()
    {
        $query = "SELECT * FROM parameters.tb_equipment_standards ORDER BY id";
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

    public function readEquipmentStandardsOne($id)
    {
        $query = "SELECT * FROM parameters.tb_equipment_standards WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
