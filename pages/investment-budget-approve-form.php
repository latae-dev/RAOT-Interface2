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
        <h1 class="page-title fw-semibold fs-18 mb-0">แบบฟอร์มคำขอตั้งงบลงทุน</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item fw-semibold"><a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a></li>
                    <li class="breadcrumb-item fw-semibold active" aria-current="page">แบบฟอร์มคำขอตั้งงบลงทุน</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Page Header Close -->

    <div class="row">
        <div class="col-xl-12">

            <!-- ข้อมูลทั่วไป -->
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">ข้อมูลทั่วไป</div>
                </div>

                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- ปีงบประมาณ -->
                        <div class="col-xl-6">
                            <label class="form-label mt-2">ปีงบประมาณ :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="2568">2568</option>
                                <option value="2569">2569</option>
                                <option value="2570">2570</option>
                            </select>
                        </div>

                        <!-- เวอร์ชั่น (แผน) -->
                        <div class="col-xl-6">
                            <label class="form-label mt-2">เวอร์ชั่น (แผน):</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="s-1">ระดับสาขา</option>
                                <option value="s-2">ระดับจังหวัด/กอง</option>
                                <option value="s-3">ระดับเขต/ฝ่าย</option>
                                <option value="s-4">ระดับสำนักงานใหญ่</option>
                            </select>
                        </div>

                        <!-- ประเภทข้อมูล -->
                        <div class="col-xl-6">
                            <label class="form-label mt-2">ประเภทข้อมูล :</label>
                            <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก</option>
                                <option value="1091030000">เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                <option value="1091040000">เครื่องตกแต่งและอุปกรณ์สำนักงาน</option>
                                <option value="1091010000">ที่ดิน</option>
                                <option value="1091050000">ยานพาหนะ</option>
                                <option value="1091070000">สวนปาล์ม</option>
                                <option value="1091060000">สวนยาง</option>
                                <option value="1101010000">สินทรัพย์ไม่มีตัวตน</option>
                                <option value="1091020000">อาคารและสิ่งปลูกสร้าง</option>
                            </select>
                        </div>

                        <div class="col-xl-6">
                            <label class="form-label mt-2">รหัสประเภทข้อมูล :</label>
                            <input type="text" class="form-control" id="input-label11" placeholder="ดึง รหัสประเภทข้อมูล มาแสดง" disabled>
                        </div>

                    </div>
                </div>

            </div>

            <!-- เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร -->
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</div>
                </div>

                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวน (หน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">ราคา (ต่อหน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวนเงินรวม :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="" disabled>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามบัญชีราคามาตรฐานครุภัณฑ์) </small></option>
                                        <option value="fault">ไม่มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามใบเสนอราคา) </small></option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ลักษณะ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">302 เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                    </select>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน :</label>
                                    <div class="table-responsive">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-start"></thead>
                                            <tbody class="text-start">
                                                <tr class="product-list">
                                                    <td class="fw-normal" contenteditable="true" style="width:15%; line-height: 0.900; font-size: 0.875rem;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>

            </div>

            <!-- เครื่องตกแต่งและอุปกรณ์สำนักงาน -->
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">เครื่องตกแต่งและอุปกรณ์สำนักงาน</div>
                </div>

                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวน (หน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">ราคา (ต่อหน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวนเงินรวม :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="" disabled>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามบัญชีราคามาตรฐานครุภัณฑ์) </small></option>
                                        <option value="fault">ไม่มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามใบเสนอราคา) </small></option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ลักษณะ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">401 เครื่องตกแต่งและอุปกรณ์สำนักงาน</option>
                                    </select>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน :</label>
                                    <div class="table-responsive">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-start"></thead>
                                            <tbody class="text-start">
                                                <tr class="product-list">
                                                    <td class="fw-normal" contenteditable="true" style="width:15%; line-height: 0.900; font-size: 0.875rem;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>

            </div>

            <!-- ที่ดิน -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ที่ดิน</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="1" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="1" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">จำนวนเงิน :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">งานงวด :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">401 เครื่องตกแต่งและอุปกรณ์สำนักงาน</option>
                                    </select>
                                </div>


                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

            <!-- ยานพาหนะ -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ยานพาหนะ</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวน (หน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">ราคา (ต่อหน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวนเงินรวม :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="" disabled>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามบัญชีราคามาตรฐานครุภัณฑ์) </small></option>
                                        <option value="fault">ไม่มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามใบเสนอราคา) </small></option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ลักษณะ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">302 เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                    </select>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน :</label>
                                    <div class="table-responsive">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-start"></thead>
                                            <tbody class="text-start">
                                                <tr class="product-list">
                                                    <td class="fw-normal" contenteditable="true" style="width:15%; line-height: 0.900; font-size: 0.875rem;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

            <!-- ประเภทสวนยาง -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ประเภทสวนยาง</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">จำนวนไร่ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">302 เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ระยะเวลาเริ่มต้น โครงการฯ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX </option>
                                        <option value="">XXX </option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ระยะเวลาสิ้นสุด โครงการฯ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX </option>
                                        <option value="">XXX </option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ปีที่ดำเนินการ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">จำนวนเงิน :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

            <!-- ประเภทสวนปาร์ม -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">ประเภทสวนปาร์ม</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">จำนวนไร่ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">302 เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ระยะเวลาเริ่มต้น โครงการฯ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX </option>
                                        <option value="">XXX </option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ระยะเวลาสิ้นสุด โครงการฯ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX </option>
                                        <option value="">XXX </option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ปีที่ดำเนินการ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">จำนวนเงิน :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

            <!-- สินทรัพย์ไม่มีตัวตน -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">สินทรัพย์ไม่มีตัวตน</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวน (หน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">ราคา (ต่อหน่วย) :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">จำนวนเงินรวม :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="" disabled>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">มาตรฐานครุภัณฑ์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามบัญชีราคามาตรฐานครุภัณฑ์) </small></option>
                                        <option value="fault">ไม่มี <small class="text-danger ml-2"> * (กรุณาระบุราคาต่อหน่วยตามใบเสนอราคา) </small></option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ลักษณะ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">302 เครื่องจักร อุปกรณ์โรงงานและอุปกรณ์การเกษตร</option>
                                    </select>
                                </div>
                                <div class="col-xl-12">
                                    <label for="input-label" class="form-label mt-2">หมายเลขครุภัณฑ์ทดแทน :</label>
                                    <div class="table-responsive">
                                        <table class="table text-nowrap table-bordered">
                                            <thead class="text-start"></thead>
                                            <tbody class="text-start">
                                                <tr class="product-list">
                                                    <td class="fw-normal" contenteditable="true" style="width:15%; line-height: 0.900; font-size: 0.875rem;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                            <button class="btn btn-success-light m-1"><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                            <button class="btn btn-danger-light m-1"><i class="bi bi-dash"></i> ลบรายการ</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

            <!-- อาคารและสิ่งปลูกสร้าง -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">อาคารและสิ่งปลูกสร้าง</div>
                </div>
                <div class="card-body border-bottom">
                    <div class="row">

                        <!-- รายละเอียด -->
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <label for="input-label" class="form-label mt-2">แหล่งงบประมาณ</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="2568">G2013 งบแผ่นดิน งบลงทุน มาตรา 13</option>
                                        <option value="2569">G2491 งบแผ่นดิน งบลงทุน มาตรา 49(1)</option>
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label mt-2">รายการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="กรุณากรอก รายการ">
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">รายละเอียด</label>
                                    <textarea class="form-control" id="product-description-add" row="1" style="color:red; font-size:12px;" placeholder="กรุณากรอก รายละเอียด"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">เหตุผลความจำเป็น :</label>
                                    <textarea class="form-control" id="product-description-add" row="1" style="color:red; font-size:12px;" placeholder="กรุณากรอก เหตุผลความจำเป็น"></textarea>
                                </div>
                                <div class="col-xl-12">
                                    <label class="form-label mt-2">จำนวนเงิน :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">ยุทธศาสตร์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label mt-2">แผนงาน / โครงการ :</label>
                                    <input type="text" class="form-control" id="input-label11" placeholder="">
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">งานงวด :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="true">ตั้งใหม่</option>
                                        <option value="fault">ทดแทน</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">หมวดสินทรัพย์ :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">401 เครื่องตกแต่งและอุปกรณ์สำนักงาน</option>
                                    </select>
                                </div>


                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">วันที่คาดว่าจะเบิกจ่าย :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-4">
                                    <label for="input-label" class="form-label mt-2">แผนเบิกจ่ายมากกว่า 1 ปี :</label>
                                    <select class="form-control" data-trigger name="choices-single-groups" id="choices-single-groups">
                                        <option value="">กรุณาเลือก</option>
                                        <option value="">XXX</option>
                                        <option value="">XXX</option>
                                    </select>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="input-label" class="form-label mt-2">เอกสารแนบ: (PDF) : </label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="investment-budget-approvel-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ส่งคำขออนุมัติ</button>
                </div>
            </div>

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
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-danger">ไม่พบข้อมูล</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- การพิจารณาอนุมัติ -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">การพิจารณาอนุมัติ</div>
                </div>
                <div class="card-body px-2 py-4 px-sm-4 border-bottom">
                    <p class="mb-2 me-4"><span class="fs-15 fw-bold"> ผลการอนุมัติ <small class="text-danger ml-2"> *จำเป็นต้องกรอก </small></span></p>
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input approve" type="checkbox" value="" id="approve">
                                <label class="form-check-label" for="approve">อนุมัติ</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input reject" type="checkbox" value="" id="reject">
                                <label class="form-check-label" for="reject">ไม่อนุมัติ</label>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="row">
                                <div class="form-check mb-2 col-6">
                                    <label class="form-check-label mb-1" for="st_hf_full_name">อนุมัติโดย</label>
                                    <input type="text" id="st_hf_full_name" class="form-control bg-light" name="st_hf_full_name" readonly="" fdprocessedid="zk4toy">
                                </div>
                                <div class="form-check mb-2 col-6">
                                    <label class="form-check-label mb-1" for="st_hf_date">วันที่อนุมัติ</label>
                                    <input type="text" id="st_hf_date" class="form-control bg-light" name="st_hf_date" readonly="" fdprocessedid="ftku5n">
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label mb-1" for="remark">หมายเหตุ <small class="text-danger ml-2" id="hide_text_approve"> *จำเป็นต้องกรอก </small></label>
                                    <textarea class="form-control remark" id="remark" name="remark" rows=""></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ที่ท้ายฟอร์ม -->
                <div class="px-4 py-3 border-block-start-dashed d-sm-flex justify-content-end">
                    <a href="withdraw-money-list.php" class="btn btn-warning btn-wave waves-effect waves-light m-1"><i class="bi bi-arrow-return-left"></i> ย้อนกลับเมนู</a>
                    <button class="btn btn-success btn-wave waves-effect waves-light m-1"><i class="bi bi-send"></i> ผลการพิจารณา</button>
                </div>
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