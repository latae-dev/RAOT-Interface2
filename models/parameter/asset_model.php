<?php

class AssetModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readAssets() {
        $sql = "SELECT id, no, asset_code, asset_name, asset_category_id, created_at, updated_at 
                FROM parameters.tb_asset 
                ORDER BY no ASC, asset_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readAssetOne($id) {
        $sql = "SELECT id, no, asset_code, asset_name, asset_category_id, created_at, updated_at 
                FROM parameters.tb_asset 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createAsset($data) {
        $sql = "INSERT INTO parameters.tb_asset (no, asset_code, asset_name, created_at) 
                VALUES ($1, $2, $3, NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['no'] ?? 1,
            $data['asset_code'],
            $data['asset_name']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateAsset($id, $data) {
        $sql = "UPDATE parameters.tb_asset 
                SET no = $1, asset_code = $2, asset_name = $3, updated_at = NOW() 
                WHERE id = $4";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['no'] ?? 1,
            $data['asset_code'],
            $data['asset_name'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteAsset($id) {
        $sql = "DELETE FROM parameters.tb_asset WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchAssets($searchTerm) {
        $sql = "SELECT id, no, asset_code, asset_name, asset_category_id, created_at, updated_at 
                FROM parameters.tb_asset 
                WHERE (asset_code ILIKE $1 OR asset_name ILIKE $1) 
                ORDER BY no ASC, asset_code ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkAssetCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_asset WHERE asset_code = $1";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getAssetDropdown() {
        $sql = "SELECT id, asset_code, asset_name, asset_category_id 
                FROM parameters.tb_asset 
                ORDER BY no ASC, asset_code ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalAssets() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_asset";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}

