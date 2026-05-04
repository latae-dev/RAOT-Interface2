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

<style>
    .detail-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .detail-value {
        color: #212529;
        margin-bottom: 1rem;
    }

    .status-badge {
        font-size: 1.125rem;
        /* ขนาดเท่าหัวข้อ (ประมาณ 18px) */
        padding: 0.65rem 1.1rem;
        font-weight: 600;
    }

    /* สถานะผลการพิจารณา (มุมขวาของการ์ดผลการพิจารณา) ให้ match กับ status-badge */
    #approval-current-stage,
    #budget-approval-status-badge {
        font-size: 1.125rem;
        padding: 0.65rem 1.1rem;
        font-weight: 600;
    }

    .item-number {
        background: #0d6efd;
        color: white;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .serial-number-list {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.5rem;
        max-height: 150px;
        overflow-y: auto;
    }

    .serial-number-item {
        padding: 0.25rem 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .serial-number-item:last-child {
        border-bottom: none;
    }

    .view-section .view-field {
        margin-bottom: 0.25rem;
    }

    .view-label {
        display: block;
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
        letter-spacing: 0.01em;
    }

    .view-value {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.5rem;
        padding: 0.6rem 0.9rem;
        color: #1f2937;
        font-size: 0.95rem;
        line-height: 1.5;
        min-height: 42px;
        display: flex;
        align-items: center;
    }

    .view-value.view-value-multiline {
        white-space: pre-wrap;
        align-items: flex-start;
    }

    .view-value.no-padding {
        padding: 0;
    }

    .view-value .serial-number-list {
        width: 100%;
        background-color: transparent;
        border: none;
        padding: 0;
    }

    .view-value .serial-number-item {
        border-bottom: 1px solid #dee2e6;
    }

    .view-value .serial-number-item:last-child {
        border-bottom: none;
    }

    .approval-section-card {
        background: #f9fbff;
        border: 1px solid #e0e7ff;
    }

    .approval-section-card .card-header {
        border-bottom: none;
        padding-bottom: 0.5rem;
    }

    .approval-section-card .card-body {
        padding-top: 0.5rem;
    }

    .approval-required-label {
        font-weight: 600;
        color: #495057;
    }

    .approval-required-label .text-danger {
        font-size: 0.8rem;
    }

    .approval-radio-group .form-check {
        margin-bottom: 0.75rem;
    }

    .approval-history-empty {
        color: #dc3545;
        font-weight: 500;
    }

    .approval-alert {
        border-left: 4px solid #ffc107;
        background: #fff8e1;
        padding: 1rem 1.25rem;
        border-radius: 0.375rem;
        color: #8a6d3b;
    }

    /* Document Files Section Styles */
    .documents-section {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
    }

    .documents-title {
        font-size: 1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .documents-title i {
        font-size: 1.25rem;
        color: #0d6efd;
    }

    .document-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .document-item:hover {
        background: #f1f5f9;
        border-color: #0d6efd;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .document-item:last-child {
        margin-bottom: 0;
    }

    .document-icon {
        font-size: 1.5rem;
        color: #dc3545;
        min-width: 24px;
    }

    .document-info {
        flex: 1;
        min-width: 0;
    }

    .document-name {
        font-weight: 500;
        color: #212529;
        margin-bottom: 0.25rem;
        word-break: break-word;
        line-height: 1.4;
    }

    .document-meta {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .document-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .document-meta-item i {
        font-size: 0.875rem;
    }

    .document-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-document {
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-document:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-document i {
        font-size: 1rem;
    }

    .no-documents {
        text-align: center;
        padding: 2rem 1rem;
        color: #6c757d;
    }

    .no-documents i {
        font-size: 3rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }

    .no-documents p {
        margin: 0;
        font-size: 0.95rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .item-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .document-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .document-actions {
            width: 100%;
            flex-direction: column;
        }

        .btn-document {
            width: 100%;
            justify-content: center;
        }

        .document-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">พิจารณาคำขอตั้งงบลงทุน</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a></li>
                    <li class="breadcrumb-item fw-semibold"><a href="investment-budget-approvel-list.php">รายการคำขอรอพิจารณา</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">พิจารณาคำขอ</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูล...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="error-state" class="row" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="ri-error-warning-line fs-48 text-danger mb-3"></i>
                    <h5 class="text-danger">ไม่พบข้อมูล</h5>
                    <p class="text-muted" id="error-message">ไม่สามารถโหลดข้อมูลได้</p>
                    <a href="investment-budget-approvel-list.php" class="btn btn-primary mt-3">
                        <i class="ri-arrow-left-line me-2"></i>กลับไปรายการอนุมัติ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" style="display: none;">

        <!-- Header Card with Status -->
        <div class="row mb-3">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-2" id="request-number">-</h4>
                                <p class="text-muted mb-0">
                                    <i class="ri-calendar-line me-2"></i>
                                    <span id="created-date">-</span>
                                </p>
                            </div>
                            <div>
                                <span class="badge status-badge" id="status-badge">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Information -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">ข้อมูลทั่วไป</div>
                    </div>
                    <div class="card-body view-section">
                        <div class="row gy-4 gx-3">
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">ปีงบประมาณ</label>
                                    <div class="view-value" id="fiscal-year">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">เวอร์ชั่นแผน</label>
                                    <div class="view-value" id="version-plan">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">ประเภทข้อมูล</label>
                                    <div class="view-value" id="asset-type">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">รหัสประเภทข้อมูล</label>
                                    <div class="view-value" id="asset-type-code">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Creator Information -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">ข้อมูลผู้สร้างคำขอ</div>
                    </div>
                    <div class="card-body view-section">
                        <div class="row gy-4 gx-3">
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">ผู้สร้างคำขอ</label>
                                    <div class="view-value" id="created-by">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">หน่วยงาน</label>
                                    <div class="view-value" id="branch-info">-</div>
                                </div>
                            </div>
                            <!-- <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">จังหวัด/กอง</label>
                                    <div class="view-value" id="province-info">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">เขต/ฝ่าย</label>
                                    <div class="view-value" id="area-info">-</div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="view-field">
                                    <label class="view-label">สำนักงานใหญ่</label>
                                    <div class="view-value" id="head-office-info">-</div>
                                </div>
                            </div> -->
                            <div class="col-xl-12 col-lg-12" id="submission-info" style="display: none;">
                                <div class="view-field">
                                    <label class="view-label">วันที่ส่งอนุมัติ</label>
                                    <div class="view-value" id="submitted-at">-</div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12" id="approved-info" style="display: none;">
                                <div class="view-field">
                                    <label class="view-label">ผู้อนุมัติล่าสุด</label>
                                    <div class="view-value" id="approved-by">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Items Details -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">รายละเอียดคำขอ</div>
                    </div>
                    <div class="card-body">
                        <div id="items-container">
                            <!-- Items จะถูกแสดงที่นี่ -->
                        </div>

                        <!-- Documents Section -->
                        <div id="documents-section" class="documents-section" style="display: none;">
                            <div class="documents-title">
                                <i class="ri-file-list-3-line"></i>
                                <span>เอกสารแนบ</span>
                            </div>
                            <div id="documents-container">
                                <!-- Documents will be rendered here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval History -->
        <div class="row" id="approval-history-section" style="display: none;">
            <div class="col-xl-12">
                <div class="card custom-card approval-section-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="card-title">ผลการพิจารณาอนุมัติ</div>
                        <span class="badge bg-light text-primary" id="approval-current-stage" style="display: none;"></span>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th style="width: 80px;">ลำดับที่</th>
                                        <th style="width: 160px;">วันที่</th>
                                        <th style="width: 160px;">ผลการพิจารณา</th>
                                        <th style="width: 220px;">ผู้อนุมัติ</th>
                                        <th>หมายเหตุ</th>
                                    </tr>
                                </thead>
                                <tbody id="approval-history-body">
                                    <tr>
                                        <td colspan="5" class="text-center approval-history-empty">ไม่พบข้อมูล</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Permission Alert -->
        <div class="row" id="approval-permission-section" style="display: none;">
            <div class="col-xl-12">
                <div class="approval-alert" id="approval-permission-alert"></div>
            </div>
        </div>

        <!-- Approval Action -->
        <div class="row" id="approval-action-section" style="display: none;">
            <div class="col-xl-12">
                <div class="card custom-card approval-section-card">
                    <div class="card-header">
                        <div class="card-title">การพิจารณาอนุมัติ</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4">
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <div class="approval-required-label">
                                    ผลการอนุมัติ <span class="text-danger">*จำเป็นต้องเลือก</span>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <label class="form-label" for="approval-action">ผลการอนุมัติ</label>
                                <select class="form-control" id="approval-action" name="approval-action">
                                    <option value="">-- กรุณาเลือก --</option>
                                    <option value="approve">อนุมัติ</option>
                                    <option value="reject">ไม่อนุมัติ</option>
                                </select>
                            </div>
                            <div class="col-xl-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="approval-approver-name">อนุมัติโดย</label>
                                        <input type="text" class="form-control" id="approval-approver-name" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="approval-date">วันที่อนุมัติ</label>
                                        <input type="text" class="form-control" id="approval-date" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="approval-comments">หมายเหตุ <span class="text-danger" id="approval-comments-required" style="display: none;">*จำเป็นต้องกรอกเมื่อไม่อนุมัติ</span></label>
                                        <textarea class="form-control" id="approval-comments" rows="3" placeholder="กรุณาระบุหมายเหตุ"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between flex-wrap gap-2">
                        <a href="investment-budget-approvel-list.php" class="btn btn-warning" id="approval-back-btn">
                            <i class="ri-arrow-go-back-line me-1"></i>ย้อนกลับเมนู
                        </a>
                        <button type="button" class="btn btn-success" id="approval-submit-btn">
                            <i class="ri-send-plane-fill me-1"></i>ผลการพิจารณา
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Approval Section -->
        <div class="row d-none mt-3" id="budget-approval-row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="card-title mb-0">ผลการพิจารณางบประมาณ</div>
                        <span class="badge bg-light text-primary" id="budget-approval-status-badge"></span>
                    </div>
                    <div class="card-body">
                        <!-- Form mode -->
                        <div class="row gy-3" id="budget-approval-form-wrapper">
                            <div class="col-xl-4 col-lg-6">
                                <label class="form-label">ผลการอนุมัติงบประมาณ</label>
                                <select class="form-control" id="budget-approval-result">
                                    <option value="">กรุณาเลือก</option>
                                    <option value="approved">อนุมัติ</option>
                                    <option value="rejected">ไม่อนุมัติ</option>
                                </select>
                            </div>
                            <div class="col-xl-4 col-lg-6">
                                <label class="form-label">งบประมาณที่อนุมัติ (บาท)</label>
                                <input type="text" class="form-control" id="budget-approval-amount" placeholder="กรอกจำนวนเงิน" inputmode="numeric">
                            </div>
                            <div class="col-xl-12">
                                <label class="form-label">หมายเหตุการอนุมัติงบประมาณ</label>
                                <textarea class="form-control" id="budget-approval-remark" rows="3" placeholder="กรุณากรอกหมายเหตุ"></textarea>
                            </div>
                        </div>

                        <!-- View mode -->
                        <div class="row gy-3 d-none" id="budget-approval-view-wrapper">
                            <div class="col-xl-4 col-lg-6">
                                <label class="view-label">ผลการอนุมัติ</label>
                                <div class="view-value" id="budget-approval-result-display">-</div>
                            </div>
                            <div class="col-xl-4 col-lg-6">
                                <label class="view-label">งบประมาณที่อนุมัติ (บาท)</label>
                                <div class="view-value" id="budget-approval-amount-display">-</div>
                            </div>
                            <div class="col-xl-12">
                                <label class="view-label">หมายเหตุการอนุมัติ</label>
                                <div class="view-value view-value-multiline" id="budget-approval-remark-display">-</div>
                            </div>
                        </div>

                        <div class="text-muted small mt-2" id="budget-approval-meta"></div>

                        <div class="d-flex justify-content-end flex-wrap mt-3" id="budget-approval-actions" style="display: none;">
                            <button class="btn btn-primary" id="budget-save-btn">
                                <i class="ri-save-3-line me-1"></i>บันทึกผลการอนุมัติ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="investment-budget-approvel-list.php" class="btn btn-light">
                                <i class="ri-arrow-left-line me-2"></i>กลับ
                            </a>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-info" id="btn-edit" style="display: none;">
                                    <i class="ri-edit-line me-2"></i>แก้ไข
                                </button>
                                <button class="btn btn-primary" id="btn-print">
                                    <i class="ri-printer-line me-2"></i>พิมพ์
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Main Content -->

</div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

<!-- JQUERY JS -->
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

<!-- ส่ง BASEURL จาก PHP ไปยัง JavaScript -->
<script>
    window.BASEURL = '<?php echo $baseUrl; ?>';
    // บังคับโหมดพิจารณางบประมาณเพื่อให้ฟอร์มเปิดให้กรอกได้ - จะแสดงเฉพาะเมื่อคำขอได้รับการอนุมัติแล้วเท่านั้น
    window.BUDGET_APPROVAL_MODE_OVERRIDE = true;
</script>

<!-- Investment Budget Common Utilities -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-common.js?v=<?php echo time(); ?>"></script>

<!-- Investment Budget Approve JS -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-request-view.js?v=<?php echo time(); ?>"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->