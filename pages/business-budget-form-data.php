<?php
    if(isset($info)) {
        $total_summary_budget = $info['activity_budgets']['total_summary_amount'] + $info['expenses_budgets']['total_summary_amount'];
        $total_summary_budget = number_format($total_summary_budget, 2, '.', ',');
    }
?>

<style>
        body .card-body{
            /*background: #EEE;*/
        }
        tr, th, tr td, .form-check-input { border-color: black}
        .text_box input[type="text"]:focus, textarea {
            border: 0;
            outline: none;
            box-shadow: none;
            background: transparent;
        }

        .text_box {
            padding: 0 10px !important;
        }

        .text_box input {
            height: 43px;
        }

        .activity_name {
            width: 100%;
            height: 100%;
            resize: none;
            box-sizing: border-box;
            border: none;
        }

        td {
            position: relative;
        }

        td .activity_name {
            width: 100%;
            height: 100%;
            position:absolute;
            top: 0;
            left: 0;
        }
        #activity_table tbody.group tr:first-child .checkbox {
            display: none;
        }
        .bg_warning1 {
            background-color:MediumSeaGreen !important;
        }
        .bg_warning2 {
            background-color:rgb(102, 153, 51) !important;
        }
        .bg_warning3 {
            background-color:rgb(255, 255, 0) !important;
        }
        .bg_warning4 {
            background-color:Orange !important;
        }
        .bg_warning5 {
            background-color:#e74a3b !important;
        }
        .bg_warning1 input, .bg_warning2 input, .bg_warning3 input, .bg_warning4 input, .bg_warning5 input {
            background: none;
        }

        .error-data,
        .error-data:focus {
            border: 2px solid red !important;
            outline: none !important;
        }
    </style>
    <div class="card custom-card">
        <div class="card-header">
            <div class="card-title">ส่วนที่ 1 : ข้อมูลทั่วไป</div>
        </div>
        <div class="card-body border-bottom">
            <div class="row">
                <div class="form-group col-xl-6 mb-2">
                    <label class="form-label mt-2">ปีงบประมาณ :</label>
                    <select class="form-control" data-trigger name="year_name" id="year_name" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php for($iy = $startYear; $iy <= $endYear; $iy++): ?>
                            <option value="<?php echo $iy; ?>" <?php echo (isset($info) && $iy == $info['year_name']) ? 'selected' : null; ?>><?php echo $iy; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group col-xl-6 mb-2">
                    <label class="form-label mt-2">เวอร์ชั่น (แผน):</label>
                    <?php if(isset($info) && $info['status'] != 'draft'): ?>
                        <select class="form-control" name="version_name" id="version_name" <?php echo $permission; ?> required>
                            <option value="">กรุณาเลือก</option>
                            <?php foreach(getVersions() as $iv => $rv): ?>
                                <option value="<?php echo $iv;?>" <?php echo (isset($info) && $iv == $info['version_name']) ? 'selected' : null; ?>><?php echo $rv;?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select class="form-control" name="version_name" id="version_name" <?php echo $permission; ?> required>
                            <?php foreach(getVersions() as $iv => $rv): ?>
                                <?php if($versionUser == $iv || $versionUser == 0): ?>
                                    <option value="<?php echo $iv;?>" <?php echo (isset($info) && $iv == $info['version_name']) ? 'selected' : null; ?>><?php echo $rv;?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="form-group col-xl-6 mb-2">
                    <label class="form-label mt-2">ประเภทโครงการ :</label>
                    <select class="form-control" data-trigger name="project_type_name" id="project_type_name" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php foreach(getProjectTypes() as $ipt => $rpt): ?>
                            <option value="<?php echo $ipt;?>" <?php echo (isset($info) && $ipt == $info['project_type_name']) ? 'selected' : null; ?>><?php echo $rpt;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- โครงการเดิม -->
                <div class="form-group project_type_name_1 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '1') ? null : 'd-none'; ?>">
                    <label for="original_project" class="form-label mt-2">ลักษณะโครงการ :</label>
                    <select class="form-control" data-trigger name="original_project" id="original_project" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php foreach(getOriginalProject() as $iop => $rop): ?>
                            <option value="<?php echo $iop;?>" <?php echo (isset($info) && $iop == $info['original_project']) ? 'selected' : null; ?>><?php echo $rop;?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- โครงการเดิม >> โครงการระยะสั้น-->
                    <div class="form-group original_project_1 col-xl-12 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '1' && $info['original_project'] == '1') ? null : 'd-none'; ?>">
                        <label class="form-label mt-2">โครงการระยะสั้น</label>
                        <textarea class="form-control" id="original_project_description" style="color:red; font-size:12px;" disabled>มีระยะเวลาดําเนินงานไม่เกิน 1 ปี โดยมีการปรับปรุงโครงการเพื่อเสนอของบประมาณครั้งใหม่ มีวัตถุประสงค์ เป้าหมาย กิจกรรม ขั้นตอน วิธีการ ใกล้เคียงกับโครงการที่เคยดําเนินงาน</textarea>
                    </div>

                    <!-- โครงการเดิม >> โครงการต่อเนื่อง-->
                    <div class="form-group original_project_2 col-xl-12 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '1' && $info['original_project'] == '2') ? null : 'd-none'; ?>">
                        <div class="row">
                            <div class="col-xl-2 mb-2">
                                <label for="fiscal_year1" class="form-label mt-2">ตั้งแต่ :</label>
                                <input type="text" required  <?php echo $permission; ?> class="form-control" value="<?php echo (isset($info) && $info['fiscal_year1']) ? $info['fiscal_year1'] : null; ?>" name="fiscal_year1" id="fiscal_year1">
                            </div> 
                            <div class="col-xl-2 mb-2">
                                <label for="fiscal_year2" class="form-label mt-2">ไปจนถึง :</label>
                                <input type="text" required <?php echo $permission; ?> class="form-control" value="<?php echo (isset($info) && $info['fiscal_year2']) ? $info['fiscal_year2'] : null; ?>" name="fiscal_year2" id="fiscal_year2">
                            </div>
                            <div class="col-xl-2 mb-2">
                                <label for="fiscal_year3" class="form-label mt-2">ปีนี้เป็นปีที่ :</label>
                                <input type="text" required <?php echo $permission; ?> class="form-control" value="<?php echo (isset($info) && $info['fiscal_year3']) ? $info['fiscal_year3'] : null; ?>" name="fiscal_year3" id="fiscal_year3">
                            </div>  
                            <div class="col-xl-12 mb-2">
                                <label class="form-label mt-2">หมายเหตุ</label>
                                <textarea class="form-control" id="product-description-add" row="1" style="color:red; font-size:12px;" disabled>คือ โครงการที่ มีลักษณะการดําเนินงานเป็นขั้นตอนต่อเนื่องกัน ไม่สามารถดําเนินการให้ สิ้นสุดได้ภายใน 1 ปี</textarea>
                            </div>                           
                        </div>                            
                    </div>
                </div>

                <!-- โครงการเดิม >> ลักษณะโครงการ >> ประเภทแผนงาน >> ชื่อโครงการ -->
                <div class="form-group project_type_name_1 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '1') ? null : 'd-none'; ?>">
                    <label for="project_id" class="form-label mt-2">ชื่อโครงการ :</label>
                    <select class="custom-control" name="project_code" id="project_code" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php foreach($projects as $rgp): ?>
                            <option value="<?php echo $rgp['project_code']; ?>" <?php echo (isset($info) && $rgp['project_code'] == $info['project_code']) ? 'selected' : null; ?>><?php echo $rgp['project_name']; ?></option>
                        <?php endforeach; ?>
                    </select>                            
                </div>

                <!-- โครงการใหม่ >> ลักษณะโครงการ >> ประเภทแผนงาน >> ชื่อกิจกรรม -->
                <div class="form-group project_type_name_1 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '1') ? null : 'd-none'; ?>">
                    <label for="activity_id" class="form-label mt-2">ชื่อกิจกรรม :</label>
                    <select class="form-control" name="activity_code[]" id="activity_id" multiple <?php echo $permission; ?> required>
                        <?php foreach($projectActivity as $rpa): ?>
                            <option value="<?php echo $rpa['activity_code']; ?>" <?php echo (in_array($rpa['activity_code'], $info['activity_code'])) ? 'selected' : null; ?>><?php echo $rpa['activity_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ -->
                <div class="form-group project_type_name_2 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '2') ? null : 'd-none'; ?>">
                    <div class="row">
                        <div class="col-xl-12 mb-2">
                            <label for="processing_time" class="form-label mt-2">ระยะเวลาดําเนินการ/ปี :</label>
                            <input type="text" required class="form-control" name="processing_time" id="processing_time" value="<?php echo ((isset($info) && $info['processing_time']) ? $info['processing_time'] : null); ?>" <?php echo $permission; ?>>
                        </div> 
                        <div class="col-xl-12 mb-2">
                            <label class="form-label mt-2">หมายเหตุ</label>
                            <textarea class="form-control" id="product-description-add" row="" style="color:red; font-size:12px;" disabled>คือ โครงการที่ไม่เคยได้รับงบประมาณมาก่อน อาจมีระยะเวลาสิ้นสุดโครงการภายใน 1 ปี หรือมากกว่า 1 ปี</textarea>
                        </div>                           
                    </div>                            
                </div> 
                
                <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน -->
                <div class="form-group col-xl-6 mb-2">
                    <label class="form-label mt-2">ประเภทแผนงาน :</label>
                    <select class="form-control" data-trigger name="plan_type" name="plan_type" id="plan_type" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php foreach(getPlanTypes() as $key => $rs): ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($info) && $info['plan_type'] == $key ? 'selected' : null); ?>><?php echo $rs; ?></option>
                        <?php endforeach; ?>
                    </select>                            
                </div> 

                <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน >> ชื่อโครงการ -->
                <div class="form-group project_type_name_2 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '2') ? null : 'd-none'; ?>">
                    <label for="project_name" class="form-label mt-2">ชื่อโครงการ :</label>
                    <input type="text" required class="form-control" name="project_name" id="project_name" value="<?php echo ((isset($info) && $info['project_name']) ? $info['project_name'] : null); ?>" <?php echo $permission; ?>>                            
                </div>

                <!-- โครงการใหม่ >> ระยะเวลาดําเนินการ >> ประเภทแผนงาน >> ชื่อกิจกรรม -->
                <div class="form-group project_type_name_2 project_type_name_2 col-xl-6 mb-2 <?php echo (isset($info) && $info['project_type_name'] == '2') ? null : 'd-none'; ?>">
                    <label for="activity_name" class="form-label mt-2">ชื่อกิจกรรม :</label>
                    <input type="text" required class="form-control" name="activity_name" id="activity_name" value="<?php echo ((isset($info) && $info['activity_name']) ? $info['activity_name'] : null); ?>" <?php echo $permission; ?>>                            
                </div>
                
                <div class="form-group col-xl-6 mb-2">
                    <label for="budget_source_code" class="form-label mt-2">แหล่งงบประมาณ :</label>
                    <select class="form-control" data-trigger name="budget_source_code" id="budget_source_code" <?php echo $permission; ?> required>
                        <option value="">กรุณาเลือก</option>
                        <?php foreach($budgetSource as $key => $rs): ?>
                            <option value="<?php echo $rs['id']; ?>" <?php echo (isset($info) && $info['budget_source_code'] == $rs['id'] ? 'selected' : null); ?>><?php echo $rs['id'].' - '.$rs['full_name']; ?></option>
                        <?php endforeach; ?>
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
                            <select class="form-control" name="national_strategy_id" id="strategies" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php foreach($strategies as $kst => $rst): ?>
                                    <option value="<?php echo $rst['id']; ?>" <?php echo ((isset($info) && $info['national_strategy_id'] === $rst['id']) ? 'selected' : ''); ?>><?php echo $rst['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.1.1 เป้าหมาย :</label>
                            <select class="form-control" name="target_strategy_id" id="target_strategies" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($targetStrategies) && count($targetStrategies) > 0): ?>
                                    <?php foreach($targetStrategies as $ksi => $rsi): ?>
                                        <option value="<?php echo $rsi['id']; ?>" <?php echo ((isset($info) && $info['target_strategy_id'] === $rsi['id']) ? 'selected' : ''); ?>><?php echo $rsi['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.1.2 ประเด็น :</label>
                            <select class="form-control" name="subject_strategy_id" id="subject_strategies" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($subjectStrategies) && count($subjectStrategies) > 0): ?>
                                    <?php foreach($subjectStrategies as $kts => $rts): ?>
                                        <option value="<?php echo $rts['id']; ?>" <?php echo ((isset($info) && $info['subject_strategy_id'] === $rts['id']) ? 'selected' : ''); ?>><?php echo $rts['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2.2 แผนแม่บทภายใต้ยุทธศาสตร์ชาติ (Y) ประเด็น -->
                <div class="col-xl-12 mb-2">
                    <div class="row">
                        <div class="col-xl-6 mb-2">
                            <label for="input-label" class="form-label">2.2 แผนแม่บทภายใต้ยุทธศาสตร์ชาติ (Y) ประเด็น :</label>
                            <select class="form-control" name="plan_under_strategy_id" id="plan_under_strategies" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php foreach($planUnderStrategies as $kpus => $rpus): ?>
                                    <option value="<?php echo $rpus['id']; ?>" <?php echo ((isset($info) && $info['plan_under_strategy_id'] === $rpus['id']) ? 'selected' : ''); ?>><?php echo $rpus['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.2.1 เป้าหมายระดับประเด็น (Y2) :</label>
                            <select class="form-control" name="target_level_subject_id" id="target_level_subject" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($targetLevelSubjects) && count($targetLevelSubjects) > 0): ?>
                                    <?php foreach($targetLevelSubjects as $kss => $rss): ?>
                                        <option value="<?php echo $rss['id']; ?>" <?php echo ((isset($info) && $info['target_level_subject_id'] === $rss['id']) ? 'selected' : ''); ?>><?php echo $rss['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.2.2 แผนย่อยของแผนแม่บทฯ :</label>
                            <select class="form-control" name="sub_plan_id" id="sub_plan" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($subPlans) && count($subPlans) > 0): ?>
                                    <?php foreach($subPlans as $ksp => $rsp): ?>
                                        <option value="<?php echo $rsp['id']; ?>" <?php echo ((isset($info) && $info['sub_plan_id'] === $rsp['id']) ? 'selected' : ''); ?>><?php echo $rsp['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-xl-2 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.2.3 เป้าหมายแผนแม่บทย่อย (Y1) :</label>
                            <select class="form-control" name="target_sub_plan_id" id="target_sub_plan" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($targetSubPlans) && count($targetSubPlans) > 0): ?>
                                    <?php foreach($targetSubPlans as $ktsp => $rtsp): ?>
                                        <option value="<?php echo $rtsp['id']; ?>" <?php echo ((isset($info) && $info['target_sub_plan_id'] === $rtsp['id']) ? 'selected' : ''); ?>><?php echo $rtsp['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2.3 แผนวิสาหกิจการยางแห่งประเทศไทย พ.ศ. 2566 - 2570 (ฉบับทบทวนปี 2570) -->
                <div class="col-xl-12 mb-2">
                    <div class="row">
                        <div class="col-xl-12 mb-2">
                            <label for="input-label" class="form-label">2.3 แผนวิสาหกิจการยางแห่งประเทศไทย พ.ศ. 2566 - 2570 (ฉบับทบทวนปี 2570) :</label>
                            <!--<select class="form-control" name="" id="t" <?php echo $permission; ?>>
                                <option value="">กรุณาเลือก</option>
                            </select>-->
                        </div>
                        <div class="col-xl-6 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.3.1 ยุทธศาสตร์ที่ :</label>
                            <select class="form-control" data-trigger name="plan_strategy_id" id="plan_strategies" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php foreach($planStrategies as $kps => $rps): ?>
                                    <option value="<?php echo $rps['id']; ?>" <?php echo ((isset($info) && $info['plan_strategy_id'] === $rps['id']) ? 'selected' : ''); ?>><?php echo $rps['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.3.2 ตัวชี้วัดตามยุทธศาสตร์ :</label>
                            <select class="form-control" name="sub_indicator_id" id="sub_indicators" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($subIndicators) && count($subIndicators) > 0): ?>
                                    <?php foreach($subIndicators as $ksi => $rsi): ?>
                                        <option value="<?php echo $rsi['id']; ?>" <?php echo ((isset($info) && $info['sub_indicator_id'] === $rsi['id']) ? 'selected' : ''); ?>><?php echo $rsi['name']; ?></option>
                                    <?php endforeach; ?>    
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 mb-2">
                            <label for="input-label" class="form-label fw-normal">2.3.3 กลยุทธ์ที่ :</label>
                            <select class="form-control" name="tactic_id" id="tactics" <?php echo $permission; ?> required>
                                <option value="">กรุณาเลือก</option>
                                <?php if(isset($tactics) && count($tactics) > 0): ?>
                                    <?php foreach($tactics as $kt => $rt): ?>
                                        <option value="<?php echo $rt['id']; ?>" <?php echo ((isset($info) && $info['tactic_id'] === $rt['id']) ? 'selected' : ''); ?>><?php echo $rt['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                    <label for="input-label" class="form-label mt-2">3.1 หลักการและเหตุผล (อธิบายที่มาและความสําคัญของโครงการ โดยมีความสอดคล้องกับ ส่วนที่ 2 และมีข้อมูลเชิงประจักษ์ เช่น สถิติ/ข้อเท็จจริง รวมทั้งแสดงถึงผลลัพธ์ที่จะทําให้โครงการบรรลุ เป้าหมายได้) <small class="text-danger fw-normal ml-2"> *กรุณานำเข้าเฉพาะไฟล์ PDF </small>: </label>
                    <input class="form-control" type="file" name="image" id="formFile">
                    <input type="hidden" name="image_path" value="<?php echo isset($info) ? $info['image_path'] : null; ?>" />
                    <?php if(isset($info) && $info['image_path']): ?>
                        <a href="<?php echo $baseUrl.'/'.$info['image_path']; ?>"  class="btn btn-success-light mt-3" download>Download File</a>
                    <?php endif; ?>
                </div>

                <!-- 3.2 วัตถุประสงค์ -->
                <div class="col-xl-12 mb-2 group">
                    <label for="input-label" class="form-label mt-2">3.2 วัตถุประสงค์ :</label>
                    <div class="table-responsive mb-2">
                        <table class="table fw-normal table-bordered">
                            <thead class="text-center">
                                <tr>
                                    <th scope="col"><input class="form-check-input check_all" type="checkbox" id="all-products" value="" aria-label="..." <?php echo $permission; ?>></th>
                                    <th scope="col" class="fw-normal">รายละเอียดวัตถุประสงค์ แผนงาน/โครงการ/กิจกรรม</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if (
                                    isset($info['objective']) && is_array($info['objective']) &&
                                    count(array_filter($info['objective'], function($v){
                                        return isset($v['objective']) && trim($v['objective']) !== '';
                                    }))
                                ): ?>
                                    <?php foreach($info['objective'] as $key => $rs): ?>
                                        <tr class="product-list">
                                            <td class="checkbox" style="width:5%">
                                                <input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>>
                                            </td>
                                            <td class="fw-normal text_box" style="text-align: left; width:95%">
                                                <input type="text" required class="border-0" name="objective[]" style="width:100%" value="<?php echo $rs['objective']; ?>" <?php echo $permission; ?> >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="product-list">
                                        <td class="checkbox" style="width:5%">
                                            <input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>>
                                        </td>
                                        <td class="fw-normal text_box" style="text-align: left; width:95%">
                                            <input type="text" required class="border-0" name="objective[]" style="width:100%" <?php echo $permission; ?>>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!$permission): ?>
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                            <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 3.3 ตัวชี้วัดความสำเร็จของโครงการ -->
                <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">3.3 ตัวชี้วัดความสำเร็จของโครงการ :</label>
                </div>
                <div class="col-xl-12 mb-2">

                    <!-- 3.3.1 ผลผลิต (Output) -->
                    <div class="pb-3 group">
                        <label for="input-label" class="form-label fw-normal mt-2">3.3.1 ผลผลิต (Output) :</label>
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:50px;">
                                            <input class="form-check-input check_all" type="checkbox" id="all-products" value="" aria-label="..." <?php echo $permission; ?>>
                                        </th>
                                        <th colspan="2" scope="col" class="fw-normal" fdprocessedid="n9l8l6">ตัวชี้วัด</th>
                                        <th scope="col" class="fw-normal">ค่าเป้าหมาย</th>
                                        <th scope="col" class="fw-normal">หน่วยนับ</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <?php if (
                                        (isset($info['output_indicators']) && is_array($info['output_indicators']) &&
                                            count(array_filter($info['output_indicators'], function($v){ 
                                                return isset($v['output_indicators']) && trim($v['output_indicators']) !== ''; 
                                            }))
                                        ) ||
                                        (isset($info['output_target']) && is_array($info['output_target']) &&
                                            count(array_filter($info['output_target'], function($v){ 
                                                return isset($v['output_target']) && trim($v['output_target']) !== ''; 
                                            }))
                                        ) ||
                                        (isset($info['output_counting']) && is_array($info['output_counting']) &&
                                            count(array_filter($info['output_counting'], function($v){ 
                                                return isset($v['output_counting']) && trim($v['output_counting']) !== ''; 
                                            }))
                                        )
                                    ): ?>                                 
                                        <?php foreach($info['output_indicators'] as $key => $rs): ?>
                                            <tr class="product-list">
                                                <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                                <td class="fw-normal" style="width:15%">ผลผลิต (Output) :</td>
                                                <td class="fw-normal text_box" style="text-align: left; width:50%"><input type="text" required class="border-0" value="<?php echo isset($rs['output_indicators']) ? $rs['output_indicators'] : ''; ?>" name="output_indicators[]" style="width:100%" <?php echo $permission; ?>></td>
                                                <td class="fw-normal text_box" style="width:15%"><input type="text" required class="border-0" value="<?php echo isset($rs['output_target']) ? $rs['output_target'] : ''; ?>" name="output_target[]" style="width:100%" <?php echo $permission; ?>></td>
                                                <td class="fw-normal text_box" style="width:15%"><input type="text" required class="border-0" value="<?php echo isset($rs['output_counting']) ? $rs['output_counting'] : ''; ?>" name="output_counting[]" style="width:100%" <?php echo $permission; ?>></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="product-list">
                                            <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                            <td class="fw-normal" style="width:15%">ผลผลิต (Output) :</td>
                                            <td class="fw-normal text_box" style="text-align: left; width:50%"><input type="text" required class="border-0" name="output_indicators[]" style="width:100%" <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="width:15%"><input type="text" required class="border-0" name="output_target[]" style="width:100%" <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="width:15%"><input type="text" required class="border-0" name="output_counting[]" style="width:100%" <?php echo $permission; ?>></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(!$permission): ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 3.3.2 ผลลัพธ์ (Outcome) : -->
                    <div class="pb-3 group">
                        <label for="input-label" class="form-label fw-normal mt-2">3.3.2 ผลลัพธ์ (Outcome) : </label>
                        <div class="table-responsive mb-2">
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th scope="col" style="width:50px;">
                                            <input class="form-check-input check_all" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>>
                                        </th>
                                        <th colspan="2" scope="col" class="fw-normal">ตัวชี้วัด</th>
                                        <th scope="col" class="fw-normal">ค่าเป้าหมาย</th>
                                        <th scope="col" class="fw-normal">หน่วยนับ</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <?php if (
                                        (isset($info['outcome_indicators']) && is_array($info['outcome_indicators']) &&
                                            count(array_filter($info['outcome_indicators'], function($v){ 
                                                return isset($v['outcome_indicators']) && trim($v['outcome_indicators']) !== ''; 
                                            }))
                                        ) ||
                                        (isset($info['outcome_target']) && is_array($info['outcome_target']) &&
                                            count(array_filter($info['outcome_target'], function($v){ 
                                                return isset($v['outcome_target']) && trim($v['outcome_target']) !== ''; 
                                            }))
                                        ) ||
                                        (isset($info['outcome_counting']) && is_array($info['outcome_counting']) &&
                                            count(array_filter($info['outcome_counting'], function($v){ 
                                                return isset($v['outcome_counting']) && trim($v['outcome_counting']) !== ''; 
                                            }))
                                        )
                                    ): ?>
                                        <?php foreach($info['outcome_indicators'] as $key => $rs): ?>
                                            <tr class="product-list">
                                                <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                                <td class="fw-normal" style="width:15%">ผลผลิต (Output) :</td>
                                                <td class="fw-normal text_box" style="text-align: left; width:50%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['outcome_indicators']) ? $rs['outcome_indicators'] : ''; ?>" name="outcome_indicators[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['outcome_target']) ? $rs['outcome_target'] : ''; ?>" name="outcome_target[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['outcome_counting']) ? $rs['outcome_counting'] : ''; ?>" name="outcome_counting[]" style="width:100%"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="product-list">
                                            <td class="checkbox"  style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                            <td class="fw-normal" style="width:15%">ผลผลิต (Outcome) :</td>
                                            <td class="fw-normal text_box" style="text-align: left; width:50%"><input type="text" required <?php echo $permission; ?> class="border-0" name="outcome_indicators[]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" name="outcome_target[]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" name="outcome_counting[]" style="width:100%"></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(!$permission): ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 3.4 กลุ่มเป้าหมาย / ผู้ที่ได้รับประโยชน์ -->
                    <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">3.4 กลุ่มเป้าหมาย / ผู้ที่ได้รับประโยชน์ :</label>
                </div>
                <div class="col-xl-12 mb-2 group">
                    <div class="table-responsive mb-2">
                        <table class="table text-nowrap table-bordered">
                            <thead class="text-center">
                                <tr>
                                    <th scope="col" style="width:50px;">
                                        <input class="form-check-input check_all" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>>
                                    </th>
                                    <th scope="col" class="fw-normal">กลุ่มเป้าหมาย</th>
                                    <th scope="col" class="fw-normal">จำนวน</th>
                                    <th scope="col" class="fw-normal">พื้นที่ของกลุ่มเป้าหมาย</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if(isset($info) && count($info['target_group']) > 0): ?>
                                    <?php foreach($info['target_group'] as $key => $rs): ?>
                                        <tr class="product-list">
                                            <td class="checkbox"  style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="width:45%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['target_group']; ?>" name="target_group[]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="width:20%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['target_number']; ?>" name="target_number[]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="width:30%"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['target_area']; ?>" name="target_area[]" style="width:100%"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="product-list">
                                        <td class="checkbox"  style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                        <td class="fw-normal text_box" style="width:45%"><input type="text" required <?php echo $permission; ?> class="border-0" name="target_group[]" style="width:100%"></td>
                                        <td class="fw-normal text_box" style="width:20%"><input type="text" required <?php echo $permission; ?> class="border-0" name="target_number[]" style="width:100%"></td>
                                        <td class="fw-normal text_box" style="width:30%"><input type="text" required <?php echo $permission; ?> class="border-0" name="target_area[]" style="width:100%"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!$permission): ?>
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                            <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- 3.5 ประโยชน์ที่คาดว่าจะได้รับ -->
                <div class="col-xl-12 mb-2 group">
                    <label for="input-label" class="form-label mt-2">3.5 ประโยชน์ที่คาดว่าจะได้รับ :</label>
                    <div class="table-responsive mb-2">
                        <table class="table text-nowrap table-bordered">
                            <thead class="text-center">
                                <tr>
                                    <th scope="col" style="width:50px;">
                                        <input class="form-check-input check_all" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>>
                                    </th>
                                    <th scope="col" class="fw-normal">รายละเอียดประโยชน์ที่คาดว่าจะได้รับ</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if(isset($info) && count($info['expected_benefit']) > 0): ?>
                                    <?php foreach($info['expected_benefit'] as $key => $rs): ?>
                                        <tr class="product-list">
                                            <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:95%;"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['expected_benefit']; ?>" name="expected_benefit[]" style="width:100%"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="product-list">
                                        <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                        <td class="fw-normal text_box" style="text-align: left; width:95%;"><input type="text" required <?php echo $permission; ?> class="border-0" name="expected_benefit[]" style="width:100%"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!$permission): ?>
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                            <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 3.6 สถานที่ดําเนินการ -->
                <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">3.6 สถานที่ดําเนินการ :</label>
                    <input type="text" class="form-control" value="<?php echo ((isset($info) && $info['place']) ? $info['place'] : null); ?>" name="place" <?php echo $permission; ?> required>
                </div>

                <!-- 3.7 ระยะเวลาดําเนินการ -->
                <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">3.7 ระยะเวลาดําเนินการ :</label>
                    <input type="text" class="form-control" value="<?php echo ((isset($info) && $info['period']) ? $info['period'] : null); ?>" name="period" <?php echo $permission; ?> required>
                </div>

                <!-- 3.8 ทรัพยากรที่ใช้ในการดําเนินงาน -->
                <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">3.8 ทรัพยากรที่ใช้ในการดําเนินงาน :</label>
                </div>
                <div class="col-xl-12 mb-2">
                        
                    <!-- 3.8.1 งบประมาณ -->
                    <div class="row gy-2 gx-3 align-items-center mb-2">
                        <div class="col-auto">
                            <label for="input-label" class="form-label fw-normal mt-2">3.8.1 งบประมาณ :</label>
                        </div>
                        <div class="col-auto">
                            <label class="visually-hidden" for="budget_summary_amount"></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="budget_summary_amount" value="<?php echo isset($total_summary_budget) ? $total_summary_budget : 0; ?>" placeholder="0.00" disabled>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <label class="form-check-label" for="autoSizingCheck">บาท (ประมาณการค่าใช้จ่ายของโครงการ)</label>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียดกิจกรรม -->
                    <div id="activity_table">
                        <div class="table-responsive mb-2">
                            
                            <table class="table text-nowrap table-bordered">
                                <thead class="text-center">
                                    <tr>
                                        <th colspan="8" scope="col" class="fw-normal"> รายละเอียดกิจกรรม</th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="fw-normal">กิจกรรม</th>
                                        <th scope="col" class="fw-normal"><input class="form-check-input check_all" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></th>
                                        <th scope="col" class="fw-normal">ลำดับ</th>
                                        <th scope="col" class="fw-normal">รายการ</th>
                                        <th scope="col" class="fw-normal">จำนวน (หน่วย)</th>
                                        <th scope="col" class="fw-normal">ราคาต่อหน่วย</th>
                                        <th scope="col" class="fw-normal">จำนวนเงิน (บาท)</th>
                                        <th scope="col" class="fw-normal">รวม</th>
                                    </tr>
                                </thead>
                                <?php if(isset($info) && $info['activity_budgets']['list']): ?>
                                    <?php foreach($info['activity_budgets']['list'] as $key => $arr): ?>
                                        <tbody class="text-center group" data-group="<?php echo $key; ?>">
                                            <?php foreach($arr as $key2 => $rs): ?>
                                                <tr class="product-list">
                                                    <?php if($key2 <= 0): ?>
                                                        <td class="" style="text-align: left; width:35%" rowspan="<?php echo $key2; ?>"><textarea type="text" required class="form-control activity_name" <?php echo $permission; ?> name="activities[<?php echo $key; ?>][activity_name][]"><?php echo $rs['activity_name']; ?></textarea></td>
                                                    <?php endif; ?> 
                                                    <td class="product-checkbox" style="width:5%"><input class="form-check-input checkbox" type="checkbox"  value="" aria-label="..." <?php echo $permission; ?>></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number" name="activities[<?php echo $key; ?>][number_budget][]" value="<?php echo $rs['number_budget']; ?>" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" name="activities[<?php echo $key; ?>][list_budget][]" value="<?php echo $rs['list_budget']; ?>" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number quantity" value="<?php echo number_format((float)$rs['quantity'], 2); ?>" name="activities[<?php echo $key; ?>][quantity][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> <?php echo $permission; ?> class="border-0 input_number price" value="<?php echo $rs['price'] ? number_format($rs['price'], 2, '.', ',') : null; ?>" name="activities[<?php echo $key; ?>][price][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 amount" readonly  value="<?php echo $rs['amount'] ? number_format($rs['amount'], 2, '.', ',') : null; ?>" name="activities[<?php echo $key; ?>][amount][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 total_amount" value="<?php echo $rs['total_amount'] ? number_format($rs['total_amount'], 2, '.', ',') : null; ?>" readonly name="activities[<?php echo $key; ?>][total_amount][]" style="width:100%"></td>
                                                        
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tbody class="text-center group" data-group="0">
                                        <tr class="product-list">
                                            <td class="" style="text-align: left; width:35%" rowspan="1"><textarea type="text" required <?php echo $permission; ?> class="form-control activity_name" name="activities[0][activity_name][]"></textarea></td>
                                            <td class="product-checkbox" style="width:5%"><input class="form-check-input checkbox" <?php echo $permission; ?> type="checkbox"  value="" aria-label="..." <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number" <?php echo $permission; ?> name="activities[0][number_budget][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:15%"><input type="text" required <?php echo $permission; ?> class="border-0" <?php echo $permission; ?> <?php echo $permission; ?> name="activities[0][list_budget][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number quantity" <?php echo $permission; ?> name="activities[0][quantity][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number price" <?php echo $permission; ?> name="activities[0][price][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 amount" <?php echo $permission; ?> readonly name="activities[0][amount][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 total_amount" <?php echo $permission; ?> readonly name="activities[0][total_amount][]" style="width:100%"></td>
                                        </tr>
                                    </tbody>
                                <?php endif; ?>
                                <tfoot>
                                    <tr>
                                        <td scope="col" colspan="7" class="text-end">จำนวนเงินทั้งสิ้น</td>
                                        <td><input class="form-control total_summary_amount" type="text" value="<?php echo (isset($info) && $info['activity_budgets']) ? number_format($info['activity_budgets']['total_summary_amount'], 2, '.', ',') : 0; ?>" placeholder="0.00" disabled></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php if(!$permission): ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 add_row_table" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 remove_row_table" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ตารางรายละเอียดค่าใช้จ่ายในการเดินทาง -->
                    <div id="expense_table">
                        <div class="mt-3 mb-2">
                            <table class="table table-bordered">
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
                                <?php if(isset($info) && $info['expenses_budgets']['list']): ?>
                                    <?php foreach($info['expenses_budgets']['list'] as $key => $arr): ?>
                                        <tbody class="text-center group" data-group="<?php echo $key; ?>">
                                            <?php foreach($arr as $key2 => $rs): ?>
                                                <?php if($key2 <= 0): ?>
                                                    <tr class="expense_header">
                                                        <td colspan="17" class="fw-normal text-start text_box"><input type="text" required <?php echo $permission; ?> class="border-0 expense_name" name="expenses[<?php echo $key; ?>][expense_name][]" value="<?php echo $rs['expense_name']; ?>" style="width:100%"></td></td>
                                                    </tr>
                                                    <?php endif; ?> 
                                                <tr class="box">
                                                    <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..." <?php echo $permission; ?>></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 plan_trip" value="<?php echo $rs['plan_trip']; ?>" name="expenses[<?php echo $key; ?>][plan_trip][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 officer" value="<?php echo $rs['officer']; ?>" name="expenses[<?php echo $key; ?>][officer][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" <?php echo $permission; ?> class="border-0 input_number people" value="<?php echo $rs['work_days'] ? number_format($rs['people'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][people][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" <?php echo $permission; ?> class="border-0 input_number work_days" value="<?php echo $rs['work_days'] ? number_format($rs['work_days'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][work_days][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number allowance_rate" value="<?php echo $rs['allowance_rate'] ? number_format($rs['allowance_rate'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][allowance_rate][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> <?php echo $permission; ?> class="border-0 input_number allowance_amount" value="<?php echo $rs['allowance_amount'] ? number_format($rs['allowance_amount'], 2, '.', ',') : null; ?>" readonly name="expenses[<?php echo $key; ?>][allowance_amount][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number transport_rate" value="<?php echo $rs['transport_rate'] ? number_format($rs['transport_rate'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][transport_rate][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 transport_amount" value="<?php echo $rs['transport_amount'] ? number_format($rs['transport_amount'], 2, '.', ',') : null; ?>" readonly name="expenses[<?php echo $key; ?>][transport_amount][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 input_number stay_days" value="<?php echo $rs['stay_days'] ? number_format($rs['stay_days'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][stay_days][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number stay_rate" value="<?php echo $rs['stay_rate'] ? number_format($rs['stay_rate'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][stay_rate][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 stay_amount" value="<?php echo $rs['stay_amount'] ? number_format($rs['stay_amount'], 2, '.', ',') : null; ?>" readonly name="expenses[<?php echo $key; ?>][stay_amount][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 input_number fuel_trips" value="<?php echo $rs['fuel_trips'] ? number_format($rs['fuel_trips'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][fuel_trips][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number fuel_rate" value="<?php echo $rs['fuel_rate'] ? number_format($rs['fuel_rate'], 2, '.', ',') : null; ?>" name="expenses[<?php echo $key; ?>][fuel_rate][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 fuel_amount" value="<?php echo $rs['fuel_amount'] ? number_format($rs['fuel_amount'], 2, '.', ',') : null; ?>" readonly name="expenses[<?php echo $key; ?>][fuel_amount][]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 total_activity" value="<?php echo $rs['total_activity'] ? number_format($rs['total_activity'], 2, '.', ',') : null; ?>" readonly name="expenses[<?php echo $key; ?>][total_activity][]" style="width:100%"></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tbody class="text-center group" data-group="0">
                                        <tr class="expense_header">
                                            <td colspan="17" class="fw-normal text-start text_box"><input type="text" required <?php echo $permission; ?> class="border-0 expense_name" name="expenses[0][expense_name][]" style="width:100%"></td></td>
                                        </tr>
                                        <tr class="box">
                                            <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..." <?php echo $permission; ?>></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> class="border-0 plan_trip" name="expenses[0][plan_trip][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" required <?php echo $permission; ?> class="border-0 officer" name="expenses[0][officer][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number people" name="expenses[0][people][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:5%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number work_days" name="expenses[0][work_days][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number allowance_rate" name="expenses[0][allowance_rate][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width: 7%;"><input type="text" <?php echo $permission; ?> class="border-0 input_number allowance_amount" readonly name="expenses[0][allowance_amount][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number transport_rate" name="expenses[0][transport_rate][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 transport_amount" readonly name="expenses[0][transport_amount][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number stay_days" name="expenses[0][stay_days][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number stay_rate" name="expenses[0][stay_rate][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> class="border-0 stay_amount" readonly name="expenses[0][stay_amount][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" required <?php echo $permission; ?> class="border-0 input_number fuel_trips" name="expenses[0][fuel_trips][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number fuel_rate" name="expenses[0][fuel_rate][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:7%"><input type="text" <?php echo $permission; ?> <?php echo $permission; ?> class="border-0 fuel_amount" readonly name="expenses[0][fuel_amount][]" style="width:100%"></td>
                                            <td class="fw-normal text_box" style="text-align: left; width:10%"><input type="text" <?php echo $permission; ?> class="border-0 total_activity" readonly name="expenses[0][total_activity][]" style="width:100%"></td>
                                        </tr>
                                    </tbody>
                                <?php endif; ?>                                                            
                                <tfoot>
                                    <tr>
                                        <td scope="col" colspan="3" class="text-end fw-normal">จำนวนเงินทั้งสิ้น</td>
                                        <td colspan="2" disabled></td>
                                        <td colspan="2"><input class="form-control total_summary_allowance" value="<?php echo (isset($info) && $info['expenses_budgets']) ? number_format($info['expenses_budgets']['total_summary_allowance'], 2, '.', ',') : 0; ?>" type="text" placeholder="0.00" readonly></td>
                                        <td colspan="2"><input class="form-control total_summary_transport" value="<?php echo (isset($info) && $info['expenses_budgets']) ? number_format($info['expenses_budgets']['total_summary_transport'], 2, '.', ',') : 0; ?>" type="text" placeholder="0.00" readonly></td>
                                        <td colspan="3"><input class="form-control total_summary_stay" value="<?php echo (isset($info) && $info['expenses_budgets']) ? number_format($info['expenses_budgets']['total_summary_stay'], 2, '.', ',') : 0; ?>" type="text" placeholder="0.00" readonly></td>
                                        <td colspan="3"><input class="form-control total_summary_fuel" value="<?php echo (isset($info) && $info['expenses_budgets']) ? number_format($info['expenses_budgets']['total_summary_fuel'], 2, '.', ',') : 0; ?>" type="text" placeholder="0.00" readonly></td>
                                        <td style="width: 200px;"><input class="form-control total_summary_amount" value="<?php echo (isset($info) && $info['expenses_budgets']) ? number_format($info['expenses_budgets']['total_summary_amount'], 2, '.', ',') : 0; ?>" type="text" placeholder="0.00" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php if(!$permission): ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                <button class="btn btn-success-light m-1 add_row_table" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                <button class="btn btn-danger-light m-1 remove_row_table" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                            </div>
                        </div>  
                        <?php endif; ?>
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
                                    <th scope="col" class="fw-normal">ที่มีอยู่แล้ว	</th>
                                    <th scope="col" class="fw-normal">ที่ต้องการเพิ่มเติม</th>
                                    <th scope="col" class="fw-normal">เหตุผลที่ต้องการ เพิ่มเติมและ ผลกระทบหากไม่ได้ บุคลากรเพิ่มเติม</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr class="product-list">
                                    <td style="text-align: left; width:40%" class="fw-normal">1. จํานวนบุคลากร (คน)</td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['existing_staff'] : null); ?>" name="existing_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['additional_staff'] : null); ?>" name="additional_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['reason_staff'] : null); ?>" name="reason_staff" class="border-0" /></td>
                                </tr>
                                <tr class="product-list">
                                    <td style="text-align: left; width:40%" class="fw-normal">2. วุฒิ/สาขา ที่ต้องการ</td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['existing_qualification_staff'] : null); ?>" name="existing_qualification_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['additional_qualification_staff'] : null); ?>" name="additional_qualification_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['reason_qualification_staff'] : null); ?>" name="reason_qualification_staff" class="border-0" /></td>
                                </tr>
                                <tr class="product-list">
                                    <td style="text-align: left; width:40%" class="fw-normal">3. ความรู้/ทักษะ/ความสามารถที่จำเป็นความต้องการด้านหลักสูตรการอบรม สำหรับโครงการ</td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['existing_skill_staff'] : null); ?>" name="existing_skill_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['additional_skill_staff'] : null); ?>" name="additional_skill_staff" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['reason_skill_staff'] : null); ?>" name="reason_skill_staff" class="border-0" /></td>
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
                                    <th colspan="3" scope="col" class="fw-normal trigger_modal_year year3" contenteditable="true" attr-year="3"><span>พ.ศ. <?php echo (isset($info['year3']) && $info['year3'] ? $info['year3'] : null); ?></span> <input type="hidden" required class="validate-hidden" name="year3" value="<?php echo (isset($info['year3']) && $info['year3'] ? $info['year3'] : null); ?>"/></th>
                                </tr>
                                <tr>
                                    <th scope="col" class="fw-normal">ที่มีอยู่แล้ว	</th>
                                    <th scope="col" class="fw-normal">ที่ต้องการเพิ่มเติม</th>
                                    <th scope="col" class="fw-normal">เหตุผลที่ต้องการ เพิ่มเติมและ ผลกระทบหากไม่ได้ บุคลากรเพิ่มเติม</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr class="product-list">
                                    <td style="text-align: left; width:40%" class="fw-normal">1. ด้าน SOFTWARE (ระบุ ระบบงาน/โปรแกรมที่ใช้ใน การดําเนินงาน)</td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['existing_software'] : null); ?>" name="existing_software" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['additional_software'] : null); ?>" name="additional_software" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['reason_software'] : null); ?>"  name="reason_software" class="border-0" /></td>
                                </tr>
                                <tr class="product-list">
                                    <td style="text-align: left; width:40%" class="fw-normal">2. ด้าน HARDWARE (ระบุ คอมพิวเตอร์และอุปกรณ์ คอมพิวเตอร์)</td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['existing_hardware'] : null); ?>" name="existing_hardware" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['additional_hardware'] : null); ?>" name="additional_hardware" class="border-0" /></td>
                                    <td style="text-align: left; width:5%" class="fw-normal text_box"><input type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) ? $info['reason_hardware'] : null); ?>" name="reason_hardware" class="border-0" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 4: แผนการดําเนินงานและการวิเคราะห์ความเสี่ยง -->
    <div class="card custom-card">
        <div class="card-header"><div class="card-title">ส่วนที่ 4: แผนการดําเนินงานและการวิเคราะห์ความเสี่ยง</div></div>
        <div class="card-body border-bottom">
            <div class="row">

                <!-- 4.1 แผนการดําเนินงาน (ให้ระบุกิจกรรม/ขั้นตอนย่อยทั้งหมด) :  -->
                    
                <div id="operation_plan" class="col-xl-12 mb-2">
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
                                    <th colspan="3" scope="col" class="fw-normal trigger_modal_year year1" contenteditable="true" attr-year="1"><span class="text">พ.ศ. <?php echo (isset($info['year1']) && $info['year1'] ? $info['year1'] : null); ?></span> <input type="hidden" class="validate-hidden" name="year1" value="<?php echo (isset($info['year1']) && $info['year1'] ? $info['year1'] : null); ?>"/></th>
                                    <th colspan="9" scope="col" class="fw-normal trigger_modal_year year2" contenteditable="true" attr-year="2"><span class="text">พ.ศ. <?php echo (isset($info['year2']) && $info['year2'] ? $info['year2'] : null); ?></span> <input type="hidden" class="validate-hidden" name="year2" value="<?php echo (isset($info['year2']) && $info['year2'] ? $info['year2'] : null); ?>"/></th>
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
                            <?php if(isset($info) && $info['operations_budgets']['list']): ?>
                                <?php foreach($info['operations_budgets']['list'] as $key => $arr): ?>
                                    <?php $total_summary_weight_amount = 0; ?>
                                    <tbody class="text-center group" data-group="<?php echo $key; ?>">
                                        <?php foreach($arr as $key2 => $rs): ?>
                                            <?php $total_summary_weight_amount += (float)$rs['weight']; ?>
                                            <?php if($key2 <= 0): ?>
                                                <tr class="operation_plan_header">
                                                    <td colspan="18" class="fw-normal text-start text_box"><input type="text" required <?php echo $permission; ?> class="border-0 operation_plan_name" name="operations[<?php echo $key; ?>][operation_plan_name][]" value="<?php echo $rs['operation_plan_name']; ?>" style="width:100%"></td></td>
                                                </tr>
                                            <?php endif; ?> 
                                            <tr class="box">
                                                <td class="product-checkbox"><input class="form-check-input" <?php echo $permission; ?> type="checkbox" id="product1" value="" aria-label="..."></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['operation_plan_activity']; ?>" name="operations[<?php echo $key; ?>][operation_plan_activity][]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number weight" value="<?php echo $rs['weight'] ? number_format($rs['weight'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][weight][]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number operation_plan_target" value="<?php echo $rs['target']; ?>" name="operations[<?php echo $key; ?>][target][]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['unit']; ?>" name="operations[<?php echo $key; ?>][unit][]" style="width:100%"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number oct" value="<?php echo $rs['oct'] ? number_format($rs['oct'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][oct][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number nov" value="<?php echo $rs['nov'] ? number_format($rs['nov'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][nov][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number dec" value="<?php echo $rs['dec'] ? number_format($rs['dec'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][dec][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jan" value="<?php echo $rs['jan'] ? number_format($rs['jan'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][jan][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number feb" value="<?php echo $rs['feb'] ? number_format($rs['feb'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][feb][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number mar" value="<?php echo $rs['mar'] ? number_format($rs['mar'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][mar][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number apr" value="<?php echo $rs['apr'] ? number_format($rs['apr'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][apr][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number may" value="<?php echo $rs['may'] ? number_format($rs['may'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][may][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jun" value="<?php echo $rs['jun'] ? number_format($rs['jun'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][jun][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jul" value="<?php echo $rs['jul'] ? number_format($rs['jul'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][jul][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number aug" value="<?php echo $rs['aug'] ? number_format($rs['aug'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][aug][]"></td>
                                                <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number sep" value="<?php echo $rs['sep'] ? number_format($rs['sep'], 2, '.', ',') : null; ?>" name="operations[<?php echo $key; ?>][sep][]"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" value="<?php echo $rs['operation_plan_assignee']; ?>" name="operations[<?php echo $key; ?>][operation_plan_assignee][]" style="width:100%"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tbody class="summary" data-set="<?php echo $key; ?>">
                                        <tr>
                                            <td scope="col" class="text-end"></td>
                                            <td scope="col"  class="text-end">รวม</td>
                                            <td colspan="16"><input class="form-control total_summary_weight_amount" type="text" value="<?php echo number_format($total_summary_weight_amount, 2, '.', ','); ?>" placeholder="0.00" disabled></td>
                                        </tr>
                                    </tbody>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tbody class="text-center group" data-group="0">
                                    <tr class="operation_plan_header">
                                        <td colspan="18" scope="col" class="fw-normal text-start text_box" style="text-align: left; width:95%"><input type="text" required <?php echo $permission; ?> class="border-0 operation_plan_name" name="operations[0][operation_plan_name][]" style="width:100%"></td></td>
                                    </tr>
                                    <tr class="box">
                                        <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..." <?php echo $permission; ?>></td>
                                        <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="operations[0][operation_plan_activity][]" style="width:100%"></td>
                                        <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0 input_number weight" name="operations[0][weight][]" style="width:100%"></td>
                                        <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0 input_number operation_plan_target" name="operations[0][target][]" style="width:100%"></td>
                                        <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="operations[0][unit][]" style="width:100%"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number oct" name="operations[0][oct][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number nov" name="operations[0][nov][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number dec" name="operations[0][dec][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jan" name="operations[0][jan][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number feb" name="operations[0][feb][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number mar" name="operations[0][mar][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number apr" name="operations[0][apr][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number may" name="operations[0][may][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jun" name="operations[0][jun][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number jul" name="operations[0][jul][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number aug" name="operations[0][aug][]"></td>
                                        <td class="fw-normal text_box"><input type="text" <?php echo $permission; ?> style="width: 48px;" class="border-0 text-center input_number sep" name="operations[0][sep][]"></td>
                                        <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="operations[0][operation_plan_assignee][]" style="width:100%"></td>
                                    </tr>
                                </tbody>
                                <tbody class="summary" data-set="0">
                                    <tr>
                                        <td scope="col" class="text-end"></td>
                                        <td scope="col"  class="text-end">รวม</td>
                                        <td colspan="16"><input class="form-control total_summary_weight_amount" type="text" value="<?php echo (isset($info) && $info['operations_budgets']) ? $info['operations_budgets']['total_summary_weight_amount'] : 0; ?>" placeholder="0.00" disabled></td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>        
                            <tfoot></tfoot>
                        </table>
                    </div>
                    <?php if(!$permission): ?>
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="border-block-start-dashed d-sm-flex justify-content-end">
                            <button class="btn btn-success-light m-1 add_row_table" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                            <button class="btn btn-danger-light m-1 remove_row_table" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 4.2 การวิเคราะห์ความเสี่ยง: -->
                <div class="col-xl-12 mb-2">
                    <label for="input-label" class="form-label mt-2">4.2 การวิเคราะห์ความเสี่ยง: :</label>
                </div>
                <div class="col-xl-12 mb-2">
                    <div class="pb-3">
                        <div class="row">
                            <div class="col-xl-12 mb-2">
                                <label for="SO001" class="form-label">วัตถุประสงค์เชิงยุทธศาสตร์ :</label>
                                <select class="form-control" data-trigger name="strategic_objective" id="choices-single-groups" <?php echo $permission; ?> required>
                                    <option value="">กรุณาเลือก วัตถุประสงค์เชิงยุทธศาสตร์</option>
                                    <?php foreach($getRiskStrategicObjectives as $key => $rs): ?>
                                        <option value="<?php echo $rs['code']; ?>" <?php echo isset($info) && $rs['code'] == $info['strategic_objective'] ? 'selected' : ''; ?>><?php echo $rs['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div> 
                            <div class="col-xl-6 mb-2">
                                <label for="yot001" class="form-label">ยุทธศาสตร์ชาติ :</label>
                                <select class="form-control" name="national_strategy" id="national_strategy" <?php echo $permission; ?> required>
                                    <option value="">กรุณาเลือก ยุทธศาสตร์ชาติ</option>
                                    <?php foreach($getRiskNationalStrategies as $key => $rs): ?>
                                        <option value="<?php echo $rs['code']; ?>" <?php echo isset($info) && $rs['code'] == $info['national_strategy'] ? 'selected' : ''; ?>><?php echo $rs['name']; ?></option> 
                                    <?php endforeach; ?>
                                </select>
                            </div> 
                            <div class="col-xl-6 mb-2">
                                <label for="yot002" class="form-label">ตัวชี้วัด :</label>
                                <select class="form-control risk_indicator" name="indicator" id="risk_indicator" <?php echo $permission; ?> required>
                                    <option value="">กรุณาเลือก ตัวชี้วัด</option>
                                    <?php if(isset($getRiskIndicators)): ?>
                                        <?php foreach($getRiskIndicators as $key => $rs): ?>
                                            <option value="<?php echo $rs['code']; ?>" <?php echo isset($info) && $rs['code'] == $info['indicator'] ? 'selected' : ''; ?>><?php echo $rs['name']; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div> 
                            <div class="col-xl-6 mb-2">
                                <label for="yot003" class="form-label">กลยุทธ์ :</label>
                                <select class="form-control risk_strategy" id="risk_strategy" name="strategy" <?php echo $permission; ?> required>
                                    <option value="">กรุณาเลือก กลยุทธ์</option>
                                    <?php if(isset($getRiskStrategies)): ?>
                                        <?php foreach($getRiskStrategies as $key => $rs): ?>
                                            <option value="<?php echo $rs['code']; ?>" <?php echo isset($info) && $rs['code'] == $info['strategy'] ? 'selected' : ''; ?>><?php echo $rs['name']; ?></option>  
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div> 
                            <div class="col-xl-6 mb-2">
                                <label for="input-label" class="form-label">ตัวชี้วัดโครงการ :</label>
                                <input type="text" <?php echo $permission; ?> class="form-control" name="project_indicator" value="<?php echo (isset($info) && $info['project_indicator']) ? $info['project_indicator'] : null; ?>" id="input-label1" placeholder="กรุณากรอก ตัวชี้วัดโครงการ" required>
                            </div>                           
                        </div>                            
                        <div class="col-xl-12 mt-2 mb-2">
                            <div class="mb-2">
                                <table class="table table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th rowspan="2" scope="col" class="fw-normal">โครงการ</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ผู้รับผิดชอบ <br> (Risk Owners)</th>
                                            <th rowspan="2" scope="col" class="fw-normal">การวิเคราะห์ความเสี่ยง <br>  (Risk Scennario)</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ปัจจัยเสี่ยง <br>  (Risk Factor)</th>
                                            <th rowspan="2" scope="col" class="fw-normal">การควบคุมภายที่มีอยู่  <br> (Exising Control)</th>
                                            <th colspan="7" scope="col" class="fw-normal">การประเมินความเสี่ยง <br>  (Risk Assessment)</th>
                                            <th rowspan="2" scope="col" class="fw-normal">แผนจัดการความเสี่ยง  <br> (Risk Response)</th>
                                            <th colspan="3" scope="col" class="fw-normal">ระดับความเสี่ยงที่เหลืออยู่  <br> (Residual Risk)</th>
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
                                            <td class="text_box" style="width: 150px;"><input type="text" style="width: 100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['risk_project']) ? $info['risk_project'] : null; ?>" class="border-0 risk_project" name="risk_project"></td>
                                            <td class="text_box" style="width: 150px;"><input type="text" style="width: 100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['risk_owners']) ? $info['risk_owners'] : null; ?>" class="border-0 risk_owners" name="risk_owners"></td>
                                            <td class="text_box" style="width: 150px;"><input type="text" style="width: 100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['risk_scennario']) ? $info['risk_scennario'] : null; ?>" class="border-0 risk_scennario" name="risk_scennario"></td>
                                            <td class="text_box" style="width: 150px;"><input type="text" style="width: 100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['risk_factor']) ? $info['risk_factor'] : null; ?>" class="border-0 risk_factor" name="risk_factor"></td>
                                            <td class="text_box" style="width: 150px;"><input type="text" style="width: 100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['exising_control']) ? $info['exising_control'] : null; ?>" class="border-0 exising_control" name="exising_control"></td>
                                            <td style="width: 50px;"><input class="form-check-input risk_assessment" required type="checkbox" name="risk_assessment" value="S" aria-label="..." <?php echo (isset($info) && $info['risk_assessment'] == 'S' ? 'checked' : null); ?> <?php echo $permission; ?>></td>
                                            <td style="width: 50px;"><input class="form-check-input risk_assessment" required type="checkbox" name="risk_assessment" value="O" aria-label="..." <?php echo (isset($info) && $info['risk_assessment'] == 'O' ? 'checked' : null); ?> <?php echo $permission; ?>></td>
                                            <td style="width: 50px;"><input class="form-check-input risk_assessment" required type="checkbox" name="risk_assessment" value="F" aria-label="..." <?php echo (isset($info) && $info['risk_assessment'] == 'F' ? 'checked' : null); ?> <?php echo $permission; ?>></td>
                                            <td style="width: 50px;"><input class="form-check-input risk_assessment" required type="checkbox" name="risk_assessment" value="C" aria-label="..." <?php echo (isset($info) && $info['risk_assessment'] == 'C' ? 'checked' : null); ?> <?php echo $permission; ?>></td>
                                            <td class="text_box text-center" style="width: 50px;"><input type="text" style="width:100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['lh']) ? $info['lh'] : null; ?>" class="border-0 lh text-center" name="lh"></td>
                                            <td class="text_box" style="width: 50px;"><input type="text" style="width:100%" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['im']) ? $info['im'] : null; ?>" class="border-0 im  text-center" name="im"></td>
                                            <td style="width: 60px;" class="text_box <?php echo (isset($info)? getRiskColor(($info['lh'] * $info['im'])) : null); ?>"><input type="text" style="width: 100%" <?php echo $permission; ?> class="border-0 level text-center" value="<?php echo ((isset($info) && $info['level']) ? $info['level'] : null); ?>" name="level" readonly></td>
                                            <td class="text_box" style="width: 130px;"><input type="text" required <?php echo $permission; ?> style="width:100%" value="<?php echo (isset($info) && $info['risk_response']) ? $info['risk_response'] : null; ?>" class="border-0 risk_response" name="risk_response"></td>
                                            <td class="text_box" style="width: 50px;"><input style="width:100%" type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['residual_lh']) ? $info['residual_lh'] : null; ?>" class="border-0 residual_lh text-center" name="residual_lh"></td>
                                            <td class="text_box" style="width: 50px;"><input style="width:100%" type="text" required <?php echo $permission; ?> value="<?php echo (isset($info) && $info['residual_im']) ? $info['residual_im'] : null; ?>" class="border-0 residual_im text-center" name="residual_im"></td>
                                            <td style="width: 60px;" class="text_box <?php echo (isset($info)? getRiskColor(($info['residual_lh'] * $info['residual_im'])) : null); ?>"><input type="text" style="width: 100%" <?php echo $permission; ?> value="<?php echo ((isset($info) && $info['residual_level']) ? $info['residual_level'] : null); ?>" class="border-0 residual_level text-center" name="residual_level" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-xl-12 mt-2 mb-2 mitigation_plan group">
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th colspan="8" scope="col" class="fw-normal">แผนจัดการความเสี่ยง (Mitigation Plan)</th>
                                        </tr>
                                        <tr>
                                            <th rowspan="2" scope="col" class="fw-normal" style="width:5%;"><input class="form-check-input check_all" type="checkbox" id="product1" value="" aria-label="..." <?php echo $permission; ?>></th>
                                            <th rowspan="2" scope="col" class="fw-normal">แผนจัดการความเสี่ยง</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ผู้รับผิดชอบ</th>
                                            <th rowspan="2" scope="col" class="fw-normal">ระยะเวลา	</th>
                                            <th rowspan="2" scope="col" class="fw-normal">งบประมาณ (ถ้ามี)	</th>
                                            <th colspan="3" scope="col" class="fw-normal">ความคืบหน้าของขั้นตอน/วิธีปฏิบัติงาน</th>
                                        </tr>
                                        <tr>
                                            <!-- <th scope="col"><input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="..."></th> -->
                                            <th scope="col" class="fw-normal">แผนจัดการความเสี่ยง	</th>
                                            <th scope="col" class="fw-normal">ผู้รับผิดชอบ	</th>
                                            <th scope="col" class="fw-normal">ระยะเวลา</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center group">
                                        <?php if(isset($info) && count($info['risk_plans']) > 0): ?>
                                            <?php foreach($info['risk_plans'] as $key => $rs): ?>
                                                <tr class="product-list">
                                                    <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['risk_management_plan']) ? $rs['risk_management_plan'] : ''; ?>" name="risk_management_plan[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['responsible_person']) ? $rs['responsible_person'] : ''; ?>" name="responsible_person[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['duration']) ? $rs['duration'] : ''; ?>" name="duration[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['budget']) ? $rs['budget'] : ''; ?>" name="budget[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['progress_risk_plan']) ? $rs['progress_risk_plan'] : ''; ?>" name="progress_risk_plan[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['progress_responsible']) ? $rs['progress_responsible'] : ''; ?>" name="progress_responsible[]" style="width:100%"></td>
                                                    <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" <?php echo $permission; ?> class="border-0" value="<?php echo isset($rs['progress_duration']) ? $rs['progress_duration'] : ''; ?>" name="progress_duration[]" style="width:100%"></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr class="product-list">
                                                <td class="checkbox" style="width:5%"><input class="form-check-input" type="checkbox" value="" aria-label="..." <?php echo $permission; ?>></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="risk_management_plan[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="responsible_person[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="duration[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left;"><input type="text" required <?php echo $permission; ?> class="border-0" name="budget[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" required <?php echo $permission; ?> class="border-0" name="progress_risk_plan[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" required <?php echo $permission; ?> class="border-0" name="progress_responsible[]" style="width:100%"></td>
                                                <td class="fw-normal text_box" style="text-align: left; width:15%;"><input type="text" required <?php echo $permission; ?> class="border-0" name="progress_duration[]" style="width:100%"></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                            <?php if(!$permission): ?>
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="border-block-start-dashed d-sm-flex justify-content-end">
                                    <button class="btn btn-success-light m-1 add_row" <?php echo $permission; ?>><i class="bi bi-plus"></i> เพิ่มรายการ</button>
                                    <button class="btn btn-danger-light m-1 remove_row" <?php echo $permission; ?>><i class="bi bi-dash"></i> ลบรายการ</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-xl-12 mt-2 mb-2">
                            <div class="table-responsive mb-2">
                                <table class="table text-nowrap table-bordered">
                                    <thead class="text-center">
                                        <tr>
                                            <th scope="col" class="fw-normal" class="fw-normal" style="background-color:#111c43; color:rgba(255, 255, 255, 1);">เกณฑ์ระดับความเสี่ยง	</th>
                                            <th scope="col" class="fw-normal" class="fw-normal" style="background-color:#111c43; color:rgba(255, 255, 255, 1);">ความหมาย	</th>
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
        <div class="card-header"><div class="card-title">ส่วนที่ 5: ผู้รับผิดชอบและผู้ประสานงานโครงการ</div></div>
        <div class="card-body border-bottom">
            <div class="row">
                <div class="col-xl-12">
                    <label for="input-label11" class="form-label">5.1 ส่วนงาน/หน่วยงานที่รับผิดชอบ :</label>
                    <input type="text" class="form-control" id="input-label11" placeholder="<?php echo $user['head_office_type']; ?>" disabled>
                </div>
                <div class="col-xl-12 mt-3">
                    <label for="input-label11" class="form-label">5.2 ผู้รับผิดชอบโครงการ :</label>
                </div>
                <div class="col-xl-12">
                    <div class="py-3">
                        <div class="row">
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">ชื่อ - สกุล :</label>
                                <input type="text" required <?php echo $permission; ?> class="form-control sec_5_name" id="input-label11" name="name_1" value="<?php echo (isset($info) && $info['name_1']) ? $info['name_1'] : null; ?>" placeholder="กรุณากรอก ชื่อ - สกุล" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">ตําแหน่ง :</label>
                                <input type="text" required <?php echo $permission; ?> class="form-control sec_5_position" id="input-label11" name="position_1" value="<?php echo (isset($info) && $info['position_1']) ? $info['position_1'] : null; ?>" placeholder="กรุณากรอก ตําแหน่ง" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">โทรศัพท์ :</label>
                                <input type="text" required <?php echo $permission; ?> pattern="^[0-9]{10}$"pattern="^[0-9]{10}$" class="form-control sec_5_tel" id="input-label11" name="tel_1" value="<?php echo (isset($info) && $info['tel_1']) ? $info['tel_1'] : null; ?>" placeholder="กรุณากรอก โทรศัพท์" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">อีเมลล์ :</label>
                                <input type="email" required <?php echo $permission; ?> class="form-control sec_5_email" id="input-label11" name="email_1" value="<?php echo (isset($info) && $info['email_1']) ? $info['email_1'] : null; ?>" placeholder="กรุณากรอก อีเมลล์" >
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="col-xl-12 mt-2">
                    <label for="input-label11" class="form-label">5.3 ผู้ประสานงานโครงการ (ผู้ที่สามารถให้ข้อมูลได้) :</label>
                </div>
                <div class="col-xl-12">
                    <div class="py-3">
                        <div class="row">
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">ชื่อ - สกุล :</label>
                                <input type="text" required <?php echo $permission; ?> class="form-control sec_5_name" id="input-label11" name="name_2" value="<?php echo (isset($info) && $info['name_2']) ? $info['name_2'] : null; ?>" placeholder="กรุณากรอก ชื่อ - สกุล" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">ตําแหน่ง :</label>
                                <input type="text" required <?php echo $permission; ?> <?php echo $permission; ?> class="form-control sec_5_position" id="input-label11" name="position_2" value="<?php echo (isset($info) && $info['position_2']) ? $info['position_2'] : null; ?>" placeholder="กรุณากรอก ตําแหน่ง" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">โทรศัพท์ :</label>
                                <input type="text" required <?php echo $permission; ?> pattern="^[0-9]{10}$" class="form-control sec_5_tel" id="input-label11" name="tel_2" value="<?php echo (isset($info) && $info['tel_2']) ? $info['tel_2'] : null; ?>" placeholder="กรุณากรอก โทรศัพท์" >
                            </div>
                            <div class="col-xl-6 mb-2">
                                <label class="form-label mt-2">อีเมลล์ :</label>
                                <input type="email" required <?php echo $permission; ?> class="form-control sec_5_email" id="input-label11" name="email_2" value="<?php echo (isset($info) && $info['email_2']) ? $info['email_2'] : null; ?>" placeholder="กรุณากรอก อีเมลล์" >
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>                                
        </div>                               
    </div> 

     <!-- Start::add board modal -->
    <div class="modal fade" id="add-board" tabindex="-1" aria-hidden="true">
        <input type="hidden" value="" class="attr_year"/>
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
                            <select class="form-control dropdown_year" data-trigger name="choices-single-groups" id="choices-single-groups">
                                <option value="">กรุณาเลือก กลยุทธ์</option>
                                <?php for($iy = $startYear; $iy <= $endYear; $iy++): ?>
                                    <option value="<?php echo $iy; ?>">พ.ศ. <?php echo $iy; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary submit_year">ตกลง</button>
                </div>
            </div>
        </div>
    </div>