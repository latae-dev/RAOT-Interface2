<?php
    include_once __DIR__ . '/../../configs/init.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_report_model.php';
    require ROOT_PATH. '/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Style\Color;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;

    if (session_id() === '') {
        session_start();
    }

class BusinessBudgetReportController
{
    private $model;

    public function __construct()
    {
        $database = new Database(); 
        $db = $database->connect('raot_db_qas');
        $this->model = new BusinessBudgetReportModel($db);
    }

    public function getBusinessReportList($param = []) 
    {
        return $this->model->getBusinessReportList($param);
    }

    /*public function exportType1($params)
    {
        $res = $this->model->exportList1($params);
        $data = [];

        foreach ($res as $rs) {
            $project_code = !empty($rs['st_code']) ? $rs['st_code'] : uniqid();
            $project_names = [];
            $project_id_used = [];

            if (!isset($data[$project_code])) {
                $activity_names = [];
                $activity_id_used = [];
                
                foreach($rs['activity'] as $ar) {
                    if(!isset($activity_id_used[$ar['id']])) {
                        $activity_names[] = $ar['activity_name'];
                        $activity_id_used[$ar['id']] = true;
                    }
                }

                $activity_string = implode("\n", $activity_names);

                $data[$project_code] = [
                    'st_code' => $rs['st_code'],
                    'st_name' => $rs['st_name'],
                    'project_code' => $rs['project_code'],
                    'full_name' => $rs['full_name'],
                    'project_name' => $rs['project_name'],
                    'budget_source_code' => $rs['budget_source_code'],
                    'activity' => $activity_string,
                    'depart' => []
                ];
            }

            if (!isset($data[$project_code]['depart'][$rs['depart_code']])) {
                $data[$project_code]['depart'][$rs['depart_code']] = [
                    'depart_code' => $rs['depart_code'],
                    'total_amount' => $rs['total_amount'] ?? 0,
                    'output_indicators' => [],
                    'outcome_indicators' => [],
                ];
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] = $rs[$m] ?? 0;
                }
            } else {
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] += $rs[$m] ?? 0;
                }
                $data[$project_code]['depart'][$rs['depart_code']]['total_amount'] += $rs['total_amount'] ?? 0;
            }

            if (!empty($rs['output_indicators']) && is_array($rs['output_indicators'])) {
                foreach ($rs['output_indicators'] as $oi) {
                    if (is_array($oi) && !empty($oi['id'])) {
                        $data[$project_code]['depart'][$rs['depart_code']]['output_indicators'][$oi['id']] = $oi;
                    }
                }
            }

            if (!empty($rs['outcome_indicators']) && is_array($rs['outcome_indicators'])) {
                foreach ($rs['outcome_indicators'] as $oo) {
                    if (is_array($oo) && !empty($oo['id'])) {
                        $data[$project_code]['depart'][$rs['depart_code']]['outcome_indicators'][$oo['id']] = $oo;
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $months = ['ต.ค.','พ.ย.','ธ.ค.','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.'];

        $monthKeys = ['total_oct','total_nov','total_dec','total_jan','total_feb','total_mar','total_apr','total_may','total_jun','total_jul','total_aug','total_sep'];
        $row = 1;

        $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์ ระหว่าง วันที่ '.setDateTimeFormat($params['start_date'], 'd/m/Y').' ถึง '.setDateTimeFormat($params['end_date'], 'd/m/Y');
        $sheet->mergeCells("A{$row}:U{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        if ($data) {
            foreach ($data as $project) {
                $sheet->mergeCells("A{$row}:U{$row}");
                $sheet->setCellValue("A{$row}", 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์ ('.$project['st_name'].')');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                $headerStyle = $sheet->getStyle("A{$row}:U".($row+1));
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $headerStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);

                $sheet->mergeCells("A{$row}:A".($row+1)); $sheet->setCellValue("A{$row}", 'รหัสโครงการ');
                $sheet->mergeCells("B{$row}:B".($row+1)); $sheet->setCellValue("B{$row}", 'โครงการ');
                $sheet->mergeCells("C{$row}:C".($row+1)); $sheet->setCellValue("C{$row}", 'กิจกรรม'); 
                
                $sheet->mergeCells("D{$row}:F{$row}"); $sheet->setCellValue("D{$row}", 'ตัวชี้วัดความสำเร็จโครงการ');
                $sheet->setCellValue("D".($row+1), 'ตัวชี้วัด');
                $sheet->setCellValue("E".($row+1), 'ค่าเป้าหมาย');
                $sheet->setCellValue("F".($row+1), 'หน่วยนับ');
                
                $sheet->mergeCells("G{$row}:G".($row+1)); $sheet->setCellValue("G{$row}", 'งบประมาณ (บาท)');
                $sheet->mergeCells("H{$row}:H".($row+1)); $sheet->setCellValue("H{$row}", 'แหล่งงบประมาณ');
                $sheet->mergeCells("I{$row}:I".($row+1)); $sheet->setCellValue("I{$row}", 'ส่วนงาน');
                $sheet->mergeCells("J{$row}:U{$row}"); $sheet->setCellValue("J{$row}", 'ระยะเวลาดำเนินโครงการ');

                $col = 'J';
                foreach ($months as $month) {
                    $sheet->setCellValue($col.($row+1), $month);
                    $col++;
                }
                $row += 2;

                $projectStartRow = $row;
                $activities_string = $project['activity'] ?? ''; 
                $departments = $project['depart'] ?? [];
                $totalProjectRows = 0; 

                foreach ($departments as $depart) {
                    $records = array_merge($depart['output_indicators'] ?? [], $depart['outcome_indicators'] ?? []);
                    $records = !empty($records) ? $records : [[]];

                    foreach ($records as $record) {
                        $sheet->setCellValue("A{$row}", $project['project_code']);
                        $sheet->setCellValue("B{$row}", $project['project_name']);
                        $sheet->setCellValue("C{$row}", $activities_string);

                        if (isset($record['output_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['output_indicators']);
                            $sheet->setCellValue("E{$row}", $record['output_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['output_counting'] ?? '');
                        } elseif (isset($record['outcome_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['outcome_indicators']);
                            $sheet->setCellValue("E{$row}", $record['outcome_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['outcome_counting'] ?? '');
                        } else {
                            $sheet->setCellValue("D{$row}", '');
                            $sheet->setCellValue("E{$row}", '');
                            $sheet->setCellValue("F{$row}", '');
                        }

                        $sheet->setCellValue("G{$row}", $depart['total_amount'] ?? 0);
                        $sheet->setCellValue("H{$row}", $project['budget_source_code']);
                        $sheet->setCellValue("I{$row}", $depart['depart_code']);

                        $col = 'J';
                        foreach ($monthKeys as $mk) {
                            $sheet->setCellValue("{$col}{$row}", $depart[$mk] ?? 0);
                            $col++;
                        }

                        $row++;
                        $totalProjectRows++;
                    }
                }

                if ($totalProjectRows > 1) {
                    foreach (['A','B','C','H','I'] as $col) { 
                        $sheet->mergeCells("{$col}{$projectStartRow}:{$col}".($row-1));
                        $sheet->getStyle("{$col}{$projectStartRow}:{$col}".($row-1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP); 
                    }
                }
                
                $sheet->getStyle("C{$projectStartRow}:C".($row-1))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle("A{$projectStartRow}:U".($row-1))
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB(Color::COLOR_BLACK);
            }
        }

        foreach (range('A','U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        if (ob_get_length()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="business_report.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }*/

