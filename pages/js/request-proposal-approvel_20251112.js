/**
 * Request Proposal Edit Form JavaScript
 * สำหรับจัดการฟอร์มแก้ไขใบขอเสนอ
 */

// ========================================
// GLOBAL VARIABLES
// ========================================

let currentProposalId = null;
let originalProposalData = null;

// ========================================
// INITIALIZATION
// ========================================

// เริ่มต้นเมื่อ DOM โหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
	initializeEditForm();
});

/**
 * เริ่มต้นฟอร์มแก้ไข
 */
function initializeEditForm() {
    // แสดง popup การโหลดข้อมูล
    showLoadingMessage('กำลังเตรียมข้อมูล...');
    
    // ดึง ID ของใบขอเสนอจาก URL
    currentProposalId = getProposalIdFromUrl();
    
    if (!currentProposalId) {
        Swal.close();
        showErrorMessage('ไม่พบรหัสใบขอเสนอ กรุณาตรวจสอบ URL');
		return;
	}
	
    // ดึงข้อมูลผู้ใช้และแสดงรหัสหน่วยงาน
    loadUserDepartmentCode();

    // โหลดข้อมูลหลัก
    loadMainData();
    
    // โหลดข้อมูลวัสดุ
    loadMaterialData();
    
    // โหลดข้อมูลหน่วยและหน่วยเงิน
    loadUnitData();
    
    // โหลดข้อมูลบัญชี
    loadAccountData();
		
		// Initialize Select2 สำหรับ account category
		initializeAccountCategorySelect2();
		
		// ตั้งค่า event listeners
		setupEventListeners();
		
		// ตั้งค่า approval event listeners
		setupApprovalEventListeners();
		
    // ตั้งค่าปุ่มบันทึก
    setupUpdateButtons();
    
    // ตั้งค่าข้อมูลการอนุมัติ
    setupApprovalData();
    
    // โหลดข้อมูลใบขอเสนอ
    setTimeout(() => {
        loadProposalData();
        loadApprovalHistory();
    }, 2000); // รอให้ dropdown โหลดเสร็จก่อน
}

/**
 * ตั้งค่าข้อมูลการอนุมัติ
 */
function setupApprovalData() {
    try {
        // ดึงข้อมูลผู้ใช้ปัจจุบัน
        const currentUser = getCurrentUser();
        const currentUserName = getCurrentUserName();
        
        // ตั้งค่าผู้อนุมัติ
        const approverNameField = document.getElementById('st_hf_full_name');
        if (approverNameField) {
            approverNameField.value = currentUserName || currentUser || 'ไม่ระบุ';
        }
        
        // ตั้งค่าวันที่อนุมัติ (วันที่ปัจจุบัน)
        const currentDate = new Date();
        const formattedDate = currentDate.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const approvalDateField = document.getElementById('st_hf_date');
        if (approvalDateField) {
            approvalDateField.value = formattedDate;
        }
        
        
    } catch (error) {
        
    }
}

/**
 * ดึงชื่อผู้ใช้ปัจจุบัน
 */
function getCurrentUserName() {
    try {
        // ลองจาก sessionStorage raot_user_session (จากระบบ login)
        const raotUserSession = sessionStorage.getItem('raot_user_session');
        if (raotUserSession) {
            const userData = JSON.parse(raotUserSession);
            
            // ลองหา full_name หรือ user_name
            if (userData.full_name) {
                return userData.full_name;
            }
            if (userData.user_name) {
                return userData.user_name;
            }
            if (userData.first_name && userData.last_name) {
                return `${userData.first_name} ${userData.last_name}`;
            }
        }
        
        // ถ้าไม่เจอให้ใช้ user_code
        return getCurrentUser();
        
    } catch (error) {
        
        return 'ไม่ระบุ';
    }
}

/**
 * ตั้งค่า Event Listeners สำหรับการอนุมัติ
 */
function setupApprovalEventListeners() {
    // จัดการ checkbox อนุมัติ/ไม่อนุมัติ (เลือกได้เพียงอันเดียว)
    const approveCheckbox = document.getElementById('approve');
    const rejectCheckbox = document.getElementById('reject');
    const remarkField = document.getElementById('remark');
    const hideTextApprove = document.getElementById('hide_text_approve');
    
    if (approveCheckbox && rejectCheckbox) {
        approveCheckbox.addEventListener('change', function() {
            if (this.checked) {
                rejectCheckbox.checked = false;
                // ซ่อนข้อความ *จำเป็นต้องกรอก สำหรับหมายเหตุเมื่ออนุมัติ
                if (hideTextApprove) {
                    hideTextApprove.style.display = 'none';
                }
                if (remarkField) {
                    remarkField.required = false;
                }
            }
        });
        
        rejectCheckbox.addEventListener('change', function() {
            if (this.checked) {
                approveCheckbox.checked = false;
                // แสดงข้อความ *จำเป็นต้องกรอก สำหรับหมายเหตุเมื่อไม่อนุมัติ
                if (hideTextApprove) {
                    hideTextApprove.style.display = 'inline';
                }
                if (remarkField) {
                    remarkField.required = true;
                }
            }
        });
    }
    
    // จัดการปุ่มส่งผลการพิจารณา
    const submitApprovalBtn = document.getElementById('submit-approval-btn');
    if (submitApprovalBtn) {
        submitApprovalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleApprovalSubmission();
        });
    }
}

/**
 * จัดการการส่งผลการอนุมัติ
 */
