<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
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
                        <h1 class="page-title fw-semibold fs-18 mb-0">รายงานคำขอสร้างใบขอเสนอ</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">ระบบจัดซื้อ</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">รายงานคำขอสร้างใบขอเสนอ</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start:: row-4 -->
                    <div class="row">
                        
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="card-title">รายงานคำขอสร้างใบขอเสนอ</div>
                                    <!-- <a class="btn btn-primary btn-sm" href="parcel-withdrawal-plan.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>กำหนดแผนการเบิกพัสดุแบบพิมพ์</a> -->
                                </div>
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">รหัสคำขอสร้างใบขอเสนอ :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก คำขอสร้างใบขอเสนอ">
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">เริ่มต้น :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date-from" placeholder="กำหนด วันเริ่มต้น">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">สิ้นสุด :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date-to" placeholder="กำหนด วันสิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">ประเภทเอกสาร :</label>
                                            <select class="form-control" name="choices-single-groups" id="form-type-filter">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="3">คำขอสร้างใบขอเสนอจ้าง</option>
                                                <option value="2">คำขอสร้างใบขอเสนอเช่า</option>
                                                <option value="1">คำขอสร้างใบขอเสนอซื้อ</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">หมวดการกำหนดบัญชี :</label>
                                            <select class="form-control" name="choices-single-groups" id="account-category-filter">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="A">A สินทรัพย์</option>
                                                <option value="K">K ศูนย์ต้นทุน</option>
                                                <option value="N">ไม่เลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                                            <select class="form-control" name="choices-single-groups" id="status-filter">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="approved">อนุมัติ</option>
                                                <option value="pending">รออนุมัติ</option>
                                                <option value="rejected">ปฏิเสธ</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="text-center">
                                                <button class="btn btn-success m-1"><i class="bx bx-export me-1 fw-semibold align-middle"></i>นำข้อมูลออก (.xlxs)</button>
                                                <button id="searchBtn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                                <button id="resetBtn" class="btn btn-warning m-1" fdprocessedid="aly3wf"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body border-bottom">
                                    <div class="row">

                                        <div class="table-responsive mt-2">
                                            <table id="proposalsTable" class="table text-nowrap table-bordered">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                                        <th scope="col">รหัสรายการคำขอสร้างใบขอเสนอ</th>
                                                        <th scope="col">ประเภทเอกสาร</th>
                                                        <th scope="col">บันทึกส่วนหัว</th>
                                                        <th scope="col">หมวดการกำหนดบัญชี</th>
                                                        <th scope="col">ผู้ขออนุมัติ</th>
                                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- ข้อมูลจะถูกโหลดโดย JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top-0">
                                    <div class="d-flex align-items-center">
                                        <div id="showing-info">
                                            กำลังแสดง <span id="showing-start">0</span> ถึง <span id="showing-end">0</span> จาก <span id="total-items">0</span> รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                                        </div>
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

        <!-- JQUERY CDN (removed duplicate) -->
        
        <!-- SELECT2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        <!-- XLSX LIBRARY FOR EXCEL EXPORT -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

        <!-- INTERNAL SELECT2 JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/select2.js"></script>

        <!-- SWEETALERT2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- INTERNAL REQUEST PROPOSAL LIST JS -->
        <script src="<?php echo $baseUrl; ?>/pages/js/request-proposal-list-report.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->