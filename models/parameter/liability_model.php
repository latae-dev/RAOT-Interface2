<?php

class LiabilityModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readLiabilities() {
        $sql = "SELECT id, liability_code, liability_name, liability_description, status, created_at, updated_at 
                FROM parameters.tb_liabilities_clone 
                WHERE status = 'active' 
                ORDER BY liability_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readLiabilityOne($id) {
        $sql = "SELECT id, liability_code, liability_name, liability_description, status, created_at, updated_at 
                FROM parameters.tb_liabilities_clone 
                WHERE id = $1 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createLiability($data) {
        $sql = "INSERT INTO parameters.tb_liabilities_clone (liability_code, liability_name, liability_description, status, created_at) 
                VALUES ($1, $2, $3, 'active', NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['liability_code'],
            $data['liability_name'],
            $data['liability_description']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateLiability($id, $data) {
        $sql = "UPDATE parameters.tb_liabilities_clone 
                SET liability_code = $1, liability_name = $2, liability_description = $3, updated_at = NOW() 
                WHERE id = $4 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['liability_code'],
            $data['liability_name'],
            $data['liability_description'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteLiability($id) {
        $sql = "UPDATE parameters.tb_liabilities_clone 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchLiabilities($searchTerm) {
        $sql = "SELECT id, liability_code, liability_name, liability_description, status, created_at, updated_at 
                FROM parameters.tb_liabilities_clone 
                WHERE status = 'active' 
                AND (liability_code ILIKE $1 OR liability_name ILIKE $1 OR liability_description ILIKE $1) 
                ORDER BY liability_code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkLiabilityCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_liabilities_clone WHERE liability_code = $1 AND status = 'active'";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getLiabilityDropdown() {
        $sql = "SELECT id, liability_code, liability_name 
                FROM parameters.tb_liabilities_clone 
                WHERE status = 'active' 
                ORDER BY liability_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalLiabilities() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_liabilities_clone WHERE status = 'active'";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}
