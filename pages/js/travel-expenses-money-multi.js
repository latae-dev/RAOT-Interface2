import { getAllParams } from "./withdraw_parameter.js";

const state = {
  forms: [],
  currentIndex: 0,
  tabButtons: [],
  dataUser: null,
  posCode: "",
  expensesId: null,
};

const formsSummary = (() => {
  const parseNumeric = (value) => {
    if (typeof value === "number") return value;
    if (value === null || value === undefined) return 0;
    const cleaned = String(value).replace(/[^0-9.-]/g, "").trim();
    if (!cleaned) return 0;
    const parsed = parseFloat(cleaned);
    return Number.isNaN(parsed) ? 0 : parsed;
  };

  const formatCurrency = (amount) =>
    parseNumeric(amount).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  const resolveType = (form) => {
    const group = String(form?.main_data?.expenses_group ?? "");
    return group === "2" ? "เดินทางเป็นหมู่คณะ" : "เดินทางคนเดียว";
  };

  const resolveName = (form, typeLabel) => {
    const main = form?.main_data || {};
    if (typeLabel === "เดินทางเป็นหมู่คณะ") {
      return main.full_name || "-";
    }
    return (
      main.full_name_expenses ||
      main.expenses_full_name ||
      main.full_name ||
      "-"
    );
  };

  const computePlanTotal = () => {
    for (const form of state.forms) {
      const planItems = Array.isArray(form?.child_data_plan)
        ? form.child_data_plan
        : [];
      if (planItems.length) {
        return planItems.reduce(
          (sum, item) => sum + parseNumeric(item?.total),
          0
        );
      }
    }
    for (const form of state.forms) {
      const value = parseNumeric(form?.main_data?.plan_total);
      if (value) return value;
    }
    return 0;
  };

  const render = () => {
    const table = document.getElementById("forms-summary-table");
    if (!table) return;
    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    if (!state.forms.length) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูลแบบฟอร์ม</td></tr>';
      updateFooter(0, 0);
      return;
    }

    let formsTotal = 0;
    const rowsHtml = state.forms
      .map((form, index) => {
        const typeLabel = resolveType(form);
        const amount = parseNumeric(form?.main_data?.expenses_total);
        formsTotal += amount;
        return `
          <tr>
            <td class="text-center">${index + 1}</td>
            <td>${typeLabel}</td>
            <td>${resolveName(form, typeLabel)}</td>
            <td class="text-end">${formatCurrency(amount)}</td>
          </tr>
        `;
      })
      .join("");

    tbody.innerHTML = rowsHtml;

    const planTotal = computePlanTotal();
    updateFooter(planTotal, formsTotal);
  };

  const updateFooter = (planTotal, formsTotal) => {
    let difference = planTotal - formsTotal;

    const diffValue = Math.abs(difference);

    const planCell = document.getElementById("summary-plan-total");
    if (planCell) planCell.textContent = formatCurrency(planTotal);

    const formsCell = document.getElementById("summary-forms-total");
    if (formsCell) formsCell.textContent = formatCurrency(formsTotal);

    const diffCell = document.getElementById("summary-difference");
    if (diffCell) diffCell.textContent = formatCurrency(diffValue);

    const chkReturn = document.getElementById("expenses_return_display");
    const chkWithdraw = document.getElementById("expenses_withdraw_display");
    if (chkReturn) chkReturn.checked = false;
    if (chkWithdraw) chkWithdraw.checked = false;

    if (formsTotal > planTotal) {
      if (chkWithdraw) chkWithdraw.checked = true;
      document.getElementById("expenses_amount_display").value = formatCurrency(diffValue);
    } else if (planTotal > formsTotal) {
      if (chkReturn) chkReturn.checked = true;
      document.getElementById("expenses_amount_display").value = formatCurrency(diffValue);
    } else {
      document.getElementById("expenses_amount_display").value = "0.00";
    }
  };


  return { render };
})();

