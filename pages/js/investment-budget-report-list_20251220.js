// Initializes the investment budget report screen and handles data interactions.
class InvestmentBudgetReportManager {
  constructor() {
    this.currentTab = 'all';
    this.currentPage = {
      all: 1,
      equipment: 1,
      construction: 1,
      plantation: 1
    };
    this.perPage = 10;
    this.filters = {
      requestNumber: '',
      startDate: '',
      endDate: '',
      fiscalYear: '',
      creator: '',
      budgetSource: '',
      assetType: '',
      status: 'approved',
      creatorOption: ''
    };
    this.fiscalYears = [];
    this.departments = [];
    this.budgetSources = [];
    this.assetTypes = [];
    this.data = {
      all: [],
      equipment: [],
      construction: [],
      plantation: []
    };
    this.currentModalData = {
      itemId: null,
      field: null,
      cellElement: null
    };
    this.pendingChanges = {};
    this.masterData = {
      equipmentCodeTypes: [],
      equipmentTypes: [],
      countUnits: [],
      fundAreas: []
    };
    this.savedItems = new Set();
    this.startPicker = null;
    this.endPicker = null;
    this.currentUser = null;

    this.ensureStylesInjected();
  }

  ensureStylesInjected() {
    if (document.getElementById('ib-report-lock-style')) {
      return;
    }

    const style = document.createElement('style');
    style.id = 'ib-report-lock-style';
    style.textContent = `
      .modal-cell.disabled-cell {
        position: relative;
      }
      .modal-cell.disabled-cell .lock-indicator {
        display: inline-flex;
        align-items: center;
        margin-left: 4px;
        opacity: 0;
        transition: opacity 0.2s ease;
      }
      .modal-cell.disabled-cell:hover .lock-indicator {
        opacity: 1;
      }
    `;
    document.head.appendChild(style);
  }

