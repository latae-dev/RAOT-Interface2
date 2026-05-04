class InvestmentBudgetRequestView {
  constructor() {
    this.requestId = null;
    this.requestData = null;
    this.itemsData = [];
    this.currentUser = null;
    this.isSubmittingApproval = false;
    this.isBudgetApprovalMode = false;
    this.referenceData = {
      projectPeriods: {},
      implementationYears: {},
      installmentWorks: {},
      assetCategories: {},
      equipmentSetup: {}
    };
    this.referenceDataLoaded = false;

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

      const headers = { 'X-User-Context': encoded.value };
      if (encoded.encoding) {
        headers['X-User-Context-Encoding'] = encoded.encoding;
      }

      return headers;
    };

    this.getUserContextPayload = () => {
      const encoded = this.buildEncodedUserContext();
      if (!encoded) {
        return null;
      }

      const payload = { user_context: encoded.value };
      if (encoded.encoding) {
        payload.user_context_encoding = encoded.encoding;
      }
      return payload;
    };
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
        (_, hex) => String.fromCharCode(parseInt(hex, 16))
      );
      const base64 = window.btoa(utf8);
      return { value: base64, encoding: 'base64' };
    } catch (error) {
      console.warn(
        'ไม่สามารถเข้ารหัส user_context ด้วย Base64 จะใช้ URL encoding แทน',
        error
      );
    }

    return { value: encodeURIComponent(rawValue), encoding: 'url' };
  }

  async initialize() {
    console.log('📋 เริ่มต้นระบบแสดงรายละเอียดคำขอตั้งงบลงทุน...');

    this.requestId = this.getRequestIdFromUrl();
    this.isBudgetApprovalMode = this.getBudgetApprovalModeFromUrl();

    if (!this.requestId) {
      this.showError('ไม่พบ ID คำขอตั้งงบลงทุน');
      return;
    }

    this.loadCurrentUser();

    await this.loadReferenceData();
    await this.loadRequestData();

    this.setupEventListeners();
    this.renderBudgetApprovalSection();
    this.initBudgetApprovalChoices();
  }

  setTextContent(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = value;
    }
  }

  setDisplay(elementId, displayValue = 'block') {
    const element = document.getElementById(elementId);
    if (element) {
      element.style.display = displayValue;
    }
  }

  formatIdAndName(id, name) {
    const nameStr = this.normalizeDisplayValue(name);
    const idStr = this.normalizeDisplayValue(id);

    if (nameStr) {
      return nameStr;
    }

    if (idStr) {
      return idStr;
    }

    return '-';
  }

  formatIdCodeName(id, code, name) {
    const codeStr = this.normalizeDisplayValue(code);
    const nameStr = this.normalizeDisplayValue(name);
    const idStr = this.normalizeDisplayValue(id);

    const parts = [];
    if (codeStr) {
      parts.push(codeStr);
    }
    if (nameStr) {
      parts.push(nameStr);
    }

    if (parts.length > 0) {
      return parts.join(' - ');
    }

    if (idStr) {
      return idStr;
    }

    return '-';
  }

  formatUser(id, name) {
    return this.formatIdAndName(id, name);
  }

  normalizeDisplayValue(value) {
    if (value === null || value === undefined) {
      return '';
    }

    const stringValue = String(value).trim();
    return stringValue === '' ? '' : stringValue;
  }

  normalizeId(value) {
    if (value === undefined || value === null) {
      return '';
    }
    return String(value).trim();
  }

  getRequestIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
  }

  getBudgetApprovalModeFromUrl() {
    if (window.BUDGET_APPROVAL_MODE_OVERRIDE === true) {
      return true;
    }
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('budgetApproval') === '1';
  }

  loadCurrentUser() {
    try {
      const sessionData = sessionStorage.getItem('raot_user_session');
      if (sessionData) {
        this.currentUser = JSON.parse(sessionData);
      }
    } catch (error) {
      console.warn('ไม่สามารถอ่านข้อมูลผู้ใช้งานจาก sessionStorage ได้', error);
      this.currentUser = null;
    }
  }

  getMasterDataUrl(type, params = {}) {
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

  getEquipmentSetupLabel(value) {
    if (!value) return '';
    const key = String(value).trim();
    if (!key) return '';
    const match = this.referenceData?.equipmentSetup?.[key];
    if (match) return match;
    const item = (window.masterDataManager?.data?.equipmentSetup || []).find(
      (entry) => String(entry.id) === key
    );
    if (item?.name) return item.name;
    return key;
  }

  formatSerialNumberEntry(entry) {
    if (entry === null || entry === undefined) {
      return '';
    }
    if (typeof entry === 'string') {
      return entry.trim();
    }
    if (Array.isArray(entry)) {
      return entry
        .map((item) => this.formatSerialNumberEntry(item))
        .filter(Boolean)
        .join(', ');
    }
    if (typeof entry === 'object') {
      const priorityKeys = [
        'serial_number',
        'serialNumber',
        'serial',
        'number',
        'code',
        'label',
        'value',
        'text',
        'description'
      ];
      for (const key of priorityKeys) {
        if (entry[key]) {
          return String(entry[key]).trim();
        }
      }
      const first = Object.values(entry).find(
        (val) => val !== null && val !== undefined && String(val).trim() !== ''
      );
      if (first) {
        return String(first).trim();
      }
    }
    return String(entry).trim();
  }

  normalizeSerialNumbers(rawValue) {
    if (rawValue === null || rawValue === undefined) {
      return [];
    }

    if (Array.isArray(rawValue)) {
      return rawValue.flatMap((item) => this.normalizeSerialNumbers(item));
    }

    if (typeof rawValue === 'object') {
      return Object.values(rawValue)
        .flatMap((item) => this.normalizeSerialNumbers(item))
        .filter((val) => val && String(val).trim() !== '');
    }

    if (typeof rawValue === 'string') {
      const trimmed = rawValue.trim();
      if (!trimmed) {
        return [];
      }

      try {
        if (
          (trimmed.startsWith('[') && trimmed.endsWith(']')) ||
          (trimmed.startsWith('{') && trimmed.endsWith('}'))
        ) {
          const parsed = JSON.parse(trimmed.replace(/^\{(.*)\}$/, '[$1]'));
          return this.normalizeSerialNumbers(parsed);
        }
      } catch (error) {
        // Not a valid JSON string, continue with fallback parsing
      }

      if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
        return trimmed
          .slice(1, -1)
          .split(',')
          .map((part) => part.trim().replace(/^"(.*)"$/, '$1'))
          .filter((val) => val);
      }

      return [trimmed];
    }

    return [String(rawValue).trim()];
  }

  async loadRequestData() {
    try {
      const headers = this.getUserContextHeaders();
      const response = await fetch(
        `../controllers/investment_budget/ib_requests_controller.php?action=one&id=${this.requestId}`,
        { headers }
      );
      const data = await response.json();

      if (data.status === 'success') {
        this.requestData = data.data;
        console.log('✅ โหลดข้อมูลสำเร็จ:', this.requestData);

        this.renderRequestData();
        this.renderItemCard();
        this.renderDocuments();
        this.showMainContent();
      } else {
        console.error('❌ ไม่สามารถโหลดข้อมูลได้:', data.message);
        this.showError(data.message || 'ไม่สามารถโหลดข้อมูลได้');
      }
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);
      this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  }

  async loadReferenceData() {
    if (this.referenceDataLoaded) {
      return;
    }

    const tasks = [
      this.fetchReferenceData(
        this.getMasterDataUrl('project_period'),
        (item) => ({
          map: 'projectPeriods',
          key: item?.id,
          value: item?.name || item?.label || ''
        })
      ),
      this.fetchReferenceData(
        this.getMasterDataUrl('implementation_year'),
        (item) => ({
          map: 'implementationYears',
          key: item?.id,
          value: item?.year ?? ''
        })
      ),
      this.fetchReferenceData(
        this.getMasterDataUrl('installment_work'),
        (item) => {
          const code = item?.code ? String(item.code).trim() : '';
          const name = item?.name ? String(item.name).trim() : '';
          const value = code && name ? `${code} - ${name}` : name || code;
          return {
            map: 'installmentWorks',
            key: item?.id,
            value
          };
        }
      ),
      this.fetchReferenceData(
        this.getMasterDataUrl('asset_category'),
        (item) => {
          const code = item?.code ? String(item.code).trim() : '';
          const name = item?.name ? String(item.name).trim() : '';
          const value = code && name ? `${code} ${name}` : name || code;
          return {
            map: 'assetCategories',
            key: item?.id,
            value
          };
        }
      ),
      this.fetchReferenceData(
        this.getMasterDataUrl('equipment_setup'),
        (item) => ({
          map: 'equipmentSetup',
          key: item?.id,
          value: item?.name || ''
        })
      )
    ];

    await Promise.all(tasks);
    this.referenceDataLoaded = true;
  }

  async fetchReferenceData(url, transform) {
    try {
      const headers = this.getUserContextHeaders();
      const response = await fetch(url, { headers });
      const data = await response.json();
      if (data.status !== 'success' || !Array.isArray(data.data)) {
        return;
      }

      data.data.forEach((entry) => {
        const result = transform(entry) || {};
        const mapKey = result.map;
        const key = result.key;
        const value = result.value;

        if (!mapKey || key === undefined || key === null) {
          return;
        }

        const normalizedKey = String(key).trim();
        if (!normalizedKey) {
          return;
        }

        if (!this.referenceData[mapKey]) {
          this.referenceData[mapKey] = {};
        }

        const displayValue =
          value !== undefined && value !== null ? String(value).trim() : '';

        if (displayValue) {
          this.referenceData[mapKey][normalizedKey] = displayValue;
        }

        // สำหรับปีที่ดำเนินการ ให้แมป year เป็น key เพิ่มเติม
        if (
          mapKey === 'implementationYears' &&
          entry &&
          entry.year !== undefined &&
          entry.year !== null
        ) {
          const yearKey = String(entry.year).trim();
          if (yearKey && displayValue) {
            this.referenceData[mapKey][yearKey] = displayValue;
          }
        }
      });
    } catch (error) {
      console.warn(`⚠️ ไม่สามารถโหลดข้อมูลอ้างอิงจาก ${url}:`, error);
    }
  }

  renderRequestData() {
    const request = this.requestData;
    if (!request) {
      return;
    }

    this.setTextContent('request-number', request.request_number || 'N/A');
    this.setTextContent('created-date', this.formatDate(request.created_at));

    this.updateStatusBadge(request);

    this.setTextContent(
      'fiscal-year',
      this.formatIdAndName(request.fiscal_year_id, request.fiscal_year_name)
    );
    this.setTextContent(
      'version-plan',
      this.formatIdCodeName(
        request.version_plan_id,
        request.version_plan_code,
        request.version_plan_name
      )
    );
    this.setTextContent(
      'asset-type',
      this.formatIdAndName(request.asset_type_id, request.asset_type_name)
    );
    this.setTextContent(
      'asset-type-code',
      this.normalizeDisplayValue(request.asset_type_code) || '-'
    );
    this.setTextContent(
      'created-by',
      this.formatUser(request.created_by, request.created_by_username)
    );
    if (request.submitted_at) {
      this.setTextContent(
        'submitted-at',
        this.formatDate(request.submitted_at)
      );
      this.setDisplay('submission-info', 'block');
    } else {
      this.setDisplay('submission-info', 'none');
    }

    const approvedText = this.formatUser(
      request.approved_by,
      request.approved_by_username
    );
    this.setTextContent('approved-by', approvedText);
    if (approvedText && approvedText !== '-') {
      this.setDisplay('approved-info', 'block');
    } else {
      this.setDisplay('approved-info', 'none');
    }

    this.setTextContent(
      'branch-info',
      this.formatIdAndName(request.department_id, request.department_name)
    );
    this.setTextContent(
      'province-info',
      this.formatIdAndName(request.province_id, request.province_name)
    );
    this.setTextContent(
      'area-info',
      this.formatIdAndName(request.area_id, request.area_name)
    );
    this.setTextContent(
      'head-office-info',
      this.formatIdAndName(request.head_office_id, request.head_office_name)
    );

    this.renderApprovalHistory(request);
    this.updateApprovalSections(request);
    this.updateActionButtons(request.status);
  }

  createViewField(label, value, options = {}) {
    const {
      colClass = 'col-xl-6 col-lg-6',
      highlight = false,
      multiline = false,
      allowHtml = false,
      extraValueClass = ''
    } = options;

    if (value === null || value === undefined || value === '') {
      return '';
    }

    const valueClasses = ['view-value'];
    if (highlight) {
      valueClasses.push('text-primary', 'fw-semibold');
    }
    if (multiline) {
      valueClasses.push('view-value-multiline');
    }
    if (extraValueClass) {
      valueClasses.push(extraValueClass);
    }

    const displayValue = allowHtml ? value : this.escapeHtml(value);

    return `
      <div class="${colClass}">
        <div class="view-field">
          <label class="view-label">${this.escapeHtml(label)}</label>
          <div class="${valueClasses.join(' ')}">${displayValue}</div>
        </div>
      </div>
    `;
  }

  renderItemCard() {
    const container = document.getElementById('items-container');
    if (!container) return;

    const request = this.requestData;
    if (!request) {
      container.innerHTML = '';
      return;
    }

    if (!request.item_name) {
      container.innerHTML = `
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="ri-inbox-line fs-48 mb-3"></i>
                                <p class="mb-0">ไม่มีรายการในคำขอนี้</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
      return;
    }

    container.innerHTML = this.createItemCard(request);
  }

  renderDocuments() {
    const section = document.getElementById('documents-section');
    const container = document.getElementById('documents-container');

    if (!section || !container) {
      return;
    }

    const request = this.requestData;
    if (!request) {
      section.style.display = 'none';
      return;
    }

    let documents = [];

    // Parse attachments from different possible sources
    // 1. Try attachment_files (primary source)
    if (request.attachment_files) {
      try {
        if (typeof request.attachment_files === 'string') {
          documents = JSON.parse(request.attachment_files);
        } else if (Array.isArray(request.attachment_files)) {
          documents = request.attachment_files;
        }
      } catch (e) {
        console.warn('ไม่สามารถแปลงข้อมูล attachment_files ได้:', e);
      }
    }

    // 2. Try attachments (fallback)
    if (documents.length === 0 && request.attachments) {
      try {
        if (typeof request.attachments === 'string') {
          documents = JSON.parse(request.attachments);
        } else if (Array.isArray(request.attachments)) {
          documents = request.attachments;
        }
      } catch (e) {
        console.warn('ไม่สามารถแปลงข้อมูล attachments ได้:', e);
      }
    }

    // 3. Try pdf_attachments (another fallback)
    if (documents.length === 0 && request.pdf_attachments) {
      try {
        if (typeof request.pdf_attachments === 'string') {
          documents = JSON.parse(request.pdf_attachments);
        } else if (Array.isArray(request.pdf_attachments)) {
          documents = request.pdf_attachments;
        }
      } catch (e) {
        console.warn('ไม่สามารถแปลงข้อมูล pdf_attachments ได้:', e);
      }
    }

    console.log('📎 Documents found:', documents);

    // If no documents, hide the section
    if (!documents || documents.length === 0) {
      section.style.display = 'none';
      return;
    }

    // Show section and render documents
    section.style.display = 'block';
    container.innerHTML = documents
      .map((doc, index) => this.createDocumentItem(doc, index))
      .join('');
  }

  createDocumentItem(doc, index) {
    // Handle both object and string formats
    let fileName, filePath, fileSize, uploadDate;

    if (typeof doc === 'string') {
      // If doc is a string, it's the file path
      filePath = doc;
      fileName = doc.split('/').pop() || `เอกสาร ${index + 1}`;
      fileSize = 0;
      uploadDate = '';
    } else {
      // If doc is an object
      fileName =
        doc.filename ||
        doc.file_name ||
        doc.name ||
        doc.original_name ||
        `เอกสาร ${index + 1}`;
      filePath = doc.filepath || doc.file_path || doc.path || doc.url || '';
      fileSize = doc.filesize || doc.file_size || doc.size || 0;
      uploadDate = doc.upload_date || doc.uploaded_at || doc.created_at || '';
    }

    // Format file size
    const formattedSize = this.formatFileSize(fileSize);
    const formattedDate = uploadDate ? this.formatDate(uploadDate) : '';

    // Build full file URL - same logic as edit page
    let fileUrl;

    if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
      // Already a full URL
      fileUrl = filePath;
    } else {
      // Add leading slash if needed, then use origin
      const normalized = filePath.startsWith('/') ? filePath : `/${filePath}`;
      fileUrl = `${window.location.origin}${normalized}`;
    }

    return `
      <div class="document-item">
        <div class="document-icon">
          <i class="ri-file-pdf-2-line"></i>
        </div>
        <div class="document-info">
          <div class="document-name">${this.escapeHtml(fileName)}</div>
          <div class="document-meta">
            ${
              formattedSize
                ? `
              <div class="document-meta-item">
                <i class="ri-file-line"></i>
                <span>${formattedSize}</span>
              </div>
            `
                : ''
            }
            ${
              formattedDate
                ? `
              <div class="document-meta-item">
                <i class="ri-calendar-line"></i>
                <span>${formattedDate}</span>
              </div>
            `
                : ''
            }
          </div>
        </div>
        <div class="document-actions">
          <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary btn-document">
            <i class="ri-eye-line"></i>
            <span>ดูเอกสาร</span>
          </a>
          <a href="${fileUrl}" download class="btn btn-sm btn-outline-primary btn-document">
            <i class="ri-download-2-line"></i>
            <span>ดาวน์โหลด</span>
          </a>
        </div>
      </div>
    `;
  }

  formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '';

    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${Math.round((bytes / Math.pow(1024, i)) * 100) / 100} ${sizes[i]}`;
  }

  createItemCard(item) {
    const request = this.requestData || {};
    const serialNumbers = this.normalizeSerialNumbers(
      item.replacement_asset_numbers
    );

    let serialNumbersHtml = '';
    if (serialNumbers.length > 0) {
      const listItems = serialNumbers
        .map((serial) => {
          const normalized = this.formatSerialNumberEntry(serial);
          if (!normalized) {
            return '';
          }
          const safeSerial = this.escapeHtml(normalized);
          return `
            <div class="serial-number-item">
              <i class="ri-check-line text-success me-2"></i>${safeSerial}
            </div>
          `;
        })
        .filter(Boolean)
        .join('');

      if (listItems) {
        serialNumbersHtml = `<div class="serial-number-list">${listItems}</div>`;
      }
    }

    const assetTypeId = String(
      item.asset_type_id || request.asset_type_id || ''
    );
    const isEquipment = ['1', '2', '4', '7'].includes(assetTypeId);
    const isPlantation = ['5', '6'].includes(assetTypeId);
    const isLand = assetTypeId === '3';
    const isBuilding = assetTypeId === '8';
    const requiresInstallment = ['3', '7', '8', '9'].includes(assetTypeId);

    const hasDisplayValue = (value) => {
      if (value === null || value === undefined) return false;
      if (typeof value === 'string') {
        return value.trim() !== '';
      }
      return true;
    };

    const fields = [];
    const addField = (label, value, options = {}) => {
      if (!hasDisplayValue(value)) return;
      fields.push(this.createViewField(label, value, options));
    };
    const addTextField = (label, value, options = {}) => {
      const normalized = this.normalizeDisplayValue(value);
      if (!normalized || normalized === '-') return;
      addField(label, normalized, options);
    };
    const addHtmlField = (label, html, options = {}) => {
      if (!hasDisplayValue(html)) return;
      fields.push(
        this.createViewField(label, html, {
          ...options,
          allowHtml: true
        })
      );
    };
    const addIdNameField = (label, id, name, options = {}) => {
      const formatted = this.formatIdAndName(id, name);
      if (!formatted || formatted === '-') return;
      addField(label, formatted, options);
    };
    const addCurrencyField = (label, amount, options = {}) => {
      if (!hasDisplayValue(amount)) return;
      addField(label, `${this.formatCurrency(amount)} บาท`, options);
    };
    const addNumberField = (label, value, options = {}) => {
      if (!hasDisplayValue(value)) return;
      addField(label, this.formatNumber(value), options);
    };
    const reportBadgeWrap = (text) =>
      `<span class="badge bg-info text-dark me-2">Report</span>${text}`;
    const addReportField = (label, value, options = {}) => {
      const normalized = this.normalizeDisplayValue(value);
      if (!normalized || normalized === '-') return;
      addField(label, reportBadgeWrap(this.escapeHtml(normalized)), {
        ...options,
        allowHtml: true
      });
    };
    const addMultiYearField = (label, id, name, options = {}) => {
      if (!hasDisplayValue(id) && !hasDisplayValue(name)) return;
      const mapping = {
        1: 'เป็นแผนเบิกจ่ายภายใน 1 ปี',
        2: 'เป็นแผนเบิกจ่ายมากกว่า 1 ปี'
      };
      const key = String(id ?? '');
      const resolved = this.normalizeDisplayValue(name) || mapping[key] || key;
      addField(label, resolved, options);
    };
    const resolveStandardLabel = (standard) => {
      if (!hasDisplayValue(standard)) return '';
      const yesValues = ['yes', 'true', '1', 1, true];
      const noValues = ['no', 'false', '0', 0, false];
      if (yesValues.includes(standard)) return 'มี';
      if (noValues.includes(standard)) return 'ไม่มี';
      return `รหัสมาตรฐาน ${this.escapeHtml(String(standard))}`;
    };
    const mapProjectPeriod = (value, fallbackName) => {
      const explicit = this.normalizeDisplayValue(fallbackName);
      if (explicit) return explicit;
      const key = this.normalizeId(value);
      if (!key) return '';
      return (
        this.referenceData.projectPeriods[key] ||
        this.formatDisbursementDate(value) ||
        key
      );
    };
    const mapImplementationYear = (value, fallbackName) => {
      const explicit = this.normalizeDisplayValue(fallbackName);
      if (explicit) return explicit;
      const key = this.normalizeId(value);
      if (!key) return '';
      if (/^\d{4}$/.test(key)) {
        return key;
      }
      return (
        this.referenceData.implementationYears[key] ||
        this.referenceData.implementationYears[String(Number(key))] ||
        key
      );
    };
    const mapInstallmentWork = (value, fallbackName) => {
      const explicit = this.normalizeDisplayValue(fallbackName);
      if (explicit) return explicit;
      const key = this.normalizeId(value);
      if (!key) return '';
      return this.referenceData.installmentWorks[key] || key;
    };
    const mapAssetCategory = (value, name, code) => {
      const normalizedName = this.normalizeDisplayValue(name);
      const normalizedCode = this.normalizeDisplayValue(code);
      if (normalizedCode && normalizedName) {
        return `${normalizedCode} ${normalizedName}`;
      }
      const key = this.normalizeId(value);
      if (key && this.referenceData.assetCategories[key]) {
        return this.referenceData.assetCategories[key];
      }
      if (normalizedName) return normalizedName;
      if (normalizedCode) return normalizedCode;
      return key;
    };

    // ===== COMMON FIELDS =====
    addIdNameField(
      'แหล่งงบประมาณ',
      item.budget_source_id,
      item.budget_source_name,
      {
        colClass: 'col-xl-6 col-lg-6'
      }
    );
    addTextField('รายการ', item.item_name, {
      colClass: 'col-xl-6 col-lg-6'
    });
    addTextField('รายละเอียด', item.description, {
      colClass: 'col-12',
      multiline: true
    });

    // ===== EQUIPMENT QUANTITY & PRICE =====
    if (isEquipment) {
      addNumberField('จำนวน (หน่วย)', item.quantity, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      addCurrencyField('ราคา (ต่อหน่วย)', item.unit_price, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
    }

    addCurrencyField('จำนวนเงินรวม', item.total_amount, {
      colClass: 'col-xl-4 col-lg-4 col-md-6',
      highlight: true
    });

    // ===== EQUIPMENT DETAILS =====
    if (isEquipment) {
      if (hasDisplayValue(item.has_standard)) {
        addField('มาตรฐานครุภัณฑ์', resolveStandardLabel(item.has_standard), {
          colClass: 'col-xl-4 col-lg-4 col-md-6'
        });
      }
      const equipmentSetupLabel =
        item.asset_nature_name ||
        this.getEquipmentSetupLabel(item.asset_nature) ||
        item.asset_nature;
      addTextField('ลักษณะ', equipmentSetupLabel, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      addTextField(
        'หมวดสินทรัพย์',
        mapAssetCategory(
          item.asset_category,
          item.asset_category_name,
          item.asset_category_code
        ),
        {
          colClass: 'col-xl-4 col-lg-4 col-md-6'
        }
      );
      addReportField(
        'ชนิดรหัสครุภัณฑ์ (Report)',
        this.getEquipmentCodeTypeLabel?.(item),
        { colClass: 'col-xl-6 col-lg-6' }
      );
      addReportField(
        'ประเภทรหัสครุภัณฑ์ (Report)',
        this.getEquipmentTypeLabel?.(item),
        { colClass: 'col-xl-6 col-lg-6' }
      );
      addReportField('หน่วยนับ (Report)', this.getCountUnitLabel?.(item), {
        colClass: 'col-xl-6 col-lg-6'
      });
      addReportField(
        'รหัสบัญชี (Report)',
        item.account_code ||
          item.equipment_type_code ||
          item.equipment_code_type_code,
        { colClass: 'col-xl-6 col-lg-6' }
      );
    } else {
      addTextField(
        'หมวดสินทรัพย์',
        mapAssetCategory(
          item.asset_category,
          item.asset_category_name,
          item.asset_category_code
        ),
        {
          colClass: 'col-xl-4 col-lg-4 col-md-6'
        }
      );
    }

    // ===== REASON =====
    addTextField('เหตุผลความจำเป็น', item.reason, {
      colClass: 'col-12',
      multiline: true
    });

    // ===== STRATEGY / PROGRAM =====
    addIdNameField('ยุทธศาสตร์', item.strategy, item.strategy_name, {
      colClass: 'col-xl-6 col-lg-6'
    });
    addTextField('แผนงาน / โครงการ', item.project_program, {
      colClass: 'col-xl-6 col-lg-6'
    });

    // ===== DISBURSEMENT INFO =====
    const expectedDisbursementLabel = this.formatDisbursementDate(
      item.expected_disbursement_date
    );
    addField('วันที่คาดว่าจะเบิกจ่าย', expectedDisbursementLabel, {
      colClass: 'col-xl-6 col-lg-6'
    });
    addMultiYearField(
      'แผนเบิกจ่ายมากกว่า 1 ปี',
      item.multi_year_disbursement,
      item.multi_year_disbursement_name,
      { colClass: 'col-xl-6 col-lg-6' }
    );

    // ===== PLANTATION FIELDS =====
    if (isPlantation) {
      addNumberField('พื้นที่ (ไร่)', item.area_rai, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      const plantationStart = mapProjectPeriod(
        item.project_start_period,
        item.project_start_period_name
      );
      addTextField('ระยะเวลาเริ่มต้น โครงการฯ', plantationStart, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      const plantationEnd = mapProjectPeriod(
        item.project_end_period,
        item.project_end_period_name
      );
      addTextField('ระยะเวลาสิ้นสุด โครงการฯ', plantationEnd, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      const implementationLabel = mapImplementationYear(
        item.operation_year,
        item.operation_year_name
      );
      addTextField('ปีที่ดำเนินการ', implementationLabel, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      addReportField(
        'ชนิดรหัสครุภัณฑ์ (Report)',
        this.getEquipmentCodeTypeLabel?.(item),
        { colClass: 'col-xl-6 col-lg-6' }
      );
      addReportField(
        'ประเภทรหัสครุภัณฑ์ (Report)',
        this.getEquipmentTypeLabel?.(item),
        { colClass: 'col-xl-6 col-lg-6' }
      );
      addReportField('หน่วยนับ (Report)', this.getCountUnitLabel?.(item), {
        colClass: 'col-xl-6 col-lg-6'
      });
      addReportField(
        'รหัสบัญชี (Report)',
        item.account_code ||
          item.equipment_type_code ||
          item.equipment_code_type_code,
        { colClass: 'col-xl-6 col-lg-6' }
      );
    }

    // ===== LAND / BUILDING FIELDS =====
    if (isLand) {
      const landStart = mapProjectPeriod(
        item.project_start_period,
        item.project_start_period_name
      );
      addTextField('ช่วงเริ่มโครงการ', landStart, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      const landEnd = mapProjectPeriod(
        item.project_end_period,
        item.project_end_period_name
      );
      addTextField('ช่วงสิ้นสุดโครงการ', landEnd, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
      const operationLabel = mapImplementationYear(
        item.operation_year,
        item.operation_year_name
      );
      addTextField('ปีปฏิบัติการ', operationLabel, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
    }

    if (isBuilding) {
      const operationLabel = mapImplementationYear(
        item.operation_year,
        item.operation_year_name
      );
      addTextField('ปีปฏิบัติการ', operationLabel, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });
    }

    if (requiresInstallment) {
      const workLabel = mapInstallmentWork(
        item.work_period,
        item.work_period_name
      );
      addTextField('งานงวด', workLabel, {
        colClass: 'col-xl-4 col-lg-4 col-md-6'
      });

      if (hasDisplayValue(item.work_period_number)) {
        addTextField(
          'จำนวนงวด',
          `${this.formatNumber(item.work_period_number)} งวด`,
          {
            colClass: 'col-xl-4 col-lg-4 col-md-6'
          }
        );
      }
    }

    if (serialNumbersHtml) {
      addHtmlField('หมายเลขครุภัณฑ์ทดแทน', serialNumbersHtml, {
        colClass: 'col-12'
      });
    }

    const content = fields.filter(Boolean).join('');

    return `
      <div class="row view-section">
        <div class="col-xl-12">
          <div class="card custom-card">
            <div class="card-body">
              <div class="row gy-4 gx-3">
                ${
                  content ||
                  '<div class="col-12 text-muted">ไม่มีรายละเอียดเพิ่มเติม</div>'
                }
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  renderApprovalHistory(request) {
    if (!request) {
      return;
    }

    const section = document.getElementById('approval-history-section');
    const tbody = document.getElementById('approval-history-body');
    const stageBadge = document.getElementById('approval-current-stage');

    if (!section || !tbody) {
      return;
    }

    section.style.display = 'block';

    if (stageBadge) {
      if (request.current_stage_label) {
        stageBadge.textContent = `ขั้นตอนปัจจุบัน: ${request.current_stage_label}`;
        stageBadge.style.display = 'inline-flex';
      } else {
        stageBadge.style.display = 'none';
      }
    }

    const history = Array.isArray(request.approvals_history)
      ? request.approvals_history
      : [];

    if (!history.length) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center approval-history-empty">ไม่พบข้อมูล</td>
                </tr>
            `;
      return;
    }

    const rows = history
      .map((entry, index) => {
        const approvedAt =
          entry.approved_at_formatted || this.formatDate(entry.approved_at);
        const actionLabel = entry.action_label || entry.action || '-';
        const approverName = entry.approver_name
          ? this.escapeHtml(entry.approver_name)
          : '-';
        const stageLabel = entry.stage_label
          ? `<div class="text-muted small">${this.escapeHtml(
              entry.stage_label
            )}</div>`
          : '';
        const comments = entry.comments ? this.escapeHtml(entry.comments) : '-';

        return `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">${this.escapeHtml(approvedAt)}</td>
                    <td class="text-center">${this.escapeHtml(actionLabel)}</td>
                    <td>${approverName}${stageLabel}</td>
                    <td>${comments}</td>
                </tr>
            `;
      })
      .join('');

    tbody.innerHTML = rows;
  }

  updateApprovalSections(request) {
    if (!request) {
      return;
    }
    const actionSection = document.getElementById('approval-action-section');
    const permissionSection = document.getElementById(
      'approval-permission-section'
    );
    const permissionAlert = document.getElementById(
      'approval-permission-alert'
    );

    if (!actionSection || !permissionSection || !permissionAlert) {
      return;
    }

    actionSection.style.display = 'none';
    permissionSection.style.display = 'none';

    const status = (request.status || '').toLowerCase();
    const isFinalStatus = ['approved', 'rejected'].includes(status);
    const isReturned = this.parseBooleanFlag(request.is_returned);

    this.renderReturnBanner(isReturned ? request : null);

    if (!isFinalStatus && request.can_approve) {
      actionSection.style.display = 'block';
      this.populateApprovalForm();
      return;
    }

    const messages = [];
    permissionAlert.style.display = 'none';
    permissionAlert.innerHTML = '';
    if (isReturned && !request.can_approve) {
      const fromLabel =
        this.getStageLabel(request.returned_from_stage) || 'หน่วยงานก่อนหน้า';
      let message = `คำขอนี้ถูกส่งกลับจาก ${fromLabel}`;
      if (request.returned_reason) {
        message += ` - ${request.returned_reason}`;
      }
      messages.push(message);
    }

    if (request.current_stage_label) {
      messages.push(`ขั้นตอนปัจจุบัน: ${request.current_stage_label}`);
    }
    if (request.approval_scope_reason) {
      messages.push(request.approval_scope_reason);
    }
    if (isFinalStatus) {
      const finalLabel =
        status === 'approved'
          ? 'คำขอได้รับการอนุมัติเรียบร้อยแล้ว'
          : 'คำขอนี้ถูกปฏิเสธแล้ว';
      messages.push(finalLabel);
    }

    if (!request.can_approve && !isFinalStatus && !messages.length) {
      messages.push('คุณไม่มีสิทธิ์พิจารณาคำขอนี้ในขั้นตอนปัจจุบัน');
    }

    if (messages.length) {
      permissionAlert.innerHTML = messages
        .map((msg) => `<div>${this.escapeHtml(msg)}</div>`)
        .join('');
      permissionAlert.style.display = 'none';
      permissionSection.style.display = 'none';
    }
  }

  formatBudgetStatusBadge(status) {
    const normalized = String(status || '').toLowerCase();
    switch (normalized) {
      case 'approved':
        return '<span class="badge bg-success">อนุมัติงบประมาณแล้ว</span>';
      case 'rejected':
        return '<span class="badge bg-danger">ไม่อนุมัติงบประมาณ</span>';
      default:
        return '<span class="badge bg-secondary">รอพิจารณางบประมาณ</span>';
    }
  }

  isBudgetDecisionFinal(status) {
    const normalized = String(status || '').toLowerCase();
    return normalized === 'approved' || normalized === 'rejected';
  }

  canBudgetApprove(request) {
    if (!request) return false;

    const canHeadOffice = this.parseBooleanFlag(
      this.currentUser?.approve_head_office
    );
    const canInvestment = this.parseBooleanFlag(
      this.currentUser?.approve_investment
    );

    const hasBudgetDecision = this.isBudgetDecisionFinal(
      request.budget_approval_status
    );

    return canHeadOffice && canInvestment && !hasBudgetDecision;
  }

  renderBudgetApprovalSection() {
    const sectionRow = document.getElementById('budget-approval-row');
    if (!sectionRow) return;

    const request = this.requestData || {};
    const statusBadge = document.getElementById(
      'budget-approval-status-badge'
    );
    if (statusBadge) {
      statusBadge.innerHTML = this.formatBudgetStatusBadge(
        request.budget_approval_status
      );
    }

    const hasBudgetDecision = this.isBudgetDecisionFinal(
      request.budget_approval_status
    );
    const isApprovalMode =
      this.isBudgetApprovalMode ||
      window.BUDGET_APPROVAL_MODE_OVERRIDE === true;
    const shouldShow = isApprovalMode || Boolean(request.budget_approval_status);

    if (!shouldShow) {
      sectionRow.classList.add('d-none');
      return;
    }

    sectionRow.classList.remove('d-none');

    // หากเป็นโหมดพิจารณา ให้ใช้ฟอร์มและปุ่มบันทึก
    if (isApprovalMode) {
      const resultSelect = document.getElementById('budget-approval-result');
      const amountInput = document.getElementById('budget-approval-amount');
      const remarkInput = document.getElementById('budget-approval-remark');

      if (resultSelect && request.budget_approval_status) {
        resultSelect.value = request.budget_approval_status;
      }
      if (amountInput && request.budget_approved_amount !== undefined) {
        amountInput.value = request.budget_approved_amount || '';
      }
      if (remarkInput && request.budget_approval_remark) {
        remarkInput.value = request.budget_approval_remark;
      }

      const actions = document.getElementById('budget-approval-actions');
      if (actions) {
        actions.style.display = !hasBudgetDecision ? 'flex' : 'none';
      }

      const fields = [resultSelect, amountInput, remarkInput];
      fields.forEach((field) => {
        if (!field) return;
        if (hasBudgetDecision) {
          field.setAttribute('readonly', 'readonly');
          field.setAttribute('disabled', 'disabled');
          field.classList.add('bg-light');
        } else {
          field.removeAttribute('readonly');
          field.removeAttribute('disabled');
          field.classList.remove('bg-light');
        }
      });

      if (
        resultSelect &&
        resultSelect.choicesInstance &&
        hasBudgetDecision
      ) {
        resultSelect.choicesInstance.disable();
      } else if (resultSelect && resultSelect.choicesInstance) {
        resultSelect.choicesInstance.enable();
      }

      document
        .getElementById('budget-save-btn')
        ?.addEventListener('click', () => this.submitBudgetApproval(null));
      return;
    }

    // โหมดดูรายละเอียด: แสดงเป็น view-only
    const resultDisplay = document.getElementById(
      'budget-approval-result-display'
    );
    const amountDisplay = document.getElementById(
      'budget-approval-amount-display'
    );
    const remarkDisplay = document.getElementById(
      'budget-approval-remark-display'
    );

    if (resultDisplay) {
      const statusText = this.formatBudgetStatusText(
        request.budget_approval_status
      );
      resultDisplay.textContent = statusText || '-';
    }
    if (amountDisplay) {
      amountDisplay.textContent =
        request.budget_approved_amount !== undefined &&
        request.budget_approved_amount !== null &&
        request.budget_approved_amount !== ''
          ? this.formatCurrencyDisplay(request.budget_approved_amount)
          : '-';
    }
    if (remarkDisplay) {
      remarkDisplay.textContent = request.budget_approval_remark || '-';
    }

    // meta
    const meta = document.getElementById('budget-approval-meta');
    if (meta && request.budget_approved_at) {
      const approver =
        request.approver_name ||
        request.approved_by_username ||
        request.budget_approved_by ||
        '-';
      meta.textContent = `ล่าสุดโดย ${approver} เมื่อ ${this.formatDate(
        request.budget_approved_at
      )}`;
    }

    // ไม่แสดงปุ่มใดๆ บนหน้า view detail
  }

  formatBudgetStatusText(status) {
    const normalized = String(status || '').toLowerCase();
    switch (normalized) {
      case 'approved':
        return 'อนุมัติ';
      case 'rejected':
        return 'ไม่อนุมัติ';
      default:
        return '-';
    }
  }

  formatCurrencyDisplay(value) {
    const num = Number(
      typeof value === 'string' ? value.replace(/,/g, '') : value
    );
    if (!Number.isFinite(num)) return '-';
    return num.toLocaleString('th-TH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  initBudgetApprovalChoices() {
    const select = document.getElementById('budget-approval-result');
    if (!select || typeof Choices === 'undefined') {
      return;
    }

    if (select.choicesInstance) {
      try {
        select.choicesInstance.destroy();
      } catch (e) {
        console.warn('Destroy Choices failed', e);
      }
      select.choicesInstance = null;
    }

    const instance = new Choices(select, {
      searchEnabled: false,
      itemSelectText: '',
      shouldSort: false,
      placeholder: true,
      placeholderValue: 'กรุณาเลือก'
    });
    select.choicesInstance = instance;

    // reapply existing value if any
    if (select.value) {
      try {
        instance.setChoiceByValue(String(select.value));
      } catch (e) {
        console.warn('setChoiceByValue failed', e);
      }
    }

    // lock selection if already decided
    // ถ้ามีผลพิจารณาแล้ว (approved/rejected) ค่อย disable; ถ้ายังไม่มี ให้เลือกได้
    if (
      this.requestData &&
      this.isBudgetDecisionFinal(this.requestData.budget_approval_status)
    ) {
      instance.disable();
    } else {
      instance.enable();
    }
  }

  async submitBudgetApproval(action) {
    if (this.isSubmittingApproval) return;
    const resultSelect = document.getElementById('budget-approval-result');
    const amountInput = document.getElementById('budget-approval-amount');
    const remarkInput = document.getElementById('budget-approval-remark');

    const resultValue = resultSelect?.value || action;
    if (!resultValue) {
      alert('กรุณาเลือกผลการอนุมัติงบประมาณ');
      return;
    }

    this.isSubmittingApproval = true;
    try {
      const formData = new FormData();
      formData.append('action', resultValue);
      formData.append('approved_amount', amountInput?.value || '');
      formData.append('remark', remarkInput?.value || '');

      const headers = this.getUserContextHeaders();
      const response = await fetch(
        `../controllers/investment_budget/ib_requests_controller.php?action=budget_approve&id=${this.requestId}`,
        {
          method: 'POST',
          headers,
          body: formData
        }
      );
      const data = await response.json();
      if (data.status === 'success') {
        if (window.Swal) {
          await Swal.fire({
            icon: 'success',
            title: 'บันทึกสำเร็จ',
            text: 'บันทึกผลการอนุมัติงบประมาณแล้ว'
          });
        } else {
          alert('บันทึกผลการอนุมัติงบประมาณแล้ว');
        }
        window.location.reload();
      } else {
        throw new Error(data.message || 'บันทึกไม่สำเร็จ');
      }
    } catch (error) {
      console.error(error);
      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: error.message
        });
      } else {
        alert(error.message || 'เกิดข้อผิดพลาด');
      }
    } finally {
      this.isSubmittingApproval = false;
    }
  }

  populateApprovalForm() {
    const approverInput = document.getElementById('approval-approver-name');
    const approvalDateInput = document.getElementById('approval-date');
    const commentsInput = document.getElementById('approval-comments');
    const commentsRequired = document.getElementById(
      'approval-comments-required'
    );
    const submitBtn = document.getElementById('approval-submit-btn');

    if (approverInput) {
      approverInput.value = this.getCurrentUserName();
    }

    if (approvalDateInput) {
      approvalDateInput.value = this.formatDate(new Date());
    }

    if (commentsInput) {
      commentsInput.value = '';
    }

    if (commentsRequired) {
      commentsRequired.style.display = 'none';
    }

    const actionRadios = document.querySelectorAll(
      'input[name="approval-action"]'
    );
    actionRadios.forEach((radio) => {
      radio.checked = false;
      radio.disabled = false;
      radio.removeAttribute('title');
      radio.classList.remove('disabled');
    });

    if (submitBtn) {
      submitBtn.disabled = false;
      if (!submitBtn.dataset.defaultText) {
        submitBtn.dataset.defaultText = submitBtn.innerHTML;
      } else {
        submitBtn.innerHTML = submitBtn.dataset.defaultText;
      }
    }

    const isReturned = this.parseBooleanFlag(this.requestData?.is_returned);
    if (isReturned) {
      const approveRadio = document.getElementById('approval-action-approve');
      const rejectRadio = document.getElementById('approval-action-reject');

      if (approveRadio) {
        approveRadio.checked = false;
        approveRadio.disabled = true;
        approveRadio.title =
          'ไม่สามารถอนุมัติได้ เนื่องจากคำขอนี้ถูกส่งกลับจากหน่วยงานลำดับถัดไป';
        approveRadio.classList.add('disabled');
      }

      if (rejectRadio) {
        rejectRadio.checked = true;
      }

      if (submitBtn) {
        submitBtn.innerHTML = 'ส่งกลับลำดับถัดไป';
      }
    }

    this.handleApprovalActionChange();
  }

  handleApprovalActionChange() {
    const commentsRequired = document.getElementById(
      'approval-comments-required'
    );
    const rejectRadio = document.getElementById('approval-action-reject');

    if (!commentsRequired) {
      return;
    }

    if (rejectRadio && rejectRadio.checked) {
      commentsRequired.style.display = 'inline';
    } else {
      commentsRequired.style.display = 'none';
    }
  }

  toggleApprovalSubmitting(isSubmitting) {
    const submitBtn = document.getElementById('approval-submit-btn');
    if (!submitBtn) {
      return;
    }

    if (!submitBtn.dataset.defaultText) {
      submitBtn.dataset.defaultText = submitBtn.innerHTML;
    }

    submitBtn.disabled = isSubmitting;
    submitBtn.innerHTML = isSubmitting
      ? '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>กำลังบันทึก...'
      : submitBtn.dataset.defaultText;
  }

  showToast(message, type = 'success') {
    if (window.Swal && typeof window.Swal.fire === 'function') {
      window.Swal.fire({
        icon: type,
        title: message,
        timer: 2000,
        showConfirmButton: false
      });
    } else {
      alert(message);
    }
  }

  getCurrentUserName() {
    if (!this.currentUser) {
      return '-';
    }

    const fname = this.currentUser.user_fname || '';
    const lname = this.currentUser.user_lname || '';
    const fullName = `${fname} ${lname}`.trim();
    return fullName || this.currentUser.username || '-';
  }

  escapeHtml(value) {
    if (value === null || value === undefined) {
      return '';
    }

    return value
      .toString()
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  formatDisbursementDate(dateString) {
    if (!dateString) return 'N/A';

    const months = {
      '01': 'มกราคม',
      '02': 'กุมภาพันธ์',
      '03': 'มีนาคม',
      '04': 'เมษายน',
      '05': 'พฤษภาคม',
      '06': 'มิถุนายน',
      '07': 'กรกฎาคม',
      '08': 'สิงหาคม',
      '09': 'กันยายน',
      10: 'ตุลาคม',
      11: 'พฤศจิกายน',
      12: 'ธันวาคม'
    };

    const parts = dateString.split('-');
    if (parts.length === 2) {
      const year = parts[0];
      const month = parts[1];
      return `${months[month]} ${year}`;
    }

    return dateString;
  }

  updateActionButtons(status) {
    const btnEdit = document.getElementById('btn-edit');
    if (!btnEdit) {
      return;
    }

    btnEdit.style.display = 'none';

    if (status === 'draft') {
      btnEdit.style.display = 'inline-block';
    }
  }

  showError(message) {
    const loadingState = document.getElementById('loading-state');
    const mainContent = document.getElementById('main-content');
    const errorState = document.getElementById('error-state');
    const errorMessage = document.getElementById('error-message');

    if (loadingState) loadingState.style.display = 'none';
    if (mainContent) mainContent.style.display = 'none';
    if (errorMessage) errorMessage.textContent = message;
    if (errorState) errorState.style.display = 'block';
  }

  showMainContent() {
    const loadingState = document.getElementById('loading-state');
    const errorState = document.getElementById('error-state');
    const mainContent = document.getElementById('main-content');

    if (loadingState) loadingState.style.display = 'none';
    if (errorState) errorState.style.display = 'none';
    if (mainContent) mainContent.style.display = 'block';
  }

  setupEventListeners() {
    const btnEdit = document.getElementById('btn-edit');
    if (btnEdit) {
      btnEdit.addEventListener('click', () => {
        window.location.href = `investment-budget-form.php?id=${this.requestId}`;
      });
    }

    const submitBtn = document.getElementById('approval-submit-btn');
    if (submitBtn) {
      submitBtn.addEventListener('click', () => {
        this.handleApprovalSubmit();
      });
    }

    const approvalRadios = document.querySelectorAll(
      'input[name="approval-action"]'
    );
    approvalRadios.forEach((radio) => {
      radio.addEventListener('change', () => this.handleApprovalActionChange());
    });

    const btnPrint = document.getElementById('btn-print');
    if (btnPrint) {
      btnPrint.addEventListener('click', () => {
        window.print();
      });
    }
  }

  async handleApprovalSubmit() {
    if (this.isSubmittingApproval) {
      return;
    }

    const selectedAction = document.querySelector(
      'input[name="approval-action"]:checked'
    );
    if (!selectedAction) {
      this.showToast('กรุณาเลือกผลการอนุมัติ', 'warning');
      return;
    }

    const action = selectedAction.value;
    const commentsInput = document.getElementById('approval-comments');
    const comments = commentsInput ? commentsInput.value.trim() : '';

    if (action === 'reject' && !comments) {
      this.showToast('กรุณากรอกหมายเหตุเมื่อไม่อนุมัติ', 'warning');
      if (commentsInput) {
        commentsInput.focus();
      }
      return;
    }

    // แสดง confirmation dialog ก่อนส่งข้อมูล
    const confirmResult = await Swal.fire({
      icon: 'question',
      title: 'ยืนยันการพิจารณา',
      text:
        action === 'approve'
          ? 'คุณต้องการอนุมัติคำขอนี้ใช่หรือไม่?'
          : 'คุณต้องการปฏิเสธคำขอนี้ใช่หรือไม่?',
      showCancelButton: true,
      confirmButtonText: 'ยืนยัน',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
      cancelButtonColor: '#6c757d',
      reverseButtons: false
    });

    // ถ้าผู้ใช้กด Cancel ให้หยุดการทำงาน
    if (!confirmResult.isConfirmed) {
      return;
    }

    const payload = {
      action,
      comments
    };

    const url = `../controllers/investment_budget/ib_requests_controller.php?action=process_approval&id=${this.requestId}`;

    try {
      this.isSubmittingApproval = true;
      this.toggleApprovalSubmitting(true);
      if (typeof window.showLoading === 'function') {
        window.showLoading();
      }

      const userContext = this.getUserContextPayload();
      if (userContext) {
        Object.assign(payload, userContext);
      }

      const headers = this.getUserContextHeaders();
      headers['Content-Type'] = 'application/json';
      headers['Accept'] = 'application/json';

      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (!response.ok || data.status !== 'success') {
        throw new Error(data.message || 'ไม่สามารถบันทึกผลการอนุมัติได้');
      }

      this.requestData = data.data;
      console.log('✅ บันทึกผลการอนุมัติสำเร็จ:', this.requestData);

      this.renderRequestData();
      this.renderItemCard();
      this.renderDocuments();

      window.hideLoading();

      await Swal.fire({
        icon: 'success',
        title: 'บันทึกเรียบร้อย',
        text: 'บันทึกผลการพิจารณาเรียบร้อย',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#28a745'
      });

      window.location.href = 'investment-budget-approvel-list.php';
      return;

      //this.showToast('บันทึกผลการพิจารณาเรียบร้อย', 'success');
      // Redirect to the approve list after successful approval (with slight delay for user to see toast)
      // setTimeout(() => {
      //   window.location.href = 'investment-budget-approvel-list.php';
      // }, 800);
    } catch (error) {
      console.error('❌ ไม่สามารถบันทึกผลการอนุมัติได้:', error);
      this.showToast(
        error.message || 'เกิดข้อผิดพลาดในการบันทึกผลการอนุมัติ',
        'error'
      );
    } finally {
      if (typeof window.hideLoading === 'function') {
        window.hideLoading();
      }
      this.isSubmittingApproval = false;
      this.toggleApprovalSubmitting(false);
    }
  }

  formatDate(dateString) {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
      return 'N/A';
    }
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543;
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${day}/${month}/${year} ${hours}:${minutes} น.`;
  }

  formatCurrency(amount) {
    const value = Number(amount);
    if (Number.isNaN(value)) {
      return '0.00';
    }

    return new Intl.NumberFormat('th-TH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value);
  }

  getEquipmentCodeTypeLabel(item) {
    const code = item?.equipment_code_type_code || '';
    const name = item?.equipment_code_type_name || '';
    if (code && name) return `${code} - ${name}`;
    if (code) return code;
    if (name) return name;
    return '';
  }

  getEquipmentTypeLabel(item) {
    const code = item?.equipment_type_code || '';
    const name = item?.equipment_type_name || '';
    if (code && name) return `${code} - ${name}`;
    if (code) return code;
    if (name) return name;
    return '';
  }

  getCountUnitLabel(item) {
    if (!item) return '';
    if (item.count_unit_name) {
      return item.count_unit_name;
    }
    if (item.count_unit_code && item.count_unit_description) {
      return `${item.count_unit_code} - ${item.count_unit_description}`;
    }
    if (item.count_unit_description) {
      return item.count_unit_description;
    }
    if (item.count_unit_code) {
      return item.count_unit_code;
    }
    return '';
  }

  formatNumber(number) {
    const value = Number(number);
    if (Number.isNaN(value)) {
      return '0';
    }
    return new Intl.NumberFormat('th-TH').format(value);
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

  getStatusConfig(status) {
    const normalized = (status || '').toLowerCase();

    if (!normalized) {
      return { class: 'bg-secondary', text: 'ไม่ระบุสถานะ' };
    }

    if (normalized.startsWith('pending_')) {
      const stage = normalized.replace('pending_', '');
      const label = this.getStageLabel(stage);
      return {
        class: 'bg-info text-white',
        text: label ? `รออนุมัติ (${label})` : 'รออนุมัติ'
      };
    }

    const statusConfig = {
      draft: { class: 'bg-primary', text: 'บันทึกร่าง' },
      submitted: { class: 'bg-warning', text: 'รออนุมัติ' },
      approved: { class: 'bg-success', text: 'อนุมัติแล้ว' },
      rejected: { class: 'bg-danger', text: 'ปฏิเสธ' }
    };

    return (
      statusConfig[normalized] || {
        class: 'bg-secondary',
        text: status || 'N/A'
      }
    );
  }

  updateStatusBadge(request) {
    const statusBadge = document.getElementById('status-badge');
    if (!statusBadge) {
      return;
    }
    const statusConfig = this.getStatusConfig(request.status);
    statusBadge.textContent = statusConfig.text;
    statusBadge.className = `badge status-badge ${statusConfig.class}`;

    const container = statusBadge.parentElement;
    if (!container) {
      return;
    }

    let returnBadge = document.getElementById('status-return-badge');
    if (!this.parseBooleanFlag(request.is_returned)) {
      if (returnBadge) {
        returnBadge.remove();
      }
      return;
    }

    if (!returnBadge) {
      returnBadge = document.createElement('span');
      returnBadge.id = 'status-return-badge';
      returnBadge.className = 'badge bg-danger text-white ms-2';
      container.appendChild(returnBadge);
    }

    const stageLabel =
      this.getStageLabel(request.returned_from_stage) || 'หน่วยงานก่อนหน้า';
    returnBadge.textContent = `ส่งกลับจาก ${stageLabel}`;
    returnBadge.style.display = 'inline-block';
  }

  renderReturnBanner(request) {
    const bannerId = 'approval-return-banner';
    let banner = document.getElementById(bannerId);

    if (!request) {
      if (banner) {
        banner.remove();
      }
      return;
    }

    if (!banner) {
      const actionSection = document.getElementById('approval-action-section');
      const referenceNode =
        actionSection ||
        document.getElementById('approval-permission-section');

      if (!referenceNode || !referenceNode.parentNode) {
        return;
      }

      banner = document.createElement('div');
      banner.id = bannerId;
      banner.className = 'alert alert-warning mb-3';
      referenceNode.parentNode.insertBefore(banner, referenceNode);
    }

    const stageLabel =
      this.getStageLabel(request.returned_from_stage) || 'หน่วยงานก่อนหน้า';
    const reason = request.returned_reason
      ? `<div class="small mt-1 mb-0 text-secondary">เหตุผล: ${this.escapeHtml(
          String(request.returned_reason)
        )}</div>`
      : '';

    banner.innerHTML = `<strong>คำขอนี้ถูกส่งกลับจาก ${this.escapeHtml(
      stageLabel
    )}</strong>${reason}`;
    banner.style.display = 'block';
  }

  parseBooleanFlag(value) {
    if (typeof value === 'boolean') {
      return value;
    }
    if (value === null || value === undefined) {
      return false;
    }
    if (typeof value === 'number') {
      return value > 0;
    }
    const normalized = String(value).trim().toLowerCase();
    if (normalized === '') {
      return false;
    }
    return ['1', 'true', 't', 'yes', 'y'].includes(normalized);
  }

  getItemTypeLabel(type) {
    const labels = {
      machinery: 'เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร',
      equipment: 'เครื่องตกแต่งและอุปกรณ์สำนักงาน',
      vehicle: 'ยานพาหนะและขนส่ง',
      building: 'อาคารและสิ่งปลูกสร้าง',
      land: 'ที่ดิน',
      placeholder: 'แบบร่าง',
      other: 'อื่นๆ'
    };

    return labels[type] || (type ? `ประเภท: ${type}` : 'ไม่ระบุประเภท');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const viewDetail = new InvestmentBudgetRequestView();
  viewDetail.initialize();
});
