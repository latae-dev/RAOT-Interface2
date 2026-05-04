/**
 * ระบบจัดการข้อมูลหลัก (Master Data Manager)
 *
 * หน้าที่:
 * - โหลดข้อมูลจาก API (ปีงบประมาณ, แหล่งงบประมาณ, เวอร์ชั่นแผน, ประเภทข้อมูล)
 * - จัดการ Choices.js dropdowns แบบปลอดภัย
 * - หลีกเลี่ยงปัญหา setChoices() bug ของ Choices.js v9.1.0
 *
 
 */

class MasterDataManager {
  constructor() {
    // เก็บข้อมูลหลักทั้งหมด
    this.data = {
      fiscalYears: [], // ปีงบประมาณ
      budgetSources: [], // แหล่งงบประมาณ
      versionPlans: [], // เวอร์ชั่นแผน
      assetTypes: [], // ประเภทข้อมูล
      strategies: [], // ยุทธศาสตร์
      equipmentStandards: [], // มาตรฐานครุภัณฑ์
      equipmentSetup: [], // ลักษณะครุภัณฑ์
      assetCategories: [], // หมวดสินทรัพย์
      disbursementPlans: [], // แผนเบิกจ่าย
      installmentWorks: [], // งานงวด
      projectPeriods: [], // ระยะเวลาโครงการ
      implementationYears: [] // ปีที่ดำเนินการ
    };
    this.isInitialized = false;
  }

  /**
   * เรียกข้อมูล master data จาก controller กลาง
   * @param {string} type ประเภท master data
   * @param {Object} params พารามิเตอร์เพิ่มเติม เช่น { action: 'one', id: 1 }
   * @returns {Promise<array|object|null>}
   */
  async fetchMasterData(type, params = {}) {
    const searchParams = new URLSearchParams({
      type,
      action: params.action || 'all',
    });

    Object.entries(params).forEach(([key, value]) => {
      if (key === 'action') return;
      if (value !== undefined && value !== null) {
        searchParams.append(key, value);
      }
    });

    const url = `../controllers/investment_budget/master_data_controller.php?${searchParams.toString()}`;

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    const result = await response.json();
    if (result.status !== 'success') {
      throw new Error(result.message || 'Unknown error');
    }

    return result.data;
  }

  /**
   * เริ่มต้นระบบและโหลดข้อมูลทั้งหมด
   */
  async initialize() {
    console.log('🚀 เริ่มต้นระบบจัดการข้อมูลหลัก...');

    try {
      // โหลดข้อมูลทั้งหมดพร้อมกัน (parallel loading)
      await Promise.all([
        this.loadFiscalYears(), // โหลดปีงบประมาณ
        this.loadBudgetSources(), // โหลดแหล่งงบประมาณ
        this.loadVersionPlans(), // โหลดเวอร์ชั่นแผน
        this.loadAssetTypes(), // โหลดประเภทข้อมูล
        this.loadStrategies(), // โหลดยุทธศาสตร์
        this.loadEquipmentStandards(), // โหลดมาตรฐานครุภัณฑ์
        this.loadEquipmentSetup(), // โหลดลักษณะครุภัณฑ์
        this.loadAssetCategories(), // โหลดหมวดสินทรัพย์
        this.loadDisbursementPlans(), // โหลดแผนเบิกจ่าย
        this.loadInstallmentWorks(), // โหลดงานงวด
        this.loadProjectPeriods(), // โหลดระยะเวลาโครงการ
        this.loadImplementationYears() // โหลดปีที่ดำเนินการ
      ]);

      // เริ่มต้น dropdowns ทั้งหมด
      this.initializeDropdowns();

      this.isInitialized = true;
      console.log('✅ ระบบจัดการข้อมูลหลักพร้อมใช้งาน');
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการเริ่มต้นระบบ:', error);
    }
  }

  // ========================================
  // DATA LOADING METHODS
  // ========================================

