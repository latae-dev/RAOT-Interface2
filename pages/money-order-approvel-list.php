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
        <h1 class="page-title fw-semibold fs-18 mb-0">การอนุมัติใบสั่งจ่าย</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">การอนุมัติใบสั่งจ่าย</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start:: row-4 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="card-title">รายการใบสั่งจ่าย</div>
                </div>

                <!-- หัวข้อการค้นหา -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">รหัสใบสั่งจ่าย :</label>
                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสใบสั่งจ่าย">
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">เริ่มต้น :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="start-date" placeholder="กำหนด วันที่เริ่มต้น">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label class="form-label mt-2">สิ้นสุด :</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                    <input type="text" class="form-control" id="end-date" placeholder="กำหนด วันที่สิ้นสุด">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 mb-2" id="person-select-container">
                            <label class="form-label mt-2">ผู้ร้องขอ :</label>
                            <select class="form-control" data-trigger name="choices-single-person" id="choices-single-person"></select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">สถานะการอนุมัติ :</label>
                            <select class="js-example-basic-single" name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="waiting">รออนุมัติ</option>
                                <option value="finance_approve">กองการเงิน อนุมัติ</option>
                                <option value="finance_reject">กองการเงิน ไม่อนุมัติ</option>
                                <option value="audit_approve">กองตรวจจ่าย อนุมัติ</option>
                                <option value="audit_reject">กองตรวจจ่าย ไม่อนุมัติ</option>
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="text-center">
                                <button id="searchBtn" class="btn btn-primary m-1"><i class="bi bi-search me-1 fw-semibold align-middle"></i>ค้นหา</button>
                                <button id="resetBtn" class="btn btn-warning m-1"><i class="bi bi-eraser-fill me-1 fw-semibold align-middle"></i>ล้างการค้นหา</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:50px;">ลำดับ</th>
                                        <th scope="col">วันที่ดำเนินรายการ</th>
                                        <th scope="col">รหัสใบสั่งจ่าย</th>
                                        <th scope="col">รหัสคำขอเบิกเงินทดรองจ่าย</th>
                                        <th scope="col">รหัสคำขอเบิกค่าใช้จ่ายฯ</th>
                                        <th scope="col">ชื่อแบบฟอร์ม</th>
                                        <th scope="col">เลขที่เบิก/เลขที่จ่าย</th>
                                        <th scope="col">สถานะการส่งอนุมัติ</th>
                                        <th scope="col" style="width:150px;">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">

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

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/money-order-approvel-list.js"></script>

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
        window.choicesInstance = new Choices(element, {
            shouldSort: false
        });

        window.clearChoicesSelect = function() {
            window.choicesInstance.setChoiceByValue('');
            element.dispatchEvent(new Event('change'));
        }

        function changeSelectPerson() {
            if ($('#choices-single-person').val() !== '') {
                choicesInstance.setChoiceByValue('');
                element.dispatchEvent(new Event('change'));
            } else {
                choicesInstance.setChoiceByValue('waiting');
                element.dispatchEvent(new Event('change'));
            }
        }
    }
    $(document).on('change', '#choices-single-person', changeSelectPerson);
</script>