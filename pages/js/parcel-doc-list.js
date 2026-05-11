let userData;
let userObject;

function normVal(v) {
    if (v === null || v === undefined) return '';
    return String(v).trim();
}

function sameUserCode(sessionCode, rowCode) {
    const a = normVal(sessionCode);
    const b = normVal(rowCode);
    return a !== '' && b !== '' && a === b;
}

export async function beginFetch() {
    try {
        // ตรวจสอบว่า sessionStorage มีค่าผู้ใช้หรือไม่
        userData = sessionStorage.getItem("raot_user_session");
        if (userData) {
            try {
                userObject = JSON.parse(userData); // แปลง string -> Object
            } catch (error) {
                console.error("Error parsing user session data:", error);
                window.location.href = "login.php"; 
            }
        }

        // เรียก fetchData() เพื่อโหลดข้อมูลตาราง
        fetchData(1);

    } catch (error) {
        console.error("❌ เกิดข้อผิดพลาด:", error);
        throw error;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    beginFetch(); // เรียกฟังก์ชันหลัง DOM โหลดเสร็จ
});

async function getAll(limit = 10, offset = 0, doc_number = '', start_date = '', end_date = '', status_doc = '') {
    try {
        window.showLoading();
       /*  doc_number = doc_number === '' ? null : doc_number;
        start_date = start_date === '' ? null : start_date;
        end_date = end_date === '' ? null : end_date;
        status_doc = status_doc === '' ? null : status_doc; */

        const q = (s) => encodeURIComponent(s ?? '');
        const response = await fetch(`../controllers/parcels/doc_controller.php?action=doc_search_list&doc_type=master&
            limit=${limit}&
            offset=${offset}&
            doc_number=${q(doc_number)}&
            start_date=${q(start_date)}&
            end_date=${q(end_date)}&
            status_doc=${q(status_doc)}
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

async function fetchData(page = 1) {
    try {
        const limit = 10;
        const offset = (page - 1) * limit;

        let doc_number = document.querySelector("[name='doc_number']")?.value || '';
        let start_date = document.querySelector("[name='start_date']")?.value || '';
        let end_date = document.querySelector("[name='end_date']")?.value || '';
        let status_doc = document.querySelector("[name='status_doc']")?.value || '';

        const tableBody = document.getElementById('data-table');
        if (!userObject || !normVal(userObject.user_code)) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">ไม่พบข้อมูลผู้ใช้ กรุณาเข้าสู่ระบบใหม่</td></tr>';
            document.getElementById('showing-count').innerHTML = 'กำลังแสดง 0 รายการ';
            return;
        }

        const data = await getAll(limit, offset, doc_number, start_date, end_date, status_doc);

        if (data.status === 'error') {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">' + (data.message || 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์') + '</td></tr>';
            document.getElementById('showing-count').innerHTML = 'กำลังแสดง 0 รายการ';
            return;
        }

        if (!data.pagination || !Array.isArray(data.data)) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">โหลดข้อมูลไม่สำเร็จ</td></tr>';
            return;
        }

        const { total_pages: totalPages, current_page: currentPage } = data.pagination;
        tableBody.innerHTML = '';

        if (!data.data.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีข้อมูล</td></tr>';
            return;
        }

        let visibleRows = 0;

        data.data.forEach((item, index) => {

            if (
                sameUserCode(userObject.user_code, item.user_requ) ||
                (normVal(userObject.branch_id) === normVal(item.branch_id) && userObject.approve_branch === 'active') ||
                (normVal(userObject.province_id) === normVal(item.province_id) && userObject.approve_province === 'active' && item.status_branch === 'approve') ||
                (normVal(userObject.area_id) === normVal(item.area_id) && userObject.approve_area === 'active' && item.status_branch === 'approve' && item.status_province === 'approve') ||
                (normVal(userObject.head_office_id) === normVal(item.head_office_id) && userObject.approve_head_office === 'active' && item.status_branch === 'approve' && item.status_province === 'approve' && item.status_area === 'approve')
            ) {
                visibleRows += 1;
                // 🎯 แปลงสถานะ `status_doc` เป็นภาษไทย
                const statusLabels = {
                    pending: `<span class="badge rounded-pill bg-warning">รออนุมัติ</span>`,
                    approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
                    reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
                    edit: `<span class="badge rounded-pill bg-info">ส่งกลับแก้ไข</span>`,
                    delete: `<span class="badge rounded-pill bg-dark">ลบแล้ว</span>`
                };
                const status_thai = statusLabels[item.status_head_office] || '-';

                /* let status_thai = '-';
                if(userObject.approve_branch === 'active'){
                    status_thai = statusLabels[item.status_branch] || '-';
                }else if(userObject.approve_province === 'active'){
                    status_thai = statusLabels[item.status_province] || '-';
                }else if(userObject.approve_area === 'active'){
                    status_thai = statusLabels[item.status_area] || '-';
                }else if(userObject.approve_head_office === 'active'){
                    status_thai = statusLabels[item.status_head_office] || '-';
                }else{
                    status_thai = statusLabels[item.status_doc] || '-';
                } */

                // 🎯 สร้างแถวข้อมูลในตาราง
                const row = document.createElement('tr');
                row.classList.add('crm-contact', 'text-center');
                row.innerHTML = `
                    <td>${index + 1 + offset}</td>
                    <td>${convertToBuddhistEra(item.created_at)}</td>
                    <td>${item.doc_number}</td>
                    <td>${item.doc_form_name}</td>
                    <td>${item.quarter}</td>
                    <td>${status_thai}</td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="parcel-doc-list-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                            ${item.status_doc === 'pending' && sameUserCode(userObject.user_code, item.user_requ) ? `
                                <a href="parcel-doc-list-edit.php?id=${item.id}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill delete-btn" data-id="${item.id}"><i class="ri-delete-bin-line"></i></a>
                            ` : ''}
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
                const deleteBtn = row.querySelector('.delete-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        deleteDoc('delete', item.id);
                    });
                }
            }
        });

        if (visibleRows === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีรายการที่คุณมีสิทธิ์ดูในหน้านี้</td></tr>';
        }

        document.getElementById('showing-count').innerHTML = `กำลังแสดง ${visibleRows} รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>`;

        // 🎯 อัปเดต Pagination
        updatePagination(totalPages, currentPage);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

async function updateStatus(status_doc, id) {
    const data_sent = {
        status_doc: status_doc,
        user_res_1: userObject.user_code
    }
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/doc_controller.php?action=update_status&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });
        window.hideLoading();
        window.location.reload();

    } catch (error) {
        console.error('เกิดข้อผิดพลาด:', error);
        Swal.fire({
            title: "เกิดข้อผิดพลาด",
            text: "รายการข้อผิดพลาด: " + error.message,
            icon: "warning"
        });
    }
}

async function deleteDoc(status_doc,id) {
    const data_sent = {
        status_doc: status_doc,
        user_res_1: userObject.user_code
    }
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/doc_controller.php?action=delete_doc&id=${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });
        window.hideLoading();
        await response.json();

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

function updatePagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    // ปุ่มก่อนหน้า
    if (currentPage > 1) {
        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="fetchData(${currentPage - 1})">ก่อนหน้า</a></li>`;
    } else {
        pagination.innerHTML += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">ก่อนหน้า</a></li>`;
    }

    // ปุ่มเลขหน้า (แสดงเฉพาะ 5 หน้าใกล้กับหน้าปัจจุบัน)
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        pagination.innerHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0);" onclick="fetchData(${i})">${i}</a>
            </li>
        `;
    }

    // ปุ่มถัดไป
    if (currentPage < totalPages) {
        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="fetchData(${currentPage + 1})">ถัดไป</a></li>`;
    } else {
        pagination.innerHTML += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">ถัดไป</a></li>`;
    }
}

/* แปลงวันที่ */
function convertToBuddhistEra(dateString) {
    const date = new Date(dateString);

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); // เดือนเริ่มที่ 0 จึงต้อง +1
    const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.

    return `${day}-${month}-${year}`;
}

// เพิ่ม Event ให้ปุ่มกด
document.getElementById('searchBtn').addEventListener('click', function () {
    fetchData(1); // เรียก API และดึงข้อมูลใหม่
});

document.fetchData = fetchData;