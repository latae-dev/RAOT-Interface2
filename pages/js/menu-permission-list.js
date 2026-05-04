/**
 * Menu Permission List Management
 * ไฟล์ JavaScript สำหรับจัดการสิทธิ์เมนูตาม Position
 * 
 * วันที่: 2025-01-27
 * อัปเดต: ปรับปรุงให้ง่ายและล้อกับหน้าอื่นๆ
 */

// Base URL สำหรับ API
const baseUrl = window.location.origin + window.location.pathname.replace('/pages/menu-permission-list.php', '');

// Global variables
let positions = [];
let menuTree = [];
let flatMenus = []; // เก็บ flat menus สำหรับหา children
let categories = [];
let currentPositionId = null;
let currentPermissions = {}; // {menu_id: {id, is_granted}}
let permissionChanges = {}; // Track changes

/**
 * Initialize page
 */
$(document).ready(function() {
    console.log('Menu Permission List Page Initialized');
    
    // Scroll to top on page load
    window.scrollTo(0, 0);
    
    // Load initial data
    Promise.all([
        loadPositions(),
        loadCategories(),
        loadMenuTree()
    ]).then(() => {
        console.log('Initial data loaded');
    });
    
    // Event listeners
    $('#btn-load-permissions').on('click', function() {
        loadPermissions();
    });
    
    $('#btn-save-permissions').on('click', function() {
        savePermissions();
    });
    
    $('#btn-copy-permissions').on('click', function() {
        showCopyPermissionModal();
    });
    
    $('#btn-confirm-copy').on('click', function() {
        copyPermissions();
    });
    
    $('#btn-select-all').on('click', function() {
        selectAllMenus(true);
    });
    
    $('#btn-deselect-all').on('click', function() {
        selectAllMenus(false);
    });
    
    // Category filter change
    $(document).on('change', '#filter-category', function() {
        if (currentPositionId) {
            renderPermissionList();
        }
    });
});

/**
 * Load positions from API
 */
function loadPositions() {
    return fetch(`${baseUrl}/controllers/user/position_controller.php`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                positions = data;
                
                // Populate position dropdown
                const positionSelect = $('#filter-position');
                positionSelect.empty();
                positionSelect.append('<option value="">เลือก Position</option>');
                data.forEach(position => {
                    positionSelect.append(`<option value="${position.id}">${escapeHtml(position.position_name || position.position_code)}</option>`);
                });
                
                // Populate copy from position dropdown
                const copyFromPositionSelect = $('#copy-from-position');
                copyFromPositionSelect.empty();
                copyFromPositionSelect.append('<option value="">เลือก Position</option>');
                data.forEach(position => {
                    copyFromPositionSelect.append(`<option value="${position.id}">${escapeHtml(position.position_name || position.position_code)}</option>`);
                });
                
                // Update Choices.js
                setTimeout(() => {
                    updateChoicesSelect('filter-position');
                    updateChoicesSelect('copy-from-position');
                }, 200);
                
                console.log('Positions loaded:', positions.length);
            } else {
                console.error('Invalid positions data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading positions:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูล Position');
        });
}

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
                
                setTimeout(() => {
                    updateChoicesSelect('filter-category');
                }, 200);
                
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
 * Load menu tree from API
 */
function loadMenuTree() {
    return fetch(`${baseUrl}/controllers/user/menu_controller.php?action=tree`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                menuTree = data;
                console.log('Menu tree loaded:', menuTree.length);
            } else {
                console.error('Invalid menu tree data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading menu tree:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลเมนู');
        });
}

/**
 * Load permissions for selected position
 */
