# Spec: ตรวจสอบงบประมาณก่อนสร้างใบขอเสนอซื้อ PR Online

## 1. วัตถุประสงค์

ก่อนผู้ใช้กด **ขออนุมัติ** ใบขอเสนอซื้อ PR Online ระบบต้องเรียก SAP เพื่อตรวจสอบ **งบประมาณคงเหลือ (`balance`)** แล้วแสดง Modal ให้ผู้ใช้ยืนยันหรือยกเลิก โดย **ห้ามบันทึก PR จนกว่าผู้ใช้จะยืนยันและงบเพียงพอ**

---

## 2. ความต้องการ (Functional Requirements)

### 2.1 Flow หลัก

```
ผู้ใช้กด "ขออนุมัติ"
    → Validate ฟอร์ม (เดิม)
    → เรียก API ตรวจงบ (Backend Proxy → SAP)
    → แสดง Modal ตามเงื่อนไข
    → ถ้ายืนยันและงบพอ → บันทึก PR (POST create)
    → ถ้างบไม่พอหรือยกเลิก → หยุด ไม่บันทึก
```

### 2.2 Modal ก่อนขออนุมัติ (งบเพียงพอ)

**เงื่อนไข:** `total_amount` ของใบนี้ **≤** `balance` จาก SAP

ระบบต้องแสดง Modal Popup ประกอบด้วย:

| รายการ | คำอธิบาย | สูตร/แหล่งข้อมูล |
|--------|----------|------------------|
| งบคงเหลือ | งบประมาณคงเหลือปัจจุบัน | `balance` จาก SAP |
| ยอดใบขอเสนอ | ยอดรวมของใบนี้ | `total_amount` จากฟอร์ม |
| งบหักลบ | งบคงเหลือหลังหักใบนี้ | `balance - total_amount` |

ปุ่ม:
- **ยืนยัน** → ดำเนินการสร้าง PR ต่อ
- **ยกเลิก** → ปิด Modal ไม่บันทึก

### 2.3 Modal เมื่องบไม่เพียงพอ

**เงื่อนไข:** `total_amount` ของใบนี้ **>** `balance` จาก SAP

ระบบต้องแสดง Modal Popup แจ้ง **สร้างไม่สำเร็จ** พร้อมเหตุผล เช่น:

- งบคงเหลือ: `{balance}`
- ยอดใบขอเสนอ: `{total_amount}`
- ส่วนต่างที่เกิน: `{total_amount - balance}`

ปุ่ม:
- **ตกลง** → ปิด Modal กลับไปแก้ไขฟอร์ม

### 2.4 Modal เมื่อ SAP ตอบ Error

**เงื่อนไข:** `status = 'E'` จาก SAP

แสดง Modal แจ้งข้อผิดพลาด พร้อม `message` จาก SAP ไม่บันทึก PR

---

## 3. SAP API

### 3.1 Endpoint

```
GET https://saps4hanadev.raot.co.th:8443/sap/bc/zremaining
```

Authentication: Basic Auth (เก็บ credentials ใน environment variables เท่านั้น)

### 3.2 Request Parameters

| Field Name | Required | Data Type | Length | Description | Example | หมายเหตุ |
|------------|----------|-----------|--------|-------------|---------|----------|
| `gjahr` | Y | char | 4 | Fiscal Year | `2026` | ดึงจากปีของ `start_date` |
| `rfikrs` | Y | char | 4 | Financial Management Area | `1000` | **ค่าคงที่ `1000`** |
| `rfundsctr` | N | char | 16 | Funds Center | | map จาก `funds_center_id` |
| `rcmmtitem` | N | char | 24 | Commitment Item | | map จาก `liability_id` |
| `rfund` | N | char | 10 | Fund | | map จาก `fund_id` |
| `rfuncarea` | N | char | 16 | Functional Area | | map จาก `scope_id` |
| `projid` | N | char | 6 | Project ID | | **ไม่ส่ง / ส่งค่าว่าง** |
| `str_date` | Y | char | 8 | Posting Date Start | `20260601` | จาก `start_date` แปลง `YYYYMMDD` |
| `end_date` | Y | char | 8 | Posting Date End | `20260630` | จาก `end_date` แปลง `YYYYMMDD` |

### 3.3 Response

