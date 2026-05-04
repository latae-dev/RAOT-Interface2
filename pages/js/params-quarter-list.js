let datelists  = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'];
let monthlists = ['1','2','3','4','5','6','7','8','9','10','11','12'];
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

async function getAll(limit = 10, offset = 0) {
    try {
        window.showLoading();
        /*  plan_number = plan_number === '' ? null : plan_number;
         start_date = start_date === '' ? null : start_date;
         end_date = end_date === '' ? null : end_date;
         status_plan = status_plan === '' ? null : status_plan; */

        const response = await fetch(`../controllers/parameter/quarterlist_controller.php?action=quarter_search&
            limit=${limit}&
            offset=${offset}
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
        const data = await response.json();
        window.hideLoading();
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
        const data = await getAll(limit, offset);

        const { total_pages: totalPages, current_page: currentPage } = data.pagination;
        const tableBody = document.getElementById('data-table');
        tableBody.innerHTML = '';

        if (!data.data.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีข้อมูล</td></tr>';
            return;
        }

        // 🎯 Loop ผ่านข้อมูลเพื่อสร้างแถวตารางและกำหนดสถานะ
        data.data.forEach((item, index) => {

            // แปลง plan_requ_all จาก JSON string เป็น array สำหรับตรวจสอบและสร้าง string แสดงผล
            let planRequAllArray = [];
            if (item.plan_requ_all) {
                try {
                    const parsed = JSON.parse(item.plan_requ_all);
                    if (Array.isArray(parsed)) {
                        planRequAllArray = parsed;
                    }
                } catch (e) {
                    console.error('Error parsing plan_requ_all JSON:', e);
                }
            }
            // 🎯 สร้างแถวข้อมูลในตาราง
            const row = document.createElement('tr');
            row.classList.add('crm-contact', 'text-center');

            row.innerHTML = `
                <td>${index + 1 + offset}</td>
                <td>${item.quarter_name}</td>
                <td>
                    <select name="quarter_date_start" id="quarter_date_start_${item.quarter_code}" class="form-control selectDatelists">
                        ${selectDatelists(item.quarter_date_start)}
                    </select>
                </td>
                <td>${item.quarter_month_start}</td>
                <td>
                    <select name="quarter_date_end" id="quarter_date_end_${item.quarter_code}" class="form-control selectDatelists">
                        ${selectDatelists(item.quarter_date_end)}
                    </select>
                </td>
                <td>${item.quarter_month_end}</td>
                <td>
                    <div class="hstack gap-2 fs-15 justify-content-center">
                        <button class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="updateData(${item.quarter_code})">
                            <i class="fe fe-check-square"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);

            // เรียกใช้ select2 สำหรับ select ที่อยู่ใน row ใหม่
            $(row).find('.selectDatelists').select2({
                placeholder: "-- เลือก --",
                allowClear: false,
                data: datelists.map(mat => ({
                    id: mat,  // ใช้ mat_code เป็น id
                    text: `${mat}`  // แสดงทั้ง mat_code และ mat_name ในรายการ
                })),
                templateResult: function (data) {
                    // เมื่อแสดงรายการให้แสดง mat_code - mat_name
                    return data.text || '';
                },
                templateSelection: function (data) {
                    // เมื่อเลือกแสดงแค่ mat_code
                    return data.id || '';
                }
            });
        });

        // 🎯 อัปเดตข้อความแสดงจำนวนข้อมูล
        document.getElementById('showing-count').innerHTML = `กำลังแสดง ${data.data.length} รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>`;

        // 🎯 อัปเดต Pagination
        updatePagination(totalPages, currentPage);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}


async function updateData(id) {
    const data_sent = {
        quarter_code: id,
        quarter_date_start: $('#quarter_date_start_'+id).val(),
        quarter_date_end: $('#quarter_date_end_'+id).val(),
    }
    console.log(data_sent);// แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parameter/quarterlist_controller.php?action=update_quarter&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json();
        console.log(result);
        window.hideLoading();

        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: "ดำเนินการแก้ไขเสร็จสิ้น",
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

// ฟังก์ชันสร้างตัวเลือกวัน
function selectDatelists(selectedId) {
    return datelists.map(b =>
        `<option value="${b}" ${b === selectedId ? "selected" : ""}>
           ${b}
        </option>`
    ).join('');
}

// ฟังก์ชันสร้างตัวเลือกเดือน
function selectMonthlists(selectedId) {
    if (!Array.isArray(monthlists)) {
        return '<option value="">-- 1 --</option>';
    }
    return monthlists.map(b =>
        `<option value="${b}" ${b === selectedId ? "selected" : ""}>
           ${b}
        </option>`
    ).join('');
}

// โหลดข้อมูลครั้งแรก
document.addEventListener("DOMContentLoaded", function () {
    fetchData(1);
});


document.fetchData = fetchData;
document.updateData = updateData;

