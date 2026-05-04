<?php
require_once 'database-wrapper.php';

class Database
{
    // private $db_configs = [
    //     'raot_db_qas' => [
    //         'host' => '10.99.20.89',
    //         'db_name' => 'raot_db_qas',
    //         'username' => 'postgres',
    //         'password' => 'Raot@1234'
    //     ],
    // ];

    private $db_configs = [
        'raot_db_qas' => [
            'host' => '172.0.0.1',
            'db_name' => 'postgres',
            'username' => 'postgres',
            'password' => 'password'
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

        $envOverrides = [
            'host' => getenv('DB_HOST'),
            'db_name' => getenv('DB_NAME'),
            'username' => getenv('DB_USER') ?: getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
        ];
        foreach ($envOverrides as $key => $value) {
            if ($value !== false && $value !== '') {
                $config[$key] = $value;
            }
        }

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
