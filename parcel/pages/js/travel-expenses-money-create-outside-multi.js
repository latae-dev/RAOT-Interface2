import { getAllParams } from "./withdraw_parameter.js";
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';

const fund_all = await getAllFund();
const project_all = await getAllProject();
initselectForeign($('#advanceFrm'));
function initselectForeign(form) {
  (async () => {
    try {
      const $form = $(form);
      const $is_foreign = $form.find('.is_foreign');
      if ($is_foreign.val() == 0) {
        $($form).find('#flexRadioDefault3_start').attr('disabled', true).prop('checked', false);
        $($form).find('#flexRadioDefault3_end').attr('disabled', true).prop('checked', false);
        $($form).find('#flexRadioDefault1_start').attr('disabled', false);
        $($form).find('#flexRadioDefault2_start').attr('disabled', false);
        $($form).find('#flexRadioDefault1_end').attr('disabled', false);
        $($form).find('#flexRadioDefault2_end').attr('disabled', false);
      } else if ($is_foreign.val() == 1) {
        $($form).find('#flexRadioDefault3_start').attr('disabled', false).prop('checked', true);
        $($form).find('#flexRadioDefault3_end').attr('disabled', false).prop('checked', true);
        $($form).find('#flexRadioDefault1_start').attr('disabled', true).prop('checked', false);
        $($form).find('#flexRadioDefault2_start').attr('disabled', true).prop('checked', false);
        $($form).find('#flexRadioDefault1_end').attr('disabled', true).prop('checked', false);
        $($form).find('#flexRadioDefault2_end').attr('disabled', true).prop('checked', false);
      }
      $($is_foreign).on('change', function () {
        let val = $(this).val();
        if (val == 0) {
          $($form).find('#flexRadioDefault3_start').attr('disabled', true).prop('checked', false);
          $($form).find('#flexRadioDefault3_end').attr('disabled', true).prop('checked', false);
          $($form).find('#flexRadioDefault1_start').attr('disabled', false);
          $($form).find('#flexRadioDefault2_start').attr('disabled', false);
          $($form).find('#flexRadioDefault1_end').attr('disabled', false);
          $($form).find('#flexRadioDefault2_end').attr('disabled', false);
        } else if (val == 1) {
          $($form).find('#flexRadioDefault3_start').attr('disabled', false).prop('checked', true);
          $($form).find('#flexRadioDefault3_end').attr('disabled', false).prop('checked', true);
          $($form).find('#flexRadioDefault1_start').attr('disabled', true).prop('checked', false);
          $($form).find('#flexRadioDefault2_start').attr('disabled', true).prop('checked', false);
          $($form).find('#flexRadioDefault1_end').attr('disabled', true).prop('checked', false);
          $($form).find('#flexRadioDefault2_end').attr('disabled', true).prop('checked', false);
        }
      });
    } catch (err) {
      Swal.fire({
        title: "เกิดข้อผิดพลาด",
        text: "ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
        icon: "error"
      });
    }
  })();
}
initselect2($('#advanceFrm'));
function initselect2(form) {
  (async () => {
    try {
      const $form = $(form);
      const $fund_select = $form.find('.fund-select');
      const $project_select = $form.find('.project-select');

      // Destroy select2 เฉพาะในฟอร์มนี้
      $fund_select.each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
          $(this).select2('destroy');
        }
      });
      $project_select.each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
          $(this).select2('destroy');
        }
      });

      // โหลดข้อมูลเงินทุน
      const fundSource = fund_all.fund_lists || [];
      if (!Array.isArray(fundSource) || fundSource.length === 0) {
        Swal.fire({
          title: "เกิดข้อผิดพลาด",
          text: "ไม่พบข้อมูลหน่วยนับ กรุณาติดต่อผู้ดูแลระบบ",
          icon: "error"
        });
        return;
      }

      // สร้าง options สำหรับเงินทุน
      const fundOptions = [
        { id: '', text: 'กรุณาเลือกเงินทุน', disabled: false },
        ...fundSource.map(fund_lists => ({
          id: fund_lists.id,
          text: fund_lists.has_project == 0
            ? `${fund_lists.fund_name} <span style="color:red;">*ไม่พบโครงการ</span>`
            : fund_lists.fund_name,
          disabled: fund_lists.has_project == 0
        }))
      ];

      $fund_select.empty().select2({
        data: fundOptions,
        escapeMarkup: function (markup) { return markup; }, // ให้ HTML แสดงผล
        templateResult: function (data) {
          return $('<span>' + data.text + '</span>');
        },
        templateSelection: function (data) {
          return $('<span>' + data.text + '</span>');
        }
      });

      // โหลดโครงการเริ่มต้น (ถ้ามี)
      let initialFundId = $fund_select.val();
      let initialProjectList = [];
      if (initialFundId) {
        const project_all = await getAllProject(initialFundId);
        initialProjectList = project_all.project_lists || [];
      } else if (typeof project_all === "object" && Array.isArray(project_all.project_lists)) {
        initialProjectList = project_all.project_lists;
      }

      function updateProjectSelect(projectList) {
        $project_select.empty();
        const projectOptions = [
          { id: '', text: 'กรุณาเลือกโครงการ', disabled: true },
          ...projectList.map(project_lists => ({
            id: project_lists.id,
            text: project_lists.project_name,
          }))
        ];
        $.each(projectOptions, function (index, project) {
          $project_select.append($('<option></option>').attr('value', project.id).text(project.text));
        });
        $project_select.val('').trigger('change');
      }

      $project_select.empty().select2();
      updateProjectSelect(initialProjectList);

      $fund_select.off('change.initselect2').on('change.initselect2', async function () {
        const fund_id = $(this).val();
        const project_all = await getAllProject(fund_id);
        const projectSource = project_all.project_lists || [];
        updateProjectSelect(projectSource);
      });

    } catch (err) {
      Swal.fire({
        title: "เกิดข้อผิดพลาด",
        text: "ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
        icon: "error"
      });
    }
  })();
}
const buildRelativeUrl = (path, params = {}) => {
  const url = new URL(path, import.meta.url);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      url.searchParams.set(key, value);
    }
  });
  return url.toString();
};

const userDropdownManager = (() => {
  let users = null;
  let usersById = new Map();
  let loadError = false;

  const getFullName = (user) => {
    const first = (user?.user_fname || "").trim();
    const last = (user?.user_lname || "").trim();
    return `${first} ${last}`.trim();
  };

  const getPlaceholderText = () => {
    if (loadError) return "ไม่สามารถโหลดรายชื่อได้";
    if (Array.isArray(users) && users.length === 0) return "ไม่พบบัญชีผู้ใช้";
    return "กรุณาเลือกชื่อ-นามสกุล";
  };

  const updateFields = (form, select) => {
    const user = usersById.get(select.value);
    const fullName = user ? getFullName(user) : "";
    const positionInput = form.querySelector('[name="position_name_expenses"]');
    const levelInput = form.querySelector('[name="level_name_expenses"]');
    const affiliationInput = form.querySelector(
      '[name="affiliation_name_expenses"]'
    );
    const hiddenNameInput = form.querySelector(
      '[name="full_name_text_expenses"]'
    );

    if (hiddenNameInput) hiddenNameInput.value = fullName;
    if (positionInput) positionInput.value = user?.position_name || "";
    if (levelInput) levelInput.value = user?.level_name || "";
    if (affiliationInput)
      affiliationInput.value = user?.depart_name || "";
  };

  const populateSelect = (form, select) => {
    if (!select) return;

    const previousValue = select.value;
    select.innerHTML = "";

    const placeholderOption = document.createElement("option");
    placeholderOption.value = "";
    placeholderOption.textContent = getPlaceholderText();
    select.appendChild(placeholderOption);

    if (!Array.isArray(users) || users.length === 0) {
      select.value = "";
      select.disabled = true;
      updateFields(form, select);
      return;
    }

    users.forEach((user) => {
      const userId = user?.id;
      if (userId === undefined || userId === null) return;
      const option = document.createElement("option");
      option.value = String(userId);
      option.textContent = getFullName(user) || user.user_code || "-";
      select.appendChild(option);
    });

    if (previousValue && usersById.has(previousValue)) {
      select.value = previousValue;
    } else {
      select.value = "";
    }

    select.disabled = false;
    updateFields(form, select);
  };

  const initForm = (form) => {
    if (!form) return;
    const select = form.querySelector(".user-select");
    if (!select) return;

    if (!select.dataset.userSelectBound) {
      select.addEventListener("change", () => {
        updateFields(form, select);
        window.formsSummaryManager?.update();
      });
      select.dataset.userSelectBound = "true";
    }

    if (!Array.isArray(users) && !loadError) return;

    populateSelect(form, select);
    window.formsSummaryManager?.update();
  };

  const refreshAllForms = () => {
    document
      .querySelectorAll("form#advanceFrm")
      .forEach((form) => initForm(form));
  };

  return {
    setUsers(data) {
      const list = Array.isArray(data)
        ? data.filter(
          (item) => item && item.id !== undefined && item.id !== null
        )
        : [];
      users = list;
      loadError = false;
      usersById = new Map(list.map((user) => [String(user.id), user]));
      refreshAllForms();
    },
    setLoadError() {
      users = [];
      loadError = true;
      usersById = new Map();
      refreshAllForms();
    },
    initForm,
  };
})();

window.travelExpenseUserDropdown = userDropdownManager;

const THAI_FLATPICKR_LOCALE = {
  firstDayOfWeek: 1,
  weekdays: {
    shorthand: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส"],
    longhand: [
      "อาทิตย์",
      "จันทร์",
      "อังคาร",
      "พุธ",
      "พฤหัสบดี",
      "ศุกร์",
      "เสาร์",
    ],
  },
  months: {
    shorthand: [
      "ม.ค.",
      "ก.พ.",
      "มี.ค.",
      "เม.ย.",
      "พ.ค.",
      "มิ.ย.",
      "ก.ค.",
      "ส.ค.",
      "ก.ย.",
      "ต.ค.",
      "พ.ย.",
      "ธ.ค.",
    ],
    longhand: [
      "มกราคม",
      "กุมภาพันธ์",
      "มีนาคม",
      "เมษายน",
      "พฤษภาคม",
      "มิถุนายน",
      "กรกฎาคม",
      "สิงหาคม",
      "กันยายน",
      "ตุลาคม",
      "พฤศจิกายน",
      "ธันวาคม",
    ],
  },
};

const PLAN_FIELD_KEYS = ["plan_id", "plan_number", "plan_date", "plan_total"];
const planFieldState = PLAN_FIELD_KEYS.reduce((accumulator, key) => {
  accumulator[key] = "";
  return accumulator;
}, {});
let planFieldStateInitialized = false;
let defaultRequesterApplied = false;

const applyDefaultRequester = (currentUser, usersList) => {
  if (defaultRequesterApplied) return;
  if (!currentUser || !Array.isArray(usersList)) return;

  const userCode = currentUser.user_code;
  if (!userCode) return;

  const matchingUser = usersList.find(
    (item) => item?.user_code && item.user_code === userCode
  );
  if (!matchingUser || matchingUser.id === undefined || matchingUser.id === null)
    return;

  const baseForm = document.querySelector("form#advanceFrm");
  const select = baseForm?.querySelector(".user-select");
  if (!select || select.value) {
    defaultRequesterApplied = true;
    return;
  }

  const desiredValue = String(matchingUser.id);
  const hasOption = Array.from(select.options || []).some(
    (option) => option.value === desiredValue
  );
  if (!hasOption) return;

  select.value = desiredValue;
  select.dispatchEvent(new Event("change", { bubbles: true }));
  defaultRequesterApplied = true;
};

const setPlanFieldState = (values = {}) => {
  let updated = false;
  PLAN_FIELD_KEYS.forEach((key) => {
    if (Object.prototype.hasOwnProperty.call(values, key)) {
      const value = values[key];
      planFieldState[key] =
        value !== undefined && value !== null ? String(value) : "";
      updated = true;
    }
  });

  if (!updated) return;
  planFieldStateInitialized = true;
  applyPlanFieldsToAllForms();
  applyPlanFieldsToDisplay();
  window.formsSummaryManager?.update();
};



const capturePlanFieldStateFromForm = (form) => {
  if (!form) return;
  const captured = {};
  PLAN_FIELD_KEYS.forEach((key) => {
    const input = form.querySelector(`[name="${key}"]`);
    captured[key] = input?.value ?? "";
  });
  setPlanFieldState(captured);
};

const ensurePlanFieldStateInitialized = () => {
  if (planFieldStateInitialized) return;
  const baseForm = document.querySelector("form#advanceFrm");
  if (!baseForm) return;
  capturePlanFieldStateFromForm(baseForm);
};

const applyPlanFieldsToForm = (form) => {
  if (!form) return;
  ensurePlanFieldStateInitialized();
  PLAN_FIELD_KEYS.forEach((key) => {
    const input = form.querySelector(`[name="${key}"]`);
    if (input) {
      input.value = planFieldState[key] ?? "";
    }
  });
};

function applyPlanFieldsToAllForms() {
  document.querySelectorAll("form#advanceFrm").forEach((form) => {
    applyPlanFieldsToForm(form);
  });
}

const applyPlanFieldsToDisplay = () => {
  const { plan_number, plan_date, plan_total } = planFieldState;

  const numEl = document.getElementById("plan_number_display");
  if (numEl) numEl.value = plan_number || "";

  const dateEl = document.getElementById("plan_date_display");
  if (dateEl) dateEl.value = plan_date || "";

  const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return "0.00";
    const num = parseFloat(String(amount).replace(/,/g, "").trim());
    if (isNaN(num)) return "0.00";
    return num.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  const totalEl = document.getElementById("plan_total_display");
  if (totalEl) {
    totalEl.value = plan_total ? formatCurrency(plan_total) : "0.00";
  }
};


const attachThaiYearAdjustment = (instance) => {
  const updateThaiYearCalendar = () => {
    const yearInput = instance.currentYearElement;
    if (!yearInput) return;
    const year = parseInt(yearInput.value, 10);
    if (!Number.isNaN(year) && year < 2400) {
      yearInput.value = year + 543;
    }
  };

  updateThaiYearCalendar();

  ["onMonthChange", "onYearChange", "onOpen", "onValueUpdate"].forEach(
    (hook) => {
      const eventList = instance.config[hook];
      if (Array.isArray(eventList)) {
        eventList.push(updateThaiYearCalendar);
      } else if (typeof eventList === "function") {
        instance.config[hook] = [eventList, updateThaiYearCalendar];
      } else {
        instance.config[hook] = [updateThaiYearCalendar];
      }
    }
  );
};

const createDateTimeConfig = () => ({
  enableTime: true,
  dateFormat: "Y-m-d H:i",
  altInput: true,
  altFormat: "d-m-Y H:i",
  defaultDate: new Date(),
  time_24hr: true,
  locale: THAI_FLATPICKR_LOCALE,
  formatDate: (date) => {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const yearBE = date.getFullYear() + 543;
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    return `${day}-${month}-${yearBE} ${hours}:${minutes}`;
  },
  parseDate: (datestr) => {
    const [d, m, y, h = "00", i = "00"] = datestr
      .replace(/[:\s]/g, "-")
      .split("-");
    return new Date(+y - 543, +m - 1, +d, +h, +i);
  },
  onReady(selectedDates, dateStr, instance) {
    attachThaiYearAdjustment(instance);
  },
});

const createDateConfig = () => ({
  dateFormat: "Y-m-d",
  altInput: true,
  altFormat: "d-m-Y",
  defaultDate: new Date(),
  locale: THAI_FLATPICKR_LOCALE,
  formatDate: (date) => {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const yearBE = date.getFullYear() + 543;
    return `${day}-${month}-${yearBE}`;
  },
  parseDate: (datestr) => {
    const [d, m, y] = datestr.replace(/[:\s]/g, "-").split("-");
    return new Date(+y - 543, +m - 1, +d);
  },
  onReady(selectedDates, dateStr, instance) {
    attachThaiYearAdjustment(instance);
  },
});

const initDatePickers = (root = document) => {
  if (typeof flatpickr !== "function") return;

  root.querySelectorAll(".js-flatpickr-datetime").forEach((input) => {
    if (input._flatpickr) return;
    flatpickr(input, createDateTimeConfig());
  });

  root.querySelectorAll(".js-flatpickr-date-only").forEach((input) => {
    if (input._flatpickr) return;
    flatpickr(input, createDateConfig());
  });
};

const ensureCollapsibleStyles = () => {
  if (document.getElementById("collapsible-card-style")) return;
  const style = document.createElement("style");
  style.id = "collapsible-card-style";
  style.textContent = `
    .collapsible-card { position: relative; }
    .collapsible-card .toggle-card { font-size: 0.8rem; cursor: pointer; }
    .collapsible-card.is-collapsed > :not(.card-header) { display: none !important; }
  `;
  document.head.appendChild(style);
};

const getNextFormIndex = () =>
  document.querySelectorAll(".collapsible-card").length + 1;

const updateFormIndices = () => {
  document.querySelectorAll(".collapsible-card").forEach((card, idx) => {
    card.dataset.formIndex = String(idx + 1);
    const titleEl = card.querySelector(".card-title");
    if (!titleEl) return;
    if (!titleEl.dataset.baseTitle) {
      const current = titleEl.textContent;
      titleEl.dataset.baseTitle = current.replace(/^ฟอร์มที่ \d+\s*:\s*/, "");
    }
    titleEl.textContent = `ฟอร์มที่ ${idx + 1} : ${titleEl.dataset.baseTitle}`;
  });
  window.formsSummaryManager?.update();
};

const bindCollapsible = (card) => {
  if (!card) return;
  const toggleBtn = card.querySelector(".toggle-card");
  if (!toggleBtn) return;
  toggleBtn.addEventListener("click", () => {
    card.classList.toggle("is-collapsed");
  });
};

const ensureExpensesGroupDefault = (form, defaultValue) => {
  if (!form) return;
  const radios = Array.from(
    form.querySelectorAll('input[name="expenses_group"]')
  );
  if (!radios.length) return;

  const hasChecked = radios.some((radio) => radio.checked);
  if (hasChecked) return;

  let matched = false;
  radios.forEach((radio) => {
    if (radio.value === String(defaultValue)) {
      radio.checked = true;
      matched = true;
    } else {
      radio.checked = false;
    }
  });

  if (!matched) {
    radios[0].checked = true;
  }
};

