<?php
// Start output buffering before any output
ob_start();

// เรียกใช้ middleware สำหรับ authentication
include_once '../../configs/authMiddleware.php';

require_once __DIR__ . '/../../configs/database.php';

class ExportReportsController
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    /**
     * Export Report 1: รายงานคำขอตั้งงบลงทุน
     */
    public function exportReport1($filters = [])
    {
        try {
            $data = $this->getReport1Data($filters);

            $headers = [
                'branch_name' => 'หน่วยงาน',
                'item_name' => 'รายการ',
                'quantity' => 'จำนวนหน่วย',
                'unit_price' => 'ราคาต่อหน่วย',
                'has_standard' => 'มาตรฐานครุภัณฑ์',
                'total_amount' => 'รวมเงิน (บาท)',
                'reason' => 'เหตุผลและความจำเป็น',
                'equipment_code_type_code' => 'รหัสครุภัณฑ์ (ชนิด)',
                'equipment_type_code' => 'รหัสครุภัณฑ์ (ประเภท)'
            ];

            $this->outputHtmlTable('รายงานคำขอตั้งงบลงทุน_' . date('Y-m-d_His') . '.xls', $headers, $data);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Export Report 2: รายงาน SAP Interface
     */
    public function exportReport2($filters = [])
    {
        try {
            $rawData = $this->getReport2Data($filters);

            $headers = [
                'functional_area' => 'ศูนย์เงินทุน',
                'commitment_item' => 'รายการภาระผูกพัน',
                'fiscal_year' => 'ปี',
                'item_name' => 'รายการ',
                'quantity' => 'จำนวน',
                'unit_price' => 'ราคาต่อหน่วย',
                'total_amount' => 'รวมเงิน',
                'sap_status' => 'สถานะ Interface',
                'sap_remark' => 'หมายเหตุ'
            ];

            $data = array_map(function ($row) {
                return [
                    'functional_area' => '10000',
                    'commitment_item' => $row['commitment_item'] ?? '-',
                    'fiscal_year' => substr($row['fiscal_year_name'] ?? '', 0, 4),
                    'item_name' => $row['item_name'] ?? '-',
                    'quantity' => $row['quantity'] ?? '-',
                    'unit_price' => $row['unit_price'] ?? '-',
                    'total_amount' => $row['total_amount'] ?? '-',
                    'sap_status' => $row['sap_status'] ?? '-',
                    'sap_remark' => $row['sap_remark'] ?? '-'
                ];
            }, $rawData);

            $this->outputHtmlTable('รายงาน_SAP_Interface_' . date('Y-m-d_His') . '.xls', $headers, $data);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Export Report 3: รายงาน Asset
     */
    public function exportReport3($filters = [])
    {
        try {
            $data = $this->getReport3Data($filters);

            $headers = [
                'branch_name' => 'หน่วยงาน',
                'item_name' => 'รายการ',
                'quantity' => 'จำนวน',
                'unit_price' => 'ราคาต่อหน่วย',
                'total_amount' => 'รวมเงิน',
                'equipment_code_type_code' => 'รหัสครุภัณฑ์ (ชนิด)',
                'equipment_type_code' => 'รหัสครุภัณฑ์ (ประเภท)',
                'count_unit_name' => 'หน่วยนับ',
                'account_code' => 'รหัสบัญชี'
            ];

            $this->outputHtmlTable('รายงาน_Asset_' . date('Y-m-d_His') . '.xls', $headers, $data);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    private function getReport1Data($filters)
    {
        $where = ['r.is_deleted = false', "r.status = 'approved'"];
        $params = [];
        $param_count = 0;

        if (!empty($filters['request_number'])) {
            $param_count++;
            $where[] = 'r.request_number ILIKE $' . $param_count;
            $params[] = '%' . $filters['request_number'] . '%';
        }

        if (!empty($filters['start_date'])) {
            $param_count++;
            $where[] = 'r.created_at >= $' . $param_count;
            $params[] = $filters['start_date'] . ' 00:00:00';
        }

        if (!empty($filters['end_date'])) {
            $param_count++;
            $where[] = 'r.created_at <= $' . $param_count;
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        if (!empty($filters['fiscal_year_id'])) {
            $param_count++;
            $where[] = 'r.fiscal_year_id = $' . $param_count;
            $params[] = $filters['fiscal_year_id'];
        }

        $where_clause = implode(' AND ', $where);

        $query = "SELECT r.*,
                         b.branch_name,
                         ect.code AS equipment_code_type_code,
                         et.code AS equipment_type_code,
                         CASE WHEN r.has_standard::text IN ('1','true') THEN 'มีมาตรฐาน' ELSE 'ไม่มีมาตรฐาน' END AS has_standard
                  FROM investment_budget.tb_ib_requests r
                  LEFT JOIN users.tb_users u ON r.created_by::text = u.id::text
                  LEFT JOIN users.tb_branchs b ON b.id = u.branch_id
                  LEFT JOIN investment_budget.tb_equipment_code_type ect ON r.equipment_code_type_id = ect.id
                  LEFT JOIN investment_budget.tb_equipment_type et ON r.equipment_type_id = et.id
                  WHERE {$where_clause}
                  ORDER BY r.created_at DESC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception('Database error: ' . pg_last_error($this->conn));
        }

        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    private function getReport2Data($filters)
    {
        return $this->getReport1Data($filters); // ใช้โครงสร้างข้อมูลเดียวกันสำหรับตัวอย่าง
    }

    private function getReport3Data($filters)
    {
        return $this->getReport1Data($filters); // ใช้โครงสร้างข้อมูลเดียวกันสำหรับตัวอย่าง
    }

    private function outputHtmlTable($filename, array $headers, array $rows)
    {
        // Clear any previous output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Change extension from .xls to .csv
        $filename = preg_replace('/\.xls$/i', '.csv', $filename);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output UTF-8 BOM for Excel to recognize Thai characters
        echo "\xEF\xBB\xBF";

        // Open output stream
        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array_values($headers));

        // Write data rows
        if (empty($rows)) {
            fputcsv($output, ['ไม่พบข้อมูล']);
        } else {
            foreach ($rows as $row) {
                $rowData = [];
                foreach ($headers as $field => $_label) {
                    $rowData[] = $row[$field] ?? '';
                }
                fputcsv($output, $rowData);
            }
        }

        fclose($output);
        exit;
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $database = new Database();
    $conn = $database->connect('raot_db_qas');

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $controller = new ExportReportsController($conn);
    $action = $_GET['action'] ?? '';

    $filters = [
        'request_number' => $_GET['request_number'] ?? '',
        'start_date' => $_GET['start_date'] ?? '',
        'end_date' => $_GET['end_date'] ?? '',
        'fiscal_year_id' => $_GET['fiscal_year_id'] ?? ''
    ];

    switch ($action) {
        case 'export_report1':
            $controller->exportReport1($filters);
            break;
        case 'export_report2':
            $controller->exportReport2($filters);
            break;
        case 'export_report3':
            $controller->exportReport3($filters);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
