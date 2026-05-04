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
        <h1 class="page-title fw-semibold fs-18 mb-0">รายการสถานะการอนุมัติ</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">ระบบบริหารจัดการข้อมูลพัสดุ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายการสถานะการอนุมัติ</li>
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
                    <div class="card-title">รายการสถานะการอนุมัติ</div>
                    <!-- <a class="btn btn-primary btn-sm" href="parcel-withdrawal-plan.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>กำหนดสถานะการอนุมัติ</a> -->
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">รหัสสถานะการอนุมัติ :</label>
                            <input name="plan_number" type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสการเบิกพัสดุแบบพิมพ์">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">เริ่มต้น :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input name="start_date" type="text" class="form-control" id="date" placeholder="กำหนด วันเริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สิ้นสุด :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input name="end_date" type="text" class="form-control" id="date" placeholder="กำหนด วันสิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สถานะการรวมไฟล์ :</label>
                            <select name="status_plan" class="form-select">
                                <option value="">ทั้งหมด</option>
                                <option value="approve">อนุมัติ</option>
                                <option value="pending">รออนุมัติ</option>
                            </select>
                        </div>

                        <div class="col-xl-12">
                            <div class="text-center">
                                <button id="searchBtn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
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
                                        <th scope="col">รหัสการเบิกพัสดุแบบพิมพ์</th>
                                        <th scope="col">รายละเอียดอ้างอิง</th>
                                        <th scope="col">จังหวัด </th>
                                        <th scope="col">สถานะการรวมไฟล์</th>
                                        <th scope="col">สาขา</th>
                                        <th scope="col">จังหวัด</th>
                                        <th scope="col">เขต</th>
                                        <th scope="col">กยท.</th>
                                        <th scope="col" style="width:150px;">รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center" id="data-table">
                                    <!-- Pagination links will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top-0">
                    <div class="d-flex align-items-center">
                        <div id="showing-count">
                            กำลังแสดง 0 รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                        </div>
                        <div class="ms-auto">
                            <nav aria-label="Page navigation" class="pagination-style-4">
                                <ul class="pagination mb-0" id="pagination">
                                    <!-- Pagination links will be inserted here -->
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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-withdrawal-approve-status-list.js"></script>