const formsSummaryManager = (() => {
  const parseNumericValue = (value) => {
    if (typeof value === "number") return value;
    if (value === null || value === undefined) return 0;
    const cleaned = String(value).replace(/[^0-9.-]/g, "");
    if (!cleaned) return 0;
    const parsed = parseFloat(cleaned);
    return Number.isNaN(parsed) ? 0 : parsed;
  };

  const formatCurrency = (amount) =>
    parseNumericValue(amount).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  const resolveFormType = (card) => {
    const title = card?.querySelector(".card-title");
    const base = title?.dataset.baseTitle || title?.textContent || "";
    if (base.includes("หมู่คณะ")) return "เดินทางเป็นหมู่คณะ";
    if (base.includes("คนเดียว")) return "เดินทางคนเดียว";
    return base.trim() || "-";
  };

  const readRequesterName = (form, formType) => {
    if (formType === "เดินทางเป็นหมู่คณะ") {
      const owner = document.getElementById("full_name")?.value?.trim();
      return owner || "-";
    }

    const textapprove = $(form).find('.input-approve-name');
    if (textapprove) {
      const value = textapprove.val();
      if (value) {
        return value;
      }
    }

    const hidden = form
      .querySelector('[name="full_name_text_expenses"]')
      ?.value?.trim();
    return hidden || "-";
  };

  const buildRowHtml = (row) => `
      <tr>
        <td class="text-center">${row.index}</td>
        <td>${row.type}</td>
        <td>${row.name}</td>
        <td class="text-end">${formatCurrency(row.amount)}</td>
      </tr>
    `;

  const update = () => {
    const table = document.getElementById("forms-summary-table");
    if (!table) return;
    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    const cards = Array.from(document.querySelectorAll(".collapsible-card"));
    if (!cards.length) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูลแบบฟอร์ม</td></tr>';
      updateTotals(0);
      return;
    }

    const rows = [];
    let formsTotal = 0;
    cards.forEach((card, idx) => {
      const form = card.closest("form");
      const typeLabel = resolveFormType(card);
      const totalInput = form?.querySelector("#expenses_total");
      const amount = parseNumericValue(totalInput?.value);
      formsTotal += amount;
      rows.push({
        index: idx + 1,
        type: typeLabel,
        name: readRequesterName(form || document, typeLabel),
        amount,
      });
    });

    tbody.innerHTML = rows.map(buildRowHtml).join("");
    updateTotals(formsTotal);
  };

  const updateTotals = (formsTotal) => {
    const planTotalValue = document.getElementById("plan_total")?.value;
    const planTotal = parseNumericValue(planTotalValue);
    const difference = planTotal - formsTotal;
    const diffValue = Math.abs(difference);

    const setValue = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.textContent = value;
    };

    setValue("summary-plan-total", formatCurrency(planTotal));
    setValue("summary-forms-total", formatCurrency(formsTotal));
    setValue("summary-difference", formatCurrency(diffValue));

    const chkReturn = document.getElementById("expenses_return_display");
    const chkWithdraw = document.getElementById("expenses_withdraw_display");
    if (chkReturn) chkReturn.checked = false;
    if (chkWithdraw) chkWithdraw.checked = false;

    const amountInput = document.getElementById("expenses_amount_display");
    if (!amountInput) return;

    if (formsTotal > planTotal) {
      if (chkWithdraw) chkWithdraw.checked = true;
      amountInput.value = formatCurrency(diffValue);
    } else if (planTotal > formsTotal) {
      if (chkReturn) chkReturn.checked = true;
      amountInput.value = formatCurrency(diffValue);
    } else {
      amountInput.value = "0.00";
    }
  };


  return { update, parseNumericValue };
})();

window.formsSummaryManager = formsSummaryManager;

