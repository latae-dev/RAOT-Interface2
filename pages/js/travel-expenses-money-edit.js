import { getAllParams } from './withdraw_parameter.js';
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';

(async () => {
    // ตัวแปรสำหรับเก็บข้อมูลหลักเพื่อใช้ restore/switch ฟอร์ม
    let m = null, mp = null, cd = null, cdp = null, tgd = null;
    try {
        // ดึงข้อมูลจาก URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const fund_all = await getAllFund();
        const project_all = await getAllProject();
        const expensesId = urlParams.get('id');
        let projectDefaultId = null;

        if (!expensesId) {
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบรหัสข้อมูลที่ต้องการดู",
                icon: "error"
            }).then(() => {
                window.location.href = 'withdraw-money-list.php';
            });
            return;
        }

        const params_all = await getAllParams();
        const data_user = params_all.current_user;
        // document.getElementById('depart_code').value = data_user.depart_code || '';
        // document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        // document.getElementById('position_name').value = data_user.position_name || '';
        // document.getElementById('level_name').value = data_user.level_name || '';
        // document.getElementById('affiliation_name').value = data_user.branch_name || '';
        // console.log(params_all);

        /* กองทุน */
        const fundSource = fund_all.fund_lists || [];
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
            { id: '', text: 'กรุณาเลือกกองทุน', disabled: false },
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

            if (projectDefaultId) {
                $('#project_select').val(projectDefaultId).trigger('change');
                projectDefaultId = null;
            } else {
                $('#project_select').val('').trigger('change');
            }
        });
        /* กองทุน */
        /* โครงการ */
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
        /* โครงการ */

        const addBtn = document.querySelector('.addTable-group-people');
        const delBtn = document.querySelector('.delTable-group-people');
        const tableBody = document.querySelector('#ifYes table tbody');

        const checkAllBox = document.querySelector('.check-all');
        if (checkAllBox) {
            checkAllBox.addEventListener('change', function () {
                const checked = this.checked;
                tableBody.querySelectorAll('.group-checkbox input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }

        function updateRowNumbers() {
            tableBody.querySelectorAll('tr.group-people-list').forEach((row, idx) => {
                const numberInput = row.children[1].querySelector('input');
                if (numberInput) {
                    numberInput.value = idx + 1;
                }
            });
        }

        function updateGroupPeopleCount() {
            const groupPeopleCountInput = document.getElementById('group_people_count');
            const yesCheck = document.getElementById('yesCheck');
            const noCheck = document.getElementById('noCheck');
            const tableBody = document.querySelector('#ifYes table tbody');

            if (!groupPeopleCountInput) return;

            if (noCheck && noCheck.checked) {
                groupPeopleCountInput.value = 1;
            } else if (yesCheck && yesCheck.checked) {
                if (tableBody) {
                    const count = tableBody.querySelectorAll('tr.group-people-list').length;
                    groupPeopleCountInput.value = count;
                } else {
                    groupPeopleCountInput.value = '';
                }
            } else {
                groupPeopleCountInput.value = '';
            }
        }

        function yesnoCheck() {
            const groupPeopleCountInput = document.getElementById('group_people_count');
            const yesCheck = document.getElementById('yesCheck');
            const noCheck = document.getElementById('noCheck');
            const tableBody = document.querySelector('#ifYes table tbody');
            const ifYesDiv = document.getElementById('ifYes');
            const onePersonElements = document.querySelectorAll('.one-person');

            if (!groupPeopleCountInput) return;
            if (noCheck && noCheck.checked) {
                groupPeopleCountInput.value = 1;
                if (tableBody) {
                    tableBody.querySelectorAll('tr.group-people-list').forEach(row => row.remove());
                }
                if (ifYesDiv) ifYesDiv.style.display = "none";
                onePersonElements.forEach(el => {
                    el.style.display = "";
                    el.querySelectorAll('input, textarea').forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    $('#expenses_allowance').val(m.expenses_allowance ? parseFloat(m.expenses_allowance).toFixed(2) : '');
                    $('#expenses_allowance_days').val(m.expenses_allowance_days ? parseFloat(m.expenses_allowance_days).toFixed(1) : '');
                    $('#expenses_allowance_total').val(m.expenses_allowance_total ? parseFloat(m.expenses_allowance_total).toFixed(2) : '');
                    $('#expenses_accommodation').val(m.expenses_accommodation ? parseFloat(m.expenses_accommodation).toFixed(2) : '');
                    $('#expenses_accommodation_days').val(m.expenses_accommodation_days ? parseFloat(m.expenses_accommodation_days).toFixed(1) : '');
                    $('#expenses_accommodation_total').val(m.expenses_accommodation_total ? parseFloat(m.expenses_accommodation_total).toFixed(2) : '');
                    $('#expenses_transportation').val(m.expenses_transportation ? parseFloat(m.expenses_transportation).toFixed(2) : '');
                    $('#expenses_transportation_total').val(m.expenses_transportation_total ? parseFloat(m.expenses_transportation_total).toFixed(2) : '');
                    $('#expenses_transportation_remark').val(m.expenses_transportation_remark || '');
                    $('#expenses_moving').val(m.expenses_moving ? parseFloat(m.expenses_moving).toFixed(2) : '');
                    $('#expenses_moving_total').val(m.expenses_moving_total ? parseFloat(m.expenses_moving_total).toFixed(2) : '');
                    $('#expenses_moving_km').val(m.expenses_moving_km ? parseFloat(m.expenses_moving_km).toFixed(2) : '');
                    $('#expenses_other').val(m.expenses_other ? parseFloat(m.expenses_other).toFixed(2) : '');
                    $('#expenses_other_total').val(m.expenses_other_total ? parseFloat(m.expenses_other_total).toFixed(2) : '');
                    $('#expenses_food_per_meal').val(m.expenses_food_per_meal ? parseFloat(m.expenses_food_per_meal).toFixed(2) : '');
                    $('#expenses_food_meals').val(m.expenses_food_meals ? parseFloat(m.expenses_food_meals).toFixed(1) : '');
                    $('#expenses_food_total').val(m.expenses_food_total ? parseFloat(m.expenses_food_total).toFixed(2) : '');
                });
                updateTotalAmount();
                calcExpensesTotal();
            } else if (yesCheck && yesCheck.checked) {
                if (tableBody) {
                    tableBody.querySelectorAll('tr.group-people-list').forEach(row => row.remove());
                    if (Array.isArray(tgd) && tgd.length > 0) {
                        tgd.forEach(item => {
                            createRow();
                            const lastRow = tableBody.lastElementChild;
                            lastRow.querySelector('.group_name').value = item.fullname || '';
                            lastRow.querySelector('.group_position').value = item.position || '';
                            lastRow.querySelector('.group_allowance').value = (item.allowance ? parseFloat(item.allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_days').value = (item.allowance_days ? parseFloat(item.allowance_days).toFixed(1) : '');
                            lastRow.querySelector('.group_total_allowance').value = (item.total_allowance ? parseFloat(item.total_allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation').value = (item.accommodation ? parseFloat(item.accommodation).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation_days').value = (item.accommodation_days ? parseFloat(item.accommodation_days).toFixed(1) : '');
                            lastRow.querySelector('.group_accommodation_total').value = (item.accommodation_total ? parseFloat(item.accommodation_total).toFixed(2) : '');
                            lastRow.querySelector('.group_transport').value = (item.transport ? parseFloat(item.transport).toFixed(2) : '');
                            lastRow.querySelector('.group_other_expenses').value = (item.other_expenses ? parseFloat(item.other_expenses).toFixed(2) : '');
                            lastRow.querySelector('.group_meal').value = (item.meal ? parseFloat(item.meal).toFixed(2) : '');
                            lastRow.querySelector('.group_meal_count').value = (item.meal_count ? parseFloat(item.meal_count).toFixed(1) : '');
                            lastRow.querySelector('.group_meal_total').value = (item.meal_total ? parseFloat(item.meal_total).toFixed(2) : '');
                            lastRow.querySelector('.group_sum').value = (item.sum ? parseFloat(item.sum).toFixed(2) : '');
                            updateTotalAmount();
                        });
                    } else {
                        createRow();
                    }
                    groupPeopleCountInput.value = tableBody.querySelectorAll('tr.group-people-list').length;
                } else {
                    groupPeopleCountInput.value = '';
                }
                if (ifYesDiv) ifYesDiv.style.display = "block";
                onePersonElements.forEach(el => {
                    el.style.display = "none";
                    el.querySelectorAll('input, textarea').forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                });
                updateTotalAmount();
                calcExpensesTotal();
            } else {
                groupPeopleCountInput.value = '';
                if (ifYesDiv) ifYesDiv.style.display = "none";
                onePersonElements.forEach(el => el.style.display = "");
            }
        }

        document.getElementById('noCheck')?.addEventListener('change', yesnoCheck);
        document.getElementById('yesCheck')?.addEventListener('change', yesnoCheck);

        document.getElementById('noCheck')?.addEventListener('change', updateGroupPeopleCount);
        document.getElementById('yesCheck')?.addEventListener('change', updateGroupPeopleCount);

        function createRow() {
            const newRow = document.createElement('tr');
            newRow.classList.add('group-people-list');
            // ตรวจสอบว่าเป็นแถวแรกหรือไม่
            const isFirstRow = tableBody.querySelectorAll('tr.group-people-list').length === 0;
            const defaultName = isFirstRow ? `${data_user.user_fname || ''} ${data_user.user_lname || ''}` : '';
            const defaultPosition = isFirstRow ? data_user.position_name : '';
            // ถ้าเป็นแถวแรก ให้ซ่อน checkbox เลย
            const checkboxHtml = isFirstRow
                ? ''
                : '<input class="form-check-input" type="checkbox" aria-label="...">';
            const firstRowClass = isFirstRow ? ' first-group-row' : '';
            newRow.innerHTML = `
                <td class="group-checkbox${firstRowClass}">${checkboxHtml}</td>
                <td><input class="form-control group_numrows" type="text" placeholder="Run Number Auto 1+" disabled></td>
                <td><input class="form-control group_name" type="text" placeholder="ระบุ ชื่อ" value="${defaultName}"></td>
                <td><input class="form-control group_position" type="text" placeholder="ระบุ ตำแหน่ง/ระดับ" value="${defaultPosition}"></td>
                <td><input class="form-control group_allowance format-number-2point" type="text" placeholder="ระบุ ค่าเบี้ยเลี้ยง"></td>
                <td><input class="form-control group_days format-number-1point" type="text" placeholder="ระบุ วัน"></td>
                <td><input class="form-control group_total_allowance" type="text" placeholder="ระบุ รวม ค่าเบี้ยเลี้ยง" disabled></td>
                <td><input class="form-control group_accommodation format-number-2point" type="text" placeholder="ระบุ ค่าเช่าที่พัก"></td>
                <td><input class="form-control group_accommodation_days format-number-1point" type="text" placeholder="ระบุ วัน"></td>
                <td><input class="form-control group_accommodation_total" type="text" placeholder="ระบุ รวม ค่าเช่าที่พัก" disabled></td>
                <td><input class="form-control group_transport format-number-2point" type="text" placeholder="ระบุ ค่าพาหนะ"></td>
                <td><input class="form-control group_other_expenses format-number-2point" type="text" placeholder="ระบุ ค่าใช้จ่ายอื่นๆ"></td>
                <td><input class="form-control group_meal format-number-2point" type="text" placeholder="ระบุ มื้อละ"></td>
                <td><input class="form-control group_meal_count format-number-1point" type="text" placeholder="ระบุ จำนวน"></td>
                <td><input class="form-control group_meal_total" type="text" placeholder="ระบุ รวม" disabled></td>
                <td><input class="form-control group_sum" type="text" placeholder="0.00" disabled></td>
            `;
            tableBody.appendChild(newRow);
            bindAutoCalc(newRow);
            updateRowNumbers();
            updateGroupPeopleCount();
            document.querySelectorAll('.format-number-2point').forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value.replace(/,/g, ''));
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });
            document.querySelectorAll('.format-number-1point').forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value.replace(/,/g, ''));
                    if (!isNaN(val)) {
                        this.value = val.toFixed(1);
                    } else {
                        this.value = '';
                    }
                });
            });
        }

        function numberToThaiText(number) {
            number = Number(number).toFixed(2);
            const txtNumArr = ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
            const txtDigitArr = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
            let [baht, satang] = number.split('.');
            let bahtText = '';
            let satangText = '';

            function readNumber(num) {
                let text = '';
                num = num.replace(/^0+/, ''); // ตัด 0 หน้าสุด
                if (num.length === 0) return '';
                let len = num.length;
                for (let i = 0; i < len; i++) {
                    let n = parseInt(num.charAt(i));
                    if (n !== 0) {
                        if (i === len - 1 && n === 1 && len > 1) {
                            text += 'เอ็ด';
                        } else if (i === len - 2 && n === 2) {
                            text += 'ยี่';
                        } else if (i === len - 2 && n === 1) {
                            text += '';
                        } else {
                            text += txtNumArr[n];
                        }
                        text += txtDigitArr[len - 1 - i];
                    }
                }
                return text;
            }

            // รองรับหลักล้านขึ้นไป
            function readNumberWithMillion(num) {
                let text = '';
                let million = '';
                while (num.length > 6) {
                    let sub = num.slice(0, num.length - 6);
                    million += readNumber(sub) + 'ล้าน';
                    num = num.slice(-6);
                }
                text = million + readNumber(num);
                return text;
            }

            baht = baht.replace(/^0+/, '') || '0';
            if (parseInt(baht, 10) === 0) bahtText = 'ศูนย์บาท';
            else bahtText = readNumberWithMillion(baht) + 'บาท';

            if (parseInt(satang, 10) === 0) satangText = 'ถ้วน';
            else satangText = readNumber(satang) + 'สตางค์';

            return bahtText + satangText;
        }

        function updateTotalAmount() {
            let totalAmount = 0;
            tableBody.querySelectorAll('tr.group-people-list').forEach(row => {
                const sumInput = row.querySelector('.group_sum');
                if (sumInput) {
                    const val = parseFloat(sumInput.value.replace(/,/g, '')) || 0;
                    totalAmount += val;
                }
            });
            const totalInputs = document.querySelectorAll('#expenses_group_total');
            if (totalInputs.length > 0) {
                totalInputs[0].value = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            const totalText = document.getElementById('expenses_group_total_text');
            if (totalText) {
                if (totalText.tagName === 'INPUT') {
                    totalText.value = numberToThaiText(totalAmount);
                } else {
                    totalText.textContent = numberToThaiText(totalAmount);
                }
            }
        }

        function bindAutoCalc(row) {
            const perDiemInput = row.children[4].querySelector('input');      // ค่าเบี้ยเลี้ยง/วัน
            const perDiemDayInput = row.children[5].querySelector('input');   // จำนวน(วัน)
            const perDiemSumInput = row.children[6].querySelector('input');   // รวม(บาท)
            const hotelInput = row.children[7].querySelector('input');        // ค่าเช่าที่พัก/วัน
            const hotelDayInput = row.children[8].querySelector('input');     // จำนวน(วัน)
            const hotelSumInput = row.children[9].querySelector('input');     // รวม(บาท)
            const vehicleInput = row.children[10].querySelector('input');     // ค่าพาหนะ
            const otherInput = row.children[11].querySelector('input');       // ค่าใช้จ่ายอื่นๆ
            const mealInput = row.children[12].querySelector('input');        // มื้อละ
            const mealQtyInput = row.children[13].querySelector('input');     // จำนวน(มื้อ)
            const mealSumInput = row.children[14].querySelector('input');     // รวม(บาท) หักอาหาร
            const totalInput = row.children[15].querySelector('input');       // รวม(บาท) ทั้งหมด

            function calc() {
                // คำนวณรวมค่าเบี้ยเลี้ยง
                const perDiem = parseFloat(perDiemInput.value) || 0;
                const perDiemDay = parseFloat(perDiemDayInput.value) || 0;
                const perDiemSum = perDiem * perDiemDay;
                perDiemSumInput.value = perDiemSum ? perDiemSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // คำนวณรวมค่าเช่าที่พัก
                const hotel = parseFloat(hotelInput.value) || 0;
                const hotelDay = parseFloat(hotelDayInput.value) || 0;
                const hotelSum = hotel * hotelDay;
                hotelSumInput.value = hotelSum ? hotelSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // คำนวณหักค่าอาหาร
                const meal = parseFloat(mealInput.value) || 0;
                const mealQty = parseFloat(mealQtyInput.value) || 0;
                const mealSum = meal * mealQty;
                mealSumInput.value = mealSum ? mealSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // รวมทั้งหมด
                const vehicle = parseFloat(vehicleInput.value) || 0;
                const other = parseFloat(otherInput.value) || 0;
                const total = perDiemSum + hotelSum + vehicle + other - mealSum;
                totalInput.value = total ? total.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';

                updateTotalAmount();
                if (typeof calcExpensesTotal === 'function') calcExpensesTotal();
            }

            [perDiemInput, perDiemDayInput, hotelInput, hotelDayInput, vehicleInput, otherInput, mealInput, mealQtyInput].forEach(input => {
                input.addEventListener('input', calc);
            });
        }

        delBtn.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBody.querySelectorAll('.group-checkbox input.form-check-input:checked');
            if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
            checked.forEach(cb => cb.closest('tr')?.remove());
            updateRowNumbers();
            updateTotalAmount();
            updateGroupPeopleCount();
            calcExpensesTotal();
        });

        addBtn.addEventListener('click', e => {
            e.preventDefault();
            createRow();
            updateTotalAmount();
        });

        // createRow();
        // updateRowNumbers();
        // updateTotalAmount();

        // ====== สำหรับตารางรายละเอียดการเดินทาง (list-details) ======
        const tableBodyDetails = document.querySelector('table .list-details')
            ? document.querySelector('table .list-details').closest('tbody')
            : document.querySelectorAll('table tbody')[1];

        const addBtnDetails = document.querySelector('.addTable-list-details');
        const delBtnDetails = document.querySelector('.delTable-list-details');
        const checkAllBoxDetails = document.querySelector('.check-all-details');
        if (checkAllBoxDetails) {
            checkAllBoxDetails.addEventListener('change', function () {
                const checked = this.checked;
                tableBodyDetails.querySelectorAll('.row-check-details input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }
        function bindFlatpickr(row) {
            // วันที่
            row.querySelectorAll('.list-detail-date').forEach(input => {
                flatpickr(input, {
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
                    formatDate: (date, format, locale) => {
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const yearBE = date.getFullYear() + 543;
                        return `${day}-${month}-${yearBE}`;
                    },
                    parseDate: (datestr, format) => {
                        const [d, m, y] = datestr
                            .replace(/[:\s]/g, "-")
                            .split("-");
                        return new Date(+y - 543, +m - 1, +d,);
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
                        instance.config.onValueUpdate = [updateThaiYearCalendar];
                    }
                });
            });
            // เวลา
            // Logic ควบคุม minTime/maxTime ระหว่าง start/end กับ depart/arrive
            const timeStartInput = row.querySelector('.list-detail-time-start');
            const timeEndInput = row.querySelector('.list-detail-time-end');
            const timeDepartInput = row.querySelector('.list-detail-time-depart');
            const timeArriveInput = row.querySelector('.list-detail-time-arrive');

            let fpStart = null;
            let fpEnd = null;
            let fpDepart = null;
            let fpArrive = null;

            // สร้าง flatpickr และเก็บ instance
            row.querySelectorAll('.list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive').forEach(input => {
                const fp = flatpickr(input, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    allowInput: true
                });
                if (input.classList.contains('list-detail-time-start')) fpStart = fp;
                if (input.classList.contains('list-detail-time-end')) fpEnd = fp;
                if (input.classList.contains('list-detail-time-depart')) fpDepart = fp;
                if (input.classList.contains('list-detail-time-arrive')) fpArrive = fp;
            });

            // ฟังก์ชันหาเวลาน้อยที่สุดระหว่าง depart/arrive
            function getMinDepartArrive() {
                const departVal = timeDepartInput?.value;
                const arriveVal = timeArriveInput?.value;
                if (departVal && arriveVal) {
                    const [h1, m1] = departVal.split(':').map(Number);
                    const [h2, m2] = arriveVal.split(':').map(Number);
                    return (h1 * 60 + m1 < h2 * 60 + m2) ? departVal : arriveVal;
                }
                return departVal || arriveVal || null;
            }

            // ฟังก์ชันหาเวลามากที่สุดระหว่าง start/end
            function getMaxStartEnd() {
                const startVal = timeStartInput?.value;
                const endVal = timeEndInput?.value;
                if (startVal && endVal) {
                    const [h1, m1] = startVal.split(':').map(Number);
                    const [h2, m2] = endVal.split(':').map(Number);
                    return (h1 * 60 + m1 > h2 * 60 + m2) ? startVal : endVal;
                }
                return startVal || endVal || null;
            }

            // อัปเดต minTime ของ depart/arrive ทุกครั้งที่ start/end เปลี่ยน
            function updateMinTimeDepartArrive() {
                const maxStartEnd = getMaxStartEnd();
                if (fpDepart) fpDepart.set('minTime', maxStartEnd);
                if (fpArrive) fpArrive.set('minTime', maxStartEnd);
            }
            if (timeStartInput) timeStartInput.addEventListener('change', updateMinTimeDepartArrive);
            if (timeEndInput) timeEndInput.addEventListener('change', updateMinTimeDepartArrive);
            updateMinTimeDepartArrive();

            // อัปเดต maxTime ของ start/end ทุกครั้งที่ depart/arrive เปลี่ยน
            function updateMaxTimeStartEnd() {
                const minDepartArrive = getMinDepartArrive();
                if (fpStart) fpStart.set('maxTime', minDepartArrive);
                if (fpEnd) fpEnd.set('maxTime', minDepartArrive);
            }
            if (timeDepartInput) timeDepartInput.addEventListener('change', updateMaxTimeStartEnd);
            if (timeArriveInput) timeArriveInput.addEventListener('change', updateMaxTimeStartEnd);
            updateMaxTimeStartEnd();

            // START/END: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
            function handleTimeInputDisableStartEnd() {
                const timeStart = row.querySelector('.list-detail-time-start');
                const timeEnd = row.querySelector('.list-detail-time-end');
                if (!timeStart || !timeEnd) return;

                if (timeStart.value && !timeEnd.value) {
                    timeEnd.disabled = true;
                    timeStart.disabled = false;
                } else if (!timeStart.value && timeEnd.value) {
                    timeStart.disabled = true;
                    timeEnd.disabled = false;
                } else {
                    timeStart.disabled = false;
                    timeEnd.disabled = false;
                }
            }

            const timeStart = row.querySelector('.list-detail-time-start');
            const timeEnd = row.querySelector('.list-detail-time-end');
            if (timeStart) timeStart.addEventListener('input', handleTimeInputDisableStartEnd);
            if (timeEnd) timeEnd.addEventListener('input', handleTimeInputDisableStartEnd);
            handleTimeInputDisableStartEnd();

            // DEPART/ARRIVE: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
            function handleTimeInputDisableDepartArrive() {
                const timeDepart = row.querySelector('.list-detail-time-depart');
                const timeArrive = row.querySelector('.list-detail-time-arrive');
                if (!timeDepart || !timeArrive) return;

                if (timeDepart.value && !timeArrive.value) {
                    timeArrive.disabled = true;
                    timeDepart.disabled = false;
                } else if (!timeDepart.value && timeArrive.value) {
                    timeDepart.disabled = true;
                    timeArrive.disabled = false;
                } else {
                    timeDepart.disabled = false;
                    timeArrive.disabled = false;
                }
            }

            const timeDepart = row.querySelector('.list-detail-time-depart');
            const timeArrive = row.querySelector('.list-detail-time-arrive');
            if (timeDepart) timeDepart.addEventListener('input', handleTimeInputDisableDepartArrive);
            if (timeArrive) timeArrive.addEventListener('input', handleTimeInputDisableDepartArrive);
            handleTimeInputDisableDepartArrive();
        }

        function checkVehicleRequired() {
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            const requiredLabel = document.getElementById('expenses_vehicle_number_required');

            const tableBodyDetails = document.querySelector('table .list-details')
                ? document.querySelector('table .list-details').closest('tbody')
                : document.querySelectorAll('table tbody')[1];
            if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
                requiredLabel.style.display = 'inline';
                $('.display-vehicle').show();
                if (tableBodyDetails && tableBodyDetails.querySelectorAll('tr.list-details').length === 0) {
                    if (Array.isArray(cd) && cd.length > 0) {
                        cd.forEach(item => {
                            createRowDetails(false);
                            const row = tableBodyDetails.lastElementChild;

                            //  กรอกข้อมูล
                            row.querySelector('.list-detail-date').value = formatThaiDate(item.list_date) || '';
                            row.querySelector('.list-detail-time-start').value = item.time_start || '';
                            row.querySelector('.list-detail-time-end').value = item.time_end || '';
                            row.querySelector('.list-detail-time-depart').value = item.time_depart || '';
                            row.querySelector('.list-detail-time-arrive').value = item.time_arrive || '';
                            row.querySelector('.list-detail-distance').value = (item.distance ? parseFloat(item.distance).toFixed(2) : '');
                            row.querySelector('.list-detail-compensation').value = (item.compensation ? parseFloat(item.compensation).toFixed(2) : '');
                            row.querySelector('.remark-detail').value = item.remark || '';
                            row.querySelectorAll('.list-detail-date').forEach(input => {
                                flatpickr(input, {
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
                                    formatDate: (date, format, locale) => {
                                        const day = String(date.getDate()).padStart(2, '0');
                                        const month = String(date.getMonth() + 1).padStart(2, '0');
                                        const yearBE = date.getFullYear() + 543;
                                        return `${day}-${month}-${yearBE}`;
                                    },
                                    parseDate: (datestr, format) => {
                                        const [d, m, y] = datestr
                                            .replace(/[:\s]/g, "-")
                                            .split("-");
                                        return new Date(+y - 543, +m - 1, +d,);
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
                                        instance.config.onValueUpdate = [updateThaiYearCalendar];
                                    }
                                });
                            });
                            // เวลา
                            // Logic ควบคุม minTime/maxTime ระหว่าง start/end กับ depart/arrive
                            const timeStartInput = row.querySelector('.list-detail-time-start');
                            const timeEndInput = row.querySelector('.list-detail-time-end');
                            const timeDepartInput = row.querySelector('.list-detail-time-depart');
                            const timeArriveInput = row.querySelector('.list-detail-time-arrive');

                            let fpStart = null;
                            let fpEnd = null;
                            let fpDepart = null;
                            let fpArrive = null;

                            // สร้าง flatpickr และเก็บ instance
                            row.querySelectorAll('.list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive').forEach(input => {
                                const fp = flatpickr(input, {
                                    enableTime: true,
                                    noCalendar: true,
                                    dateFormat: "H:i",
                                    time_24hr: true,
                                    allowInput: true
                                });
                                if (input.classList.contains('list-detail-time-start')) fpStart = fp;
                                if (input.classList.contains('list-detail-time-end')) fpEnd = fp;
                                if (input.classList.contains('list-detail-time-depart')) fpDepart = fp;
                                if (input.classList.contains('list-detail-time-arrive')) fpArrive = fp;
                            });

                            // ฟังก์ชันหาเวลาน้อยที่สุดระหว่าง depart/arrive
                            function getMinDepartArrive() {
                                const departVal = timeDepartInput?.value;
                                const arriveVal = timeArriveInput?.value;
                                if (departVal && arriveVal) {
                                    const [h1, m1] = departVal.split(':').map(Number);
                                    const [h2, m2] = arriveVal.split(':').map(Number);
                                    return (h1 * 60 + m1 < h2 * 60 + m2) ? departVal : arriveVal;
                                }
                                return departVal || arriveVal || null;
                            }

                            // ฟังก์ชันหาเวลามากที่สุดระหว่าง start/end
                            function getMaxStartEnd() {
                                const startVal = timeStartInput?.value;
                                const endVal = timeEndInput?.value;
                                if (startVal && endVal) {
                                    const [h1, m1] = startVal.split(':').map(Number);
                                    const [h2, m2] = endVal.split(':').map(Number);
                                    return (h1 * 60 + m1 > h2 * 60 + m2) ? startVal : endVal;
                                }
                                return startVal || endVal || null;
                            }

                            // อัปเดต minTime ของ depart/arrive ทุกครั้งที่ start/end เปลี่ยน
                            function updateMinTimeDepartArrive() {
                                const maxStartEnd = getMaxStartEnd();
                                if (fpDepart) fpDepart.set('minTime', maxStartEnd);
                                if (fpArrive) fpArrive.set('minTime', maxStartEnd);
                            }
                            if (timeStartInput) timeStartInput.addEventListener('change', updateMinTimeDepartArrive);
                            if (timeEndInput) timeEndInput.addEventListener('change', updateMinTimeDepartArrive);
                            updateMinTimeDepartArrive();

                            // อัปเดต maxTime ของ start/end ทุกครั้งที่ depart/arrive เปลี่ยน
                            function updateMaxTimeStartEnd() {
                                const minDepartArrive = getMinDepartArrive();
                                if (fpStart) fpStart.set('maxTime', minDepartArrive);
                                if (fpEnd) fpEnd.set('maxTime', minDepartArrive);
                            }
                            if (timeDepartInput) timeDepartInput.addEventListener('change', updateMaxTimeStartEnd);
                            if (timeArriveInput) timeArriveInput.addEventListener('change', updateMaxTimeStartEnd);
                            updateMaxTimeStartEnd();

                            // START/END: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                            function handleTimeInputDisableStartEnd() {
                                const timeStart = row.querySelector('.list-detail-time-start');
                                const timeEnd = row.querySelector('.list-detail-time-end');
                                if (!timeStart || !timeEnd) return;

                                if (timeStart.value && !timeEnd.value) {
                                    timeEnd.disabled = true;
                                    timeStart.disabled = false;
                                } else if (!timeStart.value && timeEnd.value) {
                                    timeStart.disabled = true;
                                    timeEnd.disabled = false;
                                } else {
                                    timeStart.disabled = false;
                                    timeEnd.disabled = false;
                                }
                            }

                            const timeStart = row.querySelector('.list-detail-time-start');
                            const timeEnd = row.querySelector('.list-detail-time-end');
                            if (timeStart) timeStart.addEventListener('input', handleTimeInputDisableStartEnd);
                            if (timeEnd) timeEnd.addEventListener('input', handleTimeInputDisableStartEnd);
                            handleTimeInputDisableStartEnd();

                            // DEPART/ARRIVE: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                            function handleTimeInputDisableDepartArrive() {
                                const timeDepart = row.querySelector('.list-detail-time-depart');
                                const timeArrive = row.querySelector('.list-detail-time-arrive');
                                if (!timeDepart || !timeArrive) return;

                                if (timeDepart.value && !timeArrive.value) {
                                    timeArrive.disabled = true;
                                    timeDepart.disabled = false;
                                } else if (!timeDepart.value && timeArrive.value) {
                                    timeDepart.disabled = true;
                                    timeArrive.disabled = false;
                                } else {
                                    timeDepart.disabled = false;
                                    timeArrive.disabled = false;
                                }
                            }

                            const timeDepart = row.querySelector('.list-detail-time-depart');
                            const timeArrive = row.querySelector('.list-detail-time-arrive');
                            if (timeDepart) timeDepart.addEventListener('input', handleTimeInputDisableDepartArrive);
                            if (timeArrive) timeArrive.addEventListener('input', handleTimeInputDisableDepartArrive);
                            handleTimeInputDisableDepartArrive();

                            updateTotalAmountDetails();
                        });
                    } else {
                        createRowDetails();
                    }
                }
            } else {
                requiredLabel.style.display = 'none';
                $('.display-vehicle').hide();
                // ลบแถวในตารางรายละเอียดการเดินทางเมื่อไม่เลือกประเภทรถใด ๆ
                if (tableBodyDetails) {
                    tableBodyDetails.querySelectorAll('tr.list-details').forEach(row => row.remove());
                }
                // อัปเดทยอดรวมใหม่หลังลบ
                if (typeof updateTotalAmountDetails === 'function') updateTotalAmountDetails();
            }
        }

        function handleVehicleCheckboxChange(e) {
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            if (e.target.id === 'expenses_vehicle' && car.checked) {
                motorcycle.checked = false;
            } else if (e.target.id === 'expenses_motorcycle' && motorcycle.checked) {
                car.checked = false;
            }
            checkVehicleRequired();
        }

        checkVehicleRequired();
        document.getElementById('expenses_vehicle')?.addEventListener('change', handleVehicleCheckboxChange);
        document.getElementById('expenses_motorcycle')?.addEventListener('change', handleVehicleCheckboxChange);

        // ฟังก์ชันสร้างแถวใหม่
        function createRowDetails(bindflexpickr = true) {
            const newRow = document.createElement('tr');
            newRow.classList.add('list-details');
            // ตรวจสอบ vehicle
            let defaultPerKm = '';
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            if (car && car.checked) {
                defaultPerKm = '5.5';
            } else if (motorcycle && motorcycle.checked) {
                defaultPerKm = '3.0';
            }
            newRow.innerHTML = `
                <td class="row-check-details"><input class="form-check-input" type="checkbox" aria-label="..."></td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                            <input type="text" class="form-control date-input list-detail-date" placeholder="ระบุวัน เดือน ปี">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-start" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-end" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-depart" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-arrive" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td><input type="number" class="form-control distance list-detail-distance" min="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
                <td><input type="number" class="form-control distance list-detail-compensation-per-km" min="0.1" placeholder="กรุณาระบุ เงินชดเชย/กม" value="${defaultPerKm}" disabled></td>
                <td><input type="number" class="form-control compensation sum-col-details list-detail-compensation" placeholder="เงินชดเชย (บาท)"></td>
                <td><input class="form-control remark-detail" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
            `;
            tableBodyDetails.appendChild(newRow);

            if (bindflexpickr == true) {
                bindFlatpickr(newRow);
            }
            bindAutoCalcDetails(newRow);
        }

        // ฟังก์ชันคำนวณยอดรวมและแปลงเป็นตัวอักษร
        function updateTotalAmountDetails() {
            let totalAmount = 0;
            tableBodyDetails.querySelectorAll('tr.list-details').forEach(row => {
                const sumInput = row.querySelector('.sum-col-details');
                if (sumInput) {
                    const val = parseFloat(sumInput.value.replace(/,/g, '')) || 0;
                    totalAmount += val;
                }
            });
            // ช่องรวมยอดใน tfoot
            const tfoot = tableBodyDetails.parentElement.querySelector('tfoot');
            if (tfoot) {
                const totalInputs = tfoot.querySelectorAll('#total_amount_details:disabled');
                if (totalInputs.length > 0) {
                    totalInputs[0].value = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                // แสดงจำนวนเงินทั้งสิ้น (ตัวอักษร)
                const totalTextInput = tfoot.querySelector('#total_text_details');
                if (totalTextInput) {
                    totalTextInput.value = numberToThaiText(totalAmount);
                }
            }
            if (typeof calcExpensesTotal === 'function') calcExpensesTotal();
        }
        // bind auto calc เฉพาะช่องค่าชดเชย
        function bindAutoCalcDetails(row) {
            // Format compensation (เงินชดเชย) เป็น xx.xx
            const compensationInputs = row.querySelectorAll('.compensation, .compensation-detail, .sum-col-details, .list-detail-compensation');
            compensationInputs.forEach(input => {
                input.addEventListener('input', updateTotalAmountDetails);
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value);
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });

            // Format distance (ระยะทาง) เป็น xx.xx
            const distanceInputs = row.querySelectorAll('.distance, .distance-detail, .list-detail-distance');
            distanceInputs.forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value);
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });

            // คำนวณเงินชดเชยอัตโนมัติ: ระยะทาง * เงินชดเชย/กม = รวมเงินชดเชย
            const distanceInput = row.querySelector('.list-detail-distance');
            const perKmInput = row.querySelector('.list-detail-compensation-per-km');
            const sumInput = row.querySelector('.sum-col-details');
            function autoCalcSumDetails() {
                const distance = parseFloat(distanceInput?.value) || 0;
                const perKm = parseFloat(perKmInput?.value) || 0;
                const sum = distance * perKm;
                if (sumInput) sumInput.value = sum ? sum.toFixed(2) : '';
                updateTotalAmountDetails();
            }
            if (distanceInput && perKmInput && sumInput) {
                distanceInput.addEventListener('input', autoCalcSumDetails);
                perKmInput.addEventListener('input', autoCalcSumDetails);
            }
        }

        // ปุ่มเพิ่มแถว (ต้องมีปุ่ม .addTable-list-details ใน HTML)
        if (addBtnDetails) {
            addBtnDetails.addEventListener('click', e => {
                e.preventDefault();
                createRowDetails();
                updateTotalAmountDetails();
            });
        }

        // เพิ่ม event listener สำหรับ checkbox ประเภทรถ
        const carCheckbox = document.getElementById('expenses_vehicle');
        const motorcycleCheckbox = document.getElementById('expenses_motorcycle');
        function updateCompensationPerKm() {
            let value = '';
            if (carCheckbox && carCheckbox.checked) {
                value = '5.5';
            } else if (motorcycleCheckbox && motorcycleCheckbox.checked) {
                value = '3.0';
            }
            // อัปเดตทุกแถวที่มี .list-detail-compensation-per-km และคำนวณใหม่
            document.querySelectorAll('.list-detail-compensation-per-km').forEach(input => {
                input.value = value;
                // trigger event เพื่อคำนวณใหม่
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
        if (carCheckbox) carCheckbox.addEventListener('change', updateCompensationPerKm);
        if (motorcycleCheckbox) motorcycleCheckbox.addEventListener('change', updateCompensationPerKm);

        // ปุ่มลบแถว (ต้องมีปุ่ม .delTable-list-details ใน HTML)
        if (delBtnDetails) {
            delBtnDetails.addEventListener('click', e => {
                e.preventDefault();
                const checked = tableBodyDetails.querySelectorAll('.row-check-details input.form-check-input:checked');
                if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
                checked.forEach(cb => cb.closest('tr')?.remove());
                updateTotalAmountDetails();
                calcExpensesTotal();
            });
        }

        // สร้างแถวแรก
        // createRowDetails();
        // updateTotalAmountDetails();


        // ดึงข้อมูลที่ต้องการดู
        try {
            const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=get_one&id=${expensesId}`, {
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
            m = data.main_data;
            mp = data.main_data_plan;
            cd = data.child_data;
            cdp = data.child_data_plan;
            tgd = data.traveling_group_data;
            const approveButtons = `
                    <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกการแก้ไข
                    </button>
                `;
            document.getElementById('button-container').innerHTML = approveButtons;
            // เงินทดรองจ่าย
            if (mp.fund) {
                $('#fund_select_label').val(
                    mp.fund.replace(/<[^>]+>/g, '')
                );
                $('#fund_html_label').html('');
            } else {
                $('#fund_select_label').val('เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)');
                $('#fund_html_label').html('<span style="color:red;">*ไม่พบกองทุน</span>');
            }
            if (mp.project) {
                $('#project_select_label').val(mp.project);
            } else {
                document.getElementById('project_select_label').value = '';
            }
            if (mp.day_return == 't') {
                document.getElementById('day_return').checked = true;
            }
            if (mp.money_receipt == 't') {
                document.getElementById('money_receipt').checked = true;
            }
            document.getElementById('note').value = mp.note || '';
            let grandTotal = 0;
            if (cdp && Array.isArray(cdp)) {
                const tableBody = document.querySelector('table tbody');
                cdp.forEach((item, key) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="text-center">${key + 1}</td>
                        <td>${item.wrd_mat_code || ''} - ${item.wrd_mat_name || ''}</td>
                        <td class="text-center">${item.qty || ''}</td>
                        <td class="text-center">${item.count_unit_name || ''}</td>
                        <td class="text-center">${Number(item.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-center">${Number(item.total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    `;
                    tableBody.appendChild(row);
                    grandTotal += Number(item.total || 0);
                });
                const totalElement = document.getElementById('total');
                if (totalElement) {
                    totalElement.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                const sumTotal = cdp.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
                const planTotalInput = document.getElementById('plan_total');
                if (planTotalInput) {
                    planTotalInput.value = sumTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
            $('#plan_number').val(mp.plan_number || '');
            $('#plan_date').val(formatThaiDate(mp.st_hf_date) || '');
            if (m.plan_id) {
                $('.have_plan_id').css('display', 'block');
                $('.no_plan_id').css('display', 'none');
            } else {
                $('.have_plan_id').css('display', 'none');
                $('.no_plan_id').css('display', 'block');
            }
            $('#plan_id').val(m.plan_id || '');
            // ข้อมูล main_data ส่วนหัว
            $('.plan_number').text(m.plan_number || '');
            $('#expenses_form_name').val(m.expenses_form_name || '');
            $('#depart_code').val(m.depart_code || '');
            $('#full_name').val(m.full_name || '');
            $('#position_name').val(m.position_name || '');
            $('#level_name').val(m.level_name || '');
            $('#affiliation_name').val(m.affiliation_name || '');
            $('#expenses_transportation_remark').val(m.expenses_transportation_remark || '');
            $('[name="expenses_start"]').val(formatThaiDatetime(m.expenses_start || ''));
            $('[name="expenses_end"]').val(formatThaiDatetime(m.expenses_end || ''));
            // ข้อมูล main_data รายละเอียด
            $('#expenses_learn').val(m.expenses_learn || '');
            $('#expenses_code').val(m.expenses_code || '');
            if (m.expenses_group == 2) {
                $('.div-one-person').hide();
                $('.div-two-person').show();
                $('#yesCheck').prop('checked', true);
                $('#ifYes').css('display', 'block');
                $('.one-person').hide();
            } else {
                $('.div-one-person').show();
                $('.div-two-person').hide();
                $('#noCheck').prop('checked', true);
                $('#ifYes').css('display', 'none');
                $('.one-person').show();
            }
            $('#group_people_count').val(m.expenses_group_count || '');
            switch (m.expenses_start_location) {
                case 'address':
                    $('#flexRadioDefault1_start').prop('checked', true);
                    break;
                case 'office':
                    $('#flexRadioDefault2_start').prop('checked', true);
                    break;
                case 'thailand':
                    $('#flexRadioDefault3_start').prop('checked', true);
                    break;
            }
            $('[name="expenses_start_address"]').text(m.expenses_start_address || '');
            switch (m.expenses_end_location) {
                case 'address':
                    $('#flexRadioDefault1_end').prop('checked', true);
                    break;
                case 'office':
                    $('#flexRadioDefault2_end').prop('checked', true);
                    break;
                case 'thailand':
                    $('#flexRadioDefault3_end').prop('checked', true);
                    break;
            }
            $('[name="expenses_end_address"]').text(m.expenses_end_address || '');
            $('[name="expenses_date"]').val(formatThaiDate(m.expenses_date) || '');
            $('[name="expenses_start_date"]').val(formatThaiDatetime(m.expenses_start_date) || '');
            $('[name="expenses_end_date"]').val(formatThaiDatetime(m.expenses_end_date) || '');
            // ข้อมูล main_date ส่วนท้าย
            $('#expenses_allowance').val(m.expenses_allowance ? parseFloat(m.expenses_allowance).toFixed(2) : '');
            $('#expenses_allowance_days').val(m.expenses_allowance_days ? parseFloat(m.expenses_allowance_days).toFixed(1) : '');
            $('#expenses_allowance_total').val(m.expenses_allowance_total ? parseFloat(m.expenses_allowance_total).toFixed(2) : '');
            $('#expenses_accommodation').val(m.expenses_accommodation ? parseFloat(m.expenses_accommodation).toFixed(2) : '');
            $('#expenses_accommodation_days').val(m.expenses_accommodation_days ? parseFloat(m.expenses_accommodation_days).toFixed(1) : '');
            $('#expenses_accommodation_total').val(m.expenses_accommodation_total ? parseFloat(m.expenses_accommodation_total).toFixed(2) : '');
            $('#expenses_transportation').val(m.expenses_transportation ? parseFloat(m.expenses_transportation).toFixed(2) : '');
            $('#expenses_transportation_total').val(m.expenses_transportation_total ? parseFloat(m.expenses_transportation_total).toFixed(2) : '');
            $('#expenses_moving').val(m.expenses_moving ? parseFloat(m.expenses_moving).toFixed(2) : '');
            $('#expenses_moving_total').val(m.expenses_moving_total ? parseFloat(m.expenses_moving_total).toFixed(2) : '');
            $('#expenses_moving_km').val(m.expenses_moving_km ? parseFloat(m.expenses_moving_km).toFixed(2) : '');
            if (m.expenses_vehicle == 'car') {
                $('#expenses_vehicle').prop('checked', true);
                $('.display-vehicle').show();
            } else if (m.expenses_vehicle == 'motorcycle') {
                $('#expenses_motorcycle').prop('checked', true);
                $('.display-vehicle').show();
            } else {
                $('.display-vehicle').hide();
            }
            $('#expenses_vehicle_number').val(m.expenses_vehicle_number || '');
            $('#expenses_other').val(m.expenses_other ? parseFloat(m.expenses_other).toFixed(2) : '');
            $('#expenses_other_total').val(m.expenses_other_total ? parseFloat(m.expenses_other_total).toFixed(2) : '');
            $('#expenses_food_per_meal').val(m.expenses_food_per_meal ? parseFloat(m.expenses_food_per_meal).toFixed(2) : '');
            $('#expenses_food_meals').val(m.expenses_food_meals ? parseFloat(m.expenses_food_meals).toFixed(1) : '');
            $('#expenses_food_total').val(m.expenses_food_total ? parseFloat(m.expenses_food_total).toFixed(2) : '');
            $('#expenses_note').text(m.expenses_note || '');
            $('#expenses_total').val(m.expenses_total ? parseFloat(m.expenses_total).toFixed(2) : '');
            $('#expenses_total_text').val(numberToThaiText(m.expenses_total));
            if (m.expenses_action == 'return') {
                $('#expenses_return').prop('checked', true);
            }
            if (m.expenses_action == 'withdraw') {
                $('#expenses_withdraw').prop('checked', true);
            }
            $('#expenses_amount').val(Number(m.expenses_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }));
            if (m.fund_id) {
                $('#fund_select').val(m.fund_id).trigger('change');
                if (m.project_id) {
                    projectDefaultId = m.project_id;
                }
            }

            // สร้างตาราง หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ
            if (tgd && Array.isArray(tgd)) {
                tgd.forEach(item => {
                    createRow();
                    const lastRow = tableBody.lastElementChild;
                    //  กรอกข้อมูล
                    lastRow.querySelector('.group_name').value = item.fullname || '';
                    lastRow.querySelector('.group_position').value = item.position || '';
                    lastRow.querySelector('.group_allowance').value = (item.allowance ? parseFloat(item.allowance).toFixed(2) : '');
                    lastRow.querySelector('.group_days').value = (item.allowance_days ? parseFloat(item.allowance_days).toFixed(1) : '');
                    lastRow.querySelector('.group_total_allowance').value = (item.total_allowance ? parseFloat(item.total_allowance).toFixed(2) : '');
                    lastRow.querySelector('.group_accommodation').value = (item.accommodation ? parseFloat(item.accommodation).toFixed(2) : '');
                    lastRow.querySelector('.group_accommodation_days').value = (item.accommodation_days ? parseFloat(item.accommodation_days).toFixed(1) : '');
                    lastRow.querySelector('.group_accommodation_total').value = (item.accommodation_total ? parseFloat(item.accommodation_total).toFixed(2) : '');
                    lastRow.querySelector('.group_transport').value = (item.transport ? parseFloat(item.transport).toFixed(2) : '');
                    lastRow.querySelector('.group_other_expenses').value = (item.other_expenses ? parseFloat(item.other_expenses).toFixed(2) : '');
                    lastRow.querySelector('.group_meal').value = (item.meal ? parseFloat(item.meal).toFixed(2) : '');
                    lastRow.querySelector('.group_meal_count').value = (item.meal_count ? parseFloat(item.meal_count).toFixed(1) : '');
                    lastRow.querySelector('.group_meal_total').value = (item.meal_total ? parseFloat(item.meal_total).toFixed(2) : '');
                    lastRow.querySelector('.group_sum').value = (item.sum ? parseFloat(item.sum).toFixed(2) : '');
                    updateTotalAmount();
                });
            } else {
                createRow();
            }
            // สร้างตาราง หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ
            if (cd && Array.isArray(cd)) {
                cd.forEach(item => {
                    createRowDetails(false);
                    const row = tableBodyDetails.lastElementChild;

                    //  กรอกข้อมูล
                    row.querySelector('.list-detail-date').value = formatThaiDate(item.list_date) || '';
                    row.querySelector('.list-detail-time-start').value = item.time_start || '';
                    row.querySelector('.list-detail-time-end').value = item.time_end || '';
                    row.querySelector('.list-detail-time-depart').value = item.time_depart || '';
                    row.querySelector('.list-detail-time-arrive').value = item.time_arrive || '';
                    row.querySelector('.list-detail-distance').value = (item.distance ? parseFloat(item.distance).toFixed(2) : '');
                    row.querySelector('.list-detail-compensation').value = (item.compensation ? parseFloat(item.compensation).toFixed(2) : '');
                    row.querySelector('.remark-detail').value = item.remark || '';
                    row.querySelectorAll('.list-detail-date').forEach(input => {
                        flatpickr(input, {
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
                            formatDate: (date, format, locale) => {
                                const day = String(date.getDate()).padStart(2, '0');
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const yearBE = date.getFullYear() + 543;
                                return `${day}-${month}-${yearBE}`;
                            },
                            parseDate: (datestr, format) => {
                                const [d, m, y] = datestr
                                    .replace(/[:\s]/g, "-")
                                    .split("-");
                                return new Date(+y - 543, +m - 1, +d,);
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
                                instance.config.onValueUpdate = [updateThaiYearCalendar];
                            }
                        });
                    });
                    // เวลา
                    // Logic ควบคุม minTime/maxTime ระหว่าง start/end กับ depart/arrive
                    const timeStartInput = row.querySelector('.list-detail-time-start');
                    const timeEndInput = row.querySelector('.list-detail-time-end');
                    const timeDepartInput = row.querySelector('.list-detail-time-depart');
                    const timeArriveInput = row.querySelector('.list-detail-time-arrive');

                    let fpStart = null;
                    let fpEnd = null;
                    let fpDepart = null;
                    let fpArrive = null;

                    // สร้าง flatpickr และเก็บ instance
                    row.querySelectorAll('.list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive').forEach(input => {
                        const fp = flatpickr(input, {
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "H:i",
                            time_24hr: true,
                            allowInput: true
                        });
                        if (input.classList.contains('list-detail-time-start')) fpStart = fp;
                        if (input.classList.contains('list-detail-time-end')) fpEnd = fp;
                        if (input.classList.contains('list-detail-time-depart')) fpDepart = fp;
                        if (input.classList.contains('list-detail-time-arrive')) fpArrive = fp;
                    });

                    // ฟังก์ชันหาเวลาน้อยที่สุดระหว่าง depart/arrive
                    function getMinDepartArrive() {
                        const departVal = timeDepartInput?.value;
                        const arriveVal = timeArriveInput?.value;
                        if (departVal && arriveVal) {
                            const [h1, m1] = departVal.split(':').map(Number);
                            const [h2, m2] = arriveVal.split(':').map(Number);
                            return (h1 * 60 + m1 < h2 * 60 + m2) ? departVal : arriveVal;
                        }
                        return departVal || arriveVal || null;
                    }

                    // ฟังก์ชันหาเวลามากที่สุดระหว่าง start/end
                    function getMaxStartEnd() {
                        const startVal = timeStartInput?.value;
                        const endVal = timeEndInput?.value;
                        if (startVal && endVal) {
                            const [h1, m1] = startVal.split(':').map(Number);
                            const [h2, m2] = endVal.split(':').map(Number);
                            return (h1 * 60 + m1 > h2 * 60 + m2) ? startVal : endVal;
                        }
                        return startVal || endVal || null;
                    }

                    // อัปเดต minTime ของ depart/arrive ทุกครั้งที่ start/end เปลี่ยน
                    function updateMinTimeDepartArrive() {
                        const maxStartEnd = getMaxStartEnd();
                        if (fpDepart) fpDepart.set('minTime', maxStartEnd);
                        if (fpArrive) fpArrive.set('minTime', maxStartEnd);
                    }
                    if (timeStartInput) timeStartInput.addEventListener('change', updateMinTimeDepartArrive);
                    if (timeEndInput) timeEndInput.addEventListener('change', updateMinTimeDepartArrive);
                    updateMinTimeDepartArrive();

                    // อัปเดต maxTime ของ start/end ทุกครั้งที่ depart/arrive เปลี่ยน
                    function updateMaxTimeStartEnd() {
                        const minDepartArrive = getMinDepartArrive();
                        if (fpStart) fpStart.set('maxTime', minDepartArrive);
                        if (fpEnd) fpEnd.set('maxTime', minDepartArrive);
                    }
                    if (timeDepartInput) timeDepartInput.addEventListener('change', updateMaxTimeStartEnd);
                    if (timeArriveInput) timeArriveInput.addEventListener('change', updateMaxTimeStartEnd);
                    updateMaxTimeStartEnd();

                    // START/END: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                    function handleTimeInputDisableStartEnd() {
                        const timeStart = row.querySelector('.list-detail-time-start');
                        const timeEnd = row.querySelector('.list-detail-time-end');
                        if (!timeStart || !timeEnd) return;

                        if (timeStart.value && !timeEnd.value) {
                            timeEnd.disabled = true;
                            timeStart.disabled = false;
                        } else if (!timeStart.value && timeEnd.value) {
                            timeStart.disabled = true;
                            timeEnd.disabled = false;
                        } else {
                            timeStart.disabled = false;
                            timeEnd.disabled = false;
                        }
                    }

                    const timeStart = row.querySelector('.list-detail-time-start');
                    const timeEnd = row.querySelector('.list-detail-time-end');
                    if (timeStart) timeStart.addEventListener('input', handleTimeInputDisableStartEnd);
                    if (timeEnd) timeEnd.addEventListener('input', handleTimeInputDisableStartEnd);
                    handleTimeInputDisableStartEnd();

                    // DEPART/ARRIVE: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                    function handleTimeInputDisableDepartArrive() {
                        const timeDepart = row.querySelector('.list-detail-time-depart');
                        const timeArrive = row.querySelector('.list-detail-time-arrive');
                        if (!timeDepart || !timeArrive) return;

                        if (timeDepart.value && !timeArrive.value) {
                            timeArrive.disabled = true;
                            timeDepart.disabled = false;
                        } else if (!timeDepart.value && timeArrive.value) {
                            timeDepart.disabled = true;
                            timeArrive.disabled = false;
                        } else {
                            timeDepart.disabled = false;
                            timeArrive.disabled = false;
                        }
                    }

                    const timeDepart = row.querySelector('.list-detail-time-depart');
                    const timeArrive = row.querySelector('.list-detail-time-arrive');
                    if (timeDepart) timeDepart.addEventListener('input', handleTimeInputDisableDepartArrive);
                    if (timeArrive) timeArrive.addEventListener('input', handleTimeInputDisableDepartArrive);
                    handleTimeInputDisableDepartArrive();

                    updateTotalAmountDetails();
                });
            } else {
                createRowDetails();
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

        // ดึงข้อมูลกลุ่ม (Group People)
        function getGroupPeopleData() {
            const rows = document.querySelectorAll('tr.group-people-list');
            const data = [];
            rows.forEach(row => {
                const name = row.querySelector('.group_name')?.value?.trim() || '';
                const position = row.querySelector('.group_position')?.value?.trim() || '';
                const allowance = parseFloat(row.querySelector('.group_allowance')?.value) || 0;
                const days = parseFloat(row.querySelector('.group_days')?.value) || 0;
                const total_allowance = parseFloat((row.querySelector('.group_total_allowance')?.value || '0').replace(/,/g, '')) || 0;
                const accommodation = parseFloat(row.querySelector('.group_accommodation')?.value) || 0;
                const accommodation_days = parseFloat(row.querySelector('.group_accommodation_days')?.value) || 0;
                const accommodation_total = parseFloat((row.querySelector('.group_accommodation_total')?.value || '0').replace(/,/g, '')) || 0;
                const transport = parseFloat(row.querySelector('.group_transport')?.value) || 0;
                const other_expenses = parseFloat(row.querySelector('.group_other_expenses')?.value) || 0;
                const meal = parseFloat(row.querySelector('.group_meal')?.value) || 0;
                const meal_count = parseFloat(row.querySelector('.group_meal_count')?.value) || 0;
                const meal_total = parseFloat((row.querySelector('.group_meal_total')?.value || '0').replace(/,/g, '')) || 0;
                const sum = parseFloat((row.querySelector('.group_sum')?.value || '0').replace(/,/g, '')) || 0;

                // เงื่อนไข: ถ้ามีข้อมูลอย่างน้อย 1 ช่อง ให้ push
                if (
                    name || position || allowance > 0 || days > 0 ||
                    total_allowance > 0 || accommodation > 0 || accommodation_days > 0 ||
                    accommodation_total > 0 || transport > 0 || other_expenses > 0 ||
                    meal > 0 || meal_count > 0 || meal_total > 0 || sum > 0
                ) {
                    data.push({
                        name,
                        position,
                        allowance,
                        days,
                        total_allowance,
                        accommodation,
                        accommodation_days,
                        accommodation_total,
                        transport,
                        other_expenses,
                        meal,
                        meal_count,
                        meal_total,
                        sum
                    });
                }
            });
            return data;
        }

        // ดึงข้อมูลรายละเอียดการเดินทาง (List Details)
        function getListDetailsData() {
            const rows = document.querySelectorAll('tr.list-details');
            const data = [];
            rows.forEach(row => {
                const date = row.querySelector('.list-detail-date')?.value?.trim() || '';
                const time_start = row.querySelector('.list-detail-time-start')?.value?.trim() || '';
                const time_end = row.querySelector('.list-detail-time-end')?.value?.trim() || '';
                const time_depart = row.querySelector('.list-detail-time-depart')?.value?.trim() || '';
                const time_arrive = row.querySelector('.list-detail-time-arrive')?.value?.trim() || '';
                const distance = row.querySelector('.list-detail-distance')?.value
                    ? parseFloat(row.querySelector('.list-detail-distance').value) || 0
                    : 0;
                const compensation_per_km = row.querySelector('.list-detail-compensation-per-km')?.value
                    ? parseFloat(row.querySelector('.list-detail-compensation-per-km').value) || 0
                    : 0;
                const compensation = row.querySelector('.list-detail-compensation')?.value
                    ? parseFloat(row.querySelector('.list-detail-compensation').value) || 0
                    : 0;
                const remark = row.querySelector('.remark-detail')?.value?.trim() || '';

                // เงื่อนไข: ถ้ามีข้อมูลอย่างน้อย 1 ช่อง ให้ push
                if (
                    date || time_start || time_end || time_depart || time_arrive ||
                    distance > 0 || compensation > 0 || remark
                ) {
                    data.push({
                        date,
                        time_start,
                        time_end,
                        time_depart,
                        time_arrive,
                        distance,
                        compensation,
                        remark,
                        compensation_per_km
                    });
                }
            });
            return data;
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
            data['fund'] = $("#fund_select option:selected").text();
            data['fund_id'] = getFundNewValue();
            data['project'] = $("#project_select option:selected").text();
            data['project_id'] = getProjectNewValue();
            data['req_user_code'] = data_user.user_code;
            return data;
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

        const btnSubmit = document.getElementById('btn-update');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', updateData);
        }

        async function updateData() {
            try {
                // Disable submit button
                const btnSubmit = document.getElementById('btn-update');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                }

                if (!$('#plan_id').val()) {
                    // ตรวจสอบกองทุน
                    if (!validateFund()) return;

                    // ตรวจสอบโครงการ
                    if (!validateProject()) return;
                }

                // ตรวจสอบการเรียนถึง
                if (!validateExpensesLearn()) return;

                // ตรวจสอบวันที่เริ่ม-สิ้นสุด
                if (!validateDateFields()) return;

                // ตรวจสอบการนับวัน (ถ้ามี)
                if (!validateTravelExpensesForm()) return;

                // ตรวจสอบว่ามีการใช้รถส่วนตัวหรือไม่
                if (!validateVehicleInfo()) return;

                // ตรวจสอบ list_data ต้องมีอย่างน้อย 1 แถว เฉพาะกรณีที่เลือกประเภทรถ
                const car = document.getElementById('expenses_vehicle');
                const motorcycle = document.getElementById('expenses_motorcycle');
                if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
                    const listData = getListDetailsData();
                    if (!listData.length) {
                        Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกข้อมูลรายละเอียดการเดินทางอย่างน้อย 1 รายการ' });
                        return;
                    }
                    // ตรวจสอบข้อมูลในแต่ละแถว (ตัวอย่าง: ต้องมีวันที่และค่าชดเชย)
                    for (const [i, row] of listData.entries()) {
                        if (!row.date) {
                            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: `กรุณาระบุวันที่ในแถวที่ ${i + 1} ของรายละเอียดการเดินทาง` });
                            return;
                        }
                        // เพิ่ม validate field อื่นๆ ตามต้องการ
                    }
                }

                // ตรวจสอบการติ๊กยืนยัน
                if (!validateConfirmCheckbox()) return;


                const data_sent = {
                    main_data: getMainFormData(),
                    group_data: getGroupPeopleData(),
                    list_data: getListDetailsData()
                };

                window.showLoading();

                try {
                    const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=edit_one&id=${expensesId}`, {
                        method: 'PUT',
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
                            text: "รายการถูกแก้ไขแล้ว",
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
                const btnSubmit = document.getElementById('btn-update');
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


    function validateDateFields() {
        const start = document.querySelector('[name="expenses_start"]')?.value;
        const end = document.querySelector('[name="expenses_end"]')?.value;
        if (!start || !end) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันที่เริ่มต้นและสิ้นสุด' });
            return false;
        }
        if (new Date(start) > new Date(end)) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด' });
            return false;
        }
        return true;
    }

    function validateFund() {
        const fund = document.getElementById('fund_select').value.trim();
        if (!fund) {
            Swal.fire({
                title: "ข้อมูลไม่ครบถ้วน",
                text: "กรุณาเลือกกองทุน",
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

    function validateExpensesLearn() {
        const expensesLearn = document.querySelector('[name="expenses_learn"]')?.value;
        if (!expensesLearn) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกเรียนถึง' });
            return false;
        }
        return true;
    }

    function validateTravelExpensesForm() {
        // วันและเวลาเริ่มต้น
        const expensesStart = document.querySelector('[name="expenses_start"]')?.value;
        if (!expensesStart) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาเริ่มต้น' });
            return false;
        }

        // วันและเวลาสิ้นสุด
        const expensesEnd = document.querySelector('[name="expenses_end"]')?.value;
        if (!expensesEnd) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาสิ้นสุด' });
            return false;
        }

        // ตรวจสอบว่าเริ่มต้นต้องไม่มากกว่าสิ้นสุด
        if (new Date(expensesStart) > new Date(expensesEnd)) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'วันและเวลาเริ่มต้นต้องไม่มากกว่าวันและเวลาสิ้นสุด' });
            return false;
        }

        // คำสั่ง/บันทึก
        const expensesCode = document.getElementById('expenses_code')?.value;
        if (!expensesCode) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกคำสั่ง/บันทึก' });
            return false;
        }

        // ลงวันที่
        const expensesDate = document.querySelector('[name="expenses_date"]')?.value;
        if (!expensesDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันที่ (ลงวันที่)' });
            return false;
        }

        // ค่าใช้จ่ายสำหรับ (radio)
        const expensesGroup = document.querySelector('input[name="expenses_group"]:checked');
        if (!expensesGroup) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกค่าใช้จ่ายสำหรับ (ข้าพเจ้า/ข้าพเจ้าพร้อมคณะเดินทาง)' });
            return false;
        }

        // สถานที่เริ่มต้น (radio)
        const expensesStartLocation = document.querySelector('input[name="expenses_start_location"]:checked');
        if (!expensesStartLocation) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกสถานที่เริ่มต้น' });
            return false;
        }

        // วันที่และเวลาเดินทาง (เริ่มต้น)
        const expensesStartDate = document.querySelector('[name="expenses_start_date"]')?.value;
        if (!expensesStartDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกวันที่และเวลาเดินทาง (เริ่มต้น)' });
            return false;
        }

        // สถานที่กลับ (radio)
        const expensesEndLocation = document.querySelector('input[name="expenses_end_location"]:checked');
        if (!expensesEndLocation) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกสถานที่กลับ' });
            return false;
        }

        // วันที่และเวลาเดินทาง (กลับ)
        const expensesEndDate = document.querySelector('[name="expenses_end_date"]')?.value;
        if (!expensesEndDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกวันที่และเวลาเดินทาง (กลับ)' });
            return false;
        }

        // หมายเลขทะเบียน (ถ้าเลือกใช้ยานพาหนะ)
        const carChecked = document.getElementById('expenses_vehicle')?.checked;
        const motorcycleChecked = document.getElementById('expenses_motorcycle')?.checked;
        if (carChecked || motorcycleChecked) {
            const vehicleNumber = document.getElementById('expenses_vehicle_number')?.value;
            if (!vehicleNumber) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกหมายเลขทะเบียนยานพาหนะ' });
                return false;
            }
        }

        // ตรวจสอบตาราง group-people-list ถ้าเลือก "ข้าพเจ้าพร้อมคณะเดินทาง"
        if (expensesGroup && expensesGroup.value === "2") {
            const groupRows = document.querySelectorAll('tr.group-people-list');
            if (!groupRows.length) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกข้อมูลคณะเดินทางอย่างน้อย 1 รายการ' });
                return false;
            }
        }

        // ตรวจสอบรายละเอียดประกอบการเบิก (list-details)
        // const listRows = document.querySelectorAll('tr.list-details');
        // if (!listRows.length) {
        //     Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกรายละเอียดประกอบการเบิกอย่างน้อย 1 รายการ' });
        //     return false;
        // }

        // สามารถเพิ่ม validate อื่นๆ ตามต้องการ

        return true;
    }
    function validateVehicleInfo() {
        // ตรวจสอบว่ามีการเลือก radio ยานพาหนะหรือไม่
        const vehicle = document.querySelector('input[name="expenses_vehicle"]:checked');
        if (vehicle) {
            // ถ้าเลือกแล้ว ต้องกรอกหมายเลขทะเบียน
            const vehicleNumber = document.getElementById('expenses_vehicle_number')?.value.trim();
            if (!vehicleNumber) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกหมายเลขทะเบียนยานพาหนะส่วนตัว'
                });
                return false;
            }
        }
        return true;
    }

    function validateConfirmCheckbox() {
        const confirm = document.getElementById('expenses_confirm');
        if (!confirm || !confirm.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณายืนยันว่ารายการที่กล่าวมาข้างต้นเป็นความจริง'
            });
            return false;
        }
        return true;
    }
    flatpickr("#date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        altInput: true,
        altFormat: "d-m-Y H:i",
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
            instance.config.onValueUpdate = [updateThaiYearCalendar];
        }
    });
    flatpickr("#date_notime", {
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
        formatDate: (date, format, locale) => {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const yearBE = date.getFullYear() + 543;
            return `${day}-${month}-${yearBE}`;
        },
        parseDate: (datestr, format) => {
            const [d, m, y] = datestr
                .replace(/[:\s]/g, "-")
                .split("-");
            return new Date(+y - 543, +m - 1, +d,);
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
            instance.config.onValueUpdate = [updateThaiYearCalendar];
        }
    });

})();

// ฟังก์ชั่นสำหรับ format วันที่ วัน/เดือน/ปี เวลา
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
function formatThaiDate(datetimeStr) {
    if (!datetimeStr) return '';
    const dateObj = new Date(datetimeStr);
    if (isNaN(dateObj)) return '';
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const year = dateObj.getFullYear() + 543;
    return `${day}-${month}-${year}`;
}