/**
 * Request Proposal List JavaScript
 * จัดการหน้าแสดงรายการคำขอสร้างใบขอเสนอ
 */

$(document).ready(function() {
    
    // ========================================
    // ตัวแปรและค่าคงที่
    // ========================================
    
    let proposalsData = [];
    
    const basePath = window.location.origin + window.location.pathname.replace(/\/pages\/.*/, '');
    const apiUrl = basePath + '/controllers/proposal/request_proposal_controller.php';
    
    // ค่าคงที่สำหรับการแสดงผล
    const ITEMS_PER_PAGE = 10;
    const DATE_FORMAT = "d-m-Y";
    
    // Mapping สำหรับการแสดงผล
    const STATUS_MAP = {
        'pending': '<span class="badge rounded-pill bg-warning">รออนุมัติ</span>',
        'approved': '<span class="badge rounded-pill bg-success">อนุมัติ</span>',
        'rejected': '<span class="badge rounded-pill bg-danger">ปฏิเสธ</span>',
        'deleted': '<span class="badge rounded-pill bg-secondary">ลบแล้ว</span>'
    };
    
    const ACCOUNT_CATEGORY_MAP = {
        'A': 'สินทรัพย์',
        'K': 'ศูนย์ต้นทุน',
        'N': 'ไม่เลือก'
    };
    
    const FORM_TYPE_MAP = {
        '1': 'คำขอสร้างใบขอเสนอจ้าง',
        '2': 'คำขอสร้างใบขอเสนอเช่า',
        '3': 'คำขอสร้างใบขอเสนอซื้อ'
    };
    
    const STATUS_TEXT_MAP = {
        'approved': 'อนุมัติ',
        'pending': 'รออนุมัติ',
        'rejected': 'ปฏิเสธ'
    };
    
    const SAP_STATUS_MAP = {
        'not_synced': '<span class="badge rounded-pill bg-warning">ยังไม่ได้ Sync</span>',
        'synced': '<span class="badge rounded-pill bg-success">Sync แล้ว</span>'
    };
    
    const SAP_STATUS_TEXT_MAP = {
        'not_synced': 'ยังไม่ได้ Sync',
        'synced': 'Sync แล้ว'
    };
    
    // ========================================
    // การเริ่มต้น
    // ========================================
    
    /**
     * เริ่มต้นหน้า
     */
    function initializePage() {
        // ตรวจสอบข้อมูลผู้ใช้เมื่อเริ่มต้น
        const currentUser = getCurrentUser();
        
        loadProposals();
        setupEventHandlers();
        initializeDatePickers();
        initializeSelect2();
    }
    
    // เริ่มต้นหน้า
    initializePage();
    
    // ========================================
    // การโหลดข้อมูล
    // ========================================
    
    /**
     * โหลดข้อมูลคำขอทั้งหมด
     */
    async function loadProposals() {
        showLoading();
        
        try {
            const currentUser = getCurrentUser();
            const response = await fetch(apiUrl + '?action=get_with_details_all&current_user=' + encodeURIComponent(currentUser), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
            }
            
            const result = await response.json();
            hideLoading();
            
            if (result.status === 'success') {
                proposalsData = result.data;
                renderDataTable(proposalsData);
            } else {
                showError('ไม่สามารถโหลดข้อมูลได้: ' + result.message);
            }
        } catch (error) {
            hideLoading();
            handleError(error, 'การโหลดข้อมูล');
        }
    }
    
    // ========================================
    // การแสดงผลตาราง
    // ========================================
    
    /**
     * แสดงตารางข้อมูล
     */
    function renderDataTable(data) {
        // ล้างข้อมูลในตาราง
        $('#proposalsTable tbody').empty();
        $('#simple-pagination').parent().remove();
        $('.search-result-message').remove();
        
        if (data && data.length > 0) {
            data.forEach((proposal, index) => {
                const row = createTableRow(proposal, index + 1);
                $('#proposalsTable tbody').append(row);
            });
        } else {
            $('#proposalsTable tbody').append(`
                <tr>
                    <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
                </tr>
            `);
            updateShowingInfo(0, 0, 0);
        }
        
        initializeDataTable();
    }
    
    /**
     * สร้างแถวในตาราง
     */
    function createTableRow(proposal, index) {
        const statusBadge = getStatusBadge(proposal.status);
        const sapStatusBadge = getSapStatusBadge(proposal.sap_status);
        const accountCategory = getAccountCategoryText(proposal.account_category);
        const createdDate = formatDate(proposal.created_at);
        
        return `
            <tr class="crm-contact text-center">
                <td>${index}</td>
                <td>${createdDate}</td>
                <td>${proposal.proposal_number || '-'}</td>
                <td>${proposal.form_type_name || '-'}</td>
                <td>${proposal.header_note || '-'}</td>
                <td>${accountCategory}</td>
                <td>${proposal.created_by || '-'}</td>
                <td>${statusBadge}</td>
                <td>${sapStatusBadge}</td>
            </tr>
        `;
    }
    
    // ฟังก์ชัน getActionButtons ถูกลบออกเนื่องจากไม่ใช้ปุ่มการจัดการแล้ว
    
    // ========================================
    // การจัดการ Pagination
    // ========================================
    
    /**
     * เริ่มต้นตาราง
     */
    function initializeDataTable() {
        $('#proposalsTable').addClass('table table-bordered table-striped table-hover');
        addSimplePagination();
    }
    
    /**
     * เพิ่ม pagination ธรรมดา
     */
    function addSimplePagination() {
        const table = $('#proposalsTable');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr');
        const totalPages = Math.ceil(rows.length / ITEMS_PER_PAGE);
        
        if (totalPages <= 1) return;
        
        // ซ่อนแถวทั้งหมด
        rows.hide();
        rows.slice(0, ITEMS_PER_PAGE).show();
        
        // อัปเดตข้อมูลการแสดงผล
        updateShowingInfo(1, Math.min(ITEMS_PER_PAGE, rows.length), rows.length);
        
        // สร้าง pagination
        const pagination = $(`
            <div class="d-flex justify-content-between align-items-center mt-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0" id="simple-pagination">
                    </ul>
                </nav>
            </div>
        `);
        
        table.after(pagination);
        
        // สร้างปุ่ม pagination
        const paginationUl = $('#simple-pagination');
        for (let i = 1; i <= totalPages; i++) {
            const li = $(`<li class="page-item ${i === 1 ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`);
            paginationUl.append(li);
        }
        
        // Event handler สำหรับ pagination
        paginationUl.on('click', 'a', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            showPage(page, rows, paginationUl);
        });
    }
    
    /**
     * แสดงหน้าตามหมายเลข
     */
    function showPage(page, rows, paginationUl) {
        const start = (page - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;
        
        // ซ่อนแถวทั้งหมด
        rows.hide();
        rows.slice(start, end).show();
        
        // อัปเดต pagination
        paginationUl.find('.page-item').removeClass('active');
        paginationUl.find(`a[data-page="${page}"]`).parent().addClass('active');
        
        // อัปเดตข้อมูลการแสดงผล
        updateShowingInfo(start + 1, Math.min(end, rows.length), rows.length);
    }
    
    // ========================================
    // การค้นหาและกรอง
    // ========================================
    
    /**
     * ค้นหาข้อมูล
     */
    async function searchProposals() {
        const filters = {
            keyword: $('#input-label11').val().trim(),
            form_type_id: $('#form-type-filter').val() ? parseInt($('#form-type-filter').val()) : '',
            account_category: $('#account-category-filter').val(),
            status: $('#status-filter').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val()
        };
        
        showLoading();
        
        try {
            const currentUser = getCurrentUser();
            const queryParams = new URLSearchParams({ action: 'search' });
            
            // เพิ่ม current_user
            queryParams.append('current_user', currentUser);
            
            // เพิ่ม parameters เฉพาะที่มีค่า
            Object.keys(filters).forEach(key => {
                if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
                    // ตรวจสอบว่าเป็น string ก่อนเรียก .trim()
                    if (typeof filters[key] === 'string' && filters[key].trim() !== '') {
                        queryParams.append(key, filters[key]);
                    } else if (typeof filters[key] !== 'string') {
                        // สำหรับ non-string values (เช่น number)
                        queryParams.append(key, filters[key]);
                    }
                }
            });
            
            const response = await fetch(apiUrl + '?' + queryParams.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
            }
            
            const result = await response.json();
            hideLoading();
            
            if (result.status === 'success') {
                proposalsData = result.data;
                renderDataTable(proposalsData);
                showSearchResult(proposalsData, filters);
            } else {
                showError('ไม่สามารถค้นหาข้อมูลได้: ' + result.message);
            }
        } catch (error) {
            hideLoading();
            handleError(error, 'การค้นหา');
        }
    }
    
    /**
     * ล้างการกรอง
     */
    function resetFilters() {
        $('#input-label11').val('');
        $('#form-type-filter').val('').trigger('change');
        $('#account-category-filter').val('').trigger('change');
        $('#status-filter').val('').trigger('change');
        $('#date-from').val('');
        $('#date-to').val('');
        
        $('.search-result-message').remove();
        loadProposals();
    }
    
    /**
     * แสดงผลการค้นหา
     */
    function showSearchResult(data, filters) {
        const searchSummary = generateSearchSummary(filters, data.length);
        
        $('#proposalsTable').before(`
            <div class="search-result-message alert alert-info">
                ${searchSummary}
            </div>
        `);
        
        if (data && data.length > 0) {
            updateShowingInfo(1, Math.min(ITEMS_PER_PAGE, data.length), data.length);
        } else {
            updateShowingInfo(0, 0, 0);
        }
    }
    
    /**
     * สร้างข้อความสรุปการค้นหา
     */
    function generateSearchSummary(filters, resultCount) {
        let summary = `ผลการค้นหา: พบ ${resultCount} รายการ`;
        const activeFilters = [];
        
        if (filters.keyword) {
            activeFilters.push(`คำค้นหา: "${filters.keyword}"`);
        }
        if (filters.form_type_id) {
            activeFilters.push(`ประเภทเอกสาร: ${getFormTypeText(filters.form_type_id)}`);
        }
        if (filters.account_category) {
            activeFilters.push(`หมวดบัญชี: ${getAccountCategoryText(filters.account_category)}`);
        }
        if (filters.status) {
            activeFilters.push(`สถานะ: ${getStatusText(filters.status)}`);
        }
        if (filters.date_from || filters.date_to) {
            const dateRange = `${filters.date_from || 'ไม่ระบุ'} ถึง ${filters.date_to || 'ไม่ระบุ'}`;
            activeFilters.push(`ช่วงวันที่: ${dateRange}`);
        }
        
        if (activeFilters.length > 0) {
            summary += ` | เงื่อนไข: ${activeFilters.join(', ')}`;
        }
        
        return summary;
    }
    
    // ========================================
    // การตั้งค่า Event Handlers
    // ========================================
    
    /**
     * ตั้งค่า Event Handlers
     */
    function setupEventHandlers() {
        // ปุ่มค้นหา
        $('#searchBtn').on('click', searchProposals);
        
        // ปุ่มล้างการค้นหา
        $('#resetBtn').on('click', resetFilters);
        
        // ปุ่มนำข้อมูลออก
        $('.btn-success').on('click', function(e) {
            if ($(this).text().includes('นำข้อมูลออก')) {
                e.preventDefault();
                exportToExcel();
            }
        });
        
        // Enter key ในช่องค้นหา
        $('#input-label11').on('keypress', function(e) {
            if (e.which === 13) {
                searchProposals();
            }
        });
    }
    
    // ========================================
    // การเริ่มต้น UI Components
    // ========================================
    
    /**
     * เริ่มต้น Date Picker
     */
    function initializeDatePickers() {
        flatpickr("#date-from", {
            dateFormat: DATE_FORMAT,
            locale: "th"
        });
        
        flatpickr("#date-to", {
            dateFormat: DATE_FORMAT,
            locale: "th"
        });
    }
    
    /**
     * เริ่มต้น Select2
     */
    function initializeSelect2() {
        $('#form-type-filter, #account-category-filter, #status-filter').select2({
            placeholder: "กรุณาเลือก",
            allowClear: true
        });
    }
    
    // ========================================
    // ฟังก์ชันช่วยเหลือ
    // ========================================
    
    /**
     * แสดงสถานะ
     */
    function getStatusBadge(status) {
        return STATUS_MAP[status] || '<span class="badge rounded-pill bg-secondary">ไม่ระบุ</span>';
    }
    
    /**
     * แสดงหมวดการกำหนดบัญชี
     */
    function getAccountCategoryText(category) {
        return ACCOUNT_CATEGORY_MAP[category] || 'ไม่ระบุ';
    }
    
    /**
     * แปลงรหัสประเภทเอกสารเป็นข้อความ
     */
    function getFormTypeText(formTypeId) {
        return FORM_TYPE_MAP[formTypeId] || 'ไม่ระบุ';
    }
    
    /**
     * แปลงรหัสสถานะเป็นข้อความ
     */
    function getStatusText(status) {
        return STATUS_TEXT_MAP[status] || 'ไม่ระบุ';
    }
    
    /**
     * แสดงสถานะ SAP
     */
    function getSapStatusBadge(sapStatus) {
        return SAP_STATUS_MAP[sapStatus] || '<span class="badge rounded-pill bg-secondary">ไม่ระบุ</span>';
    }
    
    /**
     * แปลงรหัสสถานะ SAP เป็นข้อความ
     */
    function getSapStatusText(sapStatus) {
        return SAP_STATUS_TEXT_MAP[sapStatus] || 'ไม่ระบุ';
    }
    
    /**
     * จัดรูปแบบวันที่
     */
    function formatDate(dateString) {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear() + 543; // แปลงเป็น พ.ศ.
        
        return `${day}-${month}-${year}`;
    }
    
    /**
     * อัปเดตข้อมูลการแสดงผล
     */
    function updateShowingInfo(start, end, total) {
        $('#showing-start').text(start);
        $('#showing-end').text(end);
        $('#total-items').text(total);
    }
    
    /**
     * ดึงข้อมูลผู้ใช้ปัจจุบัน
     */
    function getCurrentUser() {
        // ลองหาข้อมูลผู้ใช้จากหลายแหล่ง
        
        // 1. ลองจาก sessionStorage raot_user_session (จากระบบ login)
        const raotUserSession = sessionStorage.getItem('raot_user_session');
        if (raotUserSession) {
            try {
                const userData = JSON.parse(raotUserSession);
                if (userData.user_code) {
                    return userData.user_code;
                }
                if (userData.username) {
                    return userData.username;
                }
                if (userData.name) {
                    return userData.name;
                }
            } catch (e) {
                
            }
        }
        
        // 2. ลองจาก localStorage current_user
        const currentUserData = localStorage.getItem('current_user');
        if (currentUserData) {
            try {
                const userData = JSON.parse(currentUserData);
                if (userData.username) {
                    return userData.username;
                }
                if (userData.user_code) {
                    return userData.user_code;
                }
            } catch (e) {
                
            }
        }
        
        // 3. ลองจาก localStorage user_data
        const userData = localStorage.getItem('user_data');
        if (userData) {
            try {
                const userDataObj = JSON.parse(userData);
                if (userDataObj.username) {
                    return userDataObj.username;
                }
                if (userDataObj.user_code) {
                    return userDataObj.user_code;
                }
            } catch (e) {
                
            }
        }
        
        // 4. ลองจาก sessionStorage current_user
        const sessionUser = sessionStorage.getItem('current_user');
        if (sessionUser) {
            try {
                const sessionUserData = JSON.parse(sessionUser);
                if (sessionUserData.username) {
                    return sessionUserData.username;
                }
                if (sessionUserData.user_code) {
                    return sessionUserData.user_code;
                }
            } catch (e) {
                
            }
        }
        
        // 5. ลองดึงจาก cookie หรือ global variable
        if (typeof window.currentUser !== 'undefined' && window.currentUser) {
            return window.currentUser;
        }
        
        return 'system';
    }
    
    // ========================================
    // การจัดการ UI (Loading, Messages)
    // ========================================
    
    /**
     * แสดงการโหลด
     */
    function showLoading() {
        if (!$('#loadingSpinner').length) {
            $('body').append(`
                <div id="loadingSpinner" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
            `);
        }
    }
    
    /**
     * ซ่อนการโหลด
     */
    function hideLoading() {
        $('#loadingSpinner').remove();
    }
    
    /**
     * แสดงข้อผิดพลาด
     */
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message,
            confirmButtonText: 'ตกลง'
        });
    }
    
    /**
     * แสดงความสำเร็จ
     */
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
            confirmButtonText: 'ตกลง'
        });
    }
    
    /**
     * จัดการ Error แบบรวม
     */
    function handleError(error, context) {
        
        
        let errorMessage = `เกิดข้อผิดพลาดใน${context}`;
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            errorMessage = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต';
        } else if (error.message.includes('404')) {
            errorMessage = 'ไม่พบ API endpoint ที่ระบุ';
        } else if (error.message.includes('500')) {
            errorMessage = 'เกิดข้อผิดพลาดในเซิร์ฟเวอร์ กรุณาลองใหม่อีกครั้ง';
        } else {
            errorMessage += ': ' + error.message;
        }
        
        showError(errorMessage);
    }
    
    // ========================================
    // ฟังก์ชันสำหรับการจัดการข้อมูล
    // ========================================
    
    /**
     * ดูรายละเอียดคำขอ (ไม่จำเป็นแล้ว - ใช้ link โดยตรง)
     */
    
    // ฟังก์ชันลบข้อมูลถูกลบออกเนื่องจากเป็นหน้าแสดงผลอย่างเดียว
    
    // ฟังก์ชันอัปเดตสถานะถูกลบออกเนื่องจากเป็นหน้าแสดงผลอย่างเดียว
    
    // ========================================
    // ฟังก์ชันการนำข้อมูลออก
    // ========================================
    
    /**
     * นำข้อมูลออกเป็น Excel
     */
    function exportToExcel() {
        if (!proposalsData || proposalsData.length === 0) {
            showError('ไม่มีข้อมูลให้นำออก');
            return;
        }
        
        showLoading();
        
        try {
            // สร้างข้อมูลสำหรับ Excel
            const excelData = prepareExcelData(proposalsData);
            
            // สร้างไฟล์ Excel
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(excelData);
            
            // กำหนดความกว้างของคอลัมน์
            const columnWidths = [
                { wch: 8 },   // ลำดับ
                { wch: 15 },  // วันที่ดำเนินรายการ
                { wch: 25 },  // รหัสรายการคำขอสร้างใบขอเสนอ
                { wch: 30 },  // ประเภทเอกสาร
                { wch: 40 },  // บันทึกส่วนหัว
                { wch: 20 },  // หมวดการกำหนดบัญชี
                { wch: 20 },  // ผู้ขออนุมัติ
                { wch: 15 },  // สถานะการส่งอนุมัติ
                { wch: 20 }   // สถานะ Interface SAP
            ];
            worksheet['!cols'] = columnWidths;
            
            // เพิ่ม worksheet ลงใน workbook
            XLSX.utils.book_append_sheet(workbook, worksheet, 'รายงานคำขอ_SAP');
            
            // สร้างชื่อไฟล์
            const fileName = `รายงานคำขอสร้างใบขอเสนอ_SAP_${getCurrentDate()}.xlsx`;
            
            // บันทึกไฟล์
            XLSX.writeFile(workbook, fileName);
            
            hideLoading();
            showSuccess('นำข้อมูลออกสำเร็จ');
            
        } catch (error) {
            hideLoading();
            handleError(error, 'การนำข้อมูลออก');
        }
    }
    
    /**
     * เตรียมข้อมูลสำหรับ Excel
     */
    function prepareExcelData(data) {
        return data.map((proposal, index) => ({
            'ลำดับ': index + 1,
            'วันที่ดำเนินรายการ': formatDate(proposal.created_at),
            'รหัสรายการคำขอสร้างใบขอเสนอ': proposal.proposal_number || '-',
            'ประเภทเอกสาร': proposal.form_type_name || '-',
            'บันทึกส่วนหัว': proposal.header_note || '-',
            'หมวดการกำหนดบัญชี': getAccountCategoryText(proposal.account_category),
            'ผู้ขออนุมัติ': proposal.created_by || '-',
            'สถานะการส่งอนุมัติ': getStatusText(proposal.status),
            'สถานะ Interface SAP': getSapStatusText(proposal.sap_status)
        }));
    }
    
    /**
     * ดึงวันที่ปัจจุบันสำหรับชื่อไฟล์
     */
    function getCurrentDate() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        return `${day}${month}${year}_${hours}${minutes}`;
    }
    
});