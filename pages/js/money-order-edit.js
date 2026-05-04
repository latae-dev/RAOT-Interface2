import { getAllParams } from './withdraw_parameter.js';
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';
import { getAllCompensationExpenseType } from './withdraw_compensation_expense_types.js';
import { getAllCompensationExpenseTypeParent } from './withdraw_compensation_expense_types.js';

(async () => {
    try {
        // ✅ ดึง id จาก URL
        const urlParams = new URLSearchParams(window.location.search);
        const compensationId = urlParams.get('id');
        const fund_all = await getAllFund();
        const project_all = await getAllProject();
        const compensation_expense_type_all = await getAllCompensationExpenseType();
        const compensation_expense_type_parent_all = await getAllCompensationExpenseTypeParent();
        let projectDefaultId = null;
        let compensationParentDefaultId = null;
        const params_all = await getAllParams();
        console.log(params_all);

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

        /* ประเภทค่าตอบแทนและค่าใช้จ่าย */
        const compensationExpenseSource = compensation_expense_type_all.compensation_expense_types || [];
        if (!Array.isArray(compensationExpenseSource) || compensationExpenseSource.length === 0) {
            console.error('❌ ไม่พบข้อมูล:', compensationExpenseSource);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบข้อมูลประเภทค่าตอบแทนและค่าใช้จ่าย กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

        $('#compensation_expense_select').empty(); // ล้าง option เดิม
        $('#compensation_expense_select').append(
            $('<option>', {
                value: '',
                text: 'กรุณาเลือกประเภทค่าตอบแทนและค่าใช้จ่าย'
            })
        );

        compensationExpenseSource.forEach(expense => {
            $('#compensation_expense_select').append(
                $('<option>', {
                    value: expense.id,
                    text: expense.name,
                    'data-sap': expense.is_sap
                })
            );
        });

        $('#compensation_expense_select').select2({
            placeholder: 'กรุณาเลือกประเภทค่าตอบแทนและค่าใช้จ่าย',
            escapeMarkup: markup => markup,
            templateResult: data => data.text,
            templateSelection: data => data.text
        });

        $('#compensation_expense_select').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const sapValue = selectedOption.data('sap');
            if (sapValue == 1) {
                $('.is_sap').show();
                $('.is_no_sap').addClass('d-none');
                $('#req_payee_name').val('');
                $('#req_position_name').val('');
                $('#req_affiliation_name').val('');
                $('#req_address').val('');
            } else {
                $('.is_sap').hide();
                $('.is_no_sap').removeClass('d-none');
            }
            $('.personal-list').empty();
            $('.product-list').empty();
            createRowPersonal();
            createRow();
        });
        /* ประเภทค่าตอบแทนและค่าใช้จ่าย */

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

        const approveButtons = `
        <a href="money-order-list.php" class="btn btn-warning m-1">
            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
        </a>
        <button id="btn-update" type="button" class="btn btn-primary m-1">
            <i class="bi bi-save"></i> บันทึกการแก้ไข
        </button>
    `;
        document.getElementById('button-container').innerHTML = approveButtons;

        // event สร้างตาราง
        const addBtn = document.querySelector('.addTable');
        const delBtn = document.querySelector('.delTable');
        const tableBody = document.querySelector('#evidence-table table tbody');

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
            tableBody.querySelectorAll('tr.product-list').forEach((row) => {
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
            newRow.classList.add('product-list');
            newRow.innerHTML = `
                <input class="form-control mat_id" type="hidden">
                <td class="group-checkbox first-group-row"><input class="form-check-input" type="checkbox" aria-label="..."></td>
                <td>
                    <select class="form-control select-mat-code" style="width:100%"></select>
                </td>
                <td><input class="form-control mat-name" type="text" placeholder="รายการ" readonly></td>
                <td><textarea rows="3" class="form-control detail_list" placeholder="กรุณากรอก รายละเอียดใบสำคัญจ่าย"></textarea></td>
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
            bindDetailBathStangListeners();
            updateTotalBath();
        }

        delBtn.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBody.querySelectorAll('.product-list input:checked');
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
            const rows = document.querySelectorAll('table tbody tr.product-list');
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
            const totalBathTextElement = document.getElementById('total_bath_text');
            if (totalBathTextElement) {
                totalBathTextElement.value = totalBathText;
            }
            const totalBathElement = document.getElementById('total_bath');
            if (totalBathElement) {
                // แสดงผลรวมเป็น x,xxx.xx เสมอ
                totalBathElement.value = Number(totalBath).toLocaleString('en-US');
            }
            const totalStangElement = document.getElementById('total_stang');
            if (totalStangElement) {
                totalStangElement.value = totalStang.toLocaleString('en-US');
            }
            calcCompensationAmount();
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
        // event สร้างตาราง

        const addBtnPersonal = document.querySelector('.addTable-personal');
        const delBtnPersonal = document.querySelector('.delTable-personal');
        const tableBodyPersonal = document.querySelector('#personal-table table tbody');

        const checkAllBoxPersonal = document.querySelector('.check-all-personal');
        if (checkAllBoxPersonal) {
            checkAllBoxPersonal.addEventListener('change', function () {
                const checked = this.checked;
                tableBodyPersonal.querySelectorAll('.personal-checkbox input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }

        function createRowPersonal() {
            const newRow = document.createElement('tr');
            newRow.classList.add('personal-list');
            const isFirstRow = tableBodyPersonal.querySelectorAll('tr.personal-list').length === 0;
            const checkboxHtml = isFirstRow
                ? ''
                : '<input class="form-check-input" type="checkbox" aria-label="...">';
            const firstRowClass = isFirstRow ? ' first-group-row' : '';
            newRow.innerHTML = `
                <input class="form-control personal_id" type="hidden">
                <td class="personal-checkbox${firstRowClass}">${checkboxHtml}</td>
                <td><select class="form-control select-personal" data-trigger required style="width: 100%"></select></td>
                <td height="150px">
                    <p style="line-height: 1;" class="personal-name"></p>
                    <p style="line-height: 1;" class="personal-position"></p>
                    <p style="line-height: 1;" class="personal-depart"></p>
                    <p style="line-height: 1;" class="personal-address"></p>
                </td>
                <td><input class="form-control bath-personal" type="number" placeholder="00"></td>
                <td><input class="form-control stang-personal" type="number" max="99" maxlength="2" min="0" placeholder="00" oninput="if(this.value.length>2)this.value=this.value.slice(0,2);if(+this.value>99)this.value=99;"></td>
            `;
            tableBodyPersonal.appendChild(newRow);
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

            const grouped = params_all.users_all.map(item => ({
                id: item.id, // ใช้ค่า id จาก item.id
                text: `${item.user_code}`,
                name: `${item.user_fname} ${item.user_lname}`
            }));

            const dataForSelect2 = [
                { id: '', text: 'เลือกรายการ', disabled: true },
                ...grouped
            ];

            const selectPersonal = newRow.querySelector('.select-personal');
            $(selectPersonal).select2({
                data: dataForSelect2,
                placeholder: 'เลือกรายการ',
                allowClear: false,
                width: 'resolve',
                templateResult: data => data.text,
                templateSelection: data => data.id ? data.text : 'เลือกรายการ'
            }).val('').trigger('change');

            $(selectPersonal).on('change', function () {
                const selectedId = $(this).val();
                const selectedUser = params_all.users_all.find(user => user.id == selectedId);
                newRow.querySelector('.personal_id').value = selectedUser?.id || '';
                newRow.querySelector('.personal-name').innerHTML = 'ชื่อ : ' + selectedUser.user_fname + ' ' + selectedUser.user_lname;
                newRow.querySelector('.personal-position').innerHTML = 'ตำแหน่ง : ' + selectedUser.position_name;
                newRow.querySelector('.personal-depart').innerHTML = 'หน่วยงาน : ' + selectedUser.depart_name;
                newRow.querySelector('.personal-address').innerHTML = 'ที่อยู่ : ' + selectedUser.user_address;
            });
            bindDetailBathStangListenersPersonal();
            updateTotalBathPersonal();
        }

        delBtnPersonal.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBodyPersonal.querySelectorAll('.personal-list input:checked');
            if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
            checked.forEach(cb => cb.closest('tr')?.remove());
            bindDetailBathStangListenersPersonal();
            updateTotalBathPersonal();
        });

        addBtnPersonal.addEventListener('click', e => {
            e.preventDefault();
            createRowPersonal();
        });

        function updateTotalBathPersonal() {
            const rows = document.querySelectorAll('table tbody tr.personal-list');
            let totalBath = 0;
            let totalStang = 0;
            rows.forEach(row => {
                const bathInput = row.querySelector('.bath-personal');
                if (bathInput) {
                    const bath = parseFloat(bathInput.value.replace(/,/g, '')) || 0;
                    totalBath += bath;
                }
                const stangInput = row.querySelector('.stang-personal');
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
            const totalBathTextElement = document.getElementById('personal_total_bath_text');
            if (totalBathTextElement) {
                totalBathTextElement.value = totalBathText;
            }
            const totalBathElement = document.getElementById('personal_total_bath');
            if (totalBathElement) {
                // แสดงผลรวมเป็น x,xxx.xx เสมอ
                totalBathElement.value = Number(totalBath).toLocaleString('en-US');
            }
            const totalStangElement = document.getElementById('personal_total_stang');
            if (totalStangElement) {
                totalStangElement.value = totalStang.toLocaleString('en-US');
            }
            // calcCompensationAmount();
        }

        // เพิ่ม event listener ให้ช่อง .bath-personal และ .stang-personal ทุกช่อง
        function bindDetailBathStangListenersPersonal() {
            document.querySelectorAll('.bath-personal, .stang-personal').forEach(input => {
                input.removeEventListener('input', updateTotalBathPersonal); // ป้องกันซ้ำ
                input.addEventListener('input', updateTotalBathPersonal);

                // เพิ่ม event sync ไปยัง detail_bath/detail_stang
                input.removeEventListener('input', syncPersonalToDetail); // ป้องกันซ้ำ
                input.addEventListener('input', syncPersonalToDetail);
            });
        }

        // ฟังก์ชัน sync ข้อมูลจาก personal ไป detail
        function syncPersonalToDetail(e) {
            const row = e.target.closest('tr.personal-list');
            if (!row) return;
            const productRows = $('tr.product-list');

            // ลบ comma ออกจากค่าก่อนใส่ input number
            const bathPersonal = ($('#personal_total_bath').val() || '').replace(/,/g, '');
            const stangPersonal = ($('#personal_total_stang').val() || '').replace(/,/g, '');

            $(productRows).find('.detail_bath').val(bathPersonal);
            $(productRows).find('.detail_stang').val(stangPersonal);
            updateTotalBath();
        }

        // ✅ ดึงข้อมูล user ปัจจุบัน
        const data_user = params_all.current_user;
        document.getElementById('depart_code').value = data_user.depart_code || '';
        document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        document.getElementById('position_name').value = data_user.position_name || '';
        document.getElementById('level_name').value = data_user.level_name || '';
        document.getElementById('affiliation_name').value = data_user.branch_name || '';

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
        const m = data.main_data;
        const d = data.detail_data;
        const p = data.personal_data;
        const mp = data.main_data_plan;
        const cp = data.child_data_plan;

        if (m.fund_id) {
            $('#fund_select').val(m.fund_id).trigger('change');
            if (m.project_id) {
                projectDefaultId = m.project_id;
            }
        }

        if (m.compensation_type) {
            $('#compensation_expense_select').val(m.compensation_type).trigger('change');
            if (m.parent_compensation_type) {
                compensationParentDefaultId = m.parent_compensation_type;
            }
        }

        document.getElementById('compensation_form_name').value = m.compensation_form_name || '';
        $('[name="start_date"]').val(formatThaiDatetime(m.start_date || ''));
        $('[name="end_date"]').val(formatThaiDatetime(m.end_date || ''));
        $('[name="compensation_date"]').val(formatThaiDate(m.compensation_date || ''));

        document.getElementById('depart_code').value = m.depart_code || '';
        document.getElementById('full_name').value = m.full_name || '';
        document.getElementById('position_name').value = m.position_name || '';
        document.getElementById('level_name').value = m.level_name || '';
        document.getElementById('affiliation_name').value = m.affiliation_name || '';

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

        //รายละเอียด form กรอก
        $('#fund_select_text').val(m.fund || '');
        $('#project_select_text').val(m.project || '');
        $('#compensation_type_name_text').val(m.compensation_type_name || '');
        $('#parent_compensation_type_name_text').val(m.parent_compensation_type_name || '');
        $('#req_number_withdraw').val(m.req_number_withdraw || '');
        $('#req_number_pay').val(m.req_number_pay || '');
        $('#req_payee_name').val(m.req_payee_name || '');
        $('#req_position_name').val(m.req_position_name || '');
        $('#req_affiliation_name').val(m.req_affiliation_name || '');
        // $('#expenses_number').val(m.expenses_number || '');
        $('#req_address').text(m.req_address || '');

        //ตาราง p
        if (p.length > 0) {
            $('#personal-table').show();

            const tbody_personal = document.querySelector(".personal-list").parentNode;
            tbody_personal.innerHTML = ""; // ล้างก่อน
            let personalTotal = 0;
            let personalbath = 0;
            let personalstang = 0;
            p.forEach((item, index) => {

                personalTotal += Number(item.personal_bath || 0) + Number(item.personal_stang || 0) / 100;
                personalbath += Number(item.personal_bath || 0);
                personalstang += Number(item.personal_stang || 0);

                // ถ้า personalstang เกิน 100 ให้แปลงเป็นบาท
                if (personalstang >= 100) {
                    const addBath = Math.floor(personalstang / 100);
                    personalbath += addBath;
                    personalstang = personalstang % 100;
                }

                createRowPersonal();
                const lastRow = tbody_personal.querySelector('tr.personal-list:last-child');
                $(lastRow).find('.select-personal').val(item.personal_id || '').trigger('change');
                // lastRow.querySelector('.personal-name').innerHTML = 'ชื่อ : ' + (item.user_fname || '') + ' ' + (item.user_lname || '');
                // lastRow.querySelector('.personal-position').innerHTML = 'ตำแหน่ง : ' + (item.position_name || '');
                // lastRow.querySelector('.personal-depart').innerHTML = 'หน่วยงาน : ' + (item.depart_name || '');
                // lastRow.querySelector('.personal-address').innerHTML = 'ที่อยู่ : ' + (item.user_address || '');
                lastRow.querySelector('.bath-personal').value = Number(item.personal_bath || 0).toLocaleString('en-US');
                lastRow.querySelector('.stang-personal').value = Number(item.personal_stang || 0).toLocaleString('en-US');
                bindDetailBathStangListeners();
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

            createRow();
            const lastRow = tbody_group.querySelector('tr.product-list:last-child');
            $(lastRow).find('.select-mat-code').val(item.mat_code || '').trigger('change');
            lastRow.querySelector('.detail_list').value = item.detail || '';
            lastRow.querySelector('.detail_bath').value = Number(item.bath || 0).toLocaleString('en-US');
            lastRow.querySelector('.detail_stang').value = Number(item.stang || 0).toLocaleString('en-US');
            bindDetailBathStangListeners();
        });

        // ✅ update total
        document.getElementById("total_bath").value =
            groupbath.toLocaleString(undefined);
        document.getElementById("total_stang").value =
            groupstang.toLocaleString(undefined);

        document.getElementById("total_bath_text").value = convertNumberToThaiText(groupTotal);

        //ปรับเพิ่ม
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

        // เลขที่สัญญา
        document.getElementById('plan_number').value = m.plan_number || '';
        document.getElementById('plan_date').value = m.plan_date || '';
        document.getElementById('plan_total').value = m.plan_total || '';

        // ผู้ใช้งานต้องการ return/withdraw
        if (m.compensation_action === 'return') {
            document.getElementById('compensation_return').checked = true;
        } else if (m.compensation_action === 'withdraw') {
            document.getElementById('compensation_withdraw').checked = true;
        }
        document.getElementById('compensation_amount').value = Number(m.compensation_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

        const btnSubmit = document.getElementById('btn-update');
        if (btnSubmit) {
            btnSubmit.addEventListener('click', updateData);
        }

        function getListDetailsData() {
            const rows = document.querySelectorAll('tr.product-list');
            const data = [];

            rows.forEach(row => {
                const select_mat_code = $(row.querySelector('.select-mat-code')).val();
                const mat_name = row.querySelector('.mat-name')?.value;
                const mat_id = parseInt(row.querySelector('.mat_id')?.value) || '';
                const detail_list = row.querySelector('.detail_list')?.value?.trim() || '';
                const detail_bath = row.querySelector('.detail_bath')?.value?.replace(/,/g, '').trim() || '';
                const detail_stang = row.querySelector('.detail_stang')?.value?.replace(/,/g, '').trim() || '';

                // เงื่อนไข: เฉพาะแถวที่ detail_list มีค่าเท่านั้น
                if (detail_list) {
                    data.push({
                        mat_id,
                        select_mat_code,
                        mat_name,
                        detail_list,
                        detail_bath: detail_bath ? parseFloat(detail_bath) || 0 : 0,
                        detail_stang: detail_stang ? parseFloat(detail_stang) || 0 : 0
                    });
                }
            });
            return data;
        }

        function getListPersonalData() {
            const rows = document.querySelectorAll('tr.personal-list');
            const data = [];

            rows.forEach(row => {
                const personal_id = parseInt(row.querySelector('.personal_id')?.value) || '';
                const personal_bath = row.querySelector('.bath-personal')?.value?.replace(/,/g, '').trim() || '';
                const personal_stang = row.querySelector('.stang-personal')?.value?.replace(/,/g, '').trim() || '';

                // เงื่อนไข: เฉพาะแถวที่ personal_id มีค่าเท่านั้น
                if (personal_id) {
                    data.push({
                        personal_id,
                        personal_bath: personal_bath ? parseFloat(personal_bath) || 0 : 0,
                        personal_stang: personal_stang ? parseFloat(personal_stang) || 0 : 0
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
            data['compensation'] = $("#compensation_expense_select option:selected").text();
            data['compensation_id'] = getcompensationNewValue();
            // data['compensation_parent'] = $("#compensation_expense_type_parent_select option:selected").text();
            // data['compensation_parent_id'] = getcompensationParentNewValue();
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

        function getcompensationNewValue() {
            var compensation_id = $("#compensation_expense_select").val();
            var compensation_name = $("#compensation_expense_select option:selected").text();
            return compensation_id;
        }

        function getcompensationParentNewValue() {
            var compensation_id = $("#compensation_expense_type_parent_select").val();
            var compensation_name = $("#compensation_expense_type_parent_select option:selected").text();
            return compensation_id;
        }

        function validateForm() {
            const selectedOption = $('#compensation_expense_select').find(':selected');
            const sapValue = selectedOption.data('sap');

            // วันและเวลาเริ่มต้น
            const Startdate = document.querySelector('[name="start_date"]')?.value;
            if (!Startdate) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาเริ่มต้น' });
                return false;
            }

            // วันและเวลาสิ้นสุด
            const Enddate = document.querySelector('[name="end_date"]')?.value;
            if (!Enddate) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาสิ้นสุด' });
                return false;
            }

            // ตรวจสอบกองทุน
            if (!m.plan_id && !m.expenses_id) {
                const fund = document.getElementById('fund_select').value.trim();
                if (!fund) {
                    Swal.fire({
                        title: "ข้อมูลไม่ครบถ้วน",
                        text: "กรุณาเลือกกองทุน",
                        icon: "warning"
                    });
                    return false;
                }

                // ตรวจสอบโครงการ
                const project = document.getElementById('project_select').value.trim();
                if (!project) {
                    Swal.fire({
                        title: "ข้อมูลไม่ครบถ้วน",
                        text: "กรุณาเลือกโครงการ",
                        icon: "warning"
                    });
                    return false;
                }
            }


            // ตรวจสอบประเภทค่าตอบแทนและค่าใช้จ่าย
            const compensation = document.getElementById('compensation_expense_select').value.trim();
            if (!compensation) {
                Swal.fire({
                    title: "ข้อมูลไม่ครบถ้วน",
                    text: "กรุณาเลือกประเภทค่าตอบแทนและค่าใช้จ่าย",
                    icon: "warning"
                });
                return false;
            }

            const compensationDate = document.querySelector('[name="compensation_date"]')?.value;
            if (!compensationDate) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุลงวันที่' });
                return false;
            }

            if (sapValue != 1) {
                // ตรวจสอบผู้รับเงิน
                const reqPayeeName = document.getElementById('req_payee_name')?.value;
                if (!reqPayeeName) {
                    Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกบุคคลที่ต้องการจ่ายให้' });
                    return false;
                }

                const reqAddress = document.getElementById('req_address')?.value;
                if (!reqAddress) {
                    Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกที่อยู่บุคคลที่ต้องการจ่ายให้' });
                    return false;
                }
            }

            // ตรวจสอบรายละเอียดใบสำคัญจ่าย (detail_list)
            const rows = document.querySelectorAll('tr.product-list');
            let hasDetailList = false;
            rows.forEach(row => {
                const detail_list = row.querySelector('.detail_list')?.value?.trim() || '';
                if (detail_list) hasDetailList = true;
            });
            if (!hasDetailList) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกรายละเอียดใบสำคัญจ่ายอย่างน้อย 1 รายการ'
                });
                return false;
            }

            // ตรวจสอบการยืนยันความถูกต้อง (is_confirm)
            const confirmCheckbox = document.getElementById('is_confirm');
            if (!confirmCheckbox || !confirmCheckbox.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณายืนยันว่ารายการที่กล่าวมาข้างต้นเป็นความจริง'
                });
                return false;
            }

            return true;
        }

        async function updateData() {
            try {
                // Disable submit button
                const btnSubmit = document.getElementById('btn-update');
                // if (btnSubmit) {
                //     btnSubmit.disabled = true;
                // }

                // validate form
                if (!validateForm()) return;

                const data_sent = {
                    main_data: getMainFormData(),
                    detail_data: getListDetailsData(),
                    personal_data: getListPersonalData()
                };

                window.showLoading();

                try {
                    const response = await fetch(`../controllers/withdraws/wrd_compensation_controller.php?action=edit_one&id=${compensationId}`, {
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
                            window.location.href = 'money-order-list.php';
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

