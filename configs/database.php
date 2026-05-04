<?php
require_once 'database-wrapper.php';

class Database
{
    private $db_configs = [
        'raot_db_qas' => [
            'host' => '10.99.20.89',
            'db_name' => 'raot_db_qas',
            'username' => 'postgres',
            'password' => 'Raot@1234'
        ],
    ];

    private $conn;

    // ฟังก์ชัน connect ที่รองรับการเลือกฐานข้อมูล
    public function connect($db_key)
    {
        $this->conn = null;

        if (!isset($this->db_configs[$db_key])) {
            echo 'Database configuration not found!';
            return null;
        }

        $config = $this->db_configs[$db_key];

        try {
            // เช็คว่ามี pg_connect หรือไม่
            if (function_exists('pg_connect')) {
                $this->conn = pg_connect("host={$config['host']} dbname={$config['db_name']} user={$config['username']} password={$config['password']}");
            } else {
                // ใช้ PDO แทน
                $dsn = "pgsql:host={$config['host']};dbname={$config['db_name']}";
                $this->conn = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            }
        } catch (Exception $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        // Return raw connection for compatibility with existing code
        return $this->conn;
    }
}
