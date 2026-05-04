const choiceInstances = {};
var base_url = $('.base_url').val();

function destroyChoices(selector) {
    if (choiceInstances[selector]) {
        choiceInstances[selector].destroy();
    }
}

function updateRowspan(group) {
    let rowCount = group.find("tr").length;

    group.find("td:has(.activity_name)").attr("rowspan", rowCount);
    group.find("td:has(.activity_name) textarea").attr("rowspan", rowCount);

     $("#activity_table tbody.group").each(function(groupIndex) {
        let group = $(this);

        group.find("textarea.activity_name").attr("name", `activities[${groupIndex}][activity_name][]`);

        group.find("tr").each(function(rowIndex) {
            let inputs = $(this).find("td input, td select, td textarea").not(".activity_name");

            let colNames = ["selected","number_budget","list_budget","quantity","price","amount","total_amount"];

            inputs.each(function(i) {
                $(this).attr("name", `activities[${groupIndex}][${colNames[i]}][]`);
            });
        });
    });
}

function updateRowspanExpense(group) {
    let rowCount = group.find("tr").length;

    // อัปเดต name attribute ของ input ต่าง ๆ
    $("#expense_table tbody.group").each(function(groupIndex) {
        let group = $(this);

        group.find(".expense_name").attr("name", `expenses[${groupIndex}][expense_name][]`);

        group.find("tr").each(function(rowIndex) {
            let inputs = $(this).find("td input, td select, td textarea").not(".expense_name");

            let colNames = ["selected","plan_trip","officer","people","work_days","allowance_rate","allowance_amount",
                            "transport_rate","transport_amount","stay_days","stay_rate","stay_amount",
                            "fuel_trips","fuel_rate","fuel_amount","total_activity"];

            inputs.each(function(i) {
                $(this).attr("name", `expenses[${groupIndex}][${colNames[i]}][]`);
            });
        });
    });
}

function updateRowNames() {
    $("#activity_table").find("tbody.group").each(function (groupIndex, group) {
        $(group).find("tr").each(function (rowIndex, row) {
            $(row).find("input, select, textarea").each(function () {
                let oldName = $(this).attr("name");
                if (oldName) {
                    let newName = oldName.replace(/\[\d+\]/, "[" + groupIndex + "]");
                    $(this).attr("name", newName);
                }
            });
        });
    });
}

function updateRowspanOperation() {
    $("#operation_plan tbody.group").each(function(groupIndex) {
        let group = $(this);

        group.find("input.operation_plan_name")
             .attr("name", `operations[${groupIndex}][operation_plan_name][]`);

        let colNames = [
            "selected", 
            "operation_plan_activity",
            "weight", 
            "target", 
            "unit",
            "oct", "nov", "dec", "jan", "feb", "mar",
            "apr", "may", "jun", "jul", "aug", "sep",
            'operation_plan_assignee'
        ];

        group.find("tr.box").each(function() {
            let inputs = $(this).find("td input, td select, td textarea");

            inputs.each(function(i) {
                if (colNames[i] !== undefined) {
                    $(this).attr("name", `operations[${groupIndex}][${colNames[i]}][]`);
                }
            });
        });
    });
}

function initChoices(selector) {
  choiceInstances[selector] = new Choices(selector, {
    removeItemButton: true,
    shouldSort: false,
    shouldSortItems: false,
  });
}

function resetDropdown(id) {
    const $select = $(id);
    $select.empty();
    $select.append('<option value="">โปรดเลือก</option>');
}

function resetDropdownChoices(id) {
    destroyChoices(id);
    resetDropdown(id);
    initChoices(id);
}

function calculateRowActivity($row) {
    let qty   = parseNumber($row.find(".quantity").val());
    let price = parseNumber($row.find(".price").val());

    if (qty > 0 && price > 0) {
        let amount = qty * price;
        $row.find(".amount").val(amount.toFixed(2));
        $row.find(".total_amount").val(amount.toFixed(2));
    } else {
        $row.find(".amount").val("");
        $row.find(".total_amount").val("");
    }
}

function parseNumber(val) {
    return parseFloat((val || "").toString().replace(/,/g, '')) || 0;
}

function calculateSummary() {
    let total = 0;
    $("#activity_table .amount").each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $("#activity_table .total_summary_amount").val(total.toFixed(2));
}

