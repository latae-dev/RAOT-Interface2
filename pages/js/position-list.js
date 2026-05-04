/**
 * Position List Management
 * ไฟล์ JavaScript สำหรับจัดการข้อมูลตำแหน่ง
 * 
 * วันที่: 2025-01-27
 */

// Base URL สำหรับ API
const baseUrl = window.location.origin + window.location.pathname.replace('/pages/position-list.php', '');

// Global variables
let positions = [];
let filters = {
    position_code: '',
    position_name: ''
};

/**
 * Initialize page
 */
$(document).ready(function() {
    console.log('Position List Page Initialized');
    
    // Scroll to top on page load
    window.scrollTo(0, 0);
    
    // Load initial data
    loadPositions();
    
    // Event listeners
    $('#filter-search-btn').on('click', function() {
        applyFilters();
    });
    
    $('#filter-reset-btn').on('click', function() {
        resetFilters();
    });
    
    // Create/Edit modal events
    $('#btn-create-position').on('click', function() {
        showCreateModal();
    });
    
    $('#btn-save-position').on('click', function() {
        savePosition();
    });
    
    // Modal close event - reset form
    $('#position-modal').on('hidden.bs.modal', function() {
        resetPositionForm();
    });
    
    // Form validation on input
    $('#position-code, #position-name').on('blur', function() {
        validateField($(this));
    });
    
    // Enter key to submit
    $('#position-form').on('submit', function(e) {
        e.preventDefault();
        savePosition();
    });
});

/**
 * Load positions from API
 */
function loadPositions() {
    console.log('Loading positions...');
    
    // Show loading
    $('#positions-tbody').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="mt-2">กำลังโหลดข้อมูล...</p>
            </td>
        </tr>
    `);
    
    // Fetch positions
    fetch(`${baseUrl}/controllers/user/position_controller.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                positions = data;
                applyFilters();
            } else {
                console.error('Invalid data format:', data);
                showError('ไม่สามารถโหลดข้อมูลตำแหน่งได้');
            }
        })
        .catch(error => {
            console.error('Error loading positions:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
        });
}

/**
 * Apply filters
 */
function applyFilters() {
    // Get filter values
    filters.position_code = $('#filter-position-code').val().trim();
    filters.position_name = $('#filter-position-name').val().trim();
    
    // Filter positions
    let filteredPositions = positions;
    
    if (filters.position_code) {
        filteredPositions = filteredPositions.filter(position => 
            (position.position_code || '').toLowerCase().includes(filters.position_code.toLowerCase())
        );
    }
    
    if (filters.position_name) {
        filteredPositions = filteredPositions.filter(position => 
            (position.position_name || '').toLowerCase().includes(filters.position_name.toLowerCase())
        );
    }
    
    // Render table
    renderPositionsTable(filteredPositions);
    
    // Update pagination info
    updatePaginationInfo(filteredPositions.length);
}

/**
 * Reset filters
 */
function resetFilters() {
    $('#filter-position-code').val('');
    $('#filter-position-name').val('');
    
    // Reload all positions
    applyFilters();
}

/**
 * Render positions table
 */
