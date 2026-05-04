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
            <h1 class="page-title fw-semibold fs-18 mb-0">จัดการสิทธิ์เมนูระบบ</h1>
            <p class="text-muted mb-0">กำหนดสิทธิ์การเข้าถึงเมนูตาม Position</p>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ผู้ดูแลระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold"><a href="menu-list.php">จัดการเมนูระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">จัดการสิทธิ์เมนู</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="card-title">จัดการสิทธิ์เมนูตาม Position</div>
                        <p class="text-muted mb-0 small">เลือก Position และกำหนดสิทธิ์การเข้าถึงเมนู</p>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-4">
                            <label class="form-label mt-2">Position <span class="text-danger">*</span></label>
                            <select class="form-control" id="filter-position" required>
                                <option value="">เลือก Position</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-xl-4">
                            <label class="form-label mt-2">หมวดหมู่เมนู :</label>
                            <select class="form-control" id="filter-category">
                                <option value="">ทั้งหมด</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-xl-4">
                            <label class="form-label mt-2">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary m-1" id="btn-load-permissions">
                                    <i class="bi bi-search me-1 fw-semibold align-middle"></i>โหลดข้อมูล
                                </button>
                                <button type="button" class="btn btn-info m-1" id="btn-copy-permissions" style="display: none;">
                                    <i class="ri-file-copy-line me-1 fw-semibold align-middle"></i>คัดลอกสิทธิ์
                                </button>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <button type="button" class="btn btn-success m-1" id="btn-save-permissions" style="display: none;">
                                    <i class="ri-save-line me-1 fw-semibold align-middle"></i>บันทึกสิทธิ์
                                </button>
                                <button type="button" class="btn btn-warning m-1" id="btn-select-all" style="display: none;">
                                    <i class="ri-checkbox-multiple-line me-1 fw-semibold align-middle"></i>เลือกทั้งหมด
                                </button>
                                <button type="button" class="btn btn-warning m-1" id="btn-deselect-all" style="display: none;">
                                    <i class="ri-checkbox-blank-line me-1 fw-semibold align-middle"></i>ยกเลิกทั้งหมด
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission List -->
                <div class="card-body border-bottom">
                    <div id="permission-list-container">
                        <div class="text-center p-5">
                            <p class="text-muted">กรุณาเลือก Position และกดปุ่ม "โหลดข้อมูล" เพื่อแสดงรายการสิทธิ์</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Modal สำหรับคัดลอกสิทธิ์ -->
<div class="modal fade" id="copy-permission-modal" tabindex="-1" aria-labelledby="copy-permission-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="copy-permission-modal-label">คัดลอกสิทธิ์จาก Position อื่น</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="copy-permission-form">
                    <div class="mb-3">
                        <label class="form-label">คัดลอกจาก Position:</label>
                        <select class="form-control" id="copy-from-position" required>
                            <option value="">เลือก Position</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        สิทธิ์ทั้งหมดจาก Position ที่เลือกจะถูกคัดลอกมาแทนที่สิทธิ์ปัจจุบัน
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-copy">คัดลอก</button>
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

<!-- Menu Permission List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/menu-permission-list.js?v=<?php echo time(); ?>"></script>

<!-- Initialize Choices.js -->
<!-- Note: Choices.js initialization จะถูกจัดการใน menu-permission-list.js หลังจากข้อมูลโหลดเสร็จแล้ว -->
<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
