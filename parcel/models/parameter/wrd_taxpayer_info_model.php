<?php
class WrdTaxpayerInfoModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll()
    {
        /* $query = "
            SELECT 
                WM.*, 
                WG.code AS wg_code, 
                WG.name AS wg_name, 
                WG.wg_no 
            FROM parameters.tb_wrd_mats AS WM
            LEFT JOIN parameters.tb_wrd_groups AS WG 
                ON WG.id = WM.wrd_group_id
        "; */

        $query = "SELECT * FROM parameters.tb_taxpayer_info order by id asc";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("❌ Query error: " . pg_last_error($this->conn));
            return []; // ป้องกันการ return false
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }
}
