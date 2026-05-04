/**
 * Menu List Management
 * ไฟล์ JavaScript สำหรับจัดการข้อมูลเมนูระบบ
 * 
 * วันที่: 2025-01-27
 */

// Base URL สำหรับ API
const baseUrl = window.location.origin + window.location.pathname.replace('/pages/menu-list.php', '');

// Global variables
let menusTable = null;
let allMenus = [];
let menuTree = [];
let categories = [];
let filters = {
    menu_code: '',
    menu_name: '',
    category: '',
    is_active: ''
};

/**
 * Initialize page
 */
$(document).ready(function() {
    console.log('Menu List Page Initialized');
    
    // Scroll to top on page load
    window.scrollTo(0, 0);
    
    // Load categories first
    loadCategories().then(() => {
        // Then load initial data
        loadMenus();
    });
    
    // Event listeners
    $('#filter-search-btn').on('click', function() {
        applyFilters();
    });
    
    $('#filter-reset-btn').on('click', function() {
        resetFilters();
    });
    
    // Create/Edit modal events
    $('#btn-create-menu').on('click', function() {
        showCreateModal();
    });
    
    $('#btn-save-menu').on('click', function() {
        saveMenu();
    });
    
    // Modal close event - reset form
    $('#menu-modal').on('hidden.bs.modal', function() {
        resetMenuForm();
    });
    
    // Form validation on input
    $('#menu-code, #menu-name').on('blur', function() {
        validateField($(this));
    });
    
    // Enter key to submit
    $('#menu-form').on('submit', function(e) {
        e.preventDefault();
        saveMenu();
    });
});

/**
 * Load categories from API
 */
