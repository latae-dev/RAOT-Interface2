<?php

require_once __DIR__ . '/../proposal/sap_budget_client.php';

class DashboardAnnualBudgetService
{
    private SapBudgetClient $sapClient;
    private array $config;

    public function __construct(SapBudgetClient $sapClient, array $config)
    {
        $this->sapClient = $sapClient;
        $this->config = $config;
    }

    /**
     * @return array{status:string,message?:string,data?:array,year?:int,sap_params?:array}
     */
    public function getAnnualBudgetReport(?int $year = null): array
    {
        $year = $year ?? $this->getCurrentFiscalYear();

        if ($year < 2000 || $year > 2100) {
            return [
                'status' => 'error',
                'message' => 'ปีงบประมาณไม่ถูกต้อง',
            ];
        }

        $dateRange = $this->buildFiscalYearDateRange($year);
        $sapParams = [
            'gjahr' => (string) $year,
            'rfikrs' => (string) ($this->config['rfikrs'] ?? '1000'),
            'str_date' => $dateRange['str_date'],
            'end_date' => $dateRange['end_date'],
        ];

        try {
            $sapResponse = $this->sapClient->fetchRemainingBudget($sapParams);
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อ SAP ได้',
                'year' => $year,
                'sap_params' => $sapParams,
            ];
        }

        if (($sapResponse['sap_status'] ?? 'E') !== 'S') {
            return [
                'status' => 'error',
                'message' => $sapResponse['message'] ?: 'SAP ตอบกลับด้วยสถานะ Error',
                'year' => $year,
                'sap_params' => $sapParams,
            ];
        }

        $rows = $sapResponse['data'] ?? [];
        $items = array_map(fn(array $row) => $this->mapReportRow($row, $year), $rows);

        return [
            'status' => 'success',
            'message' => $sapResponse['message'] ?: 'Success',
            'year' => $year,
            'sap_params' => $sapParams,
            'data' => $items,
            'empty' => $items === [],
        ];
    }

    private function mapReportRow(array $row, int $year): array
    {
        $gjahr = trim((string) ($row['gjahr'] ?? $year));
        $budget = $this->toFloat($row['budget'] ?? 0);
        $balance = $this->toFloat($row['balance'] ?? 0);

        return [
            'fiscal_year' => $this->formatFiscalYearDisplay($gjahr),
            'funds_center' => $this->formatCodeName($row['rfundsctr'] ?? '', $row['fund_center_name'] ?? ''),
            'fund' => $this->formatCodeName($row['rfund'] ?? '', $row['fund_name'] ?? ''),
            'functional_area' => $this->formatCodeName($row['rfuncarea'] ?? '', $row['fund_area_name'] ?? ''),
            'commitment_item' => $this->formatCodeName($row['rcmmtitem'] ?? '', $row['rcmmt_text'] ?? ''),
            'budget' => $budget,
            'balance' => $balance,
            'budget_formatted' => $this->formatCurrency($budget),
            'balance_formatted' => $this->formatCurrency($balance),
        ];
    }

    private function formatFiscalYearDisplay(string $gjahr): string
    {
        if (preg_match('/^\d{4}$/', $gjahr)) {
            return (string) ((int) $gjahr + 543);
        }

        return $gjahr;
    }

    private function formatCodeName($code, $name): string
    {
        $code = trim((string) $code);
        $name = trim((string) $name);

        if ($code !== '' && $name !== '') {
            return $code . ' - ' . $name;
        }

        if ($code !== '') {
            return $code;
        }

        return $name !== '' ? $name : '-';
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, '.', ',');
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

    /**
     * ปีงบประมาณไทย (1 ต.ค. - 30 ก.ย.) ใช้ปี ค.ศ. ที่สิ้นสุดในเดือนกันยายน
     */
    public function getCurrentFiscalYear(): int
    {
        $calendarYear = (int) date('Y');
        $month = (int) date('n');

        return $month >= 10 ? $calendarYear + 1 : $calendarYear;
    }

    /**
     * @return array{str_date:string,end_date:string}
     */
    public function buildFiscalYearDateRange(int $fiscalYear): array
    {
        return [
            'str_date' => ($fiscalYear - 1) . '1001',
            'end_date' => $fiscalYear . '0930',
        ];
    }
}