| Field Name | Data Type | Description |
|------------|-----------|-------------|
| `status` | char(1) | `S` = Success, `E` = Error |
| `message` | char(100) | ข้อความ |
| `gjahr` | char(4) | Fiscal Year |
| `rfikrs` | char(4) | FM Area |
| `rfund` | char(10) | Fund |
| `fund_name` | char(40) | Fund Description |
| `rfuncarea` | char(16) | Functional Area |
| `fund_area_name` | char(25) | Functional Area Description |
| `rfundsctr` | char(16) | Funds Center |
| `fund_center_name` | char(40) | Funds Center Description |
| `rcmmtitem` | char(24) | Commitment Item |
| `rcmmt_text` | char(50) | Commitment Item Text |
| `budget` | CURR | งบประมาณ |
| `actual` | CURR | ยอดใช้จริง |
| **`balance`** | **CURR** | **งบคงเหลือ — ใช้เทียบกับ `total_amount`** |
| `pr_amt` | CURR | ยอด PR สะสม |
| `po_amt` | CURR | ยอด PO สะสม |
| `avail_bdg_amt` | CURR | งบที่ใช้ได้ (ไม่ใช้เทียบใน spec นี้) |

### 3.4 กฎการเทียบงบ

```
งบเพียงพอ  : total_amount <= balance
งบไม่พึงพอ  : total_amount > balance
งบหักลบ    : balance - total_amount   (แสดงใน Modal 3.1 เท่านั้น)
```

---

## 4. Mapping ฟิลด์ฟอร์ม → SAP

| ฟิลด์ฟอร์ม (`request-proposal-form`) | SAP Parameter | หมายเหตุ |
|--------------------------------------|---------------|----------|
| `start_date` | `str_date` | แปลง `DD-MM-YYYY` → `YYYYMMDD` |
| `end_date` | `end_date` | แปลง `DD-MM-YYYY` → `YYYYMMDD` |
| `start_date` (ปี) | `gjahr` | 4 หลัก เช่น `2026` |
| — | `rfikrs` | คงที่ `1000` |
| `cost_center_id` | `rfundsctr` | **หลัก** — resolve เป็น `cost_center_code` เช่น `10000` |
| `funds_center_id` | `rfundsctr` | **fallback** — UI โหลดจาก tb_cost_centers เช่นกัน (ใช้เมื่อไม่มี cost_center_id) |
| `fund_id` | `rfund` | resolve เป็น `funding_code` เช่น `G1491` |
| `scope_id` | `rfuncarea` | ส่ง code ไม่ใช่ id |
| — | `projid` | **ไม่ส่ง** |
| `total_amount` | — | ใช้เทียบกับ `balance` |

> **หมายเหตุ:** dropdown ในฟอร์มเก็บ `id` แต่ SAP ต้องการ `code` — Backend ต้อง resolve code จาก master data ก่อนเรียก SAP

---

## 5. สถาปัตยกรรมที่แนะนำ (ปลอดภัย + มีประสิทธิภาพ)

### 5.1 เรียก API ผ่าน Backend Proxy เท่านั้น

**ห้าม** เรียก SAP จาก Browser โดยตรง (credentials รั่ว, CORS, ไม่มี cache)

```
Browser (request-proposal-form.js)
    → POST /controllers/proposal/budget_check_controller.php?action=check
    → Service: BudgetCheckService
    → Repository / SAP Client: เรียก SAP zremaining
    → คืน JSON { status, balance, budget, actual, message, ... }
```

### 5.2 โครงสร้างไฟล์ที่แนะนำ

```
controllers/proposal/budget_check_controller.php   ← รับ request, validate, ส่ง response
services/proposal/budget_check_service.php         ← business logic, เทียบ balance
services/proposal/sap_budget_client.php            ← เรียก SAP HTTP (curl)
configs/sap_config.php หรือ .env                     ← SAP URL, User, Pass
pages/js/request-proposal-form.js                  ← เรียก budget check ก่อน saveProposal()
```

### 5.3 มาตรการความปลอดภัย

- เก็บ SAP credentials ใน **environment variables** ไม่ hardcode ใน source code
- Validate input ฝั่ง server ก่อนส่ง SAP
- Log request/response (ไม่ log password)
- Timeout SAP call (เช่น 10–15 วินาที)
- Auth middleware ตรวจ session ผู้ใช้ก่อนเรียก budget check

### 5.4 ประสิทธิภาพ

- Cache ผล SAP ชั่วคราว (เช่น 30–60 วินาที) ด้วย key จาก parameter ชุดเดียวกัน ป้องกัน double-call
- เรียก budget check **ครั้งเดียว** ต่อการกดขออนุมัติ
- Backend ใช้ HTTP keep-alive / connection reuse ไป SAP

