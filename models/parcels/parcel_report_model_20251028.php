<?php
class ParcelReportModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function quarterCheck()
    {
        $month = date('n'); // ดึงค่าของเดือนปัจจุบัน (1-12)
        $quarter = ceil($month / 3); // หาร 3 แล้วปัดขึ้น
        return $quarter;
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

    public function readPlanApproveReport($doc_type, $limit = 10, $offset = 0, $plan_number = null, $start_date = null, $end_date = null, $status_plan = null)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_plans WHERE doc_type = $1 AND status_plan != 'delete'";
        $params = [$doc_type];

        if (!empty($plan_number)) {
            $query_count .= " AND plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query_count .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_plan)) {
            $query_count .= " AND status_plan = $" . (count($params) + 1);
            $params[] = $status_plan;
        }

        $result_count = pg_query_params($this->conn, $query_count, $params);

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ดึงข้อมูลหลักพร้อมแบ่งหน้า
        $query = "SELECT id, plan_number, plan_form_name, status_plan, plan_start, plan_end, created_at, user_requ, user_requ_name, 
        branch_id, province_id, area_id, head_office_id,
        user_branch,date_branch,status_branch,
        user_province,date_province,status_province,
        user_area,date_area,status_area,
        user_head_office,date_head_office,status_head_office,
        status_merge, plan_requ_all, branch_type, merge_provice
        FROM parcels.tb_plans WHERE doc_type = $1 AND status_plan != 'delete'";
        $params = [$doc_type];

        if (!empty($plan_number)) {
            $query .= " AND plan_number = $" . (count($params) + 1);
            $params[] = $plan_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_plan)) {
            $query .= " AND status_plan = $" . (count($params) + 1);
            $params[] = $status_plan;
        }
        if (!empty($plan_number) && !empty($status_plan) && empty($start_date) && empty($end_date)) {
            $query .= " AND plan_number = $" . (count($params) + 1) . " AND status_plan = $" . (count($params) + 2);
            $params[] = $plan_number;
            $params[] = $status_plan;
        }

        $query .= " ORDER BY created_at DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if ($result) {
            $rows = pg_fetch_all($result) ?: [];
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "quarter" => $this->quarterCheck(),
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }

    public function readWithdrawalApproveReport($doc_type, $limit = 10, $offset = 0, $doc_number = null, $start_date = null, $end_date = null, $status_doc = null)
    {
        // นับจำนวนทั้งหมดของข้อมูล
        $query_count = "SELECT COUNT(*) AS total FROM parcels.tb_docs WHERE doc_type = $1 AND status_doc != 'delete'";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query_count .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query_count .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query_count .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
        }

        $result_count = pg_query_params($this->conn, $query_count, $params);

        if ($result_count) {
            $count_row = pg_fetch_assoc($result_count);
            $total_count = $count_row ? (int)$count_row['total'] : 0;
        } else {
            return ["status" => "error", "message" => "Count query failed"];
        }

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ($limit > 0) ? ceil($total_count / $limit) : 1;

        // ดึงข้อมูลหลักพร้อมแบ่งหน้า
        $query = "SELECT D.id, D.doc_number, D.doc_form_name, D.status_doc, D.doc_start, D.doc_end, D.created_at, D.user_requ_name, DM.quarter,
        D.user_requ, D.branch_id, D.province_id, D.area_id, D.head_office_id,
        D.user_branch,D.date_branch,D.status_branch,
        D.user_province,D.date_province,D.status_province,
        D.user_area,D.date_area,D.status_area,
        D.user_head_office,D.date_head_office,D.status_head_office
        FROM parcels.tb_docs AS D
        LEFT JOIN (
            SELECT DISTINCT ON (doc_id) doc_id, quarter
            FROM parcels.tb_doc_mats
            ORDER BY doc_id, id ASC
        ) AS DM ON DM.doc_id = D.id
        WHERE doc_type = $1 AND status_doc != 'delete'";
        $params = [$doc_type];

        if (!empty($doc_number)) {
            $query .= " AND doc_number = $" . (count($params) + 1);
            $params[] = $doc_number;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN $" . (count($params) + 1) . " AND $" . (count($params) + 2);
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($status_doc)) {
            $query .= " AND status_doc = $" . (count($params) + 1);
            $params[] = $status_doc;
        }
        if (!empty($doc_number) && !empty($status_doc) && empty($start_date) && empty($end_date)) {
            $query .= " AND doc_number = $" . (count($params) + 1) . " AND status_doc = $" . (count($params) + 2);
            $params[] = $doc_number;
            $params[] = $status_doc;
        }

        $query .= " ORDER BY created_at DESC LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;

        $result = pg_query_params($this->conn, $query, $params);

        if ($result) {
            $rows = pg_fetch_all($result) ?: [];
            return [
                "status" => "success",
                "pagination" => [
                    "total_records" => $total_count,
                    "total_pages" => $total_pages,
                    "current_page" => ($offset / $limit) + 1,
                    "limit_per_page" => $limit,
                ],
                "data" => $rows
            ];
        } else {
            return ["status" => "error", "message" => "Data query failed"];
        }
    }
}
