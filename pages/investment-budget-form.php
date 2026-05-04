<?php

/**
 * แบบฟอร์มคำขอตั้งงบลงทุน (Investment Budget Request Form)
 * ฟีเจอร์หลัก:
 * - เลือกปีงบประมาณ, เวอร์ชั่นแผน, ประเภทข้อมูล
 * - แสดงการ์ดตามประเภทข้อมูลที่เลือก
 * - Validation แบบ real-time
 * - บันทึกร่างและส่งคำขออนุมัติ
 
 */

// สร้าง Base URL สำหรับ assets
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST']
    . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEditMode = $requestId > 0;

// ดึงข้อมูล user hierarchy เพื่อกำหนด default version_plan
session_start();
$defaultVersionPlan = null;
$userHierarchyDebug = null;
$detectedLevelInfo = null;
$detectedLevel = null;

// ใช้ logic จาก ib_approval_hierarchy.php
if (!empty($_SESSION['user_id'])) {
    // Include files ที่จำเป็น
    include_once __DIR__ . '/../configs/database.php';
    include_once __DIR__ . '/../models/investment_budget/ib_requests_model.php';
    include_once __DIR__ . '/../models/user/depart_model.php';
    include_once __DIR__ . '/../controllers/investment_budget/ib_approval_hierarchy.php';

    try {
        $database = new Database();
        $db = $database->connect('raot_db_dqas');
        $ibRequestsModel = new IbRequestsModel($db);
        $approvalHierarchy = new IbApprovalHierarchy($ibRequestsModel, $db);
        $departModel = new DepartModel($db);

        // ดึง context และข้อมูล hierarchy
        $context = $approvalHierarchy->getCurrentUserContext();
        if ($context) {
            $departCode = $context['depart_code'] ?? null;

            // หาก depart_code ใน session ไม่พร้อมใช้งาน ให้ดึงจาก depart_id
            if (($departCode === null || $departCode === '' || strtoupper((string) $departCode) === 'N/A') &&
                !empty($context['depart_id'])
            ) {
                $departRow = $departModel->readDepartOne($context['depart_id']);
                if ($departRow && !empty($departRow['depart_code'])) {
                    $departCode = $departRow['depart_code'];
                    $context['depart_code'] = $departCode;
                    $_SESSION['depart_code'] = $departCode;
                }
            }

            $detectedLevelInfo = $approvalHierarchy->resolveLevelFromDepartCode($departCode ?? null);
            if ($detectedLevelInfo) {
                $detectedLevel = $detectedLevelInfo['level'] ?? null;
            }

            $userHierarchyDebug = $approvalHierarchy->getDebugInfo($context);
        }

        $levelToVersionMapping = [
            'branch' => 1,       // id=1, code=s-1, สาขา
            'province' => 2,     // id=2, code=s-2, จังหวัด
            'area' => 3,         // id=3, code=s-3, เขต
            'head_office' => 4,  // id=4, code=s-4, สำนักงานใหญ่
        ];

        if ($detectedLevel) {
            $defaultVersionPlan = $levelToVersionMapping[$detectedLevel] ?? null;
            if (is_array($userHierarchyDebug)) {
                $levelLabels = [
                    'branch' => 'ระดับสาขา',
                    'province' => 'ระดับจังหวัด/กอง',
                    'area' => 'ระดับเขต/ฝ่าย',
                    'head_office' => 'ระดับสำนักงานใหญ่',
                ];
                $userHierarchyDebug['depart_detected_level'] = $detectedLevel;
                $userHierarchyDebug['depart_detected_level_label'] = $levelLabels[$detectedLevel] ?? $detectedLevel;
            }
        }

        if ($defaultVersionPlan === null && $userHierarchyDebug) {
            $approvalLevel = $userHierarchyDebug['approval_level'] ?? null;
            if ($approvalLevel) {
                $defaultVersionPlan = $levelToVersionMapping[$approvalLevel] ?? null;
            }
        }
    } catch (Exception $e) {
        error_log('Error getting user hierarchy: ' . $e->getMessage());
    }
}
?>

<?php ob_start(); // เริ่ม output buffering สำหรับ CSS 
?>

<!-- QUILL CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.snow.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.bubble.css">

<!-- FILEPOND CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone.css">

<!-- PRISM CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/prismjs/themes/prism-coy.min.css">

<!-- FILEPOND CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">

<!-- DATA TABLES CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

<!-- DATE & TIME PICKER CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">

<!-- SWEETALERT2 CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.css">

