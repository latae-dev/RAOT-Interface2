<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- DATA TABLES CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
        
        <!-- FLATPICKER CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">

        <!-- SELECT2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

                <!-- FILEPOND CSS -->
                <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <div>
                            <h1 class="page-title fw-semibold fs-18 mb-0">รายงานสรุปการเบิกพัสดุแบบพิมม์</h1>
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
                                    <li class="breadcrumb-item fw-semibold active" aria-current="page">รายงานสรุปการเบิกพัสดุแบบพิมม์</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start:: row-4 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">รายงานสรุปการเบิกพัสดุแบบพิมม์</div>
                                </div>
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">รายการพัสดุแบบพิมม์ :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสการเบิกพัสดุแบบพิมพ์">
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
                                            <label class="form-label mt-2">จังหวัด :</label>
                                            <select name="province_id" id="province_id" class="form-control">
                                                <option value="">ทั้งหมด</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="text-center">
                                                <button  id="searchBtn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body border-bottom">
                                    <div class="row">
                                        <div class="table-responsive mb-2">
                                            <table class="table text-nowrap table-bordered">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th scope="col"  style="width:50px;">ลำดับ</th>
                                                        <th scope="col">รหัสพัสดุ</th>
                                                        <th scope="col">รายการพัสดุ</th>
                                                        <th scope="col">จำนวนที่เบิก</th>
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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-withdrawal-summary-report.js"></script>