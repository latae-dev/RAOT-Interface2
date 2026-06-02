<?php

/**
 * โหลดค่าจากไฟล์ .env เข้า environment (เฉพาะ key ที่ยังไม่ถูกตั้งค่า)
 */
function loadEnvFile(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        $value = trim($value, " \t\"'");

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}