(async () => {
  try {
    ensureCollapsibleStyles();
    // ดึงข้อมูลจาก URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const planId = urlParams.get("id");
    const type = urlParams.get("type");

    // if (!planId) {
    //   Swal.fire({
    //     title: "เกิดข้อผิดพลาด",
    //     text: "ไม่พบรหัสข้อมูลที่ต้องการดู",
    //     icon: "error",
    //   }).then(() => {
    //     window.location.href = "withdraw-money-list.php";
    //   });
    //   return;
    // }

    if (type === "3") {
      $(".div-one-person").show();
      //   $(".div-two-person").hide();
      //   $("#noCheck").prop("checked", true);
    } else {
      //   $(".div-one-person").hide();
      //   $(".div-two-person").show();
      //   $("#yesCheck").prop("checked", true);
    }

    const params_all = await getAllParams();
    const data_user = params_all.current_user;
    document.getElementById("depart_code").value = data_user.depart_code || "";
    document.getElementById("full_name").value = `${data_user.user_fname || ""
      } ${data_user.user_lname || ""}`;
    document.getElementById("position_name").value =
      data_user.position_name || "";
    document.getElementById("level_name").value = data_user.level_name || "";
    document.getElementById("affiliation_name").value =
      data_user.depart_name || "";
    console.log(data_user);

    const fetchUsersAll = async () => {
      try {
        const response = await fetch(
          buildRelativeUrl("../../controllers/user/user_controller.php", {
            action: "read_all",
          }),
          {
            credentials: "include",
          }
        );
        if (!response.ok)
          throw new Error(`HTTP ${response.status} ${response.statusText}`);
        const result = await response.json();
        if (result?.status === "success" && Array.isArray(result.data)) {
          return result.data;
        }
        throw new Error(result?.message || "Invalid response payload");
      } catch (error) {
        console.error("ไม่สามารถโหลดรายชื่อผู้ใช้:", error);
        return null;
      }
    };

    let usersAll = Array.isArray(params_all.users_all)
      ? params_all.users_all
      : null;

    if (!usersAll) {
      usersAll = await fetchUsersAll();
    }

    if (usersAll) {
      userDropdownManager.setUsers(usersAll);
      setTimeout(() => applyDefaultRequester(data_user, usersAll), 0);
    } else {
      userDropdownManager.setLoadError();
    }

    const dynamicFormManager = (() => {
      const parseNumeric = (value) => {
        if (typeof value === "number") return value;
        if (value === null || value === undefined) return 0;
        const cleaned = String(value).replace(/,/g, "").trim();
        if (!cleaned) return 0;
        const parsed = parseFloat(cleaned);
        return Number.isNaN(parsed) ? 0 : parsed;
      };

      const formatNumeric = (
        input,
        value,
        fractionDigits = 2,
        useLocaleFormatting = true
      ) => {
        if (!input) return;
        if (value === null || value === undefined || Number.isNaN(value)) {
          input.value = "";
          return;
        }

        const numeric = Number(value);
        if (Number.isNaN(numeric)) {
          input.value = "";
          return;
        }

        const isNumberInput = input.type === "number" || input.type === "range";
        if (!useLocaleFormatting || isNumberInput) {
          input.value = numeric.toFixed(fractionDigits);
          return;
        }

        input.value = numeric.toLocaleString("en-US", {
          minimumFractionDigits: fractionDigits,
          maximumFractionDigits: fractionDigits,
        });
      };

      const getThaiText = (amount) => {
        if (typeof numberToThaiText === "function") {
          return numberToThaiText(amount);
        }
        return "";
      };

      const multiplyToTotal = (form, unitName, qtyName, totalName) => {
        const unit = parseNumeric(
          form.querySelector(`[name="${unitName}"]`)?.value
        );
        const qty = parseNumeric(
          form.querySelector(`[name="${qtyName}"]`)?.value
        );
        const total = unit * qty;
        formatNumeric(form.querySelector(`[name="${totalName}"]`), total, 2);
        return total;
      };

      const copyToTotal = (
        form,
        sourceName,
        targetName,
        fractionDigits = 2
      ) => {
        const value = parseNumeric(
          form.querySelector(`[name="${sourceName}"]`)?.value
        );
        formatNumeric(
          form.querySelector(`[name="${targetName}"]`),
          value,
          fractionDigits
        );
        return value;
      };

      const updateExpensesAmount = (form, totalValue) => {
        const total =
          totalValue !== undefined && totalValue !== null
            ? totalValue
            : parseNumeric(form.querySelector("#expenses_total")?.value);
        const planTotal = parseNumeric(
          form.querySelector("#plan_total")?.value
        );
        const diff = total - planTotal;
        formatNumeric(
          form.querySelector("#expenses_amount"),
          Math.abs(diff),
          2
        );

        const withdraw = form.querySelector("#expenses_withdraw");
        const returnBox = form.querySelector("#expenses_return");
        if (withdraw && returnBox) {
          if (total > planTotal) {
            withdraw.checked = true;
            returnBox.checked = false;
          } else if (total < planTotal) {
            withdraw.checked = false;
            returnBox.checked = true;
          } else {
            withdraw.checked = false;
            returnBox.checked = false;
          }
        }
      };

      const updateExpensesTotal = (form) => {
        const read = (selector) =>
          parseNumeric(form.querySelector(selector)?.value);
        const allowance = read('[name="expenses_allowance_total"]');
        const accommodation = read('[name="expenses_accommodation_total"]');
        const transportation = read('[name="expenses_transportation_total"]');
        const moving = read('[name="expenses_moving_total"]');
        const other = read('[name="expenses_other_total"]');
        const food = read('[name="expenses_food_total"]');
        const details = read("#total_amount_details");
        const group = read("#expenses_group_total");

        const total =
          allowance +
          accommodation +
          transportation +
          moving +
          other -
          food +
          details +
          group;

        formatNumeric(form.querySelector("#expenses_total"), total, 2);

        const totalTextInput = form.querySelector("#expenses_total_text");
        if (totalTextInput) {
          const thai = total ? getThaiText(total) : "ศูนย์บาทถ้วน";
          if (totalTextInput.tagName === "INPUT") {
            totalTextInput.value = thai;
          } else {
            totalTextInput.textContent = thai;
          }
        }

        updateExpensesAmount(form, total);
        window.formsSummaryManager?.update();
      };

      const formatNumberOnBlur = (event) => {
        const target = event.target;
        if (target.classList.contains("format-number-2point")) {
          if (!target.value) {
            target.value = "";
            return;
          }
          const value = parseNumeric(target.value);
          formatNumeric(target, value, 2, false);
        }
        if (target.classList.contains("format-number-1point")) {
          if (!target.value) {
            target.value = "";
            return;
          }
          const value = parseNumeric(target.value);
          formatNumeric(target, value, 1, false);
        }

        // ===== 0 decimal =====
        if (target.classList.contains("format-number-0point")) {
          if (!target.value) {
            target.value = "";
            return;
          }

          let val = parseInt(target.value.replace(/,/g, ''), 10);
          target.value = !isNaN(val) ? val : "";
        }

        // ===== step 0.5 (x.0 หรือ x.5) =====
        if (target.classList.contains("format-number-step-05")) {
          const errorSpan = target.nextElementSibling;

          if (!target.value) {
            errorSpan.style.display = "none";
            return;
          }

          let val = parseFloat(target.value);

          if (isNaN(val)) {
            target.value = "";
            errorSpan.style.display = "none";
            return;
          }

          const decimal = (val % 1).toFixed(1);

          if (decimal === "0.0" || decimal === "0.5") {
            target.value = val.toFixed(1);
            errorSpan.style.display = "none";
          } else {
            errorSpan.style.display = "inline";
          }
        }
      };

      const getVehicleSection = (form) => {
        const car = form.querySelector("#expenses_vehicle");
        const motorcycle = form.querySelector("#expenses_motorcycle");
        const section = form.querySelector(".display-vehicle");
        const required = form.querySelector(
          "#expenses_vehicle_number_required"
        );
        return { car, motorcycle, section, required };
      };

      const getListDetailsContext = (form) => {
        let table = null;
        let tbody = null;

        const existingRow = form.querySelector("tr.list-details");
        if (existingRow) {
          tbody = existingRow.closest("tbody");
          table = tbody?.closest("table") || null;
        } else {
          const addBtn = form.querySelector(".addTable-list-details");
          const responsiveContainer = addBtn?.closest(
            ".border-block-start-dashed"
          )?.previousElementSibling;
          if (responsiveContainer?.querySelector) {
            table = responsiveContainer.querySelector("table");
          }
          if (!table) {
            table = form.querySelector(
              ".display-vehicle table.table-bordered.border-success"
            );
          }
          tbody = table?.querySelector("tbody") || null;
        }

        if (!table || !tbody) return null;
        const tfoot = table.querySelector("tfoot");
        return { table, tbody, tfoot };
      };

      const getGroupTableContext = (form) => {
        const table = form.querySelector("#ifYes table");
        if (!table) return null;
        const tbody = table.querySelector("tbody");
        return { table, tbody };
      };

      const getDefaultCompensationPerKm = (form) => {
        const { car, motorcycle } = getVehicleSection(form);
        if (car?.checked) return "5.5";
        if (motorcycle?.checked) return "3.0";
        return "";
      };

      const setupListDetailPickers = (row) => {
        row.querySelectorAll(".list-detail-date").forEach((input) => {
          flatpickr(input, {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: {
              firstDayOfWeek: 1,
              weekdays: {
                shorthand: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส"],
                longhand: [
                  "อาทิตย์",
                  "จันทร์",
                  "อังคาร",
                  "พุธ",
                  "พฤหัสบดี",
                  "ศุกร์",
                  "เสาร์",
                ],
              },
              months: {
                shorthand: [
                  "ม.ค.",
                  "ก.พ.",
                  "มี.ค.",
                  "เม.ย.",
                  "พ.ค.",
                  "มิ.ย.",
                  "ก.ค.",
                  "ส.ค.",
                  "ก.ย.",
                  "ต.ค.",
                  "พ.ย.",
                  "ธ.ค.",
                ],
                longhand: [
                  "มกราคม",
                  "กุมภาพันธ์",
                  "มีนาคม",
                  "เมษายน",
                  "พฤษภาคม",
                  "มิถุนายน",
                  "กรกฎาคม",
                  "สิงหาคม",
                  "กันยายน",
                  "ตุลาคม",
                  "พฤศจิกายน",
                  "ธันวาคม",
                ],
              },
            },
            formatDate: (date) => {
              const day = String(date.getDate()).padStart(2, "0");
              const month = String(date.getMonth() + 1).padStart(2, "0");
              const yearBE = date.getFullYear() + 543;
              return `${day}-${month}-${yearBE}`;
            },
            parseDate: (datestr) => {
              const [d, m, y] = datestr.replace(/[:\s]/g, "-").split("-");
              return new Date(+y - 543, +m - 1, +d);
            },
            onReady(selectedDates, dateStr, instance) {
              const updateThaiYearCalendar = () => {
                const yearInput = instance.currentYearElement;
                if (!yearInput) return;
                const year = parseInt(yearInput.value, 10);
                if (!Number.isNaN(year) && year < 2400) {
                  yearInput.value = year + 543;
                }
              };
              updateThaiYearCalendar();
              instance.config.onMonthChange.push(updateThaiYearCalendar);
              instance.config.onYearChange.push(updateThaiYearCalendar);
              instance.config.onOpen = [updateThaiYearCalendar];
              instance.config.onValueUpdate = [updateThaiYearCalendar];
            },
          });
        });

        const timeInputs = row.querySelectorAll(
          ".list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive"
        );
        const instances = {};
        timeInputs.forEach((input) => {
          const fp = flatpickr(input, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
          });
          if (input.classList.contains("list-detail-time-start"))
            instances.start = fp;
          if (input.classList.contains("list-detail-time-end"))
            instances.end = fp;
          if (input.classList.contains("list-detail-time-depart"))
            instances.depart = fp;
          if (input.classList.contains("list-detail-time-arrive"))
            instances.arrive = fp;
        });

        const clearTimeInput = (input) => {
          if (!input) return;
          const hadValue = Boolean(input.value);
          input.value = "";
          if (input._flatpickr && (hadValue || input._flatpickr.input?.value)) {
            input._flatpickr.clear();
          }
        };

        const enforceBounds = () => {
          const startVal = row.querySelector(".list-detail-time-start")?.value;
          const endVal = row.querySelector(".list-detail-time-end")?.value;
          const departVal = row.querySelector(
            ".list-detail-time-depart"
          )?.value;
          const arriveVal = row.querySelector(
            ".list-detail-time-arrive"
          )?.value;

          const toMinutes = (value) => {
            if (!value) return null;
            const [h, m] = value.split(":").map(Number);
            return h * 60 + m;
          };

          const maxStartEnd = (() => {
            const start = toMinutes(startVal);
            const end = toMinutes(endVal);
            if (start !== null && end !== null) {
              return start > end ? startVal : endVal;
            }
            return startVal || endVal || null;
          })();

          const minDepartArrive = (() => {
            const depart = toMinutes(departVal);
            const arrive = toMinutes(arriveVal);
            if (depart !== null && arrive !== null) {
              return depart < arrive ? departVal : arriveVal;
            }
            return departVal || arriveVal || null;
          })();

          if (instances.depart && maxStartEnd)
            instances.depart.set("minTime", maxStartEnd);
          if (instances.arrive && maxStartEnd)
            instances.arrive.set("minTime", maxStartEnd);
          if (instances.start && minDepartArrive)
            instances.start.set("maxTime", minDepartArrive);
          if (instances.end && minDepartArrive)
            instances.end.set("maxTime", minDepartArrive);
        };

        row
          .querySelectorAll(
            ".list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive"
          )
          .forEach((input) => {
            input.addEventListener("change", enforceBounds);
          });

        enforceBounds();

        const attachExclusive = (input, handler) => {
          if (!input) return;
          ["input", "change", "blur"].forEach((evtName) =>
            input.addEventListener(evtName, handler)
          );
        };

        const handleStartEndToggle = (evt) => {
          const timeStart = row.querySelector(".list-detail-time-start");
          const timeEnd = row.querySelector(".list-detail-time-end");
          if (!timeStart || !timeEnd) return;

          let startHasValue = Boolean(timeStart.value);
          let endHasValue = Boolean(timeEnd.value);

          if (startHasValue && endHasValue) {
            if (evt?.target === timeStart) {
              clearTimeInput(timeEnd);
              endHasValue = false;
            } else if (evt?.target === timeEnd) {
              clearTimeInput(timeStart);
              startHasValue = false;
            } else {
              clearTimeInput(timeEnd);
              endHasValue = false;
            }
          }

          if (startHasValue) {
            clearTimeInput(timeEnd);
            timeEnd.disabled = true;
            timeStart.disabled = false;
          } else if (endHasValue) {
            clearTimeInput(timeStart);
            timeStart.disabled = true;
            timeEnd.disabled = false;
          } else {
            timeStart.disabled = false;
            timeEnd.disabled = false;
          }
        };

        const handleDepartArriveToggle = (evt) => {
          const timeDepart = row.querySelector(".list-detail-time-depart");
          const timeArrive = row.querySelector(".list-detail-time-arrive");
          if (!timeDepart || !timeArrive) return;

          let departHasValue = Boolean(timeDepart.value);
          let arriveHasValue = Boolean(timeArrive.value);

          if (departHasValue && arriveHasValue) {
            if (evt?.target === timeDepart) {
              clearTimeInput(timeArrive);
              arriveHasValue = false;
            } else if (evt?.target === timeArrive) {
              clearTimeInput(timeDepart);
              departHasValue = false;
            } else {
              clearTimeInput(timeArrive);
              arriveHasValue = false;
            }
          }

          if (departHasValue) {
            clearTimeInput(timeArrive);
            timeArrive.disabled = true;
            timeDepart.disabled = false;
          } else if (arriveHasValue) {
            clearTimeInput(timeDepart);
            timeDepart.disabled = true;
            timeArrive.disabled = false;
          } else {
            timeDepart.disabled = false;
            timeArrive.disabled = false;
          }
        };

        attachExclusive(
          row.querySelector(".list-detail-time-start"),
          handleStartEndToggle
        );
        attachExclusive(
          row.querySelector(".list-detail-time-end"),
          handleStartEndToggle
        );
        handleStartEndToggle();

        attachExclusive(
          row.querySelector(".list-detail-time-depart"),
          handleDepartArriveToggle
        );
        attachExclusive(
          row.querySelector(".list-detail-time-arrive"),
          handleDepartArriveToggle
        );
        handleDepartArriveToggle();
      };

      const recalcListDetailRow = (row, form) => {
        const distanceInput = row.querySelector(".list-detail-distance");
        const perKmInput = row.querySelector(
          ".list-detail-compensation-per-km"
        );
        const sumInput = row.querySelector(".sum-col-details");
        if (!distanceInput || !perKmInput || !sumInput) return;
        const distance = parseNumeric(distanceInput.value);
        const perKm = parseNumeric(perKmInput.value);
        const sum = distance * perKm;
        formatNumeric(sumInput, sum, 2);
        updateTotalAmountDetails(form);
      };

      const addListDetailRow = (form) => {
        const ctx = getListDetailsContext(form);
        if (!ctx) return;
        ctx.tbody.querySelectorAll("tr.list-details").forEach((existingRow) => {
          if (!existingRow.querySelector(".sum-col-details")) {
            existingRow.remove();
          }
        });
        const row = document.createElement("tr");
        row.classList.add("list-details");
        const defaultPerKm = getDefaultCompensationPerKm(form);
        row.innerHTML = `
          <td class="row-check-details"><input class="form-check-input" type="checkbox" aria-label="..."></td>
          <td>
              <div class="form-group">
                  <div class="input-group">
                      <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                      <input type="text" class="form-control date-input list-detail-date" placeholder="ระบุวัน เดือน ปี">
                  </div>
              </div>
          </td>
          <td>
              <div class="form-group">
                  <div class="input-group">
                      <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                      <input type="text" class="form-control time-input list-detail-time-start" placeholder="ระบุเวลา">
                  </div>
              </div>
          </td>
          <td>
              <div class="form-group">
                  <div class="input-group">
                      <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                      <input type="text" class="form-control time-input list-detail-time-end" placeholder="ระบุเวลา">
                  </div>
              </div>
          </td>
          <td>
              <div class="form-group">
                  <div class="input-group">
                      <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                      <input type="text" class="form-control time-input list-detail-time-depart" placeholder="ระบุเวลา">
                  </div>
              </div>
          </td>
          <td>
              <div class="form-group">
                  <div class="input-group">
                      <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                      <input type="text" class="form-control time-input list-detail-time-arrive" placeholder="ระบุเวลา">
                  </div>
              </div>
          </td>
          <td><input type="number" class="form-control distance list-detail-distance" min="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
          <td><input type="number" class="form-control distance list-detail-compensation-per-km" min="0.1" placeholder="กรุณาระบุ เงินชดเชย/กม" value="${defaultPerKm}" ${defaultPerKm ? "disabled" : ""
          }></td>
          <td><input type="number" class="form-control compensation sum-col-details list-detail-compensation" placeholder="เงินชดเชย (บาท)"></td>
          <td><input class="form-control remark-detail" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
        `;
        ctx.tbody.appendChild(row);
        setupListDetailPickers(row);
        recalcListDetailRow(row, form);
        updateTotalAmountDetails(form);
      };

      const removeListDetailRows = (form) => {
        const ctx = getListDetailsContext(form);
        if (!ctx) return;
        const rows = Array.from(ctx.tbody.querySelectorAll("tr.list-details"));
        const removable = rows.filter((row) => {
          const checkbox = row.querySelector(
            ".row-check-details input.form-check-input"
          );
          return checkbox?.checked;
        });
        if (!removable.length) {
          alert("กรุณาเลือกแถวที่ต้องการลบ");
          return;
        }
        removable.forEach((row) => row.remove());
        updateTotalAmountDetails(form);
      };

      const updateTotalAmountDetails = (form) => {
        const ctx = getListDetailsContext(form);
        if (!ctx) {
          updateExpensesTotal(form);
          return;
        }
        let total = 0;
        ctx.tbody.querySelectorAll(".sum-col-details").forEach((input) => {
          total += parseNumeric(input.value);
        });
        formatNumeric(form.querySelector("#total_amount_details"), total, 2);
        const totalText = form.querySelector("#total_text_details");
        if (totalText) {
          const thai = total ? getThaiText(total) : "ศูนย์บาทถ้วน";
          if (totalText.tagName === "INPUT") {
            totalText.value = thai;
          } else {
            totalText.textContent = thai;
          }
        }
        updateExpensesTotal(form);
      };

      const updateVehicleSectionState = (form) => {
        const { car, motorcycle, section, required } = getVehicleSection(form);
        const hasVehicle = Boolean(car?.checked || motorcycle?.checked);
        if (section) {
          section.style.display = hasVehicle ? "" : "none";
        }
        if (required) {
          required.style.display = hasVehicle ? "inline" : "none";
        }
        const perKm = getDefaultCompensationPerKm(form);
        form
          .querySelectorAll(".list-detail-compensation-per-km")
          .forEach((input) => {
            if (perKm) {
              input.value = perKm;
              input.setAttribute("disabled", "disabled");
            } else {
              input.removeAttribute("disabled");
              input.value = "";
            }
            const row = input.closest("tr.list-details");
            if (row) recalcListDetailRow(row, form);
          });
        updateTotalAmountDetails(form);
      };

      const updateGroupRowNumbers = (tbody) => {
        Array.from(tbody.querySelectorAll("tr.group-people-list")).forEach(
          (row, index) => {
            const numInput = row.querySelector(".group_numrows");
            if (numInput) numInput.value = index + 1;
          }
        );
      };

      const recalcGroupRow = (row, form) => {
        const allowanceInput = row.querySelector(".group_allowance");
        const allowanceDayInput = row.querySelector(".group_days");
        const allowanceTotalInput = row.querySelector(".group_total_allowance");
        const accommodationInput = row.querySelector(".group_accommodation");
        const accommodationDayInput = row.querySelector(
          ".group_accommodation_days"
        );
        const accommodationTotalInput = row.querySelector(
          ".group_accommodation_total"
        );
        const vehicleInput = row.querySelector(".group_transport");
        const otherInput = row.querySelector(".group_other_expenses");
        const mealInput = row.querySelector(".group_meal");
        const mealCountInput = row.querySelector(".group_meal_count");
        const mealTotalInput = row.querySelector(".group_meal_total");
        const sumInput = row.querySelector(".group_sum");

        const allowance = parseNumeric(allowanceInput?.value);
        const allowanceDays = parseNumeric(allowanceDayInput?.value);
        const allowanceTotal = allowance * allowanceDays;
        formatNumeric(allowanceTotalInput, allowanceTotal, 2);

        const accommodation = parseNumeric(accommodationInput?.value);
        const accommodationDays = parseNumeric(accommodationDayInput?.value);
        const accommodationTotal = accommodation * accommodationDays;
        formatNumeric(accommodationTotalInput, accommodationTotal, 2);

        const meal = parseNumeric(mealInput?.value);
        const mealCount = parseNumeric(mealCountInput?.value);
        const mealTotal = meal * mealCount;
        formatNumeric(mealTotalInput, mealTotal, 2);

        const vehicle = parseNumeric(vehicleInput?.value);
        const other = parseNumeric(otherInput?.value);
        const total =
          allowanceTotal + accommodationTotal + vehicle + other - mealTotal;
        formatNumeric(sumInput, total, 2);

        updateGroupTotals(form);
      };

      const updateGroupTotals = (form) => {
        const ctx = getGroupTableContext(form);
        if (!ctx) {
          updateExpensesTotal(form);
          return;
        }
        let total = 0;
        ctx.tbody.querySelectorAll(".group_sum").forEach((input) => {
          total += parseNumeric(input.value);
        });
        formatNumeric(form.querySelector("#expenses_group_total"), total, 2);
        const totalText = form.querySelector("#expenses_group_total_text");
        if (totalText) {
          const thai = total ? getThaiText(total) : "ศูนย์บาทถ้วน";
          if (totalText.tagName === "INPUT") {
            totalText.value = thai;
          } else {
            totalText.textContent = thai;
          }
        }
        updateExpensesTotal(form);
      };

      const updateGroupPeopleCount = (form) => {
        const ctx = getGroupTableContext(form);
        const counter = form.querySelector("#group_people_count");
        if (!ctx || !counter) {
          return;
        }
        const count = ctx.tbody.querySelectorAll("tr.group-people-list").length;
        counter.value = count;
      };

      document.querySelectorAll('.format-number-0point').forEach(input => {
        input.addEventListener('input', function () {
          this.value = this.value.replace(/\./g, '');
        });
        input.addEventListener('blur', function () {
          let val = parseInt(this.value.replace(/,/g, ''), 10);

          if (!isNaN(val)) {
            this.value = val;
          } else {
            this.value = '';
          }
        });
      });

      document.addEventListener('blur', function (e) {
        if (e.target.classList.contains('format-number-step-05')) {
          const value = e.target.value;

          if (!value) {
            e.target.classList.remove('is-invalid');
            e.target.removeAttribute('title'); // ลบ tooltip ถ้าไม่มี error
            return;
          }

          const val = parseFloat(value);
          const decimal = (val % 1).toFixed(1);

          if (decimal === '0.0' || decimal === '0.5') {
            e.target.value = val.toFixed(1);
            e.target.classList.remove('is-invalid');
            e.target.removeAttribute('title'); // ลบ tooltip
          } else {
            e.target.classList.add('is-invalid');
            e.target.setAttribute('title', 'กรอกได้เฉพาะจำนวน .0 หรือ .5 เท่านั้น'); // ใส่ tooltip
          }
        }
      }, true);

      function isValidThaiID(id) {
        if (!/^\d{13}$/.test(id)) return false;
        let sum = 0;
        for (let i = 0; i < 12; i++) {
          sum += parseInt(id.charAt(i), 10) * (13 - i);
        }
        let checkDigit = (11 - (sum % 11)) % 10;
        return checkDigit === parseInt(id.charAt(12), 10);
      }

      // เพิ่ม event ให้ input.group_id_card ทุกตัว
      document.addEventListener('blur', function (e) {
        if (e.target.classList.contains('group_id_card')) {
          const val = e.target.value.replace(/\D/g, '');
          if (val && !isValidThaiID(val)) {
            e.target.classList.add('is-invalid');
          } else {
            e.target.classList.remove('is-invalid');
          }
        }
      }, true);

      const addGroupRow = (form) => {
        const ctx = getGroupTableContext(form);
        if (!ctx) return;
        ctx.tbody.querySelectorAll("tr").forEach((row) => {
          if (!row.querySelector(".group_sum")) row.remove();
        });
        const isFirstRow =
          ctx.tbody.querySelectorAll("tr.group-people-list").length === 0;
        const row = document.createElement("tr");
        row.classList.add("group-people-list");
        const checkboxCell = isFirstRow
          ? ""
          : '<input class="form-check-input" type="checkbox" aria-label="...">';
        const firstRowClass = isFirstRow ? " first-group-row" : "";
        const defaultName = isFirstRow
          ? `${data_user.user_fname || ""} ${data_user.user_lname || ""}`.trim()
          : "";
        const defaultPosition = isFirstRow ? data_user.position_name || "" : "";
        row.innerHTML = `
                <td class="group-checkbox first-group-row"><input class="form-check-input" type="checkbox" aria-label="..."></td>
                <td><input class="form-control group_numrows" type="text" placeholder="Run Number Auto 1+" disabled></td>
                <td><input class="form-control group_name" type="text" placeholder="ระบุ ชื่อ"></td>
                <td><input class="form-control group_position" type="text" placeholder="ระบุ ตำแหน่ง/ระดับ"></td>
                <td><input class="form-control group_id_card" type="text" minlength="13" maxlength="13" placeholder="ระบุ เลขบัตรประชาชน"></td>
                <td><input class="form-control group_allowance format-number-2point" type="text" placeholder="ระบุ ค่าเบี้ยเลี้ยง"></td>
                <td><input class="form-control group_days format-number-step-05" type="number" placeholder="ระบุ วัน" step="0.5" min="0"></td>
                <td><input class="form-control group_total_allowance" type="text" placeholder="ระบุ รวม ค่าเบี้ยเลี้ยง" disabled></td>
                <td><input class="form-control group_accommodation format-number-2point" type="text" placeholder="ระบุ ค่าเช่าที่พัก"></td>
                <td><input class="form-control group_accommodation_days format-number-0point" type="number" placeholder="ระบุ วัน" step="1" min="0"></td>
                <td><input class="form-control group_accommodation_total" type="text" placeholder="ระบุ รวม ค่าเช่าที่พัก" disabled></td>
                <td><input class="form-control group_transport format-number-2point" type="text" placeholder="ระบุ ค่าพาหนะ"></td>
                <td><input class="form-control group_other_expenses format-number-2point" type="text" placeholder="ระบุ ค่าใช้จ่ายอื่นๆ"></td>
                <td><input class="form-control group_meal format-number-2point" type="text" placeholder="ระบุ มื้อละ"></td>
                <td><input class="form-control group_meal_count format-number-1point" type="text" placeholder="ระบุ จำนวน"></td>
                <td><input class="form-control group_meal_total" type="text" placeholder="ระบุ รวม" disabled></td>
                <td><input class="form-control group_sum" type="text" placeholder="0.00" disabled></td>
        `;
        ctx.tbody.appendChild(row);
        recalcGroupRow(row, form);
        updateGroupRowNumbers(ctx.tbody);
        updateGroupPeopleCount(form);
      };

      const removeGroupRows = (form) => {
        const ctx = getGroupTableContext(form);
        if (!ctx) return;
        const rows = Array.from(
          ctx.tbody.querySelectorAll("tr.group-people-list")
        );
        const firstRow = rows[0];
        const removable = rows.filter((row) => {
          const checkbox = row.querySelector(".group-checkbox input");
          return checkbox?.checked;
        });
        if (!removable.length) {
          alert("กรุณาเลือกแถวที่ต้องการลบ");
          return;
        }
        removable.forEach((row) => {
          if (row === firstRow) return;
          row.remove();
        });
        updateGroupRowNumbers(ctx.tbody);
        updateGroupTotals(form);
        updateGroupPeopleCount(form);
      };

      const handleCheckboxToggle = (form, target) => {
        if (
          target.id === "expenses_vehicle" ||
          target.id === "expenses_motorcycle"
        ) {
          const { car, motorcycle } = getVehicleSection(form);
          if (target === car && car?.checked) {
            motorcycle && (motorcycle.checked = false);
          }
          if (target === motorcycle && motorcycle?.checked) {
            car && (car.checked = false);
          }
          updateVehicleSectionState(form);
        }
        if (
          target.id === "expenses_return" ||
          target.id === "expenses_withdraw"
        ) {
          const withdraw = form.querySelector("#expenses_withdraw");
          const returnBox = form.querySelector("#expenses_return");
          if (target === withdraw && withdraw?.checked) {
            returnBox && (returnBox.checked = false);
          }
          if (target === returnBox && returnBox?.checked) {
            withdraw && (withdraw.checked = false);
          }
        }
        if (target.classList.contains("check-all")) {
          const ctx = getGroupTableContext(form);
          if (!ctx) return;
          ctx.tbody
            .querySelectorAll(".group-checkbox input")
            .forEach((checkbox) => {
              checkbox.checked = target.checked;
            });
        }
        if (target.classList.contains("check-all-details")) {
          const ctx = getListDetailsContext(form);
          if (!ctx) return;
          ctx.tbody
            .querySelectorAll(".row-check-details input")
            .forEach((checkbox) => {
              checkbox.checked = target.checked;
            });
        }
      };

      const handleInputEvent = (form, target) => {
        if (
          target.matches(
            '[name="expenses_allowance"], [name="expenses_allowance_days"]'
          )
        ) {
          multiplyToTotal(
            form,
            "expenses_allowance",
            "expenses_allowance_days",
            "expenses_allowance_total"
          );
          updateExpensesTotal(form);
          return;
        }
        if (
          target.matches(
            '[name="expenses_accommodation"], [name="expenses_accommodation_days"]'
          )
        ) {
          multiplyToTotal(
            form,
            "expenses_accommodation",
            "expenses_accommodation_days",
            "expenses_accommodation_total"
          );
          updateExpensesTotal(form);
          return;
        }
        if (target.matches('[name="expenses_transportation"]')) {
          copyToTotal(
            form,
            "expenses_transportation",
            "expenses_transportation_total"
          );
          updateExpensesTotal(form);
          return;
        }
        if (target.matches('[name="expenses_moving"]')) {
          copyToTotal(form, "expenses_moving", "expenses_moving_total");
          updateExpensesTotal(form);
          return;
        }
        if (target.matches('[name="expenses_other"]')) {
          copyToTotal(form, "expenses_other", "expenses_other_total");
          updateExpensesTotal(form);
          return;
        }
        if (
          target.matches(
            '[name="expenses_food_per_meal"], [name="expenses_food_meals"]'
          )
        ) {
          multiplyToTotal(
            form,
            "expenses_food_per_meal",
            "expenses_food_meals",
            "expenses_food_total"
          );
          updateExpensesTotal(form);
          return;
        }
        if (target.classList.contains("list-detail-distance")) {
          const row = target.closest("tr.list-details");
          if (row) recalcListDetailRow(row, form);
          return;
        }
        if (target.classList.contains("list-detail-compensation-per-km")) {
          const row = target.closest("tr.list-details");
          if (row) recalcListDetailRow(row, form);
          return;
        }
        if (target.classList.contains("sum-col-details")) {
          updateTotalAmountDetails(form);
          return;
        }
        if (
          target.classList.contains("group_allowance") ||
          target.classList.contains("group_days") ||
          target.classList.contains("group_accommodation") ||
          target.classList.contains("group_accommodation_days") ||
          target.classList.contains("group_transport") ||
          target.classList.contains("group_other_expenses") ||
          target.classList.contains("group_meal") ||
          target.classList.contains("group_meal_count")
        ) {
          const row = target.closest("tr.group-people-list");
          if (row) recalcGroupRow(row, form);
          return;
        }
      };

      const ensureInitialRows = (form) => {
        const listCtx = getListDetailsContext(form);
        if (listCtx) {
          const hasDataRow = listCtx.tbody.querySelector(
            "tr.list-details .sum-col-details"
          );
          if (!hasDataRow) addListDetailRow(form);
        }

        const groupCtx = getGroupTableContext(form);
        if (groupCtx) {
          const hasDataRow = groupCtx.tbody.querySelector(
            "tr.group-people-list .group_sum"
          );
          if (!hasDataRow) addGroupRow(form);
        }
      };

      const initForm = (form) => {
        if (!form || form.dataset.dynamicExpensesInit) return;
        form.dataset.dynamicExpensesInit = "true";

        form.addEventListener("blur", formatNumberOnBlur, true);

        form.addEventListener("input", (event) => {
          handleInputEvent(form, event.target);
        });

        form.addEventListener("change", (event) => {
          handleCheckboxToggle(form, event.target);
          if (
            event.target.classList.contains("group-checkbox") ||
            event.target.closest(".group-checkbox")
          ) {
            updateGroupTotals(form);
          }
        });

        form.addEventListener("keyup", (event) => {
          window.formsSummaryManager?.update();
        });

        form.addEventListener("click", (event) => {
          const addDetails = event.target.closest(".addTable-list-details");
          if (addDetails) {
            event.preventDefault();
            addListDetailRow(form);
            return;
          }
          const delDetails = event.target.closest(".delTable-list-details");
          if (delDetails) {
            event.preventDefault();
            removeListDetailRows(form);
            return;
          }
          const addGroup = event.target.closest(".addTable-group-people");
          if (addGroup) {
            event.preventDefault();
            addGroupRow(form);
            return;
          }
          const delGroup = event.target.closest(".delTable-group-people");
          if (delGroup) {
            event.preventDefault();
            removeGroupRows(form);
          }
        });

        ensureInitialRows(form);
        updateVehicleSectionState(form);
        updateTotalAmountDetails(form);
        updateGroupTotals(form);
        updateExpensesTotal(form);
      };

      const recalcForm = (form) => {
        if (!form) return;
        updateVehicleSectionState(form);
        updateGroupTotals(form);
        updateGroupPeopleCount(form);
        updateTotalAmountDetails(form);
        updateExpensesTotal(form);
      };

      return {
        init: initForm,
        recalc: recalcForm,
        updateTotals: updateExpensesTotal,
      };
    })();

    window.travelExpenseMultiManager = dynamicFormManager;

    document.querySelectorAll("form#advanceFrm").forEach((form, index) => {
      dynamicFormManager.init(form);
      userDropdownManager.initForm(form);
      ensureExpensesGroupDefault(form, index === 0 ? "3" : "1");
      const card =
        form.querySelector(".collapsible-card") ||
        form.closest(".collapsible-card");
      bindCollapsible(card);
      if (card) {
        const title = card.querySelector(".card-title");
        if (title && !title.dataset.baseTitle) {
          title.dataset.baseTitle = title.textContent.replace(
            /^ฟอร์มที่ \d+\s*:\s*/,
            ""
          );
        }
      }
    });

    ensurePlanFieldStateInitialized();

    initDatePickers();
    updateFormIndices();

    // const addBtn = document.querySelector(".addTable-group-people");
    // const delBtn = document.querySelector(".delTable-group-people");
    const tableBody = document.querySelector("#ifYes table tbody");

    const checkAllBox = document.querySelector(".check-all");
    if (checkAllBox) {
      checkAllBox.addEventListener("change", function () {
        const checked = this.checked;
        tableBody
          .querySelectorAll(".group-checkbox input.form-check-input")
          .forEach((cb) => (cb.checked = checked));
      });
    }

    function updateRowNumbers() {
      tableBody.querySelectorAll("tr.group-people-list").forEach((row, idx) => {
        const numberInput = row.children[1].querySelector("input");
        if (numberInput) {
          numberInput.value = idx + 1;
        }
      });
    }

    function updateGroupPeopleCount() {
      const groupPeopleCountInput =
        document.getElementById("group_people_count");
      const yesCheck = document.getElementById("yesCheck");
      const noCheck = document.getElementById("noCheck");
      const tableBody = document.querySelector("#ifYes table tbody");

      if (!groupPeopleCountInput) return;

      if (noCheck && noCheck.checked) {
        groupPeopleCountInput.value = 1;
      } else if (yesCheck && yesCheck.checked) {
        if (tableBody) {
          const count = tableBody.querySelectorAll(
            "tr.group-people-list"
          ).length;
          groupPeopleCountInput.value = count;
        } else {
          groupPeopleCountInput.value = "";
        }
      } else {
        groupPeopleCountInput.value = "";
      }
    }
    function yesnoCheck() {
      const groupPeopleCountInput =
        document.getElementById("group_people_count");
      const yesCheck = document.getElementById("yesCheck");
      const noCheck = document.getElementById("noCheck");
      const tableBody = document.querySelector("#ifYes table tbody");
      const ifYesDiv = document.getElementById("ifYes");
      const onePersonElements = document.querySelectorAll(".one-person");

      if (!groupPeopleCountInput) return;
      if (noCheck && noCheck.checked) {
        groupPeopleCountInput.value = 1;
        if (tableBody) {
          tableBody
            .querySelectorAll("tr.group-people-list")
            .forEach((row) => row.remove());
        }
        if (ifYesDiv) ifYesDiv.style.display = "none";
        onePersonElements.forEach((el) => (el.style.display = ""));
        updateTotalAmount();
        calcExpensesTotal();
      } else if (yesCheck && yesCheck.checked) {
        if (tableBody) {
          tableBody
            .querySelectorAll("tr.group-people-list")
            .forEach((row) => row.remove());
          if (typeof window.createRow === "function") {
            window.createRow();
          } else if (typeof createRow === "function") {
            createRow();
          }
          groupPeopleCountInput.value = 1;
        } else {
          groupPeopleCountInput.value = "";
        }
        if (ifYesDiv) ifYesDiv.style.display = "block";
        onePersonElements.forEach((el) => {
          el.style.display = "none";
          el.querySelectorAll("input, textarea").forEach((input) => {
            if (input.type === "checkbox" || input.type === "radio") {
              input.checked = false;
            } else {
              input.value = "";
            }
          });
        });
        updateTotalAmount();
        calcExpensesTotal();
      } else {
        groupPeopleCountInput.value = "";
        if (ifYesDiv) ifYesDiv.style.display = "none";
        onePersonElements.forEach((el) => (el.style.display = ""));
      }
    }

    document.getElementById("noCheck")?.addEventListener("change", yesnoCheck);
    document.getElementById("yesCheck")?.addEventListener("change", yesnoCheck);

    document
      .getElementById("noCheck")
      ?.addEventListener("change", updateGroupPeopleCount);
    document
      .getElementById("yesCheck")
      ?.addEventListener("change", updateGroupPeopleCount);

    function createRow() {
      const newRow = document.createElement("tr");
      newRow.classList.add("group-people-list");
      // ตรวจสอบว่าเป็นแถวแรกหรือไม่
      const isFirstRow =
        tableBody.querySelectorAll("tr.group-people-list").length === 0;
      const defaultName = "";
      const defaultPosition = "";
      // ถ้าเป็นแถวแรก ให้ซ่อน checkbox เลย
      const checkboxHtml = isFirstRow
        ? ""
        : '<input class="form-check-input" type="checkbox" aria-label="...">';
      const firstRowClass = isFirstRow ? " first-group-row" : "";
      newRow.innerHTML = `
                <td class="group-checkbox${firstRowClass}">${checkboxHtml}</td>
                <td><input class="form-control group_numrows" type="text" placeholder="Run Number Auto 1+" disabled></td>
                <td><input class="form-control group_name" type="text" placeholder="ระบุ ชื่อ" value="${defaultName}"></td>
                <td><input class="form-control group_position" type="text" placeholder="ระบุ ตำแหน่ง/ระดับ" value="${defaultPosition}"></td>
                <td><input class="form-control group_allowance format-number-2point" type="text" placeholder="ระบุ ค่าเบี้ยเลี้ยง"></td>
                <td><input class="form-control group_days format-number-step-05" type="number" placeholder="ระบุ วัน" step="0.5" min="0"></td>
                <td><input class="form-control group_total_allowance" type="text" placeholder="ระบุ รวม ค่าเบี้ยเลี้ยง" disabled></td>
                <td><input class="form-control group_accommodation format-number-2point" type="text" placeholder="ระบุ ค่าเช่าที่พัก"></td>
                <td><input class="form-control group_accommodation_days format-number-0point" type="number" placeholder="ระบุ วัน" step="1" min="0"></td>
                <td><input class="form-control group_accommodation_total" type="text" placeholder="ระบุ รวม ค่าเช่าที่พัก" disabled></td>
                <td><input class="form-control group_transport format-number-2point" type="text" placeholder="ระบุ ค่าพาหนะ"></td>
                <td><input class="form-control group_other_expenses format-number-2point" type="text" placeholder="ระบุ ค่าใช้จ่ายอื่นๆ"></td>
                <td><input class="form-control group_meal format-number-2point" type="text" placeholder="ระบุ มื้อละ"></td>
                <td><input class="form-control group_meal_count format-number-1point" type="text" placeholder="ระบุ จำนวน"></td>
                <td><input class="form-control group_meal_total" type="text" placeholder="ระบุ รวม" disabled></td>
                <td><input class="form-control group_sum" type="text" placeholder="0.00" disabled></td>
            `;
      tableBody.appendChild(newRow);
      bindAutoCalc(newRow);
      updateRowNumbers();
      updateGroupPeopleCount();
      document.querySelectorAll(".format-number-2point").forEach((input) => {
        input.addEventListener("blur", function () {
          let val = parseFloat(this.value.replace(/,/g, ""));
          if (!isNaN(val)) {
            this.value = val.toFixed(2);
          } else {
            this.value = "";
          }
        });
      });
      document.querySelectorAll(".format-number-1point").forEach((input) => {
        input.addEventListener("blur", function () {
          let val = parseFloat(this.value.replace(/,/g, ""));
          if (!isNaN(val)) {
            this.value = val.toFixed(1);
          } else {
            this.value = "";
          }
        });
      });
      document.querySelectorAll('.format-number-0point').forEach(input => {
        input.addEventListener('input', function () {
          this.value = this.value.replace(/\./g, '');
        });
        input.addEventListener('blur', function () {
          let val = parseInt(this.value.replace(/,/g, ''), 10);

          if (!isNaN(val)) {
            this.value = val;
          } else {
            this.value = '';
          }
        });
      });

      document.addEventListener('blur', function (e) {
        if (e.target.classList.contains('format-number-step-05')) {
          const value = e.target.value;

          if (!value) {
            e.target.classList.remove('is-invalid');
            e.target.removeAttribute('title'); // ลบ tooltip ถ้าไม่มี error
            return;
          }

          const val = parseFloat(value);
          const decimal = (val % 1).toFixed(1);

          if (decimal === '0.0' || decimal === '0.5') {
            e.target.value = val.toFixed(1);
            e.target.classList.remove('is-invalid');
            e.target.removeAttribute('title'); // ลบ tooltip
          } else {
            e.target.classList.add('is-invalid');
            e.target.setAttribute('title', 'กรอกได้เฉพาะจำนวน .0 หรือ .5 เท่านั้น'); // ใส่ tooltip
          }
        }
      }, true);
    }

    function numberToThaiText(number) {
      number = Number(number).toFixed(2);
      const txtNumArr = [
        "ศูนย์",
        "หนึ่ง",
        "สอง",
        "สาม",
        "สี่",
        "ห้า",
        "หก",
        "เจ็ด",
        "แปด",
        "เก้า",
      ];
      const txtDigitArr = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
      let [baht, satang] = number.split(".");
      let bahtText = "";
      let satangText = "";

      function readNumber(num) {
        let text = "";
        num = num.replace(/^0+/, ""); // ตัด 0 หน้าสุด
        if (num.length === 0) return "";
        let len = num.length;
        for (let i = 0; i < len; i++) {
          let n = parseInt(num.charAt(i));
          if (n !== 0) {
            if (i === len - 1 && n === 1 && len > 1) {
              text += "เอ็ด";
            } else if (i === len - 2 && n === 2) {
              text += "ยี่";
            } else if (i === len - 2 && n === 1) {
              text += "";
            } else {
              text += txtNumArr[n];
            }
            text += txtDigitArr[len - 1 - i];
          }
        }
        return text;
      }

      // รองรับหลักล้านขึ้นไป
      function readNumberWithMillion(num) {
        let text = "";
        let million = "";
        while (num.length > 6) {
          let sub = num.slice(0, num.length - 6);
          million += readNumber(sub) + "ล้าน";
          num = num.slice(-6);
        }
        text = million + readNumber(num);
        return text;
      }

      baht = baht.replace(/^0+/, "") || "0";
      if (parseInt(baht, 10) === 0) bahtText = "ศูนย์บาท";
      else bahtText = readNumberWithMillion(baht) + "บาท";

      if (parseInt(satang, 10) === 0) satangText = "ถ้วน";
      else satangText = readNumber(satang) + "สตางค์";

      return bahtText + satangText;
    }

    function updateTotalAmount() {
      let totalAmount = 0;
      tableBody.querySelectorAll("tr.group-people-list").forEach((row) => {
        const sumInput = row.querySelector(".group_sum");
        if (sumInput) {
          const val = parseFloat(sumInput.value.replace(/,/g, "")) || 0;
          totalAmount += val;
        }
      });
      const totalInputs = document.querySelectorAll("#expenses_group_total");
      if (totalInputs.length > 0) {
        totalInputs[0].value = totalAmount.toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        });
      }
      const totalText = document.getElementById("expenses_group_total_text");
      if (totalText) {
        if (totalText.tagName === "INPUT") {
          totalText.value = numberToThaiText(totalAmount);
        } else {
          totalText.textContent = numberToThaiText(totalAmount);
        }
      }
    }

    function bindAutoCalc(row) {
      const perDiemInput = row.children[4].querySelector("input"); // ค่าเบี้ยเลี้ยง/วัน
      const perDiemDayInput = row.children[5].querySelector("input"); // จำนวน(วัน)
      const perDiemSumInput = row.children[6].querySelector("input"); // รวม(บาท)
      const hotelInput = row.children[7].querySelector("input"); // ค่าเช่าที่พัก/วัน
      const hotelDayInput = row.children[8].querySelector("input"); // จำนวน(วัน)
      const hotelSumInput = row.children[9].querySelector("input"); // รวม(บาท)
      const vehicleInput = row.children[10].querySelector("input"); // ค่าพาหนะ
      const otherInput = row.children[11].querySelector("input"); // ค่าใช้จ่ายอื่นๆ
      const mealInput = row.children[12].querySelector("input"); // มื้อละ
      const mealQtyInput = row.children[13].querySelector("input"); // จำนวน(มื้อ)
      const mealSumInput = row.children[14].querySelector("input"); // รวม(บาท) หักอาหาร
      const totalInput = row.children[15].querySelector("input"); // รวม(บาท) ทั้งหมด

      function calc() {
        // คำนวณรวมค่าเบี้ยเลี้ยง
        const perDiem = parseFloat(perDiemInput.value) || 0;
        const perDiemDay = parseFloat(perDiemDayInput.value) || 0;
        const perDiemSum = perDiem * perDiemDay;
        perDiemSumInput.value = perDiemSum
          ? perDiemSum.toLocaleString("en-US", { minimumFractionDigits: 2 })
          : "";

        // คำนวณรวมค่าเช่าที่พัก
        const hotel = parseFloat(hotelInput.value) || 0;
        const hotelDay = parseFloat(hotelDayInput.value) || 0;
        const hotelSum = hotel * hotelDay;
        hotelSumInput.value = hotelSum
          ? hotelSum.toLocaleString("en-US", { minimumFractionDigits: 2 })
          : "";

        // คำนวณหักค่าอาหาร
        const meal = parseFloat(mealInput.value) || 0;
        const mealQty = parseFloat(mealQtyInput.value) || 0;
        const mealSum = meal * mealQty;
        mealSumInput.value = mealSum
          ? mealSum.toLocaleString("en-US", { minimumFractionDigits: 2 })
          : "";

        // รวมทั้งหมด
        const vehicle = parseFloat(vehicleInput.value) || 0;
        const other = parseFloat(otherInput.value) || 0;
        const total = perDiemSum + hotelSum + vehicle + other - mealSum;
        totalInput.value = total
          ? total.toLocaleString("en-US", { minimumFractionDigits: 2 })
          : "0.00";

        updateTotalAmount();
        if (typeof calcExpensesTotal === "function") calcExpensesTotal();
      }

      [
        perDiemInput,
        perDiemDayInput,
        hotelInput,
        hotelDayInput,
        vehicleInput,
        otherInput,
        mealInput,
        mealQtyInput,
      ].forEach((input) => {
        input.addEventListener("input", calc);
      });
    }

    // delBtn.addEventListener("click", (e) => {
    //   e.preventDefault();
    //   // ไม่อนุญาตให้ลบแถวแรก (first-group-row)
    //   const checked = tableBody.querySelectorAll(
    //     ".group-checkbox:not(.first-group-row) input.form-check-input:checked"
    //   );
    //   if (!checked.length)
    //     return alert("กรุณาเลือกแถวที่ต้องการลบ (ยกเว้นแถวแรก)");
    //   checked.forEach((cb) => cb.closest("tr")?.remove());
    //   updateRowNumbers();
    //   updateTotalAmount();
    //   updateGroupPeopleCount();
    //   calcExpensesTotal();
    // });

    // addBtn.addEventListener("click", (e) => {
    //   e.preventDefault();
    //   createRow();
    //   updateTotalAmount();
    // });

    // createRow();
    // updateRowNumbers();
    // updateTotalAmount();
    yesnoCheck();

    // ====== สำหรับตารางรายละเอียดการเดินทาง (list-details) ======
    if (!window.travelExpenseMultiManager) {
      const tableBodyDetails = document.querySelector("table .list-details")
        ? document.querySelector("table .list-details").closest("tbody")
        : document.querySelectorAll("table tbody")[1];

      const addBtnDetails = document.querySelector(".addTable-list-details");
      const delBtnDetails = document.querySelector(".delTable-list-details");
      const checkAllBoxDetails = document.querySelector(".check-all-details");
      if (checkAllBoxDetails && tableBodyDetails) {
        checkAllBoxDetails.addEventListener("change", function () {
          const checked = this.checked;
          tableBodyDetails
            .querySelectorAll(".row-check-details input.form-check-input")
            .forEach((cb) => (cb.checked = checked));
        });
      }

      function bindFlatpickr(row) {
        row.querySelectorAll(".list-detail-date").forEach((input) => {
          flatpickr(input, {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: {
              firstDayOfWeek: 1,
              weekdays: {
                shorthand: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส"],
                longhand: [
                  "อาทิตย์",
                  "จันทร์",
                  "อังคาร",
                  "พุธ",
                  "พฤหัสบดี",
                  "ศุกร์",
                  "เสาร์",
                ],
              },
              months: {
                shorthand: [
                  "ม.ค.",
                  "ก.พ.",
                  "มี.ค.",
                  "เม.ย.",
                  "พ.ค.",
                  "มิ.ย.",
                  "ก.ค.",
                  "ส.ค.",
                  "ก.ย.",
                  "ต.ค.",
                  "พ.ย.",
                  "ธ.ค.",
                ],
                longhand: [
                  "มกราคม",
                  "กุมภาพันธ์",
                  "มีนาคม",
                  "เมษายน",
                  "พฤษภาคม",
                  "มิถุนายน",
                  "กรกฎาคม",
                  "สิงหาคม",
                  "กันยายน",
                  "ตุลาคม",
                  "พฤศจิกายน",
                  "ธันวาคม",
                ],
              },
            },
            formatDate: (date) => {
              const day = String(date.getDate()).padStart(2, "0");
              const month = String(date.getMonth() + 1).padStart(2, "0");
              const yearBE = date.getFullYear() + 543;
              return `${day}-${month}-${yearBE}`;
            },
            parseDate: (datestr) => {
              const [d, m, y] = datestr.replace(/[:\s]/g, "-").split("-");
              return new Date(+y - 543, +m - 1, +d);
            },
            onReady(selectedDates, dateStr, instance) {
              const updateThaiYearCalendar = () => {
                if (instance.currentYearElement) {
                  let year = parseInt(instance.currentYearElement.value);
                  if (!isNaN(year) && year < 2400) {
                    instance.currentYearElement.value = year + 543;
                  }
                }
              };
              updateThaiYearCalendar();
              instance.config.onMonthChange.push(updateThaiYearCalendar);
              instance.config.onYearChange.push(updateThaiYearCalendar);
              instance.config.onOpen = [updateThaiYearCalendar];
              instance.config.onValueUpdate = [updateThaiYearCalendar];
            },
          });
        });

        const timeStartInput = row.querySelector(".list-detail-time-start");
        const timeEndInput = row.querySelector(".list-detail-time-end");
        const timeDepartInput = row.querySelector(".list-detail-time-depart");
        const timeArriveInput = row.querySelector(".list-detail-time-arrive");

        let fpStart = null;
        let fpEnd = null;
        let fpDepart = null;
        let fpArrive = null;

        row
          .querySelectorAll(
            ".list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive"
          )
          .forEach((input) => {
            const fp = flatpickr(input, {
              enableTime: true,
              noCalendar: true,
              dateFormat: "H:i",
              time_24hr: true,
              allowInput: true,
            });
            if (input.classList.contains("list-detail-time-start"))
              fpStart = fp;
            if (input.classList.contains("list-detail-time-end")) fpEnd = fp;
            if (input.classList.contains("list-detail-time-depart"))
              fpDepart = fp;
            if (input.classList.contains("list-detail-time-arrive"))
              fpArrive = fp;
          });

        const clearTimeInput = (input) => {
          if (!input) return;
          const hadValue = Boolean(input.value);
          input.value = "";
          if (input._flatpickr && (hadValue || input._flatpickr.input?.value)) {
            input._flatpickr.clear();
          }
        };

        const getMinDepartArrive = () => {
          const departVal = timeDepartInput?.value;
          const arriveVal = timeArriveInput?.value;
          if (departVal && arriveVal) {
            const [h1, m1] = departVal.split(":").map(Number);
            const [h2, m2] = arriveVal.split(":").map(Number);
            return h1 * 60 + m1 < h2 * 60 + m2 ? departVal : arriveVal;
          }
          return departVal || arriveVal || null;
        };

        const getMaxStartEnd = () => {
          const startVal = timeStartInput?.value;
          const endVal = timeEndInput?.value;
          if (startVal && endVal) {
            const [h1, m1] = startVal.split(":").map(Number);
            const [h2, m2] = endVal.split(":").map(Number);
            return h1 * 60 + m1 > h2 * 60 + m2 ? startVal : endVal;
          }
          return startVal || endVal || null;
        };

        const updateMinTimeDepartArrive = () => {
          const maxStartEnd = getMaxStartEnd();
          if (fpDepart) fpDepart.set("minTime", maxStartEnd);
          if (fpArrive) fpArrive.set("minTime", maxStartEnd);
        };
        const bindTimeRangeUpdate = (input, handler) => {
          if (!input) return;
          ["input", "change"].forEach((evtName) =>
            input.addEventListener(evtName, handler)
          );
        };

        bindTimeRangeUpdate(timeStartInput, updateMinTimeDepartArrive);
        bindTimeRangeUpdate(timeEndInput, updateMinTimeDepartArrive);
        updateMinTimeDepartArrive();

        const updateMaxTimeStartEnd = () => {
          const minDepartArrive = getMinDepartArrive();
          if (fpStart) fpStart.set("maxTime", minDepartArrive);
          if (fpEnd) fpEnd.set("maxTime", minDepartArrive);
        };
        bindTimeRangeUpdate(timeDepartInput, updateMaxTimeStartEnd);
        bindTimeRangeUpdate(timeArriveInput, updateMaxTimeStartEnd);
        updateMaxTimeStartEnd();

        const attachMutualExclusiveListeners = (input, handler) => {
          if (!input) return;
          ["input", "change", "blur"].forEach((evtName) =>
            input.addEventListener(evtName, handler)
          );
        };

        const handleTimeInputDisableStartEnd = (evt) => {
          const timeStart = row.querySelector(".list-detail-time-start");
          const timeEnd = row.querySelector(".list-detail-time-end");
          if (!timeStart || !timeEnd) return;

          let startHasValue = Boolean(timeStart.value);
          let endHasValue = Boolean(timeEnd.value);

          if (startHasValue && endHasValue) {
            if (evt?.target === timeStart) {
              clearTimeInput(timeEnd);
              endHasValue = false;
            } else if (evt?.target === timeEnd) {
              clearTimeInput(timeStart);
              startHasValue = false;
            } else {
              clearTimeInput(timeEnd);
              endHasValue = false;
            }
          }

          if (startHasValue) {
            clearTimeInput(timeEnd);
            timeEnd.disabled = true;
            timeStart.disabled = false;
          } else if (endHasValue) {
            clearTimeInput(timeStart);
            timeStart.disabled = true;
            timeEnd.disabled = false;
          } else {
            timeStart.disabled = false;
            timeEnd.disabled = false;
          }
        };

        const timeStart = row.querySelector(".list-detail-time-start");
        const timeEnd = row.querySelector(".list-detail-time-end");
        attachMutualExclusiveListeners(
          timeStart,
          handleTimeInputDisableStartEnd
        );
        attachMutualExclusiveListeners(timeEnd, handleTimeInputDisableStartEnd);
        handleTimeInputDisableStartEnd();

        const handleTimeInputDisableDepartArrive = (evt) => {
          const timeDepart = row.querySelector(".list-detail-time-depart");
          const timeArrive = row.querySelector(".list-detail-time-arrive");
          if (!timeDepart || !timeArrive) return;

          let departHasValue = Boolean(timeDepart.value);
          let arriveHasValue = Boolean(timeArrive.value);

          if (departHasValue && arriveHasValue) {
            if (evt?.target === timeDepart) {
              clearTimeInput(timeArrive);
              arriveHasValue = false;
            } else if (evt?.target === timeArrive) {
              clearTimeInput(timeDepart);
              departHasValue = false;
            } else {
              clearTimeInput(timeArrive);
              arriveHasValue = false;
            }
          }

          if (departHasValue) {
            clearTimeInput(timeArrive);
            timeArrive.disabled = true;
            timeDepart.disabled = false;
          } else if (arriveHasValue) {
            clearTimeInput(timeDepart);
            timeDepart.disabled = true;
            timeArrive.disabled = false;
          } else {
            timeDepart.disabled = false;
            timeArrive.disabled = false;
          }
        };

        const timeDepart = row.querySelector(".list-detail-time-depart");
        const timeArrive = row.querySelector(".list-detail-time-arrive");
        attachMutualExclusiveListeners(
          timeDepart,
          handleTimeInputDisableDepartArrive
        );
        attachMutualExclusiveListeners(
          timeArrive,
          handleTimeInputDisableDepartArrive
        );
        handleTimeInputDisableDepartArrive();
      }

      function checkVehicleRequired() {
        const car = document.getElementById("expenses_vehicle");
        const motorcycle = document.getElementById("expenses_motorcycle");
        const requiredLabel = document.getElementById(
          "expenses_vehicle_number_required"
        );

        if (!tableBodyDetails || !requiredLabel) return;

        if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
          requiredLabel.style.display = "inline";
          $(".display-vehicle").show();
          if (
            tableBodyDetails.querySelectorAll("tr.list-details").length === 0
          ) {
            createRowDetails();
          }
        } else {
          requiredLabel.style.display = "none";
          $(".display-vehicle").hide();
          tableBodyDetails
            .querySelectorAll("tr.list-details")
            .forEach((row) => row.remove());
          updateTotalAmountDetails();
        }
      }

      function handleVehicleCheckboxChange(e) {
        const car = document.getElementById("expenses_vehicle");
        const motorcycle = document.getElementById("expenses_motorcycle");
        if (e.target.id === "expenses_vehicle" && car?.checked) {
          if (motorcycle) motorcycle.checked = false;
        } else if (
          e.target.id === "expenses_motorcycle" &&
          motorcycle?.checked
        ) {
          if (car) car.checked = false;
        }
        checkVehicleRequired();
      }

      function createRowDetails() {
        if (!tableBodyDetails) return;
        const newRow = document.createElement("tr");
        newRow.classList.add("list-details");

        let defaultPerKm = "";
        const car = document.getElementById("expenses_vehicle");
        const motorcycle = document.getElementById("expenses_motorcycle");
        if (car && car.checked) {
          defaultPerKm = "5.5";
        } else if (motorcycle && motorcycle.checked) {
          defaultPerKm = "3.0";
        }

        newRow.innerHTML = `
                <td class="row-check-details"><input class="form-check-input" type="checkbox" aria-label="..."></td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                            <input type="text" class="form-control date-input list-detail-date" placeholder="ระบุวัน เดือน ปี">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-start" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-end" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-depart" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-arrive" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td><input type="number" class="form-control distance list-detail-distance" min="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
                <td><input type="number" class="form-control distance list-detail-compensation-per-km" min="0.1" placeholder="กรุณาระบุ เงินชดเชย/กม" value="${defaultPerKm}" disabled></td>
                <td><input type="number" class="form-control compensation sum-col-details list-detail-compensation" placeholder="เงินชดเชย (บาท)"></td>
                <td><input class="form-control remark-detail" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
            `;

        tableBodyDetails.appendChild(newRow);

        bindFlatpickr(newRow);
        bindAutoCalcDetails(newRow);
      }

      function updateTotalAmountDetails() {
        if (!tableBodyDetails) return;
        let totalAmount = 0;
        tableBodyDetails.querySelectorAll("tr.list-details").forEach((row) => {
          const sumInput = row.querySelector(".sum-col-details");
          if (sumInput) {
            const val = parseFloat(sumInput.value.replace(/,/g, "")) || 0;
            totalAmount += val;
          }
        });

        const tfoot = tableBodyDetails.parentElement.querySelector("tfoot");
        if (tfoot) {
          const totalInput = tfoot.querySelector("#total_amount_details");
          if (totalInput) {
            totalInput.value = totalAmount.toLocaleString("en-US", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            });
          }
          const totalTextInput = tfoot.querySelector("#total_text_details");
          if (totalTextInput) {
            totalTextInput.value = numberToThaiText(totalAmount);
          }
        }

        if (typeof calcExpensesTotal === "function") calcExpensesTotal();
      }

      function bindAutoCalcDetails(row) {
        row
          .querySelectorAll(
            ".compensation, .compensation-detail, .sum-col-details, .list-detail-compensation"
          )
          .forEach((input) => {
            input.addEventListener("input", updateTotalAmountDetails);
            input.addEventListener("blur", function () {
              const val = parseFloat(this.value);
              this.value = !isNaN(val) ? val.toFixed(2) : "";
            });
          });

        row
          .querySelectorAll(
            ".distance, .distance-detail, .list-detail-distance"
          )
          .forEach((input) => {
            input.addEventListener("blur", function () {
              const val = parseFloat(this.value);
              this.value = !isNaN(val) ? val.toFixed(2) : "";
            });
          });

        const distanceInput = row.querySelector(".list-detail-distance");
        const perKmInput = row.querySelector(
          ".list-detail-compensation-per-km"
        );
        const sumInput = row.querySelector(".sum-col-details");

        const autoCalcSumDetails = () => {
          const distance = parseFloat(distanceInput?.value) || 0;
          const perKm = parseFloat(perKmInput?.value) || 0;
          const sum = distance * perKm;
          if (sumInput) sumInput.value = sum ? sum.toFixed(2) : "";
          updateTotalAmountDetails();
        };

        if (distanceInput && perKmInput && sumInput) {
          distanceInput.addEventListener("input", autoCalcSumDetails);
          perKmInput.addEventListener("input", autoCalcSumDetails);
        }
      }

      if (addBtnDetails) {
        addBtnDetails.addEventListener("click", (e) => {
          e.preventDefault();
          createRowDetails();
          updateTotalAmountDetails();
        });
      }

      const carCheckbox = document.getElementById("expenses_vehicle");
      const motorcycleCheckbox = document.getElementById("expenses_motorcycle");
      const updateCompensationPerKm = () => {
        let value = "";
        if (carCheckbox && carCheckbox.checked) {
          value = "5.5";
        } else if (motorcycleCheckbox && motorcycleCheckbox.checked) {
          value = "3.0";
        }
        document
          .querySelectorAll(".list-detail-compensation-per-km")
          .forEach((input) => {
            input.value = value;
            input.dispatchEvent(new Event("input", { bubbles: true }));
          });
      };

      if (carCheckbox)
        carCheckbox.addEventListener("change", updateCompensationPerKm);
      if (motorcycleCheckbox)
        motorcycleCheckbox.addEventListener("change", updateCompensationPerKm);

      if (delBtnDetails && tableBodyDetails) {
        delBtnDetails.addEventListener("click", (e) => {
          e.preventDefault();
          const checked = tableBodyDetails.querySelectorAll(
            ".row-check-details input.form-check-input:checked"
          );
          if (!checked.length) {
            alert("กรุณาเลือกแถวที่ต้องการลบ");
            return;
          }
          checked.forEach((cb) => cb.closest("tr")?.remove());
          updateTotalAmountDetails();
          if (typeof calcExpensesTotal === "function") calcExpensesTotal();
        });
      }

      checkVehicleRequired();
      document
        .getElementById("expenses_vehicle")
        ?.addEventListener("change", handleVehicleCheckboxChange);
      document
        .getElementById("expenses_motorcycle")
        ?.addEventListener("change", handleVehicleCheckboxChange);

      createRowDetails();
      updateTotalAmountDetails();
    }
    /* Legacy global handlers (kept for reference, disabled because
       dynamicFormManager now manages these interactions per form.)
    function checkVehicleRequired() {
      const car = document.getElementById("expenses_vehicle");
      const motorcycle = document.getElementById("expenses_motorcycle");
      const requiredLabel = document.getElementById(
        "expenses_vehicle_number_required"
      );

      const tableBodyDetails = document.querySelector("table .list-details")
        ? document.querySelector("table .list-details").closest("tbody")
        : document.querySelectorAll("table tbody")[1];
      if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
        requiredLabel.style.display = "inline";
        $(".display-vehicle").show();
        // ถ้ายังไม่มี row ใน table ให้เพิ่มแถวใหม่
        if (
          tableBodyDetails &&
          tableBodyDetails.querySelectorAll("tr.list-details").length === 0
        ) {
          if (typeof window.createRowDetails === "function") {
            window.createRowDetails();
          } else if (typeof createRowDetails === "function") {
            createRowDetails();
          }
        }
      } else {
        requiredLabel.style.display = "none";
        $(".display-vehicle").hide();
        // ลบแถวในตารางรายละเอียดการเดินทางเมื่อไม่เลือกประเภทรถใด ๆ
        if (tableBodyDetails) {
          tableBodyDetails
            .querySelectorAll("tr.list-details")
            .forEach((row) => row.remove());
        }
        // อัปเดทยอดรวมใหม่หลังลบ
        if (typeof updateTotalAmountDetails === "function")
          updateTotalAmountDetails();
      }
    }

    function handleVehicleCheckboxChange(e) {
      const car = document.getElementById("expenses_vehicle");
      const motorcycle = document.getElementById("expenses_motorcycle");
      if (e.target.id === "expenses_vehicle" && car.checked) {
        motorcycle.checked = false;
      } else if (e.target.id === "expenses_motorcycle" && motorcycle.checked) {
        car.checked = false;
      }
      checkVehicleRequired();
    }

    checkVehicleRequired();
    document
      .getElementById("expenses_vehicle")
      ?.addEventListener("change", handleVehicleCheckboxChange);
    document
      .getElementById("expenses_motorcycle")
      ?.addEventListener("change", handleVehicleCheckboxChange);
    // ฟังก์ชันสร้างแถวใหม่
    function createRowDetails() {
      const newRow = document.createElement("tr");
      newRow.classList.add("list-details");
      // ตรวจสอบ vehicle
      let defaultPerKm = "";
      const car = document.getElementById("expenses_vehicle");
      const motorcycle = document.getElementById("expenses_motorcycle");
      if (car && car.checked) {
        defaultPerKm = "5.5";
      } else if (motorcycle && motorcycle.checked) {
        defaultPerKm = "3.0";
      }
      newRow.innerHTML = `
                <td class="row-check-details"><input class="form-check-input" type="checkbox" aria-label="..."></td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                            <input type="text" class="form-control date-input list-detail-date" placeholder="ระบุวัน เดือน ปี">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-start" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-end" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-depart" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                            <input type="text" class="form-control time-input list-detail-time-arrive" placeholder="ระบุเวลา">
                        </div>
                    </div>
                </td>
                <td><input type="number" class="form-control distance list-detail-distance" min="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
                <td><input type="number" class="form-control distance list-detail-compensation-per-km" min="0.1" placeholder="กรุณาระบุ เงินชดเชย/กม" value="${defaultPerKm}" disabled></td>
                <td><input type="number" class="form-control compensation sum-col-details list-detail-compensation" placeholder="เงินชดเชย (บาท)"></td>
                <td><input class="form-control remark-detail" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
            `;
      tableBodyDetails.appendChild(newRow);

      bindFlatpickr(newRow);
      bindAutoCalcDetails(newRow);
    }

    // ฟังก์ชันคำนวณยอดรวมและแปลงเป็นตัวอักษร
    function updateTotalAmountDetails() {
      let totalAmount = 0;
      tableBodyDetails.querySelectorAll("tr.list-details").forEach((row) => {
        const sumInput = row.querySelector(".sum-col-details");
        if (sumInput) {
          const val = parseFloat(sumInput.value.replace(/,/g, "")) || 0;
          totalAmount += val;
        }
      });
      // ช่องรวมยอดใน tfoot
      const tfoot = tableBodyDetails.parentElement.querySelector("tfoot");
      if (tfoot) {
        const totalInputs = tfoot.querySelectorAll(
          "#total_amount_details:disabled"
        );
        if (totalInputs.length > 0) {
          totalInputs[0].value = totalAmount.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
        }
        // แสดงจำนวนเงินทั้งสิ้น (ตัวอักษร)
        const totalTextInput = tfoot.querySelector("#total_text_details");
        if (totalTextInput) {
          totalTextInput.value = numberToThaiText(totalAmount);
        }
      }
      if (typeof calcExpensesTotal === "function") calcExpensesTotal();
    }
    // bind auto calc เฉพาะช่องค่าชดเชย
    function bindAutoCalcDetails(row) {
      // Format compensation (เงินชดเชย) เป็น xx.xx
      const compensationInputs = row.querySelectorAll(
        ".compensation, .compensation-detail, .sum-col-details, .list-detail-compensation"
      );
      compensationInputs.forEach((input) => {
        input.addEventListener("input", updateTotalAmountDetails);
        input.addEventListener("blur", function () {
          let val = parseFloat(this.value);
          if (!isNaN(val)) {
            this.value = val.toFixed(2);
          } else {
            this.value = "";
          }
        });
      });

      // Format distance (ระยะทาง) เป็น xx.xx
      const distanceInputs = row.querySelectorAll(
        ".distance, .distance-detail, .list-detail-distance"
      );
      distanceInputs.forEach((input) => {
        input.addEventListener("blur", function () {
          let val = parseFloat(this.value);
          if (!isNaN(val)) {
            this.value = val.toFixed(2);
          } else {
            this.value = "";
          }
        });
      });

      // คำนวณเงินชดเชยอัตโนมัติ: ระยะทาง * เงินชดเชย/กม = รวมเงินชดเชย
      const distanceInput = row.querySelector(".list-detail-distance");
      const perKmInput = row.querySelector(".list-detail-compensation-per-km");
      const sumInput = row.querySelector(".sum-col-details");
      function autoCalcSumDetails() {
        const distance = parseFloat(distanceInput?.value) || 0;
        const perKm = parseFloat(perKmInput?.value) || 0;
        const sum = distance * perKm;
        if (sumInput) sumInput.value = sum ? sum.toFixed(2) : "";
        updateTotalAmountDetails();
      }
      if (distanceInput && perKmInput && sumInput) {
        distanceInput.addEventListener("input", autoCalcSumDetails);
        perKmInput.addEventListener("input", autoCalcSumDetails);
      }
    }

    // ปุ่มเพิ่มแถว (ต้องมีปุ่ม .addTable-list-details ใน HTML)
    if (addBtnDetails) {
      addBtnDetails.addEventListener("click", (e) => {
        e.preventDefault();
        createRowDetails();
        updateTotalAmountDetails();
      });
    }

    // เพิ่ม event listener สำหรับ checkbox ประเภทรถ
    const carCheckbox = document.getElementById("expenses_vehicle");
    const motorcycleCheckbox = document.getElementById("expenses_motorcycle");
    function updateCompensationPerKm() {
      let value = "";
      if (carCheckbox && carCheckbox.checked) {
        value = "5.5";
      } else if (motorcycleCheckbox && motorcycleCheckbox.checked) {
        value = "3.0";
      }
      // อัปเดตทุกแถวที่มี .list-detail-compensation-per-km และคำนวณใหม่
      document
        .querySelectorAll(".list-detail-compensation-per-km")
        .forEach((input) => {
          input.value = value;
          // trigger event เพื่อคำนวณใหม่
          input.dispatchEvent(new Event("input", { bubbles: true }));
        });
    }
    if (carCheckbox)
      carCheckbox.addEventListener("change", updateCompensationPerKm);
    if (motorcycleCheckbox)
      motorcycleCheckbox.addEventListener("change", updateCompensationPerKm);

    // ปุ่มลบแถว (ต้องมีปุ่ม .delTable-list-details ใน HTML)
    if (delBtnDetails) {
      delBtnDetails.addEventListener("click", (e) => {
        e.preventDefault();
        const checked = tableBodyDetails.querySelectorAll(
          ".row-check-details input.form-check-input:checked"
        );
        if (!checked.length) return alert("กรุณาเลือกแถวที่ต้องการลบ");
        checked.forEach((cb) => cb.closest("tr")?.remove());
        updateTotalAmountDetails();
        calcExpensesTotal();
      });
    }

    // สร้างแถวแรก
    createRowDetails();
    updateTotalAmountDetails();
    
    */

    const approveButtons = `
            <a href="withdraw-money-list.php" class="btn btn-warning m-1">
                <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
            </a>
            <button id="btn-add-one" type="button" class="btn btn-primary m-1">
                <i class="bi bi-person-plus"></i> + เพิ่มข้อมูล เดินทางคนเดียว
            </button>
            <button id="btn-add-group" type="button" class="btn btn-info m-1">
                <i class="bi bi-people"></i> + เพิ่มข้อมูล แบบเดินทางเป็นหมู่คณะ
            </button>
            <button id="btn-create" type="button" class="btn btn-success m-1">
                <i class="bi bi-check-circle"></i> ขออนุมัติ
            </button>
        `;
    document.getElementById("button-container").innerHTML = approveButtons;

    // ดึงข้อมูลที่ต้องการดู
    // try {
    //   const response = await fetch(
    //     `../controllers/withdraws/wrd_controller.php?action=get_one&id=${planId}`,
    //     {
    //       method: "GET",
    //       headers: {
    //         "Content-Type": "application/json",
    //         "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
    //           ?.content,
    //       },
    //     }
    //   );

    //   if (!response.ok) {
    //     throw new Error("ไม่สามารถดึงข้อมูลได้");
    //   }

    //   const data = await response.json();

    //   /* ปุ่มอนุมัติ ไม่อนุมัติ */
    //   // if (data.main_data.st_hf === 'waiting' && data_user.approve_finance === 'active') {
    //   //     const approveButtons = `
    //   //         <a href="withdraw-money-list.php" class="btn btn-warning m-1">
    //   //             <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
    //   //          </a>
    //   //         <button id="btn-approve" type="button" class="btn btn-success m-1">
    //   //             <i class="bi bi-check-circle"></i> อนุมัติ
    //   //         </button>
    //   //         <button id="btn-reject" type="button" class="btn btn-danger m-1">
    //   //             <i class="bi bi-x-circle"></i> ไม่อนุมัติ
    //   //         </button>
    //   //     `;
    //   //     document.getElementById('button-container').innerHTML = approveButtons;
    //   // } else {


    //   // }
    //   /* ปุ่มอนุมัติ ไม่อนุมัติ */

    //   /* ข้อมูล main */
    //   $(".plan_number").text(data.main_data.plan_number || "");

    //   const planFieldValues = {
    //     plan_id: data.main_data.id || "",
    //     plan_number: data.main_data.plan_number || "",
    //     plan_date: formatThaiDate(data.main_data.st_hf_date) || "",
    //   };

    //   if (data.child_data && Array.isArray(data.child_data)) {
    //     const sumTotal = data.child_data.reduce(
    //       (sum, item) => sum + (parseFloat(item.total) || 0),
    //       0
    //     );
    //     planFieldValues.plan_total = sumTotal.toLocaleString("en-US", {
    //       minimumFractionDigits: 2,
    //       maximumFractionDigits: 2,
    //     });
    //   }

    //   setPlanFieldState(planFieldValues);
    //   // document.getElementById('plan_form_name').value = data.main_data.plan_form_name || '';
    //   // document.querySelector('[name="plan_start"]').value = formatThaiDatetime(data.main_data.plan_start) || '';
    //   // document.querySelector('[name="plan_end"]').value = formatThaiDatetime(data.main_data.plan_end) || '';
    //   // document.getElementById('depart_code').value = data.main_data.depart_code || '';
    //   // document.getElementById('full_name').value = data.main_data.full_name || '';
    //   // document.getElementById('position_name').value = data.main_data.position_name || '';
    //   // document.getElementById('level_name').value = data.main_data.level_name || '';
    //   // document.getElementById('affiliation_name').value = data.main_data.affiliation_name || '';
    //   /* ข้อมูล main */

    //   // เลือกเงินทุน
    //   if (data.main_data.fund) {
    //     $("#fund_select_label").val(
    //       data.main_data.fund.replace(/<[^>]+>/g, "")
    //     );
    //     $("#fund_html_label").html("");
    //   } else {
    //     $("#fund_select_label").val("เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)");
    //     $("#fund_html_label").html(
    //       '<span style="color:red;">*ไม่พบเงินทุน</span>'
    //     );
    //   }
    //   // เลือกโครงการ
    //   if (data.main_data.project) {
    //     $("#project_select_label").val(data.main_data.project);
    //   } else {
    //     document.getElementById("project_select_label").value = "";
    //   }

    //   // เลือกการนับวัน
    //   if (data.main_data.day_return == "t") {
    //     document.getElementById("day_return").checked = true;
    //   }
    //   if (data.main_data.money_receipt == "t") {
    //     document.getElementById("money_receipt").checked = true;
    //   }

    //   // กรอกเหตุผล
    //   document.getElementById("note").value = data.main_data.note || "";

    //   // สร้างตารางรายการ
    //   let grandTotal = 0;
    //   if (data.child_data && Array.isArray(data.child_data)) {
    //     const tableBody = document.querySelector("table tbody");
    //     data.child_data.forEach((item, key) => {
    //       const row = document.createElement("tr");
    //       row.innerHTML = `
    //                     <td class="text-center">${key + 1}</td>
    //                     <td>${item.wrd_mat_code || ""} - ${item.wrd_mat_name || ""
    //         }</td>
    //                     <td class="text-center">${item.qty || ""}</td>
    //                     <td class="text-center">${item.count_unit_name || ""
    //         }</td>
    //                     <td class="text-center">${Number(
    //           item.price || 0
    //         ).toLocaleString("en-US", {
    //           minimumFractionDigits: 2,
    //           maximumFractionDigits: 2,
    //         })}</td>
    //                     <td class="text-center">${Number(
    //           item.total || 0
    //         ).toLocaleString("en-US", {
    //           minimumFractionDigits: 2,
    //           maximumFractionDigits: 2,
    //         })}</td>
    //                 `;
    //       tableBody.appendChild(row);
    //       grandTotal += Number(item.total || 0);
    //     });

    //     // แสดงยอดรวมในช่อง total
    //     const totalElement = document.getElementById("total");
    //     if (totalElement) {
    //       totalElement.textContent = grandTotal.toLocaleString("en-US", {
    //         minimumFractionDigits: 2,
    //         maximumFractionDigits: 2,
    //       });
    //     }
    //   }

    //   if (window.travelExpenseMultiManager) {
    //     const baseForm = document.querySelector("form#advanceFrm");
    //     window.travelExpenseMultiManager.recalc(baseForm);
    //   }
    // } catch (error) {
    //   console.error("เกิดข้อผิดพลาดในการดึงข้อมูล:", error);
    //   Swal.fire({
    //     title: "เกิดข้อผิดพลาด",
    //     text: "ไม่สามารถดึงข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
    //     icon: "error",
    //   });
    //   return;
    // }

    // ดึงข้อมูลกลุ่ม (Group People)
    function getGroupPeopleData(form) {
      const yesCheck = form.querySelector("#yesCheck");
      let isGroup = yesCheck?.checked || false;

      if (!isGroup) {
        const expensesGroupSelected = form.querySelector(
          'input[name="expenses_group"]:checked'
        );
        if (expensesGroupSelected?.value === "2") {
          isGroup = true;
        }
      }

      const rows = form.querySelectorAll("tr.group-people-list");
      if (!isGroup || !rows.length) return [];

      const parseNumeric = (value) => {
        if (value === null || value === undefined) return 0;
        const cleaned = String(value).replace(/,/g, "").trim();
        if (!cleaned) return 0;
        const parsed = parseFloat(cleaned);
        return Number.isNaN(parsed) ? 0 : parsed;
      };

      const data = [];
      rows.forEach((row) => {
        const readValue = (selector) =>
          row.querySelector(selector)?.value?.trim() || "";
        const entry = {
          name: readValue(".group_name"),
          position: readValue(".group_position"),
          group_id_card: readValue(".group_id_card"),
          allowance: parseNumeric(readValue(".group_allowance")),
          days: parseNumeric(readValue(".group_days")),
          total_allowance: parseNumeric(readValue(".group_total_allowance")),
          accommodation: parseNumeric(readValue(".group_accommodation")),
          accommodation_days: parseNumeric(
            readValue(".group_accommodation_days")
          ),
          accommodation_total: parseNumeric(
            readValue(".group_accommodation_total")
          ),
          transport: parseNumeric(readValue(".group_transport")),
          other_expenses: parseNumeric(readValue(".group_other_expenses")),
          meal: parseNumeric(readValue(".group_meal")),
          meal_count: parseNumeric(readValue(".group_meal_count")),
          meal_total: parseNumeric(readValue(".group_meal_total")),
          sum: parseNumeric(readValue(".group_sum")),
        };

        const hasContent = Object.entries(entry).some(([key, value]) => {
          if (typeof value === "number") return value > 0;
          return Boolean(value);
        });

        if (hasContent) {
          data.push(entry);
        }
      });
      return data;
    }

    // ดึงข้อมูลรายละเอียดการเดินทาง (List Details)
    function getListDetailsData(form) {
      const rows = form.querySelectorAll("tr.list-details");
      const data = [];
      const parseNumeric = (value) => {
        if (value === null || value === undefined) return 0;
        const cleaned = String(value).replace(/,/g, "").trim();
        if (!cleaned) return 0;
        const parsed = parseFloat(cleaned);
        return Number.isNaN(parsed) ? 0 : parsed;
      };

      rows.forEach((row) => {
        const readValue = (selector) =>
          row.querySelector(selector)?.value?.trim() || "";
        const distance = parseNumeric(readValue(".list-detail-distance"));
        const compensationPerKm = parseNumeric(
          readValue(".list-detail-compensation-per-km")
        );
        const compensation = parseNumeric(
          readValue(".list-detail-compensation")
        );

        const entry = {
          date: readValue(".list-detail-date"),
          time_start: readValue(".list-detail-time-start"),
          time_end: readValue(".list-detail-time-end"),
          time_depart: readValue(".list-detail-time-depart"),
          time_arrive: readValue(".list-detail-time-arrive"),
          distance,
          compensation_per_km: compensationPerKm,
          compensation,
          remark: readValue(".remark-detail"),
        };

        const hasContent =
          entry.date ||
          entry.time_start ||
          entry.time_end ||
          entry.time_depart ||
          entry.time_arrive ||
          distance > 0 ||
          compensation > 0 ||
          entry.remark;

        if (hasContent) {
          data.push(entry);
        }
      });
      return data;
    }

    function getMainFormData(form) {
      const data = {};
      form
        .querySelectorAll("input[name], textarea[name], select[name]")
        .forEach((el) => {
          const { name } = el;
          if (!name) return;
          if (el.type === "checkbox") {
            data[name] = el.checked;
          } else if (el.type === "radio") {
            if (el.checked) data[name] = el.value;
          } else {
            data[name] = el.value;
          }
        });
      data["req_user_code"] = data_user.user_code;
      data['fund'] = $(form).find(".fund-select option:selected").text();
      data['fund_id'] = getFundNewValue(form);
      data['project'] = $(form).find(".project-select option:selected").text();
      data['project_id'] = getProjectNewValue(form);
      data['expenses_form_name'] = $("#expenses_form_name").val();
      data['expenses_start'] = $("[name='expenses_start']").val();
      data['expenses_end'] = $("[name='expenses_end']").val();
      return data;
    }

    function getFundNewValue(form) {
      var fund_id = $(form).find(".fund-select").val();
      var fun_name = $(form).find(".fund-select option:selected").text();
      return fund_id;
    }

    function getProjectNewValue(form) {
      var project_id = $(form).find(".project-select").val();
      var project_name = $(form).find(".project-select option:selected").text();
      return project_id;
    }

    let isSubmitting = false;

    const btnSubmit = document.getElementById("btn-create");
    if (btnSubmit) {
      btnSubmit.addEventListener("click", createData);
    }

    async function createData(event) {
      if (isSubmitting) {
        event?.preventDefault?.();
        return;
      }

      const submitButtons = Array.from(
        document.querySelectorAll(".btn-create, #btn-create")
      );
      submitButtons.forEach((btn) => {
        btn.disabled = true;
      });

      isSubmitting = true;

      const forms = Array.from(document.querySelectorAll("form#advanceFrm"));
      if (!forms.length) {
        // submitButtons.forEach((btn) => {
        //   btn.disabled = false;
        // });
        isSubmitting = false;
        Swal.fire({
          icon: "warning",
          title: "ไม่พบฟอร์ม",
          text: "ไม่พบฟอร์มสำหรับบันทึกข้อมูล",
        });
        return;
      }

      let validationMessage = null;
      let validationForm = null;
      const payloads = [];

      for (let index = 0; index < forms.length; index += 1) {
        const form = forms[index];
        const labelSuffix = forms.length > 1 ? ` (ฟอร์มที่ ${index + 1})` : "";
        const wrapMessage = (message) =>
          forms.length > 1 ? `${message}${labelSuffix}` : message;

        const validations = [
          validateFund,
          validateProject,
          validateExpensesLearn,
          validateTextApprove,
          validateDateFields,
          validateTravelExpensesForm,
          validateVehicleInfo,
          validateConfirmCheckbox,
        ];

        for (const validate of validations) {
          const error = validate(form);
          if (error) {
            validationMessage = wrapMessage(error);
            validationForm = form;
            break;
          }
        }
        if (validationMessage) break;

        const listData = getListDetailsData(form);
        const car = form.querySelector("#expenses_vehicle");
        const motorcycle = form.querySelector("#expenses_motorcycle");
        if ((car?.checked || motorcycle?.checked) && !listData.length) {
          validationMessage = wrapMessage(
            "กรุณากรอกข้อมูลรายละเอียดการเดินทางอย่างน้อย 1 รายการ"
          );
          validationForm = form;
          break;
        }

        for (let rowIndex = 0; rowIndex < listData.length; rowIndex += 1) {
          const row = listData[rowIndex];
          if (!row.date) {
            validationMessage = wrapMessage(
              `กรุณาระบุวันที่ในแถวที่ ${rowIndex + 1} ของรายละเอียดการเดินทาง`
            );
            validationForm = form;
            break;
          }
        }
        if (validationMessage) break;

        payloads.push({
          main_data: getMainFormData(form),
          group_data: getGroupPeopleData(form),
          list_data: listData,
        });
      }

      if (validationMessage) {
        Swal.fire({
          icon: "warning",
          title: "ข้อมูลไม่ครบถ้วน",
          text: validationMessage,
        });
        validationForm?.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
        submitButtons.forEach((btn) => {
          btn.disabled = false;
        });
        isSubmitting = false;
        return;
      }

      let loadingShown = false;

      try {
        window.showLoading();
        loadingShown = true;

        const createdNumbers = [];
        let rootExpensesId = null;
        let sharedExpensesNumber = null;

        console.log("Payloads to submit:", payloads);
        for (const payload of payloads) {
          if (rootExpensesId && sharedExpensesNumber) {
            payload.main_data.parent_id = rootExpensesId;
            payload.main_data.expenses_number = sharedExpensesNumber;
          } else {
            delete payload.main_data.parent_id;
            if (!payload.main_data.expenses_number) {
              delete payload.main_data.expenses_number;
            }
          }

          const response = await fetch(
            "../controllers/withdraws/wrd_expenses_controller.php?action=create_plan_outside_multi",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                  'meta[name="csrf-token"]'
                )?.content,
              },
              body: JSON.stringify(payload),
            }
          );

          const result = await response.json();

          if (result.status !== "success") {
            throw new Error(
              result.message || "เกิดข้อผิดพลาดในการบันทึกข้อมูล"
            );
          }

          const responseData = result.data;
          const responseNumber =
            (responseData && typeof responseData === "object"
              ? responseData.expenses_number
              : null) ??
            (typeof responseData === "string" ||
              typeof responseData === "number"
              ? responseData
              : null) ??
            result.expenses_number ??
            null;
          const responseId =
            responseData && typeof responseData === "object"
              ? responseData.id ?? null
              : null;

          if (!rootExpensesId && responseId) {
            rootExpensesId = responseId;
          }

          const normalizedNumber =
            responseNumber !== null && responseNumber !== undefined
              ? String(responseNumber)
              : null;

          if (!sharedExpensesNumber && normalizedNumber) {
            sharedExpensesNumber = normalizedNumber;
          }

          if (sharedExpensesNumber) {
            payload.main_data.expenses_number = sharedExpensesNumber;
          }

          createdNumbers.push(responseData ?? responseNumber);
        }

        const successMessage = `เลขที่เอกสารคือ: ${createdNumbers[0].expenses_number}`;

        Swal.fire({
          title: "บันทึกข้อมูลสำเร็จ",
          html: successMessage,
          icon: "success",
        }).then(() => {
          window.location.href = "travel-expenses-money-list.php";
        });
      } catch (error) {
        console.error("เกิดข้อผิดพลาด:", error);
        Swal.fire({
          title: "เกิดข้อผิดพลาด",
          text:
            error.message || "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
          icon: "error",
        });
      } finally {
        submitButtons.forEach((btn) => {
          btn.disabled = false;
        });
        if (loadingShown) {
          window.hideLoading();
        }
        isSubmitting = false;
      }
    }

    window.createData = createData;
    window.formsSummaryManager?.update();
  } catch (err) {
    console.error("เกิดข้อผิดพลาด:", err);
    alert("ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
  }

  function validateFund(form) {
    const fund = $(form).find('.fund-select').val()?.trim();
    return fund ? null : "กรุณาเลือกเงินทุน";
  }

  function validateProject(form) {
    const project = $(form).find('.project-select').val()?.trim();
    return project ? null : "กรุณาเลือกโครงการ";
  }

  function validateExpensesLearn(form) {
    const expensesLearn = form
      .querySelector('[name="expenses_learn"]')
      ?.value?.trim();
    return expensesLearn ? null : "กรุณากรอกเรียนถึง";
  }

  function validateTextApprove(form) {
    const haveText = $(form).find('[name="text_approve"]').length;
    console.log(haveText);
    if (haveText) {
      const text_approve = form
        .querySelector('[name="text_approve"]')
        ?.value?.trim();
      return text_approve ? null : "กรุณากรอกได้อนุมัติให้";
    }
    return null;
  }

  const parseThaiDateTime = (raw) => {
    if (!raw) return null;
    const trimmed = raw.trim();
    if (!trimmed) return null;

    const [datePart, timePart = "00:00"] = trimmed.split(/\s+/);
    if (!datePart) return null;

    const dateSegments = datePart.includes("/")
      ? datePart.split("/")
      : datePart.split("-");
    if (dateSegments.length < 3) return null;
    const [d, m, y] = dateSegments.map((segment) => parseInt(segment, 10));
    if (!d || !m || !y) return null;
    const yearCE = y > 2400 ? y - 543 : y;

    const [hour = "00", minute = "00"] = timePart.split(":");
    const h = parseInt(hour, 10);
    const min = parseInt(minute, 10);

    const date = new Date(yearCE, m - 1, d, h, min);
    return Number.isNaN(date.getTime()) ? null : date;
  };

  function validateDateFields(form) {
    const startField = form.querySelector('[name="expenses_start"]');
    const endField = form.querySelector('[name="expenses_end"]');

    if (!startField && !endField) {
      return null;
    }

    const startValue = startField?.value?.trim();
    const endValue = endField?.value?.trim();

    if (!startValue || !endValue) {
      return "กรุณาระบุวันที่เริ่มต้นและสิ้นสุด";
    }

    const startDate = parseThaiDateTime(startValue);
    const endDate = parseThaiDateTime(endValue);

    if (startDate && endDate && startDate > endDate) {
      return "วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด";
    }

    return null;
  }

  function validateTravelExpensesForm(form) {
    const fullNameSelect = form.querySelector(".user-select");
    if (fullNameSelect && !fullNameSelect.value) {
      return "กรุณาเลือกชื่อ-นามสกุล";
    }

    const positionField = form
      .querySelector('[name="position_name_expenses"]')
      ?.value?.trim();
    const levelField = form
      .querySelector('[name="level_name_expenses"]')
      ?.value?.trim();
    const affiliationField = form
      .querySelector('[name="affiliation_name_expenses"]')
      ?.value?.trim();

    if (fullNameSelect && !positionField) {
      return "กรุณาเลือกชื่อ-นามสกุล";
    }
    if (fullNameSelect && !levelField) {
      return "กรุณาเลือกชื่อ-นามสกุล";
    }
    if (fullNameSelect && !affiliationField) {
      return "กรุณาเลือกชื่อ-นามสกุล";
    }

    const expensesStartField = form.querySelector('[name="expenses_start"]');
    const expensesEndField = form.querySelector('[name="expenses_end"]');

    if (expensesStartField || expensesEndField) {
      const startValue = expensesStartField?.value?.trim();
      if (!startValue) {
        return "กรุณาระบุวันและเวลาเริ่มต้น";
      }

      const endValue = expensesEndField?.value?.trim();
      if (!endValue) {
        return "กรุณาระบุวันและเวลาสิ้นสุด";
      }

      const startDate = parseThaiDateTime(startValue);
      const endDate = parseThaiDateTime(endValue);
      if (startDate && endDate && startDate > endDate) {
        return "วันและเวลาเริ่มต้นต้องไม่มากกว่าวันและเวลาสิ้นสุด";
      }
    }

    const expensesCode = form.querySelector("#expenses_code")?.value?.trim();
    if (!expensesCode) {
      return "กรุณากรอกคำสั่ง/บันทึก";
    }

    const expensesDate = form.querySelector('[name="expenses_date"]')?.value;
    if (!expensesDate) {
      return "กรุณาระบุวันที่ (ลงวันที่)";
    }

    const expensesGroupControl = form.querySelector('[name="expenses_group"]');
    let expensesGroup = null;
    if (expensesGroupControl) {
      expensesGroup = form.querySelector(
        'input[name="expenses_group"]:checked'
      );
      if (!expensesGroup) {
        return "กรุณาเลือกค่าใช้จ่ายสำหรับ (ข้าพเจ้า/ข้าพเจ้าพร้อมคณะเดินทาง)";
      }
    }

    const expensesStartLocation = form.querySelector(
      'input[name="expenses_start_location"]:checked'
    );
    if (!expensesStartLocation) {
      return "กรุณาเลือกสถานที่เริ่มต้น";
    }

    const expensesStartDate = form.querySelector(
      '[name="expenses_start_date"]'
    )?.value;
    if (!expensesStartDate) {
      return "กรุณากรอกวันที่และเวลาเดินทาง (เริ่มต้น)";
    }

    const expensesEndLocation = form.querySelector(
      'input[name="expenses_end_location"]:checked'
    );
    if (!expensesEndLocation) {
      return "กรุณาเลือกสถานที่กลับ";
    }

    const expensesEndDate = form.querySelector(
      '[name="expenses_end_date"]'
    )?.value;
    if (!expensesEndDate) {
      return "กรุณากรอกวันที่และเวลาเดินทาง (กลับ)";
    }

    const carChecked = form.querySelector("#expenses_vehicle")?.checked;
    const motorcycleChecked = form.querySelector(
      "#expenses_motorcycle"
    )?.checked;
    if (carChecked || motorcycleChecked) {
      const vehicleNumber = form
        .querySelector("#expenses_vehicle_number")
        ?.value?.trim();
      if (!vehicleNumber) {
        return "กรุณากรอกหมายเลขทะเบียนยานพาหนะ";
      }
    }

    if (expensesGroup?.value === "1" || expensesGroup?.value === "3") {
      const inputEl = form.elements['expenses_allowance_days'];
      const expenses_allowance_days = inputEl.value;
      if (expenses_allowance_days) {
        const val = parseFloat(expenses_allowance_days);
        const decimal = (val % 1).toFixed(1);
        if (decimal !== '0.0' && decimal !== '0.5') {
          inputEl.focus();
          return "กรุณากรอกจำนวน (วัน) ให้ถูกต้อง";
        }
      }
    }

    if (expensesGroup?.value === "2") {
      const groupRows = form.querySelectorAll("tr.group-people-list");
      if (!groupRows.length) {
        return "กรุณากรอกข้อมูลคณะเดินทางอย่างน้อย 1 รายการ";
      }

      const hasData = Array.from(groupRows).some((row) => {
        const fields = row.querySelectorAll(
          "input:not([type='checkbox']):not([disabled]), textarea"
        );
        return Array.from(fields).some((field) => field.value.trim());
      });

      if (!hasData) {
        return "กรุณากรอกข้อมูลคณะเดินทางอย่างน้อย 1 รายการ";
      }

      for (const row of groupRows) {
        const inputEl = row.querySelector('.group_days');
        const value = inputEl?.value;

        // ถ้าไม่กรอก → ข้าม
        if (!value) continue;

        const val = parseFloat(value);
        const decimal = (val % 1).toFixed(1);

        if (decimal !== '0.0' && decimal !== '0.5') {
          inputEl.focus();
          return 'กรุณากรอกจำนวน (วัน) ให้ถูกต้อง';
        }
      }
    }

    return null;
  }

  function validateVehicleInfo(form) {
    const vehicle = form.querySelector(
      'input[name="expenses_vehicle"]:checked'
    );
    if (vehicle) {
      const vehicleNumber = form
        .querySelector("#expenses_vehicle_number")
        ?.value?.trim();
      if (!vehicleNumber) {
        return "กรุณากรอกหมายเลขทะเบียนยานพาหนะส่วนตัว";
      }
    }
    return null;
  }

  function validateConfirmCheckbox(form) {
    const confirm = form.querySelector("#expenses_confirm");
    if (!confirm || !confirm.checked) {
      return "กรุณายืนยันว่ารายการที่กล่าวมาข้างต้นเป็นความจริง";
    }
    return null;
  }
})();

