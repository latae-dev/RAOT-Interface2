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
        <h1 class="page-title fw-semibold fs-18 mb-0">รายการคำขอตั้งงบลงทุน</h1>
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
                            <input type="text" class="form-control" id="approval-filter-request-number" placeholder="กรุณากรอก รหัสคำขอ">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">เริ่มต้น :</label>
                            <div class="input-group">
                                <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                <input type="text" class="form-control flatpickr-input" id="approval-filter-start-date" placeholder="เลือกวันเริ่มต้น">
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สิ้นสุด :</label>
                            <div class="input-group">
                                <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                <input type="text" class="form-control flatpickr-input" id="approval-filter-end-date" placeholder="เลือกวันสิ้นสุด">
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label mt-2">สถานะคำขอ :</label>
                            <select class="form-control" id="approval-filter-status">
                                <option value="submitted" selected>รออนุมัติ</option>
                                <option value="approved">อนุมัติแล้ว</option>
                                <option value="rejected">ปฏิเสธแล้ว</option>
                            </select>
                        </div>
                        <div class="col-xl-12 d-flex justify-content-center align-items-center flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-primary m-1" id="approval-filter-search-btn"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                            <button type="button" id="approval-filter-reset-btn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                        </div>
                    </div>
                </div>

                <!-- List รายการคำขอตั้งงบลงทุน -->
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
                                <tbody id="approval-requests-tbody">
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
                        <div id="approval-pagination-info">กำลังแสดง 0 รายการ</div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation" class="pagination-style-4">
                                <ul class="pagination mb-0" id="approval-pagination-controls"></ul>
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


<!-- DATE & TIME PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

<!-- CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- Thai Datepicker Helper (ต้องโหลดก่อน investment-budget-approvel-list.js) -->
<script src="<?php echo $baseUrl; ?>/pages/js/thai-datepicker.helper.js"></script>

<!-- Investment Budget Approval List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-approvel-list.js"></script>

<!-- Initialize Choices.js for filter select -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // รอให้ DOM พร้อมสมบูรณ์ก่อน
        setTimeout(function() {
            // Initialize Choices.js for approval-filter-status select
            const filterStatusSelect = document.getElementById('approval-filter-status');

            console.log('🔍 Initializing Choices.js for approval-filter-status');
            console.log('  - Element found:', !!filterStatusSelect);
            console.log('  - Choices available:', typeof Choices !== 'undefined');

            if (filterStatusSelect && typeof Choices !== 'undefined') {
                try {
                    const choicesInstance = new Choices(filterStatusSelect, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholder: true,
                        placeholderValue: 'เลือกสถานะ'
                    });
                    console.log('✅ Choices.js initialized successfully for approval-filter-status');

                    // Store instance for later use if needed
                    filterStatusSelect.choicesInstance = choicesInstance;
                } catch (error) {
                    console.error('❌ Error initializing Choices.js:', error);
                }
            } else {
                if (!filterStatusSelect) {
                    console.warn('⚠️ Element #approval-filter-status not found');
                }
                if (typeof Choices === 'undefined') {
                    console.warn('⚠️ Choices library not loaded');
                }
            }
        }, 500); // รอ 500ms ให้ทุกอย่างพร้อม
    });
</script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->