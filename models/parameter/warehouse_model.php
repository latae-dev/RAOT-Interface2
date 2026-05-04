<?php

class WarehouseModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readWarehouses()
    {
        $query = "SELECT * FROM parameters.tb_warehouses WHERE status = 'active' ORDER BY warehouse_name ASC";
        $result = pg_query($this->conn, $query);

        // ตรวจสอบผลลัพธ์ว่าได้ข้อมูลมาหรือไม่
        if ($result) {
            // ดึงข้อมูลทั้งหมดในฐานะอาร์เรย์
            $rows = pg_fetch_all($result);

            if ($rows) {
                return $rows; // คืนค่าข้อมูลทั้งหมดที่ได้
            } else {
                return ["status" => "error", "message" => "No data found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function readWarehouseOne($id)
    {
        $query = "SELECT * FROM parameters.tb_warehouses WHERE id = $1 AND status = 'active'";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }

    public function createWarehouse($data)
    {
        $query = "INSERT INTO parameters.tb_warehouses (warehouse_name, warehouse_code, status, created_at) 
                  VALUES ($1, $2, 'active', NOW())";
        
        $result = pg_query_params($this->conn, $query, array(
            $data['warehouse_name'],
            $data['warehouse_code'] ?? ''
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }

    public function updateWarehouse($id, $data)
    {
        $updateFields = [];
        $updateValues = [];
        
        if (isset($data['warehouse_name'])) {
            $updateFields[] = "warehouse_name = $" . (count($updateValues) + 1);
            $updateValues[] = $data['warehouse_name'];
        }
        
        if (isset($data['warehouse_code'])) {
            $updateFields[] = "warehouse_code = $" . (count($updateValues) + 1);
            $updateValues[] = $data['warehouse_code'];
        }
        
        $updateFields[] = "updated_at = NOW()";
        $updateValues[] = $id;
        
        $query = "UPDATE parameters.tb_warehouses SET " . implode(', ', $updateFields) . " WHERE id = $" . (count($updateValues));
        $result = pg_query_params($this->conn, $query, $updateValues);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }

    public function deleteWarehouse($id)
    {
        $query = "UPDATE parameters.tb_warehouses SET status = 'inactive', updated_at = NOW() WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }

    public function searchWarehouses($keyword)
    {
        $query = "SELECT * FROM parameters.tb_warehouses 
                  WHERE (warehouse_name ILIKE $1 OR warehouse_code ILIKE $2) 
                  AND status = 'active' 
                  ORDER BY warehouse_name ASC";
        
        $searchTerm = "%{$keyword}%";
        $result = pg_query_params($this->conn, $query, array($searchTerm, $searchTerm));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result);
        return $data;
    }

    public function isNameExists($name, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM parameters.tb_warehouses WHERE warehouse_name = $1 AND status = 'active'";
        $params = array($name);
        
        if ($excludeId) {
            $query .= " AND id != $" . (count($params) + 1);
            $params[] = $excludeId;
        }
        
        $result = pg_query_params($this->conn, $query, $params);
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        $count = pg_fetch_result($result, 0, 0);
        return $count > 0;
    }

    public function getForDropdown()
    {
        $query = "SELECT id, warehouse_name, warehouse_code FROM parameters.tb_warehouses 
                  WHERE status = 'active' 
                  ORDER BY warehouse_name ASC";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result);
        return $data;
    }

    public function countAll()
    {
        $query = "SELECT COUNT(*) FROM parameters.tb_warehouses WHERE status = 'active'";
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        $count = pg_fetch_result($result, 0, 0);
        return $count;
    }
}
