<?php

class AssetCategoryModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAssetCategory()
    {
        $query = "SELECT * FROM parameters.tb_asset_category ORDER BY code";
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

    public function readAssetCategoryOne($id)
    {
        $query = "SELECT * FROM parameters.tb_asset_category WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readAssetCategoryByCode($code)
    {
        $query = "SELECT * FROM parameters.tb_asset_category WHERE code = $1";
        $result = pg_query_params($this->conn, $query, array($code));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    /**
     * Get asset categories filtered by asset type and installment work
     * @param int $assetTypeId - ID of asset type
     * @param int $installmentWorkId - 0=ไม่ระบุ/no filter, 1=ไม่เป็นงวด, 2=เป็นงวด
     * @return array|false - Array of asset categories or false on error
     */
    public function readAssetCategoryByFilters($assetTypeId, $installmentWorkId)
    {
        $query = "
            SELECT DISTINCT ac.*
            FROM parameters.tb_asset_category ac
            INNER JOIN parameters.tb_asset_type_category atc
                ON ac.id = atc.asset_category_id
            WHERE atc.asset_type_id = $1
                AND atc.installment_work_id = $2
            ORDER BY ac.code
        ";

        $result = pg_query_params($this->conn, $query, array($assetTypeId, $installmentWorkId));

        if (!$result) {
            error_log("Error in readAssetCategoryByFilters: " . pg_last_error($this->conn));
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $categories = [];
        while ($row = pg_fetch_assoc($result)) {
            $categories[] = $row;
        }

        return $categories;
    }
}
