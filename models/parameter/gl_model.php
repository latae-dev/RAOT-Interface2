<?php

class GLModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readGLAccounts()
    {
        $query = "SELECT * FROM parameters.tb_gl_accounts WHERE status = 'active' ORDER BY gl_account_code ASC";
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

    public function createGLAccount($data)
    {
        $query = "INSERT INTO parameters.tb_gl_accounts (gl_account_code, gl_account_name, gl_account_description, status) VALUES ($1, $2, $3, $4) RETURNING id";
        $result = pg_query_params($this->conn, $query, array($data['gl_account_code'], $data['gl_account_name'], $data['gl_account_description'], $data['status']));
        if ($result) {
            $row = pg_fetch_assoc($result);
            return ["status" => "success", "message" => "GL Account created successfully", "id" => $row['id']];
        } else {
            return ["status" => "error", "message" => "Failed to create GL Account: " . pg_last_error($this->conn)];
        }
    }

    public function updateGLAccount($id, $data)
    {
        $query = "UPDATE parameters.tb_gl_accounts SET gl_account_code = $1, gl_account_name = $2, gl_account_description = $3, status = $4, updated_at = CURRENT_TIMESTAMP WHERE id = $5";
        $result = pg_query_params($this->conn, $query, array($data['gl_account_code'], $data['gl_account_name'], $data['gl_account_description'], $data['status'], $id));
        if ($result) {
            return ["status" => "success", "message" => "GL Account updated successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to update GL Account: " . pg_last_error($this->conn)];
        }
    }

    public function deleteGLAccount($id)
    {
        $query = "UPDATE parameters.tb_gl_accounts SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = $1";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            return ["status" => "success", "message" => "GL Account deleted successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to delete GL Account: " . pg_last_error($this->conn)];
        }
    }

    public function getGLAccountById($id)
    {
        $query = "SELECT * FROM parameters.tb_gl_accounts WHERE id = $1 AND status = 'active'";
        $result = pg_query_params($this->conn, $query, array($id));
        if ($result) {
            $row = pg_fetch_assoc($result);
            if ($row) {
                return $row;
            } else {
                return ["status" => "error", "message" => "GL Account not found"];
            }
        } else {
            return ["status" => "error", "message" => "Query failed"];
        }
    }

    public function searchGLAccounts($searchTerm)
    {
        $query = "SELECT * FROM parameters.tb_gl_accounts WHERE (gl_account_code ILIKE $1 OR gl_account_name ILIKE $1 OR gl_account_description ILIKE $1) AND status = 'active' ORDER BY gl_account_code ASC";
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

    public function checkGLAccountCodeExists($code, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_gl_accounts WHERE gl_account_code = $1 AND id != $2 AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($code, $excludeId));
        } else {
            $query = "SELECT COUNT(*) as count FROM parameters.tb_gl_accounts WHERE gl_account_code = $1 AND status = 'active'";
            $result = pg_query_params($this->conn, $query, array($code));
        }
        
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['count'] > 0;
        } else {
            return false;
        }
    }

    public function getGLAccountDropdownData()
    {
        $query = "SELECT id, gl_account_code, gl_account_name, gl_account_description FROM parameters.tb_gl_accounts WHERE status = 'active' ORDER BY gl_account_code ASC";
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

    public function getTotalGLAccounts()
    {
        $query = "SELECT COUNT(*) as total FROM parameters.tb_gl_accounts WHERE status = 'active'";
        $result = pg_query($this->conn, $query);
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }
}
