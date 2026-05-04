<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
// Start session if not already started
if (session_id() === '') {
    session_start();
}

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

<!-- CHOICES CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/styles/choices.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">จัดการผู้ใช้งาน</h1>
            <p class="text-muted mb-0">จัดการข้อมูลผู้ใช้งาน (User) ในระบบ</p>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ผู้ดูแลระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">จัดการผู้ใช้งาน</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการผู้ใช้งาน -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="card-title">รายการผู้ใช้งาน</div>
                        <p class="text-muted mb-0 small">แสดงรายการผู้ใช้งานทั้งหมดในระบบ</p>
                    </div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-3">
                            <label class="form-label mt-2">รหัสผู้ใช้งาน :</label>
                            <input type="text" class="form-control" id="filter-user-code" placeholder="กรุณากรอกรหัสผู้ใช้งาน">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">ชื่อ :</label>
                            <input type="text" class="form-control" id="filter-user-fname" placeholder="กรุณากรอกชื่อ">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">นามสกุล :</label>
                            <input type="text" class="form-control" id="filter-user-lname" placeholder="กรุณากรอกนามสกุล">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สถานะ :</label>
                            <select class="form-control" id="filter-user-status">
                                <option value="">ทั้งหมด</option>
                                <option value="active">ใช้งาน</option>
                                <option value="pending">รออนุมัติ</option>
                                <option value="inactive">ไม่ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-xl-12 d-flex justify-content-center align-items-center flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-success m-1" id="btn-create-user" data-bs-toggle="modal" data-bs-target="#user-modal">
                                <i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างผู้ใช้งานใหม่
                            </button>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <button type="button" class="btn btn-primary m-1" id="filter-search-btn">
                                    <i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา
                                </button>
                                <button id="filter-reset-btn" class="btn btn-warning m-1">
                                    <i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List รายการผู้ใช้งาน -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered" id="users-table">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:5%;">ลำดับ</th>
                                        <th scope="col" style="width:12%;">รหัสผู้ใช้งาน</th>
                                        <th scope="col" style="width:15%;">ชื่อ-นามสกุล</th>
                                        <th scope="col" style="width:12%;">อีเมล</th>
                                        <th scope="col" style="width:12%;">ตำแหน่ง</th>
                                        <th scope="col" style="width:12%;">หน่วยงาน</th>
                                        <th scope="col" style="width:10%;">สถานะ</th>
                                        <th scope="col" style="width:10%;">วันที่สร้าง</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody">
                                    <!-- ข้อมูลจะถูกโหลดด้วย JavaScript -->
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">กำลังโหลด...</span>
                                            </div>
                                            <p class="mt-2">กำลังโหลดข้อมูล...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top-0">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div id="pagination-info">กำลังแสดง 0 รายการ</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- End:: row-4 -->

</div>

<!-- Modal สำหรับดูข้อมูลผู้ใช้งาน -->
<div class="modal fade" id="user-view-modal" tabindex="-1" aria-labelledby="user-view-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="user-view-modal-label">ดูข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="user-view-content">
                    <!-- ข้อมูลจะถูกโหลดด้วย JavaScript -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับสร้าง/แก้ไขผู้ใช้งาน -->
<div class="modal fade" id="user-modal" tabindex="-1" aria-labelledby="user-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="user-modal-label">สร้างผู้ใช้งานใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <input type="hidden" id="user-id" name="id">
                    
                    <!-- ข้อมูลพื้นฐาน -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">รหัสผู้ใช้งาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-code" name="user_code" required>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">รหัสผู้ใช้งานต้องไม่ซ้ำกัน</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="user-pass" name="user_pass" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-fname" name="user_fname" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-lname" name="user_lname" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="user-email" name="user_email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select class="form-select" id="user-status" name="user_status" required>
                                <option value="pending">รออนุมัติ</option>
                                <option value="active">ใช้งาน</option>
                                <option value="inactive">ไม่ใช้งาน</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- ข้อมูลการเชื่อมโยง -->
                    <hr>
                    <h6 class="mb-3">ข้อมูลการเชื่อมโยง</h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">หน่วยงาน</label>
                            <select class="form-control" id="user-depart" name="depart_id">
                                <option value="">-- เลือกหน่วยงาน --</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ตำแหน่ง</label>
                            <select class="form-control" id="user-position" name="position_id">
                                <option value="">-- เลือกตำแหน่ง --</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">บทบาท</label>
                            <select class="form-control" id="user-role" name="role_id">
                                <option value="">-- เลือกบทบาท --</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-user">บันทึก</button>
            </div>
        </div>
    </div>
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

<!-- CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- User List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/user-list.js?v=<?php echo time(); ?>"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
