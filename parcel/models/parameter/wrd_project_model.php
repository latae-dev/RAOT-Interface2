<?php
class WrdProjectModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_wrd_project
    public function readAll($fund_id = null)
    {
        if ($fund_id != 'null' && $fund_id != null) {
            $query_project_list = "SELECT * FROM parameters.tb_wrd_project_lists where fund_id=$fund_id order by id asc";
            $result_project_list = pg_query($this->conn, $query_project_list);
            if (!$result_project_list) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
            $data_projectlist = [];
            while ($row_project_list = pg_fetch_assoc($result_project_list)) {
                $data_projectlist[] = $row_project_list;
            }
            if (!empty($data_projectlist)) {
                $item = [];
                foreach ($data_projectlist as $record) {
                    $item[] = $record['project_id'];
                }
                $query = "SELECT * FROM parameters.tb_wrd_project where is_active = 1 and id in (" . implode(',', $item) . ") order by id asc";
                $result = pg_query($this->conn, $query);

                if (!$result) {
                    echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                    return false;
                }

                $datas = [];
                while ($row = pg_fetch_assoc($result)) {
                    $datas[] = $row;
                }
                foreach ($datas as &$rec) {
                    $rec['project_name'] = $rec['project_code'] . ' ' . $rec['project_name'];
                }
            } else {
                return [];
            }
        } else {
            $datas = [];
        }

        return $datas;
    }

    public function readById($id)
    {
        $query = "SELECT * FROM parameters.tb_wrd_project WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
}
