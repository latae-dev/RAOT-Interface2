import { getParameter } from "./parameter.js";

let parameter = {};
let userObject = {};

let id;
let doc_type;
let quarter;
/** ระดับคำขอจากแผน (แหล่งจริงตาม tb_plans) — ใช้ก่อนการคำนวณจากเซสชัน */
let planRequLevelFromPlan = null;
let planBranchTypeFromPlan = null;
const dataSelects = [];
async function beginFetch() {
    const urlParams = new URLSearchParams(window.location.search); // ดึงพารามิเตอร์จาก URL
    id = urlParams.get('id'); // ดึงค่าของพารามิเตอร์ 'id'
    quarter = urlParams.get('quarter'); // ดึงค่าของพารามิเตอร์ 'quarter'

    parameter = await getParameter(); // โหลดพารามิเตอร์
    // console.log('parameter',parameter.matlists);
    const userData = sessionStorage.getItem("raot_user_session");
    // console.log(userData);
    if (userData) {
        try {
            userObject = JSON.parse(userData); // แปลง string -> Object
            document.querySelector('input[name="depart_code"]').value = userObject.depart_code || "";
        } catch (error) {
            console.error("Error parsing user session data:", error);
            window.location.href = "login.php";
        }
    }

    getPlanOne(id); // เรียกฟังก์ชันดึงข้อมูลแผน
}

