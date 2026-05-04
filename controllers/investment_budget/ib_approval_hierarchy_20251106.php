<?php

/**
 * Investment Budget Approval Hierarchy Helper
 * ไฟล์สำหรับจัดการ logic เกี่ยวกับ hierarchy และ approval scope
 *
 * หน้าที่หลัก:
 * - กำหนดระดับของ Department (สาขา/จังหวัด/เขต/สำนักงานใหญ่)
 * - สร้าง approval scope ตามสิทธิ์ของ user
 * - ประเมินว่า user สามารถอนุมัติคำขอได้หรือไม่
 * - กรอง stage ที่ user มองเห็นได้
 * - ป้องกันการอนุมัติของตนเอง
 */

class IbApprovalHierarchy
{
    private $ibRequestsModel;
    private $db;

    /**
     * ลำดับขั้นตอนการอนุมัติ
     */
    private const APPROVAL_STAGES = ['branch', 'province', 'area', 'head_office'];

    /**
     * ระดับของแต่ละ stage (ใช้เปรียบเทียบลำดับ)
     */
    private const STAGE_LEVELS = [
        'branch' => 1,
        'province' => 2,
        'area' => 3,
        'head_office' => 4,
    ];

    public function __construct($ibRequestsModel = null, $db = null)
    {
        $this->ibRequestsModel = $ibRequestsModel;
        $this->db = $db;
    }

