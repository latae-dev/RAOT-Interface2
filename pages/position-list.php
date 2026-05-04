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
            <h1 class="page-title fw-semibold fs-18 mb-0">จัดการตำแหน่ง</h1>
            <p class="text-muted mb-0">จัดการข้อมูลตำแหน่ง (Position) ในระบบ</p>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ผู้ดูแลระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">จัดการตำแหน่ง</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการตำแหน่ง -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="card-title">รายการตำแหน่ง</div>
                        <p class="text-muted mb-0 small">แสดงรายการตำแหน่งทั้งหมดในระบบ</p>
                    </div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-4">
                            <label class="form-label mt-2">รหัสตำแหน่ง :</label>
                            <input type="text" class="form-control" id="filter-position-code" placeholder="กรุณากรอกรหัสตำแหน่ง">
                        </div>
                        <div class="col-xl-4">
                            <label class="form-label mt-2">ชื่อตำแหน่ง :</label>
                            <input type="text" class="form-control" id="filter-position-name" placeholder="กรุณากรอกชื่อตำแหน่ง">
                        </div>
                        <div class="col-xl-12 d-flex justify-content-center align-items-center flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-success m-1" id="btn-create-position" data-bs-toggle="modal" data-bs-target="#position-modal">
                                <i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างตำแหน่งใหม่
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

                <!-- List รายการตำแหน่ง -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered" id="positions-table">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:5%;">ลำดับ</th>
                                        <th scope="col" style="width:15%;">รหัสตำแหน่ง</th>
                                        <th scope="col" style="width:25%;">ชื่อตำแหน่ง</th>
                                        <th scope="col" style="width:25%;">สิทธิ์การอนุมัติ</th>
                                        <th scope="col" style="width:12%;">วันที่สร้าง</th>
                                        <th scope="col" style="width:12%;">วันที่อัปเดต</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="positions-tbody">
                                    <!-- ข้อมูลจะถูกโหลดด้วย JavaScript -->
                                    <tr>
                                        <td colspan="7" class="text-center">
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

<!-- Modal สำหรับดูข้อมูลตำแหน่ง -->
<div class="modal fade" id="position-view-modal" tabindex="-1" aria-labelledby="position-view-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="position-view-modal-label">ดูข้อมูลตำแหน่ง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="position-view-content">
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

<!-- Modal สำหรับสร้าง/แก้ไขตำแหน่ง -->
<div class="modal fade" id="position-modal" tabindex="-1" aria-labelledby="position-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="position-modal-label">สร้างตำแหน่งใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="position-form">
                    <input type="hidden" id="position-id" name="id">
                    
                    <!-- ข้อมูลพื้นฐาน -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">รหัสตำแหน่ง <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position-code" name="position_code" required>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">รหัสตำแหน่งต้องไม่ซ้ำกัน (เช่น: APBR, APPV)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อตำแหน่ง <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position-name" name="position_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- สิทธิ์การอนุมัติ (Approve Permissions) -->
                    <hr>
                    <h6 class="mb-3">สิทธิ์การอนุมัติ</h6>
                    
                    <!-- กลุ่มที่ 1: Approve Basic (active/nonactive) -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติสาขา</label>
                            <select class="form-select" id="approve-branch" name="approve_branch">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติจังหวัด</label>
                            <select class="form-select" id="approve-province" name="approve_province">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติเขต</label>
                            <select class="form-select" id="approve-airea" name="approve_airea">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติสำนักงานใหญ่</label>
                            <select class="form-select" id="approve-head-office" name="approve_head_office">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติการเงิน</label>
                            <select class="form-select" id="approve-finance" name="approve_finance">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติการจ่าย</label>
                            <select class="form-select" id="approve-payment" name="approve_payment">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                    </div>

                    <!-- กลุ่มที่ 2: Approve Advanced -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติพื้นที่</label>
                            <select class="form-select" id="approve-area" name="approve_area">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติงบทำการ (ผู้รับผิดชอบหลัก)</label>
                            <select class="form-select" id="approve-operating-primary" name="approve_operating_primary">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติงบทำการ (ยุทธศาสตร์)</label>
                            <select class="form-select" id="approve-operating-strategic" name="approve_operating_strategic">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติงบลงทุน</label>
                            <select class="form-select" id="approve-investment" name="approve_investment">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติพัสดุ</label>
                            <select class="form-select" id="approve-pacels" name="approve_pacels">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">อนุมัติใบขอเสนอ</label>
                            <select class="form-select" id="approve-proposal" name="approve_proposal">
                                <option value="nonactive">ไม่ใช้งาน</option>
                                <option value="active">ใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-position">บันทึก</button>
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

<!-- Position List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/position-list.js?v=<?php echo time(); ?>"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