    public function exportType1($params)
    {
        $res = $this->model->exportList1($params);
        $data = [];

        foreach($res as $rs) {
            $st_code = !empty($rs['st_code']) ? $rs['st_code'] : uniqid();

            if (!isset($data[$st_code])) {

                $data[$st_code] = [
                    'st_code' => $rs['st_code'],
                    'st_name' => $rs['st_name'],
                    'project_code' => $rs['project_code'],
                    'full_name' => $rs['full_name'],
                    'project_name' => $rs['project_name'],
                    'budget_source_code' => $rs['budget_source_code'],
                    'activity' => array(),
                    'activity_code' => array(),
                    'project_name_list' => array(),
                    'depart' => []
                ];
            }

            $data[$st_code]['project_name_list'][] = $rs['project_name'];

            foreach($rs['activity'] as $ar) {
                /*if(!isset($data[$st_code]['activity_code'][$ar['id']])) {
                    $data[$st_code]['activity'][$ar['id']] = $ar['activity_name'];
                    $data[$st_code]['activity_code'][$ar['id']]  = $ar['activity_code'];
                }*/
                $data[$st_code]['activity'][] = $ar['activity_name'];
                $data[$st_code]['activity_code'][]  = $ar['activity_code'];
            }

            if (!isset($data[$st_code]['depart'][$rs['depart_code']])) {
                $data[$st_code]['depart'][$rs['depart_code']] = [
                    'depart_code' => $rs['depart_code'],
                    'total_amount' => $rs['total_amount'] ?? 0,
                    'output_indicators' => [],
                    'outcome_indicators' => [],
                ];
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$st_code]['depart'][$rs['depart_code']]["total_$m"] = $rs[$m] ?? 0;
                }
            } else {
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$st_code]['depart'][$rs['depart_code']]["total_$m"] += $rs[$m] ?? 0;
                }
                $data[$st_code]['depart'][$rs['depart_code']]['total_amount'] += $rs['total_amount'] ?? 0;
            }

            if (!empty($rs['output_indicators']) && is_array($rs['output_indicators'])) {
                foreach ($rs['output_indicators'] as $oi) {
                    if (is_array($oi) && !empty($oi['id'])) {
                        $data[$st_code]['depart'][$rs['depart_code']]['output_indicators'][$oi['id']] = $oi;
                    }
                }
            }

            if (!empty($rs['outcome_indicators']) && is_array($rs['outcome_indicators'])) {
                foreach ($rs['outcome_indicators'] as $oo) {
                    if (is_array($oo) && !empty($oo['id'])) {
                        $data[$st_code]['depart'][$rs['depart_code']]['outcome_indicators'][$oo['id']] = $oo;
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $months = ['ต.ค.','พ.ย.','ธ.ค.','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.'];

        $monthKeys = ['total_oct','total_nov','total_dec','total_jan','total_feb','total_mar','total_apr','total_may','total_jun','total_jul','total_aug','total_sep'];
        $row = 1;

        $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์ ระหว่าง วันที่ '.setDateTimeFormat($params['start_date'], 'd/m/Y').' ถึง '.setDateTimeFormat($params['end_date'], 'd/m/Y');
        $sheet->mergeCells("A{$row}:U{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        if ($data) {
            foreach ($data as $project) {
                $sheet->mergeCells("A{$row}:U{$row}");
                $sheet->setCellValue("A{$row}", 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์ ('.$project['st_name'].')');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                $headerStyle = $sheet->getStyle("A{$row}:U".($row+1));
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $headerStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);

                $sheet->mergeCells("A{$row}:A".($row+1)); $sheet->setCellValue("A{$row}", 'รหัสโครงการ');
                $sheet->mergeCells("B{$row}:B".($row+1)); $sheet->setCellValue("B{$row}", 'โครงการ');
                $sheet->mergeCells("C{$row}:C".($row+1)); $sheet->setCellValue("C{$row}", 'กิจกรรม'); 
                
                $sheet->mergeCells("D{$row}:F{$row}"); $sheet->setCellValue("D{$row}", 'ตัวชี้วัดความสำเร็จโครงการ');
                $sheet->setCellValue("D".($row+1), 'ตัวชี้วัด');
                $sheet->setCellValue("E".($row+1), 'ค่าเป้าหมาย');
                $sheet->setCellValue("F".($row+1), 'หน่วยนับ');
                
                $sheet->mergeCells("G{$row}:G".($row+1)); $sheet->setCellValue("G{$row}", 'งบประมาณ (บาท)');
                $sheet->mergeCells("H{$row}:H".($row+1)); $sheet->setCellValue("H{$row}", 'แหล่งงบประมาณ');
                $sheet->mergeCells("I{$row}:I".($row+1)); $sheet->setCellValue("I{$row}", 'ส่วนงาน');
                $sheet->mergeCells("J{$row}:U{$row}"); $sheet->setCellValue("J{$row}", 'ระยะเวลาดำเนินโครงการ');

                $col = 'J';
                foreach ($months as $month) {
                    $sheet->setCellValue($col.($row+1), $month);
                    $col++;
                }
                $row += 2;

                $projectStartRow = $row;

                $activities_string = implode("\n", $project['activity']);
                $activity_id_string = implode("\n", $project['activity_code']);
                $project_name_string = implode("\n", $project['project_name_list']);

                $departments = $project['depart'] ?? [];
                $totalProjectRows = 0; 

                foreach ($departments as $depart) {
                    $records = array_merge($depart['output_indicators'] ?? [], $depart['outcome_indicators'] ?? []);
                    $records = !empty($records) ? $records : [[]];

                    foreach ($records as $record) {
                        $sheet->setCellValue("A{$row}", $activity_id_string);
                        $sheet->setCellValue("B{$row}", $project_name_string);
                        $sheet->setCellValue("C{$row}", $activities_string);

                        if (isset($record['output_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['output_indicators']);
                            $sheet->setCellValue("E{$row}", $record['output_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['output_counting'] ?? '');
                        } elseif (isset($record['outcome_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['outcome_indicators']);
                            $sheet->setCellValue("E{$row}", $record['outcome_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['outcome_counting'] ?? '');
                        } else {
                            $sheet->setCellValue("D{$row}", '');
                            $sheet->setCellValue("E{$row}", '');
                            $sheet->setCellValue("F{$row}", '');
                        }

                        $sheet->setCellValue("G{$row}", $depart['total_amount'] ?? 0);
                        $sheet->setCellValue("H{$row}", $project['budget_source_code']);
                        $sheet->setCellValue("I{$row}", $depart['depart_code']);

                        $col = 'J';
                        foreach ($monthKeys as $mk) {
                            $sheet->setCellValue("{$col}{$row}", $depart[$mk] ?? 0);
                            $col++;
                        }

                        $row++;
                        $totalProjectRows++;
                    }
                }

                if ($totalProjectRows > 1) {
                    foreach (['A','B','C','H','I'] as $col) { 
                        $sheet->mergeCells("{$col}{$projectStartRow}:{$col}".($row-1));
                        $sheet->getStyle("{$col}{$projectStartRow}:{$col}".($row-1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP); 
                    }
                }
                
                $sheet->getStyle("C{$projectStartRow}:C".($row-1))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle("A{$projectStartRow}:U".($row-1))
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB(Color::COLOR_BLACK);
            }
        }

        foreach (range('A','U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        if (ob_get_length()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="business_report.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /*public function exportType2($params)
    {
        $res = $this->model->exportList2($params);

        $data = [];

        foreach ($res as $rs) {
            $project_code = !empty($rs['project_code']) ? $rs['project_code'] : uniqid();

            if (!isset($data[$project_code])) {
                $data[$project_code] = [
                    'project_code' => $rs['project_code'],
                    'full_name' => $rs['full_name'],
                    'project_name' => $rs['project_name'],
                    'budget_source_code' => $rs['budget_source_code'],
                    'depart' => []
                ];
            }

            if (!isset($data[$project_code]['depart'][$rs['depart_code']])) {
                $data[$project_code]['depart'][$rs['depart_code']] = [
                    'depart_code' => $rs['depart_code'],
                    'total_amount' => $rs['total_amount'] ?? 0,
                    'output_indicators' => [],
                    'outcome_indicators' => [],
                ];
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] = $rs[$m] ?? 0;
                }
            } else {
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] += $rs[$m] ?? 0;
                }
                $data[$project_code]['depart'][$rs['depart_code']]['total_amount'] += $rs['total_amount'] ?? 0;
            }

            if (!empty($rs['output_indicators']) && is_array($rs['output_indicators'])) {
                foreach ($rs['output_indicators'] as $oi) {
                    if (is_array($oi)) {
                        $data[$project_code]['depart'][$rs['depart_code']]['output_indicators'][$oi['id']] = $oi;
                    }
                }
            }

            if (!empty($rs['outcome_indicators']) && is_array($rs['outcome_indicators'])) {
                foreach ($rs['outcome_indicators'] as $oo) {
                    if (is_array($oo)) {
                        $data[$project_code]['depart'][$rs['depart_code']]['outcome_indicators'][$oo['id']] = $oo;
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $months = ['ต.ค.','พ.ย.','ธ.ค.','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.'];
        $monthKeys = ['total_oct','total_nov','total_dec','total_jan','total_feb','total_mar','total_apr','total_may','total_jun','total_jul','total_aug','total_sep'];
        $row = 1;

        $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามวงเว็บ ระหว่าง วันที่ '.setDateTimeFormat($params['start_date'], 'd/m/Y').' ถึง '.setDateTimeFormat($params['end_date'], 'd/m/Y').'';
        $sheet->mergeCells("A{$row}:T{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:T{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
        $row++;
        $row++;

        if ($data) {
            foreach ($data as $project) {
                $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามวงเว็บ ('.$project['full_name'].')';
                $sheet->mergeCells("A{$row}:T{$row}");
                $sheet->setCellValue("A{$row}", $title);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:T{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
                $row++;

                $sheet->mergeCells("A{$row}:A" . ($row+1)); $sheet->setCellValue("A{$row}", 'รหัสโครงการ');
                $sheet->mergeCells("B{$row}:B" . ($row+1)); $sheet->setCellValue("B{$row}", 'โครงการ');
                $sheet->mergeCells("C{$row}:E{$row}"); $sheet->setCellValue("C{$row}", 'ตัวชี้วัดความสำเร็จโครงการ');
                $sheet->setCellValue("C" . ($row+1), 'ตัวชี้วัด');
                $sheet->setCellValue("D" . ($row+1), 'ค่าเป้าหมาย');
                $sheet->setCellValue("E" . ($row+1), 'หน่วยนับ');
                $sheet->mergeCells("F{$row}:F" . ($row+1)); $sheet->setCellValue("F{$row}", 'งบประมาณ (บาท)');
                $sheet->mergeCells("G{$row}:G" . ($row+1)); $sheet->setCellValue("G{$row}", 'แหล่งงบประมาณ');
                $sheet->mergeCells("H{$row}:H" . ($row+1)); $sheet->setCellValue("H{$row}", 'ส่วนงาน');
                $sheet->mergeCells("I{$row}:T{$row}"); $sheet->setCellValue("I{$row}", 'ระยะเวลาดำเนินโครงการ');

                $col = 'I';
                foreach ($months as $month) {
                    $sheet->setCellValue($col.($row+1), $month);
                    $col++;
                }

                $sheet->getStyle("A{$row}:T" . ($row+1))->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:T" . ($row+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:T" . ($row+1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$row}:H" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9B382');
                $sheet->getStyle("C" . ($row+1) . ":E" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9CB9C');
                $sheet->getStyle("I{$row}:T" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFA07A');
                $sheet->getStyle("A{$row}:T" . ($row+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
                $row += 2;

                $projectStartRow = $row;
                foreach($project['depart'] as $depart) {
                    $records = array_merge($depart['output_indicators'] ?? [], $depart['outcome_indicators'] ?? []);
                    $rowCount = max(1, count($records));
                    $departStartRow = $row;

                    foreach ($records as $i => $record) {
                        $sheet->setCellValue("A{$row}", $project['project_code']);
                        $sheet->setCellValue("B{$row}", $project['project_name']);

                        if (isset($record['output_indicators'])) {
                            $sheet->setCellValue("C{$row}", $record['output_indicators']);
                            $sheet->setCellValue("D{$row}", $record['output_target'] ?? '');
                            $sheet->setCellValue("E{$row}", $record['output_counting'] ?? '');
                        } elseif (isset($record['outcome_indicators'])) {
                            $sheet->setCellValue("C{$row}", $record['outcome_indicators']);
                            $sheet->setCellValue("D{$row}", $record['outcome_target'] ?? '');
                            $sheet->setCellValue("E{$row}", $record['outcome_counting'] ?? '');
                        }

                        $sheet->setCellValue("G{$row}", $project['budget_source_code']);
                        $sheet->setCellValue("H{$row}", $depart['depart_code']);

                        $row++;
                    }

                    if ($rowCount > 1) {
                        $sheet->mergeCells("A{$departStartRow}:A" . ($departStartRow + $rowCount - 1));
                        $sheet->mergeCells("B{$departStartRow}:B" . ($departStartRow + $rowCount - 1));
                        $sheet->mergeCells("G{$departStartRow}:G" . ($departStartRow + $rowCount - 1));
                        $sheet->mergeCells("H{$departStartRow}:H" . ($departStartRow + $rowCount - 1));
                    }

                    $sheet->mergeCells("F{$departStartRow}:F" . ($departStartRow + $rowCount - 1));
                    $sheet->setCellValue("F{$departStartRow}", $depart['total_amount']);
                    $sheet->getStyle("F{$departStartRow}:F" . ($departStartRow + $rowCount - 1))
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $sheet->getStyle("G{$departStartRow}:G" . ($departStartRow + $rowCount - 1))
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $sheet->getStyle("H{$departStartRow}:H" . ($departStartRow + $rowCount - 1))
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    for ($r=$departStartRow; $r<=$departStartRow+$rowCount-1; $r++) {
                        $sheet->setCellValue("G{$r}", $project['budget_source_code']);
                        $sheet->setCellValue("H{$r}", $depart['depart_code']);
                    }

                    $col = 'I';
                    foreach ($monthKeys as $mk) {
                        $sheet->mergeCells("{$col}{$departStartRow}:{$col}".($departStartRow + $rowCount - 1));
                        $sheet->setCellValue("{$col}{$departStartRow}", $depart[$mk] ?? 0);
                        $sheet->getStyle("{$col}{$departStartRow}:{$col}".($departStartRow + $rowCount - 1))
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $col++;
                    }

                    $sheet->getStyle("A{$departStartRow}:T" . ($departStartRow + $rowCount - 1))->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setARGB(Color::COLOR_BLACK);
                }

                $row++;
            }
        }

        foreach (range('A','T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="business_report.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }*/

    public function exportType2($params)
    {
        $res = $this->model->exportList2($params);

        $data = [];

        foreach ($res as $rs) {
            $project_code = !empty($rs['project_code']) ? $rs['project_code'] : uniqid();

            if (!isset($data[$project_code])) {
                $data[$project_code] = [
                    'project_code' => $rs['project_code'],
                    'full_name' => $rs['full_name'],
                    'project_name' => $rs['project_name'],
                    'budget_source_code' => $rs['budget_source_code'],
                    'activity' => array(),
                    'activity_code' => array(),
                    'project_name_list' => array(),
                    'depart' => []
                ];
            }

            $data[$project_code]['project_name_list'][] = $rs['project_name'];

            foreach($rs['activity'] as $ar) {
                $data[$project_code]['activity'][] = $ar['activity_name'];
                $data[$project_code]['activity_code'][]  = $ar['activity_code'];
            }

            if (!isset($data[$project_code]['depart'][$rs['depart_code']])) {
                $data[$project_code]['depart'][$rs['depart_code']] = [
                    'depart_code' => $rs['depart_code'],
                    'total_amount' => $rs['total_amount'] ?? 0,
                    'output_indicators' => [],
                    'outcome_indicators' => [],
                ];
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] = $rs[$m] ?? 0;
                }
            } else {
                foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                    $data[$project_code]['depart'][$rs['depart_code']]["total_$m"] += $rs[$m] ?? 0;
                }
                $data[$project_code]['depart'][$rs['depart_code']]['total_amount'] += $rs['total_amount'] ?? 0;
            }

            if (!empty($rs['output_indicators']) && is_array($rs['output_indicators'])) {
                foreach ($rs['output_indicators'] as $oi) {
                    if (is_array($oi)) {
                        $data[$project_code]['depart'][$rs['depart_code']]['output_indicators'][$oi['id']] = $oi;
                    }
                }
            }

            if (!empty($rs['outcome_indicators']) && is_array($rs['outcome_indicators'])) {
                foreach ($rs['outcome_indicators'] as $oo) {
                    if (is_array($oo)) {
                        $data[$project_code]['depart'][$rs['depart_code']]['outcome_indicators'][$oo['id']] = $oo;
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $months = ['ต.ค.','พ.ย.','ธ.ค.','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.'];
        $monthKeys = ['total_oct','total_nov','total_dec','total_jan','total_feb','total_mar','total_apr','total_may','total_jun','total_jul','total_aug','total_sep'];
        $row = 1;

        $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามวงเว็บ ระหว่าง วันที่ '.setDateTimeFormat($params['start_date'], 'd/m/Y').' ถึง '.setDateTimeFormat($params['end_date'], 'd/m/Y').'';
        $sheet->mergeCells("A{$row}:U{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:U{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
        $row += 2;

        if ($data) {
            foreach ($data as $project) {
                $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามวงเว็บ ('.$project['full_name'].')';
                $sheet->mergeCells("A{$row}:U{$row}");
                $sheet->setCellValue("A{$row}", $title);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:U{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
                $row++;

                // Header row
                $sheet->mergeCells("A{$row}:A" . ($row+1)); $sheet->setCellValue("A{$row}", 'รหัสโครงการ');
                $sheet->mergeCells("B{$row}:B" . ($row+1)); $sheet->setCellValue("B{$row}", 'โครงการ');
                $sheet->mergeCells("C{$row}:C" . ($row+1)); $sheet->setCellValue("C{$row}", 'กิจกรรม');
                $sheet->mergeCells("D{$row}:F{$row}"); $sheet->setCellValue("D{$row}", 'ตัวชี้วัดความสำเร็จโครงการ');
                $sheet->setCellValue("D" . ($row+1), 'ตัวชี้วัด');
                $sheet->setCellValue("E" . ($row+1), 'ค่าเป้าหมาย');
                $sheet->setCellValue("F" . ($row+1), 'หน่วยนับ');
                $sheet->mergeCells("G{$row}:G" . ($row+1)); $sheet->setCellValue("G{$row}", 'งบประมาณ (บาท)');
                $sheet->mergeCells("H{$row}:H" . ($row+1)); $sheet->setCellValue("H{$row}", 'แหล่งงบประมาณ');
                $sheet->mergeCells("I{$row}:I" . ($row+1)); $sheet->setCellValue("I{$row}", 'ส่วนงาน');
                $sheet->mergeCells("J{$row}:U{$row}"); $sheet->setCellValue("J{$row}", 'ระยะเวลาดำเนินโครงการ');

                $col = 'J';
                foreach ($months as $month) {
                    $sheet->setCellValue($col.($row+1), $month);
                    $col++;
                }

                $sheet->getStyle("A{$row}:U" . ($row+1))->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:U" . ($row+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:U" . ($row+1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$row}:I" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9B382');
                $sheet->getStyle("D" . ($row+1) . ":F" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9CB9C');
                $sheet->getStyle("J{$row}:U" . ($row+1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFA07A');
                $sheet->getStyle("A{$row}:U" . ($row+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
                $row += 2;

                // เริ่มบันทึกข้อมูลโครงการ
                $activities_string = implode("\n", $project['activity']);
                $activity_id_string = implode("\n", $project['activity_code']);
                $project_name_string = implode("\n", $project['project_name_list']);

                $sheet->setCellValue("A{$row}", $activity_id_string);
                $sheet->setCellValue("B{$row}", $project_name_string);
                $sheet->setCellValue("C{$row}", $activities_string);

                $projectStartRow = $row;

                foreach($project['depart'] as $key => $depart) {
                    $records = array_merge($depart['output_indicators'] ?? [], $depart['outcome_indicators'] ?? []);
                    $rowCount = max(1, count($records));
                    $departStartRow = $row;

                    foreach ($records as $record) {
                        if (isset($record['output_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['output_indicators']);
                            $sheet->setCellValue("E{$row}", $record['output_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['output_counting'] ?? '');
                        } elseif (isset($record['outcome_indicators'])) {
                            $sheet->setCellValue("D{$row}", $record['outcome_indicators']);
                            $sheet->setCellValue("E{$row}", $record['outcome_target'] ?? '');
                            $sheet->setCellValue("F{$row}", $record['outcome_counting'] ?? '');
                        }

                        $sheet->setCellValue("H{$row}", $project['budget_source_code']);
                        $sheet->setCellValue("I{$row}", $depart['depart_code']);
                        $row++;
                    }

                    $sheet->mergeCells("G{$departStartRow}:G" . ($departStartRow + $rowCount - 1));
                    $sheet->setCellValue("G{$departStartRow}", $depart['total_amount']);

                    $col = 'J';
                    foreach ($monthKeys as $mk) {
                        $sheet->mergeCells("{$col}{$departStartRow}:{$col}".($departStartRow + $rowCount - 1));
                        $sheet->setCellValue("{$col}{$departStartRow}", $depart[$mk] ?? 0);
                        $col++;
                    }

                    $sheet->getStyle("A{$departStartRow}:U" . ($departStartRow + $rowCount - 1))
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setARGB(Color::COLOR_BLACK);
                }

                $projectEndRow = $row - 1;
                $sheet->mergeCells("A{$projectStartRow}:A{$projectEndRow}");
                $sheet->mergeCells("B{$projectStartRow}:B{$projectEndRow}");
                $sheet->mergeCells("C{$projectStartRow}:C{$projectEndRow}");

                $sheet->getStyle("A{$projectStartRow}:C{$projectEndRow}")
                    ->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $row++;
            }
        }

        foreach (range('A','U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="business_report.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }


    public function exportSap($params)
    {
        $res = $this->model->exportSap($params);
        $data = $res;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        $sheet->mergeCells("A{$row}:A" . ($row + 1)); $sheet->setCellValue("A{$row}", 'รหัสบริษัท');
        $sheet->mergeCells("B{$row}:B" . ($row + 1)); $sheet->setCellValue("B{$row}", 'ปี');
        $sheet->mergeCells("C{$row}:C" . ($row + 1)); $sheet->setCellValue("C{$row}", 'หมายเลข');
        $sheet->mergeCells("D{$row}:D" . ($row + 1)); $sheet->setCellValue("D{$row}", 'รหัสกิจกรรม');
        $sheet->mergeCells("E{$row}:E" . ($row + 1)); $sheet->setCellValue("E{$row}", 'ชื่อกิจกรรม');
        $sheet->mergeCells("F{$row}:F" . ($row + 1)); $sheet->setCellValue("F{$row}", 'กรอกงบประมาณ');
        $sheet->mergeCells("G{$row}:G" . ($row + 1)); $sheet->setCellValue("G{$row}", 'เงินทุน');
        $sheet->mergeCells("H{$row}:H" . ($row + 1)); $sheet->setCellValue("H{$row}", 'รหัสงบประมาณ');
        $sheet->mergeCells("I{$row}:I" . ($row + 1)); $sheet->setCellValue("I{$row}", 'ชื่องบประมาณ');
        $sheet->mergeCells("J{$row}:J" . ($row + 1)); $sheet->setCellValue("J{$row}", 'หมายเหตุ');

        $sheet->getStyle("A{$row}:J" . ($row + 1))->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:J" . ($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:J" . ($row + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $row += 2;

        if (!empty($data)) {
            foreach ($data as $project) {
                $sheet->setCellValue("A{$row}", '1000');
                $sheet->setCellValue("B{$row}", $project['year_name'] ?? '');
                $sheet->setCellValue("C{$row}", 'N/A');
                $sheet->setCellValue("D{$row}", $project['activity_code'] ?? '');
                $sheet->setCellValue("E{$row}", $project['activity_name'] ?? '');
                $sheet->setCellValue("F{$row}", number_format(($project['total_activity_amount'] + $project['total_expenses_amount']) ?? 0, 2, '.', ','));
                $sheet->setCellValue("G{$row}", $project['budget_source_code'] ?? '');
                $sheet->setCellValue("H{$row}", $project['activity_code']);
                $sheet->setCellValue("I{$row}", $project['full_name'] ?? '');
                $sheet->setCellValue("J{$row}", '');

                $row++;
            }
        } else {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->setCellValue("A{$row}", 'ไม่พบข้อมูล');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'SAP_Interface_' . date('Y-m-d\TH-i-s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportType4($params)
    {
        date_default_timezone_set('Asia/Bangkok');

        $params['sap_status'] = 'not_synced';

        $res = $this->model->exportSap($params);
        $data = $res;

        $ids = array();

        if (empty($data)) {
            return;
        }

        $filename = 'SAP_Interface_' . date('Y-m-d\TH-i-s') . '.txt';

        $storageDir = ROOT_PATH. '/storage/budgets';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }

        $filePath = $storageDir . DIRECTORY_SEPARATOR . $filename;

        $fp = fopen($filePath, 'w');
        foreach ($data as $project) {
            $ids[] = $project['id'];
            $fields = [
                '10000',
                $project['budget_source_code'] ?? '',
                $project['activity_code'] ?? '',
                '',
                isset($project['year_name']) ? $project['year_name'] - 543 : '',
                '8',
                number_format(($project['total_activity_amount'] + $project['total_expenses_amount']) ?? 0, 2, '.', ','),
                $project['activity_name'] ?? ''
            ];

            $line = implode('|', $fields);
            fwrite($fp, $line . PHP_EOL);
        }
        fclose($fp);

        if($ids) {
            $this->model->syncToSap($ids);
        }

        header('Content-Type: text/plain; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        readfile($filePath);

        exit;
    }

    public function exportType4Excel($params)
    {
        date_default_timezone_set('Asia/Bangkok');

        $params['sap_status'] = 'not_synced';
        $res = $this->model->exportSap($params);
        $data = $res;

        $ids = array();

        if (empty($data)) {
            return;
        }

        // สร้าง Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        $sheet->mergeCells("A{$row}:A" . ($row + 1)); $sheet->setCellValue("A{$row}", 'ศูนย์เงินทุน');
        $sheet->mergeCells("B{$row}:B" . ($row + 1)); $sheet->setCellValue("B{$row}", 'เงินทุน');
        $sheet->mergeCells("C{$row}:C" . ($row + 1)); $sheet->setCellValue("C{$row}", 'ขอบเขตหน้าที่');
        $sheet->mergeCells("D{$row}:D" . ($row + 1)); $sheet->setCellValue("D{$row}", 'รายการภาระผูกพัน');
        $sheet->mergeCells("E{$row}:E" . ($row + 1)); $sheet->setCellValue("E{$row}", 'ปี');
        $sheet->mergeCells("F{$row}:F" . ($row + 1)); $sheet->setCellValue("F{$row}", 'ประเภทงบ');
        $sheet->mergeCells("G{$row}:G" . ($row + 1)); $sheet->setCellValue("G{$row}", 'จำนวนเงิน');
        $sheet->mergeCells("H{$row}:H" . ($row + 1)); $sheet->setCellValue("H{$row}", 'รายการ');

        $sheet->getStyle("A{$row}:H" . ($row + 1))->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H" . ($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:H" . ($row + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $row += 2;

        if (!empty($data)) {
            foreach ($data as $project) {
                $sheet->setCellValue("A{$row}", '10000');
                $sheet->setCellValue("B{$row}", $project['budget_source_code'] ?? '');
                $sheet->setCellValue("C{$row}", $project['activity_code'] ?? '');
                $sheet->setCellValue("D{$row}", '');
                $sheet->setCellValue("E{$row}", ($project['year_name'] - 543) ?? '');
                $sheet->setCellValue("F{$row}", '8');
                $sheet->setCellValue("G{$row}", number_format(($project['total_activity_amount'] + $project['total_expenses_amount']) ?? 0, 2, '.', ','));
                $sheet->setCellValue("H{$row}", $project['activity_name']);
                $row++;
            }
        } else {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", 'ไม่พบข้อมูล');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($ids) {
            //$this->model->syncToSap($ids);
        }

        // ส่ง Excel ออก
        if (ob_get_length()) ob_end_clean();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'SAP_Interface_' . date('Y-m-d\TH-i-s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    public function exportSap2($params)
    {
        $res = $this->model->exportSap($params);
        $data = $res;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        $sheet->mergeCells("A{$row}:A" . ($row + 1)); $sheet->setCellValue("A{$row}", 'รหัสบริษัท');
        $sheet->mergeCells("B{$row}:B" . ($row + 1)); $sheet->setCellValue("B{$row}", 'ปี');
        $sheet->mergeCells("C{$row}:C" . ($row + 1)); $sheet->setCellValue("C{$row}", 'หมายเลข');
        $sheet->mergeCells("D{$row}:D" . ($row + 1)); $sheet->setCellValue("D{$row}", 'รหัสกิจกรรม');
        $sheet->mergeCells("E{$row}:E" . ($row + 1)); $sheet->setCellValue("E{$row}", 'ชื่อกิจกรรม');
        $sheet->mergeCells("F{$row}:F" . ($row + 1)); $sheet->setCellValue("F{$row}", 'กรอกงบประมาณ');
        $sheet->mergeCells("G{$row}:G" . ($row + 1)); $sheet->setCellValue("G{$row}", 'เงินทุน');
        $sheet->mergeCells("H{$row}:H" . ($row + 1)); $sheet->setCellValue("H{$row}", 'รหัสงบประมาณ');
        $sheet->mergeCells("I{$row}:I" . ($row + 1)); $sheet->setCellValue("I{$row}", 'ชื่องบประมาณ');
        $sheet->mergeCells("J{$row}:J" . ($row + 1)); $sheet->setCellValue("J{$row}", 'หมายเหตุ');

        $sheet->getStyle("A{$row}:J" . ($row + 1))->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:J" . ($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:J" . ($row + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $row += 2;

        if (!empty($data)) {
            foreach ($data as $project) {
                $sheet->setCellValue("A{$row}", '1000');
                $sheet->setCellValue("B{$row}", $project['year_name'] ?? '');
                $sheet->setCellValue("C{$row}", 'N/A');
                $sheet->setCellValue("D{$row}", $project['activity_code'] ?? '');
                $sheet->setCellValue("E{$row}", $project['activity_name'] ?? '');
                $sheet->setCellValue("F{$row}", number_format(($project['total_activity_amount'] + $project['total_expenses_amount']) ?? 0, 2, '.', ','));
                $sheet->setCellValue("G{$row}", $project['budget_source_code'] ?? '');
                $sheet->setCellValue("H{$row}", $project['activity_code']);
                $sheet->setCellValue("I{$row}", $project['full_name'] ?? '');
                $sheet->setCellValue("J{$row}", '');

                $row++;
            }
        } else {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->setCellValue("A{$row}", 'ไม่พบข้อมูล');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="business_report_sap.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }


}
$controller = new BusinessBudgetReportController();

if (isset($_GET['action']) && $_GET['action'] === 'exportExcel') {
    $params = $_GET;    

    if($params['export_type'] == 1) {
        return $controller->exportType1($params);
    } else if($params['export_type'] == 2) {
        return $controller->exportType2($params);
    } else if($params['export_type'] == 3) {
        return $controller->exportSap($params);
    } else if($params['export_type'] == 4) {
        return $controller->exportType4Excel($params);
    } else if($params['export_type'] == 5) {
        return $controller->exportSap2($params);
    }
}