<!-- CSS สำหรับ Validation ของฟอร์ม -->
<style>
    /**
 * ========================================
 * VALIDATION FEEDBACK STYLES
 * ========================================
 */

    /* สไตล์พื้นฐานสำหรับข้อความ error */
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: -0.1rem;
        /* ใช้ negative margin เพื่อให้ชิดกับ input */
        margin-bottom: 0;
        padding-top: 0.15rem;
        font-size: 0.8rem;
        color: #dc3545;
        /* สีแดง Bootstrap */
        line-height: 1.1;
    }

    /* สไตล์พื้นฐานสำหรับข้อความ success */
    .valid-feedback {
        display: none;
        width: 100%;
        margin-top: -0.1rem;
        margin-bottom: 0;
        padding-top: 0.15rem;
        font-size: 0.8rem;
        color: #198754;
        /* สีเขียว Bootstrap */
        line-height: 1.1;
    }

    /**
 * ========================================
 * FORM CONTROL SPACING
 * ========================================
 */

    /* ลด spacing ของ form controls เพื่อให้ feedback ชิดขึ้น */
    .form-control,
    .form-select {
        margin-bottom: 0.1rem;
    }

    /* เพิ่ม spacing กลับสำหรับ element ที่อยู่หลัง feedback */
    .invalid-feedback+*,
    .valid-feedback+* {
        margin-top: 0.5rem;
    }

    /* สถานะการโหลดฟอร์ม */
    /**
 * ========================================
 * CHOICES.JS INTEGRATION
 * ========================================
 */

    /* แก้ไข spacing ของ Choices.js dropdown */
    .choices {
        margin-bottom: 0.1rem !important;
    }

    /* แก้ z-index และ overflow ของ Choices.js dropdown */
    .choices__list--dropdown {
        z-index: 9999 !important;
    }

    .choices[data-type*=select-one] .choices__inner,
    .choices[data-type*=select-multiple] .choices__inner {
        z-index: 1;
    }

    /* แก้ปัญหา dropdown ถูกบังด้วย table overflow */
    .table-responsive {
        overflow: visible !important;
    }

    /* แก้ปัญหา dropdown ถูกบังด้วย tbody/table */
    table tbody {
        overflow: visible !important;
    }

    /* ให้ table cell ที่มี dropdown มี position relative และ overflow visible */
    table td:has(.choices) {
        position: relative;
        overflow: visible !important;
    }

    /* ให้ feedback ชิดกับ Choices.js dropdown มากพิเศษ */
    .choices+.invalid-feedback {
        margin-top: -0.2rem !important;
        padding-top: 0.2rem;
    }

    .choices+.valid-feedback {
        margin-top: -0.2rem !important;
        padding-top: 0.2rem;
    }

    /**
 * ========================================
 * VALIDATION DISPLAY RULES
 * ========================================
 */

    /* แสดง invalid feedback เมื่อ field อยู่สถานะ invalid */
    [data-validation-state="invalid"]~.invalid-feedback {
        display: block !important;
    }

    /* แสดง valid feedback เมื่อ field อยู่สถานะ valid */
    [data-validation-state="valid"]~.valid-feedback {
        display: block !important;
    }

    /* กำหนดสไตล์เสริมเมื่ออยู่ในสถานะ invalid/valid */
    [data-validation-state="invalid"].form-control,
    [data-validation-state="invalid"].form-select {
        border-color: #dc3545 !important;
        box-shadow: none;
    }

    [data-validation-state="valid"].form-control,
    [data-validation-state="valid"].form-select {
        border-color: #198754 !important;
        box-shadow: none;
    }

    .choices[data-validation-state="invalid"] .choices__inner {
        border-color: #dc3545 !important;
        box-shadow: none;
    }

    .choices[data-validation-state="valid"] .choices__inner {
        border-color: #198754 !important;
        box-shadow: none;
    }

    /* Bootstrap default validation behavior */
    .was-validated .form-control:invalid~.invalid-feedback,
    .was-validated .form-select:invalid~.invalid-feedback {
        display: block !important;
    }

    .was-validated .form-control:valid~.valid-feedback,
    .was-validated .form-select:valid~.valid-feedback {
        display: block !important;
    }

    /* Class สำหรับ JavaScript ใช้ force แสดง feedback */
    .invalid-feedback.show {
        display: block !important;
    }

    .valid-feedback.show {
        display: block !important;
    }

    .form-label.required-indicator::after {
        content: ' *';
        color: #dc3545;
    }

    /* ล็อค dropdown เมื่อถูกกำหนดตามสิทธิ์ */
    .choices-locked {
        pointer-events: none;
        opacity: 0.85;
    }

    .choices-locked .choices__inner {
        background-color: #f5f5f5;
    }

    #version-plan-select[data-locked-by-hierarchy="true"] {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }
