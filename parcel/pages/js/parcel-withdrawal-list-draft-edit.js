import { getParameter } from "./parameter.js";

let parameter = {};
let userObject = {};

let id;
let doc_type;
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
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=plan_one&id=${id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer`
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json(); // เปลี่ยนจาก `.done()` เป็น `await response.json()`
        data.tb_plan_mats.forEach(item => {
            addNewRow(item, item.mat_code, item.count_unit_name); // Update this line to call addNewRow
        });
        addDataPlan(data.tb_plans);
        doc_type = data.tb_plans.doc_type

        console.log(data);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

function addNewRow(item = {}, matlistsSelectId = "", countUnitSelectId = "") {
    const row = document.createElement("tr");
    row.innerHTML = `
    <td class="product-checkbox">
        <input class="form-check-input rowCheck" type="checkbox" aria-label="...">
    </td>
    <td>
        <select name="mat_code" class="form-control matlists">
            ${matlists(matlistsSelectId)}
        </select>
    </td>
    <td><input name="mat_name" value="${item.mat_name !== undefined ? item.mat_name : ""}" class="form-control" type="text"></td>
    <td>
        <select name="count_unit_name" class="form-control countUnits">
            ${countUnits(countUnitSelectId)}
        </select>
    </td>
    <td><input name="quarter_1" class="form-control text-center quarter" type="text"  value="${item.quarter_1 !== undefined ? item.quarter_1 : 0}" min="0"></td>
    <td><input name="quarter_2" class="form-control text-center quarter" type="text"  value="${item.quarter_2 !== undefined ? item.quarter_2 : 0}" min="0"></td>
    <td><input name="quarter_3" class="form-control text-center quarter" type="text"  value="${item.quarter_3 !== undefined ? item.quarter_3 : 0}" min="0"></td>
    <td><input name="quarter_4" class="form-control text-center quarter" type="text"  value="${item.quarter_4 !== undefined ? item.quarter_4 : 0}" min="0"></td>
    <td><input name="quantity_total" class="form-control total text-end" disabled type="number"  value="${item.quantity_total !== undefined ? item.quantity_total : 0}"></td>
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

    $(row).find('.matlists').on('change', function () {
        const selectedMatCode = $(this).val();
        console.log('Selected Mat Code:', selectedMatCode);

        const selectedMat = parameter.matlists.find(mat => mat.mat_code === selectedMatCode);
        if (selectedMat) {
            $(row).find('input[name="mat_name"]').val(selectedMat.mat_name);
        } else {
            console.log('Material not found');
        }
    });

    row.querySelectorAll('.quarter').forEach(input => {
        input.addEventListener('input', () => updateTotal(row));
    });

    updateRowIndexes();  // เรียกใช้งานอัปเดตดัชนีแถว
    return row;
}

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

function updateTotal(row) {
    let total = 0;
    row.querySelectorAll('.quarter').forEach(input => {
        let value = parseFloat(input.value) || 0;
        if (value < 0) value = 0;
        total += value;
    });
    row.querySelector('.total').value = total;
    updateGransTotal();
}

function updateGransTotal() {
    let net_total = 0
    document.querySelectorAll("#table-body tr").forEach(row => {
        net_total += parseFloat(row.querySelector('input[name="quantity_total"]').value);
    });
    const netTotalInput = document.querySelector('input[name="net_total"]');
    if (netTotalInput) {
        netTotalInput.value = net_total;
    }
}

