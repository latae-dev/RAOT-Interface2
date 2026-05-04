/**
 * Department List Management
 * ไฟล์ JavaScript สำหรับจัดการข้อมูลหน่วยงาน
 * 
 * วันที่: 2025-01-27
 */

// Base URL สำหรับ API
const baseUrl = window.location.origin + window.location.pathname.replace('/pages/department-list.php', '');

// Global variables
let departmentsTable = null;
let currentPage = 1;
let pageSize = 10;
let totalRecords = 0;
let filters = {
    depart_code: '',
    depart_name: '',
    depart_type_id: '',
    is_active: ''
};

// Department types mapping (will be loaded from API)
let departmentTypes = {};

/**
 * Helper function to safely destroy Choices.js instance
 */
function destroyChoicesInstance(selectElement) {
    if (!selectElement) {
        return;
    }
    
    // Destroy instance if exists
    if (selectElement.choicesInstance) {
        try {
            // Check if instance has valid element property before destroy
            if (selectElement.choicesInstance.element && 
                typeof selectElement.choicesInstance.destroy === 'function') {
                selectElement.choicesInstance.destroy();
            }
        } catch (destroyError) {
            // Ignore destroy errors - instance may already be destroyed
            console.warn('Error destroying Choices instance (ignored):', destroyError);
        }
        selectElement.choicesInstance = null;
    }
    
    // Remove Choices.js wrapper element if exists (check both nextSibling and parent)
    let wrapper = selectElement.nextElementSibling;
    if (wrapper && wrapper.classList.contains('choices')) {
        wrapper.remove();
    } else {
        // Sometimes wrapper is the parent
        const parent = selectElement.parentElement;
        if (parent && parent.classList.contains('choices')) {
            // Find the actual select element and restore it
            const actualSelect = parent.querySelector('select');
            if (actualSelect) {
                parent.replaceWith(actualSelect);
            }
        }
    }
    
    // Remove Choices.js classes and attributes
    selectElement.classList.remove('choices__input', 'choices__input--hidden');
    selectElement.removeAttribute('data-choice');
    
    // Ensure element is visible and enabled
    selectElement.style.display = '';
    selectElement.removeAttribute('tabindex');
}

/**
 * Helper function to safely initialize Choices.js instance
 */
function initializeChoicesInstance(selectElement, options = {}) {
    if (!selectElement || typeof Choices === 'undefined') {
        return null;
    }
    
    // Always destroy existing instance first to ensure clean state
    destroyChoicesInstance(selectElement);
    
    // Double check - remove any remaining Choices.js artifacts
    if (selectElement.hasAttribute('data-choice')) {
        selectElement.removeAttribute('data-choice');
    }
    selectElement.classList.remove('choices__input', 'choices__input--hidden');
    
    // Wait a tiny bit to ensure DOM is clean (if needed)
    // But usually not necessary, so we proceed immediately
    
    // Create new instance
    try {
        const defaultOptions = {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        };
        
        // Use Object.assign for better browser compatibility
        const mergedOptions = Object.assign({}, defaultOptions, options);
        
        selectElement.choicesInstance = new Choices(selectElement, mergedOptions);
        
        return selectElement.choicesInstance;
    } catch (e) {
        // If initialization fails, it might be because element is already initialized
        // Try to get existing instance
        if (selectElement.choicesInstance) {
            return selectElement.choicesInstance;
        }
        console.error('Error initializing Choices.js:', e);
        return null;
    }
}

/**
 * Initialize page
 */
$(document).ready(function() {
    console.log('Department List Page Initialized');
    
    // Scroll to top on page load
    window.scrollTo(0, 0);
    
    // Load department types first
    loadDepartmentTypes().then(() => {
        // Then load initial data
        loadDepartments();
    });
    
    // Event listeners
    $('#filter-search-btn').on('click', function() {
        applyFilters();
    });
    
    $('#filter-reset-btn').on('click', function() {
        resetFilters();
    });
    
    // Create/Edit modal events
    $('#btn-create-department').on('click', function() {
        showCreateModal();
    });
    
    $('#btn-save-department').on('click', function() {
        saveDepartment();
    });
    
    // Modal close event - reset form
    $('#department-modal').on('hidden.bs.modal', function() {
        resetDepartmentForm();
    });
    
    // Department type change - update parent options
    $('#department-type').on('change', function() {
        const departTypeId = $(this).val();
        const parentField = $('#department-parent').closest('.col-md-6');
        
        if (!departTypeId) {
            parentField.hide();
            return;
        }
        
        // Check if this type can have parent (level > 1)
        const currentType = departmentTypes[departTypeId];
        if (currentType && currentType.level === 1) {
            // HQ (level 1) cannot have parent
            parentField.hide();
            parentField.find('label').html('หน่วยงานแม่');
            $('#department-parent').val('');
            $('#department-parent').removeClass('is-invalid');
            $('#department-parent').siblings('.invalid-feedback').text('');
            
            // Clear Choices
            const parentSelectElement = document.getElementById('department-parent');
            if (parentSelectElement && typeof Choices !== 'undefined') {
                try {
                    if (parentSelectElement.choicesInstance) {
                        parentSelectElement.choicesInstance.setChoiceByValue('');
                    }
                } catch (e) {
                    // If Choices not initialized, skip
                }
            }
        } else {
            // Other types need parent - show field first
            parentField.show();
            parentField.find('label').html('หน่วยงานแม่ <span class="text-danger">*</span>');
            // Load parent options (async)
            updateParentOptions(departTypeId, null).catch(error => {
                console.error('Error updating parent options:', error);
            });
        }
    });
    
    // Form validation on input
    $('#department-code, #department-name, #department-type').on('blur', function() {
        validateField($(this));
    });
    
    // Enter key to submit
    $('#department-form').on('submit', function(e) {
        e.preventDefault();
        saveDepartment();
    });
});

