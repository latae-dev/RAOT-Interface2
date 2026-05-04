<?php

class EquipmentCodeTypeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readEquipmentCodeType()
    {
        $query = "SELECT * FROM parameters.tb_equipment_code_type ORDER BY code";
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

    public function readEquipmentCodeTypeOne($id)
    {
        $query = "SELECT * FROM parameters.tb_equipment_code_type WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readEquipmentCodeTypeByCode($code)
    {
        $query = "SELECT * FROM parameters.tb_equipment_code_type WHERE code = $1";
        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readEquipmentCodeTypeByPrefix($prefix)
    {
        $query = "SELECT * FROM parameters.tb_equipment_code_type WHERE code LIKE $1 ORDER BY code";
        $result = pg_query_params($this->conn, $query, array($prefix . '%'));

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