// ฟังก์ชั่นสำหรับ format วันที่ วัน/เดือน/ปี เวลา
function formatThaiDatetime(datetimeStr) {
  if (!datetimeStr) return "";
  const dateObj = new Date(datetimeStr);
  if (isNaN(dateObj)) return "";
  const day = String(dateObj.getDate()).padStart(2, "0");
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const year = dateObj.getFullYear() + 543;
  const hours = String(dateObj.getHours()).padStart(2, "0");
  const minutes = String(dateObj.getMinutes()).padStart(2, "0");
  return `${day}-${month}-${year} ${hours}:${minutes}`;
}
function formatThaiDate(datetimeStr) {
  if (!datetimeStr) return "";
  const dateObj = new Date(datetimeStr);
  if (isNaN(dateObj)) return "";
  const day = String(dateObj.getDate()).padStart(2, "0");
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const year = dateObj.getFullYear() + 543;
  return `${day}-${month}-${year}`;
}

// ฟังก์ชันสร้างฟอร์มคนเดียว
function createOnePersonForm() {
  ensureCollapsibleStyles();
  const baseForm = document.querySelector("#advanceFrm");
  const container = baseForm?.parentElement || baseForm;
  if (!container) return;
  const index = getNextFormIndex();
  const newDiv = document.createElement("div");
  newDiv.innerHTML = `
    <form id="advanceFrm" novalidate>
        <!-- Start::row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card collapsible-card" data-form-index="${index}">

                    <!-- ส่วนหัว รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน-->
                    <div class="card-header justify-content-between align-items-center">
                        <div class="card-title" data-base-title="รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก) (เดินทางคนเดียว)">ฟอร์มที่ ${index} : รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก) (เดินทางคนเดียว)</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary toggle-card">ซ่อน/แสดง</button>
                    </div>

                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1">เงินทุน</label>
                                <select class="form-control fund-select" data-trigge required style="width: 100%"></select>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1">โครงการ</label>
                                <select class="form-control project-select" data-trigge required style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">เรียน :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_learn" name="expenses_learn" placeholder="กรุณากรอก เรียน" autocomplete="off">
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">คำสั่ง/บันทึก :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_code" name="expenses_code" placeholder="กรุณากรอก คำสั่ง/บันทึก" autocomplete="off">
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">ลงวันที :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-date-only" name="expenses_date" placeholder="ระบุวัน เดือน ปี" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 mb-2 d-none expense-group-choice">
                                        <div class="row">
                                            <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="1" checked>
                                                    <label class="form-check-label">ข้าพเจ้าเดินทางคนเดียว</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="2">
                                                    <label class="form-check-label">ข้าพเจ้าพร้อมคณะเดินทาง</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 mb-2">
                                    </div>
                                    <div class="col-xl-6 mb-3 div-one-person">
                                        <label class="form-label">ได้อนุมัติให้ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control input-approve-name" id="text_approve" name="text_approve" placeholder="กรุณากรอก ได้อนุมัติให้" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-4 mb-2">
                                <div class="form-group row">
                                    <label class="col-auto col-form-label form-label mb-2">ลักษณะการเดินทาง :</label>
                                    <div class="col-8">
                                        <select class="form-select is_foreign" id="is_foreign" name="is_foreign" required style="width: 100%">
                                            <option value="0" selected>- เดินทางในประเทศไทย</option>
                                            <option value="1">- เดินทางต่างประเทศ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault1_start" value="address">
                                            <label class="form-check-label">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault2_start" value="office">
                                            <label class="form-check-label">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault3_start" value="thailand">
                                            <label class="form-check-label">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-datetime" name="expenses_start_date" placeholder="กรุณากรอก วันที่เดินทาง">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_start_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault1_end" value="address">
                                            <label class="form-check-label">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault2_end" value="office">
                                            <label class="form-check-label">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault3_end" value="thailand">
                                            <label class="form-check-label">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-datetime" name="expenses_end_date" placeholder="กรุณากรอก วันที่เดินทาง">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_end_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียด ค่าเบี้ยเลี้ยง ค่าเช่าที่พัก ค่าพาหนะ ค่าใช้จ่ายอื่นๆ หักค่าอาหารฝึกอบรม-->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ฐานะ การเบิกของผู้เบิก :</label>
                                        <input type="text" class="form-control" id="position_withdraw" name="position_withdraw" placeholder="กรุณากรอก ฐานะ การเบิกของผู้เบิก" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_allowance" name="expenses_allowance" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-step-05" id="expenses_allowance_days" name="expenses_allowance_days" placeholder="กรุณากรอก จำนวน (วัน)" step="0.5" min="0">
                                        <span class="text-danger error-message" style="display:none;">
                                            กรอกได้แค่ 0.0 หรือ 0.5 เท่านั้น
                                        </span>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_allowance_total" name="expenses_allowance_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าเช่าที่พักประเภท (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_accommodation" name="expenses_accommodation" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-0point" id="expenses_accommodation_days" name="expenses_accommodation_days" placeholder="กรุณากรอก จำนวน (วัน)" step="1" min="0">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_accommodation_total" name="expenses_accommodation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าพาหนะ (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_transportation" name="expenses_transportation" placeholder="กรุณากรอก ค่าพาหนะ (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รายละเอียดค่าพาหนะ :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_remark" name="expenses_transportation_remark" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ" autocomplete="off">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_total" name="expenses_transportation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_other" name="expenses_other" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                      <label for="expenses_other_remark" class="form-label">รายละเอียดค่าใช้จ่ายอื่นๆ :</label>
                                      <input type="text" class="form-control" id="expenses_other_remark" name="expenses_other_remark" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" autocomplete="off">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_other_total" name="expenses_other_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_food_per_meal" name="expenses_food_per_meal" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวน (มื้อ) :</label>
                                        <input type="number" class="form-control" id="expenses_food_meals" name="expenses_food_meals" placeholder="กรุณากรอก จำนวน (มื้อ)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) *หักลบ :</label>
                                        <input type="text" class="form-control" id="expenses_food_total" name="expenses_food_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">กรณีใช้ยานพาหนะส่วนตัว กรุณาระบุข้อมูล :</label>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle" id="expenses_vehicle" value="car">
                                            <label class="form-check-label">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_motorcycle" id="expenses_motorcycle" value="motorcycle">
                                            <label class="form-check-label">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label class="form-label">หมายเลขทะเบียน :<small class="text-danger ml-2" id="expenses_vehicle_number_required" style="font-weight: lighter;"> * จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_vehicle_number" name="expenses_vehicle_number" placeholder="กรุณากรอก หมายเลขทะเบียน" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 display-vehicle" style="display: none;">
                                <div class="row ">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success">
                                            <thead class="text-center">
                                                <tr>
                                                    <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25" style="min-width: 160px;">วัน เดือน ปี</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                    <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">หมายเหตุ</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ระยะทาง</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย/กม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(กม.)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="list-details"></tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_amount_details" type="text" placeholder="0.00" disabled></td>
                                                </tr>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_text_details" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1 addTable-list-details" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1 delTable-list-details" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                            <style>
                                .table-responsive {
                                    overflow-x: auto;
                                }

                                .table-bordered th,
                                .table-bordered td {
                                    min-width: 120px;
                                    /* ปรับตามความเหมาะสมแต่ละช่อง */
                                    white-space: nowrap;
                                    text-align: center;
                                }

                                .table-bordered th:first-child,
                                .table-bordered td:first-child {
                                    min-width: 40px;
                                }

                                .table-bordered th:nth-child(2),
                                .table-bordered td:nth-child(2) {
                                    min-width: 60px;
                                }

                                .table-bordered th:nth-child(3),
                                .table-bordered td:nth-child(3) {
                                    min-width: 230px;
                                }

                                .table-bordered th:nth-child(4),
                                .table-bordered td:nth-child(4) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(5),
                                .table-bordered td:nth-child(5) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(6),
                                .table-bordered td:nth-child(6) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(7),
                                .table-bordered td:nth-child(7) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(8),
                                .table-bordered td:nth-child(8) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(9),
                                .table-bordered td:nth-child(9) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(10),
                                .table-bordered td:nth-child(10) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(11),
                                .table-bordered td:nth-child(11) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(12),
                                .table-bordered td:nth-child(12) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(13),
                                .table-bordered td:nth-child(13) {
                                    min-width: 120px;
                                }

                                .table-bordered th:nth-child(14),
                                .table-bordered td:nth-child(14) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(15),
                                .table-bordered td:nth-child(15) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(16),
                                .table-bordered td:nth-child(16) {
                                    min-width: 150px;
                                }

                                /* ปรับ min-width เฉพาะช่องที่ต้องการให้กว้างขึ้น */
                                .table-bordered th.bg-info,
                                .table-bordered td.bg-info {
                                    min-width: 50px;
                                }
                            </style>
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label mb-2">หมายเหตุ :</label>
                                        <textarea class="form-control" id="expenses_note" name="expenses_note" rows="2"></textarea>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวมทั้งสิ้น (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_total" name="expenses_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวมทั้งสิ้น (ตัวอักษร) :</label>
                                        <input type="text" class="form-control" id="expenses_total_text" name="expenses_total_text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled>
                                    </div>
                                </div>
                            </div>
                            <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                            <div class="col-xl-12 mb-2" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                        <input type="text" class="form-control" id="plan_number" name="plan_number" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ลงวันที่ :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control flatpickr-input active" id="plan_date" name="plan_date" placeholder="กรุณาระบุ วันที่" readonly="readonly" fdprocessedid="jp9fs9" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวนเงินทดรอง (บาท) :</label>
                                        <input type="text" class="form-control" id="plan_total" name="plan_total" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2" style="display: none;>
                                <div class="row">
                                    <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_return" id="expenses_return" value="return">
                                            <label class="form-check-label">ส่งคืน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_withdraw" id="expenses_withdraw" value="withdraw">
                                            <label class="form-check-label">เบิกเพิ่ม</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2" style="display: none;>
                                <div class="row">
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_amount" name="expenses_amount" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                            <div class="card-body border-bottom">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="expenses_confirm" name="expenses_confirm">
                                            <label class="form-check-label">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง <span class="text-danger"> *</span> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

        
                            <!-- ส่วนท้าย การขออนุมัติ -->
                            <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end button-container"></div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <!--End::row -->

    </form>
  `;
  container.appendChild(newDiv);

  // ใส่ปุ่มให้ฟอร์มใหม่
  addButtonsToForm(newDiv);

  const newForm = newDiv.querySelector("form");
  if (newForm && window.travelExpenseMultiManager) {
    window.travelExpenseMultiManager.init(newForm);
    window.travelExpenseMultiManager.recalc(newForm);
  }

  if (newForm) {
    userDropdownManager.initForm(newForm);
    applyPlanFieldsToForm(newForm);
  }

  initDatePickers(newDiv);
  initselect2(newForm);
  initselectForeign(newForm);

  ensureExpensesGroupDefault(newForm, "1");
  bindCollapsible(newDiv.querySelector(".collapsible-card"));
  updateFormIndices();

  // bind event ปุ่ม
  newDiv
    .querySelector(".btn-add-one")
    ?.addEventListener("click", createOnePersonForm);
  newDiv
    .querySelector(".btn-add-group")
    ?.addEventListener("click", createGroupForm);
  newDiv.querySelector(".btn-create")?.addEventListener("click", (event) => {
    window.createData(event);
  });
}

