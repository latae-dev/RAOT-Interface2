<?php
class ParcelReportModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readReportPlan()
    {
        $query = "SELECT * FROM parcels.tb_plan_mats AS M LEFT JOIN parcels.tb_plans AS P ON P.id = M.plan_id WHERE P.status_head_office = $1";
        $result = pg_query_params($this->conn, $query, array('approve'));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function readReportDoc()
    {
        $query = "SELECT * FROM parcels.tb_doc_mats AS M LEFT JOIN parcels.tb_docs AS D ON D.id = M.doc_id WHERE D.status_head_office = $1";
        $result = pg_query_params($this->conn, $query, array('approve'));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

}
