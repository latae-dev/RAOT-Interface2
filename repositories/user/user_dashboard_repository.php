<?php

class UserDashboardRepository
{
    private $conn;
    /** @var bool */
    private static $presenceTableReady = false;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function logPgError(string $context): void
    {
        if (!is_resource($this->conn) && !is_object($this->conn)) {
            return;
        }

        $error = function_exists('pg_last_error') ? pg_last_error($this->conn) : '';
        if ($error !== '' && $error !== false) {
            error_log($context . ': ' . $error);
        }
    }

    public function ensurePresenceTable(): void
    {
        if (self::$presenceTableReady) {
            return;
        }

        $query = "CREATE TABLE IF NOT EXISTS users.tb_user_presence (
            user_id INTEGER PRIMARY KEY,
            last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            access_count INTEGER NOT NULL DEFAULT 0
        )";
        $result = @pg_query($this->conn, $query);
        if ($result === false) {
            $this->logPgError('UserDashboardRepository::ensurePresenceTable create');
            return;
        }

        @pg_query(
            $this->conn,
            'ALTER TABLE users.tb_user_presence ADD COLUMN IF NOT EXISTS access_count INTEGER NOT NULL DEFAULT 0'
        );

        self::$presenceTableReady = true;
    }

    /**
     * อัปเดตสถานะออนไลน์ทุกครั้ง แต่เพิ่ม access_count เฉพาะเมื่อห่างจากครั้งก่อนเกิน visitIntervalMinutes
     */
    public function touchUserPresence(int $userId, int $visitIntervalMinutes = 30): bool
    {
        $this->ensurePresenceTable();

        $query = "INSERT INTO users.tb_user_presence (user_id, last_seen_at, access_count)
                  VALUES ($1, CURRENT_TIMESTAMP, 1)
                  ON CONFLICT (user_id)
                  DO UPDATE SET
                    last_seen_at = CURRENT_TIMESTAMP,
                    access_count = CASE
                        WHEN users.tb_user_presence.last_seen_at < (
                            CURRENT_TIMESTAMP - make_interval(mins => $2::integer)
                        )
                        THEN users.tb_user_presence.access_count + 1
                        ELSE users.tb_user_presence.access_count
                    END";
        $result = @pg_query_params($this->conn, $query, [$userId, $visitIntervalMinutes]);
        if ($result === false) {
            $this->logPgError('UserDashboardRepository::touchUserPresence');
        }

        return $result !== false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopFrequentUsers(int $limit = 5): array
    {
        $this->ensurePresenceTable();

        $query = "SELECT
                    u.id,
                    u.user_code,
                    u.user_fname,
                    u.user_lname,
                    u.user_email,
                    COALESCE(p.access_count, 0)::integer AS access_count
                  FROM users.tb_user_presence p
                  INNER JOIN users.tb_users u ON u.id = p.user_id
                  WHERE u.user_status = 'active'
                    AND COALESCE(p.access_count, 0) > 0
                  ORDER BY p.access_count DESC, p.last_seen_at DESC
                  LIMIT $1";
        $result = @pg_query_params($this->conn, $query, [$limit]);

        if ($result === false) {
            $this->logPgError('UserDashboardRepository::getTopFrequentUsers');
            return [];
        }

        $users = [];
        while ($row = pg_fetch_assoc($result)) {
            $users[] = $row;
        }

        return $users;
    }

    public function countOnlineUsers(int $timeoutMinutes): int
    {
        $this->ensurePresenceTable();

        $query = "SELECT COUNT(*)::integer AS total
                  FROM users.tb_user_presence
                  WHERE last_seen_at >= (CURRENT_TIMESTAMP - make_interval(mins => $1::integer))";
        $result = @pg_query_params($this->conn, $query, [$timeoutMinutes]);

        if ($result === false) {
            $this->logPgError('UserDashboardRepository::countOnlineUsers');
            return 0;
        }

        $row = pg_fetch_assoc($result);

        return (int) ($row['total'] ?? 0);
    }

    public function countActiveUsers(): int
    {
        $query = "SELECT COUNT(*)::integer AS total
                  FROM users.tb_users
                  WHERE user_status = 'active'";
        $result = @pg_query($this->conn, $query);

        if ($result === false) {
            $this->logPgError('UserDashboardRepository::countActiveUsers');
            return 0;
        }

        $row = pg_fetch_assoc($result);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @param string[] $adminRoleCodes
     */
    public function countAdminUsers(array $adminRoleCodes): int
    {
        $params = [];
        $roleMatches = [
            "UPPER(COALESCE(r.role_code, '')) LIKE '%ADMIN%'",
            "COALESCE(p.position_name, '') ILIKE '%ผู้ดูแล%'",
        ];

        foreach ($adminRoleCodes as $code) {
            if ($code === '') {
                continue;
            }
            $params[] = strtoupper($code);
            $roleMatches[] = "UPPER(COALESCE(r.role_code, '')) = $" . count($params);
        }

        $roleClause = implode(' OR ', $roleMatches);
        $query = "SELECT COUNT(DISTINCT u.id)::integer AS total
                  FROM users.tb_users u
                  LEFT JOIN users.tb_roles r ON r.id = u.role_id
                  LEFT JOIN users.tb_positions p ON p.id = u.position_id
                  WHERE u.user_status = 'active'
                  AND ($roleClause)";

        $result = !empty($params)
            ? @pg_query_params($this->conn, $query, $params)
            : @pg_query($this->conn, $query);

        if ($result === false) {
            $this->logPgError('UserDashboardRepository::countAdminUsers');
            return 0;
        }

        $row = pg_fetch_assoc($result);

        return (int) ($row['total'] ?? 0);
    }
}
