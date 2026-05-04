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
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มลงรับเอกสาร</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มลงรับเอกสาร</li>
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
                        <div class="card-title">ผู้บันทึกข้อมูล</div>
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
        <div class="row" id="transfer-section">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">การลงรับเอกสาร</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="col-xl-12 mb-2 one-person">
                            <div class="row">
                                <div class="col-3 mb-2">
                                    <label for="transfer_number" class="form-label" data-text="เลขรับเอกสาร">เลขรับเอกสาร :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <input type="text" class="form-control" id="transfer_number" name="transfer_number" placeholder="กรุณากรอก เลขรับเอกสาร" autocomplete="off">
                                </div>
                                <div class="col-3 mb-2">
                                    <label for="transfer_date" class="form-label" data-text="วันที่และเวลาที่รับเอกสาร">วันที่และเวลาที่รับเอกสาร :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <input type="text" class="form-control" id="transfer_date" name="transfer_date" placeholder="กรุณากรอก วันที่และเวลาที่รับเอกสาร">
                                </div>
                                <div class="col-3 mb-2">
                                    <label for="transfer_user_id" class="form-label" data-text="ผู้รับเอกสาร">ผู้รับเอกสาร :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <input type="text" class="form-control" id="transfer_user_id" name="transfer_user_id" placeholder="กรุณากรอก ผู้รับเอกสาร" autocomplete="off">
                                </div>
                                <input type="hidden" name="expenses_id" id="expenses_id">
                            </div>
                        </div>
                    </div>
                    <!-- ที่ท้ายฟอร์ม -->

                </div>
            </div>
        </div>
        <div class="row" id="history-section" style="display:none;">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">ประวัติการแก้ไข</div>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="col-xl-12">
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-fixed-dark" scope="col" style="width: 70px;">ลำดับที่</th>
                                            <th class="text-center text-fixed-dark" scope="col">เลขรับเอกสาร</th>
                                            <th class="text-center text-fixed-dark" scope="col">วันที่และเวลาที่รับเอกสาร</th>
                                            <th class="text-center text-fixed-dark" scope="col">ผู้รับเอกสาร</th>
                                            <th class="text-center text-fixed-dark" scope="col">ผู้บันทึกข้อมูล</th>
                                            <th class="text-center text-fixed-dark" scope="col">วันที่และเวลาที่บันทึกข้อมูล</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/travel-expenses-money-transfer.js"></script>