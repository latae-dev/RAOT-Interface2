<?php

class BusinessBudgetModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function sanitizeForInsert($data) 
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeForInsert($value);
            }
            return $data;
        } else {
            return ($data === '') ? null : $data;
        }
    }

    private function generateRunningNumber($module) 
    {
        $query = "SELECT *
                FROM ".BUDGETS_TB_RUNNING_NUMBERS."
                WHERE module_name = $1";
        $result = pg_query_params($this->conn, $query, [$module]);

        if (pg_num_rows($result) === 0) {
            throw new Exception("No running number config found for prefix $prefix and language $lang");
        }

        $row = pg_fetch_assoc($result);

        $currentNumber = trim($row['current_number']) + 1;

        $updateQuery = "UPDATE ".BUDGETS_TB_RUNNING_NUMBERS."
                        SET current_number = $1, updated_at = NOW() 
                        WHERE id = $2";
        pg_query_params($this->conn, $updateQuery, [$currentNumber, $row['id']]);

        if (trim($row['year_lang']) === 'TH') {
            $yearNumber = date('Y') + 543;
        } else {
            $yearNumber = date('Y');
        }

        if (trim($row['year_format']) === '2') {
            $year = substr($yearNumber, 2, 2);
        } else {
            $year = $yearNumber;
        }

        $runningNumber = str_pad($currentNumber, $row['digit'], '0', STR_PAD_LEFT);

        return $row['prefix'] . $year . $runningNumber;
    }

    private function buildObject($data) 
    {
        $user = $_SESSION['user_data'];
        
        return array(
            'year_name' => isset($data['year_name']) ? (int)$data['year_name'] : null,
            'version_name' => isset($data['version_name']) ? (int)$data['version_name'] : null,
            'project_type_name' => isset($data['project_type_name']) ? (int)$data['project_type_name'] : null,
            'original_project' => isset($data['original_project']) ? (int)$data['original_project'] : null,
            'fiscal_year1' => isset($data['fiscal_year1']) ? $data['fiscal_year1'] : null,
            'fiscal_year2' => isset($data['fiscal_year2']) ? $data['fiscal_year2'] : null,
            'fiscal_year3' => isset($data['fiscal_year3']) ? $data['fiscal_year3'] : null,
            'processing_time' => isset($data['processing_time']) ? $data['processing_time'] : null,
            'plan_type' => isset($data['plan_type']) ? $data['plan_type'] : null,
            'project_name' => isset($data['project_name']) ? $data['project_name'] : null,
            'project_code' => isset($data['project_code']) ? $data['project_code'] : null,
            'activity_name' => isset($data['activity_name']) ? $data['activity_name'] : null,
            'activity_code' => isset($data['activity_code']) ? (int)$data['activity_code'] : null,
            'budget_source_code' => isset($data['budget_source_code']) ? $data['budget_source_code'] : null,
            'national_strategy_id' => isset($data['national_strategy_id']) ? $data['national_strategy_id'] : null,
            'target_strategy_id' => isset($data['target_strategy_id']) ? $data['target_strategy_id'] : null,
            'subject_strategy_id' => isset($data['subject_strategy_id']) ? $data['subject_strategy_id'] : null,
            'plan_under_strategy_id' => isset($data['plan_under_strategy_id']) ? $data['plan_under_strategy_id'] : null,
            'target_level_subject_id' => isset($data['target_level_subject_id']) ? $data['target_level_subject_id'] : null,
            'sub_plan_id' => isset($data['sub_plan_id']) ? $data['sub_plan_id'] : null,
            'target_sub_plan_id' => isset($data['target_sub_plan_id']) ? $data['target_sub_plan_id'] : null,
            'plan_strategy_id' => isset($data['plan_strategy_id']) ? $data['plan_strategy_id'] : null,
            'sub_indicator_id' => isset($data['sub_indicator_id']) ? $data['sub_indicator_id'] : null,
            'tactic_id' => isset($data['tactic_id']) ? $data['tactic_id'] : null,
            'existing_staff' => isset($data['existing_staff']) ? $data['existing_staff'] : null,
            'additional_staff' => isset($data['additional_staff']) ? $data['additional_staff'] : null,
            'reason_staff' => isset($data['reason_staff']) ? $data['reason_staff'] : null,
            'existing_qualification_staff' => isset($data['existing_qualification_staff']) ? $data['existing_qualification_staff'] : null,
            'additional_qualification_staff' => isset($data['additional_qualification_staff']) ? $data['additional_qualification_staff'] : null,
            'reason_qualification_staff' => isset($data['reason_qualification_staff']) ? $data['reason_qualification_staff'] : null,
            'existing_skill_staff' => isset($data['existing_skill_staff']) ? $data['existing_skill_staff'] : null,
            'additional_skill_staff' => isset($data['additional_skill_staff']) ? $data['additional_skill_staff'] : null,
            'reason_skill_staff' => isset($data['reason_skill_staff']) ? $data['reason_skill_staff'] : null,
            'year3' => isset($data['year3']) ? (int)$data['year3'] : null,
            'existing_software' => isset($data['existing_software']) ? $data['existing_software'] : null,
            'additional_software' => isset($data['additional_software']) ? $data['additional_software'] : null,
            'reason_software' => isset($data['reason_software']) ? $data['reason_software'] : null,
            'existing_hardware' => isset($data['existing_hardware']) ? $data['existing_hardware'] : null,
            'additional_hardware' => isset($data['additional_hardware']) ? $data['additional_hardware'] : null,
            'reason_hardware' => isset($data['reason_hardware']) ? $data['reason_hardware'] : null,
            'year1' => isset($data['year1']) ? (int)$data['year1'] : null,
            'year2' => isset($data['year2']) ? (int)$data['year2'] : null,
            'place' => isset($data['place']) ? $data['place'] : null,
            'period' => isset($data['period']) ? $data['period'] : null,
            'strategic_objective' => isset($data['strategic_objective']) ? $data['strategic_objective'] : null,
            'national_strategy' => isset($data['national_strategy']) ? $data['national_strategy'] : null,
            'indicator' => isset($data['indicator']) ? $data['indicator'] : null,
            'strategy' => isset($data['strategy']) ? $data['strategy'] : null,
            'project_indicator' => isset($data['project_indicator']) ? $data['project_indicator'] : null,
            'risk_project' => isset($data['risk_project']) ? $data['risk_project'] : null,
            'risk_owners' => isset($data['risk_owners']) ? $data['risk_owners'] : null,
            'risk_scennario' => isset($data['risk_scennario']) ? $data['risk_scennario'] : null,
            'risk_factor' => isset($data['risk_factor']) ? $data['risk_factor'] : null,
            'exising_control' => isset($data['exising_control']) ? $data['exising_control'] : null,
            'risk_assessment' => isset($data['risk_assessment']) ? $data['risk_assessment'] : null,
            'lh' => isset($data['lh']) ? $data['lh'] : null,
            'im' => isset($data['im']) ? $data['im'] : null,
            'level' => isset($data['level']) ? $data['level'] : null,
            'risk_response' => isset($data['risk_response']) ? $data['risk_response'] : null,
            'residual_lh' => isset($data['residual_lh']) ? $data['residual_lh'] : null,
            'residual_im' => isset($data['residual_im']) ? $data['residual_im'] : null,
            'residual_level' => isset($data['residual_level']) ? $data['residual_level'] : null,
            'name_1' => isset($data['name_1']) ? $data['name_1'] : null,
            'position_1' => isset($data['position_1']) ? $data['position_1'] : null,
            'tel_1' => isset($data['tel_1']) ? $data['tel_1'] : null,
            'email_1' => isset($data['email_1']) ? $data['email_1'] : null,
            'name_2' => isset($data['name_2']) ? $data['name_2'] : null,
            'position_2' => isset($data['position_2']) ? $data['position_2'] : null,
            'tel_2' => isset($data['tel_2']) ? $data['tel_2'] : null,
            'email_2' => isset($data['email_2']) ? $data['email_2'] : null,
            'status' => isset($data['status']) ? $data['status'] : null,
            'head_office_id' => isset($data['head_office_id']) ? (int)$data['head_office_id'] : null,
            'image_path' => isset($data['image_path']) ? $data['image_path'] : null,
            'approve_position_code' => isset($data['approve_position_code']) ? $data['approve_position_code'] : null,
            'responsibilities_approves' => isset($data['responsibilities_approves']) ? $data['responsibilities_approves'] : null,
            'position_id' => $user['position_id'],
        );
    }

    public function insertObjectives($budget_id, $objectives) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_OBJECTIVES." 
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seq = 1;

        foreach ($objectives as $objective) {
            if (empty($objective)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_OBJECTIVES." 
                (seq, business_budget_id, objective)
                VALUES ($1, $2, $3)
            ";

            pg_query_params($this->conn, $query, [
                $seq++,
                $budget_id,
                $objective
            ]);
        }
    }


    public function insertOutputIndicators($budget_id, $indicators, $targets, $countings) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_OUTPUT_INDICATORS." 
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seq = 1;

        $len = max(count($indicators), count($targets), count($countings));

        for ($i = 0; $i < $len; $i++) {
            $indicator = isset($indicators[$i]) ? $indicators[$i] : null;
            $target    = isset($targets[$i]) ? $targets[$i] : null;
            $counting  = isset($countings[$i]) ? $countings[$i] : null;

            if (empty($indicator) && empty($target) && empty($counting)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_OUTPUT_INDICATORS." 
                (seq, business_budget_id, output_indicators, output_target, output_counting)
                VALUES ($1, $2, $3, $4, $5)
            ";

            pg_query_params($this->conn, $query, [
                $seq++,
                $budget_id,
                $indicator !== '' ? $indicator : null,
                $target !== '' ? $target : null,
                $counting !== '' ? $counting : null
            ]);
        }
    }


    public function insertOutcomeIndicators($budget_id, $indicators, $targets, $countings) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_OUTCOME_INDICATORS." 
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $len = max(count($indicators), count($targets), count($countings));
        $seq = 1;

        for ($i = 0; $i < $len; $i++) {
            $indicator = isset($indicators[$i]) ? $indicators[$i] : null;
            $target    = isset($targets[$i]) ? $targets[$i] : null;
            $counting  = isset($countings[$i]) ? $countings[$i] : null;

            if (empty($indicator) && empty($target) && empty($counting)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_OUTCOME_INDICATORS."
                (seq, business_budget_id, outcome_indicators, outcome_target, outcome_counting)
                VALUES ($1, $2, $3, $4, $5)
            ";

            $object = [
                $seq++,
                $budget_id,
                $indicator !== '' ? $indicator : null,
                $target !== '' ? $target : null,
                $counting !== '' ? $counting : null
            ];

            pg_query_params($this->conn, $query, $object);
        }
    }


    public function insertTargetGroups($budget_id, $groups, $numbers, $areas) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_TARGET_GROUPS." 
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seq = 1;

        $len = max(count($groups), count($numbers), count($areas));

        for ($i = 0; $i < $len; $i++) {
            $group  = isset($groups[$i]) ? $groups[$i] : null;
            $number = isset($numbers[$i]) ? $numbers[$i] : null;
            $area   = isset($areas[$i]) ? $areas[$i] : null;

            if (empty($group) && empty($number) && empty($area)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_TARGET_GROUPS." 
                (seq, business_budget_id, target_group, target_number, target_area)
                VALUES ($1, $2, $3, $4, $5)
            ";

            pg_query_params($this->conn, $query, [
                $seq++,
                $budget_id,
                $group !== '' ? $group : null,
                $number !== '' ? $number : null,
                $area !== '' ? $area : null
            ]);
        }
    }


    public function insertExpectedBenefits($budget_id, $benefits) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_EXPECTED_BENEFITS." 
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seq = 1;
        $len = count($benefits);

        for ($i = 0; $i < $len; $i++) {
            $benefit = isset($benefits[$i]) ? $benefits[$i] : null;

            if (empty($benefit)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_EXPECTED_BENEFITS." 
                (seq, business_budget_id, expected_benefit)
                VALUES ($1, $2, $3)
            ";

            pg_query_params($this->conn, $query, [
                $seq++,
                $budget_id,
                $benefit !== '' ? $benefit : null
            ]);
        }
    }


    public function insertActivityBudgets($budget_id, $activities) 
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_ACTIVITY_BUDGETS."
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        foreach ($activities as $key => $activity) {
            $activityName = $activity['activity_name'][0];

            $len = max(
                isset($activity['activity_name']) ? count($activity['activity_name']) : 0,
                isset($activity['number_budget']) ? count($activity['number_budget']) : 0,
                isset($activity['list_budget']) ? count($activity['list_budget']) : 0,
                isset($activity['quantity']) ? count($activity['quantity']) : 0,
                isset($activity['price']) ? count($activity['price']) : 0,
                isset($activity['amount']) ? count($activity['amount']) : 0,
                isset($activity['total_amount']) ? count($activity['total_amount']) : 0
            );

            $hasInserted = false; 

            for ($i = 0; $i < $len; $i++) {
                $numberBudget = isset($activity['number_budget'][$i]) ? $activity['number_budget'][$i] : null;
                $listBudget   = isset($activity['list_budget'][$i])   ? $activity['list_budget'][$i]   : null;
                $quantity     = isset($activity['quantity'][$i])      ? $activity['quantity'][$i]      : null;
                $price        = isset($activity['price'][$i])         ? $activity['price'][$i]         : null;
                $amount       = isset($activity['amount'][$i])        ? $activity['amount'][$i]        : null;
                $totalAmount  = isset($activity['total_amount'][$i])  ? $activity['total_amount'][$i]  : null;

                if (
                    ($numberBudget === null || $numberBudget === '') &&
                    ($listBudget === null   || $listBudget === '') &&
                    ($quantity === null     || $quantity === '') &&
                    ($price === null        || $price === '') &&
                    ($amount === null       || $amount === '') &&
                    ($totalAmount === null  || $totalAmount === '')
                ) {
                    continue;
                }

                $query = "
                    INSERT INTO ".BUDGETS_TB_ACTIVITY_BUDGETS."
                    (business_budget_id, seq, activity_name, number_budget, list_budget, quantity, price, amount, total_amount)
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
                ";

                $obj = [
                    $budget_id,
                    $key +1,
                    (!$hasInserted) ? $activityName : null,
                    ($numberBudget !== null && $numberBudget !== '') ? $numberBudget : null,
                    $listBudget,
                    ($quantity     !== null && $quantity     !== '') ? $quantity     : null,
                    ($price        !== null && $price        !== '') ? $price        : null,
                    ($amount       !== null && $amount       !== '') ? $amount       : null,
                    ($totalAmount  !== null && $totalAmount  !== '') ? $totalAmount  : null,
                ];

                pg_query_params($this->conn, $query, $obj);

                $hasInserted = true;
            }

            
            if (!$hasInserted) {
                $activityName = isset($activity['activity_name'][0]) ? $activity['activity_name'][0] : null;

                $query = "
                    INSERT INTO ".BUDGETS_TB_ACTIVITY_BUDGETS."
                    (business_budget_id, seq, activity_name, number_budget, list_budget, quantity, price, amount, total_amount)
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
                ";
                pg_query_params($this->conn, $query, [
                    $budget_id,
                    $key + 1,
                    $activityName,
                    null, null, null, null, null, null
                ]);
            }
        }
    }

    public function insertExpensesBudgets($budget_id, $expenses)
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_EXPENSES_BUDGETS."
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        foreach ($expenses as $key => $expense) {
            $expenseName = isset($expense['expense_name'][0]) ? $expense['expense_name'][0] : null;
            $len = max(
                isset($expense['expense_name']) ? count($expense['expense_name']) : 0,
                isset($expense['plan_trip']) ? count($expense['plan_trip']) : 0,
                isset($expense['officer']) ? count($expense['officer']) : 0,
                isset($expense['people']) ? count($expense['people']) : 0,
                isset($expense['work_days']) ? count($expense['work_days']) : 0,
                isset($expense['allowance_rate']) ? count($expense['allowance_rate']) : 0,
                isset($expense['allowance_amount']) ? count($expense['allowance_amount']) : 0,
                isset($expense['transport_rate']) ? count($expense['transport_rate']) : 0,
                isset($expense['transport_amount']) ? count($expense['transport_amount']) : 0,
                isset($expense['stay_days']) ? count($expense['stay_days']) : 0,
                isset($expense['stay_rate']) ? count($expense['stay_rate']) : 0,
                isset($expense['stay_amount']) ? count($expense['stay_amount']) : 0,
                isset($expense['fuel_trips']) ? count($expense['fuel_trips']) : 0,
                isset($expense['fuel_rate']) ? count($expense['fuel_rate']) : 0,
                isset($expense['fuel_amount']) ? count($expense['fuel_amount']) : 0,
                isset($expense['total_activity']) ? count($expense['total_activity']) : 0
            );

            $hasInserted = false;

            for ($i = 0; $i < $len; $i++) {
                $planTrip        = isset($expense['plan_trip'][$i]) ? $expense['plan_trip'][$i] : null;
                $officer         = isset($expense['officer'][$i]) ? $expense['officer'][$i] : null;
                $people          = isset($expense['people'][$i]) ? $expense['people'][$i] : null;
                $workDays        = isset($expense['work_days'][$i]) ? $expense['work_days'][$i] : null;
                $allowanceRate   = isset($expense['allowance_rate'][$i]) ? $expense['allowance_rate'][$i] : null;
                $allowanceAmount = isset($expense['allowance_amount'][$i]) ? $expense['allowance_amount'][$i] : null;
                $transportRate   = isset($expense['transport_rate'][$i]) ? $expense['transport_rate'][$i] : null;
                $transportAmount = isset($expense['transport_amount'][$i]) ? $expense['transport_amount'][$i] : null;
                $stayDays        = isset($expense['stay_days'][$i]) ? $expense['stay_days'][$i] : null;
                $stayRate        = isset($expense['stay_rate'][$i]) ? $expense['stay_rate'][$i] : null;
                $stayAmount      = isset($expense['stay_amount'][$i]) ? $expense['stay_amount'][$i] : null;
                $fuelTrips       = isset($expense['fuel_trips'][$i]) ? $expense['fuel_trips'][$i] : null;
                $fuelRate        = isset($expense['fuel_rate'][$i]) ? $expense['fuel_rate'][$i] : null;
                $fuelAmount      = isset($expense['fuel_amount'][$i]) ? $expense['fuel_amount'][$i] : null;
                $totalActivity   = isset($expense['total_activity'][$i]) ? $expense['total_activity'][$i] : null;

                $numericFieldsEmpty = ($planTrip === null || $planTrip === '') &&
                                    ($officer === null || $officer === '') &&
                                    ($people === null || $people === '') &&
                                    ($workDays === null || $workDays === '') &&
                                    ($allowanceRate === null || $allowanceRate === '') &&
                                    ($transportRate === null || $transportRate === '') &&
                                    ($stayDays === null || $stayDays === '') &&
                                    ($stayRate === null || $stayRate === '') &&
                                    ($fuelTrips === null || $fuelTrips === '') &&
                                    ($fuelRate === null || $fuelRate === '');

                if ($numericFieldsEmpty) {
                    continue;
                }

                $query = "
                    INSERT INTO ".BUDGETS_TB_EXPENSES_BUDGETS."
                    (business_budget_id, seq, expense_name, plan_trip, officer, people, work_days,
                    allowance_rate, allowance_amount, transport_rate, transport_amount,
                    stay_days, stay_rate, stay_amount, fuel_trips, fuel_rate, fuel_amount,
                    total_activity)
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18)
                ";

                $obj = [
                    $budget_id,
                    $key + 1,
                    (!$hasInserted) ? $expenseName : null,
                    $planTrip,
                    $officer,
                    $people         !== null && $people         !== '' ? $people  : null,
                    $workDays       !== null && $workDays       !== '' ? $workDays  : null,
                    $allowanceRate  !== null && $allowanceRate  !== '' ? $allowanceRate  : null,
                    $allowanceAmount!== null && $allowanceAmount!== '' ? $allowanceAmount: null,
                    $transportRate  !== null && $transportRate  !== '' ? $transportRate  : null,
                    $transportAmount!== null && $transportAmount!== '' ? $transportAmount: null,
                    $stayDays       !== null && $stayDays       !== '' ? $stayDays       : null,
                    $stayRate       !== null && $stayRate       !== '' ? $stayRate       : null,
                    $stayAmount     !== null && $stayAmount     !== '' ? $stayAmount     : null,
                    $fuelTrips      !== null && $fuelTrips      !== '' ? $fuelTrips      : null,
                    $fuelRate       !== null && $fuelRate       !== '' ? $fuelRate       : null,
                    $fuelAmount     !== null && $fuelAmount     !== '' ? $fuelAmount     : null,
                    $totalActivity  !== null && $totalActivity  !== '' ? $totalActivity  : null
                ];

                pg_query_params($this->conn, $query, $obj);

                $hasInserted = true;
            }

            if (!$hasInserted) {
                $query = "
                    INSERT INTO ".BUDGETS_TB_EXPENSES_BUDGETS."
                    (business_budget_id, seq, expense_name, plan_trip, officer, people, work_days,
                    allowance_rate, allowance_amount, transport_rate, transport_amount,
                    stay_days, stay_rate, stay_amount, fuel_trips, fuel_rate, fuel_amount,
                    total_activity)
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18)
                ";
                pg_query_params($this->conn, $query, [
                    $budget_id,
                    $key + 1,
                    $expenseName,
                    null, null, null, null,
                    null, null, null, null,
                    null, null, null, null, null, null, null
                ]);
            }
        }
    }

    public function insertOperationBudgets($budget_id, $operations)
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_OPERATION_BUDGETS."
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        foreach ($operations as $key => $operation) {
            $planName = isset($operation['operation_plan_name'][0]) ? $operation['operation_plan_name'][0] : null;
            $len = max(
                isset($operation['operation_plan_name']) ? count($operation['operation_plan_name']) : 0,
                isset($operation['operation_plan_activity']) ? count($operation['operation_plan_activity']) : 0,
                isset($operation['weight']) ? count($operation['weight']) : 0,
                isset($operation['target']) ? count($operation['target']) : 0,
                isset($operation['unit']) ? count($operation['unit']) : 0,
                isset($operation['oct']) ? count($operation['oct']) : 0,
                isset($operation['nov']) ? count($operation['nov']) : 0,
                isset($operation['dec']) ? count($operation['dec']) : 0,
                isset($operation['jan']) ? count($operation['jan']) : 0,
                isset($operation['feb']) ? count($operation['feb']) : 0,
                isset($operation['mar']) ? count($operation['mar']) : 0,
                isset($operation['apr']) ? count($operation['apr']) : 0,
                isset($operation['may']) ? count($operation['may']) : 0,
                isset($operation['jun']) ? count($operation['jun']) : 0,
                isset($operation['jul']) ? count($operation['jul']) : 0,
                isset($operation['aug']) ? count($operation['aug']) : 0,
                isset($operation['sep']) ? count($operation['sep']) : 0,
                isset($operation['operation_plan_assignee']) ? count($operation['operation_plan_assignee']) : 0
            );

            $hasInserted = false;

            for ($i = 0; $i < $len; $i++) {
                $planActivity = isset($operation['operation_plan_activity'][$i]) ? $operation['operation_plan_activity'][$i] : null;
                $weight       = isset($operation['weight'][$i]) ? $operation['weight'][$i] : null;
                $target       = isset($operation['target'][$i]) ? $operation['target'][$i] : null;
                $unit         = isset($operation['unit'][$i]) ? $operation['unit'][$i] : null;
                $oct          = isset($operation['oct'][$i]) ? $operation['oct'][$i] : null;
                $nov          = isset($operation['nov'][$i]) ? $operation['nov'][$i] : null;
                $dec          = isset($operation['dec'][$i]) ? $operation['dec'][$i] : null;
                $jan          = isset($operation['jan'][$i]) ? $operation['jan'][$i] : null;
                $feb          = isset($operation['feb'][$i]) ? $operation['feb'][$i] : null;
                $mar          = isset($operation['mar'][$i]) ? $operation['mar'][$i] : null;
                $apr          = isset($operation['apr'][$i]) ? $operation['apr'][$i] : null;
                $may          = isset($operation['may'][$i]) ? $operation['may'][$i] : null;
                $jun          = isset($operation['jun'][$i]) ? $operation['jun'][$i] : null;
                $jul          = isset($operation['jul'][$i]) ? $operation['jul'][$i] : null;
                $aug          = isset($operation['aug'][$i]) ? $operation['aug'][$i] : null;
                $sep          = isset($operation['sep'][$i]) ? $operation['sep'][$i] : null;
                $assignee     = isset($operation['operation_plan_assignee'][$i]) ? $operation['operation_plan_assignee'][$i] : null;

                $numericEmpty = ($planActivity === null || $planActivity === '') &&
                                ($weight === null || $weight === '') &&
                                ($unit === null || $unit === '') &&
                                ($oct === null || $oct === '') &&
                                ($nov === null || $nov === '') &&
                                ($dec === null || $dec === '') &&
                                ($jan === null || $jan === '') &&
                                ($feb === null || $feb === '') &&
                                ($mar === null || $mar === '') &&
                                ($apr === null || $apr === '') &&
                                ($may === null || $may === '') &&
                                ($jun === null || $jun === '') &&
                                ($jul === null || $jul === '') &&
                                ($aug === null || $aug === '') &&
                                ($sep === null || $sep === '') &&
                                ($assignee === null || $assignee === '');

                if ($numericEmpty) {
                    continue;
                }

                $query = "
                    INSERT INTO ".BUDGETS_TB_OPERATION_BUDGETS."
                    (business_budget_id, seq, operation_plan_name, operation_plan_activity, weight, target, unit,
                    oct, nov, dec, jan, feb, mar, apr, may, jun, jul, aug, sep, operation_plan_assignee)
                    VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20)
                ";

                $obj = [
                    $budget_id,
                    $key + 1,
                    (!$hasInserted) ? $planName : null,
                    $planActivity,
                    $weight,
                    $target  !== null && $target  !== '' ? $target  : null,
                    $unit,
                    $oct     !== null && $oct     !== '' ? $oct     : null,
                    $nov     !== null && $nov     !== '' ? $nov     : null,
                    $dec     !== null && $dec     !== '' ? $dec     : null,
                    $jan     !== null && $jan     !== '' ? $jan     : null,
                    $feb     !== null && $feb     !== '' ? $feb     : null,
                    $mar     !== null && $mar     !== '' ? $mar     : null,
                    $apr     !== null && $apr     !== '' ? $apr     : null,
                    $may     !== null && $may     !== '' ? $may     : null,
                    $jun     !== null && $jun     !== '' ? $jun     : null,
                    $jul     !== null && $jul     !== '' ? $jul     : null,
                    $aug     !== null && $aug     !== '' ? $aug     : null,
                    $sep     !== null && $sep     !== '' ? $sep     : null,
                    $assignee
                ];

                pg_query_params($this->conn, $query, $obj);

                $hasInserted = true;
            }

            if (!$hasInserted) {
                $query = "
                    INSERT INTO ".BUDGETS_TB_OPERATION_BUDGETS."
                    (business_budget_id, seq, operation_plan_name, operation_plan_activity, weight, target, unit,
                    oct, nov, dec, jan, feb, mar, apr, may, jun, jul, aug, sep, operation_plan_assignee)
                    VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20)
                ";
                pg_query_params($this->conn, $query, [
                    $budget_id,
                    $key + 1,
                    $planName,
                    null,null,null,null,
                    null,null,null,null,null,null,null,null,null,null,null,null,
                    null
                ]);
            }
        }
    }



    public function insertRiskPlans($budget_id, $data)
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_RISK_PLANS."
            WHERE business_budget_id = $1
        ";
        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seqCounter = 1;

        $len = isset($data['risk_management_plan']) ? count($data['risk_management_plan']) : 0;

        for ($i = 0; $i < $len; $i++) {
            $riskPlan          = isset($data['risk_management_plan'][$i]) ? $data['risk_management_plan'][$i] : null;
            $responsiblePerson = isset($data['responsible_person'][$i]) ? $data['responsible_person'][$i] : null;
            $duration          = isset($data['duration'][$i]) ? $data['duration'][$i] : null;
            $budget            = isset($data['budget'][$i]) ? $data['budget'][$i] : null;
            $progressPlan      = isset($data['progress_risk_plan'][$i]) ? $data['progress_risk_plan'][$i] : null;
            $progressResp      = isset($data['progress_responsible'][$i]) ? $data['progress_responsible'][$i] : null;
            $progressDuration  = isset($data['progress_duration'][$i]) ? $data['progress_duration'][$i] : null;

            if (empty($riskPlan) && empty($responsiblePerson) && empty($duration) && empty($budget) &&
                empty($progressPlan) && empty($progressResp) && empty($progressDuration)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_RISK_PLANS."
                (business_budget_id, seq, risk_management_plan, responsible_person, duration, budget,
                progress_risk_plan, progress_responsible, progress_duration)
                VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
            ";

            $object = [
                $budget_id,
                $seqCounter++,
                $riskPlan,
                $responsiblePerson,
                $duration,
                $budget,
                $progressPlan,
                $progressResp,
                $progressDuration
            ];

            pg_query_params($this->conn, $query, $object);
        }
    }


    public function insertActivities($budget_id, $data)
    {
        $deleteQuery = "
            DELETE FROM ".BUDGETS_TB_ACTIVITIES."
            WHERE business_budget_id = $1
        ";

        pg_query_params($this->conn, $deleteQuery, [$budget_id]);

        $seq = 1;

        foreach ($data as $act) {
            if (empty($act)) {
                continue;
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_ACTIVITIES."
                (business_budget_id, seq, activity_code)
                VALUES ($1, $2, $3)
            ";

            $object = [
                $budget_id,
                $seq++,
                $act,
            ];
            

            pg_query_params($this->conn, $query, $object);
        }
    }


    private function insertAsses($budget_id, $data)
    {
        if (!empty(array_filter(isset($data['activity_code']) ? $data['activity_code'] : []))) {
            $this->insertActivities($budget_id, $data['activity_code']);
        }
        
        if (!empty(array_filter(isset($data['objective']) ? $data['objective'] : []))) {
            $this->insertObjectives($budget_id, $data['objective']);
        }

        $output_indicators = isset($data['output_indicators']) ? $data['output_indicators'] : [];
        $output_target     = isset($data['output_target']) ? $data['output_target'] : [];
        $output_counting   = isset($data['output_counting']) ? $data['output_counting'] : [];

        if (!empty(array_filter($output_indicators)) 
            || !empty(array_filter($output_target)) 
            || !empty(array_filter($output_counting))) {
            
            $this->insertOutputIndicators($budget_id, $output_indicators, $output_target, $output_counting);
        }

        $outcome_indicators = isset($data['outcome_indicators']) ? $data['outcome_indicators'] : [];
        $outcome_target     = isset($data['outcome_target']) ? $data['outcome_target'] : [];
        $outcome_counting   = isset($data['outcome_counting']) ? $data['outcome_counting'] : [];

        if (!empty(array_filter($outcome_indicators))
            || !empty(array_filter($outcome_target))
            || !empty(array_filter($outcome_counting))) {
            
            $this->insertOutcomeIndicators($budget_id, $outcome_indicators, $outcome_target, $outcome_counting);
        }


        $target_group  = isset($data['target_group']) ? $data['target_group'] : [];
        $target_number = isset($data['target_number']) ? $data['target_number'] : [];
        $target_area   = isset($data['target_area']) ? $data['target_area'] : [];

        if (!empty(array_filter($target_group))
            || !empty(array_filter($target_number))
            || !empty(array_filter($target_area))) {
                
            $this->insertTargetGroups($budget_id, $target_group, $target_number, $target_area);
        }

        $expected_benefit = isset($data['expected_benefit']) ? $data['expected_benefit'] : [];
        if (!empty(array_filter($expected_benefit))) {
            $this->insertExpectedBenefits($budget_id, $expected_benefit);
        }

        $activities = isset($data['activities']) ? $data['activities'] : [];
        if (!empty(array_filter($activities))) {
            $this->insertActivityBudgets($budget_id, $activities);
        }

        $expenses = isset($data['expenses']) ? $data['expenses'] : [];
        if (!empty(array_filter($expenses))) {
            $this->insertExpensesBudgets($budget_id, $expenses);
        }

        $operations = isset($data['operations']) ? $data['operations'] : [];
        if (!empty(array_filter($operations))) {
            $this->insertOperationBudgets($budget_id, $operations);
        }

        $this->insertRiskPlans($budget_id, $data);
    }

    public function insertBusinessBudget($data) 
    {
        pg_query($this->conn, "BEGIN");

        try {
            $obj = $this->buildObject($data);

            $obj = $this->sanitizeForInsert($obj);

            $documentNumber = $this->generateRunningNumber('BUDGET');

            $obj['document_number'] = $documentNumber;

            $obj['created_at'] = convertDateTime();
            $obj['created_by'] = $_SESSION['user_data']['id'];
            $obj['depart_id'] = $_SESSION['user_data']['depart_id'];

            $placeholders = array();
            for ($i = 1; $i <= count($obj); $i++) {
                $placeholders[] = '$' . $i;
                
            }

            $query = "
                INSERT INTO ".BUDGETS_TB_BUSINESS_BUDGETS." (".implode(',', array_keys($obj)).")
                VALUES (".implode(',', $placeholders).")
                RETURNING id;
            ";

            $result = pg_query_params($this->conn, $query, array_values($obj));

            if (!$result) {
                arrx($result);
                throw new Exception("Insert ".BUDGETS_TB_BUSINESS_BUDGETS." failed");
            }

            $row = pg_fetch_assoc($result);
            $budget_id = $row['id'];

            $this->insertAsses($budget_id, $data);

            pg_query($this->conn, "COMMIT");

            return $budget_id;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log($e->getMessage());
            return null;
        }
    }

    public function updateBusinessBudget($id, $data) 
    {
        pg_query($this->conn, "BEGIN");

        try {
            $obj = $this->buildObject($data);

            $obj = $this->sanitizeForInsert($obj);
            $obj['updated_at'] = convertDateTime();
            $obj['updated_by'] = $_SESSION['user_data']['id'];

            $setParts = array();
            $i = 1;
            foreach ($obj as $key => $value) {
                $setParts[] = "$key = $" . $i;
                $i++;
            }

            $query = "
                UPDATE ".BUDGETS_TB_BUSINESS_BUDGETS."
                SET " . implode(', ', $setParts) . "
                WHERE id = $" . $i . "
                RETURNING id;
            ";

            $values = array_values($obj);
            $values[] = $id;

            $result = pg_query_params($this->conn, $query, $values);

            if (!$result) {
                throw new Exception("Update ".BUDGETS_TB_BUSINESS_BUDGETS." failed");
            }

            $row = pg_fetch_assoc($result);

            $this->insertAsses($id, $data);

            pg_query($this->conn, "COMMIT");

            return $id;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log($e->getMessage());
            return null;
        }
    }

    // condition document list for user create only
    public function getBusinessList($param = []) 
    {
        try {
            $user = $_SESSION['user_data'];
            $params = [];
            $conditions = ["bb.deleted_at IS NULL"];
            $conditions[] = "bb.position_id = '".$user['position_id']."'";
            $conditions[] = "bb.created_by = '".$user['id']."'";
            $count = 0;
            
            if (isset($param['document_number']) && trim($param['document_number']) !== '') {
                $conditions[] = "bb.document_number ILIKE $" . (count($params) + 1);
                $params[] = '%' . trim($param['document_number']) . '%';
            }

            if (isset($param['start_date']) && trim($param['start_date']) !== '') {
                $conditions[] = "bb.created_at >= $" . (count($params) + 1);
                $params[] = trim($param['start_date']);
            }

            if (isset($param['end_date']) && trim($param['end_date']) !== '') {
                $conditions[] = "bb.created_at <= $" . (count($params) + 1);
                $params[] = trim($param['end_date']. ' 23:59:59');
            }

            if (isset($param['status']) && trim($param['status']) !== '') {
                $conditions[] = "bb.status = $" . (count($params) + 1);
                $params[] = trim($param['status']);
            }

            $whereClause = "WHERE " . implode(" AND ", $conditions);

            $query = "SELECT 
                DISTINCT bb.*,
                d.depart_name,
                (SELECT project_name FROM ".PARAMETERS_TB_PROJECTS." WHERE project_code = bb.project_code) AS old_project_name,
                (SELECT concat(user_fname, ' ', user_lname) FROM ".USERS_TB_USERS." WHERE id = bb.created_by) AS request_name
            FROM ".BUDGETS_TB_BUSINESS_BUDGETS." bb 
            join ".USERS_TB_DEPARTS." d on d.id = bb.depart_id";

            $query .= "
                $whereClause
                ORDER BY id DESC";

            $result = pg_query_params($this->conn, $query, $params);

            return $result ? pg_fetch_all($result) : [];
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getBusinessApproveList($param = []) 
    {
        try {
            $user = $_SESSION['user_data'];

            if (empty($user['position_list']) || !is_array($user['position_list'])) {
                return [];
            }

            $positionList = $user['position_list'];

            $params = [];
            $conditions = ["bb.deleted_at IS NULL"];
            $conditions[] = "bb.created_by != '".$user['id']."'";
            $count = 0;

            $allowedPositions = [];

            foreach($user['position_list'] as $key => $rs) {
                if($rs['position_code'] === 'APBR' && $rs['approve_branch'] === 'active') {
                    $allowedPositions[] = $rs['position_code'];
                }

                if($rs['position_code'] === 'APPV' && $rs['approve_province'] === 'active') {
                    $allowedPositions[] = $rs['position_code'];
                }

                if($rs['position_code'] === 'APAR' && $rs['approve_airea'] === 'active') {
                    $allowedPositions[] = $rs['position_code'];
                }

                if($rs['position_code'] === 'APPRP' && $rs['approve_head_office'] === 'active') {
                    $allowedPositions[] = $rs['position_code'];
                }

                if($rs['position_code'] === 'APSTR' && $rs['approve_head_office'] === 'active') {
                    $allowedPositions[] = $rs['position_code'];
                }
            }

            //$allowedPositions = array_column(array_filter($positionList, fn($p) => $p['position_code'] !== 'EMPY'),'position_code');

            if (empty($allowedPositions)) {
                return [];
            }

            /*$approveConditions = [];

            foreach ($allowedPositions as $pos) {
                if ($pos === 'APPRP') {
                    $approveConditions[] = "(bb.approve_position_code = 'APPRP' AND bb.responsibilities_approves = '".$user['depart_code']."')";
                } else {
                    $approveConditions[] = "(bb.approve_position_code = '".$pos."' AND bb.responsibilities_approves IS NULL)";
                }
            }

            $conditions[] = "(" . implode(' OR ', $approveConditions) . ")";*/


            $approveConditions = [];
            
            $geoConditions = [];

            foreach ($allowedPositions as $pos) {
                if ($pos === 'APPRP') {
                    $approveConditions[] = "(bb.approve_position_code = 'APPRP' AND bb.responsibilities_approves = '".$user['depart_code']."')";
                } elseif ($pos === 'APBR') {
                    $geoConditions[] = "(b.id = ".$user['branch_id'].")";
                    $approveConditions[] = "(bb.approve_position_code = 'APBR' AND " . end($geoConditions) . ")";
                } elseif ($pos === 'APPV') {
                    $geoConditions[] = "(b.id IN (
                        SELECT id 
                        FROM ".USERS_TB_BRANCHS." 
                        WHERE province_id = (
                            SELECT province_id 
                            FROM ".USERS_TB_BRANCHS." 
                            WHERE id = ".$user['branch_id']."
                        )
                    ))";
                    $approveConditions[] = "(bb.approve_position_code = 'APPV' AND " . end($geoConditions) . ")";
                } elseif ($pos === 'APAR') {
                    $geoConditions[] = "(a.id = ".$user['area_id'].")";
                    $approveConditions[] = "(bb.approve_position_code = 'APAR' AND " . end($geoConditions) . ")";
                } elseif ($pos === 'APPRP') {
                    $approveConditions[] = "(bb.approve_position_code = 'APPRP' AND bb.responsibilities_approves = '".$user['depart_code']."')";
                } else {
                    $approveConditions[] = "(bb.approve_position_code = '".$pos."' AND bb.responsibilities_approves IS NULL)";
                }
            }
            
            $conditions[] = "(" . implode(' OR ', $approveConditions) . ")";

            if (isset($param['document_number']) && trim($param['document_number']) !== '') {
                $conditions[] = "bb.document_number ILIKE $" . (count($params) + 1);
                $params[] = '%' . trim($param['document_number']) . '%';
            }

            if (isset($param['start_date']) && trim($param['start_date']) !== '') {
                $conditions[] = "bb.created_at >= $" . (count($params) + 1);
                $params[] = trim($param['start_date']);
            }

            if (isset($param['end_date']) && trim($param['end_date']) !== '') {
                $conditions[] = "bb.created_at <= $" . (count($params) + 1);
                $params[] = trim($param['end_date']. ' 23:59:59');
            }

            $whereClause = "WHERE " . implode(" AND ", $conditions);

            $query = "SELECT 
                DISTINCT bb.*,
                d.depart_name,
                (SELECT project_name FROM ".PARAMETERS_TB_PROJECTS." WHERE project_code = bb.project_code) AS old_project_name,
                (SELECT concat(user_fname, ' ', user_lname) FROM ".USERS_TB_USERS." WHERE id = bb.created_by) AS request_name
            FROM ".BUDGETS_TB_BUSINESS_BUDGETS." bb 
            join ".USERS_TB_DEPARTS." d on d.id = bb.depart_id 
            join users.tb_users u on u.id = bb.created_by
            join users.tb_branchs b on b.id = u.branch_id 
            join users.tb_provinces p on p.id = b.province_id 
            join users.tb_areas a on a.id = p.area_id ";

            $query .= "
                $whereClause
                ORDER BY id DESC";

            $result = pg_query_params($this->conn, $query, $params);

            return $result ? pg_fetch_all($result) : [];
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }


    public function getBusinessBudgetById($id) {
        $query = "SELECT * FROM ".BUDGETS_TB_BUSINESS_BUDGETS." WHERE id = $1";
        $result = pg_query_params($this->conn, $query, [$id]);

        if ($result && pg_num_rows($result) > 0) {
            $data = pg_fetch_assoc($result);

            $data['activity_code'] = $this->getActivityCode($data['id']);
            $data['expected_benefit'] = $this->getExpectedBenefits($data['id']);
            $data['objective'] = $this->getObjectives($data['id']);
            $data['output_indicators'] = $this->getOutputIndicators($data['id']);
            $data['outcome_indicators'] = $this->getOutcomeIndicators($data['id']);
            $data['target_group'] = $this->getTargetGroups($data['id']);
            $data['activity_budgets'] = $this->getActivityBudgets($data['id']);
            $data['expenses_budgets'] = $this->getExpensesBudgets($data['id']);
            $data['operations_budgets'] = $this->getOperationsBudgets($data['id']);
            $data['risk_plans'] = $this->getRiskPlans($data['id']);
            return $data;
        }
        return null;
    }

    public function getStrategies() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_NATIONAL_STRATEGIES." ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getProject()
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_PROJECTS." ORDER BY project_code ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectActivity($projectId)
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_PROJECTS_ACTIVITIES." WHERE project_code = $1 ORDER BY activity_code ASC";
            $result = pg_query_params($this->conn, $query, [$projectId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getTargetStrategies($strategyId) 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_NATIONAL_STRATEGY_GOALS." where national_strategy_id = '".$strategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getSubjectStrategies($strategyId) 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_SUBJECT_STRATEGIES." where national_strategy_id = '".$strategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getPlanUnderStrategies() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_PLAN_UNDER_STRATEGIES." ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getBudgetSource() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_BUDGET_SOURCES." ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getTargetLevelSubjects($planUnderStrategyId)
    {
        try {
            $query = "SELECT id, name FROM ".PARAMETERS_TB_TARGET_LEVEL_SUBJECTS." where plan_under_strategy_id = '".$planUnderStrategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getSubPlans($planUnderStrategyId) 
    {
        try {
            $query = "SELECT id, name FROM ".PARAMETERS_TB_SUB_PLANS." where plan_under_strategy_id = '".$planUnderStrategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getTargetSubPlans($subPlanId) 
    {
        try {
            $query = "SELECT id, name FROM ".PARAMETERS_TB_TARGET_SUB_PLANS." where sub_plan_id = '".$subPlanId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getPlanStrategies() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_PLAN_STRATEGIES." ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getSubIndicators($planStrategyId) 
    {
        try {
            $query = "SELECT id, name FROM ".PARAMETERS_TB_SUB_INDICATORS." where plan_strategy_id = '".$planStrategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getTactics($planStrategyId) 
    {
        try {
            $query = "SELECT id, name FROM ".PARAMETERS_TB_TACTICS." where plan_strategy_id = '".$planStrategyId."' ORDER BY id ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getExpectedBenefits($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_EXPECTED_BENEFITS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }   

    public function getObjectives($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OBJECTIVES." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getOutputIndicators($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OUTPUT_INDICATORS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getOutcomeIndicators($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OUTCOME_INDICATORS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getTargetGroups($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_TARGET_GROUPS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getActivityBudgets($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_ACTIVITY_BUDGETS." where business_budget_id = $1 ORDER BY id ASC, seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                $data = array(
                    'total_summary_amount' => 0,
                    'list' => array()
                );

                if($rows) {
                    foreach($rows as $key => $rs) {
                        $data['total_summary_amount'] += $rs['total_amount'];
                        $data['list'][$rs['seq'] - 1][] = $rs;
                    }
                }
                
                return $data;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getExpensesBudgets($businessBudgetId) 
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_EXPENSES_BUDGETS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                
                $data = array(
                    'total_summary_allowance' => 0,
                    'total_summary_transport' => 0,
                    'total_summary_stay' => 0,
                    'total_summary_fuel' => 0,
                    'total_summary_amount' => 0,
                    'list' => array()
                );

                if($rows) {
                    foreach($rows as $key => $rs) {
                        $data['total_summary_allowance'] += $rs['allowance_amount'];
                        $data['total_summary_transport'] += $rs['transport_amount'];
                        $data['total_summary_stay'] += $rs['stay_amount'];
                        $data['total_summary_fuel'] += $rs['fuel_amount'];
                        $data['list'][$rs['seq'] - 1][] = $rs;
                    }

                    $data['total_summary_amount'] = $data['total_summary_allowance'] + $data['total_summary_transport'] + $data['total_summary_stay'] + $data['total_summary_fuel'];
                }
                
                return $data;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getOperationsBudgets($businessBudgetId)
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_OPERATION_BUDGETS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                
                $data = array(
                    'total_summary_weight_amount' => 0,
                    'list' => array()
                );

                if($rows) {
                    foreach($rows as $key => $rs) {
                        $data['list'][$rs['seq'] - 1][] = $rs;
                        $data['total_summary_weight_amount'] += (float)$rs['weight'];
                    }
                }
                return $data;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getRiskPlans($businessBudgetId)
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_RISK_PLANS." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                
                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getActivityCode($businessBudgetId)
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_ACTIVITIES." where business_budget_id = $1 ORDER BY seq ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);

                $data = array();

                if($rows) {
                    foreach($rows as $key => $rs) {
                        $data[] = $rs['activity_code'];
                    }
                }

                return $data;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepart()
    {
        try {
            $query = "SELECT * FROM ".USERS_TB_DEPARTS." order by id asc";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function approve($post)
    {
        pg_query($this->conn, "BEGIN");

        try {
            $id = $post['id'];
            $objUpdate = array(
                'approved_by' => $post['approved_by'],
                'approved_at' => convertDateTime()
            );

            if (isset($post['approve_status']) && ($post['position_code'] == 'APPRP' || $post['position_code'] == 'APSTR')) {
                $objUpdate['status'] = $post['status'];
                $objUpdate['approve_position_code'] = null;
            } else {
                $objUpdate['approve_position_code'] = $post['approve_position_code'];

                if (isset($post['depart_code']) && !empty($post['depart_code'])) {
                    $objUpdate['responsibilities_approves'] = $post['depart_code'];
                }
            }

            $setParts = [];
            $values = [];
            $i = 1;
            foreach ($objUpdate as $key => $val) {
                $setParts[] = "$key = \$$i";
                $values[] = $val;
                $i++;
            }

            $values[] = $id;

            $query = "
                UPDATE ".BUDGETS_TB_BUSINESS_BUDGETS."
                SET " . implode(', ', $setParts) . "
                WHERE id = $" . $i . "
                RETURNING id;
            ";

            $result = pg_query_params($this->conn, $query, $values);

            if (!$result) {
                throw new Exception("Update ".BUDGETS_TB_BUSINESS_BUDGETS." failed");
            }

            $row = pg_fetch_assoc($result);

            $this->approve_history($post);

            pg_query($this->conn, "COMMIT");

            return $id;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log($e->getMessage());
            return null;
        }
    }

    public function reject($post)
    {
        pg_query($this->conn, "BEGIN");

        try {
            $id = $post['id'];
            $objUpdate = array(
                'status' => 'rejected',
                'approved_by' => $post['approved_by'],
                'approved_at' => convertDateTime(),
                'approve_position_code' => null,
                'responsibilities_approves' => null
            );

            $setParts = [];
            $values = [];
            $i = 1;
            foreach ($objUpdate as $key => $val) {
                $setParts[] = "$key = \$$i";
                $values[] = $val;
                $i++;
            }

            $values[] = $id;

            $query = "
                UPDATE ".BUDGETS_TB_BUSINESS_BUDGETS."
                SET " . implode(', ', $setParts) . "
                WHERE id = $" . $i . "
                RETURNING id;
            ";

            $result = pg_query_params($this->conn, $query, $values);

            if (!$result) {
                throw new Exception("Update ".BUDGETS_TB_BUSINESS_BUDGETS." failed");
            }

            $row = pg_fetch_assoc($result);

            $this->approve_history($post);

            pg_query($this->conn, "COMMIT");

            return $id;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log($e->getMessage());
            return null;
        }
    }

   public function approve_history($object)
    {
        if (empty($object['id'])) {
            throw new Exception("Business budget ID is required");
        }

        $businessBudgetId = $object['id'];
        $approveDate = convertDateTime();
        $status = isset($object['approve_status']) && $object['approve_status'] == 'approve' ? 'approved' : 'rejected';
        $approverName = $object['approve_name'] ? $object['approve_name'] : '';
        $positionCode =  $object['position_code'] ? $object['position_code'] : '';  
        $departCode = isset($object['depart_code']) ? $object['depart_code'] : '';
        $remark = isset($object['remark']) ? $object['remark'] : '';     

        $query = "
            INSERT INTO ".BUDGETS_TB_APPROVE_HISTORIES."
            (business_budget_id, approve_date, status, approver_name, position_code, remark, depart_code)
            VALUES
            ($1, $2, $3, $4, $5, $6, $7)
            RETURNING id;
        ";

        $params = [
            $businessBudgetId,
            $approveDate,
            $status,
            $approverName,
            $positionCode,
            $remark,
            $departCode
        ];

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception("Insert into tb_approve_history failed");
        }

        $row = pg_fetch_assoc($result);
        return $row['id'];
    }

    public function getApproveHistory($businessBudgetId)
    {
        try {
            $query = "SELECT * FROM ".BUDGETS_TB_APPROVE_HISTORIES." where business_budget_id = $1 ORDER BY id ASC";
            $result = pg_query_params($this->conn, $query, [$businessBudgetId]);

            if ($result) {
                $rows = pg_fetch_all($result);
                
                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function delete($id)
    {
        pg_query($this->conn, "BEGIN");

        try {
            $id = intval($id);

            $objUpdate = array(
                'deleted_at' => convertDateTime(),
                'deleted_by' => $_SESSION['user_data']['id']
            );

            $query = "UPDATE ".BUDGETS_TB_BUSINESS_BUDGETS."
                    SET deleted_at = $1, deleted_by = $2
                    WHERE id = $3
                    RETURNING id";

            $values = [$objUpdate['deleted_at'], $objUpdate['deleted_by'], $id];

            $result = pg_query_params($this->conn, $query, $values);

            if (!$result) {
                throw new Exception("Soft delete ".BUDGETS_TB_BUSINESS_BUDGETS." failed");
            }

            pg_query($this->conn, "COMMIT");

            return $id;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log($e->getMessage());
            return null;
        }
    }

    public function getRiskStrategicObjectives() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_RISK_STRATEGIES_OBJECTIVES." ORDER BY code ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getRiskNationalStrategies() 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_RISK_NATIONAL_STRATEGIES."  ORDER BY code ASC";
            $result = pg_query($this->conn, $query);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getRiskStrategiesIndicators($id) 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_RISK_STRATEGIES_INDICATORS." where national_strategies_code = $1 ORDER BY code ASC";
            $result = pg_query_params($this->conn, $query, [$id]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function getRiskStrategies($id) 
    {
        try {
            $query = "SELECT * FROM ".PARAMETERS_TB_RISK_STRATEGIES." where national_strategies_code = $1 ORDER BY code ASC";
            $result = pg_query_params($this->conn, $query, [$id]);

            if ($result) {
                $rows = pg_fetch_all($result);

                return $rows;
            } else {
                return;
            }
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }
}
