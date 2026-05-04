<?php
    include_once __DIR__ . '/../../configs/init.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_model.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_model.php';

    if (session_id() === '') {
        session_start();
    }

class BusinessBudgetController
{
    private $model;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_dev');
        $this->model = new BusinessBudgetModel($db);
    }

    public function getStrategies() 
    {
        return $this->model->getStrategies();
    }

    public function getProject() 
    {
        return $this->model->getProject();
    }

    public function getBudgetSource()
    {
        return $this->model->getBudgetSource();
    }

    public function getProjectActivity($projectCode)
    {
        return $this->model->getProjectActivity($projectCode);
    }

    public function getPlanUnderStrategies() 
    {
        return $this->model->getPlanUnderStrategies();
    }

    public function getPlanStrategies() 
    {
        return $this->model->getPlanStrategies();
    }

    public function getBusinessList($param = []) 
    {
        return $this->model->getBusinessList($param);
    }

    public function getBusinessBudgetById($id) 
    {
        return $this->model->getBusinessBudgetById($id);
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

    public function getRiskStrategicObjectives() 
    {
        return $this->model->getRiskStrategicObjectives();
    }

    public function getRiskNationalStrategies() 
    {
        return $this->model->getRiskNationalStrategies();
    }

    public function getRiskStrategiesIndicators($id)
    {
        return $this->model->getRiskStrategiesIndicators($id);
    }

    public function getRiskStrategies($id)
    {
        return $this->model->getRiskStrategies($id);
    }

    public function create($payload)
    {
        try {
            $id = $this->model->insertBusinessBudget($payload);

            if ($id) {
                return [
                    "success" => true,
                    "id" => $id,
                    "message" => "Business budget created successfully"
                ];
            }

            return [
                "success" => false,
                "message" => "Failed to create business budget"
            ];
        } catch (Exception $e) {
            error_log("Controller create error: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Update existing business budget
     */
    public function update($id, $payload)
    {
        try {
            $ok = $this->model->updateBusinessBudget($id, $payload);

            if ($ok) {
                return [
                    "success" => true,
                    "message" => "Business budget updated successfully"
                ];
            }

            return [
                "success" => false,
                "message" => "Failed to update business budget"
            ];
        } catch (Exception $e) {
            error_log("Controller update error: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        return $this->model->delete($id);
    }
}

$controller = new BusinessBudgetController();
$controller->getStrategies();
$controller->getProject();
$controller->getPlanUnderStrategies();
$controller->getPlanStrategies();
$controller->getProjectActivity('');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);

    if ($id > 0) {
        $deletedId = $controller->delete($id);

        if ($deletedId) {
            $qs = $_GET;
            unset($qs['action'], $qs['id']);
            $redirectQuery = http_build_query($qs);

            $url = BASE_URL."/pages/business-budget-list.php";

            if ($redirectQuery) {
                $url .= "?" . $redirectQuery;
            }

            header("Location: $url");
            exit();
        } else {
            arrx("Delete failed");
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    $userData = $_SESSION['user_data'];

    if(!$userData) {
        header("Location: ".BASE_URL."/pages/login.php");
    }

    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){

        $file = $_FILES['image'];

        $allowed = ['doc','docx','zip','xls','xlsx','pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)){
            die("ไฟล์ไม่ถูกต้อง");
        }

        $ymd = date('Ymd');
        $uploadDir = $rootPath . "/files/buisness-budget/$ymd/";
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . rand(1000,9999) . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if(move_uploaded_file($file['tmp_name'], $filepath)){
            $image_path = "files/buisness-budget/$ymd/$filename";

            
            $data['image_path'] = $image_path;
        }
    } 

    if($data['action'] == 'draft') {
        $data['status'] = 'draft';
    } else if($data['action'] == 'waiting') {
        $data['status'] = 'waiting';
        
        $versionApprove = getVersionApprove($data['version_name']);

        $data['approve_position_code'] = $versionApprove;

        /*if($userData['position_code'] != 'EMPY' && $userData['position_code'] != 'APPRP') {
            $data['approve_position_code'] = $userData['position_code'];
        } else if($userData['position_code'] == 'APPRP') {
            $data['approve_position_code'] = 'APPRP';
            $data['responsibilities_approves'] = $userData['depart_code'];
        } else {
            $data['approve_position_code'] = 'APBR';
        }*/
    }
    
    if($data['id']) {
        $data['updated_by'] = $userData['id'];
        $data['head_office_id'] = $userData['head_office_id'];
        $result = $controller->update($data['id'], $data);
    } else {
        $data['created_by'] = $userData['id'];
        $data['head_office_id'] = $userData['head_office_id'];
        $result = $controller->create($data);
    }

    if ($result['success']) {
        header("Location: ".BASE_URL."/pages/business-budget-list.php");
        exit();
    } else {
        arrx($result);
    }
}