function loadPermissions() {
    // Get value using helper function
    const positionId = getSelectValue('filter-position');
    
    if (!positionId) {
        showError('กรุณาเลือก Position');
        return;
    }
    
    currentPositionId = parseInt(positionId);
    permissionChanges = {};
    
    // Show loading
    $('#permission-list-container').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-2">กำลังโหลดข้อมูลสิทธิ์...</p>
        </div>
    `);
    
    // Fetch permissions for this position
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=permissions-by-position&position_id=${currentPositionId}`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                // Build permissions object
                currentPermissions = {};
                data.forEach(perm => {
                    currentPermissions[perm.menu_id] = {
                        id: perm.id,
                        is_granted: perm.is_granted
                    };
                });
                
                // Render permission list
                renderPermissionList();
                
                // Show action buttons
                $('#btn-save-permissions').show();
                $('#btn-copy-permissions').show();
                $('#btn-select-all').show();
                $('#btn-deselect-all').show();
            } else {
                console.error('Invalid permissions data format:', data);
                showError('ไม่สามารถโหลดข้อมูลสิทธิ์ได้');
            }
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลสิทธิ์');
        });
}

/**
 * Render permission list (แบบง่าย)
 */
function renderPermissionList() {
    if (!currentPositionId || !menuTree || menuTree.length === 0) {
        return;
    }
    
    const selectedCategory = getSelectValue('filter-category');
    
    // Filter menu tree by category if selected
    let filteredTree = menuTree;
    if (selectedCategory) {
        filteredTree = filterMenuTreeByCategory(menuTree, selectedCategory);
    }
    
    // Flatten menu tree และเก็บไว้ใน global variable
    flatMenus = flattenMenuTree(filteredTree);
    
    // Build HTML table
    let html = '<div class="table-responsive mt-2">';
    html += '<table class="table text-nowrap table-bordered">';
    html += '<thead class="text-center table-light">';
    html += '<tr>';
    html += '<th style="width: 5%;">ลำดับ</th>';
    html += '<th style="width: 50%;">เมนู</th>';
    html += '<th style="width: 15%;">หมวดหมู่</th>';
    html += '<th style="width: 10%;" class="text-center">มีสิทธิ์</th>';
    html += '<th style="width: 20%;" class="text-center">การจัดการ</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    if (flatMenus.length === 0) {
        html += '<tr><td colspan="5" class="text-center">ไม่พบข้อมูลเมนู</td></tr>';
    } else {
        flatMenus.forEach((menu, index) => {
            const permission = currentPermissions[menu.id];
            let hasPermission = false;
            
            if (permission) {
                hasPermission = permission.is_granted;
            }
            
            // Check if changed
            const changeKey = `menu_${menu.id}`;
            if (permissionChanges[changeKey] !== undefined) {
                hasPermission = permissionChanges[changeKey];
            }
            
            const indent = menu.level * 20;
            const levelClass = `level-${menu.level + 1}`;
            const iconHtml = menu.icon ? `<i class="${escapeHtml(menu.icon)}"></i> ` : '';
            
            html += `<tr class="menu-tree-item ${levelClass}">`;
            html += `<td class="text-center">${index + 1}</td>`;
            html += `<td style="padding-left: ${indent}px;">`;
            html += `${iconHtml}<strong>${escapeHtml(menu.menu_name)}</strong>`;
            if (menu.menu_code) {
                html += `<br><small class="text-muted">${escapeHtml(menu.menu_code)}</small>`;
            }
            html += `</td>`;
            html += `<td>${escapeHtml(menu.category || '-')}</td>`;
            html += `<td class="text-center">`;
            html += `<div class="form-check d-inline-block">`;
            html += `<input class="form-check-input permission-checkbox" type="checkbox" id="perm_${menu.id}" `;
            html += `onchange="updatePermission(${menu.id}, this.checked)" ${hasPermission ? 'checked' : ''}>`;
            html += `<label class="form-check-label" for="perm_${menu.id}"></label>`;
            html += `</div>`;
            html += `</td>`;
            html += `<td class="text-center">`;
            // ตรวจสอบว่า menu นี้มี children หรือไม่ (จาก original tree)
            const hasChildren = checkMenuHasChildren(menu.id);
            if (hasChildren) {
                html += `<button type="button" class="btn btn-sm btn-info" onclick="window.applyToChildren(${menu.id})" title="ใช้กับเมนูย่อยทั้งหมด">`;
                html += `<i class="ri-subtract-line"></i> ใช้กับย่อย`;
                html += `</button>`;
            }
            html += `</td>`;
            html += `</tr>`;
        });
    }
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    $('#permission-list-container').html(html);
}

