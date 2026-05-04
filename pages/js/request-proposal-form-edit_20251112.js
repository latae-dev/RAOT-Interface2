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
		
		// ตั้งค่า event listener สำหรับการเปลี่ยนหมวดการกำหนดบัญชี
		setupAccountCategoryChangeHandler();
		
    // ตั้งค่า event listeners
		setupEventListeners();
		
		// เริ่มต้น Date Pickers
		initializeDatePickers();
		
		// เริ่มต้น Select2
		initializeSelect2();
		
    // เติมข้อมูลในแถวเริ่มต้น
    setTimeout(() => {
        fillInitialRows();
    }, 1500);
		
    // ตั้งค่าปุ่มบันทึก
    setupUpdateButtons();
    
    // โหลดข้อมูลใบขอเสนอ
    setTimeout(() => {
        loadProposalData();
    }, 2000); // รอให้ dropdown โหลดเสร็จก่อน
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

/**
 * เติมข้อมูลในแถวเริ่มต้น
 */
function fillInitialRows() {
    // เติมข้อมูลในแถวเริ่มต้นของส่วน "ไม่เลือก" (ใช้ wrd_mat)
    const noCategoryRow = document.querySelector('#no-category-section tbody tr');
    if (noCategoryRow) {
        fillDropdownsInRow(noCategoryRow);
    }
    
    // เติมข้อมูลในแถวเริ่มต้นของส่วน A สินทรัพย์ (ใช้ matlist)
    const assetRow = document.querySelector('#asset-section tbody tr');
    if (assetRow) {
        fillDropdownsInRowForAsset(assetRow);
    }
    
    // เติมข้อมูลในแถวเริ่มต้นของส่วน K ศูนย์ต้นทุน
    const costCenterRow = document.querySelector('#cost-center-section tbody tr');
    if (costCenterRow) {
        fillDropdownsInRowForCostCenter(costCenterRow);
    }
}

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
    setupAddRemoveButtons();
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

/**
 * ดึงรหัสหน่วยงานจาก input หรือ sessionStorage
 */