  async initialize() {
    console.log('📊 เริ่มต้นระบบรายงาน...');
    this.loadCurrentUser();
    this.initFlatpickr();
    await this.loadMasterData();
    await this.loadAllData(); // loadAllData จะเรียก updateFiscalYearsFromData เอง
    this.setupEventListeners();
    this.setupModalHandlers();
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

  getUserContextPayload() {
    const encoded = this.buildEncodedUserContext();
    if (!encoded) {
      return null;
    }

    const payload = {
      user_context: encoded.value
    };
    if (encoded.encoding) {
      payload.user_context_encoding = encoded.encoding;
    }
    return payload;
  }

  loadCurrentUser() {
    try {
      const sessionData = sessionStorage.getItem('raot_user_session');
      if (sessionData) {
        this.currentUser = JSON.parse(sessionData);
      }
    } catch (error) {
      console.warn('ไม่สามารถอ่าน session user:', error);
      this.currentUser = null;
    }
  }

  parseBooleanFlag(value) {
    const normalized = String(value ?? '').toLowerCase();
    if (!normalized) return false;
    const truthy = [
      '1',
      'y',
      'yes',
      'true',
      'active',
      'enable',
      'enabled',
      'on'
    ];
    return truthy.includes(normalized);
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

  extractCurrentUserId() {
    try {
      const raw = sessionStorage.getItem('raot_user_session');
      if (!raw) {
        return null;
      }

      let parsed = raw;
      if (typeof raw === 'string') {
        try {
          parsed = JSON.parse(raw);
        } catch (error) {
          console.warn('⚠️ ไม่สามารถแปลง session เป็น JSON:', error);
          const match = raw.match(/"user_id"\s*:\s*"?(?<id>\d+)/);
          if (match?.groups?.id) {
            return String(match.groups.id);
          }
          return null;
        }
      }

      const candidate =
        parsed?.user_id ??
        parsed?.data?.user_id ??
        parsed?.user?.id ??
        parsed?.user?.user_id ??
        null;

      return candidate !== undefined && candidate !== null
        ? String(candidate)
        : null;
    } catch (error) {
      console.error('❌ extractCurrentUserId ล้มเหลว:', error);
      return null;
    }
  }

  async updateFiscalYearsFromData(items) {
    console.log(
      '📅 updateFiscalYearsFromData called with items:',
      items?.length
    );

    // อัพเดทรายการปีงบประมาณจากข้อมูลที่ได้รับมา
    const yearMap = new Map();

    if (Array.isArray(items)) {
      console.log('  🔍 Processing items to extract fiscal years...');
      items.forEach((item) => {
        if (item.fiscal_year_id && item.fiscal_year_name) {
          const yearKey = item.fiscal_year_id;
          console.log(
            `    Found: ${item.fiscal_year_name} (ID: ${item.fiscal_year_id})`
          );
          if (!yearMap.has(yearKey)) {
            yearMap.set(yearKey, {
              id: item.fiscal_year_id,
              name: item.fiscal_year_name
            });
          }
        }
      });
    }

    console.log('  📊 Unique years found:', yearMap.size);

    // เก็บค่า selected ปัจจุบันไว้
    const select = document.getElementById('filter-fiscal-year');
    const currentSelected = select ? select.value : '';
    console.log('  💾 Current selected value:', currentSelected);

    // แปลง Map เป็น Array และเรียงลำดับตามปี (มากไปน้อย)
    this.fiscalYears = Array.from(yearMap.values()).sort((a, b) => {
      const yearA = parseInt(a.name) || 0;
      const yearB = parseInt(b.name) || 0;
      return yearB - yearA;
    });

    console.log('  ✅ Final fiscal years:', this.fiscalYears);
    this.renderFiscalYearOptions(currentSelected);
  }

  async updateFiltersFromData(items) {
    console.log('🔧 updateFiltersFromData called with items:', items?.length);

    const creatorMap = new Map();
    const budgetMap = new Map();
    const assetMap = new Map();

    if (Array.isArray(items)) {
      items.forEach((item) => {
        const creatorId =
          item?.created_by ?? item?.createdBy ?? item?.created_by_id ?? null;
        if (creatorId !== undefined && creatorId !== null && creatorId !== '') {
          const creatorKey = String(creatorId);
          if (!creatorMap.has(creatorKey)) {
            const creatorName =
              item?.created_by_username ??
              item?.creator_name ??
              item?.created_by_name ??
              '';
            const label = creatorName
              ? `ผู้สร้าง: ${creatorName}`
              : `ผู้สร้าง ID: ${creatorKey}`;
            creatorMap.set(creatorKey, {
              id: `creator:${creatorKey}`,
              name: label,
              creatorId: creatorKey
            });
          }
        }

        if (item.budget_source_id && item.budget_source_name_full) {
          if (!budgetMap.has(item.budget_source_id)) {
            budgetMap.set(item.budget_source_id, {
              id: item.budget_source_id,
              name: item.budget_source_name_full
            });
          }
        }

        if (item.asset_type_id && item.asset_type_name) {
          if (!assetMap.has(item.asset_type_id)) {
            assetMap.set(item.asset_type_id, {
              id: item.asset_type_id,
              name: item.asset_type_name
            });
          }
        }
      });
    }

    const deptSelect = document.getElementById('filter-department');
    const budgetSelect = document.getElementById('filter-budget-source');
    const assetSelect = document.getElementById('filter-asset-type');

    const currentCreatorOption =
      this.filters.creatorOption || (deptSelect ? deptSelect.value : '');
    const currentBudget = budgetSelect ? budgetSelect.value : '';
    const currentAsset = assetSelect ? assetSelect.value : '';

    this.departments = Array.from(creatorMap.values()).sort((a, b) =>
      (a.name || '').localeCompare(b.name || '', 'th')
    );
    this.budgetSources = Array.from(budgetMap.values()).sort((a, b) =>
      (a.name || '').localeCompare(b.name || '', 'th')
    );
    this.assetTypes = Array.from(assetMap.values()).sort((a, b) =>
      (a.name || '').localeCompare(b.name || '', 'th')
    );

    console.log('  ✅ Creators:', this.departments.length, this.departments);
    console.log(
      '  ✅ Budget Sources:',
      this.budgetSources.length,
      this.budgetSources
    );
    console.log('  ✅ Asset Types:', this.assetTypes.length, this.assetTypes);

    this.renderDepartmentOptions(currentCreatorOption);
    this.renderBudgetSourceOptions(currentBudget);
    this.renderAssetTypeOptions(currentAsset);
  }

  renderFiscalYearOptions(selectedValue = '') {
    console.log('🎨 Rendering fiscal year options...');
    const select = document.getElementById('filter-fiscal-year');
    if (!select) {
      console.warn('  ⚠️ Element #filter-fiscal-year not found');
      return;
    }
    this.reinitializeChoices(
      select,
      this.fiscalYears,
      'id',
      'name',
      selectedValue
    );
    console.log('  ✅ Fiscal year options rendered:', this.fiscalYears.length);
  }

  renderDepartmentOptions(selectedValue = '') {
    console.log('🏢 Rendering department options...');
    const select = document.getElementById('filter-department');
    if (!select) {
      console.warn('  ⚠️ Element #filter-department not found');
      return;
    }
    this.reinitializeChoices(
      select,
      this.departments,
      'id',
      'name',
      selectedValue
    );
    console.log('  ✅ Department options rendered:', this.departments.length);
  }

  renderBudgetSourceOptions(selectedValue = '') {
    console.log('💰 Rendering budget source options...');
    const select = document.getElementById('filter-budget-source');
    if (!select) {
      console.warn('  ⚠️ Element #filter-budget-source not found');
      return;
    }
    this.reinitializeChoices(
      select,
      this.budgetSources,
      'id',
      'name',
      selectedValue
    );
    console.log(
      '  ✅ Budget source options rendered:',
      this.budgetSources.length
    );
  }

  renderAssetTypeOptions(selectedValue = '') {
    console.log('📦 Rendering asset type options...');
    const select = document.getElementById('filter-asset-type');
    if (!select) {
      console.warn('  ⚠️ Element #filter-asset-type not found');
      return;
    }
    this.reinitializeChoices(
      select,
      this.assetTypes,
      'id',
      'name',
      selectedValue
    );
    console.log('  ✅ Asset type options rendered:', this.assetTypes.length);
  }

  reinitializeChoices(
    element,
    dataArray,
    valueField,
    labelField,
    selectedValue = '',
    placeholderText = 'ทั้งหมด'
  ) {
    // 1. ลบ Choices instance เดิม (ถ้ามี)
    try {
      if (element.choicesInstance) {
        if (typeof element.choicesInstance.removeActiveItems === 'function') {
          element.choicesInstance.removeActiveItems();
        }
        element.choicesInstance.destroy();
        element.choicesInstance = null;
      }
    } catch (error) {
      console.warn(`⚠️ ไม่สามารถ destroy Choices instance:`, error);
      element.choicesInstance = null;
    }

    // 2. ล้างข้อมูลเดิมและเพิ่มข้อมูลใหม่
    element.innerHTML = `<option value="">${placeholderText}</option>`;

    if (dataArray && dataArray.length > 0) {
      dataArray.forEach((item) => {
        const option = document.createElement('option');
        option.value = item[valueField];
        option.textContent = item[labelField] || '';
        if (selectedValue && item[valueField] == selectedValue) {
          option.selected = true;
        }
        element.appendChild(option);
      });
    }

    // 3. สร้าง Choices.js instance ใหม่
    try {
      element.choicesInstance = new Choices(element, {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false,
        placeholder: true,
        placeholderValue: placeholderText,
        searchPlaceholderValue: 'ค้นหา...',
        noResultsText: 'ไม่พบข้อมูล'
      });

      console.log(
        `✅ สร้าง Choices.js instance สำหรับ ${element.id} (${
          dataArray ? dataArray.length : 0
        } รายการ)`
      );
    } catch (error) {
      console.error(`❌ ไม่สามารถสร้าง Choices.js instance:`, error);
    }
  }

  async loadMasterData() {
    try {
      const headers = this.getUserContextHeaders();
      const [codeTypes, equipTypes, fundAreas, parameters] = await Promise.all([
        this.fetchMasterData('equipment_code_type'),
        this.fetchMasterData('equipment_type'),
        this.fetchMasterData('fund_area'),
        fetch('../controllers/parameter/parameter_controller.php?action=all', {
          headers
        }).then((r) => r.json())
      ]);

      this.masterData.equipmentCodeTypes = Array.isArray(codeTypes)
        ? codeTypes
        : [];
      this.masterData.equipmentTypes = Array.isArray(equipTypes)
        ? equipTypes
        : [];
      this.masterData.fundAreas = Array.isArray(fundAreas) ? fundAreas : [];
      if (!this.masterData.fundAreas.length) {
        // fallback sample (tb_ib_fund_area.sql)
        this.masterData.fundAreas = [
          { id: 1, code: '21101101', name: 'ที่ดิน' },
          { id: 2, code: '21401366', name: 'เครื่องปรับอากาศ' },
          { id: 3, code: '21401336', name: 'โต๊ะทำงาน' },
          { id: 4, code: '21401576', name: 'ชุดเครื่องเสียง' },
          { id: 5, code: '21401308', name: 'ตู้เย็น ตู้แช่' },
          { id: 6, code: '21401419', name: 'เครื่องพิมม์คอมพิวเตอร์' },
          { id: 7, code: '21601601', name: 'สวนยาง' },
          { id: 8, code: '21601602', name: 'สวนปาร์ม' }
        ];
      }
      if (
        parameters.status === 'success' &&
        Array.isArray(parameters.count_units)
      ) {
        this.masterData.countUnits = parameters.count_units;
      }
      if (!this.masterData.countUnits.length) {
        this.masterData.countUnits = [
          { id: 1, name: 'ชิ้น' },
          { id: 2, name: 'เครื่อง' },
          { id: 3, name: 'ตัว' },
          { id: 4, name: 'ชุด' },
          { id: 5, name: 'อัน' },
          { id: 6, name: 'หน่วย' }
        ];
      }

      this.populateModalSelects();
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการโหลด master data:', error);
    }
  }

  populateModalSelects() {
    console.log('📝 Populating modal selects...');

    // ใช้ reinitializeChoices pattern เหมือน filter dropdowns
    const codeTypeSelect = document.getElementById(
      'select-equipment-code-type'
    );
    if (codeTypeSelect && this.masterData.equipmentCodeTypes) {
      // แปลงข้อมูลให้มี format ที่ถูกต้อง
      const codeTypeData = this.masterData.equipmentCodeTypes.map((item) => ({
        id: item.id,
        name: `${item.code || item.id} - ${item.name}`
      }));
      this.reinitializeChoices(
        codeTypeSelect,
        codeTypeData,
        'id',
        'name',
        '',
        'กรุณาเลือก ชนิดรหัสครุภัณฑ์'
      );
    }

    const equipTypeSelect = document.getElementById('select-equipment-type');
    if (equipTypeSelect && this.masterData.equipmentTypes) {
      this.reinitializeChoices(
        equipTypeSelect,
        this.masterData.equipmentTypes,
        'id',
        'name',
        '',
        'กรุณาเลือก ประเภทรหัสครุภัณฑ์'
      );
    }

    const unitsSelect = document.getElementById('select-count-units');
    if (unitsSelect && this.masterData.countUnits) {
      // แปลงข้อมูลให้แสดง display name
      const unitsData = this.masterData.countUnits.map((item) => ({
        id: item.id,
        name: this.getCountUnitDisplayName(item)
      }));
      this.reinitializeChoices(
        unitsSelect,
        unitsData,
        'id',
        'name',
        '',
        'กรุณาเลือก หน่วยนับ'
      );
    }

    const fundAreaSelect = document.getElementById('select-fund-area');
    if (fundAreaSelect && this.masterData.fundAreas) {
      const fundAreaData = this.masterData.fundAreas.map((item) => ({
        id: item.id,
        name: `${item.code || item.id} - ${item.name}`
      }));
      this.reinitializeChoices(
        fundAreaSelect,
        fundAreaData,
        'id',
        'name',
        '',
        'กรุณาเลือกรายการ'
      );
    }

    console.log('✅ Modal selects populated with Choices.js');
  }

  async loadAllData() {
    console.log('🔄 loadAllData called');
    this.updateFiltersFromInputs();
    const url = this.buildRequestUrl();
    console.log('📡 API URL:', url);
    this.showLoadingState();

    try {
      const headers = this.getUserContextHeaders();
      const response = await fetch(url, {
        headers
      });
      const result = await response.json();
      console.log('📥 API Response:', result);

      if (result.status === 'success') {
        const items = Array.isArray(result.data)
          ? result.data
          : result.data?.items || [];
        console.log('📦 Total items received:', items.length);
        console.log('📦 Sample item:', items[0]);

        this.categorizeData(items);

        // อัพเดทรายการปีงบประมาณและ filters จากข้อมูลที่โหลดมา
        await this.updateFiscalYearsFromData(items);
        await this.updateFiltersFromData(items);

        this.pendingChanges = {};
        this.renderCurrentTab();
      } else {
        console.error('❌ API returned error:', result.message);
        this.showError(result.message || 'ไม่สามารถโหลดข้อมูลได้');
      }
    } catch (error) {
      console.error('❌ Error:', error);
      this.showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
    }
  }

  categorizeData(items) {
    // ใช้เฉพาะรายการที่ผ่านการพิจารณางบแล้ว (budget_approval_status = approved) สำหรับ tab 2/3/4
    const approvedBudgetItems = Array.isArray(items)
      ? items.filter(
          (it) =>
            String(it?.budget_approval_status || '').toLowerCase() ===
            'approved'
        )
      : [];

    const getType = (item) => {
      const raw =
        item?.asset_type_id ??
        item?.assetType ??
        item?.asset_type ??
        item?.type ??
        item?.asset_nature ??
        '';
      return String(raw).trim();
    };

    this.data.all = items;
    this.data.equipment = approvedBudgetItems.filter((item) => {
      const type = getType(item);
      return ['1', '2', '4', '7', '10'].includes(type);
    });
    this.data.construction = approvedBudgetItems.filter((item) => {
      const type = getType(item);
      return ['3', '8', '9'].includes(type);
    });
    this.data.plantation = approvedBudgetItems.filter((item) => {
      const type = getType(item);
      return ['5', '6'].includes(type);
    });
  }

  buildRequestUrl() {
    const params = new URLSearchParams();
    params.append('per_page', '100');
    params.append('status', 'approved');

    if (this.filters.requestNumber)
      params.append('request_number', this.filters.requestNumber);
    if (this.filters.startDate)
      params.append('start_date', this.filters.startDate);
    if (this.filters.endDate) params.append('end_date', this.filters.endDate);
    if (this.filters.fiscalYear)
      params.append('fiscal_year_id', this.filters.fiscalYear);
    if (this.filters.creator) params.append('created_by', this.filters.creator);
    if (this.filters.budgetSource)
      params.append('budget_source_id', this.filters.budgetSource);
    if (this.filters.assetType)
      params.append('asset_type_id', this.filters.assetType);

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
    const creatorOption =
      document.getElementById('filter-department')?.value || '';
    this.filters.creatorOption = creatorOption;
    this.filters.creator = '';
    if (creatorOption.startsWith('creator:')) {
      this.filters.creator = creatorOption.replace('creator:', '');
    }
    this.filters.budgetSource =
      document.getElementById('filter-budget-source')?.value || '';
    this.filters.assetType =
      document.getElementById('filter-asset-type')?.value || '';
  }

  renderCurrentTab() {
    const tab = this.currentTab;
    const data = this.data[tab] || [];
    const start = (this.currentPage[tab] - 1) * this.perPage;
    const end = start + this.perPage;
    const pageData = data.slice(start, end);

    if (tab === 'all') {
      this.renderAllTab(pageData, start);
    } else if (tab === 'equipment') {
      this.renderEquipmentTab(pageData, start);
    } else if (tab === 'construction') {
      this.renderConstructionTab(pageData, start);
    } else if (tab === 'plantation') {
      this.renderPlantationTab(pageData, start);
    }

    this.renderPagination(tab, data.length);
    this.updateFiscalYearDisplay(tab);
  }

  renderAllTab(data, startIndex) {
    const tbody = document.getElementById('all-tbody');
    if (!tbody) {
      return;
    }

    if (!data.length) {
      tbody.innerHTML =
        '<tr><td colspan="8" class="text-center">ไม่พบข้อมูล</td></tr>';
      return;
    }

    const rows = data
      .map((item, i) => {
        const index = startIndex + i + 1;
        const requestNumber = this.escapeHtml(item.request_number || '-');
        const itemName = this.escapeHtml(item.item_name || '-');
        const fiscalYear = this.escapeHtml(item.fiscal_year_id || '-');
        const departName = this.escapeHtml(item.department_name || '-');
        const budgetStatus = String(item.budget_approval_status || '')
          .trim()
          .toLowerCase();
        const canBudgetApprove =
          this.parseBooleanFlag(this.currentUser?.approve_head_office) &&
          this.parseBooleanFlag(this.currentUser?.approve_investment) &&
          budgetStatus === 'pending';

        return `
            <tr class="text-center">
                <td>${index}</td>
                <td>${this.formatDate(item.created_at)}</td>
                <td>${requestNumber}</td>
                <td class="text-start">${itemName}</td>
                <td>${fiscalYear}</td>
                <td>${departName}</td>
                <td><span class="badge bg-success">อนุมัติ</span></td>
                <td>
                    <div class="hstack gap-2 fs-15 justify-content-center">
                        <a href="investment-budget-view-detail.php?id=${
                          item.id
                        }" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" title="ดูรายละเอียด">
                            <i class="fe fe-eye"></i>
                        </a>
                        ${
                          canBudgetApprove
                            ? `<a href="investment-budget-view-detail.php?id=${item.id}&budgetApproval=1" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" title="พิจารณาอนุมัติงบประมาณ"><i class="fe fe-check-circle"></i></a>`
                            : ''
                        }
                    </div>
                </td>
            </tr>
        `;
      })
      .join('');
    tbody.innerHTML = rows;
  }

  renderEquipmentTab(data, startIndex) {
    const tbody = document.getElementById('equipment-tbody');
    if (!tbody) {
      return;
    }

    if (!data.length) {
      tbody.innerHTML =
        '<tr><td colspan="15" class="text-center">ไม่พบข้อมูล</td></tr>';
      return;
    }

    const rows = data
      .map((item, i) => {
        const index = startIndex + i + 1;
        const departName = this.escapeHtml(item.department_name || '-');
        const requestNumber = this.escapeHtml(item.request_number || '-');
        const itemName = this.escapeHtml(item.item_name || '-');
        const reason = this.escapeHtml(item.reason || '-');
        const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
        const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
        const countUnitLabel = this.getCountUnitLabel(item);
        const fundAreaLabel = this.getFundAreaLabel(item);
        const equipmentCodeTypeValue = item.equipment_code_type_id ?? '';
        const equipmentTypeValue = item.equipment_type_id ?? '';
        const countUnitValue = item.count_unit_id ?? '';
        const fundAreaValue = item.fund_area_id ?? '';

        const equipmentCodeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-code-type',
          field: 'equipment_code_type',
          value: equipmentCodeTypeValue,
          label: equipmentCodeTypeLabel
        });
        const equipmentTypeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-type',
          field: 'equipment_type',
          value: equipmentTypeValue,
          label: equipmentTypeLabel
        });
        const countUnitCell = this.buildSelectionCell(item, {
          modalId: 'modal-count-units',
          field: 'count_units',
          value: countUnitValue,
          label: countUnitLabel
        });
        const fundAreaCell = this.buildSelectionCell(item, {
          modalId: 'modal-fund-area',
          field: 'fund_area',
          value: fundAreaValue,
          label: fundAreaLabel
        });

        return `
            <tr class="text-center">
                <td>${index}</td>
                <td>${departName}</td>
                <td>${this.formatDate(item.created_at)}</td>
                <td>${requestNumber}</td>
                <td>${itemName}</td>
                <td>${
                  item.quantity == 0 || item.quantity == '0'
                    ? '-'
                    : item.quantity ?? '-'
                }</td>
                <td>${this.formatCurrency(item.unit_price)}</td>
                <td>${this.formatCurrency(item.total_amount)}</td>
                <td>${reason}</td>
                ${fundAreaCell}
                ${equipmentCodeCell}
                <td>${this.escapeHtml(item.asset_type_code || '-')}</td>
                ${equipmentTypeCell}
                ${countUnitCell}
                <td>
                    ${this.getActionButtonsHtml(item)}
                </td>
            </tr>
        `;
      })
      .join('');
    tbody.innerHTML = rows;
  }

  renderConstructionTab(data, startIndex) {
    const tbody = document.getElementById('construction-tbody');
    if (!tbody) {
      return;
    }

    if (!data.length) {
      tbody.innerHTML =
        '<tr><td colspan="14" class="text-center">ไม่พบข้อมูล</td></tr>';
      return;
    }

    const rows = data
      .map((item, i) => {
        const index = startIndex + i + 1;
        const departName = this.escapeHtml(item.department_name || '-');
        const requestNumber = this.escapeHtml(item.request_number || '-');
        const itemName = this.escapeHtml(item.item_name || '-');
        const reason = this.escapeHtml(item.reason || '-');
        const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
        const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
        const countUnitLabel = this.getCountUnitLabel(item);
        const fundAreaLabel = this.getFundAreaLabel(item);
        const equipmentCodeTypeValue = item.equipment_code_type_id ?? '';
        const equipmentTypeValue = item.equipment_type_id ?? '';
        const countUnitValue = item.count_unit_id ?? '';
        const fundAreaValue = item.fund_area_id ?? '';

        const equipmentCodeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-code-type',
          field: 'equipment_code_type',
          value: equipmentCodeTypeValue,
          label: equipmentCodeTypeLabel
        });
        const equipmentTypeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-type',
          field: 'equipment_type',
          value: equipmentTypeValue,
          label: equipmentTypeLabel
        });
        const countUnitCell = this.buildSelectionCell(item, {
          modalId: 'modal-count-units',
          field: 'count_units',
          value: countUnitValue,
          label: countUnitLabel
        });
        const fundAreaCell = this.buildSelectionCell(item, {
          modalId: 'modal-fund-area',
          field: 'fund_area',
          value: fundAreaValue,
          label: fundAreaLabel
        });

        return `
            <tr class="text-center">
                <td>${index}</td>
                <td>${departName}</td>
                <td>${this.formatDate(item.created_at)}</td>
                <td>${requestNumber}</td>
                <td>${itemName}</td>
                <td>${this.formatCurrency(item.total_amount)}</td>
                <td>${reason}</td>
                ${fundAreaCell}
                ${equipmentCodeCell}
                <td>${this.escapeHtml(item.asset_type_code || '-')}</td>
                ${equipmentTypeCell}
                ${countUnitCell}
                <td>${this.escapeHtml(
                  item.expected_disbursement_date || '-'
                )}</td>
                <td>
                    ${this.getActionButtonsHtml(item)}
                </td>
            </tr>
        `;
      })
      .join('');
    tbody.innerHTML = rows;
  }

  renderPlantationTab(data, startIndex) {
    const tbody = document.getElementById('plantation-tbody');
    if (!tbody) {
      return;
    }

    if (!data.length) {
      tbody.innerHTML =
        '<tr><td colspan="14" class="text-center">ไม่พบข้อมูล</td></tr>';
      return;
    }

    const rows = data
      .map((item, i) => {
        const index = startIndex + i + 1;
        const departName = this.escapeHtml(item.department_name || '-');
        const requestNumber = this.escapeHtml(item.request_number || '-');
        const itemName = this.escapeHtml(item.item_name || '-');
        const reason = this.escapeHtml(item.reason || '-');
        const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
        const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
        const countUnitLabel = this.getCountUnitLabel(item);
        const fundAreaLabel = this.getFundAreaLabel(item);
        const equipmentCodeTypeValue = item.equipment_code_type_id ?? '';
        const equipmentTypeValue = item.equipment_type_id ?? '';
        const countUnitValue = item.count_unit_id ?? '';
        const fundAreaValue = item.fund_area_id ?? '';

        const equipmentCodeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-code-type',
          field: 'equipment_code_type',
          value: equipmentCodeTypeValue,
          label: equipmentCodeTypeLabel
        });
        const equipmentTypeCell = this.buildSelectionCell(item, {
          modalId: 'modal-equipment-type',
          field: 'equipment_type',
          value: equipmentTypeValue,
          label: equipmentTypeLabel
        });
        const countUnitCell = this.buildSelectionCell(item, {
          modalId: 'modal-count-units',
          field: 'count_units',
          value: countUnitValue,
          label: countUnitLabel
        });
        const fundAreaCell = this.buildSelectionCell(item, {
          modalId: 'modal-fund-area',
          field: 'fund_area',
          value: fundAreaValue,
          label: fundAreaLabel
        });

        return `
            <tr class="text-center">
                <td>${index}</td>
                <td>${departName}</td>
                <td>${this.formatDate(item.created_at)}</td>
                <td>${requestNumber}</td>
                <td>${itemName}</td>
                <td>${this.formatCurrency(item.total_amount)}</td>
                <td>${reason}</td>
                ${fundAreaCell}
                ${equipmentCodeCell}
                <td>${this.escapeHtml(item.asset_type_code || '-')}</td>
                ${equipmentTypeCell}
                ${countUnitCell}
                <td>${this.escapeHtml(item.plantation_area || '-')}</td>
                <td>
                    ${this.getActionButtonsHtml(item)}
                </td>
            </tr>
        `;
      })
      .join('');
    tbody.innerHTML = rows;
  }

  canEditItem(item) {
    if (!item || item.id === undefined || item.id === null) {
      return false;
    }
    const sapStatus = (item.sap_status || '').toLowerCase();
    if (sapStatus === 'success') {
      return false;
    }
    return !this.savedItems.has(String(item.id));
  }

  buildSelectionCell(item, { modalId, field, value, label }) {
    const canEdit = this.canEditItem(item);
    const className = canEdit ? 'modal-cell' : 'modal-cell disabled-cell';
    const style = canEdit
      ? 'cursor: pointer;'
      : 'cursor: not-allowed; opacity: 0.6;';

    const idAttr =
      item && item.id !== undefined && item.id !== null ? String(item.id) : '';
    const valueAttr =
      value !== undefined && value !== null ? String(value) : '';
    const sapStatusAttr =
      item && item.sap_status !== undefined && item.sap_status !== null
        ? String(item.sap_status)
        : '';

    const attrParts = [
      `data-field="${field}"`,
      `data-id="${this.escapeAttr(idAttr)}"`,
      `data-value="${this.escapeAttr(valueAttr)}"`,
      `data-sap-status="${this.escapeAttr(sapStatusAttr)}"`
    ];

    if (canEdit) {
      attrParts.push('data-bs-toggle="modal"');
      attrParts.push(`data-bs-target="#${modalId}"`);
    } else {
      attrParts.push('aria-disabled="true"');
      attrParts.push(
        'title="ไม่สามารถแก้ไขได้หลังจากบันทึกหรือส่งข้อมูลไป SAP แล้ว"'
      );
    }

    const attrString = attrParts.join(' ');
    const displayText =
      label !== undefined && label !== null && label !== ''
        ? this.escapeHtml(label)
        : '-';

    const lockIndicator = canEdit
      ? ''
      : '<span class="lock-indicator" aria-hidden="true"><i class="bx bx-block text-danger"></i></span>';

    return `<td class="${className}" ${attrString} style="${style}">${displayText}${lockIndicator}</td>`;
  }

  getActionButtonsHtml(item) {
    const viewButton = `<a href="investment-budget-view-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill" title="ดูรายละเอียด"><i class="fe fe-eye"></i></a>`;
    const containerStart =
      '<div class="hstack gap-2 fs-15 justify-content-center">';
    const containerEnd = '</div>';

    if (!item?.id) {
      return `${containerStart}${viewButton}${containerEnd}`;
    }

    if (!this.canEditItem(item)) {
      return `${containerStart}${viewButton}${containerEnd}`;
    }

    const saveButton = `<a href="javascript:void(0);" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill save-btn" data-id="${item.id}" title="บันทึก"><i class="bx bx-save"></i></a>`;
    return `${containerStart}${saveButton}${viewButton}${containerEnd}`;
  }

  renderPagination(tab, total) {
    const paginationEl = document.getElementById(`pagination-${tab}`);
    const infoEl = document.getElementById(`pagination-info-${tab}`);
    if (!paginationEl || !infoEl) {
      return;
    }
    const totalPages = Math.ceil(total / this.perPage);
    const currentPage = this.currentPage[tab];

    if (totalPages <= 1) {
      paginationEl.innerHTML = '';
    } else {
      let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0);" data-page="${
                  currentPage - 1
                }">ก่อนหน้า</a>
            </li>`;

      for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                </li>`;
      }

      html += `<li class="page-item ${
        currentPage === totalPages ? 'disabled' : ''
      }">
                <a class="page-link" href="javascript:void(0);" data-page="${
                  currentPage + 1
                }">ถัดไป</a>
            </li>`;

      paginationEl.innerHTML = html;
    }

    const start = (currentPage - 1) * this.perPage + 1;
    const end = Math.min(currentPage * this.perPage, total);
    infoEl.textContent =
      total > 0
        ? `กำลังแสดง ${start}-${end} จาก ${total} รายการ`
        : 'ไม่พบข้อมูล';
  }

  updateFiscalYearDisplay(tab) {
    const el = document.getElementById(`fiscal-year-display-${tab}`);
    if (el && this.filters.fiscalYear) {
      const fy = this.fiscalYears.find((f) => f.id === this.filters.fiscalYear);
      el.textContent = fy ? `ประจำปีงบประมาณ ${fy.name}` : '';
    } else if (el) {
      el.textContent = '';
    }
  }

  setupEventListeners() {
    document
      .getElementById('filter-search-btn')
      ?.addEventListener('click', () => this.loadAllData());
    document
      .getElementById('filter-reset-btn')
      ?.addEventListener('click', () => {
        const requestInput = document.getElementById('filter-request-number');
        const startInput = document.getElementById('filter-start-date');
        const endInput = document.getElementById('filter-end-date');
        const fiscalSelect = document.getElementById('filter-fiscal-year');
        const deptSelect = document.getElementById('filter-department');
        const budgetSelect = document.getElementById('filter-budget-source');
        const assetSelect = document.getElementById('filter-asset-type');
        const exportTypeSelect = document.getElementById('export-type-select');

        if (requestInput) requestInput.value = '';
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
        if (fiscalSelect) fiscalSelect.value = '';
        if (deptSelect) deptSelect.value = '';
        if (budgetSelect) budgetSelect.value = '';
        if (assetSelect) assetSelect.value = '';
        if (exportTypeSelect) exportTypeSelect.value = '';

        // Clear datepickers
        if (this.startPicker && this.startPicker.instance) {
          this.startPicker.instance.clear();
          this.startPicker.instance.set('maxDate', null);
        }
        if (this.endPicker && this.endPicker.instance) {
          this.endPicker.instance.clear();
          this.endPicker.instance.set('minDate', null);
        }

        // Clear Choices.js selections
        const selects = [
          fiscalSelect,
          deptSelect,
          budgetSelect,
          assetSelect,
          exportTypeSelect
        ];
        selects.forEach((select) => {
          if (select && select.choicesInstance) {
            select.choicesInstance.setChoiceByValue('');
          }
        });

        this.filters.creator = '';
        this.filters.creatorOption = '';

        this.loadAllData();
      });

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach((btn) => {
      btn.addEventListener('shown.bs.tab', (e) => {
        const target = e.target.getAttribute('data-bs-target') || '';
        if (target.includes('all')) this.currentTab = 'all';
        else if (target.includes('equipment')) this.currentTab = 'equipment';
        else if (target.includes('construction'))
          this.currentTab = 'construction';
        else if (target.includes('plantation')) this.currentTab = 'plantation';
        this.renderCurrentTab();
      });
    });

    ['all', 'equipment', 'construction', 'plantation'].forEach((tab) => {
      document
        .getElementById(`pagination-${tab}`)
        ?.addEventListener('click', (e) => {
          const page = parseInt(e.target.dataset.page, 10);
          if (page && page > 0) {
            this.currentPage[tab] = page;
            this.renderCurrentTab();
          }
        });
    });

    document.addEventListener('click', (e) => {
      const saveBtn = e.target.closest('.save-btn');
      if (saveBtn) {
        const id = saveBtn.dataset.id;
        this.handleSave(id, saveBtn);
      }
    });

    // Export button with validation
    document.getElementById('export-btn')?.addEventListener('click', () => {
      const exportType = document.getElementById('export-type-select')?.value;

      if (!exportType || exportType === '') {
        this.showToast('กรุณาเลือกประเภทรายการนำข้อมูลออก', 'warning');
        return;
      }

      // เรียก export function ตาม type ที่เลือก
      switch (exportType) {
        case 'asset':
          this.exportAssetReport();
          break;
        case 'sap':
          this.exportSAPReport();
          break;
        case 'project':
          this.exportProjectReport();
          break;
        case 'asset-detail':
          this.exportAssetDetailReport();
          break;
        default:
          this.showToast('ประเภทการนำออกไม่ถูกต้อง', 'error');
      }
    });

    // Tab-specific export buttons (display format)
    document
      .getElementById('export-excel-all-tab-btn')
      ?.addEventListener('click', () => {
        this.exportToExcel('all');
      });

    document
      .getElementById('export-excel-equipment-btn')
      ?.addEventListener('click', () => {
        this.exportToExcel('equipment');
      });

    document
      .getElementById('export-excel-construction-btn')
      ?.addEventListener('click', () => {
        this.exportToExcel('construction');
      });

    document
      .getElementById('export-excel-plantation-btn')
      ?.addEventListener('click', () => {
        this.exportToExcel('plantation');
      });
  }

  setupModalHandlers() {
    document.addEventListener('click', (event) => {
      const cell = event.target.closest('.modal-cell');
      if (!cell) return;

      // ตรวจสอบว่า sap_status = 'success' หรือไม่
      const sapStatus = cell.dataset.sapStatus || '';
      if (sapStatus === 'success') {
        console.log('⚠️ ไม่สามารถแก้ไขได้ เนื่องจาก SAP status = success');
        event.preventDefault();
        event.stopPropagation();
        return;
      }

      const field = cell.dataset.field;
      const itemId = cell.dataset.id;
      if (!field || !itemId) return;

      this.currentModalData = {
        itemId,
        field,
        cellElement: cell
      };

      const currentValue = cell.dataset.value || '';

      if (field === 'equipment_code_type') {
        const select = document.getElementById('select-equipment-code-type');
        if (select) {
          if (select.choicesInstance) {
            select.choicesInstance.setChoiceByValue(currentValue);
          } else {
            select.value = currentValue;
          }
        }
      } else if (field === 'equipment_type') {
        const select = document.getElementById('select-equipment-type');
        if (select) {
          if (select.choicesInstance) {
            select.choicesInstance.setChoiceByValue(currentValue);
          } else {
            select.value = currentValue;
          }
        }
      } else if (field === 'count_units') {
        const select = document.getElementById('select-count-units');
        if (select) {
          if (select.choicesInstance) {
            select.choicesInstance.setChoiceByValue(currentValue);
          } else {
            select.value = currentValue;
          }
        }
      } else if (field === 'fund_area') {
        const select = document.getElementById('select-fund-area');
        if (select) {
          if (select.choicesInstance) {
            select.choicesInstance.setChoiceByValue(currentValue);
          } else {
            select.value = currentValue;
          }
        }
      }
    });

    document
      .getElementById('btn-save-equipment-code-type')
      ?.addEventListener('click', () => {
        const select = document.getElementById('select-equipment-code-type');
        const value = select ? select.value || null : null;
        this.applySelection('equipment_code_type', value);
        this.hideModal('modal-equipment-code-type');
      });

    document
      .getElementById('btn-save-equipment-type')
      ?.addEventListener('click', () => {
        const select = document.getElementById('select-equipment-type');
        const value = select ? select.value || null : null;
        this.applySelection('equipment_type', value);
        this.hideModal('modal-equipment-type');
      });

    document
      .getElementById('btn-save-count-units')
      ?.addEventListener('click', () => {
        const select = document.getElementById('select-count-units');
        const value = select ? select.value || null : null;
        console.log('Selected count unit value:', value);
        this.applySelection('count_units', value);
        this.hideModal('modal-count-units');
      });

    document
      .getElementById('btn-save-fund-area')
      ?.addEventListener('click', () => {
        const select = document.getElementById('select-fund-area');
        const value = select ? select.value || null : null;
        this.applySelection('fund_area', value);
        this.hideModal('modal-fund-area');
      });

    document
      .getElementById('btn-save-account-code')
      ?.addEventListener('click', () => {
        const input = document.getElementById('input-account-code');
        const value = input ? input.value.trim() : '';
        if (this.currentModalData?.cellElement) {
          this.currentModalData.cellElement.textContent = value;
          this.currentModalData.cellElement.dataset.value = value;
          this.queuePendingChange(this.currentModalData.itemId, {
            account_code: value || null
          });
        }
        this.hideModal('modal-account-code');
      });
  }

  hideModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;
    const instance = window.bootstrap?.Modal.getInstance(modalEl);
    instance?.hide();
    this.currentModalData = {
      itemId: null,
      field: null,
      cellElement: null
    };
  }

  applySelection(field, value) {
    const { itemId, cellElement } = this.currentModalData || {};
    if (!itemId || !cellElement) {
      return;
    }
    cellElement.classList.remove('validation-error');

    let label = '';
    const changes = {};
    const pending = {};

    switch (field) {
      case 'equipment_code_type': {
        const selected = this.masterData.equipmentCodeTypes.find(
          (item) => String(item.id) === String(value)
        );
        if (selected) {
          label = `${selected.code || selected.id} - ${selected.name}`;
          changes.equipment_code_type_id = selected.id;
          changes.equipment_code_type_code = selected.code || null;
          changes.equipment_code_type_name = selected.name || null;
          pending.equipment_code_type_id = selected.id;
        } else {
          changes.equipment_code_type_id = null;
          changes.equipment_code_type_code = null;
          changes.equipment_code_type_name = null;
          pending.equipment_code_type_id = null;
        }
        break;
      }
      case 'equipment_type': {
        const selected = this.masterData.equipmentTypes.find(
          (item) => String(item.id) === String(value)
        );
        if (selected) {
          label = selected.code
            ? `${selected.code} - ${selected.name}`
            : selected.name;
          changes.equipment_type_id = selected.id;
          changes.equipment_type_code = selected.code || null;
          changes.equipment_type_name = selected.name || null;
          pending.equipment_type_id = selected.id;
        } else {
          changes.equipment_type_id = null;
          changes.equipment_type_code = null;
          changes.equipment_type_name = null;
          pending.equipment_type_id = null;
        }
        break;
      }
      case 'count_units': {
        const selected = this.masterData.countUnits.find(
          (item) => String(item.id) === String(value)
        );
        if (selected) {
          label = this.getCountUnitDisplayName(selected);
          changes.count_unit_id = selected.id;
          changes.count_unit_name = this.getCountUnitDisplayName(selected);
          pending.count_unit_id = selected.id;
          console.log('Count unit selected:', selected, 'pending:', pending);
        } else {
          label = '';
          changes.count_unit_id = null;
          changes.count_unit_name = null;
          pending.count_unit_id = null;
          console.log('Count unit cleared');
        }
        break;
      }
      case 'fund_area': {
        const selected = this.masterData.fundAreas.find(
          (item) => String(item.id) === String(value)
        );
        if (selected) {
          label = selected.code
            ? `${selected.code} - ${selected.name}`
            : selected.name || '';
          changes.fund_area_id = selected.id;
          changes.fund_area_code = selected.code || null;
          changes.fund_area_name = selected.name || null;
          pending.fund_area_id = selected.id;
        } else {
          label = '';
          changes.fund_area_id = null;
          changes.fund_area_code = null;
          changes.fund_area_name = null;
          pending.fund_area_id = null;
        }
        break;
      }
      default:
        break;
    }

    cellElement.dataset.value = value || '';
    cellElement.textContent = label;

    this.queuePendingChange(itemId, pending);
    this.updateItemData(itemId, changes);
  }

  clearValidationState(row) {
    if (!row) return;
    row
      .querySelectorAll('.validation-error')
      .forEach((el) => el.classList.remove('validation-error'));
  }

  markValidationError(element) {
    if (!element) return;
    element.classList.add('validation-error');
    if (element instanceof HTMLElement && typeof element.focus === 'function') {
      try {
        element.focus({
          preventScroll: false
        });
      } catch (error) {
        // Ignore focus errors on non-focusable elements.
      }
    }
  }

  validateRowBeforeSave(id, item, row) {
    if (!row) {
      return 'ไม่พบข้อมูลแถว';
    }

    this.clearValidationState(row);

    const requiredFields = [
      {
        selector: `[data-field="equipment_code_type"][data-id="${id}"]`,
        message: 'กรุณาเลือกชนิดครุภัณฑ์'
      },
      {
        selector: `[data-field="equipment_type"][data-id="${id}"]`,
        message: 'กรุณาเลือกประเภทครุภัณฑ์'
      },
      {
        selector: `[data-field="count_units"][data-id="${id}"]`,
        message: 'กรุณาเลือกหน่วยนับ'
      },
      {
        selector: `[data-field="fund_area"][data-id="${id}"]`,
        message: 'กรุณาเลือกรายการ'
      }
    ];

    for (const field of requiredFields) {
      const cell = row.querySelector(field.selector);
      if (!cell) {
        continue;
      }

      let value = (cell.dataset.value ?? '').trim();
      if (
        !value ||
        value.toLowerCase() === 'null' ||
        value.toLowerCase() === 'undefined'
      ) {
        value = cell.textContent.trim();
      }
      if (
        !value ||
        value.toLowerCase() === 'null' ||
        value.toLowerCase() === 'undefined'
      ) {
        this.markValidationError(cell);
        return field.message;
      }
    }

    return null;
  }

  handleSave(id, button) {
    const item = this.findItemById(id);
    if (!item) {
      this.showToast('ไม่พบข้อมูลคำขอ', 'error');
      return;
    }

    const row = button?.closest('tr');
    const validationError = this.validateRowBeforeSave(id, item, row);
    if (validationError) {
      this.showToast(validationError, 'warning');
      return;
    }

    const payload = {
      ...(this.pendingChanges[id] || {})
    };

    if (Object.keys(payload).length === 0) {
      this.showToast('ไม่มีข้อมูลที่เปลี่ยนแปลง', 'info');
      return;
    }

    this.saveReportFields(id, payload, button);
  }

  async saveReportFields(id, payload, triggerEl) {
    const button = triggerEl?.closest('button, a');
    if (button) {
      if (button.tagName === 'BUTTON') {
        button.disabled = true;
      } else {
        button.classList.add('disabled');
        button.setAttribute('aria-disabled', 'true');
      }
    }

    let isSuccessful = false;

    try {
      const userContext = this.getUserContextPayload();
      if (userContext) {
        payload = {
          ...payload,
          ...userContext
        };
      }

      const headers = this.getUserContextHeaders();
      headers['Content-Type'] = 'application/json';

      const response = await fetch(
        `../controllers/investment_budget/ib_requests_controller.php?action=update_report_fields&id=${id}`,
        {
          method: 'POST',
          headers,
          body: JSON.stringify(payload)
        }
      );

      const result = await response.json();

      if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || 'ไม่สามารถบันทึกข้อมูลได้');
      }

      const updatedRecord = result.data || null;
      if (updatedRecord) {
        this.applyServerUpdate(updatedRecord);
        this.savedItems.add(String(updatedRecord.id || id));
        this.freezeRow(updatedRecord.id || id);
        this.renderCurrentTab();
      }

      this.clearPendingChange(id);
      this.showToast('บันทึกข้อมูลเรียบร้อย', 'success');
      isSuccessful = true;
    } catch (error) {
      console.error(error);
      this.showToast(
        error.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
        'error'
      );
    } finally {
      if (!isSuccessful && button) {
        if (button.tagName === 'BUTTON') {
          button.disabled = false;
        } else {
          button.classList.remove('disabled');
          button.removeAttribute('aria-disabled');
        }
      }
    }
  }

  freezeRow(id) {
    const row =
      document.querySelector(`.save-btn[data-id="${id}"]`)?.closest('tr') ||
      document.querySelector(`tr [data-field][data-id="${id}"]`)?.closest('tr');
    if (!row) {
      return;
    }

    row.querySelectorAll('.modal-cell').forEach((cell) => {
      cell.classList.add('disabled-cell');
      cell.removeAttribute('data-bs-toggle');
      cell.removeAttribute('data-bs-target');
      cell.style.pointerEvents = 'none';
    });

    row.querySelectorAll('.editable-cell').forEach((cell) => {
      cell.classList.add('disabled-cell');
      cell.setAttribute('contenteditable', 'false');
    });

    const saveButton = row.querySelector('.save-btn');
    if (saveButton) {
      saveButton.classList.add('disabled', 'btn-outline-secondary');
      saveButton.classList.remove('btn-primary-transparent');
      saveButton.style.pointerEvents = 'none';
      saveButton.setAttribute('aria-disabled', 'true');
      saveButton.setAttribute('title', 'บันทึกแล้ว');
      saveButton.removeAttribute('data-id');
    }

    const viewButton = row.querySelector('.btn-success-transparent');
    if (viewButton && viewButton.parentElement) {
      viewButton.parentElement.innerHTML = viewButton.outerHTML;
    }
  }

  applyServerUpdate(record) {
    if (!record || !record.id) {
      return;
    }

    const changes = {
      equipment_code_type_id: record.equipment_code_type_id ?? null,
      equipment_code_type_code: record.equipment_code_type_code ?? null,
      equipment_code_type_name: record.equipment_code_type_name ?? null,
      equipment_type_id: record.equipment_type_id ?? null,
      equipment_type_code: record.equipment_type_code ?? null,
      equipment_type_name: record.equipment_type_name ?? null,
      count_unit_id: record.count_unit_id ?? null,
      count_unit_name: record.count_unit_name ?? null,
      account_code: record.account_code ?? null,
      fund_area_id: record.fund_area_id ?? null,
      fund_area_code: record.fund_area_code ?? null,
      fund_area_name: record.fund_area_name ?? null
    };

    this.updateItemData(record.id, changes);
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

  formatNumberForExport(value) {
    if (value === null || value === undefined || value === '') {
      return '0.00';
    }
    const numericValue = Number(
      typeof value === 'string' ? value.replace(/,/g, '') : value
    );
    if (!Number.isFinite(numericValue)) {
      return '0.00';
    }
    return numericValue.toFixed(2);
  }

  formatQuantityForExport(quantity) {
    if (quantity === null || quantity === undefined || quantity === '') {
      return { quantity: 1, wasEmpty: true };
    }
    const qty = parseInt(quantity, 10);
    if (Number.isNaN(qty) || qty === 0) {
      return { quantity: 1, wasEmpty: true };
    }
    return { quantity: qty, wasEmpty: false };
  }

  formatUnitPriceForExport(unitPrice, totalAmount, quantity) {
    let result = 0;

    const qtyNum = Number(
      typeof quantity === 'string' ? quantity.replace(/,/g, '') : quantity
    );
    const hasQty = Number.isFinite(qtyNum) && qtyNum > 0;

    if (hasQty) {
      const totalNum = Number(
        typeof totalAmount === 'string'
          ? totalAmount.replace(/,/g, '')
          : totalAmount
      );
      result = Number.isFinite(totalNum) ? totalNum / qtyNum : 0;
    } else {
      const price = Number(
        typeof unitPrice === 'string' ? unitPrice.replace(/,/g, '') : unitPrice
      );
      result = Number.isFinite(price) ? price : 0;
    }

    return this.formatNumberForExport(result);
  }

  formatAmountForExport(amount) {
    return this.formatNumberForExport(amount);
  }

  // คืนยอดงบที่อนุมัติ ถ้าไม่มีให้ fallback เป็นยอดรวม
  getApprovedAmount(item) {
    if (!item) return 0;
    const raw =
      item.budget_approved_amount !== undefined &&
      item.budget_approved_amount !== null &&
      item.budget_approved_amount !== ''
        ? item.budget_approved_amount
        : item.total_amount;

    const num =
      typeof raw === 'string' ? Number(raw.replace(/,/g, '')) : Number(raw);
    return Number.isFinite(num) ? num : 0;
  }

  escapeHtml(value) {
    if (value === null || value === undefined) {
      return '';
    }
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
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

  getCountUnitDisplayName(unit) {
    if (!unit) {
      return '';
    }

    const candidateKeys = [
      'name',
      'count_unit_name',
      'unit_name',
      'unit_th',
      'unit_name_th',
      'unit',
      'description'
    ];

    for (const key of candidateKeys) {
      if (unit[key]) {
        return this.escapeHtml(unit[key]);
      }
    }

    return this.escapeHtml(unit.id || '');
  }

  getFundAreaLabel(item) {
    if (!item) return '';

    const code = item.fund_area_code || '';
    const name = item.fund_area_name || '';
    if (code && name) return `${code} - ${name}`;
    if (code) return code;
    if (name) return name;

    if (item.fund_area_id && this.masterData.fundAreas?.length) {
      const found = this.masterData.fundAreas.find(
        (fa) => String(fa.id) === String(item.fund_area_id)
      );
      if (found) {
        const faCode = found.code || '';
        const faName = found.name || '';
        if (faCode && faName) return `${faCode} - ${faName}`;
        if (faCode) return faCode;
        if (faName) return faName;
      }
    }

    return '';
  }

  getCountUnitLabel(item) {
    if (!item) return '';

    if (item.count_unit_name) {
      return item.count_unit_name;
    }

    if (item.count_unit_id) {
      const unit = this.masterData.countUnits.find(
        (u) => String(u.id) === String(item.count_unit_id)
      );
      if (unit) {
        return this.getCountUnitDisplayName(unit);
      }
    }

    return '';
  }

  findItemById(id) {
    const tabs = ['all', 'equipment', 'construction', 'plantation'];
    for (const tab of tabs) {
      const item = (this.data[tab] || []).find(
        (el) => String(el.id) === String(id)
      );
      if (item) return item;
    }
    return null;
  }

  updateItemData(id, changes) {
    const tabs = ['all', 'equipment', 'construction', 'plantation'];
    tabs.forEach((tab) => {
      const list = this.data[tab];
      if (!Array.isArray(list)) return;
      const index = list.findIndex((item) => String(item.id) === String(id));
      if (index !== -1) {
        this.data[tab][index] = {
          ...list[index],
          ...changes
        };
      }
    });
  }

  queuePendingChange(id, changes) {
    if (!this.pendingChanges[id]) {
      this.pendingChanges[id] = {};
    }
    Object.entries(changes).forEach(([key, value]) => {
      this.pendingChanges[id][key] = value;
    });
  }

  clearPendingChange(id) {
    delete this.pendingChanges[id];
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

  escapeAttr(value) {
    return this.escapeHtml(value);
  }

  showError(message) {
    console.error(message);
    ['all', 'equipment', 'construction', 'plantation'].forEach((tab) => {
      const tbody = document.getElementById(`${tab}-tbody`);
      if (tbody) {
        const colspan = tab === 'all' ? 8 : tab === 'equipment' ? 15 : 14;
        tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">${message}</td></tr>`;
      }
    });
  }

  showLoadingState() {
    const configs = [
      { tab: 'all', columns: 8 },
      { tab: 'equipment', columns: 15 },
      { tab: 'construction', columns: 14 },
      { tab: 'plantation', columns: 14 }
    ];

    configs.forEach(({ tab, columns }) => {
      const tbody = document.getElementById(`${tab}-tbody`);
      if (tbody) {
        tbody.innerHTML = `
                        <tr>
                            <td colspan="${columns}" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">กำลังโหลด...</span>
                                </div>
                                <p class="mt-2 mb-0">กำลังโหลดข้อมูล...</p>
                            </td>
                        </tr>
                    `;
      }
    });
  }

  async exportToExcel(tab = null) {
    // ตรวจสอบว่ามี XLSX library หรือไม่
    if (typeof XLSX === 'undefined') {
      alert('ไม่พบ XLSX library กรุณาโหลดหน้าใหม่');
      return;
    }

    try {
      // ใช้ tab ที่ระบุ หรือใช้ tab ที่ active
      let currentTab = tab;

      if (!currentTab) {
        // ตรวจสอบ tab ที่ active จริงๆ จาก DOM
        const activeTabPane = document.querySelector('.tab-pane.active');
        currentTab = this.currentTab;

        if (activeTabPane) {
          const tabId = activeTabPane.id;
          if (tabId.includes('all')) currentTab = 'all';
          else if (tabId.includes('equipment')) currentTab = 'equipment';
          else if (tabId.includes('construction')) currentTab = 'construction';
          else if (tabId.includes('plantation')) currentTab = 'plantation';
        }
      }

      let data = this.data[currentTab] || [];

      console.log('🔍 Export Tab (Display Format):', currentTab);
      console.log('🔍 Initial data length:', data.length);

      // สำหรับ tab ที่มีข้อมูลครุภัณฑ์ ให้กรองเฉพาะที่ข้อมูลครบถ้วน
      let excludedCount = 0;
      if (
        currentTab === 'equipment' ||
        currentTab === 'construction' ||
        currentTab === 'plantation'
      ) {
        const originalLength = data.length;
        data = data.filter((item) => {
          const hasEquipmentCodeType =
            item.equipment_code_type_id && item.equipment_code_type_id !== '';
          const hasEquipmentType =
            item.equipment_type_id && item.equipment_type_id !== '';
          const hasCountUnit = item.count_unit_id && item.count_unit_id !== '';
          return hasEquipmentCodeType && hasEquipmentType && hasCountUnit;
        });
        excludedCount = originalLength - data.length;

        if (excludedCount > 0) {
          console.log(`⚠️ กรอง ${excludedCount} รายการที่ข้อมูลครุภัณฑ์ไม่ครบ`);
        }
      }

      console.log('🔍 Filtered data length:', data.length);

      if (data.length === 0) {
        let message = 'ไม่มีข้อมูลสำหรับ Export';
        if (excludedCount > 0) {
          message = `ไม่มีข้อมูลที่สามารถ export ได้\n\nรายการทั้งหมด ${excludedCount} รายการ ขาดข้อมูลครุภัณฑ์\nกรุณาเพิ่มข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ`;
        }
        alert(message);
        return;
      }

      // แจ้งเตือนถ้ามีรายการที่ถูกกรองออก
      if (excludedCount > 0) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            icon: 'warning',
            title: 'แจ้งเตือน',
            html: `มีรายการที่ไม่สามารถ export ได้ <strong>${excludedCount}</strong> รายการ<br>
                   เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน<br><br>
                   <small class="text-muted">ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ</small><br><br>
                   จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน <strong>${data.length}</strong> รายการ`,
            confirmButtonText: 'ตกลง, Export ต่อ',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก'
          });

          if (!result.isConfirmed) {
            return;
          }
        } else {
          const proceed = confirm(
            `มีรายการที่ไม่สามารถ export ได้ ${excludedCount} รายการ\n` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน\n\n` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ\n\n` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน ${data.length} รายการ\n\n` +
              `ต้องการดำเนินการต่อหรือไม่?`
          );

          if (!proceed) {
            return;
          }
        }
      }

      // สร้าง array สำหรับ Excel (รูปแบบตาม display ในตาราง)
      const rows = [];

      // เพิ่มแถวหัวตารางตาม tab ปัจจุบัน (ตามรูปแบบที่แสดงในตาราง)
      if (currentTab === 'all') {
        rows.push([
          'ลำดับ',
          'วันที่ดำเนินรายการ',
          'รหัสคำขอ',
          'ชื่อโครงการ',
          'ปีงบประมาณ',
          'หน่วยงาน',
          'สถานะ',
          'ทดสอบรายการ'
        ]);
        data.forEach((item, index) => {
          rows.push([
            index + 1,
            this.formatDate(item.created_at),
            item.request_number || '-',
            item.item_name || '-',
            item.fiscal_year_id || '-',
            item.department_name || '-',
            'อนุมัติ',
            item.fund_area_code || '-'
          ]);
        });
      } else if (currentTab === 'equipment') {
        // Export ตามรูปแบบที่แสดงในตาราง
        rows.push([
          'ลำดับ',
          'หน่วยงาน',
          'วันที่ดำเนินรายการ',
          'รหัสคำขอ',
          'รายการ',
          'จำนวน',
          'ราคาต่อหน่วย',
          'รวมเงิน',
          'เหตุผลและความจำเป็น',
          'รายการ',
          'รหัสครุภัณฑ์ (ชนิด)',
          'รหัสประเภทสินทรัพย์',
          'รหัสครุภัณฑ์ (ประเภท)',
          'หน่วยนับ',
          'ทดสอบรายการ'
        ]);

        data.forEach((item, index) => {
          const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
          const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
          const countUnitLabel = this.getCountUnitLabel(item);

          // Format quantity and check if it was empty
          const qtyResult = this.formatQuantityForExport(item.quantity);
          const approvedAmount = this.getApprovedAmount(item);
          const unitPrice = this.formatUnitPriceForExport(
            item.unit_price,
            approvedAmount,
            qtyResult.quantity
          );

          rows.push([
            index + 1,
            item.department_name || '-',
            this.formatDate(item.created_at),
            item.request_number || '-',
            item.item_name || '-',
            qtyResult.quantity,
            unitPrice,
            this.formatAmountForExport(approvedAmount),
            item.reason || '-',
            this.getFundAreaLabel(item) || '-',
            equipmentCodeTypeLabel || '-',
            item.asset_type_code || '-',
            equipmentTypeLabel || '-',
            countUnitLabel || '-',
            item.fund_area_code || '-'
          ]);
        });
      } else if (currentTab === 'construction') {
        // Export ตามรูปแบบที่แสดงในตาราง
        rows.push([
          'ลำดับ',
          'หน่วยงาน',
          'วันที่ดำเนินรายการ',
          'รหัสคำขอ',
          'รายการ',
          'รวมเงิน',
          'เหตุผลและความจำเป็น',
          'รายการ',
          'รหัสครุภัณฑ์ (ชนิด)',
          'รหัสประเภทสินทรัพย์',
          'รหัสครุภัณฑ์ (ประเภท)',
          'หน่วยนับ',
          'วันที่คาดว่าจะเบิกจ่าย',
          'ทดสอบรายการ'
        ]);

        data.forEach((item, index) => {
          const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
          const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
          const countUnitLabel = this.getCountUnitLabel(item);

          rows.push([
            index + 1,
            item.department_name || '-',
            this.formatDate(item.created_at),
            item.request_number || '-',
            item.item_name || '-',
            this.formatAmountForExport(this.getApprovedAmount(item)),
            item.reason || '-',
            this.getFundAreaLabel(item) || '-',
            equipmentCodeTypeLabel || '-',
            item.asset_type_code || '-',
            equipmentTypeLabel || '-',
            countUnitLabel || '-',
            item.expected_disbursement_date || '-',
            item.fund_area_code || '-'
          ]);
        });
      } else if (currentTab === 'plantation') {
        // Export ตามรูปแบบที่แสดงในตาราง
        rows.push([
          'ลำดับ',
          'หน่วยงาน',
          'วันที่ดำเนินรายการ',
          'รหัสคำขอ',
          'รายการ',
          'รวมเงิน',
          'เหตุผลและความจำเป็น',
          'รายการ',
          'รหัสครุภัณฑ์ (ชนิด)',
          'รหัสประเภทสินทรัพย์',
          'รหัสครุภัณฑ์ (ประเภท)',
          'หน่วยนับ',
          'พื้นที่ปลูก (ไร่)',
          'ทดสอบรายการ'
        ]);

        data.forEach((item, index) => {
          const equipmentCodeTypeLabel = this.getEquipmentCodeTypeLabel(item);
          const equipmentTypeLabel = this.getEquipmentTypeLabel(item);
          const countUnitLabel = this.getCountUnitLabel(item);

          rows.push([
            index + 1,
            item.department_name || '-',
            this.formatDate(item.created_at),
            item.request_number || '-',
            item.item_name || '-',
            this.formatAmountForExport(this.getApprovedAmount(item)),
            item.reason || '-',
            this.getFundAreaLabel(item) || '-',
            equipmentCodeTypeLabel || '-',
            item.asset_type_code || '-',
            equipmentTypeLabel || '-',
            countUnitLabel || '-',
            item.plantation_area || '-',
            item.fund_area_code || '-'
          ]);
        });
      }

      // สร้าง worksheet และ workbook
      const ws = XLSX.utils.aoa_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'รายงาน');

      // กำหนดชื่อไฟล์
      const timestamp = new Date()
        .toISOString()
        .replace(/[:.]/g, '-')
        .slice(0, 19);
      const filename = `รายงานคำขอตั้งงบลงทุน_${currentTab}_${timestamp}.xlsx`;

      // ดาวน์โหลดไฟล์
      XLSX.writeFile(wb, filename);

      console.log('✅ Export สำเร็จ');
    } catch (error) {
      console.error('❌ Export ล้มเหลว:', error);
      alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
    }
  }

  async exportAssetReport() {
    // ส่งออกข้อมูล Asset (รูปแบบปัจจุบัน)
    if (typeof XLSX === 'undefined') {
      if (window.Swal && typeof window.Swal.fire === 'function') {
        await window.Swal.fire({
          icon: 'error',
          title: 'ข้อผิดพลาด',
          text: 'ไม่พบ XLSX library กรุณาโหลดหน้าใหม่',
          confirmButtonText: 'ตกลง'
        });
      } else {
        alert('ไม่พบ XLSX library กรุณาโหลดหน้าใหม่');
      }
      return;
    }

    try {
      let allData = this.data.all || [];

      console.log('🔍 Export Asset Report');
      console.log('🔍 Initial data length:', allData.length);

      // กรองเฉพาะรายการที่มีข้อมูลครุภัณฑ์ครบ 3 รายการ
      const originalLength = allData.length;
      allData = allData.filter((item) => {
        const hasEquipmentCodeType =
          item.equipment_code_type_id && item.equipment_code_type_id !== '';
        const hasEquipmentType =
          item.equipment_type_id && item.equipment_type_id !== '';
        const hasCountUnit = item.count_unit_id && item.count_unit_id !== '';
        return hasEquipmentCodeType && hasEquipmentType && hasCountUnit;
      });

      const excludedCount = originalLength - allData.length;
      if (excludedCount > 0) {
        console.log(`⚠️ กรอง ${excludedCount} รายการที่ข้อมูลครุภัณฑ์ไม่ครบ`);
      }

      console.log('🔍 Filtered data length:', allData.length);

      if (allData.length === 0) {
        let message = 'ไม่มีข้อมูลสำหรับ Export';
        if (excludedCount > 0) {
          message = `ไม่มีข้อมูลที่สามารถ export ได้\n\nรายการทั้งหมด ${excludedCount} รายการ ขาดข้อมูลครุภัณฑ์\nกรุณาเพิ่มข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ`;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
          await window.Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถ Export ได้',
            text: message,
            confirmButtonText: 'ตกลง'
          });
        } else {
          alert(message);
        }
        return;
      }

      // แจ้งเตือนถ้ามีรายการที่ถูกกรองออก
      if (excludedCount > 0) {
        let proceed = false;

        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            icon: 'warning',
            title: 'แจ้งเตือน',
            html:
              `มีรายการที่ไม่สามารถ export ได้ <strong>${excludedCount}</strong> รายการ<br>` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน<br><br>` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ<br><br>` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน <strong>${allData.length}</strong> รายการ<br><br>` +
              `ต้องการดำเนินการต่อหรือไม่?`,
            confirmButtonText: 'ตกลง, Export ต่อ',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก'
          });
          proceed = result.isConfirmed;
        } else {
          proceed = confirm(
            `มีรายการที่ไม่สามารถ export ได้ ${excludedCount} รายการ\n` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน\n\n` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ\n\n` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน ${allData.length} รายการ\n\n` +
              `ต้องการดำเนินการต่อหรือไม่?`
          );
        }

        if (!proceed) {
          return;
        }
      }

      const rows = [];

      // จัดกลุ่มข้อมูลตามหน่วยงาน
      const groupedData = {};
      allData.forEach((item) => {
        const departName = item.department_name || 'ไม่ระบุหน่วยงาน';
        if (!groupedData[departName]) {
          groupedData[departName] = [];
        }
        groupedData[departName].push(item);
      });

      // เพิ่มหัวตาราง
      rows.push([
        'ลำดับที่',
        'รหัสหน่วยงาน',
        'หน่วยงาน',
        'รายการ',
        'จำนวน (หน่วย)',
        'ราคาต่อหน่วย',
        'รวมเงิน (บาท)',
        'เหตุผลและความจำเป็น',
        'รหัสครุภัณฑ์ (ชนิด)',
        'รหัสครุภัณฑ์ (ประเภท)'
      ]);

      // เพิ่มข้อมูลแต่ละหน่วยงาน
      let runningNumber = 1;
      Object.keys(groupedData)
        .sort()
        .forEach((departName) => {
          const items = groupedData[departName];
          items.forEach((item) => {
            // Format quantity and check if it was empty
            const qtyResult = this.formatQuantityForExport(item.quantity);
            const approvedAmount = this.getApprovedAmount(item);
            const unitPrice = this.formatUnitPriceForExport(
              item.unit_price,
              approvedAmount,
              qtyResult.quantity
            );

            rows.push([
              runningNumber++,
              item.department_code || item.branch_code || '-',
              item.department_name || item.department_name || departName,
              item.item_name || '-',
              qtyResult.quantity,
              unitPrice,
              this.formatAmountForExport(approvedAmount),
              item.reason || '-',
              item.equipment_code_type_code || '-',
              item.equipment_type_code || '-'
              // item.fund_area_code || '-'
            ]);
          });
        });

      const ws = XLSX.utils.aoa_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Asset Report');

      const timestamp = new Date()
        .toISOString()
        .replace(/[:.]/g, '-')
        .slice(0, 19);
      const filename = `Asset_Report_${timestamp}.xlsx`;

      XLSX.writeFile(wb, filename);
      console.log('✅ Export Asset Report สำเร็จ');
    } catch (error) {
      console.error('❌ Export Asset Report ล้มเหลว:', error);
      alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
    }
  }

  async exportSAPReport() {
    // ส่งออกข้อมูล SAP Interface เป็น .txt ไฟล์ คั่นด้วย Tab (TSV format)
    try {
      let allData = this.data.all || [];
      console.log('Initial allData length:', allData);

      // กรองข้อมูลเฉพาะรายการที่:
      // 1. sap_status ไม่ใช่ 'success'
      // 2. มีข้อมูลครุภัณฑ์ครบ 3 รายการ (รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, หน่วยนับ)
      const filteredData = allData.filter((item) => {
        // ตรวจสอบ sap_status
        if (item.sap_status === 'success') {
          return false;
        }

        // ตรวจสอบข้อมูลครุภัณฑ์ครบถ้วน
        const hasEquipmentCodeType =
          item.equipment_code_type_id && item.equipment_code_type_id !== '';
        const hasEquipmentType =
          item.equipment_type_id && item.equipment_type_id !== '';
        const hasCountUnit = item.count_unit_id && item.count_unit_id !== '';

        // ต้องมีครบทั้ง 3 รายการ
        return hasEquipmentCodeType && hasEquipmentType && hasCountUnit;
      });

      // นับจำนวนที่ถูกกรองออกเพราะข้อมูลไม่ครบ
      const excludedCount =
        allData.filter((item) => item.sap_status !== 'success').length -
        filteredData.length;
      if (excludedCount > 0) {
        console.log(
          `⚠️ ไม่สามารถ export ได้ ${excludedCount} รายการ เนื่องจากข้อมูลครุภัณฑ์ไม่ครบ (ต้องมี: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, หน่วยนับ)`
        );
      }

      // ใช้ filteredData แทน allData ในขั้นตอนถัดไป
      allData = filteredData || [];

      console.log('🔍 Export SAP Report');
      console.log('🔍 Total data length:', allData.length);

      // แจ้งเตือนถ้ามีรายการที่ไม่สามารถ export ได้
      if (excludedCount > 0 && allData.length > 0) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
          await window.Swal.fire({
            icon: 'warning',
            title: 'แจ้งเตือน',
            html: `มีรายการที่ไม่สามารถ export ได้ <strong>${excludedCount}</strong> รายการ<br>
                   เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน<br><br>
                   <small class="text-muted">ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ</small><br><br>
                   จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน <strong>${allData.length}</strong> รายการ`,
            confirmButtonText: 'ตกลง, Export ต่อ',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก'
          }).then((result) => {
            if (!result.isConfirmed) {
              throw new Error('User cancelled export');
            }
          });
        }
      }

      if (allData.length === 0) {
        let message = 'ไม่มีข้อมูลสำหรับ Export';
        if (excludedCount > 0) {
          message = `ไม่มีข้อมูลที่สามารถ export ได้<br><br>รายการทั้งหมด <strong>${excludedCount}</strong> รายการ ขาดข้อมูลครุภัณฑ์<br><small class="text-muted">กรุณาเพิ่มข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ</small>`;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
          window.Swal.fire({
            icon: 'info',
            title: 'ไม่มีข้อมูลสำหรับ Export',
            html: message,
            confirmButtonText: 'ตกลง'
          });
        } else {
          alert('ไม่มีข้อมูลสำหรับ Export');
        }
        return;
      }

      const TAB = '\t';
      const NEWLINE = '\r\n';
      const lines = [];
      const exportedIds = []; // เก็บ ID ของรายการที่ export

      // ไม่ใช้ header - export เฉพาะข้อมูล (TSV format)
      // Header ถูกปิดการใช้งานเพื่อให้ SAP import ได้โดยตรง

      // เพิ่มข้อมูล
      allData.forEach((item) => {
        // เก็บ ID สำหรับอัพเดท SAP status
        if (item.id) {
          exportedIds.push(item.id);
        }

        // ศูนย์เงินทุน - จาก department_code
        const fundCenter = item.department_code || '';

        // เงินทุน - จาก budget_source_code
        const fund = item.budget_source_code || '';

        // ประเภทครุภัณฑ์ - equipment_type (full และ cut)
        const equipmentType = this.getEquipmentTypeCode(item);

        // ชนิดครุภัณฑ์ - equipment_code_type (full และ cut)
        const equipmentCodeType = this.getEquipmentCodeTypeCode(item);

        // ขอบเขตหน้าที่ - Generate merge
        const functionalArea = this.generateActivityCode(
          equipmentType,
          equipmentCodeType
        );

        // ขอบเขตหน้าที่ - เว้นว่าง
        //const functionalArea = '';

        // รายการภาระผูกพัน - จาก asset_type_code
        const commitment = item.asset_type_code || '';

        // ปี - แปลงจาก พ.ศ. เป็น ค.ศ.
        const fiscalYear = this.convertFiscalYearToAD(item.fiscal_year_id);

        // ประเภทงบ - FIX = 8
        const budgetType = '8';

        // จำนวนเงิน (ไม่ใส่ format เพื่อให้เป็นตัวเลข)
        // const amount = this.formatAmountForExport(item.total_amount);

        const amountNum = this.getApprovedAmount(item);
        const amount = Number.isFinite(amountNum) ? String(amountNum) : '0';

        // รายการ (แทนที่ Tab, Newline, CR เพื่อไม่ให้ขัดกับ TSV format)
        const itemName = (item.item_name || '').replace(/[\t\r\n]/g, ' ');

        // ทดสอบรายการ (fund area code)
        const fundAreaCode = item.fund_area_code || '';

        lines.push(
          [
            fundCenter,
            fund,
            fundAreaCode,
            functionalArea,
            commitment,
            fiscalYear,
            budgetType,
            amount,
            itemName
          ].join(TAB)
        );
      });

      // สร้างเนื้อหาไฟล์ .txt
      const txtContent = lines.join(NEWLINE);

      const timestamp = new Date()
        .toISOString()
        .replace(/[:.]/g, '-')
        .slice(0, 19);
      const filename = `SAP_Interface_${timestamp}.txt`;

      // ดาวน์โหลดไฟล์ที่ฝั่ง client โดยไม่บันทึกลง storage
      const blob = new Blob([txtContent], {
        type: 'text/plain;charset=utf-8'
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      this.showToast('Export SAP Report (.txt) สำเร็จ', 'success');

      // อัพเดท SAP status เป็น 'success' สำหรับรายการที่ export
      if (exportedIds.length > 0) {
        await this.updateSapStatus(exportedIds);
      }
    } catch (error) {
      console.error('❌ Export SAP Report ล้มเหลว:', error);
      alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
    }
  }

  async updateSapStatus(requestIds) {
    try {
      console.log(
        '🔄 Updating SAP status for',
        requestIds.length,
        'requests...'
      );

      const response = await fetch(
        '../controllers/investment_budget/ib_requests_controller.php',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'update_sap_status',
            request_ids: requestIds,
            status: 'success'
          })
        }
      );

      const result = await response.json();

      if (result.status === 'success') {
        console.log('✅ SAP status updated successfully');
        this.showToast('อัพเดท SAP status สำเร็จ', 'success');
        // รีโหลดข้อมูลเพื่อแสดง status ใหม่
        await this.loadAllData();
      } else {
        console.error('❌ Failed to update SAP status:', result.message);
        this.showToast(
          'ไม่สามารถอัพเดท SAP status ได้: ' + result.message,
          'error'
        );
      }
    } catch (error) {
      console.error('❌ Error updating SAP status:', error);
      this.showToast('เกิดข้อผิดพลาดในการอัพเดท SAP status', 'error');
    }
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

  async exportProjectReport() {
    // ส่งออกข้อมูลรายงาน (CSV format with Project data)
    try {
      let allData = this.data.all || [];

      console.log('🔍 Export Project Report (CSV)');
      console.log('🔍 Initial data length:', allData.length);

      // กรองเฉพาะรายการที่มีข้อมูลครุภัณฑ์ครบ 3 รายการ
      const originalLength = allData.length;
      allData = allData.filter((item) => {
        const hasEquipmentCodeType =
          item.equipment_code_type_id && item.equipment_code_type_id !== '';
        const hasEquipmentType =
          item.equipment_type_id && item.equipment_type_id !== '';
        const hasCountUnit = item.count_unit_id && item.count_unit_id !== '';
        return hasEquipmentCodeType && hasEquipmentType && hasCountUnit;
      });

      const excludedCount = originalLength - allData.length;
      if (excludedCount > 0) {
        console.log(`⚠️ กรอง ${excludedCount} รายการที่ข้อมูลครุภัณฑ์ไม่ครบ`);
      }

      console.log('🔍 Filtered data length:', allData.length);

      if (allData.length === 0) {
        let message = 'ไม่มีข้อมูลสำหรับ Export';
        if (excludedCount > 0) {
          message = `ไม่มีข้อมูลที่สามารถ export ได้\n\nรายการทั้งหมด ${excludedCount} รายการ ขาดข้อมูลครุภัณฑ์\nกรุณาเพิ่มข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ`;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
          await window.Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถ Export ได้',
            text: message,
            confirmButtonText: 'ตกลง'
          });
        } else {
          alert(message);
        }
        return;
      }

      // แจ้งเตือนถ้ามีรายการที่ถูกกรองออก
      if (excludedCount > 0) {
        let proceed = false;

        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            icon: 'warning',
            title: 'แจ้งเตือน',
            html:
              `มีรายการที่ไม่สามารถ export ได้ <strong>${excludedCount}</strong> รายการ<br>` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน<br><br>` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ<br><br>` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน <strong>${allData.length}</strong> รายการ<br><br>` +
              `ต้องการดำเนินการต่อหรือไม่?`,
            confirmButtonText: 'ตกลง, Export ต่อ',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก'
          });
          proceed = result.isConfirmed;
        } else {
          proceed = confirm(
            `มีรายการที่ไม่สามารถ export ได้ ${excludedCount} รายการ\n` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน\n\n` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ\n\n` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน ${allData.length} รายการ\n\n` +
              `ต้องการดำเนินการต่อหรือไม่?`
          );
        }

        if (!proceed) {
          return;
        }
      }

      const rows = [];

      // เพิ่มหัวตาราง
      rows.push([
        'รหัสบริษัท',
        'ปี',
        'หมายเลข',
        'รหัสโครงการ',
        'ชื่อโครงการ',
        'รหัสกิจกรรม',
        'ชื่อกิจกรรม',
        'กรอกงบประมาณ',
        'เงินทุน',
        // 'รหัสงบประมาณ',
        // 'ชื่องบประมาณ',
        'หมายเหตุ'
        // 'ทดสอบรายการ'
      ]);

      // เพิ่มข้อมูล
      allData.forEach((item) => {
        // รหัสบริษัท - จาก department_code
        // const companyCode = item.department_code || '';
        const companyCode = '10000'; // กำหนดเป็นค่าเดียวเสมอ

        // ปี - แปลงเป็น ค.ศ.
        const year = this.convertFiscalYearToAD(item.fiscal_year_id);

        // หมายเลข - FIX: N/A
        const number = 'N/A';

        // ประเภทครุภัณฑ์ - equipment_type (full และ cut)
        const equipmentType = this.getEquipmentTypeCode(item);

        // ชนิดครุภัณฑ์ - equipment_code_type (full และ cut)
        const equipmentCodeType = this.getEquipmentCodeTypeCode(item);

        // ขอบเขตหน้าที่ - Generate merge
        const functionalArea = this.generateActivityCode(
          equipmentType,
          equipmentCodeType
        );

        // รหัสโครงการ - Generate จาก merge (ตัดออก 3 digit สุดท้าย)
        const projectCode = this.generateProjectCode(functionalArea);

        // ชื่อโครงการ - จาก project_program
        const projectName = item.project_program || '';

        // รหัสกิจกรรม - Generate จาก merge
        const activityCode = functionalArea; //this.generateActivityCode(item);

        // ชื่อกิจกรรม - จาก item_name (attachments)
        const activityName = item.attachment_item_name || item.item_name || '';

        // กรอกงบประมาณ - total_amount (as number with 2 decimals)
        const budgetAmount = this.formatAmountForExport(item.total_amount);

        // เงินทุน - budget_source_code
        const fund = item.budget_source_code || '';

        // รหัสงบประมาณ - เหมือนกับรหัสกิจกรรม
        const budgetCode = activityCode;

        // ชื่องบประมาณ - เหมือนกับชื่อกิจกรรม
        const budgetName = activityName;

        // หมายเหตุ - เว้นว่าง
        const remark = '';

        rows.push([
          companyCode,
          year,
          number,
          // item.fund_area_code ตัก 3 หลักท้ายออก
          item.fund_area_code?.slice(0, -3) || '-',
          // projectCode,
          projectName,
          item.fund_area_code || '-',
          // activityCode,
          activityName,
          budgetAmount,
          fund,
          // budgetCode,
          // budgetName,
          remark
          // item.fund_area_code || '-'
        ]);
      });

      // สร้าง CSV content
      const csvContent = rows
        .map((row) =>
          row
            .map((cell) => {
              // Escape quotes and wrap in quotes if contains comma, quote, or newline
              const cellStr = String(cell);
              if (
                cellStr.includes(',') ||
                cellStr.includes('"') ||
                cellStr.includes('\n')
              ) {
                return `"${cellStr.replace(/"/g, '""')}"`;
              }
              return cellStr;
            })
            .join(',')
        )
        .join('\n');

      // Add BOM for UTF-8
      const BOM = '\uFEFF';
      const blob = new Blob([BOM + csvContent], {
        type: 'text/csv;charset=utf-8;'
      });

      // Create download link
      const link = document.createElement('a');
      const timestamp = new Date()
        .toISOString()
        .replace(/[:.]/g, '-')
        .slice(0, 19);
      const filename = `Project_Report_${timestamp}.csv`;

      if (navigator.msSaveBlob) {
        // IE 10+
        navigator.msSaveBlob(blob, filename);
      } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
      }

      console.log('✅ Export Project Report (CSV) สำเร็จ');
    } catch (error) {
      console.error('❌ Export Project Report ล้มเหลว:', error);
      alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
    }
  }

  generateProjectCode(functionalArea) {
    // ตัวอย่าง: 21401212 -> 21401
    const merged = String(functionalArea).replace(/\D/g, ''); // เอาแต่ตัวเลข
    if (merged.length >= 3) {
      return merged.slice(0, -3); // ตัดออก 3 ตัวสุดท้าย
    }
    return merged;
  }

  generateActivityCode(equipmentType, equipmentCodeType) {
    // Generate: merge "2+1401+212" (เต็ม)
    if (!equipmentType || !equipmentCodeType) return '';
    return `2${equipmentType}${equipmentCodeType}`;
  }

  async exportAssetDetailReport() {
    // ส่งออกรายงาน Asset (รูปแบบรายละเอียดครบถ้วน)
    if (typeof XLSX === 'undefined') {
      if (window.Swal && typeof window.Swal.fire === 'function') {
        await window.Swal.fire({
          icon: 'error',
          title: 'ข้อผิดพลาด',
          text: 'ไม่พบ XLSX library กรุณาโหลดหน้าใหม่',
          confirmButtonText: 'ตกลง'
        });
      } else {
        alert('ไม่พบ XLSX library กรุณาโหลดหน้าใหม่');
      }
      return;
    }

    try {
      let allData = this.data.all || [];

      console.log('🔍 Export Asset Detail Report');
      console.log('🔍 Initial data length:', allData.length);
      // console.log(allData);

      // กรองเฉพาะรายการที่มีข้อมูลครุภัณฑ์ครบ 3 รายการ
      const originalLength = allData.length;
      allData = allData.filter((item) => {
        const hasEquipmentCodeType =
          item.equipment_code_type_id && item.equipment_code_type_id !== '';
        const hasEquipmentType =
          item.equipment_type_id && item.equipment_type_id !== '';
        const hasCountUnit = item.count_unit_id && item.count_unit_id !== '';
        return hasEquipmentCodeType && hasEquipmentType && hasCountUnit;
      });

      const excludedCount = originalLength - allData.length;
      if (excludedCount > 0) {
        console.log(`⚠️ กรอง ${excludedCount} รายการที่ข้อมูลครุภัณฑ์ไม่ครบ`);
      }

      console.log('🔍 Filtered data length:', allData.length);

      if (allData.length === 0) {
        let message = 'ไม่มีข้อมูลสำหรับ Export';
        if (excludedCount > 0) {
          message = `ไม่มีข้อมูลที่สามารถ export ได้\n\nรายการทั้งหมด ${excludedCount} รายการ ขาดข้อมูลครุภัณฑ์\nกรุณาเพิ่มข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ`;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
          await window.Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถ Export ได้',
            text: message,
            confirmButtonText: 'ตกลง'
          });
        } else {
          alert(message);
        }
        return;
      }

      // แจ้งเตือนถ้ามีรายการที่ถูกกรองออก
      if (excludedCount > 0) {
        let proceed = false;

        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            icon: 'warning',
            title: 'แจ้งเตือน',
            html:
              `มีรายการที่ไม่สามารถ export ได้ <strong>${excludedCount}</strong> รายการ<br>` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน<br><br>` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ<br><br>` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน <strong>${allData.length}</strong> รายการ<br><br>` +
              `ต้องการดำเนินการต่อหรือไม่?`,
            confirmButtonText: 'ตกลง, Export ต่อ',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก'
          });
          proceed = result.isConfirmed;
        } else {
          proceed = confirm(
            `มีรายการที่ไม่สามารถ export ได้ ${excludedCount} รายการ\n` +
              `เนื่องจากข้อมูลครุภัณฑ์ไม่ครบถ้วน\n\n` +
              `ต้องมีข้อมูล: รหัสครุภัณฑ์, ชนิด ประเภทครุภัณฑ์, และหน่วยนับ\n\n` +
              `จะทำการ export เฉพาะรายการที่ข้อมูลครบถ้วน ${allData.length} รายการ\n\n` +
              `ต้องการดำเนินการต่อหรือไม่?`
          );
        }

        if (!proceed) {
          return;
        }
      }

      const rows = [];

      // เพิ่มหัวตาราง (ตามรูปแบบที่กำหนด)
      rows.push([
        'จำนวน (หน่วย)',
        'หน่วย',
        'หมวดสินทรัพย์',
        'รายการ(50)',
        'รายการ(2)',
        'ละเอียด',
        'วันที่ลงทุน',
        'ประเภทธุรกิจ',
        'ศูนย์เงินทุน',
        'ศูนย์เงินทุน/ที่รับผิดชอบ',
        'เงินทุน',
        'ขอบเขตหน้าที่',
        'ประเภทครุภัณฑ์',
        'ชนิดครุภัณฑ์',
        'สถานะครุภัณฑ์',
        'ประเภทหมวดหมู่ค่าใช้จ่าย',
        'Useful life of Depre. Area 01',
        'Period of Depre. Area 01'
        // 'ทดสอบรายการ'
      ]);

      // เพิ่มข้อมูล
      allData.forEach((item) => {
        console.log('Processing item ID:', item.id);
        console.log('Item data:', item);
        // จำนวน (หน่วย) - For export, use 1 if no value
        const qtyResult = this.formatQuantityForExport(item.quantity);
        const quantity = qtyResult.quantity;

        // หน่วย - count_unit_code
        const countUnit = item.count_unit_code || '';

        // หมวดสินทรัพย์ - asset_category
        const assetCategory = item.asset_category_code || '';

        // รายการ - item_name
        const itemName = item.item_name || '';

        // ละเอียด - description
        const description = item.description || '';

        // วันที่ลงทุน - current_date in format DD.MM.YYYY
        const invertmentDate = this.formatDateDDMMYYYY(item.created_at);

        // ประเภทธุรกิจ - budget_source (ตัด G ออก และแปลง 2 เป็น 1)
        const businessType = this.convertBusinessType(item.budget_source_code);

        // ศูนย์เงินทุน - department_code
        const fundCenter = item.department_code || '';

        // ศูนย์เงินทุน/ที่รับผิดชอบ - department_code (เหมือนกัน)
        const fundCenterResponsible = item.department_code || '';

        // เงินทุน - budget_source_code
        const fund = item.budget_source_code || '';

        // ประเภทครุภัณฑ์ - equipment_type (full และ cut)
        const equipmentType = this.getEquipmentTypeCode(item);

        // ชนิดครุภัณฑ์ - equipment_code_type (full และ cut)
        const equipmentCodeType = this.getEquipmentCodeTypeCode(item);

        // ขอบเขตหน้าที่ - Generate merge
        const functionalArea = this.generateActivityCode(
          equipmentType,
          equipmentCodeType
        );

        // สถานะครุภัณฑ์ - FIX: S01
        const equipmentStatus = 'S01';

        // ประเภทหมวดหมู่ค่าใช้จ่าย - FIX: 6
        const expenseCategory = '6';

        rows.push([
          quantity,
          countUnit,
          assetCategory,
          itemName,
          itemName,
          description,
          invertmentDate,
          businessType,
          fundCenter,
          fundCenterResponsible,
          fund,
          item.fund_area_code || '-',
          // functionalArea,
          equipmentType,
          equipmentCodeType,
          equipmentStatus,
          expenseCategory
        ]);
      });

      const ws = XLSX.utils.aoa_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Asset Detail Report');

      const timestamp = new Date()
        .toISOString()
        .replace(/[:.]/g, '-')
        .slice(0, 19);
      const filename = `Asset_Detail_Report_${timestamp}.xlsx`;

      XLSX.writeFile(wb, filename);
      console.log('✅ Export Asset Detail Report สำเร็จ');
    } catch (error) {
      console.error('❌ Export Asset Detail Report ล้มเหลว:', error);
      alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
    }
  }

  formatDateDDMMYYYY(dateString) {
    // แปลงวันที่เป็นรูปแบบ DD.MM.YYYY
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
  }

  convertBusinessType(budgetSourceCode) {
    // ตัด G ออก และแปลง 2 เป็น 1
    // ตัวอย่าง: G2013 (C) -> 1013
    if (!budgetSourceCode) return '';

    let result = String(budgetSourceCode).substring(1); // ตัดตัวอักษรตัวแรกออก
    result = result.replace(/\s*\([^)]*\)/g, ''); // ตัด (C) ออก

    // แปลงเลข 2 ตัวแรกเป็น 1 ถ้าขึ้นต้นด้วย 2
    if (result.startsWith('2')) {
      result = '1' + result.substring(1);
    }

    return result;
  }

  getEquipmentTypeCode(item) {
    // full: 1401, cut: 1401
    const equipmentTypeId = item.equipment_type_id || '';
    if (!equipmentTypeId) return '';

    // ในกรณีที่มีข้อมูล equipment_type_code จาก DB
    if (item.equipment_type_code) {
      return item.equipment_type_code;
    }

    return String(equipmentTypeId);
  }

  getEquipmentCodeTypeCode(item) {
    // full: 21201011, cut: 212
    const equipmentCodeTypeCode = item.equipment_code_type_code || '';
    if (!equipmentCodeTypeCode) return '';

    // ถ้ามีข้อมูล equipment_code_type_code จาก DB, ใช้ return ไปแล้วข้างบน
    // ฟอร์แมตแบบ 'cut': คืนค่าเลข 3 หลักแรก
    if (
      typeof equipmentCodeTypeCode === 'string' ||
      typeof equipmentCodeTypeCode === 'number'
    ) {
      let code = String(equipmentCodeTypeCode);
      // ฟอร์แมต full เช่น 21201011 หรือ 21205002
      if (code.length >= 3) {
        return code.substring(0, 3);
      }
      return code;
    }

    //return String(equipmentCodeTypeId);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const manager = new InvestmentBudgetReportManager();
  manager.initialize();
});
