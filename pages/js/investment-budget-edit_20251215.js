/**
 * Investment Budget Edit Manager
 * จัดการการแก้ไขคำขอตั้งงบลงทุน
 */
class InvestmentBudgetEditManager {
  constructor() {
    this.requestId = typeof window !== 'undefined' ? window.REQUEST_ID : null;
    this.requestData = null;
    this.isEditMode = true;
  }

  withValidationSuspended(callback) {
    const formInstance = window.investmentBudgetForm;

    if (
      formInstance &&
      typeof formInstance.runWithValidationSuspended === 'function'
    ) {
      return formInstance.runWithValidationSuspended(callback);
    }

    return typeof callback === 'function' ? callback() : undefined;
  }

  /**
   * เริ่มต้นระบบ edit
   */
  async initialize() {
    console.log('📝 เริ่มต้นระบบแก้ไข Request ID:', this.requestId);

    // รอให้ master data manager โหลดข้อมูลเสร็จก่อน
    if (window.masterDataManager && !window.masterDataManager.isInitialized) {
      console.log('⏳ รอ master data manager...');
      await new Promise((resolve) => {
        const checkInterval = setInterval(() => {
          if (window.masterDataManager.isInitialized) {
            clearInterval(checkInterval);
            resolve();
          }
        }, 100);
      });
    }

    // รอให้ DOM พร้อม
    await new Promise((resolve) => setTimeout(resolve, 500));

    // โหลดข้อมูลคำขอและ items
    await this.loadRequestData();

    // รอให้ master data โหลดเสร็จก่อน
    if (window.masterDataManager) {
      // รอให้ master data initialize เสร็จ
      const maxWait = 5000; // รอสูงสุด 5 วินาที
      const startTime = Date.now();
      while (
        !window.masterDataManager.isInitialized &&
        Date.now() - startTime < maxWait
      ) {
        await new Promise((resolve) => setTimeout(resolve, 100));
      }
      console.log('✅ Master data พร้อมใช้งาน');
    }

    // รอเพิ่มอีกนิดให้ dropdowns พร้อม
    await new Promise((resolve) => setTimeout(resolve, 300));

    // แสดงข้อมูลในฟอร์ม
    this.populateForm();

    // เพิ่ม event listeners
    this.setupEventListeners();
    if (
      window.investmentBudgetForm &&
      typeof window.investmentBudgetForm.hideLoading === 'function'
    ) {
      window.investmentBudgetForm.hideLoading();
    }
  }