function getDepartmentCode() {
    try {
        // ลองดึงจาก input field ก่อน
        const departmentField = document.getElementById('department-code');
        if (departmentField && departmentField.value) {
            return departmentField.value;
        }
        
        // ถ้าไม่มีใน input ให้ดึงจาก sessionStorage
        const userSession = sessionStorage.getItem('raot_user_session');
        if (userSession) {
            const userData = JSON.parse(userSession);
            return userData.depart_code || '';
        }
    } catch (error) {
    }
    return '';
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
        // หา dropdown หน่วยเงินทั้งหมด
        const monetaryUnitSelects = document.querySelectorAll('.monetary-unit-select');
        if (monetaryUnitSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
                
                // ตั้งค่า Select2 สำหรับหน่วยเงินอังกฤษ
                $(selectEl).select2({
                    placeholder: "กรุณาเลือกหน่วยเงิน",
                    allowClear: true,
                    width: '50%'
                });
                
                // ตั้งค่า event listener สำหรับการเลือกหน่วยเงิน
                $(selectEl).on('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const thName = selectedOption.dataset.thName;
                    
                    // หา dropdown หน่วยเงินไทยในแถวเดียวกัน
                    const row = this.closest('.row');
                    if (row && thName) {
                        const thSelect = row.querySelector('.monetary-unit-th-select');
                        if (thSelect) {
                            // หา option ที่มี value ตรงกับ thName
                            const thOptions = thSelect.querySelectorAll('option');
                            thOptions.forEach(option => {
                                if (option.value === thName) {
                                    $(thSelect).val(thName).trigger('change');
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
                
                // ตั้งค่า Select2 สำหรับหน่วยเงินไทย
                $(selectEl).select2({
                    placeholder: "กรุณาเลือกหน่วยเงิน",
                    allowClear: true,
                    width: '50%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

// ========================================
// LOAD OTHER DROPDOWNS
// ========================================

async function loadGLAccounts() {
    try {
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        // ใช้ liability_controller เพราะ G/L และ Liability ต้องเป็นค่าเดียวกัน
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
            // เก็บข้อมูลไว้ใน global variable เพื่อใช้ใน loadLiabilities()
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
                
                // ตั้งค่า event listener เพื่อ sync ค่ากับ Liability
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
            // ดึงรหัสหน่วยงานจาก input หรือ sessionStorage
            const departmentCode = getDepartmentCode();
            
            // หา id ของรายการที่ cost_center_code ตรงกับรหัสหน่วยงาน
            let defaultCostCenterId = '';
            if (departmentCode) {
                const matchedItem = result.data.find(item => item.cost_center_code === departmentCode);
                if (matchedItem) {
                    defaultCostCenterId = matchedItem.id;
                }
            }
            
            const costCenterSelects = document.querySelectorAll('#cost-center-select, #cost-center-select-asset, #cost-center-select-cost-center');
            
            const costCenterOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.cost_center_code} - ${item.cost_center_name}`,
                    code: item.cost_center_code
                }))
            ];
            
            costCenterSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                costCenterOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
                
                // ตั้งค่า default ถ้าพบรายการที่ตรงกับรหัสหน่วยงาน
                if (defaultCostCenterId) {
                    $(selectEl).val(defaultCostCenterId).trigger('change');
                }
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
            // ดึงรหัสหน่วยงานจาก input หรือ sessionStorage
            const departmentCode = getDepartmentCode();
            
            // หา id ของรายการที่ fund_code ตรงกับรหัสหน่วยงาน
            let defaultFundId = '';
            if (departmentCode) {
                const matchedItem = result.data.find(item => item.fund_code === departmentCode);
                if (matchedItem) {
                    defaultFundId = matchedItem.id;
                }
            }
            
            const wrdFundSelects = document.querySelectorAll('#funds-center-select, #funds-center-select-asset, #funds-center-select-cost-center');
            
            const wrdFundOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.fund_code} - ${item.fund_name}`,
                    code: item.fund_code
                }))
            ];
            
            wrdFundSelects.forEach((selectEl) => {
                selectEl.innerHTML = '';
                
                wrdFundOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
                
                // ตั้งค่า default ถ้าพบรายการที่ตรงกับรหัสหน่วยงาน
                if (defaultFundId) {
                    $(selectEl).val(defaultFundId).trigger('change');
                }
            });
        }
    } catch (err) {
    }
}

async function loadLiabilities() {
    try {
        // ถ้ามีข้อมูลใน window.liabilityData แล้ว ให้ใช้เลย
        let result;
        if (window.liabilityData) {
            result = { status: 'success', data: window.liabilityData };
        } else {
            // ถ้ายังไม่มี ให้โหลดจาก API
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
            
            // เก็บข้อมูลไว้ใน global variable
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
                
                // ตั้งค่า event listener เพื่อ sync ค่ากับ G/L
                setupLiabilityToGLSync(selectEl);
            });
        }
    } catch (err) {
    }
}

// ========================================
// G/L AND LIABILITY SYNC
// ========================================

/**
 * ตั้งค่าการ Sync จาก G/L ไป Liability
 * เมื่อเลือก G/L Account ให้เลือก Liability เป็นค่าเดียวกันโดยอัตโนมัติ
 */
function setupGLToLiabilitySync(glSelectEl) {
    if (!glSelectEl) return;
    
    // หา liability dropdown ที่สัมพันธ์กัน
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
    
    // ตั้งค่า event listener
    $(glSelectEl).on('change.glsync', function(e) {
        const selectedValue = $(this).val();
        const liabilitySelect = document.getElementById(liabilityId);
        
        if (liabilitySelect && selectedValue) {
            // ป้องกัน infinite loop
            $(liabilitySelect).off('change.liabilitysync');
            $(liabilitySelect).val(selectedValue).trigger('change');
            
            // เปิด event listener กลับมาหลังจาก delay เล็กน้อย
            setTimeout(() => {
                setupLiabilityToGLSync(liabilitySelect);
            }, 100);
        }
    });
}

/**
 * ตั้งค่าการ Sync จาก Liability ไป G/L
 * เมื่อเลือก Liability ให้เลือก G/L Account เป็นค่าเดียวกันโดยอัตโนมัติ
 */
function setupLiabilityToGLSync(liabilitySelectEl) {
    if (!liabilitySelectEl) return;
    
    // หา GL dropdown ที่สัมพันธ์กัน
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
    
    // ตั้งค่า event listener
    $(liabilitySelectEl).on('change.liabilitysync', function(e) {
        const selectedValue = $(this).val();
        const glSelect = document.getElementById(glId);
        
        if (glSelect && selectedValue) {
            // ป้องกัน infinite loop
            $(glSelect).off('change.glsync');
            $(glSelect).val(selectedValue).trigger('change');
            
            // เปิด event listener กลับมาหลังจาก delay เล็กน้อย
            setTimeout(() => {
                setupGLToLiabilitySync(glSelect);
            }, 100);
        }
    });
}

// ========================================
// LOAD MRD GROUPS AND ASSET CATEGORIES
// ========================================

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
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
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
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
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
            
        } else {
            
        }
    } catch (err) {
    }
}

// ========================================
// SELECT2 INITIALIZATION
// ========================================

