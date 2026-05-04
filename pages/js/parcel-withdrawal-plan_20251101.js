import { getParameter } from "../js/parameter.js";

let parameter = {};
let userObject = {};

async function beginFetch() {
    parameter = await getParameter(); // โหลดพารามิเตอร์
    console.log(parameter);

    const userData = sessionStorage.getItem("raot_user_session");
    if (userData) {
        try {
            userObject = JSON.parse(userData); // แปลง string -> Object
            document.querySelector('input[name="depart_code"]').value = userObject.depart_code || "";
        } catch (error) {
            window.location.href = "login.php";
            console.error("Error parsing user session data:", error);
        }
    }

    // 🎯 กำหนดค่าเริ่มต้นเป็นวันที่ปัจจุบันสำหรับ plan_start และ plan_end
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0'); // เพิ่ม 1 เพราะเดือนเริ่มจาก 0
    const day = String(today.getDate()).padStart(2, '0');
    const currentDate = `${year}-${month}-${day}`;

    const planStartInput = document.querySelector('input[name="plan_start"]');
    const planEndInput = document.querySelector('input[name="plan_end"]');

    if (planStartInput) {
        planStartInput.value = currentDate;
    }
    if (planEndInput) {
        planEndInput.value = currentDate;
    }

    // 🎯 เรียก addNewRow และเลือกรายการแรกหากมีวัสดุ
    if (parameter.matlists && Array.isArray(parameter.matlists) && parameter.matlists.length > 0) {
        addNewRow(parameter.matlists[0].mat_code);
    } else {
        addNewRow("", "");
    }
}

// ฟังก์ชันสร้างแถวใหม่
function addNewRow(matlistsSelectId = "", countUnitSelectId = "") {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td class="product-checkbox">
        <input class="form-check-input rowCheck" type="checkbox" value="" aria-label="...">
    </td>
    <td>
        <select name="mat_code" class="form-control matlists">
            ${matlists(matlistsSelectId)}
        </select>
    </td>
    <td><input name="mat_name" class="form-control" type="text"></td>
    <td>
        <select name="count_unit_name" class="form-control countUnits">
            ${countUnits(countUnitSelectId)}
        </select>
    </td>
    <td><input name="quarter_1" class="form-control text-center quarter" type="text" value="0" min="0"></td>
    <td><input name="quarter_2" class="form-control text-center quarter" type="text" value="0" min="0"></td>
    <td><input name="quarter_3" class="form-control text-center quarter" type="text" value="0" min="0"></td>
    <td><input name="quarter_4" class="form-control text-center quarter" type="text" value="0" min="0"></td>
    <td><input name="quantity_total" class="form-control total text-end" disabled type="number" value="0"></td>
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
            // เมื่อแสดงรายการให้แสดง mat_code - mat_name
            return data.text || '';
        },
        templateSelection: function (data) {
            // เมื่อเลือกแสดงแค่ mat_code
            return data.id || '';
        }
    });
    $(row).find('.countUnits').select2({
        placeholder: "-- เลือก --",
        allowClear: false,
    });

    // เพิ่ม event listener สำหรับ matlists เพื่ออัปเดต mat_name โดยใช้ on() ของ jQuery
    $(row).find('.matlists').on('change', function () {
        const selectedMatCode = $(this).val();  // ใช้ .val() จาก jQuery เพื่อดึงค่า mat_code ที่ถูกเลือก
        console.log('Selected Mat Code:', selectedMatCode);  // ตรวจสอบค่า mat_code ที่ถูกเลือก

        const selectedMat = parameter.matlists.find(mat => mat.mat_code === selectedMatCode);
        if (selectedMat) {
            console.log('Selected Material:', selectedMat);  // ตรวจสอบว่าเจอข้อมูลหรือไม่
            $(row).find('input[name="mat_name"]').val(selectedMat.mat_name);  // ใช้ .val() ของ jQuery เพื่ออัปเดตค่า mat_name
        } else {
            console.log('Material not found');
            $(row).find('input[name="mat_name"]').val(''); // เคลียร์ค่าถ้าไม่พบ
        }
    });

    // 🎯 เรียก trigger Event change ด้วยตนเองหากมีการกำหนดค่าเริ่มต้น เพื่อให้อัปเดต mat_name ทันที
    if (matlistsSelectId) {
         $(row).find('.matlists').trigger('change');
    }

    // เพิ่ม event listener สำหรับไตรมาส (Q1 - Q4) เพื่อคำนวณจำนวนทั้งหมด
    row.querySelectorAll('.quarter').forEach(input => {
        input.addEventListener('input', () => updateTotal(row));
    });

    updateRowIndexes();  // เรียกใช้งานอัปเดตดัชนีแถว
    return row;
}