  /**
  * โหลดข้อมูลปีงบประมาณ
  */
  async loadFiscalYears() {
    try {
      // Generate 10 ปีนับจากปีปัจจุบัน (พ.ศ.)
      const currentYearBE = new Date().getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.
      const fiscalYears = [];

      for (let i = 0; i < 10; i++) {
        const year = currentYearBE + i;
        fiscalYears.push({
          id: year,
          name: String(year),
          year: year
        });
      }

      this.data.fiscalYears = fiscalYears;
      console.log(`📅 สร้างปีงบประมาณ: ${this.data.fiscalYears.length} รายการ (${currentYearBE}-${currentYearBE + 9})`);
    } catch (error) {
      console.error('❌ ไม่สามารถสร้างข้อมูลปีงบประมาณได้:', error);
    }
  }

  /**
   * โหลดข้อมูลแหล่งงบประมาณ
   */
  async loadBudgetSources() {
    try {
      const data = await this.fetchMasterData('budget_source');
      this.data.budgetSources = Array.isArray(data) ? data : [];
      console.log(`💰 โหลดแหล่งงบประมาณ: ${this.data.budgetSources.length} รายการ`);
      console.log('📋 รายการแหล่งงบประมาณ:', this.data.budgetSources);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลแหล่งงบประมาณได้:', error);
    }
  }