/**
 * Load department types from API
 */
function loadDepartmentTypes() {
    return fetch(`${baseUrl}/controllers/user/depart_controller.php?action=types`)
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success' && response.data) {
                // Build departmentTypes object
                departmentTypes = {};
                response.data.forEach(type => {
                    departmentTypes[type.id] = {
                        name: type.depart_type_name,
                        code: type.depart_type_name.toUpperCase(),
                        level: type.depart_type_level
                    };
                });
                
                // Populate department type dropdown
                const typeSelect = $('#department-type');
                typeSelect.empty();
                typeSelect.append('<option value="">เลือกประเภท</option>');
                response.data.forEach(type => {
                    typeSelect.append(`<option value="${type.id}">${escapeHtml(type.depart_type_name)} (Level ${type.depart_type_level})</option>`);
                });
                
                // Update Choices.js if initialized
                const typeSelectElement = document.getElementById('department-type');
                if (typeSelectElement) {
                    initializeChoicesInstance(typeSelectElement, {
                        searchEnabled: false,
                        itemSelectText: '',
                        shouldSort: false
                    });
                }
                
                console.log('Department types loaded:', departmentTypes);
            } else {
                console.error('Failed to load department types');
            }
        })
        .catch(error => {
            console.error('Error loading department types:', error);
        });
}

/**
 * Load departments from API
 */
