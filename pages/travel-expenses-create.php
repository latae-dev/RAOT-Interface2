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
    <!-- Page Header Close -->

    <!-- รายละเอียดผู้ขอเบิก -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">รายละเอียดผู้ยื่นคำขอ</div>
                </div>
                <div class="card-body px-2 py-4 px-sm-4">
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label">ชื่อแบบฟอร์ม :</label>
                            <input type="text" class="form-control" id="input-label" placeholder="เบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน" disabled>
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
                            <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รหัสหน่วยงาน">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ชื่อ-นามสกุล">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ตำแหน่ง">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ระดับ :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ระดับ">
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">สังกัด :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก สังกัด">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End::row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">รายละเอียดการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="accordion accordion-solid-primary" id="accordionPrimarySolidExample">

                        <!-- Tab รายละเอียดค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPrimarySolidOne">
                                <button class="accordion-button" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapsePrimarySolidOne"
                                    aria-expanded="true"
                                    aria-controls="collapsePrimarySolidOne">รายละเอียดค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน
                                </button>
                            </h2>
                            <div id="collapsePrimarySolidOne" class="accordion-collapse collapse show"
                                aria-labelledby="headingPrimarySolidOne"
                                data-bs-parent="#accordionPrimarySolidExample">
                                <div class="accordion-body">
                                    <div class="row">
                                        <label class="form-label mb-2">เบิกจ่าย กองทุนฯเงินทุนใด ให้ใส่เครื่องหมาย / ในช่อง <span class="text-danger"> *</span> : </label>
                                        <div class="col-xl-3">
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
                                        <div class="col-xl-3">
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
                                        <div class="col-xl-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="" id="other">
                                                <label class="form-check-label mb-1" for="c-1">อื่นๆ</label>
                                                <textarea class="form-control" id="product-description-add" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
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
                                            <div class="col-xl-12 mb-2">
                                                <div class="row">
                                                    <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการปฏิบัติงานสำหรับ <span class="text-danger"> *</span> :</label>
                                                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">ข้าพเจ้า</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                            <label class="form-check-label" for="flexRadioDefault2">คณะเดินทาง</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
                                                    <label class="form-label mb-2">กรุณาเลือกค่าใช้จ่ายในการปฏิบัติงานสำหรับ <span class="text-danger"> *</span> :</label>
                                                    <div class="col-xl-6 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">ข้าพเจ้า</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                            <label class="form-check-label" for="flexRadioDefault2">คณะเดินทาง</label>
                                                        </div>
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
                                                    <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                        <label for="input-label1" class="form-label">การเดินทางไปปฎิบัติงานครั้งนี้ ได้ยืมเงินทดรองจำนวน :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ระยะทาง/กิโลเมตร">
                                                    </div>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <label for="input-label1" class="form-label">รวม :</label>
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 mb-2">
                                                <div class="row">
                                                    <label class="form-label mb-2">ผู้ใช้งานจะต้อง :</label>
                                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                                            <label class="form-check-label" for="flexRadioDefault1">ส่งคืน</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-8 col-lg-4 col-md-4 col-sm-4 mb-2">
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
                                                        <input type="text" class="form-control" id="input-label1" placeholder="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="form-label mb-2">รายละเอียดประกอบการเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน :</label>
                                        <div class="table-responsive mb-2">
                                            <table class="table text-nowrap table-bordered border-success">
                                                <thead class="text-center">
                                                    <tr>
                                                        <td scope="col" rowspan="3" class="bg-dark text-fixed-dark bg-opacity-25">วัน เดือน ปี</td>
                                                        <td scope="col" colspan="2" class="bg-dark text-fixed-dark bg-opacity-25">ออกจาก</td>
                                                        <td scope="col" colspan="2" class="bg-dark text-fixed-dark bg-opacity-25">กลับถึง</td>
                                                        <td scope="col" colspan="2" class="bg-dark text-fixed-dark bg-opacity-25">ค่าพาหนะส่วนตัว</td>
                                                        <td scope="col" rowspan="3" class="bg-dark text-fixed-dark bg-opacity-25">หมายเหตุ</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ที่อยู่/สำนักงาน</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ที่พักแรม</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ระยะทาง</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เงินชดเชย</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เวลา</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เวลา</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เวลา</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เวลา</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">(กม.)</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">บาท</td>
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
                                                        <td><input type="number" class="form-control" type="text" required min="0.1" placeholder="กรุณาระบุ ระยะทาง"></td>
                                                        <td><input type="number" class="form-control" type="text" placeholder="กรุณาระบุ ค่าชดเชยค่าหนะ"></td>
                                                        <td><input class="form-control" type="text" placeholder="กรุณาระบุ หมายเหตุ"></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td scope="col" colspan="6" class="text-center">รวมเงิน</td>
                                                        <td><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                        <td><input class="form-control" type="text" placeholder="กรุณาระบุ หมายเหตุ"> </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab ใบรับรองการจ่ายเงินแทนใบเสร็จรับเงิน -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPrimarySolidTwo">
                                <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapsePrimarySolidTwo"
                                    aria-expanded="false"
                                    aria-controls="collapsePrimarySolidTwo">ใบรับรองการจ่ายเงินแทนใบเสร็จรับเงิน
                                </button>
                            </h2>
                            <div id="collapsePrimarySolidTwo" class="accordion-collapse collapse"
                                aria-labelledby="headingPrimarySolidTwo"
                                data-bs-parent="#accordionPrimarySolidExample">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="table-responsive mb-2">
                                            <table class="table text-nowrap table-bordered border-success">
                                                <thead class="text-center">
                                                    <tr>
                                                        <td scope="col" rowspan="2" class="bg-dark text-fixed-dark bg-opacity-25">วันที่จ่าย</td>
                                                        <td scope="col" colspan="3" class="bg-dark text-fixed-dark bg-opacity-25">รายละเอียด</td>
                                                        <td scope="col" rowspan="2" class="bg-dark text-fixed-dark bg-opacity-25">จำนวนเงิน</td>
                                                        <td scope="col" rowspan="2" class="bg-dark text-fixed-dark bg-opacity-25">หมายเหตุ</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ค่าโดยสาร</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">เริ่มจาก</td>
                                                        <td class="bg-dark text-fixed-dark bg-opacity-10">ถึง</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="product-list">
                                                        <td>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                    <input type="text" class="form-control" id="startDate" placeholder="กรุณากรอก วันที่จ่าย">
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><input class="form-control" type="text"></td>
                                                        <td><textarea class="form-control" id="product-description-add" rows="1"></textarea></td>
                                                        <td><textarea class="form-control" id="product-description-add" rows="1"></textarea></td>
                                                        <td><input class="form-control" type="number" placeholder="ทศนิยม 2 ตำแหน่ง Ex. 0.00"></td>
                                                        <td><input class="form-control" type="text"></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td scope="col" colspan="4" class="text-center">รวมเงิน</td>
                                                        <td><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                                <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                                <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="" id="491">
                                            <label class="form-check-label" for="c-1">ข้าพเจ้าขอรับรองว่ารายจ่ายที่กล่าวไว้ข้างต้น ข้าพเจ้าได้จ่ายไปในนามของการยางแห่งประเทศไทยและข้าพเจ้าได้เรียกเก็บใบสำคัญของผู้รับเงินไว้บ้างตามแต่จะเรียกได้</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingPrimarySolidThree">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapsePrimarySolidThree"
                                                        aria-expanded="false" aria-controls="collapsePrimarySolidThree">
                                                        Accordion Item #3
                                                    </button>
                                                </h2>
                                                <div id="collapsePrimarySolidThree" class="accordion-collapse collapse"
                                                    aria-labelledby="headingPrimarySolidThree"
                                                    data-bs-parent="#accordionPrimarySolidExample">
                                                    <div class="accordion-body">
                                                        <strong>ใบรับรองการจ่ายเงินแทนใบเสร็จรับเงิน.</strong> ใบรับรองการจ่ายเงินแทนใบเสร็จรับเงิน
                                                        <code>.accordion-body</code>, though the transition does limit overflow.
                                                    </div>
                                                </div>
                                            </div> -->
                    </div>
                </div>

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


<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->

<script type="module" src="<?php echo $baseUrl; ?>/pages/js/parcel-doc-list.js"></script>