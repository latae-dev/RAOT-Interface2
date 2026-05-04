$(document).ready(function () {
    let table = new DataTable('#business_list');

    let table2 = new DataTable('#report_list');

    $(document).on("click", ".delete", function(e) {
        e.preventDefault();

        let url = $(this).attr("href");

        Swal.fire({
            title: "คุณต้องการลบข้อมูล?",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "ตกลง",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

     $('button[name="action"][value="exportExcel"]').on('click', function (e) {
        const startDate = $('input[name="start_date"]').val().trim();
        const endDate = $('input[name="end_date"]').val().trim();
        const exportType = $('select[name="export_type"]').val().trim();

        if (startDate === '' || endDate === '' || exportType === '') {
            e.preventDefault();
            alert('กรุณากรอกวันที่เริ่มต้นและวันที่สิ้นสุด และ คำขอแยกตามประเภท ก่อนทำการ Export Excel');
        }
    });
    
});

//version แผนเป็นตัวกำหนดการ approve 