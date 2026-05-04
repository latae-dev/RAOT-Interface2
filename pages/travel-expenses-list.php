<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start:: row-4 -->
    <div class="row">

        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="card-title">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</div>
                    <!-- <a class="btn btn-primary btn-sm" href="parcel-withdrawal-plan.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>กำหนดแผนการเบิกพัสดุแบบพิมพ์</a> -->
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">รหัสคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน :</label>
                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก คำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">เริ่มต้น :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันเริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สิ้นสุด :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันสิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="s-1">อนุมติ</option>
                                <option value="s-2">รออนุมัติ</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <a class="btn btn-success m-1" href="travel-expenses-create.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>ยื่นคำขอเบิกค่าใช้จ่าย</a>
                                <a class="btn btn-success m-1" href="travel-expenses-create-equ.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>ยื่นคำขอเบิกค่าใช้จ่าย (บุคคลภายนอก)</a>
                                <button class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <div class="table-responsive mt-2">
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                        <th scope="col">รหัสแผนคำขอเบิกเงินทดรอง</th>
                                        <th scope="col">ชื่อแบบฟอร์ม</th>
                                        <th scope="col">หน่วยงาน</th>
                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                        <th scope="col">สร้างใบเบิกค่าใช้จ่าย</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="crm-contact text-center">
                                        <td>1</td>
                                        <td>01-03-2567</td>
                                        <td>ุ6800001</td>
                                        <td>ยืมเงินทดลอง</td>
                                        <td>นนทบุรี</td>
                                        <td><span class="badge rounded-pill bg-success">อนุมติ</span></td>
                                        <td>
                                            <div class="btn-list">
                                                <a href="travel-expenses-create.php" class="btn btn-success-light btn-sm">ส่งเงินยืมทดรองจ่าย</a>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 fs-15">
                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="crm-contact text-center">
                                        <td>2</td>
                                        <td>28-02-2567</td>
                                        <td>ุ6800002</td>
                                        <td>ยืมเงินทดลอง</td>
                                        <td>น่าน</td>
                                        <td><span class="badge rounded-pill bg-warning">รออนุมติ</span></td>
                                        <td>
                                            <div class="btn-list">
                                                <a href="travel-expenses-create.php" class="btn btn-success-light btn-sm disabled">ส่งเงินยืมทดรองจ่าย</a>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 fs-15">
                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                                                <a href="./export/travel-expenses-list-export.php" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill">
                                                    <i class="bx bx-export"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill"><i class="ri-delete-bin-line"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top-0">
                    <div class="d-flex align-items-center">
                        <div>
                            กำลังแสดง 10 รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                        </div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation" class="pagination-style-4">
                                <ul class="pagination mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="javascript:void(0);">
                                            ก่อนหน้า
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                    <li class="page-item">
                                        <a class="page-link text-primary" href="javascript:void(0);">
                                            ถัดไป
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End:: row-4 -->

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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-doc-list.js"></script>