  /**
   * โหลดข้อมูลคำขอจาก API
   */
  async loadRequestData() {
    try {
      console.log('📥 โหลดข้อมูลคำขอ ID:', this.requestId);

      const headers = this.getUserContextHeaders();
      const response = await fetch(
        `../controllers/investment_budget/ib_requests_controller.php?action=one&id=${this.requestId}`,
        { headers }
      );
      const data = await response.json();

      if (data.status === 'success') {
        this.requestData = data.data;
        // ตอนนี้ item data รวมอยู่ใน requestData แล้ว
        console.log('✅ โหลดข้อมูลสำเร็จ:', this.requestData);
      } else {
        throw new Error(data.message || 'ไม่สามารถโหลดข้อมูลได้');
      }
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);

      if (
        window.investmentBudgetForm &&
        typeof window.investmentBudgetForm.hideLoading === 'function'
      ) {
        window.investmentBudgetForm.hideLoading();
      }

      Swal.fire({
        icon: 'error',
        title: 'ไม่สามารถโหลดข้อมูลได้',
        text: error.message,
        confirmButtonText: 'ตกลง'
      }).then(() => {
        window.location.href = 'investment-budget-list.php';
      });
    }
  }

  /**
   * แสดงข้อมูลในฟอร์ม
   */
  populateForm() {
    if (!this.requestData) {
      console.error('❌ ไม่มีข้อมูล requestData สำหรับ populate form');
      return;
    }

    console.log('📝 แสดงข้อมูลในฟอร์ม...', this.requestData);
    console.log('🏷️ Asset Type Name:', this.requestData.asset_type_name);

    // กำหนดค่า dropdowns หลัก
    this.setDropdownValue(
      'fiscal-year-select',
      this.requestData.fiscal_year_id
    );
    this.setDropdownValue(
      'version-plan-select',
      this.requestData.version_plan_id
    );
    this.setDropdownValue('asset-type-select', this.requestData.asset_type_id);

    // กำหนดค่า budget source สำหรับทุก card
    if (this.requestData.budget_source_id) {
      for (let i = 1; i <= 8; i++) {
        this.setDropdownValue(
          `budget-source-select-${i}`,
          this.requestData.budget_source_id
        );
      }
    }

    // กำหนดค่า equipment setup (ลักษณะครุภัณฑ์) สำหรับทุก card
    if (this.requestData.asset_nature) {
      for (let i = 1; i <= 8; i++) {
        this.setDropdownValue(
          `equipment-setup-select-${i}`,
          this.requestData.asset_nature
        );
      }
    }

    // กำหนดค่า asset category (หมวดสินทรัพย์) สำหรับทุก card
    if (this.requestData.asset_category) {
      for (let i = 1; i <= 8; i++) {
        this.setDropdownValue(
          `asset-category-select-${i}`,
          this.requestData.asset_category
        );
      }
    }

    // กำหนดค่า strategy (ยุทธศาสตร์) สำหรับทุก card
    if (this.requestData.strategy) {
      for (let i = 1; i <= 8; i++) {
        this.setDropdownValue(
          `strategy-select-${i}`,
          this.requestData.strategy
        );
      }
    }

    // กำหนดค่า equipment standards (มาตรฐานครุภัณฑ์) สำหรับ card ที่มี
    if (this.requestData.equipment_standards_id) {
      for (let i = 1; i <= 8; i++) {
        this.setDropdownValue(
          `equipment-standards-select-${i}`,
          this.requestData.equipment_standards_id
        );
      }
    }

    // กำหนดค่า asset type code
    const assetTypeCodeInput = document.getElementById('asset-type-code');
    if (assetTypeCodeInput && this.requestData.asset_type_code) {
      assetTypeCodeInput.value = this.requestData.asset_type_code;
    }

    this.setInputAcrossCards('item-name', this.requestData.item_name);
    this.setInputAcrossCards('description', this.requestData.description);
    this.setInputAcrossCards('reason', this.requestData.reason);
    this.setInputAcrossCards(
      'project-program',
      this.requestData.project_program
    );

    this.setNumericAcrossCards('quantity', this.requestData.quantity, {
      format: false,
      dispatchEvents: true
    });
    this.setNumericAcrossCards('price', this.requestData.unit_price, {
      format: false,
      dispatchEvents: true
    });
    this.setNumericAcrossCards('total', this.requestData.total_amount, {
      format: true,
      rawAttribute: 'data-raw-value',
      dispatchEvents: true
    });
    this.setNumericAcrossCards('area-rai', this.requestData.area_rai, {
      format: false,
      dispatchEvents: true
    });

    const assetTypeId = String(this.requestData.asset_type_id || '');
    if (assetTypeId) {
      if (this.requestData.expected_disbursement_date) {
        this.setDropdownValue(
          `expected-disbursement-date-${assetTypeId}`,
          this.requestData.expected_disbursement_date
        );
      }

      if (this.requestData.multi_year_disbursement) {
        this.setDropdownValue(
          `disbursement-plan-select-${assetTypeId}`,
          this.requestData.multi_year_disbursement
        );
      }

      if (['5', '6'].includes(assetTypeId)) {
        if (this.requestData.project_start_period) {
          this.setDropdownValue(
            `project-start-period-select-${assetTypeId}`,
            this.requestData.project_start_period
          );
        }
        if (this.requestData.project_end_period) {
          this.setDropdownValue(
            `project-end-period-select-${assetTypeId}`,
            this.requestData.project_end_period
          );
        }
        if (this.requestData.operation_year) {
          this.setDropdownValue(
            `implementation-year-select-${assetTypeId}`,
            this.requestData.operation_year
          );
        }
      }

      if (
        ['3', '8', '9'].includes(assetTypeId) &&
        this.requestData.work_period
      ) {
        this.setDropdownValue(
          `installment-work-select-${assetTypeId}`,
          this.requestData.work_period
        );
      }
    }

    // เปิด card ตามประเภทข้อมูลที่เลือก
    console.log('🎯 เรียกใช้ showRelevantCard...');
    this.showRelevantCard();

    // แสดงสถานะ
    this.showStatus();

    // แสดงไฟล์แนบเดิม
    this.renderAttachments(
      this.requestData.attachments || this.requestData.attachment_files || []
    );
  }

  /**
   * กำหนดค่า dropdown
   */
  setDropdownValue(elementId, value) {
    if (!value) {
      console.warn(`⚠️ ไม่มี value สำหรับ ${elementId}`);
      return;
    }

    console.log(`🔧 Setting dropdown value: ${elementId} = ${value}`);

    // ใช้ master data manager ในการ set ค่า (เหมือนกับปีงบประมาณ)
    if (
      window.masterDataManager &&
      typeof window.masterDataManager.setValue === 'function'
    ) {
      this.withValidationSuspended(() => {
        window.masterDataManager.setValue(elementId, String(value));
      });
      console.log(
        `✅ Set value via masterDataManager: ${elementId} = ${value}`
      );
    } else {
      // Fallback: set ค่าโดยตรง
      const element = document.getElementById(elementId);
      if (element) {
        element.value = value;

        // ถ้ามี Choices.js instance
        if (element.choicesInstance) {
          try {
            element.choicesInstance.setChoiceByValue(String(value));
          } catch (error) {
            console.error(
              `❌ Error setting Choices.js value for ${elementId}:`,
              error
            );
          }
        }

        // Trigger change event
        this.withValidationSuspended(() => {
          element.dispatchEvent(
            new Event('change', {
              bubbles: true
            })
          );
        });
        console.log(`✅ Set value directly: ${elementId} = ${value}`);
      }
    }
  }

  /**
   * กำหนดค่า input ที่ผูกกับ assetType ทุกการ์ด
   */
  setInputAcrossCards(prefix, value) {
    if (value === undefined || value === null || value === '') {
      return;
    }

    for (let i = 1; i <= 8; i++) {
      const element = document.getElementById(`${prefix}-${i}`);
      if (!element) continue;

      element.value = value;
      this.withValidationSuspended(() => {
        element.dispatchEvent(
          new Event('input', {
            bubbles: true
          })
        );
        element.dispatchEvent(
          new Event('change', {
            bubbles: true
          })
        );
      });
    }
  }

  /**
   * จัดรูปแบบตัวเลขตามจำนวนทศนิยมที่ต้องการ
   */
  formatNumberValue(value, fractionDigits = 2) {
    const num = Number(value);
    if (!Number.isFinite(num)) {
      return '';
    }

    if (
      fractionDigits === 2 &&
      window.investmentBudgetForm &&
      typeof window.investmentBudgetForm.formatNumber === 'function'
    ) {
      return window.investmentBudgetForm.formatNumber(num);
    }

    return num.toLocaleString('th-TH', {
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits
    });
  }

  /**
   * แปลงค่าให้เป็นตัวเลข (รองรับ string ที่มี comma)
   */
  parseNumericValue(value) {
    if (value === undefined || value === null || value === '') {
      return NaN;
    }

    if (typeof value === 'number') {
      return Number.isFinite(value) ? value : NaN;
    }

    if (typeof value === 'string') {
      const cleaned = value.replace(/,/g, '').trim();
      if (cleaned === '') {
        return NaN;
      }
      const parsed = Number(cleaned);
      return Number.isFinite(parsed) ? parsed : NaN;
    }

    const casted = Number(value);
    return Number.isFinite(casted) ? casted : NaN;
  }

  /**
   * กำหนดค่า numeric inputs ที่มี prefix ตาม assetType
   */
  setNumericAcrossCards(prefix, value, options = {}) {
    if (value === undefined || value === null || value === '') {
      return;
    }

    const {
      format = false,
      fractionDigits = 2,
      rawAttribute,
      dispatchEvents = false
    } = options;

    const numericValue = this.parseNumericValue(value);
    if (format && !Number.isFinite(numericValue)) {
      return;
    }

    const formattedValue = format
      ? this.formatNumberValue(numericValue, fractionDigits)
      : String(value);

    for (let i = 1; i <= 8; i++) {
      const element = document.getElementById(`${prefix}-${i}`);
      if (!element) continue;

      element.value = formattedValue;

      if (rawAttribute && Number.isFinite(numericValue)) {
        element.setAttribute(rawAttribute, numericValue);
      }

      if (dispatchEvents) {
        this.withValidationSuspended(() => {
          element.dispatchEvent(new Event('input', { bubbles: true }));
          element.dispatchEvent(new Event('change', { bubbles: true }));
        });
      }
    }
  }

  /**
   * เปิด card ตามประเภทข้อมูลที่เลือก
   */
  showRelevantCard() {
    if (!this.requestData || !this.requestData.asset_type_name) {
      console.error('❌ ไม่มีข้อมูล requestData หรือ asset_type_name');
      return;
    }

    console.log(
      '🎴 เปิด card สำหรับ asset type ID:',
      this.requestData.asset_type_id
    );

    // ใช้ delay เพื่อให้ DOM พร้อม
    setTimeout(() => {
      console.log('⚡ เริ่มต้น showCardByAssetTypeId หลัง delay...');
      this.showCardByAssetTypeId(this.requestData.asset_type_id);

      // รอให้ card แสดงเรียบร้อยแล้วค่อยโหลดข้อมูล
      setTimeout(() => {
        console.log('📥 เริ่มโหลดข้อมูล item...');
        this.populateItems();
      }, 500);
    }, 1500);
  }

  /**
   * เปิด card ตาม asset-type-id (ใช้ data-asset-type-id แทนชื่อ)
   */
  showCardByAssetTypeId(assetTypeId) {
    console.log('🎴 เปิด card สำหรับ asset-type-id:', assetTypeId);

    // ซ่อนการ์ดทั้งหมดก่อน (ยกเว้นข้อมูลทั่วไป)
    const allAssetCards = document.querySelectorAll(
      '.card.custom-card[data-asset-type-id]'
    );
    allAssetCards.forEach((card) => {
      card.style.display = 'none';
    });

    // หาและแสดงการ์ดที่ตรงกับ asset-type-id
    const targetCard = document.querySelector(
      `.card.custom-card[data-asset-type-id="${assetTypeId}"]`
    );

    if (targetCard) {
      targetCard.style.display = 'block';
      console.log('✅ แสดง card สำหรับ asset-type-id:', assetTypeId);

      // scroll ไปยัง card ที่เปิด
      setTimeout(() => {
        targetCard.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }, 500);
    } else {
      console.warn('⚠️ ไม่พบ card ที่ตรงกับ asset-type-id:', assetTypeId);
    }
  }

  /**
   * แสดงข้อมูล items (ตอนนี้รวมอยู่ใน requestData แล้ว)
   */
  populateItems() {
    if (!this.requestData || !this.requestData.item_name) return;

    // ข้อมูล item อยู่ใน requestData โดยตรง
    const item = this.requestData;
    const assetTypeId = this.requestData.asset_type_id;
    console.log('📥 Populating items for asset type:', assetTypeId, item);

    if (item && item.item_name) {
      // ใช้ ID ที่ถูกต้องตาม asset type
      this.setInputValue(`item-name-${assetTypeId}`, item.item_name);
      this.setInputValue(`description-${assetTypeId}`, item.description);
      this.setNumericValue(`quantity-${assetTypeId}`, item.quantity, {
        format: false
      });
      this.setNumericValue(`price-${assetTypeId}`, item.unit_price, {
        format: false
      });
      //alert(`total_amount: ${item.total_amount}`);
      this.setNumericValue(`total-${assetTypeId}`, item.total_amount, {
        format: true,
        rawAttribute: 'data-raw-value'
      });

      this.setInputValue(`total-${assetTypeId}`, item.total_amount);

      // กำหนดค่า budget source สำหรับ asset type นี้
      if (item.budget_source_id) {
        this.setDropdownValue(
          `budget-source-select-${assetTypeId}`,
          item.budget_source_id
        );
      }

      // กำหนดค่าวันที่คาดว่าจะเบิกจ่าย
      if (item.expected_disbursement_date) {
        this.setDropdownValue(
          `expected-disbursement-date-${assetTypeId}`,
          item.expected_disbursement_date
        );
      }

      // กำหนดค่ามาตรฐานครุภัณฑ์
      if (item.has_standard !== null && item.has_standard !== undefined) {
        this.setDropdownValue(
          `equipment-standards-select-${assetTypeId}`,
          item.has_standard.toString()
        );
      }

      // กำหนดค่าลักษณะครุภัณฑ์
      if (item.asset_nature) {
        this.setDropdownValue(
          `equipment-setup-select-${assetTypeId}`,
          item.asset_nature
        );
      }

      // กำหนดค่าหมวดสินทรัพย์
      if (item.asset_category) {
        this.setDropdownValue(
          `asset-category-select-${assetTypeId}`,
          item.asset_category
        );
      }

      // กำหนดค่าเหตุผลความจำเป็น
      if (item.reason) {
        this.setInputValue(`reason-${assetTypeId}`, item.reason);
      }

      // กำหนดค่ายุทธศาสตร์
      if (item.strategy) {
        this.setDropdownValue(`strategy-select-${assetTypeId}`, item.strategy);
      }

      // กำหนดค่าแผนงาน/โครงการ
      if (item.project_program) {
        this.setInputValue(
          `project-program-${assetTypeId}`,
          item.project_program
        );
      }

      // กำหนดค่าแผนเบิกจ่าย
      if (
        item.multi_year_disbursement !== null &&
        item.multi_year_disbursement !== undefined
      ) {
        this.setDropdownValue(
          `disbursement-plan-select-${assetTypeId}`,
          item.multi_year_disbursement.toString()
        );
      }

      // โหลด serial numbers (หมายเลขครุภัณฑ์ทดแทน)
      if (item.replacement_asset_numbers) {
        // หา tbody ของการ์ดที่แสดงอยู่
        const visibleCard = Array.from(
          document.querySelectorAll('.card.custom-card')
        ).find(
          (card) =>
            card.style.display !== 'none' &&
            card.querySelector('[id^="machinery-serial-tbody-"]')
        );

        if (visibleCard) {
          const tbody = visibleCard.querySelector(
            '[id^="machinery-serial-tbody-"]'
          );
          if (tbody) {
            this.populateSerialNumbers(
              tbody.id,
              item.replacement_asset_numbers
            );
          }
        }
      }
    }
  }

  /**
   * กำหนดค่า input field
   */
  setInputValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value !== null && value !== undefined) {
      element.value = value;

      this.withValidationSuspended(() => {
        // Trigger input event สำหรับ validation
        element.dispatchEvent(
          new Event('input', {
            bubbles: true
          })
        );

        // Trigger change event เพื่อให้ logic อื่นๆ ทำงาน (เช่น การแสดงหมายเลขครุภัณฑ์)
        element.dispatchEvent(
          new Event('change', {
            bubbles: true
          })
        );
      });
    }
  }

  /**
   * กำหนดค่า numeric field (พร้อม format และ data attribute)
   */
  setNumericValue(elementId, value, options = {}) {
    if (value === undefined || value === null || value === '') {
      return;
    }

    const {
      format = false,
      fractionDigits = 2,
      rawAttribute,
      dispatchEvents = true
    } = options;

    const element = document.getElementById(elementId);
    if (!element) {
      return;
    }

    const numericValue = this.parseNumericValue(value);
    if (format) {
      if (!Number.isFinite(numericValue)) {
        return;
      }
      element.value = this.formatNumberValue(numericValue, fractionDigits);
    } else {
      element.value = String(value);
    }

    if (rawAttribute && Number.isFinite(numericValue)) {
      element.setAttribute(rawAttribute, numericValue);
    }

    if (dispatchEvents) {
      this.withValidationSuspended(() => {
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
      });
    }
  }

  /**
   * โหลด serial numbers ที่มีอยู่
   * @param {string} tbodyId - ID ของ tbody element
   * @param {Array|string} serialNumbers - Array ของ serial numbers หรือ string (จะแปลงเป็น array)
   */
  populateSerialNumbers(tbodyId, serialNumbers) {
    console.log('🔢 โหลด Serial Numbers:', tbodyId, serialNumbers);

    const tbody = document.getElementById(tbodyId);
    if (!tbody) {
      console.warn('⚠️ ไม่พบ tbody สำหรับ serial numbers:', tbodyId);
      return;
    }

    // ล้าง rows ที่มีอยู่
    tbody.innerHTML = '';

    // แปลง serialNumbers ให้เป็น array ถ้าไม่ใช่
    let serialNumbersArray = [];
    if (serialNumbers) {
      if (Array.isArray(serialNumbers)) {
        serialNumbersArray = serialNumbers;
      } else if (typeof serialNumbers === 'string') {
        // ถ้าเป็น string พยายาม parse เป็น JSON
        try {
          serialNumbersArray = JSON.parse(serialNumbers);
        } catch (e) {
          // ถ้า parse ไม่ได้ ให้ถือว่าเป็น single value
          serialNumbersArray = [serialNumbers];
        }
      } else {
        console.warn(
          '⚠️ serialNumbers ไม่ใช่ array หรือ string:',
          typeof serialNumbers
        );
      }
    }

    console.log('📋 Serial Numbers Array:', serialNumbersArray);

    // ถ้าไม่มี serial numbers หรือเป็น array ว่าง ให้สร้าง row เปล่า
    if (serialNumbersArray.length === 0) {
      this.addEmptySerialRow(tbody);
      return;
    }

    // เพิ่ม rows สำหรับแต่ละ serial number
    serialNumbersArray.forEach((serialNumber, index) => {
      this.addSerialRow(
        tbody,
        serialNumber,
        index === serialNumbersArray.length - 1
      );
    });
  }

  /**
   * เพิ่ม row ว่างสำหรับ serial number
   */
  addEmptySerialRow(tbody) {
    const row = this.createSerialRow('');
    tbody.appendChild(row);
  }

  /**
   * เพิ่ม row สำหรับ serial number ที่มีค่า
   */
  addSerialRow(tbody, serialNumber, isLast) {
    const row = this.createSerialRow(serialNumber);
    tbody.appendChild(row);

    // เพิ่ม row ว่างต่อท้ายถ้าเป็น item สุดท้าย
    if (isLast) {
      this.addEmptySerialRow(tbody);
    }
  }

  /**
   * สร้าง row สำหรับ serial number
   */
  createSerialRow(value) {
    const row = document.createElement('tr');
    row.className = 'equipment-row';

    row.innerHTML = `
            <td>
                <input type="text" class="form-control equipment-input" 
                       value="${value || ''}"
                       placeholder="กรุณากรอกหมายเลขครุภัณฑ์" 
                       style="border: none; box-shadow: none; font-size: 0.875rem;">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger-light remove-row-btn" title="ลบรายการ">
                    <i class="bi bi-dash"></i> ลบรายการ
                </button>
            </td>
        `;

    return row;
  }

  /**
   * แสดงสถานะของคำขอ
   */
  showStatus() {
    if (!this.requestData) return;

    const statusBadge = this.getStatusBadge(this.requestData.status);

    // เพิ่มการแสดงสถานะที่หัวฟอร์ม
    const pageTitle = document.querySelector('.page-title');
    if (pageTitle) {
      const statusHtml = `<div class="d-flex align-items-center gap-2">
                <span>แก้ไขคำขอตั้งงบลงทุน</span>
                ${statusBadge}
                ${
                  this.requestData.request_number &&
                  this.requestData.request_number !== 'null'
                    ? `<small class="text-muted">(${this.requestData.request_number})</small>`
                    : ''
                }
            </div>`;
      pageTitle.innerHTML = statusHtml;
    }
  }

  /**
   * สร้าง status badge
   */
  getStatusBadge(status) {
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
        text: 'อนุมัติ'
      },
      rejected: {
        class: 'bg-danger',
        text: 'ปฏิเสธ'
      }
    };

    const config = statusConfig[status] || {
      class: 'bg-secondary',
      text: status
    };
    return `<span class="badge ${config.class}">${config.text}</span>`;
  }

  /**
   * ตั้งค่า event listeners
   */
  setupEventListeners() {
    // ปิดการใช้งานฟอร์มถ้าไม่ใช่ draft
    if (this.requestData && this.requestData.status !== 'draft') {
      this.disableForm();
    }
  }

  /**
   * ปิดการใช้งานฟอร์มสำหรับสถานะที่ไม่สามารถแก้ไขได้
   */
  disableForm() {
    console.log(
      '🔒 ปิดการใช้งานฟอร์ม (สถานะ: ' + this.requestData.status + ')'
    );

    // ปิดการใช้งาน input fields
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      input.disabled = true;
      input.style.backgroundColor = '#f8f9fa';
    });

    // ปิดการใช้งานปุ่ม
    const buttons = document.querySelectorAll(
      '#save-draft-btn, #submit-approval-btn'
    );
    buttons.forEach((button) => {
      button.style.display = 'none';
    });

    // แสดงข้อความแจ้งเตือน
    const alertHtml = `
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="ri-information-line me-2"></i>
                <div>คำขอนี้อยู่ในสถานะ "${this.getStatusText(
                  this.requestData.status
                )}" จึงไม่สามารถแก้ไขได้</div>
            </div>
        `;

    const firstCard = document.querySelector('.card.custom-card');
    if (firstCard) {
      firstCard.insertAdjacentHTML('beforebegin', alertHtml);
    }
  }

  /**
   * แปลงสถานะเป็นข้อความไทย
   */
  getStatusText(status) {
    const statusTexts = {
      draft: 'บันทึกร่าง',
      submitted: 'รออนุมัติ',
      approved: 'อนุมัติ',
      rejected: 'ปฏิเสธ'
    };
    return statusTexts[status] || status;
  }

  /**
   * แสดงไฟล์แนบที่มีอยู่
   */
  renderAttachments(attachments) {
    const containers = document.querySelectorAll('[data-attachment-list]');
    if (!containers.length) {
      return;
    }

    const hasAttachments = Array.isArray(attachments) && attachments.length > 0;

    let content = '';
    if (hasAttachments) {
      const listItems = attachments
        .map((attachment) => {
          let path = '';
          let displayName = 'เอกสารแนบ';
          let sizeText = '-';
          let uploadedAt = '';

          if (typeof attachment === 'string') {
            path = attachment;
            displayName = this.getFileNameFromPath(attachment) || displayName;
          } else if (attachment && typeof attachment === 'object') {
            path = attachment.file_path || '';
            displayName =
              attachment.file_name ||
              attachment.stored_name ||
              this.getFileNameFromPath(path) ||
              displayName;
            sizeText = this.formatFileSize(attachment.file_size);
            uploadedAt = attachment.created_at
              ? new Date(attachment.created_at).toLocaleString('th-TH')
              : '';
          }

          const url = this.getAttachmentUrl(path);

          return `
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <a href="${url}" target="_blank" rel="noopener" class="fw-semibold">
                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>${displayName}
                            </a>
                            ${
                              uploadedAt
                                ? `<div class="small text-muted">อัปโหลดเมื่อ: ${uploadedAt}</div>`
                                : ''
                            }
                        </div>
                        <span class="badge bg-secondary align-self-center">${sizeText}</span>
                    </li>
                `;
        })
        .join('');

      content = `<ul class="list-group">${listItems}</ul>`;
    } else {
      content = '<div class="text-muted small">ไม่มีเอกสารแนบ</div>';
    }

    containers.forEach((container) => {
      container.innerHTML = content;
      if (hasAttachments) {
        container.classList.remove('d-none');
      } else {
        container.classList.add('d-none');
      }
    });
  }

  /**
   * จัดรูปแบบขนาดไฟล์เป็นข้อความอ่านง่าย
   */
  formatFileSize(bytes) {
    if (!bytes || Number.isNaN(Number(bytes))) {
      return '-';
    }

    const size = Number(bytes);
    if (size >= 1024 * 1024) {
      return (size / (1024 * 1024)).toFixed(2) + ' MB';
    }
    if (size >= 1024) {
      return (size / 1024).toFixed(2) + ' KB';
    }
    return size + ' B';
  }

  /**
   * สร้าง URL สำหรับไฟล์แนบ
   */
  getAttachmentUrl(path) {
    if (!path) {
      return '#';
    }

    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    const normalized = path.startsWith('/') ? path : `/${path}`;
    return `${window.location.origin}${normalized}`;
  }

  getFileNameFromPath(path) {
    if (!path) {
      return '';
    }

    try {
      const parts = path.split('/');
      return parts[parts.length - 1] || '';
    } catch (error) {
      return '';
    }
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

  getUserContextHeaders() {
    const encoded = this.buildEncodedUserContext();
    if (!encoded) {
      return {};
    }

    const headers = { 'X-User-Context': encoded.value };
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

    const payload = { user_context: encoded.value };
    if (encoded.encoding) {
      payload.user_context_encoding = encoded.encoding;
    }
    return payload;
  }
}