---

## 6. การเปลี่ยนแปลง Frontend

### 6.1 จุดที่แก้ไข

ไฟล์: `pages/js/request-proposal-form.js`

ฟังก์ชัน `saveProposal()` ปรับเป็น:

```
saveProposal()
  1. validateRequiredFields()
  2. collectProposalData() → ได้ total_amount + ฟิลด์ budget
  3. checkBudget(data)     → เรียก backend proxy
  4. แสดง Modal ตามผล
  5. ถ้ายืนยัน → submitProposal(data)  (เดิม)
```

### 6.2 ตัวอย่าง Modal 3.1 (งบเพียงพอ)

```
┌─────────────────────────────────────────┐
│  ตรวจสอบงบประมาณ                        │
├─────────────────────────────────────────┤
│  งบคงเหลือ      : 500,000.00 บาท        │
│  ยอดใบขอเสนอ    : 120,000.00 บาท        │
│  งบหักลบ        : 380,000.00 บาท        │
├─────────────────────────────────────────┤
│         [ยกเลิก]    [ยืนยันขออนุมัติ]    │
└─────────────────────────────────────────┘
```

### 6.3 ตัวอย่าง Modal 3.2 (งบไม่พอ)

```
┌─────────────────────────────────────────┐
│  ⚠ ไม่สามารถขออนุมัติได้                │
├─────────────────────────────────────────┤
│  งบคงเหลือไม่เพียงพอ                    │
│  งบคงเหลือ      :  50,000.00 บาท        │
│  ยอดใบขอเสนอ    : 120,000.00 บาท        │
│  เกินงบ         :  70,000.00 บาท        │
├─────────────────────────────────────────┤
│                    [ตกลง]               │
└─────────────────────────────────────────┘
```

---

## 7. API ภายในระบบ (Backend Proxy)

### 7.1 Request

```
POST /controllers/proposal/budget_check_controller.php?action=check
Content-Type: application/json
```

```json
{
  "start_date": "01-06-2026",
  "end_date": "30-06-2026",
  "funds_center_id": "123",
  "liability_id": "456",
  "fund_id": "789",
  "scope_id": "101",
  "total_amount": 120000.00
}
```

### 7.2 Response — งบเพียงพอ

```json
{
  "status": "success",
  "sap_status": "S",
  "data": {
    "balance": 500000.00,
    "budget": 600000.00,
    "actual": 100000.00,
    "total_amount": 120000.00,
    "remaining_after_deduct": 380000.00,
    "is_sufficient": true
  }
}
```

### 7.3 Response — งบไม่พอ

```json
{
  "status": "success",
  "sap_status": "S",
  "data": {
    "balance": 50000.00,
    "total_amount": 120000.00,
    "remaining_after_deduct": -70000.00,
    "is_sufficient": false,
    "message": "งบคงเหลือไม่เพียงพอ"
  }
}
```

### 7.4 Response — SAP Error

```json
{
  "status": "error",
  "sap_status": "E",
  "message": "ข้อความ error จาก SAP"
}
```

---

## 8. สรุป Business Rules

| # | เงื่อนไข | ผลลัพธ์ |
|---|---------|---------|
| 1 | SAP `status = S` และ `total_amount <= balance` | Modal 3.1 → ยืนยันแล้วบันทึกได้ |
| 2 | SAP `status = S` และ `total_amount > balance` | Modal 3.2 → ห้ามบันทึก |
| 3 | SAP `status = E` | Modal error → ห้ามบันทึก |
| 4 | Backend/Network error | Modal error → ห้ามบันทึก |
| 5 | ผู้ใช้กดยกเลิกใน Modal 3.1 | ห้ามบันทึก |

---

## 9. Environment Variables ที่ต้องเตรียม

```env
SAP_BUDGET_URL=https://saps4hanadev.raot.co.th:8443/sap/bc/zremaining
SAP_BUDGET_USER=
SAP_BUDGET_PASS=
SAP_BUDGET_FMIKRS=1000
SAP_BUDGET_TIMEOUT=15
```

---

## 10. Out of Scope (ยังไม่ทำในรอบนี้)

- ส่ง `projid` (Project ID)
- เทียบงบกับ `avail_bdg_amt`
- บันทึกประวัติการตรวจงบลง database
- ตัดงบจริงใน SAP หลังอนุมัติ (reserve/commit)
