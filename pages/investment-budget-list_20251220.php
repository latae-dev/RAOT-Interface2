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
            <h1 class="page-title fw-semibold fs-18 mb-0">รายการคำขอตั้งงบลงทุน</h1>
            <div class="mt-2">
                <span class="badge bg-primary-transparent" id="user-approval-level">
                    <i class="bi bi-person-badge"></i> กำลังโหลด...
                </span>
                <span class="badge bg-info-transparent ms-1" id="user-department">
                    <i class="bi bi-building"></i> กำลังโหลด...
                </span>
            </div>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายการคำขอตั้งงบลงทุน</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการคำขอตั้งงบลงทุน -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="card-title">รายการคำขอตั้งงบลงทุน</div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-xl-3">
                            <label class="form-label mt-2">รหัสคำขอตั้งงบลงทุน :</label>
                            <input type="text" class="form-control" id="filter-request-number" placeholder="กรุณากรอกรหัสคำขอ">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">เริ่มต้น :</label>
                            <div class="input-group">
                                <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                <input type="text" class="form-control flatpickr-input" id="filter-start-date" placeholder="เลือกวันเริ่มต้น">
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สิ้นสุด :</label>
                            <div class="input-group">
                                <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                <input type="text" class="form-control flatpickr-input" id="filter-end-date" placeholder="เลือกวันสิ้นสุด">
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สถานะคำขอ :</label>
                            <select class="form-control" id="filter-status">
                                <option value="">ทั้งหมด</option>
                                <option value="draft">บันทึกร่าง</option>
                                <option value="submitted">รออนุมัติ</option>
                                <option value="approved">อนุมัติ</option>
                                <option value="rejected">ปฏิเสธ</option>
                            </select>
                        </div>
                        <div class="col-xl-12 d-flex justify-content-center align-items-center flex-wrap gap-2 pt-2">
                            <a class="btn btn-success m-1" href="investment-budget-form.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>สร้างคำขอตั้งงบลงทุน</a>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <button type="button" class="btn btn-primary m-1" id="filter-search-btn"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                <button id="filter-reset-btn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                                <!-- <button type="button" class="btn btn-warning m-1" id="filter-reset-btn"><i class="bi bi-arrow-counterclockwise me-1 fw-semibold align-middle"></i>ล้างค่า</button> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List รายการคําของบประมาณรายจ่ายประจําปี -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:5%;">ลำดับ</th>
                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                        <th scope="col">รหัสคำขอตั้งงบลงทุน</th>
                                        <th scope="col">มาตรา</th>
                                        <th scope="col">ประเภทรายการ</th>
                                        <th scope="col">รายการ</th>
                                        <th scope="col">จำนวนหน่วย</th>
                                        <th scope="col">ราคาต่อหน่วย</th>
                                        <th scope="col">จำนวนเงินรวม</th>
                                        <th scope="col">หน่วยงาน</th>
                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="investment-requests-tbody">
                                    <!-- ข้อมูลจะถูกโหลดด้วย JavaScript -->
                                    <tr>
                                        <td colspan="12" class="text-center">
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

<!-- INTERNAL DATATABLES JS - ปิดการใช้งานชั่วคราว -->
<!-- <script src="<?php echo $baseUrl; ?>/assets/js/datatables.js"></script> -->


<!-- DATE & TIME PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

<!-- CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- Thai Datepicker Helper (ต้องโหลดก่อน investment-budget-list.js) -->
<script src="<?php echo $baseUrl; ?>/pages/js/thai-datepicker.helper.js"></script>

<!-- Investment Budget List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-list.js"></script>

<!-- Initialize Choices.js for filter select -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for filter-status select
        const filterStatusSelect = document.getElementById('filter-status');
        if (filterStatusSelect && typeof Choices !== 'undefined') {
            new Choices(filterStatusSelect, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false
            });
        }

        // โหลดข้อมูล user hierarchy
        loadUserHierarchyInfo();
    });

    // ฟังก์ชันโหลดข้อมูล user hierarchy
    function loadUserHierarchyInfo() {
        const baseUrl = '<?php echo $baseUrl; ?>';
        const url = `${baseUrl}/controllers/investment_budget/ib_requests_controller.php?action=user_hierarchy`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    const debug = data.data;

                    const levelBadge = document.getElementById('user-approval-level');
                    const deptBadge = document.getElementById('user-department');

                    const levelLabel =
                        debug.approval_level_label ||
                        debug.depart_detected_level_label ||
                        null;

                    if (levelBadge) {
                        if (levelLabel) {
                            levelBadge.innerHTML = `<i class="bi bi-person-badge"></i> ระดับ: ${levelLabel}`;
                            levelBadge.className = 'badge bg-primary-transparent';
                            levelBadge.style.display = '';
                        } else {
                            levelBadge.style.display = 'none';
                        }
                    }

                    if (deptBadge) {
                        const departCode =
                            debug.depart_code && debug.depart_code !== 'N/A'
                                ? debug.depart_code
                                : null;
                        if (departCode) {
                            deptBadge.innerHTML = `<i class="bi bi-building"></i> รหัสหน่วยงาน: ${departCode}`;
                            deptBadge.className = 'badge bg-info-transparent ms-1';
                            deptBadge.style.display = '';
                        } else {
                            deptBadge.style.display = 'none';
                        }
                    }
                } else {
                    // ซ่อน badges ถ้าไม่มีข้อมูล
                    const levelBadge = document.getElementById('user-approval-level');
                    const deptBadge = document.getElementById('user-department');
                    if (levelBadge) levelBadge.style.display = 'none';
                    if (deptBadge) deptBadge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading user hierarchy info:', error);
                const levelBadge = document.getElementById('user-approval-level');
                const deptBadge = document.getElementById('user-department');
                if (levelBadge) levelBadge.style.display = 'none';
                if (deptBadge) deptBadge.style.display = 'none';
            });
    }
</script>
<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
