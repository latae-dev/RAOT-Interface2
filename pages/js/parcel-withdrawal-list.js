import { getQuarter } from "./quarter.js";

let quarterlist = {};
/** @type {Array<{id:any,name?:string,month_start:number,month_end:number,day_start:number,day_end:number}>} */
let quarter = [];

let userData;
let userObject;

function defaultQuarterRanges() {
    return [
        { id: 1, month_start: 10, month_end: 12, day_start: 1, day_end: 31 },
        { id: 2, month_start: 1, month_end: 3, day_start: 1, day_end: 31 },
        { id: 3, month_start: 4, month_end: 6, day_start: 1, day_end: 30 },
        { id: 4, month_start: 7, month_end: 9, day_start: 1, day_end: 30 },
    ];
}

let quarterReadyPromise = null;

async function ensureQuartersLoaded() {
    if (Array.isArray(quarter) && quarter.length >= 4 && quarter[0]?.month_start != null) {
        return;
    }
    if (!quarterReadyPromise) {
        quarterReadyPromise = (async () => {
            try {
                quarterlist = await getQuarter();
                const raw = quarterlist?.data;
                if (Array.isArray(raw) && raw.length >= 4) {
                    quarter = raw.map((qarter) => ({
                        id: qarter.quarter_code,
                        name: qarter.quarter_name,
                        month_start: Number(qarter.quarter_month_start),
                        month_end: Number(qarter.quarter_month_end),
                        day_start: Number(qarter.quarter_date_start),
                        day_end: Number(qarter.quarter_date_end),
                    }));
                } else {
                    quarter = defaultQuarterRanges();
                }
            } catch (e) {
                console.warn("getQuarter ไม่สำเร็จ ใช้ช่วงไตรมาสเริ่มต้น", e);
                quarter = defaultQuarterRanges();
            }
        })();
    }
    await quarterReadyPromise;
}

export async function beginFetch() {
    try {
        await ensureQuartersLoaded();
        userData = sessionStorage.getItem("raot_user_session");
        if (userData) {
            try {
                userObject = JSON.parse(userData);
            } catch (error) {
                console.error("Error parsing user session data:", error);
                window.location.href = "login.php";
                return;
            }
        }
        await fetchData(1);
    } catch (error) {
        console.error("❌ เกิดข้อผิดพลาด:", error);
        throw error;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const searchBtn = document.getElementById("searchBtn");
    if (searchBtn) {
        searchBtn.addEventListener("click", function () {
            fetchData(1);
        });
    }
    beginFetch();
});

async function getAll(limit = 10, offset = 0, plan_number = "", start_date = "", end_date = "", status_plan = "") {
    try {
        window.showLoading();
        const response = await fetch(
            `../controllers/parcels/plan_controller.php?action=plan_search_list&doc_type=master&
            limit=${limit}&
            offset=${offset}&
            plan_number=${plan_number}&
            start_date=${start_date}&
            end_date=${end_date}&
            status_plan=${status_plan}
            `,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                },
            }
        );

        if (!response.ok) {
            window.hideLoading();
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        window.hideLoading();
        return data;
    } catch (error) {
        window.hideLoading();
        console.error("❌ Error:", error);
        throw error;
    }
}

async function fetchData(page = 1) {
    try {
        await ensureQuartersLoaded();

        const limit = 10;
        const offset = (page - 1) * limit;

        let plan_number = document.querySelector("[name='plan_number']")?.value || "";
        let start_date = document.querySelector("[name='start_date']")?.value || "";
        let end_date = document.querySelector("[name='end_date']")?.value || "";
        let status_plan = document.querySelector("[name='status_plan']")?.value || "";

        const data = await getAll(limit, offset, plan_number, start_date, end_date, status_plan);

        const { total_pages: totalPages, current_page: currentPage } = data.pagination;
        const tableBody = document.getElementById("data-table");
        tableBody.innerHTML = "";

        if (!data.data.length) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">ไม่มีข้อมูล</td></tr>';
            return;
        }

        data.data.forEach((item, index) => {
            const createdMonth = new Date(item.created_at).getMonth() + 1;
            const reqDate = new Date().getDate();

            let currentQuarter = 0;
            const q = quarter;
            if (q[0] && createdMonth >= q[0].month_start && createdMonth <= q[0].month_end) {
                currentQuarter = 1;
            } else if (q[1] && createdMonth >= q[1].month_start && createdMonth <= q[1].month_end) {
                currentQuarter = 2;
            } else if (q[2] && createdMonth >= q[2].month_start && createdMonth <= q[2].month_end) {
                currentQuarter = 3;
            } else if (q[3] && createdMonth >= q[3].month_start && createdMonth <= q[3].month_end) {
                currentQuarter = 4;
            }

            const quarterButtons =
                item.status_head_office === "approve"
                    ? Array.from({ length: 4 }, (_, i) => {
                          const qi = i + 1;
                          const isCurrent = currentQuarter === qi;
                          return `<a href="javascript:void(0);" onclick="checkPeriod(${qi} , ${reqDate} , '${isCurrent ? `parcel-doc-form.php?id=${item.id}&quarter=${qi}` : "#"}');" 
                                    class="btn btn-icon btn-sm ${isCurrent ? "btn-info" : "btn-light"} rounded-pill">
                                    ${qi}
                                </a>`;
                      }).join("")
                    : '<span class="text-muted">สถานะต้องเป็นอนุมัติ</span>';

            const statusLabels = {
                pending: `<span class="badge rounded-pill bg-warning">รออนุมัติ</span>`,
                approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
                reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
                edit: `<span class="badge rounded-pill bg-info">ส่งกลับแก้ไข</span>`,
                delete: `<span class="badge rounded-pill bg-dark">ลบแล้ว</span>`,
            };

            let statusToUse = item.status_head_office;
            if (item.status_branch === "reject") {
                statusToUse = item.status_branch;
            } else if (item.status_province === "reject") {
                statusToUse = item.status_province;
            } else if (item.status_area === "reject") {
                statusToUse = item.status_area;
            }
            const status_thai = statusLabels[statusToUse] || "-";

            const row = document.createElement("tr");
            row.classList.add("crm-contact", "text-center");
            row.innerHTML = `
                        <td>${index + 1 + offset}</td>
                        <td>${convertToBuddhistEra(item.created_at)}</td>
                        <td>${item.plan_number}</td>
                        <td>${item.plan_form_name}</td>
                        <td>${status_thai}</td>
                        <td><div class="btn-list">${quarterButtons}</div></td>
                        <td>
                            <div class="hstack gap-2 fs-15">
                                <a href="parcel-withdrawal-list-detail.php?id=${item.id}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill"><i class="fe fe-eye"></i></a>
                                ${item.status_plan === "pending" && item.user_requ === (userObject?.user_code ?? null) ? `
                                    <a href="parcel-withdrawal-list-edit.php?id=${item.id}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                    <a href="#" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill delete-btn" data-id="${item.id}"><i class="ri-delete-bin-line"></i></a>
                                ` : ""}
                            </div>
                        </td>
                    `;
            tableBody.appendChild(row);
            const deleteBtn = row.querySelector(".delete-btn");
            if (deleteBtn) {
                deleteBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    confirmAndDelete(item.id);
                });
            }
        });

        document.getElementById("showing-count").innerHTML = `กำลังแสดง ${data.data.length} รายการ <i class="bi bi-arrow-right ms-2 fw-semibold"></i>`;
        updatePagination(totalPages, currentPage);
    } catch (error) {
        console.error("❌ Error:", error);
    }
}

