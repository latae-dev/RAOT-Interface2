<?php

/**
 * ค่าตั้งต้นสำหรับ Dashboard (อ่านจาก environment variable ได้)
 */
return [
    'online_timeout_minutes' => max(1, (int) (getenv('DASHBOARD_ONLINE_TIMEOUT_MINUTES') ?: 15)),
    'visit_count_interval_minutes' => max(1, (int) (getenv('DASHBOARD_VISIT_INTERVAL_MINUTES') ?: 30)),
    'admin_role_codes' => array_values(array_filter(array_map(
        'trim',
        explode(',', getenv('DASHBOARD_ADMIN_ROLE_CODES') ?: 'ADMIN')
    ))),
];
