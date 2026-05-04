<?php
    include_once __DIR__ . '/../../configs/init.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_model.php';

    if (session_id() === '') {
        session_start();
    }
class BusinessBudgetApprovelController
{
    private $model;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->model = new BusinessBudgetModel($db);
    }

    public function getBusinessApproveList($param = []) 
    {
        return $this->model->getBusinessApproveList($param);
    }

    public function getDepart()
    {
        return $this->model->getDepart();
    }

    public function getApproveHistory($id)
    {
        return $this->model->getApproveHistory($id);
    }

    public function approve($post)
    {
        return $this->model->approve($post);
    }

    public function reject($post)
    {
        return $this->model->reject($post);
    }   
}

$controller = new BusinessBudgetApprovelController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;
    
    $user = $_SESSION['user_data'];

    $post['approved_by'] = $user['id'];

    if(isset($post['approve_status']) && $post['approve_status'] == 'approve') {
        if($post['position_code'] == 'APPRP' || $post['position_code'] == 'APSTR') {
            $post['status'] = 'approved';
        } else if(isset($post['depart_code']) && !empty($post['depart_code']) && $post['position_code'] == 'APAR') {
            $post['responsibilities_approves'] = $post['depart_code'];
            $post['approve_position_code'] = getNextApprove('APPRP');
        } else {
            $post['approve_position_code'] = getNextApprove($post['position_code']);
        }
        
        $result = $controller->approve($post);
    } else if(isset($post['approve_status']) && $post['approve_status'] == 'reject') {
        $post['status'] = 'rejected';

        $result = $controller->reject($post);
    }

    if ($result) {
        header("Location: ".BASE_URL."/pages/business-budget-approvel-list.php");
        exit();
    } else {
        arrx($result);
    }
}