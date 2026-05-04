/**
 * User List Management
 * ไฟล์ JavaScript สำหรับจัดการข้อมูลผู้ใช้งาน
 *
 * วันที่: 2025-01-27
 */

// Base URL สำหรับ API
const baseUrl =
  window.location.origin +
  window.location.pathname.replace('/pages/user-list.php', '');

// Global variables
let departments = [];
let positions = [];
let roles = [];

// SSR Pagination state
let allRows = []; // เก็บทุก TR ที่ render มาจาก PHP
let filteredRows = []; // เก็บ TR ที่ผ่านการ filter
let currentPage = 1;
let perPage = 10;

/**
 * Initialize page
 */
$(document).ready(function () {
  console.log('User List Page Initialized');

  // Scroll to top on page load
  window.scrollTo(0, 0);

  // Load dropdown data (for create/edit modal)
  loadDepartments();
  loadPositions();
  loadRoles();

  // SSR Pagination: เก็บ rows ทั้งหมดไว้ แล้วแสดงเฉพาะหน้าแรก
  allRows = $('#users-tbody tr[data-user-code]').toArray();
  filteredRows = allRows.slice();
  renderPage();

  // Event listeners
  $('#filter-search-btn').on('click', function () {
    filterTableSSR();
  });

  $('#filter-reset-btn').on('click', function () {
    resetFiltersSSR();
  });

  // Per-page selector
  $('#per-page-select').on('change', function () {
    perPage = parseInt($(this).val(), 10) || 10;
    currentPage = 1;
    renderPage();
  });

  // Pagination click
  $(document).on('click', '#pagination-controls a.page-link', function (e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'), 10);
    if (!isNaN(page)) {
      goToPage(page);
    }
  });

  // Create/Edit modal events
  $('#btn-create-user').on('click', function () {
    showCreateModal();
  });

  $('#btn-save-user').on('click', function () {
    saveUser();
  });

  // Modal close event - reset form
  $('#user-modal').on('hidden.bs.modal', function () {
    resetUserForm();
    editUserData = null; // Clear edit data
    // Destroy Choices instances
    ['user-depart', 'user-position', 'user-role'].forEach((selectId) => {
      const select = document.getElementById(selectId);
      if (
        select &&
        select.choicesInstance &&
        typeof select.choicesInstance.destroy === 'function'
      ) {
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
  $('#user-modal').on('shown.bs.modal', function () {
    // Wait a bit for DOM to be ready
    setTimeout(() => {
      if (editUserData) {
        // Edit mode: populate with selected values
        // Check both possible field names (user_depart_id and depart_id)
        const departId =
          editUserData.user_depart_id || editUserData.depart_id || null;
        const positionId =
          editUserData.user_position_id || editUserData.position_id || null;
        const roleId =
          editUserData.user_role_id || editUserData.role_id || null;

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
                positionSelect.choicesInstance.setChoiceByValue(
                  String(positionId)
                );
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
  $('#user-code, #user-fname, #user-lname, #user-email').on(
    'blur',
    function () {
      validateField($(this));
    }
  );

  // Enter key to submit
  $('#user-form').on('submit', function (e) {
    e.preventDefault();
    saveUser();
  });
});

/**
 * Load departments from API
 */
function loadDepartments() {
  return fetch(
    `${baseUrl}/controllers/user/depart_controller.php?action=list&page_size=9999`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data && data.status === 'success' && Array.isArray(data.data)) {
        departments = data.data;
      }
    })
    .catch((error) => {
      console.error('Error loading departments:', error);
    });
}

/**
 * Load positions from API
 */
function loadPositions() {
  return fetch(`${baseUrl}/controllers/user/position_controller.php`)
    .then((response) => response.json())
    .then((data) => {
      if (Array.isArray(data)) {
        positions = data;
      }
    })
    .catch((error) => {
      console.error('Error loading positions:', error);
    });
}

/**
 * Load roles from API
 */
function loadRoles() {
  return fetch(`${baseUrl}/controllers/user/role_controller.php`)
    .then((response) => response.json())
    .then((data) => {
      if (Array.isArray(data)) {
        roles = data;
      }
    })
    .catch((error) => {
      console.error('Error loading roles:', error);
    });
}

/**
 * SSR Filter: กรองข้อมูลจาก data attributes แล้ว render หน้าแรก
 */
function filterTableSSR() {
  const filterCode = $('#filter-user-code').val().trim().toLowerCase();
  const filterFname = $('#filter-user-fname').val().trim().toLowerCase();
  const filterLname = $('#filter-user-lname').val().trim().toLowerCase();
  const filterStatus = $('#filter-user-status').val();

  filteredRows = allRows.filter(function (tr) {
    const $row = $(tr);
    const code = ($row.data('user-code') || '').toString().toLowerCase();
    const fname = ($row.data('user-fname') || '').toString().toLowerCase();
    const lname = ($row.data('user-lname') || '').toString().toLowerCase();
    const status = ($row.data('user-status') || '').toString();

    if (filterCode && !code.includes(filterCode)) return false;
    if (filterFname && !fname.includes(filterFname)) return false;
    if (filterLname && !lname.includes(filterLname)) return false;
    if (filterStatus && status !== filterStatus) return false;
    return true;
  });

  currentPage = 1;
  renderPage();
}

/**
 * SSR Reset filters
 */
function resetFiltersSSR() {
  $('#filter-user-code').val('');
  $('#filter-user-fname').val('');
  $('#filter-user-lname').val('');
  $('#filter-user-status').val('');

  filteredRows = allRows.slice();
  currentPage = 1;
  renderPage();
}

/**
 * Render current page: show/hide rows + update numbering + pagination
 */
function renderPage() {
  const tbody = $('#users-tbody');
  tbody.empty();

  const totalRows = filteredRows.length;
  const totalPages = Math.max(1, Math.ceil(totalRows / perPage));

  if (currentPage > totalPages) currentPage = totalPages;

  if (totalRows === 0) {
    tbody.html('<tr><td colspan="9" class="text-center">ไม่พบข้อมูล</td></tr>');
    updatePaginationInfo(0, 0, 0);
    renderPaginationControls(0, 0);
    return;
  }

  const startIdx = (currentPage - 1) * perPage;
  const endIdx = Math.min(startIdx + perPage, totalRows);
  const pageRows = filteredRows.slice(startIdx, endIdx);

  pageRows.forEach(function (tr, i) {
    const $tr = $(tr).clone();
    $tr.find('td:first').text(startIdx + i + 1);
    $tr.show();
    tbody.append($tr);
  });

  updatePaginationInfo(startIdx + 1, endIdx, totalRows);
  renderPaginationControls(currentPage, totalPages);
}

/**
 * Go to specific page
 */
function goToPage(page) {
  const totalPages = Math.max(1, Math.ceil(filteredRows.length / perPage));
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  renderPage();
}

/**
 * Render pagination controls (matching investment-budget-list pattern)
 */
function renderPaginationControls(page, totalPages) {
  const controls = $('#pagination-controls');
  controls.empty();

  if (totalPages <= 1) return;

  function addPageItem(label, targetPage, disabled, active) {
    const liClass =
      'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
    const link = disabled
      ? `<a class="page-link" href="javascript:void(0);">${label}</a>`
      : `<a class="page-link" href="javascript:void(0);" data-page="${targetPage}">${label}</a>`;
    controls.append(`<li class="${liClass}">${link}</li>`);
  }

  // Previous
  addPageItem('ก่อนหน้า', page - 1, page <= 1, false);

  // Page numbers
  const maxVisible = 5;
  let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
  let endPage = Math.min(totalPages, startPage + maxVisible - 1);
  if (endPage - startPage + 1 < maxVisible) {
    startPage = Math.max(1, endPage - maxVisible + 1);
  }
  for (let p = startPage; p <= endPage; p++) {
    addPageItem(p.toString(), p, false, p === page);
  }

  // Next
  addPageItem('ถัดไป', page + 1, page >= totalPages, false);
}

/**
 * Reload page (after CRUD operations)
 */
function reloadPage() {
  window.location.reload();
}

/**
 * Get status badge
 */
function getStatusBadge(status) {
  switch (status) {
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
function updatePaginationInfo(start, end, total) {
  if (arguments.length === 1) {
    // backward compat: single arg = total
    $('#pagination-info').text(`กำลังแสดง ${start} รายการ`);
  } else if (total === 0) {
    $('#pagination-info').text('ไม่พบข้อมูล');
  } else {
    $('#pagination-info').text(`กำลังแสดง ${start}-${end} จาก ${total} รายการ`);
  }
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
    options.forEach((option) => {
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
              console.log(
                `Fallback: Set value directly for ${selectId}: ${selectedValue}`
              );
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
  if (
    selectElement.choicesInstance &&
    typeof selectElement.choicesInstance.getValue === 'function'
  ) {
    const value = selectElement.choicesInstance.getValue(true);
    return Array.isArray(value) ? value[0] || '' : value || '';
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
  departments.forEach((dept) => {
    departSelect.append(
      `<option value="${dept.id}">${escapeHtml(dept.depart_code || '')} - ${escapeHtml(dept.depart_name || '')}</option>`
    );
  });
  const departValue = selectedValues ? selectedValues.depart_id || null : null;
  console.log('Setting depart value:', departValue);
  updateChoicesSelect('user-depart', null, departValue);

  // Populate positions
  const positionSelect = $('#user-position');
  positionSelect.empty();
  positionSelect.append('<option value="">-- เลือกตำแหน่ง --</option>');
  positions.forEach((position) => {
    positionSelect.append(
      `<option value="${position.id}">${escapeHtml(position.position_code || '')} - ${escapeHtml(position.position_name || '')}</option>`
    );
  });
  const positionValue = selectedValues
    ? selectedValues.position_id || null
    : null;
  console.log('Setting position value:', positionValue);
  updateChoicesSelect('user-position', null, positionValue);

  // Populate roles
  const roleSelect = $('#user-role');
  roleSelect.empty();
  roleSelect.append('<option value="">-- เลือกบทบาท --</option>');
  roles.forEach((role) => {
    const roleName = role.position_name || role.role_name || '';
    roleSelect.append(
      `<option value="${role.id}">${escapeHtml(role.role_code || '')} - ${escapeHtml(roleName)}</option>`
    );
  });
  const roleValue = selectedValues ? selectedValues.role_id || null : null;
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
    .then((response) => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((user) => {
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
    .catch((error) => {
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
    .then((response) => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((user) => {
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
    .catch((error) => {
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
    .then((response) => response.json())
    .then((data) => {
      if (data.message && data.message.includes('successfully')) {
        showSuccess(data.message);
        reloadPage(); // Reload page to refresh SSR data
      } else {
        showError(data.message || 'ไม่สามารถลบผู้ใช้งานได้');
      }
    })
    .catch((error) => {
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
    .then((response) => response.json())
    .then((data) => {
      if (
        data.message &&
        (data.message.includes('successfully') ||
          data.message.includes('สำเร็จ'))
      ) {
        showSuccess(data.message);
        $('#user-modal').modal('hide');
        reloadPage(); // Reload page to refresh SSR data
      } else {
        showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกผู้ใช้งาน');
      }
    })
    .catch((error) => {
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
  return String(text).replace(/[&<>"']/g, (m) => map[m]);
}
