<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>

        <!-- SWEETALERTS CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.css">

        <!-- SELECT2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

        <!-- QUILL CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.snow.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/quill/quill.bubble.css">

        <!-- FLATPICKER CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.css">

        <!-- FILEPOND CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond/filepond.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">

        <!-- DATA TABLES CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

        <!-- PRISM CSS -->
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/prismjs/themes/prism-coy.min.css">


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                        <h1 class="page-title fw-semibold fs-18 mb-0">คำขอสร้างใบขอเสนอ</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">ระบบจัดซื้อ</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">คำขอสร้างใบขอเสนอ</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">แบบฟอร์มคำขอสร้างใบขอเสนอ</div>
                                </div>

                                <!-- แบบฟอร์มกรอก -->
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ประเภทแบบฟอร์ม : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="s-1">คำขอสร้างใบขอเสนอซื้อ</option>
                                                <option value="s-2">คำขอสร้างใบขอเสนอเช่า</option>
                                                <option value="s-3">คำขอสร้างใบขอเสนอจ้าง</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เริ่มต้น : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันเริ่มต้น">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">สิ้นสุด : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" placeholder="กำหนด วันสิ้นสุด">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                                            <input type="text" class="form-control" id="input-label11" placeholder="10432" disabled>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label for="input-label11" class="form-label">บันทึกส่วนหัว :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก บันทึกส่วนหัว"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label for="input-label11" class="form-label">คำอธิบาย :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก คำอธิบาย"></textarea>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">หมวดการกำหนดบัญชี : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="A">A สินทรัพย์</option>
                                                <option value="K">K ศูนย์ต้นทุน</option>
                                                <option value="">ไม่เลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">โรงงาน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="A">1000 - กยท. สำนักงานใหญ่</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ที่เก็บสินค้า : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="1000">1000 - การยางแห่งประเทศไทย</option>
                                                <option value="1001">1001 - คลังพัสดุ</option>
                                                <option value="1010">1010 - กยท. สำนักงานใหญ่</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">กลุ่มการจัดซื้อ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="100">100 - การยางแห่งประเทศไทย</option>
                                                <option value="101">101 - กยท. สำนักงานใหญ่</option>
                                                <option value="102">102 - สำนักผู้ว่าการ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                
                            </div>
                        </div>
                    </div>
                    <!--End::row -->

                    <!-- หมวดการกำหนดบัญชีไม่เลือก -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ</div>
                                </div>

                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <th scope="col" style="width: 3px;">
                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                            </th>
                                                            <th scope="col" style="width: 15%;">วัสดุ </th>
                                                            <th scope="col" style="width: 10%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 10%;">หน่วย</th>
                                                            <th scope="col" style="width: 18%;">วันที่ส่งมอบ</th>
                                                            <th scope="col" style="width: 20%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 12%;">ราคา</th>
                                                            <th scope="col" style="width: 12%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                            <td contenteditable="true" class="text-start">5001000005</td>
                                                            <td contenteditable="true">88</td>
                                                            <td contenteditable="true">ซอง</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" id="date" placeholder="กำหนดวันที่ส่งมอบ" style="border: none; width: 100%; background: transparent; text-align: center;">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td contenteditable="true" class="text-start">105230000 - วัสดุการเกษตรคงเหลือ</td>
                                                            <td contenteditable="true">1,000.00</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tfooter class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td contenteditable="true" class="text-end">88.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                        </tr>
                                                    </tfooter>
                                                </table>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="TH">TH</option>
                                                        <option value="USD">USD</option>
                                                        <option value="JPD">JPD</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="บาท">บาท</option>
                                                        <option value="ดอลล่า">ดอลล่า</option>
                                                        <option value="เย็น">เย็น</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รหัสภาษี : </label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">0%</option>
                                                <option value="">3%</option>
                                                <option value="">5%</option>
                                                <option value="">7%</option>
                                            </select>
                                        </div>                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="parcel-withdrawal-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                    <button class="btn btn-danger btn-wave waves-effect waves-light m-1"><i class='bx bxs-reset' ></i> ล้างข้อมูล</button>
                                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class='bx bxs-save'></i> ขออนุมัติ</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดการกำหนดบัญชี A สินทรัพทย์-->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ</div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <th scope="col" style="width: 3px;">
                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                            </th>
                                                            <th scope="col" style="width: 15%;">รหัสสินทรัพย์ </th>
                                                            <th scope="col" style="width: 10%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 10%;">หน่วย</th>
                                                            <th scope="col" style="width: 18%;">วันที่ส่งมอบ</th>
                                                            <th scope="col" style="width: 20%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 12%;">ราคา</th>
                                                            <th scope="col" style="width: 12%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                            <td contenteditable="true" class="text-start">302000000053</td>
                                                            <td contenteditable="true">10</td>
                                                            <td contenteditable="true">เครื่อง</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" id="date" placeholder="กำหนดวันที่ส่งมอบ" style="border: none; width: 100%; background: transparent; text-align: center;">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td contenteditable="true" class="text-start">105230000 - วัสดุการเกษตรคงเหลือ</td>
                                                            <td contenteditable="true">1,000.00</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tfooter class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td contenteditable="true" class="text-end">88.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                        </tr>
                                                    </tfooter>
                                                </table>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="TH">TH</option>
                                                        <option value="USD">USD</option>
                                                        <option value="JPD">JPD</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="บาท">บาท</option>
                                                        <option value="ดอลล่า">ดอลล่า</option>
                                                        <option value="เย็น">เย็น</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รหัสภาษี :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">0%</option>
                                                <option value="">3%</option>
                                                <option value="">5%</option>
                                                <option value="">7%</option>
                                            </select>
                                        </div>                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="parcel-withdrawal-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                    <button class="btn btn-danger btn-wave waves-effect waves-light m-1"><i class='bx bxs-reset' ></i> ล้างข้อมูล</button>
                                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class='bx bxs-save'></i> ขออนุมัติ</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดการกำหนดบัญชี K ศูนย์ต้นทุน-->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ</div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <th scope="col" style="width: 3px;">
                                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                            </th>
                                                            <th scope="col" style="width: 30%;">รายการค่าใช้จ่าย</th>
                                                            <th scope="col" style="width: 7%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 7%;">หน่วย</th>
                                                            <th scope="col" style="width: 16%;">วันที่ส่งมอบ</th>
                                                            <th scope="col" style="width: 17%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 10%;">ราคา</th>
                                                            <th scope="col" style="width: 10%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                            <td contenteditable="true" class="text-start">จ้างพัฒนา Web Application โครงการ ERP</td>
                                                            <td contenteditable="true">1</td>
                                                            <td contenteditable="true">งาน</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" id="date" placeholder="กำหนดวันที่ส่งมอบ" style="border: none; width: 100%; background: transparent; text-align: center;">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td contenteditable="true" class="text-start">105230000 - วัสดุการเกษตรคงเหลือ</td>
                                                            <td contenteditable="true">1,000.00</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tfooter class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td contenteditable="true" class="text-end">88.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="7" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td contenteditable="true" class="text-end">88,000.00</td>
                                                        </tr>
                                                    </tfooter>
                                                </table>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="TH">TH</option>
                                                        <option value="USD">USD</option>
                                                        <option value="JPD">JPD</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="js-example-basic-single" name="state">
                                                        <option value="บาท">บาท</option>
                                                        <option value="ดอลล่า">ดอลล่า</option>
                                                        <option value="เย็น">เย็น</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รหัสภาษี : </label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">0%</option>
                                                <option value="">3%</option>
                                                <option value="">5%</option>
                                                <option value="">7%</option>
                                            </select>
                                        </div>                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                                <option value="">XX%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" id="text-area" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="parcel-withdrawal-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                    <button class="btn btn-danger btn-wave waves-effect waves-light m-1"><i class='bx bxs-reset' ></i> ล้างข้อมูล</button>
                                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class='bx bxs-save'></i> ขออนุมัติ</button>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>
        
        <!-- SWEETALERTS JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/sweet-alerts.js"></script>

        <!-- JQUERY JS -->
        <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

        <!-- QUILL JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/quill/quill.min.js"></script>

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

        <!-- INTERNAL DATATABLES JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/datatables.js"></script>

        <!-- INTERNAL PRODUCT DETAILS JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>

        <!-- CREATE PROJECT JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>
        
        <!-- SELECT2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- INTERNAL SELECT2 JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/select2.js"></script>

        <!-- PRISM JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/prismjs/prism.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/prism-custom.js"></script>
        
        <!-- INTERNAL CHOICES JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/choices.js"></script>



<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->