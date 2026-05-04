<?php
ini_set('log_errors', 1);
$logFile = '../../logs/' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0); // ปิดการแสดงผล
header("Content-Type: application/json");
include_once '../configs/database.php';

include_once '../models/withdraws/wrd_expenses_model.php';
include_once '../models/withdraws/wrd_expenses_list_model.php';
include_once '../models/withdraws/wrd_expenses_group_model.php';
include_once '../models/withdraws/wrd_expenses_number_model.php';
include_once '../models/withdraws/wrd_expenses_approve_history.php';
include_once '../models/withdraws/wrd_plan_model.php';
include_once '../models/withdraws/wrd_plan_list_model.php';
class WrdWithdrawApi
{
    private $expensesModel;
    private $expensesNumberModel;
    private $expensesListModel;
    private $expensesGroupModel;
    private $WrdExpensesApproveHistoryModel;
    private $wrdPlanModel;
    private $planListModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->expensesModel = new ExpensesModel($db);
        $this->expensesNumberModel = new ExpensesNumberModel($db);
        $this->expensesListModel = new ExpensesListModel($db);
        $this->expensesGroupModel = new ExpensesGroupModel($db);
        $this->WrdExpensesApproveHistoryModel = new WrdExpensesApproveHistoryModel($db);
        $this->wrdPlanModel = new WrdPlanModel($db);
        $this->planListModel = new PlanListModel($db);
    }

    public function processRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $expensesModel = $this->expensesModel->getExpensesByParentId('', $_GET['is_type_req'], '', $_GET['expense_cat']);
                foreach ($expensesModel as $key => $expense) {
                    $expensesModel[$key]['expense_items'] = $this->expensesModel->getExpensesByParentId($expense['id'], '', $_GET['is_foreign'], $_GET['expense_cat']);
                }
                $item = [];
                foreach ($expensesModel as $key => $value) {
                    $item[$key]['id'] = (int)$value['id'];
                    $item[$key]['document_date'] = $value['expenses_start'];
                    $item[$key]['posting_date'] = $value['updated_at'];
                    $item[$key]['reference'] = $value['expenses_number'];
                    $item[$key]['account'] = $value['req_user_code'];
                    $item[$key]['cost_center'] = $value['depart_code'];
                    if ($value['plan_number']) {
                        $item[$key]['business_area'] = $value['fund_code'];
                        $item[$key]['fund'] = $value['fund_rcode'];
                        $item[$key]['functional_area'] = $value['project_code'];
                    } else {
                        $item[$key]['business_area'] = $value['fund_code_2'];
                        $item[$key]['fund'] = $value['fund_rcode_2'];
                        $item[$key]['functional_area'] = $value['project_code_2'];
                    }
                    $item[$key]['plan_number'] = $value['plan_number'];
                    if ($_GET['is_foreign'] == $value['is_foreign']) {
                        $item[$key]['expenses_allowance_total'] = $value['expenses_allowance_total'];
                        $item[$key]['expenses_accommodation_total'] = $value['expenses_accommodation_total'];
                        $item[$key]['expenses_transportation_total'] = $value['expenses_transportation_total'];
                        $item[$key]['expenses_other_total'] = $value['expenses_other_total'];
                        $item[$key]['expenses_food_total'] = $value['expenses_food_total'];
                        $item[$key]['expenses_moving_total'] = $value['expenses_moving_total'];
                        $item[$key]['compensation_total'] = $value['total_compensation'];
                    } else {
                        $item[$key]['expenses_allowance_total'] = 0;
                        $item[$key]['expenses_accommodation_total'] = 0;
                        $item[$key]['expenses_transportation_total'] = 0;
                        $item[$key]['expenses_other_total'] = 0;
                        $item[$key]['expenses_food_total'] = 0;
                        $item[$key]['expenses_moving_total'] = 0;
                        $item[$key]['compensation_total'] = 0;
                    }
                    foreach ($value['expense_items'] as $item_key => $item_value) {
                        $item[$key]['expenses_allowance_total'] += $item_value['expenses_allowance_total'];
                        $item[$key]['expenses_allowance_total'] += $item_value['total_allowance_group'];
                        $item[$key]['expenses_accommodation_total'] += $item_value['expenses_accommodation_total'];
                        $item[$key]['expenses_accommodation_total'] += $item_value['total_accommodation_group'];
                        $item[$key]['expenses_transportation_total'] += $item_value['expenses_transportation_total'];
                        $item[$key]['expenses_transportation_total'] += $item_value['total_transport_group'];
                        $item[$key]['expenses_other_total'] += $item_value['expenses_other_total'];
                        $item[$key]['expenses_other_total'] += $item_value['total_other_group'];
                        $item[$key]['expenses_food_total'] += $item_value['expenses_food_total'];
                        $item[$key]['expenses_food_total'] += $item_value['total_meal_group'];
                        $item[$key]['expenses_moving_total'] += $item_value['expenses_moving_total'];
                        $item[$key]['compensation_total'] += $item_value['total_compensation_group'];
                    }
                }
                echo json_encode($item);
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method Not Allowed']);
                break;
        }
    }
}

$dataController = new WrdWithdrawApi();
$dataController->processRequest();
