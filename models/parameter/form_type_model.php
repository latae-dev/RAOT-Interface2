<?php

class FormTypeModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    
    public function readFormTypes()
    {
        $query = "SELECT * FROM parameters.tb_form_types WHERE status = 'active' ORDER BY form_type_name ASC";
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
    
    public function readFormTypeOne($id)
    {
        $query = "SELECT * FROM parameters.tb_form_types WHERE id = $1 AND status = 'active'";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_assoc($result);
        return $data;
    }
    
    public function createFormType($data)
    {
        $query = "INSERT INTO parameters.tb_form_types (form_type_name, form_type_code, status, created_at) 
                  VALUES ($1, $2, 'active', NOW())";
        
        $result = pg_query_params($this->conn, $query, array(
            $data['form_type_name'],
            $data['form_type_code'] ?? ''
        ));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }
    
    public function updateFormType($id, $data)
    {
        $updateFields = [];
        $updateValues = [];
        
        if (isset($data['form_type_name'])) {
            $updateFields[] = "form_type_name = $" . (count($updateValues) + 1);
            $updateValues[] = $data['form_type_name'];
        }
        
        if (isset($data['form_type_code'])) {
            $updateFields[] = "form_type_code = $" . (count($updateValues) + 1);
            $updateValues[] = $data['form_type_code'];
        }
        
        $updateFields[] = "updated_at = NOW()";
        $updateValues[] = $id;
        
        $query = "UPDATE parameters.tb_form_types SET " . implode(', ', $updateFields) . " WHERE id = $" . (count($updateValues));
        $result = pg_query_params($this->conn, $query, $updateValues);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }
    
    public function deleteFormType($id)
    {
        $query = "UPDATE parameters.tb_form_types SET status = 'inactive', updated_at = NOW() WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        return true;
    }
    
    public function searchFormTypes($keyword)
    {
        $query = "SELECT * FROM parameters.tb_form_types 
                  WHERE (form_type_name ILIKE $1 OR form_type_code ILIKE $2) 
                  AND status = 'active' 
                  ORDER BY form_type_name ASC";
        
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
        $query = "SELECT COUNT(*) FROM parameters.tb_form_types WHERE form_type_name = $1 AND status = 'active'";
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
    
    public function isInUse($id)
    {
        $query = "SELECT COUNT(*) FROM request_proposals WHERE form_type_id = $1 AND status != 'deleted'";
        $result = pg_query_params($this->conn, $query, array($id));
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        $count = pg_fetch_result($result, 0, 0);
        return $count;
    }
    
    public function getForDropdown()
    {
        $query = "SELECT id, form_type_name, form_type_code FROM parameters.tb_form_types 
                  WHERE status = 'active' 
                  ORDER BY form_type_name ASC";
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
        $query = "SELECT COUNT(*) FROM parameters.tb_form_types WHERE status = 'active'";
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        
        $count = pg_fetch_result($result, 0, 0);
        return $count;
    }
    
    public function getPaginated($page = 1, $limit = 10, $search = '')
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE status = 'active'";
        $params = array();
        
        if (!empty($search)) {
            $whereClause .= " AND (form_type_name ILIKE $" . (count($params) + 1) . " OR form_type_code ILIKE $" . (count($params) + 2) . ")";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // ดึงข้อมูล
        $query = "SELECT * FROM parameters.tb_form_types {$whereClause} 
                  ORDER BY form_type_name ASC 
                  LIMIT $" . (count($params) + 1) . " OFFSET $" . (count($params) + 2);
        $params[] = $limit;
        $params[] = $offset;
        
        $result = pg_query_params($this->conn, $query, $params);
        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }
        $data = pg_fetch_all($result);
        
        // นับจำนวนทั้งหมด
        $countQuery = "SELECT COUNT(*) FROM parameters.tb_form_types {$whereClause}";
        $countParams = array_slice($params, 0, -2); // ลบ limit และ offset
        $countResult = pg_query_params($this->conn, $countQuery, $countParams);
        $total = pg_fetch_result($countResult, 0, 0);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
}
