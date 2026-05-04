<?php
#- Root Project
define('ROOT_PATH', realpath(__DIR__ . '/..'));

#- Log Path
define('LOG_PATH', ROOT_PATH . '/logs/' . date('Y-m-d') . '.log');

#- Error Reporting
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH);
error_reporting(E_ALL);
ini_set('display_errors', 1);

#- Base URL
define(
    'BASE_URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST']
    . preg_replace('#/(pages|controllers)(/.*)?$#', '', $_SERVER['SCRIPT_NAME'])
);

#- Include config และ helper
include_once ROOT_PATH . '/configs/constants.php';
include_once ROOT_PATH . '/configs/database.php';
include_once ROOT_PATH . '/helper/common.php';

?>
