export async function getAllCompensationExpenseType() {
    try {
        const userData = JSON.parse(sessionStorage.getItem("raot_user_session"));

        const response = await fetch(`../controllers/withdraws/wrd_compensation_expense_types_controller.php?action=getFirst&user_id=${userData.id}`, {
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

export async function getAllCompensationExpenseTypeParent(id = null) {
    try {
        const userData = JSON.parse(sessionStorage.getItem("raot_user_session"));

        const response = await fetch(`../controllers/withdraws/wrd_compensation_expense_types_controller.php?action=getParent&user_id=${userData.id}&id=${id}`, {
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