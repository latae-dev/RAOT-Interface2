(function () {
    const config = window.BUDGET_CHECK_TESTER || {};
    const btnRun = document.getElementById('btn-run-test');
    const btnSimulate = document.getElementById('btn-simulate-submit');
    const customAmountInput = document.getElementById('custom-amount');

    if (!btnRun) return;

    btnRun.addEventListener('click', runFullTest);
    if (btnSimulate) {
        btnSimulate.addEventListener('click', () => simulateFormSubmitFlow(parseFloat(customAmountInput.value)));
    }

    // --- Modal ชุดเดียวกับ request-proposal-form.js ---

    function formatCurrency(amount) {
        const value = parseFloat(amount) || 0;
        return value.toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    async function closeSwal() {
        if (typeof Swal === 'undefined') {
            return;
        }
        if (Swal.isVisible()) {
            await Swal.close();
        }
    }

    async function showErrorMessage(title, message) {
        const errorTitle = message ? title : 'เกิดข้อผิดพลาด';
        const errorText = message || title;

        if (typeof Swal !== 'undefined') {
            await closeSwal();
            await Swal.fire({
                icon: 'error',
                title: errorTitle,
                text: errorText,
                confirmButtonText: 'ตกลง',
            });
        } else {
            alert(errorTitle + (errorText ? '\n' + errorText : ''));
        }
    }

    async function showLoadingMessage(message) {
        if (typeof Swal === 'undefined') {
            return;
        }

        await closeSwal();
        Swal.fire({
            title: 'กำลังประมวลผล',
            text: message,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCancelButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    }

    async function showBudgetConfirmModal(budgetData) {
        await closeSwal();
        const balance = formatCurrency(budgetData.balance);
        const totalAmount = formatCurrency(budgetData.total_amount);
        const remaining = formatCurrency(budgetData.remaining_after_deduct);

        const html = `
            <div class="text-start">
                <p class="mb-2">งบคงเหลือปัจจุบัน: <strong>${balance}</strong> บาท</p>
                <p class="mb-2">ยอดใบขอเสนอ: <strong>${totalAmount}</strong> บาท</p>
                <p class="mb-0">งบหักลบ: <strong>${remaining}</strong> บาท</p>
            </div>
        `;

        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm('งบเพียงพอ ต้องการขออนุมัติหรือไม่?'));
        }

        const result = await Swal.fire({
            icon: 'info',
            title: 'ตรวจสอบงบประมาณ',
            html: html,
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'ยืนยันขออนุมัติ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            allowOutsideClick: false,
        });

        return result.isConfirmed;
    }

    async function showInsufficientBudgetModal(budgetData) {
        await closeSwal();
        const balance = formatCurrency(budgetData.balance);
        const totalAmount = formatCurrency(budgetData.total_amount);
        const overAmount = formatCurrency(
            budgetData.over_amount || budgetData.total_amount - budgetData.balance
        );

        const html = `
            <div class="text-start">
                <p class="mb-2">งบคงเหลือไม่เพียงพอสำหรับการขออนุมัติ</p>
                <p class="mb-2">งบคงเหลือ: <strong>${balance}</strong> บาท</p>
                <p class="mb-2">ยอดใบขอเสนอ: <strong>${totalAmount}</strong> บาท</p>
                <p class="mb-0">เกินงบ: <strong>${overAmount}</strong> บาท</p>
            </div>
        `;

        if (typeof Swal === 'undefined') {
            alert('งบคงเหลือไม่เพียงพอ');
            return Promise.resolve();
        }

        await Swal.fire({
            icon: 'warning',
            title: 'ไม่สามารถขออนุมัติได้',
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'ตกลง',
            allowOutsideClick: false,
        });
    }

    async function showTesterSuccessAfterConfirm() {
        if (typeof Swal === 'undefined') {
            alert('จำลอง: ยืนยันแล้ว — ระบบจริงจะบันทึก PR ต่อ');
            return;
        }

        await closeSwal();
        await Swal.fire({
            icon: 'success',
            title: 'จำลอง: ยืนยันขออนุมัติแล้ว',
            text: 'ในระบบจริงจะบันทึกใบขอเสนอต่อ (หน้านี้ไม่บันทึกจริง)',
            confirmButtonText: 'ตกลง',
        });
    }

    // --- จำลอง flow saveProposal (เฉพาะส่วนเช็คงบ) ---

    function toBudgetData(row, balance) {
        const totalAmount = round2(row.total_amount);
        const bal = round2(balance);
        const isSufficient = row.check_status === 'sufficient';

        return {
            balance: bal,
            total_amount: totalAmount,
            remaining_after_deduct: isSufficient ? round2(bal - totalAmount) : round2(bal - totalAmount),
            is_sufficient: isSufficient,
            over_amount: isSufficient ? 0 : round2(totalAmount - bal),
            budget: row.budget,
            actual: row.actual,
        };
    }

    async function simulateFormSubmitFlow(amount) {
        if (Number.isNaN(amount)) {
            showErrorMessage('กรุณากรอกข้อมูล', 'กรุณาระบุยอดเงินเป็นตัวเลข');
            return;
        }

        const needFetch = !config.lastResult || config.lastResult.status !== 'success';

        try {
            let result = config.lastResult;

            if (needFetch) {
                await showLoadingMessage('กำลังตรวจสอบงบประมาณ...');
                result = await callApi(collectSapParams(), []);
                config.lastResult = result;
                await closeSwal();
            } else {
                await closeSwal();
            }

            if (result.status !== 'success') {
                await showErrorMessage('ไม่สามารถตรวจสอบงบประมาณได้', result.message || 'เชื่อมต่อ SAP ไม่สำเร็จ');
                return;
            }

            const row = evaluateOne(amount, result.balance, result.sap_response?.data?.[0]);

            if (row.check_status === 'validation_error') {
                await showErrorMessage('ไม่สามารถตรวจสอบงบประมาณได้', row.message);
                return;
            }

            const budgetData = toBudgetData(row, result.balance);

            if (!budgetData.is_sufficient) {
                await showInsufficientBudgetModal(budgetData);
                return;
            }

            const confirmed = await showBudgetConfirmModal(budgetData);
            if (confirmed) {
                showTesterSuccessAfterConfirm();
            }
        } catch (e) {
            await closeSwal();
            await showErrorMessage('เกิดข้อผิดพลาด', e.message || 'ไม่ทราบสาเหตุ');
        }
    }

    async function showModalForScenario(row) {
        await closeSwal();

        if (!config.lastResult || config.lastResult.status !== 'success') {
            await showErrorMessage('กรุณากดดึงงบจาก SAP ก่อน');
            return;
        }

        if (row.check_status === 'validation_error') {
            await showErrorMessage('ไม่สามารถตรวจสอบงบประมาณได้', row.message);
            return;
        }

        const budgetData = toBudgetData(row, config.lastResult.balance);

        if (budgetData.is_sufficient) {
            const confirmed = await showBudgetConfirmModal(budgetData);
            if (confirmed) {
                showTesterSuccessAfterConfirm();
            }
        } else {
            await showInsufficientBudgetModal(budgetData);
        }
    }

    // --- UI helpers ---

    function formatMoney(value) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '-';
        }
        return formatCurrency(value) + ' บาท';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text ?? '');
        return div.innerHTML;
    }

    function dateToYmd(value) {
        const v = (value || '').trim();
        const dmy = v.match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if (dmy) return dmy[3] + dmy[2] + dmy[1];
        if (/^\d{8}$/.test(v)) return v;
        return v;
    }

    function collectSapParams() {
        return {
            gjahr: document.getElementById('f-gjahr').value.trim(),
            rfikrs: '1000',
            rfundsctr: document.getElementById('f-fundsctr').value.trim(),
            rcmmtitem: document.getElementById('f-cmmtitem').value.trim(),
            rfund: document.getElementById('f-fund').value.trim(),
            rfuncarea: document.getElementById('f-funcarea').value.trim(),
            str_date: dateToYmd(document.getElementById('f-str-date').value),
            end_date: dateToYmd(document.getElementById('f-end-date').value),
        };
    }

    function round2(n) {
        return Math.round(Number(n) * 100) / 100;
    }

    function evaluateOne(amount, balance, firstRow) {
        amount = round2(amount);
        balance = round2(balance);

        if (amount <= 0) {
            return {
                check_status: 'validation_error',
                is_sufficient: false,
                total_amount: amount,
                balance,
                message: 'ยอดรวมใบขอเสนอต้องมากกว่า 0',
            };
        }

        const sufficient = amount <= balance;

        return {
            check_status: sufficient ? 'sufficient' : 'insufficient',
            is_sufficient: sufficient,
            total_amount: amount,
            balance,
            remaining_after_deduct: round2(balance - amount),
            over_amount: sufficient ? 0 : round2(amount - balance),
            message: sufficient ? 'งบเพียงพอ' : 'งบคงเหลือไม่เพียงพอ',
            budget: firstRow?.budget,
            actual: firstRow?.actual,
        };
    }

    function hideAllResults() {
        ['block-balance', 'block-summary', 'block-cases', 'block-custom', 'block-error'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
    }

    function showError(msg) {
        hideAllResults();
        const el = document.getElementById('block-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function caseClass(status) {
        if (status === 'sufficient') return 'case-pass';
        if (status === 'insufficient') return 'case-fail';
        return 'case-warn';
    }

    function renderCaseCard(row, index) {
        const btnLabel = row.check_status === 'validation_error'
            ? 'ดู Modal Error'
            : 'ดู Modal ตามระบบ';

        return `
            <div class="case-item ${caseClass(row.check_status)}">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                    <div>
                        <div class="fw-bold">${escapeHtml(row.label)}</div>
                        <div class="mt-1">ยอดขอ: <strong>${formatMoney(row.total_amount)}</strong></div>
                        <div class="small text-muted">งบคงเหลือ: ${formatMoney(row.balance)}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-dark btn-show-modal" data-case-index="${index}">
                        ${btnLabel}
                    </button>
                </div>
            </div>
        `;
    }

    function bindCaseButtons(scenarios) {
        document.querySelectorAll('.btn-show-modal').forEach((btn) => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-case-index'), 10);
                if (!Number.isNaN(idx) && scenarios[idx]) {
                    showModalForScenario(scenarios[idx]);
                }
            });
        });
    }

    function renderFullResult(result) {
        config.lastResult = result;
        config.lastScenarios = result.scenarios || [];
        hideAllResults();

        if (result.status !== 'success') {
            showError(result.message || 'เช็คงบไม่สำเร็จ');
            void showErrorMessage('ไม่สามารถตรวจสอบงบประมาณได้', result.message);
            document.getElementById('raw-response').textContent = JSON.stringify(result, null, 2);
            return;
        }

        const balance = result.balance;
        const sapRow = (result.sap_response && result.sap_response.data && result.sap_response.data[0]) || {};

        document.getElementById('block-balance').classList.remove('d-none');
        document.getElementById('display-balance').textContent = formatMoney(balance);
        document.getElementById('display-sap-msg').textContent = 'SAP: ' + (result.message || 'สำเร็จ');
        document.getElementById('display-budget').textContent = formatMoney(result.budget);
        document.getElementById('display-actual').textContent = formatMoney(result.actual);
        document.getElementById('display-fund-name').textContent =
            (sapRow.rfund || '') + (sapRow.fund_name ? ' — ' + sapRow.fund_name : '') || '-';

        const scenarios = result.scenarios || [];
        let pass = 0, fail = 0, warn = 0;
        scenarios.forEach((s) => {
            if (s.check_status === 'sufficient') pass++;
            else if (s.check_status === 'insufficient') fail++;
            else warn++;
        });

        const summary = document.getElementById('block-summary');
        summary.classList.remove('d-none');
        summary.innerHTML = `
            <strong>สรุป:</strong> ผ่าน ${pass} · ไม่ผ่าน ${fail} · ผิดรูปแบบ ${warn}
            <br><small>กดปุ่ม「ดู Modal ตามระบบ」ในแต่ละแถว หรือใส่ยอดแล้วกด「จำลองกดขออนุมัติ」</small>
        `;

        document.getElementById('block-cases').classList.remove('d-none');
        document.getElementById('cases-list').innerHTML = scenarios
            .map((row, index) => renderCaseCard(row, index))
            .join('');
        bindCaseButtons(scenarios);

        document.getElementById('block-custom').classList.remove('d-none');
        if (customAmountInput && !customAmountInput.value) {
            customAmountInput.value = '50000';
        }

        document.getElementById('raw-response').textContent = JSON.stringify(result, null, 2);
    }

    async function callApi(sapParams, customAmounts) {
        const response = await fetch(config.apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ sap_params: sapParams, custom_amounts: customAmounts || [] }),
        });
        return response.json();
    }

    async function runFullTest() {
        btnRun.disabled = true;
        btnRun.textContent = 'กำลังเช็ค...';
        hideAllResults();

        try {
            await showLoadingMessage('กำลังตรวจสอบงบประมาณ...');
            const result = await callApi(collectSapParams(), []);
            await closeSwal();
            renderFullResult(result);
        } catch (e) {
            await closeSwal();
            showError(e.message || 'เกิดข้อผิดพลาด');
            void showErrorMessage('เกิดข้อผิดพลาด', e.message);
        } finally {
            btnRun.disabled = false;
            btnRun.textContent = 'ดึงงบจาก SAP และทดสอบทุกกรณี';
        }
    }
})();
