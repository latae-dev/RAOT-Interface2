<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->
<?php
$rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages-raot', '', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = 'http://localhost:8080/'; // Use this line for local development
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
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone.css">

<!-- PRISM CSS -->
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/libs/prismjs/themes/prism-coy.min.css">

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


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มคําของบประมาณรายจ่ายประจําปี</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคําของบประมาณรายจ่ายประจําปี</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคําของบประมาณรายจ่ายประจําปี</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <!-- ส่วนหัว รายละเอียดผู้ยื่นคําของบประมาณรายจ่ายประจําปี -->
                <div class="card-header">
                    <div class="card-title">รายละเอียดผู้ยื่นคำขอ</div>
                </div>

                <!-- รายละเอียดผู้ยื่นคําของบประมาณรายจ่ายประจําปี -->
                <div class="card-body">

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
                            <input type="text" class="form-control" id="input-label11" placeholder="ดึงข้อมูลมาแสดง" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ชื่อ-นามสกุล :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ตำแหน่ง :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">ระดับ :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label1" class="form-label">สังกัด :</label>
                            <input type="text" class="form-control" id="input-label1" placeholder="ดึงข้อมูลมาแสดง" disabled>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">

            <!-- ส่วนที่ 1 : ข้อมูลทั่วไป -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ส่วนที่ 1 : ข้อมูลทั่วไป</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">ปีงบประมาณ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="2568">2568</option>
                                <option value="2569">2569</option>
                                <option value="2570">2570</option>
                            </select>
                        </div>
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">เวอร์ชั่น (แผน):</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="s-1">ระดับสาขา</option>
                                <option value="s-2">ระดับจังหวัด/กอง</option>
                                <option value="s-2">ระดับเขต/ฝ่าย</option>
                                <option value="s-2">ระดับผู้รับผิดชอบหลัก</option>
                                <option value="s-2">ระดับฝ่ายยุทธ์ศาสตร์</option>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label class="form-label mt-2">ประเภทโครงการ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="s-1">โครงการเดิม</option>
                                <option value="s-2">โครงการใหม่</option>
                            </select>
                        </div>


                        <!-- โครงการเดิม -->
                        <!-- <div class="col-xl-3 mb-2">
                                            <label class="form-label mt-2">ลักษณะโครงการ :</label>
                                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                                <option value="">กรุณาเลือก</option>
                                                <option value="s-1">โครงการระยะสั้น</option>
                                                <option value="s-2">โครงการต่อเนื่อง</option>
                                            </select>
                                        </div>  -->
                        <!-- โครงการเดิม >> โครงการระยะสั้น-->
                        <!-- <div class="col-xl-6 mb-2">
                                            <label class="form-label mt-2">โครงการระยะสั้น</label>
                                            <textarea class="form-control" id="product-description-add" style="color:red;">มีระยะเวลาดําเนินงานไม่เกิน 1 ปี โดยมีการปรับปรุงโครงการเพื่อเสนอของบประมาณครั้งใหม่ มีวัตถุประสงค์ เป้าหมาย กิจกรรม ขั้นตอน วิธีการ ใกล้เคียงกับโครงการที่เคยดําเนินงาน</textarea>
                                        </div>  -->
                        <!-- โครงการเดิม >> โครงการต่อเนื่อง-->
                        <!-- <div class="col-xl-6 mb-2">
                                            <div class="row">
                                                <div class="col-xl-2 mb-2">
                                                    <label class="form-label mt-2">ตั้งแต่ :</label>
                                                    <input type="text" class="form-control" id="input-label1">
                                                </div> 
                                                <div class="col-xl-2 mb-2">
                                                    <label class="form-label mt-2">ไปจนถึง :</label>
                                                    <input type="text" class="form-control" id="input-label1">
                                                </div>
                                                <div class="col-xl-2 mb-2">
                                                    <label class="form-label mt-2">ปีนี้เป็นปีที่ :</label>
                                                    <input type="text" class="form-control" id="input-label1">
                                                </div>  
                                                <div class="col-xl-6 mb-2">
                                                    <label class="form-label mt-2">หมายเหตุ</label>
                                                    <textarea class="form-control" id="product-description-add" row="1" style="color:red;" font-size:12px;">คือ โครงการที่ มีลักษณะการดําเนินงานเป็นขั้นตอนต่อเนื่องกัน ไม่สามารถดําเนินการให้ สิ้นสุดได้ภายใน 1 ปี</textarea>
                                                </div>                           
                                            </div>                            
                                        </div>  -->

                        <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ -->
                        <div class="col-xl-9 mb-2">
                            <div class="row">
                                <div class="col-xl-4 mb-2">
                                    <label class="form-label mt-2">ระยะเวลาดําเนินการ/ปี :</label>
                                    <input type="text" class="form-control" id="input-label1">
                                </div>
                                <div class="col-xl-8 mb-2">
                                    <label class="form-label mt-2">หมายเหตุ</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" disabled>คือ โครงการที่ไม่เคยได้รับงบประมาณมาก่อน อาจมีระยะเวลาสิ้นสุดโครงการภายใน 1 ปี หรือมากกว่า 1 ปี</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน -->
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">ประเภทแผนงาน :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="s-1">แผนงานด้านธุรกิจ</option>
                                <option value="s-2">แผนงานบริหารทั่วไป</option>
                                <option value="s-2">แผนงานขับเคลื่อนของยุทธศาสตร์ กยท.</option>
                                <option value="s-2">แผนงานพื้นฐานสนับสนุนการดำเนินงานของ กยท.</option>
                                <option value="s-2">แผนงานตามนโยบายภาครัฐ เงินอุดหนุนรัฐบาล</option>
                            </select>
                        </div>

                        <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน >> ชื่อโครงการ -->
                        <!-- <div class="col-xl-6 mb-2">
                                            <label class="form-label mt-2">ชื่อโครงการ :</label>
                                            <input type="text" class="form-control" id="input-label1">                            
                                        </div>  -->

                        <!-- โครงการเดิม >> ลักษณะโครงการ >> ประเภทแผนงาน >> ชื่อโครงการ -->
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">ชื่อโครงการ :</label>
                            <select class="custom-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="010101">บริหารจัดการโรงอบ/รมยาง โรงเรือน โรงอัดก้อนยาง อุปกรณ์อัดก้อน และโกดังเก็บยาง</option>
                                <option value="010109">จัดสวัสดิการเกษตรกรชาวสวนยาง</option>
                                <option value="010110">ปรับปรุงประสิทธิภาพการผลิตให้มีศักยภาพและเป็นมิตรต่อสิ่งแวดล้อม (BCG)</option>
                                <option value="010111">ส่งเสริม สนับสนุน และให้ความช่วยเหลือเกษตรกรชาวสวนยางเพื่อการปลูกแทน</option>
                                <option value="010112">ส่งเสริมและพัฒนาสถาบันเกษตรกรชาวสวนยางและ</option>
                                <option value="010113">ขับเคลื่อนเศรษฐกิจเพื่อความยั่งยืน (BCG)</option>
                                <option value="010124">โฉนดต้นยางพาราเพื่อเป็นหลักประกันสินเชื่อ</option>
                                <option value="010203">ขับเคลื่อนเพื่อเพิ่มศักยภาพการจัดการสวนยางตามหลัก GAP</option>
                                <option value="010204">การพัฒนาการผลิต และการแปรรูปของเกษตรกร สถาบันเกษตรกรให้เข้าสู่มาตรฐาน </option>
                                <option value="010305">สนับสนุนเครือข่ายตลาดกลางยางพารา</option>
                                <option value="010307">เงินให้กู้ยืมและเงินอุดหนุน ตามมาตรา 49 (3)</option>
                            </select>
                        </div>

                        <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน >> ชื่อกิจกรรม -->
                        <!-- <div class="col-xl-6 mb-2">
                                            <label class="form-label mt-2">ชื่อกิจกรรม :</label>
                                            <input type="text" class="form-control" id="input-label1">                            
                                        </div> -->

                        <!-- โครงการใหม่ >> ลักษณะโครงการ >> ประเภทแผนงาน >> ชื่อกิจกรรม -->
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">ชื่อกิจกรรม :</label>
                            <select class="form-control" data-trigger name="choices-multiple-default" id="choices-multiple-default" multiple>
                                <option value="ex Choice 1">ตัวอย่าง กิจกรรม แผนปฏิบัติการจัดสวัสดิการเพื่อเกษตรกรชาวสวนยาง</option>
                                <option value="ex Choice 2">ตัวอย่าง กิจกรรม แผนปฏิบัติการส่งเสริมและพัฒนาอาชีพให้เกษตรกรชาวสวนยาง</option>
                                <option value="ex Choice 3">ตัวอย่าง กิจกรรม การสนับสนุนด้านอื่นๆ</option>
                            </select>
                        </div>
                        <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> แหล่งงบประมาณ -->
                        <div class="col-xl-6 mb-2">
                            <label class="form-label mt-2">แหล่งงบประมาณ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="G1013">G1013 งบแผ่นดิน งบทำการ มาตรา 13</option>
                                <option value="G1491">G1491 งบแผ่นดิน งบทำการ มาตรา 49(1)</option>
                                <option value="G1492">G1492 งบแผ่นดิน งบทำการ มาตรา 49(2)</option>
                                <option value="G1493">G1493 งบแผ่นดิน งบทำการ มาตรา 49(3)</option>
                                <option value="G1494">G1494 งบแผ่นดิน งบทำการ มาตรา 49(4)</option>
                                <option value="G1495">G1495 งบแผ่นดิน งบทำการ มาตรา 49(5)</option>
                                <option value="G1496">G1496 งบแผ่นดิน งบทำการ มาตรา 49(6)</option>
                                <option value="R1013">R1013 งบ กยท. งบทำการ มาตรา 13</option>
                                <option value="R1491">R1491 งบ กยท. งบทำการ มาตรา 49(1)</option>
                                <option value="R1492">R1492 งบ กยท. งบทำการ มาตรา 49(2)</option>
                                <option value="R1493">R1493 งบ กยท. งบทำการ มาตรา 49(3)</option>
                                <option value="R1494">R1494 งบ กยท. งบทำการ มาตรา 49(4)</option>
                                <option value="R1495">R1495 งบ กยท. งบทำการ มาตรา 49(5)</option>
                                <option value="R1496">R1496 งบ กยท. งบทำการ มาตรา 49(6)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ส่วนที่ 2 : ความเชื่อมโยงกับแผนงานขับเคลื่อนยุทธศาสตร์ของ กยท. -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ส่วนที่ 2 : ความเชื่อมโยงกับแผนงานขับเคลื่อนยุทธศาสตร์ของ กยท.</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- 2.1 ยุทธศาสตร์ชาติ 20 ปี -->
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-6 mb-2">
                                    <label for="input-label" class="form-label">2.1 ยุทธศาสตร์ชาติ 20 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.1.1 เป้าหมาย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-3 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.1.2 ประเด็น :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2.2 แผนแม่บทภายใต้ยุทธศาสตร์ชาติ (Y) ประเด็น -->
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-6 mb-2">
                                    <label for="input-label" class="form-label">2.2 แผนแม่บทภายใต้ยุทธศาสตร์ชาติ (Y) ประเด็น :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.2.1 เป้าหมายระดับประเด็น (Y2) :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.2.2 แผนย่อยของแผนแม่บทฯ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.2.4 เป้าหมายแผนแม่บทย่อย (Y1) :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2.3 แผนวิสาหกิจการยางแห่งประเทศไทย พ.ศ. 2566 - 2570 (ฉบับทบทวนปี 2570) -->
                        <div class="col-xl-12 mb-2">
                            <div class="row">
                                <div class="col-xl-6 mb-2">
                                    <label for="input-label" class="form-label">2.3 แผนวิสาหกิจการยางแห่งประเทศไทย พ.ศ. 2566 - 2570 (ฉบับทบทวนปี 2570) :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.3.1 ยุทธศาสตร์ที่ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.3.2 ตัวชี้วัดตามยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 mb-2">
                                    <label for="input-label" class="form-label fw-normal">2.3.3 กลยุทธ์ที่ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">ตัวอย่าง</option>
                                        <option value="2569">ตัวอย่าง</option>
                                        <option value="2570">ตัวอย่าง</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ส่วนที่ 3: รายละเอียดแผนงาน/โครงการ/กิจกรรม -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ส่วนที่ 3: รายละเอียดแผนงาน/โครงการ/กิจกรรม</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <!-- 3.1 หลักการและเหตุผล (อธิบายที่มาและความสําคัญของโครงการ โดยมีความสอดคล้องกับ ส่วนที่ 2 และมีข้อมูลเชิงประจักษ์ เช่น สถิติ/ข้อเท็จจริง รวมทั้งแสดงถึงผลลัพธ์ที่จะทําให้โครงการบรรลุ เป้าหมายได้)(ไฟล์ PDF): -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.1 หลักการและเหตุผล (อธิบายที่มาและความสําคัญของโครงการ โดยมีความสอดคล้องกับ ส่วนที่ 2 และมีข้อมูลเชิงประจักษ์ เช่น สถิติ/ข้อเท็จจริง รวมทั้งแสดงถึงผลลัพธ์ที่จะทําให้โครงการบรรลุ เป้าหมายได้)(ไฟล์ PDF): </label>
                            <input class="form-control" type="file" id="formFile">
                        </div>

                        <!-- 3.2 วัตถุประสงค์ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.2 วัตถุประสงค์ :</label>
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th scope="col"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th>
                                            <th scope="col" class="fw-normal">รายละเอียดวัตถุประสงค์ แผนงาน/โครงการ/กิจกรรม</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <tr class="product-list">
                                            <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                            <td contenteditable="true" style="text-align: left; width:95%"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3.3 ตัวชี้วัดความสำเร็จของโครงการ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.3 ตัวชี้วัดความสำเร็จของโครงการ :</label>
                        </div>
                        <div class="col-xl-12 mb-2">
                            <!-- 3.3.1 ผลผลิต (Output) -->
                            <div class="border p-3">
                                <label for="input-label" class="form-label fw-normal mt-2">3.3.1 ผลผลิต (Output) :</label>
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead class="text-center">
                                            <tr>
                                                <th scope="col" style="width:50px;">
                                                    <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                </th>
                                                <th colspan="2" scope="col" class="fw-normal" fdprocessedid="n9l8l6">ตัวชี้วัด</th>
                                                <th scope="col" class="fw-normal">ค่าเป้าหมาย</th>
                                                <th scope="col" class="fw-normal">หน่วยนับ</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td class="fw-normal" style="width:15%">ผลผลิต (Output) :</td>
                                                <td contenteditable="true" style="text-align: left; width:50%"></td>
                                                <td contenteditable="true" style="width:15%"></td>
                                                <td contentEditable="true" style="width:15%"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                        <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                        <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3.3.2 ผลลัพธ์ (Outcome) : -->
                            <div class="border p-3">
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label fw-normal mt-2">3.3.2 ผลลัพธ์ (Outcome) : </label>
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th scope="col" style="width:50px;">
                                                        <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                    </th>
                                                    <th colspan="2" scope="col" class="fw-normal">ตัวชี้วัด</th>
                                                    <th scope="col" class="fw-normal">ค่าเป้าหมาย</th>
                                                    <th scope="col" class="fw-normal">หน่วยนับ</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                <tr class="product-list">
                                                    <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td class="fw-normal" style="width:15%">ผลผลิต (Outcome) :</td>
                                                    <td contenteditable="true" style="text-align: left; width:50%"></td>
                                                    <td contenteditable="true" style="width:15%"></td>
                                                    <td contentEditable="true" style="width:15%"></td>
                                                </tr>
                                            </tbody>
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

                        <!-- 3.4 กลุ่มเป้าหมาย / ผู้ที่ได้รับประโยชน์ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.4 กลุ่มเป้าหมาย / ผู้ที่ได้รับประโยชน์ :</label>
                            <div class="col-xl-12 mb-2">
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead class="text-center">
                                            <tr>
                                                <th scope="col" style="width:50px;">
                                                    <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                </th>
                                                <th scope="col" class="fw-normal">กลุ่มเป้าหมาย</th>
                                                <th scope="col" class="fw-normal">จำนวน</th>
                                                <th scope="col" class="fw-normal">พื้นที่ของกลุ่มเป้าหมาย</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contentEditable="true" style="width:45%"></td>
                                                <td contentEditable="true" style="width:20%"></td>
                                                <td contentEditable="true" style="width:30%"></td>
                                            </tr>
                                        </tbody>
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

                        <!-- 3.5 ประโยชน์ที่คาดว่าจะได้รับ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.5 ประโยชน์ที่คาดว่าจะได้รับ :</label>
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th scope="col" style="width:50px;">
                                                <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                            </th>
                                            <th scope="col" class="fw-normal">รายละเอียดประโยชน์ที่คาดว่าจะได้รับ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <tr class="product-list">
                                            <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                            <td contentEditable="true" style="text-align: left; width:95%;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3.6 สถานที่ดําเนินการ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.6 สถานที่ดําเนินการ :</label>
                            <input type="text" class="form-control" id="input-label1">
                        </div>

                        <!-- 3.7 ระยะเวลาดําเนินการ -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.7 ระยะเวลาดําเนินการ :</label>
                            <input type="text" class="form-control" id="input-label1">
                        </div>

                        <!-- 3.8 ทรัพยากรที่ใช้ในการดําเนินงาน -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">3.8 ทรัพยากรที่ใช้ในการดําเนินงาน :</label>
                        </div>
                        <div class="col-xl-12 mb-2">
                            <div class="border p-3">
                                <!-- 3.8.1 งบประมาณ -->
                                <div class="row gy-2 gx-3 align-items-center mb-2">
                                    <div class="col-auto">
                                        <label for="input-label" class="form-label fw-normal mt-2">3.8.1 งบประมาณ :</label>
                                    </div>
                                    <div class="col-auto">
                                        <label class="visually-hidden" for="autoSizingInputGroup" disabled></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="autoSizingInputGroup" placeholder="" fdprocessedid="q6asdu">
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <label class="form-check-label" for="autoSizingCheck">บาท (ประมาณการค่าใช้จ่ายของโครงการ)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- รายละเอียดกิจกรรม -->
                                <div class="table-responsive mb-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="8" scope="col" class="fw-normal">รายละเอียดกิจกรรม</th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="fw-normal">กิจกรรม</th>
                                                <th scope="col" class="fw-normal"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></th>
                                                <th scope="col" class="fw-normal">ลำดับ</th>
                                                <th scope="col" class="fw-normal">รายการ</th>
                                                <th scope="col" class="fw-normal">จำนวน (หน่วย)</th>
                                                <th scope="col" class="fw-normal">ราคาต่อหน่วย</th>
                                                <th scope="col" class="fw-normal">จำนวนเงิน (บาท)</th>
                                                <th scope="col" class="fw-normal">รวม</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td contenteditable="true" style="text-align: left; width:35%" rowspan="2"></td>
                                                <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%" rowspan="2"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td contenteditable="true" style="text-align: left; width:35%" rowspan="2"></td>
                                                <td class="product-checkbox" style="width:5%"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%" rowspan="2"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                                <td><input class="form-control" type="number" placeholder="0.00" disabled></td>
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

                                <!-- ตารางรายละเอียดค่าใช้จ่ายในการเดินทาง -->
                                <div class="table-responsive mt-3 mb-2">
                                    <table class="table text-nowrap table-bordered">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="16" scope="col" class="fw-normal">ตารางรายละเอียดค่าใช้จ่ายในการเดินทาง</th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" scope="col" style="width:5%"></th>
                                                <th rowspan="2" scope="col" class="fw-normal">แผนการเดินทาง</th>
                                                <th rowspan="2" scope="col" class="fw-normal">เจ้าหน้าที่ ตําแหน่ง/ระดับ</th>
                                                <th colspan="2" scope="col" class="fw-normal">จำนวน</th>
                                                <th colspan="2" scope="col" class="fw-normal">เบี้ยเลี้ยง</th>
                                                <th colspan="2" scope="col" class="fw-normal">ค่าพาหนะ</th>
                                                <th colspan="3" scope="col" class="fw-normal">ค่าที่พัก</th>
                                                <th colspan="3" scope="col" class="fw-normal">วัสดุเชื้อเพลิงและหล่อลื่น</th>
                                                <th rowspan="2" scope="col" class="fw-normal" style="width:150px;">รวมเงิน</th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="fw-normal">คน</th>
                                                <th scope="col" class="fw-normal">วันทำงาน</th>
                                                <th scope="col" class="fw-normal">อัตรา</th>
                                                <th scope="col" class="fw-normal">จำนวนเงิน</th>
                                                <th scope="col" class="fw-normal">อัตรา</th>
                                                <th scope="col" class="fw-normal">จำนวนเงิน</th>
                                                <th scope="col" class="fw-normal">จำนวนวัน</th>
                                                <th scope="col" class="fw-normal">อัตรา</th>
                                                <th scope="col" class="fw-normal">จำนวนเงิน</th>
                                                <th scope="col" class="fw-normal">จำนวนเที่ยว</th>
                                                <th scope="col" class="fw-normal">อัตรา</th>
                                                <th scope="col" class="fw-normal">จำนวนเงิน</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td colspan="17" class="fw-normal text-start">กิจกรรม กำหนดการจัดประชุมคณะทำงานแก้ไขปัญหาโรงอบ/รมยางฯ ของ กยท. และลงพื้นที่ ติดตามผลการดำเนินงาน</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td colspan="17" class="fw-normal text-start">กิจกรรม กำหนดการจัดประชุมคณะทำงานแก้ไขปัญหาโรงอบ/รมยางฯ ของ กยท. และลงพื้นที่ ติดตามผลการดำเนินงาน</td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                                <td contenteditable="true"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td scope="col" colspan="3" class="text-end fw-normal">จำนวนเงินทั้งสิ้น</td>
                                                <td colspan="2"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                <td colspan="2"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                <td colspan="2"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                <td colspan="3"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                <td colspan="3"><input class="form-control" type="number" placeholder="0.00" disabled></td>
                                                <td><input class="form-control" type="number" placeholder="0.00" disabled></td>
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

                                <!-- 3.8.2 ทรัพยากรบุคคลที่ใช้ในการดําเนินงาน -->
                                <div class="row gy-2 gx-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="input-label" class="form-label fw-normal mt-2">3.8.2 ทรัพยากรบุคคลที่ใช้ในการดําเนินงาน :</label>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-bordered mb-2">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" scope="col" class="fw-normal">รายการ</th>
                                                <th colspan="3" scope="col" class="fw-normal">จํานวนบุคลากรที่ใช้ในโครงการ</th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="fw-normal">ที่มีอยู่แล้ว </th>
                                                <th scope="col" class="fw-normal">ที่ต้องการเพิ่มเติม</th>
                                                <th scope="col" class="fw-normal">เหตุผลที่ต้องการ เพิ่มเติมและ ผลกระทบหากไม่ได้ บุคลากรเพิ่มเติม</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td style="text-align: left; width:40%" class="fw-normal">1. จํานวนบุคลากร (คน)</td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td style="text-align: left; width:40%" class="fw-normal">2. วุฒิ/สาขา ที่ต้องการ</td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td style="text-align: left; width:40%" class="fw-normal">3. ความรู้/ทักษะ/ความสามารถที่จำเป็นความต้องการด้านหลักสูตรการอบรม สำหรับโครงการ</td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 3.8.3 ทรัพยากรทางด้านเทคโนโลยีดิจิทัลที่ใช้ในการดําเนินงาน -->
                                <div class="row gy-2 gx-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="input-label" class="form-label fw-normal mt-2">3.8.3 ทรัพยากรทางด้านเทคโนโลยีดิจิทัลที่ใช้ในการดําเนินงาน :</label>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-bordered mb-2">
                                        <thead class="text-center">
                                            <tr>
                                                <th rowspan="2" scope="col" class="fw-normal">รายการ</th>
                                                <th colspan="3" scope="col" class="fw-normal" contentEditable="true" data-bs-toggle="modal" data-bs-target="#add-board">ปี พ.ศ.</th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="fw-normal">ที่มีอยู่แล้ว </th>
                                                <th scope="col" class="fw-normal">ที่ต้องการเพิ่มเติม</th>
                                                <th scope="col" class="fw-normal">เหตุผลที่ต้องการ เพิ่มเติมและ ผลกระทบหากไม่ได้ บุคลากรเพิ่มเติม</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr class="product-list">
                                                <td style="text-align: left; width:40%" class="fw-normal">1. ด้าน SOFTWARE (ระบุ ระบบงาน/โปรแกรมที่ใช้ใน การดําเนินงาน)</td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                            </tr>
                                            <tr class="product-list">
                                                <td style="text-align: left; width:40%" class="fw-normal">2. ด้าน HARDWARE (ระบุ คอมพิวเตอร์และอุปกรณ์ คอมพิวเตอร์)</td>
                                                <td contentEditable="true" style="text-align: left; width:5%"></td>
                                                <td contenteditable="true" style="text-align: left; width:15%"></td>
                                                <td contenteditable="true" style="text-align: left; width:10%"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ส่วนที่ 4: แผนการดําเนินงานและการวิเคราะห์ความเสี่ยง -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ส่วนที่ 4: แผนการดําเนินงานและการวิเคราะห์ความเสี่ยง</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- 4.1 แผนการดําเนินงาน (ให้ระบุกิจกรรม/ขั้นตอนย่อยทั้งหมด) :  -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">4.1 แผนการดําเนินงาน (ให้ระบุกิจกรรม/ขั้นตอนย่อยทั้งหมด) : </label>
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th rowspan="2" scope="col" style="width:5%"></th>
                                            <th rowspan="2" scope="col" class="fw-normal">กิจกรรม</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ค่าน้ำหนัก</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ค่าเป้าหมาย</th>
                                            <th rowspan="2" scope="col" class="fw-normal">หน่วยนับ</th>
                                            <th colspan="3" scope="col" class="fw-normal" data-bs-toggle="modal" data-bs-target="#add-board" contenteditable="true">พ.ศ.</th>
                                            <th colspan="9" scope="col" class="fw-normal" data-bs-toggle="modal" data-bs-target="#add-board" contenteditable="true">พ.ศ.</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ผู้รับผิดชอบ</th>
                                        </tr>
                                        <tr>
                                            <!-- <th scope="col"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                            <th scope="col" class="fw-normal">ต.ค.</th>
                                            <th scope="col" class="fw-normal">พ.ย.</th>
                                            <th scope="col" class="fw-normal">ธ.ค.</th>
                                            <th scope="col" class="fw-normal">ม.ค.</th>
                                            <th scope="col" class="fw-normal">ก.พ.</th>
                                            <th scope="col" class="fw-normal">มี.ค.</th>
                                            <th scope="col" class="fw-normal">เม.ย.</th>
                                            <th scope="col" class="fw-normal">พ.ค.</th>
                                            <th scope="col" class="fw-normal">มิ.ย.</th>
                                            <th scope="col" class="fw-normal">ก.ค.</th>
                                            <th scope="col" class="fw-normal">ส.ค.</th>
                                            <th scope="col" class="fw-normal">ก.ย.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <tr class="product-list">
                                            <td colspan="18" scope="col" class="fw-normal" contenteditable="true" style="text-align: left; width:95%">กิจกรรม กำหนดการจัดประชุมคณะทำงานแก้ไขปัญหาโรงอบ/รมยางฯ ของ กยท. และลงพื้นที่ ติดตามผลการดำเนินงาน</td>
                                        </tr>
                                        <tr class="product-list">
                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true">0.00</td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                        </tr>
                                        <tr class="product-list">
                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true">0.00</td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                            <td contenteditable="true"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                    <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                    <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                </div>
                            </div>
                        </div>

                        <!-- 4.2 การวิเคราะห์ความเสี่ยง: -->
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label mt-2">4.2 การวิเคราะห์ความเสี่ยง: :</label>
                        </div>
                        <div class="col-xl-12 mb-2">
                            <div class="border p-3">
                                <div class="row">
                                    <div class="col-xl-12 mb-2">
                                        <label for="SO001" class="form-label">วัตถุประสงค์เชิงยุทธศาสตร์ :</label>
                                        <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                            <option value="">กรุณาเลือก วัตถุประสงค์เชิงยุทธศาสตร์</option>
                                            <option value="SO1">SO1 ผลักดันความร่วมมือของกลุ่มผู้มีส่วนได้ส่วนเสียตลอดห่วงโซ่อุปทานเพื่อยกระดับความสามารถทางการแข่งขันของประเทศไทยบนพื้นฐานของงานวิจัย เทคโนโลยีและนวัตกรรมที่เป็นมิตรต่อสิ่งแวดล้อม</option>
                                            <option value="SO2">SO2 เพิ่มมูลค่ายางด้วยงานวิจัย เทคโนโลยี และนวัตกรรม</option>
                                            <option value="SO3">SO3 สร้างองค์กรที่มีสมรรถนะสูง ขับเคลื่อนด้วยเทคโนโลยีและนวัตกรรม บนพื้นฐานของการกำกับดูแลที่ดีโปร่งใส สามารถตรวจสอบได้</option>
                                            <option value="SO4">SO4 บริหารจัดการรายได้ รายจ่าย และสินทรัพย์ขององค์กรให้เกิดประโยชน์สูงสุด</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label for="yot001" class="form-label">ยุทธศาสตร์ชาติ :</label>
                                        <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                            <option value="">กรุณาเลือก ยุทธศาสตร์ชาติ</option>
                                            <option value="yot1">ยุทธศาสตร์ที่ 1 พัฒนาอุตสาหกรรมยางพาราอย่างยั่งยืน</option>
                                            <option value="yot2">ยุทธศาสตร์ที่ 2 สร้างนวัตกรรมเพื่อความยั่งยืน</option>
                                            <option value="yot3">ยุทธศาสตร์ที่ 3 พัฒนาสู่องค์กรแห่งความรู้ที่มีสมรรถนะสูง (KO&HPO)</option>
                                            <option value="yot4">ยุทธศาสตร์ที่ 3 พัฒนาสู่องค์กรแห่งความรู้ที่มีสมรรถนะสูง (KO&HPO)</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label for="yot002" class="form-label">ตัวชี้วัด :</label>
                                        <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                            <option value="">กรุณาเลือก ตัวชี้วัด</option>
                                            <option value="yot11">1.1 พื้นที่สวนยางยั่งยืนเพิ่มขึ้นไม่น้อยกว่า (ของเป้าหมายปลูกแทน) ร้อยละ 12.5</option>
                                            <option value="yot12">1.2 จำนวนสถาบันเกษตรกร/ผู้ประกอบการที่ได้รับมาตรฐาน จำนวน 7 แห่ง</option>
                                            <option value="yot13">1.3 มูลค่าการส่งออกผลิตภัณฑ์ยางพาราเพิ่มขึ้น (รวมถึงสินค้าจากยางพาราในขั้นกลางน้ำและปลายน้ำ) จำนวน 550,000 ล้านบาท</option>
                                            <option value="yot21">2.1 จำนวนงานวิจัยด้านนวัตกรรมที่ กยท./เกษตรกร/สถาบันเกษตรกร/ผู้ประกอบการ/ Start-up นำไปใช้จำนวน 6 เรื่อง</option>
                                            <option value="yot22">2.2 จำนวนงานวิจัย/นวัตกรรมที่ยื่นจดทะเบียนทรัพย์สินทางปัญญาเพิ่มขึ้น จำนวน 6 เรื่อง</option>
                                            <option value="yot23">2.3 สนับสนุนการจัดทำฐานข้อมมูลนักวิจัย 1 ระบบ</option>
                                            <option value="yot31">3.1 ผลการดำเนินงานการบริหารทุนมนุษย์ ผ่านเกณฑ์ SE-AM ระดับ 3.50</option>
                                            <option value="yot32">3.2 ผลการดำเนินงานการพัฒนาเทคโนโลยีดิจิทัล ผ่านเกณฑ์ SE-AM ระดับ 3.20</option>
                                            <option value="yot33">3.3 ความพึงพอใจของผู้ใช้บริการ และผู้มีส่วนได้ส่วนเสียเฉลี่ย ไม่ต่ำกว่าร้อยละ 90</option>
                                            <option value="yot34">3.4 คะแนน ITA ไม่ต่ำกว่า 95 คะแนน</option>
                                            <option value="yot41">4.1 กำไรจากการดำเนินธุรกิจ (ขั้นต้น) จำนวน 147 ล้านบาท</option>
                                            <option value="yot42">4.2 รายได้ค่าธรรมเนียมการส่งออก จำนวน 8,173 ล้านบาท</option>
                                            <option value="yot43">4.3 ความพึงพอใจของผู้ชำระค่าธรรมเนียม ไม่ต่ำกว่าร้อยละ 90</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label for="yot003" class="form-label">กลยุทธ์ :</label>
                                        <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                            <option value="">กรุณาเลือก กลยุทธ์</option>
                                            <option value="yot11">กลยุทธ์ที่ 1.1 ส่งเสริมการจัดการสวนยาง และการแปรรูปยางอย่างยั่งยืน</option>
                                            <option value="yot12">กลยุทธ์ที่ 1.2 สนับสนุนมาตรฐานแหล่งผลิตและสินค้ายาง</option>
                                            <option value="yot13">กลยุทธ์ที่ 1.3 สร้างสภาพแวดล้อมที่เอื้ออำนวยต่อการพัฒนาของอุตสาหกรรมยางอย่างยั่งยืน</option>
                                            <option value="yot21">กลยุทธ์ที่ 2.1 สนับสนุนการวิจัยเทคโนโลยีและนวัตกรรมยาง</option>
                                            <option value="yot22">กลยุทธ์ที่ 2.2 ส่งเสริมให้เกษตรกร และผู้ประกอบกิจการยางประยุกต์ใช้เทคโนโลยี นวัตกรรม ในเชิงพาณิชย์และสังคม</option>
                                            <option value="yot23">กลยุทธ์ที่ 2.3 จัดตั้งเครือข่ายนักวิจัยด้านยางพารา</option>
                                            <option value="yot31">กลยุทธ์ที่ 3.1 พัฒนาทรัพยากรมนุษย์ให้มีขีดความสามารถสูง</option>
                                            <option value="yot32">กลยุทธ์ที่ 3.2 พัฒนาเป็นองค์กรดิจิตัล และองค์กรแห่งความรู้</option>
                                            <option value="yot33">กลยุทธ์ที่ 3.3 ส่งเสริมความสัมพันธ์กับผู้มีส่วนได้ส่วนเสีย</option>
                                            <option value="yot34">กลยุทธ์ที่ 3.4 บริหารจัดการองค์กรอย่างมีธรรมาภิบาล</option>
                                            <option value="yot41">กลยุทธ์ที่ 4.1 สร้างและพัฒนาธุรกิจองค์กรให้มีศักยภาพแข่งขันในระดับสากล</option>
                                            <option value="yot42">กลยุทธ์ที่ 4.2 เพิ่มประสิทธิภาพการจัดเก็บค่าธรรมเนียมส่งออกยาง</option>
                                            <option value="yot43">กลยุทธ์ที่ 4.3 เพิ่มประสิทธิภาพการบริหารการคลัง</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label for="input-label" class="form-label">ตัวชี้วัดโครงการ :</label>
                                        <input type="text" class="form-control" id="input-label1" placeholder="กรุณากรอก ตัวชี้วัดโครงการ">
                                    </div>
                                </div>
                                <div class="col-xl-12 mt-2 mb-2">
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th rowspan="2" scope="col" class="fw-normal">โครงการ</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">ผู้รับผิดชอบ <br> (Risk Owners)</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">การวิเคราะห์ความเสี่ยง <br> (Risk Scennario)</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">ปัจจัยเสี่ยง <br> (Risk Factor)</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">การควบคุมภายที่มีอยู่ <br> (Exising Control)</th>
                                                    <th colspan="7" scope="col" class="fw-normal">การประเมินความเสี่ยง <br> (Risk Assessment)</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">แผนจัดการความเสี่ยง <br> (Risk Response)</th>
                                                    <th colspan="3" scope="col" class="fw-normal">ระดับความเสี่ยงที่เหลืออยู่ <br> (Residual Risk)</th>
                                                </tr>
                                                <tr>
                                                    <!-- <th scope="col"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                                    <th scope="col" class="fw-normal">S</th>
                                                    <th scope="col" class="fw-normal">O</th>
                                                    <th scope="col" class="fw-normal">F</th>
                                                    <th scope="col" class="fw-normal">C</th>
                                                    <th scope="col" class="fw-normal">LH</th>
                                                    <th scope="col" class="fw-normal">IM</th>
                                                    <th scope="col" class="fw-normal">LEVEL</th>
                                                    <th scope="col" class="fw-normal">LH</th>
                                                    <th scope="col" class="fw-normal">IM</th>
                                                    <th scope="col" class="fw-normal">LEVEL</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                <tr class="product-list">
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-xl-12 mt-2 mb-2">
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th colspan="8" scope="col" class="fw-normal">แผนจัดการความเสี่ยง (Mitigation Plan)</th>
                                                </tr>
                                                <tr>
                                                    <th rowspan="2" scope="col" class="fw-normal" style="width:5%;"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></th>
                                                    <th rowspan="2" scope="col" class="fw-normal">แผนจัดการความเสี่ยง</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">ผู้รับผิดชอบ</th>
                                                    <th rowspan="2" scope="col" class="fw-normal">ระยะเวลา </th>
                                                    <th rowspan="2" scope="col" class="fw-normal">งบประมาณ (ถ้ามี) </th>
                                                    <th colspan="3" scope="col" class="fw-normal">ความคืบหน้าของขั้นตอน/วิธีปฏิบัติงาน</th>
                                                </tr>
                                                <tr>
                                                    <!-- <th scope="col"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                                    <th scope="col" class="fw-normal">แผนจัดการความเสี่ยง </th>
                                                    <th scope="col" class="fw-normal">ผู้รับผิดชอบ </th>
                                                    <th scope="col" class="fw-normal">ระยะเวลา</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                <tr class="product-list">
                                                    <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true"></td>
                                                    <td contenteditable="true" style="text-align: left; width:15%;"></td>
                                                    <td contenteditable="true" style="text-align: left; width:15%;"></td>
                                                    <td contenteditable="true" style="text-align: left; width:15%;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 mt-2 mb-2">
                                    <div class="table-responsive mb-2">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-center">
                                                <tr>
                                                    <th scope="col" class="fw-normal" class="fw-normal" style="background-color:#111c43; color:rgba(255, 255, 255, 1);">เกณฑ์ระดับความเสี่ยง </th>
                                                    <th scope="col" class="fw-normal" class="fw-normal" style="background-color:#111c43; color:rgba(255, 255, 255, 1);">ความหมาย </th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                <tr class="product-list text-start">
                                                    <td class="fw-normal" style="background-color:red;">สูง: (H: High) (20-25)</td>
                                                    <td class="fw-normal" style="background-color:red;">ความเสี่ยงที่มีความสำคัญสูงมาก จำเป็นต้องได้รับการจัดการทันที</td>
                                                </tr>
                                                <tr class="product-list text-start">
                                                    <td class="fw-normal" style="background-color:Orange;">ค่อนข้างสูง (NH: Nearly High) (10-16)</td>
                                                    <td class="fw-normal" style="background-color:Orange;">ความเสี่ยงที่มีความสำคัญสูง จะต้องได้รับการจัดการในลำดับถัดมา</td>
                                                </tr>
                                                <tr class="product-list text-start">
                                                    <td class="fw-normal" style="background-color:rgb(255 255 0);">ปานกลาง (M: Medium) (5-9)</td>
                                                    <td class="fw-normal" style="background-color:rgb(255 255 0);">ความเสี่ยงที่มีความสำคัญ และต้องติดตามการปฏิบัติตามมาตรการจัดการความเสี่ยงที่ดำเนินการอยู่ในปัจจุบันอย่างเคร่งครัด</td>
                                                </tr>
                                                <tr class="product-list text-start">
                                                    <td class="fw-normal" style="background-color:rgb(102, 153, 51);">ค่อนข้างต่ำ (NL: Nearly Low) (3-4)</td>
                                                    <td class="fw-normal" style="background-color:rgb(102, 153, 51);">ความเสี่ยงที่มีความสำคัญน้อย อยู่ในระดับที่ผู้บริหารยอมรับได้ แต่ต้องติดตามอย่างสม่ำเสมอ</td>
                                                </tr>
                                                <tr class="product-list text-start">
                                                    <td class="fw-normal" style="background-color:MediumSeaGreen;">ต่ำ (L: Low) (1-2)</td>
                                                    <td class="fw-normal" style="background-color:MediumSeaGreen;">ความเสี่ยงที่มีความสำคัญน้อย อยู่ในระดับที่ผู้บริหารยอมรับได้</td>
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

            <!-- ส่วนที่ 5: ผู้รับผิดชอบและผู้ประสานงานโครงการ -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ส่วนที่ 5: ผู้รับผิดชอบและผู้ประสานงานโครงการ</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-xl-12">
                            <label for="input-label11" class="form-label">5.1 ส่วนงาน/หน่วยงานที่รับผิดชอบ :</label>
                            <input type="text" class="form-control" id="input-label11" placeholder="สำนักงานใหญ่" disabled>
                        </div>
                        <div class="col-xl-12 mt-2">
                            <label for="input-label11" class="form-label">5.2 ผู้รับผิดชอบโครงการ :</label>
                        </div>
                        <div class="col-xl-12">
                            <div class="border p-3">
                                <div class="row">
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">ชื่อ - สกุล :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก ชื่อ - สกุล">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">ตําแหน่ง :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก ตําแหน่ง">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">โทรศัพท์ :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก โทรศัพท์">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">อีเมลล์ :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก อีเมลล์">
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-xl-12 mt-2">
                            <label for="input-label11" class="form-label">5.3 ผู้ประสานงานโครงการ (ผู้ที่สามารถให้ข้อมูลได้ถ้ามี) :</label>
                        </div>
                        <div class="col-xl-12">
                            <div class="border p-3">
                                <div class="row">
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">ชื่อ - สกุล :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก ชื่อ - สกุล">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">ตําแหน่ง :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก ตําแหน่ง">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">โทรศัพท์ :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก โทรศัพท์">
                                    </div>
                                    <div class="col-xl-6 mb-2">
                                        <label class="form-label mt-2">อีเมลล์ :</label>
                                        <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก อีเมลล์">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="withdraw-money-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-primary btn-wave waves-effect waves-light m-1"><i class="bi bi-save"></i> บันทึกร่างคำของบประมาณ</button>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำของบประมาณ</button>
                </div>
            </div>

        </div>
    </div>
    <!--End::row -->