function handleApprovalSubmission() {
    const approveCheckbox = document.getElementById('approve');
    const rejectCheckbox = document.getElementById('reject');
    const remarkField = document.getElementById('remark');
    
    // ตรวจสอบว่าเลือกผลการอนุมัติแล้วหรือไม่
    if (!approveCheckbox.checked && !rejectCheckbox.checked) {
        showErrorMessage('กรุณาเลือกผลการอนุมัติ', 'คุณต้องเลือก "อนุมัติ" หรือ "ไม่อนุมัติ"');
        return;
    }
    
    // ตรวจสอบหมายเหตุเมื่อไม่อนุมัติ
    if (rejectCheckbox.checked && (!remarkField.value || remarkField.value.trim() === '')) {
        showErrorMessage('กรุณากรอกหมายเหตุ', 'จำเป็นต้องระบุเหตุผลเมื่อไม่อนุมัติ');
        remarkField.focus();
        return;
    }
    
    // เตรียมข้อมูลสำหรับส่ง
    const approvalData = {
        proposal_id: currentProposalId,
        approval_status: approveCheckbox.checked ? 'approved' : 'rejected',
        remark: remarkField.value.trim(),
        approved_by: getCurrentUser(),
        approved_at: new Date().toISOString()
    };
    
    
    // แสดงการยืนยัน
    const statusText = approveCheckbox.checked ? 'อนุมัติ' : 'ไม่อนุมัติ';
    Swal.fire({
        title: `ยืนยันการ${statusText}`,
        text: `คุณต้องการ${statusText}ใบขอเสนอนี้หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: approveCheckbox.checked ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `ยืนยัน${statusText}`,
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            submitApprovalData(approvalData);
        }
    });
}

/**
 * ส่งข้อมูลการอนุมัติไปยัง API
 */
async function submitApprovalData(approvalData) {
    try {
        showLoadingMessage('กำลังบันทึกผลการอนุมัติ...');
        
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/proposal/request_proposal_controller.php?action=approve';
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(approvalData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.close();
            
            // โหลดประวัติการอนุมัติใหม่
            await loadApprovalHistory();
            
            showSuccessMessage('บันทึกผลการอนุมัติสำเร็จ', 'ระบบได้บันทึกผลการพิจารณาเรียบร้อยแล้ว');
            
            // กลับไปหน้า list หลังจาก 2 วินาที
            setTimeout(() => {
                window.location.href = 'request-proposal-approvel-list.php';
            }, 2000);
        } else {
            throw new Error(result.message || 'เกิดข้อผิดพลาดในการบันทึก');
        }
        
    } catch (error) {
        
        Swal.close();
        showErrorMessage('เกิดข้อผิดพลาดในการบันทึก', error.message);
    }
}

/**
 * ดึง ID ของใบขอเสนอจาก URL
 */
function getProposalIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

// ========================================
// DATA LOADING
// ========================================

function loadMainData() {
    loadFormTypes();
    loadFactories();
    loadWarehouses();
    loadPurchaseGroups();
    loadExpenPr();
}

function loadMaterialData() {
    loadMatlist();
    loadAssets();
}

function loadUnitData() {
    loadCountUnits();
    loadMonetaryUnits();
}

function loadAccountData() {
    loadGLAccounts();
    loadCostCenters();
    loadTaxes();
    loadFundings();
    loadScopes();
    loadWrdFunds();
    loadLiabilities();
    loadMrdGroups();
    loadAssetCategories();
}

function setupEventListeners() {
    setupAccountCategoryHandler();
    setupPriceCalculation();
    setupClearAndBackButtons();
}

/**
 * Initialize Select2 for Account Category dropdown
 */
function initializeAccountCategorySelect2() {
    const accountCategorySelect = $('#account-category-select');
    
    if (accountCategorySelect.length > 0) {
        // ตรวจสอบว่า Select2 ถูก initialize แล้วหรือไม่
        if (accountCategorySelect.hasClass('select2-hidden-accessible')) {
            accountCategorySelect.select2('destroy');
        }
        
        // Initialize Select2
        accountCategorySelect.select2({
            placeholder: 'กรุณาเลือกหมวดการกำหนดบัญชี',
            allowClear: false,
            width: '100%'
        });
        
    }
}

/**
 * ดึงข้อมูลรหัสหน่วยงานจาก session
 */
function loadUserDepartmentCode() {
    try {
        const userSession = sessionStorage.getItem('raot_user_session');
        
        if (userSession) {
            const userData = JSON.parse(userSession);
            const departmentCode = userData.depart_code || '';
            
            const departmentField = document.getElementById('department-code');
            if (departmentField) {
                departmentField.value = departmentCode;
            }
        } else {
            showErrorMessage('ไม่พบข้อมูลผู้ใช้ กรุณาเข้าสู่ระบบใหม่');
        }
    } catch (error) {
        showErrorMessage('เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้');
    }
}

// ========================================
// API LOADING FUNCTIONS
// ========================================

async function loadFormTypes() {
    try {
        const selectEl = document.getElementById('form-type-select');
        if (!selectEl) return;

        if ($('#form-type-select').hasClass('select2-hidden-accessible')) {
            $('#form-type-select').select2('destroy');
        }

        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/form_type_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const formTypeOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.form_type_name
                }))
            ];
            
            $('#form-type-select').select2({
                data: formTypeOptions,
                placeholder: 'กรุณาเลือกประเภทแบบฟอร์ม',
                allowClear: false,
                width: '100%'
            });
        }
	} catch (err) {
        
    }
}

async function loadFactories() {
    try {
        const selectEl = document.getElementById('factory-select');
        if (!selectEl) return;

        if ($('#factory-select').hasClass('select2-hidden-accessible')) {
            $('#factory-select').select2('destroy');
        }

	const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/factory_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const factoryOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.factory_name
                }))
            ];
            
            $('#factory-select').select2({
                data: factoryOptions,
                placeholder: 'กรุณาเลือกโรงงาน',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        
    }
}

async function loadWarehouses() {
    try {
        const selectEl = document.getElementById('warehouse-select');
        if (!selectEl) return;

        if ($('#warehouse-select').hasClass('select2-hidden-accessible')) {
            $('#warehouse-select').select2('destroy');
        }

        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/warehouse_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const warehouseOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.warehouse_name
                }))
            ];
            
            $('#warehouse-select').select2({
                data: warehouseOptions,
                placeholder: 'กรุณาเลือกที่เก็บสินค้า',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        
    }
}

async function loadPurchaseGroups() {
    try {
        const selectEl = document.getElementById('purchase-group-select');
        if (!selectEl) return;

        if ($('#purchase-group-select').hasClass('select2-hidden-accessible')) {
            $('#purchase-group-select').select2('destroy');
        }

        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/purchase_group_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const purchaseGroupOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.purchase_group_name
                }))
            ];
            
            $('#purchase-group-select').select2({
                data: purchaseGroupOptions,
                placeholder: 'กรุณาเลือกกลุ่มการจัดซื้อ',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        
    }
}

async function loadMatlist() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/matlist_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.matlistData = result.data;
        }
    } catch (err) {
        
    }
}

async function loadAssets() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/asset_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.assetData = result.data;
        }
    } catch (err) {
        
    }
}

async function loadCountUnits() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/count_unit_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.unitData = result.data;
        }
    } catch (err) {
        
    }
}

async function loadMonetaryUnits() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/monetary_unit_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            // เก็บข้อมูลไว้ใน global variable
            window.monetaryUnitData = result.data;
            
            // หา dropdown หน่วยเงินทั้งหมด
            const monetaryUnitSelects = document.querySelectorAll('.monetary-unit-select');
            
            // เตรียมข้อมูลสำหรับ dropdown
            const monetaryUnitOptions = [
                { value: '', text: 'กรุณาเลือกหน่วยเงิน' },
                ...result.data.map(item => ({
                    value: item.monetary_unit_name_en,
                    text: item.monetary_unit_name_en,
                    th_name: item.monetary_unit_name_th
                }))
            ];
            
            // เติมข้อมูลใน dropdown หน่วยเงินทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            monetaryUnitSelects.forEach(selectEl => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                monetaryUnitOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.thName = option.th_name || '';
                    selectEl.appendChild(optionEl);
                });
                
                // ตั้งค่า event listener สำหรับการเลือกหน่วยเงิน
                selectEl.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const thName = selectedOption.dataset.thName;
                    
                    // หา dropdown หน่วยเงินไทยในแถวเดียวกัน
                    const container = this.closest('.row') || this.closest('.card-body');
                    if (container && thName) {
                        const thSelect = container.querySelector('.monetary-unit-th-select');
                        if (thSelect) {
                            // หา option ที่มี value ตรงกับ thName
                            const thOptions = thSelect.querySelectorAll('option');
                            thOptions.forEach(option => {
                                if (option.value === thName) {
                                    thSelect.value = thName;
                                }
                            });
                        }
                    }
                });
            });
            
            // เติมข้อมูลใน dropdown หน่วยเงินไทยทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            const thSelects = document.querySelectorAll('.monetary-unit-th-select');
            thSelects.forEach(selectEl => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                const thOptions = [
                    { value: '', text: 'กรุณาเลือกหน่วยเงิน' },
                    ...result.data.map(item => ({
                        value: item.monetary_unit_name_th,
                        text: item.monetary_unit_name_th
                    }))
                ];
                
                thOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
            });
        }
    } catch (err) {
        
    }
}

// ========================================
// LOAD OTHER DROPDOWNS
// ========================================

async function loadGLAccounts() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/liability_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.liabilityData = result.data;
            
            const glSelects = document.querySelectorAll('#gl-account-select, #gl-account-select-asset, #gl-account-select-cost-center');
            
            const glOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.liability_code} - ${item.liability_name}`
                }))
            ];
            
            glSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                glOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
                
                setupGLToLiabilitySync(selectEl);
            });
        }
    } catch (err) {
        
    }
}

