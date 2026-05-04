export async function getQuarter() {
    try {
        const userData = sessionStorage.getItem("raot_user_session");
        
        // เพิ่ม key ให้กับแต่ละ result
        const resultsWithKeys = await getAllParams();

        return resultsWithKeys; // ส่งกลับออบเจ็กต์ที่มี key และ value
    } catch (error) {
        console.error("❌ เกิดข้อผิดพลาด:", error);
        throw error; // ส่ง error กลับไปให้ handle ต่อ
    }
}

async function getAllParams() {
    try {
        const response = await fetch("../controllers/parameter/quarterlist_controller.php?action=all", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer`
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json(); // เปลี่ยนจาก `.done()` เป็น `await response.json()`
        return data; // ✅ ส่งค่ากลับไปให้ใช้งาน
    } catch (error) {
        console.error("❌ Error:", error);
        throw error; // ❌ ส่ง error กลับไปให้ handle ต่อ
    }
}