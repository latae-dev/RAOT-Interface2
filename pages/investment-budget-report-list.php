<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/choices.js/public/assets/styles/choices.min.css">
<style>
    .modal-cell {
        cursor: pointer;
        background-color: #fffacd;
        min-height: 30px;
        padding: 5px;
    }

    .modal-cell:hover {
        background-color: #ffeb3b;
    }

    .modal-cell:empty:before {
        content: 'คลิกเพื่อเลือก';
        color: #999;
        font-style: italic;
    }

    .validation-error {
        border: 1px solid #dc3545 !important;
        background-color: #fff5f5 !important;
    }
</style>
<?php $styles = ob_get_clean(); ?>

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">รายงานคำขอตั้งงบลงทุน</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายงานคำขอตั้งงบลงทุน</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="card-title">ค้นหาและกรองข้อมูล</div>
                </div>

                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">รหัสคำขอตั้งงบลงทุน:</label>
                            <input type="text" class="form-control" id="filter-request-number" placeholder="กรุณากรอก รหัสคำขอตั้งงบลงทุน">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">เริ่มต้น:</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                    <input type="text" class="form-control flatpickr-input" id="filter-start-date" placeholder="กำหนด วันเริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สิ้นสุด:</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"><i class="ri-calendar-line"></i></div>
                                    <input type="text" class="form-control flatpickr-input" id="filter-end-date" placeholder="กำหนด วันสิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">ปีงบประมาณ:</label>
                            <select class="form-control" id="filter-fiscal-year">
                                <option value="">กรุณาเลือก</option>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">หน่วยงาน:</label>
                            <select class="form-control" id="filter-department">
                                <option value="">ทั้งหมด</option>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">แหล่งงบประมาณ:</label>
                            <select class="form-control" id="filter-budget-source">
                                <option value="">ทั้งหมด</option>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">ประเภทข้อมูล:</label>
                            <select class="form-control" id="filter-asset-type">
                                <option value="">ทั้งหมด</option>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">ประเภทรายการนำข้อมูลออก:</label>
                            <select class="form-control" id="export-type-select">
                                <option value="">กรุณาเลือก</option>
                                <option value="asset">ส่งออกข้อมูล (.xlsx)</option>
                                <option value="sap">ส่งออกข้อมูล SAP (.txt)</option>
                                <option value="asset-detail">ส่งออกรายงาน Asset (.xlsx)</option>
                                <option value="project">ส่งออกข้อมูลรายงาน (.csv)</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <button id="filter-search-btn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                <button id="filter-reset-btn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                                <button id="export-btn" class="btn btn-success m-1"><i class="bx bx-export me-1 fw-semibold align-middle"></i>นำข้อมูลออก</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body p-0 product-checkout">
                    <ul class="nav nav-tabs tab-style-2 d-sm-flex d-block border-bottom border-block-end-dashed" id="myTab1" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane" type="button" role="tab" aria-controls="all-tab" aria-selected="true">
                                <i class="ri-truck-line me-2 align-middle"></i>ทั้งหมด
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment-tab-pane" type="button" role="tab" aria-controls="equipment-tab" aria-selected="false">
                                <i class="ri-user-3-line me-2 align-middle"></i>ครุภัณฑ์ เครื่องจักรและอุปกรณ์/เครื่องใช้สำนักงาน/ยานพาหนะ/โปรแกรมคอมพิวเตอร์
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="construction-tab" data-bs-toggle="tab" data-bs-target="#construction-tab-pane" type="button" role="tab" aria-controls="construction-tab" aria-selected="false">
                                <i class="ri-bank-card-line me-2 align-middle"></i>ที่ดิน / สิ่งก่อสร้าง
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="plantation-tab" data-bs-toggle="tab" data-bs-target="#plantation-tab-pane" type="button" role="tab" aria-controls="plantation-tab" aria-selected="false">
                                <i class="ri-checkbox-circle-line me-2 align-middle"></i>สวนยาง / สวนปาล์ม
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Tab ทั้งหมด -->
                        <div class="tab-pane fade show active border-0 p-0" id="all-tab-pane" role="tabpanel" tabindex="0">
                            <div class="card-body border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="fs-15 fw-semibold flex-grow-1 text-center">
                                        <div>รายงานคำขอตั้งงบลงทุนทั้งหมด <span id="fiscal-year-display-all"></span></div>
                                    </div>
                                    <button id="export-excel-all-tab-btn" class="btn btn-success btn-sm"><i class="bx bx-export me-1"></i>Export Excel</button>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead class="text-center">
                                            <tr>
                                                <th scope="col" style="width:50px;">ลำดับ</th>
                                                <th scope="col">วันที่ดำเนินรายการ</th>
                                                <th scope="col">รหัสคำขอ</th>
                                                <th scope="col">ชื่อโครงการ</th>
                                                <th scope="col">ปีงบประมาณ</th>
                                                <th scope="col">หน่วยงาน</th>
                                                <th scope="col">สถานะการส่งอนุมัติ</th>
                                                <th scope="col">ผลการพิจารณางบประมาณ</th>
                                                <th scope="col" style="width:150px;">การจัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="all-tbody">
                                            <tr>
                                                <td colspan="9" class="text-center">กำลังโหลดข้อมูล...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="d-flex align-items-center">
                                    <div id="pagination-info-all">กำลังแสดง 0 รายการ</div>
                                    <div class="ms-auto">
                                        <nav aria-label="Page navigation" class="pagination-style-4">
                                            <ul class="pagination mb-0" id="pagination-all"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab ครุภัณฑ์ -->
                        <div class="tab-pane fade border-0 p-0" id="equipment-tab-pane" role="tabpanel" tabindex="0">
                            <div class="card-body border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="fs-15 fw-semibold flex-grow-1 text-center">
                                        <div>รายงานคำขอตั้งงบลงทุน แยกตามครุภัณฑ์ เครื่องจักรและอุปกรณ์ <span id="fiscal-year-display-equipment"></span></div>
                                    </div>
                                    <button id="export-excel-equipment-btn" class="btn btn-success btn-sm"><i class="bx bx-export me-1"></i>Export Excel</button>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table class="table text-nowrap table-bordered" id="equipment-table">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" style="width:50px;">ลำดับ</th>
                                                <th rowspan="2">หน่วยงาน</th>
                                                <th rowspan="2">วันที่ดำเนินรายการ</th>
                                                <th rowspan="2">รหัสคำขอ</th>
                                                <th rowspan="2">รายการ</th>
                                                <th rowspan="2">จำนวนหน่วย</th>
                                                <th rowspan="2">ราคาต่อหน่วย</th>
                                                <th rowspan="2">รวมเงิน (บาท)</th>
                                                <th rowspan="2">เหตุผลและความจำเป็น</th>
                                                <th rowspan="2">รายการ</th>
                                                <th colspan="4">รหัสครุภัณฑ์</th>
                                                <th rowspan="2" style="width:150px;">การจัดการ</th>
                                            </tr>
                                            <tr>
                                                <th>ชนิด</th>
                                                <th>ประเภท</th>
                                                <th>ประเภทครุภัณฑ์</th>
                                                <th>หน่วยนับ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="equipment-tbody">
                                            <tr>
                                                <td colspan="15" class="text-center">กำลังโหลดข้อมูล...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="d-flex align-items-center">
                                    <div id="pagination-info-equipment">กำลังแสดง 0 รายการ</div>
                                    <div class="ms-auto">
                                        <nav aria-label="Page navigation" class="pagination-style-4">
                                            <ul class="pagination mb-0" id="pagination-equipment"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab ที่ดิน/สิ่งก่อสร้าง -->
                        <div class="tab-pane fade border-0 p-0" id="construction-tab-pane" role="tabpanel" tabindex="0">
                            <div class="card-body border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="fs-15 fw-semibold flex-grow-1 text-center">
                                        <div>รายงานคำขอตั้งงบลงทุน แยกตาม ที่ดิน/สิ่งก่อสร้าง <span id="fiscal-year-display-construction"></span></div>
                                    </div>
                                    <button id="export-excel-construction-btn" class="btn btn-success btn-sm"><i class="bx bx-export me-1"></i>Export Excel</button>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table class="table text-nowrap table-bordered" id="construction-table">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" style="width:50px;">ลำดับ</th>
                                                <th rowspan="2">หน่วยงาน</th>
                                                <th rowspan="2">วันที่ดำเนินรายการ</th>
                                                <th rowspan="2">รหัสคำขอ</th>
                                                <th rowspan="2">รายการ</th>
                                                <th rowspan="2">รวมเงิน (บาท)</th>
                                                <th rowspan="2">เหตุผลและความจำเป็น</th>
                                                <th rowspan="2">รายการ</th>
                                                <th colspan="4">รหัสครุภัณฑ์</th>
                                                <th rowspan="2">วันที่คาดว่าจะเบิกจ่าย</th>
                                                <th rowspan="2" style="width:150px;">การจัดการ</th>
                                            </tr>
                                            <tr>
                                                <th>ชนิด</th>
                                                <th>ประเภท</th>
                                                <th>ประเภทครุภัณฑ์</th>
                                                <th>หน่วยนับ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="construction-tbody">
                                            <tr>
                                                <td colspan="14" class="text-center">กำลังโหลดข้อมูล...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="d-flex align-items-center">
                                    <div id="pagination-info-construction">กำลังแสดง 0 รายการ</div>
                                    <div class="ms-auto">
                                        <nav aria-label="Page navigation" class="pagination-style-4">
                                            <ul class="pagination mb-0" id="pagination-construction"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab สวนยาง/สวนปาล์ม -->
                        <div class="tab-pane fade border-0 p-0" id="plantation-tab-pane" role="tabpanel" tabindex="0">
                            <div class="card-body border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="fs-15 fw-semibold flex-grow-1 text-center">
                                        <div>รายงานคำขอตั้งงบลงทุน สวนยาง / สวนปาล์ม <span id="fiscal-year-display-plantation"></span></div>
                                    </div>
                                    <button id="export-excel-plantation-btn" class="btn btn-success btn-sm"><i class="bx bx-export me-1"></i>Export Excel</button>
                                </div>
                                <div class="table-responsive mt-2">
                                    <table class="table text-nowrap table-bordered" id="plantation-table">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" style="width:50px;">ลำดับ</th>
                                                <th rowspan="2">หน่วยงาน</th>
                                                <th rowspan="2">วันที่ดำเนินรายการ</th>
                                                <th rowspan="2">รหัสคำขอ</th>
                                                <th rowspan="2">รายการ</th>
                                                <th rowspan="2">รวมเงิน (บาท)</th>
                                                <th rowspan="2">เหตุผลและความจำเป็น</th>
                                                <th rowspan="2">รายการ</th>
                                                <th colspan="4">รหัสครุภัณฑ์</th>
                                                <th rowspan="2">รหัสบัญชี</th>
                                                <th rowspan="2" style="width:150px;">การจัดการ</th>
                                            </tr>
                                            <tr>
                                                <th>ชนิด</th>
                                                <th>ประเภท</th>
                                                <th>ประเภทครุภัณฑ์</th>
                                                <th>หน่วยนับ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="plantation-tbody">
                                            <tr>
                                                <td colspan="14" class="text-center">กำลังโหลดข้อมูล...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="d-flex align-items-center">
                                    <div id="pagination-info-plantation">กำลังแสดง 0 รายการ</div>
                                    <div class="ms-auto">
                                        <nav aria-label="Page navigation" class="pagination-style-4">
                                            <ul class="pagination mb-0" id="pagination-plantation"></ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: เลือกรายการ (Fund Area) -->