/**
 * Flatten menu tree to flat array (แต่ยังเก็บ children ไว้)
 */
function flattenMenuTree(tree, level = 0) {
    let result = [];
    
    tree.forEach(menu => {
        // สร้าง menu item โดยเก็บ children ไว้ด้วย
        const menuItem = {
            ...menu,
            level: level,
            // เก็บ children reference ไว้เพื่อใช้ใน applyToChildren
            children: menu.children || []
        };
        result.push(menuItem);
        
        if (menu.children && menu.children.length > 0) {
            const children = flattenMenuTree(menu.children, level + 1);
            result = result.concat(children);
        }
    });
    
    return result;
}

/**
 * Check if menu has children
 */
function checkMenuHasChildren(menuId) {
    function findMenuInTree(tree, id) {
        const searchId = parseInt(id);
        for (let menu of tree) {
            const menuId = parseInt(menu.id);
            if (menuId === searchId) {
                return menu;
            }
            if (menu.children && menu.children.length > 0) {
                const found = findMenuInTree(menu.children, searchId);
                if (found) return found;
            }
        }
        return null;
    }
    
    if (!menuTree || menuTree.length === 0) {
        return false;
    }
    
    const menu = findMenuInTree(menuTree, parseInt(menuId));
    return menu && menu.children && menu.children.length > 0;
}

/**
 * Filter menu tree by category
 */
function filterMenuTreeByCategory(tree, category) {
    let result = [];
    
    tree.forEach(menu => {
        if (menu.category === category) {
            result.push(menu);
        } else if (menu.children && menu.children.length > 0) {
            const filteredChildren = filterMenuTreeByCategory(menu.children, category);
            if (filteredChildren.length > 0) {
                const menuCopy = {...menu, children: filteredChildren};
                result.push(menuCopy);
            }
        }
    });
    
    return result;
}

/**
 * Update permission for a menu
 */
function updatePermission(menuId, isGranted) {
    const changeKey = `menu_${menuId}`;
    permissionChanges[changeKey] = isGranted;
    console.log('Permission changed:', changeKey, isGranted);
}

/**
 * Apply permission to all children
 * ประกาศเป็น global function เพื่อให้เรียกจาก onclick ได้
 */
