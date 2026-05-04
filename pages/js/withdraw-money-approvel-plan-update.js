import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        // ดึงข้อมูลจาก URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const planId = urlParams.get('id');

        if (!planId) {
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
        // console.log(params_all);

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

        // ดึงข้อมูลที่ต้องการดู
        try {
            const response = await fetch(`../controllers/withdraws/wrd_approvel_controller.php?action=get_one&id=${planId}`, {
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

            /* ปุ่มอนุมัติ ไม่อนุมัติ */
            // if (data.main_data.st_hf === 'waiting' && data_user.approve_finance === 'active') {
            //     const approveButtons = `
            //         <a href="withdraw-money-approvel-plan.php" class="btn btn-warning m-1">
            //             <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
            //          </a>
            //         <button id="btn-approve" type="button" class="btn btn-success m-1">
            //             <i class="bi bi-check-circle"></i> อนุมัติ
            //         </button>
            //         <button id="btn-reject" type="button" class="btn btn-danger m-1">
            //             <i class="bi bi-x-circle"></i> ไม่อนุมัติ
            //         </button>
            //     `;
            //     document.getElementById('button-container').innerHTML = approveButtons;
            // } else {
            const approveButtons = `
                    <a href="withdraw-money-approvel-plan.php" class="btn btn-warning m-1">
                        <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                     </a>
                    <button id="btn-update" type="button" class="btn btn-primary m-1">
                        <i class="bi bi-save"></i> บันทึกข้อมูล
                    </button>
                `;
            document.getElementById('button-container').innerHTML = approveButtons;
            // }
            /* ปุ่มอนุมัติ ไม่อนุมัติ */

            /* ข้อมูล main */
            document.querySelector('span[name="plan_number"]').textContent = data.main_data.plan_number || '';
            document.getElementById('plan_form_name').value = data.main_data.plan_form_name || '';
            document.querySelector('[name="plan_start"]').value = formatThaiDatetime(data.main_data.plan_start) || '';
            document.querySelector('[name="plan_end"]').value = formatThaiDatetime(data.main_data.plan_end) || '';
            document.getElementById('depart_code').value = data.main_data.depart_code || '';
            document.getElementById('full_name').value = data.main_data.full_name || '';
            document.getElementById('position_name').value = data.main_data.position_name || '';
            document.getElementById('level_name').value = data.main_data.level_name || '';
            document.getElementById('affiliation_name').value = data.main_data.affiliation_name || '';
            document.getElementById('st_hf_full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}` || '';
            document.getElementById('st_hf_date').value = formatThaiDate(new Date()) || '';
            /* ข้อมูล main */

            // เลือกกองทุน
            if (data.main_data.fund) {
                $('#fund_select_label').val(
                    data.main_data.fund.replace(/<[^>]+>/g, '')
                );
                $('#fund_html_label').html('');
            } else {
                $('#fund_select_label').val('เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)');
                $('#fund_html_label').html('<span style="color:red;">*ไม่พบกองทุน</span>');
            }
            // เลือกโครงการ
            if (data.main_data.project) {
                $('#project_select_label').val(data.main_data.project);
            } else {
                document.getElementById('project_select_label').value = '';
            }

            // เลือกการนับวัน
            if (data.main_data.day_return == 't') {
                document.getElementById('day_return').checked = true;
            }
            if (data.main_data.money_receipt == 't') {
                document.getElementById('money_receipt').checked = true;
            }
            console.log(data.main_data);

            // กรอกเหตุผล
            document.getElementById('note').value = data.main_data.note || '';

            //ตรวจสอบการอนุมัติ
            // if (data.approve_data.approve_status == 'approve') {
            //     document.getElementById('approve').checked = true;
            // }
            // if (data.approve_data.approve_status == 'reject') {
            //     document.getElementById('reject').checked = true;
            //     remarkTextarea.disabled = false; // เพิ่มบรรทัดนี้
            //     remarkTextarea.focus(); // ถ้าต้องการให้ focus ด้วย
            // }

            // สร้างตารางรายการ
            let grandTotal = 0;
            if (data.child_data && Array.isArray(data.child_data)) {
                const tableBody = document.querySelector('table tbody');
                data.child_data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.wrd_mat_code || ''}</td>
                        <td>${item.wrd_mat_name || ''}</td>
                        <td class="text-center">${item.qty || ''}</td>
                        <td class="text-center">${item.count_unit_name || ''}</td>
                        <td class="text-end">${Number(item.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-end">${Number(item.total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                            <td class="text-center">${item.approve_status === 'approve' ? 'อนุมัติ' : item.approve_status === 'reject' ? 'ไม่อนุมัติ' : ''}</td>
                            <td>${item.user_fname || ''} ${item.user_lname || ''}</td>
                            <td>${item.remark || ''}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
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
        // เพิ่ม Event Listeners สำหรับปุ่มอนุมัติและไม่อนุมัติ
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
                    const response = await fetch(`../controllers/withdraws/wrd_approvel_controller.php?action=edit_one&id=${planId}`, {
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
                            window.location.href = 'withdraw-money-approvel-plan.php';
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