<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="index.php" class="header-logo">
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
                    <a href="index.php" class="side-menu__item">
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
                            <a href="parcel-withdrawal-list.php" class="side-menu__item"> รายการแผนการเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide">
                            <a href="parcel-withdrawal-plan.php" class="side-menu__item"> กำหนดแผนการเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide">
                            <a href="parcel-withdrawal-list-draft.php" class="side-menu__item"> รายการบันทึกร่าง</a>
                        </li>
                        <li class="slide">
                            <a href="parcel-doc-list.php" class="side-menu__item"> รายการใบเบิกพัสดุแบบพิมพ์</a>
                        </li>
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">รายงานประวัติการขออนุมัติ</a>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">รายงานประวัติการขออนุมัติ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="parcel-withdrawal-approvel-list.php" class="side-menu__item">การอนุมัติแผนการเบิกพัสดุแบบพิมพ์</a>
                                </li>
                                <li class="slide">
                                    <a href="parcel-withdrawal-approvel-plan-merge.php" class="side-menu__item">การอนุมัติแผนการเบิกพัสดุแบบพิมพ์ (รวมไฟล์)</a>
                                </li>
                                <li class="slide">
                                    <a href="parcel-doc-list-approvel.php" class="side-menu__item">การอนุมัติใบเบิกพัสดุแบบพิมพ์</a>
                                </li>
                            </ul>
                        </li>
                        <li class="slide">
                            <a href="parcel-withdrawal-approve-status-list.php" class="side-menu__item"> รายการสถานะการอนุมัติ</a>
                        </li>
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
                        <span class="side-menu__label">ระบบเงินสดย่อย CM</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบเงินสดย่อย CM</a>
                        </li>
                        <li class="slide">
                            <a href="withdraw-money-list.php" class="side-menu__item">รายการคำขอเบิกเงินทดรองจ่าย</a>
                        </li>
                        <!-- <li class="slide">
                            <a href="withdraw-money-plan.php" class="side-menu__item">แผนคำขอเบิกเงินทดรองจ่าย</a>
                        </li> -->
                        <li class="slide">
                            <a href="travel-expenses-money-list.php" class="side-menu__item">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                        </li>
                        <li class="slide">
                            <a href="money-order-list.php" class="side-menu__item">รายการใบสั่งจ่าย</a>
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
                                    <a href="withdraw-money-approvel-plan.php" class="side-menu__item">การอนุมัติคำขอเบิกเงินทดรองจ่าย</a>
                                </li>
                                <li class="slide">
                                    <a href="travel-expenses-approvel-list.php" class="side-menu__item">การอนุมัติคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                                </li>
                                <li class="slide">
                                    <a href="money-order-approvel-list.php" class="side-menu__item">การอนุมัติใบสั่งจ่าย</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- End::ระบบเงินสดย่อยและเงินทดรองจ่าย CM -->

                <!-- Start::ระบบเงินสดย่อยและเงินทดรองจ่าย NTA -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-money side-menu__icon"></i>
                        <span class="side-menu__label">ระบบเงินสดย่อย NTA</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบเงินสดย่อย NTA</a>
                        </li>
                        <li class="slide">
                            <a href="withdraw-money-list2.php" class="side-menu__item">รายการคำขอเบิกเงินทดรองจ่าย</a>
                        </li>
                        <li class="slide">
                            <a href="withdraw-money-plan2.php" class="side-menu__item">แบบฟอร์มคำขอเบิกเงินทดรองจ่าย</a>
                        </li>
                        <li class="slide">
                            <a href="travel-expenses-list2.php" class="side-menu__item">รายการคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                        </li>
                        <li class="slide">
                            <a href="travel-expenses-creates2.php" class="side-menu__item">แบบฟอร์มใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                        </li>
                        <li class="slide">
                            <a href="travel-expenses-creates-equ2.php" class="side-menu__item">แบบฟอร์มใบเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน (บุคคลภายนอก)</a>
                        </li>
                        <li class="slide">
                            <a href="money-order-list2.php" class="side-menu__item">รายการใบสั่งจ่าย</a>
                        </li>
                        <li class="slide">
                            <a href="money-order-create.php" class="side-menu__item">แบบฟอร์มใบสั่งจ่าย</a>
                        </li>
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">การจัดการข้อมูลการนุมัติ</a>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการนุมัติ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="withdraw-money-approvel-plan2.php" class="side-menu__item">การอนุมัติคำขอเบิกเงินทดรองจ่าย</a>
                                </li>
                                <li class="slide">
                                    <a href="travel-expenses-approvel-list2.php" class="side-menu__item">การอนุมัติคำขอเบิกค่าใช้จ่ายในการเดินทางไปปฏิบัติงาน</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- End::ระบบเงินสดย่อยและเงินทดรองจ่าย NTA-->

                <!-- Start::ระบบจัดซื้อ -->
                <!-- <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-purchase-tag-alt side-menu__icon"></i>
                        <span class="side-menu__label">ระบบจัดซื้อ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">ระบบจัดซื้อ</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">PO Online</a>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการนุมัติ
                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="#" class="side-menu__item">การอนุมัติระดับสาขา</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item">การอนุมติระดับเขต</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item">การอนุมติระดับจังหวัด</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item">การอนุมติระดับสำนักงานใหญ่</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
                <!-- End::ระบบจัดซื้อ -->

                <!-- Start::ระบบงบประมาณ -->
                <!-- <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="bx bx-purchase-tag-alt side-menu__icon"></i>
                        <span class="side-menu__label">ระบบงบประมาณ</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">คำขอตั้งงบทำการ</a>
                        </li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">คำขอตั้งงบทำการ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                            <ul class="slide-menu child2">
                                <li class="slide">
                                    <a href="#" class="side-menu__item">รายการคำขอตั้งงบทำการ</a>
                                </li>
                                <li class="slide">
                                    <a href="#" class="side-menu__item">แบบฟอร์มคำขอตั้งงบทำการ</a>
                                </li>
                        </li>
                    </ul>
                </li>
                <li class="slide side-menu__label1">
                    <a href="javascript:void(0);">คำขอตั้งงบลงทุน</a>
                </li>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">คำขอตั้งงบลงทุน<i class="fe fe-chevron-right side-menu__angle"></i></a>
                    <ul class="slide-menu child2">
                        <li class="slide">
                            <a href="#" class="side-menu__item">รายการคำขอตั้งงบลงทุน</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">แบบฟอร์มคำขอตั้งงบลงทุน</a>
                        </li>
                    </ul>
                </li> -->
                <!-- <li class="slide side-menu__label1">
                    <a href="javascript:void(0);">การจัดการข้อมูลการนุมัติ</a>
                </li>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">การจัดการข้อมูลการนุมัติ<i class="fe fe-chevron-right side-menu__angle"></i></a>
                    <ul class="slide-menu child2">
                        <li class="slide">
                            <a href="#" class="side-menu__item">การอนุมัติระดับหน่วยงาน</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">การอนุมัติระดับสาขา</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">การอนุมติระดับเขต</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">การอนุมติระดับจังหวัด</a>
                        </li>
                        <li class="slide">
                            <a href="#" class="side-menu__item">การอนุมติระดับสำนักงานใหญ่</a>
                        </li>
                    </ul>
                </li> -->
                <!-- </ul>
            </li> -->
                <!-- End::ระบบงบประมาณ -->

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
                            <a href="api-sap.php" class="side-menu__item">ระบบบริหารจัดการข้อมูลพัสดุ</a>
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

                <!-- Start::รายงานบริหารจัดการข้อมูลพัสดุ -->
                <!-- <li class="slide has-sub">
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
                        <a href="#" class="side-menu__item">รายงานการเบิกพัสดุแบบพิมพ์</a>
                    </li>
                    <li class="slide">
                        <a href="#" class="side-menu__item">รายงานบันทึกร่างแผนการเบิกพัสดุแบบพิมพ์</a>
                    </li>
                    <li class="slide side-menu__label1">
                        <a href="javascript:void(0);">รายงานประวัติการขออนุมัติ</a>
                    </li>
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">รายงานประวัติการขออนุมัติ<i class="fe fe-chevron-right side-menu__angle"></i></a>
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
                <!-- End::รายงานบริหารจัดการข้อมูลพัสดุ -->

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
                <!-- <li class="slide__category"><span class="category-name">การตั้งค่า</span></li> -->
                <!-- End::slide__การตั้งค่า -->

                <!-- Start::slide -->
                <!-- <li class="slide has-sub">
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
                        <a href="roles-permission.php" class="side-menu__item">Roles & Permision</a>
                    </li>
                    <li class="slide">
                        <a href="activity-log.php" class="side-menu__item">Activity Log</a>
                    </li>
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">การตั้งค่าเงื่อนไขระบบ
                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                        <ul class="slide-menu child2">
                            <li class="slide">
                                <a href="#" class="side-menu__item">การจัดการไตรมาส</a>
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

            </li> -->
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
    });
</script>