import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        // ✅ ดึง id จาก URL
        const urlParams = new URLSearchParams(window.location.search);
        const compensationId = urlParams.get('id');
        const is_approve = urlParams.get('is_approve');
        const is_action = urlParams.get('is_action');

        if (!compensationId) {
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
        document.getElementById('affiliation_name').value = data_user.depart_name || '';

        // ✅ ดึงข้อมูล plan ตาม id
        const response = await fetch(`../controllers/withdraws/wrd_compensation_controller.php?action=get_one&id=${compensationId}`, {
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

        if (!data.main_data.plan_id) {
            var permissionAPHO = params_all.position_list_id.some(id => ['5'].includes(id));
            var permissionAPHOP = params_all.position_list_id.some(id => ['6'].includes(id));
            var permissionCHO = params_all.position_list_id.some(id => ['12'].includes(id));
            var permissionAPAHO = params_all.position_list_id.some(id => ['13'].includes(id));
            if (is_approve === '0') {
                // เคสไม่ให้อนุมัติเลย
                document.getElementById("approve-section").style.display = "none";
                if (is_action === 'approve') {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                } else {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                }
            } else if (permissionAPHOP && approveStatus === "waiting") {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                        <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <button id="btn-update" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> บันทึกข้อมูล
                        </button>
                    `;
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else if (permissionAPHO && approveStatus === "finance_check") {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                        <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <button id="btn-update" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> บันทึกข้อมูล
                        </button>
                    `;
                $('#compensation-detail-table').show();
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else if (permissionCHO && permissionAPAHO && (approveStatus === "audit_approve")) {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').hide();
                $('.div_permissionCHO').show();
                $('.div_permissionAPAHO').show();
            } else if (permissionCHO && approveStatus === "audit_approve") {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').hide();
                $('.div_permissionCHO').show();
                $('.div_permissionAPAHO').hide();
            } else if (permissionAPAHO && (approveStatus === "finance_check")) {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else {
                // ❌ reject หรืออนุมัติขั้นสุดท้ายแล้ว → ไม่มีใครกดได้
                document.getElementById("approve-section").style.display = "none";
                if (is_action === 'approve') {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                } else {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                }
            }
        } else {
            var permissionAPHO = params_all.position_list_id.some(id => ['5'].includes(id));
            var permissionCHO = params_all.position_list_id.some(id => ['12'].includes(id));
            var permissionAPAHO = params_all.position_list_id.some(id => ['13'].includes(id));
            if (is_approve === '0') {
                // เคสไม่ให้อนุมัติเลย
                document.getElementById("approve-section").style.display = "none";
                if (is_action === 'approve') {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                } else {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                }
            } else if (permissionAPHO && approveStatus === "finance_check") {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                        <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                        </a>
                        <button id="btn-update" type="button" class="btn btn-primary m-1">
                            <i class="bi bi-save"></i> บันทึกข้อมูล
                        </button>
                    `;
                $('#compensation-detail-table').show();
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else if (permissionCHO && permissionAPAHO && (approveStatus === "finance_check")) {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else if (permissionCHO && permissionAPAHO && (approveStatus === "waiting" || approveStatus === "finance_check")) {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').hide();
                $('.div_permissionCHO').show();
                $('.div_permissionAPAHO').show();
            } else if (permissionCHO && approveStatus === "waiting") {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').hide();
                $('.div_permissionCHO').show();
                $('.div_permissionAPAHO').hide();
            } else if (permissionAPAHO && (approveStatus === "waiting" || approveStatus === "finance_check")) {
                document.getElementById('st_hf_full_name').value =
                    `${data_user.user_fname || ''} ${data_user.user_lname || ''}`.trim();
                document.getElementById('st_hf_date').value = formatThaiDate(new Date());

                document.getElementById('button-container-approve').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
                $('.div_permissionAPHO').show();
                $('.div_permissionCHO').hide();
                $('.div_permissionAPAHO').hide();
            } else {
                // ❌ reject หรืออนุมัติขั้นสุดท้ายแล้ว → ไม่มีใครกดได้
                document.getElementById("approve-section").style.display = "none";
                if (is_action === 'approve') {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-approvel-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                } else {
                    document.getElementById('button-container').innerHTML = `
                    <a href="money-order-list.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                    </a>
                `;
                }
            }
        }

        const addBtn = document.querySelector('.addTable');
        const delBtn = document.querySelector('.delTable');
        const tableBody = document.querySelector('#compensation-detail-table table tbody');

        const checkAllBox = document.querySelector('.check-all');
        if (checkAllBox) {
            checkAllBox.addEventListener('change', function () {
                const checked = this.checked;
                tableBody.querySelectorAll('.group-checkbox input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }

        function updateRowNumbers() {
            if (!tableBody) return;
            let order = 1;
            tableBody.querySelectorAll('tr.product-list-gl').forEach((row) => {
                const col = row.children[1];
                if (col) {
                    const numberInput = col.querySelector('input');
                    if (numberInput) {
                        numberInput.value = order;
                        order++;
                    }
                }
            });
        }

        function createRow() {
            const selectedOption = $('#compensation_expense_select').find(':selected');
            const sapValue = selectedOption.data('sap');
            var readonly = '';
            if (sapValue == 1) {
                readonly = 'readonly';
            }
            const newRow = document.createElement('tr');
            newRow.classList.add('product-list-gl');
            const isFirstRow = tableBody.querySelectorAll('tr.product-list-gl').length === 0;
            const checkboxHtml = isFirstRow
                ? ''
                : '<input class="form-check-input" type="checkbox" aria-label="...">';
            const firstRowClass = isFirstRow ? ' first-group-row' : '';
            newRow.innerHTML = `
                <input class="form-control mat_id" type="hidden">
                <td class="group-checkbox${firstRowClass}">${checkboxHtml}</td>
                <td>
                    <select class="form-control select-mat-code" style="width:100%"></select>
                </td>
                <td><input class="form-control mat-name" type="text" placeholder="รายการ" readonly></td>
                <td><input class="form-control detail_bath" type="number" placeholder="00" ${readonly}></td>
                <td><input class="form-control detail_stang" type="number" max="99" ${readonly} maxlength="2" min="0" placeholder="00" oninput="if(this.value.length>2)this.value=this.value.slice(0,2);if(+this.value>99)this.value=99;"></td>
            `;
            tableBody.appendChild(newRow);
            // updateRowNumbers();
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

            const grouped = params_all.wrds_mat_compensation.reduce((acc, item) => {
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
                const item = params_all.wrds_mat_compensation.find(it => it.mat_code === code);
                newRow.querySelector('.mat-name').value = item?.mat_name || '';
                newRow.querySelector('.mat_id').value = item?.mat_id || '';
            });
            bindDetailBathStangListeners();
            updateTotalBath();
        }

        delBtn.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBody.querySelectorAll('.product-list-gl input:checked');
            if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
            checked.forEach(cb => cb.closest('tr')?.remove());
            updateRowNumbers();
            bindDetailBathStangListeners();
            updateTotalBath();
        });

        addBtn.addEventListener('click', e => {
            e.preventDefault();
            createRow();
        });

        function updateTotalBath() {
            const rows = document.querySelectorAll('table tbody tr.product-list-gl');
            let totalBath = 0;
            let totalStang = 0;
            rows.forEach(row => {
                const bathInput = row.querySelector('.detail_bath');
                if (bathInput) {
                    const bath = parseFloat(bathInput.value.replace(/,/g, '')) || 0;
                    totalBath += bath;
                }
                const stangInput = row.querySelector('.detail_stang');
                if (stangInput) {
                    const stang = parseFloat(stangInput.value.replace(/,/g, '')) || 0;
                    totalStang += stang;
                }
            });

            // ถ้า totalStang >= 100 ให้ยกไปบวกใน totalBath
            if (totalStang >= 100) {
                const addBath = Math.floor(totalStang / 100);
                totalBath += addBath;
                totalStang = totalStang % 100;
            }
            // รวมยอดบาทและสตางค์เป็นยอดเดียว เช่น 1234.56
            const totalSum = Number(totalBath) + (Number(totalStang) / 100);
            const totalBathText = numberToThaiText(totalSum);
            const totalBathTextElement = document.getElementById('compensation_total_bath_text');
            const totalBathElement = document.getElementById('compensation_total_bath');
            const totalStangElement = document.getElementById('compensation_total_stang');

            if (totalBathTextElement) {
                totalBathTextElement.value = totalBathText;
            }
            if (totalBathElement) {
                // แสดงผลรวมเป็น x,xxx.xx เสมอ
                totalBathElement.value = Number(totalBath).toLocaleString('en-US');
            }
            if (totalStangElement) {
                totalStangElement.value = totalStang.toLocaleString('en-US');
            }

            let groupTotal = 0;
            try {
                const groupTotalElement = document.getElementById('total_bath');
                const groupStangElement = document.getElementById('total_stang');
                if (groupTotalElement && groupStangElement) {
                    const groupBath = parseFloat(groupTotalElement.value.replace(/,/g, '')) || 0;
                    const groupStang = parseFloat(groupStangElement.value.replace(/,/g, '')) || 0;
                    groupTotal = groupBath + (groupStang / 100);
                }
            } catch (e) {
                groupTotal = 0;
            }

            // เปรียบเทียบผลรวม
            const isNotEqual = Math.abs(totalSum - groupTotal) > 0.009; // เผื่อจุดทศนิยม
            const errorStyle = 'border: 1px solid red; color: red;';
            const normalStyle = '';
            if (totalBathElement) totalBathElement.style = isNotEqual ? errorStyle : normalStyle;
            if (totalStangElement) totalStangElement.style = isNotEqual ? errorStyle : normalStyle;
            if (totalBathTextElement) totalBathTextElement.style = isNotEqual ? errorStyle : normalStyle;
        }

        createRow();
        updateRowNumbers();

        // เพิ่ม event listener ให้ช่อง .detail_bath และ .detail_stang ทุกช่อง
        function bindDetailBathStangListeners() {
            document.querySelectorAll('.detail_bath, .detail_stang').forEach(input => {
                input.removeEventListener('input', updateTotalBath); // ป้องกันซ้ำ
                input.addEventListener('input', updateTotalBath);
            });
        }
        bindDetailBathStangListeners();
        // กรณีมีการเพิ่มแถวใหม่ ให้ bind ใหม่หลังจากสร้างแถว
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                setTimeout(bindDetailBathStangListeners, 100); // รอ DOM update
            });
        }

        const m = data.main_data;
        const d = data.detail_data;
        const p = data.personal_data;
        const mp = data.main_data_plan;
        const cp = data.child_data_plan;

        if (m.plan_id) {
            $('.has_plan_id').show();
        }
        if (m.expenses_id) {
            const expensesId = m.expenses_id;
            if (expensesId) {
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
                console.log(data);

                const m = data.main_data;
                const mp = data.main_data_plan;
                const cp = data.child_data_plan;
                const tvg = data.traveling_group_data || [];

                $('#expenses_id').val(m.id || '');
                $('.expenses_number').text(m.expenses_number || '');
                if (m.expenses_group != 3) {

                    if (expensesId) {
                        if (m.is_type_req == 1) {
                            $('.has_expenses_id').show();
                        }
                        if (m.is_type_req == 2) {
                            $('.has_expenses_id_type_2').show();
                        }
                    }

                    if (m.is_type_req == 2) {
                        $('#fund_select_label_noplan_2').val(m.fund || '');
                        $('#project_select_label_noplan_2').val(m.project || '');
                        $('#expenses_learn_2').val(m.expenses_learn || '');
                        $('#expenses_code_2').val(m.expenses_code || '');
                        $('[name="expenses_date_2"]').val(formatThaiDate(m.expenses_date) || '');
                        if (m.expenses_group == 1) {
                            $('#text_approve_2').val(m.text_approve || '');
                        } else {
                            $('.one-person').hide();
                        }
                        switch (m.expenses_start_location) {
                            case 'address':
                                $('#flexRadioDefault1_start_2').prop('checked', true);
                                break;
                            case 'office':
                                $('#flexRadioDefault2_start_2').prop('checked', true);
                                break;
                            case 'thailand':
                                $('#flexRadioDefault3_start_2').prop('checked', true);
                                break;
                        }
                        switch (m.expenses_end_location) {
                            case 'address':
                                $('#flexRadioDefault1_end_2').prop('checked', true);
                                break;
                            case 'office':
                                $('#flexRadioDefault2_end_2').prop('checked', true);
                                break;
                            case 'thailand':
                                $('#flexRadioDefault3_end_2').prop('checked', true);
                                break;
                        }
                        $('[name="expenses_start_address_2"]').text(m.expenses_start_address || '');
                        $('[name="expenses_end_address_2"]').text(m.expenses_end_address || '');
                        $('[name="expenses_date_2"]').val(formatThaiDate(m.expenses_date) || '');
                        $('[name="expenses_start_date_2"]').val(formatThaiDatetime(m.expenses_start_date) || '');
                        $('[name="expenses_end_date_2"]').val(formatThaiDatetime(m.expenses_end_date) || '');
                        $('#position_withdraw_2').val(m.position_withdraw || '');
                        $('#expenses_allowance_2').val(m.expenses_allowance ? parseFloat(m.expenses_allowance).toFixed(2) : '');
                        $('#expenses_allowance_days_2').val(m.expenses_allowance_days ? parseFloat(m.expenses_allowance_days).toFixed(1) : '');
                        $('#expenses_allowance_total_2').val(m.expenses_allowance_total ? parseFloat(m.expenses_allowance_total).toFixed(2) : '');
                        $('#expenses_accommodation_2').val(m.expenses_accommodation ? parseFloat(m.expenses_accommodation).toFixed(2) : '');
                        $('#expenses_accommodation_days_2').val(m.expenses_accommodation_days ? parseFloat(m.expenses_accommodation_days).toFixed(1) : '');
                        $('#expenses_accommodation_total_2').val(m.expenses_accommodation_total ? parseFloat(m.expenses_accommodation_total).toFixed(2) : '');
                        $('#expenses_transportation_2').val(m.expenses_transportation ? parseFloat(m.expenses_transportation).toFixed(2) : '');
                        $('#expenses_transportation_total_2').val(m.expenses_transportation_total ? parseFloat(m.expenses_transportation_total).toFixed(2) : '');
                        $('#expenses_moving_2').val(m.expenses_moving ? parseFloat(m.expenses_moving).toFixed(2) : '');
                        $('#expenses_moving_total_2').val(m.expenses_moving_total ? parseFloat(m.expenses_moving_total).toFixed(2) : '');
                        if (m.expenses_vehicle == 'car') {
                            $('#expenses_vehicle_2').prop('checked', true);
                            $('.display-vehicle').show();
                        } else if (m.expenses_vehicle == 'motorcycle') {
                            $('#expenses_motorcycle_2').prop('checked', true);
                            $('.display-vehicle').show();
                        } else {
                            $('.display-vehicle').hide();
                        }
                        $('#expenses_vehicle_number_2').val(m.expenses_vehicle_number || '');
                        $('#expenses_other_2').val(m.expenses_other ? parseFloat(m.expenses_other).toFixed(2) : '');
                        $('#expenses_other_total_2').val(m.expenses_other_total ? parseFloat(m.expenses_other_total).toFixed(2) : '');
                        $('#expenses_food_per_meal_2').val(m.expenses_food_per_meal ? parseFloat(m.expenses_food_per_meal).toFixed(2) : '');
                        $('#expenses_food_meals_2').val(m.expenses_food_meals ? parseFloat(m.expenses_food_meals).toFixed(1) : '');
                        $('#expenses_food_total_2').val(m.expenses_food_total ? parseFloat(m.expenses_food_total).toFixed(2) : '');
                        $('#expenses_note_2').text(m.expenses_note || '');
                        $('#expenses_total_2').val(m.expenses_total ? parseFloat(m.expenses_total).toFixed(2) : '');
                        $('#expenses_total_text_2').val(numberToThaiText(m.expenses_total));
                        document.getElementById('expenses_transportation_remark_2').value = m.expenses_transportation_remark || '';
                        if (m.expenses_vehicle != '') {
                            $('.has_vehicle').show();
                            const tbody = document.getElementById("list-details-2");
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
                            document.getElementById("total_amount_details_2").value =
                                total.toLocaleString(undefined, { minimumFractionDigits: 2 });

                            document.getElementById("total_text_details_2").value = convertNumberToThaiText(total);
                        } else {
                            $('.has_vehicle').hide();
                        }
                        // ✅ toggle ตาราง
                        const ifYes = document.getElementById("ifYes_2");
                        if (m.expenses_group === "2") {
                            ifYes.style.display = "block";
                        } else {
                            ifYes.style.display = "none";
                        }

                        // ✅ render group data
                        const tbody_group = document.querySelector(".group-people-listgroup-people-list-2").parentNode;
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
                        document.getElementById("expenses_group_total_2").value =
                            groupTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });

                        document.getElementById("expenses_group_total_text_2").value = convertNumberToThaiText(groupTotal);
                    }
                    if (m.is_type_req == 1) {
                        $('#fund_select_label_noplan').val(m.fund || '');
                        $('#project_select_label_noplan').val(m.project || '');
                        $('#expenses_learn').val(m.expenses_learn || '');
                        $('#expenses_code').val(m.expenses_code || '');
                        $('[name="expenses_date"]').val(formatThaiDate(m.expenses_date) || '');
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
                        document.getElementById('expenses_transportation_remark').value = m.expenses_transportation_remark || '';
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
                    }
                } else {
                    $('.group_type_3').show();
                    $('#link_group_type_3').attr('href', `travel-expenses-money-multi.php?id=${expensesId}&is_approve=0&is_action=view`);
                }
            }

        }

        document.getElementById('compensation_form_name').value = m.compensation_form_name || '';
        setupThaiDateTimePicker("#start_date", m.start_date, true);
        setupThaiDateTimePicker("#end_date", m.end_date, true);

        document.getElementById('depart_code').value = m.depart_code || '';
        document.getElementById('full_name').value = m.full_name || '';
        document.getElementById('position_name').value = m.position_name || '';
        document.getElementById('level_name').value = m.level_name || '';
        document.getElementById('affiliation_name').value = m.affiliation_name || '';

        $('.plan_number').text(m.plan_number || '');
        // เลือกเงินทุน
        if (mp.fund) {
            $('#fund_select_label').val(
                mp.fund.replace(/<[^>]+>/g, '')
            );
            $('#fund_html_label').html('');
        } else {
            $('#fund_select_label').val('เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)');
            $('#fund_html_label').html('<span style="color:red;">*ไม่พบเงินทุน</span>');
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

        //รายละเอียด form กรอก
        if (m.fund_id) {
            $('.have-fund').show();
            $('#fund_select_text').val(m.fund || '');
            $('#project_select_text').val(m.project || '');
        }
        $('#compensation_type_name_text').val(m.compensation_type_name || '');
        $('#parent_compensation_type_name_text').val(m.parent_compensation_type_name || '');
        $('#req_number_withdraw').val(m.req_number_withdraw || '');
        $('#req_number_pay').val(m.req_number_pay || '');
        $('#req_payee_name').val(m.req_payee_name || '');
        $('#req_position_name').val(m.req_position_name || '');
        $('#req_affiliation_name').val(m.req_affiliation_name || '');
        setupThaiDateTimePicker("#compensation_date", m.compensation_date, false);
        // $('#expenses_number').val(m.expenses_number || '');
        $('#req_address').text(m.req_address || '');


        if (p.length > 0) {
            $('#personal-table').show();
            $('.is_no_sap').hide();

            const tbody_personal = document.querySelector(".personal-list").parentNode;
            tbody_personal.innerHTML = ""; // ล้างก่อน
            let personalTotal = 0;
            let personalbath = 0;
            let personalstang = 0;
            p.forEach((item, index) => {

                // รวมยอดหลัก (บาท) และเศษสตางค์
                personalTotal += Number(item.personal_bath || 0) + Number(item.personal_stang || 0) / 100;
                personalbath += Number(item.personal_bath || 0);
                personalstang += Number(item.personal_stang || 0);

                // ถ้า personalstang เกิน 100 ให้แปลงเป็นบาท
                if (personalstang >= 100) {
                    const addBath = Math.floor(personalstang / 100);
                    personalbath += addBath;
                    personalstang = personalstang % 100;
                }

                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="text-center">${item.code || ''}</td>
                    <td class="text-left text-wrap" height="100px">
                        <p style="line-height: 1;" class="personal-name">ชื่อ : ${item.name || ''}</p>
                        <p style="line-height: 1;" class="personal-address">ที่อยู่ : ${item.address || ''} ${item.road || ''} ${item.sub_district || ''} ${item.amphur || ''} ${item.province || ''} ${item.postcode || ''}</p>
                    </td>
                    <td class="text-center">${Number(item.personal_bath || 0).toLocaleString('en-US')}</td>
                    <td class="text-center">${item.personal_stang}</td>
                `;
                tbody_personal.appendChild(tr);
            });

            // ✅ update total
            document.getElementById("personal_total_bath").value =
                personalbath.toLocaleString(undefined);
            document.getElementById("personal_total_stang").value =
                personalstang.toLocaleString(undefined);

            document.getElementById("personal_total_bath_text").value = convertNumberToThaiText(personalTotal);
        }
        //ตารางหลักฐานการจ่ายเงินค่าใช้จ่าย
        const tbody_group = document.querySelector(".product-list").parentNode;
        tbody_group.innerHTML = ""; // ล้างก่อน
        let groupTotal = 0;
        let groupbath = 0;
        let groupstang = 0;

        d.forEach((item, index) => {

            // รวมยอดหลัก (บาท) และเศษสตางค์
            groupTotal += Number(item.bath || 0) + Number(item.stang || 0) / 100;
            groupbath += Number(item.bath || 0);
            groupstang += Number(item.stang || 0);

            // ถ้า groupstang เกิน 100 ให้แปลงเป็นบาท
            if (groupstang >= 100) {
                const addBath = Math.floor(groupstang / 100);
                groupbath += addBath;
                groupstang = groupstang % 100;
            }

            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td class="text-left text-wrap">${item.detail}</td>
            <td class="text-center">${Number(item.bath || 0).toLocaleString('en-US')}</td>
            <td class="text-center">${item.stang}</td>
        `;
            tbody_group.appendChild(tr);
        });

        // ✅ update total
        document.getElementById("total_bath").value =
            groupbath.toLocaleString(undefined);
        document.getElementById("total_stang").value =
            groupstang.toLocaleString(undefined);

        document.getElementById("total_bath_text").value = convertNumberToThaiText(groupTotal);

        //ปรับเพิ่ม
        if (!m.plan_id) {
            $('.has_plan_id').hide();
        }

        // เลขที่สัญญา
        document.getElementById('plan_number').value = m.plan_number || '';
        document.getElementById('plan_date').value = formatThaiDate(m.plan_date) || '';
        document.getElementById('plan_total').value = m.plan_total || '';

        // ผู้ใช้งานต้องการ return/withdraw
        if (m.compensation_action === 'return') {
            document.getElementById('compensation_return').checked = true;
        } else if (m.compensation_action === 'withdraw') {
            document.getElementById('compensation_withdraw').checked = true;
        }
        document.getElementById('compensation_amount').value = Number(m.compensation_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

        // ยืนยัน
        if (m.is_confirm === "1") {
            document.getElementById('is_confirm').checked = true;
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
                            ${item.approve_status === 'finance_approve' ? 'อนุมัติ' :
                            item.approve_status === 'audit_approve' ? 'ผ่านการตรวจสอบแล้ว' :
                                item.approve_status === 'finance_reject' ? 'ไม่อนุมัติ' :
                                    item.approve_status === 'audit_reject' ? 'ไม่ผ่านการตรวจสอบ' :
                                        item.approve_status === 'finance_check' ? 'ผ่านการตรวจสอบแล้ว' :
                                            item.approve_status === 'cho_reject' ? 'ไม่ผ่านการตรวจสอบ' :
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

        if (data.gl_detail_data && Array.isArray(data.gl_detail_data)) {
            const tableBody = document.querySelector('#gl-approval-table tbody');
            tableBody.innerHTML = ''; // ล้างข้อมูลเดิมก่อน
            if (data.gl_detail_data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td>`;
                tableBody.appendChild(row);
            } else {
                let glTotal = 0;
                let glbath = 0;
                let glstang = 0;
                data.gl_detail_data.forEach((item, index) => {
                    glTotal += Number(item.bath || 0) + Number(item.stang || 0) / 100;
                    glbath += Number(item.bath || 0);
                    glstang += Number(item.stang || 0);

                    // ถ้า glstang เกิน 100 ให้แปลงเป็นบาท
                    if (glstang >= 100) {
                        const addBath = Math.floor(glstang / 100);
                        glbath += addBath;
                        glstang = glstang % 100;
                    }
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${item.mat_code}</td>
                        <td class="text-left">${item.mat_name}</td>
                        <td class="text-center">${Number(item.bath || 0).toLocaleString('en-US')}</td>
                        <td class="text-center">${item.stang}</td>
                    `;
                    tableBody.appendChild(row);
                });
                document.getElementById("gl_total_bath").value =
                    glbath.toLocaleString(undefined);
                document.getElementById("gl_total_stang").value =
                    glstang.toLocaleString(undefined);

                document.getElementById("gl_total_bath_text").value = convertNumberToThaiText(glTotal);
            }
        }else{
            $('#div-gl-approval-table').hide();
        }

        const approveCheckbox = document.getElementById('approve');
        const rejectCheckbox = document.getElementById('reject');
        const remarkTextarea = document.getElementById('remark');
        const checkCheckbox = document.getElementById('check');
        const RejectCheckCheckbox = document.getElementById('reject_check');
        const CheckAndApproveCheckbox = document.getElementById('check_and_approve');
        var permissionAPHOP = params_all.position_list_id.some(id => ['6'].includes(id));
        // remarkTextarea.disabled = true;

        approveCheckbox.addEventListener('change', () => {
            if (approveCheckbox.checked) {
                rejectCheckbox.checked = false;
                checkCheckbox.checked = false;
                RejectCheckCheckbox.checked = false;
                CheckAndApproveCheckbox.checked = false;
                $('#hide_text_approve').hide();
            } else if (!rejectCheckbox.checked) {
                $('#hide_text_approve').show();
            }
            if (!permissionAPHOP) {
                $('#compensation-detail-table').show();
            }
        });

        rejectCheckbox.addEventListener('change', () => {
            if (rejectCheckbox.checked) {
                approveCheckbox.checked = false;
                checkCheckbox.checked = false;
                RejectCheckCheckbox.checked = false;
                CheckAndApproveCheckbox.checked = false;
                $('#hide_text_approve').show();
            } else if (!approveCheckbox.checked) {
                $('#hide_text_approve').show();
            }
            $('#compensation-detail-table').hide();
        });

        checkCheckbox.addEventListener('change', () => {
            if (checkCheckbox.checked) {
                approveCheckbox.checked = false;
                rejectCheckbox.checked = false;
                RejectCheckCheckbox.checked = false;
                CheckAndApproveCheckbox.checked = false;
                $('#hide_text_approve').hide();
            } else if (!rejectCheckbox.checked) {
                $('#hide_text_approve').show();
            }
            $('#compensation-detail-table').hide();
        });

        RejectCheckCheckbox.addEventListener('change', () => {
            if (RejectCheckCheckbox.checked) {
                approveCheckbox.checked = false;
                rejectCheckbox.checked = false;
                checkCheckbox.checked = false;
                CheckAndApproveCheckbox.checked = false;
                $('#hide_text_approve').show();
            } else if (!approveCheckbox.checked) {
                $('#hide_text_approve').show();
            }
            $('#compensation-detail-table').hide();
        });

        CheckAndApproveCheckbox.addEventListener('change', () => {
            if (CheckAndApproveCheckbox.checked) {
                approveCheckbox.checked = false;
                rejectCheckbox.checked = false;
                checkCheckbox.checked = false;
                RejectCheckCheckbox.checked = false;
                $('#hide_text_approve').hide();
            } else if (!rejectCheckbox.checked) {
                $('#hide_text_approve').show();
            }
            $('#compensation-detail-table').show();
        });

        const btnSubmit = document.getElementById('btn-update');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', updateData);
        }

        function getListDetailsData() {
            if (((typeof CheckAndApproveCheckbox !== 'undefined' && CheckAndApproveCheckbox.checked) || (typeof approveCheckbox !== 'undefined' && approveCheckbox.checked)) && !permissionAPHOP) {

                const rows = document.querySelectorAll('tr.product-list-gl');
                const data = [];

                rows.forEach(row => {
                    const select_mat_code = $(row.querySelector('.select-mat-code')).val();
                    const mat_name = row.querySelector('.mat-name')?.value;
                    const mat_id = parseInt(row.querySelector('.mat_id')?.value) || '';
                    const detail_bath = row.querySelector('.detail_bath')?.value?.replace(/,/g, '').trim() || '';
                    const detail_stang = row.querySelector('.detail_stang')?.value?.replace(/,/g, '').trim() || '';

                    if (mat_id) {
                        data.push({
                            mat_id,
                            select_mat_code,
                            mat_name,
                            detail_bath: detail_bath ? parseFloat(detail_bath) || 0 : 0,
                            detail_stang: detail_stang ? parseFloat(detail_stang) || 0 : 0
                        });
                    }
                });
                return data;
            } else {
                return [];
            }
        }

        async function updateData() {
            try {
                // Disable submit button
                const btnSubmit = document.getElementById('btn-update');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                }

                const checkboxGroups = ['.approve, .reject, .check, .reject_check, .check_and_approve'];

                const valid = validateRequiredFields(checkboxGroups);
                if (!valid) return;
                if (!validateRejectRemark()) return;
                if (!validateGLDetail()) return;

                const data_sent = {
                    main_data: {
                        approve: approveCheckbox.checked,
                        reject: rejectCheckbox.checked,
                        remark: remarkTextarea.value.trim(),
                        check: checkCheckbox.checked,
                        reject_check: RejectCheckCheckbox.checked,
                        check_and_approve: CheckAndApproveCheckbox.checked,
                        user_code: data_user.user_code,
                    },
                    gl_detail_data: getListDetailsData(),
                };

                window.showLoading();

                try {
                    const response = await fetch(`../controllers/withdraws/wrd_compensation_controller.php?action=approve_one&id=${compensationId}&position_code=${posCode}`, {
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
                            window.location.href = 'money-order-approvel-list.php';
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
            if (rejectCheckbox.checked || RejectCheckCheckbox.checked) {
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

        function validateGLDetail() {
            // เปลี่ยนเงื่อนไข: ถ้า CheckAndApproveCheckbox หรือ approveCheckbox ถูกติ๊ก
            if (((typeof CheckAndApproveCheckbox !== 'undefined' && CheckAndApproveCheckbox.checked) || (typeof approveCheckbox !== 'undefined' && approveCheckbox.checked)) && !permissionAPHOP) {
                const rows = document.querySelectorAll('tr.product-list-gl');
                let hasDetailList = false;
                rows.forEach(row => {
                    const detail_list = row.querySelector('.mat_id')?.value?.trim() || '';
                    if (detail_list) hasDetailList = true;
                });
                if (!hasDetailList) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากรอกรายละเอียดใบสำคัญจ่ายอย่างน้อย 1 รายการ'
                    });
                    if (btnSubmit) btnSubmit.disabled = false;
                    window.hideLoading();
                    return false;
                }

                let totalBath = 0, totalStang = 0;
                rows.forEach(row => {
                    const bathInput = row.querySelector('.detail_bath');
                    if (bathInput) {
                        const bath = parseFloat(bathInput.value.replace(/,/g, '')) || 0;
                        totalBath += bath;
                    }
                    const stangInput = row.querySelector('.detail_stang');
                    if (stangInput) {
                        const stang = parseFloat(stangInput.value.replace(/,/g, '')) || 0;
                        totalStang += stang;
                    }
                });
                if (totalStang >= 100) {
                    const addBath = Math.floor(totalStang / 100);
                    totalBath += addBath;
                    totalStang = totalStang % 100;
                }
                const totalSum = Number(totalBath) + (Number(totalStang) / 100);
                let groupTotal = 0;
                try {
                    const groupTotalElement = document.getElementById('total_bath');
                    const groupStangElement = document.getElementById('total_stang');
                    if (groupTotalElement && groupStangElement) {
                        const groupBath = parseFloat(groupTotalElement.value.replace(/,/g, '')) || 0;
                        const groupStang = parseFloat(groupStangElement.value.replace(/,/g, '')) || 0;
                        groupTotal = groupBath + (groupStang / 100);
                    }
                } catch (e) {
                    groupTotal = 0;
                }
                if (Math.abs(totalSum - groupTotal) > 0.009) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ถูกต้อง',
                        text: 'ยอดรวมไม่ตรงกับยอดรวมของตารางรายละเอียดค่าใช้จ่าย'
                    });
                    if (btnSubmit) btnSubmit.disabled = false;
                    window.hideLoading();
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

