<?php

class BusinessBudgetReportModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getBusinessReportList($param = [])
    {
        try {
            $user = $_SESSION['user_data'];
            $params = [];
            $conditions = [];
            $joins = [];

            $positionHierarchy = ['EMPY' => 1, 'APBR' => 2, 'APPV' => 3, 'APPRP' => 4, ''];
            $userPositions = isset($user['position_list']) ? array_column($user['position_list'], 'position_code') : [];
            $highestPosition = null;
            $highestLevel = 0;

            foreach ($userPositions as $pos) {
                if (isset($positionHierarchy[$pos]) && $positionHierarchy[$pos] > $highestLevel) {
                    $highestLevel = $positionHierarchy[$pos];
                    $highestPosition = $pos;
                }
            }

            if(!empty($param['export_type']) && $param['export_type'] == 1) {
                $joins[] = "join ".PARAMETERS_TB_RISK_NATIONAL_STRATEGIES." trn on trn.code = tbb.national_strategy";
                $joins[] = "left join ".PARAMETERS_TB_BUDGET_SOURCES." tbs on tbs.id = tbb.budget_source_code ";
            }

            if(!empty($param['export_type']) && $param['export_type'] == 2) {
                $joins[] = "join ".PARAMETERS_TB_BUDGET_SOURCES." tbs on tbs.id = tbb.budget_source_code ";
            }

            $joins[] = "JOIN users.tb_departs d ON d.id = tbb.depart_id";
            $joins[] = "JOIN users.tb_users u ON u.id = tbb.created_by";
            $joins[] = "JOIN users.tb_branchs b ON b.id = u.branch_id";
            $joins[] = "JOIN users.tb_provinces p ON p.id = b.province_id";
            $joins[] = "JOIN users.tb_areas a ON a.id = p.area_id";

            if ($highestPosition === 'APBR') {
                $conditions[] = "(b.id = ".$user['branch_id']." and tbb.version_name = 1) ";

            } elseif ($highestPosition === 'APPV') {
                $conditions[] = "(p.id = (
                    SELECT p2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    WHERE u2.id = '{$user['id']}'
                )  and tbb.version_name in (1, 2) )";

            } elseif ($highestPosition === 'APAR') {
                $conditions[] = "(a.id = (
                    SELECT a2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    JOIN users.tb_areas a2 ON a2.id = p2.area_id
                    WHERE u2.id = '{$user['id']}'
                ) and tbb.version_name in (1, 2, 3) )";
            } else if($highestPosition === 'APPRP') {
                $conditions[] = "(tbb.responsibilities_approves = '".$user['depart_code']."') ";
            }

            if (!empty($param['document_number'])) {
                $conditions[] = "tbb.document_number ILIKE $" . (count($params) + 1);
                $params[] = '%' . trim($param['document_number']) . '%';
            }

            if (!empty($param['start_date'])) {
                $conditions[] = "tbb.created_at >= $" . (count($params) + 1);
                $params[] = trim($param['start_date']);
            }

            if (!empty($param['end_date'])) {
                $conditions[] = "tbb.created_at <= $" . (count($params) + 1);
                $params[] = trim($param['end_date']) . ' 23:59:59';
            }

            if (isset($param['status']) && (trim($param['status']) == 'approved' || $param['status'] == 'rejected' || $param['status'] == 'waiting') ) {
                $conditions[] = "tbb.status = $" . (count($params) + 1);
                $params[] = trim($param['status']);
            } else if (isset($param['status']) && $param['status'] == 'deleted') {
                $conditions[] = "(tbb.deleted_at is not null and tbb.status != 'draft')";
            } else {
                $conditions[] = "tbb.status != 'draft'";
            }

            $whereClause = "WHERE " . implode(" AND ", $conditions);

            $query = "
                SELECT DISTINCT 
                    tbb.*, 
                    d.depart_name,
                    (SELECT project_name FROM " . PARAMETERS_TB_PROJECTS . " WHERE project_code = tbb.project_code) AS old_project_name,
                    (SELECT CONCAT(user_fname, ' ', user_lname) FROM " . USERS_TB_USERS . " WHERE id = tbb.created_by) AS request_name
                FROM " . BUDGETS_TB_BUSINESS_BUDGETS . " tbb
                " . implode("\n", $joins) . "
                $whereClause
                ORDER BY tbb.id DESC
            ";

            $result = pg_query_params($this->conn, $query, $params);
            return $result ? pg_fetch_all($result) : [];

        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }


    public function exportList1($param = []) 
    {
        try {
            $user = $_SESSION['user_data'];
            $params = [];
            $conditions = [];
            $joins = [];

            $positionHierarchy = ['EMPY' => 1, 'APBR' => 2, 'APPV' => 3, 'APPRP' => 4, ''];
            $userPositions = isset($user['position_list']) ? array_column($user['position_list'], 'position_code') : [];
            $highestPosition = null;
            $highestLevel = 0;

            foreach ($userPositions as $pos) {
                if (isset($positionHierarchy[$pos]) && $positionHierarchy[$pos] > $highestLevel) {
                    $highestLevel = $positionHierarchy[$pos];
                    $highestPosition = $pos;
                }
            }

            if ($highestPosition === 'APBR') {
                $conditions[] = "(b.id = ".$user['branch_id']." and tbb.version_name = 1) ";

            } elseif ($highestPosition === 'APPV') {
                $conditions[] = "(p.id = (
                    SELECT p2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    WHERE u2.id = '{$user['id']}'
                )  and tbb.version_name in (1, 2) )";

            } elseif ($highestPosition === 'APAR') {
                $conditions[] = "(a.id = (
                    SELECT a2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    JOIN users.tb_areas a2 ON a2.id = p2.area_id
                    WHERE u2.id = '{$user['id']}'
                ) and tbb.version_name in (1, 2, 3) )";
            } else if($highestPosition === 'APPRP') {
                $conditions[] = "(tbb.responsibilities_approves = '".$user['depart_code']."') ";
            }

            if (!empty($param['document_number'])) {
                $conditions[] = "tbb.document_number ILIKE $" . (count($params) + 1);
                $params[] = '%' . trim($param['document_number']) . '%';
            }

            if (!empty($param['start_date'])) {
                $conditions[] = "tbb.created_at >= $" . (count($params) + 1);
                $params[] = trim($param['start_date']);
            }

            if (!empty($param['end_date'])) {
                $conditions[] = "tbb.created_at <= $" . (count($params) + 1);
                $params[] = trim($param['end_date']) . ' 23:59:59';
            }

            if (isset($param['status']) && (trim($param['status']) == 'approved' || $param['status'] == 'rejected' || $param['status'] == 'waiting') ) {
                $conditions[] = "tbb.status = $" . (count($params) + 1);
                $params[] = trim($param['status']);
            } else if (isset($param['status']) && $param['status'] == 'deleted') {
                $conditions[] = "(tbb.deleted_at is not null and tbb.status != 'draft')";
            } else {
                $conditions[] = "tbb.status != 'draft'";
            }

            $whereClause = "WHERE " . implode(" AND ", $conditions);

            $query = "
                select 
                    tbb.id,
                    project_code,
                    tbs.full_name,
                    (case when project_type_name = 1 
                    then (select project_name from ".PARAMETERS_TB_PROJECTS." where project_code = tbb.project_code)
                    else project_name
                    end) as project_name,
                    tbb.budget_source_code,
                    td.depart_code,
                    trn.code as st_code,
                    trn.name as st_name,
                    (select sum(jan) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jan,
                    (select sum(feb) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as feb,
                    (select sum(mar) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as mar,
                    (select sum(apr) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as apr,
                    (select sum(may) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as may,
                    (select sum(jun) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jun,
                    (select sum(jul) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jul,
                    (select sum(aug) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as aug,
                    (select sum(sep) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as sep,
                    (select sum(oct) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as oct,
                    (select sum(nov) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as nov,
                    (select sum(dec) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as dec,
                    (select sum(total_amount) FROM ".BUDGETS_TB_ACTIVITY_BUDGETS." where business_budget_id = tbb.id) as total_activity_amount,
                    (select (sum(allowance_amount) + sum(transport_amount) + sum(stay_amount) + sum(fuel_amount))  FROM ".BUDGETS_TB_EXPENSES_BUDGETS." where business_budget_id = tbb.id) as total_expenses_amount
                from ".BUDGETS_TB_BUSINESS_BUDGETS." tbb 
                join ".PARAMETERS_TB_RISK_NATIONAL_STRATEGIES." trn on trn.code = tbb.national_strategy
                left join ".PARAMETERS_TB_BUDGET_SOURCES." tbs on tbs.id = tbb.budget_source_code 
                left join users.tb_users tu on tu.id = tbb.created_by 
                left join users.tb_departs td on td.id = tu.depart_id 
                join users.tb_branchs b ON b.id = tu.branch_id
                join users.tb_provinces p ON p.id = b.province_id
                join users.tb_areas a ON a.id = p.area_id";

            $query .= "
                $whereClause
                ORDER BY tbb.id DESC";

            $result = pg_query_params($this->conn, $query, $params);

            if ($result) {
                $rows = pg_fetch_all($result);

                foreach($rows as $key => $rs) {
                    $rows[$key]['total_amount'] = $rs['total_activity_amount'] + $rs['total_expenses_amount'];
                    $rows[$key]['output_indicators'] = $this->getOutputIndicators($rs['id']);
                    $rows[$key]['outcome_indicators'] = $this->getOutcomeIndicators($rs['id']);
                }

                return $rows;
            } else {
                return [];
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function exportList2($param = []) 
    {
        try {
            $user = $_SESSION['user_data'];
            $params = [];
            $conditions = [];
            $joins = [];

            $positionHierarchy = ['EMPY' => 1, 'APBR' => 2, 'APPV' => 3, 'APPRP' => 4, ''];
            $userPositions = isset($user['position_list']) ? array_column($user['position_list'], 'position_code') : [];
            $highestPosition = null;
            $highestLevel = 0;

            foreach ($userPositions as $pos) {
                if (isset($positionHierarchy[$pos]) && $positionHierarchy[$pos] > $highestLevel) {
                    $highestLevel = $positionHierarchy[$pos];
                    $highestPosition = $pos;
                }
            }

            if ($highestPosition === 'APBR') {
                $conditions[] = "(b.id = ".$user['branch_id']." and tbb.version_name = 1) ";

            } elseif ($highestPosition === 'APPV') {
                $conditions[] = "(p.id = (
                    SELECT p2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    WHERE u2.id = '{$user['id']}'
                )  and tbb.version_name in (1, 2) )";

            } elseif ($highestPosition === 'APAR') {
                $conditions[] = "(a.id = (
                    SELECT a2.id
                    FROM users.tb_users u2
                    JOIN users.tb_branchs b2 ON b2.id = u2.branch_id
                    JOIN users.tb_provinces p2 ON p2.id = b2.province_id
                    JOIN users.tb_areas a2 ON a2.id = p2.area_id
                    WHERE u2.id = '{$user['id']}'
                ) and tbb.version_name in (1, 2, 3) )";
            } else if($highestPosition === 'APPRP') {
                $conditions[] = "(tbb.responsibilities_approves = '".$user['depart_code']."') ";
            }

            if (!empty($param['document_number'])) {
                $conditions[] = "tbb.document_number ILIKE $" . (count($params) + 1);
                $params[] = '%' . trim($param['document_number']) . '%';
            }

            if (!empty($param['start_date'])) {
                $conditions[] = "tbb.created_at >= $" . (count($params) + 1);
                $params[] = trim($param['start_date']);
            }

            if (!empty($param['end_date'])) {
                $conditions[] = "tbb.created_at <= $" . (count($params) + 1);
                $params[] = trim($param['end_date']) . ' 23:59:59';
            }

            if (isset($param['status']) && (trim($param['status']) == 'approved' || $param['status'] == 'rejected' || $param['status'] == 'waiting') ) {
                $conditions[] = "tbb.status = $" . (count($params) + 1);
                $params[] = trim($param['status']);
            } else if (isset($param['status']) && $param['status'] == 'deleted') {
                $conditions[] = "(tbb.deleted_at is null and tbb.status != 'draft')";
            } else {
                $conditions[] = "tbb.status != 'draft'";
            }

            $whereClause = "WHERE " . implode(" AND ", $conditions);

            $query = "
                select 
                    tbb.id,
                    project_code,
                    tbs.full_name,
                    (case when project_type_name = 1 
                    then (select project_name from ".PARAMETERS_TB_PROJECTS." where project_code = tbb.project_code)
                    else project_name
                    end) as project_name,
                    tbb.budget_source_code,
                    td.depart_code,
                    (select sum(jan) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jan,
                    (select sum(feb) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as feb,
                    (select sum(mar) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as mar,
                    (select sum(apr) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as apr,
                    (select sum(may) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as may,
                    (select sum(jun) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jun,
                    (select sum(jul) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as jul,
                    (select sum(aug) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as aug,
                    (select sum(sep) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as sep,
                    (select sum(oct) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as oct,
                    (select sum(nov) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as nov,
                    (select sum(dec) from ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = tbb.id) as dec,
                    (select sum(total_amount) FROM ".BUDGETS_TB_ACTIVITY_BUDGETS." where business_budget_id = tbb.id) as total_activity_amount,
                    (select (sum(allowance_amount) + sum(transport_amount) + sum(stay_amount) + sum(fuel_amount))  FROM ".BUDGETS_TB_EXPENSES_BUDGETS." where business_budget_id = tbb.id) as total_expenses_amount
                from ".BUDGETS_TB_BUSINESS_BUDGETS." tbb 
                join ".PARAMETERS_TB_BUDGET_SOURCES." tbs on tbs.id = tbb.budget_source_code 
                left join users.tb_users tu on tu.id = tbb.created_by 
                left join users.tb_departs td on td.id = tu.depart_id
                join users.tb_branchs b ON b.id = tu.branch_id
                join users.tb_provinces p ON p.id = b.province_id
                join users.tb_areas a ON a.id = p.area_id";

            $query .= "
                $whereClause
                ORDER BY tbb.id DESC";

            $result = pg_query_params($this->conn, $query, $params);

            if ($result) {
                $rows = pg_fetch_all($result);

                foreach($rows as $key => $rs) {
                    $rows[$key]['total_amount'] = $rs['total_activity_amount'] + $rs['total_expenses_amount'];
                    $rows[$key]['output_indicators'] = $this->getOutputIndicators($rs['id']);
                    $rows[$key]['outcome_indicators'] = $this->getOutcomeIndicators($rs['id']);
                }

                return $rows;
            } else {
                return [];
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getOutputIndicators($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OUTPUT_INDICATORS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getOutcomeIndicators($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OUTCOME_INDICATORS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }


}