<?php

class AuthenModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function loginUser($user_code, $user_pass)
    {
        if (!($this->conn instanceof \PgSql\Connection)) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }

        $query = "SELECT * FROM users.tb_users WHERE user_code = $1";
        $result = pg_query_params($this->conn, $query, [$user_code]);

        if ($row = pg_fetch_assoc($result)) {
            if ($row['user_pass'] === $user_pass) {
                return $row;
            } else {
                return ["status" => "error", "message" => "Invalid Password"];
            }
        } else {
            return ["status" => "error", "message" => "User not found"];
        }
    }

}