// ฟังก์ชันอัปเดตดัชนีของแถว
function updateRowIndexes() {
    const billTableBody = document.getElementById("table-body"); // ใช้ table-body ที่ตรงกับ HTML
    const rows = billTableBody.querySelectorAll("tr");
    rows.forEach((row, index) => {
        const rowIndexCell = row.querySelector(".rowIndex");
        if (rowIndexCell) {
            rowIndexCell.textContent = index + 1;
        }
    });
}

// คำนวณ "จำนวนทั้งหมด" โดยรวมค่าไตรมาสทั้ง 4
function updateTotal(row) {
    let total = 0;

    // คำนวณผลรวมของไตรมาส
    row.querySelectorAll('.quarter').forEach(input => {
        let value = parseFloat(input.value) || 0;  // หากไม่ได้กรอก จะถือเป็น 0
        if (value < 0) value = 0;  // หากค่าต่ำกว่า 0 ให้เป็น 0
        total += value;

        input.value = value;
    });
    // อัปเดตจำนวนทั้งหมด
    row.querySelector('.total').value = total;
    updateGransTotal()
}

function updateGransTotal() {
    let net_total = 0
    document.querySelectorAll("#table-body tr").forEach(row => {
        net_total += parseFloat(row.querySelector('input[name="quantity_total"]').value)
    });
    const netTotalInput = document.querySelector('input[name="net_total"]');
    if (netTotalInput) {
        netTotalInput.value = net_total // กำหนดให้แสดงเป็นเลขทศนิยม 2 ตำแหน่ง
    }
}

// ลบแถวที่เลือก
function deleteSelectedRows() {
    document.querySelectorAll(".rowCheck:checked").forEach(checkbox => {
        checkbox.closest("tr").remove();
    });
    // ยกเลิกการเลือก checkbox "check-all" ด้วย
    const checkAll = document.getElementById("all-products");
    if (checkAll) {
        checkAll.checked = false; // ยกเลิกการเลือก checkbox "check-all"
    }
    updateRowIndexes();  // เรียกใช้เมื่อมีการลบแถว
}

// ฟังก์ชันสร้างตัวเลือกหน่วยนับ
function countUnits(selectedId) {
    if (!parameter.count_units || !Array.isArray(parameter.count_units)) {
        return '<option value="">-- เลือก --</option>';
    }
    return parameter.count_units.map(b =>
        `<option value="${b.count_unit_name}" ${b.count_unit_name === selectedId ? "selected" : ""}>
            ${b.count_unit_name}
        </option>`
    ).join('');
}

// ฟังก์ชันสร้างตัวเลือกวัสดุ
function matlists(selectedId) {
    if (!parameter.matlists || !Array.isArray(parameter.matlists)) {
        return '<option value="">-- เลือก --</option>';
    }
    return parameter.matlists.map(b =>
        `<option value="${b.mat_code}" ${b.mat_code === selectedId ? "selected" : ""}>
           ${b.mat_code} - ${b.mat_name}
        </option>`
    ).join('');
}

// เรียกใช้งานเมื่อโหลดสคริปต์
beginFetch();

