<?php

class PurchaseGroupModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readPurchaseGroups()
    {
        $query = "SELECT * FROM parameters.tb_purchase_groups WHERE status = 'active' ORDER BY purchase_group_name ASC";
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

    public function createPurchaseGroup($data)
    {
        $query = "INSERT INTO parameters.tb_purchase_groups (purchase_group_name, purchase_group_code, status) VALUES ($1, $2, $3) RETURNING id";
        $result = pg_query_params($this->conn, $query, array($data['purchase_group_name'], $data['purchase_group_code'], $data['status']));
        if ($result) {
            $row = pg_fetch_assoc($result);
            return ["status" => "success", "message" => "Purchase group created successfully", "id" => $row['id']];
        } else {
            return ["status" => "error", "message" => "Failed to create purchase group: " . pg_last_error($this->conn)];
        }
    }

    public function updatePurchaseGroup($id, $data)
    {
        $query = "UPDATE parameters.tb_purchase_groups SET purchase_group_name = $1, purchase_group_code = $2, status = $3, updated_at = CURRENT_TIMESTAMP WHERE id = $4";
        $result = pg_query_params($this->conn, $query, array($data['purchase_group_name'], $data['purchase_group_code'], $data['status'], $id));
        if ($result) {
            return ["status" => "success", "message" => "Purchase group updated successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to update purchase group: " . pg_last_error($this->conn)];
        }
    }

    public function deletePurchaseGroup($id)
    {
        $query = "UPDATE parameters.tb_purchase_groups SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            return ["status" => "success", "message" => "Purchase group deleted successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to delete purchase group: " . pg_last_error($this->conn)];
        }
    }

    public function getPurchaseGroupById($id)
    {
        $query = "SELECT * FROM parameters.tb_purchase_groups WHERE id = $1 AND status = 'active'";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            $row = pg_fetch_assoc($result);
            if ($row) {
                return $row;
            } else {
                return ["status" => "error", "message" => "Purchase group not found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function searchPurchaseGroups($searchTerm)
    {
        $query = "SELECT * FROM parameters.tb_purchase_groups WHERE (purchase_group_name ILIKE $1 OR purchase_group_code ILIKE $1) AND status = 'active' ORDER BY purchase_group_name ASC";
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

    public function checkPurchaseGroupNameExists($name, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_purchase_groups WHERE purchase_group_name = $1 AND id != $2 AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($name, $excludeId));
        } else {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_purchase_groups WHERE purchase_group_name = $1 AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($name));
        }
        
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['count'] > 0;
        } else {
            return false;
        }
    }

    public function getPurchaseGroupDropdownData()
    {
        $query = "SELECT id, purchase_group_name, purchase_group_code FROM parameters.tb_purchase_groups WHERE status = 'active' ORDER BY purchase_group_name ASC";
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

    public function getTotalPurchaseGroups()
    {
        $query = "SELECT COUNT(*) as total FROM parameters.tb_purchase_groups WHERE status = 'active'";
        $result = pg_query($this->conn, $query);
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }
}