function calculateRowExpense($row) {
    let people          = parseNumber($row.find(".people").val());
    let work_days       = parseNumber($row.find(".work_days").val());
    let allowance_rate  = parseNumber($row.find(".allowance_rate").val());
    let transport_rate  = parseNumber($row.find(".transport_rate").val());
    let stay_days       = parseNumber($row.find(".stay_days").val());
    let stay_rate       = parseNumber($row.find(".stay_rate").val());
    let fuel_trips      = parseNumber($row.find(".fuel_trips").val());
    let fuel_rate       = parseNumber($row.find(".fuel_rate").val());

    let allowance_amount = people * work_days * allowance_rate;
    let transport_amount = people * transport_rate;
    let stay_amount      = stay_days * stay_rate * people;
    let fuel_amount      = fuel_trips * fuel_rate;
    let total_activity   = allowance_amount + transport_amount + stay_amount + fuel_amount;

    $row.find(".allowance_amount").val(allowance_amount.toFixed(2));
    $row.find(".transport_amount").val(transport_amount.toFixed(2));
    $row.find(".stay_amount").val(stay_amount.toFixed(2));
    $row.find(".fuel_amount").val(fuel_amount.toFixed(2));
    $row.find(".total_activity").val(total_activity.toFixed(2));
}

function calculateSummaryExpense() {
    let total_allowance = 0;
    let total_transport = 0;
    let total_stay      = 0;
    let total_fuel      = 0;
    let total_amount    = 0;

    $("#expense_table tbody tr.box").each(function () {
        total_allowance += parseNumber($(this).find(".allowance_amount").val());
        total_transport += parseNumber($(this).find(".transport_amount").val());
        total_stay      += parseNumber($(this).find(".stay_amount").val());
        total_fuel      += parseNumber($(this).find(".fuel_amount").val());
        total_amount    += parseNumber($(this).find(".total_activity").val());
    });

    $("#expense_table .total_summary_allowance").val(total_allowance.toFixed(2));
    $("#expense_table .total_summary_transport").val(total_transport.toFixed(2));
    $("#expense_table .total_summary_stay").val(total_stay.toFixed(2));
    $("#expense_table .total_summary_fuel").val(total_fuel.toFixed(2));
    $("#expense_table .total_summary_amount").val(total_amount.toFixed(2));

    calculateSummaryBudget();
}

function calculateRowOperation($row) {
    let target = parseFloat($row.find(".operation_plan_target").val()) || 0;
    let weight = parseFloat($row.find(".operation_plan_weight").val()) || 0;

    let months = ["oct","nov","dec","jan","feb","mar","apr","may","jun","jul","aug","sep"];
    let total = 0;

    months.forEach(function(month) {
        let val = parseFloat($row.find("." + month).val()) || 0;
        total += val;
    }); 

    $row.find(".operation_plan_target").val(total.toFixed(2));
}

function calculateSummaryBudget() {
    var total_summary_expense = parseNumber($("#expense_table .total_summary_amount").val());
    var total_summary_activity = parseNumber($("#activity_table .total_summary_amount").val());
    var total_summary_budget = total_summary_expense + total_summary_activity;

    $("#budget_summary_amount").val(total_summary_budget.toFixed(2));
}

function populateUserSessionInput() {
    const userSession = sessionStorage.getItem("raot_user_session");

    if (!userSession) return;

    $('.user_session').val(userSession);
}

function updateAllGroupsSummary() {
    $("#operation_plan tbody.group").each(function() {
        let $group = $(this);
        let groupIndex = $group.attr("data-group");

        let sum = 0;
        $group.find("tr.box").each(function() {
            let val = parseFloat($(this).find(".weight").val()) || 0;
            sum += val;
        });

        let $summary = $("#operation_plan tbody.summary[data-set='" + groupIndex + "']");
        $summary.addClass('active');
        $summary.find(".total_summary_weight_amount").val(sum.toFixed(2));
    });
}


var dropdownChoices = ['#national_strategy', '#risk_indicator', '#risk_strategy', '#version_name', '#strategies', '#target_strategies', '#subject_strategies', '#plan_under_strategies', '#target_level_subject', '#sub_plan', '#target_sub_plan', '#plan_strategies', '#sub_indicators', '#tactics', '#project_code', '#activity_id'];

