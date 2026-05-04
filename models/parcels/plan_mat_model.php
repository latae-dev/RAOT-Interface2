<?php
class PlanMatModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ฟังก์ชันในการดึงข้อมูลทั้งหมดจาก tb_plan_mats
    public function readPlanMat()
    {
        $query = "SELECT * FROM parcels.tb_plan_mats";
        $result = pg_query($this->conn, $query);

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        $datas = [];
        while ($row = pg_fetch_assoc($result)) {
            $datas[] = $row;
        }

        return $datas;
    }

    // หาจาก mat_code
    public function readPlanMatCode($tb_doc_mats)
    {
        if (empty($tb_doc_mats)) {
            return [];
        }

        for ($i = 0; $i < count($tb_doc_mats); $i++) {
            $quarterCol = "quarter_" . $tb_doc_mats[$i]["quarter"];
            $matCode = $tb_doc_mats[$i]["mat_code"];

            // ตรวจสอบชื่อคอลัมน์
            if (!preg_match('/^quarter_[1-4]$/', $quarterCol)) {
                $tb_doc_mats[$i]["quarter_value"] = null;
                continue;
            }

            $query = "SELECT $quarterCol AS quarter_value FROM parcels.tb_plan_mats WHERE mat_code = $1";
            $result = pg_query_params($this->conn, $query, array($matCode));

            if (!$result) {
                echo "Query failed: " . pg_last_error($this->conn) . "\n";
                $tb_doc_mats[$i]["quarter_value"] = null;
                continue;
            }

            $row = pg_fetch_assoc($result);
            $tb_doc_mats[$i]["quantity_max"] = $row ? $row["quarter_value"] : null;
        }

        return $tb_doc_mats;
    }

    // ฟังก์ชันในการดึงข้อมูลของ plan_mat หนึ่งรายการ
    public function readPlanMatOne($id)
    {
        $query = "SELECT * FROM parcels.tb_plan_mats WHERE plan_id = $1";
        $result = pg_query_params($this->conn, $query, array($id));

        if (!$result) {
            echo "An error occurred: " . pg_last_error($this->conn) . "\n";
            return false;
        }

        // ดึงข้อมูลทั้งหมดจากผลลัพธ์ในรูปแบบของอาร์เรย์
        $data = [];
        while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $data[] = $row; // เก็บแถวในอาร์เรย์
        }

        return $data;
    }

    // ฟังก์ชันสำหรับการสร้าง plan_mat ใหม่
    public function createPlanMat($data, $return_id)
    {
        if (!isset($data['tb_plan_mats']) || !is_array($data['tb_plan_mats'])) {
            echo "Invalid data format.";
            return false;
        }

        $plan_id = $return_id ? $return_id : null;

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO parcels.tb_plan_mats (
            plan_id, mat_code, mat_name, count_unit_name, quarter_1, quarter_2, quarter_3, quarter_4, quantity_total
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";

        foreach ($data['tb_plan_mats'] as $mat) {
            // ดึงค่าของแต่ละรายการ
            $mat_code = isset($mat['mat_code']) ? $mat['mat_code'] : null;
            $mat_name = isset($mat['mat_name']) ? $mat['mat_name'] : null;
            $count_unit_name = isset($mat['count_unit_name']) ? $mat['count_unit_name'] : null;
            $quarter_1 = isset($mat['quarter_1']) ? $mat['quarter_1'] : 0;
            $quarter_2 = isset($mat['quarter_2']) ? $mat['quarter_2'] : 0;
            $quarter_3 = isset($mat['quarter_3']) ? $mat['quarter_3'] : 0;
            $quarter_4 = isset($mat['quarter_4']) ? $mat['quarter_4'] : 0;
            $quantity_total = isset($mat['quantity_total']) ? $mat['quantity_total'] : 0;

            // ส่งค่าไปยัง pg_query_params
            $result = pg_query_params($this->conn, $query, array(
                $plan_id,
                $mat_code,
                $mat_name,
                $count_unit_name,
                $quarter_1,
                $quarter_2,
                $quarter_3,
                $quarter_4,
                $quantity_total
            ));

            // ตรวจสอบข้อผิดพลาด
            if (!$result) {
                echo "An error occurred: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;  // ✅ ถ้าไม่มีข้อผิดพลาดคืนค่า true
    }

    public function createPlanMatMerc($data, $return_id)
    {
        $plan_id = $return_id ?? null;

        // ✅ แปลง string id เป็น array ของ integer
        $ids = array_map('intval', $data['tb_plan_mats']);

        // ✅ ใช้ PostgreSQL ANY ด้วย array
        $query_select = "SELECT * FROM parcels.tb_plan_mats WHERE plan_id = ANY($1)";
        $result_select = pg_query_params($this->conn, $query_select, array('{' . implode(',', $ids) . '}'));
        $data_select = pg_fetch_all($result_select);

        if (!$data_select) return false;

        // สร้าง array สำหรับเก็บข้อมูลที่รวมแล้ว
        $merged_data = [];

        foreach ($data_select as $item) {
            // ตรวจสอบว่า mat_code และ count_unit_name ซ้ำกันใน plan_id เดียวกัน
            $key = $item['mat_code'] . '-' . $item['count_unit_name'];

            // ถ้าไม่มีข้อมูลใน array ให้เพิ่มข้อมูลใหม่
            if (!isset($merged_data[$key])) {
                $merged_data[$key] = [
                    'plan_id' => $plan_id,
                    'mat_code' => $item['mat_code'],
                    'mat_name' => $item['mat_name'],
                    'count_unit_name' => $item['count_unit_name'],
                    'quarter_1' => $item['quarter_1'] ?? 0,
                    'quarter_2' => $item['quarter_2'] ?? 0,
                    'quarter_3' => $item['quarter_3'] ?? 0,
                    'quarter_4' => $item['quarter_4'] ?? 0,
                    'quantity_total' => $item['quantity_total'] ?? 0
                ];
            } else {
                // ถ้ามีข้อมูลที่ตรงกันให้รวมค่ากัน
                $merged_data[$key]['quarter_1'] += $item['quarter_1'] ?? 0;
                $merged_data[$key]['quarter_2'] += $item['quarter_2'] ?? 0;
                $merged_data[$key]['quarter_3'] += $item['quarter_3'] ?? 0;
                $merged_data[$key]['quarter_4'] += $item['quarter_4'] ?? 0;
                $merged_data[$key]['quantity_total'] += $item['quantity_total'] ?? 0;
            }
        }

        // ทำการ insert ข้อมูลที่รวมกันแล้ว
        foreach ($merged_data as $item) {
            $query_insert = "INSERT INTO parcels.tb_plan_mats (
            plan_id, mat_code, mat_name, count_unit_name, quarter_1, quarter_2, quarter_3, quarter_4, quantity_total
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";

            $params_insert = [
                $item['plan_id'],
                $item['mat_code'],
                $item['mat_name'],
                $item['count_unit_name'],
                $item['quarter_1'],
                $item['quarter_2'],
                $item['quarter_3'],
                $item['quarter_4'],
                $item['quantity_total']
            ];

            $result_insert = pg_query_params($this->conn, $query_insert, $params_insert);
            if (!$result_insert) {
                echo "❌ Insert error: " . pg_last_error($this->conn) . "\n";
                return false;
            }
        }

        return true;
    }

    public function updatePlanMat($data, $id)
    {
        // ตรวจสอบว่า plan_id ถูกต้องหรือไม่
        if (!is_numeric($id) || $id <= 0) {
            echo "Invalid plan_id.";
            return false;
        }

        // เริ่ม transaction
        pg_query($this->conn, "BEGIN");

        try {
            // ดึงข้อมูลเดิมก่อนลบ
            $backupQuery = "SELECT * FROM parcels.tb_plan_mats WHERE plan_id = $1";
            $backupResult = pg_query_params($this->conn, $backupQuery, array($id));

            if (!$backupResult) {
                throw new Exception("Failed to fetch existing data: " . pg_last_error($this->conn));
            }

            // เก็บข้อมูลเดิมไว้ในอาร์เรย์
            $oldData = pg_fetch_all($backupResult) ?: [];

            // ลบข้อมูลเดิม
            $deleteQuery = "DELETE FROM parcels.tb_plan_mats WHERE plan_id = $1";
            $deleteResult = pg_query_params($this->conn, $deleteQuery, array($id));

            if (!$deleteResult) {
                throw new Exception("Failed to delete old data: " . pg_last_error($this->conn));
            }

            // ตรวจสอบว่ามีข้อมูลใหม่ให้เพิ่มหรือไม่
            if (!isset($data['tb_plan_mats']) || !is_array($data['tb_plan_mats']) || empty($data['tb_plan_mats'])) {
                pg_query($this->conn, "COMMIT");
                return true; // ✅ ลบสำเร็จ แต่ไม่มีข้อมูลใหม่ให้เพิ่ม
            }

            // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูลใหม่
            $insertQuery = "INSERT INTO parcels.tb_plan_mats (
            plan_id, mat_code, mat_name, count_unit_name, quarter_1, quarter_2, quarter_3, quarter_4, quantity_total
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";

            foreach ($data['tb_plan_mats'] as $mat) {
                $insertResult = pg_query_params($this->conn, $insertQuery, array(
                    $id,
                    $mat['mat_code'] ?? null,
                    $mat['mat_name'] ?? null,
                    $mat['count_unit_name'] ?? null,
                    $mat['quarter_1'] ?? 0,
                    $mat['quarter_2'] ?? 0,
                    $mat['quarter_3'] ?? 0,
                    $mat['quarter_4'] ?? 0,
                    $mat['quantity_total'] ?? 0
                ));

                if (!$insertResult) {
                    throw new Exception("Failed to insert new data: " . pg_last_error($this->conn));
                }
            }

            // ทุกอย่างผ่านไปด้วยดี ให้ commit
            pg_query($this->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            // เกิดข้อผิดพลาด ให้ rollback
            pg_query($this->conn, "ROLLBACK");
            echo $e->getMessage();
            return $oldData; // คืนค่าข้อมูลเดิมเพื่อกู้คืน
        }
    }

    public function cutQuantityPlanMat($data)
    {
        try {
            // เริ่ม transaction
            pg_query($this->conn, "BEGIN");

            // เก็บข้อมูลเดิมทั้งหมด
            $plan_id = $data['tb_docs']['plan_id'];
            $oldData = [];
            foreach ($data['tb_doc_mats'] as $item) {
                $quarter = $item['quarter'];
                $query = "SELECT quarter_{$quarter}, quarter_{$quarter}_use 
                         FROM parcels.tb_plan_mats 
                         WHERE plan_id = $1 AND mat_code = $2";
                $result = pg_query_params($this->conn, $query, array($plan_id, $item['mat_code']));

                if (!$result) {
                    throw new Exception("เกิดข้อผิดพลาดในการดึงข้อมูล: " . pg_last_error($this->conn));
                }

                $row = pg_fetch_assoc($result);
                if (!$row) {
                    throw new Exception("ไม่พบข้อมูลที่ต้องการอัพเดท");
                }

                $oldData[] = [
                    'plan_id' => $plan_id,
                    'mat_code' => $item['mat_code'],
                    'quarter' => $item['quarter'],
                    'current_quantity' => $row["quarter_{$item['quarter']}"],
                    'current_use' => $row["quarter_{$item['quarter']}_use"] ?? 0
                ];
            }

            // อัพเดทข้อมูลทั้งหมด
            foreach ($data['tb_doc_mats'] as $item) {
                $current_quantity = $oldData[array_search($item['mat_code'], array_column($oldData, 'mat_code'))]['current_quantity'];
                $current_use = $oldData[array_search($item['mat_code'], array_column($oldData, 'mat_code'))]['current_use'];

                // คำนวณค่าใหม่
                $new_use = $current_use + $item['quantity'];

                // ตรวจสอบไม่ให้ติดลบ
                if ($new_use < 0) {
                    throw new Exception("จำนวนที่ใช้ไม่สามารถติดลบได้");
                }

                // ตรวจสอบไม่ให้เกินจำนวนที่มี
                if ($new_use > $current_quantity) {
                    throw new Exception("จำนวนที่ใช้เกินจำนวนที่มีอยู่");
                }

                // อัพเดทค่าใหม่
                $updateQuery = "UPDATE parcels.tb_plan_mats 
                              SET quarter_{$item['quarter']}_use = $1 
                              WHERE plan_id = $2 AND mat_code = $3";
                $updateResult = pg_query_params($this->conn, $updateQuery, array($new_use, $plan_id, $item['mat_code']));

                if (!$updateResult) {
                    throw new Exception("เกิดข้อผิดพลาดในการอัพเดทข้อมูล: " . pg_last_error($this->conn));
                }
            }

            // ถ้าทุกอย่างผ่าน ให้ commit
            pg_query($this->conn, "COMMIT");
            return true;
        } catch (Exception $e) {
            // ถ้าเกิดข้อผิดพลาด ให้ rollback
            pg_query($this->conn, "ROLLBACK");
            return $e->getMessage();
        }
    }
}