function loadDepartments() {
    console.log('Loading departments...');
    
    // Build API URL with filters
    let apiUrl = `${baseUrl}/controllers/user/depart_controller.php?action=list`;
    
    // Add filters to URL
    const filterParams = [];
    if (filters.depart_code) filterParams.push(`depart_code=${encodeURIComponent(filters.depart_code)}`);
    if (filters.depart_name) filterParams.push(`depart_name=${encodeURIComponent(filters.depart_name)}`);
    if (filters.depart_type_id) filterParams.push(`depart_type_id=${encodeURIComponent(filters.depart_type_id)}`);
    if (filters.is_active !== '') filterParams.push(`is_active=${encodeURIComponent(filters.is_active)}`);
    
    if (filterParams.length > 0) {
        apiUrl += '&' + filterParams.join('&');
    }
    
    // Add pagination
    apiUrl += `&page=${currentPage}&page_size=${pageSize}`;
    
    // Show loading
    $('#departments-tbody').html(`
        <tr>
            <td colspan="10" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="mt-2">กำลังโหลดข้อมูล...</p>
            </td>
        </tr>
    `);
    
    // Fetch data
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(response => {
            console.log('Departments loaded:', response);
            
            if (response.status === 'error') {
                throw new Error(response.message || 'เกิดข้อผิดพลาด');
            }
            
            // Handle response format from API
            if (response.data && Array.isArray(response.data)) {
                renderDepartmentsTable({
                    data: response.data,
                    total: response.pagination ? response.pagination.total : response.data.length,
                    page: response.pagination ? response.pagination.page : 1,
                    page_size: response.pagination ? response.pagination.page_size : pageSize,
                    total_pages: response.pagination ? response.pagination.total_pages : 1
                });
            } else {
                // Fallback for old format
                renderDepartmentsTable(response);
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
            $('#departments-tbody').html(`
                <tr>
                    <td colspan="10" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล: ${error.message}
                    </td>
                </tr>
            `);
            $('#pagination-info').text('เกิดข้อผิดพลาด');
            $('#pagination-controls').empty();
        });
}

/**
 * Render departments table
 */
function renderDepartmentsTable(response) {
    const tbody = $('#departments-tbody');
    tbody.empty();
    
    // Handle different response formats
    let departments = [];
    let total = 0;
    
    if (response && response.data && Array.isArray(response.data)) {
        departments = response.data;
        total = response.total || (response.pagination && response.pagination.total) || departments.length;
    } else if (Array.isArray(response)) {
        // Fallback: if response is directly an array
        departments = response;
        total = response.length;
    }
    
    if (departments.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="10" class="text-center">
                    <p class="text-muted">ไม่พบข้อมูล</p>
                </td>
            </tr>
        `);
        updatePagination(0);
        return;
    }
    
    totalRecords = total;
    
    departments.forEach((dept, index) => {
        const rowNumber = (currentPage - 1) * pageSize + index + 1;
        // Convert user_count to number (PostgreSQL may return as string)
        const userCount = parseInt(dept.user_count || 0, 10);
        const deptId = dept.id;
        
        // Debug log
        if (index === 0) {
            console.log('Sample department data:', {
                id: deptId,
                user_count: dept.user_count,
                userCount_parsed: userCount,
                type: typeof dept.user_count
            });
        }
        
        const row = `
            <tr>
                <td class="text-center">${rowNumber}</td>
                <td>${escapeHtml(dept.depart_code || '')}</td>
                <td>${escapeHtml(dept.depart_name || '')}</td>
                <td class="text-center">
                    ${formatDepartType(dept.depart_type_id)}
                </td>
                <td>${formatParentInfo(dept)}</td>
                <td class="text-center">
                    ${dept.is_active !== false ? 
                        '<span class="badge bg-success-transparent">ใช้งาน</span>' : 
                        '<span class="badge bg-danger-transparent">ไม่ใช้งาน</span>'}
                </td>
                <td class="text-center">
                    ${userCount > 0 ? 
                        `<a href="javascript:void(0);" class="text-primary fw-semibold view-users-link" data-depart-id="${deptId}" title="คลิกเพื่อดูรายชื่อ Users" style="cursor: pointer; text-decoration: underline;" onclick="viewUsers(${deptId}); return false;">
                            ${userCount}
                        </a>` : 
                        '<span class="text-muted">0</span>'}
                </td>
                <td class="text-center">${formatDate(dept.created_at)}</td>
                <td class="text-center">${formatDate(dept.updated_at)}</td>
                <td class="text-center">
                    <div class="hstack gap-2 fs-15 justify-content-center">
                        <button class="btn btn-icon btn-sm rounded-pill" style="background-color: rgba(138, 43, 226, 0.1); color: #8a2be2; border-color: rgba(138, 43, 226, 0.2);" onmouseover="this.style.backgroundColor='rgba(138, 43, 226, 0.2)'" onmouseout="this.style.backgroundColor='rgba(138, 43, 226, 0.1)'" onclick="viewHierarchy(${deptId})" title="ดู Hierarchy">
                            <i class="ri-node-tree"></i>
                        </button>
                        <button class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="viewDepartment(${deptId})" title="ดูรายละเอียด">
                            <i class="ri-eye-line"></i>
                        </button>
                        <button class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick="editDepartment(${deptId})" title="แก้ไข">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deleteDepartment(${deptId})" title="ลบ">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Add event listener for view users links using event delegation
    $(document).off('click', '.view-users-link').on('click', '.view-users-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const departId = $(this).data('depart-id') || $(this).attr('data-depart-id');
        console.log('View users clicked for department ID:', departId);
        if (departId) {
            viewUsers(parseInt(departId, 10));
        } else {
            console.error('Department ID not found');
            alert('ไม่พบ ID ของหน่วยงาน');
        }
    });
    
    // Update pagination
    updatePagination(total);
}

/**
 * Format department type
 */
function formatDepartType(typeId) {
    if (!typeId || !departmentTypes[typeId]) {
        return '<span class="text-muted">-</span>';
    }
    const type = departmentTypes[typeId];
    return `<span class="badge bg-info-transparent">${type.name} (${type.code})</span>`;
}

/**
 * Format parent information with hierarchy
 */
function formatParentInfo(dept) {
    if (!dept.parent_id || !dept.parent_depart_code) {
        return '<span class="text-muted">-</span>';
    }
    
    // Show parent code and name
    const parentCode = escapeHtml(dept.parent_depart_code || '');
    const parentName = escapeHtml(dept.parent_depart_name || '');
    const parentType = dept.parent_depart_type_name ? 
        `<span class="badge bg-secondary-transparent badge-sm ms-1">${dept.parent_depart_type_name.toUpperCase()}</span>` : '';
    
    return `
        <div class="d-flex flex-column">
            <span class="fw-semibold">${parentCode}</span>
            <span class="text-muted small">${parentName}</span>
            ${parentType}
        </div>
    `;
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Apply filters
 */
function applyFilters() {
    filters = {
        depart_code: $('#filter-depart-code').val().trim(),
        depart_name: $('#filter-depart-name').val().trim(),
        depart_type_id: $('#filter-depart-type').val(),
        is_active: $('#filter-is-active').val()
    };
    
    currentPage = 1;
    loadDepartments();
}

/**
 * Reset filters
 */
function resetFilters() {
    $('#filter-depart-code').val('');
    $('#filter-depart-name').val('');
    
    // Reset Choices.js selects - ใช้วิธีที่ปลอดภัยกว่า
    const filterTypeSelect = document.getElementById('filter-depart-type');
    const filterActiveSelect = document.getElementById('filter-is-active');
    
    if (filterTypeSelect) {
        filterTypeSelect.value = '';
        // Reset Choices.js if initialized
        if (typeof Choices !== 'undefined') {
            try {
                if (filterTypeSelect.choicesInstance) {
                    filterTypeSelect.choicesInstance.setChoiceByValue('');
                } else {
                    filterTypeSelect.value = '';
                }
            } catch (e) {
                // If Choices not initialized, just set value directly
                filterTypeSelect.value = '';
            }
        }
    }
    
    if (filterActiveSelect) {
        filterActiveSelect.value = '';
        // Reset Choices.js if initialized
        if (typeof Choices !== 'undefined') {
            try {
                if (filterActiveSelect.choicesInstance) {
                    filterActiveSelect.choicesInstance.setChoiceByValue('');
                } else {
                    filterActiveSelect.value = '';
                }
            } catch (e) {
                // If Choices not initialized, just set value directly
                filterActiveSelect.value = '';
            }
        }
    }
    
    filters = {
        depart_code: '',
        depart_name: '',
        depart_type_id: '',
        is_active: ''
    };
    
    currentPage = 1;
    loadDepartments();
}

/**
 * Update pagination
 */
function updatePagination(total) {
    totalRecords = total;
    const totalPages = Math.ceil(total / pageSize);
    
    // Update pagination info
    if (total === 0) {
        $('#pagination-info').text('ไม่พบข้อมูล');
    } else {
        const start = ((currentPage - 1) * pageSize) + 1;
        const end = Math.min(currentPage * pageSize, total);
        $('#pagination-info').text(`กำลังแสดง ${start} - ${end} จาก ${total} รายการ`);
    }
    
    const pagination = $('#pagination-controls');
    pagination.empty();
    
    if (totalPages <= 1) {
        return;
    }
    
    // Previous button
    pagination.append(`
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0);" onclick="goToPage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `);
    
    // Page numbers
    let lastPage = 0;
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(${i})">${i}</a>
                </li>
            `);
            lastPage = i;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            if (i > lastPage + 1) {
                pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }
    }
    
    // Next button
    pagination.append(`
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0);" onclick="goToPage(${currentPage + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `);
}

/**
 * Go to page
 */
function goToPage(page) {
    const totalPages = Math.ceil(totalRecords / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadDepartments();
}

/**
 * Show create modal
 */
async function showCreateModal() {
    console.log('showCreateModal called');
    
    // Ensure department types are loaded first
    if (Object.keys(departmentTypes).length === 0) {
        await loadDepartmentTypes();
    }
    
    $('#department-modal-label').text('สร้างหน่วยงานใหม่');
    $('#department-id').val('');
    
    // Reset form fields
    $('#department-code').val('');
    $('#department-name').val('');
    $('#department-is-active').prop('checked', true);
    
    // Reset type select
    const typeSelect = $('#department-type');
    typeSelect.val('');
    const typeSelectElement = document.getElementById('department-type');
    if (typeSelectElement && typeSelectElement.choicesInstance) {
        try {
            typeSelectElement.choicesInstance.setChoiceByValue('');
        } catch (e) {
            console.warn('Error resetting type select:', e);
        }
    }
    
    // Reset parent options
    const parentSelect = $('#department-parent');
    const parentField = parentSelect.closest('.col-md-6');
    parentSelect.empty();
    parentSelect.append('<option value="">ไม่มี (เป็นหน่วยงานระดับสูงสุด)</option>');
    
    // Hide parent field initially (will show when type is selected)
    parentField.hide();
    parentField.find('label').html('หน่วยงานแม่');
    
    // Reset Choices.js for parent select
    const parentSelectElement = document.getElementById('department-parent');
    if (parentSelectElement) {
        initializeChoicesInstance(parentSelectElement, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        });
    }
    
    // Remove validation classes
    $('#department-code, #department-name, #department-type, #department-parent').removeClass('is-invalid is-valid');
    $('.invalid-feedback').text('');
    
    $('#department-modal').modal('show');
}

/**
 * Edit department
 */
async function editDepartment(id) {
    console.log('Editing department:', id);
    
    // Ensure department types are loaded first
    if (Object.keys(departmentTypes).length === 0) {
        await loadDepartmentTypes();
    }
    
    // Fetch department data
    try {
        const response = await fetch(`${baseUrl}/controllers/user/depart_controller.php?action=get&id=${id}`);
        const result = await response.json();
        
        console.log('Edit department response:', result);
        
        if (result.status === 'error') {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: result.message || 'ไม่พบข้อมูลหน่วยงาน',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
            return;
        }
        
        const data = result.data || result;
        console.log('Department data:', data);
        
        if (data && data.id) {
            // Ensure department types are loaded first
            if (Object.keys(departmentTypes).length === 0) {
                await loadDepartmentTypes();
            }
            
            $('#department-modal-label').text('แก้ไขหน่วยงาน');
            $('#department-id').val(data.id);
            $('#department-code').val(data.depart_code || '');
            $('#department-name').val(data.depart_name || '');
            $('#department-is-active').prop('checked', data.is_active !== false);
            
            // Set department type
            const typeSelect = $('#department-type');
            const typeSelectElement = document.getElementById('department-type');
            const departTypeId = data.depart_type_id || '';
            
            typeSelect.val(departTypeId);
            if (typeSelectElement && typeof Choices !== 'undefined') {
                try {
                    if (typeSelectElement.choicesInstance) {
                        typeSelectElement.choicesInstance.setChoiceByValue(departTypeId.toString());
                    }
                } catch (e) {
                    console.warn('Error setting type select value:', e);
                }
            }
            
            // Update parent field visibility and load parent options
            const parentField = $('#department-parent').closest('.col-md-6');
            const currentType = departmentTypes[departTypeId];
            console.log('Current type:', currentType, 'departTypeId:', departTypeId);
            
            if (currentType && currentType.level === 1) {
                // HQ (level 1) cannot have parent
                parentField.hide();
                parentField.find('label').html('หน่วยงานแม่');
                $('#department-parent').val('');
            } else {
                // Other types need parent - show field and load options
                parentField.show();
                parentField.find('label').html('หน่วยงานแม่ <span class="text-danger">*</span>');
                // Update parent options and set value
                await updateParentOptions(departTypeId, data.parent_id);
            }
            
            // Remove validation classes
            $('#department-code, #department-name, #department-type, #department-parent').removeClass('is-invalid is-valid');
            $('.invalid-feedback').text('');
            
            $('#department-modal').modal('show');
        } else {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่พบข้อมูลหน่วยงาน',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    } catch (error) {
        console.error('Error loading department:', error);
        Swal.fire({
            title: 'เกิดข้อผิดพลาด',
            text: 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
            icon: 'error',
            confirmButtonText: 'ตกลง'
        });
    }
}

/**
 * Update parent options based on department type
 */
async function updateParentOptions(departTypeId, selectedParentId = null) {
    console.log('updateParentOptions called:', { departTypeId, selectedParentId, departmentTypes });
    
    const parentSelect = $('#department-parent');
    const parentSelectElement = document.getElementById('department-parent');
    
    if (!parentSelectElement) {
        console.error('Parent select element not found!');
        return;
    }
    
    // Show loading state
    parentSelect.prop('disabled', true);
    parentSelect.empty();
    parentSelect.append('<option value="">กำลังโหลด...</option>');
    
    // Show parent field immediately
    const parentField = parentSelect.closest('.col-md-6');
    parentField.show();
    parentField.find('label').html('หน่วยงานแม่ <span class="text-danger">*</span>');
    
    if (!departTypeId) {
        parentSelect.empty();
        parentSelect.append('<option value="">ไม่มี (เป็นหน่วยงานระดับสูงสุด)</option>');
        parentSelect.prop('disabled', false);
        
        const choicesInstance = initializeChoicesInstance(parentSelectElement, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        });
        if (choicesInstance) {
            choicesInstance.setChoiceByValue('');
        }
        return;
    }
    
    // Determine parent type based on current type
    let parentTypeId = null;
    const currentType = departmentTypes[departTypeId];
    console.log('Current type:', currentType);
    
    if (currentType) {
        // Find parent type (level - 1)
        const parentLevel = currentType.level - 1;
        console.log('Looking for parent level:', parentLevel);
        for (const [typeId, type] of Object.entries(departmentTypes)) {
            if (type.level === parentLevel) {
                parentTypeId = parseInt(typeId);
                console.log('Found parent type:', parentTypeId, type);
                break;
            }
        }
    }
    
    // Fallback to hardcoded values if departmentTypes not loaded
    if (!parentTypeId) {
        console.log('Using fallback values');
        if (departTypeId == 2) parentTypeId = 1; // Area -> HQ
        else if (departTypeId == 3) parentTypeId = 2; // Province -> Area
        else if (departTypeId == 4) parentTypeId = 3; // Branch -> Province
    }
    
    console.log('Parent type ID:', parentTypeId);
    
    if (!parentTypeId) {
        parentSelect.empty();
        parentSelect.append('<option value="">ไม่มี (เป็นหน่วยงานระดับสูงสุด)</option>');
        parentSelect.prop('disabled', false);
        
        const choicesInstance = initializeChoicesInstance(parentSelectElement, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        });
        if (choicesInstance) {
            choicesInstance.setChoiceByValue('');
        } else {
            parentSelect.val('');
        }
        return;
    }
    
    // Fetch parent departments
    const apiUrl = `${baseUrl}/controllers/user/depart_controller.php?action=list&depart_type_id=${parentTypeId}&is_active=true&page_size=1000`;
    console.log('Fetching parent departments from:', apiUrl);
    
    try {
        const response = await fetch(apiUrl);
        const result = await response.json();
        
        console.log('Parent departments response:', result);
        
        parentSelect.empty();
        parentSelect.append('<option value="">เลือกหน่วยงานแม่</option>');
        
        // Handle response format - API returns { status: 'success', data: [...], pagination: {...} }
        let departments = [];
        if (result.status === 'success' && result.data) {
            departments = Array.isArray(result.data) ? result.data : [];
        } else if (result.pagination && result.pagination.data) {
            departments = Array.isArray(result.pagination.data) ? result.pagination.data : [];
        } else if (Array.isArray(result)) {
            departments = result;
        } else if (result.data && Array.isArray(result.data)) {
            departments = result.data;
        }
        
        console.log('Departments found:', departments.length);
        
        if (departments.length > 0) {
            departments.forEach(dept => {
                const selected = selectedParentId && dept.id == selectedParentId ? 'selected' : '';
                parentSelect.append(`<option value="${dept.id}" ${selected}>${escapeHtml(dept.depart_code)} - ${escapeHtml(dept.depart_name)}</option>`);
            });
        } else {
            parentSelect.append('<option value="">ไม่พบข้อมูลหน่วยงานแม่</option>');
        }
        
        parentSelect.prop('disabled', false);
        
        // Ensure parent field is visible
        parentField.show();
        parentField.find('label').html('หน่วยงานแม่ <span class="text-danger">*</span>');
        
        // Update Choices.js
        const choicesInstance = initializeChoicesInstance(parentSelectElement, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        });
        
        if (choicesInstance) {
            console.log('Choices.js initialized for parent select');
            if (selectedParentId) {
                choicesInstance.setChoiceByValue(selectedParentId.toString());
            }
        } else if (selectedParentId) {
            parentSelect.val(selectedParentId.toString());
        }
    } catch (error) {
        console.error('Error loading parent departments:', error);
        parentSelect.empty();
        parentSelect.append('<option value="">เกิดข้อผิดพลาดในการโหลดข้อมูล</option>');
        parentSelect.prop('disabled', false);
        
        // Show parent field even on error
        if (parentTypeId) {
            parentField.show();
            parentField.find('label').html('หน่วยงานแม่ <span class="text-danger">*</span>');
        }
    }
}

/**
 * Validate form field
 */
function validateField($field) {
    const fieldId = $field.attr('id');
    const value = $field.val().trim();
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous validation classes
    $field.removeClass('is-invalid is-valid');
    $field.siblings('.invalid-feedback').text('');
    
    if (fieldId === 'department-code') {
        if (!value) {
            isValid = false;
            errorMessage = 'กรุณากรอกรหัสหน่วยงาน';
        } else if (value.length > 50) {
            isValid = false;
            errorMessage = 'รหัสหน่วยงานต้องไม่เกิน 50 ตัวอักษร';
        }
    } else if (fieldId === 'department-name') {
        if (!value) {
            isValid = false;
            errorMessage = 'กรุณากรอกชื่อหน่วยงาน';
        } else if (value.length > 200) {
            isValid = false;
            errorMessage = 'ชื่อหน่วยงานต้องไม่เกิน 200 ตัวอักษร';
        }
    } else if (fieldId === 'department-type') {
        if (!value) {
            isValid = false;
            errorMessage = 'กรุณาเลือกประเภทหน่วยงาน';
        }
    }
    
    if (isValid) {
        $field.addClass('is-valid');
    } else {
        $field.addClass('is-invalid');
        $field.siblings('.invalid-feedback').text(errorMessage);
    }
    
    return isValid;
}

/**
 * Validate entire form
 */
function validateForm() {
    let isValid = true;
    
    // Validate required fields
    isValid = validateField($('#department-code')) && isValid;
    isValid = validateField($('#department-name')) && isValid;
    isValid = validateField($('#department-type')) && isValid;
    
    // Additional validation for hierarchy
    const departTypeId = parseInt($('#department-type').val());
    const parentId = $('#department-parent').val();
    
    // Validate parent selection based on type
    if (departTypeId) {
        if (departTypeId === 1) {
            // HQ should not have parent
            if (parentId) {
                $('#department-parent').addClass('is-invalid');
                $('#department-parent').siblings('.invalid-feedback').text('สำนักงานใหญ่ไม่สามารถมีหน่วยงานแม่ได้');
                isValid = false;
            }
        } else {
            // Area, Province, Branch should have parent
            if (!parentId) {
                $('#department-parent').addClass('is-invalid');
                $('#department-parent').siblings('.invalid-feedback').text('กรุณาเลือกหน่วยงานแม่');
                isValid = false;
            } else {
                $('#department-parent').removeClass('is-invalid');
                $('#department-parent').siblings('.invalid-feedback').text('');
            }
        }
    }
    
    return isValid;
}

/**
 * Save department
 */
function saveDepartment() {
    // Validate form
    if (!validateForm()) {
        showNotification('กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง', 'error');
        return;
    }
    
    const departmentId = $('#department-id').val();
    const data = {
        depart_code: $('#department-code').val().trim(),
        depart_name: $('#department-name').val().trim(),
        depart_type_id: $('#department-type').val(),
        parent_id: $('#department-parent').val() || null,
        is_active: $('#department-is-active').is(':checked')
    };
    
    // Additional validation
    if (!data.depart_code || !data.depart_name || !data.depart_type_id) {
        showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
        return;
    }
    
    const url = `${baseUrl}/controllers/user/depart_controller.php`;
    const method = departmentId ? 'PUT' : 'POST';
    
    // Show loading
    const $saveBtn = $('#btn-save-department');
    const originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: departmentId || null,
            ...data
        })
    })
    .then(response => {
        return response.json().then(result => {
            if (!response.ok) {
                throw new Error(result.message || 'เกิดข้อผิดพลาด');
            }
            return result;
        });
    })
    .then(result => {
        $saveBtn.prop('disabled', false).html(originalText);
        
        if (result.status === 'success') {
            $('#department-modal').modal('hide');
            
            // Show success message
            const message = departmentId ? 'แก้ไขข้อมูลสำเร็จ' : 'สร้างข้อมูลสำเร็จ';
            showNotification(message, 'success');
            
            // Reload data after short delay
            setTimeout(() => {
                loadDepartments();
            }, 500);
        } else {
            const errorMsg = result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            showNotification(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving department:', error);
        $saveBtn.prop('disabled', false).html(originalText);
        showNotification(error.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
    });
}

/**
 * Delete department
 */
function deleteDepartment(id) {
    // Get department name for confirmation
    fetch(`${baseUrl}/controllers/user/depart_controller.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(response => {
            const dept = response.data || response;
            const deptName = dept ? (dept.depart_name || dept.depart_code || 'หน่วยงานนี้') : 'หน่วยงานนี้';
            const deptCode = dept ? (dept.depart_code || '') : '';
            
            // Use SweetAlert for confirmation
            Swal.fire({
                title: deptCode ? `รหัสหน่วยงาน: ${deptCode}` : 'ยืนยันการลบ',
                text: `คุณแน่ใจหรือไม่ว่าต้องการลบ "${deptName}"?\n\nหมายเหตุ: ไม่สามารถลบได้หากมีหน่วยงานย่อยหรือผู้ใช้อยู่ในหน่วยงานนี้`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with deletion
                    return fetch(`${baseUrl}/controllers/user/depart_controller.php`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    });
                } else {
                    return null; // User cancelled
                }
            })
            .then(response => {
                if (!response) return; // User cancelled
                
                return response.json().then(result => {
                    if (!response.ok) {
                        throw new Error(result.message || 'เกิดข้อผิดพลาด');
                    }
                    return result;
                });
            })
            .then(result => {
                if (result && result.status === 'success') {
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'ลบข้อมูลสำเร็จ',
                        icon: 'success',
                        confirmButtonText: 'ตกลง',
                        timer: 2000,
                        timerProgressBar: true
                    });
                    
                    // Reload data after short delay
                    setTimeout(() => {
                        loadDepartments();
                    }, 500);
                } else if (result) {
                    const errorMsg = result.message || 'เกิดข้อผิดพลาดในการลบข้อมูล';
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error deleting department:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'เกิดข้อผิดพลาดในการลบข้อมูล',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
        })
        .catch(error => {
            console.error('Error loading department:', error);
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถโหลดข้อมูลหน่วยงานได้',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        });
}

/**
 * Reset department form
 */
function resetDepartmentForm() {
    const form = $('#department-form')[0];
    form.reset();
    
    $('#department-id').val('');
    $('#department-is-active').prop('checked', true);
    
    // Remove validation classes
    $('#department-code, #department-name, #department-type').removeClass('is-invalid is-valid');
    $('.invalid-feedback').text('');
    
    // Reset Choices.js selects
    const typeSelectElement = document.getElementById('department-type');
    const parentSelectElement = document.getElementById('department-parent');
    
    if (typeSelectElement) {
        typeSelectElement.value = '';
        if (typeSelectElement.choicesInstance) {
            try {
                typeSelectElement.choicesInstance.setChoiceByValue('');
            } catch (e) {
                // If Choices not initialized, value already set above
            }
        }
    }
    
    if (parentSelectElement) {
        parentSelectElement.value = '';
        if (parentSelectElement.choicesInstance) {
            try {
                parentSelectElement.choicesInstance.setChoiceByValue('');
            } catch (e) {
                // If Choices not initialized, value already set above
            }
        }
    }
}

/**
 * View department details
 */
function viewDepartment(id) {
    fetch(`${baseUrl}/controllers/user/depart_controller.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(response => {
            const dept = response.data || response;
            if (dept) {
                const typeName = departmentTypes[dept.depart_type_id] ? departmentTypes[dept.depart_type_id].name : '-';
                const statusBadge = dept.is_active !== false ? 
                    '<span class="badge bg-success-transparent">ใช้งาน</span>' : 
                    '<span class="badge bg-danger-transparent">ไม่ใช้งาน</span>';
                
                const content = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">รหัสหน่วยงาน:</label>
                            <p class="mb-0">${escapeHtml(dept.depart_code || '-')}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ชื่อหน่วยงาน:</label>
                            <p class="mb-0">${escapeHtml(dept.depart_name || '-')}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ประเภทหน่วยงาน:</label>
                            <p class="mb-0">${formatDepartType(dept.depart_type_id)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">สถานะ:</label>
                            <p class="mb-0">${statusBadge}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">หน่วยงานแม่:</label>
                            <p class="mb-0">${formatParentInfo(dept)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">วันที่สร้าง:</label>
                            <p class="mb-0">${formatDate(dept.created_at)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">วันที่อัปเดต:</label>
                            <p class="mb-0">${formatDate(dept.updated_at)}</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('view-department-content').innerHTML = content;
                document.getElementById('view-department-modal-label').textContent = `รายละเอียด: ${escapeHtml(dept.depart_name || dept.depart_code)}`;
                $('#view-department-modal').modal('show');
            } else {
                alert('ไม่พบข้อมูลหน่วยงาน');
            }
        })
        .catch(error => {
            console.error('Error loading department:', error);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
        });
}

/**
 * View department hierarchy
 */
function viewHierarchy(id) {
    const container = document.getElementById('hierarchy-tree-container');
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">กำลังโหลด...</span></div></div>';
    
    $('#hierarchy-modal').modal('show');
    
    fetch(`${baseUrl}/controllers/user/depart_controller.php?action=hierarchy&id=${id}`)
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success' && response.data) {
                const hierarchy = response.data;
                if (hierarchy.length > 0) {
                    // Build tree structure
                    const treeHtml = buildHierarchyTree(hierarchy);
                    container.innerHTML = treeHtml;
                } else {
                    container.innerHTML = '<div class="alert alert-info">ไม่พบข้อมูล hierarchy</div>';
                }
            } else {
                container.innerHTML = '<div class="alert alert-danger">ไม่สามารถโหลดข้อมูล hierarchy ได้</div>';
            }
        })
        .catch(error => {
            console.error('Error loading hierarchy:', error);
            container.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
}

/**
 * Build hierarchy tree HTML
 */
function buildHierarchyTree(hierarchy) {
    if (!hierarchy || hierarchy.length === 0) {
        return '<div class="alert alert-info">ไม่พบข้อมูล</div>';
    }
    
    // Find root (level 1 or lowest level)
    const root = hierarchy.find(h => h.level === 1) || hierarchy[0];
    if (!root) {
        return '<div class="alert alert-info">ไม่พบข้อมูล root</div>';
    }
    
    // Build tree structure
    const tree = buildTreeStructure(hierarchy, root.id);
    
    // Render tree
    return renderTree(tree, 0);
}

/**
 * Build tree structure from flat hierarchy array
 */
function buildTreeStructure(hierarchy, rootId) {
    const nodeMap = {};
    
    // Create node map
    hierarchy.forEach(item => {
        nodeMap[item.id] = {
            ...item,
            children: []
        };
    });
    
    // Build tree - find root first
    let root = null;
    hierarchy.forEach(item => {
        if (item.id === rootId || item.level === 1) {
            root = nodeMap[item.id];
        }
    });
    
    // If no root found, use first item
    if (!root && hierarchy.length > 0) {
        root = nodeMap[hierarchy[0].id];
    }
    
    // Build children relationships
    hierarchy.forEach(item => {
        if (item.parent_id && nodeMap[item.parent_id] && item.id !== rootId) {
            // Add to parent's children if not already added
            const exists = nodeMap[item.parent_id].children.some(child => child.id === item.id);
            if (!exists) {
                nodeMap[item.parent_id].children.push(nodeMap[item.id]);
            }
        }
    });
    
    // Sort children by level and code
    function sortChildren(node) {
        if (node.children && node.children.length > 0) {
            node.children.sort((a, b) => {
                if (a.level !== b.level) return a.level - b.level;
                if (a.depart_type_level !== b.depart_type_level) return a.depart_type_level - b.depart_type_level;
                return (a.depart_code || '').localeCompare(b.depart_code || '');
            });
            node.children.forEach(child => sortChildren(child));
        }
    }
    
    if (root) {
        sortChildren(root);
    }
    
    return root;
}

/**
 * Render tree HTML with expand/collapse
 */
function renderTree(node, level = 0) {
    if (!node) return '';
    
    const indent = level * 20;
    const hasChildren = node.children && node.children.length > 0;
    const uniqueId = `hierarchy-${node.id}-${Date.now()}`;
    const typeBadge = formatDepartType(node.depart_type_id);
    const statusBadge = node.is_active !== false ? 
        '<span class="badge bg-success-transparent">ใช้งาน</span>' : 
        '<span class="badge bg-danger-transparent">ไม่ใช้งาน</span>';
    
    let html = `
        <div class="hierarchy-item mb-2" style="margin-left: ${indent}px;">
            <div class="d-flex align-items-center p-2 border rounded" style="background-color: ${level === 0 ? '#e7f3ff' : '#f8f9fa'};">
                ${hasChildren ? `
                    <button class="btn btn-sm btn-link p-0 me-2" type="button" data-bs-toggle="collapse" data-bs-target="#${uniqueId}" aria-expanded="false">
                        <i class="ri-arrow-right-s-line toggle-icon"></i>
                    </button>
                ` : '<span class="me-2" style="width: 20px;"></span>'}
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <strong>${escapeHtml(node.depart_code || '')}</strong>
                        <span>-</span>
                        <span>${escapeHtml(node.depart_name || '')}</span>
                        ${typeBadge}
                        ${statusBadge}
                    </div>
                </div>
            </div>
    `;
    
    if (hasChildren) {
        html += `
            <div class="collapse" id="${uniqueId}">
                <div class="mt-2">
        `;
        
        node.children.forEach(child => {
            html += renderTree(child, level + 1);
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    
    return html;
}

// Add event listener for collapse toggle icon
$(document).on('show.bs.collapse', '.collapse', function() {
    $(this).siblings('.d-flex').find('.toggle-icon').removeClass('ri-arrow-right-s-line').addClass('ri-arrow-down-s-line');
});

$(document).on('hide.bs.collapse', '.collapse', function() {
    $(this).siblings('.d-flex').find('.toggle-icon').removeClass('ri-arrow-down-s-line').addClass('ri-arrow-right-s-line');
});

/**
 * View users in department
 * Make it globally accessible
 */
window.viewUsers = function(departId) {
    console.log('viewUsers called with departId:', departId);
    
    const container = document.getElementById('users-container');
    if (!container) {
        console.error('users-container element not found!');
        alert('ไม่พบ element สำหรับแสดงข้อมูล users');
        return;
    }
    
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">กำลังโหลด...</span></div></div>';
    
    // Show modal - try both Bootstrap 5 and jQuery methods
    const modalElement = document.getElementById('users-modal');
    if (modalElement) {
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                // Check if modal instance already exists
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalElement);
                }
                modalInstance.show();
                console.log('Bootstrap 5 modal shown');
            } catch (e) {
                console.error('Error showing Bootstrap 5 modal:', e);
                // Fallback to jQuery
                if (typeof $ !== 'undefined') {
                    $('#users-modal').modal('show');
                }
            }
        } else if (typeof $ !== 'undefined') {
            // jQuery/Bootstrap 4
            $('#users-modal').modal('show');
            console.log('jQuery modal shown');
        } else {
            console.error('Bootstrap modal not available');
            // Show element directly as fallback
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
        }
    } else {
        console.error('users-modal element not found!');
        alert('ไม่พบ modal สำหรับแสดงข้อมูล users');
        return;
    }
    
    const apiUrl = `${baseUrl}/controllers/user/depart_controller.php?action=users&id=${departId}`;
    console.log('Fetching users from:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            console.log('API Response:', response);
            
            if (response.status === 'success' && response.data) {
                const department = response.data.department;
                const users = response.data.users || [];
                const count = response.data.count || users.length;
                
                console.log('Department:', department);
                console.log('Users count:', count);
                console.log('Users array:', users);
                
                // Update modal title
                document.getElementById('users-modal-label').textContent = 
                    `รายชื่อ Users ในหน่วยงาน: ${escapeHtml(department.depart_name || department.depart_code)}`;
                
                if (users.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>ไม่พบ Users ในหน่วยงานนี้
                        </div>
                    `;
                    return;
                }
                
                // Build users table
                let tableHtml = `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">จำนวน Users ทั้งหมด: <span class="badge bg-primary">${count}</span></h6>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%;" class="text-center">ลำดับ</th>
                                    <th style="width:15%;">รหัสผู้ใช้</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>อีเมล</th>
                                    <th style="width:15%;">ตำแหน่ง</th>
                                    <th style="width:10%;" class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                users.forEach((user, index) => {
                    const statusBadge = user.user_status === 'active' ? 
                        '<span class="badge bg-success-transparent">ใช้งาน</span>' : 
                        '<span class="badge bg-danger-transparent">ไม่ใช้งาน</span>';
                    
                    tableHtml += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${escapeHtml(user.user_code || '-')}</td>
                            <td>${escapeHtml((user.user_fname || '') + ' ' + (user.user_lname || ''))}</td>
                            <td>${escapeHtml(user.user_email || '-')}</td>
                            <td>${escapeHtml(user.position_name || '-')}</td>
                            <td class="text-center">${statusBadge}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                container.innerHTML = tableHtml;
                console.log('Users table rendered successfully');
            } else {
                console.error('API response error:', response);
                const errorMsg = response.message || 'ไม่สามารถโหลดข้อมูล users ได้';
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line me-2"></i>${escapeHtml(errorMsg)}
                        <br><small>Response: ${JSON.stringify(response)}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            console.error('Error stack:', error.stack);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>เกิดข้อผิดพลาดในการโหลดข้อมูล: ${escapeHtml(error.message)}
                    <br><small>กรุณาตรวจสอบ Console สำหรับรายละเอียดเพิ่มเติม</small>
                </div>
            `;
        });
};

/**
 * Show notification (simple alert replacement)
 */
function showNotification(message, type = 'info') {
    // Use Bootstrap toast if available, otherwise use alert
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        // Remove existing toasts
        $('.toast-notification').remove();
        
        // Create toast element
        const bgColor = type === 'success' ? 'success' : (type === 'error' ? 'danger' : 'info');
        const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-triangle' : 'info-circle');
        
        const toastHtml = `
            <div class="toast toast-notification align-items-center text-white bg-${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${icon} me-2"></i>${escapeHtml(message)}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        const toastElement = $(toastHtml).appendTo('body');
        const toast = new bootstrap.Toast(toastElement[0], { 
            delay: type === 'error' ? 5000 : 3000,
            autohide: true
        });
        toast.show();
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    } else {
        // Fallback to alert
        alert(message);
    }
}

