<?php

class ScopeModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readScopes() {
        $sql = "SELECT id, scope_code, scope_name, scope_description, status, created_at, updated_at 
                FROM parameters.tb_scopes 
                WHERE status = 'active' 
                ORDER BY scope_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readScopeOne($id) {
        $sql = "SELECT id, scope_code, scope_name, scope_description, status, created_at, updated_at 
                FROM parameters.tb_scopes 
                WHERE id = $1 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createScope($data) {
        $sql = "INSERT INTO parameters.tb_scopes (scope_code, scope_name, scope_description, status, created_at) 
                VALUES ($1, $2, $3, 'active', NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['scope_code'],
            $data['scope_name'],
            $data['scope_description']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateScope($id, $data) {
        $sql = "UPDATE parameters.tb_scopes 
                SET scope_code = $1, scope_name = $2, scope_description = $3, updated_at = NOW() 
                WHERE id = $4 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['scope_code'],
            $data['scope_name'],
            $data['scope_description'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteScope($id) {
        $sql = "UPDATE parameters.tb_scopes 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchScopes($searchTerm) {
        $sql = "SELECT id, scope_code, scope_name, scope_description, status, created_at, updated_at 
                FROM parameters.tb_scopes 
                WHERE status = 'active' 
                AND (scope_code ILIKE $1 OR scope_name ILIKE $1 OR scope_description ILIKE $1) 
                ORDER BY scope_code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkScopeCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_scopes WHERE scope_code = $1 AND status = 'active'";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getScopeDropdown() {
        $sql = "SELECT id, scope_code, scope_name 
                FROM parameters.tb_scopes 
                WHERE status = 'active' 
                ORDER BY scope_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalScopes() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_scopes WHERE status = 'active'";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}
