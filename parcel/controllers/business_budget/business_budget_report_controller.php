<?php
    include_once __DIR__ . '/../../configs/init.php';
    include_once ROOT_PATH . '/models/business_budget/business_budget_report_model.php';
    require ROOT_PATH. '/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Style\Color;
    use PhpOffice\PhpSpreadsheet\Style\Border;

    if (session_id() === '') {
        session_start();
    }

class BusinessBudgetReportController
{
    private $model;

    public function __construct()
    {
        $database = new Database(); 
        $db = $database->connect('raot_db_dev');
        $this->model = new BusinessBudgetReportModel($db);
    }

    public function getBusinessReportList($param = []) 
    {
        return $this->model->getBusinessReportList($param);
    }

    public function exportType1($params)
    {
        $res = $this->model->exportList1($params);

        $data = [];

        foreach ($res as $rs) {
            $project_code = !empty($rs['st_code']) ? $rs['st_code'] : uniqid();

            if (!isset($data[$project_code])) {
                $data[$project_code] = [
                    'st_code' => $rs['st_code'],
                    'st_name' => $rs['st_name'],
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

        $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์ ระหว่าง วันที่ '.setDateTimeFormat($params['start_date'], 'd/m/Y').' ถึง '.setDateTimeFormat($params['end_date'], 'd/m/Y').'';
        $sheet->mergeCells("A{$row}:T{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:T{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
        $row++;
        $row++;

        if ($data) {
            foreach ($data as $project) {
                $title = 'รายงานรายละเอียดโครงการ และกิจกรรม จำแนกตามยุทธศาสตร์  ('.$project['st_name'].')';
                $sheet->mergeCells("A{$row}:T{$row}");
                $sheet->setCellValue("A{$row}", $title);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:T{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(Color::COLOR_BLACK);
                $row++;

                // --- Header 2 แถว ---
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
    }

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

                // --- Header 2 แถว ---
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
    }
}

$controller = new BusinessBudgetReportController();

if (isset($_GET['action']) && $_GET['action'] === 'exportExcel') {
    $params = $_GET;

    if($params['export_type'] == 1) {
        return $controller->exportType1($params);
    } else {
        return $controller->exportType2($params);
    }
}

