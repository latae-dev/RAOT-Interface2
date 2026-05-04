$(document).ready(function () {
    $("input[name='approve_status']").change(function () {
        if ($("#reject").is(":checked")) {
            $("#remark").prop("disabled", false);
            $("#remark").attr("required", true);
        } else {
            $("#remark").prop("disabled", true);
            $("#remark").removeAttr("required");
            $("#remark").val("");
        }
    });

    $(document).on("submit", "#approveForm", function (e) {
        e.preventDefault();

        const form = this;
        const status = $("input[name='approve_status']:checked").val();
        const remark = $("#remark").val().trim();

        if (status === "reject" && remark === "") {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกเหตุผล',
                text: 'กรุณากรอกเหตุผลในการไม่อนุมัติ',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                $("#remark").focus();
            });
            return false;
        }

        Swal.fire({
            title: 'ยืนยันการส่งผลการพิจารณา',
            text: "คุณต้องการส่งผลการอนุมัติ/ไม่อนุมัติ ใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ส่งเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});