</div>

<!-- Start::add board modal -->
<div class="modal fade" id="add-board" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">เลือกปี พ.ศ.</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row">
                    <div class="col-xl-12">
                        <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                            <option value="">กรุณาเลือก กลยุทธ์</option>
                            <option value="kol2558">พ.ศ. 2558</option>
                            <option value="kol2559">พ.ศ. 2559</option>
                            <option value="kol2560">พ.ศ. 2560</option>
                            <option value="kol2561">พ.ศ. 2561</option>
                            <option value="kol2562">พ.ศ. 2562</option>
                            <option value="kol2563">พ.ศ. 2563</option>
                            <option value="kol2564">พ.ศ. 2564</option>
                            <option value="kol2565">พ.ศ. 2565</option>
                            <option value="kol2566">พ.ศ. 2566</option>
                            <option value="kol2567">พ.ศ. 2567</option>
                            <option value="kol2568">พ.ศ. 2568</option>
                            <option value="kol2569">พ.ศ. 2569</option>
                            <option value="kol2570">พ.ศ. 2570</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Board</button>
            </div>
        </div>
    </div>
</div>
<!-- End::add board modal -->


<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

<!-- PRISM JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/prismjs/prism.js"></script>
<script src="<?php echo $baseUrl; ?>/assets/js/prism-custom.js"></script>

<!-- INTERNAL CHOICES JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/choices.js"></script>

<!-- DATE & TIME PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

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

<!-- JQUERY JS -->
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

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

<!-- INTERNAL PRODUCT DETAILS JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/product-list.js"></script>

<!-- FLAT PICKER JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/flatpickr/flatpickr.min.js"></script>

<!-- CREATE PROJECT JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/create-project.js"></script>

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

<!-- DROPZONE JS -->
<script src="<?php echo $baseUrl; ?>/assets/libs/dropzone/dropzone-min.js"></script>

<!-- FILEUPLOAD JS -->
<script src="<?php echo $baseUrl; ?>/assets/js/fileupload.js"></script>

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include 'layouts/base.php'; ?>
<!-- This code use for render base file -->