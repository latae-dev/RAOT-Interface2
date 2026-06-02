<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

require_once __DIR__ . '/../configs/authMiddleware.php';
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

ob_start();

$dashboardConfig = require __DIR__ . '/../configs/dashboard_config.php';
$dashboardStats = [
    'online_users' => 0,
    'total_users' => 0,
    'admin_users' => 0,
    'online_timeout_minutes' => $dashboardConfig['online_timeout_minutes'],
];
$dashboardTopUsers = [];
$dashboardMonthlyDocuments = [
    'labels' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
    'month_labels' => [],
    'year' => (int) date('Y'),
    'approved' => array_fill(0, 12, 0),
    'pending' => array_fill(0, 12, 0),
];

try {
    require_once __DIR__ . '/../configs/database.php';
    require_once __DIR__ . '/../configs/constants.php';
    require_once __DIR__ . '/../repositories/user/user_dashboard_repository.php';
    require_once __DIR__ . '/../repositories/dashboard/dashboard_document_repository.php';
    require_once __DIR__ . '/../services/dashboard/dashboard_user_stats_service.php';
    require_once __DIR__ . '/../services/dashboard/dashboard_overview_service.php';

    $database = new Database();
    $db = $database->connect('raot_db_qas');
    if ($db) {
        $dashboardRepository = new UserDashboardRepository($db);
        $documentRepository = new DashboardDocumentRepository($db);
        $dashboardStatsService = new DashboardUserStatsService($dashboardRepository, $dashboardConfig);
        $dashboardOverviewService = new DashboardOverviewService($dashboardStatsService, $documentRepository);
        $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $dashboardOverview = $dashboardOverviewService->getOverview($currentUserId);
        $dashboardStats = $dashboardOverview['user_stats'];
        $dashboardTopUsers = $dashboardOverview['top_users'];
        $dashboardMonthlyDocuments = $dashboardOverview['monthly_documents'];
    }
} catch (Throwable $e) {
    error_log('index.php dashboard stats error: ' . $e->getMessage());
}

if (ob_get_length()) {
    ob_end_clean();
}

function formatDashboardUserCount(int $count): string
{
    return number_format($count) . ' User';
}

