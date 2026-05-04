import { getAllParams } from './withdraw_parameter.js';

(async () => {
    try {
        const params_all = await getAllParams();
        console.log(params_all);
        const tableBody = document.querySelector('tbody');
        let currentPage = 1;
        let currentLimit = 10;

        $.ajax({
            type: "GET",
            url: "../controllers/withdraws/wrd_approvel_controller.php?action=get_persons_report&depart_code=" + params_all.current_user.depart_code,
            dataType: "json",
            success: function (response) {
                var $select = $('<select>', {
                    class: 'form-control',
                    id: 'choices-single-person',
                    name: 'choices-single-person'
                });
                $select.append('<option value="">เลือกผู้ร้องขอ</option>');
                $.each(response, function (i, person) {
                    $select.append(
                        $('<option>', {
                            value: person.req_user_code,
                            text: person.full_name
                        })
                    );
                });

                $('#person-select-container').empty()
                    .append('<label class="form-label mt-2">ผู้ร้องขอ :</label>')
                    .append($select);

                $('#choices-single-person').select2({
                    placeholder: "เลือกผู้ร้องขอ",
                    allowClear: true,
                    minimumInputLength: 1,
                    language: {
                        inputTooShort: function (args) {
                            return "กรอกชื่อผู้ร้องขอ";
                        },
                        noResults: function () {
                            return "ไม่พบผู้ร้องขอที่ค้นหา";
                        }
                    }
                });

                $('#choices-single-person').val('').trigger('change');
            }
        });

        var $deptSelect = $('<select>', {
            class: 'form-control',
            id: 'choices-single-department',
            name: 'choices-single-department'
        });
        $deptSelect.append('<option value="">เลือกหน่วยงาน</option>');
        params_all.departs.forEach(function (dept) {
            $deptSelect.append(
                $('<option>', {
                    value: dept.depart_code,
                    text: dept.depart_name
                })
            );
        });

        $('#department-select-container').empty()
            .append('<label class="form-label mt-2">หน่วยงานผู้ร้องขอ :</label>')
            .append($deptSelect);

        $('#choices-single-department').select2({
            placeholder: "เลือกหน่วยงาน",
            allowClear: true,
            language: {
                inputTooShort: function (args) {
                    return "กรอกชื่อหน่วยงาน";
                },
                noResults: function () {
                    return "ไม่พบหน่วยงานที่ค้นหา";
                }
            }
        });

        if (!params_all.current_user.head_office_code) {
            $('#choices-single-department').val(params_all.current_user.depart_code).trigger('change').attr('disabled', true);
        }

        document.getElementById('pageSizeSelect').addEventListener('change', async function () {
            const value = this.value;
            if (value === 'all') {
                currentLimit = 99999; // หรือจำนวนสูงสุดที่ backend รองรับ
            } else {
                currentLimit = parseInt(value, 10);
            }
            await searchAndDisplay(1);
        });
        // ฟังก์ชันแสดงผลข้อมูลในตาราง
        function displayData(data, pageOffset = 0) {
            tableBody.innerHTML = ''; // ล้างข้อมูลเก่า

            if (!data || data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="14" class="text-center">ไม่พบข้อมูล</td>';
                tableBody.appendChild(row);
                return;
            }

            data.forEach((item, index) => {
                const statusLabels = {
                    waiting: `รอการตรวจสอบ`,
                    approve: `อนุมัติ`,
                    reject: `ไม่อนุมัติ`,
                    edit: `ส่งกลับแก้ไข</span>`,
                    delete: `ลบแล้ว`,
                    finance_check: `ผ่านการตรวจสอบแล้ว`,
                    cho_reject: `ไม่ผ่านการตรวจสอบ`
                };

                const row = document.createElement('tr');
                row.classList.add('text-center');

                // ✅ ตรวจสอบสิทธิ์: เป็นผู้ร้องขอเองและยังไม่อนุมัติ (เช็คเงือนไขเพิ่มสำรหับ reject และ waiting ให้ปุ่มสามารถเปิดให้ใช้งานได้)
                const canApprove = item.st_hf === 'waiting' && item.req_user_code != params_all.current_user.user_code;
                // ✅ ถ้า st_hf === 'approve' ไม่ใส่ disabled
                const disabledAttr = item.st_hf === 'approve' ? '' : 'disabled';

                row.innerHTML = `
                    <td>${convertToBuddhistEra(item.plan_start)}</td>
                    <td>${convertToBuddhistEra(item.plan_end)}</td>
                    <td style="text-align:left;">${item.full_name}</td>
                    <td style="text-align:left;">${item.material_list}</td>
                    <td>${item.plan_number}</td>
                    <td>${statusLabels[item.st_hf] || '-'}</td>
                    <td>${item.st_hf_date ? convertToBuddhistEra(item.st_hf_date) : '-'}</td>
                    <td>${(item.total && item.total != 0) ? Number(item.total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${(item.expenses_inside_total && item.expenses_inside_total != 0) ? Number(item.expenses_inside_total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${(item.expenses_outside_total && item.expenses_outside_total != 0) ? Number(item.expenses_outside_total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${(item.compensation_total && item.compensation_total != 0) ? Number(item.compensation_total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${(item.expenses_total && item.expenses_total != 0) ? Number(item.expenses_total).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${(item.amount && item.amount != 0) ? Number(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '-'}</td>
                    <td>${item.fund}</td>
                `;

                tableBody.appendChild(row);

                // ✅ เงื่อนไขผูก event ลบ ถ้ามีปุ่ม delete
                const deleteBtn = row.querySelector('.delete-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        Swal.fire({
                            title: "ท่านแน่ใจหรือไม่?",
                            text: "ท่านต้องการลบรายการนี้หรือไม่?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "ยืนยัน",
                            cancelButtonText: `ยกเลิก`
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteStatus(item.id);
                            }
                        });
                    });
                }
            });
        }

        async function deleteStatus(id) {
            try {
                window.showLoading();
                const response = await fetch(`../controllers/withdraws/wrd_approvel_controller.php?action=delete_status&id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                console.log(result);
                window.hideLoading();

                Swal.fire({
                    title: "ดำเนินการสำเร็จ",
                    text: "ดำเนินการลบเอกสารสำเร็จ",
                    icon: "success"
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                window.hideLoading();
                console.error('เกิดข้อผิดพลาด:', error);
                Swal.fire({
                    title: "เกิดข้อผิดพลาด",
                    text: "รายการข้อผิดพลาด: " + error.message,
                    icon: "warning"
                });
            }
        }

        // ฟังก์ชันอัพเดท pagination
        function updatePagination(pagination) {
            const paginationContainer = document.querySelector('.pagination');
            const totalPages = pagination.total_pages;
            const currentPage = pagination.current_page;

            let paginationHTML = `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">
                        ก่อนหน้า
                    </a>
                </li>
            `;

            for (let i = 1; i <= totalPages; i++) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">
                        ถัดไป
                    </a>
                </li>
            `;

            paginationContainer.innerHTML = paginationHTML;

            // อัพเดทข้อความแสดงจำนวนรายการ
            document.querySelector('.card-footer .d-flex div:first-child').textContent =
                `กำลังแสดง ${pagination.total_records} รายการ`;
        }

        // ฟังก์ชันค้นหา
        async function getSearch(limit = 10, offset = 0, doc_number = '', start_date = '', end_date = '', status = '', key = '', req_user_code = '') {
            try {
                window.showLoading();
                const response = await fetch(`../controllers/withdraws/wrd_controller.php?
                    action=report&
                    key=${key}&
                    limit=${limit}&
                    offset=${offset}&
                    plan_number=${doc_number}&
                    start_date=${start_date}&
                    end_date=${end_date}&
                    status=${status}&
                    depart_code=${$('#choices-single-department').val()}&
                    req_user_code=${req_user_code}
                    `, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                    }
                });

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
                const docNumber = document.getElementById('input-label11').value;
                const startDate = document.querySelector('input[placeholder="กำหนด วันที่เริ่มต้น"]').value;
                const endDate = document.querySelector('input[placeholder="กำหนด วันที่สิ้นสุด"]').value;
                const status = document.getElementById('choices-single-groups').value;
                const req_user_code = document.getElementById('choices-single-person').value;

                const pageOffset = (page - 1) * currentLimit;
                const result = await getSearch(currentLimit, pageOffset, docNumber, startDate, endDate, status, 'st_hf', req_user_code);

                if (result.status === 'success') {
                    displayData(result.data, pageOffset);
                    updatePagination(result.pagination);
                } else {
                    console.error('เกิดข้อผิดพลาด:', result.message);
                    alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
                }
            } catch (error) {
                console.error('เกิดข้อผิดพลาด:', error);
                alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }

        // เพิ่ม Event Listeners
        document.querySelector('.btn-primary').addEventListener('click', () => searchAndDisplay(1));

        document.querySelector('.pagination').addEventListener('click', (e) => {
            if (e.target.classList.contains('page-link')) {
                const page = parseInt(e.target.dataset.page);
                if (!isNaN(page)) {
                    searchAndDisplay(page);
                }
            }
        });

        // โหลดข้อมูลครั้งแรก
        await searchAndDisplay(1);

        document.getElementById('searchBtn').addEventListener('click', () => searchAndDisplay(1));

        function resetSearchFields() {
            document.getElementById('input-label11').value = '';
            startDatePicker.clear();
            endDatePicker.clear();
            $('#choices-single-person').val('').trigger('change');
            if (params_all.current_user.head_office_code) {
                $('#choices-single-department').val('').trigger('change');
            }
            clearChoicesSelect();
        }

        document.getElementById('resetBtn').addEventListener('click', () => {
            resetSearchFields();
            searchAndDisplay(1);
        });

    } catch (err) {
        console.error('เกิดข้อผิดพลาด:', err);
        alert('ไม่สามารถโหลดข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ');
    }
})();
function convertToBuddhistEra(dateString) {
    const date = new Date(dateString);

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); // เดือนเริ่มที่ 0 จึงต้อง +1
    const year = date.getFullYear() + 543; // แปลง ค.ศ. เป็น พ.ศ.
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${day}-${month}-${year}`;
}

document.getElementById("exportExcelBtn").addEventListener("click", function () {
    const table = document.getElementById("tableId");

    // -----------------------------
    // ฟังก์ชันแปลง table → data + merges
    // -----------------------------
    function parseTable(table) {
        const rows = table.rows;
        const data = [];
        const merges = [];
        const map = [];

        for (let r = 0; r < rows.length; r++) {
            const row = [];
            if (!map[r]) map[r] = [];

            let colIndex = 0;

            for (let c = 0; c < rows[r].cells.length; c++) {
                while (map[r][colIndex]) colIndex++;

                const cell = rows[r].cells[c];
                let text = cell.innerHTML.replace(/<br\s*\/?>/gi, "\n").trim();

                const colspan = cell.colSpan || 1;
                const rowspan = cell.rowSpan || 1;

                row[colIndex] = text;

                for (let rr = r; rr < r + rowspan; rr++) {
                    if (!map[rr]) map[rr] = [];
                    for (let cc = colIndex; cc < colIndex + colspan; cc++) {
                        map[rr][cc] = true;
                    }
                }
                map[r][colIndex] = false;

                if (colspan > 1 || rowspan > 1) {
                    merges.push({
                        s: { r: r, c: colIndex },
                        e: { r: r + rowspan - 1, c: colIndex + colspan - 1 }
                    });
                }

                colIndex += colspan;
            }

            data.push(row);
        }

        return { data, merges };
    }

    // -----------------------------
    // ฟังก์ชันเติม border ใน merge range
    // -----------------------------
    function applyBorderToMergeRanges(ws, merges) {
        merges.forEach(({ s, e }) => {
            for (let r = s.r; r <= e.r; r++) {
                for (let c = s.c; c <= e.c; c++) {
                    const addr = XLSX.utils.encode_cell({ r, c });
                    if (!ws[addr]) ws[addr] = { v: "", t: "s" };

                    if (!ws[addr].s) ws[addr].s = {};
                    if (!ws[addr].s.border) ws[addr].s.border = {};

                    // เติม border ด้านบน
                    if (r === s.r) ws[addr].s.border.top = { style: "thin", color: { rgb: "000000" } };
                    else ws[addr].s.border.top = ws[addr].s.border.top || {};

                    // ด้านล่าง
                    if (r === e.r) ws[addr].s.border.bottom = { style: "thin", color: { rgb: "000000" } };
                    else ws[addr].s.border.bottom = ws[addr].s.border.bottom || {};

                    // ด้านซ้าย
                    if (c === s.c) ws[addr].s.border.left = { style: "thin", color: { rgb: "000000" } };
                    else ws[addr].s.border.left = ws[addr].s.border.left || {};

                    // ด้านขวา
                    if (c === e.c) ws[addr].s.border.right = { style: "thin", color: { rgb: "000000" } };
                    else ws[addr].s.border.right = ws[addr].s.border.right || {};
                }
            }
        });
    }

    // -----------------------------
    // แปลง table → data + merges
    // -----------------------------
    const { data, merges } = parseTable(table);
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = merges;

    // -----------------------------
    // ตั้ง style, alignment, wrap text, date, number
    // -----------------------------
    const dateCols = [0, 1, 6];
    const numberCols = [7, 8, 9, 10, 11, 12];
    const range = XLSX.utils.decode_range(ws['!ref']);

    for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const addr = XLSX.utils.encode_cell({ r: R, c: C });
            const cell = ws[addr];
            if (!cell) continue;

            // สไตล์เริ่มต้น
            let style = {
                border: {
                    top: { style: "thin", color: { rgb: "000000" } },
                    bottom: { style: "thin", color: { rgb: "000000" } },
                    left: { style: "thin", color: { rgb: "000000" } },
                    right: { style: "thin", color: { rgb: "000000" } }
                },
                alignment: { vertical: "center" }
            };

            // Alignment
            if (R === 0) {
                style.alignment.horizontal = "center";
                // ลบ font.bold ออกแล้ว
            } else if ([2, 3].includes(C)) {
                style.alignment.horizontal = "left";
            } else {
                style.alignment.horizontal = "center";
            }

            // wrap text
            if (typeof cell.v === "string" && cell.v.includes("\n")) {
                style.alignment.wrapText = true;
            }

            // แปลงวันที่
            if (R > 0 && dateCols.includes(C)) {
                const parts = cell.v.split(/[-/]/);
                if (parts.length === 3) {
                    let d = parseInt(parts[0], 10);
                    let m = parseInt(parts[1], 10);
                    let y = parseInt(parts[2], 10);
                    if (y > 2400) y -= 543; // แปลง พ.ศ. → ค.ศ.
                    cell.t = "d";
                    cell.v = new Date(y, m - 1, d);
                    cell.z = "dd-mm-yyyy";
                }
            }

            // แปลงตัวเลข
            if (R > 0 && numberCols.includes(C)) {
                const num = parseFloat(cell.v.toString().replace(/,/g, ""));
                if (!isNaN(num)) {
                    cell.t = "n";
                    cell.v = num;
                    style.numFmt = "#,##0.00";
                }
            }

            cell.s = style;
        }
    }

    // -----------------------------
    // เติม border ให้ merge ครบทุก cell
    // -----------------------------
    applyBorderToMergeRanges(ws, merges);

    // -----------------------------
    // กำหนดความกว้างคอลัมน์
    // -----------------------------
    ws["!cols"] = [
        { wch: 12 }, { wch: 12 }, { wch: 40 }, { wch: 50 },
        { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 15 },
        { wch: 30 }, { wch: 30 }, { wch: 15 }, { wch: 15 },
        { wch: 30 }, { wch: 30 }
    ];

    // -----------------------------
    // สร้างไฟล์และดาวน์โหลด
    // -----------------------------
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    XLSX.writeFile(wb, "รายงานเงินทดรอง.xlsx");
});


