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
                        <h1 class="page-title fw-semibold fs-18 mb-0">ใบขอเสนอ</h1>
                        <div class="ms-md-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">ระบบจัดซื้อ</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">ใบขอเสนอ</li>
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
                                    <div class="card-title">แบบฟอร์มใบขอเสนอ</div>
                                </div>

                                <!-- แบบฟอร์มกรอก -->
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ประเภทแบบฟอร์ม : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="form_type_id" id="form-type-select" style="width: 100%" disabled></select>
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เริ่มต้น : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="date" class="form-control" disabled name="start_date" placeholder="กำหนด วันเริ่มต้น" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">สิ้นสุด : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="date" class="form-control" disabled name="end_date" placeholder="กำหนด วันสิ้นสุด" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label for="input-label11" class="form-label">รหัสหน่วยงาน :</label>
                                            <input type="text" class="form-control" id="department-code" placeholder="รหัสหน่วยงาน" disabled>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label for="input-label11" class="form-label">บันทึกส่วนหัว :</label>
                                            <textarea class="form-control" disabled name="header_note" id="header_note" rows="2" placeholder="กรุณากรอก บันทึกส่วนหัว" disabled></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label for="input-label11" class="form-label">คำอธิบาย :</label>
                                            <textarea class="form-control" disabled name="description" rows="2" placeholder="กรุณากรอก คำอธิบาย" disabled></textarea>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">หมวดการกำหนดบัญชี : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="account_category" id="account-category-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="A">A สินทรัพย์</option>
                                                <option value="K">K ศูนย์ต้นทุน</option>
                                                <option value="N">ไม่เลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">โรงงาน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="factory_id" id="factory-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ที่เก็บสินค้า : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="warehouse_id" id="warehouse-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">กลุ่มการจัดซื้อ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="purchase_group_id" id="purchase-group-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                
                            </div>
                        </div>
                    </div>
                    <!--End::row -->

                    <!-- หมวดการกำหนดบัญชีไม่เลือก -->
                    <div class="row" id="no-category-section">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ (ไม่เลือก)</div>
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
                                                            <th scope="col" style="width: 12%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 12%;">หน่วย</th>
                                                            <th scope="col" style="width: 33%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 13%;">ราคา</th>
                                                            <th scope="col" style="width: 13%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" disabled value="" aria-label="..."></td>
                                                            <td class="text-start">
                                                                <select class="form-control matlist-select" name="mat_code">
                                                                    <option value="">กรุณาเลือกวัสดุ</option>
                                                                </select>
                                                            </td>
                                                            <td contenteditable="false">0</td>
                                                            <td>
                                                                <select class="form-control unit-select" name="unit" style="width: 100%">
                                                                    <option value="">กรุณาเลือกหน่วย</option>
                                                                </select>
                                                            </td>
                                                            <td contenteditable="false" class="text-start">105230000 - วัสดุการเกษตรคงเหลือ</td>
                                                            <td contenteditable="false">0.00</td>
                                                            <td contenteditable="false" class="text-end">0.00</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td class="text-end">0.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td class="text-end">0.00</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-2">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-select" name="monetary_unit_en">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-th-select" name="monetary_unit_th">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">รหัสภาษี : </label>
                                            <select class="form-control" disabled name="tax_id" id="tax-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>  
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">วันที่ส่งมอบ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="date" class="form-control" disabled name="delivery_date" placeholder="กำหนด วันที่ส่งมอบ">
                                                </div>
                                            </div>
                                        </div>                                      
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L :</label>
                                            <select class="form-control" disabled name="gl_account_id" id="gl-account-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <!-- <div class="col-xl-3 mb-2">
                                            <label class="form-label">ประเภทธุรกิจ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="business_type_id" id="business-type-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div> -->
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="cost_center_id" id="cost-center-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="fund_id" id="fund-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="scope_id" id="scope-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="funds_center_id" id="funds-center-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="liability_id" id="liability-select" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" disabled name="purchase_text" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" disabled name="item_note" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" disabled name="delivery_text" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" disabled name="material_order_text" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" disabled name="quotation_text" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="request-proposal-approvel-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดการกำหนดบัญชี A สินทรัพย์-->
                    <div class="row" id="asset-section">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ (A สินทรัพย์)</div>
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
                                                            <th scope="col" style="width: 15%;">รหัสสินทรัพย์</th>
                                                            <th scope="col" style="width: 12%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 12%;">หน่วย</th>
                                                            <th scope="col" style="width: 33%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 13%;">ราคา</th>
                                                            <th scope="col" style="width: 13%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" disabled value="" aria-label="..."></td>
                                                            <td class="text-start">
                                                                <select class="form-control asset-select" name="asset_code">
                                                                    <option value="">กรุณาเลือกรหัสสินทรัพย์</option>
                                                                </select>
                                                            </td>
                                                            <td contenteditable="false">0</td>
                                                            <td>
                                                                <select class="form-control unit-select" name="unit" style="width: 100%">
                                                                    <option value="">กรุณาเลือกหน่วย</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-start">
                                                                <select class="form-control asset-category-select" name="asset_category_id" style="width: 100%">
                                                                    <option value="">กรุณาเลือกกลุ่มวัสดุ</option>
                                                                </select>
                                                            </td>
                                                            <td contenteditable="false">0.00</td>
                                                            <td contenteditable="false" class="text-end">0.00</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td class="text-end">10.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td class="text-end">10,000.00</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-2">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-select" name="monetary_unit_en">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-th-select" name="monetary_unit_th">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">รหัสภาษี :</label>
                                            <select class="form-control" disabled name="tax_id_asset" id="tax-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>  
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">วันที่ส่งมอบ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="date" class="form-control" disabled name="delivery_date" placeholder="กำหนด วันที่ส่งมอบ">
                                                </div>
                                            </div>
                                        </div>                                      
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L :</label>
                                            <select class="form-control" disabled name="gl_account_id_asset" id="gl-account-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <!-- <div class="col-xl-3 mb-2">
                                            <label class="form-label">ประเภทธุรกิจ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="business_type_id_asset" id="business-type-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div> -->
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="cost_center_id_asset" id="cost-center-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="fund_id_asset" id="fund-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="scope_id_asset" id="scope-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="funds_center_id_asset" id="funds-center-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="liability_id_asset" id="liability-select-asset" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" disabled name="purchase_text" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" disabled name="item_note" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" disabled name="delivery_text" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" disabled name="material_order_text" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" disabled name="quotation_text" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="request-proposal-approvel-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดการกำหนดบัญชี K ศูนย์ต้นทุน-->
                    <div class="row" id="cost-center-section">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">การจัดการข้อมูลใบขอเสนอ (K ศูนย์ต้นทุน)</div>
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
                                                            <th scope="col" style="width: 40%;">รายการค่าใช้จ่าย</th>
                                                            <th scope="col" style="width: 10%;">ปริมาณ</th>
                                                            <th scope="col" style="width: 12%;">หน่วย</th>
                                                            <th scope="col" style="width: 15%;">กลุ่มวัสดุ</th>
                                                            <th scope="col" style="width: 10%;">ราคา</th>
                                                            <th scope="col" style="width: 10%;">ราคาทั้งหมด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-center">
                                                        <tr class="product-list">
                                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" disabled value="" aria-label="..."></td>
                                                            <td contenteditable="false" class="text-start">จ้างพัฒนา Web Application โครงการ ERP</td>
                                                            <td contenteditable="false">0</td>
                                                            <td>
                                                                <select class="form-control unit-select" name="unit" style="width: 100%">
                                                                    <option value="">กรุณาเลือกหน่วย</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-start">
                                                                <select class="form-control expen-pr-select" name="expen_pr_id" style="width: 100%" disabled>
                                                                    <option value="">กรุณาเลือกรายการค่าใช้จ่าย</option>
                                                                </select>
                                                            </td>
                                                            <td contenteditable="false">0.00</td>
                                                            <td contenteditable="false" class="text-end">0.00</td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="text-center">
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">ปริมาณทั้งหมด</td>
                                                            <td class="text-end">0.00</td>
                                                        </tr>
                                                        <tr class="product-list">
                                                            <td colspan="6" class="text-end">รวมเป็นเงินทั้งสิ้น</td>
                                                            <td class="text-end">0.00</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-2">
                                            <div class="row">
                                                <label class="form-label">หน่วยเงิน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-select" name="monetary_unit_en">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                                <div class="col-md mb-2">
                                                    <select class="form-control monetary-unit-th-select" name="monetary_unit_th">
                                                        <option value="">กรุณาเลือกหน่วยเงิน</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">รหัสภาษี : </label>
                                            <select class="form-control" disabled name="tax_id_cost_center" id="tax-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>  
                                        <div class="col-xl-2 mb-2">
                                            <label class="form-label">วันที่ส่งมอบ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="date" class="form-control" disabled name="delivery_date" placeholder="กำหนด วันที่ส่งมอบ">
                                                </div>
                                            </div>
                                        </div>                                      
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">บัญชี G/L : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="gl_account_id_cost_center" id="gl-account-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <!-- <div class="col-xl-3 mb-2">
                                            <label class="form-label">ประเภทธุรกิจ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="business_type_id_cost_center" id="business-type-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div> -->
                                        
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ศูนย์ต้นทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="cost_center_id_cost_center" id="cost-center-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">เงินทุน : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="fund_id_cost_center" id="fund-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">ขอบเขตตามหน้าที่ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="scope_id_cost_center" id="scope-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">Funds Center : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="funds_center_id_cost_center" id="funds-center-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                        <div class="col-xl-3 mb-2">
                                            <label class="form-label">รายการภาระผูกพันธ์ : <small class="text-danger ml-2" style="font-weight: lighter;"> *จำเป็นต้องกรอก </small></label>
                                            <select class="form-control" disabled name="liability_id_cost_center" id="liability-select-cost-center" style="width: 100%">
                                                <option value="">กรุณาเลือก</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12 mb-2">
                                            <label class="form-label">ข้อความจัดซื้อจัดจ้าง :</label>
                                            <textarea class="form-control" disabled name="purchase_text" rows="2" placeholder="กรุณากรอก ข้อความจัดซื้อจัดจ้าง"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">หมายเหตุรายการ :</label>
                                            <textarea class="form-control" disabled name="item_note" rows="2" placeholder="กรุณากรอก หมายเหตุรายการ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความการส่งมอบ :</label>
                                            <textarea class="form-control" disabled name="delivery_text" rows="2" placeholder="กรุณากรอก ข้อความการส่งมอบ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความในสั่งซื้อวัสดุ :</label>
                                            <textarea class="form-control" disabled name="material_order_text" rows="2" placeholder="กรุณากรอก ข้อความในสั่งซื้อวัสดุ"></textarea>
                                        </div>
                                        <div class="col-xl-6 mb-2">
                                            <label class="form-label">ข้อความใบเสนอราคา :</label>
                                            <textarea class="form-control" disabled name="quotation_text" rows="2" placeholder="กรุณากรอก ข้อความใบเสนอราคา"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                    <a class="btn btn-warning btn-wave waves-effect waves-light m-1" href="request-proposal-approvel-list.php"><i class='bx bx-undo'></i></i> ย้อนกลับเมนู</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ผลการพิจารณาอนุมัติ -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">ผลการพิจารณาอนุมัติ</div>
                                </div>
                                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered" id="approval-table">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-start" scope="col" style="width: 30px;">ลำดับที่</th>
                                                            <th class="text-center" scope="col" style="width: 200px;">วันที่</th>
                                                            <th class="text-center" scope="col" style="width: 200px;">ผลการพิจารณา</th>
                                                            <th class="text-center" scope="col" style="width: 400px;">ผู้อนุมัติ</th>
                                                            <th class="text-center" scope="col">หมายเหตุ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody><tr><td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td></tr></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
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

        <!-- INTERNAL REQUEST PROPOSAL JS -->
        <script src="<?php echo $baseUrl; ?>/pages/js/request-proposal-form-approvel-view.js"></script>
        
        <!-- CUSTOM CSS FOR UNIT SELECT -->
        <style>
        .custom-unit-select {
            color: #000000 !important;
            background-color: #ffffff !important;
            border: 1px solid #ced4da !important;
            font-size: 14px !important;
            padding: 8px !important;
        }
        .custom-unit-select option {
            color: #000000 !important;
            background-color: #ffffff !important;
        }
        
        /* FIX DROPDOWN POSITIONING */
        .form-control {
            position: relative !important;
            z-index: 1 !important;
        }
        
        /* FIX DROPDOWN OVERFLOW */
        .card-body {
            overflow: visible !important;
        }
        
        /* FIX DROPDOWN CONTAINER */
        .col-xl-3, .col-xl-2 {
            overflow: visible !important;
        }
        
        /* FIX SPECIFIC DROPDOWN POSITIONING */
        select.form-control {
            position: relative !important;
            z-index: 999 !important;
        }
        
        /* FIX DROPDOWN OPTIONS */
        select.form-control option {
            position: relative !important;
            z-index: 999 !important;
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        
        /* FIX DROPDOWN FOCUS */
        select.form-control:focus {
            z-index: 1000 !important;
        }
        
        /* UNIFIED TEXT COLOR FOR ALL INPUTS */
        .form-control, 
        .form-control:focus,
        .form-control:active,
        .form-control:hover,
        input[type="text"],
        input[type="text"]:focus,
        input[type="text"]:active,
        input[type="text"]:hover,
        textarea,
        textarea:focus,
        textarea:active,
        textarea:hover,
        select,
        select:focus,
        select:active,
        select:hover {
            color: #6c757d !important;
            font-weight: 300 !important;
        }
        
        /* UNIFIED PLACEHOLDER COLOR */
        .form-control::placeholder,
        input::placeholder,
        textarea::placeholder {
            color: #adb5bd !important;
            font-weight: 300 !important;
        }
        
        /* UNIFIED OPTION COLOR */
        select option {
            color: #6c757d !important;
            font-weight: 300 !important;
        }
        </style>



<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->