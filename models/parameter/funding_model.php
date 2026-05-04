<?php

class FundingModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readFundings() {
        $sql = "SELECT id, funding_code, funding_name, funding_description, status, created_at, updated_at 
                FROM parameters.tb_fundings 
                WHERE status = 'active' 
                ORDER BY funding_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readFundingOne($id) {
        $sql = "SELECT id, funding_code, funding_name, funding_description, status, created_at, updated_at 
                FROM parameters.tb_fundings 
                WHERE id = $1 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createFunding($data) {
        $sql = "INSERT INTO parameters.tb_fundings (funding_code, funding_name, funding_description, status, created_at) 
                VALUES ($1, $2, $3, 'active', NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['funding_code'],
            $data['funding_name'],
            $data['funding_description']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateFunding($id, $data) {
        $sql = "UPDATE parameters.tb_fundings 
                SET funding_code = $1, funding_name = $2, funding_description = $3, updated_at = NOW() 
                WHERE id = $4 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['funding_code'],
            $data['funding_name'],
            $data['funding_description'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteFunding($id) {
        $sql = "UPDATE parameters.tb_fundings 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchFundings($searchTerm) {
        $sql = "SELECT id, funding_code, funding_name, funding_description, status, created_at, updated_at 
                FROM parameters.tb_fundings 
                WHERE status = 'active' 
                AND (funding_code ILIKE $1 OR funding_name ILIKE $1 OR funding_description ILIKE $1) 
                ORDER BY funding_code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkFundingCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_fundings WHERE funding_code = $1 AND status = 'active'";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getFundingDropdown() {
        $sql = "SELECT id, funding_code, funding_name 
                FROM parameters.tb_fundings 
                WHERE status = 'active' 
                ORDER BY funding_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalFundings() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_fundings WHERE status = 'active'";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}
