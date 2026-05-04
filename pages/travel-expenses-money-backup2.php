<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages-raot', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- QUILL CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.snow.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.bubble.css">

        <!-- FILEPOND CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">

        <!-- DATA TABLES CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

        <!-- DATE & TIME PICKER CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">

        <!-- SWEET ALERTS CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.css">

<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มคำขอเบิกเงินทดรองจ่าย</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคำขอเบิกเงินทดรองจ่าย</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Page Header Close -->

                    <!-- รายละเอียดผู้ขอเบิก -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">รายละเอียดผู้ยื่นคำขอเบิกเงินทดรองจ่าย</div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                            <input type="text" class="form-control" id="input-label" placeholder="ยืมเงินทดลอง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="publish-date" class="form-label">วันที่เริ่ม :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เริ่ม">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">วันที่สิ้นสุด :</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="endDate" placeholder="กรุณากรอก วันที่สิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="ดึงข้อมูล User มาแสดง" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูล User มาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูล User มาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">ระดับ :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูล User มาแสดง" disabled>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label1" class="form-label">สังกัด :</label>
                                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูล User มาแสดง" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">รายการทำการขอเบิกทดรองจ่าย</div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap  table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">ลำดับ</th>
                                                            <th scope="col">รายการ</th>
                                                            <th scope="col">จำนวน</th>
                                                            <th scope="col">หน่วย</th>
                                                            <th scope="col">ราคา/หน่วย</th>
                                                            <th scope="col">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="product-list">
                                                            <td>1</td>
                                                            <td>5010400020 - ค่าพาหนะในการเดินทางในประเทศทดสอบ</td>
                                                            <td>100</td>
                                                            <td>2</td>
                                                            <td>100</td>
                                                            <td>10,000.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td>1</td>
                                                            <td>5010400020 - ค่าพาหนะในการเดินทางในประเทศทดสอบ</td>
                                                            <td>100</td>
                                                            <td>2</td>
                                                            <td>100</td>
                                                            <td>10,000.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td>ผลรวมทั้งหมด</td>
                                                            <td colspan="5" class="text-center">20,000.00</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="col-xl-12">
                                        <div class="custom-card">
                                            <ul class="nav nav-pills justify-content-start nav-style-3 mb-3 fs-15" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link fw-bold fs-15 active" data-bs-toggle="tab" role="tab" aria-current="page" href="#travel-expense-claim-right" aria-selected="true">ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                                                </li>
                                                <li class="nav-item fw-bold fs-15">
                                                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#money-order-right" aria-selected="true">ใบสั่งจ่าย</a>
                                                </li>
                                                <li class="nav-item fw-bold fs-15">
                                                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#director-compensation-right" aria-selected="true">ใบสั่งจ่ายตอบแทนกรรมการ</a>
                                                </li>
                                                <li class="nav-item fw-bold fs-15">
                                                    <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#director-compensation-expert-right" aria-selected="true">ใบสั่งจ่ายตอบแทนผู้ทรงคุณวุฒิ</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane show active" id="travel-expense-claim-right" role="tabpanel">
                                                    <div class="row border-bottom mb-2">
                                                        <p class="fs-15 fw-bold mb-2 me-4">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง :</p>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการบริหาร 49(1)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="492">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="493">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="494">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="495">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="496">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="para">
                                                                <label class="form-check-label" for="c-1">กองทุนพัฒนายางพารา</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="other">
                                                                <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                                <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="col-xl-12 mb-2">
                                                                <form>
                                                                    <div class="row">
                                                                        <label class="form-label fs-15 mb-2">กรุณาเลือกสถานที่เริ่มต้น</label>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                                <label class="form-check-label" for="flexRadioDefault1">ที่อยู่</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                                <label class="form-check-label" for="flexRadioDefault2">สำนักงาน</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                                                <label class="form-check-label" for="flexRadioDefault2">ประเทศไทย</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-12 mb-2">
                                                                            <label class="form-label fs-15 mb-2">วันที่เดินทาง :</label>
                                                                            <div class="form-group">
                                                                                <div class="input-group">
                                                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เดินทาง">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-12 mb-2">
                                                                            <label class="form-label fs-15 mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                                                            <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="col-xl-12 mb-2">
                                                                <form>
                                                                    <div class="row">
                                                                        <label class="form-label fs-15 mb-2">กรุณาเลือกสถานที่กลับ</label>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                                <label class="form-check-label" for="flexRadioDefault1">ที่อยู่</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                                <label class="form-check-label" for="flexRadioDefault2">สำนักงาน</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-4 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                                                <label class="form-check-label" for="flexRadioDefault2">ประเทศไทย</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-12 mb-2">
                                                                            <label class="form-label fs-15 mb-2">วันที่เดินทาง :</label>
                                                                            <div class="form-group">
                                                                                <div class="input-group">
                                                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เดินทาง">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-xl-12 mb-2">
                                                                            <label class="form-label fs-15 mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                                                            <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-6  mb-2">
                                                            <div class="col-xl-12 mb-2">
                                                                <div class="row">
                                                                    <label class="form-label fs-15 mb-2">กรุณาเลือกจำนวนการเดินทาง</label>
                                                                    <div class="col-xl-4 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                            <label class="form-check-label" for="flexRadioDefault1">ข้าพเจ้า</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xl-4 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                            <label class="form-check-label" for="flexRadioDefault2">คณะเดินทาง</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xl-4 mb-2">
                                                                        <div class="input-group rounded flex-nowrap">
                                                                            <button class="btn btn-icon btn-light input-group-text flex-fill product-quantity-minus border-end-0" fdprocessedid="g5sbvt"><i class="ri-subtract-line"></i></button>
                                                                            <input type="text" class="form-control form-control-sm text-center w-100" aria-label="quantity" id="product-quantity" value="1" fdprocessedid="gerxeg">
                                                                            <button class="btn btn-icon btn-light input-group-text flex-fill product-quantity-plus border-start-0" fdprocessedid="3mlr"><i class="ri-add-line"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6  mb-2">
                                                            <div class="col-xl-12 mb-2">
                                                                <div class="row">
                                                                    <label class="form-label fs-15 mb-2">กรณีใช้รถส่วนตัว</label>
                                                                    <div class="col-xl-4 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                            <label class="form-check-label" for="flexRadioDefault1">ที่อยู่</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-12">
                                                            <label class="form-label fs-15 mb-2">ตารางค่าใช้จ่าย</label>
                                                            <div class="table-responsive mb-2">
                                                                <table class="table text-nowrap  table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">
                                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                                            </th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col">ราคาหน่วย</th>
                                                                            <th scope="col">ราคาทั้งหมด</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="product-list">
                                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <label class="form-label fs-15 mb-2">ใบรับรองการจ่ายเงินแทนใบเสร็จรับเงิน</label>
                                                            <div class="table-responsive mb-2">
                                                                <table class="table text-nowrap  table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">
                                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                                            </th>
                                                                            <th scope="col" style="width:210px;">วันที่จ่าย</th>
                                                                            <th scope="col">รายละเอียด</th>
                                                                            <th scope="col">จาก</th>
                                                                            <th scope="col">ถึง</th>
                                                                            <th scope="col">จำนวนเงิน</th>
                                                                            <th scope="col">บ้านพักเลขที่</th>
                                                                            <th scope="col">หมู่ที่	</th>
                                                                            <th scope="col">ตำบล</th>
                                                                            <th scope="col">อำเภอ</th>
                                                                            <th scope="col">จังหวัด</th>
                                                                            <th scope="col">หมายเหตุ</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="product-list">
                                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <div class="input-group">
                                                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                                        <input type="text" class="form-control" id="endDate" placeholder="กรุณากรอก วันที่จ่าย">
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>    
                                                <div class="tab-pane" id="money-order-right" role="tabpanel">
                                                    <div class="row border-bottom mb-2">
                                                        <p class="fs-15 fw-bold mb-2 me-4">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง :</p>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการบริหาร 49(1)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="492">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="493">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="494">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="495">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="496">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="para">
                                                                <label class="form-check-label" for="c-1">กองทุนพัฒนายางพารา</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="other">
                                                                <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                                <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-12">
                                                            <label class="form-label fs-15 mb-2">ตารางค่าใช้จ่าย</label>
                                                            <div class="table-responsive mb-2">
                                                                <table class="table text-nowrap  table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">
                                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                                            </th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col">ราคาหน่วย</th>
                                                                            <th scope="col">ราคาทั้งหมด</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="product-list">
                                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="director-compensation-right" role="tabpanel">
                                                    <div class="row border-bottom mb-2">
                                                        <p class="fs-15 fw-bold mb-2 me-4">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง :</p>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการบริหาร 49(1)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="492">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="493">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="494">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="495">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="496">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="para">
                                                                <label class="form-check-label" for="c-1">กองทุนพัฒนายางพารา</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="other">
                                                                <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                                <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-12">
                                                            <label class="form-label fs-15 mb-2">ตารางค่าใช้จ่าย</label>
                                                            <div class="table-responsive mb-2">
                                                                <table class="table text-nowrap  table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">
                                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                                            </th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col">ราคาหน่วย</th>
                                                                            <th scope="col">ราคาทั้งหมด</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="product-list">
                                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="director-compensation-expert-right" role="tabpanel">
                                                <div class="row border-bottom mb-2">
                                                        <p class="fs-15 fw-bold mb-2 me-4">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง :</p>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการบริหาร 49(1)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="492">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="493">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="494">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-3 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="495">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="496">
                                                                <label class="form-check-label" for="c-1">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6)</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="para">
                                                                <label class="form-check-label" for="c-1">กองทุนพัฒนายางพารา</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="" id="other">
                                                                <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                                <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-bottom mb-2">
                                                        <div class="col-xl-12">
                                                            <label class="form-label fs-15 mb-2">ตารางค่าใช้จ่าย</label>
                                                            <div class="table-responsive mb-2">
                                                                <table class="table text-nowrap  table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">
                                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                                            </th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col">ราคาหน่วย</th>
                                                                            <th scope="col">ราคาทั้งหมด</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="product-list">
                                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                            <td><input class="form-control" type="text"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="" id="491">
                                                <label class="form-check-label" for="c-1">นับจากวันกลับมาถึง</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="" id="493" checked>
                                                <label class="form-check-label" for="c-1">นับแต่วันที่ได้รับเงิน</label>
                                            </div>
                                        </div>
                                        <div class="col-xl-9">
                                            <div class="form-check mb-2">
                                                <label class="form-check-label mb-1" for="c-1">เหตุผลของการขอยืมเงินทดรอง <small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></label>
                                                <textarea class="form-control" id="product-description-add" rows=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a href="withdraw-money-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class="bi bi-save"></i> ขออนุมัติ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row -->

                </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

        <!-- FILEPOND JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

        <!-- DATATABLES CDN JS -->
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        
        <!-- JQUERY JS -->
        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

        <!-- QUILL JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/quill/quill.min.js"></script>

        <!-- INTERNAL PRODUCT DETAILS JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

        <!-- FLAT PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        
        <!-- CREATE PROJECT JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>

        <!-- INTERNAL CART JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/cart.js"></script>


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->