/* ดึงข้อมูลจาก id param */
async function getPlanOne(id) {
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=plan_one_and_check_doc&id=${id}&quarter=${quarter}`, {
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

        if(data.tb_doc_byuser){
            Swal.fire({
                text: "1 เอกสาร เบิกได้ 1 ครั้ง/ไตรมาส !!!",
                icon: "warning"
            }).then(() => {
                history.back();
            });
            
        }


        data.tb_plan_mats.forEach(item => {
            dataSelects.push({
                mat_code: item.mat_code,
                mat_name: item.mat_name,
                count_unit_name: item.count_unit_name,
                quantity_max: item[`quarter_${quarter}`],
            });
        });
        data.tb_plan_mats.forEach(item => {
            addNewRow(item, item.mat_code, item.count_unit_name); // Update this line to call addNewRow
        });
        addDataPlan(data.tb_plans);
        doc_type = data.tb_plans.plan_type;
        planRequLevelFromPlan = data.tb_plans.plan_requ_level ?? null;
        planBranchTypeFromPlan = data.tb_plans.branch_type ?? null;

        console.log(data);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

function addNewRow(item = {}, matlistsSelectId = "", countUnitSelectId = "") {
    const row = document.createElement("tr");
    const index = document.querySelectorAll("#table-body tr").length; // นับจำนวนแถวในตาราง
    const selectedMat = parameter.matlists.find(mat => mat.mat_code === matlistsSelectId);
    console.log(selectedMat);
    row.innerHTML = `
    <td class="product-checkbox">
        <input class="form-check-input rowCheck" type="checkbox" aria-label="...">
    </td>
    <td>
        <select disabled name="mat_code" class="form-control matlists">
            ${matlists(matlistsSelectId)}
        </select>
    </td>
    <td><input name="mat_name" value="${item.mat_name !== undefined ? item.mat_name : ""}" class="form-control" type="text"></td>
    <td>
        <input disabled name="quantity_max" class="form-control text-center quarter" type="text"
            value="${parseFloat(item[`quarter_${quarter}`])-parseFloat(item[`quarter_${quarter}_use`]) !== undefined ? parseFloat(item[`quarter_${quarter}`])-parseFloat(item[`quarter_${quarter}_use`]) : 0}" 
            min="0" id="quantity_max_${index}">
    </td>
    <td>
        <input name="quantity" class="form-control text-center quarter" type="text" value="0" min="0">
        <div id="validationFeedback_${item.id}" class="invalid-feedback"> * ขั้นต่ำ ${selectedMat.mat_min} และสูงสุดที่ ${selectedMat.mat_max} </div>
        <input name="qty_min" id="qty_min_${index}" class="form-control text-center" type="hidden" value="${selectedMat.mat_min}" >
        <input name="qty_max" id="qty_max_${index}" class="form-control text-center" type="hidden" value="${selectedMat.mat_max}" >
    </td>
    <td>
        <select disabled name="count_unit_name" class="form-control countUnits">
            ${countUnits(countUnitSelectId)}
        </select>
    </td>
    <td>
        <input name="deli_date" class="form-control text-center" type="date">
        <div id="validationFeedback_${item.id}" class="invalid-feedback"> * กรุณาป้อนข้อมูลให้ครบ </div>
    </td>
    <td style="display: none;">
        <input name="quantity_total" class="form-control total text-end" disabled type="number"  
            value="${item.quantity_total !== undefined ? item.quantity_total : 0}">
    </td>
    <td style="display: none;">
        <input name="plan_mat_id" class="form-control plan_mat_id text-end" disabled type="text"  
            value="${item.id}">
    </td>
    `;

    // เพิ่มแถวลงในตาราง
    document.querySelector("#table-body").appendChild(row);

    // ตรวจสอบจำนวนไม่ให้เกิน
    row.querySelector('input[name="quantity"]').addEventListener("input", function () {
        validateQuantity(this, index);
    });

    // อัปเดตค่า total อัตโนมัติ
    row.querySelectorAll('.quarter').forEach(input => {
        input.addEventListener('input', () => updateTotal(row));
    });

    // ใช้งาน select2 กับ dropdown
    $(row).find('.matlists').select2({
        placeholder: "-- เลือก --",
        allowClear: false,
        data: dataSelects.map(mat => ({
            id: mat.mat_code,
            text: `${mat.mat_code} - ${mat.mat_name}`
        })),
        templateResult: data => data.text || '',
        templateSelection: data => data.id || ''
    });

    $(row).find('.countUnits').select2({ placeholder: "-- เลือก --", allowClear: false });

    // เมื่อเลือก material อัปเดตชื่อ material อัตโนมัติ
    $(row).find('.matlists').on('change', function () {
        const selectedMatCode = $(this).val();
        const selectedMat = dataSelects.find(mat => mat.mat_code === selectedMatCode);
        if (selectedMat) {
            $(row).find('input[name="mat_name"]').val(selectedMat.mat_name);
        }
    });

    updateRowIndexes(); // อัปเดตดัชนีของแถว
    return row;
}

// ฟังก์ชันตรวจสอบค่า quantity ไม่ให้เกิน quantity_max
function validateQuantity(input, index) {

    let maxInput = document.getElementById(`quantity_max_${index}`);
    let maxVal = parseFloat(maxInput.value) || 0;
    let currentVal = parseFloat(input.value) || 0;
    // console.log(currentVal);
    if (currentVal > maxVal) {
        input.value = maxVal;  // ถ้าเกินให้ตั้งค่าเป็น max
    }else{
        input.value = currentVal;  // ถ้าไม่เกินให้ใช้ค่าปัจจุบัน
    }
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
    updateMaxTotal();
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

function updateMaxTotal() {
    let quantity_max = 0
    document.querySelectorAll("#table-body tr").forEach(row => {
        quantity_max += parseFloat(row.querySelector('input[name="quantity_max"]').value);
    });
    const netTotalInput = document.querySelector('input[name="net_total"]');
    if (netTotalInput) {
        netTotalInput.value = quantity_max;
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
    document.querySelector('input[name="doc_start"]').value = item.plan_start;
    document.querySelector('input[name="doc_end"]').value = item.plan_end;
    document.querySelector('input[name="depart_code"]').value = item.depart_code;
    document.querySelector('input[name="net_total"]').value = 0;
    document.querySelector('input[name="quantity_order"]').value = item.quantity_order;
    document.querySelector('input[name="quantity_outst"]').value = item.quantity_outst;
    document.querySelector('input[name="at_close"]').checked = item.at_close === "t";
    document.querySelector('input[name="const_id"]').checked = item.const_id === "t";
    document.querySelector('input[name="date_deli"]').value = item.date_deli;
    document.querySelector('input[name="date_requ"]').value = item.date_requ;
    document.querySelector('input[name="date_approv"]').value = item.date_approv;
    document.querySelector('input[name="time_deli_doc"]').value = item.time_deli_plan;
    document.querySelector('input[name="time_pick_doc"]').value = item.time_pick_plan;
    document.querySelector('input[name="note_1"]').value = item.note_1;
    document.querySelector('input[name="note_2"]').value = item.note_2;
    document.querySelector('input[name="note_3"]').value = item.note_3;

    document.querySelector('input[name="quarter"]').value = quarter;
}

async function createData() {
    // ดึค่าจากตาราง
    const tb_doc_mats = [];
    let checked = true;
    let checked_quantity = true;
    document.querySelectorAll("#table-body tr").forEach(row => {
        const rowData = {
            mat_code: row.querySelector('select[name="mat_code"]').value,
            mat_name: row.querySelector('input[name="mat_name"]').value,
            count_unit_name: row.querySelector('select[name="count_unit_name"]').value,
            plan_mat_id: row.querySelector('input[name="plan_mat_id"]').value,
            quarter: quarter,
            quantity: row.querySelector('input[name="quantity"]').value,
            deli_date: row.querySelector('input[name="deli_date"]').value
        };
        let row_deli_date = row.querySelector('input[name="deli_date"]');
        if(row_deli_date.value == ''){
            $(row_deli_date).addClass('is-invalid');
            checked = false;
        }else{
            $(row_deli_date).removeClass('is-invalid');
        }

        let row_quantity = row.querySelector('input[name="quantity"]');
        if(row_quantity.value == 0){
            $(row_quantity).addClass('is-invalid');
            checked = false;
        }else{
            $(row_quantity).removeClass('is-invalid');
        }
        if(parseInt(row_quantity.value) >= row.querySelector('input[name="qty_min"]').value && parseInt(row_quantity.value) <= row.querySelector('input[name="qty_max"]').value){
            $(row_quantity).removeClass('is-invalid');
            console.log('ok');
        }else{
            checked_quantity = false;
            $(row_quantity).addClass('is-invalid');
            console.log('no ok');
        }

        tb_doc_mats.push(rowData);
    });

    if(!checked){
        Swal.fire({
            text: "กรุณาป้อนข้อมูลให้ครบ !!!",
            icon: "warning"
        });
        return;
    }

    if(!checked_quantity){
        Swal.fire({
            text: "กรุณาตรวจสอบ ขั้นต่ำ และสูงสุด ในการเบิก !!!",
            icon: "warning"
        });
        return;
    }

    // ระดับคำขอ: ใช้จากแผนก่อน (ต้องตรง tb_plans.plan_requ_level) แล้วจึง fallback ตามเซสชัน
    let branch_type_merc = '-'
    let plan_requ_level = '-'

    const prlRaw = planRequLevelFromPlan != null ? String(planRequLevelFromPlan).trim() : ''
    if (prlRaw !== '' && prlRaw !== '-') {
        plan_requ_level = prlRaw
        const low = prlRaw.toLowerCase()
        if (low === 'จังหวัด' || low === 'province') branch_type_merc = 'จังหวัด'
        else if (low === 'เขต' || low === 'area') branch_type_merc = 'เขต'
        else if (low === 'สาขา' || low === 'branch') branch_type_merc = 'สาขา'
        else if (low === 'กยท' || low === 'head_office') branch_type_merc = 'กยท'
        else if (planBranchTypeFromPlan != null && String(planBranchTypeFromPlan).trim() !== '') {
            branch_type_merc = String(planBranchTypeFromPlan).trim()
        }
    }

    // ระดับคำขอ (fallback): ให้สอดคล้อง parcel-withdrawal-plan.js — depart_type_name → branch_type เซสชัน → position_code
    if (plan_requ_level === '-') {
        const dtn = String(userObject.depart_type_name ?? '').trim().toLowerCase()
        if (dtn === 'area') {
            branch_type_merc = 'เขต'
            plan_requ_level = 'เขต'
        } else if (dtn === 'province') {
            branch_type_merc = 'จังหวัด'
            plan_requ_level = 'จังหวัด'
        } else if (dtn === 'branch') {
            branch_type_merc = 'สาขา'
            plan_requ_level = 'สาขา'
        } else if (dtn === 'hq') {
            branch_type_merc = 'กยท'
            plan_requ_level = 'กยท'
        }
    }

    if (plan_requ_level === '-') {
        const orgBranchType = userObject.branch_type
        if (orgBranchType === 'เขต') {
            branch_type_merc = 'เขต'
            plan_requ_level = 'เขต'
        } else if (orgBranchType === 'จังหวัด') {
            branch_type_merc = 'จังหวัด'
            plan_requ_level = 'จังหวัด'
        } else if (orgBranchType === 'สาขา') {
            branch_type_merc = 'สาขา'
            plan_requ_level = 'สาขา'
        }
    }

    if (plan_requ_level === '-') {
        if (userObject.position_code == 'EMPY') {
            branch_type_merc = 'สาขา';
            plan_requ_level = 'สาขา';
        } else if (userObject.position_code == 'APBR') {
            branch_type_merc = 'สาขา';
            plan_requ_level = 'สาขา';
        } else if (userObject.position_code == 'APPV') {
            branch_type_merc = 'จังหวัด';
            plan_requ_level = 'จังหวัด';
        } else if (userObject.position_code == 'APAR') {
            branch_type_merc = 'เขต';
            plan_requ_level = 'เขต';
        } else if (userObject.position_code == 'APPC' || userObject.position_code == 'APHO') {
            branch_type_merc = 'กยท';
            plan_requ_level = 'กยท';
        }
    }

    const data_sent = {
        tb_docs: {
            doc_form_name: 'ใบเบิกเบิกพัสดุแบบพิมพ์',
            doc_requ_level: plan_requ_level,
            branch_type: branch_type_merc,//ระดับที่แสดงรอ merce
            doc_start: document.querySelector('input[name="doc_start"]').value,
            doc_end: document.querySelector('input[name="doc_end"]').value,
            depart_code: document.querySelector('input[name="depart_code"]').value,
            quantity_total: document.querySelector('input[name="net_total"]').value,
            quantity_order: document.querySelector('input[name="quantity_order"]').value,
            quantity_outst: document.querySelector('input[name="quantity_outst"]').value,
            date_deli: document.querySelector('input[name="date_deli"]').value,
            date_requ: document.querySelector('input[name="date_requ"]').value,
            date_approv: document.querySelector('input[name="date_approv"]').value,
            time_deli_doc: document.querySelector('input[name="time_deli_doc"]').value,
            time_pick_doc: document.querySelector('input[name="time_pick_doc"]').value,
            note_1: document.querySelector('input[name="note_1"]').value,
            note_2: document.querySelector('input[name="note_2"]').value,
            note_3: document.querySelector('input[name="note_3"]').value,
            branch_type: branch_type_merc,
            user_requ: userObject.user_code,
            user_requ_name: `${userObject.user_fname} ${userObject.user_lname}`,
            at_close: document.querySelector('input[name="at_close"]').checked,
            const_id: document.querySelector('input[name="const_id"]').checked,
            plan_id: id,
            branch_id: userObject.branch_id,
            province_id: userObject.province_id,
            area_id: userObject.area_id,
            head_office_id: userObject.head_office_id
        },
        tb_doc_mats: tb_doc_mats
    }

    console.log(data_sent); // แสดงค่าใน Console เพื่อตรวจสอบ
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/doc_controller.php?action=create_doc`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data_sent)
        });

        const result = await response.json(); 
        console.log(result);
        window.hideLoading();
        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: "เลขที่ใบเบิกพัสดุแบบพิมพ์คือ " + result.data,
            icon: "success"
        }).then(() => {
            window.location.href = "parcel-withdrawal-list.php";
        });

    } catch (error) {
        window.hideLoading();
        console.error('เกิดข้อผิดพลาด:', error);
        alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
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
    if (!dataSelects || !Array.isArray(dataSelects)) {
        return '<option value="">-- เลือก --</option>';
    }
    return dataSelects.map(b =>
        `<option value="${b.mat_code}" ${b.mat_code === selectedId ? "selected" : ""}>
           ${b.mat_code} - ${b.mat_name}
        </option>`
    ).join('');
}

beginFetch(); // เรียกใช้ฟังก์ชัน

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("createData").addEventListener("click", function () {
        createData();
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