async function createData(doc_type) {
    // ดึค่าจากตาราง
    const tb_plan_mats = [];
    document.querySelectorAll("#table-body tr").forEach(row => {
        const rowData = {
            mat_code: row.querySelector('select[name="mat_code"]').value,
            mat_name: row.querySelector('input[name="mat_name"]').value,
            count_unit_name: row.querySelector('select[name="count_unit_name"]').value,
            quarter_1: row.querySelector('input[name="quarter_1"]').value,
            quarter_2: row.querySelector('input[name="quarter_2"]').value,
            quarter_3: row.querySelector('input[name="quarter_3"]').value,
            quarter_4: row.querySelector('input[name="quarter_4"]').value,
            quantity_total: row.querySelector('input[name="quantity_total"]').value
        };
        tb_plan_mats.push(rowData);
    });

    // ดึงค่าจากฟอร์ม ระดับที่ต้อง merce สาขา,จังหวัด,เขต, กยท
    let branch_type_merc = '-'
    let plan_requ_level = '-'
    if (userObject.position_code == 'EMPY') {
        branch_type_merc = 'สาขา';
        plan_requ_level = 'สาขา';
    } else if (userObject.position_code == 'APBR') {
        branch_type_merc = 'สาขา';
        plan_requ_level = 'สาขา';
    }else if (userObject.position_code == 'APPV') {
        branch_type_merc = 'จังหวัด';
        plan_requ_level = 'จังหวัด';
    } else if (userObject.position_code == 'APAR') {
        branch_type_merc = 'เขต';
        plan_requ_level = 'เขต';
    } else if (userObject.position_code == 'APPC') {
        branch_type_merc = 'กยท';
        plan_requ_level = 'กยท';
    }

    let checked   = true;
    let date_deli = document.querySelector('input[name="date_deli"]');
    if(date_deli.value == ''){
        $(date_deli).addClass('border-danger');
        checked = false;
    }else{
        $(date_deli).removeClass('border-danger');
    }

    let date_requ = document.querySelector('input[name="date_requ"]');
    if(date_requ.value == ''){
        $(date_requ).addClass('border-danger');
        checked = false;
    }else{
        $(date_requ).removeClass('border-danger');
    }

    let date_approv = document.querySelector('input[name="date_approv"]');
    if(date_approv.value == ''){
        $(date_approv).addClass('border-danger');
        checked = false;
    }else{
        $(date_approv).removeClass('border-danger');
    }

    let time_deli_plan = document.querySelector('input[name="time_deli_plan"]');
    if(time_deli_plan.value == ''){
        $(time_deli_plan).addClass('border-danger');
        checked = false;
    }else{
        $(time_deli_plan).removeClass('border-danger');
    }

    let time_pick_plan = document.querySelector('input[name="time_pick_plan"]');
    if(time_pick_plan.value == ''){
        $(time_pick_plan).addClass('border-danger');
        checked = false;
    }else{
        $(time_pick_plan).removeClass('border-danger');
    }

    if(!checked){
        Swal.fire({
            text: "กรุณาป้อนข้อมูลให้ครบ !!!",
            icon: "warning"
        });
        return;
    }
    
    const data_sent = {
        tb_plans: {
            doc_type: doc_type,

            plan_number: null, // เลขที่เอกสาร
            plan_number_first: null, //เอกสารแรกสำหรับยกเลิก
            plan_requ_level: plan_requ_level, //ระดับการขอ
            plan_requ_all: '[]',
            branch_type: branch_type_merc,//ระดับที่แสดงรอ merce
            status_merge: 'none', //สถานะการรวมไฟล์
            status_plan: 'pending', //สถานะปัจจุบัน
            user_requ: userObject.user_code, // อ้างอิง USER ผู้ขอ
            user_requ_name: `${userObject.user_fname} ${userObject.user_lname}`, // อ้างชื่อนามสหุลอิงผู้ขอ
            user_res_1: null, //อ้างอิงผู้อนุมัติหรือยกเลิก ระดับ 1
            user_res_2: null, // อ้างอิงผู้อนุมัติหรือยกเลิก ระดับ 2
            date_res_1: null, //วันที่อ้างอิง
            date_res_2: null, // วันที่อ้างอิง

            branch_id: userObject.branch_id,
            province_id: userObject.province_id,
            area_id: userObject.area_id,
            head_office_id: userObject.head_office_id,

            plan_form_name: "แผนการเบิกพัสดุแบบพิมพ์",
            plan_start: document.querySelector('input[name="plan_start"]').value,
            plan_end: document.querySelector('input[name="plan_end"]').value,
            depart_code: document.querySelector('input[name="depart_code"]').value,
            note_1: document.querySelector('input[name="note_1"]').value,
            note_2: document.querySelector('input[name="note_2"]').value,
            note_3: document.querySelector('input[name="note_3"]').value,
            quantity_total: document.querySelector('input[name="net_total"]').value,
            quantity_order: document.querySelector('input[name="quantity_order"]').value,
            quantity_outst: document.querySelector('input[name="quantity_outst"]').value,
            at_close: document.querySelector('input[name="at_close"]').checked, // เช็คว่าติ๊กถูกหรือไม่
            const_id: document.querySelector('input[name="const_id"]').checked,
            date_deli: document.querySelector('input[name="date_deli"]').value,
            date_requ: document.querySelector('input[name="date_requ"]').value,
            date_approv: document.querySelector('input[name="date_approv"]').value,
            time_deli_plan: document.querySelector('input[name="time_deli_plan"]').value,
            time_pick_plan: document.querySelector('input[name="time_pick_plan"]').value,

            province_name: userObject.province_name,
        },
        tb_plan_mats: tb_plan_mats
    }

    try {
        window.showLoading();
        const response = await fetch('../controllers/parcels/plan_controller.php?action=create_plan', {
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
}
// รอให้ DOM โหลดเสร็จ
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("save-draft").addEventListener("click", function () {
        createData("draft");
    });
    document.getElementById("save-master").addEventListener("click", function () {
        createData("master");
    });
});

// รอให้ DOM โหลดเสร็จ
document.addEventListener("DOMContentLoaded", async function () {
    document.querySelector(".btn-success-light")?.addEventListener("click", () => {
        // 🎯 เลือกรายการแรกเมื่อเพิ่มแถวใหม่ หากมีวัสดุ
        if (parameter.matlists && Array.isArray(parameter.matlists) && parameter.matlists.length > 0) {
            addNewRow(parameter.matlists[0].mat_code);
        } else {
            addNewRow("", "");
        }
    });
    document.querySelector(".btn-danger-light")?.addEventListener("click", deleteSelectedRows);
    /* เลือกทั้งหมดในตาราง */
    // หาตัวเลือก "check-all"
    const checkAll = document.getElementById('all-products');
    // ฟังก์ชันสำหรับเลือกหรือยกเลิกเลือกทั้งหมด
    checkAll.addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.rowCheck');  // ค้นหาทุก checkbox ที่มี class "rowCheck"
        // เปลี่ยนสถานะของ checkbox ทั้งหมดตามสถานะของ "check-all"
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = checkAll.checked;
        });
    });
    /* เลือกทั้งหมดในตาราง */
});

