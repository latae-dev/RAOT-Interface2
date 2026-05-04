import { getAllParams } from "./withdraw_parameter.js";

(async () => {
  try {
    const params_all = await getAllParams();
    const tableBody = document.querySelector("tbody");
    let currentPage = 1;
    let currentLimit = 10;

    $.ajax({
      type: "GET",
      url:
        "../controllers/withdraws/wrd_expenses_controller.php?action=get_persons&depart_code=" +
        params_all.current_user.depart_code,
      dataType: "json",
      success: function (response) {
        var $select = $("<select>", {
          class: "form-control",
          id: "choices-single-person",
          name: "choices-single-person",
        });
        $select.append('<option value="">กรุณาเลือกผู้ร้องขอ</option>');
        $.each(response, function (i, person) {
          $select.append(
            $("<option>", {
              value: person.req_user_code,
              text: person.full_name,
            })
          );
        });

        $("#person-select-container")
          .empty()
          .append('<label class="form-label mt-2">ผู้ร้องขอ :</label>')
          .append($select);

        $("#choices-single-person").select2({
          placeholder: "กรุณาเลือกผู้ร้องขอ",
          allowClear: true,
          minimumInputLength: 1,
          language: {
            inputTooShort: function (args) {
              return "กรุณากรอกชื่อผู้ร้องขอ";
            },
            noResults: function () {
              return "ไม่พบผู้ร้องขอที่ค้นหา";
            },
          },
        });

        $("#choices-single-person").val("").trigger("change");
      },
    });
    // หลังจากได้ params_all แล้ว
    // if (params_all.current_user.position_code === 'APHO' && window.choicesInstance) {
    //     console.log("ลบ audit options...");

    //     // เก็บเฉพาะที่ไม่ใช่ audit
    //     const keepChoices = window.choicesInstance._store.choices.filter(c =>
    //         c.value !== 'audit_approve' && c.value !== 'audit_reject'
    //     );

    //     // clear แล้ว set ใหม่
    //     window.choicesInstance.clearChoices();
    //     window.choicesInstance.setChoices(
    //         keepChoices.map(c => ({
    //             value: c.value,
    //             label: c.label,
    //             selected: c.selected,
    //             disabled: c.disabled
    //         })),
    //         'value', 'label', false
    //     );
    // }

    if (params_all.current_user.position_code === "APHO") {
      window.choicesInstance.setChoiceByValue("waiting");
    } else if (params_all.current_user.position_code === "APHOP") {
      window.choicesInstance.setChoiceByValue("finance_approve");
    }

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
          finance_approve: `<span class="badge rounded-pill bg-success">กองการเงิน อนุมัติ</span>`,
          finance_reject: `<span class="badge rounded-pill bg-danger">กองการเงิน ไม่อนุมัติ</span>`,
          audit_approve: `<span class="badge rounded-pill bg-info">กองตรวจจ่าย อนุมัติ</span>`,
          audit_reject: `<span class="badge rounded-pill bg-danger">กองตรวจจ่าย ไม่อนุมัติ</span>`,
        };

        const row = document.createElement("tr");
        row.classList.add("text-center");

        let approveButton = "";
        const linkView =
          item.expenses_group === "3"
            ? item.is_type_req == 1
              ? `travel-expenses-money-multi.php`
              : "travel-expenses-money-outside-multi.php"
            : item.is_type_req == 1
            ? "travel-expenses-money-view.php"
            : "travel-expenses-money-outside-view.php";
        if (
          (params_all.current_user.position_code === "APHO" &&
            item.approve_status === "waiting") ||
          (params_all.current_user.position_code === "APHOP" &&
            item.approve_status === "finance_approve")
        ) {
          approveButton = `
                        <a href="${linkView}?id=${item.id}&is_approve=1&is_action=approve" 
                        class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" 
                        title="การพิจารณาอนุมัติ">
                            <i class="bx bx-list-check"></i>
                        </a>`;
        }

        row.innerHTML = `
                    <td>${index + 1 + pageOffset}</td>
                    <td>${convertToBuddhistEra(item.req_user_date)}</td>
                    <td>${item.expenses_number}</td>
                    <td align='left'>${item.expenses_form_name}</td>
                    <td>${item.expenses_code}</td>
                    <td>${convertToBuddhistEranoTime(item.expenses_date)}</td>
                    <td>${item.full_name}</td>
                    <td>${statusLabels[item.approve_status] || "-"}</td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="${linkView}?id=${
          item.id
        }&is_approve=0&is_action=approve" 
                            class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                <i class="fe fe-eye"></i>
                            </a>
                            ${approveButton}
                        </div>
                    </td>
                `;

        tableBody.appendChild(row);
      });
    }

    // ฟังก์ชันอัพเดท pagination
    function updatePagination(pagination) {
      const paginationContainer = document.querySelector(".pagination");
      const totalPages = pagination.total_pages;
      const currentPage = pagination.current_page;

      let paginationHTML = `
                <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="javascript:void(0);" data-page="${
                      currentPage - 1
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
                <li class="page-item ${
                  currentPage === totalPages ? "disabled" : ""
                }">
                    <a class="page-link" href="javascript:void(0);" data-page="${
                      currentPage + 1
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
      key = "",
      req_user_code = ""
    ) {
      try {
        window.showLoading();
        const response = await fetch(
          `../controllers/withdraws/wrd_expenses_controller.php?
                    action=expenses_approvel_search&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    expenses_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    req_user_code=${params_all.current_user.user_code}&
                    req_position_code=${params_all.current_user.position_code}&
                    req_depart_code=${params_all.current_user.depart_code}&
                    search_user_code=${req_user_code}
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
        console.log(data);
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
        const req_user_code = document.getElementById(
          "choices-single-person"
        ).value;

        const pageOffset = (page - 1) * currentLimit;
        const result = await getSearch(
          currentLimit,
          pageOffset,
          docNumber,
          startDate,
          endDate,
          status,
          "approve_status",
          req_user_code
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
      $("#choices-single-person").val("").trigger("change");
      if (params_all.current_user.position_code === "APHO") {
        window.choicesInstance.setChoiceByValue("waiting");
      } else if (params_all.current_user.position_code === "APHOP") {
        window.choicesInstance.setChoiceByValue("finance_approve");
      }
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
function convertToBuddhistEranoTime(dateString) {
  const date = new Date(dateString);

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0"); // เดือนเริ่มที่ 0 จึงต้อง +1
  const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.

  return `${day}-${month}-${year}`;
}
