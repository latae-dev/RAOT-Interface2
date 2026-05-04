import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        const params_all = await getAllParams();
        const tableBody = document.querySelector('tbody');
        let currentPage = 1;
        let currentLimit = 10;
        
        if (params_all.current_user.position_code === "APHOP") {
            window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
            window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
        } else {
            window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
            window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
            window.choicesInstance.setChoiceByValue("finance_check"); // เลือก placeholder
        }
        $.ajax({
            type: "GET",
            url: "../controllers/withdraws/wrd_approvel_controller.php?action=get_persons&depart_code=" + params_all.current_user.depart_code,
            dataType: "json",
            success: function (response) {
                var $select = $('<select>', {
                    class: 'form-control',
                    id: 'choices-single-person',
                    name: 'choices-single-person'
                });
                $select.append('<option value="">กรุณาเลือกผู้ร้องขอ</option>');
                $.each(response, function (i, person) {
                    $select.append(
                        $('<option>', {
                            value: person.req_user_code,
                            text: person.full_name
                        })
                    );
                });

                $('#person-select-container').empty()
                    .append('<label class="form-label mt-2">ผู้ร้องขอ :</label>')
                    .append($select);

                $('#choices-single-person').select2({
                    placeholder: "กรุณาเลือกผู้ร้องขอ",
                    allowClear: true,
                    minimumInputLength: 1,
                    language: {
                        inputTooShort: function (args) {
                            return "กรุณากรอกชื่อผู้ร้องขอ";
                        },
                        noResults: function () {
                            return "ไม่พบผู้ร้องขอที่ค้นหา";
                        }
                    }
                });

                $('#choices-single-person').val('').trigger('change');
            }
        });
        // ฟังก์ชันแสดงผลข้อมูลในตาราง
        function displayData(data, pageOffset = 0) {
            tableBody.innerHTML = ''; // ล้างข้อมูลเก่า

            if (!data || data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="8" class="text-center">ไม่พบข้อมูล</td>';
                tableBody.appendChild(row);
                return;
            }

            data.forEach((item, index) => {
                const statusLabels = {
                    waiting: `<span class="badge rounded-pill bg-warning">รอการตรวจสอบคำขอเบิกเงินทดรองจ่าย</span>`,
                    approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
                    reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
                    edit: `<span class="badge rounded-pill bg-info">ส่งกลับแก้ไข</span>`,
                    delete: `<span class="badge rounded-pill bg-dark">ลบแล้ว</span>`,
                    finance_check: `<span class="badge rounded-pill bg-info">ผ่านการตรวจสอบแล้ว</span>`,
                    cho_reject: `<span class="badge rounded-pill bg-danger">ไม่ผ่านการตรวจสอบ</span>`
                };

                const row = document.createElement('tr');
                row.classList.add('text-center');

                // ✅ ตรวจสอบสิทธิ์: เป็นผู้ร้องขอเองและยังไม่อนุมัติ (เช็คเงือนไขเพิ่มสำรหับ reject และ waiting ให้ปุ่มสามารถเปิดให้ใช้งานได้)
                const isCheck = params_all.position_list_id
                    .some(id => ['12'].includes(id));
                const isApprove = params_all.position_list_id
                    .some(id => ['5', '13'].includes(id));
                // console.log(params_all.position_list_id);
                // console.log(item.st_hf === 'waiting' && item.req_user_code != params_all.current_user.user_code);
                var canApprove = false;
                if (isCheck && isApprove && (item.st_hf === 'waiting' || item.st_hf === 'finance_check') && item.req_user_code != params_all.current_user.user_code) {
                    canApprove = true;
                } else if (isCheck && !isApprove && item.st_hf === 'waiting' && item.req_user_code != params_all.current_user.user_code) {
                    canApprove = true;
                } if (!isCheck && isApprove && (item.st_hf === 'finance_check') && item.req_user_code != params_all.current_user.user_code) {
                    canApprove = true;
                }
                //  = item.st_hf === 'waiting' && item.req_user_code != params_all.current_user.user_code;
                // ✅ ถ้า st_hf === 'approve' ไม่ใส่ disabled
                const disabledAttr = item.st_hf === 'approve' ? '' : 'disabled';

                row.innerHTML = `
                    <td>${index + 1 + pageOffset}</td>
                    <td>${convertToBuddhistEra(item.req_user_date)}</td>
                    <td>${item.plan_number}</td>
                    <td>${item.full_name}</td>` +
                    // <td>${item.province_name}</td>
                    `<td>${statusLabels[item.st_hf] || '-'}</td>
                    <td>
                        <div class="hstack gap-2 justify-content-start fs-15">
                            <a href="withdraw-money-approvel-plan-view.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" title="รายละเอียด">
                                <i class="fe fe-eye"></i>
                            </a>
                            ${canApprove ? `
                            <a href="withdraw-money-approvel-plan-update.php?id=${item.id}" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" title="การพิจารณาอนุมัติ">
                                <i class="bx bx-list-check"></i>
                            </a>
                            ` : ''}

                        </div>
                    </td>
                `;

                tableBody.appendChild(row);

                // ✅ เงื่อนไขผูก event ลบ ถ้ามีปุ่ม delete
                const deleteBtn = row.querySelector('.delete-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        Swal.fire({
                            title: "ท่านแน่ใจหรือไม่?",
                            text: "ท่านต้องการลบรายการนี้หรือไม่?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "ยืนยัน",
                            cancelButtonText: `ยกเลิก`
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteStatus(item.id);
                            }
                        });
                    });
                }
            });
        }

        async function deleteStatus(id) {
            try {
                window.showLoading();
                const response = await fetch(`../controllers/withdraws/wrd_approvel_controller.php?action=delete_status&id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                window.hideLoading();

                Swal.fire({
                    title: "ดำเนินการสำเร็จ",
                    text: "ดำเนินการลบเอกสารสำเร็จ",
                    icon: "success"
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                window.hideLoading();
                console.error('เกิดข้อผิดพลาด:', error);
                Swal.fire({
                    title: "เกิดข้อผิดพลาด",
                    text: "รายการข้อผิดพลาด: " + error.message,
                    icon: "warning"
                });
            }
        }

        // ฟังก์ชันอัพเดท pagination
        function updatePagination(pagination) {
            const paginationContainer = document.querySelector('.pagination');
            const totalPages = pagination.total_pages;
            const currentPage = pagination.current_page;

            let paginationHTML = `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">
                        ก่อนหน้า
                    </a>
                </li>
            `;

            for (let i = 1; i <= totalPages; i++) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">
                        ถัดไป
                    </a>
                </li>
            `;

            paginationContainer.innerHTML = paginationHTML;

            // อัพเดทข้อความแสดงจำนวนรายการ
            document.querySelector('.card-footer .d-flex div:first-child').textContent =
                `กำลังแสดง ${pagination.total_records} รายการ`;
        }

        // ฟังก์ชันค้นหา
        async function getSearch(limit = 10, offset = 0, doc_number = '', start_date = '', end_date = '', status = '', key = '', req_user_code = '') {
            try {
                window.showLoading();
                const response = await fetch(`../controllers/withdraws/wrd_approvel_controller.php?
                    action=plan_search&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    plan_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    depart_code=${params_all.current_user.depart_code}&
                    req_user_code=${req_user_code}
                    `, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                    }
                });

                if (!response.ok) {
                    window.hideLoading();
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                window.hideLoading();
                const data = await response.json();
                return data;
            } catch (error) {
                window.hideLoading();
                console.error("❌ Error:", error);
                throw error;
            }
        }

        // ฟังก์ชันค้นหาและแสดงผล
        async function searchAndDisplay(page = 1) {
            try {
                const docNumber = document.getElementById('input-label11').value;
                const startDate = document.querySelector('input[placeholder="กำหนด วันที่เริ่มต้น"]').value;
                const endDate = document.querySelector('input[placeholder="กำหนด วันที่สิ้นสุด"]').value;
                // const status = document.getElementById('choices-single-groups').value;
                const status = Array.from(document.getElementsByName("choices[]")[0].selectedOptions)
                    .map(opt => opt.value)
                    .filter(v => v !== "") // ตัด placeholder ออก
                    .join(",");
                console.log(status);
                const req_user_code = document.getElementById('choices-single-person').value;

                const pageOffset = (page - 1) * currentLimit;
                const result = await getSearch(currentLimit, pageOffset, docNumber, startDate, endDate, status, 'st_hf', req_user_code);

                if (result.status === 'success') {
                    displayData(result.data, pageOffset);
                    updatePagination(result.pagination);
                } else {
                    console.error('เกิดข้อผิดพลาด:', result.message);
                    alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
                }
            } catch (error) {
                console.error('เกิดข้อผิดพลาด:', error);
                alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }

        // เพิ่ม Event Listeners
        document.querySelector('.btn-primary').addEventListener('click', () => searchAndDisplay(1));

        document.querySelector('.pagination').addEventListener('click', (e) => {
            if (e.target.classList.contains('page-link')) {
                const page = parseInt(e.target.dataset.page);
                if (!isNaN(page)) {
                    searchAndDisplay(page);
                }
            }
        });

        // โหลดข้อมูลครั้งแรก
        await searchAndDisplay(1);

        document.getElementById('searchBtn').addEventListener('click', () => searchAndDisplay(1));

        function resetSearchFields() {
            document.getElementById('input-label11').value = '';
            startDatePicker.clear();
            endDatePicker.clear();
            $('#choices-single-person').val('').trigger('change');
            if (params_all.current_user.position_code === "APHOP") {
                window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
                window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
            } else {
                window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
                window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
                window.choicesInstance.setChoiceByValue("finance_check"); // เลือก placeholder
            }
        }

        document.getElementById('resetBtn').addEventListener('click', () => {
            resetSearchFields();
            searchAndDisplay(1);
        });

    } catch (err) {
        console.error('เกิดข้อผิดพลาด:', err);
        alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
    }
})();
function convertToBuddhistEra(dateString) {
    const date = new Date(dateString);

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); // เดือนเริ่มที่ 0 จึงต้อง +1
    const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

