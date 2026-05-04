<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
    $pathUrl = $baseUrl . '/pages/business-budget-form.php';

    $currentQuery = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : '';
    parse_str($currentQuery, $queryArray);
    unset($queryArray['action'], $queryArray['id']);
?>

<?php 
    include_once __DIR__ . "/../helper/common.php"; 
    include_once __DIR__ . '/../controllers/business_budget/business_budget_controller.php';
    include_once __DIR__ . '/../models/business_budget/business_budget_model.php';

    $controller = new BusinessBudgetController();
    $param = array();

    $user = $_SESSION['user_data'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $param = $_GET;
    }


    $businessList = $controller->getBusinessList($param);
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- DATA TABLES CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
        
        <!-- FLATPICKER CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">

        <!-- SELECT2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">รายการคําของบประมาณรายจ่ายประจําปี</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคําของบประมาณรายจ่ายประจําปี</a></li>
                                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายการคําของบประมาณรายจ่ายประจําปี</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                
                                <!-- ส่วนหัว รายการคําของบประมาณรายจ่ายประจําปี -->
                                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="card-title">รายการคําของบประมาณรายจ่ายประจําปี</div>
                                </div>

                                <!-- หัวข้อการค้นหา -->
                                <div class="card-body border-bottom">
                                    <form action="" method="GET">
                                        <div class="row">
                                            <div class="col-xl-3 mb-2">
                                                <label class="form-label mt-2">รหัสคําของบประมาณรายจ่ายประจําปี :</label>
                                                <input type="text" class="form-control" id="input-label11" name="document_number" value="<?php echo ((isset($_GET['document_number'])) ? htmlspecialchars($_GET['document_number']) : ''); ?>" placeholder="กรุณากรอก รหัสแผนคำขอเบิกเงินทดรอง">
                                            </div>
                                            <div class="col-xl-3 mb-2">
                                                <label class="form-label mt-2">เริ่มต้น :</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="date" name="start_date" value="<?php echo ((isset($_GET['start_date'])) ? htmlspecialchars($_GET['start_date']) : ''); ?>" placeholder="กำหนด วันเริ่มต้น">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 mb-2">
                                                <label class="form-label mt-2">สิ้นสุด :</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="date" name="end_date" value="<?php echo ((isset($_GET['end_date'])) ? htmlspecialchars($_GET['end_date']) : ''); ?>" placeholder="กำหนด วันสิ้นสุด">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 mb-2">
                                                <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                                                <select class="form-control" data-trigger name="status" id="choices-single-groups">
                                                    <option value="">กรุณาเลือก</option>
                                                    <option value="draft" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'draft') ? 'selected' : ''); ?>>ดราฟ</option>
                                                    <option value="approved" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'approved') ? 'selected' : ''); ?>>อนุมติ</option>
                                                    <option value="waiting" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'waiting') ? 'selected' : ''); ?>>รออนุมัติ</option>
                                                </select>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="text-center">
                                                    <a class="btn btn-success m-1" href="business-budget-form.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างคําของบประมาณรายจ่ายประจําปี</a>
                                                    <button class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                                    <a href="business-budget-list.php" id="resetBtn" class="btn btn-warning m-1" fdprocessedid="aly3wf"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- List รายการคําของบประมาณรายจ่ายประจําปี 1 -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="table-responsive mt-2">
                                            <table id="business_list" class="table text-nowrap table-bordered">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                                        <th scope="col">รหัสคําของบประมาณรายจ่ายประจําปี</th>
                                                        <th scope="col">ชื่อโครงการ</th>
                                                        <th scope="col">ปีงบประมาณ</th>
                                                        <th scope="col">หน่วยงาน</th>
                                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($businessList): ?>
                                                        <?php foreach($businessList as $key => $rs): ?>
                                                            <?php
                                                                $linkParams = $queryArray;
                                                                $linkParams['action'] = 'delete';
                                                                $linkParams['id'] = $rs['id'];
                                                                $deleteUrl = $pathUrl . '?' . http_build_query($linkParams);
                                                            ?>
                                                            <tr class="crm-contact text-center">
                                                                <td><?php echo $key + 1; ?></td>
                                                                <td><?php echo setDateTimeFormat($rs['created_at']);?></td>
                                                                <td><?php echo $rs['document_number']; ?></td>
                                                                <td><?php echo ($rs['project_type_name'] == '1') ? $rs['old_project_name'] : $rs['project_name']; ?></td>
                                                                <td><?php echo $rs['year_name']; ?></td>
                                                                <td><?php echo $rs['depart_name']; ?></td>
                                                                <td>
                                                                    <?php if($rs['status'] == 'waiting' || $rs['status'] == 'draft') { ?>
                                                                        <span class="badge rounded-pill bg-warning"><?php echo getStatus($rs['status'], $rs['approve_position_code']); ?></span>
                                                                    <?php } else if($rs['status'] == 'rejected') { ?>
                                                                        <span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>
                                                                    <?php } else if($rs['status'] == 'approved') { ?>
                                                                        <span class="badge rounded-pill bg-success">อนุมติ</span>
                                                                    <?php } ?>
                                                                </td>
                                                                <td>
                                                                    <div class="hstack gap-2 fs-15">
                                                                        <?php if($rs['status'] == 'draft' || $rs['status'] == 'rejected'): ?>
                                                                            <a href="<?php echo $pathUrl . '?id=' . $rs['id']; ?>" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill">
                                                                                <i class="fe fe-edit"></i>
                                                                            </a>
                                                                             <a href="<?= $deleteUrl ?>" 
                                                                                class="btn btn-icon btn-sm btn-danger-transparent rounded-pill delete">
                                                                                    <i class="ri-delete-bin-line"></i>
                                                                                </a>
                                                                        <?php endif; ?>
                                                                        <?php if($rs['status'] == 'waiting' || $rs['status'] == 'approved'): ?>
                                                                            <a href="<?php echo $pathUrl . '?id=' . $rs['id']; ?>" class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                                                                <i class="fe fe-eye"></i>
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!--<div class="card-footer border-top-0">
                                    <div class="d-flex align-items-center">
                                        <div>กำลังแสดง 10 รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i></div>
                                        <div class="ms-auto">
                                            <nav aria-label="Page navigation" class="pagination-style-4">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            ก่อนหน้า
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link text-primary" href="javascript:void(0);">
                                                            ถัดไป
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>-->
                                
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-4 -->

                </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

        <!-- JQUERY JS -->
        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

        <!-- DATATABLES CDN JS -->
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

        <!-- INTERNAL DATATABLES JS -->


        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

       
        <!-- SELECT2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- INTERNAL SELECT2 JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/select2.js"></script>

        <script type="module" src="<?php echo $baseUrl; ?>/pages/js/business-budget-list.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->