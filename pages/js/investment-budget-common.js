/**
 * Investment Budget Common Utilities
 *
 * Shared utility functions used across investment budget modules
 * to avoid code duplication and maintain consistency.
 *
 * Usage: Include this file before other investment-budget-*.js files
 */

const InvestmentBudgetCommon = {
  /**
   * Format currency with Thai locale
   * Returns '-' for zero, null, undefined, or empty values
   *
   * @param {number|string|null|undefined} amount - Amount to format
   * @returns {string} Formatted currency or '-'
   */
  formatCurrency(amount) {
    // Check for null/undefined/empty first before converting to number
    if (amount === null || amount === undefined || amount === '') {
      return '-';
    }

    // Handle string '-' pass-through
    if (amount === '-') {
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
  },

  /**
   * Format quantity value
   * Returns '-' for zero, null, undefined values
   *
   * @param {number|string|null|undefined} quantity - Quantity to format
   * @returns {string} Formatted quantity or '-'
   */
  formatQuantity(quantity) {
    if (quantity === null || quantity === undefined) {
      return '-';
    }

    const qty = parseInt(quantity, 10);
    if (Number.isNaN(qty) || qty === 0) {
      return '-';
    }

    return qty.toString();
  },

  /**
   * Format quantity value for Excel export
   * Returns 1 for null, undefined, empty, or zero values
   * Used specifically for Excel exports where quantity must have a value
   *
   * @param {number|string|null|undefined} quantity - Quantity to format
   * @returns {Object} { quantity: number, wasEmpty: boolean }
   */
  formatQuantityForExport(quantity) {
    if (quantity === null || quantity === undefined || quantity === '') {
      return { quantity: 1, wasEmpty: true };
    }

    const qty = parseInt(quantity, 10);
    if (Number.isNaN(qty) || qty === 0) {
      return { quantity: 1, wasEmpty: true };
    }

    return { quantity: qty, wasEmpty: false };
  },

  /**
   * Format unit price for Excel export
   * If quantity was empty and unit_price is empty, use total_amount
   * Otherwise return the actual unit_price or 0
   * Always returns with 2 decimal places
   *
   * @param {number|string|null|undefined} unitPrice - Unit price
   * @param {number|string|null|undefined} totalAmount - Total amount
   * @param {boolean} quantityWasEmpty - Whether quantity was empty
   * @returns {number} Formatted unit price with 2 decimals
   */
  formatUnitPriceForExport(unitPrice, totalAmount, quantityWasEmpty) {
    let result = 0;

    // If quantity was empty and no unit price provided, use total amount
    if (quantityWasEmpty && (unitPrice === null || unitPrice === undefined || unitPrice === '')) {
      const total = parseFloat(totalAmount);
      result = Number.isNaN(total) ? 0 : total;
    } else {
      // Otherwise use the actual unit price
      const price = parseFloat(unitPrice);
      result = Number.isNaN(price) ? 0 : price;
    }

    // Return with 2 decimal places
    return parseFloat(result.toFixed(2));
  },

  /**
   * Format amount for Excel export with 2 decimal places
   *
   * @param {number|string|null|undefined} amount - Amount to format
   * @returns {number} Formatted amount with 2 decimals
   */
  formatAmountForExport(amount) {
    const value = parseFloat(amount);
    if (Number.isNaN(value)) {
      return 0.00;
    }
    return parseFloat(value.toFixed(2));
  },

  /**
   * Format unit price
   * Returns '-' for zero, null, undefined values
   * Otherwise returns the numeric value for further formatting
   *
   * @param {number|string|null|undefined} unitPrice - Unit price to check
   * @returns {string|number} '-' or numeric value
   */
  formatUnitPrice(unitPrice) {
    if (unitPrice === null || unitPrice === undefined) {
      return '-';
    }

    const price = parseFloat(unitPrice);
    if (Number.isNaN(price) || price === 0) {
      return '-';
    }

    return price;
  },

  /**
   * Format total amount
   * Returns '-' for zero, null, undefined values
   * Otherwise returns the numeric value for further formatting
   *
   * @param {number|string|null|undefined} totalAmount - Total amount to check
   * @returns {string|number} '-' or numeric value
   */
  formatTotalAmount(totalAmount) {
    if (totalAmount === null || totalAmount === undefined) {
      return '-';
    }

    const amount = parseFloat(totalAmount);
    if (Number.isNaN(amount) || amount === 0) {
      return '-';
    }

    return amount;
  },

  /**
   * Format date string to Thai Buddhist calendar format
   *
   * @param {string} dateString - ISO date string
   * @returns {string} Formatted date or '-'
   */
  formatDate(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
      return '-';
    }

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543; // Convert to Buddhist calendar

    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
  },

  /**
   * Format date to short format (DD-MM-YYYY)
   *
   * @param {string} dateString - ISO date string
   * @returns {string} Formatted date or '-'
   */
  formatDateShort(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
      return '-';
    }

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear() + 543;

    return `${day}-${month}-${year}`;
  },

  /**
   * Escape HTML special characters to prevent XSS
   *
   * @param {*} value - Value to escape
   * @returns {string} Escaped string
   */
  escapeHtml(value) {
    if (value === null || value === undefined) {
      return '';
    }

    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  },

  /**
   * Escape value for use in HTML attributes
   *
   * @param {*} value - Value to escape
   * @returns {string} Escaped string
   */
  escapeAttr(value) {
    if (value === null || value === undefined) {
      return '';
    }

    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  },

  /**
   * Get status badge HTML
   *
   * @param {Object} request - Request object
   * @returns {string} Status badge HTML
   */
  getStatusBadge(request) {
    const status = (request.status || '').toLowerCase();
    const pendingBadgeClass = 'bg-warning text-white';

    if (status.startsWith('pending_')) {
      const stage = request.current_stage || status.replace('pending_', '');
      const label = request.current_stage_label || this.getStageLabel(stage);
      const isCurrent =
        request.can_approve === true || request.is_current_stage === true;
      const badgeClass = isCurrent ? pendingBadgeClass : 'bg-info text-white';

      return `<span class="badge rounded-pill ${badgeClass}">รออนุมัติ${
        label ? ` (${label})` : ''
      }</span>`;
    }

    switch (status) {
      case 'approved':
        return '<span class="badge rounded-pill bg-success text-white">อนุมัติแล้ว</span>';
      case 'rejected':
        return '<span class="badge rounded-pill bg-danger text-white">ปฏิเสธ</span>';
      case 'draft':
        return '<span class="badge rounded-pill bg-secondary text-white">แบบร่าง</span>';
      case 'submitted':
        return '<span class="badge rounded-pill bg-primary text-white">ส่งคำขอแล้ว</span>';
      default:
        return `<span class="badge rounded-pill bg-light text-dark">${status}</span>`;
    }
  },

  /**
   * Get stage label in Thai
   *
   * @param {string} stage - Stage code
   * @returns {string} Stage label
   */
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
  },

  /**
   * Get SAP status badge HTML
   *
   * @param {string} sapStatus - SAP sync status
   * @returns {string} SAP status badge HTML
   */
  getSapStatusBadge(sapStatus) {
    switch (sapStatus) {
      case 'success':
        return '<span class="badge rounded-pill bg-success text-white">Synced</span>';
      case 'error':
        return '<span class="badge rounded-pill bg-danger text-white">Error</span>';
      case 'not_synced':
        return '<span class="badge rounded-pill bg-warning text-white">Not Synced</span>';
      default:
        return '<span class="badge rounded-pill bg-secondary text-white">-</span>';
    }
  },

  /**
   * Convert fiscal year from Buddhist to Gregorian calendar
   *
   * @param {string} fiscalYearName - Fiscal year name (e.g., "2568" or "ปี 2568")
   * @returns {string} Gregorian year or '-'
   */
  convertFiscalYearToAD(fiscalYearName) {
    if (!fiscalYearName) return '-';

    // Extract year (e.g., "2568" or "ปี 2568")
    const match = fiscalYearName.match(/(\d{4})/);
    if (match) {
      const buddhistYear = parseInt(match[1]);
      const gregorianYear = buddhistYear - 543;
      return String(gregorianYear);
    }

    return fiscalYearName;
  },

  /**
   * Show error message in table body
   *
   * @param {string} tbodyId - Table body element ID
   * @param {string} message - Error message
   * @param {number} colspan - Number of columns to span
   */
  showTableError(tbodyId, message, colspan = 12) {
    const tbody = document.getElementById(tbodyId);
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="${colspan}" class="text-center text-danger">
            <i class="ri-error-warning-line fs-18 mb-2"></i>
            <p class="mb-0">${this.escapeHtml(message)}</p>
          </td>
        </tr>
      `;
    }
  },

  /**
   * Show loading message in table body
   *
   * @param {string} tbodyId - Table body element ID
   * @param {string} message - Loading message
   * @param {number} colspan - Number of columns to span
   */
  showTableLoading(tbodyId, message = 'กำลังโหลดข้อมูล...', colspan = 12) {
    const tbody = document.getElementById(tbodyId);
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="${colspan}" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-2">${this.escapeHtml(message)}</p>
          </td>
        </tr>
      `;
    }
  },

  /**
   * Show empty message in table body
   *
   * @param {string} tbodyId - Table body element ID
   * @param {string} message - Empty message
   * @param {number} colspan - Number of columns to span
   */
  showTableEmpty(tbodyId, message = 'ไม่พบข้อมูล', colspan = 12) {
    const tbody = document.getElementById(tbodyId);
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="${colspan}" class="text-center text-muted">
            <i class="ri-inbox-line fs-18 mb-2"></i>
            <p class="mb-0">${this.escapeHtml(message)}</p>
          </td>
        </tr>
      `;
    }
  },

  /**
   * Format number with thousand separators
   *
   * @param {number|string} num - Number to format
   * @returns {string} Formatted number
   */
  formatNumber(num) {
    if (num === null || num === undefined || num === '') {
      return '-';
    }

    const value = Number(num);
    if (Number.isNaN(value)) {
      return '-';
    }

    return new Intl.NumberFormat('th-TH').format(value);
  },

  /**
   * Validate required fields in a form
   *
   * @param {Object} data - Form data object
   * @param {Array<string>} requiredFields - Array of required field names
   * @returns {Object} { isValid: boolean, missingFields: Array<string> }
   */
  validateRequiredFields(data, requiredFields) {
    const missingFields = [];

    requiredFields.forEach((field) => {
      if (
        data[field] === null ||
        data[field] === undefined ||
        data[field] === ''
      ) {
        missingFields.push(field);
      }
    });

    return {
      isValid: missingFields.length === 0,
      missingFields
    };
  },

  /**
   * Debounce function execution
   *
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in milliseconds
   * @returns {Function} Debounced function
   */
  debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Format number with commas for display (real-time input formatting)
   * Preserves cursor position during typing
   *
   * @param {string} value - Input value to format
   * @param {number} decimals - Number of decimal places (default: 2)
   * @returns {string} Formatted number with commas
   */
  formatNumberWithCommas(value, decimals = 2) {
    if (!value || value === '') return '';

    // Remove all non-numeric characters except dots and minus
    let cleaned = value.replace(/[^\d.-]/g, '');

    // Handle multiple dots - keep only the first one
    const parts = cleaned.split('.');
    if (parts.length > 2) {
      cleaned = parts[0] + '.' + parts.slice(1).join('');
    }

    // Split into integer and decimal parts
    const [integerPart, decimalPart] = cleaned.split('.');

    // Format integer part with commas
    let formatted = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Add decimal part if exists
    if (decimalPart !== undefined) {
      // Limit decimal places
      const limitedDecimals = decimalPart.slice(0, decimals);
      formatted += '.' + limitedDecimals;
    }

    return formatted;
  },

  /**
   * Remove commas from formatted number to get raw numeric value
   *
   * @param {string} value - Formatted number string
   * @returns {number} Raw numeric value
   */
  parseFormattedNumber(value) {
    if (!value || value === '') return 0;
    const cleaned = value.replace(/,/g, '');
    const parsed = parseFloat(cleaned);
    return Number.isNaN(parsed) ? 0 : parsed;
  },

  /**
   * Setup real-time comma formatting for price input fields
   * Usage: Call this function with a selector for price inputs
   *
   * @param {string} selector - CSS selector for price input fields (e.g., '[id^="price-"]')
   * @param {number} decimals - Number of decimal places (default: 2)
   */
  setupPriceFormatting(selector, decimals = 2) {
    const inputs = document.querySelectorAll(selector);

    inputs.forEach((input) => {
      // Change input type to text to support selection range
      if (input.type === 'number') {
        input.type = 'text';
        input.setAttribute('inputmode', 'decimal');
        input.setAttribute('pattern', '[0-9,.]*');
      }

      // Format on input (real-time)
      input.addEventListener('input', function () {
        const cursorPosition = this.selectionStart;
        const oldValue = this.value;

        // Format the value
        const formattedValue = InvestmentBudgetCommon.formatNumberWithCommas(
          oldValue,
          decimals
        );

        // Update value
        this.value = formattedValue;

        // Calculate new cursor position
        // Count digits before cursor (excluding commas and other chars)
        const oldBeforeCursor = oldValue.substring(0, cursorPosition);
        const digitsBeforeCursor = oldBeforeCursor.replace(/[^\d]/g, '').length;

        // Find position in new value where we have the same number of digits
        let newCursorPosition = 0;
        let digitCount = 0;
        for (let i = 0; i < formattedValue.length; i++) {
          if (/\d/.test(formattedValue[i])) {
            digitCount++;
            if (digitCount === digitsBeforeCursor) {
              newCursorPosition = i + 1;
              break;
            }
          }
        }

        // If we didn't find enough digits, put cursor at end
        if (digitCount < digitsBeforeCursor) {
          newCursorPosition = formattedValue.length;
        }

        // Set cursor position (safe for text inputs)
        try {
          this.setSelectionRange(newCursorPosition, newCursorPosition);
        } catch (e) {
          // Ignore error if input type doesn't support selection
          console.warn('Cannot set selection range on this input type');
        }
      });

      // Format on blur (ensure proper format)
      input.addEventListener('blur', function () {
        if (this.value === '') return;

        const rawValue = InvestmentBudgetCommon.parseFormattedNumber(this.value);
        if (rawValue === 0) {
          this.value = '';
          return;
        }

        // Format with exact decimal places
        const formatted = new Intl.NumberFormat('th-TH', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        }).format(rawValue);

        this.value = formatted;
      });

      // Store raw value in data attribute for easy access
      input.addEventListener('change', function () {
        const rawValue = InvestmentBudgetCommon.parseFormattedNumber(this.value);
        this.setAttribute('data-raw-value', rawValue);
      });
    });
  },

  /**
   * Get raw numeric value from a formatted input field
   *
   * @param {HTMLInputElement|string} inputOrSelector - Input element or selector
   * @returns {number} Raw numeric value
   */
  getRawValue(inputOrSelector) {
    const input =
      typeof inputOrSelector === 'string'
        ? document.querySelector(inputOrSelector)
        : inputOrSelector;

    if (!input) return 0;

    // Try to get from data attribute first
    const dataValue = input.getAttribute('data-raw-value');
    if (dataValue !== null) {
      const parsed = parseFloat(dataValue);
      return Number.isNaN(parsed) ? 0 : parsed;
    }

    // Otherwise parse from value
    return InvestmentBudgetCommon.parseFormattedNumber(input.value);
  }
};

// Make it available globally
window.InvestmentBudgetCommon = InvestmentBudgetCommon;

console.log('✅ Investment Budget Common utilities loaded');
