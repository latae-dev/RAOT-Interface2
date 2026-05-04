import { getAllParams } from './withdraw_parameter.js';
import { getAllFund } from './withdraw_fund.js';
import { getAllProject } from './parameter_project.js';

const cloneDeep = (value) => {
    if (value === null || value === undefined) return value;
    return JSON.parse(JSON.stringify(value));
};

const state = {
    forms: [],
    currentIndex: 0,
    rootId: null,
    currentId: null,
    expensesNumber: null,
    rootFormIndex: 0,
    rootMainData: null,
    rootPlanData: null,
    rootPlanItems: [],
};

const normalizeMoneyValue = (value) => {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : NaN;
    }
    if (value === null || value === undefined) return NaN;
    const cleaned = String(value).replace(/[^0-9.-]/g, '').trim();
    if (!cleaned) return NaN;
    const parsed = parseFloat(cleaned);
    return Number.isNaN(parsed) ? NaN : parsed;
};

const syncCurrentMainData = (updates = {}) => {
    if (!updates || typeof updates !== 'object') return;
    const current = state.forms?.[state.currentIndex];
    if (!current) return;
    current.main_data = {
        ...(current.main_data || {}),
        ...updates,
    };
};

const commitExpensesTotalToState = (value) => {
    const numeric = normalizeMoneyValue(value);
    const safeNumber = Number.isFinite(numeric) ? numeric : 0;
    const formatted = safeNumber.toFixed(2);
    syncCurrentMainData({ expenses_total: formatted });
    return formatted;
};

