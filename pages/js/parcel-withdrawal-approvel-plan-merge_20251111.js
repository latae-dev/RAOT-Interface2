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

async function getAll(limit = 10, offset = 0, plan_number = '', start_date = '', end_date = '', status_plan = '') {
    try {
        window.showLoading();
        /*  plan_number = plan_number === '' ? null : plan_number;
         start_date = start_date === '' ? null : start_date;
         end_date = end_date === '' ? null : end_date;
         status_plan = status_plan === '' ? null : status_plan; */

        const response = await fetch(`../controllers/parcels/plan_controller.php?action=plan_search&doc_type=master&
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
        const data = await response.json();
        window.hideLoading();
        return data;
    } catch (error) {
        window.hideLoading();
        console.error("❌ Error:", error);
        throw error;
    }
}

// ตัวแปรเก็บรายการที่เลือก
const selectedItems = [];
let data_result;

async function fetchData(page = 1) {
    data_result = [];
    try {
        const limit = 10;
        const offset = (page - 1) * limit;

        let plan_number = document.querySelector("[name='plan_number']")?.value || '';
        let start_date = document.querySelector("[name='start_date']")?.value || '';
        let end_date = document.querySelector("[name='end_date']")?.value || '';
        let status_plan = document.querySelector("[name='status_plan']")?.value || '';

        const data = await getAll(limit, offset, plan_number, start_date, end_date, status_plan);

        const { total_pages: totalPages = 0, current_page: currentPage = 0 } = data.pagination;
        const tableBody = document.getElementById('data-table');
        tableBody.innerHTML = '';

        console.log(data);


        // 🔍 คัดกรองเฉพาะรายการที่ user สามารถอนุมัติได้
        const data_use = data.data.filter(item =>
            (item.status_merge === 'none') && (
                (userObject.area_id === item.area_id &&
                    userObject.approve_province === 'active' &&
                    item.status_branch === 'approve' &&
                    item.status_province === 'approve')
            )
        );

        // 📋 แสดงข้อมูล
        let data_lenght = 0;
        if (data_lenght) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีข้อมูล</td></tr>';
            return;
        }
        data_use.forEach((item, index) => {
            if (item.merge_provice === 'no') {
                data_lenght+=1;
                data_result.push(item);

                const row = document.createElement('tr');
                row.classList.add('crm-contact', 'text-center');
                row.setAttribute('data-id', item.id);

                row.innerHTML = `
                    <td>
                        <input class="form-check-input check-item" type="checkbox" value="${item.id}">
                    </td>
                    <td>${index + 1 + offset}</td>
                    <td>${convertToBuddhistEra(item.created_at)}</td>
                    <td>${item.plan_number}</td>
                    <td>${item.user_requ_name}</td>
                    <td>
                        ${userObject.approve_province === 'active' ? `<span class="badge rounded-pill bg-info">อนุมัติระดับจังหวัด</span>` :
                        userObject.approve_area === 'active' ? `<span class="badge rounded-pill bg-info">อนุมัติระดับเขต</span>` :
                            userObject.approve_head_office === 'active' ? `<span class="badge rounded-pill bg-info">อนุมัติระดับ กยท (สำนักงานใหญ่)</span>` : `-`}
                    </td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="parcel-withdrawal-list-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);

                // ✅ Checkbox event
                const checkbox = row.querySelector('.check-item');
                checkbox.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        if (!selectedItems.some(i => i.id === item.id)) {
                            selectedItems.push(item);
                        }
                    } else {
                        const indexToRemove = selectedItems.findIndex(i => i.id === item.id);
                        if (indexToRemove !== -1) selectedItems.splice(indexToRemove, 1);
                    }
                    console.log('Selected Items:', selectedItems);
                    updateMercButtonVisibility();
                });
            }
        });

        // 🎯 แสดงจำนวน
        document.getElementById('showing-count').innerHTML = `กำลังแสดง ${data_lenght} รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>`;

        // 🎯 อัปเดต Pagination
        updatePagination(totalPages, currentPage);

    } catch (error) {
        console.error("❌ Error:", error);
    }
}

// เชื่อมโยง checkbox เลือกทั้งหมด
const checkAll = document.getElementById('all-products');
checkAll.addEventListener('change', (e) => {
    const allCheckboxes = document.querySelectorAll('.check-item');
    selectedItems.length = 0;

    if (e.target.checked) {
        data_result.forEach((item) => {
            if (!selectedItems.some(i => i.id === item.id)) {
                selectedItems.push(item);
            }
        });
    }

    allCheckboxes.forEach((checkbox) => {
        checkbox.checked = e.target.checked;
    });

    console.log('Selected Items:', selectedItems);
    updateMercButtonVisibility(); // <-- เพิ่มตรงนี้ด้วย
});

function updateMercButtonVisibility() {
    if (selectedItems.length > 0) {
        document.getElementById('mercData').style.display = 'block';
    } else {
        document.getElementById('mercData').style.display = 'none';
    }
}

document.getElementById('mercData').addEventListener('click', async () => {
    console.log(selectedItems);
    const plan_all_id = []
    const plan_all_number = []
    selectedItems.forEach(element => {
        plan_all_id.push(element.id)
        plan_all_number.push(element.plan_number)
    });
    let branch_type_merc = '-'
    let plan_requ_level = '-'
    if (userObject.position_code == 'APPV') {
        branch_type_merc = 'เขต';
        plan_requ_level = 'จังหวัด';
    } else if (userObject.position_code == 'APAR') {
        branch_type_merc = 'กยท';
        plan_requ_level = 'เขต';
    }

    const data_sent = {
        tb_plans: {
            doc_type: 'master',
            plan_requ_level: plan_requ_level, //ระดับการขอ
            plan_requ_all: JSON.stringify(plan_all_number),
            branch_type: branch_type_merc,//ระดับที่แสดงรอ merce
            status_merge: 'none', //สถานะการรวมไฟล์
            status_plan: 'pending', //สถานะปัจจุบัน
            user_requ: userObject.user_code, // อ้างอิง USER ผู้ขอ
            user_requ_name: `${userObject.user_fname} ${userObject.user_lname}`, // อ้างชื่อนามสหุลอิงผู้ขอ
            branch_id: userObject.branch_id,
            province_id: userObject.province_id,
            area_id: userObject.area_id,
            head_office_id: userObject.head_office_id,
            plan_form_name: "รวมแผนการเบิกพัสดุแบบพิมพ์",
            depart_code: userObject.depart_code,
            merge_provice: "yes",

            province_name: userObject.province_name,
        },
        tb_plan_mats: plan_all_id
    }

    try {
        window.showLoading();
        const response = await fetch('../controllers/parcels/plan_controller.php?action=merc_plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json();
        console.log(result);
        window.hideLoading();
        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: "เลขที่แผนการเบิกพัสดุแบบพิมพ์คือ " + result.data,
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
});

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