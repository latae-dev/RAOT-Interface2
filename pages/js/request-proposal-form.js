/**
 * Request Proposal Form JavaScript
 * สำหรับจัดการฟอร์มคำขอสร้างใบขอเสนอ
 */

// ========================================
// INITIALIZATION
// ========================================

// เริ่มต้นเมื่อ DOM โหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeDatePickers();
    initializeSelect2();
});

/**
 * เริ่มต้น Date Picker ด้วย Flatpickr
 */
function initializeDatePickers() {
    // เริ่มต้น Date Picker สำหรับวันที่เริ่มต้น
    flatpickr("#start-date", {
        dateFormat: "d-m-Y",
        locale: "th"
    });
    
    // เริ่มต้น Date Picker สำหรับวันที่สิ้นสุด
    flatpickr("#end-date", {
        dateFormat: "d-m-Y",
        locale: "th"
    });
    
    // เริ่มต้น Date Picker สำหรับวันที่ส่งมอบ
    flatpickr("#delivery-date", {
        dateFormat: "d-m-Y",
        locale: "th"
    });
}

/**
 * เริ่มต้น Select2 สำหรับหมวดการกำหนดบัญชีและหน่วยเงิน
 */
function initializeSelect2() {
    // เริ่มต้น Select2 สำหรับหมวดการกำหนดบัญชี
    $('#account-category-select').select2({
        placeholder: "กรุณาเลือก",
        allowClear: true,
        width: '100%'
    });
    
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

// เพิ่ม fallback สำหรับกรณีที่ DOMContentLoaded ไม่ทำงาน
window.addEventListener('load', function() {
    setTimeout(function() {
        const selectEl = document.getElementById('form-type-select');
        if (selectEl && selectEl.options.length <= 1) {
            loadFormTypes();
        }
    }, 1000);
});

/**
 * เริ่มต้นฟอร์ม
 */
function initializeForm() {
    // ดึงข้อมูลผู้ใช้และแสดงรหัสหน่วยงาน
    loadUserDepartmentCode();

    // โหลดข้อมูลหลัก
    loadMainData();
    
    // เติมข้อมูลในแถวเริ่มต้น
    setTimeout(() => {
        fillInitialRows();
    }, 1000);
    
    // โหลดข้อมูลวัสดุ
    loadMaterialData();
    
    // โหลดข้อมูลหน่วยและหน่วยเงิน
    loadUnitData();
    
    // โหลดข้อมูลบัญชี
    loadAccountData();
    
    // ตั้งค่า event listeners
    setupEventListeners();
    
    // ตั้งค่าปุ่มบันทึก
    setupSaveButtons();
    
    // ตั้งค่าเริ่มต้นให้เลือก "ไม่เลือก"
    setTimeout(() => {
        const accountCategorySelect = document.getElementById('account-category-select');
        if (accountCategorySelect && !accountCategorySelect.value) {
            accountCategorySelect.value = 'N';
            showAccountSection('no-category-section');
        }
    }, 500);
}

/**
 * โหลดข้อมูลหลัก
 */
function loadMainData() {
    loadFormTypes();
    loadFactories();
    loadWarehouses();
    loadPurchaseGroups();
    loadBusinessTypes();
    loadMrdGroups();
    loadExpenPr();
}

/**
 * โหลดข้อมูลวัสดุ
 */
function loadMaterialData() {
    loadMatlist();  // สำหรับส่วน "ไม่เลือก" และอื่นๆ
    loadAssets();   // สำหรับส่วน A สินทรัพย์
}

/**
 * โหลดข้อมูลหน่วยและหน่วยเงิน
 */
function loadUnitData() {
    loadCountUnits();
    loadMonetaryUnits();
}

/**
 * โหลดข้อมูลบัญชี
 */
function loadAccountData() {
    loadGLAccounts();
    loadCostCenters();
    loadTaxes();
    loadFundings();
    loadScopes();
    loadWrdFunds();
    loadLiabilities();
    loadAssetCategories();
}

/**
 * ตั้งค่า event listeners
 */
function setupEventListeners() {
    setupAccountCategoryHandler();
    setupPriceCalculation();
    setupAddRemoveButtons();
    setupAddRemoveButtonsForAsset();
    setupAddRemoveButtonsForCostCenter();
    setupClearAndBackButtons();
}

// ========================================
// USER DATA & UTILITIES
// ========================================

/**
 * ดึงข้อมูลรหัสหน่วยงานจาก session
 */
function loadUserDepartmentCode() {
    try {
        // ดึงข้อมูลจาก sessionStorage
        const userSession = sessionStorage.getItem('raot_user_session');
        
        if (userSession) {
            const userData = JSON.parse(userSession);
            const departmentCode = userData.depart_code || '';
            
            // แสดงรหัสหน่วยงานในฟิลด์
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

/**
 * แสดงข้อความผิดพลาด
 */
function showErrorMessage(title, message) {
    const errorTitle = message ? title : 'เกิดข้อผิดพลาด';
    const errorText = message || title;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: errorTitle,
            text: errorText,
            confirmButtonText: 'ตกลง'
        });
    } else {
        alert(errorTitle + (errorText ? '\n' + errorText : ''));
    }
}

/**
 * จัดรูปแบบตัวเลขเป็นเงิน
 */
function formatCurrency(amount) {
    const value = parseFloat(amount) || 0;
    return value.toLocaleString('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * ดึง base path ของ API
 */
function getApiBasePath() {
    return window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
}

// Export functions สำหรับใช้ในไฟล์อื่น
window.RequestProposalForm = {
    loadUserDepartmentCode,
    loadFormTypes
};

// ========================================
// MAIN DATA LOADING
// ========================================

/**
 * ดึงรายการประเภทแบบฟอร์มจาก API และเติมลง dropdown
 */
async function loadFormTypes() {
    try {
        const selectEl = document.getElementById('form-type-select');
        if (!selectEl) {
            return;
        }

        // เคลียร์ Select2 ก่อน (ถ้ามี)
        if ($('#form-type-select').hasClass('select2-hidden-accessible')) {
            $('#form-type-select').select2('destroy');
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ Select2
            const formTypeOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.form_type_name
                }))
            ];
            
            // ใช้ Select2 แทนการเพิ่ม options ธรรมดา
            $('#form-type-select').select2({
                data: formTypeOptions,
                placeholder: 'กรุณาเลือกประเภทแบบฟอร์ม',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการโรงงานจาก API และเติมลง dropdown
 */
async function loadFactories() {
    try {
        const selectEl = document.getElementById('factory-select');
        if (!selectEl) {
            return;
        }

        // เคลียร์ Select2 ก่อน (ถ้ามี)
        if ($('#factory-select').hasClass('select2-hidden-accessible')) {
            $('#factory-select').select2('destroy');
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ Select2
            const factoryOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.factory_name
                }))
            ];
            
            // ใช้ Select2 แทนการเพิ่ม options ธรรมดา
            $('#factory-select').select2({
                data: factoryOptions,
                placeholder: 'กรุณาเลือกโรงงาน',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการที่เก็บสินค้าจาก API และเติมลง dropdown
 */
async function loadWarehouses() {
    try {
        const selectEl = document.getElementById('warehouse-select');
        if (!selectEl) {
            return;
        }

        // เคลียร์ Select2 ก่อน (ถ้ามี)
        if ($('#warehouse-select').hasClass('select2-hidden-accessible')) {
            $('#warehouse-select').select2('destroy');
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ Select2
            const warehouseOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.warehouse_name
                }))
            ];
            
            // ใช้ Select2 แทนการเพิ่ม options ธรรมดา
            $('#warehouse-select').select2({
                data: warehouseOptions,
                placeholder: 'กรุณาเลือกที่เก็บสินค้า',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการกลุ่มการจัดซื้อจาก API และเติมลง dropdown
 */
async function loadPurchaseGroups() {
    try {
        const selectEl = document.getElementById('purchase-group-select');
        if (!selectEl) {
            return;
        }

        // เคลียร์ Select2 ก่อน (ถ้ามี)
        if ($('#purchase-group-select').hasClass('select2-hidden-accessible')) {
            $('#purchase-group-select').select2('destroy');
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ Select2
            const purchaseGroupOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.purchase_group_name
                }))
            ];
            
            // ใช้ Select2 แทนการเพิ่ม options ธรรมดา
            $('#purchase-group-select').select2({
                data: purchaseGroupOptions,
                placeholder: 'กรุณาเลือกกลุ่มการจัดซื้อ',
                allowClear: false,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการประเภทธุรกิจจาก API และเติมลง dropdown
 * สำหรับทุกส่วน (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
 */
async function loadBusinessTypes() {
    try {
        // หา dropdown ประเภทธุรกิจทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const businessTypeSelects = document.querySelectorAll('#business-type-select, #business-type-select-asset, #business-type-select-cost-center');
        if (businessTypeSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        const apiUrl = basePath + '/controllers/parameter/business_type_controller.php?action=all';

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
            // เตรียมข้อมูลสำหรับ Select2 (เก็บเป็น id)
            const businessTypeOptions = [
                { id: '', text: 'กรุณาเลือก', disabled: true },
                ...result.data.map(item => ({
                    id: item.id,
                    text: item.busa_code && item.busa_description ? `${item.busa_code} - ${item.busa_description}` : (item.busa_description || item.busa_code || '')
                }))
            ];
            
            // เติมข้อมูลใน dropdown ประเภทธุรกิจทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            businessTypeSelects.forEach((selectEl) => {
                // ตรวจสอบว่า element อยู่ใน DOM และแสดงอยู่
                if (!selectEl || !selectEl.offsetParent && selectEl.style.display === 'none') {
                    return; // ข้าม element ที่ซ่อนอยู่
                }
                
                // เคลียร์ Select2 ก่อน (ถ้ามี)
                if ($(selectEl).hasClass('select2-hidden-accessible')) {
                    $(selectEl).select2('destroy');
                }
                
                // ใช้ Select2 แทนการเพิ่ม options ธรรมดา
                $(selectEl).select2({
                    data: businessTypeOptions,
                    placeholder: 'กรุณาเลือกประเภทธุรกิจ',
                    allowClear: false,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

// ========================================
// MATERIAL DATA LOADING
// ========================================

/**
 * ดึงรายการวัสดุจาก matlist API (สำหรับส่วน "ไม่เลือก")
 */
async function loadMatlist() {
    try {
        const selectEl = document.getElementById('matlist-select-no-category');
        if (!selectEl) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เก็บข้อมูลไว้ใน global variable
            window.matlistData = result.data;
            
            // เตรียมข้อมูลสำหรับ dropdown
            const matlistOptions = [
                { value: '', text: 'กรุณาเลือกวัสดุ' },
                ...result.data.map(item => ({
                    value: item.mat_code,
                    text: `${item.mat_code} - ${item.mat_name}`,
                    mat_name: item.mat_name,
                    group_name: item.group_name || ''
                }))
            ];
            
            // ล้าง options เดิม
            selectEl.innerHTML = '';
            
            // เพิ่ม options ใหม่
            matlistOptions.forEach(option => {
                const optionEl = document.createElement('option');
                optionEl.value = option.value;
                optionEl.textContent = option.text;
                optionEl.dataset.matName = option.mat_name || '';
                selectEl.appendChild(optionEl);
            });
            
            // ตั้งค่า Select2 สำหรับวัสดุ
            $('#matlist-select-no-category').select2({
                placeholder: "กรุณาเลือกวัสดุ",
                allowClear: true,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการสินทรัพย์จาก asset API (สำหรับส่วน A สินทรัพย์)
 */
async function loadAssets() {
    try {
        const selectEl = document.getElementById('asset-select');
        if (!selectEl) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เก็บข้อมูลไว้ใน global variable
            window.assetData = result.data;
            
            // เตรียมข้อมูลสำหรับ dropdown
            const assetOptions = [
                { value: '', text: 'กรุณาเลือกรหัสสินทรัพย์' },
                ...result.data.map(item => ({
                    value: item.asset_code,
                    text: `${item.asset_code} - ${item.asset_name}`,
                    asset_name: item.asset_name,
                    asset_category_id: item.asset_category_id
                }))
            ];
            
            // ล้าง options เดิม
            selectEl.innerHTML = '';
            
            // เพิ่ม options ใหม่
            assetOptions.forEach(option => {
                const optionEl = document.createElement('option');
                optionEl.value = option.value;
                optionEl.textContent = option.text;
                optionEl.dataset.assetName = option.asset_name || '';
                optionEl.dataset.assetCategoryId = option.asset_category_id || '';
                selectEl.appendChild(optionEl);
            });
            
            // ตั้งค่า Select2 สำหรับรหัสสินทรัพย์
            $('#asset-select').select2({
                placeholder: "กรุณาเลือกรหัสสินทรัพย์",
                allowClear: true,
                width: '100%'
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

// ========================================
// UNIT DATA LOADING
// ========================================

/**
 * ดึงรายการหมวดหมู่สินทรัพย์จาก API สำหรับส่วน A สินทรัพย์
 * ใช้ tb_wrd_groups และกรองเฉพาะ code ที่ขึ้นต้นด้วย 'A'
 */
async function loadAssetCategories() {
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
            // กรองเฉพาะ code ที่ขึ้นต้นด้วย 'A' สำหรับ TYPE A
            window.assetCategoryData = result.data.filter(item => item.code && item.code.startsWith('A'));
            
            // หลังจากโหลดข้อมูลเสร็จ ให้เติมข้อมูลใน dropdown ที่มีอยู่แล้ว
            const assetRows = document.querySelectorAll('#asset-section tbody tr');
            assetRows.forEach(row => {
                const assetCategorySelect = row.querySelector('.asset-category-select');
                if (assetCategorySelect) {
                    fillAssetCategoryDropdown(assetCategorySelect);
                }
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการกลุ่มวัสดุจาก API และเติมลง dropdown
 */
async function loadMrdGroups() {
    try {
        // หา dropdown กลุ่มวัสดุทั้งหมด
        const mrdGroupSelects = document.querySelectorAll('.mrd-group-select');
        if (mrdGroupSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // กรองข้อมูลที่ไม่แสดง code ที่ขึ้นต้นด้วย 'A'
            const filteredData = result.data.filter(item => !item.code || !item.code.startsWith('A'));
            
            // เก็บข้อมูลที่กรองแล้วไว้ใน global variable
            window.mrdGroupData = filteredData;
            
            // เตรียมข้อมูลสำหรับ dropdown
            const mrdGroupOptions = [
                { value: '', text: 'กรุณาเลือกกลุ่มวัสดุ' },
                ...filteredData.map(item => ({
                    value: item.id,
                    text: `${item.code} - ${item.name}`,
                    name: item.name,
                    code: item.code
                }))
            ];
            
            // เติมข้อมูลใน dropdown กลุ่มวัสดุทั้งหมด
            mrdGroupSelects.forEach(selectEl => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                mrdGroupOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.name = option.name || '';
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                // ตั้งค่า Select2 สำหรับกลุ่มวัสดุ
                $(selectEl).select2({
                    placeholder: "กรุณาเลือกกลุ่มวัสดุ",
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการค่าใช้จ่าย PR จาก API และเติมลง dropdown (สำหรับ TYPE K)
 * ใช้ tb_wrd_groups และกรองไม่ให้แสดง code ที่ขึ้นต้นด้วย 'A'
 */
async function loadExpenPr() {
    try {
        // หา dropdown รายการค่าใช้จ่าย PR ทั้งหมด
        const expenPrSelects = document.querySelectorAll('.expen-pr-select');
        if (expenPrSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // กรองข้อมูลที่ไม่แสดง code ที่ขึ้นต้นด้วย 'A' สำหรับ TYPE K
            const filteredData = result.data.filter(item => !item.code || !item.code.startsWith('A'));
            
            // เก็บข้อมูลที่กรองแล้วไว้ใน global variable
            window.expenPrData = filteredData;
            
            // เตรียมข้อมูลสำหรับ dropdown
            const expenPrOptions = [
                { value: '', text: 'กรุณาเลือกรายการค่าใช้จ่าย' },
                ...filteredData.map(item => ({
                    value: item.id,
                    text: `${item.code} - ${item.name}`,
                    name: item.name,
                    code: item.code
                }))
            ];
            
            // เติมข้อมูลใน dropdown รายการค่าใช้จ่าย PR ทั้งหมด
            expenPrSelects.forEach(selectEl => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                expenPrOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.name = option.name || '';
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                // ตั้งค่า Select2 สำหรับรายการค่าใช้จ่าย
                $(selectEl).select2({
                    placeholder: "กรุณาเลือกรายการค่าใช้จ่าย",
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการหน่วยจาก API และเติมลง dropdown
 */
async function loadCountUnits() {
    try {
        // หา dropdown หน่วยทั้งหมดในตาราง
        const unitSelects = document.querySelectorAll('.unit-select');
        if (unitSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เก็บข้อมูลไว้ใน global variable
            window.unitData = result.data;
            
            // เตรียมข้อมูลสำหรับ dropdown
            const unitOptions = [
                { value: '', text: 'กรุณาเลือกหน่วย' },
                ...result.data.map(item => ({
                    value: item.count_unit_code,
                    text: item.count_unit_name
                }))
            ];
            
            // เติมข้อมูลใน dropdown หน่วยทั้งหมด
            unitSelects.forEach(selectEl => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                unitOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือกหน่วย',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการหน่วยเงินจาก API และเติมลง dropdown
 */
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
// ACCOUNT DATA LOADING
// ========================================

/**
 * ดึงรายการบัญชี G/L จาก API และเติมลง dropdown
 * ใช้ข้อมูลจาก liability_controller
 */
async function loadGLAccounts() {
    try {
        // หา dropdown บัญชี G/L ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const glSelects = document.querySelectorAll('#gl-account-select, #gl-account-select-asset, #gl-account-select-cost-center');
        if (glSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
        // ใช้ liability_controller สำหรับ G/L Account
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
            // เก็บข้อมูลไว้ใน global variable เพื่อใช้ใน loadLiabilities()
            window.liabilityData = result.data;
            
            // เตรียมข้อมูลสำหรับ dropdown (ใช้ liability_code และ liability_name)
            const glOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.liability_code} - ${item.liability_name}`
                }))
            ];
            
            // เติมข้อมูลใน dropdown บัญชี G/L ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            glSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                glOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการศูนย์ต้นทุนจาก API และเติมลง dropdown
 */
async function loadCostCenters() {
    try {
        // หา dropdown ศูนย์ต้นทุนทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const costCenterSelects = document.querySelectorAll('#cost-center-select, #cost-center-select-asset, #cost-center-select-cost-center');
        if (costCenterSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            
            // เตรียมข้อมูลสำหรับ dropdown
            const costCenterOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.cost_center_code} - ${item.cost_center_name}`,
                    code: item.cost_center_code
                }))
            ];
            
            // เติมข้อมูลใน dropdown ศูนย์ต้นทุนทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            costCenterSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                costCenterOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
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
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการภาษีจาก API และเติมลง dropdown
 */
async function loadTaxes() {
    try {
        // หา dropdown ภาษีทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const taxSelects = document.querySelectorAll('#tax-select, #tax-select-asset, #tax-select-cost-center');
        if (taxSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ dropdown
            const taxOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.tax_name} (${item.tax_rate}%)`
                }))
            ];
            
            // เติมข้อมูลใน dropdown ภาษีทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            taxSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                taxOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการเงินทุนจาก API และเติมลง dropdown
 */
async function loadFundings() {
    try {
        // หา dropdown เงินทุนทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const fundingSelects = document.querySelectorAll('#fund-select, #fund-select-asset, #fund-select-cost-center');
        if (fundingSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ dropdown
            const fundingOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.funding_code} - ${item.funding_name}`
                }))
            ];
            
            // เติมข้อมูลใน dropdown เงินทุนทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            fundingSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                fundingOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการขอบเขตตามหน้าที่จาก API และเติมลง dropdown
 */
async function loadScopes() {
    try {
        // หา dropdown ขอบเขตตามหน้าที่ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const scopeSelects = document.querySelectorAll('#scope-select, #scope-select-asset, #scope-select-cost-center');
        if (scopeSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
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
            // เตรียมข้อมูลสำหรับ dropdown
            const scopeOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.scope_code} - ${item.scope_name}`
                }))
            ];
            
            // เติมข้อมูลใน dropdown ขอบเขตตามหน้าที่ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            scopeSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                scopeOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการ Funds Center จาก API และเติมลง dropdown
 * ใช้ API และข้อมูลเดียวกันกับ Cost Center
 */
async function loadWrdFunds() {
    try {
        // หา dropdown Funds Center ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const wrdFundSelects = document.querySelectorAll('#funds-center-select, #funds-center-select-asset, #funds-center-select-cost-center');
        if (wrdFundSelects.length === 0) {
            return;
        }

        // baseUrl จาก php ถูกคำนวนในหน้าและ path controller
        const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
        // ใช้ API เดียวกับ Cost Center
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
            let defaultFundId = '';
            if (departmentCode) {
                const matchedItem = result.data.find(item => item.cost_center_code === departmentCode);
                if (matchedItem) {
                    defaultFundId = matchedItem.id;
                }
            }
            
            // เตรียมข้อมูลสำหรับ dropdown (ใช้ข้อมูลจาก Cost Center)
            const wrdFundOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.cost_center_code} - ${item.cost_center_name}`,
                    code: item.cost_center_code
                }))
            ];
            
            // เติมข้อมูลใน dropdown Funds Center ทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            wrdFundSelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                wrdFundOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    optionEl.dataset.code = option.code || '';
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
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
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}

/**
 * ดึงรายการภาระผูกพันจาก API และเติมลง dropdown
 * ใช้ข้อมูลจาก window.liabilityData ที่โหลดไว้แล้วใน loadGLAccounts()
 */
async function loadLiabilities() {
    try {
        // หา dropdown ภาระผูกพันทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
        const liabilitySelects = document.querySelectorAll('#liability-select, #liability-select-asset, #liability-select-cost-center');
        if (liabilitySelects.length === 0) {
            return;
        }

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
            // เตรียมข้อมูลสำหรับ dropdown
            const liabilityOptions = [
                { value: '', text: 'กรุณาเลือก' },
                ...result.data.map(item => ({
                    value: item.id,
                    text: `${item.liability_code} - ${item.liability_name}`
                }))
            ];
            
            // เติมข้อมูลใน dropdown ภาระผูกพันทั้งหมด (ไม่เลือก, A สินทรัพย์, K ศูนย์ต้นทุน)
            liabilitySelects.forEach((selectEl, index) => {
                // ล้าง options เดิม
                selectEl.innerHTML = '';
                
                // เพิ่ม options ใหม่
                liabilityOptions.forEach(option => {
                    const optionEl = document.createElement('option');
                    optionEl.value = option.value;
                    optionEl.textContent = option.text;
                    selectEl.appendChild(optionEl);
                });
                
                // Initialize Select2 for this dropdown
                $(selectEl).select2({
                    placeholder: 'กรุณาเลือก',
                    allowClear: true,
                    width: '100%'
                });
            });
        }
    } catch (err) {
        // ไม่แสดง error เพื่อไม่รบกวนผู้ใช้
    }
}


// ========================================
// TABLE MANAGEMENT - ASSET SECTION
// ========================================

/**
 * ตั้งค่า event listener สำหรับปุ่มเพิ่ม/ลบรายการในส่วน A สินทรัพย์
 */
function setupAddRemoveButtonsForAsset() {
    // หาปุ่มเพิ่มรายการในส่วน A สินทรัพย์
    const addButtons = document.querySelectorAll('#asset-section .btn-success-light');
    const removeButtons = document.querySelectorAll('#asset-section .btn-danger-light');
    
    // ตั้งค่า event listener สำหรับปุ่มเพิ่มรายการ
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            addNewRowForAsset(this);
        });
    });
    
    // ตั้งค่า event listener สำหรับปุ่มลบรายการ
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            removeSelectedRowsForAsset(this);
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
    
    // เติมข้อมูลใน dropdown ของแถวใหม่
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
            <select class="form-control asset-select" name="asset_code" style="width: 100%">
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

// ========================================
// TABLE MANAGEMENT - COST CENTER SECTION
// ========================================

/**
 * เติมข้อมูลในแถวเริ่มต้นของทุกส่วน
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
        setupPriceCalculationForCostCenter(costCenterRow);
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
        
        // เพิ่ม options ใหม่ (กรอง code ที่ขึ้นต้นด้วย 'A')
        window.expenPrData
            .filter(item => !item.code || !item.code.startsWith('A'))
            .forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.code} - ${item.name}`;
                option.dataset.name = item.name;
                option.dataset.code = item.code;
                expenPrSelect.appendChild(option);
            });
        
        // ตั้งค่า Select2 สำหรับรายการค่าใช้จ่าย
        $(expenPrSelect).select2({
            placeholder: "กรุณาเลือกรายการค่าใช้จ่าย",
            allowClear: true,
            width: '100%'
        });
    }
}

/**
 * ตั้งค่าการคำนวณราคาสำหรับส่วน K ศูนย์ต้นทุน
 */
function setupPriceCalculationForCostCenter(row) {
    // ตั้งค่า event listener สำหรับการเปลี่ยนแปลงปริมาณและราคา
    const quantityCell = row.querySelector('td:nth-child(3)'); // ช่องปริมาณ
    const priceCell = row.querySelector('td:nth-child(6)'); // ช่องราคา
    const totalPriceCell = row.querySelector('td:nth-child(7)'); // ช่องราคาทั้งหมด
    
    if (quantityCell && priceCell && totalPriceCell) {
        // Event listener สำหรับปริมาณ
        quantityCell.addEventListener('input', function() {
            calculateTotalPriceForCostCenter(row);
            calculateGrandTotalForCostCenter();
        });
        
        // Event listener สำหรับราคา
        priceCell.addEventListener('input', function() {
            calculateTotalPriceForCostCenter(row);
            calculateGrandTotalForCostCenter();
        });
    }
}

/**
 * คำนวณราคาทั้งหมดสำหรับแถวในส่วน K ศูนย์ต้นทุน
 */
function calculateTotalPriceForCostCenter(row) {
    const quantityCell = row.querySelector('td:nth-child(3)');
    const priceCell = row.querySelector('td:nth-child(6)');
    const totalPriceCell = row.querySelector('td:nth-child(7)');
    
    if (quantityCell && priceCell && totalPriceCell) {
        const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
        const unitPrice = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
        const totalPrice = quantity * unitPrice;
        
        totalPriceCell.textContent = totalPrice.toFixed(2);
    }
}

/**
 * คำนวณยอดรวมทั้งหมดสำหรับส่วน K ศูนย์ต้นทุน
 */
function calculateGrandTotalForCostCenter() {
    const section = document.querySelector('#cost-center-section');
    if (!section) return;
    
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
    
    // อัปเดตยอดรวมในตาราง
    const tfoot = section.querySelector('tfoot');
    if (tfoot) {
        const totalQuantityCell = tfoot.querySelector('tr:first-child td:last-child');
        const totalAmountCell = tfoot.querySelector('tr:last-child td:last-child');
        
        if (totalQuantityCell) {
            totalQuantityCell.textContent = totalQuantity.toFixed(2);
        }
        if (totalAmountCell) {
            totalAmountCell.textContent = totalAmount.toFixed(2);
        }
    }
}

/**
 * ตั้งค่า event listener สำหรับปุ่มเพิ่ม/ลบรายการในส่วน K ศูนย์ต้นทุน
 */
function setupAddRemoveButtonsForCostCenter() {
    const costCenterSection = document.querySelector('#cost-center-section');
    if (!costCenterSection) return;
    
    // ปุ่มเพิ่มรายการ
    const addButton = costCenterSection.querySelector('.btn-success-light');
    if (addButton) {
        addButton.addEventListener('click', function() {
            addNewRowForCostCenter();
        });
    }
    
    // ปุ่มลบรายการ
    const removeButton = costCenterSection.querySelector('.btn-danger-light');
    if (removeButton) {
        removeButton.addEventListener('click', function() {
            removeSelectedRowsForCostCenter();
        });
    }
}

/**
 * เพิ่มแถวใหม่ในส่วน K ศูนย์ต้นทุน
 */
function addNewRowForCostCenter() {
    const tbody = document.querySelector('#cost-center-section tbody');
    if (!tbody) return;
    
    // สร้างแถวใหม่
    const newRow = createNewRowForCostCenter();
    tbody.appendChild(newRow);
    
    // เติมข้อมูลใน dropdown ของแถวใหม่
    fillDropdownsInRowForCostCenter(newRow);
    setupPriceCalculationForCostCenter(newRow);
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotalForCostCenter();
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
 * ลบแถวที่เลือกในส่วน K ศูนย์ต้นทุน
 */
function removeSelectedRowsForCostCenter() {
    const costCenterSection = document.querySelector('#cost-center-section');
    if (!costCenterSection) return;
    
    const checkedBoxes = costCenterSection.querySelectorAll('tbody tr .form-check-input:checked');
    if (checkedBoxes.length === 0) {
        return;
    }
    
    checkedBoxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row) {
            row.remove();
        }
    });
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotalForCostCenter();
}

// ========================================
// BUTTON MANAGEMENT
// ========================================

/**
 * ตั้งค่า event listener สำหรับปุ่มล้างข้อมูลและย้อนกลับ
 */
function setupClearAndBackButtons() {
    // ปุ่มล้างข้อมูล - ทั้ง 3 Type (รีเฟรชหน้า)
    const clearButtons = document.querySelectorAll('button[class*="btn-danger"]');
    clearButtons.forEach(button => {
        if (button.textContent.includes('ล้างข้อมูล')) {
            button.addEventListener('click', function() {
                // รีเฟรชหน้าเพื่อล้างข้อมูลทั้งหมด
                window.location.reload();
            });
        }
    });
    
    // ปุ่มย้อนกลับเมนู - ทั้ง 3 Type
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
// DATA CLEARING FUNCTIONS
// ========================================

/**
 * ล้างข้อมูลทั้งหมดทั้ง 3 Type
 */
function clearAllData() {
    // ล้างข้อมูลฟอร์มหลักทั้งหมด
    clearAllFormFields();
    
    // ล้างข้อมูลส่วน "ไม่เลือก"
    clearNoCategorySection();
    
    // ล้างข้อมูลส่วน A สินทรัพย์
    clearAssetSection();
    
    // ล้างข้อมูลส่วน K ศูนย์ต้นทุน
    clearCostCenterSection();
    
    // รีเซ็ต dropdown หลัก
    resetMainDropdowns();
}

/**
 * ล้างข้อมูลส่วน "ไม่เลือก"
 */
function clearNoCategorySection() {
    const section = document.querySelector('#no-category-section');
    if (!section) return;
    
    // ล้างข้อมูลในตาราง
    const tbody = section.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="product-list">
                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                <td class="text-start">
                    <select class="form-control" id="matlist-select-no-category" name="mat_code">
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
            </tr>
        `;
        
        // เติมข้อมูลในแถวเริ่มต้น
        const row = tbody.querySelector('tr');
        if (row) {
            fillDropdownsInRow(row);
            setupPriceCalculation(row);
        }
    }
    
    // ล้างข้อมูลในฟอร์ม
    clearSectionForm(section);
}

/**
 * ล้างข้อมูลส่วน A สินทรัพย์
 */
function clearAssetSection() {
    const section = document.querySelector('#asset-section');
    if (!section) return;
    
    // ล้างข้อมูลในตาราง
    const tbody = section.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="product-list">
                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                <td class="text-start">
                    <select class="form-control" id="asset-select" name="asset_code">
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
            <select class="form-control mrd-group-select" name="mrd_group_id" style="width: 100%">
                <option value="">กรุณาเลือกกลุ่มวัสดุ</option>
            </select>
        </td>
                <td contenteditable="true">0.00</td>
                <td contenteditable="true" class="text-end">0.00</td>
            </tr>
        `;
        
        // เติมข้อมูลในแถวเริ่มต้น
        const row = tbody.querySelector('tr');
        if (row) {
            fillDropdownsInRowForAsset(row);
            setupPriceCalculationForAsset(row);
        }
    }
    
    // ล้างข้อมูลในฟอร์ม
    clearSectionForm(section);
}

/**
 * ล้างข้อมูลส่วน K ศูนย์ต้นทุน
 */
function clearCostCenterSection() {
    const section = document.querySelector('#cost-center-section');
    if (!section) return;
    
    // ล้างข้อมูลในตาราง
    const tbody = section.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="product-list">
                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
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
            </tr>
        `;
        
        // เติมข้อมูลในแถวเริ่มต้น
        const row = tbody.querySelector('tr');
        if (row) {
            fillDropdownsInRowForCostCenter(row);
            setupPriceCalculationForCostCenter(row);
        }
    }
    
    // ล้างข้อมูลในฟอร์ม
    clearSectionForm(section);
}

/**
 * ล้างข้อมูลฟิลด์ทั้งหมดในหน้า รวมถึงส่วนหัว
 */
function clearAllFormFields() {
    // ล้าง input fields ทั้งหมด (รวมส่วนหัว)
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"]');
    inputs.forEach(input => {
        if (!input.disabled && !input.readOnly) {
            input.value = '';
        }
    });
    
    // ล้าง textarea ทั้งหมด (รวมส่วนหัว)
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.value = '';
    });
    
    // ล้าง contenteditable ทั้งหมด
    const contentEditables = document.querySelectorAll('[contenteditable="true"]');
    contentEditables.forEach(element => {
        element.textContent = '';
    });
    
    // ล้าง checkbox ทั้งหมด
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // ล้าง radio buttons ทั้งหมด
    const radios = document.querySelectorAll('input[type="radio"]');
    radios.forEach(radio => {
        radio.checked = false;
    });
    
    // ล้างข้อมูลส่วนหัวเฉพาะ
    clearHeaderSection();
}

/**
 * ล้างข้อมูลส่วนหัว
 */
function clearHeaderSection() {
    // ล้างข้อมูลฟอร์มส่วนหัว
    const headerSection = document.querySelector('.card-body.px-2.py-4.px-sm-4.border-bottom');
    if (headerSection) {
        // ล้าง input fields ในส่วนหัว
        const headerInputs = headerSection.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"]');
        headerInputs.forEach(input => {
            if (!input.disabled && !input.readOnly) {
                input.value = '';
            }
        });
        
        // ล้าง textarea ในส่วนหัว
        const headerTextareas = headerSection.querySelectorAll('textarea');
        headerTextareas.forEach(textarea => {
            textarea.value = '';
        });
        
        // ล้าง select dropdowns ในส่วนหัว (ยกเว้น dropdown หลัก)
        const headerSelects = headerSection.querySelectorAll('select');
        headerSelects.forEach(select => {
            if (select.id !== 'form-type-select' && 
                select.id !== 'account-category-select' && 
                select.id !== 'factory-select' && 
                select.id !== 'warehouse-select' && 
                select.id !== 'purchase-group-select' &&
                select.id !== 'business-type-select' &&
                select.id !== 'business-type-select-asset' &&
                select.id !== 'business-type-select-cost-center') {
                select.selectedIndex = 0;
            }
        });
    }
}

/**
 * รีเซ็ต dropdown หลัก
 */
function resetMainDropdowns() {
    // รีเซ็ต dropdown หลัก
    const mainDropdowns = [
        'form-type-select',
        'account-category-select', 
        'factory-select',
        'warehouse-select',
        'purchase-group-select',
        'business-type-select',
        'business-type-select-asset',
        'business-type-select-cost-center'
    ];
    
    mainDropdowns.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.selectedIndex = 0;
        }
    });
    
    // ซ่อนทุกส่วน
    hideAllAccountSections();
}

/**
 * ล้างข้อมูลฟอร์มหลัก
 */
function clearMainForm() {
    // ล้างข้อมูลฟอร์มหลัก
    const form = document.querySelector('form') || document;
    
    // ล้าง input fields
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
    inputs.forEach(input => {
        if (!input.disabled) {
            input.value = '';
        }
    });
    
    // ล้าง textarea
    const textareas = form.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.value = '';
    });
    
    // ล้าง select dropdowns
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        if (select.id !== 'form-type-select' && 
            select.id !== 'account-category-select' && 
            select.id !== 'factory-select' && 
            select.id !== 'warehouse-select' && 
            select.id !== 'purchase-group-select' &&
            select.id !== 'business-type-select' &&
            select.id !== 'business-type-select-asset' &&
            select.id !== 'business-type-select-cost-center') {
            select.selectedIndex = 0;
        }
    });
}

/**
 * ล้างข้อมูลฟอร์มในแต่ละส่วน
 */
function clearSectionForm(section) {
    // ล้าง input fields
    const inputs = section.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
    inputs.forEach(input => {
        if (!input.disabled) {
            input.value = '';
        }
    });
    
    // ล้าง textarea
    const textareas = section.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.value = '';
    });
    
    // ล้าง select dropdowns (ยกเว้น dropdown หลัก)
    const selects = section.querySelectorAll('select');
    selects.forEach(select => {
        if (!select.id.includes('asset-select') && 
            !select.id.includes('unit-select') &&
            !select.id.includes('monetary-unit')) {
            select.selectedIndex = 0;
        }
    });
}

// ========================================
// PRICE CALCULATION - ASSET SECTION
// ========================================

/**
 * ตั้งค่าการคำนวณราคาสำหรับส่วน A สินทรัพย์
 */
function setupPriceCalculationForAsset(row) {
    // ตั้งค่า event listener สำหรับการเปลี่ยนแปลงปริมาณและราคา
    const quantityCell = row.querySelector('td:nth-child(3)'); // ช่องปริมาณ
    const priceCell = row.querySelector('td:nth-child(6)'); // ช่องราคา
    const totalPriceCell = row.querySelector('td:nth-child(7)'); // ช่องราคาทั้งหมด
    
    if (quantityCell && priceCell && totalPriceCell) {
        // Event listener สำหรับปริมาณ
        quantityCell.addEventListener('input', function() {
            calculateTotalPriceForAsset(row);
            calculateGrandTotalForAsset();
        });
        
        // Event listener สำหรับราคา
        priceCell.addEventListener('input', function() {
            calculateTotalPriceForAsset(row);
            calculateGrandTotalForAsset();
        });
    }
}

/**
 * คำนวณราคาทั้งหมดสำหรับแถวในส่วน A สินทรัพย์
 */
function calculateTotalPriceForAsset(row) {
    const quantityCell = row.querySelector('td:nth-child(3)');
    const priceCell = row.querySelector('td:nth-child(6)');
    const totalPriceCell = row.querySelector('td:nth-child(7)');
    
    if (quantityCell && priceCell && totalPriceCell) {
        const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
        const unitPrice = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
        const totalPrice = quantity * unitPrice;
        
        totalPriceCell.textContent = totalPrice.toFixed(2);
    }
}

/**
 * คำนวณยอดรวมทั้งหมดสำหรับส่วน A สินทรัพย์
 */
function calculateGrandTotalForAsset() {
    const section = document.querySelector('#asset-section');
    if (!section) return;
    
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
    
    // อัปเดตยอดรวมในตาราง
    const tfoot = section.querySelector('tfoot');
    if (tfoot) {
        const totalQuantityCell = tfoot.querySelector('tr:first-child td:last-child');
        const totalAmountCell = tfoot.querySelector('tr:last-child td:last-child');
        
        if (totalQuantityCell) {
            totalQuantityCell.textContent = totalQuantity.toFixed(2);
        }
        if (totalAmountCell) {
            totalAmountCell.textContent = totalAmount.toFixed(2);
        }
    }
}

/**
 * เติมข้อมูลใน dropdown ของแถวใหม่ในส่วน A สินทรัพย์
 */
function fillDropdownsInRowForAsset(row) {
    // เติมข้อมูลสินทรัพย์ (ถ้ามี)
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
    
    // เติมข้อมูลกลุ่มวัสดุ (ใช้ Asset Category สำหรับส่วน A สินทรัพย์)
    const assetCategorySelect = row.querySelector('.asset-category-select');
    if (assetCategorySelect) {
        fillAssetCategoryDropdown(assetCategorySelect);
    }
}

/**
 * เติมข้อมูล Asset Category ลง dropdown
 */
function fillAssetCategoryDropdown(assetCategorySelect) {
    // ใช้ assetCategoryData ถ้ามี หรือใช้ mrdGroupData และกรองเฉพาะ code ที่ขึ้นต้นด้วย 'A'
    const dataSource = window.assetCategoryData || (window.mrdGroupData ? window.mrdGroupData.filter(item => item.code && item.code.startsWith('A')) : []);
    
    if (dataSource && dataSource.length > 0) {
        // Destroy Select2 ก่อน (ถ้ามี)
        if ($(assetCategorySelect).hasClass('select2-hidden-accessible')) {
            $(assetCategorySelect).select2('destroy');
        }
        
        // ล้าง options เดิม (ยกเว้น option แรก)
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
        
        // เพิ่ม options ใหม่ (ใช้ข้อมูลจาก tb_wrd_groups ที่กรองเฉพาะ code ที่ขึ้นต้นด้วย 'A')
        dataSource.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.code} - ${item.name}`;
            option.dataset.name = item.name;
            option.dataset.code = item.code;
            assetCategorySelect.appendChild(option);
        });
        
        // ตั้งค่า Select2 ใหม่
        $(assetCategorySelect).select2({
            placeholder: "กรุณาเลือกกลุ่มวัสดุ",
            allowClear: true,
            width: '100%'
        });
    }
}

/**
 * ลบแถวที่เลือกในส่วน A สินทรัพย์
 */
function removeSelectedRowsForAsset(button) {
    // หาตารางในส่วน A สินทรัพย์
    const table = button.closest('#asset-section').querySelector('table');
    const tbody = table.querySelector('tbody');
    
    // หาแถวที่ถูกเลือก
    const rowsToRemove = [];
    const checkboxes = tbody.querySelectorAll('input[type="checkbox"]:checked');
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row) {
            rowsToRemove.push(row);
        }
    });
    
    // ลบแถวที่เลือก
    rowsToRemove.forEach(row => {
        tbody.removeChild(row);
    });
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotalForAsset(table);
}

/**
 * คำนวณยอดรวมในส่วน A สินทรัพย์
 */
function calculateGrandTotalForAsset(table) {
    const tbody = table.querySelector('tbody');
    const tfoot = table.querySelector('tfoot');
    
    if (!tbody || !tfoot) return;
    
    let totalQuantity = 0;
    let totalAmount = 0;
    
    // คำนวณจากแถวที่มีข้อมูล
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const quantityCell = row.querySelector('td:nth-child(3)');
        const amountCell = row.querySelector('td:nth-child(7)');
        
        if (quantityCell && amountCell) {
            const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
            const amount = parseFloat(amountCell.textContent.replace(/,/g, '')) || 0;
            
            totalQuantity += quantity;
            totalAmount += amount;
        }
    });
    
    // อัปเดตยอดรวมใน footer
    const quantityFooter = tfoot.querySelector('tr:first-child td:last-child');
    const amountFooter = tfoot.querySelector('tr:last-child td:last-child');
    
    if (quantityFooter) {
        quantityFooter.textContent = totalQuantity.toFixed(2);
    }
    if (amountFooter) {
        amountFooter.textContent = totalAmount.toFixed(2);
    }
}

/**
 * ตั้งค่า event listener สำหรับการคำนวณราคา
 */
function setupPriceCalculation() {
    // หาตารางทั้งหมดที่มีการคำนวณราคา
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        // หาแถวข้อมูลทั้งหมด
        const rows = table.querySelectorAll('tbody tr.product-list');
        
        rows.forEach(row => {
            // หาช่องปริมาณ (column ที่ 3)
            const quantityCell = row.querySelector('td:nth-child(3)');
            // หาช่องราคาต่อหน่วย (column ที่ 6)
            const unitPriceCell = row.querySelector('td:nth-child(6)');
            // หาช่องราคาทั้งหมด (column ที่ 7)
            const totalPriceCell = row.querySelector('td:nth-child(7)');
            
            if (quantityCell && unitPriceCell && totalPriceCell) {
                // ตั้งค่า event listener สำหรับปริมาณ
                quantityCell.addEventListener('input', function() {
                    calculateTotalPrice(this, unitPriceCell, totalPriceCell);
                });
                
                // ตั้งค่า event listener สำหรับราคาต่อหน่วย
                unitPriceCell.addEventListener('input', function() {
                    calculateTotalPrice(this, quantityCell, totalPriceCell);
                });
            }
        });
    });
}

/**
 * คำนวณราคาทั้งหมด
 */
function calculateTotalPrice(triggerCell, otherCell, totalCell) {
    try {
        // ดึงค่าปริมาณ
        const quantity = parseFloat(triggerCell.textContent.replace(/,/g, '')) || 0;
        // ดึงค่าราคาต่อหน่วย
        const unitPrice = parseFloat(otherCell.textContent.replace(/,/g, '')) || 0;
        
        // คำนวณราคาทั้งหมด
        const totalPrice = quantity * unitPrice;
        
        // แสดงผลราคาทั้งหมด
        if (totalPrice > 0) {
            totalCell.textContent = totalPrice.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else {
            totalCell.textContent = '0.00';
        }
        
        // คำนวณยอดรวมทั้งหมด
        calculateGrandTotal();
        
    } catch (err) {
    }
}

/**
 * คำนวณยอดรวมทั้งหมด
 */
function calculateGrandTotal() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        // หาแถวข้อมูลทั้งหมด
        const rows = table.querySelectorAll('tbody tr.product-list');
        let grandTotal = 0;
        let totalQuantity = 0;
        
        rows.forEach(row => {
            // หาช่องราคาทั้งหมด (column ที่ 7)
            const totalPriceCell = row.querySelector('td:nth-child(7)');
            // หาช่องปริมาณ (column ที่ 3)
            const quantityCell = row.querySelector('td:nth-child(3)');
            
            if (totalPriceCell) {
                const price = parseFloat(totalPriceCell.textContent.replace(/,/g, '')) || 0;
                grandTotal += price;
            }
            
            if (quantityCell) {
                const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
                totalQuantity += quantity;
            }
        });
        
        // อัปเดตยอดรวมในตาราง
        const totalQuantityCell = table.querySelector('tfoot tr:first-child td:last-child');
        const grandTotalCell = table.querySelector('tfoot tr:last-child td:last-child');
        
        if (totalQuantityCell) {
            // ปริมาณทั้งหมด = ผลรวมของปริมาณ
            totalQuantityCell.textContent = totalQuantity.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        if (grandTotalCell) {
            // รวมเป็นเงินทั้งสิ้น = ผลรวมของราคาทั้งหมด
            grandTotalCell.textContent = grandTotal.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
}

// ========================================
// TABLE MANAGEMENT - NO CATEGORY SECTION
// ========================================

/**
 * ตั้งค่า event listener สำหรับปุ่มเพิ่ม/ลบรายการ
 */
function setupAddRemoveButtons() {
    // หาปุ่มเพิ่มรายการในส่วน "ไม่เลือก" เท่านั้น
    const addButtons = document.querySelectorAll('#no-category-section .btn-success-light');
    const removeButtons = document.querySelectorAll('#no-category-section .btn-danger-light');
    
    // ตั้งค่า event listener สำหรับปุ่มเพิ่มรายการ
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            addNewRow(this);
        });
    });
    
    // ตั้งค่า event listener สำหรับปุ่มลบรายการ
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            removeSelectedRows(this);
        });
    });
}

/**
 * เพิ่มแถวใหม่
 */
function addNewRow(button) {
    // หาตารางในส่วน "ไม่เลือก" เท่านั้น
    const table = button.closest('#no-category-section').querySelector('table');
    const tbody = table.querySelector('tbody');
    
    // สร้างแถวใหม่
    const newRow = createNewRow();
    
    // เพิ่มแถวใหม่ลงในตาราง
    tbody.appendChild(newRow);
    
    // เติมข้อมูลใน dropdown ของแถวใหม่
    fillDropdownsInRow(newRow);
    
    // ตั้งค่า event listener สำหรับแถวใหม่
    setupRowEventListeners(newRow);
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotal();
}

/**
 * สร้างแถวใหม่
 */
function createNewRow() {
    const row = document.createElement('tr');
    row.className = 'product-list';
    
    // สร้าง HTML สำหรับแถวใหม่
    row.innerHTML = `
        <td class="product-checkbox">
            <input class="form-check-input" type="checkbox" value="" aria-label="...">
        </td>
        <td class="text-start">
            <select class="form-control" name="mat_code">
                <option value="">กรุณาเลือกวัสดุ</option>
            </select>
        </td>
        <td contenteditable="true">0</td>
        <td>
            <select class="form-control unit-select custom-unit-select" name="unit">
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
    
    // เติมข้อมูลวัสดุและหน่วยในแถวใหม่
    fillDropdownsInRow(row);
    
    return row;
}

/**
 * เติมข้อมูล dropdown ในแถว
 */
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
        
        // ตั้งค่า Select2 สำหรับหน่วยเงินอังกฤษ
        $(monetaryUnitSelect).select2({
            placeholder: "กรุณาเลือกหน่วยเงิน",
            allowClear: true,
            width: '100%'
        });
        
        // ตั้งค่า event listener สำหรับการเชื่อมโยงกับหน่วยเงินไทย
        $(monetaryUnitSelect).on('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const thName = selectedOption.dataset.thName;
            
            // หา dropdown หน่วยเงินไทยในแถวเดียวกัน
            const row = this.closest('tr');
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
        
        // ตั้งค่า Select2 สำหรับหน่วยเงินไทย
        $(monetaryUnitThSelect).select2({
            placeholder: "กรุณาเลือกหน่วยเงิน",
            allowClear: true,
            width: '100%'
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
        
        // เพิ่ม options ใหม่ (กรอง code ที่ขึ้นต้นด้วย 'A')
        window.mrdGroupData
            .filter(item => !item.code || !item.code.startsWith('A'))
            .forEach(item => {
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
 * ลบแถวที่เลือก
 */
function removeSelectedRows(button) {
    // หาตารางในส่วน "ไม่เลือก" เท่านั้น
    const table = button.closest('#no-category-section').querySelector('table');
    const tbody = table.querySelector('tbody');
    
    // หาแถวที่เลือก (มี checkbox ถูกเลือก)
    const selectedRows = tbody.querySelectorAll('tr.product-list');
    const rowsToRemove = [];
    
    selectedRows.forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.checked) {
            rowsToRemove.push(row);
        }
    });
    
    // ลบแถวที่เลือก
    rowsToRemove.forEach(row => {
        tbody.removeChild(row);
    });
    
    // คำนวณยอดรวมใหม่
    calculateGrandTotal();
}

/**
 * ตั้งค่า event listener สำหรับแถว
 */
function setupRowEventListeners(row) {
    // หาช่องปริมาณ (column ที่ 3)
    const quantityCell = row.querySelector('td:nth-child(3)');
    // หาช่องราคาต่อหน่วย (column ที่ 6)
    const unitPriceCell = row.querySelector('td:nth-child(6)');
    // หาช่องราคาทั้งหมด (column ที่ 7)
    const totalPriceCell = row.querySelector('td:nth-child(7)');
    
    if (quantityCell && unitPriceCell && totalPriceCell) {
        // ตั้งค่า event listener สำหรับปริมาณ
        quantityCell.addEventListener('input', function() {
            calculateTotalPrice(this, unitPriceCell, totalPriceCell);
        });
        
        // ตั้งค่า event listener สำหรับราคาต่อหน่วย
        unitPriceCell.addEventListener('input', function() {
            calculateTotalPrice(this, quantityCell, totalPriceCell);
        });
    }
}

// ========================================
// ACCOUNT CATEGORY MANAGEMENT
// ========================================

/**
 * ตั้งค่า event listener สำหรับหมวดการกำหนดบัญชี
 */
function setupAccountCategoryHandler() {
    const accountCategorySelect = document.getElementById('account-category-select');
    
    if (accountCategorySelect) {
        // ใช้ jQuery event สำหรับ Select2
        $('#account-category-select').on('change', function() {
            const selectedValue = this.value;
            toggleAccountCategorySections(selectedValue);
        });
        
        // เรียกใช้ครั้งแรกเพื่อตั้งค่าเริ่มต้น
        const initialValue = accountCategorySelect.value || 'N';
        toggleAccountCategorySections(initialValue);
        
        // ถ้าไม่มีค่าที่เลือก ให้เลือก "ไม่เลือก" เป็นค่าเริ่มต้น
        if (!accountCategorySelect.value) {
            $('#account-category-select').val('N').trigger('change');
            showAccountSection('no-category-section');
        }
    }
}

/**
 * แสดง/ซ่อนส่วนต่างๆ ตามหมวดการกำหนดบัญชีที่เลือก
 */
function toggleAccountCategorySections(selectedValue) {
    // ซ่อนทุกส่วนก่อน
    hideAllAccountSections();
    
    // แสดงส่วนที่เกี่ยวข้องตามการเลือก
    switch(selectedValue) {
        case 'A':
            showAccountSection('asset-section');
            // โหลดข้อมูลประเภทธุรกิจสำหรับส่วน A สินทรัพย์
            setTimeout(() => {
                loadBusinessTypes();
            }, 100);
            break;
        case 'K':
            showAccountSection('cost-center-section');
            // โหลดข้อมูลประเภทธุรกิจสำหรับส่วน K ศูนย์ต้นทุน
            setTimeout(() => {
                loadBusinessTypes();
            }, 100);
            break;
        case 'N':
        case '':
        case 'ไม่เลือก':
            showAccountSection('no-category-section');
            // โหลดข้อมูลประเภทธุรกิจสำหรับส่วนไม่เลือก
            setTimeout(() => {
                loadBusinessTypes();
            }, 100);
            break;
        default:
            // ไม่แสดงส่วนไหนเลย
            break;
    }
}

/**
 * ซ่อนทุกส่วนของหมวดการกำหนดบัญชี
 */
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

/**
 * แสดงส่วนที่เลือก
 */
function showAccountSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = 'block';
    }
}

// ========================================
// SAVE FUNCTIONALITY
// ========================================

/**
 * ตั้งค่าปุ่มบันทึก
 */
function setupSaveButtons() {
    // หาปุ่ม "ขออนุมัติ" ทั้งหมด
    const saveButtons = document.querySelectorAll('button[class*="btn-primary"]');
    
    saveButtons.forEach(button => {
        if (button.textContent.includes('ขออนุมัติ')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                saveProposal();
            });
        }
    });
}

/**
 * บันทึกข้อมูลคำขอ
 */
async function saveProposal() {
    try {
        if (!validateRequiredFields()) {
            return;
        }

        const proposalData = collectProposalData();

        showLoadingMessage('กำลังตรวจสอบงบประมาณ...');
        const budgetResult = await checkBudget(proposalData);

        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        if (budgetResult.status === 'error') {
            showErrorMessage('ไม่สามารถตรวจสอบงบประมาณได้', budgetResult.message);
            return;
        }

        const budgetData = budgetResult.data || {};

        if (!budgetData.is_sufficient) {
            showInsufficientBudgetModal(budgetData);
            return;
        }

        const confirmed = await showBudgetConfirmModal(budgetData);
        if (!confirmed) {
            return;
        }

        showLoadingMessage('กำลังบันทึกข้อมูล...');
        const result = await submitProposal(proposalData);

        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        if (result.success) {
            showSuccessMessage('บันทึกข้อมูลสำเร็จ', `หมายเลขคำขอ: ${result.proposal_number}`);
        } else {
            showErrorMessage('เกิดข้อผิดพลาดในการบันทึกข้อมูล', result.message);
        }
    } catch (error) {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        showErrorMessage('เกิดข้อผิดพลาดในการบันทึกข้อมูล', error.message);
    }
}

/**
 * เรียก API ตรวจสอบงบประมาณ
 */
async function checkBudget(proposalData) {
    const apiUrl = getApiBasePath() + '/controllers/proposal/budget_check_controller.php?action=check';

    const payload = {
        start_date: proposalData.start_date,
        end_date: proposalData.end_date,
        cost_center_id: proposalData.cost_center_id || '',
        funds_center_id: proposalData.funds_center_id || '',
        liability_id: proposalData.liability_id || '',
        fund_id: proposalData.fund_id || '',
        scope_id: proposalData.scope_id || '',
        total_amount: proposalData.total_amount || 0
    };

    console.group('[Budget Check] ส่ง request');
    console.log('URL:', apiUrl);
    console.log('Payload → Backend:', JSON.parse(JSON.stringify(payload)));
    console.log('Proposal Data (full):', JSON.parse(JSON.stringify(proposalData)));
    console.groupEnd();

    const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    let result;
    try {
        result = await response.json();
    } catch (parseError) {
        throw new Error(`ไม่สามารถอ่านผลตอบกลับจากระบบได้ (HTTP ${response.status})`);
    }

    console.group('[Budget Check] ได้ response กลับมา');
    console.log('HTTP Status:', response.status, response.statusText);
    console.log('Result ← Backend:', JSON.parse(JSON.stringify(result)));

    if (result.debug) {
        console.group('Debug (Backend → SAP)');
        console.log('Frontend Payload:', result.debug.frontend_payload);
        console.log('SAP Params (resolve code แล้ว):', result.debug.sap_params);
        if (result.debug.sap_url) {
            console.log('SAP URL:', result.debug.sap_url);
        }
        if (result.debug.sap_method) {
            console.log('SAP Method:', result.debug.sap_method);
        }
        if (result.debug.http_code !== undefined) {
            console.log('SAP HTTP Code:', result.debug.http_code);
        }
        if (result.debug.from_cache !== undefined) {
            console.log('From Cache:', result.debug.from_cache);
        }
        if (result.debug.raw_response) {
            console.log('SAP Raw Response:', result.debug.raw_response);
        }
        if (result.debug.sap_normalized) {
            console.log('SAP Normalized:', result.debug.sap_normalized);
        }
        console.groupEnd();
    }
    console.groupEnd();

    if (result && (result.status === 'success' || result.status === 'error')) {
        return result;
    }

    if (!response.ok) {
        throw new Error(result?.message || `HTTP error! status: ${response.status}`);
    }

    return result;
}

/**
 * Modal ยืนยันเมื่องบเพียงพอ
 */
function showBudgetConfirmModal(budgetData) {
    const balance = formatCurrency(budgetData.balance);
    const totalAmount = formatCurrency(budgetData.total_amount);
    const remaining = formatCurrency(budgetData.remaining_after_deduct);

    const html = `
        <div class="text-start">
            <p class="mb-2">งบคงเหลือปัจจุบัน: <strong>${balance}</strong> บาท</p>
            <p class="mb-2">ยอดใบขอเสนอ: <strong>${totalAmount}</strong> บาท</p>
            <p class="mb-0">งบหักลบ: <strong>${remaining}</strong> บาท</p>
        </div>
    `;

    if (typeof Swal === 'undefined') {
        return Promise.resolve(window.confirm('งบเพียงพอ ต้องการขออนุมัติหรือไม่?'));
    }

    return Swal.fire({
        icon: 'info',
        title: 'ตรวจสอบงบประมาณ',
        html: html,
        showCancelButton: true,
        confirmButtonText: 'ยืนยันขออนุมัติ',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => result.isConfirmed);
}

/**
 * Modal เมื่องบไม่เพียงพอ
 */
function showInsufficientBudgetModal(budgetData) {
    const balance = formatCurrency(budgetData.balance);
    const totalAmount = formatCurrency(budgetData.total_amount);
    const overAmount = formatCurrency(budgetData.over_amount || (budgetData.total_amount - budgetData.balance));

    const html = `
        <div class="text-start">
            <p class="mb-2">งบคงเหลือไม่เพียงพอสำหรับการขออนุมัติ</p>
            <p class="mb-2">งบคงเหลือ: <strong>${balance}</strong> บาท</p>
            <p class="mb-2">ยอดใบขอเสนอ: <strong>${totalAmount}</strong> บาท</p>
            <p class="mb-0">เกินงบ: <strong>${overAmount}</strong> บาท</p>
        </div>
    `;

    if (typeof Swal === 'undefined') {
        alert('งบคงเหลือไม่เพียงพอ');
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'ไม่สามารถขออนุมัติได้',
        html: html,
        confirmButtonText: 'ตกลง'
    });
}

/**
 * ตรวจสอบข้อมูลที่จำเป็น
 */
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
    
    // ตรวจสอบประเภทธุรกิจในส่วนที่แสดงอยู่ (ถูกซ่อนไว้ชั่วคราว)
    // const accountCategory = document.getElementById('account-category-select').value;
    // let businessTypeId = '';
    // 
    // if (accountCategory === 'A') {
    //     const businessTypeSelect = document.getElementById('business-type-select-asset');
    //     if (businessTypeSelect) businessTypeId = businessTypeSelect.value;
    // } else if (accountCategory === 'K') {
    //     const businessTypeSelect = document.getElementById('business-type-select-cost-center');
    //     if (businessTypeSelect) businessTypeId = businessTypeSelect.value;
    // } else {
    //     const businessTypeSelect = document.getElementById('business-type-select');
    //     if (businessTypeSelect) businessTypeId = businessTypeSelect.value;
    // }
    // 
    // if (!businessTypeId) {
    //     showErrorMessage('กรุณากรอกข้อมูลที่จำเป็น', 'กรุณาเลือกประเภทธุรกิจ');
    //     return false;
    // }
    
    // ตรวจสอบข้อมูลในส่วนที่แสดงอยู่
    const accountCategory = document.getElementById('account-category-select').value;
    if (!validateSectionData(accountCategory)) {
        return false;
    }
    
    return true;
}

/**
 * ตรวจสอบข้อมูลในส่วนที่แสดงอยู่
 */
function validateSectionData(accountCategory) {
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
    
    if (!sectionId) return true;
    
    const section = document.getElementById(sectionId);
    if (!section) return true;
    
    // ตรวจสอบว่าส่วนนี้แสดงอยู่หรือไม่
    if (section.style.display === 'none') {
        return true; // ไม่ต้องตรวจสอบถ้าส่วนนี้ซ่อนอยู่
    }
    
    // ตรวจสอบว่ามีรายการในตารางหรือไม่
    const rows = section.querySelectorAll('tbody tr');
    if (rows.length === 0) {
        showErrorMessage('กรุณาเพิ่มรายการในตาราง', 'ต้องมีรายการอย่างน้อย 1 รายการ');
        return false;
    }
    
    // กำหนดตำแหน่งของ column ตาม section
    let quantityColumnIndex, priceColumnIndex;
    if (accountCategory === 'K') {
        // ส่วน K ศูนย์ต้นทุน: ปริมาณที่ column 3, ราคาที่ column 6
        quantityColumnIndex = 3;
        priceColumnIndex = 6;
    } else {
        // ส่วนอื่นๆ: ปริมาณที่ column 3, ราคาที่ column 6
        quantityColumnIndex = 3;
        priceColumnIndex = 6;
    }
    
    // ตรวจสอบข้อมูลในแต่ละแถว
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const quantityCell = row.querySelector(`td:nth-child(${quantityColumnIndex})`);
        const priceCell = row.querySelector(`td:nth-child(${priceColumnIndex})`);
        
        if (quantityCell && priceCell) {
            const quantity = parseFloat(quantityCell.textContent.replace(/,/g, '')) || 0;
            const price = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
            
            if (quantity <= 0) {
                showErrorMessage('กรุณากรอกปริมาณที่ถูกต้อง', `แถวที่ ${i + 1}: ปริมาณต้องมากกว่า 0`);
                quantityCell.focus();
                return false;
            }
            
            if (price <= 0) {
                showErrorMessage('กรุณากรอกราคาที่ถูกต้อง', `แถวที่ ${i + 1}: ราคาต้องมากกว่า 0`);
                priceCell.focus();
                return false;
            }
        }
    }
    
    return true;
}

/**
 * รวบรวมข้อมูลคำขอ
 */
function collectProposalData() {
    // ข้อมูลหลัก
    const formData = {
        form_type_id: document.getElementById('form-type-select').value,
        account_category: document.getElementById('account-category-select').value,
        start_date: document.querySelector('input[placeholder*="วันเริ่มต้น"]').value,
        end_date: document.querySelector('input[placeholder*="วันสิ้นสุด"]').value,
        department_code: document.getElementById('department-code').value,
        header_note: document.querySelector('textarea[placeholder*="บันทึกส่วนหัว"]').value,
        description: document.querySelector('textarea[placeholder*="คำอธิบาย"]').value,
        factory_id: document.getElementById('factory-select').value,
        warehouse_id: document.getElementById('warehouse-select').value,
        purchase_group_id: document.getElementById('purchase-group-select').value,
        created_by: getCurrentUser()
    };
    
    // ข้อมูลจากส่วนที่แสดงอยู่
    const accountCategory = formData.account_category;
    
    // เก็บข้อมูลประเภทธุรกิจจากส่วนที่แสดงอยู่ (ถูกซ่อนไว้ชั่วคราว - ส่ง null)
    let businessTypeId = null;
    if (accountCategory === 'A') {
        const businessTypeSelect = document.getElementById('business-type-select-asset');
        if (businessTypeSelect && businessTypeSelect.value) businessTypeId = businessTypeSelect.value;
    } else if (accountCategory === 'K') {
        const businessTypeSelect = document.getElementById('business-type-select-cost-center');
        if (businessTypeSelect && businessTypeSelect.value) businessTypeId = businessTypeSelect.value;
    } else {
        const businessTypeSelect = document.getElementById('business-type-select');
        if (businessTypeSelect && businessTypeSelect.value) businessTypeId = businessTypeSelect.value;
    }
    const sectionData = collectSectionData(accountCategory);
    
    // คำนวณยอดรวมจากส่วนที่แสดงอยู่
    const totals = calculateTotals(accountCategory);
    
    // รวมข้อมูล (ตั้งค่า business_type_id หลังรวม sectionData เพื่อให้แน่ใจว่าใช้ค่าจาก collectAllFormData)
    return {
        ...formData,
        ...sectionData,
        ...totals,
        business_type_id: businessTypeId  // ตั้งค่าหลังสุดเพื่อ override ค่าจาก sectionData (ถ้ามี)
    };
}

/**
 * คำนวณยอดรวมจากส่วนที่แสดงอยู่
 */
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
    
    // ตรวจสอบว่าส่วนนี้แสดงอยู่หรือไม่
    if (section.style.display === 'none') {
        return { total_quantity: 0, total_amount: 0 };
    }
    
    // คำนวณยอดรวมจากตาราง
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

/**
 * รวบรวมข้อมูลจากส่วนที่แสดงอยู่
 */
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
    
    // รวบรวมข้อมูลจากฟอร์ม
    const formData = collectFormData(section);
    
    // รวบรวมข้อมูลจากตาราง
    const items = collectTableItems(section);
    
    return {
        ...formData,
        items: items
    };
}

/**
 * รวบรวมข้อมูลจากฟอร์ม
 */
function collectFormData(section) {
    const formData = {};
    
    // หน่วยเงิน
    const monetaryUnitEn = section.querySelector('.monetary-unit-select');
    const monetaryUnitTh = section.querySelector('.monetary-unit-th-select');
    if (monetaryUnitEn) formData.monetary_unit_en = monetaryUnitEn.value;
    if (monetaryUnitTh) formData.monetary_unit_th = monetaryUnitTh.value;
    
    // ภาษี
    const taxSelect = section.querySelector('[id*="tax-select"]');
    if (taxSelect) formData.tax_id = taxSelect.value || null;
    
    // วันที่ส่งมอบ
    const deliveryDate = section.querySelector('input[placeholder*="วันที่ส่งมอบ"]');
    if (deliveryDate) formData.delivery_date = deliveryDate.value;
    
    // บัญชี G/L (TYPE N และ TYPE A สามารถเป็นค่าว่างได้)
    const glSelect = section.querySelector('[id*="gl-account-select"]');
    if (glSelect) {
        // ถ้าไม่มีค่า ให้ส่ง null
        formData.gl_account_id = glSelect.value || null;
    }
    
    // ประเภทธุรกิจ (ถูกซ่อนไว้ชั่วคราว - ไม่ต้องส่งจาก section เพราะจะส่งจาก collectAllFormData)
    // const businessTypeSelect = section.querySelector('[id*="business-type-select"]');
    // if (businessTypeSelect) formData.business_type_id = businessTypeSelect.value;
    
    // ศูนย์ต้นทุน
    const costCenterSelect = section.querySelector('[id*="cost-center-select"]');
    if (costCenterSelect) formData.cost_center_id = costCenterSelect.value;
    
    // เงินทุน
    const fundSelect = section.querySelector('[id*="fund-select"]');
    if (fundSelect) formData.fund_id = fundSelect.value;
    
    // ขอบเขตตามหน้าที่
    const scopeSelect = section.querySelector('[id*="scope-select"]');
    if (scopeSelect) formData.scope_id = scopeSelect.value;
    
    // Funds Center
    const fundsCenterSelect = section.querySelector('[id*="funds-center-select"]');
    if (fundsCenterSelect) formData.funds_center_id = fundsCenterSelect.value;
    
    // ภาระผูกพัน
    const liabilitySelect = section.querySelector('[id*="liability-select"]');
    if (liabilitySelect) formData.liability_id = liabilitySelect.value;
    
    // ข้อความต่างๆ
    const purchaseText = section.querySelector('textarea[placeholder*="ข้อความจัดซื้อจัดจ้าง"]');
    if (purchaseText) formData.purchase_text = purchaseText.value;
    
    const itemNote = section.querySelector('textarea[placeholder*="หมายเหตุรายการ"]');
    if (itemNote) formData.item_note = itemNote.value;
    
    const deliveryText = section.querySelector('textarea[placeholder*="ข้อความการส่งมอบ"]');
    if (deliveryText) formData.delivery_text = deliveryText.value;
    
    const materialOrderText = section.querySelector('textarea[placeholder*="ข้อความในสั่งซื้อวัสดุ"]');
    if (materialOrderText) formData.material_order_text = materialOrderText.value;
    
    const quotationText = section.querySelector('textarea[placeholder*="ข้อความใบเสนอราคา"]');
    if (quotationText) formData.quotation_text = quotationText.value;
    
    return formData;
}

/**
 * รวบรวมข้อมูลจากตาราง
 */
function collectTableItems(section) {
    const items = [];
    const rows = section.querySelectorAll('tbody tr');
    
    // ตรวจสอบว่าเป็นส่วนไหน
    const isCostCenterSection = section.id === 'cost-center-section';
    const isAssetSection = section.id === 'asset-section';
    
    rows.forEach((row, index) => {
        const item = {};
        
        // วัสดุ/รายการ
        if (isCostCenterSection) {
            // ส่วน K: ข้อความสั้น = รายการค่าใช้จ่าย (column 2)
            const shortTextCell = row.querySelector('td:nth-child(2)');
            if (shortTextCell) {
                item.short_text = shortTextCell.textContent;
                item.material_name = shortTextCell.textContent;
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
            const quantityText = quantityCell.textContent.replace(/,/g, '');
            item.quantity = parseFloat(quantityText) || 0;
        }
        
        // หน่วย
        const unitSelect = row.querySelector('.unit-select');
        if (unitSelect) item.unit_code = unitSelect.value;
        
        // กลุ่มวัสดุ (ตรวจสอบทั้ง asset-category-select, mrd-group-select, และ expen-pr-select)
        if (isCostCenterSection) {
            // ส่วน K ศูนย์ต้นทุน: ใช้ expen-pr-select
            const expenPrSelect = row.querySelector('.expen-pr-select');
            if (expenPrSelect) {
                item.expen_pr_id = expenPrSelect.value;
                item.group_name = expenPrSelect.value;
            }
        } else if (isAssetSection) {
            // ส่วน A สินทรัพย์: ใช้ asset-category-select
            const assetCategorySelect = row.querySelector('.asset-category-select');
            if (assetCategorySelect) {
                item.asset_category_id = assetCategorySelect.value;
                item.group_name = assetCategorySelect.value;
            }
        } else {
            // ส่วนอื่นๆ (ไม่เลือก): ใช้ mrd-group-select
            const mrdGroupSelect = row.querySelector('.mrd-group-select');
            if (mrdGroupSelect) {
                item.mrd_group_id = mrdGroupSelect.value;
                item.group_name = mrdGroupSelect.value;
            }
        }
        
        // ราคา
        const priceCellColumn = isCostCenterSection ? 6 : 6;
        const priceCell = row.querySelector(`td:nth-child(${priceCellColumn})`);
        if (priceCell) item.unit_price = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
        
        // ราคาทั้งหมด
        const totalPriceCellColumn = isCostCenterSection ? 7 : 7;
        const totalPriceCell = row.querySelector(`td:nth-child(${totalPriceCellColumn})`);
        if (totalPriceCell) {
            const totalPriceText = totalPriceCell.textContent.replace(/,/g, '');
            item.total_price = parseFloat(totalPriceText) || 0;
        }
        
        // เพิ่มเฉพาะรายการที่มีข้อมูล
        if (item.material_code || item.asset_code || item.quantity > 0 || item.unit_price > 0) {
            items.push(item);
        }
    });
    
    return items;
}

/**
 * ส่งข้อมูลไปยัง API
 */
async function submitProposal(data) {
    const apiUrl = getApiBasePath() + '/controllers/proposal/request_proposal_controller.php?action=create';
    
    const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.status === 'success') {
        return {
            success: true,
            proposal_id: result.data.proposal_id,
            proposal_number: result.data.proposal_number
        };
    } else {
        throw new Error(result.message || 'Unknown error');
    }
}

/**
 * ดึงข้อมูลผู้ใช้ปัจจุบัน
 */
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

/**
 * แสดงข้อความ loading
 */
function showLoadingMessage(message) {
    if (typeof Swal !== 'undefined') {
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

/**
 * แสดงข้อความสำเร็จ
 */
function showSuccessMessage(title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            confirmButtonText: 'ตกลง'
        }).then(() => {
            // กลับไปหน้า list หลังจากบันทึกสำเร็จ
            window.location.href = 'request-proposal-list.php';
        });
    } else {
        alert(`${title}: ${message}`);
        window.location.href = 'request-proposal-list.php';
    }
}
