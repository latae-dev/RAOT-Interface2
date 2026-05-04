(function (window, document) {
  /**
   * Investment Budget Approval List Manager
   * จัดการรายการคำขอตั้งงบลงทุน (สำหรับผู้อนุมัติ)
   */
  class InvestmentBudgetApprovalListManager {
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
      this.hasApprovalScope = true;

      this.buildEncodedUserContext = () => {
        const sessionData = sessionStorage.getItem('raot_user_session');
        if (!sessionData) {
          return null;
        }

        let normalized = sessionData;
        try {
          normalized = JSON.stringify(JSON.parse(sessionData));
        } catch (error) {
          console.warn(
            'ไม่สามารถแปลง user_context ให้เป็น JSON ที่ถูกต้อง จะใช้ค่าดั้งเดิมแทน',
            error
          );
        }

        return this.encodeUserContext(normalized);
      };

      this.getUserContextHeaders = () => {
        const encoded = this.buildEncodedUserContext();
        if (!encoded) {
          return {};
        }

        const headers = {
          'X-User-Context': encoded.value
        };
        if (encoded.encoding) {
          headers['X-User-Context-Encoding'] = encoded.encoding;
        }
        return headers;
      };

      this.filters = {
        requestNumber: '',
        status: 'submitted',
        startDate: '',
        endDate: ''
      };
      this.isLoading = false;
      this.startPicker = null;
      this.endPicker = null;
    }

    encodeUserContext(rawValue) {
      if (typeof rawValue !== 'string') {
        return {
          value: ''
        };
      }

      const requiresEncoding = /[^\x00-\x7F]/.test(rawValue);
      if (!requiresEncoding) {
        return {
          value: rawValue
        };
      }

      try {
        const utf8 = encodeURIComponent(rawValue).replace(
          /%([0-9A-F]{2})/g,
          (_, hex) => String.fromCharCode(parseInt(hex, 16))
        );
        const base64 = window.btoa(utf8);
        return {
          value: base64,
          encoding: 'base64'
        };
      } catch (error) {
        console.warn(
          'ไม่สามารถเข้ารหัส user_context ด้วย Base64 จะใช้ URL encoding แทน',
          error
        );
      }

      return {
        value: encodeURIComponent(rawValue),
        encoding: 'url'
      };
    }

    async initialize() {
      console.log('📋 เริ่มต้นระบบรายการคำขอตั้งงบลงทุน (สำหรับผู้อนุมัติ)...');
      this.initDatePickers();
      this.updateFiltersFromInputs();
      if (!this.filters.status) {
        this.filters.status = 'submitted';
        const statusSelect = document.getElementById('approval-filter-status');
        if (statusSelect) {
          statusSelect.value = 'submitted';
        }
      }
      await this.loadRequests();
      this.setupEventListeners();
    }

    initDatePickers() {
      if (typeof setupThaiBEFlatpickr !== 'function') {
        console.warn('setupThaiBEFlatpickr helper ไม่พร้อมใช้งาน');
        return;
      }

      // Start Date picker with minDate/maxDate logic
      const startPickerObj = setupThaiBEFlatpickr(
        '#approval-filter-start-date',
        {
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
        }
      );

      // End Date picker with minDate/maxDate logic
      const endPickerObj = setupThaiBEFlatpickr('#approval-filter-end-date', {
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
        'approval-filter-request-number'
      );
      const statusSelect = document.getElementById('approval-filter-status');
      const startDateInput = document.getElementById(
        'approval-filter-start-date'
      );
      const endDateInput = document.getElementById('approval-filter-end-date');

      this.filters.requestNumber = requestNumberInput
        ? requestNumberInput.value.trim()
        : '';
      this.filters.status = statusSelect ? statusSelect.value : 'submitted';
      this.filters.startDate = startDateInput ? startDateInput.value : '';
      this.filters.endDate = endDateInput ? endDateInput.value : '';

      if (
        this.filters.startDate &&
        this.filters.endDate &&
        this.filters.startDate > this.filters.endDate
      ) {
        const tmp = this.filters.startDate;
        this.filters.startDate = this.filters.endDate;
        this.filters.endDate = tmp;

        if (startDateInput && endDateInput) {
          startDateInput.value = this.filters.startDate;
          endDateInput.value = this.filters.endDate;
        }
      }
    }

    async loadRequests() {
      this.isLoading = true;
      this.showLoadingState();

      try {
        const url = this.buildRequestUrl();
        const headers = this.getUserContextHeaders();
        const response = await fetch(url, {
          credentials: 'same-origin',
          headers
        });
        const data = await response.json();

        if (data.status === 'success') {
          const payload = data.data;

          if (Array.isArray(payload)) {
            this.requests = payload;
            this.meta = {
              total: payload.length,
              page: 1,
              per_page: payload.length || this.perPage,
              total_pages: 1,
              filters: null
            };
            this.hasApprovalScope = true;
          } else {
            this.requests = payload?.items || [];
            const meta = payload?.meta || {};
            this.meta = {
              total: Math.max(0, meta.total ?? this.requests.length),
              page: Math.max(1, meta.page ?? this.currentPage),
              per_page: Math.max(1, meta.per_page ?? this.perPage),
              total_pages: Math.max(1, meta.total_pages ?? 1),
              filters: meta.filters ?? null
            };
            this.hasApprovalScope =
              (this.meta.filters ?? '').toString().toUpperCase() !== 'NO_SCOPE';
          }

          this.currentPage = this.meta.page;
          this.perPage = this.meta.per_page;

          console.log(
            '✅ โหลดข้อมูลสำหรับการอนุมัติสำเร็จ:',
            this.meta.total,
            'รายการ'
          );
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
      if (this.filters.status && this.filters.status !== 'all') {
        params.append('status', this.filters.status);
      }
      if (this.filters.startDate) {
        params.append('start_date', this.filters.startDate);
      }
      if (this.filters.endDate) {
        params.append('end_date', this.filters.endDate);
      }

      params.append('include_scope_annotations', '1');

      const query = params.toString();
      return `../controllers/investment_budget/ib_requests_controller.php?action=approvals${
        query ? '&' + query : ''
      }`;
    }

    showLoadingState() {
      const tbody = document.getElementById('approval-requests-tbody');
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

      const infoElement = document.getElementById('approval-pagination-info');
      if (infoElement) {
        infoElement.textContent = 'กำลังโหลดข้อมูล...';
      }
    }

    renderRequestsTable() {
      const tbody = document.getElementById('approval-requests-tbody');
      if (!tbody) return;

      if (!this.requests.length) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center">
                        <p class="mb-0">${
                          this.hasApprovalScope
                            ? 'ไม่พบข้อมูลคำขอตั้งงบลงทุน'
                            : 'คุณไม่มีสิทธิ์เข้าถึงรายการคำขออนุมัติ หากคิดว่าเป็นความผิดพลาด กรุณาติดต่อผู้ดูแลระบบ'
                        }</p>
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
      const actionButtons = this.getActionButtons(request);

      const sectionLabel =
        request.budget_source_name_full ||
        request.section_name ||
        request.budget_source_name ||
        request.budget_source_code ||
        request.asset_type_name ||
        '-';

      return `
            <tr class="crm-contact text-center">
                <td>${rowNumber}</td>
                <td>${createdDate}</td>
                <td>${request.request_number || '-'}</td>
                <td>${sectionLabel}</td>
                <td>${request.asset_type_name || '-'}</td>
                <td>${this.getItemName(request)}</td>
                <td>${this.getItemQuantity(request)}</td>
                <td>${this.formatUnitPrice(request)}</td>
                <td>${this.formatTotalAmount(request)}</td>
                <td>${request.department_name || '-'}</td>
                <td>${statusBadge}</td>
                <td>${actionButtons}</td>
            </tr>
        `;
    }

    getItemName(request) {
      return request.item_name || '-';
    }

    getItemQuantity(request) {
      if (request.quantity === null || request.quantity === undefined) {
        return '-';
      }
      const quantity = parseInt(request.quantity, 10);
      if (Number.isNaN(quantity) || quantity === 0) {
        return '-';
      }
      return quantity.toString();
    }

    getItemUnitPrice(request) {
      if (request.unit_price === null || request.unit_price === undefined) {
        return '-';
      }
      const price = parseFloat(request.unit_price);
      if (Number.isNaN(price) || price === 0) {
        return '-';
      }
      return price;
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
      const status = (request.status || '').toLowerCase();
      const pendingBadgeClass = 'bg-warning text-white';

      if (status.startsWith('pending_')) {
        const stage = request.current_stage || status.replace('pending_', '');
        const label = request.current_stage_label || this.getStageLabel(stage);
        const isCurrent =
          request.can_approve === true || request.is_current_stage === true;
        const badgeClass = isCurrent ? pendingBadgeClass : 'bg-info text-white';
        const returnBadge = this.buildReturnBadge(request);
        return `<span class="badge rounded-pill ${badgeClass}">รออนุมัติ${
          label ? ` (${label})` : ''
        }</span>${returnBadge}`;
      }

      const statusConfig = {
        draft: {
          class: 'bg-primary',
          text: 'บันทึกร่าง'
        },
        approved: {
          class: 'bg-success',
          text: 'อนุมัติแล้ว'
        },
        rejected: {
          class: 'bg-danger',
          text: 'ปฏิเสธแล้ว'
        }
      };

      if (status === 'submitted') {
        const returnBadge = this.buildReturnBadge(request);
        return `<span class="badge rounded-pill ${pendingBadgeClass}">รออนุมัติ</span>${returnBadge}`;
      }

      const config = statusConfig[status] || {
        class: 'bg-secondary',
        text: status || '-'
      };
      const returnBadge = this.buildReturnBadge(request);
      return `<span class="badge rounded-pill ${config.class}">${config.text}</span>${returnBadge}`;
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
      const viewUrl = `investment-budget-view-detail.php?id=${request.id}`;
      const approveUrl = `investment-budget-approve.php?id=${request.id}`;

      const viewButton = `
            <a href="${viewUrl}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" title="รายละเอียดคำขอ">
                <i class="fe fe-eye"></i>
            </a>
        `;
      const levelLabel =
        request.approval_scope_level_label ||
        this.getApprovalLevelLabel(request.approval_scope_level);
      const approveButton = `
            <a href="${approveUrl}"
               class="btn btn-icon btn-sm ${
                 request.can_approve
                   ? 'btn-warning-transparent'
                   : 'btn-info-transparent'
               } rounded-pill"
               title="${
                 request.can_approve
                   ? `ตรวจ${levelLabel ? ` - สิทธิระดับ ${levelLabel}` : ''}`
                   : 'ดูรายละเอียด (อยู่นอกสิทธิ์ของคุณ)'
               }">
                <i class="bx bx-list-check"></i>
            </a>
        `;

      return `
            <div class="hstack gap-2 fs-15">
                ${viewButton}
                ${approveButton}
            </div>
        `;
    }

    getApprovalLevelLabel(level) {
      // alert(level);
      switch (level) {
        case 'branch':
          return 'สาขา';
        case 'province':
          return 'จังหวัด/กอง';
        case 'area':
          return 'เขต/ฝ่าย';
        case 'head_office':
          return 'สำนักงานใหญ่';
        default:
          return '';
      }
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

    formatUnitPrice(request) {
      const price = this.getItemUnitPrice(request);
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
      // Return '-' instead of formatting if value is '-'
      if (amount === '-') {
        return '-';
      }

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
      const tbody = document.getElementById('approval-requests-tbody');
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

      const infoElement = document.getElementById('approval-pagination-info');
      if (infoElement) {
        infoElement.textContent = message;
      }
    }

    renderPagination() {
      const paginationControls = document.getElementById(
        'approval-pagination-controls'
      );
      if (!paginationControls) return;

      paginationControls.innerHTML = '';

      if (!this.meta.total) {
        this.updatePaginationInfo();
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
      const infoElement = document.getElementById('approval-pagination-info');
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
      const searchBtn = document.getElementById('approval-filter-search-btn');
      if (searchBtn) {
        searchBtn.addEventListener('click', () => {
          if (this.isLoading) return;
          this.handleSearch();
        });
      }

      const resetBtn = document.getElementById('approval-filter-reset-btn');
      if (resetBtn) {
        resetBtn.addEventListener('click', () => {
          if (this.isLoading) return;
          this.handleReset();
        });
      }

      const requestNumberInput = document.getElementById(
        'approval-filter-request-number'
      );
      if (requestNumberInput) {
        requestNumberInput.addEventListener('keyup', (event) => {
          if (event.key === 'Enter') {
            this.handleSearch();
          }
        });
      }

      const statusSelect = document.getElementById('approval-filter-status');
      if (statusSelect) {
        statusSelect.addEventListener('change', () => {
          this.handleSearch();
        });
      }

      const paginationControls = document.getElementById(
        'approval-pagination-controls'
      );
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
      if (this.isLoading) return;

      const requestNumberInput = document.getElementById(
        'approval-filter-request-number'
      );
      const statusSelect = document.getElementById('approval-filter-status');
      const startDateInput = document.getElementById(
        'approval-filter-start-date'
      );
      const endDateInput = document.getElementById('approval-filter-end-date');

      if (requestNumberInput) requestNumberInput.value = '';
      if (statusSelect) statusSelect.value = 'submitted';
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

  window.InvestmentBudgetApprovalListManager =
    InvestmentBudgetApprovalListManager;

  document.addEventListener('DOMContentLoaded', () => {
    const approvalListManager = new InvestmentBudgetApprovalListManager();
    approvalListManager.initialize();
  });
})(window, document);