// ฟังก์ชันสร้างฟอร์มหมู่คณะ
function createGroupForm() {
  ensureCollapsibleStyles();
  const baseForm = document.querySelector("#advanceFrm");
  const container = baseForm?.parentElement || baseForm;
  if (!container) return;
  const index = getNextFormIndex();
  const newDiv = document.createElement("div");
  newDiv.innerHTML = `
    <form id="advanceFrm" novalidate>
        <!-- Start::row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card collapsible-card" data-form-index="${index}">

                    <!-- ส่วนหัว รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน-->
                    <div class="card-header justify-content-between align-items-center">
                        <div class="card-title" data-base-title="รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก) (เดินทางเป็นหมู่คณะ)">ฟอร์มที่ ${index} : รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก) (เดินทางเป็นหมู่คณะ)</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary toggle-card">ซ่อน/แสดง</button>
                    </div>
                    <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                        <div class="row">
                            <p class="mb-2 me-4"><span class="fs-15 fw-bold">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง : </span><small class="text-danger ml-2"> *จำเป็นต้องเลือก </small></p>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1">เงินทุน</label>
                                <select class="form-control fund-select" data-trigge required style="width: 100%"></select>
                            </div>
                            <div class="col-xl-6">
                                <label class="form-check-label mb-1">โครงการ</label>
                                <select class="form-control project-select" data-trigge required style="width: 100%"></select>
                            </div>
                        </div>
                    </div>
                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">เรียน :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_learn" name="expenses_learn" placeholder="กรุณากรอก เรียน" autocomplete="off">
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">คำสั่ง/บันทึก :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_code" name="expenses_code" placeholder="กรุณากรอก คำสั่ง/บันทึก" autocomplete="off">
                                    </div>
                                    <div class="col-xl-3 mb-2">
                                        <label class="form-label">ลงวันที :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-date-only" name="expenses_date" placeholder="ระบุวัน เดือน ปี" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 mb-2 d-none expense-group-choice">
                                        <div class="row">
                                            <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="1">
                                                    <label class="form-check-label">ข้าพเจ้าเดินทางคนเดียว</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="expenses_group" value="2" checked>
                                                    <label class="form-check-label">ข้าพเจ้าพร้อมคณะเดินทาง</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 mb-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-4 mb-2">
                                <div class="form-group row">
                                    <label class="col-auto col-form-label form-label mb-2">ลักษณะการเดินทาง :</label>
                                    <div class="col-8">
                                        <select class="form-select is_foreign" id="is_foreign" name="is_foreign" required style="width: 100%">
                                            <option value="0" selected>- เดินทางในประเทศไทย</option>
                                            <option value="1">- เดินทางต่างประเทศ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault1_start" value="address">
                                            <label class="form-check-label">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault2_start" value="office">
                                            <label class="form-check-label">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_start_location" id="flexRadioDefault3_start" value="thailand">
                                            <label class="form-check-label">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-datetime" name="expenses_start_date" placeholder="กรุณากรอก วันที่เดินทาง">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_start_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3" style="border: 1px solid #eee; padding: 10px;">
                                <div class="row">
                                    <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault1_end" value="address">
                                            <label class="form-check-label">ที่อยู่</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault2_end" value="office">
                                            <label class="form-check-label">สำนักงาน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="expenses_end_location" id="flexRadioDefault3_end" value="thailand">
                                            <label class="form-check-label">ประเทศไทย</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">วันที่และเวลาเดินทาง :<small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control js-flatpickr-datetime" name="expenses_end_date" placeholder="กรุณากรอก วันที่เดินทาง">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                        <textarea class="form-control" id="product-description-add" name="expenses_end_address" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียด ค่าเบี้ยเลี้ยง ค่าเช่าที่พัก ค่าพาหนะ ค่าใช้จ่ายอื่นๆ หักค่าอาหารฝึกอบรม-->
                    <div class="card-body border-bottom">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ฐานะ การเบิกของผู้เบิก :</label>
                                        <input type="text" class="form-control" id="position_withdraw" name="position_withdraw" placeholder="กรุณากรอก ฐานะ การเบิกของผู้เบิก" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าเบี้ยเลี้ยง (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_allowance" name="expenses_allowance" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_allowance_days" class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-step-05" id="expenses_allowance_days" name="expenses_allowance_days" placeholder="กรุณากรอก จำนวน (วัน)" step="0.5" min="0">
                                        <span class="text-danger error-message" style="display:none;">
                                            กรอกได้แค่ 0.0 หรือ 0.5 เท่านั้น
                                        </span>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_allowance_total" name="expenses_allowance_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าเช่าที่พักประเภท (บาท/วัน) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_accommodation" name="expenses_accommodation" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท (บาท/วัน)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวน (วัน) :</label>
                                        <input type="number" class="form-control format-number-0point" id="expenses_accommodation_days" name="expenses_accommodation_days" placeholder="กรุณากรอก จำนวน (วัน)" step="1" min="0">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_accommodation_total" name="expenses_accommodation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าพาหนะ (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_transportation" name="expenses_transportation" placeholder="กรุณากรอก ค่าพาหนะ (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รายละเอียดค่าพาหนะ :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_remark" name="expenses_transportation_remark" placeholder="กรุณากรอก รายละเอียดค่าพาหนะ" autocomplete="off">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_transportation_total" name="expenses_transportation_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าขนย้ายสิ่งของส่วนตัว (บาท) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving" name="expenses_moving" placeholder="กรุณากรอก ค่าขนย้ายสิ่งของส่วนตัว (บาท)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ระยะทาง (กิโลเมตร) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_moving_km" name="expenses_moving_km" placeholder="กรุณากรอก ระยะทาง (กิโลเมตร)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_moving_total" name="expenses_moving_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_other" name="expenses_other" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label for="expenses_other_remark" class="form-label">รายละเอียดค่าใช้จ่ายอื่นๆ :</label>
                                        <input type="text" class="form-control" id="expenses_other_remark" name="expenses_other_remark" placeholder="กรุณากรอก รายละเอียดค่าใช้จ่ายอื่นๆ" autocomplete="off">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_other_total" name="expenses_other_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 one-person">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ) :</label>
                                        <input type="number" class="form-control format-number-2point" id="expenses_food_per_meal" name="expenses_food_per_meal" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม (บาท/มื้อ)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวน (มื้อ) :</label>
                                        <input type="number" class="form-control" id="expenses_food_meals" name="expenses_food_meals" placeholder="กรุณากรอก จำนวน (มื้อ)">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวม (บาท) *หักลบ :</label>
                                        <input type="text" class="form-control" id="expenses_food_total" name="expenses_food_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-2">
                                <div class="row">
                                    <label class="form-label mb-2">กรณีใช้ยานพาหนะส่วนตัว กรุณาระบุข้อมูล :</label>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_vehicle" id="expenses_vehicle" value="car">
                                            <label class="form-check-label">รถยนต์</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_motorcycle" id="expenses_motorcycle" value="motorcycle">
                                            <label class="form-check-label">รถจักรยานยนต์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 mb-3">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label class="form-label">หมายเลขทะเบียน :<small class="text-danger ml-2" id="expenses_vehicle_number_required" style="font-weight: lighter;"> * จำเป็นต้องกรอก </small></label>
                                        <input type="text" class="form-control" id="expenses_vehicle_number" name="expenses_vehicle_number" placeholder="กรุณากรอก หมายเลขทะเบียน" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2 display-vehicle" style="display: none;">
                                <div class="row ">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label">เงินชดเชย (กรุณาระบุรายละเอียดในตาราง รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน) :</label>
                                    </div>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered border-success">
                                            <thead class="text-center">
                                                <tr>
                                                    <th scope="col" rowspan="3"><input class="form-check-input check-all-details" type="checkbox" id="all-products" value="" aria-label="..."></th>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25" style="min-width: 160px;">วัน เดือน ปี</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                    <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                    <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                    <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">หมายเหตุ</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">ระยะทาง</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย/กม</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย</td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(กม.)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                    <td class="bg-info text-fixed-dark bg-opacity-10">(บาท)</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="list-details"></tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (บาท)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_amount_details" type="text" placeholder="0.00" disabled></td>
                                                </tr>
                                                <tr>
                                                    <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                    <td scope="col" colspan="3"><input class="form-control" id="total_text_details" type="text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1 addTable-list-details" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1 delTable-list-details" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
<style>
                                .table-responsive {
                                    overflow-x: auto;
                                }

                                .table-bordered th,
                                .table-bordered td {
                                    min-width: 120px;
                                    /* ปรับตามความเหมาะสมแต่ละช่อง */
                                    white-space: nowrap;
                                    text-align: center;
                                }

                                .table-bordered th:first-child,
                                .table-bordered td:first-child {
                                    min-width: 40px;
                                }

                                .table-bordered th:nth-child(2),
                                .table-bordered td:nth-child(2) {
                                    min-width: 60px;
                                }

                                .table-bordered th:nth-child(3),
                                .table-bordered td:nth-child(3) {
                                    min-width: 210px;
                                }

                                .table-bordered th:nth-child(4),
                                .table-bordered td:nth-child(4) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(5),
                                .table-bordered td:nth-child(5) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(6),
                                .table-bordered td:nth-child(6) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(7),
                                .table-bordered td:nth-child(7) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(8),
                                .table-bordered td:nth-child(8) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(9),
                                .table-bordered td:nth-child(9) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(10),
                                .table-bordered td:nth-child(10) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(11),
                                .table-bordered td:nth-child(11) {
                                    min-width: 180px;
                                }

                                .table-bordered th:nth-child(12),
                                .table-bordered td:nth-child(12) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(13),
                                .table-bordered td:nth-child(13) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(14),
                                .table-bordered td:nth-child(14) {
                                    min-width: 120px;
                                }

                                .table-bordered th:nth-child(15),
                                .table-bordered td:nth-child(15) {
                                    min-width: 130px;
                                }

                                .table-bordered th:nth-child(16),
                                .table-bordered td:nth-child(16) {
                                    min-width: 150px;
                                }

                                .table-bordered th:nth-child(17),
                                .table-bordered td:nth-child(17) {
                                    min-width: 150px;
                                }
                                /* ปรับ min-width เฉพาะช่องที่ต้องการให้กว้างขึ้น */
                                .table-bordered th.bg-info,
                                .table-bordered td.bg-info {
                                    min-width: 50px;
                                }
                            </style>
                            <div class="col-xl-12 mb-2" id="ifYes">
                                <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                <div class="table-responsive mb-2">
                                      <table class="table text-nowrap table-bordered border-success">
                                        <thead class="text-center">
                                            <tr>
                                                <th scope="col" rowspan="3"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ลำดับที่</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ชื่อ-นามสกุล</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ตำแหน่ง/ระดับ</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">เลขบัตรประชาชน</td>
                                                <td scope="col" colspan="11" class="bg-info text-fixed-dark bg-opacity-25">ค่าใช้จ่าย (บาท)</td>
                                                <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">รวม (บาท)</td>
                                            </tr>
                                            <tr>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยง</td>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พัก</td>
                                                <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าพาหนะ</td>
                                                <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าใช้จ่ายอื่นๆ</td>
                                                <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">หักค่าอาหารฝึกอบรม</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยงต่อวัน (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (วัน)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พักต่อวัน (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (วัน)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">มื้อละ (บาท)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">จำนวน (มื้อ)</td>
                                                <td class="bg-info text-fixed-dark bg-opacity-10">รวม (บาท)</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="group-people-listgroup-people-list">
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="flex-wrap">
                                    <div class="col-12">
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9 text-end">
                                                จำนวนเงินทั้งสิ้น (บาท)
                                            </div>
                                            <div class="col-3">
                                                <input class="form-control" type="text" id="expenses_group_total" placeholder="0.00" disabled>
                                            </div>
                                            <div class="col-9 text-end mt-2">
                                                จำนวนเงินทั้งสิ้น (ตัวอักษร)
                                            </div>
                                            <div class="col-3 mt-2">
                                                <input class="form-control" type="text" id="expenses_group_total_text" placeholder="ศูนย์บาทถ้วน" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                        <button class="btn btn-success-light m-1 addTable-group-people" type="button"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                        <button class="btn btn-danger-light m-1 delTable-group-people" type="button"><i class="bi bi-dash"></i> ลบรายการ</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                        <label class="form-label mb-2">หมายเหตุ :</label>
                                        <textarea class="form-control" id="expenses_note" name="expenses_note" rows="2"></textarea>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวมทั้งสิ้น (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_total" name="expenses_total" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 mb-2">
                                <div class="row justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">รวมทั้งสิ้น (ตัวอักษร) :</label>
                                        <input type="text" class="form-control" id="expenses_total_text" name="expenses_total_text" placeholder="ศูนย์บาทศูนย์สตางค์" disabled>
                                    </div>
                                </div>
                            </div>
                            <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                            <div class="col-xl-12 mb-2" style="display: none;">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                        <input type="text" class="form-control" id="plan_number" name="plan_number" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง" disabled>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">ลงวันที่ :</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control flatpickr-input active" id="plan_date" name="plan_date" placeholder="กรุณาระบุ วันที่" readonly="readonly" fdprocessedid="jp9fs9" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                        <label class="form-label">จำนวนเงินทดรอง (บาท) :</label>
                                        <input type="text" class="form-control" id="plan_total" name="plan_total" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2" style="display: none;>
                                <div class="row">
                                    <label class="form-label mb-2">ผู้ใช้งานต้องการ :</label>
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_return" id="expenses_return" value="return">
                                            <label class="form-check-label">ส่งคืน</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="expenses_withdraw" id="expenses_withdraw" value="withdraw">
                                            <label class="form-check-label">เบิกเพิ่ม</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2" style="display: none;>
                                <div class="row">
                                    <div class="col-xl-12 mb-2">
                                        <label class="form-label">จำนวนเงินที่ต้องการส่งคืน/เบิกเพิ่ม (บาท) :</label>
                                        <input type="text" class="form-control" id="expenses_amount" name="expenses_amount" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                            <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                            <div class="card-body border-bottom">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="expenses_confirm" name="expenses_confirm">
                                            <label class="form-check-label">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง <span class="text-danger"> *</span> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
        
                            <!-- ส่วนท้าย การขออนุมัติ -->
                            <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end button-container"></div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <!--End::row -->

    </form>
  `;
  container.appendChild(newDiv);

  // ใส่ปุ่มให้ฟอร์มใหม่
  addButtonsToForm(newDiv);

  const newForm = newDiv.querySelector("form");
  if (newForm && window.travelExpenseMultiManager) {
    window.travelExpenseMultiManager.init(newForm);
    window.travelExpenseMultiManager.recalc(newForm);
  }

  if (newForm) {
    userDropdownManager.initForm(newForm);
    applyPlanFieldsToForm(newForm);
  }

  initDatePickers(newDiv);
  initselect2(newForm);
  initselectForeign(newForm);

  newDiv
    .querySelectorAll(".one-person")
    .forEach((section) => section.remove());

  ensureExpensesGroupDefault(newForm, "2");
  bindCollapsible(newDiv.querySelector(".collapsible-card"));
  updateFormIndices();

  // bind event ปุ่ม
  newDiv
    .querySelector(".btn-add-one")
    ?.addEventListener("click", createOnePersonForm);
  newDiv
    .querySelector(".btn-add-group")
    ?.addEventListener("click", createGroupForm);
  newDiv.querySelector(".btn-create")?.addEventListener("click", (event) => {
    window.createData(event);
  });
}

