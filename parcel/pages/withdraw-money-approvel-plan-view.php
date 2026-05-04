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
    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

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

<div class="container-fluid" id="div-approve" style="display:none;">
    <!-- เพิ่ม CSRF Token -->
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">ดูรายละเอียดคำขอเบิกเงินทดรองจ่าย</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">ดูรายละเอียดแบบฟอร์มคำขอเบิกเงินทดรองจ่าย</li>
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
                        <div class="card-title">เลขที่เอกสาร: <span name="plan_number"></span></div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                <input type="text" class="form-control bg-light" id="plan_form_name" name="plan_form_name" disabled>
                            </div>
                            <div class="col-xl-3 mb-2">
                                <label for="publish-date" class="form-label">วันและเวลาเริ่มต้น :</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="plan_start" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 mb-2">
                                <label class="form-label">วันและเวลาสิ้นสุด :</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                        <input type="text" class="form-control" id="date" name="plan_end" disabled>
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
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">กองทุนฯเงิน</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold"> เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1" for="other">เงินทุน</label>
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
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-start" scope="col" style="width: 200px;">รหัสรายการ</th>
                                                <th class="text-center" scope="col" style="width: 300px;">รายการ</th>
                                                <th class="text-center" scope="col">จำนวน</th>
                                                <th class="text-center" scope="col" style="width: 150px;">หน่วย</th>
                                                <th class="text-center" scope="col">ราคาหน่วย</th>
                                                <th class="text-center" scope="col">จำนวนวัน</th>
                                                <th class="text-center" scope="col">ราคาทั้งหมด</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-end fw-bold">รวม</td>
                                                <td class="total-price text-end" id="total">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
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
                    <div class="card-header">
                        <div class="card-title">ผลการพิจารณาอนุมัติ</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
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
                                                <th class="text-center" scope="col">หมายเหตุ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ที่ท้ายฟอร์ม -->
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userData = JSON.parse(sessionStorage.getItem("raot_user_session"));
        const hasMatch = userData?.position_list_id
            .some(id => ['5', '12', '13'].includes(id));
        if (!userData || !hasMatch) {
            window.location.href = "index.php?no_permission=1";
        } else {
            document.getElementById('div-approve').style.display = "";
            loadMainScripts();
        }
    });

    function loadMainScripts() {
        const script = document.createElement('script');
        script.type = "module";
        script.src = "<?php echo $baseUrl; ?>/pages/js/withdraw-money-approvel-plan-view.js";
        document.body.appendChild(script);
    }
</script>