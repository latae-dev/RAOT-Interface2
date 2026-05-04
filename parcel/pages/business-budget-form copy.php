<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    include_once __DIR__ . '/../controllers/business_budget/business_budget_controller.php';
    
    //$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    ///$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    $rootFolder = ROOT_PATH;
    $baseUrl = BASE_URL;
    $formAction = $baseUrl . '/controllers/business_budget/business_budget_controller.php';

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    $permission = '';
    $user = $_SESSION['user_data'];

    $startYear = 2568;
    $endYear   = $startYear + 20;

    $controller = new BusinessBudgetController();
    $strategies = $controller->getStrategies();
    $projects = $controller->getProject();
    $budgetSource = $controller->getBudgetSource();
    $planUnderStrategies = $controller->getPlanUnderStrategies();
    $planStrategies = $controller->getPlanStrategies();
    $getRiskStrategicObjectives = $controller->getRiskStrategicObjectives();
    $getRiskNationalStrategies = $controller->getRiskNationalStrategies();

    $versionPermission = [
        'EMPY' => 0,
        'APBR' => 1,
        'APPV' => 2,
        'APAR' => 3,
        'APPRP' => 4,
        'APSTR' => 5
    ];

    $positionCode = getPositionCode();
    $versionUser = $versionPermission[$positionCode];

    if ($id) {
        $info = $controller->getBusinessBudgetById($id);   

        if($info['status'] == 'waiting' || $info['status'] == 'approved' || ($info['created_by'] != $user['id'])) {
            $permission = 'disabled';
        }

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

        if($info['status'] != 'draft' && $info['status'] != 'rejected') {
            $permission = 'disabled';
        }
    }
?>
<?php ob_start(); ?>

<?php $styles = ob_get_clean(); ?>

<?php ob_start(); ?>
    
    <form id="budgetForm" method="POST" action="<?php echo $formAction; ?>" enctype="multipart/form-data" novalidate> 
        <input type="hidden" value="<?php echo $baseUrl; ?>" class="base_url"/>
        <input type="hidden" value="<?php echo (isset($info) && $info['id']) ? $info['id'] : null; ?>" name="id" class="root_folder"/>
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
            <div class="row">
                <div class="col-xl-12">
                    <!-- ส่วนที่ 1 : ข้อมูลทั่วไป -->

                    <?php include 'business-budget-form-data.php'; ?>  
                    
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                        <a href="business-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1" <?php echo $permission; ?>><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                        <?php if(!$permission): ?>
                            <input type="hidden" name="action" id="actionField" value="">
                            <button type="submit" name="action" value="draft" class="btn btn-primary btn-wave waves-effect waves-light m-1" <?php echo $permission; ?>><i class="bi bi-save"></i> บันทึกร่าง</button>
                            <button type="submit" name="action" value="waiting" class="btn btn-success btn-wave waves-effect waves-light m-1" <?php echo $permission; ?>><i class="bi bi-send"></i> ส่งคำขอเพื่ออนุมัติ</button>
                        <?php endif; ?>
                    </div> 

                </div>
            </div>
            <!--End::row -->
        </div>
    </form>
    <!-- End::add board modal -->


<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

        <!--<script src="<?php echo $baseUrl; ?>/assets/libs/prismjs/prism.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/prism-custom.js"></script>
        
        <script src="<?php echo $baseUrl; ?>/assets/js/choices.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/quill/quill.min.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        
        <script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

        <script src="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone-min.js"></script>
        
        <script src="<?php echo $baseUrl; ?>/assets/js/fileupload.js"></script>-->

<?php $scripts = ob_get_clean(); ?>

<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
 <script type="module" src="<?php echo $baseUrl; ?>/pages/js/business-budget-form.js"></script>