$(function () {
    if (typeof $.fn.DataTable === 'undefined') {
        return;
    }

    const $table = $('#datatable-basic');
    if (!$table.length || $.fn.DataTable.isDataTable($table)) {
        return;
    }

    $table.DataTable({
        language: {
            searchPlaceholder: 'Search...',
            sSearch: '',
        },
        pageLength: 10,
    });
});
