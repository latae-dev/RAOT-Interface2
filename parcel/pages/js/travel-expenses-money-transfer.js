import { getAllParams } from './withdraw_parameter.js';
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';
(async () => {
    // ฟังก์ชันสำหรับแสดงปุ่ม action ใน section ที่ถูกต้อง
    function renderActionButtons(targetCardBody) {
        // ลบปุ่มเดิมถ้ามี
        const oldAction = document.getElementById('action-buttons-section');
        if (oldAction) oldAction.remove();
        // สร้าง div ปุ่มใหม่
        const div = document.createElement('div');
        div.className = 'px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end';
        div.id = 'action-buttons-section';
        div.innerHTML = `
                <a href="withdraw-money-list.php" class="btn btn-warning m-1">
                    <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                </a>
                <button id="btn-create" type="button" class="btn btn-primary m-1">
                    <i class="bi bi-save"></i> ขออนุมัติ
                </button>
            `;
        targetCardBody.appendChild(div);
        // รีผูก event
        const btnSubmit = document.getElementById('btn-create');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', createData);
        }
    }
    try {


        const fund_all = await getAllFund();
        // console.log(fund_all);

        const params_all = await getAllParams();
        // console.log(params_all);

        const project_all = await getAllProject();
        // console.log(project_all);

        /* ข้อมูล user */
        const data_user = params_all.current_user;
        // document.getElementById('depart_code').value = data_user.depart_code || '';
        document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        document.getElementById('position_name').value = data_user.position_name || '';
        document.getElementById('level_name').value = data_user.level_name || '';
        document.getElementById('affiliation_name').value = data_user.depart_name || '';
        document.getElementById('transfer_user_id').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        const urlParams = new URLSearchParams(window.location.search);
        const expensesIdFromUrl = urlParams.get('id');
        document.getElementById('expenses_id').value = expensesIdFromUrl || data_user.depart_name || '';
        /* ข้อมูล user */

        flatpickr("#transfer_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            altInput: true,
            altFormat: "d-m-Y H:i",
            defaultDate: new Date(),
            time_24hr: true,
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
            formatDate: (date, format, locale) => {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const yearBE = date.getFullYear() + 543;
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}-${month}-${yearBE} ${hours}:${minutes}`;
            },
            parseDate: (datestr, format) => {
                const [d, m, y, h = "00", i = "00"] = datestr
                    .replace(/[:\s]/g, "-")
                    .split("-");
                return new Date(+y - 543, +m - 1, +d, +h, +i);
            },
            onReady(selectedDates, dateStr, instance) {
                const updateThaiYearCalendar = () => {
                    if (instance.currentYearElement) {
                        let year = parseInt(instance.currentYearElement.value);
                        if (!isNaN(year) && year < 2400) {
                            instance.currentYearElement.value = year + 543;
                        }
                    }
                };
                updateThaiYearCalendar();
                instance.config.onMonthChange.push(updateThaiYearCalendar);
                instance.config.onYearChange.push(updateThaiYearCalendar);
                instance.config.onOpen = [updateThaiYearCalendar];
                instance.config.onValueUpdate = [updateThaiYearCalendar]; // เพิ่มบรรทัดนี้
            }
        });

        try {
            const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=get_transfer&id=${expensesIdFromUrl}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (!response.ok) {
                throw new Error('ไม่สามารถดึงข้อมูลได้');
            }

            const data = await response.json();

            if (data.data.length === 0) {
                $('#history-section').hide();
                $('#transfer-section .custom-card').append(`
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                        <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <!-- ปุ่มนี้คือส่งฟอร์มจริง -->
                        <button id="btn-create" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> บันทึก
                        </button>
                    </div>
                `);
            } else {
                let grandTotal = 0;
                if (data.data && Array.isArray(data.data)) {
                    const tableBody = document.querySelector('table tbody');
                    data.data.forEach((item, key) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="text-center">${key + 1}</td>
                        <td class="text-center">${item.transfer_number}</td>
                        <td class="text-center">${formatThaiDatetime(item.transfer_date)}</td>
                        <td class="text-center">${item.transfer_user_id || ''}</td>
                        <td class="text-center">${item.full_name || ''}</td>
                        <td class="text-center">${formatThaiDatetime(item.created_at)}</td>
                    `;
                        tableBody.appendChild(row);
                        grandTotal += Number(item.total || 0);
                    });

                    // แสดงยอดรวมในช่อง total
                    const totalElement = document.getElementById('total');
                    if (totalElement) {
                        totalElement.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                }
                $('#history-section').show();
                $('#history-section .custom-card').append(`
                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                        <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <!-- ปุ่มนี้คือส่งฟอร์มจริง -->
                        <button id="btn-create" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> บันทึก
                        </button>
                    </div>
                `);
            }

        } catch (error) {
            console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่สามารถดึงข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

        function getMainFormData() {
            const data = {};
            document.querySelectorAll('input[name], textarea[name], select[name]').forEach(el => {
                const name = el.name;
                if (!name) return;
                if (el.type === 'checkbox') {
                    data[name] = el.checked;
                } else if (el.type === 'radio') {
                    if (el.checked) data[name] = el.value;
                    // ถ้า radio ยังไม่ checked จะไม่ set ค่า
                } else {
                    data[name] = el.value;
                }
            });
            return data;
        }

        function validateRequiredFields(fields = [], checkboxGroups = [], tableSelectors = [], requiredTableFields = []) {
            // ตรวจสอบฟิลด์ที่จำเป็น
            for (const fieldName of fields) {
                const el = document.querySelector(`[name="${fieldName}"]`);
                let fieldText = fieldName;
                const label = document.querySelector(`label[for="${fieldName}"]`);
                if (label && label.getAttribute('data-text')) {
                    fieldText = label.getAttribute('data-text').trim();
                }
                if (!el) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: `ไม่พบฟิลด์: ${fieldText}`
                    }).then(() => {
                        // ไม่พบ input ให้ focus ไม่ได้
                    });
                    return false;
                }
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (!el.checked) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ข้อมูลไม่ครบถ้วน',
                            text: `กรุณาเลือก ${fieldText}`
                        }).then(() => {
                            el.focus();
                        });
                        return false;
                    }
                } else {
                    if (!el.value || el.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ข้อมูลไม่ครบถ้วน',
                            text: `กรุณากรอก ${fieldText}`
                        }).then(() => {
                            el.focus();
                        });
                        return false;
                    }
                }
            }

            return true;
        }

        const btnSubmit = document.getElementById('btn-create');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', createData);
        }

        async function createData() {
            try {
                // Disable submit button
                const btnSubmit = document.getElementById('btn-create');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                }
                const requiredFields = ['transfer_number', 'transfer_date', 'transfer_user_id'];

                const valid = validateRequiredFields(requiredFields);
                if (!valid) return;

                const data_sent = {
                    main_data: getMainFormData(),
                };

                window.showLoading();

                try {
                    const response = await fetch('../controllers/withdraws/wrd_expenses_controller.php?action=save_transfer', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify(data_sent)
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            title: "บันทึกข้อมูลสำเร็จ",
                            text: "ระบบทำการบันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success"
                        }).then(() => {
                            window.location.href = 'travel-expenses-money-list.php';
                        });
                    } else {
                        throw new Error(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                    }
                } catch (error) {
                    throw error;
                }

            } catch (error) {
                console.error('เกิดข้อผิดพลาด:', error);
                Swal.fire({
                    title: "เกิดข้อผิดพลาด",
                    text: error.message || "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
                    icon: "error"
                });
            } finally {
                // Enable submit button
                const btnSubmit = document.getElementById('btn-create');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                }
                window.hideLoading();
            }
        }


    } catch (err) {
        console.error('เกิดข้อผิดพลาด:', err);
        alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
    }


})();
function formatThaiDatetime(datetimeStr) {
    if (!datetimeStr) return '';
    const dateObj = new Date(datetimeStr);
    if (isNaN(dateObj)) return '';
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const year = dateObj.getFullYear() + 543;
    const hours = String(dateObj.getHours()).padStart(2, '0');
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}