window.travelExpensesSyncTotalToState = (value) => commitExpensesTotalToState(value);

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

    const buildDisplayName = (main = {}) => {
        const nameCandidates = [
            main.full_name_text_expenses,
            main.expenses_full_name,
            main.full_name,
        ];
        for (const candidate of nameCandidates) {
            const trimmed = candidate != null ? String(candidate).trim() : "";
            if (trimmed && !/^\d+$/.test(trimmed)) {
                return trimmed;
            }
        }

        const fallbackId = main.full_name_expenses || main.req_user_code;
        if (fallbackId && window.travelExpenseUserDropdown) {
            const user = window.travelExpenseUserDropdown.getUserById?.(String(fallbackId).trim());
            if (user) {
                const first = (user.user_fname || "").trim();
                const last = (user.user_lname || "").trim();
                const combined = `${first} ${last}`.trim();
                if (combined) return combined;
                if (user.user_code) return String(user.user_code).trim();
            }
        }

        return fallbackId ? String(fallbackId).trim() : "-";
    };

    const resolveName = (form) => {
        const main = form?.main_data || {};
        return buildDisplayName(main);
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
                const displayName = resolveName(form);
                formsTotal += amount;
                return `
          <tr>
            <td class="text-center">${index + 1}</td>
            <td class="text-start">${typeLabel}</td>
            <td class="text-start">${displayName}</td>
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


    const update = () => {
        render();
    };

    return { render, update };
})();

window.formsSummaryManager = {
    update() {
        formsSummary.update();
    },
};

const userDropdownManager = (() => {
    let users = null;
    let usersById = new Map();
    let usersByCode = new Map();
    let usersByFullName = new Map();
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
        const affiliationInput = form.querySelector('[name="affiliation_name_expenses"]');
        const hiddenNameInput = form.querySelector('[name="full_name_text_expenses"]');

        if (hiddenNameInput) hiddenNameInput.value = fullName;
        if (positionInput) positionInput.value = user?.position_name || "";
        if (levelInput) levelInput.value = user?.level_name || "";
        if (affiliationInput)
            affiliationInput.value = user?.depart_name || "";

        syncCurrentMainData({
            full_name_expenses: select.value || "",
            full_name_text_expenses: fullName,
            position_name_expenses: positionInput?.value || "",
            level_name_expenses: levelInput?.value || "",
            affiliation_name_expenses: affiliationInput?.value || "",
        });
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

    const ensureOptions = (form, select) => {
        if (!select) return false;
        // ถ้ายังไม่มี options ของ user ให้ populate ใหม่
        if (select.options.length <= 1) {
            populateSelect(form, select);
        }
        return true;
    };

    const selectInForm = (form, user) => {
        const select = form?.querySelector(".user-select");
        if (!form || !select || !user || user.id == null) return false;
        ensureOptions(form, select);
        const idStr = String(user.id);
        const hasOption = Array.from(select.options).some((o) => o.value === idStr);
        if (!hasOption) populateSelect(form, select);
        select.value = idStr;
        updateFields(form, select);
        select.dispatchEvent(new Event("change", { bubbles: true }));
        return true;
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
        document.querySelectorAll("form#advanceFrm").forEach((form) => initForm(form));
    };

    return {
        setUsers(data) {
            const list = Array.isArray(data)
                ? data.filter((item) => item && item.id !== undefined && item.id !== null)
                : [];
            users = list;
            loadError = false;

            usersById = new Map(list.map((u) => [String(u.id), u]));
            usersByCode = new Map(
                list.filter((u) => u?.user_code).map((u) => [String(u.user_code).trim(), u])
            );
            usersByFullName = new Map(list.map((u) => [getFullName(u), u]));

            refreshAllForms();
        },
        setLoadError() {
            users = [];
            loadError = true;
            usersById = new Map();
            usersByCode = new Map();
            usersByFullName = new Map();
            refreshAllForms();
        },
        initForm,
        // ✅ Public helpers
        selectByCode(form, userCode) {
            if (!userCode) return false;
            const u = usersByCode.get(String(userCode).trim());
            return selectInForm(form, u);
        },
        selectByFullName(form, fullName) {
            const name = (fullName || "").trim();
            if (!name) return false;
            const u = usersByFullName.get(name);
            return selectInForm(form, u);
        },
        getUserById(id) {
            return usersById.get(String(id));
        },
        getUserByFullName(fullName) {
            return usersByFullName.get((fullName || "").trim());
        },
    };
})();

window.travelExpenseUserDropdown = userDropdownManager;


const getGroupDescriptor = (code) => {
    const value = String(code ?? '').trim();
    if (value === '2') return 'เดินทางเป็นหมู่คณะ';
    return 'เดินทางคนเดียว';
};

const getFormLabel = (form, index) => {
    const number = form?.form_number ?? index + 1;
    const group = form?.main_data?.expenses_group;
    return `ฟอร์มที่ ${number} : ${getGroupDescriptor(group)}`;
};

const refreshFormNumbers = () => {
    state.forms.forEach((form, idx) => {
        form.form_number = idx + 1;
    });
};

const getSelectedVehicleValue = () => {
    const carInput = document.getElementById('expenses_vehicle');
    if (carInput?.checked) return 'car';
    const motorcycleInput = document.getElementById('expenses_motorcycle');
    if (motorcycleInput?.checked) return 'motorcycle';
    return '';
};

(async () => {
    // ตัวแปรสำหรับเก็บข้อมูลหลักเพื่อใช้ restore/switch ฟอร์ม
    let m = null, mp = null, cd = null, cdp = null, tgd = null;
    try {
        // ดึงข้อมูลจาก URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const fund_all = await getAllFund();
        const project_all = await getAllProject();
        const expensesId = urlParams.get('id');
        let projectDefaultId = null;

        if (!expensesId) {
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบรหัสข้อมูลที่ต้องการดู",
                icon: "error"
            }).then(() => {
                window.location.href = 'withdraw-money-list.php';
            });
            return;
        }

        const params_all = await getAllParams();
        const data_user = params_all.current_user;
        state.data_user = data_user;
        document.getElementById('depart_code').value = data_user.depart_code || '';
        document.getElementById('full_name').value = `${data_user.user_fname || ''} ${data_user.user_lname || ''}`;
        document.getElementById('position_name').value = data_user.position_name || '';
        document.getElementById('level_name').value = data_user.level_name || '';
        document.getElementById('affiliation_name').value = data_user.depart_name || '';
        // console.log(params_all);

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
        } else {
            userDropdownManager.setLoadError();
        }

        /* เงินทุน */
        const fundSource = fund_all.fund_lists || [];
        if (!Array.isArray(fundSource) || fundSource.length === 0) {
            console.error('❌ ไม่พบข้อมูล:', fundSource);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่พบข้อมูลหน่วยนับ กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

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

        $('#fund_select').select2({
            data: fundOptions,
            escapeMarkup: function (markup) { return markup; },
            templateResult: function (data) { return data.text; },
            templateSelection: function (data) { return data.text; }
        });

        // เพิ่ม event listener สำหรับ fund_select
        $('#fund_select').on('change', async function () {
            const fund_id = $(this).val();
            const project_all = await getAllProject(fund_id);
            $('#project_select').empty();

            const projectSource = project_all.project_lists || [];
            const projectOptions = [
                { id: '', text: 'กรุณาเลือกโครงการ', disabled: true },
                ...projectSource.map(project_lists => ({
                    id: project_lists.id,
                    text: project_lists.project_name,
                }))
            ];
            $.each(projectOptions, function (index, project) {
                $('#project_select').append($('<option></option>').attr('value', project.id).text(project.text));
            });

            if (projectDefaultId) {
                $('#project_select').val(projectDefaultId).trigger('change');
                projectDefaultId = null;
            } else {
                $('#project_select').val('').trigger('change');
            }
        });
        /* เงินทุน */
        /* โครงการ */
        $('#project_select').select2();
        const projectSource = project_all.project_lists || [];
        const projectOptions = [
            { id: '', text: 'กรุณาเลือกโครงการ', disabled: true },
            ...projectSource.map(project_lists => ({
                id: project_lists.id,
                text: project_lists.project_name,
            }))
        ];
        $.each(projectOptions, function (index, project) {
            $('#project_select').append($('<option></option>').attr('value', project.id).text(project.text));
        });
        /* โครงการ */

        const addBtn = document.querySelector('.addTable-group-people');
        const delBtn = document.querySelector('.delTable-group-people');
        const tableBody = document.querySelector('#ifYes table tbody');

        const checkAllBox = document.querySelector('.check-all');
        if (checkAllBox) {
            checkAllBox.addEventListener('change', function () {
                const checked = this.checked;
                tableBody.querySelectorAll('.group-checkbox input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }

        function updateRowNumbers() {
            tableBody.querySelectorAll('tr.group-people-list').forEach((row, idx) => {
                const numberInput = row.children[1].querySelector('input');
                if (numberInput) {
                    numberInput.value = idx + 1;
                }
            });
        }

        function updateGroupPeopleCount() {
            const groupPeopleCountInput = document.getElementById('group_people_count');
            const yesCheck = document.getElementById('yesCheck');
            const noCheck = document.getElementById('noCheck');
            const tableBody = document.querySelector('#ifYes table tbody');

            if (!groupPeopleCountInput) return;

            if (noCheck && noCheck.checked) {
                groupPeopleCountInput.value = 1;
            } else if (yesCheck && yesCheck.checked) {
                if (tableBody) {
                    const count = tableBody.querySelectorAll('tr.group-people-list').length;
                    groupPeopleCountInput.value = count;
                } else {
                    groupPeopleCountInput.value = '';
                }
            } else {
                groupPeopleCountInput.value = '';
            }
        }

        function yesnoCheck() {
            const groupPeopleCountInput = document.getElementById('group_people_count');
            const yesCheck = document.getElementById('yesCheck');
            const noCheck = document.getElementById('noCheck');
            const tableBody = document.querySelector('#ifYes table tbody');
            const ifYesDiv = document.getElementById('ifYes');
            const onePersonElements = document.querySelectorAll('.one-person');

            if (!groupPeopleCountInput) return;
            if (noCheck && noCheck.checked) {
                groupPeopleCountInput.value = 1;
                if (tableBody) {
                    tableBody.querySelectorAll('tr.group-people-list').forEach(row => row.remove());
                }
                if (ifYesDiv) ifYesDiv.style.display = "none";
                onePersonElements.forEach(el => {
                    el.style.display = "";
                    el.querySelectorAll('input, textarea').forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    $('#expenses_allowance').val(m.expenses_allowance ? parseFloat(m.expenses_allowance).toFixed(2) : '');
                    $('#expenses_allowance_days').val(m.expenses_allowance_days ? parseFloat(m.expenses_allowance_days).toFixed(1) : '');
                    $('#expenses_allowance_total').val(m.expenses_allowance_total ? parseFloat(m.expenses_allowance_total).toFixed(2) : '');
                    $('#expenses_accommodation').val(m.expenses_accommodation ? parseFloat(m.expenses_accommodation).toFixed(2) : '');
                    $('#expenses_accommodation_days').val(m.expenses_accommodation_days ? parseFloat(m.expenses_accommodation_days).toFixed(1) : '');
                    $('#expenses_accommodation_total').val(m.expenses_accommodation_total ? parseFloat(m.expenses_accommodation_total).toFixed(2) : '');
                    $('#expenses_transportation').val(m.expenses_transportation ? parseFloat(m.expenses_transportation).toFixed(2) : '');
                    $('#expenses_transportation_total').val(m.expenses_transportation_total ? parseFloat(m.expenses_transportation_total).toFixed(2) : '');
                    $('#expenses_transportation_remark').val(m.expenses_transportation_remark || '');
                    $('#expenses_moving').val(m.expenses_moving ? parseFloat(m.expenses_moving).toFixed(2) : '');
                    $('#expenses_moving_total').val(m.expenses_moving_total ? parseFloat(m.expenses_moving_total).toFixed(2) : '');
                    $('#expenses_moving_km').val(m.expenses_moving_km ? parseFloat(m.expenses_moving_km).toFixed(2) : '');
                    $('#expenses_other').val(m.expenses_other ? parseFloat(m.expenses_other).toFixed(2) : '');
                    $('#expenses_other_remark').val(m.expenses_other_remark || '');
                    $('#expenses_other_total').val(m.expenses_other_total ? parseFloat(m.expenses_other_total).toFixed(2) : '');
                    $('#expenses_food_per_meal').val(m.expenses_food_per_meal ? parseFloat(m.expenses_food_per_meal).toFixed(2) : '');
                    $('#expenses_food_meals').val(m.expenses_food_meals ? parseFloat(m.expenses_food_meals).toFixed(1) : '');
                    $('#expenses_food_total').val(m.expenses_food_total ? parseFloat(m.expenses_food_total).toFixed(2) : '');
                });
                updateTotalAmount();
                calcExpensesTotal();
            } else if (yesCheck && yesCheck.checked) {
                if (tableBody) {
                    tableBody.querySelectorAll('tr.group-people-list').forEach(row => row.remove());
                    if (Array.isArray(tgd) && tgd.length > 0) {
                        tgd.forEach(item => {
                            createRow();
                            const lastRow = tableBody.lastElementChild;
                            lastRow.querySelector('.group_name').value = item.fullname || '';
                            lastRow.querySelector('.group_position').value = item.position || '';
                            lastRow.querySelector('.group_allowance').value = (item.allowance ? parseFloat(item.allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_days').value = (item.allowance_days ? parseFloat(item.allowance_days).toFixed(1) : '');
                            lastRow.querySelector('.group_total_allowance').value = (item.total_allowance ? parseFloat(item.total_allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation').value = (item.accommodation ? parseFloat(item.accommodation).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation_days').value = (item.accommodation_days ? parseFloat(item.accommodation_days).toFixed(1) : '');
                            lastRow.querySelector('.group_accommodation_total').value = (item.accommodation_total ? parseFloat(item.accommodation_total).toFixed(2) : '');
                            lastRow.querySelector('.group_transport').value = (item.transport ? parseFloat(item.transport).toFixed(2) : '');
                            lastRow.querySelector('.group_other_expenses').value = (item.other_expenses ? parseFloat(item.other_expenses).toFixed(2) : '');
                            lastRow.querySelector('.group_meal').value = (item.meal ? parseFloat(item.meal).toFixed(2) : '');
                            lastRow.querySelector('.group_meal_count').value = (item.meal_count ? parseFloat(item.meal_count).toFixed(1) : '');
                            lastRow.querySelector('.group_meal_total').value = (item.meal_total ? parseFloat(item.meal_total).toFixed(2) : '');
                            lastRow.querySelector('.group_sum').value = (item.sum ? parseFloat(item.sum).toFixed(2) : '');
                            updateTotalAmount();
                        });
                    } else {
                        createRow();
                    }
                    groupPeopleCountInput.value = tableBody.querySelectorAll('tr.group-people-list').length;
                } else {
                    groupPeopleCountInput.value = '';
                }
                if (ifYesDiv) ifYesDiv.style.display = "block";
                onePersonElements.forEach(el => {
                    el.style.display = "none";
                    el.querySelectorAll('input, textarea').forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                });
                updateTotalAmount();
                calcExpensesTotal();
            } else {
                groupPeopleCountInput.value = '';
                if (ifYesDiv) ifYesDiv.style.display = "none";
                onePersonElements.forEach(el => el.style.display = "");
            }
        }

        document.getElementById('noCheck')?.addEventListener('change', yesnoCheck);
        document.getElementById('yesCheck')?.addEventListener('change', yesnoCheck);

        document.getElementById('noCheck')?.addEventListener('change', updateGroupPeopleCount);
        document.getElementById('yesCheck')?.addEventListener('change', updateGroupPeopleCount);

        function createRow() {
            const newRow = document.createElement('tr');
            newRow.classList.add('group-people-list');
            // ตรวจสอบว่าเป็นแถวแรกหรือไม่
            const isFirstRow = tableBody.querySelectorAll('tr.group-people-list').length === 0;
            const defaultName = isFirstRow ? `${data_user.user_fname || ''} ${data_user.user_lname || ''}` : '';
            const defaultPosition = isFirstRow ? data_user.position_name : '';
            // ถ้าเป็นแถวแรก ให้ซ่อน checkbox เลย
            const checkboxHtml = isFirstRow
                ? ''
                : '<input class="form-check-input" type="checkbox" aria-label="...">';
            const firstRowClass = isFirstRow ? ' first-group-row' : '';
            newRow.innerHTML = `
                <td class="group-checkbox${firstRowClass}">${checkboxHtml}</td>
                <td><input class="form-control group_numrows" type="text" placeholder="Run Number Auto 1+" disabled></td>
                <td><input class="form-control group_name" type="text" placeholder="ระบุ ชื่อ"></td>
                <td><input class="form-control group_position" type="text" placeholder="ระบุ ตำแหน่ง/ระดับ"></td>
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
            document.querySelectorAll('.format-number-2point').forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value.replace(/,/g, ''));
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });
            document.querySelectorAll('.format-number-1point').forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value.replace(/,/g, ''));
                    if (!isNaN(val)) {
                        this.value = val.toFixed(1);
                    } else {
                        this.value = '';
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
            const numericValue = Number(number);
            if (!Number.isFinite(numericValue)) {
                return 'ศูนย์บาทถ้วน';
            }

            number = numericValue.toFixed(2);
            const txtNumArr = ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
            const txtDigitArr = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
            let [baht, satang] = number.split('.');
            let bahtText = '';
            let satangText = '';

            function readNumber(num) {
                if (typeof num !== 'string') {
                    num = String(num ?? '');
                }
                let text = '';
                num = num.replace(/^0+/, ''); // ตัด 0 หน้าสุด
                if (num.length === 0) return '';
                let len = num.length;
                for (let i = 0; i < len; i++) {
                    let n = parseInt(num.charAt(i));
                    if (n !== 0) {
                        if (i === len - 1 && n === 1 && len > 1) {
                            text += 'เอ็ด';
                        } else if (i === len - 2 && n === 2) {
                            text += 'ยี่';
                        } else if (i === len - 2 && n === 1) {
                            text += '';
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
                let text = '';
                let million = '';
                while (num.length > 6) {
                    let sub = num.slice(0, num.length - 6);
                    million += readNumber(sub) + 'ล้าน';
                    num = num.slice(-6);
                }
                text = million + readNumber(num);
                return text;
            }

            baht = baht.replace(/^0+/, '') || '0';
            if (parseInt(baht, 10) === 0) bahtText = 'ศูนย์บาท';
            else bahtText = readNumberWithMillion(baht) + 'บาท';

            satang = typeof satang === 'string' ? satang : '00';
            if (parseInt(satang, 10) === 0) satangText = 'ถ้วน';
            else satangText = readNumber(satang) + 'สตางค์';

            return bahtText + satangText;
        }

        function updateTotalAmount() {
            let totalAmount = 0;
            tableBody.querySelectorAll('tr.group-people-list').forEach(row => {
                const sumInput = row.querySelector('.group_sum');
                if (sumInput) {
                    const val = parseFloat(sumInput.value.replace(/,/g, '')) || 0;
                    totalAmount += val;
                }
            });
            const totalInputs = document.querySelectorAll('#expenses_group_total');
            if (totalInputs.length > 0) {
                totalInputs[0].value = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            const totalText = document.getElementById('expenses_group_total_text');
            if (totalText) {
                if (totalText.tagName === 'INPUT') {
                    totalText.value = numberToThaiText(totalAmount);
                } else {
                    totalText.textContent = numberToThaiText(totalAmount);
                }
            }
        }

        function bindAutoCalc(row) {
            const perDiemInput = row.children[4].querySelector('input');      // ค่าเบี้ยเลี้ยง/วัน
            const perDiemDayInput = row.children[5].querySelector('input');   // จำนวน(วัน)
            const perDiemSumInput = row.children[6].querySelector('input');   // รวม(บาท)
            const hotelInput = row.children[7].querySelector('input');        // ค่าเช่าที่พัก/วัน
            const hotelDayInput = row.children[8].querySelector('input');     // จำนวน(วัน)
            const hotelSumInput = row.children[9].querySelector('input');     // รวม(บาท)
            const vehicleInput = row.children[10].querySelector('input');     // ค่าพาหนะ
            const otherInput = row.children[11].querySelector('input');       // ค่าใช้จ่ายอื่นๆ
            const mealInput = row.children[12].querySelector('input');        // มื้อละ
            const mealQtyInput = row.children[13].querySelector('input');     // จำนวน(มื้อ)
            const mealSumInput = row.children[14].querySelector('input');     // รวม(บาท) หักอาหาร
            const totalInput = row.children[15].querySelector('input');       // รวม(บาท) ทั้งหมด

            function calc() {
                // คำนวณรวมค่าเบี้ยเลี้ยง
                const perDiem = parseFloat(perDiemInput.value) || 0;
                const perDiemDay = parseFloat(perDiemDayInput.value) || 0;
                const perDiemSum = perDiem * perDiemDay;
                perDiemSumInput.value = perDiemSum ? perDiemSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // คำนวณรวมค่าเช่าที่พัก
                const hotel = parseFloat(hotelInput.value) || 0;
                const hotelDay = parseFloat(hotelDayInput.value) || 0;
                const hotelSum = hotel * hotelDay;
                hotelSumInput.value = hotelSum ? hotelSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // คำนวณหักค่าอาหาร
                const meal = parseFloat(mealInput.value) || 0;
                const mealQty = parseFloat(mealQtyInput.value) || 0;
                const mealSum = meal * mealQty;
                mealSumInput.value = mealSum ? mealSum.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '';

                // รวมทั้งหมด
                const vehicle = parseFloat(vehicleInput.value) || 0;
                const other = parseFloat(otherInput.value) || 0;
                const total = perDiemSum + hotelSum + vehicle + other - mealSum;
                totalInput.value = total ? total.toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';

                updateTotalAmount();
                if (typeof calcExpensesTotal === 'function') calcExpensesTotal();
            }

            [perDiemInput, perDiemDayInput, hotelInput, hotelDayInput, vehicleInput, otherInput, mealInput, mealQtyInput].forEach(input => {
                input.addEventListener('input', calc);
            });
        }

        delBtn.addEventListener('click', e => {
            e.preventDefault();
            const checked = tableBody.querySelectorAll('.group-checkbox input.form-check-input:checked');
            if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
            checked.forEach(cb => cb.closest('tr')?.remove());
            updateRowNumbers();
            updateTotalAmount();
            updateGroupPeopleCount();
            calcExpensesTotal();
        });

        addBtn.addEventListener('click', e => {
            e.preventDefault();
            createRow();
            updateTotalAmount();
        });

        // createRow();
        // updateRowNumbers();
        // updateTotalAmount();

        // ====== สำหรับตารางรายละเอียดการเดินทาง (list-details) ======
        const tableBodyDetails = document.getElementById('vehicle_compensation_body');

        const addBtnDetails = document.querySelector('.addTable-list-details');
        const delBtnDetails = document.querySelector('.delTable-list-details');
        const checkAllBoxDetails = document.querySelector('.check-all-details');
        if (checkAllBoxDetails) {
            checkAllBoxDetails.addEventListener('change', function () {
                const checked = this.checked;
                tableBodyDetails.querySelectorAll('.row-check-details input.form-check-input')
                    .forEach(cb => cb.checked = checked);
            });
        }
        function bindFlatpickr(row) {
            // วันที่
            row.querySelectorAll('.list-detail-date').forEach(input => {
                const existingIso = normalizeDateInput(
                    input.dataset.isoValue || input.value || ''
                );
                if (existingIso) {
                    input.dataset.isoValue = existingIso;
                    input.value = existingIso;
                } else {
                    input.dataset.isoValue = '';
                    input.value = '';
                }
                flatpickr(input, {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d-m-Y",
                    defaultDate: input.dataset.isoValue || null,
                    locale: {
                        firstDayOfWeek: 1,
                        weekdays: {
                            shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                            longhand: [
                                'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ',
                                'พฤหัสบดี', 'ศุกร์', 'เสาร์'
                            ],
                        },
                        months: {
                            shorthand: [
                                'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                            ],
                            longhand: [
                                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                            ],
                        },
                    },
                    formatDate: (date, format, locale) => {
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const yearBE = date.getFullYear() + 543;
                        return `${day}-${month}-${yearBE}`;
                    },
                    parseDate: (datestr, format) => {
                        if (!datestr) return null;
                        const isoMatch = datestr.match(/^\d{4}-\d{1,2}-\d{1,2}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?$/);
                        if (isoMatch) {
                            const isoString = datestr.length === 10 ? `${datestr}` : datestr;
                            const parsed = new Date(isoString);
                            if (!Number.isNaN(parsed.valueOf())) {
                                return parsed;
                            }
                        }
                        const [d, m, y] = datestr
                            .replace(/[:\s]/g, "-")
                            .split("-");
                        return new Date(+y - 543, +m - 1, +d,);
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
                    }
                });
                if (input?._flatpickr) {
                    const fpInstance = input._flatpickr;
                    const syncBaseValue = (selectedDates) => {
                        if (!Array.isArray(selectedDates) || !selectedDates[0]) {
                            input.dataset.isoValue = '';
                            input.value = '';
                            return;
                        }
                        const iso = fpInstance.formatDate(selectedDates[0], 'Y-m-d');
                        input.dataset.isoValue = iso;
                        input.value = iso;
                    };

                    fpInstance.config.onValueUpdate.push((selectedDates) => {
                        syncBaseValue(selectedDates);
                    });

                    if (existingIso) {
                        fpInstance.setDate(existingIso, false, 'Y-m-d');
                        fpInstance.jumpToDate(existingIso, false);
                        syncBaseValue(fpInstance.selectedDates);
                    } else {
                        syncBaseValue(fpInstance.selectedDates);
                    }
                }
            });
            // เวลา
            // Logic ควบคุม minTime/maxTime ระหว่าง start/end กับ depart/arrive
            const timeStartInput = row.querySelector('.list-detail-time-start');
            const timeEndInput = row.querySelector('.list-detail-time-end');
            const timeDepartInput = row.querySelector('.list-detail-time-depart');
            const timeArriveInput = row.querySelector('.list-detail-time-arrive');

            let fpStart = null;
            let fpEnd = null;
            let fpDepart = null;
            let fpArrive = null;

            // สร้าง flatpickr และเก็บ instance
            row.querySelectorAll('.list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive').forEach(input => {
                const fp = flatpickr(input, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    allowInput: true
                });
                if (input.classList.contains('list-detail-time-start')) fpStart = fp;
                if (input.classList.contains('list-detail-time-end')) fpEnd = fp;
                if (input.classList.contains('list-detail-time-depart')) fpDepart = fp;
                if (input.classList.contains('list-detail-time-arrive')) fpArrive = fp;
            });

            // ฟังก์ชันหาเวลาน้อยที่สุดระหว่าง depart/arrive
            function getMinDepartArrive() {
                const departVal = timeDepartInput?.value;
                const arriveVal = timeArriveInput?.value;
                if (departVal && arriveVal) {
                    const [h1, m1] = departVal.split(':').map(Number);
                    const [h2, m2] = arriveVal.split(':').map(Number);
                    return (h1 * 60 + m1 < h2 * 60 + m2) ? departVal : arriveVal;
                }
                return departVal || arriveVal || null;
            }

            // ฟังก์ชันหาเวลามากที่สุดระหว่าง start/end
            function getMaxStartEnd() {
                const startVal = timeStartInput?.value;
                const endVal = timeEndInput?.value;
                if (startVal && endVal) {
                    const [h1, m1] = startVal.split(':').map(Number);
                    const [h2, m2] = endVal.split(':').map(Number);
                    return (h1 * 60 + m1 > h2 * 60 + m2) ? startVal : endVal;
                }
                return startVal || endVal || null;
            }

            // อัปเดต minTime ของ depart/arrive ทุกครั้งที่ start/end เปลี่ยน
            function updateMinTimeDepartArrive() {
                const maxStartEnd = getMaxStartEnd();
                if (fpDepart) fpDepart.set('minTime', maxStartEnd);
                if (fpArrive) fpArrive.set('minTime', maxStartEnd);
            }
            if (timeStartInput) timeStartInput.addEventListener('change', updateMinTimeDepartArrive);
            if (timeEndInput) timeEndInput.addEventListener('change', updateMinTimeDepartArrive);
            updateMinTimeDepartArrive();

            // อัปเดต maxTime ของ start/end ทุกครั้งที่ depart/arrive เปลี่ยน
            function updateMaxTimeStartEnd() {
                const minDepartArrive = getMinDepartArrive();
                if (fpStart) fpStart.set('maxTime', minDepartArrive);
                if (fpEnd) fpEnd.set('maxTime', minDepartArrive);
            }
            if (timeDepartInput) timeDepartInput.addEventListener('change', updateMaxTimeStartEnd);
            if (timeArriveInput) timeArriveInput.addEventListener('change', updateMaxTimeStartEnd);
            updateMaxTimeStartEnd();

            // START/END: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
            function handleTimeInputDisableStartEnd() {
                const timeStart = row.querySelector('.list-detail-time-start');
                const timeEnd = row.querySelector('.list-detail-time-end');
                if (!timeStart || !timeEnd) return;

                if (timeStart.value && !timeEnd.value) {
                    timeEnd.disabled = true;
                    timeStart.disabled = false;
                } else if (!timeStart.value && timeEnd.value) {
                    timeStart.disabled = true;
                    timeEnd.disabled = false;
                } else {
                    timeStart.disabled = false;
                    timeEnd.disabled = false;
                }
            }

            const timeStart = row.querySelector('.list-detail-time-start');
            const timeEnd = row.querySelector('.list-detail-time-end');
            if (timeStart) timeStart.addEventListener('input', handleTimeInputDisableStartEnd);
            if (timeEnd) timeEnd.addEventListener('input', handleTimeInputDisableStartEnd);
            handleTimeInputDisableStartEnd();

            // DEPART/ARRIVE: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
            function handleTimeInputDisableDepartArrive() {
                const timeDepart = row.querySelector('.list-detail-time-depart');
                const timeArrive = row.querySelector('.list-detail-time-arrive');
                if (!timeDepart || !timeArrive) return;

                if (timeDepart.value && !timeArrive.value) {
                    timeArrive.disabled = true;
                    timeDepart.disabled = false;
                } else if (!timeDepart.value && timeArrive.value) {
                    timeDepart.disabled = true;
                    timeArrive.disabled = false;
                } else {
                    timeDepart.disabled = false;
                    timeArrive.disabled = false;
                }
            }

            const timeDepart = row.querySelector('.list-detail-time-depart');
            const timeArrive = row.querySelector('.list-detail-time-arrive');
            if (timeDepart) timeDepart.addEventListener('input', handleTimeInputDisableDepartArrive);
            if (timeArrive) timeArrive.addEventListener('input', handleTimeInputDisableDepartArrive);
            handleTimeInputDisableDepartArrive();
        }

        function checkVehicleRequired() {
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            const requiredLabel = document.getElementById('expenses_vehicle_number_required');

            const tableBodyDetails = document.getElementById('vehicle_compensation_body');
            if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
                requiredLabel.style.display = 'inline';
                $('.display-vehicle').show();
                if (tableBodyDetails && tableBodyDetails.querySelectorAll('tr.list-details').length === 0) {
                    if (Array.isArray(cd) && cd.length > 0) {
                        cd.forEach(item => {
                            createRowDetails(false);
                            const row = tableBodyDetails.lastElementChild;

                            //  กรอกข้อมูล
                            const dateCellInput = row.querySelector('.list-detail-date');
                            const resolvedDateIso = normalizeDateInput(item.list_date || item.date || '');
                            if (dateCellInput) {
                                dateCellInput.dataset.isoValue = resolvedDateIso || '';
                                dateCellInput.value = resolvedDateIso
                                    ? formatThaiDate(resolvedDateIso)
                                    : '';
                            }
                            row.querySelector('.list-detail-time-start').value = item.time_start || '';
                            row.querySelector('.list-detail-time-end').value = item.time_end || '';
                            row.querySelector('.list-detail-time-depart').value = item.time_depart || '';
                            row.querySelector('.list-detail-time-arrive').value = item.time_arrive || '';
                            row.querySelector('.list-detail-distance').value = (item.distance ? parseFloat(item.distance).toFixed(2) : '');
                            row.querySelector('.list-detail-compensation').value = (item.compensation ? parseFloat(item.compensation).toFixed(2) : '');
                            const perKmInput = row.querySelector('.list-detail-compensation-per-km');
                            if (perKmInput && item.compensation_per_km != null && item.compensation_per_km !== '') {
                                const perKmNumeric = parseFloat(item.compensation_per_km);
                                perKmInput.value = Number.isFinite(perKmNumeric)
                                    ? perKmNumeric.toFixed(2)
                                    : item.compensation_per_km;
                            }
                            row.querySelector('.remark-detail').value = item.remark || '';
                            bindFlatpickr(row);
                            if (dateCellInput?._flatpickr) {
                                dateCellInput._flatpickr.setDate(resolvedDateIso || null, true, "Y-m-d");
                                if (dateCellInput._flatpickr.altInput) {
                                    dateCellInput._flatpickr.altInput.value = resolvedDateIso
                                        ? formatThaiDate(resolvedDateIso)
                                        : '';
                                }
                            }
                            // เวลา
                            // Logic ควบคุม minTime/maxTime ระหว่าง start/end กับ depart/arrive
                            const timeStartInput = row.querySelector('.list-detail-time-start');
                            const timeEndInput = row.querySelector('.list-detail-time-end');
                            const timeDepartInput = row.querySelector('.list-detail-time-depart');
                            const timeArriveInput = row.querySelector('.list-detail-time-arrive');

                            let fpStart = null;
                            let fpEnd = null;
                            let fpDepart = null;
                            let fpArrive = null;

                            // สร้าง flatpickr และเก็บ instance
                            row.querySelectorAll('.list-detail-time-start, .list-detail-time-end, .list-detail-time-depart, .list-detail-time-arrive').forEach(input => {
                                const fp = flatpickr(input, {
                                    enableTime: true,
                                    noCalendar: true,
                                    dateFormat: "H:i",
                                    time_24hr: true,
                                    allowInput: true
                                });
                                if (input.classList.contains('list-detail-time-start')) fpStart = fp;
                                if (input.classList.contains('list-detail-time-end')) fpEnd = fp;
                                if (input.classList.contains('list-detail-time-depart')) fpDepart = fp;
                                if (input.classList.contains('list-detail-time-arrive')) fpArrive = fp;
                            });

                            // ฟังก์ชันหาเวลาน้อยที่สุดระหว่าง depart/arrive
                            function getMinDepartArrive() {
                                const departVal = timeDepartInput?.value;
                                const arriveVal = timeArriveInput?.value;
                                if (departVal && arriveVal) {
                                    const [h1, m1] = departVal.split(':').map(Number);
                                    const [h2, m2] = arriveVal.split(':').map(Number);
                                    return (h1 * 60 + m1 < h2 * 60 + m2) ? departVal : arriveVal;
                                }
                                return departVal || arriveVal || null;
                            }

                            // ฟังก์ชันหาเวลามากที่สุดระหว่าง start/end
                            function getMaxStartEnd() {
                                const startVal = timeStartInput?.value;
                                const endVal = timeEndInput?.value;
                                if (startVal && endVal) {
                                    const [h1, m1] = startVal.split(':').map(Number);
                                    const [h2, m2] = endVal.split(':').map(Number);
                                    return (h1 * 60 + m1 > h2 * 60 + m2) ? startVal : endVal;
                                }
                                return startVal || endVal || null;
                            }

                            // อัปเดต minTime ของ depart/arrive ทุกครั้งที่ start/end เปลี่ยน
                            function updateMinTimeDepartArrive() {
                                const maxStartEnd = getMaxStartEnd();
                                if (fpDepart) fpDepart.set('minTime', maxStartEnd);
                                if (fpArrive) fpArrive.set('minTime', maxStartEnd);
                            }
                            if (timeStartInput) timeStartInput.addEventListener('change', updateMinTimeDepartArrive);
                            if (timeEndInput) timeEndInput.addEventListener('change', updateMinTimeDepartArrive);
                            updateMinTimeDepartArrive();

                            // อัปเดต maxTime ของ start/end ทุกครั้งที่ depart/arrive เปลี่ยน
                            function updateMaxTimeStartEnd() {
                                const minDepartArrive = getMinDepartArrive();
                                if (fpStart) fpStart.set('maxTime', minDepartArrive);
                                if (fpEnd) fpEnd.set('maxTime', minDepartArrive);
                            }
                            if (timeDepartInput) timeDepartInput.addEventListener('change', updateMaxTimeStartEnd);
                            if (timeArriveInput) timeArriveInput.addEventListener('change', updateMaxTimeStartEnd);
                            updateMaxTimeStartEnd();

                            // START/END: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                            function handleTimeInputDisableStartEnd() {
                                const timeStart = row.querySelector('.list-detail-time-start');
                                const timeEnd = row.querySelector('.list-detail-time-end');
                                if (!timeStart || !timeEnd) return;

                                if (timeStart.value && !timeEnd.value) {
                                    timeEnd.disabled = true;
                                    timeStart.disabled = false;
                                } else if (!timeStart.value && timeEnd.value) {
                                    timeStart.disabled = true;
                                    timeEnd.disabled = false;
                                } else {
                                    timeStart.disabled = false;
                                    timeEnd.disabled = false;
                                }
                            }

                            const timeStart = row.querySelector('.list-detail-time-start');
                            const timeEnd = row.querySelector('.list-detail-time-end');
                            if (timeStart) timeStart.addEventListener('input', handleTimeInputDisableStartEnd);
                            if (timeEnd) timeEnd.addEventListener('input', handleTimeInputDisableStartEnd);
                            handleTimeInputDisableStartEnd();

                            // DEPART/ARRIVE: ถ้ามีการกรอกอันใดอันหนึ่ง ให้ disabled อีกอัน, ถ้าว่างทั้งคู่ให้ไม่ disabled
                            function handleTimeInputDisableDepartArrive() {
                                const timeDepart = row.querySelector('.list-detail-time-depart');
                                const timeArrive = row.querySelector('.list-detail-time-arrive');
                                if (!timeDepart || !timeArrive) return;

                                if (timeDepart.value && !timeArrive.value) {
                                    timeArrive.disabled = true;
                                    timeDepart.disabled = false;
                                } else if (!timeDepart.value && timeArrive.value) {
                                    timeDepart.disabled = true;
                                    timeArrive.disabled = false;
                                } else {
                                    timeDepart.disabled = false;
                                    timeArrive.disabled = false;
                                }
                            }

                            const timeDepart = row.querySelector('.list-detail-time-depart');
                            const timeArrive = row.querySelector('.list-detail-time-arrive');
                            if (timeDepart) timeDepart.addEventListener('input', handleTimeInputDisableDepartArrive);
                            if (timeArrive) timeArrive.addEventListener('input', handleTimeInputDisableDepartArrive);
                            handleTimeInputDisableDepartArrive();

                            updateTotalAmountDetails();
                        });
                    } else {
                        createRowDetails();
                    }
                }
            } else {
                requiredLabel.style.display = 'none';
                $('.display-vehicle').hide();
                // ลบแถวในตารางรายละเอียดการเดินทางเมื่อไม่เลือกประเภทรถใด ๆ
                if (tableBodyDetails) {
                    tableBodyDetails.querySelectorAll('tr.list-details').forEach(row => row.remove());
                }
                // อัปเดทยอดรวมใหม่หลังลบ
                if (typeof updateTotalAmountDetails === 'function') updateTotalAmountDetails();
            }
        }

        function handleVehicleCheckboxChange(e) {
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            if (e.target.id === 'expenses_vehicle' && car.checked) {
                motorcycle.checked = false;
            } else if (e.target.id === 'expenses_motorcycle' && motorcycle.checked) {
                car.checked = false;
            }
            checkVehicleRequired();
        }

        checkVehicleRequired();
        document.getElementById('expenses_vehicle')?.addEventListener('change', handleVehicleCheckboxChange);
        document.getElementById('expenses_motorcycle')?.addEventListener('change', handleVehicleCheckboxChange);

        // ฟังก์ชันสร้างแถวใหม่
        function createRowDetails(bindflexpickr = true) {
            const newRow = document.createElement('tr');
            newRow.classList.add('list-details');
            // ตรวจสอบ vehicle
            let defaultPerKm = '';
            const car = document.getElementById('expenses_vehicle');
            const motorcycle = document.getElementById('expenses_motorcycle');
            if (car && car.checked) {
                defaultPerKm = '5.5';
            } else if (motorcycle && motorcycle.checked) {
                defaultPerKm = '3.0';
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

            if (bindflexpickr == true) {
                bindFlatpickr(newRow);
            }
            bindAutoCalcDetails(newRow);
        }

        // ฟังก์ชันคำนวณยอดรวมและแปลงเป็นตัวอักษร
        function updateTotalAmountDetails() {
            let totalAmount = 0;
            tableBodyDetails.querySelectorAll('tr.list-details').forEach(row => {
                const sumInput = row.querySelector('.sum-col-details');
                if (sumInput) {
                    const val = parseFloat(sumInput.value.replace(/,/g, '')) || 0;
                    totalAmount += val;
                }
            });
            // ช่องรวมยอดใน tfoot
            const tfoot = tableBodyDetails.parentElement.querySelector('tfoot');
            if (tfoot) {
                const totalInputs = tfoot.querySelectorAll('#total_amount_details:disabled');
                if (totalInputs.length > 0) {
                    totalInputs[0].value = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                // แสดงจำนวนเงินทั้งสิ้น (ตัวอักษร)
                const totalTextInput = tfoot.querySelector('#total_text_details');
                if (totalTextInput) {
                    totalTextInput.value = numberToThaiText(totalAmount);
                }
            }
            if (typeof calcExpensesTotal === 'function') calcExpensesTotal();
        }
        // bind auto calc เฉพาะช่องค่าชดเชย
        function bindAutoCalcDetails(row) {
            // Format compensation (เงินชดเชย) เป็น xx.xx
            const compensationInputs = row.querySelectorAll('.compensation, .compensation-detail, .sum-col-details, .list-detail-compensation');
            compensationInputs.forEach(input => {
                input.addEventListener('input', updateTotalAmountDetails);
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value);
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });

            // Format distance (ระยะทาง) เป็น xx.xx
            const distanceInputs = row.querySelectorAll('.distance, .distance-detail, .list-detail-distance');
            distanceInputs.forEach(input => {
                input.addEventListener('blur', function () {
                    let val = parseFloat(this.value);
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    } else {
                        this.value = '';
                    }
                });
            });

            // คำนวณเงินชดเชยอัตโนมัติ: ระยะทาง * เงินชดเชย/กม = รวมเงินชดเชย
            const distanceInput = row.querySelector('.list-detail-distance');
            const perKmInput = row.querySelector('.list-detail-compensation-per-km');
            const sumInput = row.querySelector('.sum-col-details');
            function autoCalcSumDetails() {
                const distance = parseFloat(distanceInput?.value) || 0;
                const perKm = parseFloat(perKmInput?.value) || 0;
                const sum = distance * perKm;
                if (sumInput) sumInput.value = sum ? sum.toFixed(2) : '';
                updateTotalAmountDetails();
            }
            if (distanceInput && perKmInput && sumInput) {
                distanceInput.addEventListener('input', autoCalcSumDetails);
                perKmInput.addEventListener('input', autoCalcSumDetails);
            }
        }

        // ปุ่มเพิ่มแถว (ต้องมีปุ่ม .addTable-list-details ใน HTML)
        if (addBtnDetails) {
            addBtnDetails.addEventListener('click', e => {
                e.preventDefault();
                createRowDetails();
                updateTotalAmountDetails();
            });
        }

        // เพิ่ม event listener สำหรับ checkbox ประเภทรถ
        const carCheckbox = document.getElementById('expenses_vehicle');
        const motorcycleCheckbox = document.getElementById('expenses_motorcycle');
        function updateCompensationPerKm() {
            let value = '';
            if (carCheckbox && carCheckbox.checked) {
                value = '5.5';
            } else if (motorcycleCheckbox && motorcycleCheckbox.checked) {
                value = '3.0';
            }
            // อัปเดตทุกแถวที่มี .list-detail-compensation-per-km และคำนวณใหม่
            document.querySelectorAll('.list-detail-compensation-per-km').forEach(input => {
                input.value = value;
                // trigger event เพื่อคำนวณใหม่
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
        if (carCheckbox) carCheckbox.addEventListener('change', updateCompensationPerKm);
        if (motorcycleCheckbox) motorcycleCheckbox.addEventListener('change', updateCompensationPerKm);

        // ปุ่มลบแถว (ต้องมีปุ่ม .delTable-list-details ใน HTML)
        if (delBtnDetails) {
            delBtnDetails.addEventListener('click', e => {
                e.preventDefault();
                const checked = tableBodyDetails.querySelectorAll('.row-check-details input.form-check-input:checked');
                if (!checked.length) return alert('กรุณาเลือกแถวที่ต้องการลบ');
                checked.forEach(cb => cb.closest('tr')?.remove());
                updateTotalAmountDetails();
                calcExpensesTotal();
            });
        }

        // สร้างแถวแรก
        // createRowDetails();
        // updateTotalAmountDetails();


        function populateFromPayload(formPayload = {}) {
            const payload = formPayload || {};
            m = payload.main_data || {};
            mp = payload.main_data_plan || {};
            cd = payload.child_data || [];
            cdp = payload.child_data_plan || [];
            tgd = payload.traveling_group_data || [];

            const rootMain = state.rootMainData || {};
            const rootPlan = state.rootPlanData || {};
            const rootPlanItems = Array.isArray(state.rootPlanItems)
                ? state.rootPlanItems
                : [];

            const planTableBody = document.querySelector('#planItemsTable tbody');
            if (planTableBody) {
                planTableBody.innerHTML = '';
            }
            const groupTableBody = document.querySelector('#ifYes table tbody');
            if (groupTableBody) {
                groupTableBody.innerHTML = '';
            }
            const listDetailsBody = document.getElementById('vehicle_compensation_body');
            if (listDetailsBody) {
                listDetailsBody.innerHTML = '';
            }

            const approveButtons = `
                <a href="travel-expenses-money-list.php" class="btn btn-warning m-1">
                    <i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู
                </a>
                <button id="btn-update" type="button" class="btn btn-primary m-1">
                    <i class="bi bi-save"></i> บันทึกการแก้ไข
                </button>
            `;
            document.getElementById('button-container').innerHTML = approveButtons;

            if (rootPlan.fund) {
                $('#fund_select_label').val(rootPlan.fund.replace(/<[^>]+>/g, ''));
                $('#fund_html_label').html('');
            } else {
                $('#fund_select_label').val(rootMain.fund || 'เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)');
                $('#fund_html_label').html('<span style="color:red;">*ไม่พบเงินทุน</span>');
            }
            if (rootPlan.project) {
                $('#project_select_label').val(rootPlan.project);
            } else {
                document.getElementById('project_select_label').value = rootMain.project || '';
            }
            if (rootPlan.day_return == 't') {
                document.getElementById('day_return').checked = true;
            }
            if (rootPlan.money_receipt == 't') {
                document.getElementById('money_receipt').checked = true;
            }
            document.getElementById('note').value = rootPlan.note || rootMain.note || '';

            let grandTotal = 0;
            if (rootPlanItems.length) {
                const planTable = document.getElementById('planItemsTable');
                const tableBody = planTable?.querySelector('tbody');
                if (tableBody) {
                    rootPlanItems.forEach((item, key) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">${key + 1}</td>
                            <td>${item.wrd_mat_code || ''} - ${item.wrd_mat_name || ''}</td>
                            <td class="text-center">${item.qty || ''}</td>
                            <td class="text-center">${item.count_unit_name || ''}</td>
                            <td class="text-center">${Number(item.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="text-center">${Number(item.total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        `;
                        tableBody.appendChild(row);
                        grandTotal += Number(item.total || 0);
                    });
                }
                const totalElement = document.getElementById('total');
                if (totalElement) {
                    totalElement.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                const sumTotal = rootPlanItems.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
                const planTotalInput = document.getElementById('plan_total');
                if (planTotalInput) {
                    planTotalInput.value = sumTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
            if (!rootPlanItems.length) {
                const planTotalInput = document.getElementById('plan_total');
                if (planTotalInput) {
                    const planValue = parseFloat(rootPlan.plan_total || rootMain.plan_total || 0);
                    planTotalInput.value = planValue
                        ? planValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '';
                }
                const totalElement = document.getElementById('total');
                if (totalElement) {
                    const totalValue = parseFloat(rootPlan.plan_total || rootMain.plan_total || 0);
                    totalElement.textContent = totalValue
                        ? totalValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '0.00';
                }
            }
            $('#plan_number').val(rootPlan.plan_number || rootMain.plan_number || '');
            $('#plan_date').val(formatThaiDate(rootPlan.st_hf_date || rootMain.st_hf_date) || '');

            $('#plan_number_display').val(rootPlan.plan_number || rootMain.plan_number || '');
            $('#plan_date_display').val(formatThaiDate(rootPlan.st_hf_date || rootMain.st_hf_date) || '');
            $('#plan_total_display').val(rootPlan.plan_total || rootMain.plan_total);

            if (rootMain.plan_id) {
                $('.have_plan_id').css('display', '');
                $('.no_plan_id').css('display', 'none');
            } else {
                $('.have_plan_id').css('display', 'none');
                $('.no_plan_id').css('display', '');
            }
            $('#plan_id').val(rootMain.plan_id || rootPlan.plan_id || '');

            $('.plan_number').text(rootMain.plan_number || rootPlan.plan_number || '');
            $('#expenses_form_name').val(rootMain.expenses_form_name || '');
            // $('#depart_code').val(rootMain.depart_code || '');
            // $('#full_name').val(rootMain.full_name || '');
            // $('#position_name').val(rootMain.position_name || '');
            // $('#level_name').val(rootMain.level_name || '');
            // $('#affiliation_name').val(m.affiliation_name || '');
            $('#expenses_transportation_remark').val(m.expenses_transportation_remark || '');
            $('[name="expenses_start"]').val(formatThaiDatetime(m.expenses_start || ''));
            $('[name="expenses_end"]').val(formatThaiDatetime(m.expenses_end || ''));

            $('#expenses_learn').val(m.expenses_learn || '');
            $('#expenses_code').val(m.expenses_code || '');
            if (m.expenses_group == 2) {
                $('.div-one-person').hide();
                $('.div-two-person').show();
                $('#yesCheck').prop('checked', true);
                $('#ifYes').css('display', 'block');
                $('.one-person').hide();
            } else {
                $('.div-one-person').show();
                $('.div-two-person').hide();
                $('#noCheck').prop('checked', true);
                $('#ifYes').css('display', 'none');
                $('.one-person').show();
            }
            $('#group_people_count').val(m.expenses_group_count || '');
            $('#is_foreign').val(m.is_foreign || '').trigger('change');
            switch (m.expenses_start_location) {
                case 'address':
                    $('#flexRadioDefault1_start').prop('checked', true);
                    break;
                case 'office':
                    $('#flexRadioDefault2_start').prop('checked', true);
                    break;
                case 'thailand':
                    $('#flexRadioDefault3_start').prop('checked', true);
                    break;
            }
            $('[name="expenses_start_address"]').val(m.expenses_start_address || '');
            switch (m.expenses_end_location) {
                case 'address':
                    $('#flexRadioDefault1_end').prop('checked', true);
                    break;
                case 'office':
                    $('#flexRadioDefault2_end').prop('checked', true);
                    break;
                case 'thailand':
                    $('#flexRadioDefault3_end').prop('checked', true);
                    break;
            }
            $('[name="expenses_end_address"]').val(m.expenses_end_address || '');
            $('[name="expenses_date"]').val(formatThaiDate(m.expenses_date) || '');
            $('[name="expenses_start_date"]').val(formatThaiDate(m.expenses_start_date) || '');
            $('[name="expenses_end_date"]').val(formatThaiDate(m.expenses_end_date) || '');

            console.log('m.expenses_start_date', m.expenses_start_date);
            console.log('m.expenses_end_date', m.expenses_end_date);
            console.log('m.expenses_date', m.expenses_date);
            // ตั้งค่า flatpickr สำหรับวันที่เริ่มต้น

            const expenses_start_date_input = document.querySelector('[name="expenses_start_date"]');
            if (expenses_start_date_input && expenses_start_date_input._flatpickr) {
                expenses_start_date_input._flatpickr.setDate(formatThaiDate(m.expenses_start_date) || null, true, "Y-m-d H:i");
            }

            const expenses_end_date_input = document.querySelector('[name="expenses_end_date"]');
            if (expenses_end_date_input && expenses_end_date_input._flatpickr) {
                expenses_end_date_input._flatpickr.setDate(formatThaiDate(m.expenses_end_date) || null, true, "Y-m-d H:i");
            }

            const expenses_date_input = document.querySelector('[name="expenses_date"]');
            if (expenses_date_input && expenses_end_date_input._flatpickr) {
                expenses_date_input._flatpickr.setDate(formatThaiDate(m.expenses_date) || null, true, "Y-m-d H:i");
            }

            $('#expenses_allowance').val(m.expenses_allowance ? parseFloat(m.expenses_allowance).toFixed(2) : '');
            $('#expenses_allowance_days').val(m.expenses_allowance_days ? parseFloat(m.expenses_allowance_days).toFixed(1) : '');
            $('#expenses_allowance_total').val(m.expenses_allowance_total ? parseFloat(m.expenses_allowance_total).toFixed(2) : '');
            $('#expenses_accommodation').val(m.expenses_accommodation ? parseFloat(m.expenses_accommodation).toFixed(2) : '');
            $('#expenses_accommodation_days').val(m.expenses_accommodation_days ? parseFloat(m.expenses_accommodation_days).toFixed(1) : '');
            $('#expenses_accommodation_total').val(m.expenses_accommodation_total ? parseFloat(m.expenses_accommodation_total).toFixed(2) : '');
            $('#expenses_transportation').val(m.expenses_transportation ? parseFloat(m.expenses_transportation).toFixed(2) : '');
            $('#expenses_transportation_total').val(m.expenses_transportation_total ? parseFloat(m.expenses_transportation_total).toFixed(2) : '');
            $('#expenses_moving').val(m.expenses_moving ? parseFloat(m.expenses_moving).toFixed(2) : '');
            $('#expenses_moving_total').val(m.expenses_moving_total ? parseFloat(m.expenses_moving_total).toFixed(2) : '');
            $('#expenses_moving_km').val(m.expenses_moving_km ? parseFloat(m.expenses_moving_km).toFixed(2) : '');
            const vehicleRaw = m.expenses_vehicle;
            let vehicleResolved = '';
            if (vehicleRaw === 'car' || vehicleRaw === 'motorcycle') {
                vehicleResolved = vehicleRaw;
            } else if (vehicleRaw === true || vehicleRaw === 'true') {
                vehicleResolved = 'car';
            } else if (
                m.expenses_motorcycle === true ||
                m.expenses_motorcycle === 'true'
            ) {
                vehicleResolved = 'motorcycle';
            }

            $('#expenses_vehicle').prop('checked', vehicleResolved === 'car');
            $('#expenses_motorcycle').prop('checked', vehicleResolved === 'motorcycle');
            if (vehicleResolved) {
                $('.display-vehicle').show();
            } else {
                $('.display-vehicle').hide();
            }
            checkVehicleRequired();
            $('#expenses_vehicle_number').val(m.expenses_vehicle_number || '');
            $('#expenses_other').val(m.expenses_other ? parseFloat(m.expenses_other).toFixed(2) : '');
            $('#expenses_other_remark').val(m.expenses_other_remark || '');
            $('#expenses_other_total').val(m.expenses_other_total ? parseFloat(m.expenses_other_total).toFixed(2) : '');
            $('#expenses_food_per_meal').val(m.expenses_food_per_meal ? parseFloat(m.expenses_food_per_meal).toFixed(2) : '');
            $('#expenses_food_meals').val(m.expenses_food_meals ? parseFloat(m.expenses_food_meals).toFixed(1) : '');
            $('#expenses_food_total').val(m.expenses_food_total ? parseFloat(m.expenses_food_total).toFixed(2) : '');
            $('#expenses_note').text(m.expenses_note || '');
            const totalNumeric = normalizeMoneyValue(m.expenses_total);
            if (Number.isFinite(totalNumeric)) {
                $('#expenses_total').val(totalNumeric.toFixed(2));
                $('#expenses_total_text').val(numberToThaiText(totalNumeric));
            } else {
                $('#expenses_total').val('0.00');
                $('#expenses_total_text').val(numberToThaiText(0));
            }
            if (m.expenses_action == 'return') {
                $('#expenses_return').prop('checked', true);
            }
            if (m.expenses_action == 'withdraw') {
                $('#expenses_withdraw').prop('checked', true);
            }
            $('#expenses_amount').val(Number(m.expenses_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }));
            if (m.fund_id) {
                $('#fund_select').val(m.fund_id).trigger('change');
                if (m.project_id) {
                    projectDefaultId = m.project_id;
                }
            } else {
                $('#fund_select').val('').trigger('change');
                projectDefaultId = null;
            }

            if (tgd && Array.isArray(tgd)) {
                if (m.expenses_group == 2) {
                    if (tgd.length > 0) {
                        tgd.forEach(item => {
                            createRow();
                            const lastRow = tableBody.lastElementChild;
                            lastRow.querySelector('.group_name').value = item.fullname || '';
                            lastRow.querySelector('.group_position').value = item.position || '';
                            lastRow.querySelector('.group_allowance').value = (item.allowance ? parseFloat(item.allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_days').value = (item.allowance_days ? parseFloat(item.allowance_days).toFixed(1) : '');
                            lastRow.querySelector('.group_total_allowance').value = (item.total_allowance ? parseFloat(item.total_allowance).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation').value = (item.accommodation ? parseFloat(item.accommodation).toFixed(2) : '');
                            lastRow.querySelector('.group_accommodation_days').value = (item.accommodation_days ? parseFloat(item.accommodation_days).toFixed(1) : '');
                            lastRow.querySelector('.group_accommodation_total').value = (item.accommodation_total ? parseFloat(item.accommodation_total).toFixed(2) : '');
                            lastRow.querySelector('.group_transport').value = (item.transport ? parseFloat(item.transport).toFixed(2) : '');
                            lastRow.querySelector('.group_other_expenses').value = (item.other_expenses ? parseFloat(item.other_expenses).toFixed(2) : '');
                            lastRow.querySelector('.group_meal').value = (item.meal ? parseFloat(item.meal).toFixed(2) : '');
                            lastRow.querySelector('.group_meal_count').value = (item.meal_count ? parseFloat(item.meal_count).toFixed(1) : '');
                            lastRow.querySelector('.group_meal_total').value = (item.meal_total ? parseFloat(item.meal_total).toFixed(2) : '');
                            lastRow.querySelector('.group_sum').value = (item.sum ? parseFloat(item.sum).toFixed(2) : '');
                            updateTotalAmount();
                        });
                    } else {
                        createRow();
                        updateTotalAmount();
                    }
                    document.getElementById('group_people_count').value = tgd.length;
                }
            } else {
                document.getElementById('group_people_count').value = '';
            }

            // ✅ ตั้งค่า default ผู้ใช้ตามฟอร์ม (จาก req_user_code หรือ fallback เป็นชื่อ)
            const baseForm = document.querySelector("form#advanceFrm");
            if (baseForm) {
                // ใช้ full_name ใน payload มาตั้ง default
                const fallbackName = m.full_name_text_expenses || m.full_name || "";
                if (fallbackName) {
                    window.travelExpenseUserDropdown.selectByFullName(baseForm, fallbackName);
                }
            }

        }

        // ดึงข้อมูลที่ต้องการดู
        try {
            const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=get_one_multi&id=${expensesId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (!response.ok) {
                throw new Error('ไม่สามารถดึงข้อมูลได้');
            }

            const data = await response.json();

            if (Array.isArray(data.forms)) {
                initializeMultiForm(data);
            } else {
                populateFromPayload(data);
                state.forms = [
                    {
                        form_number: 1,
                        main_data: data.main_data || {},
                        main_data_plan: data.main_data_plan || null,
                        child_data: data.child_data || [],
                        child_data_plan: data.child_data_plan || [],
                        traveling_group_data: data.traveling_group_data || [],
                    },
                ];
                state.currentIndex = 0;
                state.currentId = data.main_data?.id
                    ? Number(data.main_data.id)
                    : Number(expensesId);
                updateFormHeader();
                updateActionButtons();
                bindSubmitButton();
                const tabsContainer = document.getElementById('formTabs');
                if (tabsContainer) {
                    tabsContainer.innerHTML = '';
                    tabsContainer.classList.add('d-none');
                }
            }

        } catch (error) {
            console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่สามารถดึงข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
                icon: "error"
            });
            return;
        }

        // ดึงข้อมูลกลุ่ม (Group People)
        function getGroupPeopleData() {
            const rows = document.querySelectorAll('tr.group-people-list');
            const data = [];
            rows.forEach(row => {
                const name = row.querySelector('.group_name')?.value?.trim() || '';
                const position = row.querySelector('.group_position')?.value?.trim() || '';
                const allowance = parseFloat(row.querySelector('.group_allowance')?.value) || 0;
                const days = parseFloat(row.querySelector('.group_days')?.value) || 0;
                const total_allowance = parseFloat((row.querySelector('.group_total_allowance')?.value || '0').replace(/,/g, '')) || 0;
                const accommodation = parseFloat(row.querySelector('.group_accommodation')?.value) || 0;
                const accommodation_days = parseFloat(row.querySelector('.group_accommodation_days')?.value) || 0;
                const accommodation_total = parseFloat((row.querySelector('.group_accommodation_total')?.value || '0').replace(/,/g, '')) || 0;
                const transport = parseFloat(row.querySelector('.group_transport')?.value) || 0;
                const other_expenses = parseFloat(row.querySelector('.group_other_expenses')?.value) || 0;
                const meal = parseFloat(row.querySelector('.group_meal')?.value) || 0;
                const meal_count = parseFloat(row.querySelector('.group_meal_count')?.value) || 0;
                const meal_total = parseFloat((row.querySelector('.group_meal_total')?.value || '0').replace(/,/g, '')) || 0;
                const sum = parseFloat((row.querySelector('.group_sum')?.value || '0').replace(/,/g, '')) || 0;

                // เงื่อนไข: ถ้ามีข้อมูลอย่างน้อย 1 ช่อง ให้ push
                if (
                    name || position || allowance > 0 || days > 0 ||
                    total_allowance > 0 || accommodation > 0 || accommodation_days > 0 ||
                    accommodation_total > 0 || transport > 0 || other_expenses > 0 ||
                    meal > 0 || meal_count > 0 || meal_total > 0 || sum > 0
                ) {
                    data.push({
                        name,
                        position,
                        allowance,
                        days,
                        total_allowance,
                        accommodation,
                        accommodation_days,
                        accommodation_total,
                        transport,
                        other_expenses,
                        meal,
                        meal_count,
                        meal_total,
                        sum
                    });
                }
            });
            return data;
        }

        // ดึงข้อมูลรายละเอียดการเดินทาง (List Details)
        function getListDetailsData() {
            const tableBodyDetails = document.getElementById('vehicle_compensation_body');
            const rows = tableBodyDetails
                ? Array.from(tableBodyDetails.querySelectorAll('tr.list-details'))
                : [];
            const data = [];
            rows.forEach(row => {
                const dateInput = row.querySelector('.list-detail-date');
                const rawDate =
                    dateInput?.dataset.isoValue?.trim() ||
                    dateInput?.value?.trim() ||
                    '';
                const normalizedDate = normalizeDateInput(rawDate);
                if (dateInput) {
                    dateInput.dataset.isoValue = normalizedDate || '';
                }
                const time_start = row.querySelector('.list-detail-time-start')?.value?.trim() || '';
                const time_end = row.querySelector('.list-detail-time-end')?.value?.trim() || '';
                const time_depart = row.querySelector('.list-detail-time-depart')?.value?.trim() || '';
                const time_arrive = row.querySelector('.list-detail-time-arrive')?.value?.trim() || '';
                const distance = row.querySelector('.list-detail-distance')?.value
                    ? parseFloat(row.querySelector('.list-detail-distance').value) || 0
                    : 0;
                const compensation_per_km = row.querySelector('.list-detail-compensation-per-km')?.value
                    ? parseFloat(row.querySelector('.list-detail-compensation-per-km').value) || 0
                    : 0;
                const compensation = row.querySelector('.list-detail-compensation')?.value
                    ? parseFloat(row.querySelector('.list-detail-compensation').value) || 0
                    : 0;
                const remark = row.querySelector('.remark-detail')?.value?.trim() || '';

                // เงื่อนไข: ถ้ามีข้อมูลอย่างน้อย 1 ช่อง ให้ push
                if (
                    normalizedDate || time_start || time_end || time_depart || time_arrive ||
                    distance > 0 || compensation > 0 || remark
                ) {
                    data.push({
                        date: normalizedDate,
                        list_date: normalizedDate,
                        time_start,
                        time_end,
                        time_depart,
                        time_arrive,
                        distance,
                        compensation,
                        remark,
                        compensation_per_km
                    });
                }
            });
            return data;
        }

        function normalizeDateInput(value) {
            if (!value) return value;
            const trimmed = String(value).trim();
            if (!trimmed) return '';

            const isoLike = trimmed.match(/^\d{4}-\d{1,2}-\d{1,2}(?:\s+\d{1,2}:\d{2}(?::\d{2})?)?$/);
            if (isoLike) {
                const [datePart, timePart] = trimmed.split(/\s+/);
                const [y, m, d] = datePart.split('-').map((part, idx) =>
                    idx === 0 ? part : part.padStart(2, '0')
                );
                if (!timePart) return `${y}-${m}-${d}`;
                const timePieces = timePart.split(':').map((piece) => piece.padStart(2, '0'));
                return `${y}-${m}-${d} ${timePieces[0]}:${timePieces[1]}:${timePieces[2] ?? '00'}`;
            }

            const dmyMatch = trimmed.match(/^(\d{1,2})-(\d{1,2})-(\d{2,4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/);
            if (!dmyMatch) return trimmed;

            const day = String(dmyMatch[1]).padStart(2, '0');
            const month = String(dmyMatch[2]).padStart(2, '0');
            let year = parseInt(dmyMatch[3], 10);
            if (year > 2400) {
                year -= 543;
            } else if (year < 100) {
                year += year >= 70 ? 1900 : 2000;
            }
            const baseDate = `${String(year).padStart(4, '0')}-${month}-${day}`;
            if (dmyMatch[4] !== undefined) {
                const hour = String(dmyMatch[4]).padStart(2, '0');
                const minute = String(dmyMatch[5]).padStart(2, '0');
                const second = String(dmyMatch[6] ?? '00').padStart(2, '0');
                return `${baseDate} ${hour}:${minute}:${second}`;
            }
            return baseDate;
        }

        function getMainFormData() {
            const data = {};
            document.querySelectorAll('input[name], textarea[name], select[name]').forEach(el => {
                const name = el.name;
                if (!name) return;
                if (el.type === 'checkbox') {
                    data[name] = el.checked;
                } else if (el.type === 'radio') {
                    if (el.checked) data[name] = el.value;
                    // ถ้า radio ยังไม่ checked จะไม่ set ค่า
                } else {
                    data[name] = el.value;
                }
            });
            data['fund'] = $("#fund_select option:selected").text();
            data['fund_id'] = getFundNewValue();
            data['project'] = $("#project_select option:selected").text();
            data['project_id'] = getProjectNewValue();
            data['req_user_code'] = data_user.user_code;

            const dateFields = [
                'expenses_start',
                'expenses_end',
                'expenses_date',
                'expenses_start_date',
                'expenses_end_date',
                'plan_date'
            ];
            dateFields.forEach((field) => {
                if (field in data) {
                    data[field] = normalizeDateInput(data[field]);
                }
            });
            return data;
        }

        function getFundNewValue() {
            var fund_id = $("#fund_select").val();
            var fun_name = $("#fund_select option:selected").text();
            return fund_id;
        }

        function getProjectNewValue() {
            var project_id = $("#project_select").val();
            var project_name = $("#project_select option:selected").text();
            return project_id;
        }

        function getCurrentForm() {
            return state.forms[state.currentIndex] || null;
        }

        function updateFormHeader() {
            const titleEl = document.getElementById('formDetailTitle');
            if (!titleEl) return;
            const current = getCurrentForm();
            if (current) {
                titleEl.textContent = getFormLabel(current, state.currentIndex);
            } else {
                titleEl.textContent = 'รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน';
            }
        }

        function updateActionButtons() {
            const deleteBtn = document.getElementById('delete-form');
            if (!deleteBtn) return;
            const current = getCurrentForm();
            const isRoot = current?.main_data?.parent_id == null;
            deleteBtn.disabled = !current || state.forms.length <= 1 || isRoot;
        }

        function updateActiveTab() {
            const tabsContainer = document.getElementById('formTabs');
            if (!tabsContainer) return;
            const buttons = tabsContainer.querySelectorAll('.nav-link');
            buttons.forEach((btn, idx) => {
                btn.classList.toggle('active', idx === state.currentIndex);
            });
        }

        function setupFormTabs() {
            const tabsContainer = document.getElementById('formTabs');
            if (!tabsContainer) return;
            tabsContainer.innerHTML = '';
            refreshFormNumbers();
            if (state.forms.length <= 1) {
                tabsContainer.classList.add('d-none');
                return;
            }
            tabsContainer.classList.remove('d-none');
            state.forms.forEach((form, idx) => {
                const li = document.createElement('li');
                li.classList.add('nav-item');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.classList.add('nav-link');
                btn.textContent = getFormLabel(form, idx);
                btn.addEventListener('click', () => {
                    if (idx === state.currentIndex) return;
                    persistCurrentForm();
                    state.currentIndex = idx;
                    const next = getCurrentForm();
                    state.currentId = next?.main_data?.id ? Number(next.main_data.id) : null;
                    renderCurrentForm();
                    updateActiveTab();
                    updateActionButtons();
                });
                li.appendChild(btn);
                tabsContainer.appendChild(li);
            });
            updateActiveTab();
        }

        function persistCurrentForm() {
            const current = getCurrentForm();
            if (!current) return;
            const vehicleSelection = getSelectedVehicleValue();
            current.main_data = {
                ...current.main_data,
                ...getMainFormData(),
            };
            current.main_data.expenses_vehicle = vehicleSelection;
            current.main_data.expenses_motorcycle =
                vehicleSelection === 'motorcycle' ? 'motorcycle' : '';
            current.group_data = getGroupPeopleData();
            current.child_data = getListDetailsData();

            current.main_data.fund = $("#fund_select option:selected").text();
            current.main_data.fund_id = $('#fund_select').val() || '';
            current.main_data.project = $("#project_select option:selected").text();
            current.main_data.project_id = $('#project_select').val() || '';

            if (state.currentIndex === state.rootFormIndex) {
                state.rootMainData = cloneDeep(current.main_data);
                state.rootPlanData = cloneDeep(current.main_data_plan || {});
                state.rootPlanItems = cloneDeep(current.child_data_plan || []);
            }

            window.formsSummaryManager?.update();
        }

        function bindExpensesTotalField() {
            const totalInput = document.getElementById('expenses_total');
            if (!totalInput) return;
            const thaiTextInput = document.getElementById('expenses_total_text');

            const applyValue = (formatInput = false) => {
                const numeric = normalizeMoneyValue(totalInput.value);
                const resolved = Number.isFinite(numeric) ? numeric : 0;
                if (formatInput) {
                    totalInput.value = resolved.toFixed(2);
                }
                if (thaiTextInput) {
                    thaiTextInput.value = numberToThaiText(resolved);
                }
                commitExpensesTotalToState(resolved);
                window.formsSummaryManager?.update();
            };

            if (!totalInput.dataset.expensesTotalBound) {
                totalInput.addEventListener('input', () => applyValue(false));
                totalInput.addEventListener('blur', () => applyValue(true));
                totalInput.dataset.expensesTotalBound = 'true';
            }

            applyValue(false);
        }

        function renderCurrentForm() {
            const current = getCurrentForm();
            if (!current) return;
            projectDefaultId = current.main_data?.project_id || null;
            populateFromPayload(current);
            bindExpensesTotalField();
            updateFormHeader();
            bindSubmitButton();
            refreshUserSelectDisabledState();
        }

        function initializeMultiForm(payload) {
            const forms = Array.isArray(payload.forms) ? payload.forms : [];
            if (!forms.length) {
                throw new Error('ไม่พบข้อมูลแบบฟอร์มที่เกี่ยวข้อง');
            }
            state.forms = forms;
            state.rootId = payload.root_id ?? null;
            state.expensesNumber = forms[0]?.main_data?.expenses_number ?? null;

            const findRootIndex = () => {
                let idx = forms.findIndex(
                    form =>
                        String(form?.main_data?.expenses_group) === '3' &&
                        form?.main_data?.plan_id
                );
                if (idx >= 0) return idx;
                idx = forms.findIndex(form => form?.main_data?.plan_id);
                if (idx >= 0) return idx;
                idx = forms.findIndex(form => form?.main_data?.parent_id == null);
                if (idx >= 0) return idx;
                return 0;
            };

            const rootIndex = findRootIndex();
            state.rootFormIndex = rootIndex >= 0 ? rootIndex : 0;
            const rootForm = state.forms[state.rootFormIndex] || state.forms[0];
            state.rootMainData = cloneDeep(rootForm?.main_data || {});
            state.rootPlanData = cloneDeep(rootForm?.main_data_plan || {});
            state.rootPlanItems = cloneDeep(rootForm?.child_data_plan || []);

            const targetId = Number(expensesId);
            const foundIdx = forms.findIndex(form => Number(form?.main_data?.id) === targetId);
            state.currentIndex = foundIdx >= 0 ? foundIdx : 0;
            const current = getCurrentForm();
            state.currentId = current?.main_data?.id ? Number(current.main_data.id) : null;
            setupFormTabs();
            renderCurrentForm();
            updateActionButtons();
            window.formsSummaryManager?.update();
        }

        function bindSubmitButton() {
            const submitBtn = document.getElementById('btn-update');
            if (submitBtn) {
                submitBtn.addEventListener('click', updateData);
            }
        }

        async function handleAddForm(groupCode) {
            try {
                const normalizedGroup = String(groupCode ?? '').trim();
                if (!['1', '2'].includes(normalizedGroup)) {
                    Swal.fire({ icon: 'warning', title: 'รูปแบบไม่ถูกต้อง', text: 'ไม่สามารถสร้างฟอร์มได้' });
                    return;
                }

                persistCurrentForm();

                const rootForm =
                    state.forms.find(form => String(form?.main_data?.expenses_group) === '3') ||
                    state.forms[state.rootFormIndex] ||
                    state.forms[0] ||
                    getCurrentForm();
                if (!rootForm) {
                    Swal.fire({ icon: 'error', title: 'ไม่พบข้อมูลฟอร์มหลัก', text: 'ไม่สามารถสร้างฟอร์มใหม่ได้' });
                    return;
                }

                const rootMain = rootForm.main_data || {};
                // const parentId = rootMain.id || state.rootId || Number(expensesId);
                const parentId = state.rootId || Number(expensesId);


                const clonedMain = {
                    expenses_form_name: rootMain.expenses_form_name || '',
                    expenses_start: rootMain.expenses_start || '',
                    expenses_end: rootMain.expenses_end || '',
                    // expenses_date: rootMain.expenses_date || '',
                    depart_code: normalizedGroup === '2' ? rootMain.depart_code || '' : '',
                    full_name: normalizedGroup === '2' ? rootMain.full_name || '' : '',
                    position_name: normalizedGroup === '2' ? rootMain.position_name || '' : '',
                    level_name: normalizedGroup === '2' ? rootMain.level_name || '' : '',
                    affiliation_name: normalizedGroup === '2' ? rootMain.affiliation_name || '' : '',
                    expenses_start_address: '',
                    expenses_end_address: '',
                    expenses_group: normalizedGroup,
                    expenses_group_count: normalizedGroup === '2' ? 0 : 1,
                    expenses_number: state.expensesNumber || rootMain.expenses_number || '',
                    parent_id: parentId,
                    approve_status: 'waiting',
                    req_user_code: rootMain.req_user_code || params_all?.current_user?.user_code || '',
                    full_name_text_expenses: rootMain.full_name,
                    expenses_total: '0.00',
                };

                const newForm = {
                    form_number: state.forms.length + 1,
                    main_data: clonedMain,
                    main_data_plan: null,
                    child_data: [],
                    child_data_plan: [],
                    traveling_group_data: [],
                    isNew: true,
                };

                state.forms.push(newForm);
                state.currentIndex = state.forms.length - 1;
                state.currentId = null;

                setupFormTabs();
                renderCurrentForm();
                updateActionButtons();
                window.formsSummaryManager?.update();

                setTimeout(() => {
                    const startInput = document.querySelector('[name="expenses_start"]');
                    if (startInput && startInput._flatpickr) {
                        startInput._flatpickr.setDate(rootMain.expenses_start || null, true, "Y-m-d H:i");
                    }

                    const endInput = document.querySelector('[name="expenses_end"]');
                    if (endInput && endInput._flatpickr) {
                        endInput._flatpickr.setDate(rootMain.expenses_end || null, true, "Y-m-d H:i");
                    }

                    // const expensesDateInput = document.querySelector('[name="expenses_date"]');
                    // if (expensesDateInput && expensesDateInput._flatpickr) {
                    //     expensesDateInput._flatpickr.setDate(rootMain.expenses_date || null, true, "Y-m-d H:i");
                    // }
                    const confirmCheckbox = document.getElementById('expenses_confirm');
                    if (confirmCheckbox) {
                        confirmCheckbox.checked = false;
                    }
                }, 0);

                setTimeout(() => {
                    const startInput = document.querySelector('[name="expenses_start"]');
                    if (startInput && startInput._flatpickr) {
                        startInput._flatpickr.setDate(rootMain.expenses_start || null, true, "Y-m-d H:i");
                    }

                    const endInput = document.querySelector('[name="expenses_end"]');
                    if (endInput && endInput._flatpickr) {
                        endInput._flatpickr.setDate(rootMain.expenses_end || null, true, "Y-m-d H:i");
                    }

                    const confirmCheckbox = document.getElementById('expenses_confirm');
                    if (confirmCheckbox) {
                        confirmCheckbox.checked = false;
                    }

                    document.querySelectorAll('[name="expenses_start_location"]').forEach(r => {
                        r.checked = false;
                    });

                    document.querySelectorAll('[name="expenses_end_location"]').forEach(r => {
                        r.checked = false;
                    });
                }, 0);


                $('#is_foreign').val(0).trigger('change');
                Swal.fire({
                    icon: 'info',
                    title: 'สร้างฟอร์มใหม่',
                    text: 'กรุณากรอกข้อมูลและกดบันทึกเพื่อบันทึกฟอร์มนี้',
                });
            } catch (error) {
                console.error('ไม่สามารถสร้างฟอร์มใหม่ได้:', error);
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถสร้างฟอร์มใหม่ได้' });
            }
        }

        async function handleDeleteForm() {
            const current = getCurrentForm();
            console.log(current);
            if (!current) return;

            const formId = current.main_data?.id;
            if (!formId) {
                state.forms.splice(state.currentIndex, 1);
                if (!state.forms.length) {
                    Swal.fire({ icon: 'info', title: 'ไม่มีฟอร์มคงเหลือ', text: 'กำลังกลับไปหน้ารายการ' }).then(() => {
                        window.location.href = 'travel-expenses-money-list.php';
                    });
                    return;
                }
                if (state.currentIndex >= state.forms.length) {
                    state.currentIndex = state.forms.length - 1;
                }
                const next = getCurrentForm();
                state.currentId = next?.main_data?.id ? Number(next.main_data.id) : null;
                setupFormTabs();
                renderCurrentForm();
                updateActionButtons();
                window.formsSummaryManager?.update();
                Swal.fire({ icon: 'success', title: 'ลบฟอร์มสำเร็จ', text: 'ฟอร์มที่ยังไม่บันทึกถูกลบแล้ว' });
                return;
            }

            const confirmDelete = await Swal.fire({
                title: 'ยืนยันการลบ',
                text: 'ต้องการลบฟอร์มที่ ' + current.form_number + ' นี้หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบฟอร์ม',
                cancelButtonText: 'ยกเลิก'
            });

            if (!confirmDelete.isConfirmed) {
                return;
            }

            try {
                window.showLoading();
                const response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=delete_status&id=${formId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error(result.message || 'ลบข้อมูลไม่สำเร็จ');
                }

                state.forms.splice(state.currentIndex, 1);
                if (!state.forms.length) {
                    Swal.fire({ icon: 'success', title: 'ลบฟอร์มสำเร็จ', text: 'ไม่มีฟอร์มคงเหลือ' }).then(() => {
                        window.location.href = 'travel-expenses-money-list.php';
                    });
                    return;
                }
                if (state.currentIndex >= state.forms.length) {
                    state.currentIndex = state.forms.length - 1;
                }
                const next = getCurrentForm();
                state.currentId = next?.main_data?.id ? Number(next.main_data.id) : null;
                setupFormTabs();
                renderCurrentForm();
                updateActionButtons();
                Swal.fire({ icon: 'success', title: 'ลบฟอร์มสำเร็จ', text: 'ฟอร์มถูกลบแล้ว' });
            } catch (error) {
                console.error('ลบฟอร์มไม่สำเร็จ:', error);
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: error.message || 'ไม่สามารถลบฟอร์มได้' });
            } finally {
                window.hideLoading();
            }
        }

        document.getElementById('add-single-form')?.addEventListener('click', () => handleAddForm('1'));
        document.getElementById('add-group-form')?.addEventListener('click', () => handleAddForm('2'));
        document.getElementById('delete-form')?.addEventListener('click', handleDeleteForm);

        async function updateData() {
            try {
                // Disable submit button
                const btnSubmit = document.getElementById('btn-update');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                }

                if (!$('#plan_id').val()) {
                    // ตรวจสอบเงินทุน
                    if (!validateFund()) return;

                    // ตรวจสอบโครงการ
                    if (!validateProject()) return;
                }

                // ตรวจสอบการเรียนถึง
                if (!validateExpensesLearn()) return;

                // ตรวจสอบวันที่เริ่ม-สิ้นสุด
                if (!validateDateFields()) return;

                // ตรวจสอบการนับวัน (ถ้ามี)
                if (!validateTravelExpensesForm()) return;

                // ตรวจสอบว่ามีการใช้รถส่วนตัวหรือไม่
                if (!validateVehicleInfo()) return;

                // ตรวจสอบ list_data ต้องมีอย่างน้อย 1 แถว เฉพาะกรณีที่เลือกประเภทรถ
                const car = document.getElementById('expenses_vehicle');
                const motorcycle = document.getElementById('expenses_motorcycle');
                if ((car && car.checked) || (motorcycle && motorcycle.checked)) {
                    const listData = getListDetailsData();
                    if (!listData.length) {
                        Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกข้อมูลรายละเอียดการเดินทางอย่างน้อย 1 รายการ' });
                        return;
                    }
                    // ตรวจสอบข้อมูลในแต่ละแถว (ตัวอย่าง: ต้องมีวันที่และค่าชดเชย)
                    for (const [i, row] of listData.entries()) {
                        if (!row.date) {
                            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: `กรุณาระบุวันที่ในแถวที่ ${i + 1} ของรายละเอียดการเดินทาง` });
                            return;
                        }
                        // เพิ่ม validate field อื่นๆ ตามต้องการ
                    }
                }

                // ตรวจสอบการติ๊กยืนยัน
                if (!validateConfirmCheckbox()) return;


                const currentForm = getCurrentForm();
                const mainFormData = getMainFormData();
                const groupData = getGroupPeopleData();
                const listData = getListDetailsData();
                const isNewForm = !currentForm?.main_data?.id;

                const payloadMain = {
                    ...(currentForm?.main_data || {}),
                    ...mainFormData,
                };

                payloadMain.expenses_group = String(
                    payloadMain.expenses_group ??
                    currentForm?.main_data?.expenses_group ??
                    ''
                );

                const selectedVehicle = getSelectedVehicleValue();
                payloadMain.expenses_vehicle = selectedVehicle;
                payloadMain.expenses_motorcycle =
                    selectedVehicle === 'motorcycle' ? 'motorcycle' : '';

                if (!payloadMain.expenses_number) {
                    payloadMain.expenses_number =
                        currentForm?.main_data?.expenses_number ||
                        state.expensesNumber ||
                        '';
                }

                const parentId =
                    currentForm?.main_data?.parent_id ??
                    state.rootId ??
                    currentForm?.main_data?.id ??
                    null;

                if (parentId) {
                    payloadMain.parent_id = parentId;
                }

                if (payloadMain.expenses_group === '2') {
                    payloadMain.expenses_group_count = groupData.length;
                } else if (payloadMain.expenses_group === '1') {
                    payloadMain.expenses_group_count = 1;
                }

                const data_sent = {
                    main_data: payloadMain,
                    group_data: groupData,
                    list_data: listData
                };

                window.showLoading();

                try {
                    let response;
                    if (isNewForm) {
                        response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=create_plan_multi`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify(data_sent)
                        });
                    } else {
                        const targetId = state.currentId || expensesId;
                        response = await fetch(`../controllers/withdraws/wrd_expenses_controller.php?action=edit_one_multi&id=${targetId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify(data_sent)
                        });
                    }

                    const result = await response.json();

                    if (result.status === 'success') {
                        if (isNewForm && currentForm) {
                            const createdId = result.data?.id;
                            if (createdId) {
                                currentForm.main_data.id = createdId;
                                state.currentId = createdId;
                            }
                            if (result.data?.parent_id) {
                                currentForm.main_data.parent_id = result.data.parent_id;
                            }
                            const newNumber = result.expenses_number || result.data?.expenses_number;
                            if (newNumber) {
                                state.expensesNumber = newNumber;
                                currentForm.main_data.expenses_number = newNumber;
                            }
                            currentForm.isNew = false;
                        }

                        // Swal.fire({
                        //     title: "บันทึกข้อมูลสำเร็จ",
                        //     text: isNewForm ? "สร้างฟอร์มใหม่เรียบร้อย" : "รายการถูกแก้ไขแล้ว",
                        //     icon: "success"
                        // }).then(() => {
                        //     window.location.href = 'travel-expenses-money-list.php';
                        // });
                        Swal.fire({
                            title: "บันทึกข้อมูลสำเร็จ",
                            text: isNewForm ? "สร้างฟอร์มใหม่เรียบร้อย" : "รายการถูกแก้ไขแล้ว",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                        updateFormHeader();
                        updateActionButtons();
                        setupFormTabs();
                        window.formsSummaryManager?.update();
                    } else {
                        throw new Error(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                    }
                } catch (error) {
                    throw error;
                }

            } catch (error) {
                console.error('เกิดข้อผิดพลาด:', error);
                Swal.fire({
                    title: "เกิดข้อผิดพลาด",
                    text: error.message || "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ",
                    icon: "error"
                });
            } finally {
                // Enable submit button
                const btnSubmit = document.getElementById('btn-update');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                }
                window.hideLoading();
            }
        }

    } catch (err) {
        console.error('เกิดข้อผิดพลาด:', err);
        alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
    }


    function validateDateFields() {
        const start = document.querySelector('[name="expenses_start"]')?.value;
        const end = document.querySelector('[name="expenses_end"]')?.value;

        console.log('Validating dates:', { start, end });
        if (!start || !end) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันที่เริ่มต้นและสิ้นสุด' });
            return false;
        }
        if (new Date(start) > new Date(end)) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด' });
            return false;
        }
        return true;
    }

    function validateFund() {
        const fund = document.getElementById('fund_select').value.trim();
        if (!fund) {
            Swal.fire({
                title: "ข้อมูลไม่ครบถ้วน",
                text: "กรุณาเลือกเงินทุน",
                icon: "warning"
            });
            return false;
        }
        return fund;
    }

    function validateProject() {
        const project = document.getElementById('project_select').value.trim();
        if (!project) {
            Swal.fire({
                title: "ข้อมูลไม่ครบถ้วน",
                text: "กรุณาเลือกโครงการ",
                icon: "warning"
            });
            return false;
        }
        return project;
    }

    function validateExpensesLearn() {
        const expensesLearn = document.querySelector('[name="expenses_learn"]')?.value;
        if (!expensesLearn) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกเรียนถึง' });
            return false;
        }
        return true;
    }

    function validateTravelExpensesForm() {
        // วันและเวลาเริ่มต้น
        const expensesStart = document.querySelector('[name="expenses_start"]')?.value;
        if (!expensesStart) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาเริ่มต้น' });
            return false;
        }

        // วันและเวลาสิ้นสุด
        const expensesEnd = document.querySelector('[name="expenses_end"]')?.value;
        if (!expensesEnd) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันและเวลาสิ้นสุด' });
            return false;
        }

        // ตรวจสอบว่าเริ่มต้นต้องไม่มากกว่าสิ้นสุด
        if (new Date(expensesStart) > new Date(expensesEnd)) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'วันและเวลาเริ่มต้นต้องไม่มากกว่าวันและเวลาสิ้นสุด' });
            return false;
        }

        // คำสั่ง/บันทึก
        const expensesCode = document.getElementById('expenses_code')?.value;
        if (!expensesCode) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกคำสั่ง/บันทึก' });
            return false;
        }

        // ลงวันที่
        const expensesDate = document.querySelector('[name="expenses_date"]')?.value;
        if (!expensesDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาระบุวันที่ (ลงวันที่)' });
            return false;
        }

        // ค่าใช้จ่ายสำหรับ (radio)
        const expensesGroup = document.querySelector('input[name="expenses_group"]:checked');
        if (!expensesGroup) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกค่าใช้จ่ายสำหรับ (ข้าพเจ้า/ข้าพเจ้าพร้อมคณะเดินทาง)' });
            return false;
        }

        // สถานที่เริ่มต้น (radio)
        const expensesStartLocation = document.querySelector('input[name="expenses_start_location"]:checked');
        if (!expensesStartLocation) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกสถานที่เริ่มต้น' });
            return false;
        }

        // วันที่และเวลาเดินทาง (เริ่มต้น)
        const expensesStartDate = document.querySelector('[name="expenses_start_date"]')?.value;
        if (!expensesStartDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกวันที่และเวลาเดินทาง (เริ่มต้น)' });
            return false;
        }

        // สถานที่กลับ (radio)
        const expensesEndLocation = document.querySelector('input[name="expenses_end_location"]:checked');
        if (!expensesEndLocation) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณาเลือกสถานที่กลับ' });
            return false;
        }

        // วันที่และเวลาเดินทาง (กลับ)
        const expensesEndDate = document.querySelector('[name="expenses_end_date"]')?.value;
        if (!expensesEndDate) {
            Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกวันที่และเวลาเดินทาง (กลับ)' });
            return false;
        }

        // หมายเลขทะเบียน (ถ้าเลือกใช้ยานพาหนะ)
        const carChecked = document.getElementById('expenses_vehicle')?.checked;
        const motorcycleChecked = document.getElementById('expenses_motorcycle')?.checked;
        if (carChecked || motorcycleChecked) {
            const vehicleNumber = document.getElementById('expenses_vehicle_number')?.value;
            if (!vehicleNumber) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกหมายเลขทะเบียนยานพาหนะ' });
                return false;
            }
        }

        // ตรวจสอบตาราง group-people-list ถ้าเลือก "คนเดียว"
        if (expensesGroup && expensesGroup.value === "1") {
            const inputEl = document.getElementById('expenses_allowance_days');
            const expenses_allowance_days = inputEl.value;
            if (expenses_allowance_days) {
                const val = parseFloat(expenses_allowance_days);
                const decimal = (val % 1).toFixed(1);
                if (decimal !== '0.0' && decimal !== '0.5') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากรอกจำนวน (วัน) ให้ถูกต้อง'
                    }).then(() => {
                        inputEl.focus();
                    });
                    return false;
                }
            }
        }

        if (expensesGroup && expensesGroup.value === "2") {
            const groupRows = document.querySelectorAll('tr.group-people-list');
            if (!groupRows.length) {
                Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกข้อมูลคณะเดินทางอย่างน้อย 1 รายการ' });
                return false;
            }
            for (const row of groupRows) {
                const inputEl = row.querySelector('.group_days');
                const value = inputEl?.value;

                // ถ้าไม่กรอก → ข้าม
                if (!value) continue;

                const val = parseFloat(value);
                const decimal = (val % 1).toFixed(1);

                if (decimal !== '0.0' && decimal !== '0.5') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากรอกจำนวน (วัน) ให้ถูกต้อง'
                    }).then(() => {
                        inputEl.focus();
                    });
                    return false;
                }
            }
        }

        // ตรวจสอบรายละเอียดประกอบการเบิก (list-details)
        // const listRows = document.querySelectorAll('tr.list-details');
        // if (!listRows.length) {
        //     Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบถ้วน', text: 'กรุณากรอกรายละเอียดประกอบการเบิกอย่างน้อย 1 รายการ' });
        //     return false;
        // }

        // สามารถเพิ่ม validate อื่นๆ ตามต้องการ

        return true;
    }
    function validateVehicleInfo() {
        // ตรวจสอบว่ามีการเลือก radio ยานพาหนะหรือไม่
        const vehicle = document.querySelector('input[name="expenses_vehicle"]:checked');
        if (vehicle) {
            // ถ้าเลือกแล้ว ต้องกรอกหมายเลขทะเบียน
            const vehicleNumber = document.getElementById('expenses_vehicle_number')?.value.trim();
            if (!vehicleNumber) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกหมายเลขทะเบียนยานพาหนะส่วนตัว'
                });
                return false;
            }
        }
        return true;
    }

    function validateConfirmCheckbox() {
        const confirm = document.getElementById('expenses_confirm');
        if (!confirm || !confirm.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณายืนยันว่ารายการที่กล่าวมาข้างต้นเป็นความจริง'
            });
            return false;
        }
        return true;
    }
    flatpickr(".date-th-datetime", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        altInput: true,
        altFormat: "d-m-Y H:i",
        time_24hr: true,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                longhand: [
                    'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ',
                    'พฤหัสบดี', 'ศุกร์', 'เสาร์'
                ],
            },
            months: {
                shorthand: [
                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ],
                longhand: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
            },
        },
        formatDate: (date, format, locale) => {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const yearBE = date.getFullYear() + 543;
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}-${month}-${yearBE} ${hours}:${minutes}`;
        },
        parseDate: (datestr, format) => {
            const [d, m, y, h = "00", i = "00"] = datestr
                .replace(/[:\s]/g, "-")
                .split("-");
            return new Date(+y - 543, +m - 1, +d, +h, +i);
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
        }
    });
    flatpickr("#date_notime", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d-m-Y",
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                longhand: [
                    'อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ',
                    'พฤหัสบดี', 'ศุกร์', 'เสาร์'
                ],
            },
            months: {
                shorthand: [
                    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ],
                longhand: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ],
            },
        },
        formatDate: (date, format, locale) => {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const yearBE = date.getFullYear() + 543;
            return `${day}-${month}-${yearBE}`;
        },
        parseDate: (datestr, format) => {
            const [d, m, y] = datestr
                .replace(/[:\s]/g, "-")
                .split("-");
            return new Date(+y - 543, +m - 1, +d,);
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
        }
    });

})();

// ฟังก์ชั่นสำหรับ format วันที่ วัน/เดือน/ปี เวลา
function formatThaiDatetime(datetimeStr) {
    if (!datetimeStr) return '';
    const dateObj = new Date(datetimeStr);
    if (isNaN(dateObj)) return '';
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const year = dateObj.getFullYear() + 543;
    const hours = String(dateObj.getHours()).padStart(2, '0');
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}
function formatThaiDate(datetimeStr) {
    if (!datetimeStr) return '';
    const dateObj = new Date(datetimeStr);
    if (isNaN(dateObj)) return '';
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const year = dateObj.getFullYear() + 543;
    return `${day}-${month}-${year}`;
}

function setupFormTabs() {
    const tabsContainer = document.getElementById('formTabs');
    if (!tabsContainer) return;
    tabsContainer.innerHTML = '';
    refreshFormNumbers();
    if (state.forms.length <= 1) {
        tabsContainer.classList.add('d-none');
        return;
    }
    tabsContainer.classList.remove('d-none');
    state.forms.forEach((form, idx) => {
        const li = document.createElement('li');
        li.classList.add('nav-item');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.classList.add('nav-link');
        btn.textContent = getFormLabel(form, idx);
        btn.addEventListener('click', () => {
            if (idx === state.currentIndex) return;
            persistCurrentForm();
            state.currentIndex = idx;
            const next = getCurrentForm();
            state.currentId = next?.main_data?.id ? Number(next.main_data.id) : null;
            renderCurrentForm();
            updateActiveTab();
            updateActionButtons();
            refreshUserSelectDisabledState();
        });
        li.appendChild(btn);
        tabsContainer.appendChild(li);
    });
    updateActiveTab();
    refreshUserSelectDisabledState();
}
function refreshUserSelectDisabledState() {
    const forms = document.querySelectorAll('form#advanceFrm');
    forms.forEach((form, idx) => {
        const select = form.querySelector('.user-select');
        if (!select) return;
        if (idx === state.currentIndex && idx === 0) {
            select.disabled = true;
        } else {
            select.disabled = false;
        }
    });
}
