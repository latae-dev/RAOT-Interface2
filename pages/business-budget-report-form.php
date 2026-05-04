<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    include_once __DIR__ . "/../helper/common.php";
    include_once __DIR__ . '/../configs/database.php';
    include_once __DIR__ . '/../controllers/business_budget/business_budget_controller.php';
    include_once __DIR__ . '/../controllers/business_budget/business_budget_approvel_controller.php';
    include_once __DIR__ . '/../models/business_budget/business_budget_model.php';
    
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $user = $_SESSION['user_data'];

    $startYear = 2568;
    $endYear   = $startYear + 20;

    $controllerApprove = new BusinessBudgetApprovelController();

    $approveHistory = $controllerApprove->getApproveHistory($id);
    $depart = $controllerApprove->getDepart();

    $controller = new BusinessBudgetController();
    $strategies = $controller->getStrategies();
    $projects = $controller->getProject();
    $budgetSource = $controller->getBudgetSource();
    $planUnderStrategies = $controller->getPlanUnderStrategies();
    $planStrategies = $controller->getPlanStrategies();
    $getRiskStrategicObjectives = $controller->getRiskStrategicObjectives();
    $getRiskNationalStrategies = $controller->getRiskNationalStrategies();
    
    if ($id) {
        $info = $controller->getBusinessBudgetById($id); 

        $permission = 'disabled';

        if($info['activity_code']) {
            $projectActivity = $controller->getProjectActivity($info['project_code']);
        }

        if($info['national_strategy_id']) {
            $targetStrategies = $controller->getTargetStrategies($info['national_strategy_id']);
            $subjectStrategies = $controller->getSubjectStrategies($info['national_strategy_id']);   
        }

        if($info['plan_under_strategy_id']) {
            $targetLevelSubjects = $controller->getTargetLevelSubjects($info['plan_under_strategy_id']);
            $subPlans = $controller->getSubPlans($info['plan_under_strategy_id']);
            $targetSubPlans = $controller->getTargetSubPlans($info['sub_plan_id']);
        }

        if($info['plan_strategy_id']) {
            $subIndicators = $controller->getSubIndicators($info['plan_strategy_id']);
            $tactics = $controller->getTactics($info['plan_strategy_id']);
        }

        if($info['national_strategy']) {
            $getRiskIndicators = $controller->getRiskStrategiesIndicators($info['national_strategy']);
            $getRiskStrategies = $controller->getRiskStrategies($info['national_strategy']);
        }
    }

    
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- QUILL CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.snow.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.bubble.css">
        
        <!-- FILEPOND CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone.css">
        
        <!-- PRISM CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/prismjs/themes/prism-coy.min.css">

        <!-- FILEPOND CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">

        <!-- DATA TABLES CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

        <!-- DATE & TIME PICKER CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>
 <style>
    .form-layout .app-content {
        margin:0 0.5rem
    }
    .form-layout .footer {
        display:none;
    }
</style>

    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มคําของบประมาณรายจ่ายประจําปี</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคําของบประมาณรายจ่ายประจําปี</a></li>
                        <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคําของบประมาณรายจ่ายประจําปี</li>
                    </ol>
                </nav>
            </div>
        </div>
            <!-- Page Header Close -->

        <div class="row form-layout">
            <div class="col-xl-12">
                
                <?php include 'business-budget-form-data.php'; ?>  
                
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">ผลการพิจารณาอนุมัติ</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered" id="approval-table">
                                        <thead>
                                            <tr>
                                                <th class="text-start" scope="col" style="width: 30px;">ลำดับที่</th>
                                                <th class="text-center" scope="col" style="width: 200px;">วันที่</th>
                                                <th class="text-center" scope="col" style="width: 200px;">ผลการพิจารณา</th>
                                                <th class="text-center" scope="col" style="width: 400px;">ผู้อนุมัติ</th>
                                                <th class="text-center" scope="col">หมายเหตุ</th>
                                            </tr>
                                        </thead>
                                        <?php if($approveHistory): ?>
                                            <?php foreach($approveHistory as $key => $rs): ?>
                                                <tr>
                                                    <th class="text-start" scope="col" style="width: 30px;"><?php echo $key +1; ?></th>
                                                    <th class="text-center" scope="col" style="width: 200px;"><?php echo setDateTimeFormat($rs['approve_date'], 'd/m/Y H:i:s'); ?></th>
                                                    <th class="text-center" scope="col" style="width: 200px;"><?php echo ($rs['status'] == 'approved') ? 'อนุมัติ' : 'ไม่อนุมัติ'; ?></th>
                                                    <th class="text-center" scope="col" style="width: 400px;"><?php echo $rs['approver_name']; ?></th>
                                                    <th class="text-center" scope="col"><?php echo $rs['remark']; ?></th>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tbody><tr><td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td></tr></tbody>
                                        <?php endif; ?>
                                        
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                <a href="business-budget-report-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row -->
    </div>



<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>



<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
    <script type="module" src="<?php echo $baseUrl; ?>/pages/js/business-budget-form.js"></script>
    <script type="module" src="<?php echo $baseUrl; ?>/pages/js/business-budget-approval-form.js"></script>