(async () => {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const expensesId = urlParams.get("id");
    const isApprove = urlParams.get("is_approve");
    const isAction = urlParams.get("is_action");

    if (!expensesId) {
      Swal.fire({
        title: "เกิดข้อผิดพลาด",
        text: "ไม่พบรหัสข้อมูลที่ต้องการดู",
        icon: "error",
      }).then(() => {
        window.location.href = "withdraw-money-list.php";
      });
      return;
    }

    state.expensesId = expensesId;

    const paramsAll = await getAllParams();
    state.dataUser = paramsAll.current_user;

    if (state.dataUser) {
      document.getElementById("depart_code").value =
        state.dataUser.depart_code || "";
      document.getElementById("full_name").value = `${state.dataUser.user_fname || ""
        } ${state.dataUser.user_lname || ""}`.trim();
      document.getElementById("position_name").value =
        state.dataUser.position_name || "";
      document.getElementById("level_name").value =
        state.dataUser.level_name || "";
      document.getElementById("affiliation_name").value =
        state.dataUser.branch_name || "";
      state.posCode = state.dataUser.position_code || "";
    }

    const response = await fetch(
      `../controllers/withdraws/wrd_expenses_controller.php?action=get_one_multi&id=${expensesId}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            ?.content,
        },
      }
    );

    if (!response.ok) {
      throw new Error("ไม่สามารถดึงข้อมูลได้");
    }

    const payload = await response.json();
    const forms = Array.isArray(payload.forms) ? payload.forms : [];
    if (!forms.length) {
      throw new Error("ไม่พบข้อมูลแบบฟอร์มที่เกี่ยวข้อง");
    }

    state.forms = forms;

    const rootForm = forms[0];
    const rootMain = rootForm?.main_data || {};

    formsSummary.render();
    setupApprovalUI(rootMain, isApprove, isAction);
    setupFormTabs();
    renderCurrentForm();
    bindApprovalControls();
  } catch (err) {
    console.error("เกิดข้อผิดพลาด:", err);
    Swal.fire({
      title: "เกิดข้อผิดพลาด",
      text: "ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
      icon: "error",
    });
  }
})();

function setupApprovalUI(rootMain, isApprove, isAction) {
  const approveStatus = rootMain?.approve_status || "";
  const posCode = state.posCode;
  const dataUser = state.dataUser || {};

  const showReturnButton = () => {
    const container = document.getElementById("button-container");
    if (!container) return;
    if (isApprove === "1") {
      container.innerHTML = "";
      return;
    }
    if (isAction === "approve") {
      container.innerHTML = `
          <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
              <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
          </a>
      `;
    } else {
      container.innerHTML = `
          <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
              <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
          </a>
      `;
    }
  };

  const setApproveControls = () => {
    document.getElementById("st_hf_full_name").value = `${dataUser.user_fname || ""
      } ${dataUser.user_lname || ""}`.trim();
    document.getElementById("st_hf_date").value = formatThaiDate(new Date());
    document.getElementById("button-container-approve").innerHTML = `
        <a href="travel-expenses-approvel-list.php" class="btn btn-warning m-1">
            <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
        </a>
        <button id="btn-update" type="button" class="btn btn-primary m-1">
            <i class="bi bi-save"></i> บันทึกข้อมูล
        </button>
    `;
  };

  if (isApprove === "0") {
    document.getElementById("approve-section").style.display = "none";
    showReturnButton();
    return;
  }

  if (posCode === "APHO" && approveStatus === "waiting") {
    setApproveControls();
  } else if (posCode === "APHOP" && approveStatus === "finance_approve") {
    setApproveControls();
  } else if (posCode === "APHOP" && approveStatus === "finance_approve") {
    setApproveControls();
  } else {
    document.getElementById("approve-section").style.display = "none";
    const approveButtons = document.getElementById("button-container-approve");
    if (approveButtons) {
      approveButtons.innerHTML = "";
    }
  }

  showReturnButton();
}

function setupFormTabs() {
  const tabsContainer = document.getElementById("formTabs");
  if (!tabsContainer) return;

  tabsContainer.innerHTML = "";
  state.tabButtons = [];

  if (state.forms.length <= 1) {
    tabsContainer.classList.add("d-none");
    return;
  }

  tabsContainer.classList.remove("d-none");

  state.forms.forEach((form, index) => {
    const li = document.createElement("li");
    li.classList.add("nav-item");

    const btn = document.createElement("button");
    btn.type = "button";
    btn.classList.add("nav-link");
    btn.textContent = getFormLabel(form);
    btn.addEventListener("click", () => {
      state.currentIndex = index;
      renderCurrentForm();
      updateActiveTab();
    });

    li.appendChild(btn);
    tabsContainer.appendChild(li);
    state.tabButtons.push(btn);
  });

  updateActiveTab();
}

function getFormLabel(form) {
  const number = form?.form_number || "";
  const group = form?.main_data?.expenses_group || "";
  const isSingle = group === "1" || group === "3";
  const groupText = isSingle ? "เดินทางคนเดียว" : "เดินทางเป็นหมู่คณะ";
  return `ฟอร์มที่ ${number} : ${groupText}`;
}

function updateActiveTab() {
  state.tabButtons.forEach((btn, idx) => {
    btn.classList.toggle("active", idx === state.currentIndex);
  });
}

function renderCurrentForm() {
  const formEntry = state.forms[state.currentIndex];
  if (!formEntry) return;

  const m = formEntry.main_data || {};
  const mp = formEntry.main_data_plan || {};
  const cp = Array.isArray(formEntry.child_data_plan)
    ? formEntry.child_data_plan
    : [];
  const tvg = Array.isArray(formEntry.traveling_group_data)
    ? formEntry.traveling_group_data
    : [];
  const vehicleRows = Array.isArray(formEntry.child_data)
    ? formEntry.child_data
    : [];
  const approvals = Array.isArray(formEntry.approve_data)
    ? formEntry.approve_data
    : [];

  const detailTitle = document.getElementById("formDetailTitle");
  if (detailTitle) {
    detailTitle.textContent = getFormLabel(formEntry);
  }

  if (m.plan_id) {
    $(".have_plan_id").css("display", "");
    $(".fund_and_project").hide();
  } else {
    $(".have_plan_id").css("display", "none");
    $(".fund_and_project").show();
  }

  document.getElementById("expenses_form_name").value =
    m.expenses_form_name || "";
  setupThaiDateTimePicker("#expenses_start", m.expenses_start, true);
  setupThaiDateTimePicker("#expenses_end", m.expenses_end, true);

  document.getElementById("depart_code").value = m.depart_code || "";
  document.getElementById("full_name").value = m.full_name || "";
  document.getElementById("position_name").value = m.position_name || "";
  document.getElementById("level_name").value = m.level_name || "";
  document.getElementById("affiliation_name").value = m.affiliation_name || "";

  $(".plan_number").text(m.plan_number || "");
  if (mp.fund) {
    $("#fund_select_label").val(mp.fund.replace(/<[^>]+>/g, ""));
    $("#fund_html_label").html("");
  } else {
    $("#fund_select_label").val("เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)");
    $("#fund_html_label").html('<span style="color:red;">*ไม่พบกองทุน</span>');
  }
  if (mp.project) {
    $("#project_select_label").val(mp.project);
  } else {
    document.getElementById("project_select_label").value = "";
  }

  const planTableBody = document.querySelector("#planItemsTable tbody");
  if (planTableBody) {
    planTableBody.innerHTML = "";
  }
  let grandTotal = 0;
  cp.forEach((item, key) => {
    const row = document.createElement("tr");
    row.innerHTML = `
                <td class="text-center">${key + 1}</td>
                <td>${item.wrd_mat_code || ""} - ${item.wrd_mat_name || ""}</td>
                <td class="text-center">${item.qty || ""}</td>
                <td class="text-center">${item.count_unit_name || ""}</td>
                <td class="text-center">${Number(
      item.price || 0
    ).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}</td>
                <td class="text-center">${Number(
      item.total || 0
    ).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}</td>
            `;
    planTableBody?.appendChild(row);
    grandTotal += Number(item.total || 0);
  });
  const totalElement = document.getElementById("total");
  if (totalElement) {
    totalElement.textContent = grandTotal.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  document.getElementById("day_return").checked = mp.day_return === "t";
  document.getElementById("money_receipt").checked = mp.money_receipt === "t";
  document.getElementById("note").value = mp.note || "";

  document.getElementById("expenses_code").value = m.expenses_code || "";
  document.getElementById("expenses_learn").value = m.expenses_learn || "";
  setupThaiDateTimePicker("#date_notime", m.expenses_date, false);

  const groupCode = String(m.expenses_group ?? "");
  const isGroup = groupCode === "2";
  document.querySelectorAll(".one-person").forEach((section) => {
    section.classList.toggle("d-none", isGroup);
  });
  const countInput = document.getElementById("group_people_count");
  if (countInput) {
    const countValue =
      m.expenses_group_count || (isGroup ? tvg.length : "");
    countInput.value = countValue || "";
  }
  const groupWrapper = document.getElementById("ifYes");
  if (groupWrapper) {
    if (isGroup && tvg.length) {
      groupWrapper.style.display = "";
    } else {
      groupWrapper.style.display = "none";
    }
  }
  if (m.expenses_start_location) {
    const startRadio = document.querySelector(
      `input[name="expenses_start_location"][value="${m.expenses_start_location}"]`
    );
    if (startRadio) startRadio.checked = true;
  }
  setupThaiDateTimePicker("#expenses_start_date", m.expenses_start_date, false);
  document.querySelector('textarea[name="expenses_start_address"]').value =
    m.expenses_start_address || "";

  if (m.expenses_end_location) {
    const endRadio = document.querySelector(
      `input[name="expenses_end_location"][value="${m.expenses_end_location}"]`
    );
    if (endRadio) endRadio.checked = true;
  }
  setupThaiDateTimePicker("#expenses_end_date", m.expenses_end_date, false);
  document.querySelector('textarea[name="expenses_end_address"]').value =
    m.expenses_end_address || "";

  document.getElementById("expenses_allowance").value =
    m.expenses_allowance || "";
  document.getElementById("expenses_allowance_days").value =
    m.expenses_allowance_days || "";
  document.getElementById("expenses_allowance_total").value =
    m.expenses_allowance_total || "";

  document.getElementById("expenses_accommodation").value =
    m.expenses_accommodation || "";
  document.getElementById("expenses_accommodation_days").value =
    m.expenses_accommodation_days || "";
  document.getElementById("expenses_accommodation_total").value =
    m.expenses_accommodation_total || "";

  document.getElementById("expenses_transportation").value =
    m.expenses_transportation || "";
  document.getElementById("expenses_transportation_total").value =
    m.expenses_transportation_total || "";
  document.getElementById("expenses_transportation_remark").value =
    m.expenses_transportation_remark || "";

  document.getElementById("expenses_moving").value = m.expenses_moving || "";
  document.getElementById("expenses_moving_km").value = m.expenses_moving_km || "";
  document.getElementById("expenses_moving_total").value =
    m.expenses_moving_total || "";

  document.getElementById("expenses_other").value = m.expenses_other || "";
  const otherTotalInput = document.getElementById("expenses_other_total");
  if (otherTotalInput) {
    const otherTotal = parseFloat(
      typeof m.expenses_other_total === "string"
        ? m.expenses_other_total.replace(/,/g, "")
        : m.expenses_other_total
    );
    otherTotalInput.value = Number.isFinite(otherTotal)
      ? otherTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })
      : "";
  }

  document.getElementById("expenses_food_per_meal").value =
    m.expenses_food_per_meal || "";
  document.getElementById("expenses_food_meals").value =
    m.expenses_food_meals || "";
  document.getElementById("expenses_food_total").value =
    m.expenses_food_total || "";

  if (!m.plan_id) {
    document.getElementById("fund_select_label_noplan").value = m.fund || "";
    document.getElementById("project_select_label_noplan").value =
      m.project || "";
  }

  $("#expenses_vehicle").prop("checked", m.expenses_vehicle === "car");
  $("#expenses_motorcycle").prop(
    "checked",
    m.expenses_vehicle === "motorcycle"
  );
  $("#expenses_vehicle_number").val(m.expenses_vehicle_number || "");

  const vehicleTableBody = document.getElementById("list-details");
  if (vehicleTableBody) {
    vehicleTableBody.innerHTML = "";
  }
  if (m.expenses_vehicle) {
    $(".has_vehicle").show();
    vehicleRows.forEach((row) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
            <td class="text-center">${formatThaiDate(row.list_date) || ""}</td>
            <td class="text-center">${row.time_start || "-"}</td>
            <td class="text-center">${row.time_end || "-"}</td>
            <td class="text-center">${row.time_depart || "-"}</td>
            <td class="text-center">${row.time_arrive || "-"}</td>
            <td class="text-end">${Number(row.distance || 0).toLocaleString(
        undefined,
        {
          minimumFractionDigits: 2,
        }
      )}</td>
            <td class="text-end">${Number(
        row.compensation_per_km || 0
      ).toLocaleString(undefined, {
        minimumFractionDigits: 2,
      })}</td>
            <td class="text-end">${Number(row.compensation || 0).toLocaleString(
        undefined,
        {
          minimumFractionDigits: 2,
        }
      )}</td>
            <td>${row.remark || ""}</td>
        `;
      vehicleTableBody?.appendChild(tr);
    });
    const totalVehicle = vehicleRows.reduce(
      (sum, row) => sum + parseFloat(row.compensation || 0),
      0
    );
    document.getElementById("total_amount_details").value =
      totalVehicle.toLocaleString(undefined, { minimumFractionDigits: 2 });
    document.getElementById("total_text_details").value =
      totalVehicle > 0 ? convertNumberToThaiText(totalVehicle) : "ศูนย์บาทถ้วน";
  } else {
    $(".has_vehicle").hide();
    document.getElementById("total_amount_details").value = "";
    document.getElementById("total_text_details").value = "";
  }

  const groupTableBody = document.querySelector("#groupPeopleTable tbody");
  if (groupTableBody) {
    groupTableBody.innerHTML = "";
  }
  let groupTotal = 0;
  tvg.forEach((item, index) => {
    const totalRow =
      parseFloat(item.total_allowance || 0) +
      parseFloat(item.accommodation_total || 0) +
      parseFloat(item.transport || 0) +
      parseFloat(item.other_expenses || 0) -
      parseFloat(item.meal_total || 0);
    groupTotal += totalRow;

    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td class="text-center">${index + 1}</td>
        <td>${item.fullname || ""}</td>
        <td>${item.position || ""}</td>
        <td class="text-end">${Number(
      item.allowance || 0
    ).toLocaleString()}</td>
        <td class="text-center">${item.allowance_days || ""}</td>
        <td class="text-end">${Number(
      item.total_allowance || 0
    ).toLocaleString()}</td>
        <td class="text-end">${Number(
      item.accommodation || 0
    ).toLocaleString()}</td>
        <td class="text-center">${item.accommodation_days || ""}</td>
        <td class="text-end">${Number(
      item.accommodation_total || 0
    ).toLocaleString()}</td>
        <td class="text-end">${Number(
      item.transport || 0
    ).toLocaleString()}</td>
        <td class="text-end">${Number(
      item.other_expenses || 0
    ).toLocaleString()}</td>
        <td class="text-end">${Number(item.meal || 0).toLocaleString()}</td>
        <td class="text-center">${item.meal_count || ""}</td>
        <td class="text-end">${Number(
      item.meal_total || 0
    ).toLocaleString()}</td>
        <td class="text-end fw-bold">${totalRow.toLocaleString(undefined, {
      minimumFractionDigits: 2,
    })}</td>
    `;
    groupTableBody?.appendChild(tr);
  });
  document.getElementById("expenses_group_total").value =
    groupTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
  document.getElementById("expenses_group_total_text").value =
    groupTotal > 0 ? convertNumberToThaiText(groupTotal) : "ศูนย์บาทถ้วน";

  document.getElementById("expenses_note").value = m.expenses_note || "";
  document.getElementById("expenses_total").value = Number(
    m.expenses_total || 0
  ).toLocaleString(undefined, { minimumFractionDigits: 2 });
  updateExpensesTotalText();

  document.getElementById("plan_number").value = m.plan_number || "";
  document.getElementById("plan_date").value = m.expenses_date || "";
  document.getElementById("plan_total").value = m.plan_total || "";

  document.getElementById("plan_number_display").value = m.plan_number || "";
  document.getElementById("plan_date_display").value = m.expenses_date || "";
  document.getElementById("plan_total_display").value = m.plan_total || "";

  document.getElementById("expenses_return").checked =
    m.expenses_action === "return";
  document.getElementById("expenses_withdraw").checked =
    m.expenses_action === "withdraw";
  document.getElementById("expenses_amount").value = Number(
    m.expenses_amount || 0
  ).toLocaleString("en-US", { minimumFractionDigits: 2 });

  document.getElementById("expenses_confirm").checked =
    m.expenses_confirm === "1";

  const approvalTable = document.querySelector("#approval-table tbody");
  if (approvalTable) {
    approvalTable.innerHTML = "";
  }
  if (!approvals.length) {
    const row = document.createElement("tr");
    row.innerHTML = `<td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td>`;
    approvalTable?.appendChild(row);
  } else {
    approvals.forEach((item, index) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${item.approve_date ? formatThaiDatetime(item.approve_date) : ""
        }</td>
                <td class="text-center">${item.approve_status === "finance_approve"
          ? "กองการเงิน อนุมัติ"
          : item.approve_status === "audit_approve"
            ? "กองตรวจจ่าย อนุมัติ"
            : item.approve_status === "finance_reject"
              ? "กองการเงิน ไม่อนุมัติ"
              : item.approve_status === "audit_reject"
                ? "กองตรวจจ่าย ไม่อนุมัติ"
                : ""
        }</td>
                <td>${item.user_fname || ""} ${item.user_lname || ""}</td>
                <td>${item.remark || ""}</td>
            `;
      approvalTable?.appendChild(row);
    });
  }
}

