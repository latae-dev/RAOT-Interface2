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
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">การอนุมัติใบเบิกเบิกพัสดุแบบพิมพ์</h1>
            <div class="mt-2">
                <span class="badge bg-primary-transparent" id="user-approval-level">
                    <i class="bi bi-person-badge"></i> กำลังโหลด...
                </span>
                <span class="badge bg-info-transparent ms-1" id="user-department">
                    <i class="bi bi-building"></i> กำลังโหลด...
                </span>
            </div>
        </div>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบบริหารจัดการข้อมูลพัสดุ</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">การอนุมัติใบเบิกเบิกพัสดุแบบพิมพ์</li>
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
                    <div class="card-title">รายการใบเบิกเบิกพัสดุแบบพิมพ์</div>
                    <!-- <a class="btn btn-primary btn-sm" href="parcel-withdrawal-doc.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>กำหนดใบเบิกเบิกพัสดุแบบพิมพ์</a> -->
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">รหัสใบเบิกเบิกพัสดุแบบพิมพ์ :</label>
                            <input name="doc_number" type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสการเบิกพัสดุแบบพิมพ์">
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
                            <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                            <select name="status_doc" class="form-select">
                                <option value="">ทั้งหมด</option>
                                <option value="approve">อนุมัติ</option>
                                <option value="pending">รออนุมัติ</option>
                            </select>
                        </div>

                        <div class="col-xl-12">
                            <div class="text-center">
                                <!-- <a class="btn btn-success m-1" href="parcel-withdrawal-doc.php"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>กำหนดใบเบิกเบิกพัสดุแบบพิมพ์</a> -->
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
                                        <th scope="col">รหัสรายการใบเบิกเบิกพัสดุ</th>
                                        <th scope="col">รายการ</th>
                                        <th scope="col">ไตรมาสที่</th>
                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>

                                    </tr>
                                </thead>
                                <tbody id="data-table">
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

<!-- Initialize Choices.js for filter select -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for filter-status select
        const filterStatusSelect = document.getElementById('filter-status');
        if (filterStatusSelect && typeof Choices !== 'undefined') {
            new Choices(filterStatusSelect, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false
            });
        }

        // โหลดข้อมูล user hierarchy
        loadUserHierarchyInfo();
    });

    // ฟังก์ชันโหลดข้อมูล user hierarchy
    function loadUserHierarchyInfo() {
        const baseUrl = '<?php echo $baseUrl; ?>';
        const url = `${baseUrl}/controllers/investment_budget/ib_requests_controller.php?action=user_hierarchy`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    const debug = data.data;

                    const levelBadge = document.getElementById('user-approval-level');
                    const deptBadge = document.getElementById('user-department');

                    const levelLabel =
                        debug.approval_level_label ||
                        debug.depart_detected_level_label ||
                        null;

                    if (levelBadge) {
                        if (levelLabel) {
                            levelBadge.innerHTML = `<i class="bi bi-person-badge"></i> ระดับ: ${levelLabel}`;
                            levelBadge.className = 'badge bg-primary-transparent';
                            levelBadge.style.display = '';
                        } else {
                            levelBadge.style.display = 'none';
                        }
                    }

                    if (deptBadge) {
                        const departCode =
                            debug.depart_code && debug.depart_code !== 'N/A'
                                ? debug.depart_code
                                : null;
                        if (departCode) {
                            deptBadge.innerHTML = `<i class="bi bi-building"></i> รหัสหน่วยงาน: ${departCode}`;
                            deptBadge.className = 'badge bg-info-transparent ms-1';
                            deptBadge.style.display = '';
                        } else {
                            deptBadge.style.display = 'none';
                        }
                    }
                } else {
                    // ซ่อน badges ถ้าไม่มีข้อมูล
                    const levelBadge = document.getElementById('user-approval-level');
                    const deptBadge = document.getElementById('user-department');
                    if (levelBadge) levelBadge.style.display = 'none';
                    if (deptBadge) deptBadge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading user hierarchy info:', error);
                const levelBadge = document.getElementById('user-approval-level');
                const deptBadge = document.getElementById('user-department');
                if (levelBadge) levelBadge.style.display = 'none';
                if (deptBadge) deptBadge.style.display = 'none';
            });
    }
</script>


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-doc-list-approvel.js"></script>