</style>

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div id="investment-budget-form-container" class="container-fluid needs-validation" novalidate>

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">
                <?php echo $isEditMode ? 'แก้ไขคำขอตั้งงบลงทุน' : 'แบบฟอร์มคำขอตั้งงบลงทุน'; ?>
            </h1>
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
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">
                        <?php echo $isEditMode ? 'แก้ไขคำขอตั้งงบลงทุน' : 'แบบฟอร์มคำขอตั้งงบลงทุน'; ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <div id="investment-budget-form-loading" class="card custom-card mb-3">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p>
        </div>
    </div>

    <div id="investment-budget-form-content" class="d-none">
        <div class="row">
            <div class="col-xl-12">

                <!-- ข้อมูลทั่วไป -->
                <div class="card custom-card">

                    <div class="card-header">
                        <div class="card-title">ข้อมูลทั่วไป</div>
                    </div>

                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- ปีงบประมาณ -->
                            <div class="col-xl-6">
                                <label class="form-label mt-2">ปีงบประมาณ : <span class="text-danger">*</span></label>
                                <select class="form-control" name="fiscal-year" id="fiscal-year-select" required>
                                    <option value="">กรุณาเลือก</option>

                                </select>
                                <div class="invalid-feedback">
                                    กรุณาเลือกปีงบประมาณ
                                </div>
                            </div>

                            <!-- เวอร์ชั่น (แผน) -->
                            <div class="col-xl-6">
                                <label class="form-label mt-2">เวอร์ชั่น (แผน): <span class="text-danger">*</span></label>
                                <select class="form-control" name="version-plan" id="version-plan-select" required>
                                    <option value="">กรุณาเลือก</option>
                                    <option value="1" <?php echo ($defaultVersionPlan === 1) ? 'selected' : ''; ?>>สาขา</option>
                                    <option value="2" <?php echo ($defaultVersionPlan === 2) ? 'selected' : ''; ?>>จังหวัด</option>
                                    <option value="3" <?php echo ($defaultVersionPlan === 3) ? 'selected' : ''; ?>>เขต</option>
                                    <option value="4" <?php echo ($defaultVersionPlan === 4) ? 'selected' : ''; ?>>สำนักงานใหญ่</option>
                                </select>
                                <small id="version-plan-lock-message" class="text-muted d-block mt-1 <?php echo (!$isEditMode && $defaultVersionPlan) ? '' : 'd-none'; ?>">
                                    <i class="bi bi-lock-fill"></i> ล็อคตามระดับแผนกของคุณ
                                </small>
                                <div class="invalid-feedback">
                                    กรุณาเลือกเวอร์ชั่น (แผน)
                                </div>
                            </div>

                            <!-- ประเภทข้อมูล -->
                            <div class="col-xl-6">
                                <label class="form-label mt-2">ประเภทข้อมูล : <span class="text-danger">*</span></label>
                                <select class="form-control" name="asset-type" id="asset-type-select" required>
                                    <option value="">กรุณาเลือก</option>
                                </select>
                                <div class="invalid-feedback">
                                    กรุณาเลือกประเภทข้อมูล
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <label class="form-label mt-2">รหัสประเภทข้อมูล :</label>
                                <input type="text" class="form-control" id="asset-type-code" placeholder="ดึง รหัสประเภทข้อมูล มาแสดง" disabled>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร -->
                <div class="card custom-card" data-asset-type-id="1" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-1" class="form-label mt-2">แหล่งงบประมาณ <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            กรุณาเลือกแหล่งงบประมาณ
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-1" name="item-name" placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกรายการ
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-1" name="description" rows="3" placeholder="กรุณากรอก รายละเอียด" required></textarea>
                                        <div class="invalid-feedback">
                                            กรุณากรอกรายละเอียด
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวน (หน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity-1" name="quantity" placeholder="กรุณากรอกจำนวน" min="1" step="1" inputmode="numeric" data-integer-only="true" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกจำนวน (หน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">ราคา (ต่อหน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price-1" name="price" placeholder="กรุณากรอกราคา" min="0" step="0.01" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกราคา (ต่อหน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวนเงินรวม : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="total-1" name="total" placeholder="0.00" readonly style="background-color: #f8f9fa;">
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงินรวม</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-standards-select" class="form-label mt-2">มาตรฐานครุภัณฑ์ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-standards-select" name="equipment-standards-select" id="equipment-standards-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            กรุณาเลือกมาตรฐานครุภัณฑ์
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-setup-select" class="form-label mt-2">ลักษณะ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-setup-select" name="equipment-setup-select" id="equipment-setup-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            กรุณาเลือกลักษณะ
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-select" id="asset-category-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            กรุณาเลือกหมวดสินทรัพย์
                                        </div>
                                    </div>
                                    <div class="col-xl-12 replacement-asset-container d-none" data-equipment-setup="equipment-setup-select-1">
                                        <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน :</label>
                                        <div class="table-responsive">
                                            <table class="table text-nowrap table-bordered" id="machinery-serial-table">
                                                <thead class="text-start">
                                                    <tr>
                                                        <th style="width: 85%;">หมายเลขครุภัณฑ์</th>
                                                        <th style="width: 15%;">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-start" id="machinery-serial-tbody-1">
                                                    <!-- <tr class="equipment-row">
                                                        <td>
                                                            <select class="form-control equipment-select"
                                                                id="asset-number-select-1-0"
                                                                data-card="1"
                                                                data-row="0"
                                                                style="border: none; box-shadow: none; font-size: 0.875rem;">
                                                                <option value="">เลือกหมายเลขครุภัณฑ์</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                                                                <i class="bi bi-dash"></i> ลบรายการ
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light m-1" id="add-machinery-serial-1">
                                                    <i class="bi bi-plus"></i> เพิ่มรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-1" name="reason" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">
                                            กรุณากรอกเหตุผลความจำเป็น
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-1" name="project-program" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-1" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-1" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-1" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-1" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- เครื่องตกแต่งและอุปกรณ์สำนักงาน -->
                <div class="card custom-card" data-asset-type-id="2" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">เครื่องตกแต่งและอุปกรณ์สำนักงาน</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-2" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-2">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-2" name="item-name" placeholder="กรุณากรอก รายการ">
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-2" name="description" rows="3" row="" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวน (หน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity-2" name="quantity-2" placeholder="กรุณากรอกจำนวน" min="1" step="1" inputmode="numeric" data-integer-only="true" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกจำนวน (หน่วย)
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">กรุณากรอกจำนวน (หน่วย)</div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">ราคา (ต่อหน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price-2" name="price-2" placeholder="กรุณากรอกราคา" min="0" step="0.01" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกราคา (ต่อหน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวนเงินรวม : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="total-2" name="total-2" placeholder="0.00" readonly style="background-color: #f8f9fa;">
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงินรวม</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-standards-select" class="form-label mt-2">มาตรฐานครุภัณฑ์ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-standards-select" name="equipment-standards-select" id="equipment-standards-select-2">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกมาตรฐานครุภัณฑ์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-setup-select" class="form-label mt-2">ลักษณะ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-setup-select" name="equipment-setup-select" id="equipment-setup-select-2">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกลักษณะ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-select" id="asset-category-select-2">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-12 replacement-asset-container d-none" data-equipment-setup="equipment-setup-select-2">
                                        <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน : <span class="text-danger">*</span></label>
                                        <div class="table-responsive">
                                            <table class="table text-nowrap table-bordered" id="machinery-serial-table-2">
                                                <thead class="text-start">
                                                    <tr>
                                                        <th style="width: 85%;">หมายเลขครุภัณฑ์</th>
                                                        <th style="width: 15%;">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-start" id="machinery-serial-tbody-2">
                                                    <!-- <tr class="equipment-row">
                                                        <td>
                                                            <select class="form-control equipment-select"
                                                                id="asset-number-select-2-0"
                                                                data-card="2"
                                                                data-row="0"
                                                                style="border: none; box-shadow: none; font-size: 0.875rem;">
                                                                <option value="">เลือกหมายเลขครุภัณฑ์</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                                                                <i class="bi bi-dash"></i> ลบรายการ
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light m-1" id="add-machinery-serial-2">
                                                    <i class="bi bi-plus"></i> เพิ่มรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-2" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-2" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-2" name="project-program-2" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-2" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-2" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-2" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-2" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ที่ดิน -->
                <div class="card custom-card" data-asset-type-id="3" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">ที่ดิน</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-3" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-3" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-3" name="item-name" placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-3" name="description" rows="3" row="1" placeholder="กรุณากรอก รายละเอียด" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-3" name="reason" row="1" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">จำนวนเงิน : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="total-3" name="total-3" placeholder="กรุณากรอกจำนวนเงิน" min="0" step="0.01" inputmode="decimal" data-max-decimals="2" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงิน</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-3" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-3" name="project-program-3" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="installment-work-select-3" class="form-label mt-2">งานงวด : <span class="text-danger">*</span></label>
                                        <select class="form-control" name="installment-work-select-3" id="installment-work-select-3" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกงานงวด</div>
                                    </div>
                                    <div class="col-xl-4 installment-count-wrapper d-none" id="installment-count-wrapper-3" aria-hidden="true">
                                        <label for="installment-count-select-3" class="form-label mt-2">จำนวนงวด (1-24) : <span class="text-danger">*</span></label>
                                        <select class="form-control installment-count-select" name="installment-count-select-3" id="installment-count-select-3">
                                            <option value="">กรุณาเลือกจำนวนงวด</option>
                                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกจำนวนงวด</div>
                                    </div>
                                    <div class="col-xl-4 installment-count-wrapper d-none" id="installment-count-wrapper-3" aria-hidden="true">
                                        <label for="installment-count-select-3" class="form-label mt-2">จำนวนงวด (1-24) : <span class="text-danger">*</span></label>
                                        <select class="form-control installment-count-select" name="installment-count-select-3" id="installment-count-select-3">
                                            <option value="">กรุณาเลือกจำนวนงวด</option>
                                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกจำนวนงวด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-select" id="asset-category-select-3">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-3" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-3" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-3" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-3" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>



                <!-- ยานพาหนะ -->
                <div class="card custom-card" data-asset-type-id="4" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">ยานพาหนะ</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-4" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-4">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-4" name="item-name" placeholder="กรุณากรอก รายการ">
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-4" name="description" row="" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวน (หน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity-4" name="quantity-4" placeholder="กรุณากรอกจำนวน" min="1" step="1" inputmode="numeric" data-integer-only="true" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกจำนวน (หน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">ราคา (ต่อหน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price-4" name="price-4" placeholder="กรุณากรอกราคา" min="0" step="0.01" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกราคา (ต่อหน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวนเงินรวม : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="total-4" name="total-4" placeholder="0.00" readonly style="background-color: #f8f9fa;">
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงินรวม</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-standards-select" name="equipment-standards-select" id="equipment-standards-select-4">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกมาตรฐานครุภัณฑ์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-setup-select" class="form-label mt-2">ลักษณะ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-setup-select" name="equipment-setup-select" id="equipment-setup-select-4">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกลักษณะ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-4" id="asset-category-select-4">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-12 replacement-asset-container d-none" data-equipment-setup="equipment-setup-select-4">
                                        <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน : <span class="text-danger">*</span></label>
                                        <div class="table-responsive">
                                            <table class="table text-nowrap table-bordered" id="machinery-serial-table-4">
                                                <thead class="text-start">
                                                    <tr>
                                                        <th style="width: 85%;">หมายเลขครุภัณฑ์</th>
                                                        <th style="width: 15%;">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-start" id="machinery-serial-tbody-4">
                                                    <!-- <tr class="equipment-row">
                                                        <td>
                                                            <select class="form-control equipment-select"
                                                                    id="asset-number-select-4-0"
                                                                    data-card="4"
                                                                    data-row="0"
                                                                    style="border: none; box-shadow: none; font-size: 0.875rem;">
                                                                <option value="">เลือกหมายเลขครุภัณฑ์</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                                                                <i class="bi bi-dash"></i> ลบรายการ
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light m-1" id="add-machinery-serial-4">
                                                    <i class="bi bi-plus"></i> เพิ่มรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-4" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-4" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-4" name="project-program-4" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-4" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-4" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-4" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-4" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- สวนปาล์ม -->
                <div class="card custom-card" data-asset-type-id="5" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">สวนปาล์ม</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-5" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-5" name="item-name" placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด</label>
                                        <textarea class="form-control" id="description-5" name="description" row="" placeholder="กรุณากรอก รายละเอียด" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">จำนวนไร่ : <span class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control"
                                            pattern="[0-9]{10}"
                                            maxlength="10"
                                            inputmode="numeric"
                                            id="area-rai-5"
                                            name="area-rai-5"
                                            placeholder="กรุณากรอก รายการ"
                                            required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนไร่</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="asset-category-select-5" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-5" id="asset-category-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="project-start-period-select-5" class="form-label mt-2">ระยะเวลาเริ่มต้น โครงการฯ : <span class="text-danger">*</span></label>
                                        <select class="form-control project-period-select" name="project-start-period-5" id="project-start-period-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกระยะเวลาเริ่มต้น โครงการฯ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="project-end-period-select-5" class="form-label mt-2">ระยะเวลาสิ้นสุด โครงการฯ <span class="text-danger">*</span></label>
                                        <select class="form-control project-period-select" name="project-end-period-5" id="project-end-period-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกระยะเวลาสิ้นสุด โครงการฯ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="implementation-year-select-5" class="form-label mt-2">ปีที่ดำเนินการ : <span class="text-danger">*</span></label>
                                        <select class="form-control implementation-year-select" name="implementation-year-select-5" id="implementation-year-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกปีที่ดำเนินการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">จำนวนเงิน : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="total-5" name="total-6" placeholder="กรุณากรอกจำนวนเงิน" min="0" step="0.01" inputmode="decimal" data-max-decimals="2" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงิน</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-5" name="project-program-5" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-5" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-5" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason-5" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                    <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="strategy-select-5" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                    <select class="form-control strategy-select" name="strategy-select" id="strategy-select-5" required>
                                        <option value="">กรุณาเลือก</option>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <label for="pdf-attachments-5" class="form-label mt-2">เอกสารแนบ</label>
                                <input class="form-control" type="file" id="pdf-attachments-5" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                </small>
                                <div class="existing-attachments mt-2" data-attachment-list></div>
                                <div class="invalid-feedback">
                                    หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- สวนยาง -->
                <div class="card custom-card" data-asset-type-id="6" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">สวนยาง</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-6" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-6" name="item-name" placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-6" name="description" row="" placeholder="กรุณากรอก รายละเอียด" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">จำนวนไร่ : <span class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control"
                                            pattern="[0-9]{10}"
                                            maxlength="10"
                                            inputmode="numeric"
                                            id="area-rai-6"
                                            name="area-rai-5"
                                            placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนไร่</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="asset-category-select-6" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-6" id="asset-category-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="project-start-period-select-6" class="form-label mt-2">ระยะเวลาเริ่มต้น โครงการฯ : <span class="text-danger">*</span></label>
                                        <select class="form-control project-period-select" name="project-start-period-6" id="project-start-period-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกระยะเวลาเริ่มต้น โครงการฯ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="project-end-period-select-6" class="form-label mt-2">ระยะเวลาสิ้นสุด โครงการฯ : <span class="text-danger">*</span></label>
                                        <select class="form-control project-period-select" name="project-end-period-6" id="project-end-period-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกระยะเวลาสิ้นสุด โครงการฯ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="implementation-year-select-6" class="form-label mt-2">ปีที่ดำเนินการ : <span class="text-danger">*</span></label>
                                        <select class="form-control implementation-year-select" name="implementation-year-select-6" id="implementation-year-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกปีที่ดำเนินการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">จำนวนเงิน : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="total-6" name="total-6" placeholder="กรุณากรอกจำนวนเงิน" min="0" step="0.01" inputmode="decimal" data-max-decimals="2" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงิน</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-6" name="project-program-6" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-6" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-6" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason-6" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                    <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                    <select class="form-control strategy-select" name="strategy-select" id="strategy-select-6" required>
                                        <option value="">กรุณาเลือก</option>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <label for="pdf-attachments-6" class="form-label mt-2">เอกสารแนบ</label>
                                <input class="form-control" type="file" id="pdf-attachments-6" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                </small>
                                <div class="existing-attachments mt-2" data-attachment-list></div>
                                <div class="invalid-feedback" style="color:red; font-size:12px;">หากมีไฟล์ประกอบสามารถแนบ PDF ได้</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>


                <!-- สินทรัพย์ไม่มีตัวตน -->
                <div class="card custom-card" data-asset-type-id="7" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">สินทรัพย์ไม่มีตัวตน</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-7" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-7">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-7" name="item-name" placeholder="กรุณากรอก รายการ">
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-7" name="description" row="" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวน (หน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity-7" name="quantity-7" placeholder="กรุณากรอกจำนวน" min="1" step="1" inputmode="numeric" data-integer-only="true" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกจำนวน (หน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">ราคา (ต่อหน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price-7" name="price-7" placeholder="กรุณากรอกราคา" min="0" step="0.01" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกราคา (ต่อหน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวนเงินรวม : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="total-7" name="total-7" placeholder="0.00" readonly style="background-color: #f8f9fa;">
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงินรวม</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-standards-select" name="equipment-standards-select" id="equipment-standards-select-7">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกมาตรฐานครุภัณฑ์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-setup-select" class="form-label mt-2">ลักษณะ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-setup-select" name="equipment-setup-select" id="equipment-setup-select-7">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกลักษณะ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-7" id="asset-category-select-7">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-12 replacement-asset-container d-none" data-equipment-setup="equipment-setup-select-7">
                                        <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน : <span class="text-danger">*</span></label>
                                        <div class="table-responsive">
                                            <table class="table text-nowrap table-bordered" id="machinery-serial-table-7">
                                                <thead class="text-start">
                                                    <tr>
                                                        <th style="width: 85%;">หมายเลขครุภัณฑ์</th>
                                                        <th style="width: 15%;">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-start" id="machinery-serial-tbody-7">
                                                    <!-- <tr class="equipment-row">
                                                        <td>
                                                            <select class="form-control equipment-select"
                                                                id="asset-number-select-7-0"
                                                                data-card="7"
                                                                data-row="0"
                                                                style="border: none; box-shadow: none; font-size: 0.875rem;">
                                                                <option value="">เลือกหมายเลขครุภัณฑ์</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                                                                <i class="bi bi-dash"></i> ลบรายการ
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light m-1" id="add-machinery-serial-7">
                                                    <i class="bi bi-plus"></i> เพิ่มรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-7" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-7" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-7" name="project-program-7" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label for="installment-work-select-7" class="form-label mt-2">งานงวด : <span class="text-danger">*</span></label>
                                        <select class="form-control" name="installment-work-select-7" id="installment-work-select-7" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกงานงวด</div>
                                    </div>
                                    <div class="col-xl-4 installment-count-wrapper d-none" id="installment-count-wrapper-7" aria-hidden="true">
                                        <label for="installment-count-select-7" class="form-label mt-2">จำนวนงวด (1-24) : <span class="text-danger">*</span></label>
                                        <select class="form-control installment-count-select" name="installment-count-select-7" id="installment-count-select-7">
                                            <option value="">กรุณาเลือกจำนวนงวด</option>
                                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกจำนวนงวด</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-7" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-7" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-7" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-7" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>


                <!-- อาคารและสิ่งปลูกสร้าง -->
                <div class="card custom-card" data-asset-type-id="8" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">อาคารและสิ่งปลูกสร้าง</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-8">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-8" name="item-name" placeholder="กรุณากรอก รายการ">
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-8" name="description" row="1" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-8" name="reason" row="1" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">จำนวนเงิน : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="total-8" name="total-8" placeholder="กรุณากรอกจำนวนเงิน" min="0" step="0.01" inputmode="decimal" data-max-decimals="2" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงิน</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-8" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-8" name="project-program-8" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="installment-work-select-8" class="form-label mt-2">งานงวด : <span class="text-danger">*</span></label>
                                        <select class="form-control" name="installment-work-select-8" id="installment-work-select-8">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกงานงวด</div>
                                    </div>
                                    <div class="col-xl-4 installment-count-wrapper d-none" id="installment-count-wrapper-8" aria-hidden="true">
                                        <label for="installment-count-select-8" class="form-label mt-2">จำนวนงวด (1-24) : <span class="text-danger">*</span></label>
                                        <select class="form-control installment-count-select" name="installment-count-select-8" id="installment-count-select-8">
                                            <option value="">กรุณาเลือกจำนวนงวด</option>
                                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกจำนวนงวด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-8" id="asset-category-select-8">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-8" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-8" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-8" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>
                <!--End::row -->

                <!-- ส่วนปรับปรุงที่ดิน -->
                <div class="card custom-card" data-asset-type-id="9" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">ส่วนปรับปรุงที่ดิน</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-9" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-9" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-9" name="item-name" placeholder="กรุณากรอก รายการ" required>
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-9" name="description" rows="3" row="1" placeholder="กรุณากรอก รายละเอียด" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-9" name="reason" row="1" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">จำนวนเงิน : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="total-9" name="total-9" placeholder="กรุณากรอกจำนวนเงิน" min="0" step="0.01" inputmode="decimal" data-max-decimals="2" required>
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงิน</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-9" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-9" name="project-program-9" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="installment-work-select-9" class="form-label mt-2">งานงวด : <span class="text-danger">*</span></label>
                                        <select class="form-control" name="installment-work-select-9" id="installment-work-select-9" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกงานงวด</div>
                                    </div>
                                    <div class="col-xl-4 installment-count-wrapper d-none" id="installment-count-wrapper-9" aria-hidden="true">
                                        <label for="installment-count-select-9" class="form-label mt-2">จำนวนงวด (1-24) : <span class="text-danger">*</span></label>
                                        <select class="form-control installment-count-select" name="installment-count-select-9" id="installment-count-select-9">
                                            <option value="">กรุณาเลือกจำนวนงวด</option>
                                            <?php for ($i = 1; $i <= 24; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกจำนวนงวด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-select" id="asset-category-select-9">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-9" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-9" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-9" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-9" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- สินทรัพย์มูลค่าต่ำ -->
                <div class="card custom-card" data-asset-type-id="10" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">สินทรัพย์มูลค่าต่ำ</div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">

                            <!-- รายละเอียด -->
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <label for="budget-source-select-10" class="form-label mt-2">แหล่งงบประมาณ : <span class="text-danger">*</span></label>
                                        <select class="form-control budget-source-select" name="budget-source" id="budget-source-select-10">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแหล่งงบประมาณ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">รายการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="item-name-10" name="item-name" placeholder="กรุณากรอก รายการ">
                                        <div class="invalid-feedback">กรุณากรอกรายการ</div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">รายละเอียด : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description-10" name="description" row="" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                        <div class="invalid-feedback">กรุณากรอกรายละเอียด</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวน (หน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity-10" name="quantity-10" placeholder="กรุณากรอกจำนวน" min="1" step="1" inputmode="numeric" data-integer-only="true" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกจำนวน (หน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">ราคา (ต่อหน่วย) : <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price-10" name="price-10" placeholder="กรุณากรอกราคา" min="0" step="0.01" required>
                                        <div class="invalid-feedback">
                                            กรุณากรอกราคา (ต่อหน่วย)
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label class="form-label mt-2">จำนวนเงินรวม : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="total-10" name="total-10" placeholder="0.00" readonly style="background-color: #f8f9fa;">
                                        <div class="invalid-feedback">กรุณากรอกจำนวนเงินรวม</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-standards-select" name="equipment-standards-select" id="equipment-standards-select-10">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกมาตรฐานครุภัณฑ์</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="equipment-setup-select" class="form-label mt-2">ลักษณะ : <span class="text-danger">*</span></label>
                                        <select class="form-control equipment-setup-select" name="equipment-setup-select" id="equipment-setup-select-10">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกลักษณะ</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="asset-category-select" class="form-label mt-2">หมวดสินทรัพย์ : <span class="text-danger">*</span></label>
                                        <select class="form-control asset-category-select" name="asset-category-10" id="asset-category-select-10">
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกหมวดสินทรัพย์</div>
                                    </div>
                                    <div class="col-xl-12 replacement-asset-container d-none" data-equipment-setup="equipment-setup-select-10">
                                        <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน : <span class="text-danger">*</span></label>
                                        <div class="table-responsive">
                                            <table class="table text-nowrap table-bordered" id="machinery-serial-table-10">
                                                <thead class="text-start">
                                                    <tr>
                                                        <th style="width: 85%;">หมายเลขครุภัณฑ์</th>
                                                        <th style="width: 15%;">จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-start" id="machinery-serial-tbody-10">
                                                    <!-- <tr class="equipment-row">
                                                        <td>
                                                            <select class="form-control equipment-select"
                                                                id="asset-number-select-10-0"
                                                                data-card="10"
                                                                data-row="0"
                                                                style="border: none; box-shadow: none; font-size: 0.875rem;">
                                                                <option value="">เลือกหมายเลขครุภัณฑ์</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                                                                <i class="bi bi-dash"></i> ลบรายการ
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light m-1" id="add-machinery-serial-10">
                                                    <i class="bi bi-plus"></i> เพิ่มรายการ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <label class="form-label mt-2">เหตุผลความจำเป็น : <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason-10" name="reason" row="" placeholder="กรุณากรอก เหตุผลความจำเป็น" required></textarea>
                                        <div class="invalid-feedback">กรุณากรอกเหตุผลความจำเป็น</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="strategy-select" class="form-label mt-2">ยุทธศาสตร์ : <span class="text-danger">*</span></label>
                                        <select class="form-control strategy-select" name="strategy-select" id="strategy-select-10" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกยุทธศาสตร์</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label class="form-label mt-2">แผนงาน / โครงการ : <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="project-program-10" name="project-program-10" placeholder="กรุณากรอกแผนงาน / โครงการ" required>
                                        <div class="invalid-feedback">กรุณากรอกแผนงาน / โครงการ</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="expected-disbursement-date" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย : <span class="text-danger">*</span></label>
                                        <select class="form-control expected-disbursement-date" name="expected-disbursement-date" id="expected-disbursement-date-10" required>
                                            <option value="">กรุณาเลือก</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย</div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="disbursement-plan-select" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี : <span class="text-danger">*</span></label>
                                        <select class="form-control disbursement-plan-select" name="disbursement-plan-select" id="disbursement-plan-select-10" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="1">เป็นแผนเบิกจ่ายภายใน 1 ปี</option>
                                            <option value="2">เป็นแผนเบิกจ่ายมากกว่า 1 ปี</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี</div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="pdf-attachments-10" class="form-label mt-2">เอกสารแนบ</label>
                                    <input class="form-control" type="file" id="pdf-attachments-10" name="pdf_attachments" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" multiple data-attachment-input="true">
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> รองรับ: PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR (สูงสุด 10MB ต่อไฟล์)
                                    </small>
                                    <div class="existing-attachments mt-2" data-attachment-list></div>
                                    <div class="invalid-feedback">
                                        หากมีไฟล์ประกอบสามารถแนบ PDF ได้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Form Action Buttons -->
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <a href="investment-budget-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1">
                                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                            </a>
                            <button type="button" class="btn btn-purple btn-wave waves-effect waves-light m-1" id="save-draft-btn">
                                <i class="bi bi-file-earmark-text"></i> บันทึกร่าง
                            </button>
                            <button type="button" class="btn btn-success btn-wave waves-effect waves-light m-1" id="submit-approval-btn">
                                <i class="bi bi-send"></i> ส่งคำขออนุมัติ
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div> <!-- /#investment-budget-form-content -->
</div>


<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

<!-- PRISM JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/prismjs/prism.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/js/prism-custom.js"></script>

<!-- INTERNAL CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/choices.js"></script>

<!-- DATE & TIME PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

<!-- QUILL JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/quill/quill.min.js"></script>

<!-- FILEPOND JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

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

<!-- INTERNAL PRODUCT DETAILS JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

<!-- FLAT PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

<!-- CREATE PROJECT JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>

<!-- FILEPOND JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

<!-- DROPZONE JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone-min.js"></script>

<!-- FILEUPLOAD JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/fileupload.js"></script>

<!-- SWEETALERT2 JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>



<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->

<!-- Investment Budget Form JS -->
<!-- ส่ง BASEURL จาก PHP ไปยัง JavaScript -->
<script>
    window.BASEURL = '<?php echo $baseUrl; ?>';
    window.REQUEST_ID = <?php echo $isEditMode ? $requestId : 'null'; ?>;
    window.IS_EDIT_MODE = <?php echo $isEditMode ? 'true' : 'false'; ?>;
    window.DEFAULT_VERSION_PLAN = <?php echo $defaultVersionPlan !== null ? (int) $defaultVersionPlan : 'null'; ?>;
    window.SERVER_USER_HIERARCHY = <?php echo json_encode($userHierarchyDebug, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<!-- Common Utilities -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-common.js?v=<?php echo time(); ?>"></script>
<!-- Master Data Manager -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-master-data.js?v=<?php echo time(); ?>"></script>
<!-- Form Handler (includes all business logic, save functionality, equipment serial management, price calculation) -->
<script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-form.js?v=<?php echo time(); ?>"></script>
<?php if ($isEditMode): ?>
    <!-- <?php //include __DIR__ . '/partials/investment-budget-edit-script.php'; 
            ?> -->
    <script src="<?php echo $baseUrl; ?>/pages/js/investment-budget-edit.js"></script>
<?php endif; ?>
<!-- Bootstrap Validation -->
<script src="<?php echo $baseUrl; ?>/assets/js/validation.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
        requiredFields.forEach((field) => {
            let label = null;
            if (field.id) {
                label = document.querySelector(`label[for="${field.id}"]`);
            }
            if (!label) {
                const container = field.closest('.col-xl-12, .col-xl-6, .col-xl-4, .col-lg-6, .col-md-6, .col-12');
                if (container) {
                    label = container.querySelector('label');
                }
            }
            if (!label) return;
            label.querySelectorAll('.text-danger').forEach((span) => span.remove());
            label.classList.add('required-indicator');
        });

        // โหลดข้อมูล user hierarchy
        loadUserHierarchyInfo();
    });

    /**
     * โหลดข้อมูล user hierarchy จาก API เพื่อแสดงใน badges
     * และล็อค version_plan_id ตามระดับของ user
     */
    function loadUserHierarchyInfo() {
        const baseUrl = '<?php echo $baseUrl; ?>';
        const url = `${baseUrl}/controllers/investment_budget/ib_requests_controller.php?action=user_hierarchy`;

        console.log('🔍 Loading user hierarchy from:', url);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('📦 API Response:', data);

                if (data.status === 'success' && data.data) {
                    const debug = data.data;
                    console.log('✅ User hierarchy data:', debug);

                    // เก็บข้อมูล user hierarchy ไว้ใน window object สำหรับใช้ในที่อื่น
                    window.userHierarchyData = debug;

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
                            debug.depart_code && debug.depart_code !== 'N/A' ?
                            debug.depart_code :
                            null;
                        if (departCode) {
                            deptBadge.innerHTML = `<i class="bi bi-building"></i> รหัสหน่วยงาน: ${departCode}`;
                            deptBadge.className = 'badge bg-info-transparent ms-1';
                            deptBadge.style.display = '';
                        } else {
                            deptBadge.style.display = 'none';
                        }
                    }

                    // เลือก version_plan_id default ตามระดับของ user (สำหรับ create mode)
                    const allowedVersionPlan =
                        debug.allowed_version_plan_id || window.DEFAULT_VERSION_PLAN || null;

                    if (allowedVersionPlan) {
                        console.log('🎯 Setting default version plan ID:', allowedVersionPlan);
                        filterVersionPlanByUserLevel(allowedVersionPlan);
                    } else {
                        console.warn('⚠️ No version plan available for this user');
                    }
                } else {
                    // ถ้าไม่มีข้อมูล ซ่อน badges
                    const levelBadge = document.getElementById('user-approval-level');
                    const deptBadge = document.getElementById('user-department');
                    if (levelBadge) levelBadge.style.display = 'none';
                    if (deptBadge) deptBadge.style.display = 'none';
                    if (window.DEFAULT_VERSION_PLAN) {
                        filterVersionPlanByUserLevel(window.DEFAULT_VERSION_PLAN);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading user hierarchy info:', error);
                // ถ้าเกิด error ซ่อน badges
                const levelBadge = document.getElementById('user-approval-level');
                const deptBadge = document.getElementById('user-department');
                if (levelBadge) levelBadge.style.display = 'none';
                if (deptBadge) deptBadge.style.display = 'none';
                if (window.DEFAULT_VERSION_PLAN) {
                    filterVersionPlanByUserLevel(window.DEFAULT_VERSION_PLAN);
                }
            });
    }

    /**
     * เลือก version_plan default ตามระดับของ user
     * ใช้วิธีเดียวกับตอน edit mode โดยใช้ masterData.setValue()
     */
    function filterVersionPlanByUserLevel(allowedVersion) {
        const isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;
        const fallbackVersion = window.DEFAULT_VERSION_PLAN ?
            String(window.DEFAULT_VERSION_PLAN) :
            '';
        const allowedValue = allowedVersion ? String(allowedVersion) : fallbackVersion;

        if (!allowedValue && !isEditMode) {
            console.warn('⚠️ ไม่มีค่า version plan สำหรับล็อค');
        }

        const startTime = Date.now();
        const maxWaitMs = 6000;

        const attemptApply = () => {
            const selectEl = document.getElementById('version-plan-select');
            const managerReady = window.masterDataManager && window.masterDataManager.isInitialized;

            if (!selectEl || !managerReady) {
                if (Date.now() - startTime < maxWaitMs) {
                    requestAnimationFrame(attemptApply);
                } else {
                    console.warn('⚠️ ไม่สามารถตั้งค่า version plan ตามระดับหน่วยงานได้ภายในเวลาที่กำหนด');
                }
                return;
            }

            const valueToLock = isEditMode ?
                (selectEl.value || allowedValue) :
                allowedValue;

            if (!valueToLock) {
                console.warn('⚠️ ไม่พบค่าที่จะใช้ล็อค version plan');
                return;
            }

            if (!isEditMode) {
                window.masterDataManager.setValue('version-plan-select', valueToLock);
            }

            lockVersionPlanSelect(valueToLock);
        };

        attemptApply();
    }

    function lockVersionPlanSelect(targetValue) {
        const selectEl = document.getElementById('version-plan-select');
        if (!selectEl) {
            return;
        }

        const valueToLock = String(targetValue ?? '');
        if (!valueToLock) {
            return;
        }

        if (selectEl.value !== valueToLock) {
            selectEl.value = valueToLock;
            selectEl.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        }

        Array.from(selectEl.options).forEach((option) => {
            if (!option.value) {
                return;
            }
            const shouldDisable = option.value !== valueToLock;
            option.disabled = shouldDisable;
            option.hidden = shouldDisable;
            if (!shouldDisable) {
                option.selected = true;
            }
        });

        selectEl.dataset.lockedByHierarchy = 'true';
        selectEl.dataset.lockedValue = valueToLock;
        selectEl.classList.add('bg-light');
        selectEl.setAttribute('aria-readonly', 'true');

        if (selectEl.choicesInstance) {
            const instance = selectEl.choicesInstance;
            if (typeof instance.setChoiceByValue === 'function') {
                instance.setChoiceByValue(valueToLock);
            }

            const container = instance.containerOuter?.element;
            if (container) {
                container.classList.add('choices-locked');
                container.style.pointerEvents = 'none';
                container.setAttribute('title', 'กำหนดโดยสิทธิ์ของผู้ใช้');
                container.setAttribute('tabindex', '-1');
            }
        }

        if (!selectEl.dataset.lockedChangeHandler) {
            selectEl.addEventListener('change', (event) => {
                const lockedValue = event.target.dataset.lockedValue;
                if (lockedValue && event.target.value !== lockedValue) {
                    event.target.value = lockedValue;
                    if (event.target.choicesInstance && typeof event.target.choicesInstance.setChoiceByValue === 'function') {
                        event.target.choicesInstance.setChoiceByValue(lockedValue);
                    }
                }
            });
            selectEl.dataset.lockedChangeHandler = 'true';
        }

        const lockNotice = document.getElementById('version-plan-lock-message');
        if (lockNotice) {
            lockNotice.classList.remove('d-none');
        }
    }
</script>