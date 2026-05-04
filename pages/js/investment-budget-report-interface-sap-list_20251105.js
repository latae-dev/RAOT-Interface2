(function (window, document) {
  /**
   * SAP Interface Report Manager
   * จัดการรายงาน Interface ข้อมูลเข้า SAP
   */
  class SAPInterfaceReportManager {
    constructor() {
      this.currentPage = 1;
      this.perPage = 10;
      this.filters = {
        requestNumber: '',
        startDate: '',
        endDate: '',
        fiscalYear: '',
        approvalStatus: '',
        sapStatus: ''
      };
      this.data = [];
      this.fiscalYears = [];
      this.startPicker = null;
      this.endPicker = null;
    }

    async initialize() {
      console.log('📊 เริ่มต้นระบบรายงาน Interface SAP...');
      this.initFlatpickr();
      await this.loadFiscalYears();
      await this.loadData();
      this.setupEventListeners();
    }

    initFlatpickr() {
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

      console.log('✅ Thai Datepicker initialized with helper');
    }

    buildEncodedUserContext() {
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

    getUserContextHeaders() {
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
    }

    buildMasterDataUrl(type, params = {}) {
      const searchParams = new URLSearchParams({
        type,
        action: params.action || 'all'
      });

      Object.entries(params).forEach(([key, value]) => {
        if (key === 'action') return;
        if (value !== undefined && value !== null) {
          searchParams.append(key, value);
        }
      });

      return `../controllers/investment_budget/master_data_controller.php?${searchParams.toString()}`;
    }

    async fetchMasterData(type, params = {}) {
      const headers = this.getUserContextHeaders();
      const response = await fetch(this.buildMasterDataUrl(type, params), {
        headers
      });
      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
      }
      const result = await response.json();
      if (result.status !== 'success') {
        throw new Error(result.message || 'Unknown error');
      }
      return result.data || [];
    }

    async loadFiscalYears() {
      console.log('📅 Loading fiscal years...');
      try {
        const data = await this.fetchMasterData('fiscal_year');
        this.fiscalYears = Array.isArray(data) ? data : [];
        console.log('  ✅ Fiscal years loaded:', this.fiscalYears.length);
        this.renderFiscalYearOptions();
      } catch (error) {
        console.error('❌ เกิดข้อผิดพลาดในการโหลดปีงบประมาณ:', error);
      }
    }

    renderFiscalYearOptions() {
      console.log('🎨 Rendering fiscal year options...');
      const select = document.getElementById('filter-fiscal-year');
      if (!select) {
        console.warn('  ⚠️ Element #filter-fiscal-year not found');
        return;
      }

      const options = this.fiscalYears
        .map((fy) => `<option value="${fy.id}">${fy.name}</option>`)
        .join('');
      select.innerHTML = '<option value="">กรุณาเลือก</option>' + options;
      console.log('  ✅ Options rendered:', this.fiscalYears.length, 'items');

      // Re-initialize Choices.js
      if (typeof window.initializeSAPChoices === 'function') {
        console.log('  🔄 Calling initializeSAPChoices...');
        setTimeout(() => {
          window.initializeSAPChoices();
        }, 100);
      } else {
        console.warn('  ⚠️ window.initializeSAPChoices not found');
      }
    }

    async loadData() {
      this.updateFiltersFromInputs();
      const url = this.buildRequestUrl();
      this.showLoadingState();

      try {
        const headers = this.getUserContextHeaders();
        const response = await fetch(url, {
          credentials: 'same-origin',
          headers
        });
        const result = await response.json();

        if (result.status === 'success') {
          this.data = Array.isArray(result.data)
            ? result.data
            : result.data?.items || [];
          console.log('📊 Data loaded:', this.data.length, 'items');
          if (this.data.length > 0) {
            console.log('🔍 First item sap_status:', this.data[0].sap_status);
          }
          this.renderTable();
        } else {
          this.showError(result.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
        }
      } catch (error) {
        console.error('❌ Error:', error);
        this.showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
      }
    }

    buildRequestUrl() {
      const params = new URLSearchParams();
      params.append('per_page', '100');

      // ใช้ approvalStatus จาก filter แทนการใช้ 'approved' แข็งไว้
      if (this.filters.approvalStatus) {
        params.append('status', this.filters.approvalStatus);
      } else {
        // ถ้าไม่ระบุ ให้โหลดทั้งหมด
        params.append('status', 'approved');
      }

      if (this.filters.requestNumber)
        params.append('request_number', this.filters.requestNumber);
      if (this.filters.startDate)
        params.append('start_date', this.filters.startDate);
      if (this.filters.endDate) params.append('end_date', this.filters.endDate);
      if (this.filters.fiscalYear)
        params.append('fiscal_year_id', this.filters.fiscalYear);
      if (this.filters.sapStatus)
        params.append('sap_status', this.filters.sapStatus);

      // แสดงข้อมูลทั้งหมด ไม่เฉพาะของ user ที่ login
      params.append('include_all', 'true');

      return `../controllers/investment_budget/ib_requests_controller.php?action=all&${params.toString()}`;
    }

    updateFiltersFromInputs() {
      this.filters.requestNumber =
        document.getElementById('filter-request-number')?.value.trim() || '';
      this.filters.startDate =
        document.getElementById('filter-start-date')?.value || '';
      this.filters.endDate =
        document.getElementById('filter-end-date')?.value || '';
      this.filters.fiscalYear =
        document.getElementById('filter-fiscal-year')?.value || '';
      this.filters.approvalStatus =
        document.getElementById('filter-approval-status')?.value || '';
      this.filters.sapStatus =
        document.getElementById('filter-sap-status')?.value || '';
    }

    showLoadingState() {
      const tbody = document.getElementById('sap-tbody');
      if (!tbody) return;
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

    renderTable() {
      let filteredData = this.filterData();
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      const pageData = filteredData.slice(start, end);

      const tbody = document.getElementById('sap-tbody');
      if (!pageData.length) {
        tbody.innerHTML =
          '<tr><td colspan="12" class="text-center">ไม่พบข้อมูล</td></tr>';
        this.renderPagination(0);
        return;
      }

      const rows = pageData
        .map((item, i) => {
          const index = start + i + 1;
          const requestNumber = this.escapeHtml(item.request_number || '-');
          const itemName = this.escapeHtml(item.item_name || '-');

          // ศูนย์เงินทุน (Cost Center) - จาก department_code ของ user
          const fundCenter = this.escapeHtml(item.department_code || '-');

          // เงินทุน (Fund/Budget Source) - จาก budget_source_code
          const fund = this.escapeHtml(item.budget_source_code || '-');

          // ขอบเขตหน้าที่ (Functional Area) - จาก functional_area ในฐานข้อมูล
          const functionalArea = this.escapeHtml(item.functional_area || '-');

          // รายการภาระผูกพัน (Commitment Item) - จาก asset_type_code
          const commitment = this.escapeHtml(item.asset_type_code || '-');

          // ปี (Fiscal Year) - แปลงจาก พ.ศ. เป็น ค.ศ.
          const fiscalYear = this.convertFiscalYearToAD(item.fiscal_year_id);

          // ประเภทงบ (Budget Type) - FIX = 8
          const budgetType = '8';

          // สถานะการอนุมัติ
          const approvalBadge = this.getApprovalBadge(item.status);

          // สถานะ SAP จากฐานข้อมูล
          const sapBadge = this.getSAPBadge(item);

          return `
                    <tr class="text-center">
                        <td>${index}</td>
                        <td>${this.formatDate(item.created_at)}</td>
                        <td>${requestNumber}</td>
                        <td>${fundCenter}</td>
                        <td>${fund}</td>
                        <td>${functionalArea}</td>
                        <td>${commitment}</td>
                        <td>${fiscalYear}</td>
                        <td class="text-end">${this.formatCurrency(
                          item.total_amount
                        )}</td>
                        <td class="text-start">${itemName}</td>
                        <td>${approvalBadge}</td>
                        <td>${sapBadge}</td>
                    </tr>
                `;
        })
        .join('');

      tbody.innerHTML = rows;
      this.renderPagination(filteredData.length);
    }

    filterData() {
      let filtered = [...this.data];

      // Filter by approval status
      if (this.filters.approvalStatus) {
        filtered = filtered.filter((item) => {
          if (this.filters.approvalStatus === 'approved') {
            return item.status === 'approved';
          } else if (this.filters.approvalStatus === 'rejected') {
            return item.status === 'rejected';
          }
          return true;
        });
      }

      // Filter by SAP status (ใช้ข้อมูลจากฐานข้อมูล)
      if (this.filters.sapStatus) {
        filtered = filtered.filter((item) => {
          const sapStatus = item.sap_status || 'not_synced';
          if (this.filters.sapStatus === 'success') {
            return sapStatus === 'success';
          } else if (this.filters.sapStatus === 'failed') {
            return sapStatus === 'failed';
          } else if (this.filters.sapStatus === 'not_synced') {
            return sapStatus === 'not_synced';
          }
          return true;
        });
      }

      return filtered;
    }

    getApprovalBadge(status) {
      if (status === 'approved') {
        return '<span class="badge rounded-pill bg-success">อนุมัติ</span>';
      } else if (status === 'rejected') {
        return '<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>';
      }
      return '<span class="badge rounded-pill bg-secondary">รอดำเนินการ</span>';
    }

    getSAPBadge(item) {
      const sapStatus = item.sap_status || 'not_synced';

      if (sapStatus === 'success') {
        return '<span class="badge rounded-pill bg-success">Interface SAP Success</span>';
      } else if (sapStatus === 'failed') {
        return '<span class="badge rounded-pill bg-danger">Interface SAP Failed</span>';
      } else {
        return '<span class="badge rounded-pill bg-secondary">Not Synced</span>';
      }
    }

    renderPagination(total) {
      const paginationEl = document.getElementById('pagination');
      const infoEl = document.getElementById('pagination-info');
      const totalPages = Math.ceil(total / this.perPage);

      if (totalPages <= 1) {
        paginationEl.innerHTML = '';
      } else {
        let html = `<li class="page-item ${
          this.currentPage === 1 ? 'disabled' : ''
        }">
                    <a class="page-link" href="javascript:void(0);" data-page="${
                      this.currentPage - 1
                    }">ก่อนหน้า</a>
                </li>`;

        for (let i = 1; i <= totalPages; i++) {
          html += `<li class="page-item ${
            i === this.currentPage ? 'active' : ''
          }">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>`;
        }

        html += `<li class="page-item ${
          this.currentPage === totalPages ? 'disabled' : ''
        }">
                    <a class="page-link" href="javascript:void(0);" data-page="${
                      this.currentPage + 1
                    }">ถัดไป</a>
                </li>`;

        paginationEl.innerHTML = html;
      }

      const start = (this.currentPage - 1) * this.perPage + 1;
      const end = Math.min(this.currentPage * this.perPage, total);
      infoEl.textContent =
        total > 0
          ? `กำลังแสดง ${start}-${end} จาก ${total} รายการ`
          : 'ไม่พบข้อมูล';
    }

    setupEventListeners() {
      // Search & Reset
      document
        .getElementById('filter-search-btn')
        ?.addEventListener('click', () => {
          this.currentPage = 1;
          this.loadData();
        });

      document
        .getElementById('filter-reset-btn')
        ?.addEventListener('click', () => {
          // Reset text input
          const requestNumberEl = document.getElementById(
            'filter-request-number'
          );
          if (requestNumberEl) requestNumberEl.value = '';

          // Reset date inputs
          const startDateEl = document.getElementById('filter-start-date');
          const endDateEl = document.getElementById('filter-end-date');
          if (startDateEl) startDateEl.value = '';
          if (endDateEl) endDateEl.value = '';

          // Reset Choices.js dropdowns
          const approvalStatusEl = document.getElementById(
            'filter-approval-status'
          );
          if (approvalStatusEl && approvalStatusEl.choicesInstance) {
            approvalStatusEl.choicesInstance.setChoiceByValue('');
          } else if (approvalStatusEl) {
            approvalStatusEl.value = '';
          }

          const sapStatusEl = document.getElementById('filter-sap-status');
          if (sapStatusEl && sapStatusEl.choicesInstance) {
            sapStatusEl.choicesInstance.setChoiceByValue('');
          } else if (sapStatusEl) {
            sapStatusEl.value = '';
          }

          // Clear datepickers
          if (this.startPicker && this.startPicker.instance) {
            this.startPicker.instance.clear();
            this.startPicker.instance.set('maxDate', null);
          }
          if (this.endPicker && this.endPicker.instance) {
            this.endPicker.instance.clear();
            this.endPicker.instance.set('minDate', null);
          }

          // Reset filters object
          this.filters = {
            requestNumber: '',
            startDate: '',
            endDate: '',
            fiscalYear: '',
            approvalStatus: '',
            sapStatus: ''
          };

          this.currentPage = 1;
          this.loadData();
        });

      // Pagination
      document.getElementById('pagination')?.addEventListener('click', (e) => {
        const page = parseInt(e.target.dataset.page);
        if (page && page > 0) {
          this.currentPage = page;
          this.renderTable();
        }
      });

      // Export
      document
        .getElementById('export-excel-btn')
        ?.addEventListener('click', () => {
          this.exportToExcel();
        });
    }

    formatDate(dateString) {
      if (!dateString) return '-';
      const date = new Date(dateString);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear() + 543;
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      const seconds = date.getSeconds().toString().padStart(2, '0');
      return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
    }

    convertFiscalYearToAD(fiscalYearName) {
      if (!fiscalYearName) return '-';

      // แยกปี พ.ศ. จากชื่อ เช่น "2568" หรือ "ปี 2568"
      const match = String(fiscalYearName).match(/(\d{4})/);
      if (match) {
        const buddhistYear = parseInt(match[1]);
        // แปลง พ.ศ. เป็น ค.ศ. (ลบ 543)
        const adYear = buddhistYear - 543;
        return String(adYear);
      }

      return fiscalYearName;
    }

    formatCurrency(amount) {
      const value = Number(amount);
      return isNaN(value)
        ? '0.00'
        : new Intl.NumberFormat('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }).format(value);
    }

    escapeHtml(value) {
      if (value === null || value === undefined) return '';
      return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    showError(message) {
      console.error(message);
      const tbody = document.getElementById('sap-tbody');
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="12" class="text-center text-danger">${message}</td></tr>`;
      }
    }

    exportToExcel() {
      // ตรวจสอบว่ามี XLSX library หรือไม่
      if (typeof XLSX === 'undefined') {
        alert('ไม่พบ XLSX library กรุณาโหลดหน้าใหม่');
        return;
      }

      try {
        const data = this.data || [];

        if (data.length === 0) {
          alert('ไม่มีข้อมูลสำหรับ Export');
          return;
        }

        // สร้าง array สำหรับ Excel (ตามรูปแบบที่กำหนด)
        const rows = [];

        // เพิ่มแถวหัวตาราง (ตามตัวอย่างที่ส่งมา)
        rows.push([
          'ศูนย์เงินทุน',
          'เงินทุน',
          'ขอบเขตหน้าที่',
          'รายการภาระผูกพัน',
          'ปี',
          'ประเภทงบ',
          'จำนวนเงิน',
          'รายการ'
        ]);

        // เพิ่มข้อมูล
        data.forEach((item) => {
          // ศูนย์เงินทุน (Cost Center) - จาก department_code
          const fundCenter = item.department_code || '';

          // เงินทุน (Fund/Budget Source) - จาก budget_source_code
          const fund = item.budget_source_code || '';

          // ขอบเขตหน้าที่ (Functional Area) - จาก functional_area ในฐานข้อมูล
          const functionalArea = item.functional_area || '';

          // รายการภาระผูกพัน (Commitment Item) - จาก asset_type_code
          const commitment = item.asset_type_code || '';

          // ปี (Fiscal Year) - แปลงจาก พ.ศ. เป็น ค.ศ.
          const fiscalYear = this.convertFiscalYearToAD(item.fiscal_year_id);

          // ประเภทงบ (Budget Type) - FIX = 8
          const budgetType = '8';

          // จำนวนเงิน (Amount) - แสดงเป็นตัวเลขไม่มี format
          const amount = item.total_amount || 0;

          // รายการ (Item Name)
          const itemName = item.item_name || '';

          rows.push([
            fundCenter,
            fund,
            functionalArea,
            commitment,
            fiscalYear,
            budgetType,
            amount,
            itemName
          ]);
        });

        // สร้าง worksheet และ workbook
        const ws = XLSX.utils.aoa_to_sheet(rows);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'SAP Interface');

        // กำหนดชื่อไฟล์
        const timestamp = new Date()
          .toISOString()
          .replace(/[:.]/g, '-')
          .slice(0, 19);
        const filename = `SAP_Interface_${timestamp}.xlsx`;

        // ดาวน์โหลดไฟล์
        XLSX.writeFile(wb, filename);

        console.log('✅ Export สำเร็จ');
      } catch (error) {
        console.error('❌ Export ล้มเหลว:', error);
        alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
      }
    }
  }

  window.SAPInterfaceReportManager = SAPInterfaceReportManager;

  // Initialize
  document.addEventListener('DOMContentLoaded', () => {
    const manager = new SAPInterfaceReportManager();
    manager.initialize();
  });
})(window, document);
