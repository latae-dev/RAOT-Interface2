<?php

class PositionUserModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readPositionList($userId)
    {
        if ($userId === null || $userId === '') {
            return [];
        }
        try {
            $query = "SELECT p.* FROM users.tb_positions_users pu
                join users.tb_positions p on p.id = pu.position_id
                where user_id = $1 ORDER BY position_id DESC";
            $result = pg_query_params($this->conn, $query, [$userId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                
                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }
}
