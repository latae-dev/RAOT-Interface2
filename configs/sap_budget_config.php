<?php

require_once __DIR__ . '/../helper/env_loader.php';

loadEnvFile(dirname(__DIR__) . '/.env');

return [
    'url' => getenv('SAP_BUDGET_URL') ?: 'https://saps4hanadev.raot.co.th:8443/sap/bc/zremaining?sap-client=700',
    'http_method' => strtoupper(getenv('SAP_BUDGET_HTTP_METHOD') ?: 'POST'),
    'user' => getenv('SAP_BUDGET_USER') ?: '',
    'pass' => getenv('SAP_BUDGET_PASS') ?: '',
    'rfikrs' => getenv('SAP_BUDGET_FMIKRS') ?: '1000',
    'timeout' => max(5, (int) (getenv('SAP_BUDGET_TIMEOUT') ?: 15)),
    'cache_ttl' => max(0, (int) (getenv('SAP_BUDGET_CACHE_TTL') ?: 60)),
    'verify_ssl' => filter_var(getenv('SAP_BUDGET_VERIFY_SSL') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'debug' => filter_var(getenv('SAP_BUDGET_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
];
