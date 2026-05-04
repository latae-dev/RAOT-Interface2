<?php
class FiscalYearModel
{
    private $conn;
    private $table = 'parameters.tb_fiscal_year';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Read all fiscal years
     */
    public function readFiscalYear()
    {
        $query = "SELECT id, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE is_active = true 
                  ORDER BY name ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Database error in readFiscalYear: " . pg_last_error($this->conn));
            return false;
        }

        $fiscal_years = [];
        while ($row = pg_fetch_assoc($result)) {
            $fiscal_years[] = $row;
        }

        return $fiscal_years;
    }

    /**
     * Read one fiscal year by ID
     */
    public function readFiscalYearOne($id)
    {
        $query = "SELECT id, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE id = $1";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in readFiscalYearOne: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_assoc($result);
    }

    /**
     * Read fiscal year by name
     */
    public function readFiscalYearByName($name)
    {
        $query = "SELECT id, name, created_at, updated_at, is_active 
                  FROM " . $this->table . " 
                  WHERE name = $1";

        $result = pg_query_params($this->conn, $query, array($name));

        if (!$result) {
            error_log("Database error in readFiscalYearByName: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_assoc($result);
    }

    /**
     * Create new fiscal year
     */
    public function createFiscalYear($name)
    {
        $query = "INSERT INTO " . $this->table . " (name) 
                  VALUES ($1) 
                  RETURNING id, name, created_at, updated_at, is_active";

        $result = pg_query_params($this->conn, $query, array($name));

        if (!$result) {
            error_log("Database error in createFiscalYear: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_assoc($result);
    }

    /**
     * Update fiscal year
     */
    public function updateFiscalYear($id, $name)
    {
        $query = "UPDATE " . $this->table . " 
                  SET name = $1, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $2 
                  RETURNING id, name, created_at, updated_at, is_active";

        $result = pg_query_params($this->conn, $query, array($name, $id));

        if (!$result) {
            error_log("Database error in updateFiscalYear: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_assoc($result);
    }

    /**
     * Delete fiscal year (soft delete)
     */
    public function deleteFiscalYear($id)
    {
        $query = "UPDATE " . $this->table . " 
                  SET is_active = false, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = $1";

        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            error_log("Database error in deleteFiscalYear: " . pg_last_error($this->conn));
            return false;
        }

        return pg_affected_rows($result) > 0;
    }
}
