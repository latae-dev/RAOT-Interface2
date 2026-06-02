<?php

require_once __DIR__ . '/sap_budget_client.php';

class BudgetCheckTesterService
{
    private SapBudgetClient $sapClient;
    private array $config;

    public function __construct(SapBudgetClient $sapClient, array $config)
    {
        $this->sapClient = $sapClient;
        $this->config = $config;
    }

    /**
     * @return array{status:string,message?:string,sap_params?:array,sap_response?:array,balance?:float,scenarios?:array}
     */
    public function runScenarios(array $sapParams, array $customAmounts = []): array
    {
        $normalizedParams = $this->normalizeSapParams($sapParams);
        $validationError = $this->validateSapParams($normalizedParams);
        if ($validationError !== null) {
            return [
                'status' => 'error',
                'message' => $validationError,
                'sap_params' => $normalizedParams,
            ];
        }

        try {
            $sapResponse = $this->sapClient->fetchRemainingBudget($normalizedParams);
        } catch (RuntimeException $e) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อ SAP ได้: ' . $e->getMessage(),
                'sap_params' => $normalizedParams,
            ];
        }

        $sapStatus = $sapResponse['sap_status'] ?? 'E';
        $sapMessage = $sapResponse['message'] ?? '';
        $rows = $sapResponse['data'] ?? [];

        if ($sapStatus !== 'S') {
            return [
                'status' => 'error',
                'message' => $sapMessage ?: 'SAP ตอบกลับด้วยสถานะ Error',
                'sap_status' => $sapStatus,
                'sap_params' => $normalizedParams,
                'sap_response' => $this->stripInternalDebug($sapResponse),
            ];
        }

        if ($rows === []) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลงบประมาณจาก SAP',
                'sap_status' => 'S',
                'sap_params' => $normalizedParams,
                'sap_response' => $this->stripInternalDebug($sapResponse),
            ];
        }

        $balance = $this->resolveBalance($rows);
        $firstRow = $rows[0];
        $scenarioAmounts = $this->buildScenarioAmounts($balance, $customAmounts);
        $scenarios = [];

        foreach ($scenarioAmounts as $scenario) {
            $scenarios[] = $this->evaluateScenario(
                $scenario['key'],
                $scenario['label'],
                $scenario['amount'],
                $balance,
                $firstRow
            );
        }

        return [
            'status' => 'success',
            'message' => $sapMessage ?: 'Success',
            'sap_status' => 'S',
            'sap_params' => $normalizedParams,
            'sap_response' => $this->stripInternalDebug($sapResponse),
            'balance' => $balance,
            'budget' => $this->toFloat($firstRow['budget'] ?? 0),
            'actual' => $this->toFloat($firstRow['actual'] ?? 0),
            'sap_rows' => count($rows),
            'scenarios' => $scenarios,
        ];
    }

    private function normalizeSapParams(array $params): array
    {
        $allowed = [
            'gjahr', 'rfikrs', 'rfundsctr', 'rcmmtitem', 'rfund', 'rfuncarea', 'projid', 'str_date', 'end_date',
        ];

        $normalized = [];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $params)) {
                continue;
            }
            $value = trim((string) $params[$key]);
            if ($value === '') {
                continue;
            }
            $normalized[$key] = $value;
        }

        if (!isset($normalized['rfikrs'])) {
            $normalized['rfikrs'] = (string) ($this->config['rfikrs'] ?? '1000');
        }

        return $normalized;
    }

    private function validateSapParams(array $params): ?string
    {
        $required = [
            'gjahr' => 'ปีงบ (gjahr)',
            'str_date' => 'วันที่เริ่ม (str_date)',
            'end_date' => 'วันที่สิ้นสุด (end_date)',
        ];

        foreach ($required as $field => $label) {
            if (empty($params[$field])) {
                return "กรุณาระบุ{$label}";
            }
        }

        return null;
    }

    /**
     * @return array<int, array{key:string,label:string,amount:float}>
     */
    private function buildScenarioAmounts(float $balance, array $customAmounts): array
    {
        $scenarios = [
            ['key' => 'zero', 'label' => 'ยอดขอ = 0 (ควรไม่ผ่าน validation)', 'amount' => 0.0],
            ['key' => 'one', 'label' => 'ยอดขอ = 1 บาท (น้อยมาก)', 'amount' => 1.0],
        ];

        if ($balance > 0) {
            $scenarios[] = [
                'key' => 'half_balance',
                'label' => 'ยอดขอ = 50% ของงบคงเหลือ',
                'amount' => round($balance * 0.5, 2),
            ];
            $scenarios[] = [
                'key' => 'balance_minus_one',
                'label' => 'ยอดขอ = งบคงเหลือ - 1',
                'amount' => round(max(0, $balance - 1), 2),
            ];
            $scenarios[] = [
                'key' => 'equal_balance',
                'label' => 'ยอดขอ = งบคงเหลือพอดี',
                'amount' => round($balance, 2),
            ];
            $scenarios[] = [
                'key' => 'balance_plus_one',
                'label' => 'ยอดขอ = งบคงเหลือ + 1',
                'amount' => round($balance + 1, 2),
            ];
            $scenarios[] = [
                'key' => 'double_balance',
                'label' => 'ยอดขอ = 200% ของงบคงเหลือ',
                'amount' => round($balance * 2, 2),
            ];
        } else {
            $scenarios[] = [
                'key' => 'any_positive',
                'label' => 'ยอดขอ = 1,000 (งบคงเหลือ = 0)',
                'amount' => 1000.0,
            ];
        }

        foreach ($customAmounts as $index => $amount) {
            $amount = round((float) $amount, 2);
            if ($amount < 0) {
                continue;
            }
            $scenarios[] = [
                'key' => 'custom_' . ($index + 1),
                'label' => 'ยอดขอกำหนดเอง #' . ($index + 1),
                'amount' => $amount,
            ];
        }

        return $scenarios;
    }

    private function evaluateScenario(
        string $key,
        string $label,
        float $totalAmount,
        float $balance,
        array $firstRow
    ): array {
        if ($totalAmount <= 0) {
            return [
                'key' => $key,
                'label' => $label,
                'total_amount' => $totalAmount,
                'expected_ui' => 'แสดง error validation — ยอดต้องมากกว่า 0',
                'check_status' => 'validation_error',
                'is_sufficient' => false,
                'balance' => $balance,
                'remaining_after_deduct' => null,
                'over_amount' => null,
                'message' => 'ยอดรวมใบขอเสนอต้องมากกว่า 0',
            ];
        }

        $remainingAfterDeduct = round($balance - $totalAmount, 2);
        $isSufficient = $totalAmount <= $balance;

        return [
            'key' => $key,
            'label' => $label,
            'total_amount' => $totalAmount,
            'expected_ui' => $isSufficient
                ? 'Modal ยืนยัน — งบเพียงพอ'
                : 'Modal แจ้งเตือน — งบไม่พอ',
            'check_status' => $isSufficient ? 'sufficient' : 'insufficient',
            'is_sufficient' => $isSufficient,
            'balance' => $balance,
            'budget' => $this->toFloat($firstRow['budget'] ?? 0),
            'actual' => $this->toFloat($firstRow['actual'] ?? 0),
            'remaining_after_deduct' => $remainingAfterDeduct,
            'over_amount' => $isSufficient ? 0 : round($totalAmount - $balance, 2),
            'message' => $isSufficient ? 'งบเพียงพอ' : 'งบคงเหลือไม่เพียงพอ',
        ];
    }

    private function resolveBalance(array $rows): float
    {
        $balance = 0.0;
        foreach ($rows as $row) {
            if (!isset($row['balance'])) {
                continue;
            }
            $balance += $this->toFloat($row['balance']);
        }

        return round($balance, 2);
    }

    private function toFloat($value): float
    {
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        if (is_string($value)) {
            $normalized = str_replace(',', '', trim($value));
            if ($normalized !== '' && is_numeric($normalized)) {
                return round((float) $normalized, 2);
            }
        }

        return 0.0;
    }

    private function stripInternalDebug(array $sapResponse): array
    {
        unset($sapResponse['_debug']);
        return $sapResponse;
    }
}