/**
 * เริ่มต้น Select2 สำหรับ dropdown ต่างๆ
 */
function initializeSelect2() {
    // เริ่มต้น Select2 สำหรับหน่วยเงิน (อังกฤษ)
    $('.monetary-unit-select').select2({
        placeholder: "กรุณาเลือกหน่วยเงิน",
        allowClear: true,
        width: '100%'
    });
    
    // เริ่มต้น Select2 สำหรับหน่วยเงิน (ไทย)
    $('.monetary-unit-th-select').select2({
        placeholder: "กรุณาเลือกหน่วยเงิน",
        allowClear: true,
        width: '100%'
    });
    
    // เริ่มต้น Select2 สำหรับวัสดุ (ส่วนไม่เลือก)
    $('select[name="mat_code"]').select2({
        placeholder: "กรุณาเลือกวัสดุ",
        allowClear: true,
        width: '100%'
    });
    
    // เริ่มต้น Select2 สำหรับรหัสสินทรัพย์ (ส่วน A)
    $('select[name="asset_code"]').select2({
        placeholder: "กรุณาเลือกรหัสสินทรัพย์",
        allowClear: true,
        width: '100%'
    });
    
    // เริ่มต้น Select2 สำหรับกลุ่มวัสดุ
    $('.mrd-group-select').select2({
        placeholder: "กรุณาเลือกกลุ่มวัสดุ",
        allowClear: true,
        width: '100%'
    });
}

// ========================================
// DATE PICKER INITIALIZATION
// ========================================

/**
 * เริ่มต้น Date Pickers
 */