function deleteSelectedRows() {
    document.querySelectorAll(".rowCheck:checked").forEach(checkbox => {
        checkbox.closest("tr").remove();
    });
    const checkAll = document.getElementById("all-products");
    if (checkAll) {
        checkAll.checked = false;
    }
    updateRowIndexes();
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
            document.querySelector('span[name="status_plan"]').innerHTML = '<span class="text-warning">รอดำเนินการแก้ไขแบบร่าง</span>';
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

async function updateData() {
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
    let branch_type_merc = userObject.branch_type
    if (userObject.branch_type == 'สาขา') {
        branch_type_merc = 'จังหวัด';
    } else if (userObject.branch_type == 'จังหวัด') {
        branch_type_merc = 'เขต';
    } else if (userObject.branch_type == 'เขต') {
        branch_type_merc = 'กยท';
    } else {
        branch_type_merc = null;
    }
    const data_sent = {
        tb_plans: {
            plan_start: document.querySelector('input[name="plan_start"]').value,
            plan_end: document.querySelector('input[name="plan_end"]').value,
            depart_code: document.querySelector('input[name="depart_code"]').value,
            quantity_total: document.querySelector('input[name="net_total"]').value,
            quantity_order: document.querySelector('input[name="quantity_order"]').value,
            quantity_outst: document.querySelector('input[name="quantity_outst"]').value,
            date_deli: document.querySelector('input[name="date_deli"]').value,
            date_requ: document.querySelector('input[name="date_requ"]').value,
            date_approv: document.querySelector('input[name="date_approv"]').value,
            time_deli_plan: document.querySelector('input[name="time_deli_plan"]').value,
            time_pick_plan: document.querySelector('input[name="time_pick_plan"]').value,
            note_1: document.querySelector('input[name="note_1"]').value,
            note_2: document.querySelector('input[name="note_2"]').value,
            note_3: document.querySelector('input[name="note_3"]').value,
            branch_type: branch_type_merc,
            user_requ: userObject.user_code,
            user_requ_name: `${userObject.user_fname} ${userObject.user_lname}`,
            at_close: document.querySelector('input[name="at_close"]').checked,
            const_id: document.querySelector('input[name="const_id"]').checked
        },
        tb_plan_mats: tb_plan_mats
    }

    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=update_plan&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json(); 
        console.log(result);
        window.hideLoading();
        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: "ดำเนินการแก้ไขเอกสารสำเร็จ",
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

async function updateDataDoctype() {
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
    let branch_type_merc = userObject.branch_type
    if (userObject.branch_type == 'สาขา') {
        branch_type_merc = 'จังหวัด';
    } else if (userObject.branch_type == 'จังหวัด') {
        branch_type_merc = 'เขต';
    } else if (userObject.branch_type == 'เขต') {
        branch_type_merc = 'กยท';
    } else {
        branch_type_merc = null;
    }
    const data_sent = {
        tb_plans: {
            plan_start: document.querySelector('input[name="plan_start"]').value,
            plan_end: document.querySelector('input[name="plan_end"]').value,
            depart_code: document.querySelector('input[name="depart_code"]').value,
            quantity_total: document.querySelector('input[name="net_total"]').value,
            quantity_order: document.querySelector('input[name="quantity_order"]').value,
            quantity_outst: document.querySelector('input[name="quantity_outst"]').value,
            date_deli: document.querySelector('input[name="date_deli"]').value,
            date_requ: document.querySelector('input[name="date_requ"]').value,
            date_approv: document.querySelector('input[name="date_approv"]').value,
            time_deli_plan: document.querySelector('input[name="time_deli_plan"]').value,
            time_pick_plan: document.querySelector('input[name="time_pick_plan"]').value,
            note_1: document.querySelector('input[name="note_1"]').value,
            note_2: document.querySelector('input[name="note_2"]').value,
            note_3: document.querySelector('input[name="note_3"]').value,
            branch_type: branch_type_merc,
            user_requ: userObject.user_code,
            user_requ_name: `${userObject.user_fname} ${userObject.user_lname}`,
            at_close: document.querySelector('input[name="at_close"]').checked,
            const_id: document.querySelector('input[name="const_id"]').checked
        },
        tb_plan_mats: tb_plan_mats
    }

    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=update_plan_doctype&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json(); 
        console.log(result);

        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: "ดำเนินการแก้ไขเอกสารและส่งขออนุมัติสำเร็จ",
            icon: "success"
        }).then(() => {
            window.location.href = "parcel-withdrawal-list-draft.php";
        });

    } catch (error) {
        console.error('เกิดข้อผิดพลาด:', error);
        Swal.fire({
            title: "เกิดข้อผิดพลาด",
            text: "รายการข้อผิดพลาด: " + error.message,
            icon: "warning"
        });
    }
}

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

beginFetch(); // เรียกใช้ฟังก์ชัน

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("updateSave").addEventListener("click", function () {
        updateData();
    });
    document.getElementById("updateSaveDoctype").addEventListener("click", function () {
        updateDataDoctype();
    });
});

document.addEventListener("DOMContentLoaded", async function () {
    document.querySelector(".btn-success-light")?.addEventListener("click", () => addNewRow());
    document.querySelector(".btn-danger-light")?.addEventListener("click", deleteSelectedRows);
    const checkAll = document.getElementById('all-products');
    checkAll.addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.rowCheck');
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = checkAll.checked;
        });
    });
});

document.querySelector("#table-body").addEventListener('input', (event) => {
    if (event.target.classList.contains('quarter')) {
        let value = event.target.value.replace(/[^0-9]/g, '');
        if (value === '') value = '0';
        event.target.value = value;
    }
});

document.querySelectorAll(".numOnly").forEach(inputElement => {
    inputElement.addEventListener('input', (event) => {
        let value = event.target.value.replace(/[^0-9]/g, '');
        if (value === '') value = '0';
        event.target.value = value;
    });
});