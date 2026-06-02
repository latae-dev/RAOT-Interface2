<?php

if (!defined('BUDGETS_TB_BUSINESS_BUDGETS')) {
    require_once __DIR__ . '/../../configs/constants.php';
}

class DashboardDocumentRepository
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * สถิติเอกสารรายเดือน เดือน 1–12 ของปีปัจจุบัน (อนุมัติแล้ว / รอดำเนินการ)
     *
     * @return array{labels: string[], month_labels: string[], year: int, approved: int[], pending: int[]}
     */
    public function getMonthlyDocumentTrends(): array
    {
        $currentYear = (int) date('Y');
        $thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $labels = [];
        $monthLabels = [];
        $approved = array_fill(0, 12, 0);
        $pending = array_fill(0, 12, 0);

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = (string) $month;
            $monthLabels[] = $thaiMonths[$month - 1] . ' ' . ($currentYear + 543);
        }

        $query = "SELECT
                    EXTRACT(MONTH FROM doc.created_at)::integer AS month_num,
                    COUNT(*) FILTER (
                        WHERE LOWER(doc.status) IN ('approved', 'approve')
                           OR LOWER(doc.status) LIKE '%approved%'
                    )::integer AS approved_count,
                    COUNT(*) FILTER (
                        WHERE LOWER(doc.status) NOT IN ('approved', 'approve', 'draft', 'delete', 'deleted', 'rejected', 'reject')
                           AND (
                               LOWER(doc.status) LIKE 'pending%'
                               OR LOWER(doc.status) IN ('waiting', 'submitted', 'pending')
                           )
                    )::integer AS pending_count
                  FROM (
                      SELECT created_at, status::text AS status
                      FROM " . BUDGETS_TB_BUSINESS_BUDGETS . "
                      WHERE deleted_at IS NULL AND status IS NOT NULL AND status != 'draft'
                      UNION ALL
                      SELECT created_at, status::text AS status
                      FROM investment_budget.tb_ib_requests
                      WHERE is_deleted = FALSE AND status IS NOT NULL AND status != 'draft'
                      UNION ALL
                      SELECT created_at, status_plan::text AS status
                      FROM parcels.tb_plans
                      WHERE status_plan IS NOT NULL AND status_plan NOT IN ('delete', 'draft')
                      UNION ALL
                      SELECT created_at, status::text AS status
                      FROM \"request-proposal\".request_proposals
                      WHERE is_deleted = FALSE AND status IS NOT NULL AND status NOT IN ('draft', 'deleted')
                  ) AS doc
                  WHERE doc.created_at >= date_trunc('year', CURRENT_DATE)
                    AND doc.created_at < (date_trunc('year', CURRENT_DATE) + INTERVAL '1 year')
                  GROUP BY 1
                  ORDER BY 1";

        $result = @pg_query($this->conn, $query);
        if (!$result) {
            return [
                'labels' => $labels,
                'month_labels' => $monthLabels,
                'year' => $currentYear,
                'approved' => $approved,
                'pending' => $pending,
            ];
        }

        while ($row = pg_fetch_assoc($result)) {
            $monthNum = (int) ($row['month_num'] ?? 0);
            if ($monthNum < 1 || $monthNum > 12) {
                continue;
            }
            $idx = $monthNum - 1;
            $approved[$idx] = (int) ($row['approved_count'] ?? 0);
            $pending[$idx] = (int) ($row['pending_count'] ?? 0);
        }

        return [
            'labels' => $labels,
            'month_labels' => $monthLabels,
            'year' => $currentYear,
            'approved' => $approved,
            'pending' => $pending,
        ];
    }

    /**
     * จำนวนรายการที่สร้างทั้งหมดในแต่ละระบบ (ไม่รวมรายการที่ถูกลบ)
     *
     * @return array{parcels: int, withdraw_money: int, business_budget: int, investment_budget: int, request_proposal: int}
     */
    public function getModuleDocumentCounts(): array
    {
        $defaults = [
            'parcels' => 0,
            'withdraw_money' => 0,
            'business_budget' => 0,
            'investment_budget' => 0,
            'request_proposal' => 0,
        ];

        $query = "SELECT
                    (SELECT COUNT(*)::integer FROM parcels.tb_plans
                     WHERE doc_type = 'master' AND status_plan != 'delete') AS parcels,
                    (SELECT COUNT(*)::integer FROM withdraws.tb_wrd_plans
                     WHERE status_plan != 'delete') AS withdraw_money,
                    (SELECT COUNT(*)::integer FROM " . BUDGETS_TB_BUSINESS_BUDGETS . "
                     WHERE deleted_at IS NULL) AS business_budget,
                    (SELECT COUNT(*)::integer FROM investment_budget.tb_ib_requests
                     WHERE is_deleted = FALSE) AS investment_budget,
                    (SELECT COUNT(*)::integer FROM \"request-proposal\".request_proposals
                     WHERE is_deleted = FALSE) AS request_proposal";

        $result = @pg_query($this->conn, $query);
        if (!$result) {
            return $defaults;
        }

        $row = pg_fetch_assoc($result);
        if (!$row) {
            return $defaults;
        }

        foreach ($defaults as $key => $value) {
            $defaults[$key] = (int) ($row[$key] ?? 0);
        }

        return $defaults;
    }
}
