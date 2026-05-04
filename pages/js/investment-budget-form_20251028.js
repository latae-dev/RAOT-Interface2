/**
 * ระบบจัดการฟอร์มงบลงทุน (Investment Budget Form Handler)
 *
 * หน้าที่:
 * - จัดการ business logic ของฟอร์ม
 * - เชื่อมต่อประเภทข้อมูล กับ รหัสประเภทข้อมูล
 * - แสดง/ซ่อน การ์ดตามประเภทข้อมูลที่เลือก
 * - ระบบ validation แบบ real-time
 * - จัดการข้อมูลฟอร์มและการส่งค่า
 *
 
 */

class InvestmentBudgetForm {
  constructor() {
    this.masterData = null;
    this.isInitialized = false;
    this.hasAttemptedSubmit = false; // Track if user has tried to submit
    this.loadingTimeout = null;
    this.maxLoadingTime = 30000; // 30 วินาที
    this.priceCalculationConfigs = [];
    this.requestId = window.REQUEST_ID ? Number(window.REQUEST_ID) : null;
    this.isEditMode = Boolean(this.requestId);
    this.requestData = null;
    this.validationSuspendCount = 0;
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

  /**
   * เริ่มต้นระบบฟอร์ม
   */
  async initialize() {
    console.log('📋 เริ่มต้นระบบจัดการฟอร์ม...');

    // แสดง loading overlay
    this.showLoading();

    // ตั้งค่า timeout สำหรับกรณีที่โหลดไม่สำเร็จ
    this.loadingTimeout = setTimeout(() => {
      if (!this.isInitialized) {
        this.showLoadingError('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      }
    }, this.maxLoadingTime);

    // รอให้ master data พร้อม
    this.waitForMasterData();
  }

  /**
   * รอให้ master data manager พร้อมใช้งาน
   */
  waitForMasterData() {
    if (window.masterDataManager && window.masterDataManager.isInitialized) {
      this.masterData = window.masterDataManager;
      this.setupFormLogic();
      this.isInitialized = true;

      // ยกเลิก timeout
      if (this.loadingTimeout) {
        clearTimeout(this.loadingTimeout);
        this.loadingTimeout = null;
      }

      // ซ่อน loading และแสดงฟอร์ม (ยกเว้น edit mode จะให้ manager เป็นคนซ่อน)
      if (!this.isEditMode) {
        this.hideLoading();
      } else {
        console.log('⏳ Form logic พร้อมแล้ว รอข้อมูลสำหรับแก้ไขเพิ่มเติม...');
      }

      console.log('✅ ระบบฟอร์มพร้อมใช้งาน');
    } else {
      // ลองใหม่ทุก 100ms จนกว่าจะพร้อม
      setTimeout(() => this.waitForMasterData(), 100);
    }
  }

  // ========================================
  // LOADING MANAGEMENT
  // ========================================

  /**
   * แสดง loading overlay
   */
  showLoading() {
    const loadingBlock = document.getElementById(
      'investment-budget-form-loading'
    );
    const contentBlock = document.getElementById(
      'investment-budget-form-content'
    );

    if (loadingBlock) {
      loadingBlock.classList.remove('d-none');

      const spinner = loadingBlock.querySelector('.spinner-border');
      if (spinner) {
        spinner.style.display = 'inline-block';
      }

      const message = loadingBlock.querySelector('p');
      if (message) {
        message.textContent = 'กำลังโหลดข้อมูล...';
        message.classList.remove('text-danger');
      }
    }

    if (contentBlock) {
      contentBlock.classList.add('d-none');
    }

    console.log('⏳ แสดง loading ของฟอร์ม');
  }

  /**
   * ซ่อน loading overlay และแสดงฟอร์ม
   */
  hideLoading() {
    const loadingBlock = document.getElementById(
      'investment-budget-form-loading'
    );
    const contentBlock = document.getElementById(
      'investment-budget-form-content'
    );

    // เพิ่ม delay เล็กน้อยเพื่อให้ดู smooth
    setTimeout(() => {
      if (loadingBlock) {
        loadingBlock.classList.add('d-none');
      }

      if (contentBlock) {
        contentBlock.classList.remove('d-none');
      }

      console.log('✅ ซ่อน loading และแสดงฟอร์ม');
    }, 300); // delay 300ms เพื่อให้ดูเป็นธรรมชาติ
  }

  /**
   * แสดง loading พร้อมข้อความ error
   */
  showLoadingError(errorMessage = 'เกิดข้อผิดพลาดในการโหลดข้อมูล') {
    const loadingBlock = document.getElementById(
      'investment-budget-form-loading'
    );
    const spinner = loadingBlock?.querySelector('.spinner-border');
    const message = loadingBlock?.querySelector('p');

    if (loadingBlock) {
      loadingBlock.classList.remove('d-none');
    }

    if (spinner) {
      spinner.style.display = 'none';
    }

    if (message) {
      message.textContent = errorMessage;
      message.classList.add('text-danger');
    }

    const contentBlock = document.getElementById(
      'investment-budget-form-content'
    );
    if (contentBlock) {
      contentBlock.classList.add('d-none');
    }

    if (window.Swal && typeof window.Swal.fire === 'function') {
      window.Swal.fire({
        icon: 'error',
        title: errorMessage,
        confirmButtonText: 'ตกลง'
      });
    } else {
      alert(errorMessage);
    }

    console.error('❌ แสดง loading error:', errorMessage);
  }

  // ========================================
  // FORM LOGIC SETUP
  // ========================================

  /**
   * ตั้งค่า business logic ของฟอร์ม
   */
  setupFormLogic() {
    console.log('🎯 ตั้งค่า business logic ของฟอร์ม...');

    // ซ่อนการ์ดประเภทข้อมูลทั้งหมดเมื่อเริ่มต้น
    this.hideAllAssetTypeCards();

    // เชื่อมต่อประเภทข้อมูล -> รหัสประเภทข้อมูล
    this.setupAssetTypeCodeConnection();

    // แสดง/ซ่อน การ์ดตามประเภทข้อมูลที่เลือก
    this.setupAssetTypeCardVisibility();

    // ตั้งค่า cascading dropdowns สำหรับหมวดสินทรัพย์
    this.setupCascadingAssetCategoryDropdowns();

    // เตรียม Choices.js สำหรับวันที่คาดว่าจะเบิกจ่าย
    this.initializeExpectedDisbursementChoices();

    // ปรับรายการวันที่คาดว่าจะเบิกจ่ายตามปีงบประมาณ
    this.setupFiscalYearWatcher();

    // ตั้งค่า validation
    this.setupFormValidation();

    // ตั้งค่า confirmation dialogs
    this.setupConfirmationDialogs();

    // เริ่มต้นระบบจัดการหมายเลขครุภัณฑ์
    this.initializeEquipmentSerialManager();

    // ควบคุมการแสดง/ซ่อนหมายเลขครุภัณฑ์ทดแทน
    this.setupReplacementAssetSections();

    // เริ่มต้นระบบคำนวณราคา
    this.initializePriceCalculator();

    // ตั้งค่า save event listeners
    this.setupSaveEventListeners();

    console.log('✅ ตั้งค่า business logic เสร็จสิ้น');
  }

  /**
   * เชื่อมต่อประเภทข้อมูล กับ รหัสประเภทข้อมูล
   */
  setupAssetTypeCodeConnection() {
    const assetTypeSelect = document.getElementById('asset-type-select');
    const codeField = document.getElementById('asset-type-code');

    if (!assetTypeSelect || !codeField) {
      console.warn('⚠️ ไม่พบ element ประเภทข้อมูล หรือ รหัสประเภทข้อมูล');
      return;
    }

    assetTypeSelect.addEventListener('change', (event) => {
      const selectedValue = event.target.value;

      if (selectedValue) {
        // หาข้อมูลประเภทข้อมูลที่เลือก
        const selectedItem = this.masterData.data.assetTypes.find(
          (item) => item.id == selectedValue
        );

        if (selectedItem) {
          codeField.value = selectedItem.code || '';
          console.log(`🔄 อัพเดตรหัสประเภทข้อมูล: ${selectedItem.code}`);
        } else {
          console.warn('⚠️ ไม่พบข้อมูลประเภทข้อมูลที่เลือก');
          codeField.value = '';
        }
      } else {
        // ล้างรหัสเมื่อไม่ได้เลือก
        codeField.value = '';
        console.log('🔄 ล้างรหัสประเภทข้อมูล');
      }
    });

    console.log('✅ เชื่อมต่อประเภทข้อมูล-รหัสเสร็จสิ้น');
  }

  /**
   * แสดง/ซ่อน การ์ดตามประเภทข้อมูลที่เลือก
   * ใช้ data-asset-type-id แทนการเปรียบเทียบชื่อ
   */
  setupAssetTypeCardVisibility() {
    const assetTypeSelect = document.getElementById('asset-type-select');

    if (!assetTypeSelect) {
      console.warn('⚠️ ไม่พบ dropdown ประเภทข้อมูล');
      return;
    }

    assetTypeSelect.addEventListener('change', (event) => {
      const selectedValue = event.target.value;
      console.log(`🔄 เปลี่ยนประเภทข้อมูล ID: ${selectedValue}`);

      // Reset validation flag เมื่อเปลี่ยน Asset Type
      this.hasAttemptedSubmit = false;

      if (selectedValue) {
        // แสดงการ์ดตาม ID ก่อน
        this.showCardByAssetTypeId(selectedValue);
        // จากนั้นค่อย Clear ค่าฟิลด์ของการ์ดที่แสดง
        this.clearItemFields();
      } else {
        // ซ่อนการ์ดทั้งหมดเมื่อไม่ได้เลือก
        this.hideAllAssetTypeCards();
        this.clearItemFields();
      }
    });

    console.log('✅ ตั้งค่าการแสดง/ซ่อนการ์ดเสร็จสิ้น');
  }

  /**
   * แสดงการ์ดตาม asset-type-id
   * @param {string} assetTypeId - ID ของประเภทข้อมูล
   */
  showCardByAssetTypeId(assetTypeId) {
    const normalizedId = String(assetTypeId ?? '').trim();
    if (!normalizedId) {
      console.warn('⚠️ assetTypeId ว่าง ไม่สามารถแสดงการ์ดได้');
      this.hideAllAssetTypeCards();
      return;
    }

    const assetCards = Array.from(
      document.querySelectorAll('.card.custom-card[data-asset-type-id]')
    );

    // ซ่อนการ์ดทั้งหมดก่อน (ยกเว้นข้อมูลทั่วไป)
    this.hideAllAssetTypeCards(assetCards);

    // หาและแสดงการ์ดที่ตรงกับ asset-type-id (เปรียบเทียบแบบ trim)
    const targetCard = assetCards.find((card) => {
      const cardId = (card.dataset.assetTypeId ?? '').trim();
      return cardId === normalizedId;
    });

    if (targetCard) {
      targetCard.style.display = 'block';
      targetCard.dataset.activeCard = 'true';

      // Debug: ตรวจสอบว่า card แสดงจริง
      const computedStyle = window.getComputedStyle(targetCard);
      const cardTitle =
        targetCard.querySelector('.card-title')?.textContent || 'ไม่มีชื่อ';
      const cardBody = targetCard.querySelector('.card-body');

      console.log(`👁️ แสดงการ์ด asset-type-id: ${assetTypeId} (${cardTitle})`);
      console.log(`  - inline display: ${targetCard.style.display}`);
      console.log(`  - computed display: ${computedStyle.display}`);
      console.log(`  - visibility: ${computedStyle.visibility}`);
      console.log(`  - overflow: ${computedStyle.overflow}`);
      console.log(`  - height CSS: ${computedStyle.height}`);
      console.log(`  - max-height: ${computedStyle.maxHeight}`);

      // เช็คว่า card อยู่ใน DOM และมองเห็นได้
      const rect = targetCard.getBoundingClientRect();
      console.log(`  - position: top=${rect.top}, height=${rect.height}`);
      console.log(
        `  - isVisible: ${rect.height > 0 && computedStyle.display !== 'none'}`
      );

      // เช็ค card-body
      if (cardBody) {
        const bodyRect = cardBody.getBoundingClientRect();
        const bodyStyle = window.getComputedStyle(cardBody);
        console.log(`  - card-body height: ${bodyRect.height}`);
        console.log(`  - card-body display: ${bodyStyle.display}`);
        console.log(
          `  - card-body innerHTML length: ${cardBody.innerHTML.length}`
        );
      }

      // บังคับ scroll ไปที่ card
      setTimeout(() => {
        targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    } else {
      console.warn(`⚠️ ไม่พบการ์ดสำหรับ asset-type-id: ${assetTypeId}`);
    }
  }

  /**
   * ตั้งค่า cascading dropdowns สำหรับหมวดสินทรัพย์
   * Asset Type ที่ต้องเลือกงวดงานก่อน: 3 (ที่ดิน), 9 (ส่วนปรับปรุงที่ดิน)
   * Asset Type อื่นๆ: เลือกหมวดสินทรัพย์ได้เลยโดยใช้ installment_work_id = 0
   */
  setupCascadingAssetCategoryDropdowns() {
    console.log('🔗 ตั้งค่า cascading dropdowns สำหรับหมวดสินทรัพย์...');

    // Asset Types ทั้งหมดที่ต้องมี cascading
    const allAssetTypes = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    // Asset Types ที่ต้องเลือกงวดงานก่อน (มี installment work dropdown)
    const assetTypesWithInstallmentWork = [3, 8, 9]; // ที่ดิน, อาคารและสิ่งปลูกสร้าง, ส่วนปรับปรุงที่ดิน

    allAssetTypes.forEach((assetTypeId) => {
      const categorySelect = document.getElementById(
        `asset-category-select-${assetTypeId}`
      );

      if (!categorySelect) {
        console.warn(`⚠️ ไม่พบ asset-category-select-${assetTypeId}, ข้าม...`);
        return;
      }

      const requiresInstallmentWork =
        assetTypesWithInstallmentWork.includes(assetTypeId);

      if (requiresInstallmentWork) {
        // สำหรับ Asset Types ที่ต้องเลือกงวดงานก่อน
        const installmentSelect = document.getElementById(
          `installment-work-select-${assetTypeId}`
        );

        if (!installmentSelect) {
          console.warn(
            `⚠️ ไม่พบ installment-work-select-${assetTypeId}, ข้าม...`
          );
          return;
        }

        // Disable หมวดสินทรัพย์จนกว่าจะเลือกงวดงาน
        categorySelect.disabled = true;
        if (categorySelect.choicesInstance) {
          categorySelect.choicesInstance.disable();
        }

        // Listen เมื่อเลือกงวดงาน
        installmentSelect.addEventListener('change', async (event) => {
          const selectedInstallmentWork = event.target.value;
          const currentAssetType =
            document.getElementById('asset-type-select').value;

          console.log(
            `🔄 งวดงานเปลี่ยนแปลง (Asset Type ${assetTypeId}): ${selectedInstallmentWork}`
          );

          if (selectedInstallmentWork && currentAssetType) {
            // Enable และอัปเดตหมวดสินทรัพย์
            categorySelect.disabled = false;
            if (categorySelect.choicesInstance) {
              categorySelect.choicesInstance.enable();
            }

            await this.masterData.updateAssetCategoryDropdown(
              categorySelect.id,
              currentAssetType,
              selectedInstallmentWork
            );

            console.log(
              `✅ อัปเดตหมวดสินทรัพย์สำหรับ Asset Type ${assetTypeId} เสร็จสิ้น`
            );
          } else {
            // Disable และ clear หมวดสินทรัพย์
            categorySelect.disabled = true;
            if (categorySelect.choicesInstance) {
              categorySelect.choicesInstance.disable();
              categorySelect.choicesInstance.setChoiceByValue('');
            } else {
              categorySelect.value = '';
            }
          }
        });

        console.log(
          `🔗 ตั้งค่า cascading สำหรับ Asset Type ${assetTypeId} (ต้องเลือกงวดงาน)`
        );
      } else {
        // สำหรับ Asset Types ที่ไม่ต้องเลือกงวดงาน
        // โหลดหมวดสินทรัพย์ทันทีเมื่อแสดง card (ใช้ installment_work_id = 0)
        const assetTypeSelect = document.getElementById('asset-type-select');

        if (!assetTypeSelect) return;

        // Listen เมื่อเลือก Asset Type
        const originalListener = assetTypeSelect.cloneNode(true);
        assetTypeSelect.addEventListener('change', async (event) => {
          const selectedAssetType = event.target.value;

          // ตรวจสอบว่า card ที่แสดงตรงกับ assetTypeId นี้หรือไม่
          if (String(selectedAssetType) === String(assetTypeId)) {
            console.log(
              `🔄 โหลดหมวดสินทรัพย์สำหรับ Asset Type ${assetTypeId} (ไม่ต้องเลือกงวดงาน)`
            );

            // โหลดหมวดสินทรัพย์ด้วย installment_work_id = 0
            await this.masterData.updateAssetCategoryDropdown(
              categorySelect.id,
              selectedAssetType,
              0 // installment_work_id = 0 หมายถึงไม่สนใจงวดงาน
            );

            console.log(
              `✅ โหลดหมวดสินทรัพย์สำหรับ Asset Type ${assetTypeId} เสร็จสิ้น`
            );
          }
        });

        console.log(
          `🔗 ตั้งค่า cascading สำหรับ Asset Type ${assetTypeId} (ไม่ต้องเลือกงวดงาน)`
        );
      }
    });

    console.log('✅ ตั้งค่า cascading dropdowns เสร็จสมบูรณ์');
  }

  /**
   * Clear ค่าฟิลด์ของ item ทั้งหมด
   */
  clearItemFields() {
    console.log('🧹 Clear ค่าฟิลด์ทั้งหมด...');

    // Clear dropdowns (ใช้ class selector เพราะมีหลาย elements)
    const dropdownClassesToClear = [
      'budget-source-select',
      'equipment-standards-select',
      'equipment-setup-select',
      'asset-category-select',
      'strategy-select',
      'expected-disbursement-date',
      'disbursement-plan-select'
    ];

    dropdownClassesToClear.forEach((className) => {
      const elements = document.querySelectorAll(`select.${className}`);
      elements.forEach((element) => {
        element.value = '';
        // ถ้ามี Choices.js instance ให้ reset
        if (element.choicesInstance) {
          element.choicesInstance.setChoiceByValue('');
        }

        // Re-disable asset category dropdowns ที่ต้องรอการเลือกงวดงาน
        if (className === 'asset-category-select') {
          const assetTypesWithInstallmentWork = [3, 8, 9]; // ต้องเลือกงานงวดก่อน
          assetTypesWithInstallmentWork.forEach((typeId) => {
            if (element.id === `asset-category-select-${typeId}`) {
              element.disabled = true;
              if (element.choicesInstance) {
                element.choicesInstance.disable();
              }
            }
          });
        }
      });
    });

    // Clear text inputs
    const inputsToClear = [
      'item-name',
      'machinery-description',
      'machinery-quantity',
      'machinery-price',
      'machinery-total',
      'reason',
      'project-program'
    ];

    inputsToClear.forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.value = '';
      }
    });

    // Clear serial numbers table
    this.populateSerialNumbers(null, []);
    this.setupEquipmentSerialEventListeners();

    console.log('✅ Clear ค่าฟิลด์เสร็จสิ้น');
  }

  /**
   * ซ่อนการ์ดประเภทข้อมูลทั้งหมด (ยกเว้นข้อมูลทั่วไป)
   * ใช้ data-asset-type-id ในการตรวจสอบ
   */
  hideAllAssetTypeCards(cards) {
    const assetCards =
      cards ??
      document.querySelectorAll('.card.custom-card[data-asset-type-id]');

    assetCards.forEach((card) => {
      card.style.display = 'none';
      delete card.dataset.activeCard;
    });

    console.log('🙈 ซ่อนการ์ดประเภทข้อมูลทั้งหมด');
  }

  // ========================================
  // CONFIRMATION DIALOGS
  // ========================================

  /**
   * ตั้งค่า confirmation dialogs สำหรับปุ่มต่างๆ
   */
  setupConfirmationDialogs() {
    console.log('🔒 ตั้งค่า confirmation dialogs...');

    // หาปุ่มส่งคำขออนุมัติทั้งหมด
    const submitButtons = document.querySelectorAll('#submit-approval-btn');

    submitButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault(); // หยุดการทำงานเดิม
        this.showSubmitConfirmation();
      });
    });

    console.log(
      `✅ ตั้งค่า confirmation สำหรับปุ่มส่งอนุมัติ ${submitButtons.length} ปุ่ม`
    );
  }

  /**
   * แสดง confirmation dialog สำหรับการส่งคำขออนุมัติ
   */
  async showSubmitConfirmation() {
    // ตรวจสอบความพร้อมของฟอร์มก่อน
    const validation = this.validateForApproval();

    if (!validation.isValid) {
      // แสดง validation errors ก่อน
      await Swal.fire({
        icon: 'warning',
        title: 'ข้อมูลไม่ครบถ้วน',
        html: `<ul style="text-align: left; padding-left: 20px;">
          ${validation.errors.map((error) => `<li>${error}</li>`).join('')}
        </ul>`,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#d33'
      });
      return;
    }

    // แสดง confirmation dialog
    const result = await Swal.fire({
      icon: 'question',
      title: 'ยืนยันการส่งคำขออนุมัติ',
      html: `
        <div style="text-align: left;">
          <p><strong>คุณต้องการส่งคำขออนุมัติหรือไม่?</strong></p>
          <p style="color: #e74c3c; font-weight: 500;">
            ⚠️ หลังจากส่งแล้วจะไม่สามารถแก้ไขได้
          </p>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'ส่งคำขออนุมัติ',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      reverseButtons: true,
      focusCancel: true
    });

    if (result.isConfirmed) {
      this.submitApprovalRequest(validation.data);
    }
  }

  /**
   * ส่งคำขออนุมัติ
   */
  async submitApprovalRequest(formData) {
    try {
      // แสดง loading
      Swal.fire({
        title: 'กำลังส่งคำขออนุมัติ...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // TODO: เรียก API ส่งข้อมูล
      console.log('📤 ส่งข้อมูลคำขออนุมัติ:', formData);

      // จำลองการส่งข้อมูล (ในอนาคตจะเป็น API call จริง)
      await new Promise((resolve) => setTimeout(resolve, 2000));

      // แสดงผลสำเร็จ
      await Swal.fire({
        icon: 'success',
        title: 'ส่งคำขออนุมัติสำเร็จ!',
        text: 'ระบบได้รับคำขออนุมัติของคุณแล้ว',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#28a745'
      });

      // Redirect ไปหน้ารายการ
      window.location.href = 'investment-budget-list.php';
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการส่งคำขออนุมัติ:', error);

      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: 'ไม่สามารถส่งคำขออนุมัติได้ กรุณาลองใหม่อีกครั้ง',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#d33'
      });
    }
  }

  // ========================================
  // VALIDATION SYSTEM
  // ========================================

  /**
   * ตั้งค่าระบบ validation
   */
  setupFormValidation() {
    console.log('📝 ตั้งค่าระบบ validation...');

    // เพิ่ม validation class ให้ container
    const container = document.querySelector('.container-fluid');
    if (container && !container.classList.contains('needs-validation')) {
      container.classList.add('needs-validation');
    }

    // ตั้งค่า validation สำหรับ dropdowns
    this.setupDropdownValidation();

    // ตั้งค่า validation สำหรับ numeric fields
    this.setupNumericValidation();

    console.log('✅ ตั้งค่า validation เสร็จสิ้น');
  }

  /**
   * Utility: อ่านสถานะ validation ของ element
   */
  getElementValidationState(element) {
    if (!element || !element.dataset) {
      return null;
    }

    const state = element.dataset.validationState;
    if (state === 'valid' || state === 'invalid') {
      return state;
    }

    return null;
  }

  /**
   * Utility: ตั้งค่าสถานะ validation ของ element โดยไม่ใช้ Bootstrap classes
   */
  setElementValidationState(element, state) {
    if (!element) {
      return;
    }

    // ถอน class bootstrap เดิมเพื่อไม่ให้มี is-valid / is-invalid คงค้าง
    if (element.classList) {
      element.classList.remove('is-valid', 'is-invalid');
    }

    const normalizedState =
      state === 'valid' ? 'valid' : state === 'invalid' ? 'invalid' : null;

    if (normalizedState) {
      element.dataset.validationState = normalizedState;
      if (normalizedState === 'invalid') {
        element.setAttribute('aria-invalid', 'true');
      } else {
        element.removeAttribute('aria-invalid');
      }
    } else {
      delete element.dataset.validationState;
      element.removeAttribute('aria-invalid');
    }
  }

  isValidationSuspended() {
    return (this.validationSuspendCount ?? 0) > 0;
  }

  suspendValidation() {
    if (typeof this.validationSuspendCount !== 'number') {
      this.validationSuspendCount = 0;
    }
    this.validationSuspendCount += 1;
  }

  resumeValidation() {
    if (typeof this.validationSuspendCount !== 'number') {
      this.validationSuspendCount = 0;
      return;
    }

    this.validationSuspendCount = Math.max(0, this.validationSuspendCount - 1);
  }

  runWithValidationSuspended(callback) {
    this.suspendValidation();

    try {
      const result = typeof callback === 'function' ? callback() : undefined;
      if (result && typeof result.then === 'function') {
        return result.finally(() => {
          this.resumeValidation();
        });
      }

      this.resumeValidation();
      return result;
    } catch (error) {
      this.resumeValidation();
      throw error;
    }
  }

  markElementValid(element) {
    if (this.isValidationSuspended()) {
      this.clearElementValidationState(element);
      return;
    }

    this.setElementValidationState(element, 'valid');
  }

  markElementInvalid(element) {
    if (this.isValidationSuspended()) {
      this.clearElementValidationState(element);
      return;
    }

    this.setElementValidationState(element, 'invalid');
  }

  clearElementValidationState(element) {
    this.setElementValidationState(element, null);
  }

  /**
   * ตั้งค่า validation สำหรับ dropdowns
   */
  setupDropdownValidation() {
    const requiredSelects = document.querySelectorAll('select[required]');

    requiredSelects.forEach((select) => {
      select.addEventListener('change', () => {
        console.log(`🔄 Select changed: ${select.id} = "${select.value}"`);

        // Debug: แสดงข้อมูลใน dropdown
        if (select.id.includes('budget-source')) {
          console.log('📊 Budget source dropdown options:');
          Array.from(select.options).forEach((option, index) => {
            console.log(
              `  ${index}: value="${option.value}", text="${option.text}"`
            );
          });
        }

        this.validateSelect(select);

        // ล้าง validation state เมื่อฟิลด์ valid
        if (select.value !== '') {
          this.markElementValid(select);

          // ซ่อน feedback ทันที
          const feedback = this.findFeedbackElement(select, 'invalid-feedback');
          if (feedback) {
            feedback.style.display = 'none';
            feedback.classList.remove('show');
          }
        }
      });
    });
  }

  /**
   * ตั้งค่า validation สำหรับ numeric fields
   */
  setupNumericValidation() {
    const numericFields = document.querySelectorAll('input[type="number"]');

    numericFields.forEach((field) => {
      // ตรวจสอบ integer only - ไม่อนุญาตให้พิมพ์ทศนิยม
      if (field.dataset.integerOnly === 'true') {
        // บล็อกการพิมพ์ที่ไม่ใช่ตัวเลข (ใช้ keypress แทน keydown สำหรับการพิมพ์)
        field.addEventListener('keypress', (e) => {
          // อนุญาตเฉพาะตัวเลข 0-9
          if (e.key < '0' || e.key > '9') {
            e.preventDefault();
          }
        });

        // ป้องกันการ paste ที่ไม่ใช่ตัวเลข
        field.addEventListener('paste', (e) => {
          e.preventDefault();
          const pastedText = (e.clipboardData || window.clipboardData).getData(
            'text'
          );
          const sanitized = pastedText.replace(/[^\d]/g, '');
          if (sanitized) {
            field.value = sanitized;
            field.dispatchEvent(new Event('input', { bubbles: true }));
          }
        });

        field.addEventListener('input', () => {
          const match = field.value.match(/^\d*/);
          const sanitizedValue = match ? match[0] : '';
          if (field.value !== sanitizedValue) {
            field.value = sanitizedValue;
          }
        });
      }
      // ตรวจสอบจำนวนทศนิยม - จำกัดจำนวนตำแหน่งทศนิยม
      else if (field.dataset.maxDecimals) {
        const maxDecimals = parseInt(field.dataset.maxDecimals, 10);

        // บล็อกการพิมพ์ที่ไม่ใช่ตัวเลขและจุดทศนิยม
        field.addEventListener('keypress', (e) => {
          const currentValue = field.value;
          const key = e.key;

          // อนุญาตตัวเลข 0-9
          if (key >= '0' && key <= '9') {
            return;
          }

          // อนุญาตจุดทศนิยมเพียง 1 จุด
          if (key === '.' && !currentValue.includes('.')) {
            return;
          }

          // บล็อกทุกอย่างอื่น
          e.preventDefault();
        });

        // ป้องกันการ paste ที่ไม่ถูกต้อง
        field.addEventListener('paste', (e) => {
          e.preventDefault();
          const pastedText = (e.clipboardData || window.clipboardData).getData(
            'text'
          );
          // อนุญาตเฉพาะตัวเลขและจุดทศนิยม 1 จุด
          const sanitized = pastedText
            .replace(/[^\d.]/g, '')
            .replace(/(\..*)\./g, '$1');
          if (sanitized) {
            // ตรวจสอบและจำกัดทศนิยม
            const parts = sanitized.split('.');
            if (parts.length > 1) {
              field.value = parts[0] + '.' + parts[1].substring(0, maxDecimals);
            } else {
              field.value = sanitized;
            }
            field.dispatchEvent(new Event('input', { bubbles: true }));
          }
        });

        field.addEventListener('input', () => {
          const value = field.value;
          // อนุญาตให้พิมพ์ตัวเลขและจุดทศนิยม แต่จำกัดจำนวนตำแหน่ง
          const pattern = new RegExp(`^\\d*\\.?\\d{0,${maxDecimals}}$`);
          if (value && !pattern.test(value)) {
            // ตัดทศนิยมส่วนเกิน
            const parts = value.split('.');
            if (parts.length > 1) {
              field.value = parts[0] + '.' + parts[1].substring(0, maxDecimals);
            } else {
              // ลบอักขระที่ไม่ใช่ตัวเลข
              field.value = value.replace(/[^\d.]/g, '');
            }
          }
        });
      }

      field.addEventListener('blur', () => {
        this.validateNumericField(field);
      });

      field.addEventListener('input', () => {
        if (field.dataset.integerOnly === 'true') {
          this.validateNumericField(field);
        }
      });
    });
  }

  /**
   * ตรวจสอบ select element
   */
  validateSelect(selectElement) {
    const selectValue = selectElement.value;
    const selectId = selectElement.id;
    const isValid = selectValue !== '' && selectValue !== null;

    console.log(
      `🔎 validateSelect - ID: ${selectId}, Value: "${selectValue}", Valid: ${isValid}`
    );

    if (isValid) {
      this.markElementValid(selectElement);

      // ตรวจสอบและล้าง container validation หากทุกฟิลด์ valid
      this.checkAndClearContainerValidation();
    } else {
      this.markElementInvalid(selectElement);
    }

    // อัพเดตการแสดง feedback
    this.updateFeedbackVisibility(selectElement);

    return isValid;
  }

  /**
   * ตรวจสอบว่าทุกฟิลด์ valid แล้วล้าง container validation
   */
  checkAndClearContainerValidation() {
    const requiredFields = document.querySelectorAll(
      'select[required], input[required]'
    );
    let allValid = true;

    requiredFields.forEach((field) => {
      if (!this.shouldValidateElement(field)) {
        return;
      }

      const state = this.getElementValidationState(field);
      if (
        state === 'invalid' ||
        (!field.value && field.hasAttribute('required'))
      ) {
        allValid = false;
      }
    });

    // หากทุกฟิลด์ valid ให้ลบ was-validated
    if (allValid) {
      const container = document.querySelector('.container-fluid');
      if (container) {
        container.classList.remove('was-validated');
        console.log('✅ ทุกฟิลด์ valid - ล้าง validation state');
      }
    }
  }

  /**
   * ตรวจสอบ numeric field
   */
  validateNumericField(field) {
    const rawValue = (field.value || '').trim();
    const minAttr = field.getAttribute('min');
    const min = !isNaN(parseFloat(minAttr)) ? parseFloat(minAttr) : null;

    let value = parseFloat(rawValue);

    // ตรวจสอบ integer only
    if (field.dataset.integerOnly === 'true') {
      if (rawValue === '') {
        value = NaN;
      } else if (!/^\d+$/.test(rawValue)) {
        value = NaN;
      } else {
        value = parseInt(rawValue, 10);
        field.value = value.toString();
      }
    }
    // ตรวจสอบจำนวนทศนิยม (max decimals)
    else if (field.dataset.maxDecimals) {
      const maxDecimals = parseInt(field.dataset.maxDecimals, 10);

      if (rawValue === '') {
        value = NaN;
      } else {
        // ตรวจสอบว่าเป็นตัวเลขที่ถูกต้อง
        const decimalPattern = new RegExp(`^\\d+(\\.\\d{0,${maxDecimals}})?$`);

        if (!decimalPattern.test(rawValue)) {
          // ถ้าไม่ถูกต้อง ให้ปัดเศษทศนิยม
          value = parseFloat(rawValue);
          if (!isNaN(value)) {
            field.value = value.toFixed(maxDecimals);
          }
        }
      }
    }

    let isValid = !isNaN(value);
    if (isValid && min !== null) {
      isValid = value >= min;
    }

    if (isValid) {
      this.markElementValid(field);
    } else {
      this.markElementInvalid(field);
    }

    // อัพเดตการแสดง feedback
    this.updateFeedbackVisibility(field);

    return isValid;
  }

  /**
   * ตัดสินใจว่า element นี้ควรถูก validation หรือไม่
   */
  shouldValidateElement(element) {
    if (!element) {
      return false;
    }

    if (
      element.hasAttribute('data-skip-validation') ||
      element.closest('[data-skip-validation]')
    ) {
      return false;
    }

    if (element.disabled) {
      return false;
    }

    const typeAttr = element.getAttribute('type');
    if (typeAttr && typeAttr.toLowerCase() === 'hidden') {
      return false;
    }

    if (
      element.hasAttribute('hidden') ||
      element.classList.contains('d-none') ||
      element.getAttribute('aria-hidden') === 'true'
    ) {
      return false;
    }

    const hiddenAncestor = element.closest(
      '[hidden], .d-none, [aria-hidden="true"]'
    );
    if (hiddenAncestor) {
      return false;
    }

    const collapseParent = element.closest('.collapse');
    if (collapseParent && !collapseParent.classList.contains('show')) {
      return false;
    }

    const card = element.closest('.card.custom-card');
    if (card) {
      const cardStyle =
        typeof window !== 'undefined'
          ? window.getComputedStyle(card)
          : { display: 'block', visibility: 'visible' };

      const cardHidden =
        card.classList.contains('d-none') ||
        card.getAttribute('aria-hidden') === 'true' ||
        cardStyle.display === 'none' ||
        cardStyle.visibility === 'hidden';

      if (cardHidden) {
        return false;
      }
    }

    return true;
  }

  /**
   * อัพเดตการแสดง feedback สำหรับฟิลด์
   */
  updateFeedbackVisibility(element) {
    if (!element) {
      console.warn('⚠️ updateFeedbackVisibility: element is null');
      return;
    }

    // ใช้ method ที่ปรับปรุงแล้วในการหา feedback
    let feedback = this.findFeedbackElement(element, 'invalid-feedback');
    let validFeedback = this.findFeedbackElement(element, 'valid-feedback');

    const validationState = this.getElementValidationState(element);
    const isInvalid = validationState === 'invalid';
    const isValid = validationState === 'valid';

    console.log('🔍 อัพเดต feedback สำหรับ:', element.id || element.className, {
      isInvalid,
      isValid,
      hasAttemptedSubmit: this.hasAttemptedSubmit,
      hasFeedback: !!feedback,
      hasValidFeedback: !!validFeedback,
      feedbackText: feedback ? feedback.textContent.trim() : 'ไม่มี'
    });

    // แสดง invalid-feedback เฉพาะเมื่อ:
    // 1. มีการพยายาม submit แล้ว (hasAttemptedSubmit = true)
    // 2. หรือ field นี้มี validation state เป็น invalid และมี value อยู่แล้ว (user กำลังแก้ไข)
    const shouldShowInvalidFeedback =
      this.hasAttemptedSubmit || (isInvalid && element.value !== '');

    if (isInvalid && shouldShowInvalidFeedback) {
      if (feedback) {
        feedback.classList.add('show');
        feedback.style.display = 'block';
        console.log(
          '✅ แสดง invalid feedback สำหรับ:',
          element.id || element.className,
          '→',
          feedback.textContent.trim()
        );
      } else {
        console.warn(
          '⚠️ ไม่พบ invalid-feedback สำหรับ:',
          element.id || element.className
        );
      }
    } else if (feedback) {
      feedback.classList.remove('show');
      feedback.style.display = 'none';
    }

    if (isValid && validFeedback) {
      validFeedback.style.display = 'block';
      console.log('✅ แสดง valid feedback สำหรับ:', element.id);
    } else if (validFeedback) {
      validFeedback.style.display = 'none';
    }
  }

  /**
   * หา feedback element สำหรับฟิลด์ที่กำหนด
   */
  findFeedbackElement(element, feedbackClass) {
    if (!element) return null;

    const elementId = element.id || element.className;

    // วิธีที่ 1: ตรวจสอบ sibling ถัดไป (วิธีที่เร็วที่สุดและใช้บ่อยที่สุด)
    let nextEl = element.nextElementSibling;
    if (nextEl && nextEl.classList.contains(feedbackClass)) {
      console.log(`✓ พบ ${feedbackClass} แบบ sibling ถัดไป สำหรับ:`, elementId);
      return nextEl;
    }

    // วิธีที่ 2: ตรวจสอบ siblings ทั้งหมดหลังจาก element
    nextEl = element.nextElementSibling;
    while (nextEl) {
      if (nextEl.classList.contains(feedbackClass)) {
        console.log(`✓ พบ ${feedbackClass} ใน siblings สำหรับ:`, elementId);
        return nextEl;
      }
      nextEl = nextEl.nextElementSibling;
    }

    // วิธีที่ 3: ค้นหาใน parent container
    const parentContainer = element.closest(
      '.col-xl-6, .col-xl-4, .col-xl-12, .card-body'
    );
    if (parentContainer) {
      const feedback = parentContainer.querySelector('.' + feedbackClass);
      if (feedback) {
        console.log(
          `✓ พบ ${feedbackClass} ใน parent container สำหรับ:`,
          elementId
        );
        return feedback;
      }
    }

    // วิธีที่ 4: ค้นหาแบบ global ตาม proximity
    const allFeedbacks = document.querySelectorAll('.' + feedbackClass);
    for (let feedback of allFeedbacks) {
      // ตรวจสอบว่า feedback อยู่ใน parent container เดียวกันหรือไม่
      const feedbackParent = feedback.closest(
        '.col-xl-6, .col-xl-4, .col-xl-12'
      );
      const elementParent = element.closest('.col-xl-6, .col-xl-4, .col-xl-12');
      if (feedbackParent === elementParent && feedbackParent) {
        console.log(
          `✓ พบ ${feedbackClass} แบบ global proximity สำหรับ:`,
          elementId
        );
        return feedback;
      }
    }

    console.warn(`⚠️ ไม่พบ ${feedbackClass} สำหรับ:`, elementId);
    return null;
  }

  // ========================================
  // FORM DATA MANAGEMENT
  // ========================================

  /**
   * Validate ฟอร์มก่อนส่งอนุมัติ
   * - ตรวจสอบเฉพาะ fields ที่แสดงอยู่จริง (isVisible)
   * - Fields ต่างกันตาม assetType:
   *   * Common (ทุก assetType): budget, itemName, description, reason, total, strategy, projectProgram, expectedDate
   *   * Equipment (1,2,4,7): quantity, price, equipStd, equipNature, assetCategory
   *   * Plantation (5,6): area, projectStart/End, implYear, assetCategory
   *   * Project (3,8): installment, assetCategory
   */
  // validateForApproval() {
  //   console.log('🔍 === START validateForApproval 2 ===');
  //   const formData = this.getFormData();
  //   const errors = [];

  //   // ===== Helpers =====
  //   const getEl = (id) => document.getElementById(id);
  //   const isVisible = (el) => {
  //     if (!el) return false;
  //     return (
  //       el.offsetParent !== null && getComputedStyle(el).display !== 'none'
  //     );
  //   };
  //   const markInvalidEl = (el) => {
  //     if (!el) return;
  //     console.log(`🔴 markInvalidEl: ${el.id || el.className}`);
  //     el.classList.add('is-invalid');
  //     el.classList.remove('is-valid');
  //     if (this.updateFeedbackVisibility) {
  //       console.log(`  → calling updateFeedbackVisibility for: ${el.id}`);
  //       this.updateFeedbackVisibility(el);
  //     } else {
  //       console.warn('⚠️ updateFeedbackVisibility not available');
  //     }
  //   };
  //   const markValidEl = (el) => {
  //     if (!el) return;
  //     el.classList.remove('is-invalid');
  //     el.classList.add('is-valid');
  //     this.updateFeedbackVisibility?.(el);
  //   };
  //   const addErr = (msg, elOrId) => {
  //     errors.push(msg);
  //     if (!elOrId) return;
  //     if (typeof elOrId === 'string') markInvalidEl(getEl(elOrId));
  //     else markInvalidEl(elOrId);
  //   };
  //   const isEmpty = (v) =>
  //     v === undefined || v === null || String(v).trim() === '';
  //   const parseNum = (v) => {
  //     const n = Number(String(v).replace(/,/g, '').trim());
  //     return Number.isFinite(n) ? n : NaN;
  //   };
  //   const isIntPos = (n) => Number.isInteger(n) && n > 0;
  //   const hasTooManyDecimals = (v) => /\.\d{3,}$/.test(String(v || ''));
  //   const isThaiYearMonth = (v) =>
  //     /^\d{4}-(0[1-9]|1[0-2])$/.test(String(v || '').trim());

  //   // active card & type
  //   const type = String(formData.assetType || '');
  //   const activeCard = type
  //     ? document.querySelector(
  //         `.card.custom-card[data-asset-type-id="${type}"]`
  //       )
  //     : null;

  //   console.log(
  //     `📝 Validating assetType: ${type}, activeCard: ${
  //       activeCard ? 'found' : 'not found'
  //     }`
  //   );

  //   // ===== กำหนดประเภท assetType =====
  //   const isEquipment = ['1', '2', '4', '7'].includes(type); // ครุภัณฑ์
  //   const isPlantation = ['5', '6'].includes(type); // สวนยาง, สวนปาล์ม
  //   const isProject = ['3', '8'].includes(type); // ที่ดิน, อาคาร
  //   const isMachinery = type === '1'; // เครื่องจักร (มี serial numbers)

  //   console.log('🏷️ ประเภท:', {
  //     isEquipment,
  //     isPlantation,
  //     isProject,
  //     isMachinery
  //   });

  //   // ========================================
  //   // COMMON VALIDATION (ทุก assetType)
  //   // ========================================

  //   // 1. ปีงบประมาณ
  //   if (isEmpty(formData.fiscalYear))
  //     addErr('กรุณาเลือกปีงบประมาณ', 'fiscal-year-select');
  //   else markValidEl(getEl('fiscal-year-select'));

  //   // 2. เวอร์ชั่น (แผน)
  //   if (isEmpty(formData.versionPlan))
  //     addErr('กรุณาเลือกเวอร์ชั่น (แผน)', 'version-plan-select');
  //   else markValidEl(getEl('version-plan-select'));

  //   // 3. ประเภทข้อมูล
  //   if (isEmpty(formData.assetType))
  //     addErr('กรุณาเลือกประเภทข้อมูล', 'asset-type-select');
  //   else markValidEl(getEl('asset-type-select'));

  //   // 4. รหัสประเภทข้อมูล
  //   if (isEmpty(formData.assetTypeCode)) addErr('รหัสประเภทข้อมูลไม่ถูกต้อง');
  //   else markValidEl(getEl('asset-type-code'));

  //   // ===== อ้างอิง id ตาม assetType (กรณีพิเศษ type=1) =====
  //   const ids = {
  //     budget: `budget-source-select-${type}`,
  //     itemName: `item-name-${type}`,
  //     description: `description-${type}`,
  //     reason: `reason-${type}`,
  //     qty: `quantity-${type}`,
  //     price: `price-${type}`,
  //     total: `total-${type}`,
  //     strategy: `strategy-select-${type}`,
  //     projectProgram: `project-program-${type}`,
  //     expectedDate: `expected-disbursement-date-${type}`,
  //     multiYear: `disbursement-plan-select-${type}`,
  //     equipStd: `equipment-standards-select-${type}`,
  //     equipNature: `equipment-setup-select-${type}`,
  //     assetCategory: `asset-category-select-${type}`,
  //     area: `area-rai-${type}`,
  //     start: `project-start-period-select-${type}`,
  //     end: `project-end-period-select-${type}`,
  //     implYear: `implementation-year-select-${type}`,
  //     installment: `installment-work-select-${type}`
  //   };

  //   // ========================================
  //   // COMMON FIELDS (ทุก assetType)
  //   // ========================================

  //   // 5. แหล่งงบประมาณ
  //   {
  //     const el = getEl(ids.budget);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.budgetSource))
  //         addErr('กรุณาเลือกแหล่งงบประมาณ', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 6. ชื่อรายการ
  //   {
  //     const el = getEl(ids.itemName);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.itemName)) addErr('กรุณากรอกรายการ', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 7. รายละเอียด
  //   {
  //     const el = getEl(ids.description);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.Description)) addErr('กรุณากรอกรายละเอียด', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 8. เหตุผลความจำเป็น
  //   {
  //     const el = getEl(ids.reason);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.Reason)) addErr('กรุณากรอกเหตุผลความจำเป็น', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // ========================================
  //   // EQUIPMENT FIELDS (assetType: 1,2,4,7)
  //   // ========================================
  //   if (isEquipment) {
  //     // 9. จำนวน (หน่วย)
  //     {
  //       const el = getEl(ids.qty);
  //       if (el && isVisible(el)) {
  //         const qty = parseNum(formData.Quantity);
  //         if (!isIntPos(qty))
  //           addErr('กรุณากรอกจำนวน (หน่วย) เป็นจำนวนเต็มมากกว่า 0', el);
  //         else markValidEl(el);
  //       }
  //     }

  //     // 10. ราคา (ต่อหน่วย)
  //     {
  //       const el = getEl(ids.price);
  //       if (el && isVisible(el)) {
  //         const price = parseNum(formData.Price);
  //         if (
  //           !(Number.isFinite(price) && price > 0) ||
  //           hasTooManyDecimals(formData.Price)
  //         ) {
  //           addErr(
  //             'กรุณากรอกราคา (ต่อหน่วย) ให้ถูกต้อง (มากกว่า 0 และทศนิยมไม่เกิน 2 ตำแหน่ง)',
  //             el
  //           );
  //         } else {
  //           markValidEl(el);
  //         }
  //       }
  //     }

  //     // 12. มาตรฐานครุภัณฑ์
  //     {
  //       const el = getEl(ids.equipStd);
  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.equipmentStandards))
  //           addErr('กรุณาเลือกมาตรฐานครุภัณฑ์', el);
  //         else markValidEl(el);
  //       }
  //     }

  //     // 13. ลักษณะครุภัณฑ์
  //     {
  //       const el = getEl(ids.equipNature);
  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.equipmentNature))
  //           addErr('กรุณาเลือกลักษณะครุภัณฑ์', el);
  //         else markValidEl(el);

  //         // ถ้า "ลักษณะ = ทดแทน (id=2)" → ต้องมี serialNumbers
  //         if (String(formData.equipmentNature) === '2') {
  //           const list = Array.isArray(formData.serialNumbers)
  //             ? formData.serialNumbers
  //             : [];
  //           if (!list.length)
  //             errors.push('กรุณากรอกหมายเลขครุภัณฑ์ทดแทนอย่างน้อย 1 รายการ');
  //           else if (list.some((sn) => isEmpty(sn)))
  //             errors.push('กรุณากรอกหมายเลขครุภัณฑ์ทดแทนให้ครบถ้วนทุกรายการ');
  //         }
  //       }
  //     }
  //   }

  //   // 11. ยอดรวม - ทุก assetType (total-1 ถึง total-8)
  //   {
  //     const el = getEl(ids.total);
  //     if (el && isVisible(el)) {
  //       const total = parseNum(formData.totalAmount);
  //       if (
  //         !(Number.isFinite(total) && total > 0) ||
  //         hasTooManyDecimals(formData.totalAmount)
  //       ) {
  //         addErr('กรุณากรอกราคารวม', el);
  //       } else {
  //         markValidEl(el);
  //       }
  //     }
  //   }

  //   // 14. หมวดสินทรัพย์ - ทุก assetType (1,2,3,4,5,6,7,8)
  //   {
  //     const el = getEl(ids.assetCategory);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.assetCategory))
  //         addErr('กรุณาเลือกหมวดสินทรัพย์', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 15. ยุทธศาสตร์ - ทุก assetType
  //   {
  //     const el = getEl(ids.strategy);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.strategy)) addErr('กรุณาเลือกยุทธศาสตร์', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 16. แผนงาน / โครงการ - ทุก assetType
  //   {
  //     const el = getEl(ids.projectProgram);
  //     if (el && isVisible(el)) {
  //       if (isEmpty(formData.projectProgram))
  //         addErr('กรุณากรอกแผนงาน / โครงการ', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 17. วันที่คาดว่าจะเบิกจ่าย - ทุก assetType
  //   {
  //     const el = getEl(ids.expectedDate);
  //     if (el && isVisible(el)) {
  //       if (!isThaiYearMonth(formData.expectedDisbursementDate))
  //         addErr('กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // 18. แผนเบิกจ่ายมากกว่า 1 ปี - ทุก assetType ยกเว้น assetType 8
  //   {
  //     const el = getEl(ids.multiYear);
  //     if (el && isVisible(el)) {
  //       const val = String(formData.multiYearDisbursement || '');
  //       if (!['1', '2'].includes(val))
  //         addErr('กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี', el);
  //       else markValidEl(el);
  //     }
  //   }

  //   // ========================================
  //   // PLANTATION FIELDS (assetType: 5,6 - สวนยาง, สวนปาล์ม)
  //   // ========================================
  //   if (isPlantation) {
  //     // 19. พื้นที่ (ไร่)
  //     {
  //       const el = getEl(ids.area);
  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.areaRai)) {
  //           addErr('กรุณากรอกพื้นที่ (ไร่)', el);
  //         } else {
  //           const a = parseNum(formData.areaRai);
  //           if (!Number.isFinite(a) || a <= 0)
  //             addErr('พื้นที่ (ไร่) ต้องเป็นตัวเลขมากกว่า 0', el);
  //           else markValidEl(el);
  //         }
  //       }
  //     }

  //     // 20. ระยะเวลาเริ่มต้นโครงการ
  //     {
  //       const el = getEl(ids.start);
  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.projectStartPeriod))
  //           addErr('กรุณาเลือกระยะเวลาเริ่มต้นโครงการฯ', el);
  //         else markValidEl(el);
  //       }
  //     }

  //     // 21. ระยะเวลาสิ้นสุดโครงการ
  //     {
  //       const el = getEl(ids.end);
  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.projectEndPeriod))
  //           addErr('กรุณาเลือกระยะเวลาสิ้นสุดโครงการฯ', el);
  //         else markValidEl(el);
  //       }
  //     }

  //     // 22. ปีที่ดำเนินการ
  //     {
  //       const el = getEl(ids.implYear);
  //       if (el && isVisible(el)) {
  //         if (
  //           isEmpty(formData.operationYear) ||
  //           !/^\d{4}$/.test(String(formData.operationYear))
  //         ) {
  //           addErr('กรุณาเลือกปีที่ดำเนินการให้ถูกต้อง (YYYY)', el);
  //         } else {
  //           markValidEl(el);
  //         }
  //       }
  //     }
  //   }

  //   // ========================================
  //   // PROJECT FIELDS (assetType: 3,8 - ที่ดิน, อาคารและสิ่งปลูกสร้าง)
  //   // ========================================
  //   if (isProject) {
  //     // 23. งานงวด
  //     {
  //       const el = getEl(ids.installment);
  //       console.log('🔍 ตรวจสอบงานงวด:', {
  //         elementId: ids.installment,
  //         found: !!el,
  //         visible: el ? isVisible(el) : false,
  //         value: formData.workPeriod
  //       });

  //       if (el && isVisible(el)) {
  //         if (isEmpty(formData.workPeriod)) addErr('กรุณาเลือกงานงวด', el);
  //         else markValidEl(el);
  //       }
  //     }
  //   }

  //   // ========================================
  //   // PDF ATTACHMENT (ทุก assetType)
  //   // ========================================

  //   // 24. แนบเอกสาร PDF
  //   if (activeCard) {
  //     const pdfInput = activeCard.querySelector(
  //       'input[type="file"][data-attachment-input="true"]'
  //     );
  //     if (pdfInput && isVisible(pdfInput)) {
  //       if (!pdfInput.files || pdfInput.files.length === 0)
  //         addErr('กรุณาแนบเอกสาร PDF อย่างน้อย 1 ไฟล์', pdfInput);
  //       else markValidEl(pdfInput);
  //     }
  //   }

  //   // ===== ผลลัพธ์ =====
  //   if (errors.length > 0) {
  //     this.forceShowInvalidFeedback?.();
  //     console.log('❌ Validation errors:', errors);
  //     console.log('🔍 === END validateForApproval (FAILED) ===');
  //   } else {
  //     console.log('✅ Validation passed (For Approval)');
  //     console.log('🔍 === END validateForApproval (SUCCESS) ===');
  //   }

  //   return {
  //     isValid: errors.length === 0,
  //     errors,
  //     data: formData
  //   };
  // }

  /**
   * Validate แบบขั้นต่ำสำหรับบันทึกร่าง (เช็คเฉพาะ 3 fields หลัก)
   */
  validateMinimal() {
    console.log('🔍 === START validateMinimal ===');
    this.recalculateActiveTotal();
    console.log('📋 Calling this.getFormData()...');
    const formData = this.getFormData();
    console.log('📋 getFormData() returned:', formData);
    const errors = [];

    console.log('🔍 Validating formData:', formData);
    console.log('  - fiscalYear:', formData.fiscalYear);
    console.log('  - versionPlan:', formData.versionPlan);
    console.log('  - assetType:', formData.assetType);

    // ตรวจสอบเฉพาะฟิลด์หลัก 3 fields
    if (!formData.fiscalYear) {
      console.warn('❌ ไม่มีปีงบประมาณ');
      errors.push('กรุณาเลือกปีงบประมาณ');
    }
    if (!formData.versionPlan) {
      console.warn('❌ ไม่มีเวอร์ชั่น (แผน)');
      errors.push('กรุณาเลือกเวอร์ชั่น (แผน)');
    }
    if (!formData.assetType) {
      console.warn('❌ ไม่มีประเภทข้อมูล');
      errors.push('กรุณาเลือกประเภทข้อมูล');
    }

    console.log('📋 Validation errors:', errors);

    return {
      isValid: errors.length === 0,
      errors: errors,
      data: formData
    };
  }

  /**
   * Validate แบบเต็มสำหรับส่งอนุมัติ (เช็คทุก required fields)
   */
  /**
   * Validate สำหรับบันทึกร่าง - ต้องการเฉพาะข้อมูลพื้นฐาน
   * - ปีงบประมาณ
   * - เวอร์ชั่น (แผน)
   * - ประเภทข้อมูล
   */
  validateDraft() {
    const formData = this.getFormData();
    const errors = [];

    console.log('🔍 เริ่มต้น validateDraft, formData:', formData);

    // 1. ปีงบประมาณ
    if (!formData.fiscalYear) {
      errors.push('กรุณาเลือกปีงบประมาณ');
      const fiscalYearSelect = document.getElementById('fiscal-year-select');
      if (fiscalYearSelect) {
        this.markElementInvalid(fiscalYearSelect);
        this.updateFeedbackVisibility(fiscalYearSelect);
      }
    }

    // 2. เวอร์ชั่น (แผน)
    if (!formData.versionPlan) {
      errors.push('กรุณาเลือกเวอร์ชั่น (แผน)');
      const versionPlanSelect = document.getElementById('version-plan-select');
      if (versionPlanSelect) {
        this.markElementInvalid(versionPlanSelect);
        this.updateFeedbackVisibility(versionPlanSelect);
      }
    }

    // 3. ประเภทข้อมูล
    if (!formData.assetType) {
      errors.push('กรุณาเลือกประเภทข้อมูล');
      const assetTypeSelect = document.getElementById('asset-type-select');
      if (assetTypeSelect) {
        this.markElementInvalid(assetTypeSelect);
        this.updateFeedbackVisibility(assetTypeSelect);
      }
    }

    console.log(
      errors.length === 0
        ? '✅ Validation passed (Draft)'
        : '❌ Validation errors (Draft):',
      errors
    );

    return {
      isValid: errors.length === 0,
      errors: errors,
      data: formData
    };
  }

  /**
   * Validate สำหรับส่งอนุมัติ - ต้องการข้อมูลครบถ้วน
   * - ทุกอย่างใน validateDraft +
   * - ทุก fields ใน card ที่เปิดอยู่
   * - Serial Numbers (สำหรับ machinery)
   * - PDF Attachment
   */
  /**
   * Validate สำหรับส่งอนุมัติ - ต้องการข้อมูลครบถ้วน
   * - ข้อมูลพื้นฐาน (ปีงบประมาณ, เวอร์ชั่น, ประเภทข้อมูล)
   * - ทุก fields ใน card ที่เปิดอยู่
   * - Serial Numbers (สำหรับ machinery)
   * - PDF Attachment
   */
  validateForApproval() {
    // ทำเครื่องหมายว่าได้มีการพยายาม submit แล้ว
    this.hasAttemptedSubmit = true;

    const formData = this.getFormData();
    const errors = [];

    const processedElements = new Set();
    const registerElement = (el) => {
      if (!el) return;
      processedElements.add(el);
    };

    // ===== Helpers =====
    const getEl = (id) => document.getElementById(id);
    const getChoicesContainer = (el) => {
      if (!el) return null;
      const isChoicesContainer = (node) =>
        !!node && node.classList && node.classList.contains('choices');

      const next = el.nextElementSibling;
      if (isChoicesContainer(next)) {
        return next;
      }

      if (typeof el.closest === 'function') {
        const closestChoices = el.closest('.choices');
        if (isChoicesContainer(closestChoices)) {
          return closestChoices;
        }
      }

      return null;
    };
    const applyChoicesState = (el, isValid) => {
      if (!el) return;
      const container = getChoicesContainer(el);
      if (!container) return null;

      this.setElementValidationState(container, isValid ? 'valid' : 'invalid');
      return container;
    };
    const shouldValidateField = (el) => {
      if (!el) return false;
      if (this.shouldValidateElement(el)) return true;
      const choicesContainer = getChoicesContainer(el);
      if (choicesContainer) {
        return this.shouldValidateElement(choicesContainer);
      }
      return false;
    };
    const markInvalidEl = (el) => {
      if (!el) return;
      registerElement(el);
      this.markElementInvalid(el);
      const choicesContainer = applyChoicesState(el, false);
      this.updateFeedbackVisibility?.(el);
      if (choicesContainer && choicesContainer !== el) {
        this.updateFeedbackVisibility?.(choicesContainer);
      }
    };
    const markValidEl = (el) => {
      if (!el) return;
      registerElement(el);
      this.markElementValid(el);
      const choicesContainer = applyChoicesState(el, true);
      this.updateFeedbackVisibility?.(el);
      if (choicesContainer && choicesContainer !== el) {
        this.updateFeedbackVisibility?.(choicesContainer);
      }
    };
    const addErr = (msg, elOrId) => {
      errors.push(msg);
      if (!elOrId) return;
      if (typeof elOrId === 'string') markInvalidEl(getEl(elOrId));
      else markInvalidEl(elOrId);
    };
    const isEmpty = (v) =>
      v === undefined || v === null || String(v).trim() === '';
    const isPosNum = (v) => Number.isFinite(Number(v)) && Number(v) > 0;
    const isInt = (v) => Number.isInteger(Number(v));
    const isDate = (v) => {
      if (isEmpty(v)) return false;
      const d = new Date(v);
      return !isNaN(d.getTime());
    };
    const parseNum = (v) => {
      const n = Number(String(v).replace(/,/g, '').trim());
      return Number.isFinite(n) ? n : NaN;
    };
    const toComparablePeriod = (value) => {
      if (isEmpty(value)) return NaN;
      const [yearStr, monthStr] = String(value).split('-');
      const year = parseInt(yearStr, 10);
      const month = parseInt(monthStr, 10);
      if (Number.isNaN(year) || Number.isNaN(month)) {
        return NaN;
      }
      const normalizedYear = year > 2400 ? year - 543 : year;
      return normalizedYear * 100 + month;
    };
    const isVisible = (el) => {
      if (!el) return false;
      const style = window.getComputedStyle(el);
      return el.offsetParent !== null && style.display !== 'none';
    };

    const activeCard = formData.assetType
      ? document.querySelector(
          `.card.custom-card[data-asset-type-id="${formData.assetType}"]`
        )
      : null;

    const resolveEl = (prefix, { tag = '', exactId } = {}) => {
      if (activeCard) {
        const inCard = activeCard.querySelector(
          tag ? `${tag}[id^="${prefix}-"]` : `[id^="${prefix}-"]`
        );
        if (inCard) return inCard;
      }
      if (exactId) {
        const el = getEl(exactId);
        if (el) return el;
      }
      if (formData.assetType) {
        const byType = getEl(`${prefix}-${formData.assetType}`);
        if (byType) return byType;
      }
      return document.querySelector(
        tag ? `${tag}[id^="${prefix}-"]` : `[id^="${prefix}-"]`
      );
    };

    // ===== กำหนดประเภท assetType =====
    const isEquipment = ['1', '2', '4', '7', '10'].includes(formData.assetType); // ครุภัณฑ์ + สินทรัพย์มูลค่าต่ำ
    const isPlantation = ['5', '6'].includes(formData.assetType); // สวนยาง, สวนปาล์ม
    const isProject = ['3', '8', '9'].includes(formData.assetType); // โครงการ + ส่วนปรับปรุงที่ดิน
    const requiresProjectTimeline = false; // Validation สำหรับ Asset Type 3 (ที่ดิน) ถูกปิดใช้งานตามคำขอ
    const requiresInstallment = ['3', '8', '9'].includes(formData.assetType); // ใช้งวดงาน

    console.log('🏷️ ประเภท:', {
      isEquipment,
      isPlantation,
      requiresProjectTimeline,
      requiresInstallment
    });

    // ========================================
    // COMMON VALIDATION (ทุก assetType)
    // ========================================

    // ===== 1. ปีงบประมาณ =====
    if (isEmpty(formData.fiscalYear))
      addErr('กรุณาเลือกปีงบประมาณ', 'fiscal-year-select');
    else markValidEl(getEl('fiscal-year-select'));

    // ===== 2. เวอร์ชั่น (แผน) =====
    if (isEmpty(formData.versionPlan))
      addErr('กรุณาเลือกเวอร์ชั่น (แผน)', 'version-plan-select');
    else markValidEl(getEl('version-plan-select'));

    // ===== 3. ประเภทข้อมูล =====
    if (isEmpty(formData.assetType))
      addErr('กรุณาเลือกประเภทข้อมูล', 'asset-type-select');
    else markValidEl(getEl('asset-type-select'));

    // ===== 4. รหัสประเภทข้อมูล =====
    {
      const el = getEl('asset-type-code');
      if (isEmpty(formData.assetTypeCode))
        addErr('รหัสประเภทข้อมูลไม่ถูกต้อง', el);
      else markValidEl(el);
    }

    // ========================================
    // COMMON FIELDS (ทุก assetType)
    // ========================================

    // ===== 5. แหล่งงบประมาณ =====
    {
      const el = resolveEl('budget-source-select', { tag: 'select' });
      if (isEmpty(formData.budgetSource)) addErr('กรุณาเลือกแหล่งงบประมาณ', el);
      else markValidEl(el);
    }

    // ===== 6. ชื่อรายการ =====
    {
      const el = resolveEl('item-name', { tag: 'input' });
      if (isEmpty(formData.itemName)) addErr('กรุณากรอกชื่อรายการ', el);
      else markValidEl(el);
    }

    // ===== 7. รายละเอียด =====
    {
      const el = resolveEl('description', { tag: 'textarea' });
      if (isEmpty(formData.Description)) addErr('กรุณากรอกรายละเอียด', el);
      else markValidEl(el);
    }

    // ===== 8. เหตุผลความจำเป็น =====
    {
      const el = resolveEl('reason', { tag: 'textarea' });
      if (isEmpty(formData.Reason)) addErr('กรุณากรอกเหตุผล/ความจำเป็น', el);
      else markValidEl(el);
    }

    // ========================================
    // EQUIPMENT FIELDS (assetType: 1,2,4,7)
    // ========================================
    if (isEquipment) {
      // ===== 9. จำนวน (หน่วย) =====
      {
        const el = resolveEl('quantity', { tag: 'input' });
        const num = parseNum(formData.Quantity);
        if (!isInt(num) || num <= 0)
          addErr('กรุณากรอกจำนวน (หน่วย) เป็นจำนวนเต็มมากกว่า 0', el);
        else markValidEl(el);
      }

      // ===== 10. ราคา (ต่อหน่วย) =====
      {
        const el = resolveEl('price', { tag: 'input' });
        const num = parseNum(formData.Price);
        const hasTooManyDecimals = /\.\d{3,}$/.test(String(formData.Price));
        if (!isPosNum(num) || hasTooManyDecimals)
          addErr(
            'กรุณากรอกราคา (ต่อหน่วย) ให้ถูกต้อง (มากกว่า 0 และทศนิยมไม่เกิน 2 ตำแหน่ง)',
            el
          );
        else markValidEl(el);
      }

      {
        const stdEl = resolveEl('equipment-standards-select', {
          tag: 'select'
        });
        const natEl = resolveEl('equipment-setup-select', { tag: 'select' });
        if (isEmpty(formData.equipmentStandards))
          addErr('กรุณาเลือกมาตรฐานครุภัณฑ์', stdEl);
        else markValidEl(stdEl);
        if (isEmpty(formData.equipmentNature))
          addErr('กรุณาเลือกลักษณะครุภัณฑ์', natEl);
        else markValidEl(natEl);
      }

      {
        if (String(formData.equipmentNature) === '2') {
          const list = Array.isArray(formData.serialNumbers)
            ? formData.serialNumbers
            : [];
          if (!list.length)
            addErr('กรุณากรอกหมายเลขครุภัณฑ์ทดแทนอย่างน้อย 1 รายการ');
          else if (list.some((sn) => isEmpty(sn)))
            addErr('กรุณากรอกหมายเลขครุภัณฑ์ทดแทนให้ครบถ้วนทุกรายการ');
        }
      }
    }

    // ===== 11. ยอดรวม =====
    {
      const el = resolveEl('total', {
        tag: 'input'
      });
      const num = parseNum(formData.totalAmount);
      const hasTooManyDecimals = /\.\d{3,}$/.test(String(formData.totalAmount));
      if (!isPosNum(num) || hasTooManyDecimals) addErr('กรุณากรอกราคารวม', el);
      else {
        // ตรวจสอบเงื่อนไขพิเศษสำหรับ Asset Type 10 (สินทรัพย์มูลค่าต่ำ)
        if (formData.assetType === '10' && num > 30000) {
          addErr('สินทรัพย์มูลค่าต่ำต้องมีมูลค่าไม่เกิน 30,000 บาท', el);
        } else {
          markValidEl(el);
        }
      }
      // const qty = parseNum(formData.Quantity);
      // const price = parseNum(formData.Price);
      // const total = parseNum(formData.totalAmount);
      // const expected = qty * price;
      // if (!Number.isFinite(total) || Math.abs(total - expected) > 0.01)
      //   addErr(
      //     `ยอดรวมไม่สอดคล้องกับ จำนวน × ราคา (${qty} × ${price} = ${expected.toFixed(
      //       2
      //     )})`,
      //     el
      //   );
      // else markValidEl(el);
    }

    // ===== 14. หมวดสินทรัพย์ =====
    {
      const el = resolveEl('asset-category-select', { tag: 'select' });
      if (isEmpty(formData.assetCategory))
        addErr('กรุณาเลือกหมวดสินทรัพย์', el);
      else markValidEl(el);
    }
    // ===== 15. ยุทธศาสตร์ =====
    {
      const el = resolveEl('strategy-select', { tag: 'select' });
      if (el && shouldValidateField(el)) {
        if (isEmpty(formData.strategy))
          addErr('กรุณาเลือกยุทธศาสตร์ที่เกี่ยวข้อง', el);
        else markValidEl(el);
      }
    }

    // ===== 16. โครงการ/โปรแกรม =====
    {
      const el = resolveEl('project-program', { tag: 'input' });
      if (isEmpty(formData.projectProgram))
        addErr('กรุณากรอกแผนงาน / โครงการ', el);
      else markValidEl(el);
    }

    // ===== 17. วันที่คาดว่าจะเบิกจ่าย =====
    {
      const el = resolveEl('expected-disbursement-date', {
        tag: 'input',
        exactId: 'expected-disbursement-date'
      });
      if (!isDate(formData.expectedDisbursementDate))
        addErr('กรุณาเลือกวันที่คาดว่าจะเบิกจ่าย', el);
      else markValidEl(el);
    }

    // ===== 18. การเบิกจ่ายหลายปี =====
    {
      const el = resolveEl('disbursement-plan-select', {
        tag: 'select'
      });

      if (el && shouldValidateField(el)) {
        if (isEmpty(formData.multiYearDisbursement))
          addErr('กรุณาเลือกแผนเบิกจ่ายมากกว่า 1 ปี', el);
        else markValidEl(el);
      }
    }

    // ========================================
    // PLANTATION FIELDS (assetType: 5,6 - สวนยาง, สวนปาล์ม)
    // ========================================

    if (isPlantation) {
      // ===== 19. พื้นที่ (ไร่) =====
      {
        const el = resolveEl('area-rai', { tag: 'input' });

        const num = parseNum(formData.areaRai);
        if (!isInt(num) || num <= 0) addErr('กรุณากรอกพื้นที่ไร่', el);
        else markValidEl(el);
      }

      // ===== 20. ระยะเวลาเริ่มต้น โครงการฯ =====
      {
        const el = resolveEl('project-start-period-select', { tag: 'select' });
        if (isEmpty(formData.projectStartPeriod)) {
          addErr('กรุณาระบุช่วงเริ่มโครงการให้ถูกต้อง', el);
        } else {
          markValidEl(el);
        }
      }

      // ===== 21. ระยะเวลาสิ้นสุด โครงการฯ =====
      {
        const el = resolveEl('project-end-period-select', { tag: 'select' });
        if (isEmpty(formData.projectEndPeriod)) {
          addErr('กรุณาระบุช่วงสิ้นสุดโครงการให้ถูกต้อง', el);
        } else {
          markValidEl(el);
        }
      }

      if (
        !isEmpty(formData.projectStartPeriod) &&
        !isEmpty(formData.projectEndPeriod)
      ) {
        const startComparable = toComparablePeriod(formData.projectStartPeriod);
        const endComparable = toComparablePeriod(formData.projectEndPeriod);
        if (
          !Number.isNaN(startComparable) &&
          !Number.isNaN(endComparable) &&
          startComparable > endComparable
        ) {
          const startEl = resolveEl('project-start-period-select', {
            tag: 'select'
          });
          const endEl = resolveEl('project-end-period-select', {
            tag: 'select'
          });
          addErr(
            'ระยะเวลาเริ่มต้นโครงการฯ ต้องน้อยกว่าหรือเท่ากับระยะเวลาสิ้นสุดโครงการฯ',
            startEl
          );
          if (endEl) {
            markInvalidEl(endEl);
          }
        }
      }

      // ===== 22. ปีที่ดำเนินการ =====
      {
        const el = resolveEl('implementation-year-select', { tag: 'select' });
        if (el && shouldValidateField(el)) {
          const value = String(formData.operationYear || '').trim();
          const yearNum = parseInt(value, 10);
          const isFourDigits = /^\d{4}$/.test(value);
          const isWithinRange =
            isFourDigits && yearNum >= 1900 && yearNum <= 2600;

          if (!isWithinRange) {
            addErr('กรุณาระบุปีปฏิบัติการให้ถูกต้อง (YYYY)', el);
          } else {
            markValidEl(el);
          }
        }
      }
    }

    // ========================================
    // PROJECT TIMELINE FIELDS (assetType: 3 - ที่ดิน, อาคาร)
    // ========================================
    if (requiresProjectTimeline) {
      // ===== 20. ช่วงเริ่ม / สิ้นสุดโครงการ =====
      {
        const startEl = resolveEl('project-start-period', {
          tag: 'input',
          exactId: 'project-start-period'
        });
        const endEl = resolveEl('project-end-period', {
          tag: 'input',
          exactId: 'project-end-period'
        });
        const hasStart = isDate(formData.projectStartPeriod);
        const hasEnd = isDate(formData.projectEndPeriod);
        if (!hasStart) addErr('กรุณาระบุช่วงเริ่มโครงการให้ถูกต้อง', startEl);
        if (!hasEnd) addErr('กรุณาระบุช่วงสิ้นสุดโครงการให้ถูกต้อง', endEl);
        if (hasStart && hasEnd) {
          const d1 = new Date(formData.projectStartPeriod).getTime();
          const d2 = new Date(formData.projectEndPeriod).getTime();
          if (d2 < d1)
            addErr(
              'ช่วงเวลาระยะโครงการไม่ถูกต้อง (สิ้นสุดต้องไม่ก่อนเริ่มต้น)',
              endEl
            );
        }
      }

      // ===== 21. ปีปฏิบัติการ =====
      {
        const el = resolveEl('operation-year', {
          tag: 'input',
          exactId: 'operation-year'
        });
        if (
          isEmpty(formData.operationYear) ||
          !/^\d{4}$/.test(String(formData.operationYear))
        )
          addErr('กรุณาระบุปีปฏิบัติการให้ถูกต้อง (YYYY)', el);
        else markValidEl(el);
      }
    }

    // ===== งวดงาน (assetType: 3,8) =====
    if (requiresInstallment) {
      const el = resolveEl('installment-work-select', {
        tag: 'select'
      });
      if (isEmpty(formData.workPeriod)) addErr('กรุณาเลือกงวดงาน', el);
      else markValidEl(el);
    }

    // ===== Generic validation สำหรับฟิลด์ที่ยังไม่ได้ตรวจสอบ =====
    {
      const radioGroupsHandled = new Set();
      const isRequiredElement = (element) => {
        if (!element) return false;
        if (element.required) return true;
        if (
          element.dataset &&
          typeof element.dataset.required !== 'undefined' &&
          element.dataset.required === 'true'
        ) {
          return true;
        }
        if (
          typeof element.getAttribute === 'function' &&
          element.getAttribute('aria-required') === 'true'
        ) {
          return true;
        }
        return false;
      };
      const shouldSkipElement = (element) => {
        if (!element) return true;
        if (!this.shouldValidateElement(element)) return true;
        if (processedElements.has(element)) return true;
        if (
          typeof element.matches === 'function' &&
          element.matches('[data-attachment-input]')
        ) {
          return true;
        }
        const rawType =
          typeof element.getAttribute === 'function'
            ? element.getAttribute('type')
            : '';
        const typeAttr =
          typeof rawType === 'string' ? rawType.toLowerCase() : '';
        if (typeAttr === 'file') return true;
        return false;
      };
      const candidateElements = Array.from(
        document.querySelectorAll('input, select, textarea')
      ).filter((el) => !shouldSkipElement(el));

      candidateElements.forEach((element) => {
        if (!isRequiredElement(element)) {
          return;
        }

        const tagName = element.tagName ? element.tagName.toLowerCase() : '';
        const rawType =
          typeof element.getAttribute === 'function'
            ? element.getAttribute('type')
            : '';
        const typeAttr =
          typeof rawType === 'string' ? rawType.toLowerCase() : '';
        const labelText = this.getFieldLabel(element);
        const verb = tagName === 'select' ? 'กรุณาเลือก' : 'กรุณากรอก';
        const buildMessage = () =>
          labelText ? `${verb}${labelText}` : 'กรุณากรอกข้อมูลให้ครบถ้วน';

        if (typeAttr === 'radio') {
          const groupName = element.getAttribute('name') || element.id || '';
          if (!groupName || radioGroupsHandled.has(groupName)) {
            return;
          }
          radioGroupsHandled.add(groupName);
          const radios = Array.from(
            document.getElementsByName(groupName)
          ).filter((node) => {
            if (
              !node ||
              !node.tagName ||
              node.tagName.toLowerCase() !== 'input'
            )
              return false;
            const rawNodeType =
              typeof node.getAttribute === 'function'
                ? node.getAttribute('type')
                : '';
            const nodeType =
              typeof rawNodeType === 'string' ? rawNodeType.toLowerCase() : '';
            if (nodeType !== 'radio') return false;
            return this.shouldValidateElement(node);
          });
          const anyChecked = radios.some((radio) => radio.checked);
          if (!anyChecked) {
            addErr(buildMessage(), element);
            radios.forEach((radio) => markInvalidEl(radio));
          } else {
            radios.forEach((radio) => markValidEl(radio));
          }
          return;
        }

        if (typeAttr === 'checkbox') {
          if (!element.checked) {
            addErr(buildMessage(), element);
          } else {
            markValidEl(element);
          }
          return;
        }

        const rawValue = element.value;
        const value = typeof rawValue === 'string' ? rawValue.trim() : rawValue;
        let isValidValue = true;

        if (value === '' || value === null || value === undefined) {
          isValidValue = false;
        } else if (typeAttr === 'number') {
          const numericValue = parseNum(rawValue);
          if (!Number.isFinite(numericValue)) {
            isValidValue = false;
          } else {
            const minAttr = element.getAttribute('min');
            if (
              minAttr !== null &&
              minAttr !== '' &&
              numericValue < Number(minAttr)
            ) {
              isValidValue = false;
            }
            if (
              element.dataset &&
              element.dataset.integerOnly === 'true' &&
              !Number.isInteger(numericValue)
            ) {
              isValidValue = false;
            }
          }
        }

        if (!isValidValue) {
          addErr(buildMessage(), element);
        } else {
          markValidEl(element);
        }
      });
    }

    // ===== ผลลัพธ์ =====
    if (errors.length > 0) {
      this.forceShowInvalidFeedback?.();
      console.log('❌ Validation errors:', errors);
    } else {
      console.log('✅ Validation passed (For Approval)');
    }

    return {
      isValid: errors.length === 0,
      errors,
      data: formData
    };
  }

  /**
   * Alias สำหรับ validateForApproval (ใช้เพื่อ backward compatibility)
   */
  validateForm() {
    return this.validateForApproval();
  }

  /**
   * ดึง label ของฟิลด์สำหรับข้อความ error
   */
  getFieldLabel(element) {
    const label = element
      .closest('.col-xl-6, .col-xl-4, .col-xl-12')
      ?.querySelector('label');
    if (label) {
      return label.textContent.replace(':', '').replace('*', '').trim();
    }
    return element.name || element.id || 'ฟิลด์นี้';
  }

  /**
   * บังคับแสดง invalid-feedback สำหรับฟิลด์ที่ invalid เท่านั้น
   */
  forceShowInvalidFeedback() {
    console.log(
      '🔧 บังคับแสดง invalid-feedback สำหรับฟิลด์ที่ invalid เท่านั้น...'
    );

    // แสดง feedback สำหรับฟิลด์ที่อยู่สถานะ invalid เท่านั้น
    const invalidFields = Array.from(
      document.querySelectorAll('[data-validation-state="invalid"]')
    ).filter((field) => this.shouldValidateElement(field));
    console.log(`พบฟิลด์ที่ invalid: ${invalidFields.length} ฟิลด์`);

    invalidFields.forEach((field) => {
      const feedback = this.findFeedbackElement(field, 'invalid-feedback');
      if (feedback) {
        feedback.style.display = 'block';
        feedback.classList.add('show');
        console.log(
          `✅ แสดง feedback สำหรับฟิลด์ invalid: ${
            field.id
          } - "${feedback.textContent.trim()}"`
        );
      }
    });

    // ซ่อน feedback สำหรับฟิลด์ที่ valid
    const validFields = Array.from(
      document.querySelectorAll('[data-validation-state="valid"]')
    ).filter((field) => this.shouldValidateElement(field));
    validFields.forEach((field) => {
      const feedback = this.findFeedbackElement(field, 'invalid-feedback');
      if (feedback) {
        feedback.style.display = 'none';
        feedback.classList.remove('show');
        console.log(`✅ ซ่อน feedback สำหรับฟิลด์ valid: ${field.id}`);
      }
    });
  }

  /**
   * รีเซ็ตฟอร์มกลับสู่สถานะเริ่มต้น
   */
  resetForm() {
    if (!this.masterData) {
      console.error('❌ Master data ไม่พร้อมใช้งาน');
      return;
    }

    // รีเซ็ต dropdowns ทั้งหมด
    this.masterData.setValue('fiscal-year-select', '');
    this.masterData.setValue('budget-source-select', '');
    this.masterData.setValue('version-plan-select', '');
    this.masterData.setValue('asset-type-select', '');
    this.masterData.setValue('equipment-standards-select', '');

    // ล้างฟิลด์รหัส
    const codeField = document.getElementById('asset-type-code');
    if (codeField) {
      codeField.value = '';
    }

    // ซ่อนการ์ดทั้งหมด
    this.hideAllAssetTypeCards();

    // ล้างสถานะ validation ที่ถูกตั้งไว้
    const allFields = document.querySelectorAll('[data-validation-state]');
    allFields.forEach((field) => {
      this.clearElementValidationState(field);
    });

    // ล้าง container validation
    const container = document.querySelector('.container-fluid');
    if (container) {
      container.classList.remove('was-validated');
    }

    console.log('🔄 รีเซ็ตฟอร์มเสร็จสิ้น');
  }

  // ========================================
  // EXPECTED DISBURSEMENT MANAGEMENT
  // ========================================

  /**
   * สร้าง Choices.js instance สำหรับ dropdown วันที่คาดว่าจะเบิกจ่าย
   */
  initializeExpectedDisbursementChoices() {
    if (typeof Choices === 'undefined') {
      console.warn(
        '⚠️ Choices.js ไม่พร้อมใช้งาน ไม่สามารถจัดรูปแบบ dropdown วันที่คาดว่าจะเบิกจ่ายได้'
      );
      return;
    }

    const selects = document.querySelectorAll(
      'select.expected-disbursement-date'
    );
    selects.forEach((select) => {
      this.destroyChoicesInstance(select);
      const instance = this.createChoicesInstance(select);
      select.choicesInstance = instance;
      if (instance) {
        this.applyYearAttributesToChoices(select);

        if (!select.dataset.yearListenerAttached) {
          select.addEventListener('change', () => {
            this.applyYearAttributesToChoices(select);
          });
          select.dataset.yearListenerAttached = 'true';
        }
      }
    });
  }

  /**
   * เฝ้าการเปลี่ยนแปลงของปีงบประมาณเพื่ออัพเดตรายการวันที่คาดว่าจะเบิกจ่าย
   */
  setupFiscalYearWatcher() {
    const fiscalYearSelect = document.getElementById('fiscal-year-select');

    if (!fiscalYearSelect) {
      console.warn('⚠️ ไม่พบ dropdown ปีงบประมาณ');
      return;
    }

    fiscalYearSelect.addEventListener('change', (event) => {
      // Reset validation flag เมื่อเปลี่ยนปีงบประมาณ
      this.hasAttemptedSubmit = false;

      const fiscalYearName = this.resolveFiscalYearName(event.target.value);
      this.updateExpectedDisbursementOptions(fiscalYearName);
    });

    const initialFiscalYearName = this.resolveFiscalYearName(
      fiscalYearSelect.value
    );
    this.updateExpectedDisbursementOptions(initialFiscalYearName);
  }

  /**
   * อัพเดตรายการตัวเลือกของวันที่คาดว่าจะเบิกจ่ายตามปีงบประมาณ
   * @param {string|number} fiscalYear - ปีงบประมาณ (เช่น 2568)
   */
  updateExpectedDisbursementOptions(fiscalYear) {
    const selects = document.querySelectorAll(
      'select.expected-disbursement-date'
    );
    if (!selects.length) {
      return;
    }

    const yearInt = parseInt(fiscalYear, 10);
    const hasValidYear = !Number.isNaN(yearInt);
    const optionData = hasValidYear
      ? this.buildExpectedDisbursementChoices(yearInt)
      : null;

    selects.forEach((select) => {
      const previousValue = select.value;
      const restoredValue = this.findRestorableValue(previousValue, optionData);

      this.destroyChoicesInstance(select);
      this.replaceSelectOptions(select, optionData, restoredValue);

      const instance = this.createChoicesInstance(select);
      select.choicesInstance = instance;

      if (instance && restoredValue) {
        instance.setChoiceByValue(restoredValue);
      }

      if (instance) {
        this.applyYearAttributesToChoices(select);

        if (!select.dataset.yearListenerAttached) {
          select.addEventListener('change', () => {
            this.applyYearAttributesToChoices(select);
          });
          select.dataset.yearListenerAttached = 'true';
        }
      }

      // ไม่ต้อง trigger change event เพื่อป้องกัน validation ทำงานก่อนเวลา
      // ค่าจะถูก sync อัตโนมัติผ่าน Choices.js
      // select.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  /**
   * ทำลาย Choices instance หากมีอยู่ และล้าง DOM wrapper
   */
  destroyChoicesInstance(select) {
    if (!select) {
      return;
    }

    if (
      select.choicesInstance &&
      typeof select.choicesInstance.destroy === 'function'
    ) {
      select.choicesInstance.destroy();
      select.choicesInstance = null;
    }

    const wrapper = select.nextElementSibling;
    if (wrapper && wrapper.classList.contains('choices')) {
      wrapper.remove();
    }

    select.classList.remove('choices__input', 'choices__input--hidden');
    select.removeAttribute('data-choice');
  }

  /**
   * สร้าง Choices instance สำหรับ select ที่กำหนด
   */
  createChoicesInstance(select) {
    if (typeof Choices === 'undefined') {
      return null;
    }

    return new Choices(select, {
      searchEnabled: true,
      itemSelectText: '',
      shouldSort: false,
      placeholder: true,
      placeholderValue: 'กรุณาเลือก',
      callbackOnCreateTemplates: (template) => {
        const buildAttributes = (data) => {
          const attrs = [];
          if (typeof data.value !== 'undefined') {
            attrs.push(`data-value="${data.value}"`);
          }
          if (data.customProperties && data.customProperties.year) {
            attrs.push(`data-year="${data.customProperties.year}"`);
          }
          return attrs.length ? ' ' + attrs.join(' ') : '';
        };

        return {
          item: (classNames, data) => {
            const attrs = buildAttributes(data);
            const roleAttributes = [];
            if (data.active) roleAttributes.push('aria-selected="true"');
            if (data.disabled) roleAttributes.push('aria-disabled="true"');
            const roleAttr = roleAttributes.length
              ? ' ' + roleAttributes.join(' ')
              : '';
            const className = `${classNames.item} ${
              data.highlighted
                ? classNames.highlightedState
                : classNames.itemSelectable
            }`;
            const label = data.label || data.value || '';
            return template(
              `<div class="${className}" data-item data-id="${data.id}"${attrs}${roleAttr}>${label}</div>`
            );
          },
          choice: (classNames, data) => {
            const attrs = buildAttributes(data);
            const className = `${classNames.item} ${classNames.itemChoice} ${
              data.disabled
                ? classNames.itemDisabled
                : classNames.itemSelectable
            }`;
            const role = data.groupId > 0 ? 'role="treeitem"' : 'role="option"';
            const disabledAttr = data.disabled
              ? 'aria-disabled="true" data-choice-disabled'
              : 'data-choice-selectable';
            const label = data.label || data.value || '';
            return template(
              `<div class="${className}" data-choice data-id="${data.id}"${attrs} ${role} ${disabledAttr}>${label}</div>`
            );
          }
        };
      }
    });
  }

  /**
   * แปลงค่า id ของปีงบประมาณให้เป็นชื่อปี (เช่น 2568)
   */
  resolveFiscalYearName(fiscalYearId) {
    if (!fiscalYearId) {
      return '';
    }

    const fiscalYears = this.masterData?.data?.fiscalYears || [];
    const record = fiscalYears.find(
      (item) => String(item.id) === String(fiscalYearId)
    );
    if (record && record.name) {
      return record.name;
    }

    if (/^\d{4}$/.test(String(fiscalYearId))) {
      return String(fiscalYearId);
    }

    return '';
  }

  /**
   * ดึงตัวเลขปี พ.ศ. จากข้อความ label
   */
  extractYearFromLabel(label) {
    if (!label) {
      return '';
    }

    const match = label.match(/(\d{4})/);
    return match ? match[1] : '';
  }

  /**
   * ใส่ attribute data-year ให้กับ choices items ตาม option ที่เกี่ยวข้อง
   */
  applyYearAttributesToChoices(select) {
    const instance = select?.choicesInstance;
    if (!instance) {
      return;
    }

    const apply = () => {
      const optionYearMap = new Map();
      select.querySelectorAll('option').forEach((option) => {
        const year = option.dataset.year;
        if (year) {
          optionYearMap.set(option.value, year);
        }
      });

      const container = instance.containerOuter?.element;
      if (!container) {
        return;
      }

      const updateElement = (element) => {
        if (!element) return;
        const value = element.getAttribute('data-value');
        if (value && optionYearMap.has(value)) {
          element.setAttribute('data-year', optionYearMap.get(value));
        } else {
          element.removeAttribute('data-year');
        }
      };

      const singleItem = container.querySelector(
        '.choices__list--single .choices__item[data-value]'
      );
      updateElement(singleItem);

      container
        .querySelectorAll('.choices__list--dropdown .choices__item[data-value]')
        .forEach(updateElement);
    };

    if (typeof requestAnimationFrame === 'function') {
      requestAnimationFrame(apply);
    } else {
      setTimeout(apply, 0);
    }
  }

  /**
   * สร้างข้อมูลเดือนสำหรับปีงบประมาณที่กำหนด
   * @param {number} fiscalYear - ปีงบประมาณ (เช่น 2568)
   * @returns {Array<{value: string, label: string}>}
   */
  buildExpectedDisbursementChoices(fiscalYear) {
    const thaiMonths = {
      1: 'มกราคม',
      2: 'กุมภาพันธ์',
      3: 'มีนาคม',
      4: 'เมษายน',
      5: 'พฤษภาคม',
      6: 'มิถุนายน',
      7: 'กรกฎาคม',
      8: 'สิงหาคม',
      9: 'กันยายน',
      10: 'ตุลาคม',
      11: 'พฤศจิกายน',
      12: 'ธันวาคม'
    };

    const maxFiscalYear = this.getMaximumFiscalYear() || fiscalYear;
    const startYear = fiscalYear - 1;
    const endYear = Math.max(fiscalYear, maxFiscalYear);
    const months = [];

    for (let year = startYear; year <= endYear; year++) {
      const startMonth = year === startYear ? 10 : 1;
      const endMonth = year === endYear ? 9 : 12;

      for (let month = startMonth; month <= endMonth; month++) {
        const paddedMonth = month.toString().padStart(2, '0');
        months.push({
          value: `${year}-${paddedMonth}`,
          label: `${thaiMonths[month]} ${year}`
        });
      }
    }

    return months;
  }

  getMaximumFiscalYear() {
    const fiscalYears = this.masterData?.data?.fiscalYears || [];
    const numericYears = fiscalYears
      .map((item) => {
        const value =
          item?.name ?? item?.year ?? item?.id ?? item?.value ?? null;
        const parsed = parseInt(value, 10);
        return Number.isNaN(parsed) ? null : parsed;
      })
      .filter((value) => value !== null);

    if (!numericYears.length) {
      return null;
    }

    return Math.max(...numericYears);
  }

  /**
   * สร้าง payload สำหรับ Choices.js
   */
  buildChoicesPayload(optionData, previousValue) {
    const payload = [
      {
        value: '',
        label: 'กรุณาเลือก',
        selected: !previousValue,
        disabled: false,
        customProperties: { year: '' }
      }
    ];

    if (optionData && optionData.length) {
      optionData.forEach((item) => {
        payload.push({
          value: item.value,
          label: item.label,
          selected: item.value === previousValue,
          disabled: false,
          customProperties: {
            year: this.extractYearFromLabel(item.label)
          }
        });
      });
    }

    return payload;
  }

  /**
   * อัพเดตตัวเลือกของ select ธรรมดา (กรณีไม่มี Choices.js)
   */
  replaceSelectOptions(select, optionData, selectedValue) {
    select.innerHTML = '';

    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = 'กรุณาเลือก';
    placeholderOption.dataset.year = '';
    placeholderOption.dataset.customProperties = JSON.stringify({ year: '' });
    select.appendChild(placeholderOption);

    if (optionData && optionData.length) {
      optionData.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        const year = this.extractYearFromLabel(item.label);
        if (year) {
          option.dataset.year = year;
          option.dataset.customProperties = JSON.stringify({ year });
        } else {
          option.dataset.customProperties = JSON.stringify({});
        }
        select.appendChild(option);
      });
    }

    select.value = selectedValue || '';
  }

  /**
   * ตรวจสอบว่าสามารถคืนค่าที่เลือกไว้ก่อนหน้าหรือไม่
   */
  findRestorableValue(previousValue, optionData) {
    if (!previousValue) {
      return '';
    }

    if (!optionData || !optionData.length) {
      return '';
    }

    const exists = optionData.some((item) => item.value === previousValue);
    return exists ? previousValue : '';
  }

  // ========================================
  // REPLACEMENT ASSET VISIBILITY
  // ========================================

  getAllReplacementContainers() {
    return Array.from(
      document.querySelectorAll('.replacement-asset-container')
    );
  }

  getVisibleReplacementContainer() {
    return document.querySelector('.replacement-asset-container:not(.d-none)');
  }

  readReplacementCache(container) {
    if (!container?.dataset?.replacementCache) {
      return {};
    }
    try {
      const data = JSON.parse(container.dataset.replacementCache);
      return data && typeof data === 'object' ? data : {};
    } catch (error) {
      console.warn('⚠️ ไม่สามารถ parse replacement cache:', error);
      return {};
    }
  }

  writeReplacementCache(container, updater) {
    if (!container) {
      return;
    }
    const current = this.readReplacementCache(container);
    const next = { ...current, ...updater };
    Object.keys(next).forEach((key) => {
      if (next[key] === undefined || next[key] === null) {
        delete next[key];
      }
    });
    if (Object.keys(next).length === 0) {
      delete container.dataset.replacementCache;
    } else {
      container.dataset.replacementCache = JSON.stringify(next);
    }
  }

  populateSerialNumbers(container, serialNumbers = []) {
    let targetContainer = container || this.getVisibleReplacementContainer();
    if (!targetContainer) {
      const allContainers = this.getAllReplacementContainers();
      targetContainer = allContainers.length ? allContainers[0] : null;
    }

    if (!targetContainer) {
      const fallbackTbody = document.getElementById('machinery-serial-tbody-1');
      if (fallbackTbody) {
        fallbackTbody.innerHTML = '';
        const values = serialNumbers.length ? serialNumbers : [''];
        values.forEach((value) => {
          const row = this.createNewSerialRow(value);
          fallbackTbody.appendChild(row);
        });
      }
      return;
    }

    const tbody = targetContainer.querySelector(
      '[id^="machinery-serial-tbody"]'
    );
    if (!tbody) {
      return;
    }

    const values = serialNumbers.length ? serialNumbers : [''];
    tbody.innerHTML = '';
    values.forEach((raw) => {
      const value = raw !== undefined && raw !== null ? String(raw).trim() : '';
      const row = this.createNewSerialRow(value);
      tbody.appendChild(row);
    });

    if (!tbody.querySelector('.equipment-input')) {
      tbody.appendChild(this.createNewSerialRow());
    }

    this.setupEquipmentSerialEventListeners();
  }

  normalizeSerialValue(entry) {
    if (entry === null || entry === undefined) {
      return '';
    }

    if (typeof entry === 'string') {
      return entry.trim();
    }

    if (Array.isArray(entry)) {
      return entry
        .map((item) => this.normalizeSerialValue(item))
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

      const fallback = Object.values(entry).find(
        (value) =>
          value !== null && value !== undefined && String(value).trim() !== ''
      );
      if (fallback) {
        return String(fallback).trim();
      }
    }

    return String(entry).trim();
  }

  /**
   * จัดการการแสดง/ซ่อนของหมายเลขครุภัณฑ์ทดแทนตามค่าลักษณะครุภัณฑ์
   */
  setupReplacementAssetSections() {
    const containers = document.querySelectorAll(
      '.replacement-asset-container'
    );

    if (!containers.length) {
      return;
    }

    console.log('🧰 ตั้งค่าการแสดงผลของหมายเลขครุภัณฑ์ทดแทน...');

    containers.forEach((container) => {
      const selectId = container.getAttribute('data-equipment-setup');
      if (!selectId) {
        console.warn(
          '⚠️ replacement container ไม่มี data-equipment-setup:',
          container
        );
        return;
      }

      const selectElement = document.getElementById(selectId);
      if (!selectElement) {
        console.warn('⚠️ ไม่พบ dropdown ลักษณะสำหรับ:', selectId);
        return;
      }

      const handleVisibility = () => {
        const selectedOption =
          selectElement.options[selectElement.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text.trim() : '';
        const shouldShow =
          selectElement.value === '2' || selectedText.includes('ทดแทน');

        if (shouldShow) {
          container.classList.remove('d-none');
          container.setAttribute('aria-hidden', 'false');
          this.restoreReplacementData(container);
        } else {
          this.storeReplacementData(container);
          this.resetReplacementSection(container);
          container.classList.add('d-none');
          container.setAttribute('aria-hidden', 'true');
        }
      };

      selectElement.addEventListener('change', handleVisibility);

      // ประมวลผลค่าเริ่มต้นทันที (รองรับหน้าแก้ไข)
      handleVisibility();
    });

    console.log('✅ ตั้งค่าการแสดงผลของหมายเลขครุภัณฑ์ทดแทนเรียบร้อย');
  }

  /**
   * เก็บค่าปัจจุบันของบล็อกหมายเลขครุภัณฑ์ทดแทน
   */
  storeReplacementData(container) {
    const data = {};

    const textInputs = container.querySelectorAll(
      'input[type="text"]:not(.equipment-input)'
    );
    if (textInputs.length) {
      data.textInputs = Array.from(textInputs).map((input) => input.value);
    }

    const textareas = container.querySelectorAll('textarea');
    if (textareas.length) {
      data.textareas = Array.from(textareas).map((textarea) => textarea.value);
    }

    const editableCells = container.querySelectorAll(
      '[contenteditable="true"]'
    );
    if (editableCells.length) {
      data.contentEditable = Array.from(editableCells).map(
        (cell) => cell.innerHTML
      );
    }

    const serialTbody = container.querySelector(
      '[id^="machinery-serial-tbody"]'
    );
    if (serialTbody) {
      const serialNumbers = Array.from(
        serialTbody.querySelectorAll('.equipment-input')
      )
        .map((input) => input.value.trim())
        .filter((value) => value !== '');
      if (serialNumbers.length) {
        data.serialNumbers = serialNumbers;
      } else {
        data.serialNumbers = null;
      }
    }

    // ถ้าไม่มีข้อมูลที่ต้องเก็บ ให้ลบ cache เดิมเพื่อไม่ให้คืนค่าผิด
    this.writeReplacementCache(container, data);
  }

  /**
   * คืนค่าข้อมูลที่เก็บไว้ให้กับบล็อก
   */
  restoreReplacementData(container) {
    const cache = container.dataset.replacementCache;
    if (!cache) {
      return;
    }

    let data;
    try {
      data = JSON.parse(cache);
    } catch (error) {
      console.warn('⚠️ ไม่สามารถ parse replacement cache:', error);
      return;
    }

    if (data.textInputs) {
      const inputs = container.querySelectorAll(
        'input[type="text"]:not(.equipment-input)'
      );
      inputs.forEach((input, index) => {
        if (index < data.textInputs.length) {
          input.value = data.textInputs[index];
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    }

    if (data.textareas) {
      const textareas = container.querySelectorAll('textarea');
      textareas.forEach((textarea, index) => {
        if (index < data.textareas.length) {
          textarea.value = data.textareas[index];
          textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    }

    if (data.contentEditable) {
      const editableCells = container.querySelectorAll(
        '[contenteditable="true"]'
      );
      editableCells.forEach((cell, index) => {
        if (index < data.contentEditable.length) {
          cell.innerHTML = data.contentEditable[index];
        }
      });
    }

    if (data.serialNumbers) {
      this.populateSerialNumbers(container, Array.from(data.serialNumbers));
      this.setupEquipmentSerialEventListeners();
    }
  }

  /**
   * รีเซ็ตค่าภายในบล็อกหมายเลขครุภัณฑ์ทดแทนเมื่อซ่อน
   * @param {HTMLElement} container - บล็อกที่ต้องการรีเซ็ตค่า
   */
  resetReplacementSection(container) {
    // ล้าง input text
    const textInputs = container.querySelectorAll('input[type="text"]');
    textInputs.forEach((input) => {
      if (input.value !== '') {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });

    // ล้าง textarea (ถ้ามี)
    const textareas = container.querySelectorAll('textarea');
    textareas.forEach((textarea) => {
      if (textarea.value !== '') {
        textarea.value = '';
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });

    // ล้าง cell ที่เป็น contenteditable
    const editableCells = container.querySelectorAll(
      '[contenteditable="true"]'
    );
    editableCells.forEach((cell) => {
      if (cell.textContent !== '') {
        cell.textContent = '';
      }
    });

    // รีเซ็ตตาราง serial number ถ้ามี
    const serialTbody = container.querySelector(
      '[id^="machinery-serial-tbody"]'
    );
    if (serialTbody) {
      const rows = Array.from(serialTbody.querySelectorAll('tr'));
      rows.forEach((row, index) => {
        const input = row.querySelector('.equipment-input');
        if (input && input.value !== '') {
          input.value = '';
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (index > 0) {
          row.remove();
        }
      });

      if (!serialTbody.querySelector('.equipment-input')) {
        serialTbody.appendChild(this.createNewSerialRow());
      }
    }
  }

  // ========================================
  // EQUIPMENT SERIAL MANAGEMENT
  // ========================================

  /**
   * เริ่มต้นระบบจัดการหมายเลขครุภัณฑ์ทดแทน
   */
  initializeEquipmentSerialManager() {
    console.log('📋 เริ่มต้นระบบจัดการหมายเลขครุภัณฑ์...');

    // ตั้งค่า event listeners
    this.setupEquipmentSerialEventListeners();

    console.log('✅ ระบบจัดการหมายเลขครุภัณฑ์พร้อมใช้งาน');
  }

  /**
   * ตั้งค่า event listeners สำหรับหมายเลขครุภัณฑ์
   */
  setupEquipmentSerialEventListeners() {
    const containers = this.getAllReplacementContainers();
    containers.forEach((container) => {
      if (!container || container.dataset.serialListenersAttached === 'true') {
        return;
      }

      const tbody = container.querySelector('[id^="machinery-serial-tbody"]');
      const addButton = container.querySelector('[id^="add-machinery-serial"]');

      if (addButton && tbody?.id) {
        addButton.addEventListener('click', () => {
          this.addNewSerialRow(tbody.id);
        });
      }

      if (tbody) {
        tbody.addEventListener('click', (e) => {
          if (e.target.closest('.remove-row-btn')) {
            const row = e.target.closest('tr');
            if (row) {
              this.removeSerialRow(row);
            }
          }
        });

        tbody.addEventListener('keydown', (e) => {
          if (
            e.key === 'Enter' &&
            e.target.classList.contains('equipment-input')
          ) {
            e.preventDefault();
            this.handleSerialEnterKey(e.target);
          }
        });

        tbody.addEventListener('input', (e) => {
          if (e.target.classList.contains('equipment-input')) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value.length > 10) {
              e.target.value = e.target.value.substring(0, 10);
            }
          }
        });
      }

      container.dataset.serialListenersAttached = 'true';
    });
  }

  /**
   * เพิ่มแถวใหม่สำหรับหมายเลขครุภัณฑ์
   */
  addNewSerialRow(tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) {
      console.error('❌ ไม่พบ tbody:', tbodyId);
      return;
    }

    const newRow = this.createNewSerialRow();
    tbody.appendChild(newRow);

    const newInput = newRow.querySelector('.equipment-input');
    if (newInput) {
      newInput.focus();
    }

    console.log('✅ เพิ่มแถวใหม่');
  }

  /**
   * สร้างแถวใหม่สำหรับหมายเลขครุภัณฑ์
   */
  createNewSerialRow(value = '') {
    const row = document.createElement('tr');
    row.className = 'equipment-row';

    const safeValue =
      value !== undefined && value !== null ? String(value).trim() : '';

    row.innerHTML = `
      <td>
        <input type="text" class="form-control equipment-input"
               value="${this.escapeHtml(safeValue)}"
               placeholder="กรุณากรอกหมายเลขครุภัณฑ์ (10 หลัก)"
               pattern="[0-9]{10}"
               maxlength="10"
               inputmode="numeric"
               title="กรุณากรอกตัวเลข 10 หลักเท่านั้น"
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
   * ลบแถวหมายเลขครุภัณฑ์
   */
  removeSerialRow(row) {
    const tbody = row.parentNode;
    const remainingRows = tbody.querySelectorAll('.equipment-row');

    // ถ้าเหลือแถวเดียว ไม่ให้ลบ
    if (remainingRows.length <= 1) {
      const input = row.querySelector('.equipment-input');
      if (input) {
        input.value = '';
        input.focus();
      }
      console.log('⚠️ ไม่สามารถลบแถวสุดท้ายได้');
      return;
    }

    row.remove();
    console.log('✅ ลบแถวเรียบร้อย');
  }

  /**
   * จัดการเมื่อกด Enter ในหมายเลขครุภัณฑ์
   */
  handleSerialEnterKey(input) {
    const currentValue = input.value.trim();
    const tbody = input.closest('tbody');
    const tbodyId = tbody?.id || 'machinery-serial-tbody-1';

    if (currentValue) {
      this.addNewSerialRow(tbodyId);
    } else {
      const currentRow = input.closest('tr');
      const nextRow = currentRow ? currentRow.nextElementSibling : null;
      if (nextRow) {
        const nextInput = nextRow.querySelector('.equipment-input');
        if (nextInput) {
          nextInput.focus();
        }
      } else {
        this.addNewSerialRow(tbodyId);
      }
    }
  }

  /**
   * ดึงหมายเลขครุภัณฑ์ทั้งหมด
   */
  getSerialNumbers(tbodyId) {
    //alert(tbodyId);
    let tbody = null;
    if (tbodyId) {
      tbody = document.getElementById(tbodyId);
    }

    if (!tbody) {
      const visibleContainer = this.getVisibleReplacementContainer();
      if (visibleContainer) {
        tbody = visibleContainer.querySelector(
          '[id^="machinery-serial-tbody"]'
        );
      }
    }

    if (!tbody) {
      const assetTypeId = document.getElementById('asset-type-select')?.value;
      tbody = document.getElementById(`machinery-serial-tbody-${assetTypeId}`);
    }

    if (!tbody) return [];

    const inputs = tbody.querySelectorAll('.equipment-input');
    const serialNumbers = [];
    //alert(inputs);
    // console.log('aaass===', inputs);
    inputs.forEach((input) => {
      const value = input.value.trim();
      //alert(value);
      if (value) {
        serialNumbers.push(value);
      }
    });

    if (serialNumbers.length > 0) {
      return serialNumbers;
    }

    const fallbackInputs = document.querySelectorAll(
      '.replacement-asset-container .equipment-input'
    );
    const fallbackSerials = [];
    fallbackInputs.forEach((input) => {
      const value = input.value.trim();
      if (value) {
        fallbackSerials.push(value);
      }
    });

    return fallbackSerials;
  }

  // ========================================
  // PRICE CALCULATION
  // ========================================

  /**
   * เริ่มต้นระบบคำนวณราคา
   */
  initializePriceCalculator() {
    console.log('💰 เริ่มต้นระบบคำนวณราคา...');

    // รีเซ็ต config เพื่อป้องกันการเพิ่มซ้ำเมื่อ initialize หลายครั้ง
    this.priceCalculationConfigs = [];

    // ตั้งค่าการคำนวณสำหรับการ์ดเครื่องจักร (Card 1)
    this.setupPriceCalculation({
      id: 'card-1',
      quantityId: 'quantity-1',
      priceId: 'price-1',
      totalId: 'total-1'
    });

    // ตั้งค่าการคำนวณสำหรับ Card 2 (เครื่องตกแต่ง)
    this.setupPriceCalculation({
      id: 'card-2',
      quantityId: 'quantity-2',
      priceId: 'price-2',
      totalId: 'total-2'
    });

    // ตั้งค่าการคำนวณสำหรับ Card 4 (ยานพาหนะ)
    this.setupPriceCalculation({
      id: 'card-4',
      quantityId: 'quantity-4',
      priceId: 'price-4',
      totalId: 'total-4'
    });

    // ตั้งค่าการคำนวณสำหรับ Card 7 (สินทรัพย์ไม่มีตัวตน)
    this.setupPriceCalculation({
      id: 'card-7',
      quantityId: 'quantity-7',
      priceId: 'price-7',
      totalId: 'total-7'
    });

    // ตั้งค่าการคำนวณสำหรับ Card 10 (สินทรัพย์มูลค่าต่ำ)
    this.setupPriceCalculation({
      id: 'card-10',
      quantityId: 'quantity-10',
      priceId: 'price-10',
      totalId: 'total-10'
    });

    console.log('✅ ระบบคำนวณราคาพร้อมใช้งาน');
  }

  /**
   * ตั้งค่าการคำนวณราคา
   */
  setupPriceCalculation(config) {
    const { quantityId, priceId, totalId } = config;

    // หา elements
    const quantityInput = document.getElementById(quantityId);
    const priceInput = document.getElementById(priceId);
    const totalInput = document.getElementById(totalId);

    if (!quantityInput || !priceInput || !totalInput) {
      console.warn(`⚠️ ไม่พบ elements สำหรับ ${config.id}`);
      return;
    }

    this.priceCalculationConfigs.push({ ...config });

    // ตั้งค่า event listeners
    quantityInput.addEventListener('input', () => {
      this.calculateTotal(config);
    });

    priceInput.addEventListener('input', () => {
      this.calculateTotal(config);
    });

    // คำนวณครั้งแรก
    this.calculateTotal(config);

    console.log(`✅ ตั้งค่าการคำนวณสำหรับ ${config.id}`);
  }

  /**
   * คำนวณราคารวม
   */
  calculateTotal(config) {
    const { quantityId, priceId, totalId } = config;

    const quantityInput = document.getElementById(quantityId);
    const priceInput = document.getElementById(priceId);
    const totalInput = document.getElementById(totalId);

    if (!quantityInput || !priceInput || !totalInput) return;

    let quantity = parseFloat(quantityInput.value) || 0;
    if (quantityInput.dataset.integerOnly === 'true') {
      quantity = parseInt(quantityInput.value || '0', 10) || 0;
    }
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;

    if (total > 0) {
      totalInput.value = this.formatNumber(total);
    } else {
      totalInput.value = '';
    }

    totalInput.setAttribute('data-raw-value', total);

    // ตรวจสอบจำนวนเงินสูงสุด (สำหรับ Asset Type 10 - สินทรัพย์มูลค่าต่ำ)
    const maxAmount = totalInput.getAttribute('data-max-amount');
    if (maxAmount && total > 0) {
      const maxValue = parseFloat(maxAmount);
      if (total > maxValue) {
        this.markElementInvalid(totalInput);
        this.updateFeedbackVisibility(totalInput);
      } else {
        this.markElementValid(totalInput);
        this.updateFeedbackVisibility(totalInput);
      }
    }
  }

  /**
   * จัดรูปแบบตัวเลข
   */
  formatNumber(number) {
    if (isNaN(number)) return '0.00';

    return new Intl.NumberFormat('th-TH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(number);
  }

  /**
   * ดึงค่าราคารวม (Simple: คำนวณจาก quantity × price โดยตรงเสมอ)
   *
   * วิธีการเดิม (ซับซ้อน):
   * - อ่านจาก data-raw-value attribute
   * - ถ้าไม่มี ค่อยคำนวณ
   * - พึ่งพา event listeners
   *
   * วิธีการใหม่ (Simple):
   * - คำนวณจาก quantity × price โดยตรงทุกครั้ง
   * - ไม่พึ่พา attributes หรือ events
   * - เชื่อถือได้ 100%
   */
  getTotalValue(totalId) {
    // หา config ที่ตรงกับ totalId
    const config = this.priceCalculationConfigs.find(
      (c) => c.totalId === totalId
    );

    if (!config) {
      console.warn('⚠️ getTotalValue: ไม่พบ config สำหรับ', totalId);
      return 0;
    }

    // ดึง input elements
    const quantityInput = document.getElementById(config.quantityId);
    const priceInput = document.getElementById(config.priceId);

    if (!quantityInput || !priceInput) {
      console.warn('⚠️ getTotalValue: ไม่พบ quantity หรือ price input');
      return 0;
    }

    // คำนวณโดยตรง: quantity × price
    const quantity = parseFloat(quantityInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;

    console.log(`📊 getTotalValue: ${quantity} × ${price} = ${total}`);
    return total;
  }

  /**
   * หา input จำนวนเงินรวมของการ์ดที่กำลังแสดง
   * (ตรวจสอบว่า card ไหนไม่ถูกซ่อน/collapse)
   */
  getActiveTotalInput() {
    console.log('🔍 กำลังหา active card...');
    console.log('  จำนวน configs:', this.priceCalculationConfigs.length);

    for (const config of this.priceCalculationConfigs) {
      const totalInput = document.getElementById(config.totalId);

      console.log(`  - ตรวจสอบ ${config.id} (${config.totalId}):`, {
        found: !!totalInput,
        shouldValidate: totalInput
          ? this.shouldValidateElement(totalInput)
          : false
      });

      if (!totalInput) {
        continue;
      }

      if (this.shouldValidateElement(totalInput)) {
        console.log(`  ✅ พบ active card: ${config.id}`);
        return totalInput;
      }
    }

    console.warn('⚠️ ไม่พบ active card ใดๆ!');
    return null;
  }

  /**
   * ดึงค่าราคารวมจากการ์ดที่กำลังแสดงอยู่
   *
   * วิธีการทำงาน:
   * 1. พยายามหา active card จาก shouldValidateElement (card ที่ display: block)
   * 2. ถ้าไม่เจอ (กรณี Swal ทำให้ DOM เปลี่ยน) ให้ใช้ fallback:
   *    - ดูจากประเภทข้อมูลที่เลือกในฟอร์ม
   *    - หา config ที่ตรงกับประเภทข้อมูลนั้น
   *    - คำนวณตรงจาก quantity × price
   */
  getActiveTotalValue() {
    console.log('🔍 getActiveTotalValue called');

    // วิธีที่ 1: พยายามหาจาก shouldValidateElement (card ที่มอง visible)
    this.recalculateActiveTotal();
    const activeTotalInput = this.getActiveTotalInput();

    if (activeTotalInput) {
      console.log(
        '✅ getActiveTotalValue: พบ activeTotalInput:',
        activeTotalInput.id
      );
      const value = this.getTotalValue(activeTotalInput.id);
      console.log('📊 getActiveTotalValue: value =', value);
      return value;
    }

    // วิธีที่ 2: Fallback - หาจากประเภทข้อมูลที่เลือก
    console.warn(
      '⚠️ getActiveTotalValue: ไม่พบ activeTotalInput, ใช้ fallback method'
    );

    const assetTypeSelect = document.getElementById('asset-type');
    if (!assetTypeSelect || !assetTypeSelect.value) {
      console.error('❌ ไม่พบการเลือกประเภทข้อมูล');
      return 0;
    }

    const selectedAssetTypeValue = assetTypeSelect.value;
    console.log('🔍 Fallback: ประเภทข้อมูลที่เลือก:', selectedAssetTypeValue);

    // หาชื่อประเภทข้อมูลจาก ID
    if (
      !this.masterData ||
      !this.masterData.data ||
      !this.masterData.data.assetTypes
    ) {
      console.error('❌ ไม่พบข้อมูล assetTypes');
      return 0;
    }

    const selectedAssetType = this.masterData.data.assetTypes.find(
      (item) => item.id == selectedAssetTypeValue
    );

    if (!selectedAssetType) {
      console.error('❌ ไม่พบประเภทข้อมูลที่เลือก');
      return 0;
    }

    console.log('🔍 Fallback: ชื่อประเภทข้อมูล:', selectedAssetType.name);

    // หา config ที่ตรงกับประเภทข้อมูลนี้
    // โดยตรวจสอบว่า totalInput อยู่ในการ์ดที่มี title ตรงกับชื่อประเภทข้อมูล
    for (const config of this.priceCalculationConfigs) {
      const totalInput = document.getElementById(config.totalId);
      if (!totalInput) continue;

      // หาการ์ดที่ totalInput อยู่
      const card = totalInput.closest('.card.custom-card');
      if (!card) continue;

      const cardTitle = card.querySelector('.card-title');
      if (!cardTitle) continue;

      const cardTitleText = cardTitle.textContent.trim();

      // ถ้าชื่อการ์ดตรงกับชื่อประเภทข้อมูล
      if (cardTitleText === selectedAssetType.name) {
        console.log(
          `🎯 Fallback: พบ config ที่ตรงกับ "${selectedAssetType.name}"`
        );

        // คำนวณตรงจาก quantity × price
        const value = this.getTotalValue(config.totalId);
        console.log('📊 Fallback: value =', value);
        return value;
      }
    }

    console.error('❌ Fallback: ไม่พบ config ที่ตรงกับประเภทข้อมูล');
    return 0;
  }

  /**
   * คำนวณจำนวนเงินรวมใหม่สำหรับการ์ดที่กำลังใช้งาน
   */
  recalculateActiveTotal() {
    console.log('🔄 recalculateActiveTotal called');
    let found = false;

    for (const config of this.priceCalculationConfigs) {
      const totalInput = document.getElementById(config.totalId);
      if (!totalInput) {
        console.log(`  - ไม่พบ totalInput: ${config.totalId}`);
        continue;
      }

      if (!this.shouldValidateElement(totalInput)) {
        console.log(
          `  - shouldValidateElement = false สำหรับ: ${config.totalId}`
        );
        continue;
      }

      console.log(`  ✅ กำลังคำนวณ: ${config.totalId}`);
      this.calculateTotal(config);
      found = true;
      break;
    }

    if (!found) {
      console.warn('⚠️ recalculateActiveTotal: ไม่พบ config ที่ใช้งานได้');
    }
  }

  // ========================================
  // SAVE FUNCTIONALITY
  // ========================================

  /**
   * ตั้งค่า save event listeners
   */
  setupSaveEventListeners() {
    console.log('💾 ตั้งค่า save event listeners...');

    // FIX: Use querySelectorAll to handle multiple buttons with same ID (should be unique but fixing here)
    const saveDraftBtns = document.querySelectorAll('[id="save-draft-btn"]');
    const submitApprovalBtns = document.querySelectorAll(
      '[id="submit-approval-btn"]'
    );

    console.log(`📋 Found ${saveDraftBtns.length} save draft buttons`);
    console.log(
      `📋 Found ${submitApprovalBtns.length} submit approval buttons`
    );

    // Attach event to ALL save draft buttons
    saveDraftBtns.forEach((btn, index) => {
      console.log(`  ✅ Attaching saveDraft to button ${index + 1}`);
      btn.addEventListener('click', () => {
        console.log(`💾 Save draft button ${index + 1} clicked`);
        this.saveDraft();
      });
    });

    // Attach event to ALL submit approval buttons
    submitApprovalBtns.forEach((btn, index) => {
      console.log(`  ✅ Attaching submitForApproval to button ${index + 1}`);
      btn.addEventListener('click', () => {
        console.log(`📋 Submit approval button ${index + 1} clicked`);
        this.submitForApproval();
      });
    });

    console.log('✅ Save event listeners ตั้งค่าเสร็จสิ้น');
  }

  /**
   * บันทึกแบบร่าง
   */
  async saveDraft() {
    console.log('💾 === START saveDraft ===');
    console.log('📋 this.masterData:', this.masterData);
    console.log('📋 Button clicked successfully');

    try {
      // Validate สำหรับ Draft (เฉพาะปีงบประมาณ, เวอร์ชั่น, ประเภทข้อมูล)
      console.log('🔍 Calling validateDraft()...');
      const validation = this.validateDraft();
      console.log('✅ validateDraft() result:', validation);

      if (!validation.isValid) {
        await Swal.fire({
          icon: 'warning',
          title: 'ข้อมูลไม่ครบถ้วน',
          html: `<ul style="text-align: left; padding-left: 20px;">
            ${validation.errors.map((error) => `<li>${error}</li>`).join('')}
          </ul>`,
          confirmButtonText: 'ตกลง',
          confirmButtonColor: '#d33'
        });
        return;
      }

      // คำนวณจำนวนเงินรวมล่าสุดก่อนรวบรวมข้อมูล
      this.recalculateActiveTotal();

      // รวบรวมข้อมูลฟอร์ม
      const formData = validation.data;

      // เพิ่มข้อมูลเฉพาะ
      formData.serialNumbers = this.getSerialNumbers();
      const calculatedTotal = this.getActiveTotalValue();
      if (Number.isFinite(calculatedTotal) && calculatedTotal > 0) {
        formData.totalAmount = calculatedTotal;
      }
      formData.status = 'draft';

      console.log('📋 ข้อมูลที่จะบันทึก:', formData);
      console.log('📋 ตรวจสอบ fields สำคัญ:');
      console.log('  - Reason:', formData.Reason);
      console.log('  - equipmentNature:', formData.equipmentNature);
      console.log('  - assetCategory:', formData.assetCategory);
      console.log('  - strategy:', formData.strategy);
      console.log('  - projectProgram:', formData.projectProgram);
      console.log(
        '  - expectedDisbursementDate:',
        formData.expectedDisbursementDate
      );
      console.log('  - multiYearDisbursement:', formData.multiYearDisbursement);

      // แสดง loading
      Swal.fire({
        title: 'กำลังบันทึก...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      // ส่งข้อมูลไปยัง API
      const response = await this.sendToAPI(formData);

      console.log('Resonse save draft', response);

      if (response.status === 'success') {
        await Swal.fire({
          icon: 'success',
          title: 'บันทึกสำเร็จ',
          text: 'บันทึกแบบร่างเรียบร้อยแล้ว',
          confirmButtonText: 'ตกลง',
          confirmButtonColor: '#28a745'
        });

        // Redirect ไปหน้ารายการ
        window.location.href = 'investment-budget-list.php';
      } else {
        throw new Error(response.message || 'เกิดข้อผิดพลาดในการบันทึก');
      }
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการบันทึก:', error);

      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: error.message || 'ไม่สามารถบันทึกได้ กรุณาลองใหม่อีกครั้ง'
      });
    }
  }

  /**
   * ส่งเพื่อขออนุมัติ
   */
  async submitForApproval() {
    console.log('📋 ส่งเพื่อขออนุมัติ...');

    try {
      // Validate สำหรับส่งอนุมัติ (ครบถ้วนทุก fields)
      console.log('🔍 Calling validateForApproval()...');
      const validation = this.validateForApproval();
      console.log('✅ validateForApproval() result:', validation);

      if (!validation.isValid) {
        await Swal.fire({
          icon: 'warning',
          title: 'ข้อมูลไม่ครบถ้วน',
          html: `<ul style="text-align: left; padding-left: 20px;">
            ${validation.errors.map((error) => `<li>${error}</li>`).join('')}
          </ul>`,
          confirmButtonText: 'ตกลง',
          confirmButtonColor: '#d33'
        });
        return;
      }

      // ยืนยันการส่ง
      const confirmation = await Swal.fire({
        title: 'ยืนยันการส่งเพื่อขออนุมัติ',
        text: 'เมื่อส่งแล้วจะไม่สามารถแก้ไขได้ คุณต้องการดำเนินการต่อหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ส่งเพื่อขออนุมัติ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
      });

      if (!confirmation.isConfirmed) return;

      // รวบรวมข้อมูลฟอร์ม (ใช้จาก validation result)
      const formData = validation.data;

      // เพิ่มข้อมูลเฉพาะ
      formData.serialNumbers = this.getSerialNumbers();
      const calculatedTotal = this.getActiveTotalValue();
      if (Number.isFinite(calculatedTotal) && calculatedTotal > 0) {
        formData.totalAmount = calculatedTotal;
      }
      formData.status = 'pending_approval';

      console.log('📋 ข้อมูลที่จะส่งเพื่อขออนุมัติ:', formData);

      // แสดง loading
      Swal.fire({
        title: 'กำลังส่งเพื่อขออนุมัติ...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      // ส่งข้อมูลไปยัง API
      const response = await this.sendToAPI(formData);

      if (response.status === 'success') {
        await Swal.fire({
          icon: 'success',
          title: 'ส่งสำเร็จ',
          text: 'ส่งเพื่อขออนุมัติเรียบร้อยแล้ว',
          confirmButtonText: 'ตกลง',
          confirmButtonColor: '#28a745'
        });

        // Redirect ไปหน้ารายการ
        window.location.href = 'investment-budget-list.php';
        return;
      }

      const errorMessage =
        response.message || 'ไม่สามารถส่งได้ กรุณาลองใหม่อีกครั้ง';
      const [title, detail] = errorMessage.split(/:\s?/, 2);

      await Swal.fire({
        icon: 'warning',
        title: detail ? title : 'ข้อมูลไม่ครบถ้วน',
        text: detail || errorMessage,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#d33'
      });

      return;
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการส่ง:', error);

      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: error.message || 'ไม่สามารถส่งได้ กรุณาลองใหม่อีกครั้ง'
      });
    }
  }

  /**
   * ส่งข้อมูลไปยัง API
   */
  async sendToAPI(formData) {
    const payload = new FormData();
    payload.append('payload', JSON.stringify(formData));

    const attachments = this.collectAttachmentFiles();
    attachments.forEach((file) => {
      payload.append('pdf_attachments[]', file, file.name);
    });

    const userContextPayload = this.getUserContextPayload();
    if (userContextPayload) {
      payload.append('user_context', userContextPayload.user_context);
      if (userContextPayload.user_context_encoding) {
        payload.append(
          'user_context_encoding',
          userContextPayload.user_context_encoding
        );
      }
    }

    const headers = this.getUserContextHeaders();

    const response = await fetch(
      `../controllers/investment_budget/ib_requests_controller.php?action=save_form`,
      {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: payload
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  }

  /**
   * รวบรวมไฟล์เอกสารแนบ PDF จากฟอร์ม
   * @returns {File[]}
   */
  collectAttachmentFiles() {
    const files = [];
    const inputs = document.querySelectorAll('[data-attachment-input]');

    inputs.forEach((input) => {
      if (input.files && input.files.length) {
        Array.from(input.files).forEach((file) => {
          if (file && file.type) {
            files.push(file);
          }
        });
      }
    });

    return files;
  }

  getFormData() {
    console.log('📦 === START getFormData (InvestmentBudgetForm) ===');

    const assetType = this.masterData.getValue('asset-type-select') || '';

    // ดึงค่า totalAmount จาก total-{assetType} field โดยตรง
    const getTotalAmount = () => {
      const totalEl = document.getElementById(`total-${assetType}`);
      console.log(`📊 getTotalAmount: กำลังดึงค่าจาก total-${assetType}`, {
        found: !!totalEl,
        value: totalEl?.value,
        rawValue: totalEl?.getAttribute('data-raw-value')
      });

      if (totalEl) {
        // ลำดับความสำคัญ: data-raw-value > value
        const rawValue = totalEl.getAttribute('data-raw-value');
        if (rawValue) {
          const parsed = parseFloat(rawValue);
          console.log(`✅ ใช้ data-raw-value: ${rawValue} → ${parsed}`);
          return parsed;
        }

        const value = totalEl.value.replace(/,/g, '');
        const parsed = parseFloat(value) || 0;
        console.log(`✅ ใช้ value: ${totalEl.value} → ${parsed}`);
        return parsed;
      }

      // Fallback: ใช้ getActiveTotalValue() สำหรับ assetType ที่มี auto-calculation
      console.warn(`⚠️ ไม่พบ total-${assetType}, ใช้ getActiveTotalValue()`);
      return this.getActiveTotalValue();
    };

    const formData = {
      fiscalYear: this.masterData.getValue('fiscal-year-select'),
      versionPlan: this.masterData.getValue('version-plan-select'),
      assetType,
      assetTypeCode: document.getElementById('asset-type-code')?.value || '',

      // ✅ ใช้ assetType สร้าง id ของแต่ละ field แบบ dynamic
      budgetSource:
        this.masterData.getValue(`budget-source-select-${assetType}`) || '',
      itemName: document.getElementById(`item-name-${assetType}`)?.value || '',
      Description:
        document.getElementById(`description-${assetType}`)?.value || '',
      Reason: document.getElementById(`reason-${assetType}`)?.value || '',
      Quantity: document.getElementById(`quantity-${assetType}`)?.value || '',
      Price: document.getElementById(`price-${assetType}`)?.value || '',
      totalAmount: document.getElementById(`total-${assetType}`)?.value || '',
      equipmentStandards:
        this.masterData.getValue(`equipment-standards-select-${assetType}`) ||
        '',
      equipmentNature:
        this.masterData.getValue(`equipment-setup-select-${assetType}`) || '',
      assetCategory:
        this.masterData.getValue(`asset-category-select-${assetType}`) || '',
      serialNumbers: this.getSerialNumbers(),
      strategy: this.masterData.getValue(`strategy-select-${assetType}`) || '',
      projectProgram:
        document.getElementById(`project-program-${assetType}`)?.value || '',
      expectedDisbursementDate:
        this.masterData.getValue(`expected-disbursement-date-${assetType}`) ||
        '',
      multiYearDisbursement:
        this.masterData.getValue(`disbursement-plan-select-${assetType}`) || '',

      // เฉพาะบาง card ใช้เฉพาะ id 5,6,8 → ตรวจเงื่อนไข assetType
      areaRai:
        (['5', '6'].includes(assetType) &&
          document.getElementById(`area-rai-${assetType}`)?.value) ||
        '',
      projectStartPeriod:
        (['5', '6'].includes(assetType) &&
          this.masterData.getValue(
            `project-start-period-select-${assetType}`
          )) ||
        '',
      projectEndPeriod:
        (['5', '6'].includes(assetType) &&
          this.masterData.getValue(`project-end-period-select-${assetType}`)) ||
        '',
      operationYear:
        (['5', '6'].includes(assetType) &&
          this.masterData.getValue(
            `implementation-year-select-${assetType}`
          )) ||
        '',
      workPeriod:
        (['3', '8', '9'].includes(assetType) &&
          this.masterData.getValue(`installment-work-select-${assetType}`)) ||
        ''
    };
    formData.replacement_asset_numbers = formData.serialNumbers; // สำหรับฟิลด์เก่า
    console.log('📦 === END getFormData (InvestmentBudgetForm) ===');
    console.log('📋 Final formData:', formData);
    return formData;
  }

  /**
   * โหลดข้อมูลเข้าฟอร์ม (สำหรับ edit mode)
   * @param {Object} data - ข้อมูลจาก API
   */
  async loadFormData(data) {
    console.log('📥 โหลดข้อมูลเข้าฟอร์ม...', data);

    try {
      // โหลดข้อมูลหลัก
      if (data.fiscal_year_id) {
        await this.masterData.setValue(
          'fiscal-year-select',
          data.fiscal_year_id
        );
      }
      if (data.version_plan_id) {
        await this.masterData.setValue(
          'version-plan-select',
          data.version_plan_id
        );
      }
      if (data.asset_type_id) {
        await this.masterData.setValue('asset-type-select', data.asset_type_id);
        // Trigger change event เพื่อแสดงการ์ดที่ถูกต้อง
        document
          .getElementById('asset-type-select')
          ?.dispatchEvent(new Event('change'));
      }
      if (data.asset_type_code) {
        const codeField = document.getElementById('asset-type-code');
        if (codeField) codeField.value = data.asset_type_code;
      }

      // รอให้การ์ดแสดงก่อน (delay เล็กน้อย)
      await new Promise((resolve) => setTimeout(resolve, 300));

      // โหลดข้อมูล item - แหล่งงบประมาณ
      if (data.budget_source_id) {
        // หาการ์ดที่แสดงอยู่และ set ค่า
        const visibleCard = Array.from(
          document.querySelectorAll('.card.custom-card')
        ).find(
          (card) =>
            card.style.display !== 'none' &&
            card.querySelector('.budget-source-select')
        );

        if (visibleCard) {
          const budgetSelect = visibleCard.querySelector(
            '.budget-source-select'
          );
          if (budgetSelect) {
            await this.masterData.setValue(
              budgetSelect.id,
              data.budget_source_id
            );
          }
        }
      }

      // โหลดข้อมูล item - รายการ
      if (data.item_name) {
        const itemNameInput = document.querySelector('input[name="item-name"]');
        if (itemNameInput) itemNameInput.value = data.item_name;
      }

      // โหลดข้อมูล item - รายละเอียด
      if (data.description) {
        const descInput = document.querySelector(
          'textarea[name="description"]'
        );
        if (descInput) descInput.value = data.description;
      }

      // โหลดข้อมูล item - เหตุผลความจำเป็น
      if (data.reason) {
        const reasonInput = document.querySelector('textarea[name="reason"]');
        if (reasonInput) reasonInput.value = data.reason;
      }

      // โหลดข้อมูล item - จำนวน
      if (data.quantity) {
        const qtyInput =
          document.getElementById('machinery-quantity') ||
          document.querySelector('input[id^="quantity-"]');
        if (qtyInput) qtyInput.value = data.quantity;
      }

      // โหลดข้อมูล item - ราคาต่อหน่วย
      if (data.unit_price) {
        const priceInput =
          document.getElementById('machinery-price') ||
          document.querySelector('input[id^="price-"]');
        if (priceInput) priceInput.value = data.unit_price;
      }

      // โหลดข้อมูล item - จำนวนเงินรวม
      if (data.total_amount !== undefined && data.total_amount !== null) {
        const totalInput =
          this.getActiveTotalInput() ||
          document.getElementById('machinery-total') ||
          document.querySelector('input[id^="total-"]');
        if (totalInput) {
          const numericValue = Number(
            typeof data.total_amount === 'number'
              ? data.total_amount
              : String(data.total_amount).replace(/[^\d.-]/g, '')
          );
          const safeValue = Number.isNaN(numericValue) ? 0 : numericValue;
          totalInput.value = this.formatNumber(safeValue);
          totalInput.setAttribute('data-raw-value', safeValue);
        }
      }

      // โหลดข้อมูล item - มาตรฐานครุภัณฑ์ (has_standard เป็น ID ของ tb_equipment_standards)
      if (data.has_standard !== null && data.has_standard !== undefined) {
        const standardSelect = document.querySelector(
          'select[id^="equipment-standards-select-"]'
        );
        if (standardSelect) {
          // has_standard เป็น integer ID แล้ว ไม่ต้องแปลง
          await this.masterData.setValue(
            standardSelect.id,
            data.has_standard.toString()
          );
        }
      }

      // โหลดข้อมูล item - ลักษณะครุภัณฑ์
      if (data.asset_nature) {
        const natureSelect = document.querySelector(
          'select[id^="equipment-setup-select-"]'
        );
        if (natureSelect) {
          await this.masterData.setValue(natureSelect.id, data.asset_nature);
        }
      }

      // โหลดข้อมูล item - หมวดสินทรัพย์
      if (data.asset_category) {
        const categorySelect = document.querySelector(
          'select[id^="asset-category-select-"]'
        );
        if (categorySelect) {
          await this.masterData.setValue(
            categorySelect.id,
            data.asset_category
          );
        }
      }

      // โหลดข้อมูล item - หมายเลขครุภัณฑ์ทดแทน (array)
      if (
        data.replacement_asset_numbers &&
        Array.isArray(data.replacement_asset_numbers)
      ) {
        this.loadSerialNumbers(data.replacement_asset_numbers);
      }

      // โหลดข้อมูล item - ยุทธศาสตร์
      if (data.strategy) {
        const strategySelect = document.querySelector(
          'select[id^="strategy-select-"]'
        );
        if (strategySelect) {
          await this.masterData.setValue(strategySelect.id, data.strategy);
        }
      }

      // โหลดข้อมูล item - แผนงาน/โครงการ
      if (data.project_program) {
        const programInput = document.getElementById('project-program');
        if (programInput) programInput.value = data.project_program;
      }

      // โหลดข้อมูล item - วันที่คาดว่าจะเบิกจ่าย
      if (data.expected_disbursement_date) {
        const dateSelect = document.querySelector(
          'select[id^="expected-disbursement-date-"]'
        );
        if (dateSelect) {
          await this.masterData.setValue(
            dateSelect.id,
            data.expected_disbursement_date
          );
        }
      }

      // โหลดข้อมูล item - แผนเบิกจ่าย (multi_year_disbursement เป็น ID ของ tb_disbursement_plan)
      if (
        data.multi_year_disbursement !== null &&
        data.multi_year_disbursement !== undefined
      ) {
        const disbursementSelect = document.querySelector(
          'select[id^="disbursement-plan-select-"]'
        );
        if (disbursementSelect) {
          // multi_year_disbursement เป็น integer ID แล้ว ไม่ต้องแปลง
          await this.masterData.setValue(
            disbursementSelect.id,
            data.multi_year_disbursement.toString()
          );
        }
      }

      // โหลดข้อมูล item - จำนวนไร่
      if (data.area_rai) {
        const areaRaiInput = document.querySelector('input[id^="area-rai-"]');
        if (areaRaiInput) areaRaiInput.value = data.area_rai;
      }

      // โหลดข้อมูล item - ระยะเวลาเริ่มต้นโครงการ
      if (data.project_start_period) {
        const startSelect = document.querySelector(
          'select[id^="project-start-period-select-"]'
        );
        if (startSelect) {
          await this.masterData.setValue(
            startSelect.id,
            data.project_start_period
          );
        }
      }

      // โหลดข้อมูล item - ระยะเวลาสิ้นสุดโครงการ
      if (data.project_end_period) {
        const endSelect = document.querySelector(
          'select[id^="project-end-period-select-"]'
        );
        if (endSelect) {
          await this.masterData.setValue(endSelect.id, data.project_end_period);
        }
      }

      // โหลดข้อมูล item - ปีที่ดำเนินการ
      if (data.operation_year) {
        const yearSelect = document.querySelector(
          'select[id^="implementation-year-select-"]'
        );
        if (yearSelect) {
          await this.masterData.setValue(yearSelect.id, data.operation_year);
        }
      }

      // โหลดข้อมูล item - งานงวด
      if (data.work_period && data.asset_type_id) {
        const workSelect = document.getElementById(
          `installment-work-select-${data.asset_type_id}`
        );
        if (workSelect) {
          await this.masterData.setValue(workSelect.id, data.work_period);
        }
      }

      if (data.project_start_period) {
        const projectStartPeriodsSelect = document.querySelector(
          'select[id^="project-start-period-"]'
        );
        if (projectStartPeriodsSelect) {
          await this.masterData.setValue(
            projectStartPeriodsSelect.id,
            data.project_start_period
          );
        }
      }

      // โหลดไฟล์แนบที่มีอยู่แล้ว
      if (data.attachment_files) {
        this.loadExistingAttachments(data.attachment_files);
      }

      console.log('✅ โหลดข้อมูลเข้าฟอร์มสำเร็จ');
    } catch (error) {
      console.error('❌ เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);
      throw error;
    }
  }

  /**
   * โหลดและแสดงไฟล์แนบที่มีอยู่แล้ว
   */
  loadExistingAttachments(attachmentFiles) {
    console.log('📎 โหลดไฟล์แนบที่มีอยู่:', attachmentFiles);

    let files = [];
    try {
      files =
        typeof attachmentFiles === 'string'
          ? JSON.parse(attachmentFiles)
          : attachmentFiles;
    } catch (e) {
      console.error('❌ ไม่สามารถ parse ไฟล์แนบ:', e);
      return;
    }

    if (!files || files.length === 0) {
      return;
    }

    // หาทุก attachment-list containers
    const containers = document.querySelectorAll('[data-attachment-list]');
    containers.forEach((container) => {
      container.innerHTML = '';

      files.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className =
          'alert alert-secondary d-flex align-items-center justify-content-between mb-2';
        fileItem.innerHTML = `
          <div class="d-flex align-items-center">
            <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
            <a href="${
              file.file_path || file
            }" target="_blank" class="text-decoration-none">
              ${
                file.original_name || file.file_name || 'ไฟล์แนบ ' + (index + 1)
              }
            </a>
          </div>
          <button type="button" class="btn btn-sm btn-danger-light remove-existing-file" data-file-index="${index}">
            <i class="bi bi-dash"></i> ลบรายการ
          </button>
        `;
        container.appendChild(fileItem);
      });
    });

    // เพิ่ม event listener สำหรับปุ่มลบ
    document.querySelectorAll('.remove-existing-file').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        const fileIndex = e.currentTarget.getAttribute('data-file-index');
        this.removeExistingFile(fileIndex, files);
      });
    });
  }

  /**
   * ลบไฟล์แนบที่มีอยู่แล้ว
   */
  removeExistingFile(fileIndex, files) {
    Swal.fire({
      title: 'ยืนยันการลบไฟล์?',
      text: 'คุณต้องการลบไฟล์นี้หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'ลบ',
      cancelButtonText: 'ยกเลิก'
    }).then((result) => {
      if (result.isConfirmed) {
        // ลบไฟล์จาก array
        files.splice(fileIndex, 1);

        // โหลดไฟล์ใหม่
        this.loadExistingAttachments(files);

        Swal.fire('ลบเรียบร้อย!', 'ไฟล์ถูกลบแล้ว', 'success');
      }
    });
  }

  /**
   * โหลดหมายเลขครุภัณฑ์ทดแทนเข้าตาราง
   * @param {Array} serialNumbers - array ของหมายเลขครุภัณฑ์
   */
  loadSerialNumbers(serialNumbers) {
    console.log('📥 โหลดหมายเลขครุภัณฑ์:', serialNumbers);

    if (!serialNumbers || !Array.isArray(serialNumbers)) {
      this.populateSerialNumbers(null, []);
      return;
    }

    const sanitized = serialNumbers
      .flatMap((value) => {
        if (value === null || value === undefined) return [];
        if (Array.isArray(value)) return value;
        return [value];
      })
      .map((value) => this.normalizeSerialValue(value))
      .filter((value) => value !== '');

    const containers = this.getAllReplacementContainers();
    if (containers.length === 0) {
      this.populateSerialNumbers(null, sanitized);
      return;
    }

    containers.forEach((container) => {
      if (sanitized.length) {
        this.writeReplacementCache(container, { serialNumbers: sanitized });
      } else {
        this.writeReplacementCache(container, { serialNumbers: null });
      }

      if (!container.classList.contains('d-none')) {
        this.populateSerialNumbers(container, sanitized);
      }
    });

    if (
      !containers.some((container) => !container.classList.contains('d-none'))
    ) {
      this.populateSerialNumbers(null, sanitized);
    }

    this.setupEquipmentSerialEventListeners();
  }

  /**
   * Escape HTML เพื่อป้องกัน XSS
   */
  escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
  }

  /**
   * ผูก event listeners สำหรับหมายเลขครุภัณฑ์
   */
  attachSerialNumberEventListeners() {
    this.setupEquipmentSerialEventListeners();
  }
}

// ========================================
// INITIALIZATION
// ========================================

// สร้าง global instance
window.investmentBudgetForm = new InvestmentBudgetForm();

// เริ่มต้นเมื่อ DOM พร้อม
document.addEventListener('DOMContentLoaded', function () {
  console.log('📋 Investment Budget Form Handler กำลังโหลด...');

  setTimeout(() => {
    window.investmentBudgetForm.initialize();
  }, 1200); // เริ่มหลัง master data manager
});

// Export สำหรับการใช้งานง่าย
window.InvestmentBudgetForm = {
  instance: window.investmentBudgetForm,
  getFormData: () => window.investmentBudgetForm.getFormData(),
  loadFormData: (data) => window.investmentBudgetForm.loadFormData(data),
  validateForm: () => window.investmentBudgetForm.validateForm(),
  resetForm: () => window.investmentBudgetForm.resetForm(),
  suspendValidation: () => window.investmentBudgetForm.suspendValidation(),
  resumeValidation: () => window.investmentBudgetForm.resumeValidation(),
  runWithValidationSuspended: (callback) =>
    window.investmentBudgetForm.runWithValidationSuspended(callback)
};

console.log('📦 Investment Budget Form Handler loaded');
console.log('💡 ใช้ InvestmentBudgetForm.getFormData() เพื่อทดสอบ');
