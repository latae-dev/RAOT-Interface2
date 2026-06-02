<?php

require_once __DIR__ . '/../../repositories/user/user_dashboard_repository.php';

class DashboardUserStatsService
{
    private UserDashboardRepository $repository;
    private int $onlineTimeoutMinutes;
    private int $visitCountIntervalMinutes;
    /** @var string[] */
    private array $adminRoleCodes;

    public function __construct(UserDashboardRepository $repository, array $config = [])
    {
        $this->repository = $repository;
        $this->onlineTimeoutMinutes = max(1, (int) ($config['online_timeout_minutes'] ?? 15));
        $this->visitCountIntervalMinutes = max(1, (int) ($config['visit_count_interval_minutes'] ?? 30));
        $this->adminRoleCodes = $config['admin_role_codes'] ?? [];
    }

    public function getUserStats(?int $currentUserId): array
    {
        if ($currentUserId) {
            $this->repository->touchUserPresence($currentUserId, $this->visitCountIntervalMinutes);
        }

        return [
            'online_users' => $this->repository->countOnlineUsers($this->onlineTimeoutMinutes),
            'total_users' => $this->repository->countActiveUsers(),
            'admin_users' => $this->repository->countAdminUsers($this->adminRoleCodes),
            'online_timeout_minutes' => $this->onlineTimeoutMinutes,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopFrequentUsers(int $limit = 5): array
    {
        return $this->repository->getTopFrequentUsers($limit);
    }
}
