<?php
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST']
    . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

$defaults = [
    'gjahr' => '2026',
    'rfundsctr' => '50100',
    'rcmmtitem' => '7300000000',
    'rfund' => 'R1492',
    'rfuncarea' => '01011103',
    'str_date' => '01-05-2026',
    'end_date' => '31-05-2026',
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบเช็คงบ — อ่านง่าย</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.css">
    <style>
        body { background: #f0f4f8; padding: 1.25rem; font-family: system-ui, sans-serif; }
        .hero-balance { font-size: 2rem; font-weight: 700; color: #0d6efd; }
        .result-card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .case-pass { background: #d1e7dd; border-left: 5px solid #198754; }
        .case-fail { background: #f8d7da; border-left: 5px solid #dc3545; }
        .case-warn { background: #fff3cd; border-left: 5px solid #fd7e14; }
        .case-item { padding: 1rem 1.25rem; margin-bottom: .75rem; border-radius: 8px; }
        .step-num { width: 28px; height: 28px; background: #0d6efd; color: #fff; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
    </style>
</head>
<body>
<div class="container" style="max-width: 900px;">

    <h1 class="h4 mb-1">ทดสอบเช็คงบ — จำลอง Modal ฟอร์มจริง</h1>
    <p class="text-muted mb-4">ใช้ SweetAlert2 ชุดเดียวกับหน้า「คำขอสร้างใบขอเสนอ」— กดแล้วจะเห็น Modal แบบเดียวกับตอนกดขออนุมัติ</p>

    <!-- ขั้นที่ 1 -->
    <div class="card result-card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="step-num">1</span>
                <strong>เงื่อนไขงบ (ค่าที่ส่งไป SAP แล้ว)</strong>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">ปีงบ</label>
                    <input type="text" class="form-control" id="f-gjahr" value="<?php echo htmlspecialchars($defaults['gjahr']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันเริ่ม (DD-MM-YYYY)</label>
                    <input type="text" class="form-control" id="f-str-date" value="<?php echo htmlspecialchars($defaults['str_date']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">วันสิ้นสุด (DD-MM-YYYY)</label>
                    <input type="text" class="form-control" id="f-end-date" value="<?php echo htmlspecialchars($defaults['end_date']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ศูนย์ต้นทุน (รหัส)</label>
                    <input type="text" class="form-control" id="f-fundsctr" value="<?php echo htmlspecialchars($defaults['rfundsctr']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ภาระผูกพัน (รหัส)</label>
                    <input type="text" class="form-control" id="f-cmmtitem" value="<?php echo htmlspecialchars($defaults['rcmmtitem']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">เงินทุน (รหัส)</label>
                    <input type="text" class="form-control" id="f-fund" value="<?php echo htmlspecialchars($defaults['rfund']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ขอบเขตตามหน้าที่ (รหัส)</label>
                    <input type="text" class="form-control" id="f-funcarea" value="<?php echo htmlspecialchars($defaults['rfuncarea']); ?>">
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-lg w-100 mt-3" id="btn-run-test">
                ดึงงบจาก SAP และทดสอบทุกกรณี
            </button>
        </div>
    </div>

    <!-- ขั้นที่ 2 ผลงบ -->
    <div id="block-balance" class="card result-card mb-3 d-none">
        <div class="card-body text-center py-4">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <span class="step-num">2</span>
                <strong>งบคงเหลือจาก SAP</strong>
            </div>
            <div class="hero-balance" id="display-balance">-</div>
            <p class="text-muted mb-0 small" id="display-sap-msg"></p>
            <div class="row mt-3 text-start small">
                <div class="col-4"><span class="text-muted">งบทั้งหมด</span><br><span id="display-budget">-</span></div>
                <div class="col-4"><span class="text-muted">ใช้ไปแล้ว</span><br><span id="display-actual">-</span></div>
                <div class="col-4"><span class="text-muted">เงินทุน</span><br><span id="display-fund-name">-</span></div>
            </div>
        </div>
    </div>

    <!-- ขั้นที่ 3 สรุป -->
    <div id="block-summary" class="alert alert-info d-none mb-3"></div>

    <!-- ขั้นที่ 4 แต่ละกรณี -->
    <div id="block-cases" class="d-none">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="step-num">3</span>
            <strong>ลองยอดขอต่างๆ — ระบบจะทำแบบนี้ในฟอร์มจริง</strong>
        </div>
        <div id="cases-list"></div>
    </div>

    <!-- ทดสอบยอดเอง -->
    <div id="block-custom" class="card result-card mb-3 d-none">
        <div class="card-body">
            <label class="form-label"><strong>จำลองกด「ขออนุมัติ」ด้วยยอดนี้ (บาท)</strong></label>
            <div class="input-group mb-2">
                <input type="number" class="form-control form-control-lg" id="custom-amount" placeholder="เช่น 50000">
                <button class="btn btn-success btn-lg" type="button" id="btn-simulate-submit">จำลองกดขออนุมัติ</button>
            </div>
            <p class="text-muted small mb-0">จะขึ้น Loading → Modal ตรวจงบ → (ถ้างบพอ) Modal ยืนยัน แบบฟอร์มจริง</p>
            <div id="custom-result" class="mt-3"></div>
        </div>
    </div>

    <!-- error -->
    <div id="block-error" class="alert alert-danger d-none"></div>

    <!-- โปรแกรมเมอร์ -->
    <details class="mt-4">
        <summary class="text-muted small">ข้อมูลเทคนิค (JSON) — กดถ้าต้องการ</summary>
        <pre id="raw-response" class="bg-white border rounded p-3 small mt-2"></pre>
    </details>
</div>

<script>
    window.BUDGET_CHECK_TESTER = {
        apiUrl: <?php echo json_encode($baseUrl . '/controllers/proposal/budget_check_tester_controller.php?action=run'); ?>,
        lastResult: null
    };
</script>
<script src="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>
<script src="<?php echo $baseUrl; ?>/pages/js/budget-check-tester.js"></script>
</body>
</html>