function bindApprovalControls() {
  const approveCheckbox = document.getElementById("approve");
  const rejectCheckbox = document.getElementById("reject");
  const remarkTextarea = document.getElementById("remark");

  approveCheckbox?.addEventListener("change", () => {
    if (approveCheckbox.checked) {
      if (rejectCheckbox) rejectCheckbox.checked = false;
      $("#hide_text_approve").hide();
    } else if (!rejectCheckbox?.checked) {
      $("#hide_text_approve").show();
    }
  });

  rejectCheckbox?.addEventListener("change", () => {
    if (rejectCheckbox.checked) {
      if (approveCheckbox) approveCheckbox.checked = false;
      $("#hide_text_approve").show();
    } else if (!approveCheckbox?.checked) {
      $("#hide_text_approve").show();
    }
  });

  const btnSubmit = document.getElementById("btn-update");
  if (btnSubmit) {
    btnSubmit.addEventListener("click", () =>
      updateData(approveCheckbox, rejectCheckbox, remarkTextarea)
    );
  }
}

async function updateData(approveCheckbox, rejectCheckbox, remarkTextarea) {
  try {
    const btnSubmit = document.getElementById("btn-update");
    if (btnSubmit) btnSubmit.disabled = true;

    const checkboxGroups = [".approve, .reject"];
    if (!validateRequiredFields(checkboxGroups)) return;
    if (!validateRejectRemark(rejectCheckbox, remarkTextarea)) return;

    const dataSent = {
      main_data: {
        approve: approveCheckbox?.checked || false,
        reject: rejectCheckbox?.checked || false,
        remark: remarkTextarea?.value.trim() || "",
        user_code: state.dataUser?.user_code || "",
      },
    };

    window.showLoading();

    const response = await fetch(
      `../controllers/withdraws/wrd_expenses_controller.php?action=approve_multi&id=${state.expensesId}&position_code=${state.posCode}`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            ?.content,
        },
        body: JSON.stringify(dataSent),
      }
    );

    const result = await response.json();
    if (result.status === "success") {
      Swal.fire({
        title: "บันทึกข้อมูลสำเร็จ",
        text: "รายการถูกแก้ไขแล้ว",
        icon: "success",
      }).then(() => {
        window.location.href = "travel-expenses-approvel-list.php";
      });
    } else {
      throw new Error(result.message || "เกิดข้อผิดพลาดในการบันทึกข้อมูล");
    }
  } catch (error) {
    console.error("เกิดข้อผิดพลาด:", error);
    Swal.fire({
      title: "เกิดข้อผิดพลาด",
      text: error.message || "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
      icon: "error",
    });
  } finally {
    const btnSubmit = document.getElementById("btn-update");
    if (btnSubmit) btnSubmit.disabled = false;
    window.hideLoading();
  }
}

