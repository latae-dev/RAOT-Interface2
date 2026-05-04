<?php
require_once __DIR__ . '/../configs/authMiddleware.php';
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Start::page-header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <p class="fw-semibold fs-18 mb-0">Welcome back, Json Taylor !</p>
            <span class="fs-semibold text-muted">Track your sales activity, leads and deals here.</span>
        </div>
        <div class="btn-list mt-md-0 mt-2">
            <button type="button" class="btn btn-primary btn-wave">
                <i class="ri-filter-3-fill me-2 align-middle d-inline-block"></i>Filters
            </button>
            <button type="button" class="btn btn-outline-secondary btn-wave">
                <i class="ri-upload-cloud-line me-2 align-middle d-inline-block"></i>Export
            </button>
        </div>
    </div>
    <!-- End::page-header -->

    <!-- Start::row -->
    <div class="row row-sm mt-lg-4">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="card bg-primary rounded-sm">
                <div class="card-body p-4">
                    <span class="text-fixed-white">NOTE:</span>
                    <p class="mt-2 mb-0 text-fixed-white">Thank you for choosing our template. if you want to create your own customised project take a refrence of our project and you can implement here.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row -->

</div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
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