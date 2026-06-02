(function () {
    'use strict';

    function formatUserCount(count) {
        return Number(count || 0).toLocaleString('th-TH') + ' User';
    }

    function updateStatElement(id, count) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = formatUserCount(count);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function getUserInitials(user) {
        const fname = (user.user_fname || '').trim();
        const lname = (user.user_lname || '').trim();

        if (fname && lname) {
            return (fname.charAt(0) + lname.charAt(0)).toUpperCase();
        }

        return (user.user_code || 'U').substring(0, 2).toUpperCase();
    }

    function getUserDisplayName(user) {
        const fullName = `${user.user_fname || ''} ${user.user_lname || ''}`.trim();
        return fullName || user.user_code || '-';
    }

    function renderTopUsers(users) {
        const tbody = document.getElementById('dashboard-top-users-body');
        if (!tbody) {
            return;
        }

        if (!Array.isArray(users) || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4">ยังไม่มีข้อมูลการเข้าใช้งาน</td></tr>';
            return;
        }

        tbody.innerHTML = users.map((user, index) => {
            const isLastRow = index === users.length - 1;
            const rowClass = isLastRow ? 'border-bottom-0' : '';
            const email = user.user_email || user.user_code || '-';

            return `
                <tr>
                    <th scope="row" class="${rowClass}">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <span class="avatar avatar-rounded bg-primary-transparent">
                                    <span class="fw-semibold text-primary">${escapeHtml(getUserInitials(user))}</span>
                                </span>
                            </div>
                            <div>
                                <span class="d-block fw-semibold">${escapeHtml(getUserDisplayName(user))}</span>
                                <span class="d-block fs-12 text-muted">${escapeHtml(email)}</span>
                            </div>
                        </div>
                    </th>
                    <td class="text-center fw-semibold ${rowClass}">${Number(user.access_count || 0).toLocaleString('th-TH')}</td>
                </tr>
            `;
        }).join('');
    }

    function updatePayoutsChart(chartData) {
        if (typeof window.updateDashboardPayoutsChart === 'function') {
            window.updateDashboardPayoutsChart(chartData);
        }
    }

    async function refreshDashboardOverview() {
        try {
            const response = await fetch('../controllers/dashboard/dashboard_controller.php?action=overview', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                console.error('refreshDashboardOverview: response is not JSON');
                return;
            }

            const result = await response.json();
            if (result.status !== 'success' || !result.data) {
                return;
            }

            const stats = result.data.user_stats || {};
            updateStatElement('dashboard-online-users', stats.online_users);
            updateStatElement('dashboard-total-users', stats.total_users);
            updateStatElement('dashboard-admin-users', stats.admin_users);
            renderTopUsers(result.data.top_users || []);
            updatePayoutsChart(result.data.monthly_documents || window.__DASHBOARD_CHART__);
        } catch (error) {
            console.error('refreshDashboardOverview:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshDashboardOverview();
        setInterval(refreshDashboardOverview, 60000);
    });
})();