    /**
     * ดึงข้อมูล context ของ user จาก session
     *
     * @return array|null ข้อมูล user context หรือ null ถ้าไม่มี session
     */
    public function getCurrentUserContext()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'depart_id' => $_SESSION['depart_id'] ?? null,
            'depart_code' => $_SESSION['depart_code'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'province_id' => $_SESSION['province_id'] ?? null,
            'area_id' => $_SESSION['area_id'] ?? null,
            'head_office_id' => $_SESSION['head_office_id'] ?? null,
            'approve_branch' => $_SESSION['approve_branch'] ?? null,
            'approve_province' => $_SESSION['approve_province'] ?? null,
            // Fallback สำหรับ typo ของ approve_airea → approve_area
            'approve_area' => $_SESSION['approve_area'] ?? ($_SESSION['approve_airea'] ?? null),
            'approve_head_office' => $_SESSION['approve_head_office'] ?? null,
            'approve_investment' => $_SESSION['approve_investment'] ?? null
        ];
    }

    /**
     * หาระดับของ Department โดยเทียบ depart_code กับ code ต่างๆ
     *
     * @param string $departCode รหัสแผนก (depart_code)
     * @param array $request ข้อมูลคำขอที่มี branch_code, province_code, area_code, head_office_code
     * @return string|null ระดับของแผนก ('branch'|'province'|'area'|'head_office') หรือ null ถ้าไม่ตรงกับอะไร
     */
    public function getDepartmentLevel($departCode, array $request)
    {
        if (empty($departCode)) {
            return null;
        }

        // เทียบกับ branch_code (ระดับสาขา)
        if (isset($request['branch_code']) && $departCode === $request['branch_code']) {
            return 'branch';
        }

        // เทียบกับ province_code (ระดับจังหวัด)
        if (isset($request['province_code']) && $departCode === $request['province_code']) {
            return 'province';
        }

        // เทียบกับ area_code (ระดับเขต)
        if (isset($request['area_code']) && $departCode === $request['area_code']) {
            return 'area';
        }

        // เทียบกับ head_office_code (ระดับสำนักงานใหญ่)
        if (isset($request['head_office_code']) && $departCode === $request['head_office_code']) {
            return 'head_office';
        }

        // ไม่ตรงกับระดับใด
        return null;
    }

    /**
     * หาระดับและ ID ของ user จาก depart_code
     * Query ไปตามตาราง tb_branchs, tb_provinces, tb_areas, tb_head_offices
     *
     * @param string $departCode รหัสแผนก (depart_code) จาก session
     * @return array|null ['level' => 'branch|province|area|head_office', 'id' => int] หรือ null ถ้าไม่พบ
     */
    private function getUserLevelAndIdFromDepartCode($departCode)
    {
        $departCode = trim((string) $departCode);

        if ($departCode === '' || !$this->db) {
            if (empty($departCode)) {
                // error_log('⚠️ getUserLevelAndIdFromDepartCode: depart_code ว่าง');
            } elseif (!$this->db) {
                // error_log('⚠️ getUserLevelAndIdFromDepartCode: ไม่มี database connection');
            }
            return null;
        }

        // 1. ลองหาใน tb_branchs (ระดับสาขา)
        $sql = "SELECT id FROM users.tb_branchs WHERE branch_code = $1 LIMIT 1";
        $result = pg_query_params($this->db, $sql, [$departCode]);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            // error_log('✅ getUserLevelAndIdFromDepartCode: depart_code ' . $departCode . ' -> branch #' . $row['id']);
            return ['level' => 'branch', 'id' => (int) $row['id']];
        }

        // 2. ลองหาใน tb_provinces (ระดับจังหวัด)
        $sql = "SELECT id FROM users.tb_provinces WHERE province_code = $1 LIMIT 1";
        $result = pg_query_params($this->db, $sql, [$departCode]);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            // error_log('✅ getUserLevelAndIdFromDepartCode: depart_code ' . $departCode . ' -> province #' . $row['id']);
            return ['level' => 'province', 'id' => (int) $row['id']];
        }

        // 3. ลองหาใน tb_areas (ระดับเขต)
        $sql = "SELECT id FROM users.tb_areas WHERE area_code = $1 LIMIT 1";
        $result = pg_query_params($this->db, $sql, [$departCode]);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            // error_log('✅ getUserLevelAndIdFromDepartCode: depart_code ' . $departCode . ' -> area #' . $row['id']);
            return ['level' => 'area', 'id' => (int) $row['id']];
        }

        // 4. ลองหาใน tb_head_offices (ระดับสำนักงานใหญ่)
        $sql = "SELECT id FROM users.tb_head_offices WHERE head_office_code = $1 LIMIT 1";
        $result = pg_query_params($this->db, $sql, [$departCode]);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            // error_log('✅ getUserLevelAndIdFromDepartCode: depart_code ' . $departCode . ' -> head_office #' . $row['id']);
            return ['level' => 'head_office', 'id' => (int) $row['id']];
        }

        // error_log('⚠️ getUserLevelAndIdFromDepartCode: ไม่พบ depart_code ' . $departCode . ' ใน branch/province/area/head_office');
        // ไม่พบใน table ใดเลย
        return null;
    }

    /**
     * เปิด method สำหรับภายนอกเพื่อหาระดับหน่วยงานจาก depart_code
     *
     * @param string|null $departCode
     * @return array|null ['level' => 'branch|province|area|head_office', 'id' => int]
     */
    public function resolveLevelFromDepartCode(?string $departCode): ?array
    {
        return $this->getUserLevelAndIdFromDepartCode($departCode);
    }

    /**
     * สร้าง approval scope ตามสิทธิ์ของ user
     * Scope คือขอบเขตที่ user สามารถอนุมัติได้
     *
     * @param array $context ข้อมูล user context
     * @return array|null scope ที่สร้างขึ้น หรือ null ถ้าไม่มีสิทธิ์อนุมัติ
     */
    public function buildApprovalScope(array $context)
    {
        $scope = [
            'user_level' => null,        // ระดับของ user (branch/province/area/head_office)
            'user_level_id' => null,     // ID ของ user ในระดับนั้น
            'branch_ids' => [],
            'province_ids' => [],
            'area_ids' => [],
            'head_office_ids' => [],
        ];

        // เช็คสำนักงานใหญ่ก่อน: ถ้ามี approve_head_office และ approve_investment
        // ไม่ต้องเช็ค depart_code ให้อนุมัติได้ทุก request
        if ($this->isTruthy($context['approve_head_office'] ?? null) &&
            $this->isTruthy($context['approve_investment'] ?? null)) {

            $scope['user_level'] = 'head_office';
            $scope['user_level_id'] = 1; // dummy ID สำหรับ head office
            $scope['user_depart_code'] = $context['depart_code'] ?? null;
            $scope['head_office_ids'][] = '*';
            $scope['branch_ids'][] = '*';
            $scope['province_ids'][] = '*';
            $scope['area_ids'][] = '*';

            return $scope;
        }

        // ใช้ depart_code หาระดับและ ID ของ user
        $departCode = $context['depart_code'] ?? null;
        $userLevelInfo = $this->getUserLevelAndIdFromDepartCode($departCode);

        if (!$userLevelInfo) {
            // ถ้าหาไม่เจอ ใช้ logic เก่า (fallback)
            return $this->buildApprovalScopeFallback($context);
        }

        $userLevel = $userLevelInfo['level'];  // branch/province/area/head_office
        $userLevelId = $userLevelInfo['id'];   // ID จากตารางนั้นๆ

        $scope['user_level'] = $userLevel;
        $scope['user_level_id'] = $userLevelId;
        $scope['user_depart_code'] = $departCode;  // เก็บ depart_code ของ user ไว้ใช้กรอง

        // ตรวจสอบสิทธิ์การอนุมัติตามระดับ
        if ($userLevel === 'branch' && $this->isTruthy($context['approve_branch'] ?? null)) {
            // User ระดับสาขา: อนุมัติได้เฉพาะ requests ที่สร้างจากสาขาตัวเอง (branch_id = user_level_id)
            $scope['branch_ids'][] = (string) $userLevelId;
        } elseif ($userLevel === 'province' && $this->isTruthy($context['approve_province'] ?? null)) {
            // User ระดับจังหวัด: อนุมัติได้ requests จากสาขาที่อยู่ภายใต้จังหวัดนี้
            // Query หา branch_ids ที่มี province_id = user_level_id
            $branchIds = $this->getBranchIdsByProvinceId($userLevelId);
            $scope['province_ids'][] = (string) $userLevelId;
            $scope['branch_ids'] = array_map('strval', $branchIds);
        } elseif ($userLevel === 'area' && $this->isTruthy($context['approve_area'] ?? null)) {
            // User ระดับเขต: อนุมัติได้ requests จากจังหวัดที่อยู่ภายใต้เขตนี้
            // Query หา province_ids ที่มี area_id = user_level_id
            $provinceIds = $this->getProvinceIdsByAreaId($userLevelId);
            $branchIds = $this->getBranchIdsByProvinceIds($provinceIds);
            $scope['area_ids'][] = (string) $userLevelId;
            $scope['province_ids'] = array_map('strval', $provinceIds);
            $scope['branch_ids'] = array_map('strval', $branchIds);
            // error_log('✅ User เขต (area): area_id=' . $userLevelId . ', province_ids=' . json_encode($provinceIds) . ', branch_ids=' . json_encode($branchIds));

        // COMMENT: Logic เดิมสำหรับ head_office ที่ต้องมี depart_code match
        // elseif ($userLevel === 'head_office' &&
        //           $this->isTruthy($context['approve_head_office'] ?? null) &&
        //           $this->isTruthy($context['approve_investment'] ?? null)) {
        //     // User ระดับสำนักงานใหญ่: อนุมัติได้ทุก requests
        //     $scope['head_office_ids'][] = (string) $userLevelId;
        //     // ใช้ wildcard สำหรับ head office (อนุมัติได้ทุก request)
        //     $scope['branch_ids'][] = '*';
        //     $scope['province_ids'][] = '*';
        //     $scope['area_ids'][] = '*';
        // }

        } else {
            // ไม่มีสิทธิ์อนุมัติ
            //  error_log('❌ buildApprovalScope: ไม่มีสิทธิ์อนุมัติ - userLevel=' . ($userLevel ?? 'null') .
            //           ', approve_branch=' . ($context['approve_branch'] ?? 'null') .
            //           ', approve_province=' . ($context['approve_province'] ?? 'null') .
            //           ', approve_area=' . ($context['approve_area'] ?? 'null') .
            //           ', approve_head_office=' . ($context['approve_head_office'] ?? 'null'));
            return null;
        }

        // ตรวจสอบว่ามี scope หรือไม่
        $hasScope = !empty($scope['branch_ids']) || !empty($scope['province_ids']) ||
                    !empty($scope['area_ids']) || !empty($scope['head_office_ids']);

        return $hasScope ? $scope : null;
    }

    /**
     * Fallback สำหรับกรณีที่ไม่มี depart_code หรือหาไม่เจอ
     * ใช้ logic เก่าที่อ่านจาก session
     */
    private function buildApprovalScopeFallback(array $context)
    {
        $scope = [
            'branch_ids' => [],
            'province_ids' => [],
            'area_ids' => [],
            'head_office_ids' => [],
            'depart_ids' => [],
            'user_level' => null,
            'user_level_id' => null,
        ];

        // กำหนด user_level ตามสิทธิ์การอนุมัติ (เรียงจากสูงสุดไปต่ำสุด)
        if ($this->isTruthy($context['approve_head_office'] ?? null) &&
            $this->isTruthy($context['approve_investment'] ?? null)) {
            $scope['user_level'] = 'head_office';
            $value = $this->normalizeScopeValue($context['head_office_id'] ?? null);
            $scope['user_level_id'] = $value;
            if ($value !== null && $value !== '') {
                $scope['head_office_ids'][] = $value;
            } else {
                $scope['head_office_ids'][] = '*';
            }
        } elseif ($this->isTruthy($context['approve_area'] ?? null)) {
            $scope['user_level'] = 'area';
            $value = $this->normalizeScopeValue($context['area_id'] ?? null);
            $scope['user_level_id'] = $value;
            if ($value !== null && $value !== '') {
                // Query หา province_ids และ branch_ids ที่อยู่ภายใต้ area นี้
                $areaIdStr = (string) $value;
                $provinceIds = $this->getProvinceIdsByAreaId($areaIdStr);
                $branchIds = $this->getBranchIdsByProvinceIds($provinceIds);

                // ถ้าไม่มี provinces ภายใต้ area นี้ = ข้อมูลไม่สมบูรณ์
                // ใช้ wildcard เพื่อให้อนุมัติได้ทุก request ที่ stage = area
                if (empty($provinceIds)) {
                    // error_log('⚠️ Fallback: User เขต area_id=' . $value . ' ไม่มี provinces ในฐานข้อมูล - ใช้ wildcard');
                    $scope['area_ids'][] = '*';
                } else {
                    $scope['area_ids'][] = $value;
                    $scope['province_ids'] = array_map('strval', $provinceIds);
                    $scope['branch_ids'] = array_map('strval', $branchIds);
                    // error_log('✅ Fallback: User เขต (area): area_id=' . $value . ', province_ids=' . json_encode($provinceIds) . ', branch_ids=' . json_encode($branchIds));
                }
            } else {
                $scope['area_ids'][] = '*';
            }
        } elseif ($this->isTruthy($context['approve_province'] ?? null)) {
            $scope['user_level'] = 'province';
            $value = $this->normalizeScopeValue($context['province_id'] ?? null);
            $scope['user_level_id'] = $value;
            if ($value !== null && $value !== '') {
                $scope['province_ids'][] = $value;
                // Query หา branch_ids ที่อยู่ภายใต้ province นี้
                $provinceId = (int) $value;
                $branchIds = $this->getBranchIdsByProvinceId($provinceId);
                $scope['branch_ids'] = array_map('strval', $branchIds);
            } else {
                $scope['province_ids'][] = '*';
            }
        } elseif ($this->isTruthy($context['approve_branch'] ?? null)) {
            $scope['user_level'] = 'branch';
            $value = $this->normalizeScopeValue($context['branch_id'] ?? null);
            $scope['user_level_id'] = $value;
            if ($value !== null && $value !== '') {
                $scope['branch_ids'][] = $value;
            } else {
                $scope['branch_ids'][] = '*';
            }
        }

        $hasScope = !empty($scope['user_level']) && (
            !empty($scope['branch_ids']) ||
            !empty($scope['province_ids']) ||
            !empty($scope['area_ids']) ||
            !empty($scope['head_office_ids'])
        );

        return $hasScope ? $scope : null;
    }

    /**
     * Query หา branch_ids ที่อยู่ภายใต้ province_id นี้
     */
    private function getBranchIdsByProvinceId($provinceId)
    {
        if (!$this->db || empty($provinceId)) {
            return [];
        }

        $sql = "SELECT id FROM users.tb_branchs WHERE province_id = $1";
        $result = pg_query_params($this->db, $sql, [$provinceId]);

        $ids = [];
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $ids[] = (int) $row['id'];
            }
        }

        return $ids;
    }

    /**
     * Query หา province_ids ที่อยู่ภายใต้ area_id นี้
     */
    private function getProvinceIdsByAreaId($areaId)
    {
        if (!$this->db || empty($areaId)) {
            return [];
        }

        $sql = "SELECT id FROM users.tb_provinces WHERE area_id = $1";
        $result = pg_query_params($this->db, $sql, [$areaId]);

        $ids = [];
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $ids[] = (int) $row['id'];
            }
        }

        return $ids;
    }

    /**
     * Query หา branch_ids ที่อยู่ภายใต้ province_ids เหล่านี้
     */
    private function getBranchIdsByProvinceIds(array $provinceIds)
    {
        if (!$this->db || empty($provinceIds)) {
            return [];
        }

        $placeholders = [];
        for ($i = 1; $i <= count($provinceIds); $i++) {
            $placeholders[] = '$' . $i;
        }

        $sql = "SELECT id FROM users.tb_branchs WHERE province_id IN (" . implode(',', $placeholders) . ")";
        $result = pg_query_params($this->db, $sql, $provinceIds);

        $ids = [];
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $ids[] = (int) $row['id'];
            }
        }

        return $ids;
    }

    /**
     * ประเมินว่า user สามารถอนุมัติคำขอนี้ได้หรือไม่
     * ตรวจสอบตาม:
     * 1. Status ของคำขอ (ต้องเป็น pending_xxx)
     * 2. Scope ของ user (ต้องมีสิทธิ์ในระดับนั้น)
     * 3. Department matching (ถ้าระดับสาขา)
     * 4. ห้ามอนุมัติของตนเอง (created_by != user_id)
     * 5. ระดับ Department ของผู้สร้างคำขอ (logic ใหม่)
     *
     * @param array|null $scope approval scope ของ user
     * @param array $request ข้อมูลคำขอ
     * @param array $context ข้อมูล user context
     * @return array ผลการประเมิน [can_approve, level, reason, stage, stage_label]
     */
    public function evaluateApprovalScope(?array $scope, array $request, array $context)
    {
        $status = strtolower($request['status'] ?? '');
        $stage = $this->getStageFromStatus($status);

        // 1. ตรวจสอบว่า status เป็น pending หรือไม่
        if (!$stage) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'not_pending',
                'stage' => null,
                'stage_label' => null,
            ];
        }

        // 2. ตรวจสอบว่า user มี scope หรือไม่
        if (!$scope) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'no_scope',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        // 3. ห้ามอนุมัติของตนเอง
        $createdBy = $this->normalizeScopeValue($request['created_by'] ?? null);
        $currentUserId = $this->normalizeScopeValue($context['user_id'] ?? null);

        if ($createdBy !== null && $currentUserId !== null && $createdBy === $currentUserId) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'self_approval_not_allowed',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        // 4. ตรวจสอบระดับ Department ของผู้สร้างคำขอ
        // Logic ใหม่: ถ้าผู้สร้างอยู่ในแผนกระดับใด ต้องให้คนในระดับเดียวกันอนุมัติก่อน
        $creatorDepartCode = $request['department_code'] ?? null;
        if ($creatorDepartCode !== null) {
            $creatorDepartLevel = $this->getDepartmentLevel($creatorDepartCode, $request);

            // ถ้าหาระดับของผู้สร้างได้ ต้องเช็คว่า stage ปัจจุบันตรงกับระดับของผู้สร้างหรือไม่
            if ($creatorDepartLevel !== null && $stage !== $creatorDepartLevel) {
                // ถ้า stage ปัจจุบันต่ำกว่าระดับของผู้สร้าง = ยังไม่ถึงคิวของผู้สร้าง (skip)
                // ถ้า stage ปัจจุบันสูงกว่าระดับของผู้สร้าง = ผ่านระดับของผู้สร้างไปแล้ว (อนุมัติได้ตามปกติ)
                $stageLevel = self::STAGE_LEVELS[$stage] ?? 0;
                $creatorLevel = self::STAGE_LEVELS[$creatorDepartLevel] ?? 0;

                if ($stageLevel < $creatorLevel) {
                    // ยังไม่ถึงระดับของผู้สร้าง ต้อง skip
                    return [
                        'can_approve' => false,
                        'level' => null,
                        'reason' => 'below_creator_level',
                        'stage' => $stage,
                        'stage_label' => $this->getStageLabel($stage),
                        'creator_level' => $creatorDepartLevel,
                        'creator_level_label' => $this->getStageLabel($creatorDepartLevel),
                    ];
                }
            }
        }

        // 5. ตรวจสอบสิทธิ์การอนุมัติสำหรับ stage ปัจจุบัน
        $userLevel = $scope['user_level'] ?? null;
        $hasStagePermission = $this->hasStagePermission($context, $stage);
        // error_log('🔍 evaluateApprovalScope: stage=' . $stage . ', userLevel=' . ($userLevel ?? 'null') . ', request_id=' . ($request['id'] ?? 'unknown') . ', hasStagePermission=' . ($hasStagePermission ? 'true' : 'false'));

        if (!$hasStagePermission) {
            // error_log('❌ evaluateApprovalScope: ไม่มีสิทธิ์สำหรับ stage นี้ - stage=' . $stage);
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'no_stage_permission',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
                'user_level' => $userLevel,
                'user_level_label' => $userLevel ? $this->getStageLabel($userLevel) : null,
            ];
        }

        if ($userLevel && $userLevel !== $stage) {
            // error_log('ℹ️ evaluateApprovalScope: stage ไม่ตรงกับ user level แต่อนุญาตเพราะมีสิทธิ์ - stage=' . $stage . ', userLevel=' . $userLevel);
        }

        // 6. ตรวจสอบว่า scope ตรงกับคำขอหรือไม่
        $stageKeyMap = [
            'branch' => ['branch_ids', 'branch_id'],
            'province' => ['province_ids', 'province_id'],
            'area' => ['area_ids', 'area_id'],
            'head_office' => ['head_office_ids', 'head_office_id'],
        ];

        if (!isset($stageKeyMap[$stage])) {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'unknown_stage',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        [$idsKey, $fieldKey] = $stageKeyMap[$stage];
        $ids = $this->normalizeScopeArray($scope[$idsKey] ?? []);

        if (empty($ids) && $stage !== 'area') {
            return [
                'can_approve' => false,
                'level' => null,
                'reason' => 'no_scope_stage',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        $value = $this->normalizeScopeValue($request[$fieldKey] ?? null);

        // 6. ตรวจสอบ wildcard หรือ match
        if (!empty($ids) && in_array('*', $ids, true)) {
            // ตรวจสอบ department สำหรับ branch level
            if ($stage === 'branch' && !$this->checkDepartmentMatch($scope, $request)) {
                return [
                    'can_approve' => false,
                    'level' => null,
                    'reason' => 'depart_mismatch',
                    'stage' => $stage,
                    'stage_label' => $this->getStageLabel($stage),
                ];
            }

            return [
                'can_approve' => true,
                'level' => $stage,
                'reason' => 'wildcard',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        if ($value !== null && $value !== '' && !empty($ids) && in_array($value, $ids, true)) {
            // ตรวจสอบ department สำหรับ branch level
            if ($stage === 'branch' && !$this->checkDepartmentMatch($scope, $request)) {
                // error_log("⚠️ Branch approval failed: depart_mismatch");
                return [
                    'can_approve' => false,
                    'level' => null,
                    'reason' => 'depart_mismatch',
                    'stage' => $stage,
                    'stage_label' => $this->getStageLabel($stage),
                ];
            }

            // error_log("✅ Approval match: stage={$stage}, value={$value}, ids=" . json_encode($ids));
            return [
                'can_approve' => true,
                'level' => $stage,
                'reason' => 'match',
                'stage' => $stage,
                'stage_label' => $this->getStageLabel($stage),
            ];
        }

        // Fallback logic สำหรับกรณีข้อมูล area_id ไม่ครบถ้วน
        if ($stage === 'area') {
            $provinceValue = $this->normalizeScopeValue($request['province_id'] ?? null);
            $provinceScope = $this->normalizeScopeArray($scope['province_ids'] ?? []);

            if ($provinceValue !== null && in_array($provinceValue, $provinceScope, true)) {
                // error_log("✅ Approval fallback (province): stage={$stage}, province={$provinceValue}");
                return [
                    'can_approve' => true,
                    'level' => $stage,
                    'reason' => 'province_fallback',
                    'stage' => $stage,
                    'stage_label' => $this->getStageLabel($stage),
                ];
            }

            $branchValue = $this->normalizeScopeValue($request['branch_id'] ?? null);
            $branchScope = $this->normalizeScopeArray($scope['branch_ids'] ?? []);

            if ($branchValue !== null && in_array($branchValue, $branchScope, true)) {
                // error_log("✅ Approval fallback (branch): stage={$stage}, branch={$branchValue}");
                return [
                    'can_approve' => true,
                    'level' => $stage,
                    'reason' => 'branch_fallback',
                    'stage' => $stage,
                    'stage_label' => $this->getStageLabel($stage),
                ];
            }
        }

        // error_log("❌ No match: stage={$stage}, value={$value}, ids=" . json_encode($ids));
        return [
            'can_approve' => false,
            'level' => $userLevel,  // แสดงระดับของ user แม้ว่าจะไม่ match
            'reason' => 'no_match',
            'stage' => $stage,
            'stage_label' => $this->getStageLabel($stage),
        ];
    }

    /**
     * หา stage ที่ user เห็นได้ตามสิทธิ์
     * Logic: user เห็นแค่ stage ของตนเอง + stage ที่สูงกว่า (ที่ผ่านมาแล้ว)
     *
     * @param array $context ข้อมูล user context
     * @return array รายการ status ที่เห็นได้ (เช่น ['pending_province', 'pending_area', ...])
     */
    public function getUserVisibleStages(array $context)
    {
        $visibleStages = [];

        $stageMap = [
            'branch' => ['permission' => 'approve_branch', 'level' => 1],
            'province' => ['permission' => 'approve_province', 'level' => 2],
            'area' => ['permission' => 'approve_area', 'level' => 3],
            'head_office' => ['permission' => 'approve_head_office', 'level' => 4],
        ];

        $userLevel = 0;

        foreach ($stageMap as $stage => $info) {
            $hasPermission = $this->hasStagePermission($context, $stage);

            if ($hasPermission) {
                $visibleStages[] = 'pending_' . $stage;
                if ($info['level'] > $userLevel) {
                    $userLevel = $info['level'];
                }
            }
        }

        // ถ้าไม่มีสิทธิ์อนุมัติเลย ไม่เห็นอะไร
        if ($userLevel === 0) {
            return [];
        }

        foreach ($stageMap as $stage => $info) {
            if ($info['level'] >= $userLevel) {
                $visibleStages[] = 'pending_' . $stage;
            }
        }

        // เพิ่ม approved และ rejected (เห็นได้ทุกคน)
        $visibleStages[] = 'approved';
        $visibleStages[] = 'rejected';

        return array_values(array_unique($visibleStages));
    }

    /**
     * ตรวจสอบว่า department_id ของคำขอตรงกับ scope หรือไม่
     * ใช้สำหรับ branch level approval
     *
     * @param array|null $scope approval scope
     * @param array $request ข้อมูลคำขอ
     * @return bool true ถ้าตรงกัน หรือไม่ต้องเช็ค
     */
    private function checkDepartmentMatch(?array $scope, array $request)
    {
        if (!$scope || !isset($scope['depart_ids'])) {
            return true;
        }

        $departIds = $scope['depart_ids'] ?? [];

        if (empty($departIds) || in_array('*', $departIds, true)) {
            return true;
        }

        $requestDepartId = $this->normalizeScopeValue($request['department_id'] ?? null);

        if ($requestDepartId === null || $requestDepartId === '') {
            return false;
        }

        return in_array($requestDepartId, $departIds, true);
    }

    /**
     * ตรวจสอบว่าสิทธิ์ของ user ครอบคลุม stage ที่กำลังพิจารณาหรือไม่
     *
     * @param array $context ข้อมูล user context
     * @param string $stage stage ปัจจุบัน
     * @return bool true ถ้ามีสิทธิ์อนุมัติ stage นี้
     */
    private function hasStagePermission(array $context, string $stage): bool
    {
        $permissionKey = 'approve_' . $stage;
        $hasPermission = $this->isTruthy($context[$permissionKey] ?? null);

        if ($stage === 'head_office') {
            $hasPermission = $hasPermission && $this->isTruthy($context['approve_investment'] ?? null);
        }

        return $hasPermission;
    }

    /**
     * แปลงค่าให้เป็น boolean
     * รองรับค่าแบบต่างๆ: 'active', 'yes', '1', true, 1, 'nonactive', 'no', '0', false, 0
     *
     * @param mixed $value ค่าที่ต้องการแปลง
     * @return bool true หรือ false
     */
    private function isTruthy($value)
    {
        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return false;
        }

        $truthy = ['y', 'yes', '1', 'true', 'active', 'enable', 'enabled', 'on'];
        $falsy = ['n', 'no', '0', 'false', 'inactive', 'nonactive', 'noactive', 'disabled', 'off'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        return (bool) $normalized;
    }

    /**
     * Normalize ค่าให้เป็น string หรือ null
     * ใช้สำหรับเปรียบเทียบ IDs
     *
     * @param mixed $value ค่าที่ต้องการ normalize
     * @return string|null ค่าที่ normalize แล้ว
     */
    private function normalizeScopeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        if (is_numeric($string)) {
            if (ctype_digit($string)) {
                return (string) ((int) $string);
            }

            return (string) ((float) $string);
        }

        return $string;
    }

    /**
     * แปลง array ของ scope ให้เป็น array ของ string (ตัดค่า null/ว่างออก)
     *
     * @param array $values รายการค่า scope
     * @return array รายการค่าที่ normalize แล้ว
     */
    private function normalizeScopeArray($values): array
    {
        if (!is_array($values)) {
            $values = (array) $values;
        }

        $normalized = [];
        foreach ($values as $value) {
            $normalizedValue = $this->normalizeScopeValue($value);
            if ($normalizedValue !== null && $normalizedValue !== '') {
                $normalized[] = $normalizedValue;
            }
        }

        return $normalized;
    }

    /**
     * ดึง stage จาก status (pending_branch -> branch)
     *
     * @param string $status status ของคำขอ
     * @return string|null stage name หรือ null
     */
    private function getStageFromStatus(?string $status)
    {
        if ($status === null || !is_string($status)) {
            return null;
        }

        $lower = strtolower($status);

        if (strpos($lower, 'pending_') !== 0) {
            return null;
        }

        $stage = substr($lower, strlen('pending_'));

        return $stage !== '' ? $stage : null;
    }

    /**
     * แปลง stage เป็นชื่อภาษาไทย
     *
     * @param string $stage stage name
     * @return string ชื่อภาษาไทย
     */
    private function getStageLabel(?string $stage)
    {
        $labels = [
            'branch' => 'สาขา',
            'province' => 'จังหวัด',
            'area' => 'เขต',
            'head_office' => 'สำนักงานใหญ่',
        ];

        return $labels[$stage] ?? $stage;
    }

    /**
     * สร้างข้อมูล debug สำหรับแสดงระดับของ user
     * ใช้สำหรับแสดงที่ UI
     *
     * @param array $context ข้อมูล user context
     * @return array ข้อมูล debug
     */
    public function getDebugInfo(array $context)
    {
        $debugInfo = [
            'user_id' => $context['user_id'] ?? null,
            'depart_id' => $context['depart_id'] ?? null,
            'depart_code' => $context['depart_code'] ?? null,
            'permissions' => [],
            'approval_level' => null,
            'approval_level_label' => null,
            'visible_stages' => [],
        ];

        // ตรวจสอบระดับจาก depart_code เสมอ เพื่อให้ user ทั่วไปเห็นข้อมูล
        $detectedLevelInfo = $this->resolveLevelFromDepartCode($context['depart_code'] ?? null);
        if ($detectedLevelInfo) {
            $debugInfo['depart_detected_level'] = $detectedLevelInfo['level'] ?? null;
            $debugInfo['depart_detected_level_id'] = $detectedLevelInfo['id'] ?? null;
            $debugInfo['depart_detected_level_label'] = $this->getStageLabel($detectedLevelInfo['level'] ?? null);
        }

        // ตรวจสอบสิทธิ์การอนุมัติแต่ละระดับ
        $permissions = [];
        if ($this->hasStagePermission($context, 'branch')) {
            $permissions[] = 'branch';
        }
        if ($this->hasStagePermission($context, 'province')) {
            $permissions[] = 'province';
        }
        if ($this->hasStagePermission($context, 'area')) {
            $permissions[] = 'area';
        }
        if ($this->hasStagePermission($context, 'head_office')) {
            $permissions[] = 'head_office';
        }
        if ($this->isTruthy($context['approve_investment'] ?? null)) {
            $permissions[] = 'investment';
        }

        $debugInfo['permissions'] = $permissions;

        // หาระดับการอนุมัติสูงสุด
        $levels = [
            'branch' => 1,
            'province' => 2,
            'area' => 3,
            'head_office' => 4,
        ];

        $maxLevel = 0;
        $maxStage = null;

        foreach ($permissions as $permission) {
            if ($permission === 'investment') {
                continue; // investment ไม่ใช่ stage
            }

            $level = $levels[$permission] ?? 0;
            if ($level > $maxLevel) {
                $maxLevel = $level;
                $maxStage = $permission;
            }
        }

        $debugInfo['approval_level'] = $maxStage;
        $debugInfo['approval_level_label'] = $maxStage ? $this->getStageLabel($maxStage) : null;

        // ถ้าไม่มี approval level แต่มีระดับที่ตรวจพบจาก depart_code ให้ใช้เป็นข้อมูลเสริม
        if (!$debugInfo['approval_level'] && !empty($debugInfo['depart_detected_level'])) {
            $debugInfo['approval_level_label'] = $debugInfo['depart_detected_level_label'] ?? $debugInfo['approval_level_label'];
        }

        // หา visible stages
        $visibleStages = $this->getUserVisibleStages($context);
        $debugInfo['visible_stages'] = $visibleStages;

        // เพิ่มข้อมูล scope
        $scope = $this->buildApprovalScope($context);
        $debugInfo['scope'] = $scope;

        return $debugInfo;
    }
}
