<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php 
    include_once __DIR__ . '/../controllers/business_budget/business_budget_report_controller.php';

    $baseUrl = BASE_URL;
    $pathUrl = $baseUrl . '/pages/business-budget-report-form.php';

    $currentQuery = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : '';
    parse_str($currentQuery, $queryArray);
    unset($queryArray['action'], $queryArray['id']);

    $controller = new BusinessBudgetReportController();
    $param = array();

    $user = $_SESSION['user_data'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $param = $_GET;
    }


    $businessList = $controller->getBusinessReportList($param);
?>

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">รายงาน Interface ข้อมูลเข้า SAP</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคําของบประมาณรายจ่ายประจําปี</a></li>
                                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายงาน Interface ข้อมูลเข้า SAP</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                
                                <!-- ส่วนหัว รายงาน Interface ข้อมูลเข้า SAP -->
                                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="card-title">รายงาน Interface ข้อมูลเข้า SAP</div>
                                </div>

                                <!-- หัวข้อการค้นหา -->
                                <div class="card-body border-bottom">
                                    <form action="" method="GET">
                                    <input type="hidden" value="5" name="export_type" />
                                    <div class="row">
                                        <div class="col-xl-4 mb-2">
                                            <label class="form-label mt-2">รหัสคําของบประมาณรายจ่ายประจําปี :</label>
                                            <input type="text" class="form-control" id="input-label11" name="document_number" value="<?php echo ((isset($_GET['document_number'])) ? htmlspecialchars($_GET['document_number']) : ''); ?>" placeholder="กรุณากรอก รหัสแผนคำขอเบิกเงินทดรอง">
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">เริ่มต้น :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" name="start_date" value="<?php echo ((isset($_GET['start_date'])) ? htmlspecialchars($_GET['start_date']) : ''); ?>" placeholder="กำหนด วันเริ่มต้น">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">สิ้นสุด :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" name="end_date" value="<?php echo ((isset($_GET['end_date'])) ? htmlspecialchars($_GET['end_date']) : ''); ?>" placeholder="กำหนด วันสิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">สถานะการอนุมติ :</label>
                                            <select class="form-control" data-trigger name="status" id="choices-single-groups">
                                                <option value="" selected disabled>กรุณาเลือก</option>
                                                <option value="approved" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'approved') ? 'selected' : ''); ?>>ผ่านการอนุมติ</option>
                                                <option value="waiting" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'waiting') ? 'selected' : ''); ?>>รออนุมัติ</option>
                                                <option value="rejected" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'rejected') ? 'selected' : ''); ?>>ไม่ผ่านการอนุมัติ</option>
                                                <option value="deleted" <?php echo ((isset($_GET['status']) && $_GET['status'] === 'deleted') ? 'selected' : ''); ?>>คำขอที่ถูกลบ</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">สถานะ Interface SAP :</label>
                                            <select class="form-control" data-trigger name="sap_status" name="sap_status" id="choices-single-groups">
                                                <option value="" selected disabled>กรุณาเลือก</option>
                                                <option value="synced" <?php echo ((isset($_GET['sap_status']) && $_GET['sap_status'] === 'synced') ? 'selected' : ''); ?>>Interface SAP Success</option>
                                                <option value="not_synced" <?php echo ((isset($_GET['sap_status']) && $_GET['sap_status'] === 'not_synced') ? 'selected' : ''); ?>>Interface SAP Wait</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="text-center">
                                                <button class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                                <a href="business-budget-report-interface-sap-list.php" id="resetBtn" class="btn btn-warning m-1" fdprocessedid="aly3wf"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</a>
                                                <button type="submit" class="btn btn-success m-1" name="action" value="exportExcel">
                                                    <i class="bx bx-export me-1 fw-semibold align-middle"></i> นำข้อมูลออก
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                
                                <!-- List รายการคําของบประมาณรายจ่ายประจําปี -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="table-responsive mt-2">
                                            <table id="report_list" class="table text-nowrap table-bordered">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                                        <th scope="col">รหัสคําของบประมาณรายจ่ายประจําปี</th>
                                                        <th scope="col">ชื่อโครงการ</th>
                                                        <th scope="col">ปีงบประมาณ</th>
                                                        <th scope="col">หน่วยงาน</th>
                                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                                        <th scope="col">สถานะ Interface SAP</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($businessList): ?>
                                                        <?php foreach($businessList as $key => $rs): ?>
                                                            <tr class="crm-contact text-center">
                                                                <td><?php echo $key + 1; ?><?php echo $key + 1; ?></td>
                                                                <td><?php echo setDateTimeFormat($rs['created_at']);?></td>
                                                                <td><?php echo $rs['document_number']; ?></td>
                                                                <td><?php echo ($rs['project_type_name'] == '1') ? $rs['old_project_name'] : $rs['project_name']; ?></td>
                                                                <td><?php echo $rs['year_name']; ?></td>
                                                                <td><?php echo $rs['depart_name']; ?></td>
                                                                <td>
                                                                    <?php if($rs['status'] == 'waiting') { ?>
                                                                        <span class="badge rounded-pill bg-warning"><?php echo getStatus($rs['status'], $rs['approve_position_code']); ?></span>
                                                                    <?php } else if($rs['status'] == 'rejected') { ?>
                                                                        <span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>
                                                                    <?php } else { ?>
                                                                        <span class="badge rounded-pill bg-success">อนุมติ</span>
                                                                    <?php } ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($rs['sap_status'] == 'not_synced'): ?>
                                                                        <span class="badge rounded-pill bg-warning">Interface SAP Faild</span>
                                                                    <?php else: ?>
                                                                        <span class="badge rounded-pill bg-info">Interface SAP Success</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
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
        <script src="<?php echo $baseUrl; ?>/assets/js/datatables.js"></script>


        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

        <!-- JQUERY CDN -->
        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        
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