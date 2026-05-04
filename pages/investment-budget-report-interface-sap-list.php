<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/styles/choices.min.css">
<?php $styles = ob_get_clean(); ?>

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">รายงาน Interface ข้อมูลเข้า SAP</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายงาน Interface ข้อมูลเข้า SAP</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายงาน Interface ข้อมูลเข้า SAP -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="card-title">รายงาน Interface ข้อมูลเข้า SAP</div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-4 mb-2">
                            <label class="form-label mt-2">รหัสคำขอตั้งงบลงทุน:</label>
                            <input type="text" class="form-control" id="filter-request-number" placeholder="กรุณากรอก รหัสคำขอตั้งงบลงทุน">
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">เริ่มต้น:</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                    <input type="text" class="form-control flatpickr-input" id="filter-start-date" placeholder="กำหนด วันเริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">สิ้นสุด:</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                    <input type="text" class="form-control flatpickr-input" id="filter-end-date" placeholder="กำหนด วันสิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">ปีงบประมาณ:</label>
                            <select class="form-control" id="filter-fiscal-year">
                                <option value="">กรุณาเลือก</option>
                            </select>
                        </div> -->
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">สถานะการอนุมัติ:</label>
                            <select class="form-control" id="filter-approval-status">
                                <option value="">กรุณาเลือก</option>
                                <option value="approved">อนุมัติ</option>
                                <option value="rejected">ไม่อนุมัติ</option>
                            </select>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">สถานะ Interface SAP:</label>
                            <select class="form-control" id="filter-sap-status">
                                <option value="">กรุณาเลือก</option>
                                <option value="not_synced">Not Synced</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <button id="filter-search-btn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                <button id="filter-reset-btn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                                <button id="export-excel-btn" class="btn btn-success m-1"><i class="bx bx-export me-1 fw-semibold align-middle"></i>นำข้อมูลออก (.xlsx)</button>
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
                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                        <th scope="col">รหัสคำขอตั้งงบลงทุน</th>
                                        <th scope="col">ศูนย์เงินทุน</th>
                                        <th scope="col">เงินทุน</th>
                                        <th scope="col">ขอบเขตหน้าที่</th>
                                        <th scope="col">ภาระผูกพัน</th>
                                        <th scope="col">ปีงบประมาณ</th>
                                        <th scope="col">จำนวนเงิน</th>
                                        <th scope="col">โครงการ</th>
                                        <th scope="col">สถานะการอนุมัติ</th>
                                        <th scope="col">สถานะ Interface SAP</th>
                                    </tr>
                                </thead>
                                <tbody id="sap-tbody">
                                    <tr>
                                        <td colspan="12" class="text-center">กำลังโหลดข้อมูล...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top-0">
                    <div class="d-flex align-items-center">
                        <div id="pagination-info">กำลังแสดง 0 รายการ</div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation" class="pagination-style-4">
                                <ul class="pagination mb-0" id="pagination"></ul>
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
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

<!-- DATE & TIME PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

<!-- CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- SheetJS (xlsx) for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<!-- Thai Datepicker Helper (ต้องโหลดก่อน investment-budget-report-interface-sap-list.js) -->
<script src="<?php echo $baseUrl; ?>/pages/js/thai-datepicker.helper.js"></script>

<!-- Investment Budget Report Interface SAP List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-report-interface-sap-list.js"></script>

<!-- Initialize Choices.js for filter selects -->
<script>
    // Global function for re-initializing Choices.js after loading fiscal years
    window.initializeSAPChoices = function() {
        const selectIds = ['filter-fiscal-year', 'filter-approval-status', 'filter-sap-status'];

        selectIds.forEach(function(selectId) {
            const selectElement = document.getElementById(selectId);
            if (selectElement && typeof Choices !== 'undefined') {
                // Destroy existing instance if any
                if (selectElement.choicesInstance) {
                    selectElement.choicesInstance.destroy();
                }
                // Create new instance
                selectElement.choicesInstance = new Choices(selectElement, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false
                });
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Initial Choices.js setup
        window.initializeSAPChoices();
    });
</script>

<?php $scripts = ob_get_clean(); ?>

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->