window.applyToChildren = function(parentMenuId) {
    console.log('applyToChildren called with parentMenuId:', parentMenuId);
    
    if (!confirm('คุณต้องการใช้สิทธิ์นี้กับเมนูย่อยทั้งหมดหรือไม่?')) {
        return;
    }
    
    // Get current permission value for parent
    const parentCheckbox = document.getElementById(`perm_${parentMenuId}`);
    if (!parentCheckbox) {
        console.error('Parent checkbox not found for menu ID:', parentMenuId);
        showError('ไม่พบข้อมูลเมนู');
        return;
    }
    
    const isGranted = parentCheckbox.checked;
    console.log('Parent permission:', isGranted);
    
    // Find parent menu in original menuTree (not flattened)
    function findMenuInTree(tree, id) {
        // แปลง id เป็น integer เพื่อเปรียบเทียบ
        const searchId = parseInt(id);
        
        for (let menu of tree) {
            const menuId = parseInt(menu.id);
            if (menuId === searchId) {
                return menu;
            }
            if (menu.children && menu.children.length > 0) {
                const found = findMenuInTree(menu.children, searchId);
                if (found) return found;
            }
        }
        return null;
    }
    
    // ตรวจสอบว่า menuTree โหลดเสร็จแล้วหรือยัง
    if (!menuTree || menuTree.length === 0) {
        console.error('menuTree is not loaded yet');
        showError('กรุณารอให้ข้อมูลเมนูโหลดเสร็จก่อน');
        return;
    }
    
    const searchId = parseInt(parentMenuId);
    console.log('Searching for menu ID:', searchId, 'Type:', typeof searchId);
    console.log('menuTree length:', menuTree.length);
    
    // หา menu จาก menuTree
    let parentMenu = findMenuInTree(menuTree, searchId);
    
    // ถ้าหาไม่เจอใน menuTree ลองหาใน flatMenus (ถ้ามี)
    if (!parentMenu && flatMenus && flatMenus.length > 0) {
        console.log('Not found in menuTree, searching in flatMenus...');
        parentMenu = flatMenus.find(m => parseInt(m.id) === searchId);
        if (parentMenu) {
            console.log('Found in flatMenus:', parentMenu);
        }
    }
    
    if (!parentMenu) {
        console.error('Parent menu not found for ID:', searchId);
        // Debug: แสดง menu IDs ที่มี
        const sampleIds = menuTree.slice(0, 5).map(m => ({id: m.id, type: typeof m.id, name: m.menu_name}));
        console.error('Sample menu IDs:', sampleIds);
        showError('ไม่พบเมนู (ID: ' + searchId + ')');
        return;
    }
    
    console.log('Parent menu found:', parentMenu);
    console.log('Parent menu children:', parentMenu.children);
    console.log('Parent menu children length:', parentMenu.children ? parentMenu.children.length : 0);
    
    // ถ้า parentMenu ไม่มี children ใน tree structure ให้หา children จาก flatMenus
    if (!parentMenu.children || parentMenu.children.length === 0) {
        // ลองหา children จาก flatMenus โดยดูที่ parent_id
        if (flatMenus && flatMenus.length > 0) {
            const childrenFromFlat = flatMenus.filter(m => {
                const menuParentId = m.parent_id ? parseInt(m.parent_id) : null;
                return menuParentId === searchId;
            });
            if (childrenFromFlat.length > 0) {
                console.log('Found children from flatMenus:', childrenFromFlat.length);
                parentMenu.children = childrenFromFlat;
            } else {
                console.log('No children found in flatMenus either');
                showError('ไม่พบเมนูย่อย');
                return;
            }
        } else {
            showError('ไม่พบเมนูย่อย');
            return;
        }
    }
    
    let updatedCount = 0;
    
    // Apply to all children recursively
    function applyToChildrenRecursive(children) {
        children.forEach(child => {
            console.log('Applying to child:', child.id, child.menu_name);
            
            // Update permission
            updatePermission(child.id, isGranted);
            updatedCount++;
            
            // Update checkbox in table
            const checkbox = document.getElementById(`perm_${child.id}`);
            if (checkbox) {
                checkbox.checked = isGranted;
                console.log('Checkbox updated for menu:', child.id);
            } else {
                console.warn(`Checkbox not found for menu ${child.id} (${child.menu_name})`);
            }
            
            // Apply to grandchildren recursively
            if (child.children && child.children.length > 0) {
                applyToChildrenRecursive(child.children);
            }
        });
    }
    
    // Apply to direct children
    applyToChildrenRecursive(parentMenu.children);
    console.log('Total children updated:', updatedCount);
    showSuccess(`ใช้สิทธิ์กับเมนูย่อยทั้งหมดแล้ว (${updatedCount} รายการ)`);
};

/**
 * Select all menus
 */
function selectAllMenus(isGranted) {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        const menuId = parseInt(checkbox.id.replace('perm_', ''));
        checkbox.checked = isGranted;
        updatePermission(menuId, isGranted);
    });
    
    showSuccess(`${isGranted ? 'เลือก' : 'ยกเลิก'}สิทธิ์ทั้งหมดแล้ว`);
}

/**
 * Save permissions
 */
function savePermissions() {
    if (!currentPositionId) {
        showError('กรุณาเลือก Position');
        return;
    }
    
    // Build permissions array - ส่งเฉพาะ checked menus
    // Backend จะจัดการสร้าง permission records สำหรับ unchecked menus (is_granted = false) เอง
    const permissions = [];
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    
    checkboxes.forEach(checkbox => {
        const menuId = parseInt(checkbox.id.replace('perm_', ''));
        const isGranted = checkbox.checked;
        
        if (isGranted) {
            permissions.push({
                menu_id: menuId,
                is_granted: true
            });
        }
    });
    
    // Send to API
    // Note: Backend จะสร้าง permission records สำหรับทุก menu
    // - checked menus: is_granted = true
    // - unchecked menus: is_granted = false (เพื่อซ่อนเมนู)
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=bulk-permissions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            position_id: currentPositionId,
            permissions: permissions
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showSuccess(data.message);
                permissionChanges = {};
                // Reload page to refresh sidebar
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกสิทธิ์');
            }
        })
        .catch(error => {
            console.error('Error saving permissions:', error);
            showError('เกิดข้อผิดพลาดในการบันทึกสิทธิ์');
        });
}