async function loadCostCenters() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/cost_center_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const costCenterSelects = document.querySelectorAll('#cost-center-select, #cost-center-select-asset, #cost-center-select-cost-center');
            
            const costCenterOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.cost_center_code} - ${item.cost_center_name}`
                }))
            ];
            
            costCenterSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                costCenterOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        
    }
}

async function loadTaxes() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/tax_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const taxSelects = document.querySelectorAll('#tax-select, #tax-select-asset, #tax-select-cost-center');
            
            const taxOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.tax_name} (${item.tax_rate}%)`
                }))
            ];
            
            taxSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                taxOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        
    }
}

async function loadFundings() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/funding_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const fundingSelects = document.querySelectorAll('#fund-select, #fund-select-asset, #fund-select-cost-center');
            
            const fundingOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.funding_code} - ${item.funding_name}`
                }))
            ];
            
            fundingSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                fundingOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        
    }
}

async function loadScopes() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/scope_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const scopeSelects = document.querySelectorAll('#scope-select, #scope-select-asset, #scope-select-cost-center');
            
            const scopeOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.scope_code} - ${item.scope_name}`
                }))
            ];
            
            scopeSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                scopeOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        
    }
}

async function loadWrdFunds() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/wrd_fund_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const wrdFundSelects = document.querySelectorAll('#funds-center-select, #funds-center-select-asset, #funds-center-select-cost-center');
            
            const wrdFundOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.fund_code} - ${item.fund_name}`
                }))
            ];
            
            wrdFundSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                wrdFundOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        
    }
}

async function loadLiabilities() {
    try {
        let result;
        if (window.liabilityData) {
            result = { status: 'success', data: window.liabilityData };
        } else {
            const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
            const apiUrl = basePath + '/controllers/parameter/liability_controller.php?action=all';

            const response = await fetch(apiUrl, { 
                method: 'GET',
                headers: { 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
            }
            
            result = await response.json();
            
            if (result && result.status === 'success' && Array.isArray(result.data)) {
                window.liabilityData = result.data;
            }
        }

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            const liabilitySelects = document.querySelectorAll('#liability-select, #liability-select-asset, #liability-select-cost-center');
            
            const liabilityOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.liability_code} - ${item.liability_name}`
                }))
            ];
            
            liabilitySelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                liabilityOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
                
                setupLiabilityToGLSync(selectEl);
            });
        }
    } catch (err) {
        
    }
}

/**
 * ดึงรายการหมวดหมู่สินทรัพย์จาก API สำหรับส่วน A สินทรัพย์
 */
async function loadAssetCategories() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/asset_category_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.assetCategoryData = result.data;
        }
    } catch (err) {
        
    }
}

async function loadMrdGroups() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/mrd_group_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.mrdGroupData = result.data;
        }
    } catch (err) {
        
    }
}

/**
 * ดึงรายการค่าใช้จ่าย PR จาก API (สำหรับ TYPE K)
 */
