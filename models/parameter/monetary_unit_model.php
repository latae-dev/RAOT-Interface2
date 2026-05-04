<?php

class MonetaryUnitModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readMonetaryUnits()
    {
        $query = "SELECT * FROM parameters.tb_monetary_units WHERE status = 'active' ORDER BY monetary_unit_name_th ASC";
        $result = pg_query($this->conn, $query);
        if ($result) {
            $rows = pg_fetch_all($result);
            if ($rows) {
                return $rows;
            } else {
                return ["status" => "error", "message" => "No data found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function createMonetaryUnit($data)
    {
        $query = "INSERT INTO parameters.tb_monetary_units (monetary_unit_code, monetary_unit_name_en, monetary_unit_name_th, status) VALUES ($1, $2, $3, $4) RETURNING id";
        $result = pg_query_params($this->conn, $query, array($data['monetary_unit_code'], $data['monetary_unit_name_en'], $data['monetary_unit_name_th'], $data['status']));
        if ($result) {
            $row = pg_fetch_assoc($result);
            return ["status" => "success", "message" => "Monetary unit created successfully", "id" => $row['id']];
        } else {
            return ["status" => "error", "message" => "Failed to create monetary unit: " . pg_last_error($this->conn)];
        }
    }

    public function updateMonetaryUnit($id, $data)
    {
        $query = "UPDATE parameters.tb_monetary_units SET monetary_unit_code = $1, monetary_unit_name_en = $2, monetary_unit_name_th = $3, status = $4, updated_at = CURRENT_TIMESTAMP WHERE id = $5";
        $result = pg_query_params($this->conn, $query, array($data['monetary_unit_code'], $data['monetary_unit_name_en'], $data['monetary_unit_name_th'], $data['status'], $id));
        if ($result) {
            return ["status" => "success", "message" => "Monetary unit updated successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to update monetary unit: " . pg_last_error($this->conn)];
        }
    }

    public function deleteMonetaryUnit($id)
    {
        $query = "UPDATE parameters.tb_monetary_units SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            return ["status" => "success", "message" => "Monetary unit deleted successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to delete monetary unit: " . pg_last_error($this->conn)];
        }
    }

    public function getMonetaryUnitById($id)
    {
        $query = "SELECT * FROM parameters.tb_monetary_units WHERE id = $1 AND status = 'active'";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            $row = pg_fetch_assoc($result);
            if ($row) {
                return $row;
            } else {
                return ["status" => "error", "message" => "Monetary unit not found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function searchMonetaryUnits($searchTerm)
    {
        $query = "SELECT * FROM parameters.tb_monetary_units WHERE (monetary_unit_name_en ILIKE $1 OR monetary_unit_name_th ILIKE $1 OR monetary_unit_code ILIKE $1) AND status = 'active' ORDER BY monetary_unit_name_th ASC";
        $result = pg_query_params($this->conn, $query, array('%' . $searchTerm . '%'));
        if ($result) {
            $rows = pg_fetch_all($result);
            if ($rows) {
                return $rows;
            } else {
                return ["status" => "error", "message" => "No data found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function checkMonetaryUnitNameExists($nameEn, $nameTh, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_monetary_units WHERE (monetary_unit_name_en = $1 OR monetary_unit_name_th = $2) AND id != $3 AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($nameEn, $nameTh, $excludeId));
        } else {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_monetary_units WHERE (monetary_unit_name_en = $1 OR monetary_unit_name_th = $2) AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($nameEn, $nameTh));
        }
        
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['count'] > 0;
        } else {
            return false;
        }
    }

    public function getMonetaryUnitDropdownData()
    {
        $query = "SELECT id, monetary_unit_code, monetary_unit_name_en, monetary_unit_name_th FROM parameters.tb_monetary_units WHERE status = 'active' ORDER BY monetary_unit_name_th ASC";
        $result = pg_query($this->conn, $query);
        if ($result) {
            $rows = pg_fetch_all($result);
            if ($rows) {
                return $rows;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    public function getTotalMonetaryUnits()
    {
        $query = "SELECT COUNT(*) as total FROM parameters.tb_monetary_units WHERE status = 'active'";
        $result = pg_query($this->conn, $query);
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }
}
