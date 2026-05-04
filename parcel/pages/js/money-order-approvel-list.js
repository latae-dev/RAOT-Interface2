import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        const params_all = await getAllParams();
        const tableBody = document.querySelector('tbody');
        let currentPage = 1;
        let currentLimit = 10;
        let is_type = '';
        const hasmatchAPHO = params_all?.position_list_id
            .some(id => ['5'].includes(id));
        const hasmatchAPHOP = params_all?.position_list_id
            .some(id => ['6'].includes(id));
        if (hasmatchAPHO) {
            is_type = 1;
        } else if (hasmatchAPHOP) {
            is_type = 2;
        }

        $.ajax({
            type: "GET",
            url: "../controllers/withdraws/wrd_compensation_controller.php?action=get_persons&depart_code=" + params_all.current_user.depart_code,
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
        // หลังจากได้ params_all แล้ว
        // if (params_all.current_user.position_code === 'APHO' && window.choicesInstance) {
        //     console.log("ลบ audit options...");

        //     // เก็บเฉพาะที่ไม่ใช่ audit
        //     const keepChoices = window.choicesInstance._store.choices.filter(c =>
        //         c.value !== 'audit_approve' && c.value !== 'audit_reject'
        //     );

        //     // clear แล้ว set ใหม่
        //     window.choicesInstance.clearChoices();
        //     window.choicesInstance.setChoices(
        //         keepChoices.map(c => ({
        //             value: c.value,
        //             label: c.label,
        //             selected: c.selected,
        //             disabled: c.disabled
        //         })),
        //         'value', 'label', false
        //     );
        // }

        if (params_all.current_user.position_code === "APHOP") {
            window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
        } else {
            window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
            window.choicesInstance.setChoiceByValue("audit_approve"); // เลือก placeholder
        }



        // ฟังก์ชันแสดงผลข้อมูลในตาราง
        function displayData(data, pageOffset = 0) {
            tableBody.innerHTML = ''; // ล้างข้อมูลเก่า

            if (!data || data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="9" class="text-center">ไม่พบข้อมูล</td>';
                tableBody.appendChild(row);
                return;
            }

            data.forEach((item, index) => {
                var text = 'รอการตรวจสอบ';
                if (item.plan_id) { text = 'รอการตรวจสอบ' }
                const statusLabels = {
                    waiting: `<span class="badge rounded-pill bg-warning">${text}</span>`,
                    finance_approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
                    finance_reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
                    audit_approve: `<span class="badge rounded-pill bg-info">ผ่านการตรวจสอบแล้ว</span>`,
                    audit_reject: `<span class="badge rounded-pill bg-danger">ไม่ผ่านการตรวจสอบ</span>`,
                    finance_check: `<span class="badge rounded-pill bg-info">ผ่านการตรวจสอบแล้ว</span>`,
                    cho_reject: `<span class="badge rounded-pill bg-danger">ไม่ผ่านการตรวจสอบ</span>`,
                };


                const row = document.createElement('tr');
                row.classList.add('text-center');

                let approveButton = '';
                const linkView = 'money-order-view.php';
                if (!item.plan_id) {
                    var permissionAPHO = params_all.position_list_id.some(id => ['5'].includes(id));
                    var permissionAPHOP = params_all.position_list_id.some(id => ['6'].includes(id));
                    var permissionCHO = params_all.position_list_id.some(id => ['12'].includes(id));
                    var permissionAPAHO = params_all.position_list_id.some(id => ['13'].includes(id));
                    if (item.req_user_code != params_all.current_user.user_code) {
                        if (permissionAPHOP && item.approve_status === "waiting") {
                            approveButton = `
                                <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                                class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                                title="การพิจารณาอนุมัติ">
                                    <i class="bx bx-list-check"></i>
                                </a>`;
                        }
                        else if (permissionAPHO && item.approve_status === "finance_check") {
                            approveButton = `
                                <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                                class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                                title="การพิจารณาอนุมัติ">
                                    <i class="bx bx-list-check"></i>
                                </a>`;
                        }
                        else if (permissionCHO && item.approve_status === "audit_approve") {
                            approveButton = `
                        <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                            class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                            title="การพิจารณาอนุมัติ">
                            <i class="bx bx-list-check"></i>
                        </a>`;
                        } else if (permissionAPAHO && (item.approve_status === "finance_check")) {
                            approveButton = `
                        <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                            class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                            title="การพิจารณาอนุมัติ">
                            <i class="bx bx-list-check"></i>
                        </a>`;
                        }
                    }
                } else {
                    var permissionAPHO = params_all.position_list_id.some(id => ['5'].includes(id));
                    var permissionCHO = params_all.position_list_id.some(id => ['12'].includes(id));
                    var permissionAPAHO = params_all.position_list_id.some(id => ['13'].includes(id));
                    if (item.req_user_code != params_all.current_user.user_code) {
                        if (permissionAPHO && item.approve_status === "finance_check") {
                            approveButton = `
                                <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                                class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                                title="การพิจารณาอนุมัติ">
                                    <i class="bx bx-list-check"></i>
                                </a>`;
                        }
                        else if (permissionCHO && item.approve_status === "waiting") {
                            approveButton = `
                        <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                            class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                            title="การพิจารณาอนุมัติ">
                            <i class="bx bx-list-check"></i>
                        </a>`;
                        } else if (permissionAPAHO && (item.approve_status === "finance_check")) {
                            approveButton = `
                        <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                            class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                            title="การพิจารณาอนุมัติ">
                            <i class="bx bx-list-check"></i>
                        </a>`;
                        }
                    }
                }

                row.innerHTML = `
                    <td>${index + 1 + pageOffset}</td>
                    <td>${convertToBuddhistEra(item.req_user_date)}</td>
                    <td>${item.compensation_number}</td>
                    <td>${item.plan_number || '-'}</td>
                    <td>${item.expenses_number || '-'}</td>
                    <td>${item.compensation_form_name}</td>
                    <td>${item.req_number_withdraw}/${item.req_number_pay}</td>
                    <td>${statusLabels[item.approve_status] || '-'}</td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="${linkView}?id=${item.id}&is_approve=0&is_action=approve" 
                            class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                <i class="fe fe-eye"></i>
                            </a>
                            ${approveButton}
                        </div>
                    </td>
                `;

                tableBody.appendChild(row);
            });
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
                const response = await fetch(`../controllers/withdraws/wrd_compensation_controller.php?
                    action=compensation_approvel_search&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    compensation_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    req_user_code=${params_all.current_user.user_code}&
                    req_position_code=${params_all.current_user.position_code}&
                    req_depart_code=${params_all.current_user.depart_code}&
                    search_user_code=${req_user_code}&
                    is_type=${is_type}
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
                console.log(data);
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
                // const status = document.getElementById("choices-single-groups").value;
                const status = Array.from(document.getElementsByName("choices[]")[0].selectedOptions)
                    .map(opt => opt.value)
                    .filter(v => v !== "") // ตัด placeholder ออก
                    .join(",");
                const req_user_code = document.getElementById('choices-single-person').value;

                const pageOffset = (page - 1) * currentLimit;
                const result = await getSearch(currentLimit, pageOffset, docNumber, startDate, endDate, status, 'approve_status', req_user_code);

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
            document.getElementById("input-label11").value = "";
            startDatePicker.clear();
            endDatePicker.clear();
            $("#choices-single-person").val("").trigger("change");
            if (params_all.current_user.position_code === "APHOP") {
                window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
                window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
            } else {
                window.choicesInstance.removeActiveItems(); // เคลียร์ทุกตัวเลือก
                window.choicesInstance.setChoiceByValue("waiting"); // เลือก placeholder
                window.choicesInstance.setChoiceByValue("audit_approve"); // เลือก placeholder
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
function convertToBuddhistEranoTime(dateString) {
    const date = new Date(dateString);

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); // เดือนเริ่มที่ 0 จึงต้อง +1
    const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.

    return `${day}-${month}-${year}`;
}