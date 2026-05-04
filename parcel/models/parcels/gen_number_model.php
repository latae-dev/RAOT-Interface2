<?php
class GenNumberModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createPlanNumber()
    {
        // ดึงปี พ.ศ. ปัจจุบันและแปลงเป็น 2 หลัก
        $currentYear = date('Y') + 543; // ปี พ.ศ. (ปัจจุบัน)
        $currentYearShort = substr($currentYear, -2); // 2 หลักสุดท้ายของปี พ.ศ.

        do {
            // ตรวจสอบว่ามีการใช้เลขที่เอกสารสำหรับปีนี้แล้วหรือไม่
            // เฉพาะรหัส xxPdddd (ไม่นับ xxPMdddd จากการรวมไฟล์) — LIKE xxP% จับทั้งสองแล้ว sort ได้ค่าผิด
            $query = "SELECT plan_number FROM parcels.tb_plans WHERE plan_number LIKE $1 AND plan_number NOT LIKE $2 ORDER BY plan_number DESC LIMIT 1";
            $result = pg_query_params($this->conn, $query, array($currentYearShort . 'P%', $currentYearShort . 'PM%'));

            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }

            $row = pg_fetch_assoc($result);
            $runningNumber = '0001';

            if ($row) {
                // ดึงหมายเลขการวิ่งจากเลขที่เอกสารล่าสุด
                $lastPlanNumber = $row['plan_number'];
                $runningNumber = substr($lastPlanNumber, -4); // ดึง 4 หลักสุดท้าย
                $runningNumber = str_pad((int)$runningNumber + 1, 4, '0', STR_PAD_LEFT); // เพิ่ม 1 และเติม 0 ถ้าจำนวนน้อยกว่า 4 หลัก
            }

            // สร้างเลขที่เอกสารตามรูปแบบ ปีพ.ศ. + "P" + รันนิ่ง
            $planNumber = $currentYearShort . 'P' . $runningNumber;

            // ตรวจสอบว่าเลขที่สร้างขึ้นมาใหม่ซ้ำกับที่มีอยู่หรือไม่
            $checkQuery = "SELECT COUNT(*) as count FROM parcels.tb_plans WHERE plan_number = $1";
            $checkResult = pg_query_params($this->conn, $checkQuery, array($planNumber));
            $checkRow = pg_fetch_assoc($checkResult);

        } while ($checkRow['count'] > 0); // ถ้ามีเลขซ้ำ ให้วนลูปสร้างเลขใหม่

        return $planNumber;
    }

    public function createDocNumber()
    {
        // ดึงปี พ.ศ. ปัจจุบันและแปลงเป็น 2 หลัก
        $currentYear = date('Y') + 543; // ปี พ.ศ. (ปัจจุบัน)
        $currentYearShort = substr($currentYear, -2); // 2 หลักสุดท้ายของปี พ.ศ.

        do {
            // ตรวจสอบว่ามีการใช้เลขที่เอกสารสำหรับปีนี้แล้วหรือไม่
            $query = "SELECT doc_number FROM parcels.tb_docs WHERE doc_number LIKE $1 ORDER BY doc_number DESC LIMIT 1";
            $result = pg_query_params($this->conn, $query, array($currentYearShort . 'D%'));

            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }

            $row = pg_fetch_assoc($result);
            $runningNumber = '0001';

            if ($row) {
                // ดึงหมายเลขการวิ่งจากเลขที่เอกสารล่าสุด
                $lastDocNumber = $row['doc_number'];
                $runningNumber = substr($lastDocNumber, -4); // ดึง 4 หลักสุดท้าย
                $runningNumber = str_pad((int)$runningNumber + 1, 4, '0', STR_PAD_LEFT); // เพิ่ม 1 และเติม 0 ถ้าจำนวนน้อยกว่า 4 หลัก
            }

            // สร้างเลขที่เอกสารตามรูปแบบ ปีพ.ศ. + "D" + รันนิ่ง
            $docNumber = $currentYearShort . 'D' . $runningNumber;

            // ตรวจสอบว่าเลขที่สร้างขึ้นมาใหม่ซ้ำกับที่มีอยู่หรือไม่
            $checkQuery = "SELECT COUNT(*) as count FROM parcels.tb_docs WHERE doc_number = $1";
            $checkResult = pg_query_params($this->conn, $checkQuery, array($docNumber));
            $checkRow = pg_fetch_assoc($checkResult);

        } while ($checkRow['count'] > 0); // ถ้ามีเลขซ้ำ ให้วนลูปสร้างเลขใหม่

        return $docNumber;
    }

    public function createPlanNumberMerc()
    {
        // ดึงปี พ.ศ. ปัจจุบันและแปลงเป็น 2 หลัก
        $currentYear = date('Y') + 543; // ปี พ.ศ. (ปัจจุบัน)
        $currentYearShort = substr($currentYear, -2); // 2 หลักสุดท้ายของปี พ.ศ.

        do {
            // ตรวจสอบว่ามีการใช้เลขที่เอกสารสำหรับปีนี้แล้วหรือไม่
            $query = "SELECT plan_number FROM parcels.tb_plans WHERE plan_number LIKE $1 ORDER BY plan_number DESC LIMIT 1";
            $result = pg_query_params($this->conn, $query, array($currentYearShort . 'PM%'));

            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }

            $row = pg_fetch_assoc($result);
            $runningNumber = '0001';

            if ($row) {
                // ดึงหมายเลขการวิ่งจากเลขที่เอกสารล่าสุด
                $lastPlanNumber = $row['plan_number'];
                $runningNumber = substr($lastPlanNumber, -4); // ดึง 4 หลักสุดท้าย
                $runningNumber = str_pad((int)$runningNumber + 1, 4, '0', STR_PAD_LEFT); // เพิ่ม 1 และเติม 0 ถ้าจำนวนน้อยกว่า 4 หลัก
            }

            // สร้างเลขที่เอกสารตามรูปแบบ ปีพ.ศ. + "PM" + รันนิ่ง
            $planNumber = $currentYearShort . 'PM' . $runningNumber;

            // ตรวจสอบว่าเลขที่สร้างขึ้นมาใหม่ซ้ำกับที่มีอยู่หรือไม่
            $checkQuery = "SELECT COUNT(*) as count FROM parcels.tb_plans WHERE plan_number = $1";
            $checkResult = pg_query_params($this->conn, $checkQuery, array($planNumber));
            $checkRow = pg_fetch_assoc($checkResult);

        } while ($checkRow['count'] > 0); // ถ้ามีเลขซ้ำ ให้วนลูปสร้างเลขใหม่

        return $planNumber;
    }
}
