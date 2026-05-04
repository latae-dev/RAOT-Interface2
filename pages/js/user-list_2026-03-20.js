/**
 * User List Management
 * ไฟล์ JavaScript สำหรับจัดการข้อมูลผู้ใช้งาน
 * 
 * วันที่: 2025-01-27
 */

// Base URL สำหรับ API
const baseUrl = window.location.origin + window.location.pathname.replace('/pages/user-list.php', '');

// Global variables
let users = [];
let departments = [];
let positions = [];
let roles = [];
let filters = {
    user_code: '',
    user_fname: '',
    user_lname: '',
    user_status: ''
};

/**
 * Initialize page
 */
$(document).ready(function() {
    console.log('User List Page Initialized');
    
    // Scroll to top on page load
    window.scrollTo(0, 0);
    
    // Load dropdown data first
    Promise.all([
        loadDepartments(),
        loadPositions(),
        loadRoles()
    ]).then(() => {
        // Then load initial user data
        loadUsers();
    });
    
    // Event listeners
    $('#filter-search-btn').on('click', function() {
        applyFilters();
    });
    
    $('#filter-reset-btn').on('click', function() {
        resetFilters();
    });
    
    // Create/Edit modal events
    $('#btn-create-user').on('click', function() {
        showCreateModal();
    });
    
    $('#btn-save-user').on('click', function() {
        saveUser();
    });
    
    // Modal close event - reset form
    $('#user-modal').on('hidden.bs.modal', function() {
        resetUserForm();
        editUserData = null; // Clear edit data
        // Destroy Choices instances
        ['user-depart', 'user-position', 'user-role'].forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select && select.choicesInstance && typeof select.choicesInstance.destroy === 'function') {
                try {
                    select.choicesInstance.destroy();
                    select.choicesInstance = null;
                } catch (e) {
                    console.warn('Error destroying Choices instance:', e);
                }
            }
        });
    });
    
    // Initialize Choices.js when modal is shown
    $('#user-modal').on('shown.bs.modal', function() {
        // Wait a bit for DOM to be ready
        setTimeout(() => {
            if (editUserData) {
                // Edit mode: populate with selected values
                // Check both possible field names (user_depart_id and depart_id)
                const departId = editUserData.user_depart_id || editUserData.depart_id || null;
                const positionId = editUserData.user_position_id || editUserData.position_id || null;
                const roleId = editUserData.user_role_id || editUserData.role_id || null;
                
                console.log('Edit mode - Setting values:', {
                    depart_id: departId,
                    position_id: positionId,
                    role_id: roleId,
                    editUserData: editUserData
                });
                
                // Store selected values for later use
                const selectedValues = {
                    depart_id: departId,
                    position_id: positionId,
                    role_id: roleId
                };
                
                // Populate dropdowns first
                populateDropdowns(selectedValues);
                
                // Then set values after Choices.js is initialized (with delay)
                setTimeout(() => {
                    if (departId) {
                        const departSelect = document.getElementById('user-depart');
                        if (departSelect && departSelect.choicesInstance) {
                            try {
                                departSelect.choicesInstance.setChoiceByValue(String(departId));
                                console.log(`✅ Set depart value: ${departId}`);
                            } catch (e) {
                                console.warn('Error setting depart value:', e);
                                $('#user-depart').val(departId);
                            }
                        } else {
                            $('#user-depart').val(departId);
                        }
                    }
                    
                    if (positionId) {
                        const positionSelect = document.getElementById('user-position');
                        if (positionSelect && positionSelect.choicesInstance) {
                            try {
                                positionSelect.choicesInstance.setChoiceByValue(String(positionId));
                                console.log(`✅ Set position value: ${positionId}`);
                            } catch (e) {
                                console.warn('Error setting position value:', e);
                                $('#user-position').val(positionId);
                            }
                        } else {
                            $('#user-position').val(positionId);
                        }
                    }
                    
                    if (roleId) {
                        const roleSelect = document.getElementById('user-role');
                        if (roleSelect && roleSelect.choicesInstance) {
                            try {
                                roleSelect.choicesInstance.setChoiceByValue(String(roleId));
                                console.log(`✅ Set role value: ${roleId}`);
                            } catch (e) {
                                console.warn('Error setting role value:', e);
                                $('#user-role').val(roleId);
                            }
                        } else {
                            $('#user-role').val(roleId);
                        }
                    }
                }, 500);
                
                // Clear edit data after use
                editUserData = null;
            } else {
                // Create mode: populate without selected values
                populateDropdowns();
            }
        }, 100);
    });
    
    // Form validation on input
    $('#user-code, #user-fname, #user-lname, #user-email').on('blur', function() {
        validateField($(this));
    });
    
    // Enter key to submit
    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        saveUser();
    });
});

