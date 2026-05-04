<?php

class EquipmentTypeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readEquipmentType()
    {
        $query = "SELECT et.*, ac.name as asset_category_name 
                  FROM parameters.tb_equipment_type et
                  LEFT JOIN parameters.tb_asset_category ac ON SUBSTRING(et.code, 1, 3) = ac.code
                  ORDER BY et.code";
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

    public function readEquipmentTypeOne($id)
    {
        $query = "SELECT et.*, ac.name as asset_category_name 
                  FROM parameters.tb_equipment_type et
                  LEFT JOIN parameters.tb_asset_category ac ON SUBSTRING(et.code, 1, 3) = ac.code
                  WHERE et.id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readEquipmentTypeByCode($code)
    {
        $query = "SELECT et.*, ac.name as asset_category_name 
                  FROM parameters.tb_equipment_type et
                  LEFT JOIN parameters.tb_asset_category ac ON SUBSTRING(et.code, 1, 3) = ac.code
                  WHERE et.code = $1";
        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readEquipmentTypeByAssetCategory($assetCategoryCode)
    {
        $query = "SELECT et.*, ac.name as asset_category_name 
                  FROM parameters.tb_equipment_type et
                  LEFT JOIN parameters.tb_asset_category ac ON SUBSTRING(et.code, 1, 3) = ac.code
                  WHERE SUBSTRING(et.code, 1, 3) = $1
                  ORDER BY et.code";
        $result = pg_query_params($this->conn, $query, array($assetCategoryCode));

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
