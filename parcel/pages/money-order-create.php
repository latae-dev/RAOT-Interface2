<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

// สร้าง CSRF Token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>


<div class="container-fluid">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มใบสั่งจ่าย</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มใบสั่งจ่าย</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- รายละเอียดผู้ขอเบิก -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">รายละเอียดผู้ยื่นส่งใช้เงินยืมทดรองจ่าย</div>
                </div>
                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                            <input type="text" class="form-control bg-light" id="compensation_form_name" name="compensation_form_name" value="ใบสั่งจ่าย" readonly>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="publish-date" class="form-label">วันและเวลาเริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="date" name="start_date">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label">วันและเวลาสิ้นสุด :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="date" name="end_date">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                            <input readonly type="text" class="form-control bg-light" id="depart_code" name="depart_code">
                        </div>
                    </div>
                </div>
                <div class="card-body px-2 py-4 px-sm-4">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                            <input readonly type="text" class="form-control bg-light" id="full_name" name="full_name">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                            <input readonly type="text" class="form-control bg-light" id="position_name" name="position_name">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ระดับ :</label>
                            <input readonly type="text" class="form-control bg-light" id="level_name" name="level_name">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">สังกัด :</label>
                            <input readonly type="text" class="form-control bg-light" id="affiliation_name" name="affiliation_name">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row has_plan_id">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">รายละเอียดคำขอเบิกเงินทดรองจ่าย (รหัสคำขอเบิกเงินทดรองจ่าย : <span class="plan_number"></span>)</div>
                </div>
                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <div class="row">
                        <!-- <p class="mb-2 me-4"><span class="fs-15 fw-bold"> เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span></p> -->
                        <div class="col-xl-6">
                            <label class="form-check-label mb-1" for="other">เงินทุนที่ใช้</label>
                            <input type="text" id="fund_select_label" class="form-control bg-light" name="fund_select_label" readonly>
                            <span id="fund_html_label"></span>
                        </div>
                        <div class="col-xl-6">
                            <label class="form-check-label mb-1" for="other">โครงการ</label>
                            <input type="text" id="project_select_label" class="form-control bg-light" name="project_select_label" readonly>
                        </div>
                        <!-- <div class="col-xl-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="" id="other" disabled>
                                    <label class="form-check-label mb-1" for="other">อื่นๆ</label>
                                    <textarea class="form-control bg-light" id="fund_more" rows="2" disabled></textarea>
                                </div>
                            </div> -->
                        <div class="col-xl-12 mt-4 mb-4">
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col" style="width: 70px;">ลำดับ</th>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col">รายการ</th>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col">จำนวน</th>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col">หน่วย</th>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col">ราคาหน่วย</th>
                                            <th class="text-center bg-info text-fixed-dark bg-opacity-25" scope="col">ราคาทั้งหมด</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-center">ผลรวมทั้งหมด</td>
                                            <td class="total-price text-center" id="total">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input day_return" type="checkbox" value="" id="day_return" disabled>
                                <label class="form-check-label" for="day_return">นับจากวันกลับมาถึง</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input money_receipt" type="checkbox" value="" id="money_receipt" disabled>
                                <label class="form-check-label" for="money_receipt">นับแต่วันที่ได้รับเงิน</label>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="form-check mb-2">
                                <label class="form-check-label mb-1" for="note">เหตุผลของการขอยืมเงินทดรอง</label>
                                <textarea class="form-control note bg-light" id="note" name="note" rows="" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายละเอียดใบสั่งจ่าย-->
                <div class="card-header justify-content-between">
                    <div class="card-title">รายละเอียดใบสั่งจ่าย</div>
                </div>

                <!-- เบิกจ่ายจาก กองทุนฯ/เงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                        <div class="col-xl-6 mb-2">
                            <label class="form-check-label mb-1" for="other">เงินทุน</label>
                            <select class="form-control fund-select" data-trigge required id="fund_select" style="width: 100%"></select>
                        </div>
                        <div class="col-xl-6 mb-2">
                            <label class="form-check-label mb-1" for="other">โครงการ</label>
                            <select class="form-control project-select" data-trigge required id="project_select" style="width: 100%"></select>
                        </div>
                    </div>
                </div>

                <!-- จ่ายให้แก่ -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <p><span class="fs-15 fw-bold">รายละเอียดประกอบใบสั่งจ่าย : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-3 mb-2">
                                    <label class="form-label">ประเภทค่าตอบเเทนและค่าใช้จ่ายในการปฏิบัติงาน <small class="text-danger"> *จำเป็นต้องเลือก</small> :</label>
                                    <select class="form-control" data-trigge required id="compensation_expense_select" style="width: 100%"></select>
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label class="form-label">ประเภทงานที่ปฏิบัติ <small class="text-danger"> *จำเป็นต้องเลือก</small> :</label>
                                    <select class="form-control" data-trigge required id="compensation_expense_type_parent_select" style="width: 100%"></select>
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_number_withdraw" class="form-label">เลขที่ผู้เบิก :</label>
                                    <input type="text" class="form-control" id="req_number_withdraw" name="req_number_withdraw" placeholder="กรุณากรอก เลขที่ผู้เบิก" autocomplete="off">
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_number_pay" class="form-label">เลขที่จ่าย :</label>
                                    <input type="text" class="form-control" id="req_number_pay" name="req_number_pay" placeholder="กรุณากรอก เลขที่จ่าย" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-3 mb-2">
                                    <label for="compensation_date" class="form-label">ลงวันที่ :<small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                            <input type="text" class="form-control flatpickr-input active" id="date_notime" name="compensation_date" placeholder="กรุณาระบุ วันที่" fdprocessedid="jp9fs9">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_payee_name" class="form-label">จ่ายให้ :<small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                    <input type="text" class="form-control" id="req_payee_name" name="req_payee_name" placeholder="กรุณากรอก บุคคลที่ต้องการจ่ายให้" autocomplete="off">
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_position_name" class="form-label">ตำแหน่ง :</label>
                                    <input type="text" class="form-control" id="req_position_name" name="req_position_name" placeholder="กรุณากรอก ตำแหน่ง" autocomplete="off">
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_affiliation_name" class="form-label">ส่วนงาน/หน่วยงาน :</label>
                                    <input type="text" class="form-control" id="req_affiliation_name" name="req_affiliation_name" placeholder="กรุณากรอก ส่วนงาน/หน่วยงาน" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label for="req_address" class="form-label">ที่อยู่ :<small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                    <textarea class="form-control" id="req_address" name="req_address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รายการ -->
                <div class="card-body border-bottom" id="evidence-table">
                    <div class="row">
                        <p><span class="fs-15 fw-bold">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ : </span><small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></p>
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered border-success">
                                <thead class="text-center">
                                    <tr>
                                        <td scope="col" rowspan="2" width="3%" class="bg-info text-fixed-dark bg-opacity-25"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></td>
                                        <td scope="col" rowspan="2" width="4%" class="bg-info text-fixed-dark bg-opacity-25">ลำดับที่</td>
                                        <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-25">รายละเอียดใบสำคัญจ่าย</td>
                                        <td scope="col" colspan="2" width="25%" class="bg-info text-fixed-dark bg-opacity-25">จำนวนเงิน</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" class="bg-info text-fixed-dark bg-opacity-10">บาท</td>
                                        <td colspan="1" class="bg-info text-fixed-dark bg-opacity-10">สต.</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="product-list">
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                        <td><input class="form-control" id="total_bath" name="total_bath" type="text" placeholder="00" disabled></td>
                                        <td><input class="form-control" id="total_stang" name="total_stang" type="text" placeholder="00" disabled></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                        <td colspan="2"><input class="form-control" id="total_bath_text" name="total_bath_text" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 addTable" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 delTable" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                        <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                        <div class="col-xl-12 mb-2 has_plan_id">
                            <input type="hidden" name="plan_id" id="plan_id">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label for="plan_number" class="form-label">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                    <input type="text" class="form-control" id="plan_number" name="plan_number" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง" disabled>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label for="plan_date" class="form-label">ลงวันที่ :</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                            <input type="text" class="form-control flatpickr-input active" id="plan_date" name="plan_date" placeholder="กรุณาระบุ วันที่" readonly="readonly" fdprocessedid="jp9fs9" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label for="plan_total" class="form-label">จำนวนเงินทดรอง (บาท) :</label>
                                    <input type="text" class="form-control" id="plan_total" name="plan_total" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2 has_plan_id">
                            <div class="row">
                                <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="compensation_return" id="compensation_return" value="return">
                                        <label class="form-check-label" for="compensation_return">ส่งคืน</label>
                                    </div>
                                </div>
                                <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="compensation_withdraw" id="compensation_withdraw" value="withdraw">
                                        <label class="form-check-label" for="compensation_withdraw">เบิกเพิ่ม</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2 has_plan_id">
                            <div class="row">
                                <div class="col-xl-12 mb-2">
                                    <label for="compensation_amount" class="form-label">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                    <input type="text" class="form-control" id="compensation_amount" name="compensation_amount" placeholder="0.00" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                <div class="card-body border-bottom">
                    <div class="col-xl-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_confirm" name="is_confirm">
                            <label class="form-check-label" for="is_confirm">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง <span class="text-danger"> *</span> </label>
                        </div>
                    </div>
                </div>

                <!-- ส่วนท้าย การขออนุมัติ -->
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end" id="button-container">
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/money-order-plan.js"></script>
<script>
    function handleCompensationActionCheckbox(e) {
        const returnBox = document.getElementById('compensation_return');
        const withdrawBox = document.getElementById('compensation_withdraw');
        if (e.target.id === 'compensation_return' && returnBox.checked) {
            withdrawBox.checked = false;
        } else if (e.target.id === 'compensation_withdraw' && withdrawBox.checked) {
            returnBox.checked = false;
        }
    }
    document.getElementById('compensation_return')?.addEventListener('change', handleCompensationActionCheckbox);
    document.getElementById('compensation_withdraw')?.addEventListener('change', handleCompensationActionCheckbox);

    function calcCompensationAmount() {
        const total_bath = document.getElementById('total_bath');
        const total_stang = document.getElementById('total_stang');
        const planTotalInput = document.getElementById('plan_total');
        const compensationAmountInput = document.getElementById('compensation_amount');
        const withdrawBox = document.getElementById('compensation_withdraw');
        const returnBox = document.getElementById('compensation_return');

        const bath = parseFloat(total_bath?.value.replace(/,/g, '') || 0);
        const stang = parseFloat(total_stang?.value.replace(/,/g, '') || 0);
        const planTotal = parseFloat(planTotalInput?.value.replace(/,/g, '') || 0);
        const expensesTotal = bath + (stang / 100);
        const compensationAmount = expensesTotal - planTotal;

        if (compensationAmountInput) {
            // แสดงจำนวนเต็มบวกเสมอ (abs)
            const total = !isNaN(compensationAmount) ? Math.abs(compensationAmount) : '';
            compensationAmountInput.value = total ? total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }

        // เช็คหรือไม่เช็ค checkbox ตามเงื่อนไข
        if (withdrawBox && returnBox) {
            if (expensesTotal > planTotal) {
                withdrawBox.checked = true;
                returnBox.checked = false;
            } else if (expensesTotal < planTotal) {
                withdrawBox.checked = false;
                returnBox.checked = true;
            } else {
                withdrawBox.checked = false;
                returnBox.checked = false;
            }
        }
    }
</script>