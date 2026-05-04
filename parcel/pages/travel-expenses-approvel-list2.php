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

                <!-- FILEPOND CSS -->
                <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">




<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">การอนุมัติคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">การอนุมัติคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">

                                <!-- ส่วนหัว รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                                <div class="card-header justify-content-between">
                                    <div class="card-title">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</div>
                                </div>

                                <!-- หัวข้อการค้นหา -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">รหัสคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสคำขอเบิกเงินทดรองจ่าย">
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">เริ่มต้น :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันเริ่มต้น">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">สิ้นสุด :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันสิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">สถานะการอนุมติ :</label>
                                            <select class="js-example-basic-single" name="state">
                                                <option value="s-0">ทั้งหมด</option>
                                                <option value="s-1">ดำเนินการอนุมัติเสร็จสิ้น</option>
                                                <option value="s-2">รออนุมัติแบบแผนการเบิกพัสดุ	</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="text-center">
                                                <button class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="table-responsive mb-2">
                                            <table class="table text-nowrap table-bordered">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th scope="col"  style="width:50px;">ลำดับ</th>
                                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                                        <th scope="col">รหัสคำขอเบิกเงินทดรองจ่าย</th>
                                                        <th scope="col">ชื่อแบบฟอร์ม</th>
                                                        <th scope="col">เลขที่คำสั่ง/บันทึกที่</th>
                                                        <th scope="col">ลงวันที่</th>
                                                        <th scope="col">ผู้ร้องขอ</th>
                                                        <th scope="col">หน่วยงาน</th>
                                                        <th scope="col">สถานะ</th>
                                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-center">
                                                    <tr class="product-list"  style="width:50px;">
                                                        <td>1</td>
                                                        <td>01-03-2567</td>
                                                        <td>680013</td>
                                                        <td>เบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน</td>
                                                        <td>10/2568</td>
                                                        <td>1 มกราคม 2568</td>
                                                        <td>นายทดสอบ ทดสอบ</td>
                                                        <td>นนทบุรี</td>
                                                        <td><span class="badge rounded-pill bg-warning">รออนุมัติคำขอเบิกเงินทดรองจ่าย</span></td>
                                                        <td>
                                                            <div class="hstack gap-2 fs-15">
                                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill"><i class='bx bx-check' ></i></a>
                                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill"><i class='bx bx-x' ></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="product-list">
                                                        <td>2</td>
                                                        <td>28-02-2567</td>
                                                        <td>680015</td>
                                                        <td>เบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน (บุคคลภายนอก)</td>
                                                        <td>11/2568</td>
                                                        <td>2 มกราคม 2568</td>
                                                        <td>นายทดสอบ ทดสอบ</td>
                                                        <td>น่าน</td>
                                                        <td><span class="badge rounded-pill bg-success">ดำเนินการอนุมัติเสร็จสิ้น</span></td>
                                                        <td>
                                                            <div class="hstack gap-2 fs-15">
                                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="card-footer border-top-0">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            กำลังแสดง 10 รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
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

        <!-- INTERNAL PRODUCT DETAILS JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

        <!-- INTERNAL DATATABLES JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/datatables.js"></script>

        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

        <!-- SELECT2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- INTERNAL SELECT2 JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/select2.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->