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
<style>
    a.btn-success-custom,
    a.btn-success-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #e7fdf1;
        color: #50C793;
    }

    a.btn-success-c2-custom,
    a.btn-success-c2-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #50C793;
        color: white;
    }

    a.btn-primary-custom,
    a.btn-primary-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #edefff;
        color: #7e96fc;
    }

    a.btn-primary-c2-custom,
    a.btn-primary-c2-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #7e96fc;
        color: white;
    }

    a.btn-info-custom,
    a.btn-info-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #e7feff;
        color: #00b8d4;
    }

    a.btn-info-c2-custom,
    a.btn-info-c2-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #0ce7fa;
        color: white;
    }

    a.btn-secondary-custom,
    a.btn-secondary-custom:hover {
        display: inline-block;
        padding: 0.25em 0.75em;
        text-transform: capitalize;
        border-radius: 0.35rem;
        background-color: #e1e4ee;
        color: #a2aab7;
    }
</style>
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
                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">วันที่เริ่มต้น :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="start-date" placeholder="กำหนด วันที่เริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">วันที่สิ้นสุด :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="end-date" placeholder="กำหนด วันที่สิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="waiting">รออนุมัติ/รอการตรวจสอบ</option>
                                <option value="audit_approve">ผ่านการตรวจสอบแล้ว</option>
                                <option value="audit_reject">ไม่ผ่านการตรวจสอบ</option>
                                <option value="finance_approve">อนุมัติ</option>
                                <option value="finance_reject">ไม่อนุมัติ</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <a class="btn btn-success m-1" href="javascript:void(0)" onclick="travelExpenses()"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>ยื่นคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน</a>
                                <a class="btn btn-success m-1" href="javascript:void(0)" onclick="travelExpensesOutside()"><i class="ri-add-fill me-1 fw-semibold align-middle"></i>ยื่นคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน (บุคคลภายนอก)</a>
                                <button id="searchBtn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                <button id="resetBtn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
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
                                        <th scope="col">รหัสคำขอเบิกค่าใช้จ่ายฯ</th>
                                        <th scope="col">รหัสคำขอเบิกเงินทดรองจ่าย</th>
                                        <th scope="col">ชื่อแบบฟอร์ม</th>
                                        <th scope="col">เลขที่คำสั่ง/บันทึก</th>
                                        <th scope="col">ลงวันที่</th>
                                        <th scope="col">สร้างใบสั่งจ่าย</th>
                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>

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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/travel-expenses-money-list.js"></script>

<script>
    function convertToBuddhistEra(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear() + 543;
        const month = date.getMonth() + 1;
        const day = date.getDate();
        const hours = date.getHours();
        const minutes = date.getMinutes();

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }
</script>
<script>
    const startDatePicker = flatpickr("#start-date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                longhand: [
                    'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ',
                    'พฤหัสบดี', 'ศุกร์', 'เสาร์'
                ],
            },
            months: {
                shorthand: [
                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ],
                longhand: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
            },
        },
        onReady(selectedDates, dateStr, instance) {
            const updateThaiYearInput = () => {
                const altInput = instance.altInput;
                if (altInput && instance.selectedDates.length > 0) {
                    const date = instance.selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    altInput.value = `${day}-${month}-${year}`;
                }
            };

            const updateThaiYearCalendar = () => {
                if (instance.currentYearElement) {
                    let year = parseInt(instance.currentYearElement.value);
                    if (!isNaN(year) && year < 2400) {
                        instance.currentYearElement.value = year + 543;
                    }
                }
            };

            updateThaiYearInput();
            updateThaiYearCalendar();

            instance.config.onChange.push(updateThaiYearInput);
            instance.config.onMonthChange.push(updateThaiYearCalendar);
            instance.config.onYearChange.push(updateThaiYearCalendar);
            instance.config.onOpen = [updateThaiYearCalendar];
        }
    });
    const endDatePicker = flatpickr("#end-date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                longhand: [
                    'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ',
                    'พฤหัสบดี', 'ศุกร์', 'เสาร์'
                ],
            },
            months: {
                shorthand: [
                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ],
                longhand: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
            },
        },
        onReady(selectedDates, dateStr, instance) {
            const updateThaiYearInput = () => {
                const altInput = instance.altInput;
                if (altInput && instance.selectedDates.length > 0) {
                    const date = instance.selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    altInput.value = `${day}-${month}-${year}`;
                }
            };

            const updateThaiYearCalendar = () => {
                if (instance.currentYearElement) {
                    let year = parseInt(instance.currentYearElement.value);
                    if (!isNaN(year) && year < 2400) {
                        instance.currentYearElement.value = year + 543;
                    }
                }
            };

            updateThaiYearInput();
            updateThaiYearCalendar();

            instance.config.onChange.push(updateThaiYearInput);
            instance.config.onMonthChange.push(updateThaiYearCalendar);
            instance.config.onYearChange.push(updateThaiYearCalendar);
            instance.config.onOpen = [updateThaiYearCalendar];
        }
    });
    const element = document.getElementById('choices-single-groups');
    if (element) {
        const choicesInstance = new Choices(element, {
            shouldSort: false
        });

        function clearChoicesSelect() {
            choicesInstance.setChoiceByValue('');
            element.dispatchEvent(new Event('change'));
        }
    }

    function travelExpenses() {
        Swal.fire({
            title: `<h5>สร้างใบเบิกค่าใช้จ่ายฯ</h5>`,
            html: `<div class="row">
                        <div class="col-4">
                            <a href="travel-expenses-money-create.php?type=1"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี คนเดียว
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="travel-expenses-money-create.php?type=2"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางเหมือนกัน
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="travel-expenses-money-create-multi.php?type=3"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางแตกต่างกัน
                            </a>
                        </div>
                    </div> `,
            width: 800,
            showCancelButton: false,
            showConfirmButton: false,
        }).then((result) => {});
    }

    function travelExpensesOutside() {
        Swal.fire({
            title: `<h5>สร้างใบเบิกค่าใช้จ่ายฯ (บุคคลภายนอก)</h5>`,
            html: `<div class="row">
                        <div class="col-4">
                            <a href="travel-expenses-money-create-outside.php?type=1"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี คนเดียว
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="travel-expenses-money-create-outside.php?type=2"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางเหมือนกัน
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="travel-expenses-money-create-outside-multi.php?type=3"
                                class="btn btn-primary btn-equal"
                                style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางแตกต่างกัน
                            </a>
                        </div>
                    </div> `,
            width: 800,
            showCancelButton: false,
            showConfirmButton: false,
        }).then((result) => {});
    }
</script>