/**
 * Show copy permission modal
 */
function showCopyPermissionModal() {
    if (!currentPositionId) {
        showError('กรุณาเลือก Position เป้าหมายก่อน');
        return;
    }
    
    // Filter out current position from copy from dropdown
    const copyFromSelect = $('#copy-from-position');
    copyFromSelect.find('option').each(function() {
        const optionValue = $(this).val();
        if (optionValue && parseInt(optionValue) === currentPositionId) {
            $(this).prop('disabled', true);
        } else {
            $(this).prop('disabled', false);
        }
    });
    
    updateChoicesSelect('copy-from-position');
    $('#copy-permission-modal').modal('show');
}

/**
 * Copy permissions
 */
function copyPermissions() {
    // Get value using helper function
    const fromPositionId = getSelectValue('copy-from-position');
    
    if (!fromPositionId) {
        showError('กรุณาเลือก Position ที่ต้องการคัดลอก');
        return;
    }
    
    if (parseInt(fromPositionId) === currentPositionId) {
        showError('ไม่สามารถคัดลอกจาก Position เดียวกันได้');
        return;
    }
    
    // Confirm
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการคัดลอกสิทธิ์? สิทธิ์ปัจจุบันจะถูกแทนที่')) {
        return;
    }
    
    // Call API
    fetch(`${baseUrl}/controllers/user/menu_controller.php?action=copy-permissions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            from_position_id: parseInt(fromPositionId),
            to_position_id: currentPositionId
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showSuccess(data.message);
                $('#copy-permission-modal').modal('hide');
                loadPermissions(); // Reload to refresh
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการคัดลอกสิทธิ์');
            }
        })
        .catch(error => {
            console.error('Error copying permissions:', error);
            showError('เกิดข้อผิดพลาดในการคัดลอกสิทธิ์');
        });
}

/**
 * Update Choices.js select
 */
function updateChoicesSelect(selectId) {
    const selectElement = document.getElementById(selectId);
    if (!selectElement || typeof Choices === 'undefined') {
        return;
    }
    
    // Destroy existing instance if exists
    try {
        if (selectElement.choicesInstance) {
            selectElement.choicesInstance.destroy();
            selectElement.choicesInstance = null;
        }
        
        // Also check if Choices.js has wrapped the element
        const choicesElement = selectElement.closest('.choices');
        if (choicesElement && choicesElement !== selectElement) {
            // Restore original select
            const originalSelect = choicesElement.querySelector('select');
            if (originalSelect) {
                choicesElement.replaceWith(originalSelect);
            }
        }
    } catch (e) {
        console.warn('Error destroying Choices instance:', e);
    }
    
    // Wait a bit to ensure DOM is clean
    setTimeout(() => {
        try {
            const choicesInstance = new Choices(selectElement, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false
            });
            selectElement.choicesInstance = choicesInstance;
        } catch (e) {
            console.error('Error initializing Choices:', e);
        }
    }, 100);
}

/**
 * Get value from Choices.js select or regular select
 */
function getSelectValue(selectId) {
    const selectElement = document.getElementById(selectId);
    if (!selectElement) {
        return null;
    }
    
    // If Choices.js is initialized, use its API
    if (selectElement.choicesInstance) {
        try {
            // getValue() returns array for multiple, single value for single select
            const value = selectElement.choicesInstance.getValue(true);
            if (Array.isArray(value)) {
                return value.length > 0 ? value[0] : null;
            }
            return value || null;
        } catch (e) {
            console.warn('Error getting value from Choices:', e);
            // Fallback to jQuery
            return $('#' + selectId).val() || null;
        }
    }
    
    // Fallback to jQuery
    return $('#' + selectId).val() || null;
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