function loadCategories() {
    return fetch(`${baseUrl}/controllers/user/menu_controller.php?action=categories`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                categories = data;
                
                // Populate category dropdown
                const categorySelect = $('#filter-category');
                categorySelect.empty();
                categorySelect.append('<option value="">ทั้งหมด</option>');
                data.forEach(category => {
                    if (category) {
                        categorySelect.append(`<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`);
                    }
                });
                
                // Update Choices.js if initialized
                const categorySelectElement = document.getElementById('filter-category');
                if (categorySelectElement && typeof Choices !== 'undefined') {
                    try {
                        if (categorySelectElement.choicesInstance) {
                            categorySelectElement.choicesInstance.destroy();
                        }
                    } catch (e) {
                        // Ignore
                    }
                    new Choices(categorySelectElement, {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false
                    });
                }
                
                console.log('Categories loaded:', categories);
            } else {
                console.warn('No categories found');
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

/**
 * Load menus from API
 */
function loadMenus() {
    console.log('Loading menus...');
    
    // Show loading only if tbody exists
    const tbody = $('#menus-tbody');
    if (tbody.length > 0) {
        tbody.html(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2">กำลังโหลดข้อมูล...</p>
                </td>
            </tr>
        `);
    }
    
    // Fetch menu tree and return promise
    return fetch(`${baseUrl}/controllers/user/menu_controller.php?action=tree`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                menuTree = data;
                
                // Flatten tree to flat array for filtering
                allMenus = flattenMenuTree(data);
                
                // Apply filters and render only if tbody exists
                if (tbody.length > 0) {
                    applyFilters();
                }
                
                return data;
            } else {
                console.error('Invalid data format:', data);
                showError('ไม่สามารถโหลดข้อมูลเมนูได้');
                return [];
            }
        })
        .catch(error => {
            console.error('Error loading menus:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
            return [];
        });
}

/**
 * Flatten menu tree to flat array
 */
function flattenMenuTree(tree, level = 0, parentName = '') {
    let result = [];
    
    tree.forEach(menu => {
        const menuItem = {
            ...menu,
            level: level,
            parent_name: parentName,
            has_children: menu.children && menu.children.length > 0
        };
        result.push(menuItem);
        
        if (menu.children && menu.children.length > 0) {
            const children = flattenMenuTree(menu.children, level + 1, menu.menu_name);
            result = result.concat(children);
        }
    });
    
    return result;
}

/**
 * Apply filters
 */
function applyFilters() {
    // Get filter values
    filters.menu_code = $('#filter-menu-code').val().trim();
    filters.menu_name = $('#filter-menu-name').val().trim();
    filters.category = $('#filter-category').val();
    filters.is_active = $('#filter-is-active').val();
    
    // Filter menus
    let filteredMenus = allMenus;
    
    if (filters.menu_code) {
        filteredMenus = filteredMenus.filter(menu => 
            menu.menu_code.toLowerCase().includes(filters.menu_code.toLowerCase())
        );
    }
    
    if (filters.menu_name) {
        filteredMenus = filteredMenus.filter(menu => 
            menu.menu_name.toLowerCase().includes(filters.menu_name.toLowerCase())
        );
    }
    
    if (filters.category) {
        filteredMenus = filteredMenus.filter(menu => 
            menu.category === filters.category
        );
    }
    
    if (filters.is_active !== '') {
        const isActive = filters.is_active === 'true';
        filteredMenus = filteredMenus.filter(menu => 
            menu.is_active === isActive
        );
    }
    
    // Render table
    renderMenusTable(filteredMenus);
    
    // Update pagination info
    updatePaginationInfo(filteredMenus.length);
}

/**
 * Reset filters
 */
function resetFilters() {
    $('#filter-menu-code').val('');
    $('#filter-menu-name').val('');
    $('#filter-category').val('');
    $('#filter-is-active').val('');
    
    // Reset Choices.js
    const categorySelect = document.getElementById('filter-category');
    if (categorySelect && categorySelect.choicesInstance) {
        categorySelect.choicesInstance.setChoiceByValue('');
    }
    
    const activeSelect = document.getElementById('filter-is-active');
    if (activeSelect && activeSelect.choicesInstance) {
        activeSelect.choicesInstance.setChoiceByValue('');
    }
    
    // Reload all menus
    applyFilters();
}

/**
 * Render menus table
 */
function renderMenusTable(menus) {
    const tbody = $('#menus-tbody');
    tbody.empty();
    
    if (menus.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
            </tr>
        `);
        return;
    }
    
    menus.forEach((menu, index) => {
        const row = createMenuRow(menu, index + 1);
        tbody.append(row);
    });
}

/**
 * Create menu table row
 */
function createMenuRow(menu, index) {
    const indent = menu.level * 20;
    const levelClass = `level-${menu.level + 1}`;
    const statusBadge = menu.is_active 
        ? '<span class="badge rounded-pill bg-success">ใช้งาน</span>'
        : '<span class="badge rounded-pill bg-danger">ไม่ใช้งาน</span>';
    
    const iconHtml = menu.icon 
        ? `<i class="${escapeHtml(menu.icon)}"></i> ${escapeHtml(menu.icon)}`
        : '-';
    
    const pathHtml = menu.menu_path 
        ? `<span class="text-muted small">${escapeHtml(menu.menu_path)}</span>`
        : '<span class="text-muted">-</span>';
    
    const categoryHtml = menu.category 
        ? escapeHtml(menu.category)
        : '-';
    
    const parentIndicator = menu.level > 0 
        ? `<span class="text-muted small">└─ ${escapeHtml(menu.parent_name || '')}</span>` 
        : '';
    
    return `
        <tr class="menu-tree-item ${levelClass}">
            <td class="text-center">${index}</td>
            <td>
                <div style="padding-left: ${indent}px;">
                    ${parentIndicator ? `<div class="small text-muted">${parentIndicator}</div>` : ''}
                    <strong>${escapeHtml(menu.menu_code)}</strong>
                </div>
            </td>
            <td>${escapeHtml(menu.menu_name)}</td>
            <td>${pathHtml}</td>
            <td>${iconHtml}</td>
            <td>${categoryHtml}</td>
            <td class="text-center">${menu.sort_order || 0}</td>
            <td class="text-center">${statusBadge}</td>
            <td class="text-center">
                <div class="hstack gap-2 fs-15 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="viewMenu(${menu.id})" title="ดูรายละเอียด">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick="editMenu(${menu.id})" title="แก้ไข">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deleteMenu(${menu.id})" title="ลบ">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Update pagination info
 */
function updatePaginationInfo(total) {
    $('#pagination-info').text(`กำลังแสดง ${total} รายการ`);
}

/**
 * Show create modal
 */
function showCreateModal() {
    $('#menu-modal-label').text('สร้างเมนูใหม่');
    $('#menu-id').val('');
    resetMenuForm();
    
    // Load parent menu options (ensure menuTree is loaded)
    if (menuTree && menuTree.length > 0) {
        loadParentMenuOptions();
    } else {
        // If menuTree is not loaded yet, load it first
        loadMenus().then(() => {
            loadParentMenuOptions();
        });
    }
    
    $('#menu-modal').modal('show');
}

/**
 * Load parent menu options
 */
function loadParentMenuOptions(excludeId = null) {
    const parentSelect = $('#menu-parent');
    if (!parentSelect.length) {
        console.warn('Parent select element not found');
        return;
    }
    
    parentSelect.empty();
    parentSelect.append('<option value="">ไม่มี (เป็นเมนูระดับสูงสุด)</option>');
    
    // Check if menuTree is available
    if (!menuTree || menuTree.length === 0) {
        console.warn('Menu tree is not loaded yet');
        parentSelect.append('<option value="" disabled>กำลังโหลดเมนู...</option>');
        return;
    }
    
    // Build options from menu tree
    function addMenuOptions(menus, level = 0) {
        menus.forEach(menu => {
            if (excludeId && menu.id === excludeId) {
                return; // Skip self
            }
            
            const indent = '&nbsp;&nbsp;'.repeat(level);
            const optionText = `${indent}${escapeHtml(menu.menu_name)} (${escapeHtml(menu.menu_code)})`;
            parentSelect.append(`<option value="${menu.id}">${optionText}</option>`);
            
            if (menu.children && menu.children.length > 0) {
                addMenuOptions(menu.children, level + 1);
            }
        });
    }
    
    addMenuOptions(menuTree);
    
    // Update Choices.js after a short delay to ensure DOM is updated
    setTimeout(() => {
        const parentSelectElement = document.getElementById('menu-parent');
        if (parentSelectElement && typeof Choices !== 'undefined') {
            try {
                // Destroy existing instance
                if (parentSelectElement.choicesInstance) {
                    parentSelectElement.choicesInstance.destroy();
                    parentSelectElement.choicesInstance = null;
                }
                
                // Remove Choices.js wrapper if exists
                const choicesWrapper = parentSelectElement.closest('.choices');
                if (choicesWrapper && choicesWrapper !== parentSelectElement) {
                    const originalSelect = choicesWrapper.querySelector('select');
                    if (originalSelect) {
                        choicesWrapper.replaceWith(originalSelect);
                    }
                }
            } catch (e) {
                console.warn('Error destroying Choices instance:', e);
            }
            
            // Initialize new Choices instance
            try {
                parentSelectElement.choicesInstance = new Choices(parentSelectElement, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false
                });
                console.log('Choices.js initialized for parent menu select');
            } catch (e) {
                console.error('Error initializing Choices:', e);
            }
        } else {
            console.warn('Choices.js not available or element not found');
        }
    }, 100);
}

/**
 * View menu
 */
function viewMenu(id) {
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=one&id=${id}`)
        .then(response => response.json())
        .then(menu => {
            if (menu && menu.id) {
                // Show menu details in alert or modal
                const details = `
รหัสเมนู: ${menu.menu_code}
ชื่อเมนู: ${menu.menu_name}
Path: ${menu.menu_path || '-'}
Icon: ${menu.icon || '-'}
หมวดหมู่: ${menu.category || '-'}
Parent ID: ${menu.parent_id || '-'}
Sort Order: ${menu.sort_order || 0}
สถานะ: ${menu.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                `;
                alert(details);
            } else {
                showError('ไม่พบข้อมูลเมนู');
            }
        })
        .catch(error => {
            console.error('Error viewing menu:', error);
            showError('เกิดข้อผิดพลาดในการดูข้อมูลเมนู');
        });
}

/**
 * Edit menu
 */
function editMenu(id) {
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=one&id=${id}`)
        .then(response => response.json())
        .then(menu => {
            if (menu && menu.id) {
                $('#menu-modal-label').text('แก้ไขเมนู');
                $('#menu-id').val(menu.id);
                $('#menu-code').val(menu.menu_code || '');
                $('#menu-name').val(menu.menu_name || '');
                $('#menu-path').val(menu.menu_path || '');
                $('#menu-icon').val(menu.icon || '');
                $('#menu-category').val(menu.category || '');
                $('#menu-sort-order').val(menu.sort_order || 0);
                $('#menu-is-active').prop('checked', menu.is_active !== false);
                
                // Load parent options (excluding self)
                if (menuTree && menuTree.length > 0) {
                    loadParentMenuOptions(id);
                    
                    // Set parent value after a delay to ensure options are loaded and Choices.js is initialized
                    setTimeout(() => {
                        const parentSelectElement = document.getElementById('menu-parent');
                        if (parentSelectElement) {
                            if (parentSelectElement.choicesInstance) {
                                try {
                                    parentSelectElement.choicesInstance.setChoiceByValue(menu.parent_id || '');
                                } catch (e) {
                                    console.warn('Error setting Choices value:', e);
                                    $('#menu-parent').val(menu.parent_id || '');
                                }
                            } else {
                                $('#menu-parent').val(menu.parent_id || '');
                            }
                        }
                    }, 500);
                } else {
                    // If menuTree is not loaded, load it first
                    loadMenus().then(() => {
                        loadParentMenuOptions(id);
                        setTimeout(() => {
                            const parentSelectElement = document.getElementById('menu-parent');
                            if (parentSelectElement) {
                                if (parentSelectElement.choicesInstance) {
                                    try {
                                        parentSelectElement.choicesInstance.setChoiceByValue(menu.parent_id || '');
                                    } catch (e) {
                                        $('#menu-parent').val(menu.parent_id || '');
                                    }
                                } else {
                                    $('#menu-parent').val(menu.parent_id || '');
                                }
                            }
                        }, 500);
                    });
                }
                
                $('#menu-modal').modal('show');
            } else {
                showError('ไม่พบข้อมูลเมนู');
            }
        })
        .catch(error => {
            console.error('Error loading menu:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลเมนู');
        });
}

/**
 * Delete menu
 */
function deleteMenu(id) {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบเมนูนี้?')) {
        return;
    }
    
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=delete&id=${id}`, {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showSuccess(data.message);
                loadMenus(); // Reload menus
            } else {
                showError('เกิดข้อผิดพลาดในการลบเมนู');
            }
        })
        .catch(error => {
            console.error('Error deleting menu:', error);
            showError('เกิดข้อผิดพลาดในการลบเมนู');
        });
}

