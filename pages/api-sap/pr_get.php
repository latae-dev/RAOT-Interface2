<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include_once '../../configs/database.php';

// ===== MODEL =====
class RequestProposalModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getPending($status)
    {
        if (!$status) {
            return [];
        }

        $query = "
            SELECT 
                p.*,
                ft.form_type_code,
                ft.form_type_name,
                f.factory_code,
                f.factory_name,
                w.warehouse_sub,
                w.warehouse_name,
                pg.purchase_group_code,
                pg.purchase_group_name,
                t.tax_code,
                t.tax_name,
                gl.liability_code as gl_account_code,
                gl.liability_name as gl_account_name,
                cc.cost_center_code,
                cc.cost_center_name,
                fund.funding_code,
                fund.funding_name,
                s.scope_code,
                s.scope_name,
                cc_fund.cost_center_code as wrd_fund_code,
                cc_fund.cost_center_name as wrd_fund_name,
                l.liability_code as liability_code,
                l.liability_name as liability_name,
                bt.busa_code
            FROM \"request-proposal\".request_proposals p
            LEFT JOIN parameters.tb_form_types ft ON p.form_type_id = ft.id
            LEFT JOIN parameters.tb_factories f ON p.factory_id = f.id
            LEFT JOIN parameters.tb_warehouses w ON p.warehouse_id = w.id
            LEFT JOIN parameters.tb_purchase_groups pg ON p.purchase_group_id = pg.id
            LEFT JOIN parameters.tb_taxes t ON p.tax_id = t.id
            LEFT JOIN parameters.tb_liabilities_clone gl ON p.gl_account_id = gl.id
            LEFT JOIN parameters.tb_cost_centers cc ON p.cost_center_id = cc.id
            LEFT JOIN parameters.tb_fundings fund ON p.fund_id = fund.id
            LEFT JOIN parameters.tb_scopes s ON p.scope_id = s.id
            LEFT JOIN parameters.tb_liabilities_clone l ON p.liability_id = l.id
            LEFT JOIN parameters.tb_cost_centers cc_fund ON p.funds_center_id = cc_fund.id
            LEFT JOIN parameters.tb_busa_master bt ON NULLIF(TRIM(p.busa_area), '')::INTEGER = bt.id
            WHERE p.status = $1 
            AND p.sap_status = $2 
            AND p.is_deleted = false
        ";
        
        try {
            $sap_status = 'not_synced';
            $result = pg_query_params($this->conn, $query, [$status, $sap_status]);

            if (!$result) {
                $error = pg_last_error($this->conn);
                error_log("PostgreSQL Error in getPending: " . $error);
                throw new Exception($error);
            }

            $data = pg_fetch_all($result);

            if (!$data) {
                return [];
            }

            // แปลงค่าที่เป็น null เป็น string ว่างเปล่า
            // แปลง created_at และ updated_at เป็นรูปแบบ YYYY-MM-DD
            $data = array_map(function($row) {
                $newRow = [];
                foreach ($row as $key => $value) {
                    if ($key === 'created_at' || $key === 'updated_at') {
                        // แปลงวันที่เป็นรูปแบบ YYYY-MM-DD
                        if ($value && $value !== '') {
                            $date = new DateTime($value);
                            $newRow[$key] = $date->format('Y-m-d');
                        } else {
                            $newRow[$key] = '';
                        }
                    } else {
                        $newRow[$key] = $value === null ? '' : $value;
                    }
                }
                return $newRow;
            }, $data);

            return $data;

        } catch (Exception $e) {
            error_log("Error in getPending: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingWithItems($status)
    {
        if (!$status) {
            return [];
        }

        $query = "
            SELECT 
                p.*,
                ft.form_type_code,
                ft.form_type_name,
                f.factory_code,
                f.factory_name,
                w.warehouse_sub,
                w.warehouse_name,
                pg.purchase_group_code,
                pg.purchase_group_name,
                t.tax_code,
                t.tax_name,
                gl.liability_code as gl_account_code,
                gl.liability_name as gl_account_name,
                cc.cost_center_code,
                cc.cost_center_name,
                fund.funding_code,
                fund.funding_name,
                s.scope_code,
                s.scope_name,
                cc_fund.cost_center_code as wrd_fund_code,
                cc_fund.cost_center_name as wrd_fund_name,
                l.liability_code as liability_code,
                l.liability_name as liability_name,
                bt.busa_code,
                i.id as item_id,
                i.material_code,
                i.material_name,
                i.short_text,
                i.group_name as group_id,
                i.quantity,
                i.unit_code,
                i.unit_price,
                i.total_price,
                ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY i.id) as line_item,
                COALESCE(wg.code, '') as group_code,
                COALESCE(wg.name, '') as group_name
            FROM \"request-proposal\".request_proposals p
            LEFT JOIN parameters.tb_form_types ft ON p.form_type_id = ft.id
            LEFT JOIN parameters.tb_factories f ON p.factory_id = f.id
            LEFT JOIN parameters.tb_warehouses w ON p.warehouse_id = w.id
            LEFT JOIN parameters.tb_purchase_groups pg ON p.purchase_group_id = pg.id
            LEFT JOIN parameters.tb_taxes t ON p.tax_id = t.id
            LEFT JOIN parameters.tb_liabilities_clone gl ON p.gl_account_id = gl.id
            LEFT JOIN parameters.tb_cost_centers cc ON p.cost_center_id = cc.id
            LEFT JOIN parameters.tb_fundings fund ON p.fund_id = fund.id
            LEFT JOIN parameters.tb_scopes s ON p.scope_id = s.id
            LEFT JOIN parameters.tb_liabilities_clone l ON p.liability_id = l.id
            LEFT JOIN parameters.tb_cost_centers cc_fund ON p.funds_center_id = cc_fund.id
            LEFT JOIN parameters.tb_busa_master bt ON NULLIF(TRIM(p.busa_area), '')::INTEGER = bt.id
            LEFT JOIN \"request-proposal\".request_proposal_items i ON p.id = i.proposal_id
            LEFT JOIN parameters.tb_wrd_groups wg ON NULLIF(TRIM(i.group_name), '')::INTEGER = wg.id
            WHERE p.status = $1 
            AND p.sap_status = $2 
            AND p.is_deleted = false
            AND i.id IS NOT NULL
            ORDER BY p.id, i.id
        ";
        
        try {
            $sap_status = 'not_synced';
            $result = pg_query_params($this->conn, $query, [$status, $sap_status]);

            if (!$result) {
                $error = pg_last_error($this->conn);
                error_log("PostgreSQL Error in getPendingWithItems: " . $error);
                throw new Exception($error);
            }

            $data = pg_fetch_all($result);

            if (!$data) {
                return [];
            }

            // แปลงค่าที่เป็น null เป็น string ว่างเปล่า และแปลง line_item เป็น integer
            // แปลง created_at และ updated_at เป็นรูปแบบ YYYY-MM-DD
            $data = array_map(function($row) {
                $newRow = [];
                foreach ($row as $key => $value) {
                    if ($key === 'line_item') {
                        // แปลง line_item เป็น integer
                        $newRow[$key] = (int)$value;
                    } elseif ($key === 'created_at' || $key === 'updated_at') {
                        // แปลงวันที่เป็นรูปแบบ YYYY-MM-DD
                        if ($value && $value !== '') {
                            $date = new DateTime($value);
                            $newRow[$key] = $date->format('Y-m-d');
                        } else {
                            $newRow[$key] = '';
                        }
                    } else {
                        $newRow[$key] = $value === null ? '' : $value;
                    }
                }
                return $newRow;
            }, $data);

            return $data;

        } catch (Exception $e) {
            error_log("Error in getPendingWithItems: " . $e->getMessage());
            return [];
        }
    }

    public function updateSapStatus($ids, $status)
    {
        if (empty($ids) || !is_array($ids) || empty($status)) {
            return false;
        }

        $query = "
            UPDATE \"request-proposal\".request_proposals 
            SET sap_status = $1, status = $2, updated_at = now()
            WHERE id = ANY ($3::int[])
            AND is_deleted = false
        ";

        try {
            $sap_status = 'synced';
            $result = pg_query_params($this->conn, $query, [$sap_status, $status, '{' . implode(',', $ids) . '}']);

            if (!$result) {
                throw new Exception(pg_last_error($this->conn));
            }

            $rows_affected = pg_affected_rows($result);
            return $rows_affected > 0;

        } catch (Exception $e) {
            error_log("Error in updateSapStatus: " . $e->getMessage());
            return false;
        }
    }
}

// ===== CONTROLLER =====
class RequestProposalApiController
{
    private $proposalModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        
        if (!$db) {
            http_response_code(500);
            echo json_encode([
                "error" => "Database connection failed"
            ]);
            exit;
        }
        
        $this->proposalModel = new RequestProposalModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;

            case 'PUT':
                $this->handlePut();
                break;

            default:
                http_response_code(405);
                echo json_encode([
                    "error" => "Method not allowed"
                ]);
                break;
        }
    }

    private function handleGet()
    {
        try {
            $status = isset($_GET['status']) ? $_GET['status'] : 'approved';
            $withItems = isset($_GET['with_items']) && $_GET['with_items'] === 'true';

            // ใช้ getPendingWithItems เสมอเพื่อให้ได้ข้อมูลในรูปแบบ line item แยกแถว
            $result = $this->proposalModel->getPendingWithItems($status);

            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error in handleGet: " . $e->getMessage());
            echo json_encode([
                "error" => "Internal server error",
                "message" => $e->getMessage()
            ]);
        }
    }

    private function handlePut()
    {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (empty($data['id']) || !is_array($data['id']) || empty($status)) {
            echo json_encode([]);
            return;
        }

        $ids = $data['id'];
        $success = $this->proposalModel->updateSapStatus($ids, $status);

        if ($success) {
            echo json_encode([
                "updated" => true,
                "rows_affected" => count($ids)
            ]);
        } else {
            echo json_encode([]);
        }
    }
}

// ===== เรียกใช้งาน =====
$controller = new RequestProposalApiController();
$controller->processRequest();
?>
