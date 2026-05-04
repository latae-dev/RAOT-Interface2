import { getAllParams } from './withdraw_parameter.js';
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';
(async () => {
    try {


        const fund_all = await getAllFund();
        // console.log(fund_all);

        const params_all = await getAllParams();
        // console.log(params_all);

        const project_all = await getAllProject();
        // console.log(project_all);

        /* ข้อมูล user */
        const data_user = params_all.current_user;
        console.log(data_user);
        document.getElementById('depart_code').value = data_user.depart_code || '';
        document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        document.getElementById('position_name').value = data_user.position_name || '';
        document.getElementById('level_name').value = data_user.level_name || '';
        document.getElementById('affiliation_name').value = data_user.depart_name || '';
        /* ข้อมูล user */

        /* หน่วยนับ */
        const unitSource = params_all.unit || [];
        if (!Array.isArray(unitSource) || unitSource.length === 0) {
            console.error('❌ ไม่พบข้อมูลหน่วยนับ (count_unit):', unitSource);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบข้อมูลหน่วยนับ กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

        const unitOptions = [
            { id: '', text: 'เลือก', disabled: true },
            ...unitSource.map(unit => ({
                id: unit.id,
                text: unit.count_unit_name,
                count_unit_code: unit.count_unit_code,
                count_unit_name: unit.count_unit_name
            }))
        ];
        /* หน่วยนับ */

        /* เงินทุน */

        const fundSource = fund_all.fund_lists || [];
        console.log(fundSource);
        if (!Array.isArray(fundSource) || fundSource.length === 0) {
            console.error('❌ ไม่พบข้อมูล:', fundSource);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบข้อมูลหน่วยนับ กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

        const fundOptions = [
            { id: '', text: 'กรุณาเลือกเงินทุน', disabled: false },
            ...fundSource.map(fund_lists => ({
                id: fund_lists.id,
                text: fund_lists.has_project == 0
                    ? `${fund_lists.fund_name} <span style="color:red;">*ไม่พบโครงการ</span>`
                    : fund_lists.fund_name,
                disabled: fund_lists.has_project == 0
            }))
        ];


        $('#fund_select').select2({
            data: fundOptions,
            escapeMarkup: function (markup) { return markup; },
            templateResult: function (data) { return data.text; },
            templateSelection: function (data) { return data.text; }
        });

        // เพิ่ม event listener สำหรับ fund_select
        $('#fund_select').on('change', async function () {
            const fund_id = $(this).val();
            const project_all = await getAllProject(fund_id);
            $('#project_select').empty();

            const projectSource = project_all.project_lists || [];
            const projectOptions = [
                { id: '', text: 'กรุณาเลือกโครงการ', disabled: true },
                ...projectSource.map(project_lists => ({
                    id: project_lists.id,
                    text: project_lists.project_name,
                }))
            ];
            $.each(projectOptions, function (index, project) {
                $('#project_select').append($('<option></option>').attr('value', project.id).text(project.text));
            });

            $('#project_select').val('').trigger('change');
        });
        /* เงินทุน */

        //select 2 project โครงการ
        $('#project_select').select2();
        const projectSource = project_all.project_lists || [];
        const projectOptions = [
            { id: '', text: 'กรุณาเลือกโครงการ', disabled: true },
            ...projectSource.map(project_lists => ({
                id: project_lists.id,
                text: project_lists.project_name,
            }))
        ];
        $.each(projectOptions, function (index, project) {
            $('#project_select').append($('<option></option>').attr('value', project.id).text(project.text));
        });
        //select 2 project โครงการ

        /* ใส่วันที่ปัจจุบันไว้ก่อน */
        // const today = new Date().toISOString().split('T')[0];
        // document.querySelector('[name="plan_start"]').value = today;
        // document.querySelector('[name="plan_end"]').value = today;
        // const now = new Date();
        // const pad = n => n.toString().padStart(2, '0');
        // const yearBE = now.getFullYear() + 543;
        // const formattedNow = `${pad(now.getDate())}-${pad(now.getMonth() + 1)}-${yearBE} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
        // document.querySelector('[name="plan_start"]').value = formattedNow;
        // document.querySelector('[name="plan_end"]').value = formattedNow;
        flatpickr("#date", {
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
        /* ใส่วันที่ปัจจุบันไว้ก่อน */

        // const checkboxes = document.querySelectorAll('.fund');
        // const otherCheckbox = document.getElementById('other');
        // const otherTextarea = document.getElementById('fund_more');

        // otherTextarea.disabled = true;

        // function handleChange(event) {
        //     const clicked = event.target;
        //     if (clicked === otherCheckbox && clicked.checked) {
        //         checkboxes.forEach(cb => cb.checked = false);
        //         otherTextarea.disabled = false;
        //         otherTextarea.focus();
        //     } else if (clicked.classList.contains('fund') && clicked.checked) {
        //         checkboxes.forEach(cb => {
        //             if (cb !== clicked) cb.checked = false;
        //         });
        //         otherCheckbox.checked = false;
        //         otherTextarea.disabled = true;
        //         otherTextarea.value = '';
        //     } else {
        //         const anyChecked = [...checkboxes].some(cb => cb.checked) || otherCheckbox.checked;
        //         if (!anyChecked) {
        //             otherTextarea.disabled = true;
        //             otherTextarea.value = '';
        //         }
        //     }
        // }

        // checkboxes.forEach(cb => cb.addEventListener('change', handleChange));
        // otherCheckbox.addEventListener('change', handleChange);

        const addBtn = document.querySelector('.addTable');
        const delBtn = document.querySelector('.delTable');
        const tableBody = document.querySelector('table tbody');
        const checkAllBox = document.querySelector('.check-all');

        checkAllBox.addEventListener('change', function () {
            const checked = this.checked;
            tableBody.querySelectorAll('.product-checkbox input.form-check-input')
                .forEach(cb => cb.checked = checked);
        });

        function createRow() {
            const newRow = document.createElement('tr');
            newRow.classList.add('product-list');
            newRow.innerHTML = `
                <input class="form-control mat_id" type="hidden">
                <td class="product-checkbox">
                    <input class="form-check-input" type="checkbox" aria-label="...">
                </td>
                <td>
                    <select class="form-control select-mat-code" style="width:100%"></select>
                </td>
                <td><input class="form-control mat-name" type="text" placeholder="รายการ"></td>
                <td><input class="form-control qty" type="number" placeholder="จำนวน" min="0" step="1" pattern="\d*"></td>
                <td>
                    <select class="form-control unit-select" style="width: 100%"></select>
                    <input type="hidden" class="unit-id">
                    <input type="hidden" class="unit-code">
                    <input type="hidden" class="unit-name">
                </td>
                <td><input class="form-control price" type="number" placeholder="ราคาหน่วย" min="0"></td>
                <td><input class="form-control days format-number-step-05" type="number" placeholder="จำนวนวัน" min="0" step="0.5" pattern="\d*"></td>
                <td><input class="form-control bg-light total" type="text" placeholder="ราคาทั้งหมด" readonly></td>
            `;
            tableBody.appendChild(newRow);

            const grouped = params_all.mat_lists.reduce((acc, item) => {
                (acc[item.group_name] ??= []).push({
                    id: item.mat_code,
                    text: `${item.mat_code} - ${item.mat_name}`,
                    mat_name: item.mat_name,
                    mat_id: item.mat_id
                });
                return acc;
            }, {});
            const dataForSelect2 = [
                { id: '', text: 'เลือกรายการ', disabled: true },
                ...Object.entries(grouped).map(([group, children]) => ({
                    text: group,
                    children
                }))
            ];

            const selectMatCode = newRow.querySelector('.select-mat-code');
            $(selectMatCode).select2({
                data: dataForSelect2,
                placeholder: 'เลือกรายการ',
                allowClear: false,
                width: 'resolve',
                templateResult: data => data.text,
                templateSelection: data => data.id || 'เลือกรายการ'
            }).val('').trigger('change');

            $(selectMatCode).on('change', function () {
                const code = $(this).val();
                const item = params_all.mat_lists.find(it => it.mat_code === code);
                newRow.querySelector('.mat-name').value = item?.mat_name || '';
                newRow.querySelector('.mat_id').value = item?.mat_id || '';
            });

            const unitSelectEl = newRow.querySelector('.unit-select');
            $(unitSelectEl).select2({
                data: unitOptions,
                placeholder: 'เลือก',
                width: 'resolve',
                allowClear: false,
                minimumResultsForSearch: 5
            }).val('').trigger('change');

            $(unitSelectEl).on('change', function () {
                const selectedId = $(this).val();
                const selected = unitOptions.find(u => u.id == selectedId);

                newRow.querySelector('.unit-id').value = selected?.id || '';
                newRow.querySelector('.unit-code').value = selected?.count_unit_code || '';
                newRow.querySelector('.unit-name').value = selected?.count_unit_name || '';
            });

            bindAutoCalc(newRow);

            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('format-number-step-05')) {
                    const value = e.target.value;

                    if (!value) {
                        e.target.classList.remove('is-invalid');
                        e.target.removeAttribute('title'); // ลบ tooltip ถ้าไม่มี error
                        return;
                    }

                    const val = parseFloat(value);
                    const decimal = (val % 1).toFixed(1);

                    if (decimal === '0.0' || decimal === '0.5') {
                        e.target.value = val.toFixed(1);
                        e.target.classList.remove('is-invalid');
                        e.target.removeAttribute('title'); // ลบ tooltip
                    } else {
                        e.target.classList.add('is-invalid');
                        e.target.setAttribute('title', 'กรอกได้เฉพาะจำนวน .0 หรือ .5 เท่านั้น'); // ใส่ tooltip
                    }
                }
            }, true);
        }

        function updateTotalAmount() {
            const rows = document.querySelectorAll('table tbody tr.product-list');
            let totalAmount = 0;
            rows.forEach(row => {
                const total = parseFloat(row.querySelector('.total').value.replace(/,/g, '')) || 0;
                totalAmount += total;
            });
            document.getElementById('total').textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function bindAutoCalc(row) {
            const qty = row.querySelector('.qty');
            const price = row.querySelector('.price');
            const days = row.querySelector('.days');
            const total = row.querySelector('.total');
            const calc = () => {
                // รับเฉพาะจำนวนเต็ม
                let q = parseInt(qty.value) || 0;
                // ถ้ากรอกทศนิยม ให้ตัดเศษทิ้งทันที
                qty.value = q;
                const p = parseFloat(price.value) || 0;
                let d = parseFloat(days?.value) || 0;
                if (d === 0) d = 1;
                let sum = q * p * d;
                // ปัดลง 2 ตำแหน่ง
                sum = Math.floor(sum * 100) / 100;
                total.value = sum.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                updateTotalAmount();
            };
            qty.addEventListener('input', calc);
            price.addEventListener('input', calc);
            days?.addEventListener('input', calc);
        }

        delBtn.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBody.querySelectorAll('.product-checkbox input:checked');
            if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
            checked.forEach(cb => cb.closest('tr')?.remove());
            updateTotalAmount();
        });

        addBtn.addEventListener('click', e => {
            e.preventDefault();
            createRow();
            updateTotalAmount();
        });

        createRow();
        updateTotalAmount();

        function getTableData() {
            const rows = document.querySelectorAll('table tbody tr.product-list');
            const data = [];

            rows.forEach(row => {
                const matCode = $(row.querySelector('.select-mat-code')).val();
                const matName = row.querySelector('.mat-name').value;
                const qty = parseFloat(row.querySelector('.qty').value) || 0;
                const days = parseFloat(row.querySelector('.days').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                const total = parseFloat(row.querySelector('.total').value.replace(/,/g, '')) || 0;
                const mat_id = parseInt(row.querySelector('.mat_id')?.value) || '';
                const unit_id = row.querySelector('.unit-id')?.value || '';
                const unit_code = row.querySelector('.unit-code')?.value || '';
                const unit_name = row.querySelector('.unit-name')?.value || '';

                data.push({
                    wrd_mat_id: mat_id,
                    wrd_mat_code: matCode,
                    wrd_mat_name: matName,
                    qty: qty,
                    count_unit_id: unit_id,
                    count_unit_code: unit_code,
                    count_unit_name: unit_name,
                    price: price,
                    days: days,
                    total: total
                });
            });

            return data;
        }

        const dayReturnCheckbox = document.getElementById('day_return');
        const moneyReceiptCheckbox = document.getElementById('money_receipt');
        dayReturnCheckbox.addEventListener('change', () => {
            if (dayReturnCheckbox.checked) {
                moneyReceiptCheckbox.checked = false;
            }
        });
        moneyReceiptCheckbox.addEventListener('change', () => {
            if (moneyReceiptCheckbox.checked) {
                dayReturnCheckbox.checked = false;
            }
        });

        function validateRequiredFields(fields = [], checkboxGroups = [], tableSelectors = [], requiredTableFields = []) {
            // ตรวจสอบฟิลด์ที่จำเป็น
            for (const fieldName of fields) {
                const el = document.querySelector(`[name="${fieldName}"]`);
                if (!el) {
                    console.log('ไม่พบฟิลด์:', fieldName);
                    return false;
                }
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (!el.checked) {
                        console.log('ยังไม่ได้เลือก:', fieldName);
                        return false;
                    }
                } else {
                    if (!el.value || el.value.trim() === '') {
                        console.log('ยังไม่ได้กรอก:', fieldName);
                        return false;
                    }
                }
            }

            // ตรวจสอบกลุ่ม checkbox
            for (const groupSelector of checkboxGroups) {
                const checkboxes = document.querySelectorAll(groupSelector);
                if (!checkboxes.length) {
                    console.log('ไม่พบ checkbox group:', groupSelector);
                    return false;
                }
                const isChecked = Array.from(checkboxes).some(cb => cb.checked);
                if (!isChecked) {
                    console.log('ยังไม่ได้เลือก checkbox group:', groupSelector);
                    return false;
                }
            }

            // ตรวจสอบข้อมูลในตาราง
            for (const selector of tableSelectors) {
                const rows = document.querySelectorAll(selector);
                if (!rows.length) {
                    console.log('ไม่พบข้อมูลในตาราง:', selector);
                    return false;
                }

                for (const row of rows) {
                    for (const fieldSelector of requiredTableFields) {
                        const el = row.querySelector(fieldSelector);
                        if (!el) {
                            console.log('ไม่พบฟิลด์ในตาราง:', fieldSelector);
                            return false;
                        }

                        let value = '';
                        if ($(el).hasClass('select2-hidden-accessible')) {
                            value = $(el).val();
                        } else if (el.tagName === 'SELECT') {
                            value = el.value;
                        } else {
                            value = el.value;
                        }

                        if (typeof value === 'string') {
                            value = value.trim();
                        }

                        // ตรวจสอบค่าว่าง
                        if (value === '') {
                            let fieldName = '';
                            if (fieldSelector === '.mat_id') fieldName = 'รหัสรายการ';
                            else if (fieldSelector === '.select-mat-code') fieldName = 'รหัสรายการ';
                            else if (fieldSelector === '.mat-name') fieldName = 'รายการ';
                            else if (fieldSelector === '.qty') fieldName = 'จำนวน';
                            else if (fieldSelector === '.unit-id') fieldName = 'หน่วย';
                            else if (fieldSelector === '.price') fieldName = 'ราคาหน่วย';

                            Swal.fire({
                                title: "ข้อมูลไม่ครบถ้วน",
                                text: `กรุณากรอก${fieldName}ให้ครบทุกรายการ`,
                                icon: "warning"
                            });
                            return false;
                        }

                        // ตรวจสอบจำนวนและราคา
                        if ((fieldSelector.includes('qty') || fieldSelector.includes('price')) &&
                            (isNaN(parseFloat(value)) || parseFloat(value) <= 0)) {
                            Swal.fire({
                                title: "ข้อมูลไม่ถูกต้อง",
                                text: "กรุณากรอกจำนวนและราคาให้ถูกต้อง",
                                icon: "warning"
                            });
                            return false;
                        }
                    }
                }
            }

            return true;
        }

        function validateDays() {
            const groupRows = document.querySelectorAll('tr.product-list');

            for (const row of groupRows) {
                const inputEl = row.querySelector('.days');

                // ไม่มี field days → ข้าม
                if (!inputEl) continue;

                const value = inputEl.value?.trim();

                // ไม่กรอก / เป็น 0 → ไม่ต้องเช็ค
                if (!value || parseFloat(value) === 0) continue;

                const val = parseFloat(value);
                const decimal = (val % 1).toFixed(1);

                if (decimal !== '0.0' && decimal !== '0.5') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากรอกจำนวน (วัน) ให้ถูกต้อง'
                    }).then(() => {
                        inputEl.focus();
                    });
                    return false; // ❌ ไม่ผ่าน
                }
            }

            return true; // ✅ ผ่านทั้งหมด
        }


        function validateDates() {
            const startDate = new Date(document.querySelector('[name="plan_start"]').value);
            const endDate = new Date(document.querySelector('[name="plan_end"]').value);

            if (startDate > endDate) {
                Swal.fire({
                    title: "วันที่ไม่ถูกต้อง",
                    text: "วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด",
                    icon: "warning"
                });
                return false;
            }
            return true;
        }

        function validateTotalAmount() {
            const rows = document.querySelectorAll('table tbody tr.product-list');
            let totalAmount = 0;

            rows.forEach(row => {
                const total = parseFloat(row.querySelector('.total').value) || 0;
                totalAmount += total;
            });

            /* if (totalAmount > 100000) {
                Swal.fire({
                    title: "จำนวนเงินเกินงบประมาณ",
                    text: "จำนวนเงินรวมทั้งหมดต้องไม่เกิน 100,000 บาท",
                    icon: "warning"
                });
                return false;
            } */
            return true;
        }

        function validateDateFields() {
            const planStart = document.querySelector('[name="plan_start"]').value;
            const planEnd = document.querySelector('[name="plan_end"]').value;
            if (!planStart || !planEnd) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณากรอกวันที่เริ่มต้นและวันที่สิ้นสุด",
                    icon: "warning"
                });
                return false;
            }
            return true;
        }

        // function validateFundSelection() {
        // if (otherCheckbox.checked) {
        //     const fundValue = otherTextarea.value.trim();
        //     if (!fundValue) {
        //         Swal.fire({
        //             title: "ข้อมูลไม่ครบถ้วน",
        //             text: "กรุณากรอกข้อมูลเงินทุนอื่นๆ",
        //             icon: "warning"
        //         });
        //         otherTextarea.focus();
        //         return false;
        //     }
        // } else {
        //     const selectedFund = document.querySelector('.fund:checked');

        //     if (!selectedFund) {
        //         Swal.fire({
        //             title: "ข้อมูลไม่ครบถ้วน",
        //             text: "กรุณาเลือกเงินทุน",
        //             icon: "warning"
        //         });
        //         return false;
        //     }
        // }

        // return true;
        // }

        function validateDayCounting() {
            if (!dayReturnCheckbox.checked && !moneyReceiptCheckbox.checked) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณาเลือกการนับวัน (นับจากวันกลับมาถึง หรือ นับแต่วันที่ได้รับเงิน)",
                    icon: "warning"
                });
                return false;
            }
            return true;
        }

        function validateFund() {
            const fund = document.getElementById('fund_select').value.trim();
            if (!fund) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณาเลือกเงินทุน",
                    icon: "warning"
                });
                return false;
            }
            return fund;
        }

        function validateProject() {
            const project = document.getElementById('project_select').value.trim();
            if (!project) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณาเลือกโครงการ",
                    icon: "warning"
                });
                return false;
            }
            return project;
        }

        function validateNote() {
            const note = document.getElementById('note').value.trim();
            if (!note) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณากรอกเหตุผลของการขอยืมเงินทดรอง",
                    icon: "warning"
                });
                return false;
            }
            return note;
        }

        function getFundValue() {
            if (otherCheckbox.checked) {
                return otherTextarea.value.trim();
            }
            return document.querySelector('.fund:checked').value;
        }

        function getFundNewValue() {
            var fund_id = $("#fund_select").val();
            var fun_name = $("#fund_select option:selected").text();
            return fund_id;
        }

        function getProjectNewValue() {
            var project_id = $("#project_select").val();
            var project_name = $("#project_select option:selected").text();
            return project_id;
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

                // ตรวจสอบข้อมูลตามลำดับ
                if (!validateDateFields()) return;
                // if (!validateFundSelection()) return;
                if (!validateDayCounting()) return;

                const fund = validateFund();
                if (!fund) return;
                const project = validateProject();
                if (!project) return;
                const note = validateNote();
                if (!note) return;

                const requiredFields = ['note'];
                const checkboxGroups = ['.day_return, .money_receipt'];
                const tableData = ['table tbody tr.product-list'];
                const requiredTableFields = ['.mat_id', '.select-mat-code', '.mat-name', '.qty', '.unit-id', '.price'];

                const valid = validateRequiredFields(requiredFields, checkboxGroups, tableData, requiredTableFields);
                if (!valid) return;

                if (!validateDays()) return;
                if (!validateDates()) return;
                if (!validateTotalAmount()) return;

                const data_sent = {
                    main_data: {
                        plan_number: '',
                        plan_requ_level: data_user.level_name || '',
                        plan_requ_all: '[]',
                        plan_form_name: document.getElementById('plan_form_name').value,
                        plan_start: document.querySelector('[name="plan_start"]').value,
                        plan_end: document.querySelector('[name="plan_end"]').value,
                        depart_code: document.getElementById('depart_code').value,
                        full_name: document.getElementById('full_name').value,
                        position_name: document.getElementById('position_name').value,
                        level_name: document.getElementById('level_name').value,
                        affiliation_name: document.getElementById('affiliation_name').value,
                        //fund: getFundNewValue(),
                        fund: $("#fund_select option:selected").text(),
                        fund_id: getFundNewValue(),
                        project: $("#project_select option:selected").text(),
                        project_id: getProjectNewValue(),
                        st_br: 'waiting',
                        st_br_date: null,
                        st_br_user_code: '',
                        st_pv: 'waiting',
                        st_pv_date: null,
                        st_pv_user_code: '',
                        st_ar: 'waiting',
                        st_ar_date: null,
                        st_ar_user_code: '',
                        st_hf: 'waiting',
                        st_hf_date: null,
                        st_hf_user_code: '',
                        user_request_code: '',
                        draft: false,
                        day_return: dayReturnCheckbox.checked,
                        money_receipt: moneyReceiptCheckbox.checked,
                        note: note,
                        req_user_code: data_user.user_code,
                        province_name: data_user.province_name
                    },
                    child_data: getTableData()
                };

                window.showLoading();

                try {
                    const response = await fetch('../controllers/withdraws/wrd_controller.php?action=create_plan', {
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
                            text: "เลขที่เอกสารคือ: " + result.data,
                            icon: "success"
                        }).then(() => {
                            window.location.href = 'withdraw-money-list.php';
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