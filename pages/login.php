<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
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

<body style="background-image: url('<?php echo $baseUrl; ?>/assets/images/authentication/background_raot.png'); 
             background-repeat: no-repeat;
             background-attachment: fixed;
             background-size: cover;">
    <?php $errorbody = ob_get_clean(); ?>

    <div id="loading" class="loading-overlay">
        <img src="<?php echo $baseUrl; ?>/assets/images/loading.svg" alt="Loading...">
    </div>
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.0);
            /* พื้นหลังโปร่งแสง */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
            /* ซ่อนเริ่มต้น */
        }
    </style>

    <div class="container">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="my-5 d-flex justify-content-center">
                    <a href="index.php">
                        <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                        <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                    </a>
                </div>
                <div class="card custom-card">
                    <div class="card-body p-5">
                        <p class="h5 fw-semibold mb-2 text-center">เข้าสู่ระบบ</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">ยินดีต้อนรับผู้ใช้งานระบบ</p>
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <label for="signin-username" class="form-label text-default">ชื่อผู้ใช้งาน</label>
                                <input type="text" class="form-control form-control-lg" name="user_code" id="signin-username" placeholder="กรุณากรอก ชื่อผู้ใช้งาน">
                            </div>
                            <div class="col-xl-12 mb-2">
                                <label for="signin-password" class="form-label text-default d-block">รหัสผ่าน</label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" name="user_pass" id="signin-password" placeholder="กรุณากรอก รหัสผ่าน">
                                    <button class="btn btn-light" type="button" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                        <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                            จดจำรหัสผ่าน ?
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 d-grid mt-2">
                                <a type="button" id="btnLogin" class="btn btn-lg btn-primary">เข้าสู่ระบบ</a>
                            </div>
                        </div>

                        <!-- <div class="text-center my-3 authentication-barrier">
                            <span>OR</span>
                        </div>
                        <div class="text-center">
                            <p class="fs-12 text-muted mt-3">แจ้งเรื่องเพื่อขอเข้าสู่ระบบ <a href="request-system.php" class="text-primary">ร้องขอ</a></p>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $content = ob_get_clean(); ?>
    <!-- This code is useful for content -->

    <!-- This code is useful for internal scripts  -->
    <?php ob_start(); ?>

    <!-- SHOW PASSWORD JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/show-password.js"></script>

    <?php $scripts = ob_get_clean(); ?>
    <!-- This code is useful for internal scripts  -->

    <!-- This code use for render base file -->
    <?php include 'layouts/custom-base.php'; ?>
    <!-- This code use for render base file -->

    <script type="module" src="<?php echo $baseUrl; ?>/pages/js/login.js"></script>