function initializeDatePickers() {
    // เริ่มต้น flatpickr สำหรับ start-date
    flatpickr("#start-date", {
        dateFormat: "d-m-Y",
        locale: "th"
    });
    
    // เริ่มต้น flatpickr สำหรับ end-date
    flatpickr("#end-date", {
        dateFormat: "d-m-Y",
        locale: "th"
    });
    
    // เริ่มต้น flatpickr สำหรับ delivery-date
    flatpickr("#delivery-date", {
        dateFormat: "d-m-Y",
        locale: "th"
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

/**
 * แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น dd-mm-yyyy
 */
function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    
    // แยกวันที่จาก YYYY-MM-DD
    const parts = dateString.split('-');
    if (parts.length === 3) {
        const year = parts[0];
        const month = parts[1];
        const day = parts[2];
        return `${day}-${month}-${year}`;
    }
    
    return dateString;
}

/**
 * แปลงรูปแบบวันที่จาก dd-mm-yyyy เป็น YYYY-MM-DD
 */
function formatDateForSubmit(dateString) {
    if (!dateString) return '';
    
    // แยกวันที่จาก dd-mm-yyyy
    const parts = dateString.split('-');
    if (parts.length === 3) {
        const day = parts[0];
        const month = parts[1];
        const year = parts[2];
        return `${year}-${month}-${day}`;
    }
    
    return dateString;
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
        const startDate = formatDateForDisplay(data.start_date);
        document.querySelector('input[name="start_date"]').value = startDate;
    }
    if (data.end_date) {
        const endDate = formatDateForDisplay(data.end_date);
        document.querySelector('input[name="end_date"]').value = endDate;
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
            
            $(monetaryUnitEn).val(data.monetary_unit_en).trigger('change');
        }
        
        const monetaryUnitTh = section.querySelector('.monetary-unit-th-select');
        if (monetaryUnitTh && data.monetary_unit_th) {
            
            $(monetaryUnitTh).val(data.monetary_unit_th).trigger('change');
        }
        
        const taxSelect = section.querySelector('[id*="tax-select"]');
        if (taxSelect && data.tax_id) {
            $(taxSelect).val(data.tax_id).trigger('change');
        }
        
        const deliveryDate = section.querySelector('input[name="delivery_date"]');
        if (deliveryDate && data.delivery_date) {
            const formattedDeliveryDate = formatDateForDisplay(data.delivery_date);
            deliveryDate.value = formattedDeliveryDate;
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
        // เรียกใช้ฟังก์ชัน fill dropdown ที่เหมาะสมตาม section
        if (sectionId === 'asset-section') {
            fillDropdownsInRowForAsset(row);
        } else if (sectionId === 'cost-center-section') {
            fillDropdownsInRowForCostCenter(row);
        } else {
            fillDropdownsInRow(row);
        }
        
        // Debug: แสดงข้อมูลที่โหลดมา
        
        
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
            
            if (item.group_name) {
                const groupSelect = row.querySelector('.asset-category-select, .mrd-group-select, .expen-pr-select');
                if (groupSelect) {
                    
                    // item.group_name เป็น asset_category_id, mrd_group_id หรือ expen_pr_id ที่เก็บในฐานข้อมูล
                    $(groupSelect).val(item.group_name).trigger('change');
                }
            }
            
            setupRowEventListeners(row);
        }, 300); // รอ 300ms หลังจาก fill dropdown
        
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

function setupAccountCategoryChangeHandler() {
    const accountCategorySelect = document.getElementById('account-category-select');
    
    if (accountCategorySelect) {
        // ใช้ jQuery event สำหรับ Select2
        $('#account-category-select').on('change', function() {
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
    // ตรวจสอบว่าแถวนี้อยู่ในส่วน K หรือไม่
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
        // สำหรับ contenteditable cells (TYPE K)
        if (isCostCenterSection) {
            // Event listener สำหรับการแก้ไข contenteditable
            quantityCell.addEventListener('input', function() {
                calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                setTimeout(() => calculateGrandTotal(), 100); // รอขนาดเริ่มรูบก่อนคำนวณทั้งหมด
            });
            
            unitPriceCell.addEventListener('input', function() {
                calculateTotalPrice(this, quantityCell, totalPriceCell);
                setTimeout(() => calculateGrandTotal(), 100); // รอขนาดเริ่มรูบก่อนคำนวณทั้งหมด
            });
            
            // Event listener สำหรับเมื่อออกจาก cell
            quantityCell.addEventListener('blur', function() {
                calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                calculateGrandTotal(); // เพิ่มการคำนวณยอดรวมทันทีเมื่อออกจากการแก้ไข
            });
            
            unitPriceCell.addEventListener('blur', function() {
                calculateTotalPrice(this, quantityCell, totalPriceCell);
                calculateGrandTotal(); // เพิ่มการคำนวณยอดรวมทันทีเมื่อออกจากการแก้ไข
            });
            
            // Event listener สำหรับการกด Enter
            quantityCell.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                    calculateGrandTotal(); // เพิ่มการคำนวณยอดรวมทันที
                    this.blur();
                }
            });
            
            unitPriceCell.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculateTotalPrice(this, quantityCell, totalPriceCell);
                    calculateGrandTotal(); // เพิ่มการคำนวณยอดรวมทันที
                    this.blur();
                }
            });
        } else {
            // สำหรับ input cells (TYPE อื่นๆ)
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
        
        // ตรวจสอบ contenteditable โดยตรง
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
        
        // ใช้ innerText สำหรับ contenteditable cell
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
        
        // ตรวจสอบว่าเป็นส่วน K หรือไม่
        const section = table.closest('[id*="-section"]');
        const isCostCenterSection = section && section.id === 'cost-center-section';
        
        rows.forEach(row => {
            // กำหนดตำแหน่งของ column ตามส่วน
            const totalPriceColumn = isCostCenterSection ? 7 : 7;
            const quantityColumn = isCostCenterSection ? 3 : 3;
            
            const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceColumn})`);
            const quantityCell = row.querySelector(`td:nth-child(${quantityColumn})`);
            
            if (totalPriceCell) {
                // ตรวจสอบว่าเป็น contenteditable หรือไม่
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
                // ตรวจสอบว่าเป็น contenteditable หรือไม่
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
            
            // Debug log for TYPE K
            if (isCostCenterSection) {
                
            }
        }
        
        if (grandTotalCell) {
            grandTotalCell.textContent = grandTotal.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            // Debug log for TYPE K  
            if (isCostCenterSection) {
                
            }
        }
    });
}

/**
 * ตั้งค่า event listeners สำหรับปุ่มเพิ่ม/ลบแถว
 */
function setupAddRemoveButtons() {
    // บัญชี A
    setupAddRemoveButtonsForAsset();
    // บัญชี K
    setupAddRemoveButtonsForCostCenter();
    // บัญชีอื่นๆ
    setupAddRemoveButtonsForNoCategory();
}

/**
 * ตั้งค่า event listeners สำหรับปุ่มเพิ่ม/ลบรายการของส่วน A สินทรัพย์
 */
function setupAddRemoveButtonsForAsset() {
    const addButtons = document.querySelectorAll('#asset-section .btn-success-light');
    const removeButtons = document.querySelectorAll('#asset-section .btn-danger-light');
    
    
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            addNewRowForAsset(this);
        });
    });
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            
            removeSelectedRows(this);
        });
    });
}

/**
 * เพิ่มแถวใหม่ในส่วน A สินทรัพย์
 */
function addNewRowForAsset(button) {
    // หาตารางในส่วน A สินทรัพย์
    const table = button.closest('#asset-section').querySelector('table');
    const tbody = table.querySelector('tbody');
    
    // สร้างแถวใหม่
    const newRow = createNewRowForAsset();
    tbody.appendChild(newRow);
    
    // เติมข้อมูลใน dropdown ของแถวใหม่ (ใช้ฟังก์ชันสำหรับ Asset ที่ใช้ matlistData)
    fillDropdownsInRowForAsset(newRow);
    
    // ตั้งค่า event listener สำหรับแถวใหม่
    setupRowEventListeners(newRow);
}

/**
 * สร้างแถวใหม่สำหรับส่วน A สินทรัพย์
 */
function createNewRowForAsset() {
    const row = document.createElement('tr');
    row.className = 'product-list';
    
    row.innerHTML = `
        <td class="product-checkbox">
            <input class="form-check-input" type="checkbox" value="" aria-label="...">
        </td>
        <td class="text-start">
            <select class="form-control asset-select" name="asset_code">
                <option value="">กรุณาเลือกรหัสสินทรัพย์</option>
            </select>
        </td>
        <td contenteditable="true">0</td>
        <td>
            <select class="form-control unit-select" name="unit" style="width: 100%">
                <option value="">กรุณาเลือกหน่วย</option>
            </select>
        </td>
        <td class="text-start">
            <select class="form-control asset-category-select" name="asset_category_id" style="width: 100%">
                <option value="">กรุณาเลือกกลุ่มวัสดุ</option>
            </select>
        </td>
        <td contenteditable="true">0.00</td>
        <td contenteditable="true" class="text-end">0.00</td>
    `;
    
    return row;
}

/**
 * ตั้งค่า event listeners สำหรับปุ่มเพิ่ม/ลบรายการของส่วน K ศูนย์ต้นทุน
 */
function setupAddRemoveButtonsForCostCenter() {
    const addButtons = document.querySelectorAll('#cost-center-section .btn-success-light');
    const removeButtons = document.querySelectorAll('#cost-center-section .btn-danger-light');
    
    
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            addNewRowForCostCenter(this);
        });
    });
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            
            removeSelectedRows(this);
        });
    });
}

/**
 * เพิ่มแถวใหม่ในส่วน K ศูนย์ต้นทุน
 */
function addNewRowForCostCenter(button) {
    const tbody = button.closest('#cost-center-section').querySelector('tbody');
    if (!tbody) return;
    
    // สร้างแถวใหม่
    const newRow = createNewRowForCostCenter();
    tbody.appendChild(newRow);
    
    // เติมข้อมูลใน dropdown ของแถวใหม่ (ใช้ฟังก์ชันสำหรับ Cost Center)
    fillDropdownsInRowForCostCenter(newRow);
    setupRowEventListeners(newRow);
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotal();
}

/**
 * สร้างแถวใหม่สำหรับส่วน K ศูนย์ต้นทุน
 */
function createNewRowForCostCenter() {
    const row = document.createElement('tr');
    row.className = 'product-list';
    
    row.innerHTML = `
        <td class="product-checkbox">
            <input class="form-check-input" type="checkbox" value="" aria-label="...">
        </td>
        <td contenteditable="true" class="text-start">รายการค่าใช้จ่าย</td>
        <td contenteditable="true">0</td>
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
        <td contenteditable="true">0.00</td>
        <td contenteditable="true" class="text-end">0.00</td>
    `;
    
    return row;
}

/**
 * ตั้งค่า event listeners สำหรับปุ่มเพิ่ม/ลบรายการของส่วน ไม่เลือก
 */
function setupAddRemoveButtonsForNoCategory() {
    const addButtons = document.querySelectorAll('#no-category-section .btn-success-light');
    const removeButtons = document.querySelectorAll('#no-category-section .btn-danger-light');
    
    
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            addNewRowForNoCategory(this);
        });
    });
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            
            removeSelectedRows(this);
        });
    });
}

/**
 * เพิ่มแถวใหม่ในส่วน ไม่เลือก
 */
function addNewRowForNoCategory(button) {
    // หาตารางในส่วน ไม่เลือก
    const table = button.closest('#no-category-section').querySelector('table');
    const tbody = table.querySelector('tbody');
    
    // สร้างแถวใหม่
    const newRow = createNewRowForNoCategory();
    tbody.appendChild(newRow);
    
    // เติมข้อมูลใน dropdown ของแถวใหม่
    fillDropdownsInRow(newRow);
    
    // ตั้งค่า event listener สำหรับแถวใหม่
    setupRowEventListeners(newRow);
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotal();
}

/**
 * สร้างแถวใหม่สำหรับส่วน ไม่เลือก
 */
function createNewRowForNoCategory() {
    const row = document.createElement('tr');
    row.className = 'product-list';
    
    row.innerHTML = `
        <td class="product-checkbox">
            <input class="form-check-input" type="checkbox" value="" aria-label="...">
        </td>
        <td class="text-start">
            <select class="form-control matlist-select" name="mat_code">
                <option value="">กรุณาเลือกวัสดุ</option>
            </select>
        </td>
        <td contenteditable="true">0</td>
        <td>
            <select class="form-control unit-select" name="unit" style="width: 100%">
                <option value="">กรุณาเลือกหน่วย</option>
            </select>
        </td>
        <td class="text-start">
            <select class="form-control mrd-group-select" name="mrd_group_id" style="width: 100%">
                <option value="">กรุณาเลือกกลุ่มวัสดุ</option>
            </select>
        </td>
        <td contenteditable="true">0.00</td>
        <td contenteditable="true" class="text-end">0.00</td>
    `;
    
    return row;
}

/**
 * ลบแถวที่เลือก
 */
function removeSelectedRows(button) {
    
    
    // หา tbody โดยใช้วิธีอื่น
    let tbody = button.closest('tbody');
    if (!tbody) {
        // ลองหา tbody จาก section ที่ปุ่มอยู่
        const section = button.closest('[id*="-section"]');
        if (section) {
            tbody = section.querySelector('tbody');
        }
    }
    
    if (!tbody) {
        
        return;
    }
    
    
    
    const checkboxes = tbody.querySelectorAll('input[type="checkbox"]:checked');
    
    
    if (checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกแถวที่ต้องการลบ',
            text: 'กรุณาเลือกแถวที่ต้องการลบก่อนกดปุ่มลบ',
            confirmButtonText: 'ตกลง'
        });
        return;
    }
    
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: `ต้องการลบ ${checkboxes.length} แถวที่เลือกหรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    
                    row.remove();
                }
            });
            
            // คำนวณยอดรวมใหม่
            calculateGrandTotal();
            
            
            // แสดงข้อความสำเร็จ
            Swal.fire({
                icon: 'success',
                title: 'ลบสำเร็จ',
                text: `ลบ ${checkboxes.length} แถวเรียบร้อยแล้ว`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

function fillDropdownsInRow(row) {
    
    
    // เติมข้อมูลวัสดุ (ใช้ข้อมูลจาก matlist สำหรับส่วน "ไม่เลือก")
    const matSelect = row.querySelector('select[name="mat_code"]');
    if (matSelect && window.matlistData) {
        
        
        // ล้าง options เดิมก่อนเติมข้อมูลใหม่
        const firstOption = matSelect.querySelector('option[value=""]');
        matSelect.innerHTML = '';
        if (firstOption) {
            matSelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกวัสดุ';
            matSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.matlistData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.mat_code;
            option.textContent = `${item.mat_code} - ${item.mat_name}`;
            option.dataset.matName = item.mat_name;
            matSelect.appendChild(option);
        });
        
        // ตั้งค่า Select2 สำหรับวัสดุ
        $(matSelect).select2({
            placeholder: "กรุณาเลือกวัสดุ",
            allowClear: true,
            width: '100%'
        });
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
        
        // Initialize Select2 for this dropdown
        $(unitSelect).select2({
            placeholder: 'กรุณาเลือกหน่วย',
            allowClear: true,
            width: '50%'
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
    
    // เติมข้อมูลกลุ่มวัสดุ
    const mrdGroupSelect = row.querySelector('.mrd-group-select');
    if (mrdGroupSelect && window.mrdGroupData) {
        
        
        // ล้าง options เดิม (ยกเว้น option แรก)
        const firstOption = mrdGroupSelect.querySelector('option[value=""]');
        mrdGroupSelect.innerHTML = '';
        if (firstOption) {
            mrdGroupSelect.appendChild(firstOption);
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
        
        // ตั้งค่า Select2 สำหรับกลุ่มวัสดุ
        $(mrdGroupSelect).select2({
            placeholder: "กรุณาเลือกกลุ่มวัสดุ",
            allowClear: true,
            width: '100%'
        });
    }
}

/**
 * เติมข้อมูลใน dropdown ของแถวใหม่ในส่วน A สินทรัพย์
 */
function fillDropdownsInRowForAsset(row) {
    // เติมข้อมูลสินทรัพย์ (ใช้ asset สำหรับส่วน A)
    const assetSelect = row.querySelector('select[name="asset_code"]');
    if (assetSelect && window.assetData) {
        // ล้าง options เดิมก่อนเติมข้อมูลใหม่
        const firstOption = assetSelect.querySelector('option[value=""]');
        assetSelect.innerHTML = '';
        if (firstOption) {
            assetSelect.appendChild(firstOption.cloneNode(true));
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'กรุณาเลือกรหัสสินทรัพย์';
            assetSelect.appendChild(defaultOption);
        }
        
        // เพิ่ม options ใหม่
        window.assetData.forEach(item => {
            const option = document.createElement('option');
            option.value = item.asset_code;
            option.textContent = `${item.asset_code} - ${item.asset_name}`;
            option.dataset.assetName = item.asset_name;
            option.dataset.assetCategoryId = item.asset_category_id || '';
            assetSelect.appendChild(option);
        });
        
        // ตั้งค่า Select2 สำหรับรหัสสินทรัพย์
        $(assetSelect).select2({
            placeholder: "กรุณาเลือกรหัสสินทรัพย์",
            allowClear: true,
            width: '100%'
        });
        
        // ตั้งค่า event listener เพื่อ auto-select กลุ่มวัสดุเมื่อเลือกรหัสสินทรัพย์
        $(assetSelect).on('change', function() {
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
    
    // เติมข้อมูลหน่วย
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
        
        // Initialize Select2 for this dropdown
        $(unitSelect).select2({
            placeholder: 'กรุณาเลือกหน่วย',
            allowClear: true,
            width: '50%'
        });
    }
    
    // เติมข้อมูลกลุ่มวัสดุ
    const mrdGroupSelect = row.querySelector('.mrd-group-select');
    if (mrdGroupSelect && window.mrdGroupData) {
        // ล้าง options เดิม (ยกเว้น option แรก)
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
        
        // เพิ่ม options ใหม่ (ใช้ asset_category สำหรับส่วน A)
        const dataSource = window.assetCategoryData || window.mrdGroupData;
        if (dataSource) {
            dataSource.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.code} - ${item.name}`;
                option.dataset.name = item.name;
                option.dataset.code = item.code;
                mrdGroupSelect.appendChild(option);
            });
        }
        
        // ตั้งค่า Select2 สำหรับกลุ่มวัสดุ
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
}

/**
 * เติมข้อมูลใน dropdown ของแถวใหม่ในส่วน K ศูนย์ต้นทุน
 */
function fillDropdownsInRowForCostCenter(row) {
    
    
    
    
    // เติมข้อมูลหน่วย (ถ้ามี)
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
        
        // Initialize Select2 for this dropdown
        $(unitSelect).select2({
            placeholder: 'กรุณาเลือกหน่วย',
            allowClear: true,
            width: '100%'
        });
    }
    
    // เติมข้อมูลรายการค่าใช้จ่าย PR (สำหรับ TYPE K)
    const expenPrSelect = row.querySelector('.expen-pr-select');
    
    
    if (expenPrSelect && window.expenPrData) {
        
        
        // ล้าง options เดิม (ยกเว้น option แรก)
        const firstOption = expenPrSelect.querySelector('option[value=""]');
        expenPrSelect.innerHTML = '';
        if (firstOption) {
            expenPrSelect.appendChild(firstOption);
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
        
        
        
        // ตั้งค่า Select2 สำหรับรายการค่าใช้จ่าย
        $(expenPrSelect).select2({
            placeholder: "กรุณาเลือกรายการค่าใช้จ่าย",
            allowClear: true,
            width: '100%'
        });
    } else {
        if (!expenPrSelect) {
            
        }
        if (!window.expenPrData) {
            
        }
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
                window.location.href = 'request-proposal-list.php';
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
        start_date: formatDateForSubmit(document.querySelector('input[name="start_date"]').value),
        end_date: formatDateForSubmit(document.querySelector('input[name="end_date"]').value),
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
    if (taxSelect) formData.tax_id = taxSelect.value || null;
    
    const deliveryDate = section.querySelector('input[name="delivery_date"]');
    if (deliveryDate) formData.delivery_date = formatDateForSubmit(deliveryDate.value);
    
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
    const isCostCenterSection = section.id === 'cost-center-section';
    const isAssetSection = section.id === 'asset-section';
    
    rows.forEach((row, index) => {
        const item = {};
        
        if (isCostCenterSection) {
            // ส่วน K ศูนย์ต้นทุน: column | 1: checkbox | 2: รายการค่าใช้จ่าย | 3: ปริมาณ | 4: หน่วย | 5: กลุ่มวัสดุ | 6: ราคา | 7: ราคาทั้งหมด |
            // ข้อความสั้น = รายการค่าใช้จ่าย (column 2)
            const shortTextCell = row.querySelector('td:nth-child(2)');
            if (shortTextCell) {
                const shortText = shortTextCell.textContent.trim();
                item.short_text = shortText;
                item.material_name = shortText;
            }
        } else if (isAssetSection) {
            // ส่วน A สินทรัพย์: มีเมนูดาวน์รหัสสินทรัพย์
            const assetSelect = row.querySelector('select[name="asset_code"]');
            if (assetSelect) {
                item.asset_code = assetSelect.value;
                item.material_code = assetSelect.value;
                item.material_name = assetSelect.options[assetSelect.selectedIndex]?.text || '';
            }
        } else {
            // ส่วนอื่นๆ (ไม่เลือก): มีเมนูดาวน์วัสดุ
            const materialSelect = row.querySelector('select[name="mat_code"]');
            if (materialSelect) {
                item.material_code = materialSelect.value;
                item.material_name = materialSelect.options[materialSelect.selectedIndex]?.text || '';
            }
        }
        
        // ปริมาณ
        const quantityCellColumn = isCostCenterSection ? 3 : 3;
        const quantityCell = row.querySelector(`td:nth-child(${quantityCellColumn})`);
        if (quantityCell) {
            const quantityText = quantityCell.textContent.replace(/,/g, '').trim();
            item.quantity = parseFloat(quantityText) || 0;
        }
        
        // หน่วย
        const unitSelect = row.querySelector('.unit-select');
        if (unitSelect) item.unit_code = unitSelect.value;
        
        // กลุ่มวัสดุ (ตรวจสอบทั้ง asset-category-select, mrd-group-select, และ expen-pr-select)
        const groupCellColumn = isCostCenterSection ? 5 : 5;
        if (isCostCenterSection) {
            // ส่วน K ศูนย์ต้นทุน: ใช้ expen-pr-select
            const expenPrSelect = row.querySelector(`td:nth-child(${groupCellColumn}) .expen-pr-select`);
            if (expenPrSelect && expenPrSelect.value) {
                item.expen_pr_id = expenPrSelect.value;
                item.group_name = expenPrSelect.value;
            }
        } else if (isAssetSection) {
            // ส่วน A สินทรัพย์: ใช้ asset-category-select
            const assetCategorySelect = row.querySelector(`td:nth-child(${groupCellColumn}) .asset-category-select`);
            if (assetCategorySelect && assetCategorySelect.value) {
                item.asset_category_id = assetCategorySelect.value;
                item.group_name = assetCategorySelect.value;
            }
        } else {
            // ส่วนอื่นๆ (ไม่เลือก): ใช้ mrd-group-select
            const mrdGroupSelect = row.querySelector(`td:nth-child(${groupCellColumn}) .mrd-group-select`);
            if (mrdGroupSelect && mrdGroupSelect.value) {
                item.mrd_group_id = mrdGroupSelect.value;
                item.group_name = mrdGroupSelect.value;
            }
        }
        
        // ราคา
        const priceCellColumn = isCostCenterSection ? 6 : 6;
        const priceCell = row.querySelector(`td:nth-child(${priceCellColumn})`);
        if (priceCell) {
            const priceText = priceCell.textContent.replace(/,/g, '').trim();
            item.unit_price = parseFloat(priceText) || 0;
        }
        
        // ราคาทั้งหมด
        const totalPriceCellColumn = isCostCenterSection ? 7 : 7;
        const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceCellColumn})`);
        if (totalPriceCell) {
            const totalPriceText = totalPriceCell.textContent.replace(/,/g, '').trim();
            item.total_price = parseFloat(totalPriceText) || 0;
        }
        
        
        // เพิ่มเฉพาะรายการที่มีข้อมูล
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
    
    // กำหนดตำแหน่งของ column ตามส่วน
    let quantityColumnIndex, totalPriceColumnIndex;
    if (sectionId === 'cost-center-section') {
        // ส่วน K ศูนย์ต้นทุน: ปริมาณที่ column 3, ราคาทั้งหมดที่ column 7
        quantityColumnIndex = 3;
        totalPriceColumnIndex = 7;
    } else {
        // ส่วนอื่นๆ: ปริมาณที่ column 3, ราคาทั้งหมดที่ column 7
        quantityColumnIndex = 3;
        totalPriceColumnIndex = 7;
    }
    
    rows.forEach(row => {
        const quantityCell = row.querySelector(`td:nth-child(${quantityColumnIndex})`);
        const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceColumnIndex})`);
        
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
            window.location.href = 'request-proposal-list.php';
        });
    } else {
        alert(`${title}: ${message}`);
        window.location.href = 'request-proposal-list.php';
    }
}

// Export functions
window.RequestProposalEditForm = {
    loadProposalData,
    updateProposal
};
