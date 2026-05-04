// ===== Helper: แปลงวันที่เป็น พ.ศ. (dd/mm/yyyy HH:MM) =====
function convertToBuddhistEra(dateInput) {
  const d = dateInput instanceof Date ? dateInput : new Date(dateInput);
  if (isNaN(d)) return '';
  const y = d.getFullYear() + 543;
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  const hh = String(d.getHours()).padStart(2, '0');
  const mi = String(d.getMinutes()).padStart(2, '0');
  return `${dd}/${mm}/${y} ${hh}:${mi}`;
}

// ===== ฟังก์ชันกลาง: Flatpickr ไทย + แสดงปี พ.ศ. =====
// ใช้ได้ทั้ง selector string หรือ element จริง ๆ
function setupThaiBEFlatpickr(target, userOptions = {}) {
  if (typeof flatpickr !== 'function') {
    console.warn('flatpickr ยังไม่ถูกโหลด');
    return null;
  }
  const el =
    typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) {
    console.warn('ไม่พบ element ของ flatpickr:', target);
    return null;
  }

  // Locale ไทย
  const thaiLocale = {
    firstDayOfWeek: 1,
    weekdays: {
      shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
      longhand: [
        'อาทิตย์',
        'จันทร์',
        'อังคาร',
        'พุธ',
        'พฤหัสบดี',
        'ศุกร์',
        'เสาร์'
      ]
    },
    months: {
      shorthand: [
        'ม.ค.',
        'ก.พ.',
        'มี.ค.',
        'เม.ย.',
        'พ.ค.',
        'มิ.ย.',
        'ก.ค.',
        'ส.ค.',
        'ก.ย.',
        'ต.ค.',
        'พ.ย.',
        'ธ.ค.'
      ],
      longhand: [
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
      ]
    }
  };

  // ฟังก์ชันอัปเดตข้อความใน altInput ให้เป็นปี พ.ศ.
  const updateThaiYearInput = (instance) => {
    const altInput = instance.altInput;
    if (!altInput) return;
    if (instance.selectedDates.length > 0) {
      const date = instance.selectedDates[0];
      const dd = String(date.getDate()).padStart(2, '0');
      const mm = String(date.getMonth() + 1).padStart(2, '0');
      const yyyyBE = date.getFullYear() + 543;
      altInput.value = `${dd}-${mm}-${yyyyBE}`;
    }
  };

  // ฟังก์ชันอัปเดตปีใน header ของปฏิทินให้เป็น พ.ศ.
  const updateThaiYearCalendar = (instance) => {
    const yEl = instance.currentYearElement;
    if (!yEl) return;
    const current = parseInt(yEl.value, 10);
    if (!isNaN(current) && current < 2400) {
      yEl.value = current + 543;
    }
  };

  // รวม options พื้นฐาน + ของผู้ใช้ โดยต่อ event hooks ให้ทำงานร่วมกัน
  const baseOptions = {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd-m-Y', // ใช้ altInput + เราไปแทนค่าด้วยปี พ.ศ. เอง
    locale: thaiLocale,
    onReady(selectedDates, dateStr, instance) {
      updateThaiYearInput(instance);
      updateThaiYearCalendar(instance);
    },
    onOpen(selectedDates, dateStr, instance) {
      updateThaiYearCalendar(instance);
    },
    onChange(selectedDates, dateStr, instance) {
      updateThaiYearInput(instance);
    },
    onMonthChange(selectedDates, dateStr, instance) {
      updateThaiYearCalendar(instance);
    },
    onYearChange(selectedDates, dateStr, instance) {
      updateThaiYearCalendar(instance);
    },
    // เมื่อผู้ใช้พิมพ์ปีเองในช่องปี ให้พยายามคง พ.ศ. ไว้
    onValueUpdate(selectedDates, dateStr, instance) {
      updateThaiYearInput(instance);
      updateThaiYearCalendar(instance);
    }
  };

  // merge: ถ้า userOptions มี hooks ให้จับรวมเป็นอาเรย์
  const hookNames = [
    'onReady',
    'onOpen',
    'onChange',
    'onMonthChange',
    'onYearChange',
    'onValueUpdate'
  ];
  const merged = { ...baseOptions, ...userOptions };
  for (const h of hookNames) {
    const baseHook = Array.isArray(baseOptions[h])
      ? baseOptions[h]
      : baseOptions[h]
      ? [baseOptions[h]]
      : [];
    const userHook = Array.isArray(userOptions[h])
      ? userOptions[h]
      : userOptions[h]
      ? [userOptions[h]]
      : [];
    if (baseHook.length || userHook.length) {
      merged[h] = [...baseHook, ...userHook];
    }
  }
  // merge locale (ป้องกัน locale ผู้ใช้ไปทับทั้งหมด)
  if (userOptions.locale) {
    merged.locale = { ...thaiLocale, ...userOptions.locale };
  }

  const instance = flatpickr(el, merged);

  // helper methods ที่อาจมีประโยชน์
  return {
    instance,
    setDateBE(date) {
      instance.setDate(date, true);
    }, // รับ Date/ISO, จะรีเฟรช altInput เป็น พ.ศ.
    getAltValue() {
      return instance.altInput ? instance.altInput.value : '';
    },
    destroy() {
      instance.destroy();
    }
  };
}

// ===== ฟังก์ชันกลาง: Choices.js =====
function setupChoicesSelect(target, options = {}) {
  if (typeof Choices !== 'function') {
    console.warn('Choices.js ยังไม่ถูกโหลด');
    return null;
  }
  const el =
    typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) {
    console.warn('ไม่พบ element ของ Choices:', target);
    return null;
  }
  const instance = new Choices(el, { shouldSort: false, ...options });

  return {
    instance,
    clear() {
      // พยายามเคลียร์ทั้งค่าและ trigger change
      instance.removeActiveItems();
      if (el) {
        el.value = '';
        el.dispatchEvent(new Event('change', { bubbles: true }));
      }
    },
    set(value) {
      instance.setChoiceByValue(String(value ?? ''));
      if (el) el.dispatchEvent(new Event('change', { bubbles: true }));
    },
    destroy() {
      instance.destroy();
    }
  };
}
