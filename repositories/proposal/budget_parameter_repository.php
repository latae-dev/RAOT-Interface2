<?php

class BudgetParameterRepository
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCostCenterCode($id): ?string
    {
        return $this->fetchCode(
            'SELECT cost_center_code FROM parameters.tb_cost_centers WHERE id = $1 AND status = $2',
            [$id, 'active'],
            'cost_center_code'
        );
    }

    public function getFundingCode($id): ?string
    {
        return $this->fetchCode(
            'SELECT funding_code FROM parameters.tb_fundings WHERE id = $1 AND status = $2',
            [$id, 'active'],
            'funding_code'
        );
    }

    public function getLiabilityCode($id): ?string
    {
        return $this->fetchCode(
            'SELECT liability_code FROM parameters.tb_liabilities_clone WHERE id = $1 AND status = $2',
            [$id, 'active'],
            'liability_code'
        );
    }

    public function getScopeCode($id): ?string
    {
        return $this->fetchCode(
            'SELECT scope_code FROM parameters.tb_scopes WHERE id = $1 AND status = $2',
            [$id, 'active'],
            'scope_code'
        );
    }

    private function fetchCode(string $sql, array $params, string $column): ?string
    {
        if (empty($params[0]) || !is_numeric($params[0])) {
            return null;
        }

        $result = pg_query_params($this->conn, $sql, $params);
        if (!$result) {
            return null;
        }

        $row = pg_fetch_assoc($result);
        if (!$row || !isset($row[$column])) {
            return null;
        }

        $code = trim((string) $row[$column]);
        return $code !== '' ? $code : null;
    }
}
