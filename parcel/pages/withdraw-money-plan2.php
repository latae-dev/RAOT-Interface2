<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

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

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">คำขอเบิกเงินทดรองจ่าย</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคำขอเบิกเงินทดรองจ่าย</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">

                                <!-- ส่วนหัว รายละเอียดผู้ยื่นคำขอเบิกเงินทดรองจ่าย -->
                                <div class="card-header">
                                    <div class="card-title">รายละเอียดผู้ยื่นคำขอเบิกเงินทดรองจ่าย</div>
                                </div>

                                <!-- รายละเอียดผู้ยื่นคำขอเบิกเงินทดรองจ่าย -->
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                            <input type="text" class="form-control" id="input-label" placeholder="ยืมเงินทดลอง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="publish-date" class="form-label">วันที่เริ่ม :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เริ่ม">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">วันที่สิ้นสุด :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="endDate" placeholder="กรุณากรอก วันที่สิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="ดึงข้อมูลมาแสดง" disabled>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ระดับ :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">สังกัด :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">

                                <!-- ส่วนหัว รายละเอียดคำขอเบิกเงินทดรองจ่าย -->
                                <div class="card-header">
                                    <div class="card-title">รายละเอียดคำขอเบิกเงินทดรองจ่าย</div>
                                </div>

                                
 
                                <!-- เบิกจ่ายจาก กองทุนฯ/เงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-4 mb-2">
                                            <label class="form-label mt-2">เงินทุนที่ใช้ :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="s-1">เงินทุนเพื่อการบริหาร 49(1)</option>
                                                <option value="s-2">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</option>
                                                <option value="s-2">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</option>
                                                <option value="s-2">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</option>
                                                <option value="s-2">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</option>
                                                <option value="s-2">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 49(6)</option>
                                                <option value="s-2">กองทุนพัฒนายางพารา 49(7)</option>
                                                <option value="s-2">อื่นๆ</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-4 mb-2">
                                            <label class="form-label mt-2">โครงการฯ :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="s-1">โครงการ 1</option>
                                                <option value="s-2">โครงการ 2</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-4 mb-2">
                                            <label class="form-label mt-2">ค่าใช้จ่าย :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="s-1">ค่าใช้จ่าย 1</option>
                                                <option value="s-2">ค่าใช้จ่าย 2</option>
                                            </select>
                                        </div>                           
                                    </div>                                
                                </div>                                

                                <!-- รายการ รายละเอียดคำขอเบิกเงินทดรองจ่าย -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap  table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">
                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                            </th>
                                                            <th scope="col">รายการ</th>
                                                            <th scope="col">จำนวน</th>
                                                            <th scope="col">หน่วย</th>
                                                            <th scope="col">ราคาหน่วย</th>
                                                            <th scope="col">ราคาทั้งหมด</th>
                                                            <th scope="col">แก้ไขยอดทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                            <td><input class="form-control" type="text"></td>
                                                            <td><input class="form-control" type="text" placeholder="0"></td>
                                                            <td>
                                                                <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                                    <option value="">กรุณาเลือก</option>
                                                                    <option value="s-1">วัน</option>
                                                                    <option value="s-2">คืน</option>
                                                                    <option value="s-3">มื้อ</option>
                                                                    <option value="s-4">คัน</option>
                                                                    <option value="s-5">คน</option>
                                                                    <option value="s-6">อื่นๆ</option>
                                                                </select>
                                                            </td>
                                                            <td><input class="form-control" type="text" placeholder="0.00"></td>
                                                            <td><input class="form-control" type="text" placeholder="0.00" disabled></td>
                                                            <td><input class="form-control" type="text"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- เหตุผลของการขอยืมเงินทดรอง -->
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                <label class="form-check-label" for="c-1">นับจากวันกลับมาถึง</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="" id="493" checked>
                                                <label class="form-check-label" for="c-1">นับแต่วันที่ได้รับเงิน</label>
                                            </div>
                                        </div>
                                        <div class="col-xl-9">
                                            <div class="form-check mb-2">
                                                <label class="form-check-label mb-1" for="c-1">เหตุผลของการขอยืมเงินทดรอง <small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                                <textarea class="form-control" id="product-description-add" rows=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a href="withdraw-money-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class="bi bi-save"></i> ขออนุมัติ</button>
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

        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

        <!-- QUILL JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/quill/quill.min.js"></script>

        <!-- FILEPOND JS -->
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

        <!-- FLAT PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        
        <!-- CREATE PROJECT JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->