-- ตารางติดตามผู้ใช้ที่ออนไลน์ (ใช้โดย Dashboard)
-- แอปจะสร้างตารางอัตโนมัติหากยังไม่มี แต่แนะนำให้รัน migration นี้บน production

CREATE TABLE IF NOT EXISTS users.tb_user_presence (
    user_id INTEGER PRIMARY KEY,
    last_seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    access_count INTEGER NOT NULL DEFAULT 0
);

ALTER TABLE users.tb_user_presence
    ADD COLUMN IF NOT EXISTS access_count INTEGER NOT NULL DEFAULT 0;

CREATE INDEX IF NOT EXISTS idx_tb_user_presence_last_seen
    ON users.tb_user_presence (last_seen_at DESC);
