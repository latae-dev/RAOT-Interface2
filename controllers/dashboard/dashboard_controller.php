<?php

ini_set('display_errors', '0');
ob_start();

include_once '../../configs/authMiddleware.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../configs/database.php';
include_once '../../configs/constants.php';
include_once '../../services/dashboard/dashboard_user_stats_service.php';
include_once '../../services/dashboard/dashboard_overview_service.php';
include_once '../../repositories/user/user_dashboard_repository.php';
include_once '../../repositories/dashboard/dashboard_document_repository.php';

class DashboardController
{
    private DashboardUserStatsService $statsService;
    private DashboardOverviewService $overviewService;

    public function __construct()
    {
        checkAuth();

        $database = new Database();
        $db = $database->connect('raot_db_qas');
        if (!$db) {
            $this->sendJson([
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้',
            ], 500);
            exit;
        }

        $config = require __DIR__ . '/../../configs/dashboard_config.php';
        $userRepository = new UserDashboardRepository($db);
        $documentRepository = new DashboardDocumentRepository($db);
        $this->statsService = new DashboardUserStatsService($userRepository, $config);
        $this->overviewService = new DashboardOverviewService($this->statsService, $documentRepository);
    }

    public function processRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $_GET['action'] ?? '';

        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        if ($action === 'user_stats') {
            $this->getUserStats();
            return;
        }

        if ($action === 'overview') {
            $this->getOverview();
            return;
        }

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

    private function sendJson(array $payload, int $statusCode = 200): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function getUserStats(): void
    {
        $userId = getCurrentUserId();
        $stats = $this->statsService->getUserStats($userId ? (int) $userId : null);

        $this->sendJson([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    private function getOverview(): void
    {
        $userId = getCurrentUserId();
        $overview = $this->overviewService->getOverview($userId ? (int) $userId : null);

        $this->sendJson([
            'status' => 'success',
            'data' => $overview,
        ]);
    }
}

$controller = new DashboardController();
$controller->processRequest();
