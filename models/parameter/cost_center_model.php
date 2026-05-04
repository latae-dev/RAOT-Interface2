<?php

class CostCenterModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readCostCenters() {
        $sql = "SELECT id, cost_center_code, cost_center_name, cost_center_description, status, created_at, updated_at 
                FROM parameters.tb_cost_centers 
                WHERE status = 'active' 
                ORDER BY cost_center_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readCostCenterOne($id) {
        $sql = "SELECT id, cost_center_code, cost_center_name, cost_center_description, status, created_at, updated_at 
                FROM parameters.tb_cost_centers 
                WHERE id = $1 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createCostCenter($data) {
        $sql = "INSERT INTO parameters.tb_cost_centers (cost_center_code, cost_center_name, cost_center_description, status, created_at) 
                VALUES ($1, $2, $3, 'active', NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['cost_center_code'],
            $data['cost_center_name'],
            $data['cost_center_description']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateCostCenter($id, $data) {
        $sql = "UPDATE parameters.tb_cost_centers 
                SET cost_center_code = $1, cost_center_name = $2, cost_center_description = $3, updated_at = NOW() 
                WHERE id = $4 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['cost_center_code'],
            $data['cost_center_name'],
            $data['cost_center_description'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteCostCenter($id) {
        $sql = "UPDATE parameters.tb_cost_centers 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchCostCenters($searchTerm) {
        $sql = "SELECT id, cost_center_code, cost_center_name, cost_center_description, status, created_at, updated_at 
                FROM parameters.tb_cost_centers 
                WHERE status = 'active' 
                AND (cost_center_code ILIKE $1 OR cost_center_name ILIKE $1 OR cost_center_description ILIKE $1) 
                ORDER BY cost_center_code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkCostCenterCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_cost_centers WHERE cost_center_code = $1 AND status = 'active'";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getCostCenterDropdown() {
        $sql = "SELECT id, cost_center_code, cost_center_name 
                FROM parameters.tb_cost_centers 
                WHERE status = 'active' 
                ORDER BY cost_center_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalCostCenters() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_cost_centers WHERE status = 'active'";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}
