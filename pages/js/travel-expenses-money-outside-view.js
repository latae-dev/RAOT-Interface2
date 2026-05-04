import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        // ✅ ดึง id จาก URL
        const urlParams = new URLSearchParams(window.location.search);
        const expensesId = urlParams.get('id');
        const is_approve = urlParams.get('is_approve');
        const is_action = urlParams.get('is_action');

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

        // ✅ ดึงข้อมูล user ปัจจุบัน
        const params_all = await getAllParams();
        const data_user = params_all.current_user;
        document.getElementById('depart_code').value = data_user.depart_code || '';
        document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        document.getElementById('position_name').value = data_user.position_name || '';
        document.getElementById('level_name').value = data_user.level_name || '';
        document.getElementById('affiliation_name').value = data_user.branch_name || '';

        // ✅ ดึงข้อมูล plan ตาม id
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

        const posCode = data_user.position_code;
        const approveStatus = data.main_data?.approve_status || "";

        if (is_approve === '0') {
            // เคสไม่ให้อนุมัติเลย
            document.getElementById("approve-section").style.display = "none";
            if (is_action === 'approve') {
                document.getElementById('button-container').innerHTML = `
                    <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
            } else {
                document.getElementById('button-container').innerHTML = `
                    <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
            }
        } else if (
            posCode === 'APHO' && approveStatus === 'waiting'
        ) {
            // ✅ APHO กดได้ตอน status ยัง waiting
            document.getElementById('st_hf_full_name').value =
                `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
            document.getElementById('st_hf_date').value = formatThaiDate(new Date());

            document.getElementById('button-container-approve').innerHTML = `
                <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
                    <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                </a>
                <button id="btn-update" type="button" class="btn btn-primary m-1">
                    <i class="bi bi-save"></i> บันทึกข้อมูล
                </button>
            `;
        } else if (
            posCode === 'APHOP' && approveStatus === 'finance_approve'
        ) {
            // ✅ APHOP กดได้หลัง APHO อนุมัติแล้ว
            document.getElementById('st_hf_full_name').value =
                `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
            document.getElementById('st_hf_date').value = formatThaiDate(new Date());

            document.getElementById('button-container-approve').innerHTML = `
                <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
                    <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                </a>
                <button id="btn-update" type="button" class="btn btn-primary m-1">
                    <i class="bi bi-save"></i> บันทึกข้อมูล
                </button>
            `;
        } else {
            // ❌ reject หรืออนุมัติขั้นสุดท้ายแล้ว → ไม่มีใครกดได้
            document.getElementById("approve-section").style.display = "none";
            if (is_action === 'approve') {
                document.getElementById('button-container').innerHTML = `
                    <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
            } else {
                document.getElementById('button-container').innerHTML = `
                    <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
            }
        }




        // -----------------------------
        // เติมค่า main_data ลง input
        // -----------------------------
        const m = data.main_data;
        const mp = data.main_data_plan;
        const cp = data.child_data_plan;
        const tvg = data.traveling_group_data || [];

        if (m.plan_id) {
            $('.have_plan_id').css('display', '');
        } else {
            $('.have_plan_id').css('display', 'none');
        }

        document.getElementById('expenses_form_name').value = m.expenses_form_name || '';
        setupThaiDateTimePicker("#expenses_start", m.expenses_start, true);
        setupThaiDateTimePicker("#expenses_end", m.expenses_end, true);

        document.getElementById('depart_code').value = m.depart_code || '';
        document.getElementById('full_name').value = m.full_name || '';
        document.getElementById('position_name').value = m.position_name || '';
        document.getElementById('level_name').value = m.level_name || '';
        document.getElementById('affiliation_name').value = m.affiliation_name || '';
        document.getElementById('text_approve').value = m.text_approve || '';

        $('.plan_number').text(m.plan_number || '');
        // เลือกกองทุน
        if (mp.fund) {
            $('#fund_select_label').val(
                mp.fund.replace(/<[^>]+>/g, '')
            );
            $('#fund_html_label').html('');
        } else {
            $('#fund_select_label').val('เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)');
            $('#fund_html_label').html('<span style="color:red;">*ไม่พบกองทุน</span>');
        }
        // เลือกโครงการ
        if (mp.project) {
            $('#project_select_label').val(mp.project);
        } else {
            document.getElementById('project_select_label').value = '';
        }

        // สร้างตารางรายการ
        let grandTotal = 0;
        if (cp && Array.isArray(cp)) {
            const tableBody = document.querySelector('table tbody');
            cp.forEach((item, key) => {
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

            // แสดงยอดรวมในช่อง total
            const totalElement = document.getElementById('total');
            if (totalElement) {
                totalElement.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
        // นับจากวันกลับมาถึง
        if (mp.day_return === "t") {
            document.getElementById('day_return').checked = true;
        }// นับแต่วันที่ได้รับเงิน
        if (mp.money_receipt === "t") {
            document.getElementById('money_receipt').checked = true;
        }
        // เหตุผลของการขอยืมเงินทดรอง
        document.getElementById('note').value = mp.note || '';

        document.getElementById('expenses_code').value = m.expenses_code || '';
        setupThaiDateTimePicker("#date_notime", m.expenses_date, false);

        // radio group ข้าพเจ้าหรือคณะเดินทาง
        // if (m.expenses_group === "1") {
        //     document.getElementById('noCheck').checked = true;
        //     yesnoCheck();
        // } else if (m.expenses_group === "2") {
        //     document.getElementById('yesCheck').checked = true;
        //     yesnoCheck();
        // }
        // document.getElementById('group_people_count').value = m.expenses_group_count || '';

        document.getElementById('position_withdraw').value = m.position_withdraw || '';

        // สถานที่เริ่มต้น/สิ้นสุด
        if (m.expenses_start_location) {
            document.querySelector(`input[name="expenses_start_location"][value="${m.expenses_start_location}"]`).checked = true;
        }

        setupThaiDateTimePicker("#expenses_start_date", m.expenses_start_date, false);
        document.querySelector('textarea[name="expenses_start_address"]').value = m.expenses_start_address || '';

        if (m.expenses_end_location) {
            document.querySelector(`input[name="expenses_end_location"][value="${m.expenses_end_location}"]`).checked = true;
        }
        setupThaiDateTimePicker("#expenses_end_date", m.expenses_end_date, false);
        document.querySelector('textarea[name="expenses_end_address"]').value = m.expenses_end_address || '';

        // ค่าใช้จ่ายต่าง ๆ
        document.getElementById('expenses_allowance').value = m.expenses_allowance || '';
        document.getElementById('expenses_allowance_days').value = m.expenses_allowance_days || '';
        document.getElementById('expenses_allowance_total').value = m.expenses_allowance_total || '';

        document.getElementById('expenses_accommodation').value = m.expenses_accommodation || '';
        document.getElementById('expenses_accommodation_days').value = m.expenses_accommodation_days || '';
        document.getElementById('expenses_accommodation_total').value = m.expenses_accommodation_total || '';

        document.getElementById('expenses_transportation').value = m.expenses_transportation || '';
        document.getElementById('expenses_transportation_total').value = m.expenses_transportation_total || '';
        document.getElementById('expenses_transportation_remark').value = m.expenses_transportation_remark || '';

        // document.getElementById('expenses_moving').value = m.expenses_moving || '';
        // document.getElementById('expenses_moving_total').value = m.expenses_moving_total || '';

        //ปรับเพิ่ม
        if (!m.plan_id) {
            document.getElementById('fund_select_label_noplan').value = m.fund || '';
            document.getElementById('project_select_label_noplan').value = m.project || '';
        } else {
            $('.fund_and_project').hide();
        }
        if (m.expenses_group == 2) {
            $('.one-person').hide();
        }
        document.getElementById('expenses_learn').value = m.expenses_learn || '';

        if (m.expenses_vehicle === 'car') {
            $('#expenses_vehicle').prop('checked', true);
        } else if (m.expenses_vehicle === 'motorcycle') {
            $('#expenses_motorcycle').prop('checked', true);
        }
        $('#expenses_vehicle_number').val(m.expenses_vehicle_number || '');
        //ปรับเพิ่ม

        // เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :
        if (m.expenses_vehicle != '') {
            $('.has_vehicle').show();
            const tbody = document.getElementById("list-details");
            tbody.innerHTML = ""; // ล้างก่อน

            let total = 0;

            data.child_data.forEach(row => {
                total += parseFloat(row.compensation || 0);

                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td class="text-center">${formatThaiDate(row.list_date) || ''}</td>

                <td class="text-center">${row.time_start || '-'}</td>
                <td class="text-center">${row.time_end || '-'}</td>

                <td class="text-center">${row.time_depart || '-'}</td>
                <td class="text-center">${row.time_arrive || '-'}</td>

                <td class="text-end">${Number(row.distance || 0).toLocaleString()}</td>
                <td class="text-end">${Number(row.compensation_per_km || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                <td class="text-end">${Number(row.compensation || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>

                <td>${row.remark || ''}</td>
            `;
                tbody.appendChild(tr);
            });
            // ✅ update ยอดรวม
            document.getElementById("total_amount_details").value =
                total.toLocaleString(undefined, { minimumFractionDigits: 2 });

            document.getElementById("total_text_details").value = convertNumberToThaiText(total);
        } else {
            $('.has_vehicle').hide();
        }

        // ค่าใช้จ่ายอื่น ๆ
        document.getElementById('expenses_other').value = m.expenses_other || '';
        document.getElementById('expenses_other_total').value = Number(m.expenses_other_total || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });

        document.getElementById('expenses_food_per_meal').value = m.expenses_food_per_meal || '';
        document.getElementById('expenses_food_meals').value = m.expenses_food_meals || '';
        document.getElementById('expenses_food_total').value = Number(m.expenses_food_total || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });


        // ✅ toggle ตาราง
        const ifYes = document.getElementById("ifYes");
        if (m.expenses_group === "2") {
            ifYes.style.display = "block";
        } else {
            ifYes.style.display = "none";
        }

        // ✅ render group data
        const tbody_group = document.querySelector(".group-people-listgroup-people-list").parentNode;
        tbody_group.innerHTML = ""; // ล้างก่อน

        let groupTotal = 0;

        tvg.forEach((item, index) => {
            const totalRow =
                (parseFloat(item.total_allowance || 0) +
                    parseFloat(item.accommodation_total || 0) +
                    parseFloat(item.transport || 0) +
                    parseFloat(item.other_expenses || 0) -
                    parseFloat(item.meal_total || 0));

            groupTotal += totalRow;

            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td>${item.fullname || ''}</td>
            <td>${item.position || ''}</td>
            <td>${item.id_card || ''}</td>

            <td class="text-end">${Number(item.allowance || 0).toLocaleString()}</td>
            <td class="text-center">${item.allowance_days || ''}</td>
            <td class="text-end">${Number(item.total_allowance || 0).toLocaleString()}</td>

            <td class="text-end">${Number(item.accommodation || 0).toLocaleString()}</td>
            <td class="text-center">${item.accommodation_days || ''}</td>
            <td class="text-end">${Number(item.accommodation_total || 0).toLocaleString()}</td>

            <td class="text-end">${Number(item.transport || 0).toLocaleString()}</td>
            <td class="text-end">${Number(item.other_expenses || 0).toLocaleString()}</td>

            <td class="text-end">${Number(item.meal || 0).toLocaleString()}</td>
            <td class="text-center">${item.meal_count || ''}</td>
            <td class="text-end">${Number(item.meal_total || 0).toLocaleString()}</td>

            <td class="text-end fw-bold">${totalRow.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
        `;
            tbody_group.appendChild(tr);
        });

        // ✅ update total
        document.getElementById("expenses_group_total").value =
            groupTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });

        document.getElementById("expenses_group_total_text").value = convertNumberToThaiText(groupTotal);


        // หมายเหตุ + รวม
        document.getElementById('expenses_note').value = m.expenses_note || '';
        document.getElementById('expenses_total').value = Number(m.expenses_total || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        updateExpensesTotalText();

        // เลขที่สัญญา
        document.getElementById('plan_number').value = m.plan_number || '';
        document.getElementById('plan_date').value = m.expenses_date || '';
        document.getElementById('plan_total').value = m.plan_total || '';

        // ผู้ใช้งานต้องการ return/withdraw
        if (m.expenses_action === 'return') {
            document.getElementById('expenses_return').checked = true;
        } else if (m.expenses_action === 'withdraw') {
            document.getElementById('expenses_withdraw').checked = true;
        }
        document.getElementById('expenses_amount').value = Number(m.expenses_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

        // ยืนยัน
        if (m.expenses_confirm === "1") {
            document.getElementById('expenses_confirm').checked = true;
        }

        // แสดงผลการพิจารณาอนุมัติ
        if (data.approve_data && Array.isArray(data.approve_data)) {
            const tableBody = document.querySelector('#approval-table tbody');
            tableBody.innerHTML = ''; // ล้างข้อมูลเดิมก่อน
            if (data.approve_data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td>`;
                tableBody.appendChild(row);
            } else {
                data.approve_data.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${item.approve_date ? formatThaiDatetime(item.approve_date) : ''}</td>
                        <td class="text-center">
                            ${item.approve_status === 'finance_approve' ? 'กองการเงิน อนุมัติ' :
                            item.approve_status === 'audit_approve' ? 'กองตรวจจ่าย อนุมัติ' :
                                item.approve_status === 'finance_reject' ? 'กองการเงิน ไม่อนุมัติ' :
                                    item.approve_status === 'audit_reject' ? 'กองตรวจจ่าย ไม่อนุมัติ' :
                                        ''
                        }
                        </td>
                        <td>${item.user_fname || ''} ${item.user_lname || ''}</td>
                        <td>${item.remark || ''}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        }

        const approveCheckbox = document.getElementById('approve');
        const rejectCheckbox = document.getElementById('reject');
        const remarkTextarea = document.getElementById('remark');

        // remarkTextarea.disabled = true;

        approveCheckbox.addEventListener('change', () => {
            if (approveCheckbox.checked) {
                rejectCheckbox.checked = false;
                $('#hide_text_approve').hide();
            } else if (!rejectCheckbox.checked) {
                $('#hide_text_approve').show();
            }
        });

        rejectCheckbox.addEventListener('change', () => {
            if (rejectCheckbox.checked) {
                approveCheckbox.checked = false;
                $('#hide_text_approve').show();
            } else if (!approveCheckbox.checked) {
                $('#hide_text_approve').show();
            }
        });

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

                const checkboxGroups = ['.approve, .reject'];

                const valid = validateRequiredFields(checkboxGroups);
                if (!valid) return;
                if (!validateRejectRemark()) return;

                const data_sent = {
                    main_data: {
                        approve: approveCheckbox.checked,
                        reject: rejectCheckbox.checked,
                        remark: remarkTextarea.value.trim(),
                        user_code: data_user.user_code,
                    },
                };

                window.showLoading();

                try {
                    const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=approve_one&id=${expensesId}&position_code=${posCode}`, {
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
                            window.location.href = 'travel-expenses-approvel-list.php';
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

        function validateRejectRemark() {
            if (rejectCheckbox.checked) {
                const remarkValue = remarkTextarea.value.trim();
                if (!remarkValue) {
                    Swal.fire({
                        title: "ข้อมูลไม่ครบถ้วน",
                        text: "กรุณากรอกข้อมูลหมายเหตุ",
                        icon: "warning"
                    });
                    remarkTextarea.focus();
                    return false;
                }
            }

            return true;
        }

        function validateRequiredFields(checkboxGroups = []) {

            // ตรวจสอบกลุ่ม checkbox
            for (const groupSelector of checkboxGroups) {
                const checkboxes = document.querySelectorAll(groupSelector);
                if (!checkboxes.length) {
                    Swal.fire({
                        title: "ข้อมูลไม่ครบถ้วน",
                        text: "ไม่พบ checkbox group: " + groupSelector,
                        icon: "warning"
                    });
                    return false;
                }
                const isChecked = Array.from(checkboxes).some(cb => cb.checked);
                if (!isChecked) {
                    Swal.fire({
                        title: "ข้อมูลไม่ครบถ้วน",
                        text: "กรุณาเลือกสถานะการอนุมัติ",
                        icon: "warning"
                    });
                    return false;
                }
            }

            return true;
        }
    } catch (err) {
        console.error('เกิดข้อผิดพลาด:', err);
        Swal.fire({
            title: "เกิดข้อผิดพลาด",
            text: "ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
            icon: "error"
        });
    }
})();

// ✅ helper format วันที่
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
function setupThaiDateTimePicker(selector, value, withTime = true) {
    const el = document.querySelector(selector);
    if (!el) return null;

    const picker = flatpickr(el, {
        enableTime: withTime,  // ✅ กำหนดว่าจะให้มีเวลาไหม
        dateFormat: withTime ? "Y-m-d H:i:S" : "Y-m-d",   // ค.ศ. เก็บไป backend
        altInput: true,
        altFormat: withTime ? "d-m-Y H:i" : "d-m-Y",      // UI ที่โชว์
        locale: "th",
        onReady(selectedDates, dateStr, instance) {
            updateThaiYear(instance, withTime);
            instance.config.onChange.push(() => updateThaiYear(instance, withTime));
            instance.config.onMonthChange.push(() => updateThaiYear(instance, withTime));
            instance.config.onYearChange.push(() => updateThaiYear(instance, withTime));
        }
    });

    if (value) {
        picker.setDate(value, true);
        updateThaiYear(picker, withTime);
    }

    return picker;
}

function updateThaiYear(instance, withTime) {
    if (instance.selectedDates.length > 0) {
        const date = instance.selectedDates[0];
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear() + 543; // ✅ แปลงเป็น พ.ศ.
        let formatted = `${day}-${month}-${year}`;

        if (withTime) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            formatted += ` ${hours}:${minutes}`;
        }

        instance.altInput.value = formatted;
    }
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
function convertNumberToThaiText(number) {
    const txtnum1 = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า', 'สิบ'];
    const txtnum2 = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
    number = number.toString().replace(/[, ]/g, ''); // ลบ comma
    let [intPart, decPart] = number.split('.');
    let bahtText = '';
    let len = intPart.length;
    for (let i = 0; i < len; i++) {
        let n = parseInt(intPart.charAt(i));
        if (n !== 0) {
            if (i === len - 1 && n === 1 && len > 1) {
                bahtText += 'เอ็ด';
            } else if (i === len - 2 && n === 2) {
                bahtText += 'ยี่';
            } else if (i === len - 2 && n === 1) {
                bahtText += '';
            } else {
                bahtText += txtnum1[n];
            }
            bahtText += txtnum2[len - i - 1];
        }
    }
    bahtText = bahtText || 'ศูนย์';
    bahtText += 'บาท';
    if (!decPart || decPart === '00') {
        bahtText += 'ถ้วน';
    } else {
        let satangText = '';
        for (let i = 0; i < decPart.length; i++) {
            let n = parseInt(decPart.charAt(i));
            if (n !== 0) {
                satangText += txtnum1[n] + txtnum2[decPart.length - i - 1];
            }
        }
        bahtText += satangText + 'สตางค์';
    }
    return bahtText;
}

