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

<div class="container-fluid">

    <style>
        .json-preview {
            background-color: black;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
    <!-- Start::page-header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <p class="fw-semibold fs-18 mb-0">SAP API INTERFACE</p>
            <span class="fs-semibold text-muted">เชื่อมต่อ SAP ผ่าน REST API</span>
        </div>
        <div class="btn-list mt-md-0 mt-2">
            <!-- <button type="button" class="btn btn-primary btn-wave">
                                <i class="ri-filter-3-fill me-2 align-middle d-inline-block"></i>Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-wave">
                                <i class="ri-upload-cloud-line me-2 align-middle d-inline-block"></i>Export
                            </button> -->
        </div>
    </div>
    <!-- End::page-header -->

    <!-- Start::row -->
    <div class="row row-sm mt-lg-4">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="card rounded-sm">
                <div class="card-body p-4">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-doc-mat-tab" data-bs-toggle="tab" data-bs-target="#nav-doc-mat" type="button" role="tab" aria-controls="nav-doc-mat" aria-selected="true">ใบเบิกพัสดุแบบพิมพ์</button>
                            <button class="nav-link" id="nav-plan-mat-tab" data-bs-toggle="tab" data-bs-target="#nav-plan-mat" type="button" role="tab" aria-controls="nav-plan-mat" aria-selected="false">แผนการเบิกพัสดุแบบพิมพ์</button>
                        </div>
                    </nav>
                    <div class="tab-content mt-3" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-doc-mat" role="tabpanel" aria-labelledby="nav-doc-mat-tab" tabindex="0">

                            <div class="row">
                                <div class="col-12">
                                    <div class="text-warning">
                                        Base URL: <?php echo $baseUrl; ?>/
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ดึงข้อมูลรายการ (GET Method)</b>
                                    </div>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">Parameter</th>
                                                <th scope="col">Require</th>
                                                <th scope="col">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">
                                                    action
                                                </th>
                                                <td class="text-danger">
                                                    จำเป็น
                                                </td>
                                                <td>
                                                    ต้องเป็น query string ส่งไปกับ Url parameter
                                                    <div>
                                                        <span class="text-muted">(Ex. {Base URL}pages/api-sap/doc_mat_get.php?action=pending)</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ตัวอย่างผลลัพ</b>
                                    </div>
                                    <pre id="jsonOutput" class="json-preview">กำลังโหลดข้อมูล...</pre>
                                    <script>
                                        // โหลดไฟล์ JSON
                                        fetch('json/doc-mat.json')
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('ไม่สามารถโหลดไฟล์ JSON ได้');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                // แสดงข้อมูล JSON ใน tag <pre>
                                                document.getElementById('jsonOutput').textContent = JSON.stringify(data, null, 2);
                                            })
                                            .catch(error => {
                                                document.getElementById('jsonOutput').textContent = 'เกิดข้อผิดพลาด: ' + error;
                                            });
                                    </script>
                                </div>

                            </div>
                            <hr>
                            <div>
                                <b>อัพเดทสถานะ (PUT Method)</b>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">Parameter</th>
                                                <th scope="col">Require</th>
                                                <th scope="col">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">action</th>
                                                <td class="text-danger">จำเป็น</td>
                                                <td class="align-top" rowspan="2">
                                                    ต้องเป็น query string ส่งไปกับ Url parameter
                                                    <div>
                                                        <span class="text-muted">(Ex. {Base URL}pages/api-sap/doc_mat_get.php?action=complete&id=12)</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">id</th>
                                                <td class="text-danger">จำเป็น</td>
                                                <!-- ตรงนี้ไม่ต้องมี <td> อีก -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ตัวอย่างผลลัพ</b>
                                    </div>
                                    <pre id="jsonOutput2" class="json-preview">กำลังโหลดข้อมูล...</pre>
                                    <script>
                                        // โหลดไฟล์ JSON
                                        fetch('json/doc-mat-update.json')
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('ไม่สามารถโหลดไฟล์ JSON ได้');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                // แสดงข้อมูล JSON ใน tag <pre>
                                                document.getElementById('jsonOutput2').textContent = JSON.stringify(data, null, 2);
                                            })
                                            .catch(error => {
                                                document.getElementById('jsonOutput2').textContent = 'เกิดข้อผิดพลาด: ' + error;
                                            });
                                    </script>
                                </div>

                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-plan-mat" role="tabpanel" aria-labelledby="nav-plan-mat-tab" tabindex="0">
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-warning">
                                        Base URL: <?php echo $baseUrl; ?>/
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ดึงข้อมูลรายการ (GET Method)</b>
                                    </div>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">Parameter</th>
                                                <th scope="col">Require</th>
                                                <th scope="col">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">
                                                    action
                                                </th>
                                                <td class="text-danger">
                                                    จำเป็น
                                                </td>
                                                <td>
                                                    ต้องเป็น query string ส่งไปกับ Url parameter
                                                    <div>
                                                        <span class="text-muted">(Ex. {Base URL}pages/api-sap/plan_mat_get.php?action=pending)</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ตัวอย่างผลลัพ</b>
                                    </div>
                                    <pre id="jsonOutput3" class="json-preview">กำลังโหลดข้อมูล...</pre>
                                    <script>
                                        // โหลดไฟล์ JSON
                                        fetch('json/plan-mat.json')
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('ไม่สามารถโหลดไฟล์ JSON ได้');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                // แสดงข้อมูล JSON ใน tag <pre>
                                                document.getElementById('jsonOutput3').textContent = JSON.stringify(data, null, 2);
                                            })
                                            .catch(error => {
                                                document.getElementById('jsonOutput3').textContent = 'เกิดข้อผิดพลาด: ' + error;
                                            });
                                    </script>
                                </div>

                            </div>
                            <hr>
                            <div>
                                <b>อัพเดทสถานะ (PUT Method)</b>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">Parameter</th>
                                                <th scope="col">Require</th>
                                                <th scope="col">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">action</th>
                                                <td class="text-danger">จำเป็น</td>
                                                <td class="align-top" rowspan="2">
                                                    ต้องเป็น query string ส่งไปกับ Url parameter
                                                    <div>
                                                        <span class="text-muted">(Ex. {Base URL}pages/api-sap/plan_mat_get.php?action=complete&id=12)</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">id</th>
                                                <td class="text-danger">จำเป็น</td>
                                                <!-- ตรงนี้ไม่ต้องมี <td> อีก -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <b>ตัวอย่างผลลัพ</b>
                                    </div>
                                    <pre id="jsonOutput4" class="json-preview">กำลังโหลดข้อมูล...</pre>
                                    <script>
                                        // โหลดไฟล์ JSON
                                        fetch('json/plan-mat-update.json')
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('ไม่สามารถโหลดไฟล์ JSON ได้');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                // แสดงข้อมูล JSON ใน tag <pre>
                                                document.getElementById('jsonOutput4').textContent = JSON.stringify(data, null, 2);
                                            })
                                            .catch(error => {
                                                document.getElementById('jsonOutput4').textContent = 'เกิดข้อผิดพลาด: ' + error;
                                            });
                                    </script>
                                </div>

                            </div>
                        </div>
                    </div>
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