// Event listener ปุ่ม
document
  .getElementById("btn-add-one")
  ?.addEventListener("click", createOnePersonForm);
document
  .getElementById("btn-add-group")
  ?.addEventListener("click", createGroupForm);
document.getElementById("btn-approve")?.addEventListener("click", () => {
  Swal.fire("สำเร็จ!", "ส่งคำขออนุมัติแล้ว", "success");
});

document.addEventListener("click", function (e) {
  if (e.target.id === "btn-add-one") {
    createOnePersonForm();
  }
  if (e.target.id === "btn-add-group") {
    createGroupForm();
  }
});

function addButtonsToForm(container) {
  const buttonContainer = container.querySelector(".button-container");
  if (buttonContainer) {
    buttonContainer.innerHTML = `
      <a href="withdraw-money-list.php" class="btn btn-warning m-1">
          <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
      </a>
      <button type="button" class="btn btn-primary m-1 btn-add-one">
          <i class="bi bi-person-plus"></i> + เพิ่มข้อมูล เดินทางคนเดียว
      </button>
      <button type="button" class="btn btn-info m-1 btn-add-group">
          <i class="bi bi-people"></i> + เพิ่มข้อมูล แบบเดินทางเป็นหมู่คณะ
      </button>
      <button type="button" class="btn btn-danger m-1 btn-remove-form">
          <i class="bi bi-trash"></i> ลบฟอร์มนี้
      </button>
      <button type="button" class="btn btn-success m-1 btn-create">
          <i class="bi bi-check-circle"></i> ขออนุมัติ
      </button>
    `;
  }

  // bind event ปุ่มในฟอร์มนี้
  container
    .querySelector(".btn-add-one")
    ?.addEventListener("click", createOnePersonForm);
  container
    .querySelector(".btn-add-group")
    ?.addEventListener("click", createGroupForm);
  //   container.querySelector(".btn-create")?.addEventListener("click", createData);
  container.querySelector(".btn-create")?.addEventListener("click", (event) => {
    if (typeof window.createData === "function") {
      window.createData(event);
    } else {
      console.error("createData is not available");
    }
  });
  container.querySelector(".btn-remove-form")?.addEventListener("click", () => {
    Swal.fire({
      title: "ยืนยันการลบ?",
      text: "คุณต้องการลบฟอร์มนี้ใช่หรือไม่",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "ลบ",
      cancelButtonText: "ยกเลิก",
    }).then((result) => {
      if (result.isConfirmed) {
        container.remove();
        updateFormIndices();
      }
    });
  });
}