function validateRejectRemark(rejectCheckbox, remarkTextarea) {
  if (rejectCheckbox?.checked) {
    const remarkValue = remarkTextarea?.value.trim();
    if (!remarkValue) {
      Swal.fire({
        title: "ข้อมูลไม่ครบถ้วน",
        text: "กรุณากรอกข้อมูลหมายเหตุ",
        icon: "warning",
      });
      remarkTextarea?.focus();
      return false;
    }
  }
  return true;
}

function validateRequiredFields(checkboxGroups = []) {
  for (const groupSelector of checkboxGroups) {
    const checkboxes = document.querySelectorAll(groupSelector);
    if (!checkboxes.length) {
      Swal.fire({
        title: "ข้อมูลไม่ครบถ้วน",
        text: "ไม่พบ checkbox group: " + groupSelector,
        icon: "warning",
      });
      return false;
    }
    const isChecked = Array.from(checkboxes).some((cb) => cb.checked);
    if (!isChecked) {
      Swal.fire({
        title: "ข้อมูลไม่ครบถ้วน",
        text: "กรุณาเลือกสถานะการอนุมัติ",
        icon: "warning",
      });
      return false;
    }
  }
  return true;
}

function updateExpensesTotalText() {
  const totalInput = document.getElementById("expenses_total");
  const textInput = document.getElementById("expenses_total_text");
  if (!totalInput || !textInput) return;
  const numeric = parseFloat(String(totalInput.value || "").replace(/,/g, ""));
  if (Number.isFinite(numeric)) {
    textInput.value = convertNumberToThaiText(numeric);
  } else {
    textInput.value = "";
  }
}

