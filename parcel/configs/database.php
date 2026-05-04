<?php
class Database
{
    private $db_configs = [
        // 'raot_db_dev' => [
        //     'host' => '10.99.20.89',
        //     'db_name' => 'raot_db_dev',
        //     'username' => 'postgres',
        //     'password' => 'Raot@1234'
        // ],
        'raot_db_dev' => [
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
            $this->conn = pg_connect("host={$config['host']} dbname={$config['db_name']} user={$config['username']} password={$config['password']}");
        } catch (Exception $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
