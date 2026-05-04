<?php
class WrdMatModel
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

        $query = "SELECT 
        WR.code AS mat_code, 
        WR.name AS mat_name,
        WR.no AS mat_no,
        WR.id AS mat_id,
        WG.no AS group_no, 
        WG.name AS group_name
        FROM parameters.tb_wrd_mats AS WR 
        LEFT JOIN parameters.tb_wrd_groups AS WG ON WG.id=WR.wrd_group_id
        where WR.wrd_group_id = 1";

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