/**
 * Save menu (create or update)
 */
function saveMenu() {
    // Validate form
    if (!validateMenuForm()) {
        return;
    }
    
    const menuId = $('#menu-id').val();
    const isUpdate = menuId !== '';
    
    const menuData = {
        menu_code: $('#menu-code').val().trim(),
        menu_name: $('#menu-name').val().trim(),
        menu_path: $('#menu-path').val().trim() || null,
        icon: $('#menu-icon').val().trim() || null,
        category: $('#menu-category').val().trim() || null,
        parent_id: $('#menu-parent').val() || null,
        sort_order: parseInt($('#menu-sort-order').val()) || 0,
        is_active: $('#menu-is-active').is(':checked')
    };
    
    // Convert empty string to null for parent_id
    if (menuData.parent_id === '') {
        menuData.parent_id = null;
    } else {
        menuData.parent_id = parseInt(menuData.parent_id);
    }
    
    const url = `${baseUrl}/controllers/user/menu_controller.php?action=${isUpdate ? 'update' : 'create'}`;
    const method = isUpdate ? 'PUT' : 'POST';
    
    if (isUpdate) {
        menuData.id = parseInt(menuId);
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(menuData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showSuccess(data.message);
                $('#menu-modal').modal('hide');
                loadMenus(); // Reload menus
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกเมนู');
            }
        })
        .catch(error => {
            console.error('Error saving menu:', error);
            showError('เกิดข้อผิดพลาดในการบันทึกเมนู');
        });
}