async function confirmAndDelete(id) {
    const ok = await Swal.fire({
        title: "ยืนยันการลบ",
        text: "ต้องการลบแผนการเบิกนี้หรือไม่?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ลบ",
        cancelButtonText: "ยกเลิก",
    });
    if (!ok.isConfirmed) {
        return;
    }
    await updateStatus("delete", id);
}

async function updateStatus(status_plan, id) {
    const userCode = userObject?.user_code ?? "";
    if (!userCode) {
        Swal.fire({ title: "กรุณาเข้าสู่ระบบ", text: "ไม่พบข้อมูลผู้ใช้ใน session", icon: "warning" });
        return;
    }
    const data_sent = {
        status_plan: status_plan,
        user_requ: userCode,
        status_branch: status_plan,
        date_requ: "",
    };
    try {
        window.showLoading();
        const response = await fetch(`../controllers/parcels/plan_controller.php?action=update_status&id=${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            credentials: "same-origin",
            body: JSON.stringify(data_sent),
        });
        window.hideLoading();

        let result;
        try {
            result = await response.json();
        } catch (parseErr) {
            Swal.fire({
                title: "เกิดข้อผิดพลาด",
                text: "ไม่สามารถอ่านคำตอบจากเซิร์ฟเวอร์ได้",
                icon: "error",
            });
            return;
        }

        if (!response.ok || result.status !== "success") {
            const msg = result.message || result.status || `HTTP ${response.status}`;
            Swal.fire({ title: "ดำเนินการไม่สำเร็จ", text: String(msg), icon: "error" });
            return;
        }

        Swal.fire({
            title: "ดำเนินการสำเร็จ",
            text: status_plan === "delete" ? "ลบรายการแล้ว" : "บันทึกสำเร็จ",
            icon: "success",
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        window.hideLoading();
        console.error("เกิดข้อผิดพลาด:", error);
        Swal.fire({
            title: "เกิดข้อผิดพลาด",
            text: "รายการข้อผิดพลาด: " + error.message,
            icon: "warning",
        });
    }
}

function updatePagination(totalPages, currentPage) {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    if (currentPage > 1) {
        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="fetchData(${currentPage - 1})">ก่อนหน้า</a></li>`;
    } else {
        pagination.innerHTML += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">ก่อนหน้า</a></li>`;
    }

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        pagination.innerHTML += `
            <li class="page-item ${i === currentPage ? "active" : ""}">
                <a class="page-link" href="javascript:void(0);" onclick="fetchData(${i})">${i}</a>
            </li>
        `;
    }

    if (currentPage < totalPages) {
        pagination.innerHTML += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="fetchData(${currentPage + 1})">ถัดไป</a></li>`;
    } else {
        pagination.innerHTML += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">ถัดไป</a></li>`;
    }
}

function checkPeriod(quart, date, href) {
    const check_quarter = quarter.find((qua) => qua.id == quart);
    if (!check_quarter) {
        Swal.fire({ text: "ไม่พบข้อมูลไตรมาส", icon: "warning" });
        return;
    }
    if (href != "#") {
        if (date >= check_quarter.day_start && date <= check_quarter.day_end) {
            window.location = href;
        } else {
            Swal.fire({
                text: "ไม่สามารถเบิกได้เนื่องจากเกินเวลาที่กำหนด !!!",
                icon: "warning",
            });
        }
    }
}

function convertToBuddhistEra(dateString) {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, "0");
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const year = date.getFullYear() + 543;
    return `${day}-${month}-${year}`;
}

document.fetchData = fetchData;
document.checkPeriod = checkPeriod;
