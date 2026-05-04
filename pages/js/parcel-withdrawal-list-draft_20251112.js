let userData;
let userObject = {};

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

async function getAll(limit = 10, offset = 0, plan_number = '', start_date = '', end_date = '', status_plan = '') {
    try {
        window.showLoading();
       /*  plan_number = plan_number === '' ? null : plan_number;
        start_date = start_date === '' ? null : start_date;
        end_date = end_date === '' ? null : end_date;
        status_plan = status_plan === '' ? null : status_plan; */

        const response = await fetch(`../controllers/parcels/plan_controller.php?action=plan_search&doc_type=draft&
            limit=${limit}&
            offset=${offset}&
            plan_number=${plan_number}&
            start_date=${start_date}&
            end_date=${end_date}&
            status_plan=${status_plan}
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
        console.error("❌ Error:", error);
        throw error;
    }
}

async function fetchData(page = 1) {
    try {
        const limit = 10;
        const offset = (page - 1) * limit;

        let plan_number = document.querySelector("[name='plan_number']")?.value || '';
        let start_date = document.querySelector("[name='start_date']")?.value || '';
        let end_date = document.querySelector("[name='end_date']")?.value || '';
        let status_plan = document.querySelector("[name='status_plan']")?.value || '';

        const data = await getAll(limit, offset, plan_number, start_date, end_date, status_plan);

        const { total_pages: totalPages, current_page: currentPage } = data.pagination;
        const tableBody = document.getElementById('data-table');
        tableBody.innerHTML = '';

        if (!data.data.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีข้อมูล</td></tr>';
            return;
        }

        data.data.forEach((item, index) => {
            if (
                userObject.user_code === item.user_requ  || 
                (userObject.branch_id === item.branch_id && userObject.approve_branch === 'active')
            ) {
                // 🎯 แปลงสถานะ `status_plan` เป็นภาษไทย
                const statusLabels = {
                    pending: `<span class="badge rounded-pill bg-warning">รอดำเนินการแก้ไขแบบร่าง</span>`,
                    approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
                    reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
                    edit: `<span class="badge rounded-pill bg-info">ส่งกลับแก้ไข</span>`,
                    delete: `<span class="badge rounded-pill bg-dark">ลบแล้ว</span>`
                };
                const status_thai = statusLabels[item.status_plan] || '-';

                // 🎯 สร้างแถวข้อมูลในตาราง
                const row = document.createElement('tr');
                row.classList.add('crm-contact', 'text-center');
                row.innerHTML = `
                    <td>${index + 1 + offset}</td>
                    <td>${convertToBuddhistEra(item.created_at)}</td>
                    <td>${item.plan_number}</td>
                    <td>${item.user_requ_name}</td>
                    <td>${status_thai}</td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="parcel-withdrawal-list-draft-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                            ${item.status_plan === 'pending' && item.user_requ === userObject.user_code ? `
                                <a href="parcel-withdrawal-list-draft-edit.php?id=${item.id}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
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
                        updateStatus('delete', item.id);
                    });
                }
            }
        });

        // 🎯 อัปเดตข้อความแสดงจำนวนข้อมูล
        document.getElementById('showing-count').innerHTML = `กำลังแสดง ${data.data.length} รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>`;

        // 🎯 อัปเดต Pagination
        updatePagination(totalPages, currentPage);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

async function updateStatus(status_plan, id) {
    const data_sent = {
        status_plan: status_plan,
        user_branch: userObject.user_code,
        status_branch: status_plan
    }
    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=update_status&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json(); 
        console.log(result);
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

// โหลดข้อมูลครั้งแรก
document.addEventListener("DOMContentLoaded", function () {
    fetchData(1);
});
// เพิ่ม Event ให้ปุ่มกด
document.getElementById('searchBtn').addEventListener('click', function () {
    fetchData(1); // เรียก API และดึงข้อมูลใหม่
});

document.fetchData = fetchData;