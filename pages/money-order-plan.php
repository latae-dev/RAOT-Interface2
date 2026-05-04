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
    <div class="row has_plan_id" style="display: none;">
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
    <input type="hidden" name="expenses_id" id="expenses_id">
    <div class="row has_expenses_id" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#collapseExpensesDetail" aria-expanded="false" aria-controls="collapseExpensesDetail">
                    <div class="card-title mb-0">
                        รายละเอียดคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (รหัสคำขอเบิกค่าใช้จ่ายฯ : <span class="expenses_number"></span>)
                    </div>
                    <span class="chevron-icon transition rotate" id="chevronExpensesDetail">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </div>
                <div id="collapseExpensesDetail" class="collapse show">
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom fund_and_project">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">กองทุน</label>
                                <input type="text" id="fund_select_label_noplan" class="form-control bg-light" name="fund_select_label_noplan" readonly>
                                <span id="fund_html_label"></span>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <input type="text" id="project_select_label_noplan" class="form-control bg-light" name="project_select_label_noplan" readonly>
                            </div>
                        </div>
                    </div>
                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-2 mb-2">
                                        <label for="expenses_learn" class="form-label">เรียน :</label>
                                        <input type="text" class="form-control" id="expenses_learn" name="expenses_learn" placeholder="กรุณากรอก เรียน" autocomplete="off" disabled>
                                    </div>
                                    <div class="col-xl-2 mb-2">
                                        <label for="expenses_code" class="form-label">คำสั่ง/บันทึก :</label>
                                        <input type="text" class="form-control" id="expenses_code" name="expenses_code" placeholder="กรุณากรอก คำสั่ง/บันทึก" autocomplete="off" disabled>
                                    </div>
                                    <div class="col-xl-2 mb-2">
                                        <label for="input-label1" class="form-label">ลงวันที :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control flatpickr-input active" id="expenses_date" name="expenses_date" placeholder="ระบุวัน เดือน ปี" readonly="readonly" fdprocessedid="jp9fs9" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 mb-2">
                                        <div class="row">
                                            <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ :</label>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2 div-one-person">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="1" id="noCheck" disabled>
                                                    <label class="form-check-label" for="no">ข้าพเจ้า</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2 div-two-person">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="2" id="yesCheck" disabled>
                                                    <label class="form-check-label" for="yes">ข้าพเจ้าพร้อมคณะเดินทาง</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 mb-2">
                                        <label for="input-label1" class="form-label">จำนวนคณะเดินทาง :</label>
                                        <input type="text" class="form-control" placeholder="รวมผล จำนวนตารางหลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ" name="expenses_group_count" id="group_people_count" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault1_start" value="address" disabled>
                                            <label class="form-check-label" for="flexRadioDefault1_start">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault2_start" value="office" disabled>
                                            <label class="form-check-label" for="flexRadioDefault2_start">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault3_start" value="thailand" disabled>
                                            <label class="form-check-label" for="flexRadioDefault3_start">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="expenses_start_date" name="expenses_start_date" placeholder="กรุณากรอก วันที่เดินทาง" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_start_address" rows="2" disabled></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault1_end" value="address" disabled>
                                            <label class="form-check-label" for="flexRadioDefault1_end">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault2_end" value="office" disabled>
                                            <label class="form-check-label" for="flexRadioDefault2_end">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault3_end" value="thailand" disabled>
                                            <label class="form-check-label" for="flexRadioDefault3_end">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="expenses_end_date" name="expenses_end_date" placeholder="กรุณากรอก วันที่เดินทาง" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_end_address" rows="2" disabled></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียด ค่าเบี้ยเลี้ยง ค่าเช่าที่พัก ค่าพาหนะ ค่าใช้จ่ายอื่นๆ หักค่าอาหารฝึกอบรม-->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance" class="form-label">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_allowance" name="expenses_allowance" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_allowance_days" name="expenses_allowance_days" placeholder="กรุณากรอก จำนวน (วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_allowance_total" name="expenses_allowance_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation" class="form-label">ค่าเช่าที่พักประเภท (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_accommodation" name="expenses_accommodation" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_accommodation_days" name="expenses_accommodation_days" placeholder="กรุณากรอก จำนวน (วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_accommodation_total" name="expenses_accommodation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_transportation" class="form-label">ค่าพาหนะ (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_transportation" name="expenses_transportation" placeholder="กรุณากรอก ค่าพาหนะ (บาท)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_transportation" class="form-label">รายละเอียดค่าพาหนะ :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_remark" name="expenses_transportation_remark" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_transportation_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_total" name="expenses_transportation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_moving" class="form-label">ค่าขนย้ายสิ่งของส่วนตัว (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving" name="expenses_moving" placeholder="กรุณากรอก ค่าขนย้ายสิ่งของส่วนตัว (บาท)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_moving_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_moving_total" name="expenses_moving_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_other" class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_other" name="expenses_other" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_other_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_other_total" name="expenses_other_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_per_meal" class="form-label">ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_food_per_meal" name="expenses_food_per_meal" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_meals" class="form-label">จำนวน (มื้อ) :</label>
                                        <input type="number" class="form-control" id="expenses_food_meals" name="expenses_food_meals" placeholder="กรุณากรอก จำนวน (มื้อ)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_total" class="form-label">รวม (บาท) *หักลบ :</label>
                                        <input type="text" class="form-control" id="expenses_food_total" name="expenses_food_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">กรณีใช้ยานพาหนะส่วนตัว กรุณาระบุข้อมูล :</label>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle" id="expenses_vehicle" value="car" disabled>
                                            <label class="form-check-label" for="expenses_vehicle">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle" id="expenses_motorcycle" value="motorcycle" disabled>
                                            <label class="form-check-label" for="expenses_motorcycle">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label for="expenses_vehicle_number" class="form-label">หมายเลขทะเบียน :</label>
                                        <input type="text" class="form-control" id="expenses_vehicle_number" name="expenses_vehicle_number" placeholder="กรุณากรอก หมายเลขทะเบียน" autocomplete="off" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 has_vehicle" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="input-label1" class="form-label">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success">
                                            <thead class="text-center">
                                                <tr>
                                                    <!-- <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">วัน เดือน ปี</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                    <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">หมายเหตุ</td>
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
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_amount_details" type="text" placeholder="0.00" disabled></td>
                                                </tr>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_text_details" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                            <div class="col-xl-12 mb-2" id="ifYes" style="display: none;">
                                <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered border-success">
                                        <thead class="text-center">
                                            <tr>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ลำดับที่</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ชื่อ-นามสกุล</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ตำแหน่ง/ระดับ</td>
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
                                                <td scope="col" colspan="13" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                <td colspan="3"><input class="form-control" type="text" id="expenses_group_total" placeholder="0.00" disabled></td>
                                            </tr>
                                            <tr>
                                                <td scope="col" colspan="13" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                <td colspan="3">
                                                    <input class="form-control" type="text" id="expenses_group_total_text" placeholder="ศูนย์บาทถ้วน" disabled>
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
                                        <textarea class="form-control" id="expenses_note" name="expenses_note" rows="2" disabled></textarea>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_total" class="form-label">รวมทั้งสิ้น (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_total" name="expenses_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_total_text" class="form-label">รวมทั้งสิ้น (ตัวอักษร) :</label>
                                        <input type="text" class="form-control" id="expenses_total_text" name="expenses_total_text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row has_expenses_id_type_2" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#collapseExpensesDetailType2" aria-expanded="false" aria-controls="collapseExpensesDetailType2">
                    <div class="card-title mb-0">
                        รายละเอียดคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก) (รหัสคำขอเบิกค่าใช้จ่ายฯ : <span class="expenses_number"></span>)
                    </div>
                    <span class="chevron-icon transition rotate" id="chevronExpensesDetailType2">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </div>
                <div id="collapseExpensesDetailType2" class="collapse show">
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom fund_and_project">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">กองทุน</label>
                                <input type="text" id="fund_select_label_noplan_2" class="form-control bg-light" name="fund_select_label_noplan_2" readonly>
                                <span id="fund_html_label"></span>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <input type="text" id="project_select_label_noplan_2" class="form-control bg-light" name="project_select_label_noplan_2" readonly>
                            </div>
                        </div>
                    </div>
                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-3 mb-2">
                                        <label for="expenses_learn" class="form-label">เรียน :</label>
                                        <input type="text" class="form-control" id="expenses_learn_2" name="expenses_learn_2" placeholder="กรุณากรอก เรียน" autocomplete="off" disabled>
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label for="expenses_code" class="form-label">คำสั่ง/บันทึก :</label>
                                        <input type="text" class="form-control" id="expenses_code_2" name="expenses_code_2" placeholder="กรุณากรอก คำสั่ง/บันทึก" autocomplete="off" disabled>
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label for="input-label1" class="form-label">ลงวันที :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control flatpickr-input active" id="expenses_date_2" name="expenses_date_2" placeholder="ระบุวัน เดือน ปี" readonly="readonly" fdprocessedid="jp9fs9" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 mb-2 one-person">
                                        <label for="text_approve" class="form-label">ได้อนุมัติให้ :</label>
                                        <input type="text" class="form-control" id="text_approve_2" name="text_approve_2" placeholder="กรุณากรอก ได้อนุมัติให้" autocomplete="off" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location_2" id="flexRadioDefault1_start_2" value="address" disabled>
                                            <label class="form-check-label" for="flexRadioDefault1_start_2">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location_2" id="flexRadioDefault2_start_2" value="office" disabled>
                                            <label class="form-check-label" for="flexRadioDefault2_start_2">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location_2" id="flexRadioDefault3_start_2" value="thailand" disabled>
                                            <label class="form-check-label" for="flexRadioDefault3_start_2">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="expenses_start_date_2" name="expenses_start_date_2" placeholder="กรุณากรอก วันที่เดินทาง" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="expenses_start_address_2" name="expenses_start_address_2" rows="2" disabled></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :</label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault1_end_2" value="address" disabled>
                                            <label class="form-check-label" for="flexRadioDefault1_end_2">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location_2" id="flexRadioDefault2_end_2" value="office" disabled>
                                            <label class="form-check-label" for="flexRadioDefault2_end_2">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location_2" id="flexRadioDefault3_end_2" value="thailand" disabled>
                                            <label class="form-check-label" for="flexRadioDefault3_end_2">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="expenses_end_date_2" name="expenses_end_date_2" placeholder="กรุณากรอก วันที่เดินทาง" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="expenses_end_address_2" name="expenses_end_address_2" rows="2" disabled></textarea>
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
                                        <label for="position_withdraw_2" class="form-label">ฐานะ การเบิกของผู้เบิก :</label>
                                        <input type="text" class="form-control" id="position_withdraw_2" name="position_withdraw_2" placeholder="กรุณากรอก ฐานะ การเบิกของผู้เบิก" autocomplete="off" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_2" class="form-label">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_allowance_2" name="expenses_allowance_2" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_days_2" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_allowance_days_2" name="expenses_allowance_days_2" placeholder="กรุณากรอก จำนวน (วัน)" disabled disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_total_2" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_allowance_total_2" name="expenses_allowance_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_2" class="form-label">ค่าเช่าที่พักประเภท (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_accommodation_2" name="expenses_accommodation_2" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_days_2" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_accommodation_days_2" name="expenses_accommodation_days_2" placeholder="กรุณากรอก จำนวน (วัน)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_total_2" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_accommodation_total_2" name="expenses_accommodation_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_transportation_2" class="form-label">ค่าพาหนะ (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_transportation_2" name="expenses_transportation_2" placeholder="กรุณากรอก ค่าพาหนะ (บาท)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_transportation_remark_2" class="form-label">รายละเอียดค่าพาหนะ :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_remark_2" name="expenses_transportation_remark_2" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_transportation_total_2" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_total_2" name="expenses_transportation_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_other_2" class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_other_2" name="expenses_other_2" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_other_total_2" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_other_total_2" name="expenses_other_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_per_meal_2" class="form-label">ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_food_per_meal_2" name="expenses_food_per_meal_2" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_meals_2" class="form-label">จำนวน (มื้อ) :</label>
                                        <input type="number" class="form-control" id="expenses_food_meals_2" name="expenses_food_meals_2" placeholder="กรุณากรอก จำนวน (มื้อ)" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_total_2" class="form-label">รวม (บาท) *หักลบ :</label>
                                        <input type="text" class="form-control" id="expenses_food_total_2" name="expenses_food_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">กรณีใช้ยานพาหนะส่วนตัว กรุณาระบุข้อมูล :</label>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle_2" id="expenses_vehicle_2" value="car" disabled>
                                            <label class="form-check-label" for="expenses_vehicle_2">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle_2" id="expenses_motorcycle_2" value="motorcycle" disabled>
                                            <label class="form-check-label" for="expenses_motorcycle_2">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label for="expenses_vehicle_number_2" class="form-label">หมายเลขทะเบียน :</label>
                                        <input type="text" class="form-control" id="expenses_vehicle_number_2" name="expenses_vehicle_number_2" placeholder="กรุณากรอก หมายเลขทะเบียน" autocomplete="off" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 has_vehicle" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="input-label1" class="form-label">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success">
                                            <thead class="text-center">
                                                <tr>
                                                    <!-- <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">วัน เดือน ปี</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                    <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">หมายเหตุ</td>
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
                                            <tbody id="list-details-2">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_amount_details_2" type="text" placeholder="0.00" disabled></td>
                                                </tr>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_text_details_2" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                            <div class="col-xl-12 mb-2" id="ifYes_2" style="display: none;">
                                <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered border-success">
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
                                            <tr class="group-people-listgroup-people-list-2">
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td scope="col" colspan="13" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                <td colspan="3"><input class="form-control" type="text" id="expenses_group_total_2" placeholder="0.00" disabled></td>
                                            </tr>
                                            <tr>
                                                <td scope="col" colspan="13" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                <td colspan="3">
                                                    <input class="form-control" type="text" id="expenses_group_total_text_2" placeholder="ศูนย์บาทถ้วน" disabled>
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
                                        <textarea class="form-control" id="expenses_note_2" name="expenses_note_2" rows="2" disabled></textarea>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_total_2" class="form-label">รวมทั้งสิ้น (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_total_2" name="expenses_total_2" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_total_text_2" class="form-label">รวมทั้งสิ้น (ตัวอักษร) :</label>
                                        <input type="text" class="form-control" id="expenses_total_text_2" name="expenses_total_text_2" placeholder="ศูนย์บาทศูนย์สตางค์" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row group_type_3" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#collapseExpensesDetail" aria-expanded="false" aria-controls="collapseExpensesDetail">
                    <div class="card-title mb-0">
                        รายละเอียดคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (รหัสคำขอเบิกค่าใช้จ่ายฯ : <span class="expenses_number"></span>)
                    </div>
                    <span class="chevron-icon transition">
                        <a href="javascript:void(0);" rel="noopener noreferrer" id="link_group_type_3">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <style>
        .chevron-icon {
            transition: transform 0.2s;
            font-size: 1.2rem;
        }

        .chevron-icon.rotate {
            transform: rotate(90deg);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var collapseEl = document.getElementById('collapseExpensesDetail');
            var chevron = document.getElementById('chevronExpensesDetail');
            if (collapseEl && chevron) {
                collapseEl.addEventListener('show.bs.collapse', function() {
                    chevron.classList.add('rotate');
                });
                collapseEl.addEventListener('hide.bs.collapse', function() {
                    chevron.classList.remove('rotate');
                });
            }
            var collapseElType2 = document.getElementById('collapseExpensesDetailType2');
            var chevronType2 = document.getElementById('chevronExpensesDetailType2');
            if (collapseElType2 && chevronType2) {
                collapseElType2.addEventListener('show.bs.collapse', function() {
                    chevronType2.classList.add('rotate');
                });
                collapseElType2.addEventListener('hide.bs.collapse', function() {
                    chevronType2.classList.remove('rotate');
                });
            }
        });
    </script>
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายละเอียดใบสั่งจ่าย-->
                <div class="card-header justify-content-between">
                    <div class="card-title">รายละเอียดใบสั่งจ่าย</div>
                </div>

                <!-- เบิกจ่ายจาก กองทุนฯ/เงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง -->
                <div class="card-body border-bottom have_fund" style="display: none;">
                    <div class="row">
                        <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                        <div class="col-xl-6 mb-2">
                            <label class="form-check-label mb-1" for="other">กองทุน</label>
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
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-3 mb-2">
                                    <label class="form-label">ประเภทค่าตอบเเทนและค่าใช้จ่ายในการปฏิบัติงาน <small class="text-danger"> *จำเป็นต้องเลือก</small> :</label>
                                    <select class="form-control" data-trigge required id="compensation_expense_select" style="width: 100%"></select>
                                </div>
                                <!-- <div class="col-xl-3 mb-2">
                                    <label class="form-label">ประเภทงานที่ปฏิบัติ <small class="text-danger"> *จำเป็นต้องเลือก</small> :</label>
                                    <select class="form-control" data-trigge required id="compensation_expense_type_parent_select" style="width: 100%"></select>
                                </div> -->
                                <div class="col-xl-3 mb-2">
                                    <label for="req_number_withdraw" class="form-label">เลขที่เบิก :</label>
                                    <input type="text" class="form-control" id="req_number_withdraw" name="req_number_withdraw" placeholder="กรุณากรอก เลขที่เบิก" autocomplete="off">
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="req_number_pay" class="form-label">เลขที่จ่าย :</label>
                                    <input type="text" class="form-control" id="req_number_pay" name="req_number_pay" placeholder="กรุณากรอก เลขที่จ่าย" autocomplete="off">
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="compensation_date" class="form-label">ลงวันที่ :<small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                            <input type="text" class="form-control flatpickr-input active" id="date_notime" name="compensation_date" placeholder="กรุณาระบุ วันที่" fdprocessedid="jp9fs9">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row is_sap mb-2" style="display: none;" id="personal-table">
                        <p><span class="fs-15 fw-bold">ตารางข้อมูลผู้รับเงิน</p>
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered border-success">
                                <thead class="text-center">
                                    <tr>
                                        <td scope="col" rowspan="2" width="3%" class="bg-info text-fixed-dark bg-opacity-25"><input class="form-check-input check-all-personal" type="checkbox" id="all-products" value="" aria-label="..."></td>
                                        <td scope="col" rowspan="2" width="15%" class="bg-info text-fixed-dark bg-opacity-25">หมายเลขผู้เสียภาษี</td>
                                        <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-25">รายละเอียด</td>
                                        <td scope="col" colspan="2" width="25%" class="bg-info text-fixed-dark bg-opacity-25">จำนวนเงิน</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" class="bg-info text-fixed-dark bg-opacity-25">บาท</td>
                                        <td colspan="1" class="bg-info text-fixed-dark bg-opacity-25">สตางค์</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="personal-list">
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                        <td><input class="form-control" id="personal_total_bath" name="personal_total_bath" type="text" placeholder="00" disabled></td>
                                        <td><input class="form-control" id="personal_total_stang" name="personal_total_stang" type="text" placeholder="00" disabled></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                        <td colspan="2"><input class="form-control" id="personal_total_bath_text" name="personal_total_bath_text" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 addTable-personal" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 delTable-personal" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                    </div>
                    <div class="row is_no_sap">
                        <div class="col-xl-12 mb-2">
                            <div class="row">
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
                        <p><span class="fs-15 fw-bold">ตารางรายละเอียดค่าใช้จ่าย : </span><small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></p>
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered border-success">
                                <thead class="text-center">
                                    <tr>
                                        <td scope="col" rowspan="2" width="3%" class="bg-info text-fixed-dark bg-opacity-25"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></td>
                                        <td scope="col" rowspan="2" width="15%" class="bg-info text-fixed-dark bg-opacity-25">รหัสรายการ</td>
                                        <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-25">รายการ</td>
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
                                        <td colspan="4" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                        <td><input class="form-control" id="total_bath" name="total_bath" type="text" placeholder="00" disabled></td>
                                        <td><input class="form-control" id="total_stang" name="total_stang" type="text" placeholder="00" disabled></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                        <td colspan="2"><input class="form-control" id="total_bath_text" name="total_bath_text" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap is_no_sap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 addTable" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 delTable" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                        <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                        <div class="col-xl-12 mb-2 has_plan_id" style="display: none;">
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
                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2 has_plan_id" style="display: none;">
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
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2 has_plan_id" style="display: none;">
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