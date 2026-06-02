<?php

require_once __DIR__ . '/sap_budget_client.php';
require_once __DIR__ . '/../../repositories/proposal/budget_parameter_repository.php';

class BudgetCheckService
{
    private BudgetParameterRepository $parameterRepository;
    private SapBudgetClient $sapClient;
    private array $config;

    public function __construct(
        BudgetParameterRepository $parameterRepository,
        SapBudgetClient $sapClient,
        array $config
    ) {
        $this->parameterRepository = $parameterRepository;
        $this->sapClient = $sapClient;
        $this->config = $config;
    }

    /**
     * @return array{status:string,sap_status?:string,message?:string,data?:array}
     */
    public function checkBudget(array $input): array
    {
        $validationError = $this->validateInput($input);
        if ($validationError !== null) {
            return $this->withDebug([
                'status' => 'error',
                'message' => $validationError,
            ], $input);
        }

        $totalAmount = round((float) $input['total_amount'], 2);
        $sapParams = $this->buildSapParams($input);

        try {
            $sapResponse = $this->sapClient->fetchRemainingBudget($sapParams);
        } catch (RuntimeException $e) {
            return $this->withDebug([
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อ SAP ได้: ' . $e->getMessage(),
            ], $input, $sapParams);
        }

        if (($sapResponse['sap_status'] ?? 'E') !== 'S') {
            return $this->withDebug([
                'status' => 'error',
                'sap_status' => $sapResponse['sap_status'] ?? 'E',
                'message' => $sapResponse['message'] ?: 'SAP ตอบกลับด้วยสถานะ Error',
            ], $input, $sapParams, $sapResponse);
        }

        $rows = $sapResponse['data'] ?? [];
        if ($rows === []) {
            return $this->withDebug([
                'status' => 'error',
                'sap_status' => 'S',
                'message' => 'ไม่พบข้อมูลงบประมาณจาก SAP สำหรับเงื่อนไขที่ระบุ',
            ], $input, $sapParams, $sapResponse);
        }

        $balance = $this->resolveBalance($rows);
        $remainingAfterDeduct = round($balance - $totalAmount, 2);
        $isSufficient = $totalAmount <= $balance;

        $firstRow = $rows[0];

        return $this->withDebug([
            'status' => 'success',
            'sap_status' => 'S',
            'message' => $sapResponse['message'] ?: 'Success',
            'data' => [
                'balance' => $balance,
                'budget' => $this->toFloat($firstRow['budget'] ?? 0),
                'actual' => $this->toFloat($firstRow['actual'] ?? 0),
                'total_amount' => $totalAmount,
                'remaining_after_deduct' => $remainingAfterDeduct,
                'is_sufficient' => $isSufficient,
                'over_amount' => $isSufficient ? 0 : round($totalAmount - $balance, 2),
                'sap_rows' => count($rows),
            ],
        ], $input, $sapParams, $sapResponse);
    }

    private function withDebug(
        array $result,
        array $input,
        ?array $sapParams = null,
        ?array $sapResponse = null
    ): array {
        if (empty($this->config['debug'])) {
            return $result;
        }

        $debug = [
            'frontend_payload' => $input,
            'sap_params' => $sapParams,
        ];

        if (is_array($sapResponse)) {
            if (isset($sapResponse['_debug'])) {
                $debug = array_merge($debug, $sapResponse['_debug']);
            }

            $normalized = $sapResponse;
            unset($normalized['_debug']);
            $debug['sap_normalized'] = $normalized;
        }

        $result['debug'] = $debug;
        return $result;
    }

    private function validateInput(array $input): ?string
    {
        $required = [
            'start_date' => 'วันที่เริ่มต้น',
            'end_date' => 'วันที่สิ้นสุด',
            'total_amount' => 'ยอดรวมใบขอเสนอ',
        ];

        foreach ($required as $field => $label) {
            if (!isset($input[$field]) || $input[$field] === '') {
                return "กรุณาระบุ{$label}";
            }
        }

        if ((float) $input['total_amount'] <= 0) {
            return 'ยอดรวมใบขอเสนอต้องมากกว่า 0';
        }

        if ($this->convertDateToYmd($input['start_date']) === null) {
            return 'รูปแบบวันที่เริ่มต้นไม่ถูกต้อง';
        }

        if ($this->convertDateToYmd($input['end_date']) === null) {
            return 'รูปแบบวันที่สิ้นสุดไม่ถูกต้อง';
        }

        return null;
    }

    private function buildSapParams(array $input): array
    {
        $startDate = $this->convertDateToYmd($input['start_date']);
        $endDate = $this->convertDateToYmd($input['end_date']);

        $params = [
            'gjahr' => substr($startDate, 0, 4),
            'rfikrs' => $this->config['rfikrs'] ?? '1000',
            'str_date' => $startDate,
            'end_date' => $endDate,
        ];

        if (!empty($input['cost_center_id'])) {
            $code = $this->parameterRepository->getCostCenterCode($input['cost_center_id']);
            if ($code) {
                $params['rfundsctr'] = $code;
            }
        } elseif (!empty($input['funds_center_id'])) {
            // fallback: Funds Center dropdown ใน UI โหลดจาก tb_cost_centers เช่นกัน
            $code = $this->parameterRepository->getCostCenterCode($input['funds_center_id']);
            if ($code) {
                $params['rfundsctr'] = $code;
            }
        }

        if (!empty($input['liability_id'])) {
            $code = $this->parameterRepository->getLiabilityCode($input['liability_id']);
            if ($code) {
                $params['rcmmtitem'] = $code;
            }
        }

        if (!empty($input['fund_id'])) {
            $code = $this->parameterRepository->getFundingCode($input['fund_id']);
            if ($code) {
                $params['rfund'] = $code;
            }
        }

        if (!empty($input['scope_id'])) {
            $code = $this->parameterRepository->getScopeCode($input['scope_id']);
            if ($code) {
                $params['rfuncarea'] = $code;
            }
        }

        return $params;
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

    private function convertDateToYmd(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
            return $matches[3] . $matches[2] . $matches[1];
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return $matches[1] . $matches[2] . $matches[3];
        }

        if (preg_match('/^\d{8}$/', $date)) {
            return $date;
        }

        return null;
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
}