async function loadExpenPr() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/expen_pr_controller.php?action=all';

        const response = await fetch(apiUrl, { 
            method: 'GET',
            headers: { 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        
        const result = await response.json();

        if (result && result.status === 'success' && Array.isArray(result.data)) {
            window.expenPrData = result.data;
        }
    } catch (err) {
        
    }
}

// ========================================
// G/L AND LIABILITY SYNC
// ========================================

function setupGLToLiabilitySync(glSelectEl) {
    if (!glSelectEl) return;
    
    const glId = glSelectEl.id;
    let liabilityId = '';
    
    if (glId === 'gl-account-select') {
        liabilityId = 'liability-select';
    } else if (glId === 'gl-account-select-asset') {
        liabilityId = 'liability-select-asset';
    } else if (glId === 'gl-account-select-cost-center') {
        liabilityId = 'liability-select-cost-center';
    }
    
    if (!liabilityId) return;
    
    $(glSelectEl).on('change.glsync', function(e) {
        const selectedValue = $(this).val();
        const liabilitySelect = document.getElementById(liabilityId);
        
        if (liabilitySelect && selectedValue) {
            $(liabilitySelect).off('change.liabilitysync');
            $(liabilitySelect).val(selectedValue).trigger('change');
            
            setTimeout(() => {
                setupLiabilityToGLSync(liabilitySelect);
            }, 100);
        }
    });
}

function setupLiabilityToGLSync(liabilitySelectEl) {
    if (!liabilitySelectEl) return;
    
    const liabilityId = liabilitySelectEl.id;
    let glId = '';
    
    if (liabilityId === 'liability-select') {
        glId = 'gl-account-select';
    } else if (liabilityId === 'liability-select-asset') {
        glId = 'gl-account-select-asset';
    } else if (liabilityId === 'liability-select-cost-center') {
        glId = 'gl-account-select-cost-center';
    }
    
    if (!glId) return;
    
    $(liabilitySelectEl).on('change.liabilitysync', function(e) {
        const selectedValue = $(this).val();
        const glSelect = document.getElementById(glId);
        
        if (glSelect && selectedValue) {
            $(glSelect).off('change.glsync');
            $(glSelect).val(selectedValue).trigger('change');
            
            setTimeout(() => {
                setupGLToLiabilitySync(glSelect);
            }, 100);
        }
    });
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * หาชื่อกลุ่มวัสดุจาก ID
 */
function getMrdGroupNameById(groupId) {
    if (!groupId || !window.mrdGroupData) {
        return '';
    }
    
    const group = window.mrdGroupData.find(item => item.id == groupId);
    return group ? group.name : '';
}

// ========================================
// PROPOSAL DATA LOADING
// ========================================

async function loadProposalData() {
    try {
        // อัปเดตข้อความการโหลด
        showLoadingMessage('กำลังโหลดข้อมูลใบขอเสนอ...');
        
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/proposal/request_proposal_controller.php?action=get_with_details&id=' + currentProposalId;

        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            originalProposalData = result.data;
            
            // อัปเดตข้อความการโหลด
            showLoadingMessage('กำลังแสดงข้อมูล...');
            
            populateFormWithData(result.data);
            
            // ปิด popup หลังจากแสดงข้อมูลเสร็จ
            setTimeout(() => {
                Swal.close();
            }, 1500);
        } else {
            throw new Error(result.message || 'Failed to load proposal data');
        }

    } catch (error) {
        
        Swal.close();
        showErrorMessage('เกิดข้อผิดพลาดในการโหลดข้อมูล', error.message);
    }
}

function populateFormWithData(data) {
    try {
        // 1. ตั้งค่า Account Category ก่อน
        const accountCategorySelect = document.getElementById('account-category-select');
        if (accountCategorySelect && data.account_category) {
            accountCategorySelect.value = data.account_category;
            
            // Trigger change event สำหรับ Select2
            $('#account-category-select').val(data.account_category).trigger('change');
        }
        
        // 2. แสดง section ที่ถูกต้อง
        showCorrectSection(data.account_category);
        
        // 3. เติมข้อมูลส่วนหัว
        populateHeaderData(data);
        
        // 4. เติมข้อมูล section ที่เลือก (รอให้ section แสดงก่อน)
        setTimeout(() => {
            populateAccountCategoryData(data);
            
            // คำนวณยอดรวมหลังจากโหลดข้อมูลเสร็จ
            calculateGrandTotal();
        }, 1000);
        
    } catch (error) {
        
        showErrorMessage('เกิดข้อผิดพลาดในการแสดงข้อมูล');
    }
}

function populateHeaderData(data) {
    setTimeout(() => {
        $('#form-type-select').val(data.form_type_id).trigger('change');
    }, 500);
    
    if (data.start_date) {
        document.querySelector('input[name="start_date"]').value = data.start_date;
    }
    if (data.end_date) {
        document.querySelector('input[name="end_date"]').value = data.end_date;
    }
    
    if (data.department_code) {
        document.getElementById('department-code').value = data.department_code;
    }
    
    if (data.header_note) {
        document.getElementById('header_note').value = data.header_note;
    }
    
    if (data.description) {
        document.querySelector('textarea[name="description"]').value = data.description;
    }
    
    // Account category ถูกตั้งค่าแล้วใน populateFormWithData
    
    setTimeout(() => {
        $('#factory-select').val(data.factory_id).trigger('change');
    }, 500);
    
    setTimeout(() => {
        $('#warehouse-select').val(data.warehouse_id).trigger('change');
    }, 500);
    
    setTimeout(() => {
        $('#purchase-group-select').val(data.purchase_group_id).trigger('change');
    }, 500);
}

function populateAccountCategoryData(data) {
    const accountCategory = data.account_category || 'N';
    
    switch(accountCategory) {
        case 'N':
            populateNoCategoryData(data);
            break;
        case 'A':
            populateAssetData(data);
            break;
        case 'K':
            populateCostCenterData(data);
            break;
    }
}

function showCorrectSection(accountCategory) {
	hideAllAccountSections();
	
    switch(accountCategory) {
        case 'A':
		showAccountSection('asset-section');
            break;
        case 'K':
		showAccountSection('cost-center-section');
            break;
        case 'N':
        case '':
        default:
		showAccountSection('no-category-section');
            break;
    }
}

function populateNoCategoryData(data) {
    const section = document.getElementById('no-category-section');
    if (!section) return;
    
    populateSectionForm(section, data);
    
    if (data.items && data.items.length > 0) {
        populateTableData(section, data.items);
    }
}

function populateAssetData(data) {
    const section = document.getElementById('asset-section');
    if (!section) return;
    
    populateSectionForm(section, data);
    
    if (data.items && data.items.length > 0) {
        populateTableData(section, data.items);
    }
}

function populateCostCenterData(data) {
    const section = document.getElementById('cost-center-section');
    if (!section) return;
    
    populateSectionForm(section, data);
    
    if (data.items && data.items.length > 0) {
        populateTableData(section, data.items);
    }
}

function populateSectionForm(section, data) {
    setTimeout(() => {
        const monetaryUnitEn = section.querySelector('.monetary-unit-select');
        if (monetaryUnitEn && data.monetary_unit_en) {
            monetaryUnitEn.value = data.monetary_unit_en;
        }
        
        const monetaryUnitTh = section.querySelector('.monetary-unit-th-select');
        if (monetaryUnitTh && data.monetary_unit_th) {
            monetaryUnitTh.value = data.monetary_unit_th;
        }
        
        const taxSelect = section.querySelector('[id*="tax-select"]');
        if (taxSelect && data.tax_id) {
            $(taxSelect).val(data.tax_id).trigger('change');
        }
        
        const deliveryDate = section.querySelector('input[name="delivery_date"]');
        if (deliveryDate && data.delivery_date) {
            deliveryDate.value = data.delivery_date;
        }
        
        const glSelect = section.querySelector('[id*="gl-account-select"]');
        if (glSelect && data.gl_account_id) {
            $(glSelect).val(data.gl_account_id).trigger('change');
        }
        
        const costCenterSelect = section.querySelector('[id*="cost-center-select"]');
        if (costCenterSelect && data.cost_center_id) {
            $(costCenterSelect).val(data.cost_center_id).trigger('change');
        }
        
        const fundSelect = section.querySelector('[id*="fund-select"]');
        if (fundSelect && data.fund_id) {
            $(fundSelect).val(data.fund_id).trigger('change');
        }
        
        const scopeSelect = section.querySelector('[id*="scope-select"]');
        if (scopeSelect && data.scope_id) {
            $(scopeSelect).val(data.scope_id).trigger('change');
        }
        
        const fundsCenterSelect = section.querySelector('[id*="funds-center-select"]');
        if (fundsCenterSelect && data.funds_center_id) {
            $(fundsCenterSelect).val(data.funds_center_id).trigger('change');
        }
        
        const liabilitySelect = section.querySelector('[id*="liability-select"]');
        if (liabilitySelect && data.liability_id) {
            $(liabilitySelect).val(data.liability_id).trigger('change');
        }
        
        // ข้อความต่างๆ
        const purchaseText = section.querySelector('textarea[name="purchase_text"]');
        if (purchaseText && data.purchase_text) {
            purchaseText.value = data.purchase_text;
        }
        
        const itemNote = section.querySelector('textarea[name="item_note"]');
        if (itemNote && data.item_note) {
            itemNote.value = data.item_note;
        }
        
        const deliveryText = section.querySelector('textarea[name="delivery_text"]');
        if (deliveryText && data.delivery_text) {
            deliveryText.value = data.delivery_text;
        }
        
        const materialOrderText = section.querySelector('textarea[name="material_order_text"]');
        if (materialOrderText && data.material_order_text) {
            materialOrderText.value = data.material_order_text;
        }
        
        const quotationText = section.querySelector('textarea[name="quotation_text"]');
        if (quotationText && data.quotation_text) {
            quotationText.value = data.quotation_text;
        }
    }, 1000);
}

function populateTableData(section, items) {
    const tbody = section.querySelector('tbody');
    if (!tbody || !items || items.length === 0) return;
    
    tbody.innerHTML = '';
    
    items.forEach((item, index) => {
        const row = createRowFromData(item, section.id);
        tbody.appendChild(row);
    });
    
    setTimeout(() => {
        calculateGrandTotal();
    }, 500);
}

function createRowFromData(item, sectionId) {
    const row = document.createElement('tr');
    row.className = 'product-list';
    
    if (sectionId === 'cost-center-section') {
        row.innerHTML = `
            <td class="product-checkbox">
                <input class="form-check-input" type="checkbox" value="" aria-label="...">
            </td>
            <td contenteditable="true" class="text-start">${item.short_text || 'รายการค่าใช้จ่าย'}</td>
            <td contenteditable="true">${item.quantity || 0}</td>
            <td>
                <select class="form-control unit-select" name="unit" style="width: 100%">
                    <option value="">กรุณาเลือกหน่วย</option>
                </select>
            </td>
            <td class="text-start">
                <select class="form-control expen-pr-select" name="expen_pr_id" style="width: 100%">
                    <option value="">กรุณาเลือกรายการค่าใช้จ่าย</option>
                </select>
            </td>
            <td contenteditable="true">${item.unit_price || '0.00'}</td>
            <td contenteditable="true" class="text-end">${item.total_price || '0.00'}</td>
        `;
    } else {
        const selectClass = sectionId === 'no-category-section' ? 'matlist-select' : 'asset-select';
        const selectName = sectionId === 'no-category-section' ? 'mat_code' : 'asset_code';
        const placeholder = sectionId === 'no-category-section' ? 'กรุณาเลือกวัสดุ' : 'กรุณาเลือกรหัสสินทรัพย์';
        const groupSelectClass = sectionId === 'asset-section' ? 'asset-category-select' : 'mrd-group-select';
        const groupSelectName = sectionId === 'asset-section' ? 'asset_category_id' : 'mrd_group_id';
        
        row.innerHTML = `
            <td class="product-checkbox">
                <input class="form-check-input" type="checkbox" value="" aria-label="...">
            </td>
            <td class="text-start">
                <select class="form-control ${selectClass}" name="${selectName}">
                    <option value="">${placeholder}</option>
                </select>
            </td>
            <td contenteditable="true">${item.quantity || 0}</td>
            <td>
                <select class="form-control unit-select" name="unit" style="width: 100%">
                    <option value="">กรุณาเลือกหน่วย</option>
                </select>
            </td>
            <td class="text-start">
                <select class="form-control ${groupSelectClass}" name="${groupSelectName}" style="width: 100%">
                    <option value="">กรุณาเลือกกลุ่มวัสดุ</option>
                </select>
            </td>
            <td contenteditable="true">${item.unit_price || '0.00'}</td>
            <td contenteditable="true" class="text-end">${item.total_price || '0.00'}</td>
        `;
    }
    
    setTimeout(() => {
        fillDropdownsInRow(row);
        
        // รอให้ dropdown เติมข้อมูลเสร็จก่อนแล้วค่อย set ค่า
        setTimeout(() => {
            if (item.material_code || item.asset_code) {
                const matSelect = row.querySelector('select[name="mat_code"], select[name="asset_code"]');
                if (matSelect) {
                    
                    $(matSelect).val(item.material_code || item.asset_code).trigger('change');
                }
            }
            
            if (item.unit_code) {
                const unitSelect = row.querySelector('.unit-select');
                if (unitSelect) {
                    
                    $(unitSelect).val(item.unit_code).trigger('change');
                }
            }
            
            // เติมค่ากลุ่มวัสดุ/Asset Category/Expen PR (รอให้ dropdown โหลดเสร็จก่อน)
            if (item.group_name) {
                const groupSelect = row.querySelector('.asset-category-select, .mrd-group-select, .expen-pr-select');
                if (groupSelect) {
                    
                    
                    
                    $(groupSelect).val(item.group_name).trigger('change');
                    
                }
            }
        }, 800); // รอเพิ่มอีก 800ms หลังจาก fill dropdown
        
        setupRowEventListeners(row);
    }, 1000);
    
    return row;
}

// ========================================
// EVENT HANDLERS
// ========================================

function setupAccountCategoryHandler() {
    const accountCategorySelect = document.getElementById('account-category-select');
    
    if (accountCategorySelect) {
        accountCategorySelect.addEventListener('change', function() {
            const selectedValue = this.value;
            toggleAccountCategorySections(selectedValue);
        });
    }
}

function toggleAccountCategorySections(selectedValue) {
    hideAllAccountSections();
    
    switch(selectedValue) {
        case 'A':
            showAccountSection('asset-section');
            break;
        case 'K':
            showAccountSection('cost-center-section');
            break;
        case 'N':
        case '':
        case 'ไม่เลือก':
            showAccountSection('no-category-section');
            break;
        default:
            break;
	}
}

function hideAllAccountSections() {
    const sections = [
        'no-category-section',
        'asset-section', 
        'cost-center-section'
    ];
    
	sections.forEach(sectionId => {
		const section = document.getElementById(sectionId);
		if (section) {
			section.style.display = 'none';
		}
	});
}

function showAccountSection(sectionId) {
	const section = document.getElementById(sectionId);
	if (section) {
        section.style.display = 'block';
    }
}

function setupPriceCalculation() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr.product-list');
        
        rows.forEach(row => {
            setupRowEventListeners(row);
        });
    });
}

