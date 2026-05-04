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
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <form id="advanceFrm" novalidate>
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
                                <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                <input type="text" class="form-control bg-light" id="expenses_form_name" name="expenses_form_name" value="ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน" readonly>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="publish-date" class="form-label">วันและเวลาเริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="expenses_start">
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 mb-2">
                                <label class="form-label">วันและเวลาสิ้นสุด :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="expenses_end">
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
        <!-- <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">รายละเอียดคำขอเบิกเงินทดรองจ่าย (รหัสคำขอเบิกเงินทดรองจ่าย : <span class="plan_number"></span>)</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">เงินทุนที่ใช้</label>
                                <input type="text" id="fund_select_label" class="form-control bg-light" name="fund_select_label" readonly>
                                <span id="fund_html_label"></span>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <input type="text" id="project_select_label" class="form-control bg-light" name="project_select_label" readonly>
                            </div>
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
        </div> -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">

                    <!-- ส่วนหัว รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน-->
                    <div class="card-header justify-content-between">
                        <div class="card-title">รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</div>
                    </div>

                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">กองทุน</label>
                                <select class="form-control fund-select" data-trigge required id="fund_select" style="width: 100%"></select>
                            </div>
                            <!-- <div class="col-xl-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="" id="other">
                                    <label class="form-check-label mb-1" for="other">อื่นๆ</label>
                                    <textarea class="form-control" id="fund_more" rows="2"></textarea>
                                </div>
                            </div> -->
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">โครงการ</label>
                                <select class="form-control project-select" data-trigge required id="project_select" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-2 mb-2">
                                        <label for="expenses_learn" class="form-label">เรียน :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_learn" name="expenses_learn" placeholder="กรุณากรอก เรียน" autocomplete="off">
                                    </div>
                                    <div class="col-xl-2 mb-2">
                                        <label for="expenses_code" class="form-label">คำสั่ง/บันทึก :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_code" name="expenses_code" placeholder="กรุณากรอก คำสั่ง/บันทึก" autocomplete="off">
                                    </div>
                                    <div class="col-xl-2 mb-2">
                                        <label for="input-label1" class="form-label">ลงวันที :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control flatpickr-input active" id="date_notime" name="expenses_date" placeholder="ระบุวัน เดือน ปี" readonly="readonly" fdprocessedid="jp9fs9">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 mb-2">
                                        <div class="row">
                                            <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2 div-one-person">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="1" id="noCheck">
                                                    <label class="form-check-label" for="no">ข้าพเจ้า</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2 div-two-person">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="2" id="yesCheck">
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
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault1_start" value="address">
                                            <label class="form-check-label" for="flexRadioDefault1_start">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault2_start" value="office">
                                            <label class="form-check-label" for="flexRadioDefault2_start">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault3_start" value="thailand">
                                            <label class="form-check-label" for="flexRadioDefault3_start">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="date" name="expenses_start_date" placeholder="กรุณากรอก วันที่เดินทาง">
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
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault1_end" value="address">
                                            <label class="form-check-label" for="flexRadioDefault1_end">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault2_end" value="office">
                                            <label class="form-check-label" for="flexRadioDefault2_end">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault3_end" value="thailand">
                                            <label class="form-check-label" for="flexRadioDefault3_end">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="date" name="expenses_end_date" placeholder="กรุณากรอก วันที่เดินทาง">
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
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance" class="form-label">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_allowance" name="expenses_allowance" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_allowance_days" name="expenses_allowance_days" placeholder="กรุณากรอก จำนวน (วัน)">
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
                                        <input type="number" class="form-control format-number-2point" id="expenses_accommodation" name="expenses_accommodation" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_accommodation_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-1point" id="expenses_accommodation_days" name="expenses_accommodation_days" placeholder="กรุณากรอก จำนวน (วัน)">
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
                                        <input type="number" class="form-control format-number-2point" id="expenses_transportation" name="expenses_transportation" placeholder="กรุณากรอก ค่าพาหนะ (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_transportation" class="form-label">รายละเอียดค่าพาหนะ :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_remark" name="expenses_transportation_remark" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_transportation_total" class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_total" name="expenses_transportation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_moving" class="form-label">ค่าขนย้ายสิ่งของส่วนตัว (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving" name="expenses_moving" placeholder="กรุณากรอก ค่าขนย้ายสิ่งของส่วนตัว (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-8 col-md-8 col-sm-8 mb-2">
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
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="expenses_other" class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_other" name="expenses_other" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ">
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
                                        <input type="number" class="form-control format-number-2point" id="expenses_food_per_meal" name="expenses_food_per_meal" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_food_meals" class="form-label">จำนวน (มื้อ) :</label>
                                        <input type="number" class="form-control" id="expenses_food_meals" name="expenses_food_meals" placeholder="กรุณากรอก จำนวน (มื้อ)">
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
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle" id="expenses_vehicle" value="car">
                                            <label class="form-check-label" for="expenses_vehicle">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_motorcycle" id="expenses_motorcycle" value="motorcycle">
                                            <label class="form-check-label" for="expenses_motorcycle">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label for="expenses_vehicle_number" class="form-label">หมายเลขทะเบียน :<small class="text-danger ml-2" id="expenses_vehicle_number_required" style="font-weight: lighter;"> * จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_vehicle_number" name="expenses_vehicle_number" placeholder="กรุณากรอก หมายเลขทะเบียน" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 display-vehicle" style="display: none;">
                                <div class="row ">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label for="input-label1" class="form-label">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success">
                                            <thead class="text-center">
                                                <tr>
                                                    <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25" style="min-width: 160px;">วัน เดือน ปี</td>
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
                                            <tbody>
                                                <tr class="list-details"></tr>
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
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1 addTable-list-details" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1 delTable-list-details" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                            <style>
                                .table-responsive {
                                    overflow-x: auto;
                                }

                                .table-bordered th,
                                .table-bordered td {
                                    min-width: 120px;
                                    /* ปรับตามความเหมาะสมแต่ละช่อง */
                                    white-space: nowrap;
                                    text-align: center;
                                }

                                .table-bordered th:first-child,
                                .table-bordered td:first-child {
                                    min-width: 40px;
                                }

                                .table-bordered th:nth-child(2),
                                .table-bordered td:nth-child(2) {
                                    min-width: 60px;
                                }

                                .table-bordered th:nth-child(3),
                                .table-bordered td:nth-child(3) {
                                    min-width: 230px;
                                }

                                .table-bordered th:nth-child(4),
                                .table-bordered td:nth-child(4) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(5),
                                .table-bordered td:nth-child(5) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(6),
                                .table-bordered td:nth-child(6) {
                                    min-width: 100px;
                                }

                                .table-bordered th:nth-child(7),
                                .table-bordered td:nth-child(7) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(8),
                                .table-bordered td:nth-child(8) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(9),
                                .table-bordered td:nth-child(9) {
                                    min-width: 100px;
                                }

                                .table-bordered th:nth-child(10),
                                .table-bordered td:nth-child(10) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(11),
                                .table-bordered td:nth-child(11) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(12),
                                .table-bordered td:nth-child(12) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(13),
                                .table-bordered td:nth-child(13) {
                                    min-width: 120px;
                                }

                                .table-bordered th:nth-child(14),
                                .table-bordered td:nth-child(14) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(15),
                                .table-bordered td:nth-child(15) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(16),
                                .table-bordered td:nth-child(16) {
                                    min-width: 150px;
                                }

                                /* ปรับ min-width เฉพาะช่องที่ต้องการให้กว้างขึ้น */
                                .table-bordered th.bg-info,
                                .table-bordered td.bg-info {
                                    min-width: 50px;
                                }
                            </style>
                            <div class="col-xl-12 mb-2" id="ifYes" style="display: none;">
                                <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered border-success">
                                        <thead class="text-center">
                                            <tr>
                                                <th scope="col" rowspan="3"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th>
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
                                    </table>
                                </div>
                                <div class="flex-wrap mt-3">
                                    <div class="col-12">
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9 text-end">
                                                จำนวนเงินทั้งสิ้น (บาท)
                                            </div>
                                            <div class="col-3">
                                                <input class="form-control" type="text" id="expenses_group_total" placeholder="0.00" disabled>
                                            </div>
                                            <div class="col-9 text-end mt-2">
                                                จำนวนเงินทั้งสิ้น (ตัวอักษร)
                                            </div>
                                            <div class="col-3 mt-2">
                                                <input class="form-control" type="text" id="expenses_group_total_text" placeholder="ศูนย์บาทถ้วน" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                        <button class="btn btn-success-light m-1 addTable-group-people" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                        <button class="btn btn-danger-light m-1 delTable-group-people" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label mb-2">หมายเหตุ :</label>
                                        <textarea class="form-control" id="expenses_note" name="expenses_note" rows="2"></textarea>
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
                            <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                            <!-- <div class="col-xl-12 mb-2">
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
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_action" id="expenses_return" value="return">
                                            <label class="form-check-label" for="expenses_return">ส่งคืน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_action" id="expenses_withdraw" value="withdraw">
                                            <label class="form-check-label" for="expenses_withdraw">เบิกเพิ่ม</label>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2">
                                <div class="row">
                                    <div class="col-xl-12 mb-2">
                                        <label for="expenses_amount" class="form-label">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                        <input type="number" class="form-control" id="expenses_amount" name="expenses_amount" placeholder="0.00">
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="expenses_confirm" name="expenses_confirm">
                                    <label class="form-check-label" for="expenses_confirm">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง <span class="text-danger"> *</span> </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ส่วนท้าย การขออนุมัติ -->
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end" id="button-container">
                    </div>

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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/travel-expenses-money-create.js"></script>
<script>
    function calcExpensesAllowanceTotal() {
        const allowance = parseFloat(document.getElementById('expenses_allowance')?.value.replace(/,/g, '') || 0);
        const days = parseFloat(document.getElementById('expenses_allowance_days')?.value.replace(/,/g, '') || 0);
        const total = allowance * days;
        const totalInput = document.getElementById('expenses_allowance_total');
        if (totalInput) {
            totalInput.value = total ? total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_allowance')?.addEventListener('input', calcExpensesAllowanceTotal);
    document.getElementById('expenses_allowance_days')?.addEventListener('input', calcExpensesAllowanceTotal);
    calcExpensesAllowanceTotal();

    function calcExpensesAccommodationTotal() {
        const price = parseFloat(document.getElementById('expenses_accommodation')?.value.replace(/,/g, '') || 0);
        const days = parseFloat(document.getElementById('expenses_accommodation_days')?.value.replace(/,/g, '') || 0);
        const total = price * days;
        const totalInput = document.getElementById('expenses_accommodation_total');
        if (totalInput) {
            totalInput.value = total ? total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_accommodation')?.addEventListener('input', calcExpensesAccommodationTotal);
    document.getElementById('expenses_accommodation_days')?.addEventListener('input', calcExpensesAccommodationTotal);
    calcExpensesAccommodationTotal();

    function calcExpensesTransportationTotal() {
        const val = parseFloat(document.getElementById('expenses_transportation')?.value.replace(/,/g, '') || 0);
        const totalInput = document.getElementById('expenses_transportation_total');
        if (totalInput) {
            totalInput.value = val ? val.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_transportation')?.addEventListener('input', calcExpensesTransportationTotal);
    calcExpensesTransportationTotal();

    function calcExpensesMovingTotal() {
        const val = parseFloat(document.getElementById('expenses_moving')?.value.replace(/,/g, '') || 0);
        const totalInput = document.getElementById('expenses_moving_total');
        if (totalInput) {
            totalInput.value = val ? val.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_moving')?.addEventListener('input', calcExpensesMovingTotal);
    calcExpensesMovingTotal();

    function calcExpensesMovingTotal() {
        const val = parseFloat(document.getElementById('expenses_moving')?.value.replace(/,/g, '') || 0);
        const totalInput = document.getElementById('expenses_moving_total');
        if (totalInput) {
            totalInput.value = val ? val.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_moving')?.addEventListener('input', calcExpensesMovingTotal);
    calcExpensesMovingTotal();

    function calcExpensesOtherTotal() {
        const val = parseFloat(document.getElementById('expenses_other')?.value.replace(/,/g, '') || 0);
        const totalInput = document.getElementById('expenses_other_total');
        if (totalInput) {
            totalInput.value = val ? val.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_other')?.addEventListener('input', calcExpensesOtherTotal);
    calcExpensesOtherTotal();

    function calcExpensesFoodTotal() {
        const perMeal = parseFloat(document.getElementById('expenses_food_per_meal')?.value.replace(/,/g, '') || 0);
        const meals = parseFloat(document.getElementById('expenses_food_meals')?.value.replace(/,/g, '') || 0);
        const total = perMeal * meals;
        const totalInput = document.getElementById('expenses_food_total');
        if (totalInput) {
            totalInput.value = total ? total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        calcExpensesTotal();
    }

    document.getElementById('expenses_food_per_meal')?.addEventListener('input', calcExpensesFoodTotal);
    document.getElementById('expenses_food_meals')?.addEventListener('input', calcExpensesFoodTotal);
    calcExpensesFoodTotal();

    function numberToThaiText(number) {
        number = Number(number).toFixed(2);
        const txtNumArr = ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
        const txtDigitArr = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
        let [baht, satang] = number.split('.');
        let bahtText = '';
        let satangText = '';

        function readNumber(num) {
            let text = '';
            num = num.replace(/^0+/, ''); // ตัด 0 หน้าสุด
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

        // รองรับหลักล้านขึ้นไป
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

    // เรียกใช้ทุกครั้งที่ยอดรวมเปลี่ยน
    document.getElementById('expenses_total')?.addEventListener('input', updateExpensesTotalText);

    // เรียกหลังคำนวณยอดรวม
    function calcExpensesTotal() {
        const allowance = parseFloat(document.getElementById('expenses_allowance_total')?.value.replace(/,/g, '') || 0);
        const accommodation = parseFloat(document.getElementById('expenses_accommodation_total')?.value.replace(/,/g, '') || 0);
        const transportation = parseFloat(document.getElementById('expenses_transportation_total')?.value.replace(/,/g, '') || 0);
        const moving = parseFloat(document.getElementById('expenses_moving_total')?.value.replace(/,/g, '') || 0);
        const other = parseFloat(document.getElementById('expenses_other_total')?.value.replace(/,/g, '') || 0);
        const food = parseFloat(document.getElementById('expenses_food_total')?.value.replace(/,/g, '') || 0);

        // เพิ่มส่วนนี้
        const totalAmountDetails = parseFloat(document.getElementById('total_amount_details')?.value.replace(/,/g, '') || 0);
        const expensesGroupTotal = parseFloat(document.getElementById('expenses_group_total')?.value.replace(/,/g, '') || 0);

        // รวมทุกช่อง - หักค่าอาหารฝึกอบรม + รายละเอียด + หมู่คณะ
        const total = allowance + accommodation + transportation + moving + other - food +
            totalAmountDetails + expensesGroupTotal;

        const totalInput = document.getElementById('expenses_total');
        if (totalInput) {
            totalInput.value = total ? total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : '';
        }
        // ถ้ามีฟังก์ชันแปลงเป็นตัวอักษร ให้เรียกต่อ
        if (typeof updateExpensesTotalText === 'function') updateExpensesTotalText();
        updateExpensesTotalText();
    }

    // เรียกครั้งแรก
    updateExpensesTotalText();

    document.querySelectorAll('.format-number-2point').forEach(input => {
        input.addEventListener('blur', function() {
            let val = parseFloat(this.value.replace(/,/g, ''));
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            } else {
                this.value = '';
            }
        });
    });
    document.querySelectorAll('.format-number-1point').forEach(input => {
        input.addEventListener('blur', function() {
            let val = parseFloat(this.value.replace(/,/g, ''));
            if (!isNaN(val)) {
                this.value = val.toFixed(1);
            } else {
                this.value = '';
            }
        });
    });
</script>