function renderPositionsTable(positionsList) {
    const tbody = $('#positions-tbody');
    tbody.empty();
    
    if (positionsList.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="7" class="text-center">ไม่พบข้อมูล</td>
            </tr>
        `);
        return;
    }
    
    positionsList.forEach((position, index) => {
        const row = createPositionRow(position, index + 1);
        tbody.append(row);
    });
}

/**
 * Create position table row
 */
function createPositionRow(position, index) {
    const formattedCreatedAt = formatDate(position.created_at);
    const formattedUpdatedAt = formatDate(position.updated_at);
    
    // สร้าง badge สำหรับสิทธิ์การอนุมัติ
    const approveBadges = getApproveBadges(position);
    
    return `
        <tr>
            <td class="text-center">${index}</td>
            <td><strong>${escapeHtml(position.position_code || '')}</strong></td>
            <td>${escapeHtml(position.position_name || '')}</td>
            <td>
                <div class="d-flex flex-wrap gap-1">
                    ${approveBadges}
                </div>
            </td>
            <td class="text-center">${formattedCreatedAt}</td>
            <td class="text-center">${formattedUpdatedAt}</td>
            <td class="text-center">
                <div class="hstack gap-2 fs-15 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="viewPosition(${position.id})" title="ดูรายละเอียด">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick="editPosition(${position.id})" title="แก้ไข">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deletePosition(${position.id})" title="ลบ">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Get approve badges for position
 */
function getApproveBadges(position) {
    const approveFields = [
        { key: 'approve_branch', label: 'สาขา' },
        { key: 'approve_province', label: 'จังหวัด' },
        { key: 'approve_airea', label: 'เขต' },
        { key: 'approve_head_office', label: 'สำนักงานใหญ่' },
        { key: 'approve_finance', label: 'การเงิน' },
        { key: 'approve_payment', label: 'การจ่าย' },
        { key: 'approve_area', label: 'พื้นที่' },
        { key: 'approve_operating_primary', label: 'งบทำการ(หลัก)' },
        { key: 'approve_operating_strategic', label: 'งบทำการ(ยุทธศาสตร์)' },
        { key: 'approve_investment', label: 'งบลงทุน' },
        { key: 'approve_pacels', label: 'พัสดุ' },
        { key: 'approve_proposal', label: 'ใบขอเสนอ' }
    ];
    
    let badges = [];
    approveFields.forEach(field => {
        const value = position[field.key] || 'nonactive';
        if (value === 'active') {
            badges.push(`<span class="badge bg-success" title="${field.label}">${field.label}</span>`);
        }
    });
    
    // ถ้าไม่มี badge ที่ active เลย ให้แสดง "ไม่มีสิทธิ์"
    if (badges.length === 0) {
        return '<span class="badge bg-secondary">ไม่มีสิทธิ์</span>';
    }
    
    return badges.join('');
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
    $('#position-modal-label').text('สร้างตำแหน่งใหม่');
    $('#position-id').val('');
    resetPositionForm();
    $('#position-modal').modal('show');
}

/**
 * View position
 */
function viewPosition(id) {
    console.log('Viewing position ID:', id);
    
    // Show loading state
    $('#position-view-content').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
        </div>
    `);
    
    // Show modal
    $('#position-view-modal').modal('show');
    
    fetch(`${baseUrl}/controllers/user/position_controller.php/${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(position => {
            console.log('Position data:', position);
            if (position && position.id) {
                // Render position details in modal
                renderPositionView(position);
            } else if (position && position.message) {
                $('#position-view-content').html(`
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-error-warning-line me-2"></i>${escapeHtml(position.message)}
                    </div>
                `);
            } else {
                $('#position-view-content').html(`
                    <div class="alert alert-warning" role="alert">
                        <i class="ri-information-line me-2"></i>ไม่พบข้อมูลตำแหน่ง
                    </div>
                `);
            }
        })
        .catch(error => {
            console.error('Error viewing position:', error);
            $('#position-view-content').html(`
                <div class="alert alert-danger" role="alert">
                    <i class="ri-error-warning-line me-2"></i>เกิดข้อผิดพลาดในการดูข้อมูลตำแหน่ง: ${escapeHtml(error.message)}
                </div>
            `);
        });
}

/**
 * Render position view content
 */
function renderPositionView(position) {
    const getStatusBadge = (status) => {
        if (status === 'active') {
            return '<span class="badge bg-success">ใช้งาน</span>';
        } else {
            return '<span class="badge bg-secondary">ไม่ใช้งาน</span>';
        }
    };
    
    const html = `
        <div class="row g-3">
            <!-- ข้อมูลพื้นฐาน -->
            <div class="col-12">
                <h6 class="mb-3"><i class="ri-information-line me-2"></i>ข้อมูลพื้นฐาน</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">รหัสตำแหน่ง</th>
                                <td><strong>${escapeHtml(position.position_code || '-')}</strong></td>
                            </tr>
                            <tr>
                                <th>ชื่อตำแหน่ง</th>
                                <td>${escapeHtml(position.position_name || '-')}</td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>${formatDate(position.created_at)}</td>
                            </tr>
                            <tr>
                                <th>วันที่อัปเดต</th>
                                <td>${formatDate(position.updated_at)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- สิทธิ์การอนุมัติ -->
            <div class="col-12">
                <h6 class="mb-3"><i class="ri-shield-check-line me-2"></i>สิทธิ์การอนุมัติ</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;">ประเภทการอนุมัติ</th>
                                <th style="width: 50%;" class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>อนุมัติสาขา</td>
                                <td class="text-center">${getStatusBadge(position.approve_branch || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติจังหวัด</td>
                                <td class="text-center">${getStatusBadge(position.approve_province || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติเขต</td>
                                <td class="text-center">${getStatusBadge(position.approve_airea || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติสำนักงานใหญ่</td>
                                <td class="text-center">${getStatusBadge(position.approve_head_office || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติการเงิน</td>
                                <td class="text-center">${getStatusBadge(position.approve_finance || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติการจ่าย</td>
                                <td class="text-center">${getStatusBadge(position.approve_payment || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติพื้นที่</td>
                                <td class="text-center">${getStatusBadge(position.approve_area || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติงบทำการ (ผู้รับผิดชอบหลัก)</td>
                                <td class="text-center">${getStatusBadge(position.approve_operating_primary || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติงบทำการ (ยุทธศาสตร์)</td>
                                <td class="text-center">${getStatusBadge(position.approve_operating_strategic || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติงบลงทุน</td>
                                <td class="text-center">${getStatusBadge(position.approve_investment || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติพัสดุ</td>
                                <td class="text-center">${getStatusBadge(position.approve_pacels || 'nonactive')}</td>
                            </tr>
                            <tr>
                                <td>อนุมัติใบขอเสนอ</td>
                                <td class="text-center">${getStatusBadge(position.approve_proposal || 'nonactive')}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#position-view-content').html(html);
}

/**
 * Edit position
 */
function editPosition(id) {
    console.log('Editing position ID:', id);
    fetch(`${baseUrl}/controllers/user/position_controller.php/${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(position => {
            console.log('Position data:', position);
            if (position && position.id) {
                $('#position-modal-label').text('แก้ไขตำแหน่ง');
                $('#position-id').val(position.id);
                $('#position-code').val(position.position_code || '');
                $('#position-name').val(position.position_name || '');
                
                // Set approve fields
                $('#approve-branch').val(position.approve_branch || 'nonactive');
                $('#approve-province').val(position.approve_province || 'nonactive');
                $('#approve-airea').val(position.approve_airea || 'nonactive');
                $('#approve-head-office').val(position.approve_head_office || 'nonactive');
                $('#approve-finance').val(position.approve_finance || 'nonactive');
                $('#approve-payment').val(position.approve_payment || 'nonactive');
                $('#approve-area').val(position.approve_area || 'nonactive');
                $('#approve-operating-primary').val(position.approve_operating_primary || 'nonactive');
                $('#approve-operating-strategic').val(position.approve_operating_strategic || 'nonactive');
                $('#approve-investment').val(position.approve_investment || 'nonactive');
                $('#approve-pacels').val(position.approve_pacels || 'nonactive');
                $('#approve-proposal').val(position.approve_proposal || 'nonactive');
                
                $('#position-modal').modal('show');
            } else if (position && position.message) {
                showError(position.message);
            } else {
                showError('ไม่พบข้อมูลตำแหน่ง');
            }
        })
        .catch(error => {
            console.error('Error loading position:', error);
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลตำแหน่ง: ' + error.message);
        });
}

/**
 * Delete position
 */
function deletePosition(id) {
    if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบตำแหน่งนี้?\n\nหมายเหตุ: ไม่สามารถลบตำแหน่งที่มีการใช้งานอยู่ได้')) {
        return;
    }
    
    fetch(`${baseUrl}/controllers/user/position_controller.php/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.message && data.message.includes('successfully')) {
                showSuccess(data.message);
                loadPositions(); // Reload positions
            } else {
                showError(data.message || 'ไม่สามารถลบตำแหน่งได้ อาจมีการใช้งานอยู่');
            }
        })
        .catch(error => {
            console.error('Error deleting position:', error);
            showError('เกิดข้อผิดพลาดในการลบตำแหน่ง');
        });
}

/**
 * Save position (create or update)
 */
function savePosition() {
    // Validate form
    if (!validatePositionForm()) {
        return;
    }
    
    const positionId = $('#position-id').val();
    const isUpdate = positionId !== '';
    
    const positionData = {
        position_code: $('#position-code').val().trim(),
        position_name: $('#position-name').val().trim(),
        approve_branch: $('#approve-branch').val() || 'nonactive',
        approve_province: $('#approve-province').val() || 'nonactive',
        approve_airea: $('#approve-airea').val() || 'nonactive',
        approve_head_office: $('#approve-head-office').val() || 'nonactive',
        approve_finance: $('#approve-finance').val() || 'nonactive',
        approve_payment: $('#approve-payment').val() || 'nonactive',
        approve_area: $('#approve-area').val() || 'nonactive',
        approve_operating_primary: $('#approve-operating-primary').val() || 'nonactive',
        approve_operating_strategic: $('#approve-operating-strategic').val() || 'nonactive',
        approve_investment: $('#approve-investment').val() || 'nonactive',
        approve_pacels: $('#approve-pacels').val() || 'nonactive',
        approve_proposal: $('#approve-proposal').val() || 'nonactive'
    };
    
    const url = `${baseUrl}/controllers/user/position_controller.php${isUpdate ? '/' + positionId : ''}`;
    const method = isUpdate ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(positionData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.message && (data.message.includes('successfully') || data.message.includes('สำเร็จ'))) {
                showSuccess(data.message);
                $('#position-modal').modal('hide');
                loadPositions(); // Reload positions
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกตำแหน่ง');
            }
        })
        .catch(error => {
            console.error('Error saving position:', error);
            showError('เกิดข้อผิดพลาดในการบันทึกตำแหน่ง');
        });
}

/**
 * Validate position form
 */
function validatePositionForm() {
    let isValid = true;
    
    // Reset validation
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Validate position code
    const positionCode = $('#position-code').val().trim();
    if (!positionCode) {
        setFieldError('#position-code', 'กรุณากรอกรหัสตำแหน่ง');
        isValid = false;
    }
    
    // Validate position name
    const positionName = $('#position-name').val().trim();
    if (!positionName) {
        setFieldError('#position-name', 'กรุณากรอกชื่อตำแหน่ง');
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
    
    if (fieldId === 'position-code' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกรหัสตำแหน่ง');
        return false;
    }
    
    if (fieldId === 'position-name' && !value) {
        setFieldError('#' + fieldId, 'กรุณากรอกชื่อตำแหน่ง');
        return false;
    }
    
    return true;
}

/**
 * Reset position form
 */
function resetPositionForm() {
    $('#position-form')[0].reset();
    $('#position-id').val('');
    
    // Reset approve fields to default
    $('#approve-branch').val('nonactive');
    $('#approve-province').val('nonactive');
    $('#approve-airea').val('nonactive');
    $('#approve-head-office').val('nonactive');
    $('#approve-finance').val('nonactive');
    $('#approve-payment').val('nonactive');
    $('#approve-area').val('nonactive');
    $('#approve-operating-primary').val('nonactive');
    $('#approve-operating-strategic').val('nonactive');
    $('#approve-investment').val('nonactive');
    $('#approve-pacels').val('nonactive');
    $('#approve-proposal').val('nonactive');
    
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