function formatThaiDatetime(datetimeStr) {
  if (!datetimeStr) return "";
  const dateObj = new Date(datetimeStr);
  if (Number.isNaN(dateObj)) return "";
  const day = String(dateObj.getDate()).padStart(2, "0");
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const year = dateObj.getFullYear() + 543;
  const hours = String(dateObj.getHours()).padStart(2, "0");
  const minutes = String(dateObj.getMinutes()).padStart(2, "0");
  return `${day}-${month}-${year} ${hours}:${minutes}`;
}

function setupThaiDateTimePicker(selector, value, withTime = true) {
  const el = document.querySelector(selector);
  if (!el) return null;

  const picker = flatpickr(el, {
    enableTime: withTime,
    dateFormat: withTime ? "Y-m-d H:i:S" : "Y-m-d",
    altInput: true,
    altFormat: withTime ? "d-m-Y H:i" : "d-m-Y",
    onReady(selectedDates, dateStr, instance) {
      updateThaiYear(instance, withTime);
      instance.config.onChange.push(() => updateThaiYear(instance, withTime));
      instance.config.onMonthChange.push(() =>
        updateThaiYear(instance, withTime)
      );
      instance.config.onYearChange.push(() =>
        updateThaiYear(instance, withTime)
      );
    },
  });

  if (value) {
    picker.setDate(value, true);
    updateThaiYear(picker, withTime);
  }

  return picker;
}

