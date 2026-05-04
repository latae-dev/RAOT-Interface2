<?php

class TaxModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function readTaxes() {
        $sql = "SELECT id, tax_code, tax_name, tax_rate, tax_description, status, created_at, updated_at 
                FROM parameters.tb_taxes 
                WHERE status = 'active' 
                ORDER BY tax_rate ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function readTaxOne($id) {
        $sql = "SELECT id, tax_code, tax_name, tax_rate, tax_description, status, created_at, updated_at 
                FROM parameters.tb_taxes 
                WHERE id = $1 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array($id));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_assoc($result);
    }

    public function createTax($data) {
        $sql = "INSERT INTO parameters.tb_taxes (tax_code, tax_name, tax_rate, tax_description, status, created_at) 
                VALUES ($1, $2, $3, $4, 'active', NOW()) 
                RETURNING id";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['tax_code'],
            $data['tax_name'],
            $data['tax_rate'],
            $data['tax_description']
        ));
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function updateTax($id, $data) {
        $sql = "UPDATE parameters.tb_taxes 
                SET tax_code = $1, tax_name = $2, tax_rate = $3, tax_description = $4, updated_at = NOW() 
                WHERE id = $5 AND status = 'active'";
        
        $result = pg_query_params($this->db, $sql, array(
            $data['tax_code'],
            $data['tax_name'],
            $data['tax_rate'],
            $data['tax_description'],
            $id
        ));
        
        return $result !== false;
    }

    public function deleteTax($id) {
        $sql = "UPDATE parameters.tb_taxes 
                SET status = 'inactive', updated_at = NOW() 
                WHERE id = $1";
        
        $result = pg_query_params($this->db, $sql, array($id));
        return $result !== false;
    }

    public function searchTaxes($searchTerm) {
        $sql = "SELECT id, tax_code, tax_name, tax_rate, tax_description, status, created_at, updated_at 
                FROM parameters.tb_taxes 
                WHERE status = 'active' 
                AND (tax_code ILIKE $1 OR tax_name ILIKE $1 OR tax_description ILIKE $1) 
                ORDER BY tax_rate ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        $result = pg_query_params($this->db, $sql, array($searchPattern));
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function checkTaxCodeExists($code, $excludeId = null) {
        $sql = "SELECT id FROM parameters.tb_taxes WHERE tax_code = $1 AND status = 'active'";
        $params = array($code);
        
        if ($excludeId) {
            $sql .= " AND id != $2";
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->db, $sql, $params);
        return pg_num_rows($result) > 0;
    }

    public function getTaxDropdown() {
        $sql = "SELECT id, tax_code, tax_name, tax_rate 
                FROM parameters.tb_taxes 
                WHERE status = 'active' 
                ORDER BY tax_rate ASC";
        
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return false;
        }
        
        return pg_fetch_all($result);
    }

    public function getTotalTaxes() {
        $sql = "SELECT COUNT(*) as total FROM parameters.tb_taxes WHERE status = 'active'";
        $result = pg_query($this->db, $sql);
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['total'];
    }
}
