(function () {
    'use strict';

    let dataTableInstance = null;
    let isLoading = false;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function showNoData() {
        destroyDataTableIfExists();

        const tbody = document.getElementById('annual-budget-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">ไม่มีข้อมูล</td></tr>';
        }
    }

    function renderRows(items) {
        const tbody = document.getElementById('annual-budget-body');
        if (!tbody) {
            return;
        }

        tbody.innerHTML = items.map((item) => `
            <tr>
                <td>${escapeHtml(item.fiscal_year)}</td>
                <td>${escapeHtml(item.funds_center)}</td>
                <td>${escapeHtml(item.fund)}</td>
                <td>${escapeHtml(item.functional_area)}</td>
                <td>${escapeHtml(item.commitment_item)}</td>
                <td class="text-end">${escapeHtml(item.budget_formatted)}</td>
                <td class="text-end">${escapeHtml(item.balance_formatted)}</td>
            </tr>
        `).join('');
    }

    function destroyDataTableIfExists() {
        if (typeof $.fn.DataTable === 'undefined') {
            return;
        }

        const $table = $('#annual-budget-table');
        if (!$table.length) {
            return;
        }

        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }

        dataTableInstance = null;
        $table.removeClass('dataTable no-footer');
    }

    function initDataTable() {
        if (typeof $.fn.DataTable === 'undefined') {
            return;
        }

        const $table = $('#annual-budget-table');
        if (!$table.length) {
            return;
        }

        destroyDataTableIfExists();

        dataTableInstance = $table.DataTable({
            language: {
                searchPlaceholder: 'ค้นหา...',
                sSearch: '',
                emptyTable: 'ไม่มีข้อมูล',
            },
            pageLength: 10,
            order: [[0, 'asc']],
        });
    }

    function formatSapDateDisplay(value) {
        if (!value || String(value).length !== 8) {
            return '';
        }

        const raw = String(value);
        const year = raw.slice(0, 4);
        const month = raw.slice(4, 6);
        const day = raw.slice(6, 8);

        return day + '/' + month + '/' + year;
    }

    function updateYearLabel(year, sapParams) {
        const label = document.getElementById('annual-budget-year-label');
        if (!label || !year) {
            return;
        }

        let text = 'ปี ' + (Number(year) + 543) + ' (' + year + ')';

        if (sapParams && sapParams.str_date && sapParams.end_date) {
            text += ' | ' + formatSapDateDisplay(sapParams.str_date) + ' - ' + formatSapDateDisplay(sapParams.end_date);
        }

        label.textContent = text;
    }

    function getSelectedFiscalYear() {
        const select = document.getElementById('annual-budget-year-select');
        if (!select) {
            return null;
        }

        const year = parseInt(select.value, 10);
        return Number.isFinite(year) ? year : null;
    }

    function showError(message) {
        destroyDataTableIfExists();

        const tbody = document.getElementById('annual-budget-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(message) + '</td></tr>';
        }
    }

    async function loadAnnualBudgetReport() {
        if (isLoading) {
            return;
        }

        isLoading = true;

        const selectedYear = getSelectedFiscalYear();
        const requestBody = selectedYear ? { year: selectedYear } : {};

        try {
            const response = await fetch('../controllers/dashboard/dashboard_controller.php?action=annual_budget', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestBody),
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                await response.text();
                showError('ระบบส่งข้อมูลไม่ถูกต้อง (ไม่ใช่ JSON)');
                return;
            }

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                showError(result.message || 'ไม่สามารถโหลดรายงานงบประมาณได้');
                return;
            }

            updateYearLabel(result.year, result.sap_params);

            const items = Array.isArray(result.data) ? result.data : [];
            if (items.length === 0) {
                showNoData();
                return;
            }

            destroyDataTableIfExists();
            renderRows(items);
            initDataTable();
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดรายงานงบประมาณ');
        } finally {
            isLoading = false;
        }
    }

    function bootAnnualBudgetReport() {
        if (window.__annualBudgetBooted) {
            return;
        }
        window.__annualBudgetBooted = true;

        const yearSelect = document.getElementById('annual-budget-year-select');
        if (yearSelect) {
            yearSelect.addEventListener('change', loadAnnualBudgetReport);
        }

        loadAnnualBudgetReport();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootAnnualBudgetReport);
    } else {
        bootAnnualBudgetReport();
    }
})();
