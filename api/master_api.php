<?php
    include_once __DIR__ . '/../configs/init.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_model.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_model.php';

class masterAPI
{
    private $model;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');
        $this->model = new BusinessBudgetModel($db);
    }

    public function getTargetStrategies($id)
    {
        return $this->model->getTargetStrategies($id);
    }

    public function getSubjectStrategies($id)
    {
        return $this->model->getSubjectStrategies($id);
    }

    public function getTargetLevelSubjects($id)
    {
        return $this->model->getTargetLevelSubjects($id);
    }

    public function getSubPlans($id)
    {
        return $this->model->getSubPlans($id);
    }

    public function getTargetSubPlans($id)
    {
        return $this->model->getTargetSubPlans($id);
    }

    public function getSubIndicators($id)
    {
        return $this->model->getSubIndicators($id);
    }

    public function getTactics($id)
    {
        return $this->model->getTactics($id);
    }

    public function getProjectActivity($projectCode)
    {
        return $this->model->getProjectActivity($projectCode);
    }   

    public function getRiskStrategiesIndicators($id)
    {
        return $this->model->getRiskStrategiesIndicators($id);
    }

    public function getRiskStrategies($id)
    {
        return $this->model->getRiskStrategies($id);
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $param = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['code']) ? $_GET['code'] : null);

    $dataController = new MasterAPI();

    try {
        if (method_exists($dataController, $action)) {
            $result = $dataController->$action($param);

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['error' => "Invalid action: $action"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}