$(document).ready(function () {

    //populateUserSessionInput();

    if(dropdownChoices.length > 0) {
        $.each(dropdownChoices, function(index, id) {
            initChoices(id);
        });
    }

    $(document).on("click", ".trigger_modal_year", function () {
        var section = $(this).attr('attr-year');

        $('.attr_year').val(section);
        $('#add-board').modal('show');
    });

    $(document).on("click", "#add-board .submit_year", function (e) {
        e.preventDefault();
        let year = $("#add-board .dropdown_year").val();
        var section = $('.attr_year').val();

        if (year) {
            $('.year'+section).find('input').val(year);
            $('.year'+section).find('span').text("พ.ศ. " + year + " ");
        }

        $("#add-board").modal("hide");
    });

    $(document).on("change", ".risk_assessment", function () {
        let $row = $(this).closest("tr");

        if (!this.checked) {
            this.checked = true;
            return;
        }

        $row.find(".risk_assessment").not(this).prop("checked", false);

        const anyChecked = $(".risk_assessment:checked").length > 0;
        if (anyChecked) {
            $(".risk_assessment").removeAttr("required");
        } else {
            $(".risk_assessment").attr("required", true);
        }
    });

    $(document).on("input", ".lh, .im", function () {
        let $row = $(this).closest("tr");
        let lh = parseFloat($row.find(".lh").val()) || 0;
        let im = parseFloat($row.find(".im").val()) || 0;

        if (lh > 5) {
            lh = 5;
            $row.find(".lh").val(lh);
        }
        if (im > 5) {
            im = 5;
            $row.find(".im").val(im);
        }

        let level = lh * im;
        $row.find(".level").val(level);
    });
    

    // ประเภทโครงการ
    $('#project_type_name').change(function () {
        var projectType = $(this).val();

        $('.project_type_name_1, .project_type_name_2').addClass('d-none');

        if (projectType === "1") { 
            $('.project_type_name_1').removeClass('d-none');
        } else if (projectType === "2") { 
            $('.project_type_name_2').removeClass('d-none');
        }
    });

    // ลักษณะโครงการ
    $('#original_project').change(function () {
        var originalProject = $(this).val();

        $('.original_project_1, .original_project_2').addClass('d-none');

        if (originalProject === "1") {
            $('.original_project_1').removeClass('d-none');
        } else if (originalProject === "2") {
            $('.original_project_2').removeClass('d-none');
        }
    });

    $('#strategies').on("change", function (e) {
        e.preventDefault();
        const id = $(this).val();

        if (!id) {
            resetDropdownChoices('#target_strategies');
            resetDropdownChoices('#subject_strategies');
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getTargetStrategies&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices('#target_strategies');

                    const $select = $('#target_strategies');
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });



                    initChoices('#target_strategies');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

            $.ajax({
                url: base_url + "/api/master_api.php?action=getSubjectStrategies&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices('#subject_strategies');

                    const $select = $('#subject_strategies');
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices('#subject_strategies');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }        
    });

    $('#plan_under_strategies').on("change", function (e) {
        e.preventDefault();
        const id = $(this).val();
        const $target_level_subject = '#target_level_subject';
        const $sub_plan = '#sub_plan';
        const $target_sub_plan = '#target_sub_plan';
        var $target_sub_plan_id = '';


        if (!id) {
            resetDropdownChoices($target_level_subject);
            resetDropdownChoices($sub_plan);
            resetDropdownChoices($target_sub_plan);
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getTargetLevelSubjects&id=" + id,
                method: "GET",
                async: false,
                dataType: "json",
                success: function(data) {
                    const $select = $($target_level_subject);
                    destroyChoices($target_level_subject);

                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices($target_level_subject);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

            $.ajax({
                url: base_url + "/api/master_api.php?action=getSubPlans&id=" + id,
                method: "GET",
                async: false,
                dataType: "json",
                success: function(data) {
                    const $select = $($sub_plan);
                    destroyChoices($sub_plan);

                    $select.empty();

                    $.each(data, function(index, option) {
                        if(index <= 0) {
                            $target_sub_plan_id = option.id;
                        }

                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices($sub_plan);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

            if($target_sub_plan_id) {
                $.ajax({
                    url: base_url + "/api/master_api.php?action=getTargetSubPlans&id=" + $target_sub_plan_id,
                    method: "GET",
                    async: false,
                    dataType: "json",
                    success: function(data) {
                        const $select = $($target_sub_plan);
                        destroyChoices($target_sub_plan);

                        $select.empty();

                        $.each(data, function(index, option) {
                            $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                        });

                        initChoices($target_sub_plan);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            }
        }        
    });

    $('#sub_plan').on("change", function (e) {
        e.preventDefault();
        const id = $(this).val();
        const $target_sub_plan = '#target_sub_plan';


        if (!id) {
            resetDropdownChoices($target_sub_plan);
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getTargetSubPlans&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    const $select = $($target_sub_plan);
                    destroyChoices($target_sub_plan);

                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices($target_sub_plan);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }        
    });

    $('#plan_strategies').on("change", function (e) {
        e.preventDefault();
        const id = $(this).val();
        const $sub_indicators = '#sub_indicators';
        const $tactics = '#tactics';

        if (!id) {
            resetDropdownChoices($sub_indicators);
            resetDropdownChoices($tactics);
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getSubIndicators&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices($sub_indicators);

                    const $select = $($sub_indicators);
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices($sub_indicators);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

            $.ajax({
                url: base_url + "/api/master_api.php?action=getTactics&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices($tactics);

                    const $select = $($tactics);
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.id + '">' + option.name + '</option>');
                    });

                    initChoices($tactics);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }        
    });

    $(document).on('change', '#project_code', function(e) {  
        e.preventDefault();
        const code = $(this).val();
        const $project_activities = '#activity_id';

        if (!code) {
            resetDropdownChoices($project_activities);
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getProjectActivity&code=" + code,
                method: "GET",
                dataType: "json",
                success: function(data) {

                    destroyChoices($project_activities);

                    const $select = $($project_activities);
                    $select.empty();

                    //$select.append('<option value="">กรุณาเลือก</option>');

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.activity_code + '">' + option.activity_name + '</option>');
                    });

                    initChoices($project_activities);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }
    });

    $('.add_row').click(function(e) {
        e.preventDefault();
        var $table = $(this).closest('.group').find('tbody');
        var $newRow = $table.find('tr:first').clone();

        $newRow.find('input[type="checkbox"]').prop('checked', false);
        $newRow.find('input[type="text"]').val('');

        $table.append($newRow);
    });

    $('.remove_row').click(function(e){
        e.preventDefault();
        var $group = $(this).closest('.group');
        var $tbody = $group.find('tbody');
        var $rows = $tbody.find('tr');
        var $checkedRows = $rows.find('input[type="checkbox"]:checked').closest('tr');

        if ($checkedRows.length >= $rows.length) {
            $checkedRows.not(':first').remove();
            $rows.first().find('input[type="checkbox"]').prop('checked', false);
            $rows.first().find('input[type="text"]').val('');
        } else {
            $checkedRows.remove();
        }
    });

    $('.check_all').click(function() {
        var isChecked = $(this).is(':checked');
        $(this).closest('table').find('tbody input[type="checkbox"]').prop('checked', isChecked);
    });

    $("#activity_id").on("change", function () {
        let selected = $(this).find("option:selected");
        let table = $("#activity_table");

        table.find("tbody.group").not(":first").remove();

        if (selected.length > 0) {
            let firstGroup = table.find("tbody.group").first();
            firstGroup.find(".activity_name").val(selected.eq(0).text());
            firstGroup.attr("data-group", 0);


            selected.each(function (i, el) {
                if (i === 0) return;

                let newGroup = firstGroup.clone();
                newGroup.find("input[type=text]").val("");
                newGroup.find("input[type=number]").val("");
                newGroup.find("input[type=checkbox]").prop("checked", false);
                newGroup.find(".activity_name").val($(el).text());

                let newIndex = table.find("tbody.group").length; 
                newGroup.attr("data-group", newIndex);

                table.find("tfoot").before(newGroup);
            });

            updateRowspan(firstGroup);
        } else {
            table.find("tbody.group").first().find(".activity_name").val("");
        }

        let table2 = $("#expense_table");

        table2.find("tbody.group").not(":first").remove();

        if (selected.length > 0) {
            let firstGroup = table2.find("tbody.group").first();
            firstGroup.find(".expense_name").val(selected.eq(0).text());
            firstGroup.attr("data-group", 0);


            selected.each(function (i, el) {
                if (i === 0) return;

                let newGroup = firstGroup.clone();
                newGroup.find("input[type=text]").val("");
                newGroup.find("input[type=number]").val("");
                newGroup.find("input[type=checkbox]").prop("checked", false);
                newGroup.find(".expense_name").val($(el).text());

                let newIndex = table2.find("tbody.group").length; 
                newGroup.attr("data-group", newIndex);

                table2.find("tfoot").before(newGroup);
            });

            updateRowspanExpense(firstGroup);
        } else {
            table2.find("tbody.group").first().find(".expense_name").val("");
        }

        let table3 = $("#operation_plan");

        table3.find("tbody.group").not(":first").remove();

        if (selected.length > 0) {
            let firstGroup = table3.find("tbody.group").first();
            firstGroup.find(".operation_plan_name").val(selected.eq(0).text());
            firstGroup.attr("data-group", 0);

            selected.each(function (i, el) {
                if (i === 0) return;

                let newGroup = firstGroup.clone();
                newGroup.find("input[type=text]").val("");
                newGroup.find("input[type=number]").val("");
                newGroup.find("input[type=checkbox]").prop("checked", false);
                newGroup.find(".operation_plan_name").val($(el).text());

                let newIndex = table3.find("tbody.group").length; 
                newGroup.attr("data-group", newIndex);

                table3.find("tfoot").before(newGroup);
                //html
                let summaryTbody = $(`
                    <tbody class="summary" data-set="${newIndex}">
                        <tr>
                            <td scope="col" class="text-end"></td>
                            <td scope="col" class="text-end">รวม</td>
                            <td colspan="16">
                                <input class="form-control total_summary_weight_amount" type="number" value="0.00" placeholder="0.00" disabled>
                            </td>
                        </tr>
                    </tbody>
                `);

                newGroup.after(summaryTbody);
            });

            updateRowspanOperation();
        } else {
            table3.find("tbody.group").first().find(".operation_plan_name").val("");
        }
    });


    $(document).on("click", "#activity_table .add_row_table", function (e) {
        e.preventDefault();

        $("#activity_table tbody.group").each(function() {
            let group = $(this);
            let firstRow = group.find("tr:first").clone();

            firstRow.find("td:first").remove();
            firstRow.find("input[type=text]").val("");
            firstRow.find("input[type=number]").val("");
            firstRow.find("input[type=checkbox]").prop("checked", false);

            group.append(firstRow);
            updateRowspan(group);

            calculateRowActivity(firstRow);
        });
    });

    $(document).on("click", "#activity_table .remove_row_table", function(e) {
        e.preventDefault();

        $("#activity_table tbody.group").each(function() {
            let group = $(this);
            let checkedRows = group.find("tr:gt(0)").find("input[type=checkbox]:checked").closest("tr");

            checkedRows.each(function() {
                $(this).remove();
            });

            updateRowspan(group);
            updateRowNames();

            group.find("tr").each(function() {
                calculateRowActivity($(this));
            });

            calculateSummary();
        });
    });

    $(document).on("input", ".input_number", function () {
        let val = $(this).val();

        val = val.replace(/[^0-9.]/g, "");

        let parts = val.split(".");
        if (parts.length > 2) {
            val = parts[0] + "." + parts.slice(1).join("");
        }

        $(this).val(val);
    });

    $(document).on("input", "#activity_table .quantity, #activity_table .price", function () {
        let $row = $(this).closest("tr");
        calculateRowActivity($row);
        calculateSummary();
    });
    //-------- End Activity Plan --------//

    //-------- Expense --------//
    $(document).on("click", "#expense_table .add_row_table", function (e) {
        e.preventDefault();

        $("#expense_table tbody.group").each(function() {
            let group = $(this);
            let firstRow = group.find("tr:eq(1)").clone();

            firstRow.find("input[type=text]").val("");
            firstRow.find("input[type=number]").val("");
            firstRow.find("input[type=checkbox]").prop("checked", false);

            group.append(firstRow);
            updateRowspanExpense(group);

            calculateRowExpense(firstRow);

            calculateSummaryExpense();
        });
    });

    $(document).on("click", "#expense_table .remove_row_table", function(e) {
        e.preventDefault();

        $("#expense_table tbody.group").each(function() {
            let group = $(this);
            let checkedRows = group.find("tr.box:gt(0)").find("input[type=checkbox]:checked").closest("tr");

            checkedRows.each(function() {
                $(this).remove();
            });

            updateRowspanExpense(group);
            updateRowNames();

            group.find("tr.box").each(function() {
                calculateRowExpense($(this));
            });

            calculateSummaryExpense();
        });
    });

    $(document).on("input", "#expense_table .input_number", function () {
        let $row = $(this).closest("tr");

        calculateRowExpense($row);
        calculateSummaryExpense();
    });
    //-------- End Expense --------//
    $(document).on("click", "#operation_plan .add_row_table", function (e) {
        e.preventDefault();

        $("#operation_plan tbody.group").each(function() {
            let group = $(this);
            let firstRow = group.find("tr:eq(1)").clone();

            firstRow.find("input[type=text]").val("");
            firstRow.find("input[type=number]").val("");
            firstRow.find("input[type=checkbox]").prop("checked", false);

            group.append(firstRow);
            updateRowspanOperation(group);

            calculateRowOperation(firstRow);

            updateAllGroupsSummary();
        });
    });

    $(document).on("click", "#operation_plan .remove_row_table", function(e) {
        e.preventDefault();

        $("#operation_plan tbody.group").each(function() {
            let group = $(this);
            let checkedRows = group.find("tr.box:gt(0)").find("input[type=checkbox]:checked").closest("tr");

            checkedRows.each(function() {
                $(this).remove();
            });

            updateRowspanOperation(group);
            updateRowNames();

            group.find("tr.box").each(function() {
                calculateRowOperation($(this));

                updateAllGroupsSummary();
            });
        });
    });

    $(document).on("input", "#operation_plan .input_number", function () {
        let $row = $(this).closest("tr");

        calculateRowOperation($row);
        //calculateSummaryExpense();

         updateAllGroupsSummary();
    });

    $(document).on("input", ".lh, .im", function () {
        let row = $(this).closest("tr");
        let lh = row.find(".lh").val();
        let im = row.find(".im").val();

        lh = lh.replace(/[^1-5]/g, "").slice(0, 1);
        im = im.replace(/[^1-5]/g, "").slice(0, 1);

        row.find(".lh").val(lh);
        row.find(".im").val(im);

        let lhNum = parseInt(lh) || 0;
        let imNum = parseInt(im) || 0;

        // คูณกันแล้วใส่ใน level
        let result = lhNum * imNum;
        row.find(".level").val(result);

        row.find(".level").parents('td').removeClass('bg_warning1');
        row.find(".level").parents('td').removeClass('bg_warning2');
        row.find(".level").parents('td').removeClass('bg_warning3');
        row.find(".level").parents('td').removeClass('bg_warning4');
        row.find(".level").parents('td').removeClass('bg_warning5');

        if(result >= 1 && result <= 2) {
            row.find(".level").parents('td').addClass('bg_warning1');
        } else if(result >= 3 && result <= 4) {
            row.find(".level").parents('td').addClass('bg_warning2');
        } else if(result >= 5 && result <= 9) {
            row.find(".level").parents('td').addClass('bg_warning3');
        } else if(result >= 10 && result <= 16) {
            row.find(".level").parents('td').addClass('bg_warning4');
        } else if(result >= 17 && result <= 25) {
            row.find(".level").parents('td').addClass('bg_warning5');
        }
    });

    $(document).on("input", ".residual_lh, .residual_im", function () {
        let row = $(this).closest("tr");
        let lh = row.find(".residual_lh").val();
        let im = row.find(".residual_im").val();

        lh = lh.replace(/[^1-5]/g, "").slice(0, 1);
        im = im.replace(/[^1-5]/g, "").slice(0, 1);
        row.find(".residual_lh").val(lh);
        row.find(".residual_im").val(im);

        let lhNum = parseInt(lh) || 0;
        let imNum = parseInt(im) || 0;

        let result = lhNum * imNum;
        row.find(".residual_level").val(result);

        row.find(".residual_level").parents('td').removeClass('bg_warning1');
        row.find(".residual_level").parents('td').removeClass('bg_warning2');
        row.find(".residual_level").parents('td').removeClass('bg_warning3');
        row.find(".residual_level").parents('td').removeClass('bg_warning4');
        row.find(".residual_level").parents('td').removeClass('bg_warning5');

        if(result >= 1 && result <= 2) {
            row.find(".residual_level").parents('td').addClass('bg_warning1');
        } else if(result >= 3 && result <= 4) {
            row.find(".residual_level").parents('td').addClass('bg_warning2');
        } else if(result >= 5 && result <= 9) {
            row.find(".residual_level").parents('td').addClass('bg_warning3');
        } else if(result >= 10 && result <= 16) {
            row.find(".residual_level").parents('td').addClass('bg_warning4');
        } else if(result >= 17 && result <= 25) {
            row.find(".residual_level").parents('td').addClass('bg_warning5');
        }
    });

    $(document).on('click', '#budgetForm button[type=submit]', function() {
         $('#actionField').val($(this).val());
    });

    $(document).on("submit", '#budgetForm', function(e) {
        e.preventDefault();
        var action = $('#actionField').val();

        $(this).find(".error-data").removeClass("error-data");

        if (action === 'waiting') {
            const anyRiskChecked = $(this).find(".risk_assessment:checked").length > 0;
            if (anyRiskChecked) {
                $(this).find(".risk_assessment").not(":checked").removeAttr("required");
            } else {
                $(this).find(".risk_assessment").attr("required", true);
            }

            let isValid = true;
            let firstInvalid = null;

            $(this).find("input, select, textarea")
                .not(".d-none input, .d-none select, .d-none textarea")
                .each(function() {
                    if (!this.checkValidity()) {
                        isValid = false;

                        if ($(this).is("select") && $(this).closest(".choices").length) {
                            $(this).closest(".choices").addClass("error-data");
                        } else if ($(this).closest("td").length) {
                            $(this).closest("td").addClass("error-data");
                        } else {
                            $(this).addClass("error-data");
                        }

                        if (!firstInvalid) {
                            firstInvalid = $(this);
                        }
                    }
                });

            $(this).find("input.validate-hidden").each(function() {
                if (!this.value || this.value.trim() === "") {
                    isValid = false;

                    let thParent = $(this).closest("th");
                    thParent.addClass("error-data");

                    if (!firstInvalid) {
                        firstInvalid = thParent;
                    }
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูล',
                    confirmButtonText: 'ตกลง'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (firstInvalid.is("select") && firstInvalid.closest(".choices").length) {
                            firstInvalid.closest(".choices").find("input.choices__input").focus();
                        } else if (firstInvalid.is("th")) {
                            firstInvalid.attr("tabindex", -1).focus();
                        } else {
                            firstInvalid.focus();
                        }

                        $('html, body').animate({
                            scrollTop: firstInvalid.offset().top - 50
                        }, 500);
                    }
                });

                return false;
            }

            Swal.fire({
                title: 'ยืนยันการทำรายการ?',
                text: "คุณต้องการส่งคำขอเพื่ออนุมัติหรือไม่",
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                width: '310px' 
            }).then((result) => {
                if (result.isConfirmed) {
                e.target.submit();
                }
            });
        } else {
            Swal.fire({
                title: 'ยืนยันการทำรายการ?',
                text: "คุณต้องการบันทึกร่างหรือไม่",
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                width: '310px' 
            }).then((result) => {
                if (result.isConfirmed) {
                e.target.submit();
                }
            });
        }
    });


    $('#national_strategy').on("change", function (e) {
        e.preventDefault();
        const id = $(this).val();

        if (!id) {
            resetDropdownChoices('#risk_indicator');
            resetDropdownChoices('#risk_strategy');
        } else {
            $.ajax({
                url: base_url + "/api/master_api.php?action=getRiskStrategiesIndicators&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices('#risk_indicator');

                    const $select = $('#risk_indicator');
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.code + '">' + option.name + '</option>');
                    });



                    initChoices('#risk_indicator');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

            $.ajax({
                url: base_url + "/api/master_api.php?action=getRiskStrategies&id=" + id,
                method: "GET",
                dataType: "json",
                success: function(data) {
                    destroyChoices('#risk_strategy');

                    const $select = $('#risk_strategy');
                    $select.empty();

                    $.each(data, function(index, option) {
                        $select.append('<option value="' + option.code + '">' + option.name + '</option>');
                    });

                    initChoices('#risk_strategy');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }        
    });

    $('#formFile').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        const allowedExtensions = ['pdf', 'doc','docx','zip','xls','xlsx'];
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
        alert('อนุญาติเฉพาะไฟล์ .pdf, .xlsx, .xls, .doc, .docx, .zip เท่านั้น');
        $(this).val('');
        return false;
        }
    });
});
