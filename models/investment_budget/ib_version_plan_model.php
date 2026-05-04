<?php

/**
 * Investment Budget Version Plan Model
 * Model for managing version plans in investment budget system
 */

class IbVersionPlanModel
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Read all version plans
     * @param bool $activeOnly - Return only active records
     * @return array
     */
    public function readVersionPlan($activeOnly = true)
    {
        try {
            $sql = "SELECT 
                        id,
                        code,
                        name,
                        created_at,
                        updated_at,
                        is_active
                    FROM parameters.tb_ib_version_plan";

            if ($activeOnly) {
                $sql .= " WHERE is_active = TRUE";
            }

            $sql .= " ORDER BY code";

            $result = pg_query($this->conn, $sql);

            if (!$result) {
                throw new Exception("Database query failed: " . pg_last_error($this->conn));
            }

            $data = [];
            while ($row = pg_fetch_assoc($result)) {
                // Format dates for display
                $row['created_at_formatted'] = date('d/m/Y H:i', strtotime($row['created_at']));

                // Add status labels
                $row['status_label'] = $this->getStatusLabel($row);

                $data[] = $row;
            }

            pg_free_result($result);
            return $data;
        } catch (Exception $e) {
            error_log("Error in readVersionPlan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Read single version plan by ID
     * @param int $id
     * @return array|null
     */
    public function readVersionPlanOne($id)
    {
        try {
            $sql = "SELECT 
                        id,
                        code,
                        name,
                        created_at,
                        updated_at,
                        is_active
                    FROM parameters.tb_ib_version_plan 
                    WHERE id = $1";

            $result = pg_query_params($this->conn, $sql, array($id));

            if (!$result) {
                throw new Exception("Database query failed: " . pg_last_error($this->conn));
            }

            $data = pg_fetch_assoc($result);

            if ($data) {
                // Format dates
                $data['created_at_formatted'] = date('d/m/Y H:i', strtotime($data['created_at']));

                // Add status label
                $data['status_label'] = $this->getStatusLabel($data);
            }

            pg_free_result($result);
            return $data;
        } catch (Exception $e) {
            error_log("Error in readVersionPlanOne: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Read version plan by code
     * @param string $code
     * @return array|null
     */
    public function readVersionPlanByCode($code)
    {
        try {
            $sql = "SELECT 
                        id,
                        code,
                        name,
                        description,
                        version_number,
                        is_active,
                        is_current,
                        effective_date,
                        created_at,
                        updated_at,
                        created_by,
                        updated_by
                    FROM parameters.tb_ib_version_plan 
                    WHERE code = $1";

            $result = pg_query_params($this->conn, $sql, array($code));

            if (!$result) {
                throw new Exception("Database query failed: " . pg_last_error($this->conn));
            }

            $data = pg_fetch_assoc($result);

            if ($data) {
                $data['effective_date_formatted'] = $data['effective_date'] ?
                    date('d/m/Y', strtotime($data['effective_date'])) : '';
                $data['created_at_formatted'] = date('d/m/Y H:i', strtotime($data['created_at']));
                $data['status_label'] = $this->getStatusLabel($data);
            }

            pg_free_result($result);
            return $data;
        } catch (Exception $e) {
            error_log("Error in readVersionPlanByCode: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current active version plan
     * @return array|null
     */
    public function getCurrentVersionPlan()
    {
        try {
            $sql = "SELECT 
                        id,
                        code,
                        name,
                        description,
                        version_number,
                        is_active,
                        is_current,
                        effective_date,
                        created_at,
                        updated_at,
                        created_by,
                        updated_by
                    FROM parameters.tb_ib_version_plan 
                    WHERE is_current = TRUE AND is_active = TRUE
                    LIMIT 1";

            $result = pg_query($this->conn, $sql);

            if (!$result) {
                throw new Exception("Database query failed: " . pg_last_error($this->conn));
            }

            $data = pg_fetch_assoc($result);

            if ($data) {
                $data['effective_date_formatted'] = $data['effective_date'] ?
                    date('d/m/Y', strtotime($data['effective_date'])) : '';
                $data['created_at_formatted'] = date('d/m/Y H:i', strtotime($data['created_at']));
                $data['status_label'] = $this->getStatusLabel($data);
            }

            pg_free_result($result);
            return $data;
        } catch (Exception $e) {
            error_log("Error in getCurrentVersionPlan: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new version plan
     * @param array $data
     * @return array
     */
    public function createVersionPlan($data)
    {
        try {
            // Validate required fields
            $required = ['code', 'name', 'version_number'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Required field missing: $field");
                }
            }

            // Check if code already exists
            $existing = $this->readVersionPlanByCode($data['code']);
            if ($existing) {
                throw new Exception("Version plan code already exists: " . $data['code']);
            }

            $sql = "INSERT INTO parameters.tb_ib_version_plan 
                    (code, name, description, version_number, is_active, is_current, effective_date, created_by) 
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8) 
                    RETURNING id";

            $params = [
                $data['code'],
                $data['name'],
                $data['description'] ?? null,
                $data['version_number'],
                $data['is_active'] ?? true,
                $data['is_current'] ?? false,
                $data['effective_date'] ?? null,
                $data['created_by'] ?? null
            ];

            $result = pg_query_params($this->conn, $sql, $params);

            if (!$result) {
                throw new Exception("Failed to create version plan: " . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            $newId = $row['id'];

            pg_free_result($result);

            return [
                'success' => true,
                'message' => 'Version plan created successfully',
                'id' => $newId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update version plan
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateVersionPlan($id, $data)
    {
        try {
            // Check if record exists
            $existing = $this->readVersionPlanOne($id);
            if (!$existing) {
                throw new Exception("Version plan not found with ID: $id");
            }

            // Check if code already exists (for different record)
            if (!empty($data['code']) && $data['code'] !== $existing['code']) {
                $codeExists = $this->readVersionPlanByCode($data['code']);
                if ($codeExists) {
                    throw new Exception("Version plan code already exists: " . $data['code']);
                }
            }

            $updateFields = [];
            $params = [];
            $paramCount = 1;

            $allowedFields = [
                'code',
                'name',
                'description',
                'version_number',
                'is_active',
                'is_current',
                'effective_date',
                'updated_by'
            ];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateFields[] = "$field = $$paramCount";
                    $params[] = $data[$field];
                    $paramCount++;
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No valid fields to update");
            }

            $sql = "UPDATE parameters.tb_ib_version_plan 
                    SET " . implode(', ', $updateFields) . "
                    WHERE id = $$paramCount";
            $params[] = $id;

            $result = pg_query_params($this->conn, $sql, $params);

            if (!$result) {
                throw new Exception("Failed to update version plan: " . pg_last_error($this->conn));
            }

            pg_free_result($result);

            return [
                'success' => true,
                'message' => 'Version plan updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete version plan
     * @param int $id
     * @return array
     */
    public function deleteVersionPlan($id)
    {
        try {
            // Check if record exists
            $existing = $this->readVersionPlanOne($id);
            if (!$existing) {
                throw new Exception("Version plan not found with ID: $id");
            }

            // Don't allow deletion of current version
            if ($existing['is_current']) {
                throw new Exception("Cannot delete current active version plan");
            }

            $sql = "DELETE FROM parameters.tb_ib_version_plan WHERE id = $1";
            $result = pg_query_params($this->conn, $sql, array($id));

            if (!$result) {
                throw new Exception("Failed to delete version plan: " . pg_last_error($this->conn));
            }

            pg_free_result($result);

            return [
                'success' => true,
                'message' => 'Version plan deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set version as current
     * @param int $id
     * @return array
     */
    public function setCurrentVersion($id)
    {
        try {
            $existing = $this->readVersionPlanOne($id);
            if (!$existing) {
                throw new Exception("Version plan not found with ID: $id");
            }

            if (!$existing['is_active']) {
                throw new Exception("Cannot set inactive version as current");
            }

            // Update using the trigger that handles single current version
            $sql = "UPDATE parameters.tb_ib_version_plan 
                    SET is_current = TRUE 
                    WHERE id = $1";

            $result = pg_query_params($this->conn, $sql, array($id));

            if (!$result) {
                throw new Exception("Failed to set current version: " . pg_last_error($this->conn));
            }

            pg_free_result($result);

            return [
                'success' => true,
                'message' => 'Version set as current successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get status label for version plan
     * @param array $data
     * @return string
     */
    private function getStatusLabel($data)
    {
        if (!$data['is_active']) {
            return 'ไม่ใช้งาน';
        } else {
            return 'ใช้งาน';
        }
    }

    /**
     * Search version plans
     * @param string $searchTerm
     * @param bool $activeOnly
     * @return array
     */
    public function searchVersionPlan($searchTerm, $activeOnly = true)
    {
        try {
            $sql = "SELECT 
                        id,
                        code,
                        name,
                        description,
                        version_number,
                        is_active,
                        is_current,
                        effective_date,
                        created_at,
                        updated_at,
                        created_by,
                        updated_by
                    FROM parameters.tb_ib_version_plan 
                    WHERE (
                        LOWER(code) LIKE LOWER($1) OR 
                        LOWER(name) LIKE LOWER($1) OR 
                        LOWER(description) LIKE LOWER($1) OR
                        LOWER(version_number) LIKE LOWER($1)
                    )";

            if ($activeOnly) {
                $sql .= " AND is_active = TRUE";
            }

            $sql .= " ORDER BY effective_date DESC, version_number DESC";

            $searchPattern = '%' . $searchTerm . '%';
            $result = pg_query_params($this->conn, $sql, array($searchPattern));

            if (!$result) {
                throw new Exception("Database query failed: " . pg_last_error($this->conn));
            }

            $data = [];
            while ($row = pg_fetch_assoc($result)) {
                $row['effective_date_formatted'] = $row['effective_date'] ?
                    date('d/m/Y', strtotime($row['effective_date'])) : '';
                $row['created_at_formatted'] = date('d/m/Y H:i', strtotime($row['created_at']));
                $row['status_label'] = $this->getStatusLabel($row);

                $data[] = $row;
            }

            pg_free_result($result);
            return $data;
        } catch (Exception $e) {
            error_log("Error in searchVersionPlan: " . $e->getMessage());
            return [];
        }
    }
}
