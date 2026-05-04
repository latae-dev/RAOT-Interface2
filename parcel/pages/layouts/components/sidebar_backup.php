<aside class="app-sidebar sticky" id="sidebar">
    <?php if (strpos($_SERVER['REQUEST_URI'], 'parcel/')) {
        $url_parcel = '';
        $url_vendor = '../../pages/';
    } else {
        $url_parcel = '../parcel/pages/';
        $url_vendor = '';
    } ?>
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="<?= $url_vendor ?>index.php" class="header-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
            <img src="<?php echo $baseUrl; ?>/assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">
                <!-- Start::slide__category -->
                <li class="slide__category"><span class="category-name">Main</span></li>
                <!-- End::slide__category -->

                <!-- Start::slide -->
                <li class="slide">
                    <a href="<?= $url_vendor ?>index.php" class="side-menu__item">
                        <i class="bx bx-home side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                <!-- <li class="slide">
                    <a href="index.php" class="side-menu__item">
                        <i class="bx bx-home side-menu__icon"></i>
                        <span class="side-menu__label">Summary</span>
                    </a>
                </li> -->
                <!-- Start::slide__ระบบงาน -->
                <li class="slide__category"><span class="category-name">ระบบงาน</span></li>
                <!-- End::slide__ระบบงาน -->

                <!-- Start::ระบบเบริหารจัดการข้อมูลพัสดุ -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-cube side-menu__icon"></i>
                        <span class="side-menu__label">ระบบเบริหารจัดการข้อมูลพัสดุ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบเบริหารจัดการข้อมูลพัสดุ</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-list.php" class="side-menu__item"> รายการแผนการเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide">
                            <!-- <a href="parcel-withdrawal-plan.php" class="side-menu__item"> กำหนดแผนการเบิกพัสดุแบบพิมพ์</a> -->
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-list-draft.php" class="side-menu__item"> รายการบันทึกร่าง</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-doc-list.php" class="side-menu__item"> รายการใบเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide side-menu__label1" id="proposal-approval-menu-labelpc">
                            <a href="javascript:void(0);">การจัดการข้อมูลการอนุมัติ</a>
                        </li>
                        <li class="slide has-sub" id="proposal-approval-menupc">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการอนุมัติ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>parcel-withdrawal-approvel-list.php" class="side-menu__item">การอนุมัติแผนการเบิกพัสดุแบบพิมพ์</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>parcel-withdrawal-approvel-plan-merge.php" class="side-menu__item">การอนุมัติแผนการเบิกพัสดุแบบพิมพ์ (รวมไฟล์)</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>parcel-doc-list-approvel.php" class="side-menu__item">การอนุมัติใบเบิกพัสดุแบบพิมพ์</a>
                                </li>
                            </ul>
                        </li>
                        <!-- <li class="slide">
                            <a href="parcel-withdrawal-approve-status-list.php" class="side-menu__item"> รายการสถานะการอนุมัติ</a>
                        </li> -->
                        <!-- <li class="slide">
                            <a href="parcel-raot-approve-report.php" class="side-menu__item"> รายงานสรุปการอนุมัติ</a>
                        </li> -->
                    </ul>
                </li>
                <!-- End::slide -->

                <!-- Start::ระบบเงินสดย่อยและเงินทดรองจ่าย CM -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-money side-menu__icon"></i>
                        <span class="side-menu__label">ระบบเงินยืมทดรอง</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบเงินยืมทดรอง</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_parcel ?>withdraw-money-list.php" class="side-menu__item">รายการคำขอเบิกเงินทดรองจ่าย</a>
                        </li>
                        <!-- <li class="slide">
                            <a href="withdraw-money-plan.php" class="side-menu__item">แผนคำขอเบิกเงินทดรองจ่าย</a>
                        </li> -->
                        <li class="slide">
                            <a href="<?= $url_parcel ?>travel-expenses-money-list.php" class="side-menu__item">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_parcel ?>money-order-list.php" class="side-menu__item">รายการใบสั่งจ่าย</a>
                        </li>
                        <!-- <li class="slide">
                            <a href="travel-expenses-create.php" class="side-menu__item">ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                        </li>
                        <li class="slide">
                            <a href="travel-expenses-create-equ.php" class="side-menu__item">ใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)</a>
                        </li> -->
                        <li class="slide side-menu__label1" id="approval-menu-label">
                            <a href="javascript:void(0);">การจัดการข้อมูลการอนุมัติ</a>
                        </li>
                        <li class="slide has-sub" id="approval-menu">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการอนุมัติ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>withdraw-money-approvel-plan.php" class="side-menu__item">การอนุมัติคำขอเบิกเงินทดรองจ่าย</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>travel-expenses-approvel-list.php" class="side-menu__item">การอนุมัติคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>money-order-approvel-list.php" class="side-menu__item">การอนุมัติใบสั่งจ่าย</a>
                                </li>
                            </ul>
                        </li>
                        <li class="slide side-menu__label1" id="report-menu-label">
                            <a href="javascript:void(0);">รายงาน</a>
                        </li>
                        <li class="slide has-sub" id="report-menu">
                            <a href="javascript:void(0);" class="side-menu__item">รายงาน
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>withdraw-money-plan-report.php" class="side-menu__item">รายงานเงินทดรอง</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>travel-expenses-report.php" class="side-menu__item">รายงานใบเบิกค่าเดินทางปฎิบัติงาน</a>
                                </li>
                                <li class="slide">
                                    <a href="<?= $url_parcel ?>money-order-report.php" class="side-menu__item">รายงานใบสั่งจ่าย</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- End::ระบบเงินสดย่อยและเงินทดรองจ่าย CM -->

                <!-- Start::ระบบคําของบประมาณรายจ่ายประจําปี -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-money side-menu__icon"></i>
                        <span class="side-menu__label">ระบบคําของบประมาณรายจ่ายประจําปี</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบคําของบประมาณรายจ่ายประจําปี</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>business-budget-list.php" class="side-menu__item">รายการคำของบประมาณรายจ่ายประจําปี</a>
                        </li>


                        <?php
                        $allowedGroups = ['APBR', 'APPV', 'APAR', 'APPRP', 'APSTR'];
                        if (
                            isset($_SESSION['user_data']['position_list']) &&
                            !empty(array_intersect(
                                array_column($_SESSION['user_data']['position_list'], 'position_code'),
                                $allowedGroups
                            ))
                        ) :
                        ?>
                            <li class="slide side-menu__label1" id="approval-menu-label-1">
                                <a href="javascript:void(0);">การจัดการข้อมูลการอนุมัติ</a>
                            </li>
                            <li class="slide has-sub" id="approval-menu-1">
                                <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการอนุมัติ
                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                <ul class="slide-menu child2">
                                    <li class="slide">
                                        <a href="<?= $url_vendor ?>business-budget-approvel-list.php" class="side-menu__item">การอนุมัติคําของบประมาณรายจ่ายประจําปี</a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <!-- End::ระบบคําของบประมาณรายจ่ายประจําปี -->

                <!-- Start::ระบบคำขอตั้งงบลงทุน -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-money side-menu__icon"></i>
                        <span class="side-menu__label">ระบบคำขอตั้งงบลงทุน</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบคำขอตั้งงบลงทุน</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>investment-budget-list.php" class="side-menu__item">รายการคำขอตั้งงบลงทุน</a>
                        </li>
                        <!-- <li class="slide">
                            <a href="investment-budget-form.php" class="side-menu__item">แบบฟอร์มคำขอตั้งงบลงทุน</a>
                        </li> -->
                        <li class="slide side-menu__label1" id="proposal-approval-menu-labelib">
                            <a href="javascript:void(0);">การจัดการข้อมูลการอนุมัติ</a>
                        </li>
                        <li class="slide has-sub" id="proposal-approval-menuib">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการอนุมัติ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>investment-budget-approvel-list.php" class="side-menu__item">การอนุมัติคำขอตั้งงบลงทุน</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- End::ระบบคำขอตั้งงบลงทุน -->

                <!-- Start::ระบบคำขอสร้างใบขอเสนอ -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-money side-menu__icon"></i>
                        <span class="side-menu__label">ระบบคำขอสร้างใบขอเสนอ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบคำขอสร้างใบขอเสนอ</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>request-proposal-list.php" class="side-menu__item">รายการคำขอสร้างใบขอเสนอ</a>
                        </li>
                        <!-- <li class="slide">
                            <a href="request-proposal-form.php" class="side-menu__item">แบบฟอร์มคำขอสร้างใบขอเสนอ</a>
                        </li> -->
                        <li class="slide side-menu__label1" id="proposal-approval-menu-label">
                            <a href="javascript:void(0);">การจัดการข้อมูลการอนุมัติ</a>
                        </li>
                        <li class="slide has-sub" id="proposal-approval-menu">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการอนุมัติ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>request-proposal-approvel-list.php" class="side-menu__item">การอนุมัติคำขอใบขอเสนอ จ้าง/เช่า/ซื้อ</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- Start::slide__รายงาน -->
                <li class="slide__category"><span class="category-name">รายงาน</span></li>

                <!-- Start::รายงานบริหารจัดการข้อมูลพัสดุ -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bxs-report side-menu__icon"></i>
                        <span class="side-menu__label">รายงานบริหารจัดการข้อมูลพัสดุ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">รายงานบริหารจัดการข้อมูลพัสดุ</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-plan-approve-report.php" class="side-menu__item">รายงานแผนการเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-approve-report.php" class="side-menu__item">รายงานใบเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-summary-report.php" class="side-menu__item">รายงานสรุปการเบิกพัสดุแบบพิมม์</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>parcel-withdrawal-approve-status-list.php" class="side-menu__item"> รายการสถานะการอนุมัติ</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">รายงาน Interface ข้อมูลเข้า SAP</a>
                        </li>
                    </ul>
                </li>
                <!-- End::รายงานบริหารจัดการข้อมูลพัสดุ -->

                <!-- Start::รายงานคําของบประมาณรายจ่ายประจําปี -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bxs-report side-menu__icon"></i>
                        <span class="side-menu__label">รายงานคําของบประมาณรายจ่ายประจําปี</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">รายงานคําของบประมาณรายจ่ายประจําปี</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>business-budget-report-list.php" class="side-menu__item">รายงานการอนุมัติคําของบประมาณรายจ่ายประจําปี</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>business-budget-report-interface-sap-list.php" class="side-menu__item">รายงาน Interface ข้อมูลเข้า SAP</a>
                        </li>
                    </ul>
                </li>
                <!-- End -->

                <!-- Start::รายงานคำขอตั้งงบลงทุน -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bxs-report side-menu__icon"></i>
                        <span class="side-menu__label">รายงานคำขอตั้งงบลงทุน</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">รายงานคำขอตั้งงบลงทุน</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>investment-budget-report-list.php" class="side-menu__item">รายงานคำขอตั้งงบลงทุน</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>investment-budget-report-interface-sap-list.php" class="side-menu__item">รายงาน Interface ข้อมูลเข้า SAP</a>
                        </li>
                    </ul>
                </li>
                <!-- End -->

                <!-- Start::รายงานใบขอเสนอ -->
                <li class="slide has-sub" id="proposal-report-menu">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bxs-report side-menu__icon"></i>
                        <span class="side-menu__label">รายงานคำขอสร้างใบขอเสนอ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child2">
                        <li class="slide">
                            <a href="<?= $url_vendor ?>request-proposal-list-report.php" class="side-menu__item">รายงานคำขอสร้างใบขอเสนอ</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>request-proposal-report-interface-sap-list.php" class="side-menu__item">รายงานคำขอสร้างใบขอเสนอ (เชื่อมโยง SAP)</a>
                        </li>
                    </ul>
                </li>
                <!-- End -->

                <!-- Start::slide__เชื่อมโยง SAP -->
                <li class="slide__category"><span class="category-name">เชื่อมโยง SAP</span></li>
                <!-- End::slide__เชื่อมโยง SAP -->

                <!-- Start::slide เชื่อมโยง SAP-->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bxs-report side-menu__icon"></i>
                        <span class="side-menu__label">เชื่อมโยง SAP</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">เชื่อมโยง SAP</a>
                        </li>
                        <!-- <li class="slide">
                        <a href="#" class="side-menu__item">ระบบเงินสดย่อยและเงินทดลองราย</a>
                    </li> -->
                        <li class="slide">
                            <a href="<?= $url_vendor ?>api-sap.php" class="side-menu__item">ระบบบริหารจัดการข้อมูลพัสดุ</a>
                        </li>
                        <!-- <li class="slide">
                        <a href="#" class="side-menu__item">ระบบจัดซื้อ</a>
                    </li>
                    <li class="slide side-menu__label1">
                        <a href="javascript:void(0);">ระบบงบประมาณ</a>
                    </li>
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">ระบบงบประมาณ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                        <ul class="slide-menu child2">
                            <li class="slide">
                                <a href="#" class="side-menu__item">งบทำการ</a>
                            </li>
                            <li class="slide">
                                <a href="#" class="side-menu__item">งบลงทุน</a>
                            </li>
                        </ul>
                    </li> -->
                    </ul>
                </li>
                <!-- End::slide เชื่อมโยง SAP-->

                <!-- Start::slide__รายงาน -->
                <!-- <li class="slide__category"><span class="category-name">รายงาน</span></li> -->
                <!-- End::slide__รายงาน -->



                <!-- Start::รายงานเงินสดย่อยและเงินทดลองจ่าย -->
                <!-- <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="bx bxs-report side-menu__icon"></i>
                    <span class="side-menu__label">รายงานเงินสดย่อยและเงินทดลองจ่าย</span>
                    <i class="fe fe-chevron-right side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1">
                        <a href="javascript:void(0);">รายงานเงินสดย่อยและเงินทดลองจ่าย</a>
                    </li>
                    <li class="slide">
                        <a href="#" class="side-menu__item">รายงานคำขอยืมเงิน</a>
                    </li>
                    <li class="slide">
                        <a href="#" class="side-menu__item">รายงานส่งใช้เงินยืม</a>
                    </li>
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">รายงานงบประมาณ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                        <ul class="slide-menu child2">
                            <li class="slide">
                                <a href="#" class="side-menu__item">รายงานคำขอตั้งงบทำการทั้งหมด</a>
                            </li>
                            <li class="slide">
                                <a href="#" class="side-menu__item">รายงานคำขอตั้งงบลงทุนทั้งหมด</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li> -->
                <!-- End::slide -->

                <!-- Start::slide__การตั้งค่า -->
                <li class="slide__category"><span class="category-name">การตั้งค่า</span></li>
                <!-- End::slide__การตั้งค่า -->

                <!-- Start::slide -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-cog side-menu__icon"></i>
                        <span class="side-menu__label">ผู้ดูแลระบบ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ผู้ดูแลระบบ</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>roles-permission.php" class="side-menu__item">Roles & Permision</a>
                        </li>
                        <li class="slide">
                            <a href="<?= $url_vendor ?>activity-log.php" class="side-menu__item">Activity Log</a>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">การตั้งค่าเงื่อนไขระบบ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="<?= $url_vendor ?>params-quarter-list.php" class="side-menu__item">การจัดการไตรมาส</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item"> จัดการข้อมูลเจ้าหนี้</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item">จัดการข้อมูลบัญชีแยกประเภททั่วไป</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item"> จัดการข้อมูลวัสดุ</a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                </li>
                <!-- End::slide -->

                <!-- Start::slide -->
                <!-- <li class="slide">
                <a href="profiles.php" class="side-menu__item">
                    <i class="bx bx-user side-menu__icon"></i>
                    <span class="side-menu__label">บัญชีของฉัน</span>
                </a>
            </li> -->
                <!-- End::slide -->

                <!-- Start::slide -->
                <li class="slide">
                    <a href="#" onclick="logOut()" class="side-menu__item">
                        <i class="ti ti-logout side-menu__icon"></i>
                        <span class="side-menu__label">ออกจากระบบ</span>
                    </a>
                </li>
                <!-- End::slide -->
                <!-- End::slide -->

            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <script>
            function logOut() {
                // ลบข้อมูล session
                sessionStorage.removeItem("raot_user_session");
                // เปลี่ยนหน้าไปยังหน้าล็อกอิน
                window.location.href = "login.php";
            }
        </script>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userData = JSON.parse(sessionStorage.getItem("raot_user_session"));
        const posId = Number(String(userData?.position_id).trim());
        console.log("User Position ID:", posId);

        if (posId !== 5 && posId !== 6) {
            document.getElementById("approval-menu-label")?.style && (document.getElementById("approval-menu-label").style.display = "none");
            document.getElementById("approval-menu")?.style && (document.getElementById("approval-menu").style.display = "none");
        }

        if (posId === 6) {
            console.log("Hiding withdraw-money-approvel-plan menu for position_id = 6");
            const withdrawMenu = document.querySelector('a[href="withdraw-money-approvel-plan.php"]');
            if (withdrawMenu) {
                withdrawMenu.closest("li.slide").style.display = "none";
            }
        }

        // เฉพาะ Position ID = 3 และ 11 เท่านั้นที่จะเห็นเมนูการจัดการข้อมูลการอนุมัติของระบบคำขอสร้างใบขอเสนอ
        // Position ID 3 = ผู้จัดการ, Position ID 11 = ผู้อนุมัติ
        if (posId !== 2 && posId !== 3 && posId !== 4 && posId !== 9) {
            document.getElementById("proposal-approval-menu-labelib")?.style && (document.getElementById("proposal-approval-menu-labelib").style.display = "none");
            document.getElementById("proposal-approval-menuib")?.style && (document.getElementById("proposal-approval-menuib").style.display = "none");
        }

        // เฉพาะ Position ID = 3 และ 11 เท่านั้นที่จะเห็นเมนูการจัดการข้อมูลการอนุมัติของระบบคำขอสร้างใบขอเสนอ
        // Position ID 3 = ผู้จัดการ, Position ID 11 = ผู้อนุมัติ
        if (posId !== 2 && posId !== 3 && posId !== 4 && posId !== 10) {
            document.getElementById("proposal-approval-menu-labelpc")?.style && (document.getElementById("proposal-approval-menu-labelpc").style.display = "none");
            document.getElementById("proposal-approval-menupc")?.style && (document.getElementById("proposal-approval-menupc").style.display = "none");
        }

        // เฉพาะ Position ID = 3 และ 11 เท่านั้นที่จะเห็นเมนูการจัดการข้อมูลการอนุมัติของระบบคำขอสร้างใบขอเสนอ
        // Position ID 3 = ผู้จัดการ, Position ID 11 = ผู้อนุมัติ
        if (posId !== 11) {
            document.getElementById("proposal-approval-menu-label")?.style && (document.getElementById("proposal-approval-menu-label").style.display = "none");
            document.getElementById("proposal-approval-menu")?.style && (document.getElementById("proposal-approval-menu").style.display = "none");
        }
    });
</script>