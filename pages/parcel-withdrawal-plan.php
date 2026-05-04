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
            <h1 class="page-title fw-semibold fs-18 mb-0">แผนการเบิกพัสดุแบบพิมพ์</h1>
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
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แผนการเบิกพัสดุแบบพิมพ์</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Start::row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">แบบฟอร์มแผนการเบิกพัสดุแบบพิมพ์</div>
                </div>

                <!-- แบบฟอร์มกรอก -->
                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label">แบบฟอร์ม :</label>
                            <input type="text" class="form-control" id="input-label" placeholder="ใบเบิกพัสดุแบบพิมพ์" disabled>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label">เริ่มต้น :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input name="plan_start" type="text" class="form-control" id="date" placeholder="กำหนด วันเริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label">สิ้นสุด :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input name="plan_end" type="text" class="form-control" id="date" placeholder="กำหนด วันสิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                            <input name="depart_code" type="text" class="form-control" id="input-label11" disabled>
                        </div>
                    </div>
                </div>

                <!-- จำนวนพัสดุ -->
                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered" id="table_data">
                                    <thead class="text-center">
                                        <tr>
                                            <th scope="col" style="width:50px;">
                                                <input class="form-check-input" type="checkbox" id="all-products" value="" aria-label="...">
                                            </th>
                                            <th scope="col" style="width: 20%;">รหัสวัสดุ</th>
                                            <th scope="col" style="width: 20%;">รายการ</th>
                                            <th scope="col" style="width: 10%;">หน่วยนับ</th>
                                            <th scope="col">ไตรมาส 1</th>
                                            <th scope="col">ไตรมาส 2</th>
                                            <th scope="col">ไตรมาส 3</th>
                                            <th scope="col">ไตรมาส 4</th>
                                            <th scope="col">จำนวนทั้งหมด</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center" id="table-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <div class="col-xl-12">
                        <div class="custom-card">

                            <ul class="nav nav-pills justify-content-start nav-style-3 mb-3 fs-15" role="tablist">
                                <!-- <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#material-right" aria-selected="true">วัสดุ</a>
                                </li> -->
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#quantity-date-right" aria-selected="true">ปริมาณ/วันที่</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#note-right" aria-selected="true">หมายเหตุ</a>
                                </li>
                            </ul>
                            <div class="tab-content">

                                <!-- <div class="tab-pane show text-muted active" id="material-right" role="tabpanel">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm mb-3">
                                                <span class="input-group-text" id="basic-addon3">วัสดุ</span>
                                                <input type="text" class="form-control mat_name" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm mb-3">
                                                <span class="input-group-text" id="basic-addon3">กลุ่มวัสดุ</span>
                                                <input type="text" class="form-control mat_code" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text" id="basic-addon3">วัสดุของผู้จัดหา</span>
                                                <input type="text" class="form-control count_unit_name" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="tab-pane show text-muted active" id="quantity-date-right" role="tabpanel">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">ปริมาณ</span>
                                                    <input name="net_total" type="text" class="form-control numOnly" id="basic-url" value="0" aria-describedby="basic-addon3">
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">ปริมาณที่สั่ง</span>
                                                    <input name="quantity_order" type="text" class="form-control numOnly" id="basic-url" value="0" aria-describedby="basic-addon3">
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">ปริมาณคงค้าง</span>
                                                    <input name="quantity_outst" type="text" class="form-control numOnly" id="basic-url" value="0" aria-describedby="basic-addon3">
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="form-check mb-2">
                                                    <input name="at_close" class="form-check-input" type="checkbox" value="" id="492">
                                                    <label class="form-check-label" for="c-1">ที่ปิด</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="form-check mb-3">
                                                    <input name="const_id" class="form-check-input" type="checkbox" value="" id="492">
                                                    <label class="form-check-label" for="c-1">ID คงที่</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">วันที่ส่งมอบ</span>
                                                    <input name="date_deli" type="text" class="form-control" id="date">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i></div>
                                                    <div id="validationFeedback_date_deli" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">วันที่ขอ</span>
                                                    <input name="date_requ" type="text" class="form-control" id="date">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i></div>
                                                    <div id="validationFeedback_date_requ" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">วันที่อนุมัติ</span>
                                                    <input name="date_approv" type="text" class="form-control" id="date">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i></div>
                                                    <div id="validationFeedback_date_approv" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text" id="basic-addon3">เวลาส่งตามแผน</span>
                                                    <input name="time_deli_plan" type="text" class="form-control flatpickr-input" id="timepickr1" placeholder="กรุณากรอก เวลาส่งตามแผน" readonly="readonly">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i></div>
                                                    <div id="validationFeedback_time_deli_plan" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text" id="basic-addon3">เวลาในการรับส่ง</span>
                                                    <input name="time_pick_plan" type="text" class="form-control flatpickr-input" id="timepickr1" placeholder="กรุณากรอก เวลาในการรับส่ง" readonly="readonly">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i></div>
                                                    <div id="validationFeedback_time_pick_plan" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted" id="note-right" role="tabpanel">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm mb-3">
                                                <span class="input-group-text" id="basic-addon3">หมายเหตุที่ 1</span>
                                                <input name="note_1" type="text" class="form-control" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm mb-3">
                                                <span class="input-group-text" id="basic-addon3">หมายเหตุที่ 2</span>
                                                <input name="note_2" type="text" class="form-control" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text" id="basic-addon3">หมายเหตุที่ 3</span>
                                                <input name="note_3" type="text" class="form-control" id="basic-url" aria-describedby="basic-addon3">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="parcel-withdrawal-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                    <button id="save-draft" class="btn btn-secondary btn-wave waves-effect waves-light m-1">
                        <i class='bx bxs-edit'></i> บันทึกแบบร่าง
                    </button>

                    <button id="save-master" class="btn btn-primary btn-wave waves-effect waves-light m-1">
                        <i class='bx bxs-save'></i> ขออนุมัติ
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--End::row -->

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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-withdrawal-plan.js"></script>