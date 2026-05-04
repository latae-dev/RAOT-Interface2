(function (window, document) {
  /**
   * Investment Budget List Manager
   * จัดการรายการคำขอตั้งงบลงทุน
   */
  class InvestmentBudgetListManager {
    constructor() {
      this.requests = [];
      this.meta = {
        total: 0,
        page: 1,
        per_page: 10,
        total_pages: 1
      };
      this.currentPage = 1;
      this.perPage = 10;
      this.filters = {
        requestNumber: '',
        status: '',
        startDate: '',
        endDate: ''
      };
      this.isLoading = false;
      this.startPicker = null;
      this.endPicker = null;
    }

    getUserContextHeaders() {
      const sessionData = sessionStorage.getItem('raot_user_session');
      if (!sessionData) {
        return {};
      }

      const headers = {};
      const encoded = this.encodeUserContext(sessionData);

      headers['X-User-Context'] = encoded.value;
      if (encoded.encoding) {
        headers['X-User-Context-Encoding'] = encoded.encoding;
      }

      return headers;
    }

    encodeUserContext(rawValue) {
      if (typeof rawValue !== 'string') {
        return { value: '' };
      }

      const requiresEncoding = /[^\x00-\x7F]/.test(rawValue);
      if (!requiresEncoding) {
        return { value: rawValue };
      }

      try {
        const utf8 = encodeURIComponent(rawValue).replace(
          /%([0-9A-F]{2})/g,
          (_, p1) => String.fromCharCode(parseInt(p1, 16))
        );
        const base64 = window.btoa(utf8);
        return { value: base64, encoding: 'base64' };
      } catch (error) {
        console.warn(
          'ไม่สามารถเข้ารหัสแบบ Base64 ได้ จะใช้การเข้ารหัสแบบ URL แทน',
          error
        );
      }

      return { value: encodeURIComponent(rawValue), encoding: 'url' };
    }

    async initialize() {
      console.log('📋 เริ่มต้นระบบรายการคำขอตั้งงบลงทุน...');

      if (!this.checkUserSession()) {
        return;
      }

      this.initDatePickers();
      this.updateFiltersFromInputs();
      await this.loadRequests();
      this.setupEventListeners();
    }

    initDatePickers() {
      if (typeof setupThaiBEFlatpickr !== 'function') {
        console.warn('setupThaiBEFlatpickr helper ไม่พร้อมใช้งาน');
        return;
      }

      // Start Date picker with minDate/maxDate logic
      const startPickerObj = setupThaiBEFlatpickr('#filter-start-date', {
        onChange: [
          (selectedDates) => {
            if (this.endPicker && this.endPicker.instance) {
              if (selectedDates.length) {
                this.endPicker.instance.set('minDate', selectedDates[0]);
              } else {
                this.endPicker.instance.set('minDate', null);
              }
            }
          }
        ]
      });

      // End Date picker with minDate/maxDate logic
      const endPickerObj = setupThaiBEFlatpickr('#filter-end-date', {
        onChange: [
          (selectedDates) => {
            if (this.startPicker && this.startPicker.instance) {
              if (selectedDates.length) {
                this.startPicker.instance.set('maxDate', selectedDates[0]);
              } else {
                this.startPicker.instance.set('maxDate', null);
              }
            }
          }
        ]
      });

      this.startPicker = startPickerObj;
      this.endPicker = endPickerObj;
    }

    updateFiltersFromInputs() {
      const requestNumberInput = document.getElementById(
        'filter-request-number'
      );
      const statusSelect = document.getElementById('filter-status');
      const startDateInput = document.getElementById('filter-start-date');
      const endDateInput = document.getElementById('filter-end-date');

      this.filters.requestNumber = requestNumberInput
        ? requestNumberInput.value.trim()
        : '';
      this.filters.status = statusSelect ? statusSelect.value : '';
      this.filters.startDate = startDateInput ? startDateInput.value : '';
      this.filters.endDate = endDateInput ? endDateInput.value : '';

      if (
        this.filters.startDate &&
        this.filters.endDate &&
        this.filters.startDate > this.filters.endDate
      ) {
        const temp = this.filters.startDate;
        this.filters.startDate = this.filters.endDate;
        this.filters.endDate = temp;
      }
    }

    async loadRequests() {
      this.isLoading = true;
      this.showLoadingState();

      try {
        const url = this.buildRequestUrl();
        const headers = this.getUserContextHeaders();
        const response = await fetch(url, { headers });
        const data = await response.json();

        if (data.status === 'success') {
          const payload = data.data;

          if (Array.isArray(payload)) {
            this.requests = payload;
            this.meta = {
              total: payload.length,
              page: 1,
              per_page: payload.length || this.perPage,
              total_pages: 1
            };
          } else {
            this.requests = payload?.items || [];
            const meta = payload?.meta || {};
            this.meta = {
              total: Math.max(0, meta.total ?? this.requests.length),
              page: Math.max(1, meta.page ?? this.currentPage),
              per_page: Math.max(1, meta.per_page ?? this.perPage),
              total_pages: Math.max(1, meta.total_pages ?? 1)
            };
          }

          this.currentPage = this.meta.page;
          this.perPage = this.meta.per_page;

          console.log('✅ โหลดข้อมูลสำเร็จ:', this.meta.total, 'รายการ');
          this.renderRequestsTable();
          this.renderPagination();
        } else {
          console.error('❌ ไม่สามารถโหลดข้อมูลได้:', data.message);
          this.requests = [];
          this.meta = {
            total: 0,
            page: this.currentPage,
            per_page: this.perPage,
            total_pages: 1
          };
          this.showErrorMessage(data.message || 'ไม่สามารถโหลดข้อมูลได้');
          this.renderPagination();
        }
      } catch (error) {
        console.error('❌ เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);
        this.requests = [];
        this.meta = {
          total: 0,
          page: this.currentPage,
          per_page: this.perPage,
          total_pages: 1
        };
        this.showErrorMessage('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        this.renderPagination();
      } finally {
        this.isLoading = false;
      }
    }

    buildRequestUrl() {
      const params = new URLSearchParams();
      params.append('page', this.currentPage);
      params.append('per_page', this.perPage);

      if (this.filters.requestNumber) {
        params.append('request_number', this.filters.requestNumber);
      }
      if (this.filters.status) {
        params.append('status', this.filters.status);
      }
      if (this.filters.startDate) {
        params.append('start_date', this.filters.startDate);
      }
      if (this.filters.endDate) {
        params.append('end_date', this.filters.endDate);
      }

      const query = params.toString();
      return `../controllers/investment_budget/ib_requests_controller.php?action=all${
        query ? '&' + query : ''
      }`;
    }

    checkUserSession() {
      const sessionData = sessionStorage.getItem('raot_user_session');
      if (!sessionData) {
        console.error('❌ ไม่พบข้อมูล session - กรุณา login ใหม่');
        alert('Session หมดอายุ กรุณา login ใหม่');
        window.location.href = '../pages/login.php';
        return false;
      }

      try {
        const userData = JSON.parse(sessionData);
        if (!userData.id && !userData.user_id) {
          console.error('❌ ข้อมูล session ไม่ถูกต้อง');
          alert('Session ไม่ถูกต้อง กรุณา login ใหม่');
          window.location.href = '../pages/login.php';
          return false;
        }
        console.log(
          '✅ พบ session ของ user:',
          userData.user_fname,
          userData.user_lname
        );
        return true;
      } catch (e) {
        console.error('❌ ไม่สามารถอ่านข้อมูล session ได้:', e);
        return false;
      }
    }

    showLoadingState() {
      const tbody = document.getElementById('investment-requests-tbody');
      if (tbody) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p>
                    </td>
                </tr>
            `;
      }

      const infoElement = document.getElementById('pagination-info');
      if (infoElement) {
        infoElement.textContent = 'กำลังโหลดข้อมูล...';
      }
    }

    renderRequestsTable() {
      const tbody = document.getElementById('investment-requests-tbody');
      if (!tbody) return;

      if (!this.requests.length) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center">
                        <p class="mb-0">ไม่พบข้อมูลคำขอตั้งงบลงทุน</p>
                    </td>
                </tr>
            `;
        this.updatePaginationInfo();
        return;
      }

      const startIndex = (this.meta.page - 1) * this.meta.per_page;
      const rows = this.requests
        .map((request, index) =>
          this.createTableRow(request, startIndex + index + 1)
        )
        .join('');

      tbody.innerHTML = rows;
      this.updatePaginationInfo();
    }

    createTableRow(request, rowNumber) {
      const createdDate = this.formatDate(request.created_at);
      const statusBadge = this.getStatusBadge(request);
      const budgetApprovalBadge = this.getBudgetApprovalBadge(request);
      const actionButtons = this.getActionButtons(request);

      const sectionLabel =
        request.budget_source_name_full ||
        request.section_name ||
        request.budget_source_name ||
        request.budget_source_code ||
        '-';

      return `
            <tr class="crm-contact text-center">
                <td>${rowNumber}</td>
                <td>${createdDate}</td>
                <td>${request.request_number || '-'}</td>
                <td>${sectionLabel}</td>
                <td>${request.asset_type_name || '-'}</td>
                <td>${this.getFirstItemName(request)}</td>
                <td>${this.getFirstItemQuantity(request)}</td>
                <td>${this.formatUnitPrice(request)}</td>
                <td>${this.formatTotalAmount(request)}</td>
                <td>${request.department_name || '-'}</td>
                <td>${statusBadge}</td>
                <td>${budgetApprovalBadge}</td>
                <td>${actionButtons}</td>
            </tr>
        `;
    }

    formatDate(dateString) {
      if (!dateString) return '-';

      const date = new Date(dateString);
      if (Number.isNaN(date.getTime())) {
        return '-';
      }

      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear() + 543;

      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      const seconds = date.getSeconds().toString().padStart(2, '0');

      return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
    }

    getStatusBadge(request) {
      const rawStatus = (request.status || '').toLowerCase();

      if (!rawStatus) {
        return '<span class="badge rounded-pill bg-secondary">-</span>';
      }

      if (rawStatus.startsWith('pending_')) {
        const stage = rawStatus.replace('pending_', '');
        const label = this.getStageLabel(stage);
        const text = label ? `รออนุมัติ (${label})` : 'รออนุมัติ';
        return `<span class="badge rounded-pill bg-info text-white">${text}</span>${this.buildReturnBadge(request)}`;
      }

      const statusConfig = {
        draft: {
          class: 'bg-primary',
          text: 'บันทึกร่าง'
        },
        submitted: {
          class: 'bg-warning',
          text: 'รออนุมัติ'
        },
        approved: {
          class: 'bg-success',
          text: 'อนุมัติแล้ว'
        },
        rejected: {
          class: 'bg-danger',
          text: 'ปฏิเสธ'
        }
      };

      const config = statusConfig[rawStatus] || {
        class: 'bg-secondary',
        text: rawStatus || '-'
      };
      return `<span class="badge rounded-pill ${config.class}">${config.text}</span>${this.buildReturnBadge(request)}`;
    }

    getStageLabel(stage) {
      switch (stage) {
        case 'branch':
          return 'ระดับสาขา';
        case 'province':
          return 'ระดับจังหวัด/กอง';
        case 'area':
          return 'ระดับเขต/ฝ่าย';
        case 'head_office':
          return 'ระดับสำนักงานใหญ่';
        default:
          return '';
      }
    }

    getBudgetApprovalBadge(request) {
      const rawStatus = (request.status || '').toLowerCase();
      const budgetApprovalStatus = (request.budget_approval_status || '').toLowerCase();

      console.log('🔍 Budget Approval Badge:', {
        requestNumber: request.request_number,
        status: rawStatus,
        budgetApprovalStatus: budgetApprovalStatus
      });

      // ถ้าสำนักงานใหญ่ยังไม่อนุมัติ (status ไม่ใช่ approved)
      if (rawStatus !== 'approved') {
        return '<span class="text-muted">-</span>';
      }

      // ถ้าสำนักงานใหญ่อนุมัติแล้ว แต่งบประมาณยังไม่พิจารณา (pending)
      if (!budgetApprovalStatus || budgetApprovalStatus === 'pending') {
        return '<span class="badge rounded-pill bg-warning text-dark">รอพิจารณางบประมาณ</span>';
      }

      // ถ้างบประมาณอนุมัติแล้ว
      if (budgetApprovalStatus === 'approved') {
        return '<span class="badge rounded-pill bg-success">อนุมัติ</span>';
      }

      // กรณีอื่นๆ
      return '<span class="text-muted">-</span>';
    }

    buildReturnBadge(request) {
      if (!this.isTruthyFlag(request?.is_returned)) {
        return '';
      }
      const stage = request?.returned_from_stage || '';
      const label = this.getStageLabel(stage) || 'หน่วยงานก่อนหน้า';
      return `<span class="badge rounded-pill bg-danger text-white ms-1">ส่งกลับจาก ${label}</span>`;
    }

    isTruthyFlag(value) {
      if (typeof value === 'boolean') {
        return value;
      }
      if (value === null || value === undefined) {
        return false;
      }
      const normalized = String(value).trim().toLowerCase();
      if (normalized === '') {
        return false;
      }
      return ['1', 'true', 't', 'yes', 'y'].includes(normalized);
    }

    getActionButtons(request) {
      let buttons = `
            <div class="hstack gap-2 fs-15">
                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" onclick="viewRequest(${request.id})">
                    <i class="fe fe-eye"></i>
                </a>
        `;

      if (request.status === 'draft' || request.status === 'rejected') {
        buttons += `
                <a href="investment-budget-form.php?id=${request.id}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill">
                    <i class="ri-edit-line"></i>
                </a>
                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="deleteRequest(${request.id})">
                    <i class="ri-delete-bin-line"></i>
                </a>
            `;
      }

      buttons += '</div>';
      return buttons;
    }

    getFirstItemName(request) {
      return request.item_name || '-';
    }

    getFirstItemQuantity(request) {
      if (request.quantity === null || request.quantity === undefined) {
        return '-';
      }
      const quantity = parseInt(request.quantity, 10);
      if (Number.isNaN(quantity) || quantity === 0) {
        return '-';
      }
      return quantity.toString();
    }

    getFirstItemUnitPrice(request) {
      if (request.unit_price === null || request.unit_price === undefined) {
        return '-';
      }
      const price = parseFloat(request.unit_price);
      if (Number.isNaN(price) || price === 0) {
        return '-';
      }
      return price;
    }

    formatUnitPrice(request) {
      const price = this.getFirstItemUnitPrice(request);
      if (price === '-') {
        return '-';
      }
      return this.formatCurrency(price);
    }

    formatTotalAmount(request) {
      if (request.total_amount === null || request.total_amount === undefined) {
        return '-';
      }
      const amount = parseFloat(request.total_amount);
      if (Number.isNaN(amount) || amount === 0) {
        return '-';
      }
      return this.formatCurrency(amount);
    }

    formatCurrency(amount) {
      // Check for null/undefined first before converting to number
      if (amount === null || amount === undefined || amount === '') {
        return '-';
      }
      const value = Number(amount);
      if (Number.isNaN(value) || value === 0) {
        return '-';
      }

      return new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value);
    }

    showErrorMessage(message) {
      const tbody = document.getElementById('investment-requests-tbody');
      if (tbody) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center text-danger">
                        <i class="ri-error-warning-line fs-18 mb-2"></i>
                        <p class="mb-0">${message}</p>
                    </td>
                </tr>
            `;
      }

      const infoElement = document.getElementById('pagination-info');
      if (infoElement) {
        infoElement.textContent = message;
      }
    }

    renderPagination() {
      const paginationControls = document.getElementById('pagination-controls');
      if (!paginationControls) return;

      paginationControls.innerHTML = '';

      if (!this.meta.total) {
        return;
      }

      const { page, total_pages: totalPages } = this.meta;

      const createPageItem = (
        label,
        targetPage,
        disabled = false,
        active = false
      ) => {
        const li = document.createElement('li');
        li.className = 'page-item';
        if (disabled) li.classList.add('disabled');
        if (active) li.classList.add('active');

        const link = document.createElement('a');
        link.className = 'page-link';
        link.href = 'javascript:void(0);';
        link.textContent = label;
        if (!disabled) {
          link.dataset.page = targetPage;
        }

        li.appendChild(link);
        paginationControls.appendChild(li);
      };

      createPageItem(
        'ก่อนหน้า',
        Math.max(1, page - 1),
        page <= 1 || this.isLoading
      );

      const maxVisible = 5;
      let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
      let endPage = Math.min(totalPages, startPage + maxVisible - 1);

      if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
      }

      for (let p = startPage; p <= endPage; p += 1) {
        createPageItem(p.toString(), p, false, p === page);
      }

      createPageItem(
        'ถัดไป',
        Math.min(totalPages, page + 1),
        page >= totalPages || this.isLoading
      );

      this.updatePaginationInfo();
    }

    updatePaginationInfo() {
      const infoElement = document.getElementById('pagination-info');
      if (!infoElement) return;

      if (!this.meta.total) {
        infoElement.textContent = 'ไม่พบข้อมูล';
        return;
      }

      const start = (this.meta.page - 1) * this.meta.per_page + 1;
      const end = Math.min(this.meta.total, start + this.meta.per_page - 1);
      infoElement.textContent = `กำลังแสดง ${start}-${end} จาก ${this.meta.total} รายการ`;
    }

    setupEventListeners() {
      const searchBtn = document.getElementById('filter-search-btn');
      if (searchBtn) {
        searchBtn.addEventListener('click', () => {
          if (this.isLoading) return;
          this.handleSearch();
        });
      }

      const resetBtn = document.getElementById('filter-reset-btn');
      if (resetBtn) {
        resetBtn.addEventListener('click', () => {
          if (this.isLoading) return;
          this.handleReset();
        });
      }

      const requestNumberInput = document.getElementById(
        'filter-request-number'
      );
      if (requestNumberInput) {
        requestNumberInput.addEventListener('keyup', (event) => {
          if (event.key === 'Enter') {
            this.handleSearch();
          }
        });
      }

      const statusSelect = document.getElementById('filter-status');
      if (statusSelect) {
        statusSelect.addEventListener('change', () => {
          this.handleSearch();
        });
      }

      const paginationControls = document.getElementById('pagination-controls');
      if (paginationControls) {
        paginationControls.addEventListener('click', (event) => {
          const link = event.target.closest('a.page-link');
          if (!link || !link.dataset.page) return;

          const page = parseInt(link.dataset.page, 10);
          if (Number.isNaN(page)) return;

          event.preventDefault();
          this.goToPage(page);
        });
      }
    }

    handleSearch() {
      if (this.isLoading) return;
      this.currentPage = 1;
      this.updateFiltersFromInputs();
      this.loadRequests();
    }

    handleReset() {
      const requestNumberInput = document.getElementById(
        'filter-request-number'
      );
      const statusSelect = document.getElementById('filter-status');
      const startDateInput = document.getElementById('filter-start-date');
      const endDateInput = document.getElementById('filter-end-date');

      if (requestNumberInput) requestNumberInput.value = '';
      if (statusSelect) statusSelect.value = '';
      if (startDateInput) startDateInput.value = '';
      if (endDateInput) endDateInput.value = '';
      if (this.startPicker && this.startPicker.instance) {
        this.startPicker.instance.clear();
        this.startPicker.instance.set('maxDate', null);
      }
      if (this.endPicker && this.endPicker.instance) {
        this.endPicker.instance.clear();
        this.endPicker.instance.set('minDate', null);
      }

      this.currentPage = 1;
      this.updateFiltersFromInputs();
      this.loadRequests();
    }

    goToPage(page) {
      if (this.isLoading) return;
      if (page < 1 || page > this.meta.total_pages) return;

      this.currentPage = page;
      this.loadRequests();
    }
  }

  function viewRequest(id) {
    console.log('👁️ ดูรายละเอียดคำขอ ID:', id);
    // สร้าง returnUrl จากหน้าปัจจุบัน (รวม query parameters ด้วย)
    const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
    window.location.href = `investment-budget-view-detail.php?id=${id}&returnUrl=${returnUrl}`;
  }

  function deleteRequest(id) {
    console.log('🗑️ ลบคำขอ ID:', id);
    if (confirm('คุณต้องการลบคำขอนี้หรือไม่?')) {
      // TODO: เรียก API ลบ
    }
  }

  window.viewRequest = viewRequest;
  window.deleteRequest = deleteRequest;
  window.InvestmentBudgetListManager = InvestmentBudgetListManager;

  document.addEventListener('DOMContentLoaded', () => {
    const listManager = new InvestmentBudgetListManager();
    listManager.initialize();
  });
})(window, document);
