import { getAllParams } from "./withdraw_parameter.js";

(async () => {
  try {
    const params_all = await getAllParams();
    const tableBody = document.querySelector("tbody");
    let currentPage = 1;
    let currentLimit = 10;
    let is_type = '';
    const hasmatchAPHO = params_all?.position_list_id
      .some(id => ['5'].includes(id));
    const hasmatchAPHOP = params_all?.position_list_id
      .some(id => ['6'].includes(id));
    const hasmatchTransfer = params_all?.position_list_id
      .some(id => ['14'].includes(id));
    if (hasmatchAPHO) {
      is_type = 1;
    } else if (hasmatchAPHOP) {
      is_type = 2;
    }

    // ฟังก์ชันแสดงผลข้อมูลในตาราง
    function displayData(data, pageOffset = 0) {
      tableBody.innerHTML = ""; // ล้างข้อมูลเก่า

      if (!data || data.length === 0) {
        const row = document.createElement("tr");
        row.innerHTML = '<td colspan="10" class="text-center">ไม่พบข้อมูล</td>';
        tableBody.appendChild(row);
        return;
      }

      data.forEach((item, index) => {
        var text = 'รอการตรวจสอบ';
        if (item.plan_id) { text = 'รอการตรวจสอบ' }
        const statusLabels = {
          waiting: `<span class="badge rounded-pill bg-warning">${text}</span>`,
          finance_approve: `<span class="badge rounded-pill bg-success">อนุมัติ</span>`,
          finance_reject: `<span class="badge rounded-pill bg-danger">ไม่อนุมัติ</span>`,
          audit_approve: `<span class="badge rounded-pill bg-info">ผ่านการตรวจสอบแล้ว</span>`,
          audit_reject: `<span class="badge rounded-pill bg-danger">ไม่ผ่านการตรวจสอบ</span>`,
          finance_check: `<span class="badge rounded-pill bg-info">ผ่านการตรวจสอบแล้ว</span>`,
          cho_reject: `<span class="badge rounded-pill bg-danger">ไม่ผ่านการตรวจสอบ</span>`,
        };

        const row = document.createElement("tr");
        row.classList.add("text-center");

        // ✅ ตรวจสอบสิทธิ์: เป็นผู้ร้องขอเองและยังไม่อนุมัติ (เช็คเงือนไขเพิ่มสำรหับ reject และ waiting ให้ปุ่มสามารถเปิดให้ใช้งานได้)
        const canEditOrDelete = item.req_user_code === params_all.current_user.user_code && (item.approve_status === 'waiting' || item.approve_status === 'finance_reject' || item.approve_status === 'audit_reject' || item.approve_status === 'cho_reject');
        // ✅ ถ้า st_hf === 'approve' ไม่ใส่ disabled
        const linkEdit =
          item.expenses_group === "3"
            ? item.is_type_req === "1" ? `travel-expenses-money-multi-edit.php?id=${item.id}&is_approve=0&is_action=edit` : `travel-expenses-money-outside-multi-edit.php?id=${item.id}&is_approve=0&is_action=edit`
            : item.is_type_req === "1"
              ? `travel-expenses-money-edit.php?id=${item.id}`
              : `travel-expenses-money-outside-edit.php?id=${item.id}`;
        const linkView =
          item.expenses_group === "3"
            ? item.is_type_req === "1" ? `travel-expenses-money-multi.php?id=${item.id}&is_approve=0&is_action=view` : `travel-expenses-money-outside-multi.php?id=${item.id}&is_approve=0&is_action=view`
            : item.is_type_req === "1"
              ? `travel-expenses-money-view.php?id=${item.id}&is_approve=0&is_action=view`
              : `travel-expenses-money-outside-view.php?id=${item.id}&is_approve=0&is_action=view`;

        const disabledAttr = item.approve_status === 'audit_approve' ? '' : 'disabled';
        var classbtn = 'btn-secondary-custom disabled';
        var linkbtn = 'javascript:void(0);';
        if (item.approve_status == 'finance_approve') {
          if (item.has_compensation != 1) {
            classbtn = 'btn-info-custom';
            linkbtn = `money-order-plan.php?expensesId=${item.id}`;
          } else {
            classbtn = 'btn-info-c2-custom';
            linkbtn = `money-order-view.php?id=${item.compensation_id}&is_approve=0&is_action=view`;
          }
        }
        const linkMoneyOrder = !item.plan_id ? `<a href="${linkbtn}" class="${classbtn}" >ส่งใบสั่งจ่าย</a>` : '-';

        let linkExport = 'javascript:void(0);';
        let target = '';
        let classExport = '';
        if (item.expenses_group == '3') {
          classExport = 'export-btn';
        } else {
          linkExport = item.is_type_req === '1' ? `./export/travel-expenses-list-export.php?id=${item.id}` : `./export/travel-expenses-list-outside-export.php?id=${item.id}`;
          target = '_blank';
        }
        let linkSend = '';
        if (hasmatchTransfer) {
          let active = ''
          if (item.has_transfer == 1) {
            active = 'active'
          }
          linkSend = `<a href="./travel-expenses-money-transfer.php?id=${item.id}" class="btn btn-icon btn-sm btn-purple-transparent rounded-pill ${active}"><i class="ri-file-transfer-line"></i></a>`;
        }

        row.innerHTML = `
                    <td>${index + 1 + pageOffset}</td>
                    <td>${convertToBuddhistEra(item.req_user_date)}</td>
                    <td>${item.expenses_number}</td>
                    <td>${item.plan_number || "-"}</td>
                    <td>${item.expenses_form_name}</td>
                    <td>${item.expenses_code}</td>
                    <td>${convertToBuddhistEranoTime(item.expenses_date)}</td>
                    <td>${linkMoneyOrder}</td>
                    <td>${statusLabels[item.approve_status] || "-"}</td>
                    <td>
                        <div class="hstack gap-2 fs-15">
                            <a href="${linkView}" class="btn btn-icon btn-sm btn-success-transparent rounded-pill">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="${linkExport}" target="${target}" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill ${classExport}">
                                <i class="bx bx-export"></i>
                            </a>
                            ${canEditOrDelete
            ? `
                                <a href="${linkEdit}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill delete-btn" data-id="${item.id}">
                                    <i class="ri-delete-bin-line"></i>
                                </a>
                            `
            : ""
          }
          ${linkSend}
                        </div>
                    </td>
                `;

        tableBody.appendChild(row);

        // ✅ เงื่อนไขผูก event ลบ ถ้ามีปุ่ม delete
        const deleteBtn = row.querySelector('.delete-btn');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            Swal.fire({
              title: `รหัสคำขอเบิกค่าใช้จ่ายฯ : ${item.expenses_number}`,
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

        const exportBtn = row.querySelector(".export-btn");
        if (exportBtn && !exportBtn.classList.contains("disabled")) {
          exportBtn.addEventListener("click", function (e) {
            e.preventDefault();
            showLoading();
            $.ajax({
              type: "GET",
              url: `../controllers/withdraws/wrd_expenses_controller.php?action=get_one_multi&id=${item.id}`,
              dataType: "json",
              success: function (response) {
                hideLoading();
                var td = '';
                $.each(response.forms, function (index, value) {
                  console.log(value.main_data.id);
                  const linkExport = value.main_data.is_type_req === '1' ? `./export/travel-expenses-list-export.php?id=${value.main_data.id}` : `./export/travel-expenses-list-outside-export.php?id=${value.main_data.id}`;
                  const title = value.main_data.expenses_group == '2' ? 'แบบหมู่คณะฯ' : ' ของ คุณ ' + value.main_data.full_name;
                  td += `
                                        <tr>
                                            <td class="text-center">${index + 1}</td>
                                            <td style="text-align: left;">${value.main_data.expenses_form_name} ${title}</td>
                                            <td class="text-center">
                                                <a href="${linkExport}" target="_blank" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill">
                                                    <i class="bx bx-export"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    `;
                });
                Swal.fire({
                  position: 'top',
                  title: `<h5>รหัสคำขอเบิกค่าใช้จ่ายฯ : ${item.expenses_number}</h5>`,
                  html: `
                                    <div class="row">
                                        <div class="col-12">
                                        <table class="table text-nowrap table-bordered border-black">
                                            <thead class="text-center">
                                                <tr>
                                                    <td width="8%" class="bg-info text-fixed-dark bg-opacity-25">ลำดับ</td>
                                                    <td width="80%" style="text-align: left;" class="bg-info text-fixed-dark bg-opacity-25">รายการ</td>
                                                    <td width="12%" class="bg-info text-fixed-dark bg-opacity-25">ดาวน์โหลด</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${td}
                                            </tbody>
                                        </table>
                                    </div>
                                    `,
                  width: '70%',
                  showCancelButton: true,
                  showConfirmButton: false,
                  cancelButtonText: `ปิด`,
                }).then((result) => { });
              }
            });
          });
        }
      });
    }

    async function deleteStatus(id) {
      try {
        window.showLoading();
        const response = await fetch(
          `../controllers/withdraws/wrd_expenses_controller.php?action=delete_status&id=${id}`,
          {
            method: "DELETE",
          }
        );

        const result = await response.json();
        console.log(result);
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
          `../controllers/withdraws/wrd_expenses_controller.php?
                    action=expenses_search&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    expenses_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    req_user_code=${params_all.current_user.user_code}&
                    is_type=${is_type}
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
          "approve_status"
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
function convertToBuddhistEranoTime(dateString) {
  const date = new Date(dateString);

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0"); // เดือนเริ่มที่ 0 จึงต้อง +1
  const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.

  return `${day}-${month}-${year}`;
}