/**
 * Load departments from API
 */
function loadDepartments() {
    return fetch(`${baseUrl}/controllers/user/depart_controller.php?action=list`)
        .then(response => response.json())
        .then(data => {
            if (data && data.status === 'success' && Array.isArray(data.data)) {
                departments = data.data;
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
        });
}

/**
 * Load positions from API
 */
function loadPositions() {
    return fetch(`${baseUrl}/controllers/user/position_controller.php`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                positions = data;
            }
        })
        .catch(error => {
            console.error('Error loading positions:', error);
        });
}

/**
 * Load roles from API
 */
function loadRoles() {
    return fetch(`${baseUrl}/controllers/user/role_controller.php`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                roles = data;
            }
        })
        .catch(error => {
            console.error('Error loading roles:', error);
        });
}

/**
 * Load users from API
 */
function loadUsers() {
    console.log('Loading users...');
    
    // Show loading
    $('#users-tbody').html(`
        <tr>
            <td colspan="9" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="mt-2">กำลังโหลดข้อมูล...</p>
            </td>
        </tr>
    `);
    
    // Fetch users
    fetch(`${baseUrl}/controllers/user/user_controller.php?action=read_all`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.status === 'success' && Array.isArray(data.data)) {
                users = data.data;
                applyFilters();
            } else if (Array.isArray(data)) {
                users = data;
                applyFilters();
            } else {
                console.error('Invalid data format:', data);
                showError('ไม่สามารถโหลดข้อมูลผู้ใช้งานได้');
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
        });
}

/**
 * Apply filters
 */
function applyFilters() {
    // Get filter values
    filters.user_code = $('#filter-user-code').val().trim();
    filters.user_fname = $('#filter-user-fname').val().trim();
    filters.user_lname = $('#filter-user-lname').val().trim();
    filters.user_status = $('#filter-user-status').val();
    
    // Filter users
    let filteredUsers = users;
    
    if (filters.user_code) {
        filteredUsers = filteredUsers.filter(user => 
            (user.user_code || '').toLowerCase().includes(filters.user_code.toLowerCase())
        );
    }
    
    if (filters.user_fname) {
        filteredUsers = filteredUsers.filter(user => 
            (user.user_fname || '').toLowerCase().includes(filters.user_fname.toLowerCase())
        );
    }
    
    if (filters.user_lname) {
        filteredUsers = filteredUsers.filter(user => 
            (user.user_lname || '').toLowerCase().includes(filters.user_lname.toLowerCase())
        );
    }
    
    if (filters.user_status) {
        filteredUsers = filteredUsers.filter(user => 
            (user.user_status || '') === filters.user_status
        );
    }
    
    // Render table
    renderUsersTable(filteredUsers);
    
    // Update pagination info
    updatePaginationInfo(filteredUsers.length);
}

/**
 * Reset filters
 */
function resetFilters() {
    $('#filter-user-code').val('');
    $('#filter-user-fname').val('');
    $('#filter-user-lname').val('');
    $('#filter-user-status').val('');
    
    // Reload all users
    applyFilters();
}

/**
 * Render users table
 */
