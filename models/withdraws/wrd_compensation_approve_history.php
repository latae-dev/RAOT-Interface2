<?php
// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
class WrdCompensationApproveHistoryModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readApproveOne($id)
    {
        $query = "SELECT * FROM withdraws.tb_wrd_compensation_approve_history approve
        JOIN users.tb_users as users ON users.user_code = approve.user_code
        WHERE compensation_id = $1 
        order by approve.approve_date asc";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $data = pg_fetch_all($result); // ✅ ใช้เพื่อให้ได้ array หลายรายการ
        return $data ?: []; // ถ้าไม่มีข้อมูล ให้คืน array ว่าง
    }

    // ฟังก์ชันสำหรับการสร้าง Logs History
    public function createApproveLogs($data, $id, $position_code)
    {
        $map = [
            'APHO' => [
                'approve' => 'finance_approve',
                'reject'  => 'finance_reject'
            ],
            'APHOP' => [
                'approve' => 'audit_approve',
                'reject'  => 'audit_reject'
            ],
        ];

        if (!isset($map[$position_code])) {
            return false; // ถ้าไม่ใช่ 2 ตำแหน่งนี้ return false เลย
        }

        $status = 'waiting';
        if (!empty($data['main_data']['approve'])) {
            $status = $map[$position_code]['approve'];
        } elseif (!empty($data['main_data']['reject'])) {
            $status = $map[$position_code]['reject'];
        }

        $user_code   = $data['main_data']['user_code'] ?? null;
        $remark      = $data['main_data']['remark'] ?? null;
        $approve_date = date('Y-m-d H:i:s');
        $now = date('Y-m-d H:i:s');

        $query = "INSERT INTO withdraws.tb_wrd_compensation_approve_history (
            compensation_id,
            user_code,
            approve_date,
            approve_status,
            created_at,
            updated_at,
            remark
        ) VALUES ($1, $2, now(), $3, now(), now(), $4)
        RETURNING id";

        $result = pg_query_params($this->conn, $query, [
            $id,
            $user_code,
            $status,
            $remark
        ]);

        if (!$result) {
            error_log("createApproveLogs failed: " . pg_last_error($this->conn));
            return false;
        }

        return pg_fetch_result($result, 0, 'id');
    }


    // แปลงวันที่จากรูปแบบ 'd-m-Y H:i' เป็น 'Y-m-d H:i:s'
    public function format_date_to_db($date)
    {
        $parts = explode(' ', $date);
        if (count($parts) !== 2) return null;
        list($dmy, $hm) = $parts;
        $dmy_parts = explode('-', $dmy);
        if (count($dmy_parts) !== 3) return null;
        $day = (int)$dmy_parts[0];
        $month = (int)$dmy_parts[1];
        $year = (int)$dmy_parts[2];
        if ($year > 2400) {
            $year -= 543;
        }
        $dateTime = DateTime::createFromFormat('Y-m-d H:i', sprintf('%04d-%02d-%02d %s', $year, $month, $day, $hm));
        if (!$dateTime) return null;

        return $dateTime->format('Y-m-d H:i:s');
    }
}