function escDashboard($text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function dashboardUserDisplayName(array $user): string
{
    $name = trim(($user['user_fname'] ?? '') . ' ' . ($user['user_lname'] ?? ''));

    return $name !== '' ? $name : ($user['user_code'] ?? '-');
}

function dashboardUserInitials(array $user): string
{
    $fname = trim($user['user_fname'] ?? '');
    $lname = trim($user['user_lname'] ?? '');

    if ($fname !== '' && $lname !== '') {
        return mb_strtoupper(mb_substr($fname, 0, 1) . mb_substr($lname, 0, 1));
    }

    $code = trim($user['user_code'] ?? 'U');

    return mb_strtoupper(mb_substr($code, 0, 2));
}
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

    <!-- DATA TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">Dashboards</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Main</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboards</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <a href="<?php echo $baseUrl; ?>/pages/parcel-withdrawal-list.php" class="category-link success text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" id="swatchbook"><path opacity="0.2" d="M9 22H5a3.003 3.003 0 0 1-3-3V5a3.003 3.003 0 0 1 3-3h4a3.003 3.003 0 0 1 3 3v14a3.003 3.003 0 0 1-3 3z"></path><path opacity="0.4" d="m20.293 6.535-2.828-2.828a3.004 3.004 0 0 0-4.243 0l-1.229 1.228c0 .022.007.043.007.065v14c0 .027-.007.052-.008.08l8.301-8.302a3.004 3.004 0 0 0 0-4.243z"></path><circle cx="7" cy="17" r="1" opacity="1"></circle><path opacity="1" d="m19.065 12.007-7.073 7.072c0-.027.008-.052.008-.079a3.003 3.003 0 0 1-3 3h10a3.003 3.003 0 0 0 3-3v-4a3 3 0 0 0-2.935-2.993z"></path></svg>
                                        <p class="fs-14 mb-1 text-default fw-semibold">ระบบบริหารจัดการข้อมูลพัสดุ</p>
                                        <span class="fs-11 text-muted">100 Doc</span>
                                    </a>
                                </div>
                                <div class="mb-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <a href="<?php echo $baseUrl; ?>/parcel/pages/withdraw-money-list.php" class="category-link secondary text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" id="swatchbook"><path opacity="0.2" d="M9 22H5a3.003 3.003 0 0 1-3-3V5a3.003 3.003 0 0 1 3-3h4a3.003 3.003 0 0 1 3 3v14a3.003 3.003 0 0 1-3 3z"></path><path opacity="0.4" d="m20.293 6.535-2.828-2.828a3.004 3.004 0 0 0-4.243 0l-1.229 1.228c0 .022.007.043.007.065v14c0 .027-.007.052-.008.08l8.301-8.302a3.004 3.004 0 0 0 0-4.243z"></path><circle cx="7" cy="17" r="1" opacity="1"></circle><path opacity="1" d="m19.065 12.007-7.073 7.072c0-.027.008-.052.008-.079a3.003 3.003 0 0 1-3 3h10a3.003 3.003 0 0 0 3-3v-4a3 3 0 0 0-2.935-2.993z"></path></svg>
                                        <p class="fs-14 mb-1 text-default fw-semibold">ระบบเงินทดรองและเบิกจ่าย</p>
                                        <span class="fs-11 text-muted">300 Doc</span>
                                    </a>
                                </div>
                                <div class="mb-4 col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <a href="<?php echo $baseUrl; ?>/pages/business-budget-list.php" class="category-link warning text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" id="swatchbook"><path opacity="0.2" d="M9 22H5a3.003 3.003 0 0 1-3-3V5a3.003 3.003 0 0 1 3-3h4a3.003 3.003 0 0 1 3 3v14a3.003 3.003 0 0 1-3 3z"></path><path opacity="0.4" d="m20.293 6.535-2.828-2.828a3.004 3.004 0 0 0-4.243 0l-1.229 1.228c0 .022.007.043.007.065v14c0 .027-.007.052-.008.08l8.301-8.302a3.004 3.004 0 0 0 0-4.243z"></path><circle cx="7" cy="17" r="1" opacity="1"></circle><path opacity="1" d="m19.065 12.007-7.073 7.072c0-.027.008-.052.008-.079a3.003 3.003 0 0 1-3 3h10a3.003 3.003 0 0 0 3-3v-4a3 3 0 0 0-2.935-2.993z"></path></svg>
                                        <p class="fs-14 mb-1 text-default fw-semibold">ระบบคำของบประมาณรายจ่ายประจำปี</p>
                                        <span class="fs-11 text-muted">200 Doc</span>
                                    </a>
                                </div>

                                <div class="mb-4 col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <a href="<?php echo $baseUrl; ?>/pages/investment-budget-list.php" class="category-link primary text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" id="swatchbook"><path opacity="0.2" d="M9 22H5a3.003 3.003 0 0 1-3-3V5a3.003 3.003 0 0 1 3-3h4a3.003 3.003 0 0 1 3 3v14a3.003 3.003 0 0 1-3 3z"></path><path opacity="0.4" d="m20.293 6.535-2.828-2.828a3.004 3.004 0 0 0-4.243 0l-1.229 1.228c0 .022.007.043.007.065v14c0 .027-.007.052-.008.08l8.301-8.302a3.004 3.004 0 0 0 0-4.243z"></path><circle cx="7" cy="17" r="1" opacity="1"></circle><path opacity="1" d="m19.065 12.007-7.073 7.072c0-.027.008-.052.008-.079a3.003 3.003 0 0 1-3 3h10a3.003 3.003 0 0 0 3-3v-4a3 3 0 0 0-2.935-2.993z"></path></svg>
                                        <p class="fs-14 mb-1 text-default fw-semibold">ระบบคำขอตั้งงบลงทุน</p>
                                        <span class="fs-11 text-muted">100 Doc</span>
                                    </a>
                                </div>
                                <div class="mb-4 col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                    <a href="<?php echo $baseUrl; ?>/pages/request-proposal-list.php" class="category-link danger text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" id="swatchbook"><path opacity="0.2" d="M9 22H5a3.003 3.003 0 0 1-3-3V5a3.003 3.003 0 0 1 3-3h4a3.003 3.003 0 0 1 3 3v14a3.003 3.003 0 0 1-3 3z"></path><path opacity="0.4" d="m20.293 6.535-2.828-2.828a3.004 3.004 0 0 0-4.243 0l-1.229 1.228c0 .022.007.043.007.065v14c0 .027-.007.052-.008.08l8.301-8.302a3.004 3.004 0 0 0 0-4.243z"></path><circle cx="7" cy="17" r="1" opacity="1"></circle><path opacity="1" d="m19.065 12.007-7.073 7.072c0-.027.008-.052.008-.079a3.003 3.003 0 0 1-3 3h10a3.003 3.003 0 0 0 3-3v-4a3 3 0 0 0-2.935-2.993z"></path></svg>
                                        <p class="fs-14 mb-1 text-default fw-semibold">ระบบใบขอเสนอซื้อ PR Onlibe</p>
                                        <span class="fs-11 text-muted">300 Doc</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <div class="row">
                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2 align-items-top">
                                <div class="me-1">
                                    <span class="avatar avatar-lg bg-secondary">
                                        <i class="ti ti-users fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h5 class="d-block fw-semibold fs-18 mb-1">ผู้ใช้งานที่ออนไลน์</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted fs-12">ภายใน <?php echo (int) $dashboardStats['online_timeout_minutes']; ?> นาทีที่ผ่านมา</div>
                                        <div class="text-danger" id="dashboard-online-users"><?php echo formatDashboardUserCount((int) $dashboardStats['online_users']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-top gap-2">
                                <div class="me-1">
                                    <span class="avatar avatar-lg bg-primary">
                                        <i class="ti ti-users fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h5 class="d-block fw-semibold fs-18 mb-1">ผู้ใช้งานในระบบ</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted fs-12">สถานะใช้งาน (active)</div>
                                        <div class="text-success" id="dashboard-total-users"><?php echo formatDashboardUserCount((int) $dashboardStats['total_users']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2 align-items-top">
                                <div class="me-1">
                                    <span class="avatar avatar-lg bg-warning">
                                        <i class="ti ti-settings fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h5 class="d-block fw-semibold fs-18 mb-1">ผู้ดูแลระบบ</h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted fs-12">บทบาทผู้ดูแลระบบ</div>
                                        <div class="text-danger" id="dashboard-admin-users"><?php echo formatDashboardUserCount((int) $dashboardStats['admin_users']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End::row-1 -->

    <!-- Start::row-2 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        รายงานงบประมาณประจำปี
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable-basic" class="table table-bordered text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ปีงบประมาณ</th>
                                    <th>รหัส - ชื่อศูนย์เงินทุน</th>
                                    <th>รหัส - ชื่อเงินทุน</th>
                                    <th>รหัส - ชื่อขอบเขตหน้าที่</th>
                                    <th>รหัส - ชื่อรายการภาระผู้พันธ์</th>
                                    <th>งบประมาณที่จัดสรร (บาท)</th>
                                    <th>งบประมาณคงเหลือ (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                                <tr>
                                    <td>2569</td>
                                    <td>60501 - กยท.ส.ท่าแซะ</td>
                                    <td>G1130 - งบทำการ มาตรา 13</td>
                                    <td>10001001 - กิจกรรมงบบริหารทั่วไป</td>
                                    <td>1151100000 - วัตถุดิบคงเหลือ</td>
                                    <td>1,000,000.00</td>
                                    <td>900,000.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-header justify-content-between">
                    <div class="card-title"> 5 อันดับผู้เข้าใช้งานบ่อยที่สุด</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col">ชื่อผู้ใช้งาน</th>
                                    <th scope="col" class="text-center">จำนวนครั้ง</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-top-users-body">
                                <?php if (empty($dashboardTopUsers)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">ยังไม่มีข้อมูลการเข้าใช้งาน</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($dashboardTopUsers as $index => $topUser): ?>
                                <?php $isLastRow = $index === count($dashboardTopUsers) - 1; ?>
                                <tr>
                                    <th scope="row" class="<?php echo $isLastRow ? 'border-bottom-0' : ''; ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <span class="avatar avatar-rounded bg-primary-transparent">
                                                    <span class="fw-semibold text-primary"><?php echo escDashboard(dashboardUserInitials($topUser)); ?></span>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="d-block fw-semibold"><?php echo escDashboard(dashboardUserDisplayName($topUser)); ?></span>
                                                <span class="d-block fs-12 text-muted"><?php echo escDashboard($topUser['user_email'] ?? $topUser['user_code'] ?? '-'); ?></span>
                                            </div>
                                        </div>
                                    </th>
                                    <td class="text-center fw-semibold <?php echo $isLastRow ? 'border-bottom-0' : ''; ?>"><?php echo number_format((int) ($topUser['access_count'] ?? 0)); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Payouts</div>
                </div>
                <div class="card-body">
                    <div id="course-payouts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-2 -->
</div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

        <!-- APEX CHARTS JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- DASHBOARD -->
        <script>
            window.__DASHBOARD_CHART__ = <?php echo json_encode($dashboardMonthlyDocuments, JSON_UNESCAPED_UNICODE); ?>;
        </script>
        <script src="<?php echo $baseUrl; ?>/assets/js/courses-dashboard.js"></script>
        <script src="<?php echo $baseUrl; ?>/pages/js/index-dashboard.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- DataTables: โหลดหลัง jquery.min.js ใน base.php -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="<?php echo $baseUrl; ?>/pages/js/index-datatables.js"></script>
<!-- This code use for render base file -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('no_permission') === '1') {
            Swal.fire({
                icon: 'error',
                title: 'ไม่มีสิทธิ์เข้าใช้งาน',
                text: 'กรุณาติดต่อผู้ดูแลระบบ',
                showConfirmButton: false,
                timer: 1500,
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
