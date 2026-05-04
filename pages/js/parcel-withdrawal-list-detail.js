import { getParameter } from "./parameter.js";

let parameter = {};
let userObject = {};

let id;
async function beginFetch() {
    const urlParams = new URLSearchParams(window.location.search); // ดึงพารามิเตอร์จาก URL
    id = urlParams.get('id'); // ดึงค่าของพารามิเตอร์ 'id'

    parameter = await getParameter(); // โหลดพารามิเตอร์
    getPlanOne(id); // เรียกฟังก์ชันดึงข้อมูลแผน

    const userData = sessionStorage.getItem("raot_user_session");
    if (userData) {
        try {
            userObject = JSON.parse(userData); // แปลง string -> Object
            document.querySelector('input[name="depart_code"]').value = userObject.depart_code || "";
        } catch (error) {
            console.error("Error parsing user session data:", error);
            window.location.href = "login.php"; 
        }
    }
}

/* ดึงข้อมูลจาก id param */
async function getPlanOne(id) {
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=plan_one&id=${id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer`
            }
        });

        if (!response.ok) {
            window.hideLoading();
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        window.hideLoading();

        const data = await response.json(); // เปลี่ยนจาก `.done()` เป็น `await response.json()`
        data.tb_plan_mats.forEach(item => {
            addDataNewRow(item);
        });
        addDataPlan(data.tb_plans)

        console.log(data);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

function addDataNewRow(item) {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td>${item.mat_code}</td>
    <td class="text-start">${item.mat_name}</td>
    <td>${item.count_unit_name}</td>
    <td>${item.quarter_1 || 0}</td>
    <td>${item.quarter_2 || 0}</td>
    <td>${item.quarter_3 || 0}</td>
    <td>${item.quarter_4 || 0}</td>
    <td class="text-end">${item.quantity_total || 0}</td>
    `;

    // ใส่ row เข้าไปใน DOM ก่อน แล้วค่อยใช้ select2
    document.querySelector("#table-body").appendChild(row); // สมมติว่าตารางมี <tbody id="table-body">

    // เรียกใช้ select2 สำหรับ select ที่อยู่ใน row ใหม่
    $(row).find('.matlists').select2({
        placeholder: "-- เลือก --",
        allowClear: false,
        data: parameter.matlists.map(mat => ({
            id: mat.mat_code,  // ใช้ mat_code เป็น id
            text: `${mat.mat_code} - ${mat.mat_name}`  // แสดงทั้ง mat_code และ mat_name ในรายการ
        })),
        templateResult: function (data) {
            return data.text || '';
        },
        templateSelection: function (data) {
            return data.id || '';
        }
    });

    $(row).find('.countUnits').select2({
        placeholder: "-- เลือก --",
        allowClear: false,
    });

    // เพิ่ม event listener สำหรับ matlists เพื่ออัปเดต mat_name
    $(row).find('.matlists').on('change', function () {
        const selectedMatCode = $(this).val();  // ใช้ .val() จาก jQuery เพื่อดึงค่า mat_code ที่ถูกเลือก
        console.log('Selected Mat Code:', selectedMatCode);

        const selectedMat = parameter.matlists.find(mat => mat.mat_code === selectedMatCode);
        if (selectedMat) {
            console.log('Selected Material:', selectedMat);
            $(row).find('input[name="mat_name"]').val(selectedMat.mat_name);  // อัปเดตชื่อวัสดุ
        } else {
            console.log('Material not found');
        }
    });

    // **ไม่จำเป็นต้องคำนวณ** `quantity_total`
    // เพราะข้อมูลทั้งหมดจะถูกใส่โดยตรงจากฐานข้อมูล (ตามคีย์)

    return row;
}

function addDataPlan(item) {
    document.querySelector('span[name="plan_number"]').textContent = item.plan_number;
    document.querySelector('input[name="plan_start"]').value = item.plan_start;
    document.querySelector('input[name="plan_end"]').value = item.plan_end;
    document.querySelector('input[name="depart_code"]').value = item.depart_code;
    document.querySelector('input[name="net_total"]').value = item.quantity_total;
    document.querySelector('input[name="quantity_order"]').value = item.quantity_order;
    document.querySelector('input[name="quantity_outst"]').value = item.quantity_outst;
    document.querySelector('input[name="at_close"]').checked = item.at_close === "t";
    document.querySelector('input[name="const_id"]').checked = item.const_id === "t";
    document.querySelector('input[name="date_deli"]').value = item.date_deli;
    document.querySelector('input[name="date_requ"]').value = item.date_requ;
    document.querySelector('input[name="date_approv"]').value = item.date_approv;
    document.querySelector('input[name="time_deli_plan"]').value = item.time_deli_plan;
    document.querySelector('input[name="time_pick_plan"]').value = item.time_pick_plan;
    document.querySelector('input[name="note_1"]').value = item.note_1;
    document.querySelector('input[name="note_2"]').value = item.note_2;
    document.querySelector('input[name="note_3"]').value = item.note_3;

    switch (item.status_plan) {
        case "pending":
            /* const rejectButton = document.getElementById("reject");
            const approveButton = document.getElementById("approve");
            approveButton.style.display = "block";
            rejectButton.style.display = "block"; */
            // ใช้ innerHTML เพื่อแทรก HTML
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-warning">รออนุมัติ</span>';
            break;
        case "approve":
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-success">อนุมัติ</span>';
            break;
        case "reject":
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-danger">ไม่อนุมัติ</span>';
            break;
        case "edit":
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-light">ต้องแก้ไข</span>';
            break;
        case "delete":
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-light">เอกสารถูกลบ</span>';
            break;
    }
}

async function updateStatus(status_plan) {

    const data_sent = {
        status_plan: status_plan,
        user_res_1: userObject.user_code
    }

    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {   
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=update_status&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });
        window.hideLoading();
        window.location.reload();

        console.log(response);

    } catch (error) {
        console.error('เกิดข้อผิดพลาด:', error);
        Swal.fire({
            title: "เกิดข้อผิดพลาด",
            text: "รายการข้อผิดพลาด: " + error.message,
            icon: "warning"
        });
    }
}

beginFetch(); // เรียกใช้ฟังก์ชัน

/* document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("reject").addEventListener("click", function () {
        updateStatus("reject");
    });
    document.getElementById("approve").addEventListener("click", function () {
        updateStatus("approve");
    });
}); */


