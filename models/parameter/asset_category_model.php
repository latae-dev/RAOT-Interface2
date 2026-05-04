<?php

class AssetCategoryModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readAssetCategories() {
        $sql = "SELECT id, code, name, created_at, updated_at 
                FROM parameters.tb_asset_category_pr 
                ORDER BY code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readAssetCategoryOne($id) {
        $sql = "SELECT id, code, name, created_at, updated_at 
                FROM parameters.tb_asset_category_pr 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createAssetCategory($data) {
        $sql = "INSERT INTO parameters.tb_asset_category_pr (code, name, created_at) 
                VALUES ($1, $2, NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['code'],
            $data['name']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateAssetCategory($id, $data) {
        $sql = "UPDATE parameters.tb_asset_category_pr 
                SET code = $1, name = $2, updated_at = NOW() 
                WHERE id = $3";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['code'],
            $data['name'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteAssetCategory($id) {
        $sql = "DELETE FROM parameters.tb_asset_category_pr WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchAssetCategories($searchTerm) {
        $sql = "SELECT id, code, name, created_at, updated_at 
                FROM parameters.tb_asset_category_pr 
                WHERE (code ILIKE $1 OR name ILIKE $1) 
                ORDER BY code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkAssetCategoryCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_asset_category_pr WHERE code = $1";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getAssetCategoryDropdown() {
        $sql = "SELECT id, code, name 
                FROM parameters.tb_asset_category_pr 
                ORDER BY code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalAssetCategories() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_asset_category_pr";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}


