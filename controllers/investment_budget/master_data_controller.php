<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);

include_once '../../configs/authMiddleware.php';
header('Content-Type: application/json');

include_once '../../configs/database.php';

class InvestmentBudgetMasterDataController
{
    private $db;

    /**
     * Mapping configuration for each master data type.
     * Each type defines the model file, class and supported actions.
     *
     * @var array<string, array<string, mixed>>
     */
    private $config = [
        'fiscal_year' => [
            'model' => 'fiscal_year_model.php',
            'class' => 'FiscalYearModel',
            'actions' => [
                'all' => ['method' => 'readFiscalYear'],
                'one' => ['method' => 'readFiscalYearOne', 'params' => ['id']],
                'by_name' => ['method' => 'readFiscalYearByName', 'params' => ['name']],
            ],
        ],
        'budget_source' => [
            'model' => 'budget_source_model.php',
            'class' => 'BudgetSourceModel',
            'actions' => [
                'all' => ['method' => 'readBudgetSource'],
                'one' => ['method' => 'readBudgetSourceOne', 'params' => ['id']],
                'by_type' => ['method' => 'readBudgetSourceByType', 'params' => ['type']],
            ],
        ],
        'version_plan' => [
            'model' => 'ib_version_plan_model.php',
            'class' => 'IbVersionPlanModel',
            'actions' => [
                'all' => ['method' => 'readVersionPlan'],
                'one' => ['method' => 'readVersionPlanOne', 'params' => ['id']],
                'by_code' => ['method' => 'readVersionPlanByCode', 'params' => ['code']],
                'current' => ['method' => 'getCurrentVersionPlan'],
            ],
        ],
        'asset_type' => [
            'model' => 'asset_types_model.php',
            'class' => 'AssetTypesModel',
            'actions' => [
                'all' => ['method' => 'readAssetTypes'],
                'one' => ['method' => 'readAssetTypeOne', 'params' => ['id']],
                'by_code' => ['method' => 'readAssetTypeByCode', 'params' => ['code']],
            ],
        ],
        'strategy' => [
            'model' => 'enterprise_plan_strategic_model.php',
            'class' => 'EnterprisePlanStrategicModel',
            'actions' => [
                'all' => ['method' => 'readEnterprisePlanStrategic'],
                'one' => ['method' => 'readEnterprisePlanStrategicOne', 'params' => ['id']],
            ],
        ],
        'equipment_standard' => [
            'model' => 'equipment_standards_model.php',
            'class' => 'EquipmentStandardsModel',
            'actions' => [
                'all' => ['method' => 'readEquipmentStandards'],
                'one' => ['method' => 'readEquipmentStandardsOne', 'params' => ['id']],
            ],
        ],
        'equipment_setup' => [
            'model' => 'equipment_setup_model.php',
            'class' => 'EquipmentSetupModel',
            'actions' => [
                'all' => ['method' => 'readEquipmentSetup'],
                'one' => ['method' => 'readEquipmentSetupOne', 'params' => ['id']],
            ],
        ],
        'equipment_code_type' => [
            'model' => 'equipment_code_type_model.php',
            'class' => 'EquipmentCodeTypeModel',
            'actions' => [
                'all' => ['method' => 'readEquipmentCodeType'],
                'one' => ['method' => 'readEquipmentCodeTypeOne', 'params' => ['id']],
            ],
        ],
        'equipment_type' => [
            'model' => 'equipment_type_model.php',
            'class' => 'EquipmentTypeModel',
            'actions' => [
                'all' => ['method' => 'readEquipmentType'],
                'one' => ['method' => 'readEquipmentTypeOne', 'params' => ['id']],
                'by_code' => ['method' => 'readEquipmentTypeByCode', 'params' => ['code']],
                'by_asset_category' => ['method' => 'readEquipmentTypeByAssetCategory', 'params' => ['asset_category']],
            ],
        ],
        'asset_category' => [
            'model' => 'asset_category_model.php',
            'class' => 'AssetCategoryModel',
            'actions' => [
                'all' => ['method' => 'readAssetCategory'],
                'one' => ['method' => 'readAssetCategoryOne', 'params' => ['id']],
                'by_code' => ['method' => 'readAssetCategoryByCode', 'params' => ['code']],
                'by_filters' => ['method' => 'readAssetCategoryByFilters', 'params' => ['asset_type_id', 'installment_work_id']],
            ],
        ],
        'disbursement_plan' => [
            'model' => 'disbursement_plan_model.php',
            'class' => 'DisbursementPlanModel',
            'actions' => [
                'all' => ['method' => 'readDisbursementPlan'],
                'one' => ['method' => 'readDisbursementPlanOne', 'params' => ['id']],
            ],
        ],
        'installment_work' => [
            'model' => 'installment_work_model.php',
            'class' => 'InstallmentWorkModel',
            'actions' => [
                'all' => ['method' => 'readInstallmentWork'],
                'one' => ['method' => 'readInstallmentWorkOne', 'params' => ['id']],
            ],
        ],
        'project_period' => [
            'model' => 'project_period_model.php',
            'class' => 'ProjectPeriodModel',
            'actions' => [
                'all' => ['method' => 'readProjectPeriod'],
                'one' => ['method' => 'readProjectPeriodOne', 'params' => ['id']],
            ],
        ],
        'implementation_year' => [
            'model' => 'implementation_year_model.php',
            'class' => 'ImplementationYearModel',
            'actions' => [
                'all' => ['method' => 'readImplementationYear'],
                'one' => ['method' => 'readImplementationYearOne', 'params' => ['id']],
            ],
        ],
        'fund_area' => [
            'model' => 'fund_area_model.php',
            'class' => 'FundAreaModel',
            'actions' => [
                'all' => ['method' => 'readFundArea'],
                'one' => ['method' => 'readFundAreaOne', 'params' => ['id']],
            ],
        ],
    ];

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect('raot_db_qas');
    }

    public function handle()
    {
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $action = isset($_GET['action']) ? trim($_GET['action']) : 'all';

        if (!$type) {
            return $this->respondError('Missing required parameter: type', 400);
        }

        if (!isset($this->config[$type])) {
            return $this->respondError('Unsupported master data type', 400);
        }

        $typeConfig = $this->config[$type];
        $actions = $typeConfig['actions'] ?? [];

        if (!isset($actions[$action])) {
            return $this->respondError('Unsupported action for master data type', 400);
        }

        $actionConfig = $actions[$action];
        $method = $actionConfig['method'];
        $paramKeys = $actionConfig['params'] ?? [];

        $modelFile = $typeConfig['model'];
        $modelClass = $typeConfig['class'];

        $modelPath = __DIR__ . '/../../models/investment_budget/' . $modelFile;
        if (!file_exists($modelPath)) {
            error_log("Master data controller: missing model file {$modelPath}");
            return $this->respondError('Server configuration error', 500);
        }

        include_once $modelPath;

        if (!class_exists($modelClass)) {
            error_log("Master data controller: missing model class {$modelClass}");
            return $this->respondError('Server configuration error', 500);
        }

        $modelInstance = new $modelClass($this->db);

        if (!method_exists($modelInstance, $method)) {
            error_log("Master data controller: missing method {$method} in {$modelClass}");
            return $this->respondError('Server configuration error', 500);
        }

        $params = [];
        foreach ($paramKeys as $key) {
            if (!isset($_GET[$key])) {
                return $this->respondError("Missing required parameter: {$key}", 400);
            }
            $params[] = $_GET[$key];
        }

        try {
            $result = call_user_func_array([$modelInstance, $method], $params);
        } catch (Throwable $th) {
            error_log("Master data controller: exception calling {$method} :: " . $th->getMessage());
            return $this->respondError('An error occurred while retrieving data.', 500);
        }

        if ($result === false || $result === null) {
            return $this->respondError('No data found or an error occurred.', 404);
        }

        // Standardize result to array when possible
        return $this->respondSuccess($result);
    }

    private function respondSuccess($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'success',
            'data' => $data,
        ]);
        return null;
    }

    private function respondError(string $message, int $statusCode = 400)
    {
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
        return null;
    }
}

$controller = new InvestmentBudgetMasterDataController();
$controller->handle();
