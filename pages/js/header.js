let user = {};
let userObject = {}
async function getUserSession() {
    try {
        const userData = sessionStorage.getItem("raot_user_session");

        if (userData === null) {
            window.location.href = "login.php";
        }
        
        if (userData) {
            try {
                userObject = JSON.parse(userData); // แปลง string -> Object
            } catch (error) {
                console.error("Error parsing user session data:", error);
                window.location.href = "login.php"; 
            }
        }

        const headerUserElement = document.getElementById("header_user");
        const positionNameElement = document.getElementById("header_position");
        if (headerUserElement) {
            headerUserElement.textContent = `${userObject.user_fname} ${userObject.user_lname}`;
            positionNameElement.textContent = `${userObject.position_name}`;
        } else {
            console.warn("Element #header_user not found!");
        }
    } catch (error) {
        console.error("Error parsing user session:", error);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    window.showLoading = function () {
        document.getElementById("loading").style.display = "flex";
    };

    window.hideLoading = function () {
        document.getElementById("loading").style.display = "none";
    };
});

getUserSession();