/* กรอกตัวเลขเท่านั้น */
// ใช้ event delegation บน table หรือ body
document.querySelector("#table-body").addEventListener('input', (event) => {
    // ตรวจสอบว่า event ถูกกระตุ้นจาก input ที่มี class 'quarter' หรือไม่
    if (event.target.classList.contains('quarter')) {
        let value = event.target.value;

        // ตรวจสอบว่าเป็นตัวเลขทั้งหมดหรือไม่ (ถ้ามีอักขระที่ไม่ใช่ตัวเลขให้ลบออก)
        value = parseFloat(value.replace(/[^0-9]/g, '')); // ลบทุกตัวที่ไม่ใช่ตัวเลข

        // ถ้าไม่มีค่า (ค่าว่าง) ให้ตั้งเป็น 0
        if (value === '' || isNaN(value)) {
            value = '0';
        }

        // อัปเดตค่าใน input
        event.target.value = value;
    }
});

document.querySelectorAll(".numOnly").forEach(inputElement => {
    inputElement.addEventListener('input', (event) => {
        let value = event.target.value;

        // ตรวจสอบว่าเป็นตัวเลขทั้งหมดหรือไม่ (ถ้ามีอักขระที่ไม่ใช่ตัวเลขให้ลบออก)
        value = parseFloat(value.replace(/[^0-9]/g, '')); // ลบทุกตัวที่ไม่ใช่ตัวเลข

        // ถ้าไม่มีค่า (ค่าว่าง) ให้ตั้งเป็น 0
        if (value === '' || isNaN(value)) {
            value = '0';
        }
        // อัปเดตค่าใน input
        event.target.value = value;
    });
});