function renderUsersTable(usersList) {
    const tbody = $('#users-tbody');
    tbody.empty();
    
    if (usersList.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
            </tr>
        `);
        return;
    }
    
    usersList.forEach((user, index) => {
        const row = createUserRow(user, index + 1);
        tbody.append(row);
    });
}

/**
 * Create user table row
 */
function createUserRow(user, index) {
    const formattedCreatedAt = formatDate(user.created_at);
    const statusBadge = getStatusBadge(user.user_status || 'pending');
    const fullName = `${escapeHtml(user.user_fname || '')} ${escapeHtml(user.user_lname || '')}`.trim();
    
    return `
        <tr>
            <td class="text-center">${index}</td>
            <td><strong>${escapeHtml(user.user_code || '')}</strong></td>
            <td>${fullName || '-'}</td>
            <td>${escapeHtml(user.user_email || '-')}</td>
            <td>${escapeHtml(user.position_name || '-')}</td>
            <td>${escapeHtml(user.depart_name || '-')}</td>
            <td class="text-center">${statusBadge}</td>
            <td class="text-center">${formattedCreatedAt}</td>
            <td class="text-center">
                <div class="hstack gap-2 fs-15 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="viewUser(${user.id})" title="ดูรายละเอียด">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick="editUser(${user.id})" title="แก้ไข">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deleteUser(${user.id})" title="ลบ">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Get status badge
 */
function getStatusBadge(status) {
    switch(status) {
        case 'active':
            return '<span class="badge bg-success">ใช้งาน</span>';
        case 'pending':
            return '<span class="badge bg-warning">รออนุมัติ</span>';
        case 'inactive':
            return '<span class="badge bg-secondary">ไม่ใช้งาน</span>';
        default:
            return '<span class="badge bg-secondary">-</span>';
    }
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
    $('#user-modal-label').text('สร้างผู้ใช้งานใหม่');
    $('#user-id').val('');
    resetUserForm();
    $('#user-modal').modal('show');
    // populateDropdowns() will be called when modal is shown
}

/**
 * Update Choices.js select element
 */
function updateChoicesSelect(selectId, options = null, selectedValue = null) {
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
    } catch (e) {
        console.warn('Error destroying Choices instance:', e);
    }
    
    // Populate options if provided
    if (options && Array.isArray(options)) {
        const $select = $(selectElement);
        $select.empty();
        options.forEach(option => {
            $select.append(option);
        });
    }
    
    // Set value on select element first (before Choices.js initialization)
    if (selectedValue !== null && selectedValue !== '') {
        selectElement.value = String(selectedValue);
    }
    
    // Initialize Choices.js with delay to ensure DOM is ready
    setTimeout(() => {
        try {
            const choicesInstance = new Choices(selectElement, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                removeItemButton: false,
                placeholder: true,
                placeholderValue: 'กรุณาเลือก',
                searchPlaceholderValue: 'ค้นหา...',
                noResultsText: 'ไม่พบข้อมูล',
                noChoicesText: 'ไม่มีตัวเลือก'
            });
            selectElement.choicesInstance = choicesInstance;
            
            // Set selected value after Choices.js is initialized
            if (selectedValue !== null && selectedValue !== '') {
                console.log(`Setting value for ${selectId}: ${selectedValue}`);
                // Use setTimeout to ensure Choices.js is fully ready
                setTimeout(() => {
                    try {
                        choicesInstance.setChoiceByValue(String(selectedValue));
                        console.log(`✅ Set value for ${selectId}: ${selectedValue}`);
                    } catch (e) {
                        console.warn(`Error setting Choices value for ${selectId}:`, e);
                        // Fallback: set value directly
                        try {
                            selectElement.value = String(selectedValue);
                            $(selectElement).trigger('change');
                            console.log(`Fallback: Set value directly for ${selectId}: ${selectedValue}`);
                        } catch (fallbackError) {
                            console.error(`Fallback failed for ${selectId}:`, fallbackError);
                        }
                    }
                }, 300);
            }
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
        return '';
    }
    
    // If Choices.js is initialized, use its method
    if (selectElement.choicesInstance && typeof selectElement.choicesInstance.getValue === 'function') {
        const value = selectElement.choicesInstance.getValue(true);
        return Array.isArray(value) ? (value[0] || '') : (value || '');
    }
    
    // Otherwise, use standard select value
    return $(selectElement).val() || '';
}

/**
 * Populate dropdowns
 * @param {Object} selectedValues - Object with selected values {depart_id, position_id, role_id}
 */
function populateDropdowns(selectedValues = null) {
    console.log('Populating dropdowns with selectedValues:', selectedValues);
    
    // Populate departments
    const departSelect = $('#user-depart');
    departSelect.empty();
    departSelect.append('<option value="">-- เลือกหน่วยงาน --</option>');
    departments.forEach(dept => {
        departSelect.append(`<option value="${dept.id}">${escapeHtml(dept.depart_code || '')} - ${escapeHtml(dept.depart_name || '')}</option>`);
    });
    const departValue = selectedValues ? (selectedValues.depart_id || null) : null;
    console.log('Setting depart value:', departValue);
    updateChoicesSelect('user-depart', null, departValue);
    
    // Populate positions
    const positionSelect = $('#user-position');
    positionSelect.empty();
    positionSelect.append('<option value="">-- เลือกตำแหน่ง --</option>');
    positions.forEach(position => {
        positionSelect.append(`<option value="${position.id}">${escapeHtml(position.position_code || '')} - ${escapeHtml(position.position_name || '')}</option>`);
    });
    const positionValue = selectedValues ? (selectedValues.position_id || null) : null;
    console.log('Setting position value:', positionValue);
    updateChoicesSelect('user-position', null, positionValue);
    
    // Populate roles
    const roleSelect = $('#user-role');
    roleSelect.empty();
    roleSelect.append('<option value="">-- เลือกบทบาท --</option>');
    roles.forEach(role => {
        const roleName = role.position_name || role.role_name || '';
        roleSelect.append(`<option value="${role.id}">${escapeHtml(role.role_code || '')} - ${escapeHtml(roleName)}</option>`);
    });
    const roleValue = selectedValues ? (selectedValues.role_id || null) : null;
    console.log('Setting role value:', roleValue);
    updateChoicesSelect('user-role', null, roleValue);
}

/**
 * View user
 */
function viewUser(id) {
    console.log('Viewing user ID:', id);
    
    // Show loading state
    $('#user-view-content').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
        </div>
    `);
    
    // Show modal
    $('#user-view-modal').modal('show');
    
    fetch(`${baseUrl}/controllers/user/user_controller.php/${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(user => {
            console.log('User data:', user);
            if (user && user.user_id) {
                renderUserView(user);
            } else if (user && user.message) {
                $('#user-view-content').html(`
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-error-warning-line me-2"></i>${escapeHtml(user.message)}
                    </div>
                `);
            } else {
                $('#user-view-content').html(`
                    <div class="alert alert-warning" role="alert">
                        <i class="ri-information-line me-2"></i>ไม่พบข้อมูลผู้ใช้งาน
                    </div>
                `);
            }
        })
        .catch(error => {
            console.error('Error viewing user:', error);
            $('#user-view-content').html(`
                <div class="alert alert-danger" role="alert">
                    <i class="ri-error-warning-line me-2"></i>เกิดข้อผิดพลาดในการดูข้อมูลผู้ใช้งาน: ${escapeHtml(error.message)}
                </div>
            `);
        });
}

/**
 * Render user view content
 */
function renderUserView(user) {
    const statusBadge = getStatusBadge(user.user_status || 'pending');
    
    const html = `
        <div class="row g-3">
            <!-- ข้อมูลพื้นฐาน -->
            <div class="col-12">
                <h6 class="mb-3"><i class="ri-information-line me-2"></i>ข้อมูลพื้นฐาน</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">รหัสผู้ใช้งาน</th>
                                <td><strong>${escapeHtml(user.user_code || '-')}</strong></td>
                            </tr>
                            <tr>
                                <th>ชื่อ</th>
                                <td>${escapeHtml(user.user_fname || '-')}</td>
                            </tr>
                            <tr>
                                <th>นามสกุล</th>
                                <td>${escapeHtml(user.user_lname || '-')}</td>
                            </tr>
                            <tr>
                                <th>อีเมล</th>
                                <td>${escapeHtml(user.user_email || '-')}</td>
                            </tr>
                            <tr>
                                <th>สถานะ</th>
                                <td>${statusBadge}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ข้อมูลการเชื่อมโยง -->
            <div class="col-12">
                <h6 class="mb-3"><i class="ri-links-line me-2"></i>ข้อมูลการเชื่อมโยง</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>หน่วยงาน</th>
                                <td>${escapeHtml(user.depart_code || '-')} ${user.depart_name ? '- ' + escapeHtml(user.depart_name) : ''}</td>
                            </tr>
                            <tr>
                                <th>ตำแหน่ง</th>
                                <td>${escapeHtml(user.position_code || '-')} ${user.position_name ? '- ' + escapeHtml(user.position_name) : ''}</td>
                            </tr>
                            <tr>
                                <th>บทบาท</th>
                                <td>${escapeHtml(user.role_code || '-')} ${user.role_name ? '- ' + escapeHtml(user.role_name) : ''}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#user-view-content').html(html);
}

// Store user data for edit mode
let editUserData = null;

/**
 * Edit user
 */
function editUser(id) {
    console.log('Editing user ID:', id);
    fetch(`${baseUrl}/controllers/user/user_controller.php/${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(user => {
            console.log('User data:', user);
            if (user && user.user_id) {
                // Store user data for later use
                editUserData = user;
                
                $('#user-modal-label').text('แก้ไขผู้ใช้งาน');
                $('#user-id').val(user.user_id);
                $('#user-code').val(user.user_code || '');
                $('#user-pass').val(''); // Don't show password
                $('#user-fname').val(user.user_fname || '');
                $('#user-lname').val(user.user_lname || '');
                $('#user-email').val(user.user_email || '');
                $('#user-status').val(user.user_status || 'pending');
                
                // Show modal first, then populate dropdowns when modal is shown
                $('#user-modal').modal('show');
            } else if (user && user.message) {
                showError(user.message);
            } else {
                showError('ไม่พบข้อมูลผู้ใช้งาน');
            }
        })
        .catch(error => {
            console.error('Error loading user:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้งาน: ' + error.message);
        });
}

/**
 * Delete user
 */
function deleteUser(id) {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้งานนี้?')) {
        return;
    }
    
    fetch(`${baseUrl}/controllers/user/user_controller.php/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.message && data.message.includes('successfully')) {
                showSuccess(data.message);
                loadUsers(); // Reload users
            } else {
                showError(data.message || 'ไม่สามารถลบผู้ใช้งานได้');
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            showError('เกิดข้อผิดพลาดในการลบผู้ใช้งาน');
        });
}

/**
 * Save user (create or update)
 */
function saveUser() {
    // Validate form
    if (!validateUserForm()) {
        return;
    }
    
    const userId = $('#user-id').val();
    const isUpdate = userId !== '';
    
    const userData = {
        user_code: $('#user-code').val().trim(),
        user_pass: $('#user-pass').val().trim(),
        user_fname: $('#user-fname').val().trim(),
        user_lname: $('#user-lname').val().trim(),
        user_email: $('#user-email').val().trim(),
        user_status: $('#user-status').val(),
        branch_id: null,
        depart_id: getSelectValue('user-depart') || null,
        role_id: getSelectValue('user-role') || null,
        position_id: getSelectValue('user-position') || null
    };
    
    // If updating and password is empty, don't send it
    if (isUpdate && !userData.user_pass) {
        delete userData.user_pass;
    }
    
    const url = `${baseUrl}/controllers/user/user_controller.php${isUpdate ? '/' + userId : ''}`;
    const method = isUpdate ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.message && (data.message.includes('successfully') || data.message.includes('สำเร็จ'))) {
                showSuccess(data.message);
                $('#user-modal').modal('hide');
                loadUsers(); // Reload users
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกผู้ใช้งาน');
            }
        })
        .catch(error => {
            console.error('Error saving user:', error);
            showError('เกิดข้อผิดพลาดในการบันทึกผู้ใช้งาน');
        });
}

/**
 * Validate user form
 */
function validateUserForm() {
    let isValid = true;
    
    // Reset validation
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Validate user code
    const userCode = $('#user-code').val().trim();
    if (!userCode) {
        setFieldError('#user-code', 'กรุณากรอกรหัสผู้ใช้งาน');
        isValid = false;
    }
    
    // Validate password (only for create)
    const userId = $('#user-id').val();
    if (!userId) {
        const userPass = $('#user-pass').val().trim();
        if (!userPass) {
            setFieldError('#user-pass', 'กรุณากรอกรหัสผ่าน');
            isValid = false;
        }
    }
    
    // Validate first name
    const userFname = $('#user-fname').val().trim();
    if (!userFname) {
        setFieldError('#user-fname', 'กรุณากรอกชื่อ');
        isValid = false;
    }
    
    // Validate last name
    const userLname = $('#user-lname').val().trim();
    if (!userLname) {
        setFieldError('#user-lname', 'กรุณากรอกนามสกุล');
        isValid = false;
    }
    
    // Validate email
    const userEmail = $('#user-email').val().trim();
    if (!userEmail) {
        setFieldError('#user-email', 'กรุณากรอกอีเมล');
        isValid = false;
    } else if (!isValidEmail(userEmail)) {
        setFieldError('#user-email', 'รูปแบบอีเมลไม่ถูกต้อง');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
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
    
    if (fieldId === 'user-code' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกรหัสผู้ใช้งาน');
        return false;
    }
    
    if (fieldId === 'user-email' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกอีเมล');
        return false;
    } else if (fieldId === 'user-email' && value && !isValidEmail(value)) {
        setFieldError('#' + fieldId, 'รูปแบบอีเมลไม่ถูกต้อง');
        return false;
    }
    
    return true;
}

/**
 * Reset user form
 */
function resetUserForm() {
    $('#user-form')[0].reset();
    $('#user-id').val('');
    $('#user-status').val('pending');
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

/**
 * Format date
 */
function formatDate(dateString) {
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
    } catch (e) {
        return dateString;
    }
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
