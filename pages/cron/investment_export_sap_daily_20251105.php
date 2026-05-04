<?php

/**
 * SAP Export Cron Job
 *
 * Purpose: Export SAP Interface data to text file automatically
 * Usage: Can be called via cron job or direct URL
 *
 * Cron Example (daily at 2 AM):
 * 0 2 * * * /usr/bin/php /path/to/interface/cron/export_sap_daily.php
 *
 * URL Example:
 * http://localhost/interface/cron/export_sap_daily.php?key=YOUR_SECRET_KEY
 */

// Security: Check for secret key (prevent unauthorized access)
$secret_key = 'sap_export_2025'; // Change this to a secure random key
$provided_key = $_GET['key'] ?? '';

if (php_sapi_name() !== 'cli' && $provided_key !== $secret_key) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Include required files
require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../models/investment_budget/ib_requests_model.php';

// Set error logging
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/cron_' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);

/**
 * Log message with timestamp
 */
function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message");
    if (php_sapi_name() === 'cli') {
        echo "[$timestamp] $message\n";
    }
}

/**
 * Convert fiscal year from Buddhist to Gregorian
 */
function convertFiscalYearToAD($fiscalYearName)
{
    if (!$fiscalYearName) return '-';

    // Extract year (e.g., "2568" or "ปี 2568")
    if (preg_match('/(\d{4})/', $fiscalYearName, $matches)) {
        $buddhistYear = (int)$matches[1];
        $gregorianYear = $buddhistYear - 543;
        return (string)$gregorianYear;
    }

    return '-';
}

try {
    logMessage("=== Starting SAP Export Cron Job ===");

    // Connect to database
    $database = new Database();
    $db = $database->connect('raot_db_qas');
    $model = new IbRequestsModel($db);

    logMessage("Database connected successfully");

    // Fetch approved requests with SAP status = 'not_synced' for current month only
    // Note: Don't include 'current_user_id' or 'approval_scope' to bypass user filtering

    // Calculate current month date range
    $startDate = date('Y-m-01'); // First day of current month
    $endDate = date('Y-m-t'); // Last day of current month

    logMessage("Filtering data for current month: {$startDate} to {$endDate}");

    $filters = [
        'status' => 'approved',
        'sap_status' => 'not_synced',
        'start_date' => $startDate,
        'end_date' => $endDate
    ];

    $requests = $model->readRequests($filters);

    // Handle both array and paginated response
    if (is_array($requests)) {
        if (isset($requests['items'])) {
            $data = $requests['items'];
        } else {
            $data = $requests;
        }
    } else {
        $data = [];
    }

    logMessage("Found " . count($data) . " requests to export");

    if (empty($data)) {
        logMessage("No data to export. Exiting.");

        // Return JSON response for web requests
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'No data to export',
                'data' => [
                    'records_exported' => 0,
                    'reason' => 'No approved requests with sap_status = not_synced found'
                ]
            ]);
        }

        exit(0);
    }

    // Prepare export data
    $lines = [];
    $exportedIds = [];

    // Add header
    $lines[] = implode('|', [
        'ศูนย์เงินทุน',
        'เงินทุน',
        'ขอบเขตหน้าที่',
        'รายการภาระผูกพัน',
        'ปี',
        'ประเภทงบ',
        'จำนวนเงิน',
        'รายการ'
    ]);

    // Add data rows
    foreach ($data as $item) {
        if (!empty($item['id'])) {
            $exportedIds[] = $item['id'];
        }

        $fundCenter = $item['department_code'] ?? '';
        $fund = $item['budget_source_code'] ?? '';
        $functionalArea = $item['functional_area'] ?? '';
        $commitment = $item['asset_type_code'] ?? '';
        $fiscalYear = convertFiscalYearToAD($item['fiscal_year_name'] ?? '');
        $budgetType = '8';
        $amount = $item['total_amount'] ?? '0';
        $itemName = str_replace('|', '-', $item['item_name'] ?? '');

        $lines[] = implode('|', [
            $fundCenter,
            $fund,
            $functionalArea,
            $commitment,
            $fiscalYear,
            $budgetType,
            $amount,
            $itemName
        ]);
    }

    // Create file content
    $txtContent = implode("\n", $lines);

    // Create directory if not exists
    $baseDir = __DIR__ . '/../../storage/investment_budget/sap';
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
        logMessage("Created directory: $baseDir");
    }

    // Generate filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "SAP_Interface_{$timestamp}.txt";
    $filepath = "{$baseDir}/{$filename}";

    // Save file
    $result = file_put_contents($filepath, $txtContent);

    if ($result === false) {
        throw new Exception("Failed to save file to $filepath");
    }

    logMessage("File saved successfully: $filepath (size: $result bytes)");

    // Update SAP status to 'success'
    if (!empty($exportedIds)) {
        $updated = $model->updateSapStatus($exportedIds, 'success');
        if ($updated) {
            logMessage("Updated SAP status for " . count($exportedIds) . " requests");
        } else {
            logMessage("WARNING: Failed to update SAP status");
        }
    }

    logMessage("=== SAP Export Completed Successfully ===");

    // Return JSON response for web requests
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'SAP export completed',
            'data' => [
                'filepath' => "storage/investment_budget/sap/$filename",
                'filename' => $filename,
                'records_exported' => count($exportedIds),
                'file_size' => $result
            ]
        ]);
    }

    exit(0);
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());

    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit(1);
}
