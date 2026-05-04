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
            <h1 class="page-title fw-semibold fs-18 mb-0">จัดการข้อมูลหน่วยงาน</h1>
            <p class="text-muted mb-0">จัดการข้อมูลหน่วยงานแบบ Hierarchy (สำนักงานใหญ่ > เขต > จังหวัด > สาขา)</p>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ผู้ดูแลระบบ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">จัดการข้อมูลหน่วยงาน</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการหน่วยงาน -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="card-title">รายการหน่วยงาน</div>
                        <p class="text-muted mb-0 small">แสดงรายการหน่วยงานทั้งหมดพร้อมข้อมูล Hierarchy</p>
                    </div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-3">
                            <label class="form-label mt-2">รหัสหน่วยงาน :</label>
                            <input type="text" class="form-control" id="filter-depart-code" placeholder="กรุณากรอกรหัสหน่วยงาน">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">ชื่อหน่วยงาน :</label>
                            <input type="text" class="form-control" id="filter-depart-name" placeholder="กรุณากรอกชื่อหน่วยงาน">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">ประเภทหน่วยงาน :</label>
                            <select class="form-control" id="filter-depart-type">
                                <option value="">ทั้งหมด</option>
                                <option value="1">สำนักงานใหญ่ (HQ)</option>
                                <option value="2">เขต (Area)</option>
                                <option value="3">จังหวัด (Province)</option>
                                <option value="4">สาขา (Branch)</option>
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
                        <div class="col-xl-12 d-flex justify-content-center align-items-center flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-success m-1" id="btn-create-department" data-bs-toggle="modal" data-bs-target="#department-modal">
                                <i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างหน่วยงานใหม่
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

                <!-- List รายการหน่วยงาน -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered" id="departments-table">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:5%;">ลำดับ</th>
                                        <th scope="col">รหัสหน่วยงาน</th>
                                        <th scope="col">ชื่อหน่วยงาน</th>
                                        <th scope="col">ประเภท</th>
                                        <th scope="col">หน่วยงานแม่</th>
                                        <th scope="col">สถานะ</th>
                                        <th scope="col" style="width:100px;">จำนวน Users</th>
                                        <th scope="col">วันที่สร้าง</th>
                                        <th scope="col">วันที่อัปเดต</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="departments-tbody">
                                    <!-- ข้อมูลจะถูกโหลดด้วย JavaScript -->
                                    <tr>
                                        <td colspan="10" class="text-center">
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
                        <div class="ms-auto">
                            <nav aria-label="Page navigation" class="pagination-style-4">
                                <ul class="pagination mb-0" id="pagination-controls"></ul>
                            </nav>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- End:: row-4 -->

</div>

<!-- Modal สำหรับสร้าง/แก้ไขหน่วยงาน -->
<div class="modal fade" id="department-modal" tabindex="-1" aria-labelledby="department-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="department-modal-label">สร้างหน่วยงานใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="department-form">
                    <input type="hidden" id="department-id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">รหัสหน่วยงาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department-code" name="depart_code" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ชื่อหน่วยงาน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department-name" name="depart_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ประเภทหน่วยงาน <span class="text-danger">*</span></label>
                            <select class="form-control" id="department-type" name="depart_type_id" required>
                                <option value="">เลือกประเภท</option>
                                <option value="1">สำนักงานใหญ่ (HQ)</option>
                                <option value="2">เขต (Area)</option>
                                <option value="3">จังหวัด (Province)</option>
                                <option value="4">สาขา (Branch)</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หน่วยงานแม่</label>
                            <select class="form-control" id="department-parent" name="parent_id">
                                <option value="">ไม่มี (เป็นหน่วยงานระดับสูงสุด)</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">เลือกหน่วยงานแม่ตามประเภทที่เลือก</small>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="department-is-active" name="is_active" checked>
                                <label class="form-check-label" for="department-is-active">
                                    ใช้งาน
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-department">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับดูรายละเอียดหน่วยงาน -->
<div class="modal fade" id="view-department-modal" tabindex="-1" aria-labelledby="view-department-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view-department-modal-label">รายละเอียดหน่วยงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view-department-content">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับดู Hierarchy -->
<div class="modal fade" id="hierarchy-modal" tabindex="-1" aria-labelledby="hierarchy-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hierarchy-modal-label">โครงสร้างหน่วยงาน (Hierarchy)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="hierarchy-tree-container">
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

<!-- Modal สำหรับดูรายชื่อ Users -->
<div class="modal fade" id="users-modal" tabindex="-1" aria-labelledby="users-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="users-modal-label">รายชื่อ Users ในหน่วยงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="users-container">
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

<!-- Department List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/department-list.js?v=<?php echo time(); ?>"></script>

<!-- Initialize Choices.js for filter selects -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for filter selects
        const filterSelects = ['filter-depart-type', 'filter-is-active'];
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

        // Initialize department type select in modal
        const departmentTypeSelect = document.getElementById('department-type');
        if (departmentTypeSelect && typeof Choices !== 'undefined') {
            new Choices(departmentTypeSelect, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
        }

        // Initialize department parent select in modal
        const departmentParentSelect = document.getElementById('department-parent');
        if (departmentParentSelect && typeof Choices !== 'undefined') {
            new Choices(departmentParentSelect, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false
            });
        }
    });
</script>
<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->