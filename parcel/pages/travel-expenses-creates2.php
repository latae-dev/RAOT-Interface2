<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));
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
                            <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</h1>
                            <div class="ms-md-1 ms-0">
                                <nav>
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบเงินสดย่อยและเงินทดรองจ่าย</a></li>
                                        <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- รายละเอียดผู้ขอเบิก -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card custom-card">
                                    
                                    <!-- ส่วนหัว รายละเอียดผู้ยื่นคำขอ -->
                                    <div class="card-header">
                                        <div class="card-title">รายละเอียดผู้ยื่นคำขอ</div>
                                    </div>

                                    <!-- รายละเอียดผู้ยื่นคำขอ -->
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-xl-3 mb-2">
                                                <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                                                <input type="text" class="form-control" id="input-label" placeholder="เบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน" disabled>
                                            </div>
                                            <div class="col-xl-3 mb-2">
                                                <label for="publish-date" class="form-label">วันที่เริ่ม  <span class="text-danger"> *</span> :</label>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เริ่ม">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xl-3 mb-2">
                                                <label class="form-label">วันที่สิ้นสุด  <span class="text-danger"> *</span> :</label>
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

                                    <!-- ส่วนหัว รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน-->
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</div>
                                    </div>

                                    <!-- เบิกจ่ายจาก กองทุนฯ/เงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง -->
                                    <div class="card-body border-bottom">
                                        <div class="row">
                                            <label class="form-label mb-2">เบิกจ่ายจาก กองทุนฯ/เงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง <span class="text-danger"> *</span> : </label>
                                            <div class="col-xl-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="491">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อการบริหาร 49(1) งบ (21)</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="492">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนการปลูกแทน 49(2) งบ (24)</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="493">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อการสนับสนุนเกษตรกร 49(3) งบ (25)</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="494">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อการศึกษาวิจัยยางพารา 49(4) งบ (26)</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="495">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อสวัสดีการเกษตรกร 49(5) งบ (27)</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="496">
                                                    <label class="form-check-label" for="c-1">เงินทุนเพื่อสนับสนุนสถาบันเกษตกร 46(6) งบ (28)</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="para">
                                                    <label class="form-check-label" for="c-1">กองทุนพัฒนายางพารา  งบ (23)</label>
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="" id="other">
                                                    <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                    <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- เลขที่คำสั่ง/บันทึกที่ -->
                                    <div class="card-body border-bottom">
                                        <div class="row">
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-3 mb-2">
                                                        <label for="input-label1" class="form-label">เลขที่คำสั่ง/บันทึกที่ <span class="text-danger"> *</span> :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก เลขที่คำสั่ง/บันทึกที่">
                                                    </div>
                                                    <div class="col-xl-3 mb-2">
                                                        <label for="input-label1" class="form-label">ลงวันที่ที่ <span class="text-danger"> *</span> :</label>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                <input type="text" class="form-control flatpickr-input active" id="date" placeholder="ระบุวัน เดือน ปี" readonly="readonly" fdprocessedid="jp9fs9">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-4 mb-2">
                                                        <div class="row">
                                                            <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการเดินทางไปปฏิบัติงานสำหรับ <span class="text-danger"> *</span> :</label>
                                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="noCheck">
                                                                    <label class="form-check-label" for="no">ข้าพเจ้า</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="yesCheck">
                                                                    <label class="form-check-label" for="yes">ข้าพเจ้าพร้อมคณะเดินทาง</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 mb-2">
                                                        <label for="input-label1" class="form-label">จำนวนคณะเดินทาง :</label>
                                                        <input type="text" class="form-control"  placeholder="รวมผล จำนวนตารางหลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- สถานที่เริ่มต้น-สิ้นสุด -->
                                    <div class="card-body border-bottom">
                                        <div class="row">                                                                
                                            <div class="col-xl-6">
                                                <form>
                                                    <div class="row">
                                                        <label class="form-label mb-2">กรุณาเลือกสถานที่เริ่มต้น <span class="text-danger"> *</span> :</label>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                <label class="form-check-label" for="flexRadioDefault1">ที่อยู่</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                <label class="form-check-label" for="flexRadioDefault2">สำนักงาน</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                                <label class="form-check-label" for="flexRadioDefault2">ประเทศไทย</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 mb-2">
                                                            <label class="form-label mb-2">วันที่เดินทาง <span class="text-danger"> *</span> :</label>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เดินทาง">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 mb-2">
                                                            <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                                            <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-xl-6">
                                                <form>
                                                    <div class="row">
                                                        <label class="form-label mb-2">กรุณาเลือกสถานที่กลับ <span class="text-danger"> *</span> : </label>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                                <label class="form-check-label" for="flexRadioDefault1">ที่อยู่</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                <label class="form-check-label" for="flexRadioDefault2">สำนักงาน</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                                <label class="form-check-label" for="flexRadioDefault2">ประเทศไทย</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 mb-2">
                                                            <label class="form-label mb-2">วันที่เดินทาง <span class="text-danger"> *</span> :</label>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่เดินทาง">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 mb-2">
                                                            <label class="form-label mb-2">รายละเอียดที่อยู่/สำนักงาน</label>
                                                            <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ -->
                                    <div class="card-body border-bottom" id="ifYes" style="display:none">
                                        <div class="row">
                                            <label class="form-label mb-2">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปปฏิบัติงานกรณีเป็นหมู่คณะ :</label>
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered border-success">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ลำดับที่</td>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ชื่อ</td>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">ตำแหน่ง/ระดับ</td>
                                                            <td scope="col" colspan="9" class="bg-info text-fixed-dark bg-opacity-25">ค่าใช้จ่าย (บาท)</td>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">รวม (บาท)</td>
                                                        </tr>
                                                        <tr>
                                                            <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยง</td>
                                                            <td scope="col" colspan="3" class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พัก</td>
                                                            <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าพาหนะ</td>
                                                            <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">ค่าใช้จ่ายอื่นๆ</td>
                                                            <td scope="col" rowspan="2" class="bg-info text-fixed-dark bg-opacity-10">หักค่าอาหารและค่าฝึกอบรม</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเบี้ยเลี้ยง</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">วัน</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">รวม</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ค่าเช่าที่พัก</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">วัน</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">รวม</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="product-list">
                                                            <td><input class="form-control" type="text" placeholder="Run Number Auto 1+" disabled></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ชื่อ"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ตำแหน่ง/ระดับ"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ค่าเบี้ยเลี้ยง"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ วัน"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ รวม ค่าเบี้ยเลี้ยง"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ค่าเช่าที่พัก"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ วัน"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ รวม ค่าเช่าที่พัก"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ค่าพาหนะ"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ ค่าใช้จ่ายอื่นๆ"></td>
                                                            <td><input class="form-control" type="text" placeholder="ระบุ หักค่าอาหารและค่าฝึกอบรม"></td>
                                                            <td><input class="form-control" type="text" placeholder="0.00" disabled></td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td scope="col" colspan="12" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                                            <td><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                        </tr>
                                                        <tr>
                                                            <td scope="col" colspan="12" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                            <td><input class="form-control" type="number" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                        </tr>
                                                    </tfoot>
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
                                    
                                    <!-- รายละเอียด ค่าเบี้ยเลี้ยง ค่าเช่าที่พัก ค่าพาหนะ ค่าใช้จ่ายอื่นๆ หักค่าอาหารฝึกอบรม-->
                                    <div class="card-body border-bottom">
                                        <div class="row">                                                                
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">ค่าเบี้ยเลี้ยง/บาท :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ค่าเบี้ยเลี้ยง/บาท">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">จำนวน/วัน :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก จำนวน/วัน">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">รวม :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">ค่าเช่าที่พักประเภท :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ค่าเช่าที่พักประเภท">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">จำนวน/วัน :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก จำนวน/วัน">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวม :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                        <label for="input-label1" class="form-label">ค่าพาหนะ :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ค่าพาหนะ">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวม :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                        <label for="input-label1" class="form-label">ค่าขนย้ายสิ่งของส่วนตัว ระยะทาง/กิโลเมตร :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ระยะทาง/กิโลเมตร">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวม :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 mb-2">
                                                <div class="row">
                                                    <label class="form-label mb-2">กรุณาเลือก กรณีใช้ยานพาหนะส่วนตัว :</label>
                                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">รถยนต์</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">รถจักรยานยนต์</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <label for="input-label1" class="form-label">หมายเลขทะเบียน :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก หมายเลขทะเบียน">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                        <label for="input-label1" class="form-label">เงินชดเชย (ตามรายละเอียดประกอบการ เบิกค่าใช้จ่ายในการเดินทางไปปฎิบัติงาน) :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก เงินชดเชย">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวม :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                            <label for="input-label1" class="form-label">ค่าใช้จ่ายอื่นๆ :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ประเภทค่าใช้จ่ายที่ต้องการเบิก">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">รวม :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">หักค่าอาหารระหว่างการฝึกอบรม จำนวน/มื้อ :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ค่าอาหารระหว่างการฝึกอบรม จำนวน/มื้อ">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">มื้อละ/บาท :</label>
                                                            <input type="number" class="form-control" id="input-label1" placeholder="กรุณากรอก มื้อละ/วัน">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                            <label for="input-label1" class="form-label">รวม :</label>
                                                            <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                        <label class="form-label mb-2">หมายเหตุ :</label>
                                                        <textarea class="form-control" id="product-description-add" rows="1"></textarea>
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวมทั้งสิ้น :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row justify-content-end">
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวมทั้งสิ้น : (รูปแบบตัวอักษร) </label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="ศูนย์บาทศูนย์สตางค์" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">เลขที่สัญญาคำขอเบิกเงินทดรอง :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก เลขที่สัญญาคำขอเบิกเงินทดรอง">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">ลงวันที่ :</label>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                <input type="text" class="form-control flatpickr-input active" id="date" placeholder="กรุณาระบุ วันที่" readonly="readonly" fdprocessedid="jp9fs9">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">จำนวนเงิน :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00 หลักการถ้าพิมม์เลขสัญญาคำขอเบิกเงินทดรอง ให้ดึงจำนวนเงินมาแสดง" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                <div class="row">
                                                    <label class="form-label mb-2">ผู้ใช้งานจะต้อง :</label>
                                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">ส่งคืน</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">เบิกเพิ่ม</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2 mb-2">
                                                <div class="row">
                                                    <div class="col-xl-12 mb-2">
                                                        <label for="input-label1" class="form-label">จำนวนเงิน :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                                    <div class="card-body border-bottom">
                                        <div class="row">
                                            <label class="form-label mb-2">รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน :</label>
                                            <div class="table-responsive mb-2">
                                                <table class="table text-nowrap table-bordered border-success">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">วัน เดือน ปี</td>
                                                            <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                            <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                            <td scope="col" colspan="2" class="bg-info text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                            <td scope="col" rowspan="3" class="bg-info text-fixed-dark bg-opacity-25">หมายเหตุ</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">ระยะทาง</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">เงินชดเชย</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">เวลา</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">(กม.)</td>
                                                            <td class="bg-info text-fixed-dark bg-opacity-10">บาท</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="product-list">
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                        <input type="text" class="form-control flatpickr-input active" id="date" placeholder="ระบุวัน เดือน ปี" readonly="readonly" fdprocessedid="jp9fs9">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                                        <input type="text" class="form-control flatpickr-input active" id="timepickr1" placeholder="ระบุเวลา" readonly="readonly" fdprocessedid="j8t7va">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                                        <input type="text" class="form-control flatpickr-input active" id="timepickr1" placeholder="ระบุเวลา" readonly="readonly" fdprocessedid="j8t7va">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                                        <input type="text" class="form-control flatpickr-input active" id="timepickr1" placeholder="ระบุเวลา" readonly="readonly" fdprocessedid="j8t7va">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                                        <input type="text" class="form-control flatpickr-input active" id="timepickr1" placeholder="ระบุเวลา" readonly="readonly" fdprocessedid="j8t7va">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><input type="number" class="form-control" type="text" required min ="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
                                                            <td><input type="number" class="form-control" type="text" placeholder="กรุณาระบุ ค่าชดเชยค่าหนะ"></td>
                                                            <td><input class="form-control" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td scope="col" colspan="6" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                                            <td scope="col" colspan="2"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                        </tr>
                                                        <tr>
                                                            <td scope="col" colspan="6" class="text-end">จำนวนเงินทั้งสิ้น (ตัวอักษร)</td>
                                                            <td scope="col" colspan="2"><input class="form-control" type="number" placeholder="ศูนย์บาทศูนย์สตางค์" disabled></td>
                                                        </tr>
                                                    </tfoot>
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

                                    <!-- ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง -->
                                    <div class="card-body border-bottom">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="491">
                                                    <label class="form-check-label" for="c-1">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง  <span class="text-danger"> *</span> </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- ส่วนท้าย การขออนุมัติ -->
                                    <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                                        <a href="withdraw-money-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                                        <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class="bi bi-save"></i> ขออนุมัติ</button>
                                    </div>
                                </div>
                            </div>
                        </div>

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

        <script type="text/javascript">

            function yesnoCheck() {
                if (document.getElementById('yesCheck').checked) {
                    document.getElementById('ifYes').style.display = 'block';
                }
                else document.getElementById('ifYes').style.display = 'none';

            }

        </script>

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
        
        <!-- DATE & TIME PICKER JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo $baseUrl; ?>/assets/js/date&time_pickers.js"></script>


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->