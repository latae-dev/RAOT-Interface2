async function login() {
    window.showLoading();
    const userCode = document.querySelector('[name="user_code"]').value.trim();
    const userPass = document.querySelector('[name="user_pass"]').value.trim();

    // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
    if (!userCode || !userPass) {
        alert("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
        return;
    }

    const dataToSend = { user_code: userCode, user_pass: userPass };
    
    try {
        const response = await fetch('../controllers/user/authen_controller.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSend)
        });

        // ตรวจสอบสถานะ HTTP Response
        if (!response.ok) {
            window.hideLoading();
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        window.hideLoading();
        const result = await response.json();

        if (result.data.id !== null) {
           
            sessionStorage.setItem("raot_user_session", JSON.stringify(result.data));
            window.location.href = "index.php"; 
        } else {
            alert(result.message || "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
        }
    } catch (error) {
        window.hideLoading();
        console.error('เกิดข้อผิดพลาด:', error);
        alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const btnLogin = document.getElementById("btnLogin");
    if (btnLogin) {
        btnLogin.addEventListener("click", async function (event) {
            event.preventDefault(); // ป้องกันการโหลดซ้ำหน้า
            await login();
        });
    } else {
        console.error("ไม่พบปุ่มที่มี id='btnLogin'");
    }
});

document.addEventListener("DOMContentLoaded", function () {
    window.showLoading = function () {
        document.getElementById("loading").style.display = "flex";
    };

    window.hideLoading = function () {
        document.getElementById("loading").style.display = "none";
    };
});