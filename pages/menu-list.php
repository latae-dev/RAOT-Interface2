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

<!-- Tree View CSS -->
<style>
    .menu-tree-item {
        padding-left: 20px;
    }

    .menu-tree-item.level-1 {
        padding-left: 0;
    }

    .menu-tree-item.level-2 {
        padding-left: 20px;
    }

    .menu-tree-item.level-3 {
        padding-left: 40px;
    }

    .menu-tree-item.level-4 {
        padding-left: 60px;
    }

    .menu-tree-toggle {
        cursor: pointer;
        margin-right: 5px;
    }

    .menu-children {
        display: none;
    }

    .menu-children.expanded {
        display: table-row-group;
    }
</style>

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">จัดการเมนูระบบ</h1>
            <p class="text-muted mb-0">จัดการเมนูระบบแบบ Hierarchy (Parent > Child) และกำหนดสิทธิ์การเข้าถึง</p>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ผู้ดูแลระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">จัดการเมนูระบบ</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการเมนู -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="card-title">รายการเมนูระบบ</div>
                        <p class="text-muted mb-0 small">แสดงรายการเมนูทั้งหมดพร้อมข้อมูล Hierarchy</p>
                    </div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-3">
                            <label class="form-label mt-2">รหัสเมนู :</label>
                            <input type="text" class="form-control" id="filter-menu-code" placeholder="กรุณากรอกรหัสเมนู">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">ชื่อเมนู :</label>
                            <input type="text" class="form-control" id="filter-menu-name" placeholder="กรุณากรอกชื่อเมนู">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">หมวดหมู่ :</label>
                            <select class="form-control" id="filter-category">
                                <option value="">ทั้งหมด</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สถานะ :</label>
                            <select class="form-control" id="filter-is-active">
                                <option value="">ทั้งหมด</option>
                                <option value="true">ใช้งาน</option>
                                <option value="false">ไม่ใช้งาน</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <button type="button" class="btn btn-success m-1" id="btn-create-menu" data-bs-toggle="modal" data-bs-target="#menu-modal">
                                    <i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างเมนูใหม่
                                </button>
                                <a href="menu-permission-list.php" class="btn btn-info m-1">
                                    <i class="ri-shield-user-line me-1 fw-semibold align-middle"></i>จัดการสิทธิ์เมนู
                                </a>
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

                <!-- List รายการเมนู -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered" id="menus-table">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:5%;">ลำดับ</th>
                                        <th scope="col" style="width:15%;">รหัสเมนู</th>
                                        <th scope="col" style="width:20%;">ชื่อเมนู</th>
                                        <th scope="col" style="width:15%;">Path</th>
                                        <th scope="col" style="width:10%;">Icon</th>
                                        <th scope="col" style="width:10%;">หมวดหมู่</th>
                                        <th scope="col" style="width:5%;">Sort</th>
                                        <th scope="col" style="width:5%;">สถานะ</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="menus-tbody">
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

<!-- Modal สำหรับสร้าง/แก้ไขเมนู -->
<div class="modal fade" id="menu-modal" tabindex="-1" aria-labelledby="menu-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menu-modal-label">สร้างเมนูใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="menu-form">
                    <input type="hidden" id="menu-id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">รหัสเมนู <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu-code" name="menu_code" required>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">รหัสเมนูต้องไม่ซ้ำกัน (เช่น: MENU_DASHBOARD)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อเมนู <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu-name" name="menu_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Path</label>
                            <input type="text" class="form-control" id="menu-path" name="menu_path" placeholder="เช่น: index.php หรือ ../parcel/pages/list.php">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Path ของหน้าเว็บ (ถ้าเป็น parent menu ให้เว้นว่าง)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon</label>
                            <input type="text" class="form-control" id="menu-icon" name="icon" placeholder="เช่น: bx bx-home">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Icon class (เช่น: bx bx-home, fe fe-home)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หมวดหมู่</label>
                            <input type="text" class="form-control" id="menu-category" name="category" placeholder="เช่น: Main, ระบบงาน">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">หมวดหมู่สำหรับจัดกลุ่มเมนู</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parent Menu</label>
                            <select class="form-control" id="menu-parent" name="parent_id">
                                <option value="">ไม่มี (เป็นเมนูระดับสูงสุด)</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">เลือกเมนูแม่ (ถ้ามี)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="menu-sort-order" name="sort_order" value="0" min="0">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">ลำดับการแสดงผล (ตัวเลขน้อยแสดงก่อน)</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="menu-is-active" name="is_active" checked>
                                <label class="form-check-label" for="menu-is-active">
                                    ใช้งาน
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-menu">บันทึก</button>
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

<!-- Menu List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/menu-list.js?v=<?php echo time(); ?>"></script>

<!-- Initialize Choices.js for filter selects -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for filter selects
        const filterSelects = ['filter-category', 'filter-is-active'];
        filterSelects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select && typeof Choices !== 'undefined') {
                new Choices(select, {
                    searchEnabled: false,
                    itemSelectText: '',
                    shouldSort: false
                });
            }
        });

        // Note: menu-parent select will be initialized dynamically in menu-list.js
        // when the modal is opened and options are loaded
    });
</script>
<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->