// เริ่มต้น edit manager เมื่อ DOM พร้อม
document.addEventListener('DOMContentLoaded', function () {
  // รอให้ form handler โหลดเสร็จก่อน (ต้องมากกว่า 1200ms ของ form.js)
  setTimeout(() => {
    const editManager = new InvestmentBudgetEditManager();
    editManager.initialize();

    // Override API endpoint สำหรับ edit mode
    if (window.investmentBudgetForm) {
      console.log('✅ Found investmentBudgetForm, setting up edit mode...');

      // ตั้งค่า validation และ price calculation สำหรับ edit mode
      // (form.js จะ initialize อัตโนมัติแล้วที่ 1200ms)
      console.log('✅ Validation และ Price Calculator ทำงานจาก form.js');

      const originalSendToAPI = window.investmentBudgetForm.sendToAPI.bind(
        window.investmentBudgetForm
      );

      window.investmentBudgetForm.sendToAPI = async function (formData) {
        const payload = new FormData();
        payload.append('payload', JSON.stringify(formData));

        const attachments = this.collectAttachmentFiles();
        attachments.forEach((file) => {
          payload.append('pdf_attachments[]', file, file.name);
        });

        const userContext = editManager.getUserContextPayload();
        if (userContext) {
          payload.append('user_context', userContext.user_context);
          if (userContext.user_context_encoding) {
            payload.append(
              'user_context_encoding',
              userContext.user_context_encoding
            );
          }
        }

        const headers = editManager.getUserContextHeaders();

        const response = await fetch(
          `../controllers/investment_budget/ib_requests_controller.php?action=update_form&id=${editManager.requestId}`,
          {
            method: 'POST',
            headers,
            body: payload
          }
        );

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
      };
    } else {
      console.error('❌ investmentBudgetForm ไม่พร้อมใช้งาน');
    }
  }, 1300); // รอให้ form.js init เสร็จก่อน (form.js ใช้ 1200ms)
});