/**
 * Validate menu form
 */
function validateMenuForm() {
    let isValid = true;
    
    // Reset validation
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Validate menu code
    const menuCode = $('#menu-code').val().trim();
    if (!menuCode) {
        setFieldError('#menu-code', 'กรุณากรอกรหัสเมนู');
        isValid = false;
    }
    
    // Validate menu name
    const menuName = $('#menu-name').val().trim();
    if (!menuName) {
        setFieldError('#menu-name', 'กรุณากรอกชื่อเมนู');
        isValid = false;
    }
    
    // Validate parent (check for circular reference)
    const parentId = $('#menu-parent').val();
    const menuId = $('#menu-id').val();
    if (parentId && menuId && parentId === menuId) {
        setFieldError('#menu-parent', 'ไม่สามารถเลือกตัวเองเป็น parent ได้');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Set field error
 */
function setFieldError(selector, message) {
    $(selector).addClass('is-invalid');
    $(selector).siblings('.invalid-feedback').text(message);
}

/**
 * Validate field
 */
function validateField($field) {
    const value = $field.val().trim();
    const fieldId = $field.attr('id');
    
    $field.removeClass('is-invalid');
    $field.siblings('.invalid-feedback').text('');
    
    if (fieldId === 'menu-code' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกรหัสเมนู');
        return false;
    }
    
    if (fieldId === 'menu-name' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกชื่อเมนู');
        return false;
    }
    
    return true;
}

/**
 * Reset menu form
 */
function resetMenuForm() {
    $('#menu-form')[0].reset();
    $('#menu-id').val('');
    $('#menu-is-active').prop('checked', true);
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

/**
 * Show success message
 */
function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

/**
 * Show error message
 */
function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message
        });
    } else {
        alert('เกิดข้อผิดพลาด: ' + message);
    }
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
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