function setupRowEventListeners(row) {
    // ตรวจสอบว่าแถวนี่อยู่ในส่วน K หรือไม่
    const section = row.closest('[id*="-section"]');
    const isCostCenterSection = section && section.id === 'cost-center-section';
    
    // กำหนดตำแหน่ง column ตามส่วน
    const quantityColumn = isCostCenterSection ? 3 : 3;
    const priceColumn = isCostCenterSection ? 6 : 6;
    const totalPriceColumn = isCostCenterSection ? 7 : 7;
    
    const quantityCell = row.querySelector(`td:nth-child(${quantityColumn})`);
    const unitPriceCell = row.querySelector(`td:nth-child(${priceColumn})`);
    const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceColumn})`);
    
    if (quantityCell && unitPriceCell && totalPriceCell) {
        if (isCostCenterSection) {
            // สำหรับ TYPE K ที่ใช้ contenteditable ต้องการ event หลายแบบ
            quantityCell.addEventListener('input', function() {
                calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                setTimeout(() => calculateGrandTotal(), 100);
            });
            
            unitPriceCell.addEventListener('input', function() {
                calculateTotalPrice(this, quantityCell, totalPriceCell);
                setTimeout(() => calculateGrandTotal(), 100);
            });
            
            quantityCell.addEventListener('blur', function() {
                calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                calculateGrandTotal();
            });
            
            unitPriceCell.addEventListener('blur', function() {
                calculateTotalPrice(this, quantityCell, totalPriceCell);
                calculateGrandTotal();
            });
            
            quantityCell.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                    calculateGrandTotal();
                    this.blur();
                }
            });
            
            unitPriceCell.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculateTotalPrice(this, quantityCell, totalPriceCell);
                    calculateGrandTotal();
                    this.blur();
                }
            });
        } else {
            // สำหรับ TYPE อื่นๆ
            quantityCell.addEventListener('input', function() {
                calculateTotalPrice(this, unitPriceCell, totalPriceCell);
            });
            
            unitPriceCell.addEventListener('input', function() {
                calculateTotalPrice(this, quantityCell, totalPriceCell);
            });
        }
    }
}

function calculateTotalPrice(triggerCell, otherCell, totalCell) {
    try {
        // ใช้ innerText หรือ textContent ตามสถานการณ์ contenteditable
        let triggerText, otherText;
        
        if (triggerCell.getAttribute && triggerCell.getAttribute('contenteditable') === 'true') {
            triggerText = triggerCell.innerText.trim().replace(/,/g, '');
        } else {
            triggerText = (triggerCell.textContent || triggerCell.innerText || '').replace(/,/g, '');
        }
        
        if (otherCell.getAttribute && otherCell.getAttribute('contenteditable') === 'true') {
            otherText = otherCell.innerText.trim().replace(/,/g, '');
        } else {
            otherText = (otherCell.textContent || otherCell.innerText || '').replace(/,/g, '');
        }
        
        const quantity = parseFloat(triggerText) || 0;
        const unitPrice = parseFloat(otherText) || 0;
        
        const totalPrice = quantity * unitPrice;
        
        if (totalPrice > 0) {
            const formattedPrice = totalPrice.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            if (totalCell.getAttribute && totalCell.getAttribute('contenteditable') === 'true') {
                totalCell.innerText = formattedPrice;
            } else {
                totalCell.textContent = formattedPrice;
            }
        } else {
            if (totalCell.getAttribute && totalCell.getAttribute('contenteditable') === 'true') {
                totalCell.innerText = '0.00';
            } else {
                totalCell.textContent = '0.00';
            }
        }
        
        calculateGrandTotal();
        
    } catch (err) {
        
    }
}

function calculateGrandTotal() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr.product-list');
        let grandTotal = 0;
        let totalQuantity = 0;
        
        // ตรวจสอบว่า table อยู่ในส่วน K หรือไม่
        const section = table.closest('[id*="-section"]');
        const isCostCenterSection = section && section.id === 'cost-center-section';
        
        // กำหนดตำแหน่ง column ตามส่วน
        const totalPriceColumn = isCostCenterSection ? 7 : 7;
        const quantityColumn = isCostCenterSection ? 3 : 3;
        
        rows.forEach(row => {
            const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceColumn})`);
            const quantityCell = row.querySelector(`td:nth-child(${quantityColumn})`);
            
            if (totalPriceCell) {
                let priceText;
                if (totalPriceCell.getAttribute && totalPriceCell.getAttribute('contenteditable') === 'true') {
                    priceText = totalPriceCell.innerText.replace(/,/g, '');
                } else {
                    priceText = totalPriceCell.textContent.replace(/,/g, '');
                }
                const price = parseFloat(priceText) || 0;
                grandTotal += price;
            }
            
            if (quantityCell) {
                let quantityText;
                if (quantityCell.getAttribute && quantityCell.getAttribute('contenteditable') === 'true') {
                    quantityText = quantityCell.innerText.replace(/,/g, '');
                } else {
                    quantityText = quantityCell.textContent.replace(/,/g, '');
                }
                const quantity = parseFloat(quantityText) || 0;
                totalQuantity += quantity;
            }
        });
        
        const totalQuantityCell = table.querySelector('tfoot tr:first-child td:last-child');
        const grandTotalCell = table.querySelector('tfoot tr:last-child td:last-child');
        
        if (totalQuantityCell) {
            totalQuantityCell.textContent = totalQuantity.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        if (grandTotalCell) {
            grandTotalCell.textContent = grandTotal.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
}

function fillDropdownsInRow(row) {
    const matSelect = row.querySelector('select[name="mat_code"], select[name="asset_code"]');
    if (matSelect) {
        // ตรวจสอบว่าเป็น dropdown ประเภทไหน
        const isAsset = matSelect.classList.contains('asset-select') || matSelect.name === 'asset_code';
        
        let dataSource;
        if (isAsset) {
            dataSource = window.assetData;
        } else {
            // ส่วนไม่เลือกและอื่นๆ ใช้ matlistData
            dataSource = window.matlistData;
        }
        
        if (dataSource) {
            // ล้าง options เดิมก่อนเติมข้อมูลใหม่
            const firstOption = matSelect.querySelector('option[value=""]');
            matSelect.innerHTML = '';
            if (firstOption) {
                matSelect.appendChild(firstOption.cloneNode(true));
            } else {
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = isAsset ? 'กรุณาเลือกรหัสสินทรัพย์' : 'กรุณาเลือกวัสดุ';
                matSelect.appendChild(defaultOption);
            }
            
            // เพิ่ม options ใหม่
            dataSource.forEach(item => {
                const option = document.createElement('option');
                if (isAsset) {
                    option.value = item.asset_code;
                    option.textContent = `${item.asset_code} - ${item.asset_name}`;
                    option.dataset.assetName = item.asset_name;
                    option.dataset.assetCategoryId = item.asset_category_id || '';
                } else {
                    option.value = item.mat_code;
                    option.textContent = `${item.mat_code} - ${item.mat_name}`;
                    option.dataset.matName = item.mat_name;
                    option.dataset.groupName = item.group_name || '';
                }
                matSelect.appendChild(option);
            });
            
            // ตั้งค่า event listener เพื่อ auto-select กลุ่มวัสดุเมื่อเลือกรหัสสินทรัพย์
            if (isAsset) {
                $(matSelect).on('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const assetCategoryId = selectedOption.dataset.assetCategoryId;
                    
                    if (assetCategoryId) {
                        // หา dropdown กลุ่มวัสดุในแถวเดียวกัน
                        const row = this.closest('tr');
                        if (row) {
                            const categorySelect = row.querySelector('.asset-category-select');
                            if (categorySelect) {
                                $(categorySelect).val(assetCategoryId).trigger('change');
                            }
                        }
                    }
                });
            }
        }
    }
    
    const unitSelect = row.querySelector('.unit-select');
    if (unitSelect && window.unitData) {
        // ล้าง options เดิมก่อนเติมข้อมูลใหม่
        const firstOption = unitSelect.querySelector('option[value=""]');
        unitSelect.innerHTML = '';
        if (firstOption) {
            unitSelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกหน่วย';
            unitSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.unitData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.count_unit_code;
            option.textContent = item.count_unit_name;
            unitSelect.appendChild(option);
        });
        
        $(unitSelect).select2({
            placeholder: 'กรุณาเลือกหน่วย',
            allowClear: true,
            width: '100%'
        });
    }
    
    // เติมข้อมูลหน่วยเงิน (อังกฤษ)
    const monetaryUnitSelect = row.querySelector('.monetary-unit-select');
    if (monetaryUnitSelect && window.monetaryUnitData) {
        // ล้าง options เดิม (ยกเว้น option แรก)
        const firstOption = monetaryUnitSelect.querySelector('option[value=""]');
        monetaryUnitSelect.innerHTML = '';
        if (firstOption) {
            monetaryUnitSelect.appendChild(firstOption);
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกหน่วยเงิน';
            monetaryUnitSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.monetaryUnitData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.monetary_unit_name_en;
            option.textContent = item.monetary_unit_name_en;
            option.dataset.thName = item.monetary_unit_name_th;
            monetaryUnitSelect.appendChild(option);
        });
        
        // เพิ่ม event listener สำหรับการเชื่อมโยงกับหน่วยเงินไทย
        monetaryUnitSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const thName = selectedOption.dataset.thName;
            const thSelect = row.querySelector('.monetary-unit-th-select');
            
            if (thSelect && thName) {
                thSelect.value = thName;
            }
        });
    }
    
    // เติมข้อมูลหน่วยเงิน (ไทย)
    const monetaryUnitThSelect = row.querySelector('.monetary-unit-th-select');
    if (monetaryUnitThSelect && window.monetaryUnitData) {
        // ล้าง options เดิม (ยกเว้น option แรก)
        const firstOption = monetaryUnitThSelect.querySelector('option[value=""]');
        monetaryUnitThSelect.innerHTML = '';
        if (firstOption) {
            monetaryUnitThSelect.appendChild(firstOption);
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกหน่วยเงิน';
            monetaryUnitThSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.monetaryUnitData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.monetary_unit_name_th;
            option.textContent = item.monetary_unit_name_th;
            monetaryUnitThSelect.appendChild(option);
        });
    }
    
    // เติมข้อมูลกลุ่มวัสดุ (ใช้ MRD Group สำหรับส่วนที่ไม่ใช่ A)
    const mrdGroupSelect = row.querySelector('.mrd-group-select');
    if (mrdGroupSelect && window.mrdGroupData) {
        // ล้าง options เดิม
        const firstOption = mrdGroupSelect.querySelector('option[value=""]');
        mrdGroupSelect.innerHTML = '';
        if (firstOption) {
            mrdGroupSelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกกลุ่มวัสดุ';
            mrdGroupSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.mrdGroupData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.code} - ${item.name}`;
            option.dataset.name = item.name;
            option.dataset.code = item.code;
            mrdGroupSelect.appendChild(option);
        });
        
        // ตั้งค่า Select2
        $(mrdGroupSelect).select2({
            placeholder: "กรุณาเลือกกลุ่มวัสดุ",
            allowClear: true,
            width: '100%'
        });
    }
    
    // เติมข้อมูล Asset Category สำหรับส่วน A สินทรัพย์
    const assetCategorySelect = row.querySelector('.asset-category-select');
    if (assetCategorySelect && window.assetCategoryData) {
        // ล้าง options เดิม
        const firstOption = assetCategorySelect.querySelector('option[value=""]');
        assetCategorySelect.innerHTML = '';
        if (firstOption) {
            assetCategorySelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกกลุ่มวัสดุ';
            assetCategorySelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.assetCategoryData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.code} - ${item.name}`;
            option.dataset.name = item.name;
            option.dataset.code = item.code;
            assetCategorySelect.appendChild(option);
        });
        
        // ตั้งค่า Select2
        $(assetCategorySelect).select2({
            placeholder: "กรุณาเลือกกลุ่มวัสดุ",
            allowClear: true,
            width: '100%'
        });
    }
    
    // เติมข้อมูล Expen PR สำหรับ TYPE K
    const expenPrSelect = row.querySelector('.expen-pr-select');
    if (expenPrSelect && window.expenPrData) {
        // ล้าง options เดิม
        const firstOption = expenPrSelect.querySelector('option[value=""]');
        expenPrSelect.innerHTML = '';
        if (firstOption) {
            expenPrSelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกรายการค่าใช้จ่าย';
            expenPrSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.expenPrData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.expen_code} - ${item.expen_name}`;
            option.dataset.name = item.expen_name;
            option.dataset.code = item.expen_code;
            expenPrSelect.appendChild(option);
        });
        
        // ตั้งค่า Select2
        $(expenPrSelect).select2({
            placeholder: "กรุณาเลือกรายการค่าใช้จ่าย",
            allowClear: true,
            width: '100%'
        });
    }
}

function setupClearAndBackButtons() {
    const clearButtons = document.querySelectorAll('button[class*="btn-danger"]');
    clearButtons.forEach(button => {
        if (button.textContent.includes('ล้างข้อมูล')) {
            button.addEventListener('click', function() {
                window.location.reload();
            });
        }
    });
    
    const backButtons = document.querySelectorAll('a[class*="btn-warning"]');
    backButtons.forEach(button => {
        if (button.textContent.includes('ย้อนกลับเมนู')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'request-proposal-approvel-list.php';
            });
        }
    });
}

// ========================================
// UPDATE FUNCTIONALITY
// ========================================

function setupUpdateButtons() {
    const updateButtons = document.querySelectorAll('#update-proposal-btn');
    
    updateButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            updateProposal();
        });
    });
}

async function updateProposal() {
    try {
		if (!validateRequiredFields()) {
			return;
		}
		
        showLoadingMessage('กำลังอัปเดตข้อมูล...');
        
		const proposalData = collectProposalData();
		
        const result = await submitUpdateProposal(proposalData);
        
        if (result.success) {
            showSuccessMessage('อัปเดตข้อมูลสำเร็จ', 'ข้อมูลใบขอเสนอได้รับการอัปเดตแล้ว');
        } else {
            showErrorMessage('เกิดข้อผิดพลาดในการอัปเดตข้อมูล', result.message);
        }
		
	} catch (error) {
		
        showErrorMessage('เกิดข้อผิดพลาดในการอัปเดตข้อมูล', error.message);
    }
}

async function submitUpdateProposal(data) {
    const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
    const apiUrl = basePath + '/controllers/proposal/request_proposal_controller.php?id=' + currentProposalId;
    
    
    const response = await fetch(apiUrl, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });
    
    
    if (!response.ok) {
        const errorText = await response.text();
        
        throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
    }
    
    const result = await response.json();
    
    if (result.status === 'success') {
        return {
            success: true,
            data: result.data
        };
    } else {
        throw new Error(result.message || 'Unknown error');
    }
}

// ========================================
// VALIDATION AND DATA COLLECTION
// ========================================

function validateRequiredFields() {
    const requiredFields = [
        { id: 'form-type-select', name: 'ประเภทแบบฟอร์ม' },
        { id: 'account-category-select', name: 'หมวดการกำหนดบัญชี' },
        { id: 'factory-select', name: 'โรงงาน' },
        { id: 'warehouse-select', name: 'ที่เก็บสินค้า' },
        { id: 'purchase-group-select', name: 'กลุ่มการจัดซื้อ' }
    ];
    
    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        if (!element || !element.value) {
            showErrorMessage('กรุณากรอกข้อมูลที่จำเป็น', `กรุณาเลือก${field.name}`);
            element?.focus();
            return false;
        }
    }
    
    return true;
}

function collectProposalData() {
    const formData = {
        form_type_id: document.getElementById('form-type-select').value,
        account_category: document.getElementById('account-category-select').value,
        start_date: document.querySelector('input[name="start_date"]').value,
        end_date: document.querySelector('input[name="end_date"]').value,
        department_code: document.getElementById('department-code').value,
        header_note: document.getElementById('header_note').value,
        description: document.querySelector('textarea[name="description"]').value,
        factory_id: document.getElementById('factory-select').value,
        warehouse_id: document.getElementById('warehouse-select').value,
        purchase_group_id: document.getElementById('purchase-group-select').value,
        updated_by: getCurrentUser()
    };
    
    const accountCategory = formData.account_category;
    const sectionData = collectSectionData(accountCategory);
    const totals = calculateTotals(accountCategory);
    
    return {
        ...formData,
        ...sectionData,
        ...totals
    };
}

function collectSectionData(accountCategory) {
    let sectionId = '';
    
    switch(accountCategory) {
        case 'A':
            sectionId = 'asset-section';
            break;
        case 'K':
            sectionId = 'cost-center-section';
            break;
        case 'N':
        case '':
        case 'ไม่เลือก':
            sectionId = 'no-category-section';
            break;
    }
    
    if (!sectionId) return {};
    
    const section = document.getElementById(sectionId);
    if (!section) return {};
    
    const formData = collectFormData(section);
    const items = collectTableItems(section);
    
    return {
        ...formData,
        items: items
    };
}

function collectFormData(section) {
    const formData = {};
    
    const monetaryUnitEn = section.querySelector('.monetary-unit-select');
    const monetaryUnitTh = section.querySelector('.monetary-unit-th-select');
    if (monetaryUnitEn) formData.monetary_unit_en = monetaryUnitEn.value;
    if (monetaryUnitTh) formData.monetary_unit_th = monetaryUnitTh.value;
    
    const taxSelect = section.querySelector('[id*="tax-select"]');
    if (taxSelect) formData.tax_id = taxSelect.value;
    
    const deliveryDate = section.querySelector('input[name="delivery_date"]');
    if (deliveryDate) formData.delivery_date = deliveryDate.value;
    
    const glSelect = section.querySelector('[id*="gl-account-select"]');
    if (glSelect) formData.gl_account_id = glSelect.value;
    
    const costCenterSelect = section.querySelector('[id*="cost-center-select"]');
    if (costCenterSelect) formData.cost_center_id = costCenterSelect.value;
    
    const fundSelect = section.querySelector('[id*="fund-select"]');
    if (fundSelect) formData.fund_id = fundSelect.value;
    
    const scopeSelect = section.querySelector('[id*="scope-select"]');
    if (scopeSelect) formData.scope_id = scopeSelect.value;
    
    const fundsCenterSelect = section.querySelector('[id*="funds-center-select"]');
    if (fundsCenterSelect) formData.funds_center_id = fundsCenterSelect.value;
    
    const liabilitySelect = section.querySelector('[id*="liability-select"]');
    if (liabilitySelect) formData.liability_id = liabilitySelect.value;
    
    const purchaseText = section.querySelector('textarea[name="purchase_text"]');
    if (purchaseText) formData.purchase_text = purchaseText.value;
    
    const itemNote = section.querySelector('textarea[name="item_note"]');
    if (itemNote) formData.item_note = itemNote.value;
    
    const deliveryText = section.querySelector('textarea[name="delivery_text"]');
    if (deliveryText) formData.delivery_text = deliveryText.value;
    
    const materialOrderText = section.querySelector('textarea[name="material_order_text"]');
    if (materialOrderText) formData.material_order_text = materialOrderText.value;
    
    const quotationText = section.querySelector('textarea[name="quotation_text"]');
    if (quotationText) formData.quotation_text = quotationText.value;
    
    return formData;
}

function collectTableItems(section) {
    const items = [];
    const rows = section.querySelectorAll('tbody tr');
    
    // ตรวจสอบว่าเป็นส่วนไหน
    const isAssetSection = section.id === 'asset-section';
    
    rows.forEach((row, index) => {
        const item = {};
        
        if (isAssetSection) {
            // ส่วน A สินทรัพย์: มีเมนูดาวน์รหัสสินทรัพย์
            const assetSelect = row.querySelector('select[name="asset_code"]');
            if (assetSelect) {
                item.asset_code = assetSelect.value;
                item.material_code = assetSelect.value;
                item.material_name = assetSelect.options[assetSelect.selectedIndex]?.text || '';
            }
        } else {
            // ส่วนอื่นๆ: มีเมนูดาวน์วัสดุ
            const materialSelect = row.querySelector('select[name="mat_code"]');
            if (materialSelect) {
                item.material_code = materialSelect.value;
                item.material_name = materialSelect.options[materialSelect.selectedIndex]?.text || '';
            }
        }
        
        const quantityCell = row.querySelector('td:nth-child(3)');
        if (quantityCell) {
            const quantityText = quantityCell.textContent.replace(/,/g, '');
            item.quantity = parseFloat(quantityText) || 0;
        }
        
        const unitSelect = row.querySelector('.unit-select');
        if (unitSelect) item.unit_code = unitSelect.value;
        
        // กลุ่มวัสดุ/Asset Category/Expen PR - เก็บ ID จาก dropdown
        const groupSelect = row.querySelector('.asset-category-select, .mrd-group-select, .expen-pr-select');
        if (groupSelect && groupSelect.value) {
            item.group_name = groupSelect.value; // เก็บ ID
            // เก็บ field เฉพาะตามประเภท
            if (groupSelect.classList.contains('expen-pr-select')) {
                item.expen_pr_id = groupSelect.value;
            } else if (groupSelect.classList.contains('asset-category-select')) {
                item.asset_category_id = groupSelect.value;
            } else if (groupSelect.classList.contains('mrd-group-select')) {
                item.mrd_group_id = groupSelect.value;
            }
        }
        
        const priceCell = row.querySelector('td:nth-child(6)');
        if (priceCell) item.unit_price = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
        
        const totalPriceCell = row.querySelector('td:nth-child(7)');
        if (totalPriceCell) {
            const totalPriceText = totalPriceCell.textContent.replace(/,/g, '');
            item.total_price = parseFloat(totalPriceText) || 0;
        }
        
        if (item.material_code || item.asset_code || item.quantity > 0 || item.unit_price > 0) {
            items.push(item);
        }
    });
    
    return items;
}

function calculateTotals(accountCategory) {
    let sectionId = '';
    
    switch(accountCategory) {
        case 'A':
            sectionId = 'asset-section';
            break;
        case 'K':
            sectionId = 'cost-center-section';
            break;
        case 'N':
        case '':
        case 'ไม่เลือก':
            sectionId = 'no-category-section';
            break;
    }
    
    if (!sectionId) {
        return { total_quantity: 0, total_amount: 0 };
    }
    
    const section = document.getElementById(sectionId);
    if (!section) {
        return { total_quantity: 0, total_amount: 0 };
    }
    
    if (section.style.display === 'none') {
        return { total_quantity: 0, total_amount: 0 };
    }
    
    const rows = section.querySelectorAll('tbody tr');
    let totalQuantity = 0;
    let totalAmount = 0;
    
    rows.forEach(row => {
        const quantityCell = row.querySelector('td:nth-child(3)');
        const totalPriceCell = row.querySelector('td:nth-child(7)');
        
        if (quantityCell && totalPriceCell) {
            const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
            const totalPrice = parseFloat(totalPriceCell.textContent.replace(/,/g, '')) || 0;
            
            totalQuantity += quantity;
            totalAmount += totalPrice;
        }
    });
    
    return {
        total_quantity: totalQuantity,
        total_amount: totalAmount
    };
}

function getCurrentUser() {
    try {
        const userSession = sessionStorage.getItem('raot_user_session');
        if (userSession) {
            const userData = JSON.parse(userSession);
            return userData.user_code || userData.username || 'unknown';
        }
    } catch (error) {
        
    }
    return 'unknown';
}

// ========================================
// APPROVAL HISTORY
// ========================================

/**
 * โหลดประวัติการอนุมัติ
 */
async function loadApprovalHistory() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/proposal/request_proposal_controller.php?action=get_approval_history&id=' + currentProposalId;
        
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            populateApprovalHistory(result.data);
        } else {
            // ถ้าไม่มีข้อมูล ให้แสดงข้อความว่าไม่พบข้อมูล
            const tbody = document.querySelector('#approval-table tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">ยังไม่มีประวัติการพิจารณา</td></tr>';
            }
        }
    } catch (error) {
        
        const tbody = document.querySelector('#approval-table tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
        }
    }
}

/**
 * แสดงประวัติการอนุมัติในตาราง
 */
function populateApprovalHistory(historyData) {
    const tbody = document.querySelector('#approval-table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!historyData || historyData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">ยังไม่มีประวัติการพิจารณา</td></tr>';
        return;
    }
    
    historyData.forEach((item, index) => {
        const row = document.createElement('tr');
        
        // ลำดับที่
        const noCell = document.createElement('td');
        noCell.className = 'text-center';
        noCell.textContent = index + 1;
        row.appendChild(noCell);
        
        // วันที่
        const dateCell = document.createElement('td');
        dateCell.className = 'text-center';
        dateCell.textContent = formatApprovalDate(item.approved_at);
        row.appendChild(dateCell);
        
        // ผลการพิจารณา
        const statusCell = document.createElement('td');
        statusCell.className = 'text-center';
        statusCell.innerHTML = getApprovalStatusBadge(item.status);
        row.appendChild(statusCell);
        
        // ผู้อนุมัติ
        const approverCell = document.createElement('td');
        approverCell.className = 'text-center';
        approverCell.textContent = item.approved_by || '-';
        row.appendChild(approverCell);
        
        // หมายเหตุ
        const remarkCell = document.createElement('td');
        remarkCell.className = 'text-start';
        remarkCell.textContent = item.remark || '-';
        row.appendChild(remarkCell);
        
        tbody.appendChild(row);
    });
}

/**
 * จัดรูปแบบวันที่สำหรับแสดงผล
 */
function formatApprovalDate(dateString) {
    if (!dateString) return '-';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

/**
 * สร้าง badge สำหรับสถานะการอนุมัติ
 */
function getApprovalStatusBadge(status) {
    switch (status) {
        case 'approved':
            return '<span class="badge bg-success">อนุมัติ</span>';
        case 'rejected':
            return '<span class="badge bg-danger">ไม่อนุมัติ</span>';
        case 'pending':
            return '<span class="badge bg-warning">รออนุมัติ</span>';
        default:
            return '<span class="badge bg-secondary">' + status + '</span>';
    }
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

function showErrorMessage(title, message = '') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonText: 'ตกลง'
		});
	} else {
        alert(`${title}: ${message}`);
    }
}

function showLoadingMessage(message) {
    if (typeof Swal !== 'undefined') {
        // ตรวจสอบว่ามี popup อยู่แล้วหรือไม่
        if (Swal.isVisible()) {
            // อัปเดตข้อความใน popup ที่มีอยู่แล้ว
            Swal.update({
                title: 'กำลังประมวลผล',
                text: message
            });
        } else {
            // สร้าง popup ใหม่
            Swal.fire({
                title: 'กำลังประมวลผล',
                text: message,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }
}

function showSuccessMessage(title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'request-proposal-approvel-list.php';
        });
    } else {
        alert(`${title}: ${message}`);
        window.location.href = 'request-proposal-approvel-list.php';
    }
}

// Export functions
window.RequestProposalEditForm = {
    loadProposalData,
    updateProposal,
    loadApprovalHistory
};