<div class="modal fade" id="modal-fund-area" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">เลือกรายการ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <select class="form-control" id="select-fund-area">
                    <option value="">กรุณาเลือกรายการ</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-fund-area">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal: เลือกชนิดรหัสครุภัณฑ์ -->
<div class="modal fade" id="modal-equipment-code-type" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">เลือกชนิดรหัสครุภัณฑ์</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <select class="form-control" id="select-equipment-code-type">
                    <option value="">กรุณาเลือก ชนิดรหัสครุภัณฑ์</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-equipment-code-type">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: เลือกประเภทรหัสครุภัณฑ์ -->
<div class="modal fade" id="modal-equipment-type" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">เลือกประเภทรหัสครุภัณฑ์</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <select class="form-control" id="select-equipment-type">
                    <option value="">กรุณาเลือก ประเภทรหัสครุภัณฑ์</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-equipment-type">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: เลือกหน่วยนับ -->
<div class="modal fade" id="modal-count-units" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">เลือกหน่วยนับ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <select class="form-control" id="select-count-units">
                    <option value="">กรุณาเลือก หน่วยนับ</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-count-units">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: กรอกรหัสบัญชี -->
<div class="modal fade" id="modal-account-code" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">กรอกรหัสบัญชี</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <input type="text" class="form-control" id="input-account-code" placeholder="กรอกรหัสบัญชี">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-account-code">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

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