  /**
   * โหลดข้อมูลเวอร์ชั่นแผน
   */
  async loadVersionPlans() {
    try {
      const data = await this.fetchMasterData('version_plan');
      this.data.versionPlans = Array.isArray(data) ? data : [];
      console.log(`📋 โหลดเวอร์ชั่นแผน: ${this.data.versionPlans.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลเวอร์ชั่นแผนได้:', error);
    }
  }

  /**
   * โหลดข้อมูลประเภทข้อมูล
   */
  async loadAssetTypes() {
    try {
      const data = await this.fetchMasterData('asset_type');
      this.data.assetTypes = Array.isArray(data) ? data : [];
      console.log(`🏷️ โหลดประเภทข้อมูล: ${this.data.assetTypes.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลประเภทข้อมูลได้:', error);
    }
  }

  /**
   * โหลดข้อมูลยุทธศาสตร์
   */
  async loadStrategies() {
    try {
      const data = await this.fetchMasterData('strategy');
      this.data.strategies = Array.isArray(data) ? data : [];
      console.log(`🎯 โหลดยุทธศาสตร์: ${this.data.strategies.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลยุทธศาสตร์ได้:', error);
    }
  }

  /**
   * โหลดข้อมูลมาตรฐานครุภัณฑ์
   */
  async loadEquipmentStandards() {
    try {
      const data = await this.fetchMasterData('equipment_standard');
      const records = Array.isArray(data) ? data : [];
      this.data.equipmentStandards = records.map((item) =>
        this.buildEquipmentStandardOption(item)
      );
      console.log(`🔧 โหลดมาตรฐานครุภัณฑ์: ${records.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลมาตรฐานครุภัณฑ์ได้:', error);
    }
  }

  /**
   * โหลดข้อมูลลักษณะครุภัณฑ์
   */
  async loadEquipmentSetup() {
    try {
      const data = await this.fetchMasterData('equipment_setup');
      this.data.equipmentSetup = Array.isArray(data) ? data : [];
      console.log(`⚙️ โหลดลักษณะครุภัณฑ์: ${this.data.equipmentSetup.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลลักษณะครุภัณฑ์ได้:', error);
    }
  }

  /**
   * โหลดข้อมูลหมวดสินทรัพย์
   */
  async loadAssetCategories() {
    try {
      const data = await this.fetchMasterData('asset_category');
      const records = Array.isArray(data) ? data : [];
      this.data.assetCategories = records.map((item) =>
        this.buildAssetCategoryOption(item)
      );
      console.log(`🏷️ โหลดหมวดสินทรัพย์: ${records.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลหมวดสินทรัพย์ได้:', error);
    }
  }

  /**
   * โหลดข้อมูลหมวดสินทรัพย์แบบกรองตาม asset type และ installment work
   * @param {number} assetTypeId - ID ของประเภททรัพย์สิน
   * @param {number} installmentWorkId - 0=ไม่ระบุ, 1=ไม่เป็นงวด, 2=เป็นงวด
   * @returns {Promise<Array>} - Array ของหมวดสินทรัพย์ที่กรองแล้ว
   */
  async loadAssetCategoriesFiltered(assetTypeId, installmentWorkId) {
    try {
      const data = await this.fetchMasterData('asset_category', {
        action: 'by_filters',
        asset_type_id: assetTypeId,
        installment_work_id: installmentWorkId
      });
      const records = Array.isArray(data) ? data : [];
      const filteredCategories = records.map((item) =>
        this.buildAssetCategoryOption(item)
      );
      console.log(
        `🔍 โหลดหมวดสินทรัพย์ที่กรอง (Asset Type: ${assetTypeId}, Installment: ${installmentWorkId}): ${records.length} รายการ`
      );
      return filteredCategories;
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลหมวดสินทรัพย์ที่กรองได้:', error);
      return [];
    }
  }

  /**
   * อัปเดต dropdown ของหมวดสินทรัพย์ด้วยตัวเลือกที่กรองแล้ว
   * @param {string} selectId - ID ของ select element
   * @param {number} assetTypeId - ID ของประเภททรัพย์สิน
   * @param {number} installmentWorkId - 0=ไม่ระบุ, 1=ไม่เป็นงวด, 2=เป็นงวด
   */
  async updateAssetCategoryDropdown(selectId, assetTypeId, installmentWorkId) {
    const element = document.getElementById(selectId);
    if (!element) {
      console.warn(`⚠️ ไม่พบ element: ${selectId}`);
      return;
    }

    console.log(`🔄 กำลังอัปเดต dropdown ${selectId}...`);

    // โหลดข้อมูลที่กรองแล้ว
    const filteredCategories = await this.loadAssetCategoriesFiltered(
      assetTypeId,
      installmentWorkId
    );

    // อัปเดต dropdown ด้วย reinitialize (เพื่อหลีกเลี่ยง bug ของ Choices.js)
    this.reinitializeChoices(element, filteredCategories, 'id', 'display_name');

    console.log(
      `✅ อัปเดต ${selectId} เสร็จสิ้น - มี ${filteredCategories.length} ตัวเลือก`
    );
  }

  /**
   * โหลดข้อมูลแผนเบิกจ่าย
   */
  async loadDisbursementPlans() {
    try {
      const data = await this.fetchMasterData('disbursement_plan');
      this.data.disbursementPlans = Array.isArray(data) ? data : [];
      console.log(`💰 โหลดแผนเบิกจ่าย: ${this.data.disbursementPlans.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลแผนเบิกจ่ายได้:', error);
    }
  }

  /**
   * โหลดข้อมูลงานงวด
   */
  async loadInstallmentWorks() {
    try {
      const data = await this.fetchMasterData('installment_work');
      this.data.installmentWorks = Array.isArray(data) ? data : [];
      console.log(`🏗️ โหลดงานงวด: ${this.data.installmentWorks.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลงานงวดได้:', error);
    }
  }

  /**
   * โหลดข้อมูลระยะเวลาโครงการ
   */
  async loadProjectPeriods() {
    try {
      const data = await this.fetchMasterData('project_period');
      this.data.projectPeriods = Array.isArray(data) ? data : [];
      console.log(`📅 โหลดระยะเวลาโครงการ: ${this.data.projectPeriods.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลระยะเวลาโครงการได้:', error);
    }
  }

  /**
   * โหลดข้อมูลปีที่ดำเนินการ
   */
  async loadImplementationYears() {
    try {
      const data = await this.fetchMasterData('implementation_year');
      this.data.implementationYears = Array.isArray(data) ? data : [];
      console.log(`📅 โหลดปีที่ดำเนินการ: ${this.data.implementationYears.length} รายการ`);
    } catch (error) {
      console.error('❌ ไม่สามารถโหลดข้อมูลปีที่ดำเนินการได้:', error);
    }
  }

  // ========================================
  // DROPDOWN INITIALIZATION METHODS
  // ========================================

  /**
   * เริ่มต้น dropdowns ทั้งหมด
   */
  initializeDropdowns() {
    console.log('🎛️ เริ่มต้น dropdowns ทั้งหมด...');

    // เริ่มต้น dropdown ปีงบประมาณ
    this.setupDropdown(
      'fiscal-year-select',
      this.data.fiscalYears,
      'id',
      'name'
    );

    // เริ่มต้น dropdown เวอร์ชั่นแผน
    this.setupDropdown(
      'version-plan-select',
      this.data.versionPlans,
      'id',
      'name'
    );

    // เริ่มต้น dropdown ประเภทข้อมูล
    this.setupDropdown('asset-type-select', this.data.assetTypes, 'id', 'name');

    // เริ่มต้น dropdown แหล่งงบประมาณ (มีหลายตัว)
    this.setupBudgetSourceDropdowns();

    // เริ่มต้น dropdown มาตรฐานครุภัณฑ์ (มีหลายตัว)
    this.setupEquipmentStandardsDropdowns();

    // เริ่มต้น dropdown ลักษณะครุภัณฑ์ (มีหลายตัว)
    this.setupEquipmentSetupDropdowns();

    // เริ่มต้น dropdown หมวดสินทรัพย์ (มีหลายตัว)
    this.setupAssetCategoriesDropdowns();

    // เริ่มต้น dropdown ยุทธศาสตร์ (มีหลายตัว)
    this.setupStrategiesDropdowns();

    // เริ่มต้น dropdown แผนเบิกจ่าย (มีหลายตัว)
    this.setupDisbursementPlansDropdowns();

    // เริ่มต้น dropdown งานงวด (มีหลายตัว)
    this.setupInstallmentWorksDropdowns();

    // เริ่มต้น dropdown ระยะเวลาโครงการ (มีหลายตัว)
    this.setupProjectPeriodsDropdowns();

    // เริ่มต้น dropdown ปีที่ดำเนินการ (มีหลายตัว)
    this.setupImplementationYearsDropdowns();

    console.log('✅ เริ่มต้น dropdowns เสร็จสิ้น');
  }

  /**
   * ตั้งค่า dropdown แบบ individual
   */
  setupDropdown(elementId, dataArray, valueField, labelField, options = {}) {
    const element = document.getElementById(elementId);
    if (!element) {
      console.warn(`⚠️ ไม่พบ element: ${elementId}`);
      return;
    }

    try {
      // ใช้วิธี re-init เพื่อหลีกเลี่ยง Choices.js bugs
      this.reinitializeChoices(element, dataArray, valueField, labelField, options);
      console.log(`✅ เริ่มต้น ${elementId} เสร็จสิ้น`);
    } catch (error) {
      console.error(`❌ เกิดข้อผิดพลาดในการเริ่มต้น ${elementId}:`, error);
    }
  }

  /**
   * เริ่มต้น dropdown แหล่งงบประมาณทั้งหมด
   */
  setupBudgetSourceDropdowns() {
    // หา dropdown แหล่งงบประมาณทั้งหมด (budget-source-select ไม่มีตัวเลขท้าย)
    const budgetSourceSelects = document.querySelectorAll(
      'select.budget-source-select'
    );

    budgetSourceSelects.forEach((element) => {
      this.setupDropdown(element.id, this.data.budgetSources, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown แหล่งงบประมาณ: ${budgetSourceSelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown มาตรฐานครุภัณฑ์ทั้งหมด
   */
  setupEquipmentStandardsDropdowns() {
    // หา dropdown มาตรฐานครุภัณฑ์ทั้งหมด (equipment-standards-select-*)
    const equipmentStandardsSelects = document.querySelectorAll(
      'select.equipment-standards-select'
    );

    console.log(`🔍 พบ equipment-standards-select: ${equipmentStandardsSelects.length} ตัว`);

    equipmentStandardsSelects.forEach((element, index) => {
      console.log(`  ${index + 1}. กำลัง setup: ${element.id}`);
      this.setupDropdown(
        element.id,
        this.data.equipmentStandards,
        'id',
        'display_name',
        { allowHtml: true }
      );
    });

    console.log(
      `✅ เริ่มต้น dropdown มาตรฐานครุภัณฑ์: ${equipmentStandardsSelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown ลักษณะครุภัณฑ์ทั้งหมด
   */
  setupEquipmentSetupDropdowns() {
    // หา dropdown ลักษณะครุภัณฑ์ทั้งหมด (equipment-setup-select-*)
    const equipmentSetupSelects = document.querySelectorAll(
      'select.equipment-setup-select'
    );

    console.log(`🔍 พบ equipment-setup-select: ${equipmentSetupSelects.length} ตัว`);

    equipmentSetupSelects.forEach((element, index) => {
      console.log(`  ${index + 1}. กำลัง setup: ${element.id}`);
      this.setupDropdown(element.id, this.data.equipmentSetup, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown ลักษณะครุภัณฑ์: ${equipmentSetupSelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown หมวดสินทรัพย์ทั้งหมด
   */
  setupAssetCategoriesDropdowns() {
    // หา dropdown หมวดสินทรัพย์ทั้งหมด (asset-category-select-*)
    const assetCategorySelects = document.querySelectorAll(
      'select.asset-category-select'
    );

    assetCategorySelects.forEach((element) => {
      this.setupDropdown(
        element.id,
        this.data.assetCategories,
        'id',
        'display_name'
      );
    });

    console.log(
      `✅ เริ่มต้น dropdown หมวดสินทรัพย์: ${assetCategorySelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown ยุทธศาสตร์ทั้งหมด
   */
  setupStrategiesDropdowns() {
    // หา dropdown ยุทธศาสตร์ทั้งหมด (strategy-select-*)
    const strategySelects = document.querySelectorAll('select.strategy-select');

    strategySelects.forEach((element) => {
      this.setupDropdown(element.id, this.data.strategies, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown ยุทธศาสตร์: ${strategySelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown แผนเบิกจ่ายทั้งหมด
   */
  setupDisbursementPlansDropdowns() {
    // หา dropdown แผนเบิกจ่ายทั้งหมด (disbursement-plan-select-*)
    const disbursementPlanSelects = document.querySelectorAll(
      'select.disbursement-plan-select'
    );

    disbursementPlanSelects.forEach((element) => {
      this.setupDropdown(element.id, this.data.disbursementPlans, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown แผนเบิกจ่าย: ${disbursementPlanSelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown งานงวดทั้งหมด
   */
  setupInstallmentWorksDropdowns() {
    // หา dropdown งานงวดทั้งหมด (installment-work-select-*)
    const installmentWorkSelects = document.querySelectorAll(
      'select[id^="installment-work-select-"]'
    );

    installmentWorkSelects.forEach((element) => {
      this.setupDropdown(element.id, this.data.installmentWorks, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown งานงวด: ${installmentWorkSelects.length} ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown ระยะเวลาโครงการทั้งหมด
   */
  setupProjectPeriodsDropdowns() {
    // หา dropdown ระยะเวลาเริ่มต้นโครงการทั้งหมด (project-start-period-select-*)
    const projectStartSelects = document.querySelectorAll(
      'select[id^="project-start-period-select-"]'
    );

    console.log(`🔍 พบ project-start-period-select: ${projectStartSelects.length} ตัว`);

    projectStartSelects.forEach((element, index) => {
      console.log(`  ${index + 1}. กำลัง setup: ${element.id}`);
      this.setupDropdown(element.id, this.data.projectPeriods, 'id', 'name');
    });

    // เริ่มต้น dropdown ระยะเวลาสิ้นสุดโครงการ (project-end-period-select-*)
    const projectEndSelects = document.querySelectorAll(
      'select[id^="project-end-period-select-"]'
    );

    console.log(`🔍 พบ project-end-period-select: ${projectEndSelects.length} ตัว`);

    projectEndSelects.forEach((element, index) => {
      console.log(`  ${index + 1}. กำลัง setup: ${element.id}`);
      this.setupDropdown(element.id, this.data.projectPeriods, 'id', 'name');
    });

    console.log(
      `✅ เริ่มต้น dropdown ระยะเวลาโครงการ: ${
        projectStartSelects.length + projectEndSelects.length
      } ตัว`
    );
  }

  /**
   * เริ่มต้น dropdown ปีที่ดำเนินการทั้งหมด
   */
  setupImplementationYearsDropdowns() {
    // หา dropdown ปีที่ดำเนินการทั้งหมด (implementation-year-select-*)
    const implementationYearSelects = document.querySelectorAll(
      'select[id^="implementation-year-select-"]'
    );

    implementationYearSelects.forEach((element) => {
      this.setupDropdown(
        element.id,
        this.data.implementationYears,
        'id',
        'year'
      );
    });

    console.log(
      `✅ เริ่มต้น dropdown ปีที่ดำเนินการ: ${implementationYearSelects.length} ตัว`
    );
  }

  /**
   * Re-initialize Choices.js instance อย่างปลอดภัย
   */
  reinitializeChoices(element, dataArray, valueField, labelField, options = {}) {
    const { allowHtml = false } = options;

    // หาก element ไม่ใช่ select element จริง (อาจเป็น choices wrapper) ให้หา original select
    let targetElement = element;
    if (!element.tagName || element.tagName !== 'SELECT') {
      console.warn(
        `⚠️ Element ${element.id} ไม่ใช่ select element, กำลังค้นหา original select...`
      );
      // หา select element ที่แท้จริง
      const originalId = element.id
        .replace(/^choices--/, '')
        .replace(/-item-choice-\d+$/, '');
      targetElement = document.getElementById(originalId);
      if (!targetElement) {
        console.error(`❌ ไม่พบ original select element สำหรับ: ${element.id}`);
        return;
      }
    }

    // 1. ลบ Choices instance เดิม (ถ้ามี) แบบปลอดภัย
    try {
      if (targetElement.choicesInstance) {
        // ลองใช้ removeActiveItems ก่อน
        if (
          typeof targetElement.choicesInstance.removeActiveItems === 'function'
        ) {
          targetElement.choicesInstance.removeActiveItems();
        }
        // จากนั้นค่อย destroy
        targetElement.choicesInstance.destroy();
        targetElement.choicesInstance = null;
      }
    } catch (error) {
      console.warn(
        `⚠️ ไม่สามารถ destroy Choices instance สำหรับ ${targetElement.id}:`,
        error
      );

      // Force cleanup
      targetElement.choicesInstance = null;

      // ลบ DOM attributes ที่เกี่ยวข้องกับ Choices.js
      targetElement.removeAttribute('data-choice');
      targetElement.classList.remove(
        'choices__input',
        'choices__input--hidden'
      );

      // ลบ wrapper ของ Choices.js ถ้ามี
      const choicesWrapper =
        targetElement.parentElement?.querySelector('.choices');
      if (choicesWrapper && choicesWrapper !== targetElement) {
        choicesWrapper.remove();
      }
    }

    // 2. ล้างข้อมูลเดิมและเพิ่มข้อมูลใหม่
    targetElement.innerHTML = '<option value="">กรุณาเลือก</option>';

    if (dataArray && dataArray.length > 0) {
      dataArray.forEach((item) => {
        const option = document.createElement('option');
        option.value = item[valueField];
        const label = item[labelField] ?? '';
        if (allowHtml) {
          option.innerHTML = label;
        } else {
          option.textContent = label;
        }
        targetElement.appendChild(option);
      });
    }

    // 3. สร้าง Choices.js instance ใหม่
    try {
      targetElement.choicesInstance = new Choices(targetElement, {
        searchEnabled: true, // เปิด search/filter
        itemSelectText: '',
        shouldSort: false,
        placeholder: true,
        placeholderValue: 'กรุณาเลือก',
        searchPlaceholderValue: 'ค้นหา...',
        noResultsText: 'ไม่พบข้อมูล'
      });

      console.log(
        `✅ สร้าง Choices.js instance ใหม่สำหรับ ${targetElement.id} (${
          dataArray ? dataArray.length : 0
        } รายการ)`
      );
    } catch (error) {
      console.error(
        `❌ ไม่สามารถสร้าง Choices.js instance สำหรับ ${targetElement.id}:`,
        error
      );
    }
  }

  buildEquipmentStandardOption(item) {
    const name = this.escapeHtml(item?.name ?? '');
    const description = this.escapeHtml(item?.description ?? '');
    const displayName = description
      ? `${name} <span class="text-danger">(${description})</span>`
      : name;
    return {
      ...item,
      display_name: displayName
    };
  }

  buildAssetCategoryOption(item) {
    const code = this.escapeHtml(item?.code ?? '');
    const name = this.escapeHtml(item?.name ?? '');
    const displayName = code && name ? `${code} ${name}` : name || code;
    return {
      ...item,
      display_name: displayName
    };
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

  /**
   * ดึงค่า value จาก dropdown
   */
  getValue(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
      console.warn(`⚠️ ไม่พบ element: ${elementId}`);
      return '';
    }
    return element.value || '';
  }

  /**
   * กำหนดค่า value ให้ dropdown
   */
  setValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (!element) {
      console.warn(`⚠️ ไม่พบ element: ${elementId}`);
      return;
    }

    element.value = value;

    // ถ้ามี Choices.js instance ให้อัพเดต
    if (element.choicesInstance) {
      element.choicesInstance.setChoiceByValue(value);
    }

    // Trigger change event
    const changeEvent = new Event('change', { bubbles: true });
    element.dispatchEvent(changeEvent);
  }

  /**
   * ดึงข้อมูลฟอร์มทั้งหมด
   */
  getFormData() {
    console.log('🔍 === START getFormData Debug ===');

    // หา card ที่กำลังแสดงอยู่
    const visibleCard = Array.from(document.querySelectorAll('.card.custom-card')).find(
      card => window.getComputedStyle(card).display !== 'none'
    );

    console.log('📋 Visible card:', visibleCard);

    const getInputValue = (baseId) => {
      // ลองหาใน visible card ก่อน
      if (visibleCard) {
        const element = visibleCard.querySelector(`[id^="${baseId}"]`);
        if (element && element.value !== undefined) {
          console.log(`✅ Input [${element.id}] = "${element.value}" (from visible card)`);
          return element.value;
        }
      }

      // ถ้าไม่เจอใน visible card ให้ลองหาแบบปกติ
      const element = document.getElementById(baseId);
      if (!element) {
        console.warn(`⚠️ ไม่พบ input element: ${baseId}`);
        return '';
      }
      const value = element.value;
      console.log(`✅ Input [${baseId}] = "${value}"`);
      return value;
    };

    const getSelectValue = (baseId) => {
      // ลองหาใน visible card ก่อน
      if (visibleCard) {
        const element = visibleCard.querySelector(`select[id^="${baseId}"]`);
        if (element) {
          const value = this.getValue(element.id);
          console.log(`✅ Select [${element.id}] = "${value}" (from visible card)`);
          return value;
        }
      }

      // ถ้าไม่เจอใน visible card ให้ลองหาแบบปกติ
      const element = document.getElementById(baseId);
      if (!element) {
        console.warn(`⚠️ ไม่พบ select element: ${baseId}`);
        return '';
      }
      const value = this.getValue(baseId);
      console.log(`✅ Select [${baseId}] = "${value}"`);
      return value;
    };

    // ทดสอบอ่านค่าจาก reason-1 โดยตรง
    console.log('📋 ทดสอบอ่านค่าโดยตรง:');
    const testReason = document.getElementById('reason-1');
    console.log('  reason-1 element:', testReason);
    console.log('  reason-1 value:', testReason ? testReason.value : 'NULL');
    console.log(
      '  reason-1 parent card display:',
      testReason
        ? window.getComputedStyle(testReason.closest('.card')).display
        : 'NULL'
    );

    console.log('📋 กำลังดึงข้อมูลจากฟอร์ม...');

    const formData = {
      // ฟิลด์หลัก
      fiscalYear: getSelectValue('fiscal-year-select'),
      versionPlan: getSelectValue('version-plan-select'),
      assetType: getSelectValue('asset-type-select'),
      assetTypeCode: getInputValue('asset-type-code'),

      // Budget Sources - ดึงจาก visible card
      budgetSource: getSelectValue('budget-source-select'),

      // Item Name - ดึงจาก visible card
      itemName: getInputValue('item-name'),

      // Description - ดึงจาก visible card
      Description: getInputValue('description'),

      // Reason - ดึงจาก visible card
      Reason: getInputValue('reason'),

      // Quantity - ดึงจาก visible card
      Quantity: getInputValue('quantity') || getInputValue('machinery-quantity'),

      // Price - ดึงจาก visible card
      Price: getInputValue('price') || getInputValue('machinery-price'),

      // Total amount - คำนวณจาก visible card
      totalAmount: parseFloat(getInputValue('total-amount') || getInputValue('machinery-total') || 0),

      // Equipment fields - ดึงจาก visible card
      equipmentStandards: getSelectValue('equipment-standards-select'),
      equipmentNature: getSelectValue('equipment-setup-select'),
      assetCategory: getSelectValue('asset-category-select'),

      // Strategy & Program - ดึงจาก visible card
      strategy: getSelectValue('strategy-select'),
      projectProgram: getInputValue('project-program'),

      // Disbursement - ดึงจาก visible card
      expectedDisbursementDate: getSelectValue('expected-disbursement-date'),
      multiYearDisbursement: getSelectValue('disbursement-plan-select')
    };

    console.log('🔍 === END getFormData Debug ===');
    console.log('📦 Final formData:', formData);

    return formData;
  }
}

// ========================================
// GLOBAL INITIALIZATION
// ========================================

// สร้าง global instance และเริ่มต้นใช้งาน
window.masterDataManager = new MasterDataManager();

// เริ่มต้นเมื่อ DOM พร้อม
document.addEventListener('DOMContentLoaded', function () {
  console.log('📊 Master Data Manager กำลังโหลด...');

  setTimeout(() => {
    window.masterDataManager.initialize();
  }, 500);
});

// Export สำหรับการใช้งานง่าย
window.MasterDataManager = {
  instance: window.masterDataManager,
  getValue: (elementId) => window.masterDataManager.getValue(elementId),
  setValue: (elementId, value) =>
    window.masterDataManager.setValue(elementId, value),
  getFormData: () => window.masterDataManager.getFormData()
};

console.log('📦 Master Data Manager loaded');
console.log('💡 ใช้ MasterDataManager.getFormData() เพื่อทดสอบ');