function updateThaiYear(instance, withTime) {
  if (!instance.selectedDates.length) return;
  const date = instance.selectedDates[0];
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear() + 543;
  let formatted = `${day}-${month}-${year}`;

  if (withTime) {
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    formatted += ` ${hours}:${minutes}`;
  }

  instance.altInput.value = formatted;
}

function formatThaiDate(datetimeStr) {
  if (!datetimeStr) return "";
  const dateObj = new Date(datetimeStr);
  if (Number.isNaN(dateObj)) return "";
  const day = String(dateObj.getDate()).padStart(2, "0");
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const year = dateObj.getFullYear() + 543;
  return `${day}-${month}-${year}`;
}

function convertNumberToThaiText(number) {
  const txtnum1 = [
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
    "สิบ",
  ];
  const txtnum2 = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
  number = number.toString().replace(/[, ]/g, "");
  let [intPart, decPart] = number.split(".");
  let bahtText = "";
  let len = intPart.length;
  for (let i = 0; i < len; i++) {
    let n = parseInt(intPart.charAt(i));
    if (n !== 0) {
      if (i === len - 1 && n === 1 && len > 1) {
        bahtText += "เอ็ด";
      } else if (i === len - 2 && n === 2) {
        bahtText += "ยี่";
      } else if (i === len - 2 && n === 1) {
        bahtText += "";
      } else {
        bahtText += txtnum1[n];
      }
      bahtText += txtnum2[len - i - 1];
    }
  }
  bahtText = bahtText || "ศูนย์";
  bahtText += "บาท";
  if (!decPart || decPart === "00") {
    bahtText += "ถ้วน";
  } else {
    let satangText = "";
    for (let i = 0; i < decPart.length; i++) {
      let n = parseInt(decPart.charAt(i));
      if (n !== 0) {
        satangText += txtnum1[n] + txtnum2[decPart.length - i - 1];
      }
    }
    bahtText += satangText + "สตางค์";
  }
  return bahtText;
}
