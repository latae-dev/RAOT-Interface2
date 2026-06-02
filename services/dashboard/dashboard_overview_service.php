<?php

require_once __DIR__ . '/dashboard_user_stats_service.php';
require_once __DIR__ . '/../../repositories/dashboard/dashboard_document_repository.php';

class DashboardOverviewService
{
    private DashboardUserStatsService $userStatsService;
    private DashboardDocumentRepository $documentRepository;

    public function __construct(
        DashboardUserStatsService $userStatsService,
        DashboardDocumentRepository $documentRepository
    ) {
        $this->userStatsService = $userStatsService;
        $this->documentRepository = $documentRepository;
    }

    public function getOverview(?int $currentUserId): array
    {
        $userStats = $this->userStatsService->getUserStats($currentUserId);

        return [
            'user_stats' => $userStats,
            'top_users' => $this->userStatsService->getTopFrequentUsers(5),
            'monthly_documents' => $this->documentRepository->getMonthlyDocumentTrends(),
            'module_counts' => $this->documentRepository->getModuleDocumentCounts(),
        ];
    }
}
