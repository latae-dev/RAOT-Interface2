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
    /* .form-control:disabled, .form-control[readonly] {
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
        <h1 class="page-title fw-semibold fs-18 mb-0">คำขอเบิกเงินทดรองจ่าย</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคำขอเบิกเงินทดรองจ่าย</li>
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
                        <div class="card-title">รายละเอียดผู้ยื่นคำขอเบิกเงินทดรองจ่าย</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                <input type="text" class="form-control bg-light" id="plan_form_name" name="plan_form_name" value="ยืมเงินทดรอง" readonly>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="publish-date" class="form-label">วันและเวลาเริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="plan_start" placeholder="กรุณากรอก วันและเวลาเริ่มต้น" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 mb-2">
                                <label class="form-label">วันและเวลาสิ้นสุด :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="plan_end" placeholder="กรุณากรอก วันและเวลาสิ้นสุด" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                                <input readonly type="text" class="form-control bg-light" id="depart_code" name="depart_code" placeholder="กรุณากรอก รหัสหน่วยงาน">
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                                <input readonly type="text" class="form-control bg-light" id="full_name" name="full_name" placeholder="กรุณากรอก ชื่อ-นามสกุล">
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                                <input readonly type="text" class="form-control bg-light" id="position_name" name="position_name" placeholder="กรุณากรอก ตำแหน่ง">
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="input-label1" class="form-label">ระดับ :</label>
                                <input readonly type="text" class="form-control bg-light" id="level_name" name="level_name" placeholder="กรุณากรอก ระดับ">
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="input-label1" class="form-label">สังกัด :</label>
                                <input readonly type="text" class="form-control bg-light" id="affiliation_name" name="affiliation_name" placeholder="กรุณากรอก สังกัด">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">กองทุนฯเงิน</div>
                    </div>
                    <!-- <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold"> เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อการบริหาร 49(1)" id="491">
                                    <label class="form-check-label" for="491">เงินทุนเพื่อการบริหาร 49(1)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)" id="492">
                                    <label class="form-check-label" for="492">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)" id="493">
                                    <label class="form-check-label" for="493">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)" id="494">
                                    <label class="form-check-label" for="494">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</label>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)" id="495">
                                    <label class="form-check-label" for="495">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)" id="496">
                                    <label class="form-check-label" for="496">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="กองทุนพัฒนายางพารา" id="para">
                                    <label class="form-check-label" for="para">กองทุนพัฒนายางพารา</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input fund" type="checkbox" value="เงิน กยท. มาตรา 13" id="13">
                                    <label class="form-check-label" for="13">เงิน กยท. มาตรา 13</label>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="" id="other">
                                    <label class="form-check-label mb-1" for="other">อื่นๆ</label>
                                    <textarea class="form-control" id="fund_more" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">เงินทุน</label>
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


                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="table-responsive mb-2">
                                    <div class="mb-2">
                                        <small class="text-danger ml-2"> *จำเป็นต้องกรอกทุกช่อง </small>
                                    </div>
                                    <table class="table text-nowrap  table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                </th>
                                                <th scope="col" style="width: 200px;">รหัสรายการ</th>
                                                <th scope="col" style="width: 300px;">รายการ</th>
                                                <th scope="col">จำนวน</th>
                                                <th scope="col" style="width: 150px;">หน่วย</th>
                                                <th scope="col">ราคาหน่วย</th>
                                                <th scope="col">จำนวนวัน</th>
                                                <th scope="col">ราคาทั้งหมด</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="7" class="text-end fw-bold">รวม</td>
                                                <td class="total-price text-end" id="total">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- ในส่วน table controls -->
                                <div class="border-block-start-dashed d-sm-flex justify-content-start">
                                    <button type="button" class="btn btn-success-light m-1 addTable">
                                        <i class="bi bi-plus"></i> เพิ่มรายการ
                                    </button>
                                    <button type="button" class="btn btn-danger-light m-1 delTable">
                                        <i class="bi bi-dash"></i> ลบรายการ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-3">
                                <div class="mb-2">
                                    <small class="text-danger ml-2"> *จำเป็นต้องเลือก </small>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input day_return" type="checkbox" value="" id="day_return">
                                    <label class="form-check-label" for="day_return">นับจากวันกลับมาถึง</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input money_receipt" type="checkbox" value="" id="money_receipt" checked>
                                    <label class="form-check-label" for="money_receipt">นับแต่วันที่ได้รับเงิน</label>
                                </div>
                            </div>
                            <div class="col-xl-9">
                                <div class="form-check mb-2">
                                    <label class="form-check-label mb-1" for="note">เหตุผลของการขอยืมเงินทดรอง <small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                    <textarea class="form-control note" id="note" name="note" rows="" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ที่ท้ายฟอร์ม -->
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                        <a href="withdraw-money-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <!-- ปุ่มนี้คือส่งฟอร์มจริง -->
                        <button id="btn-create" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> ขออนุมัติ
                        </button>
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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/withdraw-money-plan.js"></script>