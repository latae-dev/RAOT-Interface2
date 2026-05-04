let userData;
let userObject
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

        const response = await fetch(`../controllers/parcels/doc_controller.php?action=doc_search&doc_type=master&
            limit=${limit}&
            offset=${offset}&
            doc_number=${doc_number}&
            start_date=${start_date}&
            end_date=${end_date}&
            status_doc=${status_doc}
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

        const data = await getAll(limit, offset, doc_number, start_date, end_date, status_doc);

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
                (userObject.branch_id === item.branch_id && userObject.approve_branch === 'active')|| // ถ้าเป็นสาขาที่มีสิทธิ์อนุมัติ
                (userObject.province_id === item.province_id && userObject.approve_province === 'active' && item.status_branch === 'approve') || // ถ้าเป็นจังหวัดที่มีสิทธิ์อนุมัติ
                (userObject.area_id === item.area_id && userObject.approve_airea === 'active' && item.status_branch === 'approve' && item.status_province === 'approve') || // ถ้าเป็นพื้นที่ที่มีสิทธิ์อนุมัติ
                (userObject.head_office_id === item.head_office_id && userObject.approve_head_office === 'active' && item.status_branch === 'approve' && item.status_province === 'approve' && item.status_area === 'approve') // ถ้าเป็นสำนักงานที่มีสิทธิ์อนุมัติ
            ) {
                // 🎯 แปลงสถานะ `status_doc` เป็นภาษไทย
                const statusLabels = {
                    pending: `<span class="badge rounded-pill bg-warning">รออนุมัติใบเบิกพัสดุ</span>`,
                    approve: `<span class="badge rounded-pill bg-success">ดำเนินการอนุมัติเสร็จสิ้น</span>`,
                    reject: `<span class="badge rounded-pill bg-danger">ดำเนินการไม่อนุมัติ</span>`,
                    edit: `<span class="badge rounded-pill bg-info">ดำเนินการส่งกลับแก้ไข</span>`,
                    delete: `<span class="badge rounded-pill bg-dark">เอกสารถูกลบแล้ว</span>`
                };
                
                let status_thai = '-';
                if(userObject.approve_branch === 'active'){
                    status_thai = statusLabels[item.status_branch] || '-';
                }else if(userObject.approve_province === 'active'){
                    status_thai = statusLabels[item.status_province] || '-';
                }else if(userObject.approve_airea === 'active'){
                    status_thai = statusLabels[item.status_area] || '-';
                }else if(userObject.approve_head_office === 'active'){
                    status_thai = statusLabels[item.status_head_office] || '-';
                }else{
                    status_thai = statusLabels[item.status_doc] || '-';
                }

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
                            <a href="parcel-doc-list-approvel-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                <i class="fe fe-eye"></i>
                            </a>
                            ${(() => {
                                if(userObject.approve_branch === 'active'){
                                    return `
                                            ${item.status_branch === 'pending' && userObject.approve_branch === 'active' ? `
                                        <a href="#" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill approve-btn" data-id="${item.id}"><i class='bx bx-check'></i></a>
                                        <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill reject-btn" data-id="${item.id}"><i class='bx bx-x'></i></a>
                                    ` : ''}`
                                }else if(userObject.approve_province === 'active'){
                                    return `
                                            ${item.status_province === 'pending' ? `
                                        <a href="#" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill approve-btn" data-id="${item.id}"><i class='bx bx-check'></i></a>
                                        <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill reject-btn" data-id="${item.id}"><i class='bx bx-x'></i></a>
                                    ` : ''}`    
                                }else if(userObject.approve_airea === 'active'){
                                    return `
                                            ${item.status_area === 'pending' ? `
                                        <a href="#" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill approve-btn" data-id="${item.id}"><i class='bx bx-check'></i></a>
                                        <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill reject-btn" data-id="${item.id}"><i class='bx bx-x'></i></a>
                                    ` : ''}`
                                }else if(userObject.approve_head_office === 'active'){
                                    return `
                                            ${item.status_head_office === 'pending' ? `
                                        <a href="#" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill approve-btn" data-id="${item.id}"><i class='bx bx-check'></i></a>
                                        <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill reject-btn" data-id="${item.id}"><i class='bx bx-x'></i></a>
                                    ` : ''}`
                                }else{
                                    return ``
                                }

                            })()}
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
                
                // ✅ เพิ่ม Event Listener หลังจากสร้าง element
                const approveBtn = row.querySelector('.approve-btn');
                const rejectBtn = row.querySelector('.reject-btn');

                if (approveBtn) {
                    approveBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        updateStatus('approve', item.id);
                    });
                }
                if (rejectBtn) {
                    rejectBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        updateStatus('reject', item.id);
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

async function updateStatus(status_doc, id) {

    let status_doc_key = '-';
    let user_branch_key = '-';
    let status_branch_key = '-';
    let status_date_key = '-';

    if(userObject.approve_branch === 'active'){
        status_doc_key = 'status_doc';
        user_branch_key = 'user_branch';
        status_branch_key = 'status_branch';
        status_date_key = 'date_branch';
    }else if(userObject.approve_province === 'active'){
        status_doc_key = 'status_doc';
        user_branch_key = 'user_province';
        status_branch_key = 'status_province';
        status_date_key = 'date_province';
    }else if(userObject.approve_airea === 'active'){
        status_doc_key = 'status_doc';
        user_branch_key = 'user_area';
        status_branch_key = 'status_area';
        status_date_key = 'date_area';
    }else if(userObject.approve_head_office === 'active'){
        status_doc_key = 'status_doc';
        user_branch_key = 'user_head_office';
        status_branch_key = 'status_head_office';
        status_date_key = 'date_head_office';
    }else{
        status_doc_key = 'status_doc';
        user_branch_key = 'user_requ';
        status_branch_key = 'status_doc';
        status_date_key = 'date_requ';
    }

    const data_sent = {
        [status_doc_key]: status_doc,
        [user_branch_key]: userObject.user_code,
        [status_branch_key]: status_doc,
        [status_date_key]: ''
    }
    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/doc_controller.php?action=update_status&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json(); 
        console.log(result);
        window.hideLoading();
        if (status_doc === 'approve') {    
            Swal.fire({
                title: "ดำเนินการสำเร็จ",
                text: "ดำเนินการอนุมัติเสร็จสิ้น",
                icon: "success"
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: "ดำเนินการสำเร็จ",
                text: "ดำเนินการไม่อนุมัติเสร็จสิ้น",
                icon: "success"
            }).then(() => {
                window.location.reload();
            });
        }

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