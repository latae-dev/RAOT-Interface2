<?php
#- Schema
define('DB_SCHEMAS_PARAMETERS', 'parameters');
define('DB_SCHEMAS_BUDGETS', 'budgets');
define('DB_SHCEMAS_USERS', 'users');

#- Master Table
define('PARAMETERS_TB_BUDGET_SOURCES', DB_SCHEMAS_PARAMETERS . '.tb_budget_sources');
define('PARAMETERS_TB_NATIONAL_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_national_strategies');
define('PARAMETERS_TB_NATIONAL_STRATEGY_GOALS', DB_SCHEMAS_PARAMETERS . '.tb_national_strategy_goals');
define('PARAMETERS_TB_PLAN_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_plan_strategies');
define('PARAMETERS_TB_PLAN_TYPES', DB_SCHEMAS_PARAMETERS . '.tb_plan_types');
define('PARAMETERS_TB_PLAN_UNDER_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_plan_under_strategies');
define('PARAMETERS_TB_PROJECTS', DB_SCHEMAS_PARAMETERS . '.tb_projects');
define('PARAMETERS_TB_PROJECTS_ACTIVITIES', DB_SCHEMAS_PARAMETERS . '.tb_projects_activities');
define('PARAMETERS_TB_RISK_NATIONAL_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_risk_national_strategies');
define('PARAMETERS_TB_RISK_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_risk_strategies');
define('PARAMETERS_TB_RISK_STRATEGIES_INDICATORS', DB_SCHEMAS_PARAMETERS . '.tb_risk_strategies_indicators');
define('PARAMETERS_TB_RISK_STRATEGIES_OBJECTIVES', DB_SCHEMAS_PARAMETERS . '.tb_risk_strategies_objectives');
define('PARAMETERS_TB_SUB_INDICATORS', DB_SCHEMAS_PARAMETERS . '.tb_sub_indicators');
define('PARAMETERS_TB_SUB_PLANS', DB_SCHEMAS_PARAMETERS . '.tb_sub_plans');
define('PARAMETERS_TB_SUBJECT_STRATEGIES', DB_SCHEMAS_PARAMETERS . '.tb_subject_strategies');
define('PARAMETERS_TB_TACTICS', DB_SCHEMAS_PARAMETERS . '.tb_tactics');
define('PARAMETERS_TB_TARGET_LEVEL_SUBJECTS', DB_SCHEMAS_PARAMETERS . '.tb_target_level_subjects');
define('PARAMETERS_TB_TARGET_SUB_PLANS', DB_SCHEMAS_PARAMETERS . '.tb_target_sub_plans');

#- Users
define('USERS_TB_BRANCHS', DB_SHCEMAS_USERS . '.tb_branchs');
define('USERS_TB_DEPARTS', DB_SHCEMAS_USERS . '.tb_departs');
define('USERS_TB_POSITIONS', DB_SHCEMAS_USERS . '.tb_positions');
define('USERS_TB_POSITIONS_USERS', DB_SHCEMAS_USERS . '.tb_positions_users');
define('USERS_TB_PROVINCES', DB_SHCEMAS_USERS . '.tb_provinces');
define('USERS_TB_USERS', DB_SHCEMAS_USERS . '.tb_users');

#- Budgets
define('BUDGETS_TB_ACTIVITIES', DB_SCHEMAS_BUDGETS . '.tb_activities');
define('BUDGETS_TB_ACTIVITY_BUDGETS', DB_SCHEMAS_BUDGETS . '.tb_activity_budgets');
define('BUDGETS_TB_APPROVE_HISTORIES', DB_SCHEMAS_BUDGETS . '.tb_approve_histories');
define('BUDGETS_TB_BUSINESS_BUDGETS', DB_SCHEMAS_BUDGETS . '.tb_business_budgets');
define('BUDGETS_TB_EXPECTED_BENEFITS', DB_SCHEMAS_BUDGETS . '.tb_expected_benefits');
define('BUDGETS_TB_EXPENSES_BUDGETS', DB_SCHEMAS_BUDGETS . '.tb_expenses_budgets');
define('BUDGETS_TB_OBJECTIVES', DB_SCHEMAS_BUDGETS . '.tb_objectives');
define('BUDGETS_TB_OPERATION_BUDGETS', DB_SCHEMAS_BUDGETS . '.tb_operation_budgets');
define('BUDGETS_TB_OUTCOME_INDICATORS', DB_SCHEMAS_BUDGETS . '.tb_outcome_indicators');
define('BUDGETS_TB_OUTPUT_INDICATORS', DB_SCHEMAS_BUDGETS . '.tb_output_indicators');
define('BUDGETS_TB_RISK_PLANS', DB_SCHEMAS_BUDGETS . '.tb_risk_plans');
define('BUDGETS_TB_RUNNING_NUMBERS', DB_SCHEMAS_BUDGETS . '.tb_running_numbers');
define('BUDGETS_TB_TARGET_GROUPS', DB_SCHEMAS_BUDGETS . '.tb_target_groups');

?>
