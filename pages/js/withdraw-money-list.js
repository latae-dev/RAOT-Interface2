import { getAllParams } from "./withdraw_parameter.js";

(async () => {
  try {
    const params_all = await getAllParams();
    const tableBody = document.querySelector("tbody");
    let currentPage = 1;
    let currentLimit = 10;

    // ฟังก์ชันแสดงผลข้อมูลในตาราง
    function displayData(data, pageOffset = 0) {
      tableBody.innerHTML = ""; // ล้างข้อมูลเก่า

      if (!data || data.length === 0) {
        const row = document.createElement("tr");
        row.innerHTML = '<td colspan="8" class="text-center">ไม่พบข้อมูล</td>';
        tableBody.appendChild(row);
        return;
      }

      data.forEach((item, index) => {
        const statusLabels = {
          waiting: `<span class="badge rounded-pill bg-warning">รออนุมัติ</span>`,
          approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
          reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
          edit: `<span class="badge rounded-pill bg-info">ส่งกลับแก้ไข</span>`,
          delete: `<span class="badge rounded-pill bg-dark">ลบแล้ว</span>`,
        };

        const row = document.createElement("tr");
        row.classList.add("text-center");

        // ✅ ตรวจสอบสิทธิ์: เป็นผู้ร้องขอเองและยังไม่อนุมัติ (เช็คเงือนไขเพิ่มสำรหับ reject และ waiting ให้ปุ่มสามารถเปิดให้ใช้งานได้)
        // ✅ ตรวจสอบสิทธิ์: เป็นผู้ร้องขอเองและยังไม่อนุมัติ (เช็คเงือนไขเพิ่มสำรหับ reject และ waiting ให้ปุ่มสามารถเปิดให้ใช้งานได้)
        var classbtnExpenses = "btn-secondary-custom disabled";
        var classbtnExpensesOutside = "btn-secondary-custom disabled";
        var classMoneyOrder = "btn-secondary-custom disabled";
        var linkbtnExpenses = "javascript:void(0);";
        var linkbtnExpensesOutside = "javascript:void(0);";
        var linkbtnMoneyOrder = "javascript:void(0);";
        if (item.st_hf != "waiting" && item.st_hf != "reject") {
          if (item.has_expenses != 1) {
            classbtnExpenses = "btn-success-custom travel-expenses-btn";
            linkbtnExpenses = "javascript:void(0);";
          } else {
            classbtnExpenses = "btn-success-c2-custom";
            linkbtnExpenses = `travel-expenses-money-view.php?id=${item.expenses_id}&is_approve=0&is_action=view`;
          }
          if (item.has_expenses_outside != 1) {
            classbtnExpensesOutside =
              "btn-primary-custom travel-expenses-outside-btn";
            linkbtnExpensesOutside = "javascript:void(0);";
          } else {
            classbtnExpensesOutside = "btn-primary-c2-custom";
            linkbtnExpensesOutside = `travel-expenses-money-outside-view.php?id=${item.out_side_expenses_id}&is_approve=0&is_action=view`;
          }
          if (item.has_compensations != 1) {
            classMoneyOrder = "btn-info-custom";
            linkbtnMoneyOrder = `money-order-plan.php?id=${item.id}`;
          } else {
            classMoneyOrder = "btn-info-c2-custom";
            linkbtnMoneyOrder = `money-order-view.php?id=${item.compensations_id}&is_approve=0&is_action=view`;
          }
        }
        const canEditOrDelete =
          item.req_user_code === params_all.current_user.user_code &&
          (item.st_hf === "waiting" || item.st_hf === "reject");

        row.innerHTML =
          `
                    <td>${index + 1 + pageOffset}</td>
                    <td>${convertToBuddhistEra(item.req_user_date)}</td>
                    <td>${item.plan_number}</td>
                    <td>${item.plan_form_name}</td>` +
          // <td>${item.province_name}</td>
          `<td>${statusLabels[item.st_hf] || "-"}</td>
                    <td>
                        <div class="btn-list">` +
          // <a href="travel-expenses-money.php" class="btn btn-success-light btn-sm ${disabledAttr}">ส่งเงินยืมทดรองจ่าย</a>
          // <a href="travel-expenses-money.php" class="btn btn-success-light btn-sm ${disabledAttr}">ส่งเงินยืมทดรองจ่าย (บุคคลภายนอก)</a>
          `<a href="${linkbtnExpenses}" class="${classbtnExpenses}">ส่งเงินยืมทดรองจ่าย</a>
                        <a href="${linkbtnExpensesOutside}" class="${classbtnExpensesOutside}">ส่งเงินยืมทดรองจ่าย (บุคคลภายนอก)</a>
                        <a href="${linkbtnMoneyOrder}" class="${classMoneyOrder}">ส่งใบสั่งจ่าย</a>
                        </div>
                    </td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="withdraw-money-plan-view.php?id=${item.id
          }" class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="./export/withdraw-money-plan-export.php?id=${item.id
          }" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill">
                                <i class="bx bx-export"></i>
                            </a>
                            ${canEditOrDelete
            ? `
                                <a href="withdraw-money-plan-edit.php?id=${item.id}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill delete-btn" data-id="${item.id}">
                                    <i class="ri-delete-bin-line"></i>
                                </a>
                            `
            : ""
          }
                        </div>
                    </td>
                `;

        tableBody.appendChild(row);

        // ✅ เงื่อนไขผูก event ลบ ถ้ามีปุ่ม delete
        const deleteBtn = row.querySelector(".delete-btn");
        if (deleteBtn) {
          deleteBtn.addEventListener("click", (e) => {
            e.preventDefault();
            Swal.fire({
              title: `รหัสคำขอเบิกเงินทดรองจ่าย : ${item.plan_number}`,
              text: "ท่านต้องการลบรายการนี้หรือไม่?",
              icon: "error",
              showCancelButton: true,
              confirmButtonText: "ยืนยัน",
              cancelButtonText: `ยกเลิก`,
            }).then((result) => {
              if (result.isConfirmed) {
                deleteStatus(item.id);
              }
            });
          });
        }

        const insideBtn = row.querySelector(".travel-expenses-btn");
        if (insideBtn && !insideBtn.classList.contains("disabled")) {
          insideBtn.addEventListener("click", function (e) {
            e.preventDefault();
            Swal.fire({
              title: `<h5>รหัสคำขอเบิกเงินทดรองจ่าย : ${item.plan_number}</h5>`,
              html: `<h6>สร้างใบเบิกค่าใช้จ่ายฯ</h6>
                            <div class="row">
                                <div class="col-4">
                                    <a href="travel-expenses-money-plan.php?id=${item.id}&type=1"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี คนเดียว
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="travel-expenses-money-plan.php?id=${item.id}&type=2"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางเหมือนกัน
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="travel-expenses-money-plan-multi.php?id=${item.id}&type=3"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางแตกต่างกัน
                                    </a>
                                </div>
                            </div> 
                                `,
              width: 800,
              showCancelButton: false,
              showConfirmButton: false,
            }).then((result) => { });
          });
        }

        const outsideBtn = row.querySelector(".travel-expenses-outside-btn");
        if (outsideBtn && !outsideBtn.classList.contains("disabled")) {
          outsideBtn.addEventListener("click", function (e) {
            e.preventDefault();
            Swal.fire({
              title: `<h5>รหัสคำขอเบิกเงินทดรองจ่าย : ${item.plan_number}</h5>`,
              html: `<h6>สร้างใบเบิกค่าใช้จ่ายฯ (บุคคลภายนอก)</h6>
                            <div class="row">
                                <div class="col-4">
                                    <a href="travel-expenses-money-outside-plan.php?id=${item.id}&type=1"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี คนเดียว
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="travel-expenses-money-outside-plan.php?id=${item.id}&type=2"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางเหมือนกัน
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="travel-expenses-money-outside-plan-multi.php?id=${item.id}&type=3"
                                    class="btn btn-primary btn-equal"
                                    style="min-width: 220px; height: 60px; display: flex; align-items: center; justify-content: center; white-space: normal; text-align: center;">
                                    กรณี เดินทางเป็นหมู่คณะ<br>และวิธีการเดินทางแตกต่างกัน
                                    </a>
                                </div>
                            </div> 
                                `,
              width: 800,
              showCancelButton: false,
              showConfirmButton: false,
            }).then((result) => { });
          });
        }
      });
    }

    async function deleteStatus(id) {
      try {
        window.showLoading();
        const response = await fetch(
          `../controllers/withdraws/wrd_controller.php?action=delete_status&id=${id}`,
          {
            method: "DELETE",
          }
        );

        const result = await response.json();
        window.hideLoading();

        Swal.fire({
          title: "ดำเนินการสำเร็จ",
          text: "ดำเนินการลบเอกสารสำเร็จ",
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

    // ฟังก์ชันอัพเดท pagination
    function updatePagination(pagination) {
      const paginationContainer = document.querySelector(".pagination");
      const totalPages = pagination.total_pages;
      const currentPage = pagination.current_page;

      let paginationHTML = `
                <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1
        }">
                        ก่อนหน้า
                    </a>
                </li>
            `;

      for (let i = 1; i <= totalPages; i++) {
        paginationHTML += `
                    <li class="page-item ${i === currentPage ? "active" : ""}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>
                `;
      }

      paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? "disabled" : ""
        }">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1
        }">
                        ถัดไป
                    </a>
                </li>
            `;

      paginationContainer.innerHTML = paginationHTML;

      // อัพเดทข้อความแสดงจำนวนรายการ
      document.querySelector(
        ".card-footer .d-flex div:first-child"
      ).textContent = `กำลังแสดง ${pagination.total_records} รายการ`;
    }

    // ฟังก์ชันค้นหา
    async function getSearch(
      limit = 10,
      offset = 0,
      doc_number = "",
      start_date = "",
      end_date = "",
      status = "",
      key = ""
    ) {
      try {
        window.showLoading();
        const response = await fetch(
          `../controllers/withdraws/wrd_controller.php?
                    action=plan_search&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    plan_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    req_user_code=${params_all.current_user.user_code}
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
        window.hideLoading();
        const data = await response.json();
        return data;
      } catch (error) {
        window.hideLoading();
        console.error("❌ Error:", error);
        throw error;
      }
    }

    // ฟังก์ชันค้นหาและแสดงผล
    async function searchAndDisplay(page = 1) {
      try {
        const docNumber = document.getElementById("input-label11").value;
        const startDate = document.querySelector(
          'input[placeholder="กำหนด วันที่เริ่มต้น"]'
        ).value;
        const endDate = document.querySelector(
          'input[placeholder="กำหนด วันที่สิ้นสุด"]'
        ).value;
        const status = document.getElementById("choices-single-groups").value;

        const pageOffset = (page - 1) * currentLimit;
        const result = await getSearch(
          currentLimit,
          pageOffset,
          docNumber,
          startDate,
          endDate,
          status,
          "st_hf"
        );

        if (result.status === "success") {
          displayData(result.data, pageOffset);
          updatePagination(result.pagination);
        } else {
          console.error("เกิดข้อผิดพลาด:", result.message);
          alert("ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
        }
      } catch (error) {
        console.error("เกิดข้อผิดพลาด:", error);
        alert("ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
      }
    }

    // เพิ่ม Event Listeners
    document
      .querySelector(".btn-primary")
      .addEventListener("click", () => searchAndDisplay(1));

    document.querySelector(".pagination").addEventListener("click", (e) => {
      if (e.target.classList.contains("page-link")) {
        const page = parseInt(e.target.dataset.page);
        if (!isNaN(page)) {
          searchAndDisplay(page);
        }
      }
    });

    // โหลดข้อมูลครั้งแรก
    await searchAndDisplay(1);

    document
      .getElementById("searchBtn")
      .addEventListener("click", () => searchAndDisplay(1));

    function resetSearchFields() {
      document.getElementById("input-label11").value = "";
      startDatePicker.clear();
      endDatePicker.clear();
      clearChoicesSelect();
    }

    document.getElementById("resetBtn").addEventListener("click", () => {
      resetSearchFields();
      searchAndDisplay(1);
    });
  } catch (err) {
    console.error("เกิดข้อผิดพลาด:", err);
    alert("ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
  }
})();
function convertToBuddhistEra(dateString) {
  const date = new Date(dateString);

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0"); // เดือนเริ่มที่ 0 จึงต้อง +1
  const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.
  const hours = date.getHours().toString().padStart(2, "0");
  const minutes = date.getMinutes().toString().padStart(2, "0");

  return `${day}-${month}-${year} ${hours}:${minutes}`;
}