<!-- Thai Datepicker Helper (ต้องโหลดก่อน investment-budget-report-list.js) -->
<script src="<?php echo $baseUrl; ?>/pages/js/thai-datepicker.helper.js"></script>

<!-- Investment Budget Report List JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-report-list.js?v=<?= time(); ?>"></script>

<!-- Initialize Choices.js for filter selects -->
<script>
    // Global function to initialize or update Choices.js
    window.initializeReportChoices = function() {
        console.log('🎨 Initializing Choices.js for report filters...');

        const selectIds = [
            'filter-fiscal-year',
            'filter-department',
            'filter-budget-source',
            'filter-asset-type',
            'export-type-select',
            'select-equipment-code-type',
            'select-equipment-type',
            'select-count-units'
        ];

        selectIds.forEach(function(selectId) {
            const selectElement = document.getElementById(selectId);

            if (selectElement && typeof Choices !== 'undefined') {
                // ถ้ามี instance อยู่แล้ว ให้ทำลายก่อน
                if (selectElement.choicesInstance) {
                    selectElement.choicesInstance.destroy();
                    console.log('  🔄 Destroyed old Choices instance for', selectId);
                }

                // สร้าง instance ใหม่
                try {
                    selectElement.choicesInstance = new Choices(selectElement, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        removeItemButton: false,
                        placeholder: true,
                        placeholderValue: selectId.startsWith('filter-') ? 'ทั้งหมด' : 'กรุณาเลือก',
                        searchPlaceholderValue: 'ค้นหา...',
                        noResultsText: 'ไม่พบข้อมูล'
                    });
                    console.log('  ✅ Choices initialized for', selectId);
                } catch (error) {
                    console.error('  ❌ Error initializing Choices for', selectId, error);
                }
            }
        });
    };

    // Initialize เมื่อ DOM พร้อม
    document.addEventListener('DOMContentLoaded', function() {
        window.initializeReportChoices();
    });
</script>

<?php $scripts = ob_get_clean(); ?>

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
