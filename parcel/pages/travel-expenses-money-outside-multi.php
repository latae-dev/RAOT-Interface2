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
<style>
    /* .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    } */

    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    #formTabs .nav-link {
        cursor: pointer;
    }
</style>
<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->
<!-- This code is useful for content -->
<?php ob_start(); ?>
<div class="container-fluid">
    <!-- เพิ่ม CSRF Token -->
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li aria-current="page" class="breadcrumb-item fw-semibold active">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->
    <form id="advanceFrm" novalidate="">
        <!-- Start::row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">รายละเอียดผู้ยื่นส่งใช้เงินยืมทดรองจ่าย</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label">ชื่อแบบฟอร์ม :</label>
                                <input class="form-control bg-light" id="expenses_form_name" name="expenses_form_name" readonly="" type="text" value="เบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)" />
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="publish-date">วันและเวลาเริ่มต้น :</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input class="form-control" id="expenses_start" name="expenses_start" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label">วันและเวลาสิ้นสุด :</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input class="form-control" id="expenses_end" name="expenses_end" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label11">รหัสหน่วยงาน :</label>
                                <input class="form-control bg-light" id="depart_code" name="depart_code" readonly="" type="text" />
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label1">ชื่อ-นามสกุล :</label>
                                <input class="form-control bg-light" id="full_name" name="full_name" readonly="" type="text" />
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label1">ตำแหน่ง :</label>
                                <input class="form-control bg-light" id="position_name" name="position_name" readonly="" type="text" />
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label1">ระดับ :</label>
                                <input class="form-control bg-light" id="level_name" name="level_name" readonly="" type="text" />
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label class="form-label" for="input-label1">สังกัด :</label>
                                <input class="form-control bg-light" id="affiliation_name" name="affiliation_name" readonly="" type="text" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row have_plan_id">
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
                                <input class="form-control bg-light" id="fund_select_label" name="fund_select_label" readonly="" type="text" />
                                <span id="fund_html_label"></span>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <input class="form-control bg-light" id="project_select_label" name="project_select_label" readonly="" type="text" />
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
                                    <table class="table text-nowrap table-bordered" id="planItemsTable">
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
                                                <td class="text-center" colspan="5">ผลรวมทั้งหมด</td>
                                                <td class="total-price text-center" id="total">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input day_return" disabled="" id="day_return" type="checkbox" value="" />
                                    <label class="form-check-label" for="day_return">นับจากวันกลับมาถึง</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input money_receipt" disabled="" id="money_receipt" type="checkbox" value="" />
                                    <label class="form-check-label" for="money_receipt">นับแต่วันที่ได้รับเงิน</label>
                                </div>
                            </div>
                            <div class="col-xl-9">
                                <div class="form-check mb-2">
                                    <label class="form-check-label mb-1" for="note">เหตุผลของการขอยืมเงินทดรอง</label>
                                    <textarea class="form-control note bg-light" id="note" name="note" readonly="" rows=""></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card mt-3" id="forms-summary-card">
                    <div class="card-header">
                        <div class="card-title">สรุปจำนวนเงินในการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน ทั้งหมด</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="forms-summary-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 90px;">ฟอร์มที่</th>
                                        <th style="width: 200px;">ประเภทการเดินทาง</th>
                                        <th class="text-start">ชื่อ-นามสกุล</th>
                                        <th class="text-end" style="width: 160px;">รวมทั้งสิ้น (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <!-- <tr>
                                        <th colspan="3" class="text-end">จำนวนเงินคำขอทดรอง (บาท)</th>
                                        <td class="text-end" id="summary-plan-total">0.00</td>
                                    </tr> -->
                                    <tr>
                                        <th colspan="3" class="text-end">รวมทั้งสิ้นทุก (บาท)</th>
                                        <td class="text-end" id="summary-forms-total">0.00</td>
                                    </tr>
                                    <!-- <tr>
                                        <th colspan="3" class="text-end">ส่วนต่าง (บาท)</th>
                                        <td class="text-end" id="summary-difference">0.00</td>
                                    </tr> -->
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-xl-12 mb-2 mt-2 have_plan_id">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label class="form-label" for="plan_number_display">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                    <input class="form-control" disabled="" id="plan_number_display" name="plan_number_display" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง" type="text" />
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label class="form-label" for="plan_date_display">ลงวันที่ :</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                            <input class="form-control flatpickr-input active" disabled="" fdprocessedid="jp9fs9" id="plan_date_display" name="plan_date_display" placeholder="กรุณาระบุ วันที่" readonly="readonly" type="text" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                    <label class="form-label" for="plan_total_display">จำนวนเงินทดรอง (บาท) :</label>
                                    <input class="form-control" disabled="" id="plan_total_display" name="plan_total_display" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" type="text" />
                                </div>
                            </div>
                        </div>
                        <div class="row have_plan_id mb-2">
                            <!-- กล่องซ้าย -->
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_return_display" name="expenses_action_display" type="checkbox" value="return" />
                                            <label class="form-check-label" for="expenses_return_display">ส่งคืน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_withdraw_display" name="expenses_action_display" type="checkbox" value="withdraw" />
                                            <label class="form-check-label" for="expenses_withdraw_display">เบิกเพิ่ม</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- กล่องขวา -->
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <label class="form-label" for="expenses_amount_display">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                <input class="form-control" id="expenses_amount_display" name="expenses_amount_display" placeholder="0.00" type="text" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <!-- ส่วนหัว รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน-->
                    <div class="card-header flex-column flex-md-row justify-content-between align-items-md-center">
                        <div class="card-title mb-2 mb-md-0" id="formDetailTitle">รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)</div>
                        <ul class="nav nav-tabs card-header-tabs ms-md-auto" id="formTabs"></ul>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom fund_and_project">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">เงินทุน</label>
                                <input class="form-control bg-light" id="fund_select_label_noplan" name="fund_select_label_noplan" readonly="" type="text" />
                                <span id="fund_html_label"></span>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <input class="form-control bg-light" id="project_select_label_noplan" name="project_select_label_noplan" readonly="" type="text" />
                            </div>
                        </div>
                    </div>
                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label" for="expenses_learn">เรียน :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input autocomplete="off" class="form-control" id="expenses_learn" name="expenses_learn" placeholder="กรุณากรอก เรียน" type="text" />
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label" for="expenses_code">คำสั่ง/บันทึก :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input autocomplete="off" class="form-control" id="expenses_code" name="expenses_code" placeholder="กรุณากรอก คำสั่ง/บันทึก" type="text" />
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label" for="input-label1">ลงวันที :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input class="form-control flatpickr-input active" fdprocessedid="jp9fs9" id="date_notime" name="expenses_date" placeholder="ระบุวัน เดือน ปี" readonly="readonly" type="text" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <div class="row">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 mb-3 one-person">
                                        <label for="text_approve" class="form-label">ได้อนุมัติให้ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control input-approve-name" id="text_approve" name="text_approve" placeholder="กรุณากรอก ได้อนุมัติให้" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-4 mb-2">
                                <div class="form-group row">
                                    <label for="is_foreign" class="col-auto col-form-label form-label mb-2">ลักษณะการเดินทาง :</label>
                                    <div class="col-8">
                                        <select class="form-select is_foreign" id="is_foreign" name="is_foreign" required style="width: 100%">
                                            <option value="0" selected>- เดินทางในประเทศไทย</option>
                                            <option value="1">- เดินทางต่างประเทศ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault1_start" name="expenses_start_location" type="radio" value="address" />
                                            <label class="form-check-label" for="flexRadioDefault1_start">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault2_start" name="expenses_start_location" type="radio" value="office" />
                                            <label class="form-check-label" for="flexRadioDefault2_start">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault3_start" name="expenses_start_location" type="radio" value="thailand" />
                                            <label class="form-check-label" for="flexRadioDefault3_start">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input class="form-control" id="expenses_start_date" name="expenses_start_date" placeholder="กรุณากรอก วันที่เดินทาง" type="text" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_start_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault1_end" name="expenses_end_location" type="radio" value="address" />
                                            <label class="form-check-label" for="flexRadioDefault1_end">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault2_end" name="expenses_end_location" type="radio" value="office" />
                                            <label class="form-check-label" for="flexRadioDefault2_end">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="flexRadioDefault3_end" name="expenses_end_location" type="radio" value="thailand" />
                                            <label class="form-check-label" for="flexRadioDefault3_end">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input class="form-control" id="expenses_end_date" name="expenses_end_date" placeholder="กรุณากรอก วันที่เดินทาง" type="text" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_end_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- รายละเอียด ค่าเบี้ยเลี้ยง ค่าเช่าที่พัก ค่าพาหนะ ค่าใช้จ่ายอื่นๆ หักค่าอาหารฝึกอบรม-->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="position_withdraw" class="form-label">ฐานะ การเบิกของผู้เบิก :</label>
                                        <input type="text" class="form-control" id="position_withdraw" name="position_withdraw" placeholder="กรุณากรอก ฐานะ การเบิกของผู้เบิก" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_allowance">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input class="form-control format-number-2point" id="expenses_allowance" name="expenses_allowance" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_allowance_days">จำนวน (วัน) :</label>
                                        <input class="form-control format-number-1point" id="expenses_allowance_days" name="expenses_allowance_days" placeholder="กรุณากรอก จำนวน (วัน)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_allowance_total">รวม (บาท) :</label>
                                        <input class="form-control" disabled="" id="expenses_allowance_total" name="expenses_allowance_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_accommodation">ค่าเช่าที่พักประเภท (บาท/วัน) :</label>
                                        <input class="form-control format-number-2point" id="expenses_accommodation" name="expenses_accommodation" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_accommodation_days">จำนวน (วัน) :</label>
                                        <input class="form-control format-number-1point" id="expenses_accommodation_days" name="expenses_accommodation_days" placeholder="กรุณากรอก จำนวน (วัน)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_accommodation_total">รวม (บาท) :</label>
                                        <input class="form-control" disabled="" id="expenses_accommodation_total" name="expenses_accommodation_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_transportation">ค่าพาหนะ (บาท) :</label>
                                        <input class="form-control format-number-2point" id="expenses_transportation" name="expenses_transportation" placeholder="กรุณากรอก ค่าพาหนะ (บาท)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_transportation">รายละเอียดค่าพาหนะ :</label>
                                        <input class="form-control" id="expenses_transportation_remark" name="expenses_transportation_remark" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ" type="text" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_transportation_total">รวม (บาท) :</label>
                                        <input class="form-control" disabled="" id="expenses_transportation_total" name="expenses_transportation_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_moving" class="form-label">ค่าขนย้ายสิ่งของส่วนตัว (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving" name="expenses_moving" placeholder="กรุณากรอก ค่าขนย้ายสิ่งของส่วนตัว (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_moving_km" class="form-label">ระยะทาง (กิโลเมตร) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving_km" name="expenses_moving_km" placeholder="กรุณากรอก ระยะทาง (กิโลเมตร)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_moving_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_moving_total" name="expenses_moving_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_other">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input class="form-control format-number-2point" id="expenses_other" name="expenses_other" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_other_remark" class="form-label">รายละเอียดค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="text" class="form-control" id="expenses_other_remark" name="expenses_other_remark" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" autocomplete="off">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_other_total">รวม (บาท) :</label>
                                        <input class="form-control" disabled="" id="expenses_other_total" name="expenses_other_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_food_per_meal">ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ) :</label>
                                        <input class="form-control format-number-2point" id="expenses_food_per_meal" name="expenses_food_per_meal" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_food_meals">จำนวน (มื้อ) :</label>
                                        <input class="form-control" id="expenses_food_meals" name="expenses_food_meals" placeholder="กรุณากรอก จำนวน (มื้อ)" type="number" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_food_total">รวม (บาท) *หักลบ :</label>
                                        <input class="form-control" disabled="" id="expenses_food_total" name="expenses_food_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">กรณีใช้ยานพาหนะส่วนตัว กรุณาระบุข้อมูล :</label>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_vehicle" name="expenses_vehicle" type="checkbox" value="car" />
                                            <label class="form-check-label" for="expenses_vehicle">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_motorcycle" name="expenses_vehicle" type="checkbox" value="motorcycle" />
                                            <label class="form-check-label" for="expenses_motorcycle">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label class="form-label" for="expenses_vehicle_number">หมายเลขทะเบียน :</label>
                                        <input autocomplete="off" class="form-control" id="expenses_vehicle_number" name="expenses_vehicle_number" placeholder="กรุณากรอก หมายเลขทะเบียน" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 has_vehicle" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label" for="input-label1">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success" id="vehicleDetailsTable">
                                            <thead class="text-center">
                                                <tr>
                                                    <!-- <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                                    <td class="bg-info text-fixed-dark bg-opacity-25" rowspan="3" scope="col">วัน เดือน ปี</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-25" colspan="2" scope="col">ออกจาก</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-25" colspan="2" scope="col">กลับถึง</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-25" colspan="3" scope="col">ค่าพาหนะส่วนตัว</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-25" rowspan="3" scope="col">หมายเหตุ</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ระยะทาง</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย/กม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(กม.)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                </tr>
                                            </thead>
                                            <tbody id="list-details">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="text-end" colspan="7" scope="col">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                    <td colspan="3" scope="col"><input class="form-control" disabled="" id="total_amount_details" placeholder="0.00" type="text" /></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end" colspan="7" scope="col">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                    <td colspan="3" scope="col"><input class="form-control" disabled="" id="total_text_details" placeholder="ศูนย์บาทศูนย์สตางค์" type="text" /></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <!-- <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1 addTable-list-details" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1 delTable-list-details" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                            <div class="col-xl-12 mb-2" id="ifYes" style="display: none;">
                                <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered border-success" id="groupPeopleTable">
                                        <thead class="text-center">
                                            <tr>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ลำดับที่</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ชื่อ-นามสกุล</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ตำแหน่ง/ระดับ</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">เลขบัตรประชาชน</td>
                                                <td scope="col" colspan="11" class="bg-info text-fixed-dark bg-opacity-25">ค่าใช้จ่าย (บาท)</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">รวม (บาท)</td>
                                            </tr>
                                            <tr>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยง</td>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พัก</td>
                                                <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าพาหนะ</td>
                                                <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าใช้จ่ายอื่นๆ</td>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">หักค่าอาหารฝึกอบรม</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยงต่อวัน (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (วัน)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พักต่อวัน (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (วัน)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">มื้อละ (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (มื้อ)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="group-people-listgroup-people-list">
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-end" colspan="13" scope="col">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                <td colspan="3"><input class="form-control" disabled="" id="expenses_group_total" placeholder="0.00" type="text" /></td>
                                            </tr>
                                            <tr>
                                                <td class="text-end" colspan="13" scope="col">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                <td colspan="3">
                                                    <input class="form-control" disabled="" id="expenses_group_total_text" placeholder="ศูนย์บาทถ้วน" type="text" />
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label mb-2">หมายเหตุ :</label>
                                        <textarea class="form-control" id="expenses_note" name="expenses_note" rows="2"></textarea>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_total">รวมทั้งสิ้น (บาท) :</label>
                                        <input class="form-control" disabled="" id="expenses_total" name="expenses_total" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="expenses_total_text">รวมทั้งสิ้น (ตัวอักษร) :</label>
                                        <input class="form-control" disabled="" id="expenses_total_text" name="expenses_total_text" placeholder="ศูนย์บาทศูนย์สตางค์" type="text" />
                                    </div>
                                </div>
                            </div>
                            <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                            <div class="col-xl-12 mb-2" style="display: none;">
                                <input id="plan_id" name="plan_id" type="hidden" />
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="plan_number">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                        <input class="form-control" disabled="" id="plan_number" name="plan_number" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง" type="text" />
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="plan_date">ลงวันที่ :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input class="form-control flatpickr-input active" disabled="" fdprocessedid="jp9fs9" id="plan_date" name="plan_date" placeholder="กรุณาระบุ วันที่" readonly="readonly" type="text" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label" for="plan_total">จำนวนเงินทดรอง (บาท) :</label>
                                        <input class="form-control" disabled="" id="plan_total" name="plan_total" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2" style="display: none;">
                                <div class="row">
                                    <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_return" name="expenses_action" type="checkbox" value="return" />
                                            <label class="form-check-label" for="expenses_return">ส่งคืน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" id="expenses_withdraw" name="expenses_action" type="checkbox" value="withdraw" />
                                            <label class="form-check-label" for="expenses_withdraw">เบิกเพิ่ม</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label" for="expenses_amount">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                        <input class="form-control" id="expenses_amount" name="expenses_amount" placeholder="0.00" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-check">
                                    <input class="form-check-input" id="expenses_confirm" name="expenses_confirm" type="checkbox" />
                                    <label class="form-check-label" for="expenses_confirm">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง <span class="text-danger"> *</span> </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <h6 class="card-title mb-3">ผลการพิจารณาอนุมัติ</h6>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered" id="approval-table">
                                        <thead>
                                            <tr>
                                                <th class="text-start" scope="col" style="width: 30px;">ลำดับที่</th>
                                                <th class="text-center" scope="col" style="width: 200px;">วันที่</th>
                                                <th class="text-center" scope="col" style="width: 200px;">ผลการพิจารณา</th>
                                                <th class="text-center" scope="col" style="width: 400px;">ผู้อนุมัติ</th>
                                                <th class="text-center" scope="col" style="width: 250px;">จำแนกประเภทค่าใช้จ่าย</th>
                                                <th class="text-center" scope="col">หมายเหตุ</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ที่ท้ายฟอร์ม -->
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end" id="button-container"></div>
                </div>
            </div>
        </div>
        <div class="row" id="approve-section">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">การพิจารณาอนุมัติ</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row finance" style="display:none;">
                            <div class="col-xl-4 mb-2">
                                <div class="form-group row">
                                    <label for="expense_cat" class="col-auto col-form-label form-label mb-2">จำแนกค่าใช้จ่าย :</label>
                                    <div class="col-8">
                                        <select class="form-select" id="expense_cat" name="expense_cat" required style="width: 100%">
                                            <option value="">เลือกค่าใช้จ่าย</option>
                                            <option value="5">ค่าใช้จ่ายในการผลิต</option>
                                            <option value="6">ค่าใช้จ่ายในการบริหาร</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mb-2 me-4"><span class="fs-15 fw-bold"> ผลการอนุมัติ <small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></span></p>
                        <div class="row">
                            <div class="col-xl-3">
                                <div class="form-check mb-2 div_permissionAPHO">
                                    <input class="form-check-input approve" type="checkbox" value="" id="approve">
                                    <label class="form-check-label" for="approve">อนุมัติ</label>
                                </div>
                                <div class="form-check mb-2 div_permissionAPHO">
                                    <input class="form-check-input reject" type="checkbox" value="" id="reject">
                                    <label class="form-check-label" for="reject">ไม่อนุมัติ</label>
                                </div>
                                <div class="form-check mb-2 div_permissionCHO" style="display:none;">
                                    <input class="form-check-input check" type="checkbox" value="" id="check">
                                    <label class="form-check-label" for="check">ผ่านการตรวจสอบ</label>
                                </div>
                                <div class="form-check mb-2 div_permissionCHO" style="display:none;">
                                    <input class="form-check-input check" type="checkbox" value="" id="reject_check">
                                    <label class="form-check-label" for="reject_check">ไม่ผ่านการตรวจสอบ</label>
                                </div>
                                <div class="form-check mb-2 div_permissionAPAHO" style="display:none;">
                                    <input class="form-check-input check_and_approve" type="checkbox" value="" id="check_and_approve">
                                    <label class="form-check-label" for="check_and_approve">ผ่านการตรวจสอบและอนุมัติ</label>
                                </div>
                            </div>
                            <div class="col-xl-9">
                                <div class="row">
                                    <div class="form-check mb-2 col-6">
                                        <label class="form-check-label mb-1" for="st_hf_full_name">อนุมัติโดย</label>
                                        <input class="form-control bg-light" id="st_hf_full_name" name="st_hf_full_name" readonly="" type="text" />
                                    </div>
                                    <div class="form-check mb-2 col-6">
                                        <label class="form-check-label mb-1" for="st_hf_date">วันที่อนุมัติ</label>
                                        <input class="form-control bg-light" id="st_hf_date" name="st_hf_date" readonly="" type="text" />
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label mb-1" for="remark">หมายเหตุ <small class="text-danger ml-2" id="hide_text_approve"> *จำเป็นต้องกรอก </small></label>
                                        <textarea class="form-control remark" id="remark" name="remark" rows=""></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ที่ท้ายฟอร์ม -->
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end" id="button-container-approve"></div>
                </div>
            </div>
        </div>
        <!--End::row -->
    </form>
</div>
<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->
<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
<script>
    // เพิ่มฟังก์ชันสำหรับแสดง/ซ่อน loading
    window.showLoading = function() {
        Swal.fire({
            title: 'กำลังประมวลผล...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    window.hideLoading = function() {
        Swal.close();
    };
</script>
<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->
<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->
<script type="module" src="<?php echo $baseUrl; ?>/pages/js/travel-expenses-money-outside-multi.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // disable input, textarea, select ยกเว้นที่อยู่ใน #approve-section
        document.querySelectorAll("input, textarea, select").forEach(el => {
            if (!el.closest("#approve-section")) {
                el.setAttribute("disabled", "disabled");
            }
        });

        // disable ปุ่มทั้งหมด (ยกเว้นปุ่ม back หรือปุ่มใน #approve-section)
        document.querySelectorAll("button").forEach(btn => {
            if (!btn.closest("#approve-section")) {
                btn.setAttribute("disabled", "disabled");
            }
        });

        // checkbox / radio (ยกเว้นใน #approve-section)
        document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(el => {
            if (!el.closest("#approve-section")) {
                el.onclick = function() {
                    return false;
                };
            }
        });
    });

    // ✅ Loading
    window.showLoading = function() {
        Swal.fire({
            title: 'กำลังประมวลผล...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };
    window.hideLoading = function() {
        Swal.close();
    };

    // ✅ แสดง/ซ่อนคณะเดินทาง
    function yesnoCheck() {
        const groupPeopleCountInput = document.getElementById('group_people_count');
        const yesCheck = document.getElementById('yesCheck');
        const noCheck = document.getElementById('noCheck');
        const tableBody = document.querySelector('#ifYes table tbody');
        const ifYesDiv = document.getElementById('ifYes');

        if (!groupPeopleCountInput) return;

        if (noCheck && noCheck.checked) {
            groupPeopleCountInput.value = 1;
            if (ifYesDiv) ifYesDiv.style.display = "none";
        } else if (yesCheck && yesCheck.checked) {
            if (tableBody) {
                const count = tableBody.querySelectorAll('tr.group-people-list').length;
                groupPeopleCountInput.value = count + 1;
            } else {
                groupPeopleCountInput.value = '';
            }
            if (ifYesDiv) ifYesDiv.style.display = "block";
        } else {
            groupPeopleCountInput.value = '';
            if (ifYesDiv) ifYesDiv.style.display = "none";
        }
    }

    document.getElementById('noCheck')?.addEventListener('change', yesnoCheck);
    document.getElementById('yesCheck')?.addEventListener('change', yesnoCheck);

    // ✅ แปลงตัวเลข → ข้อความภาษาไทย
    function numberToThaiText(number) {
        number = Number(number).toFixed(2);
        const txtNumArr = ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
        const txtDigitArr = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
        let [baht, satang] = number.split('.');
        let bahtText = '';
        let satangText = '';

        function readNumber(num) {
            let text = '';
            num = num.replace(/^0+/, '');
            if (num.length === 0) return '';
            let len = num.length;
            for (let i = 0; i < len; i++) {
                let n = parseInt(num.charAt(i));
                if (n !== 0) {
                    if (i === len - 1 && n === 1 && len > 1) {
                        text += 'เอ็ด';
                    } else if (i === len - 2 && n === 2) {
                        text += 'ยี่';
                    } else if (i === len - 2 && n === 1) {
                        text += '';
                    } else {
                        text += txtNumArr[n];
                    }
                    text += txtDigitArr[len - 1 - i];
                }
            }
            return text;
        }

        function readNumberWithMillion(num) {
            let text = '';
            let million = '';
            while (num.length > 6) {
                let sub = num.slice(0, num.length - 6);
                million += readNumber(sub) + 'ล้าน';
                num = num.slice(-6);
            }
            text = million + readNumber(num);
            return text;
        }

        baht = baht.replace(/^0+/, '') || '0';
        if (parseInt(baht, 10) === 0) bahtText = 'ศูนย์บาท';
        else bahtText = readNumberWithMillion(baht) + 'บาท';

        if (parseInt(satang, 10) === 0) satangText = 'ถ้วน';
        else satangText = readNumber(satang) + 'สตางค์';

        return bahtText + satangText;
    }

    function updateExpensesTotalText() {
        const totalInput = document.getElementById('expenses_total');
        const textInput = document.getElementById('expenses_total_text');
        if (totalInput && textInput) {
            const val = parseFloat(totalInput.value.replace(/,/g, '') || 0);
            textInput.value = val ? numberToThaiText(val) : "ศูนย์บาทถ้วน";
        }
    }

    // เรียกครั้งแรกตอนโหลดหน้า
    updateExpensesTotalText();
</script>