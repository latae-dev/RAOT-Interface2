/* Earnings Report Chart */
var element = document.getElementById("courses-earnings");
if (element !== null) {
    element.innerHTML = "";
    var options = {
        series: [{
            name: "Earnings",
            data: [30, 25, 36, 30, 45, 35, 64, 51, 59, 36, 39, 51]
        }, {
            name: "Students",
            data: [33, 21, 32, 37, 23, 32, 47, 31, 54, 32, 20, 38]
        }],
        chart: {
            height: 340,
            type: "bar",
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [1.1, 1.1],
            show: true,
            curve: ['smooth', 'smooth'],
        },
        grid: {
            borderColor: '#f3f3f3',
            strokeDashArray: 3
        },
        xaxis: {
            axisBorder: {
                color: 'rgba(119, 119, 142, 0.05)',
            },
        },
        legend: {
            show: false
        },
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        markers: {
            size: 0
        },
        colors: ["rgb(132, 90, 223)", "#e9e9e9"],
        plotOptions: {
            bar: {
                columnWidth: "50%",
                borderRadius: 2,
            }
        },
    };
    var chart1 = new ApexCharts(document.querySelector("#courses-earnings"), options);
    chart1.render();
}

function earningsReport() {
    chart1.updateOptions({
        colors: ["rgb(" + myVarVal + ")", "#e9e9e9"],
    })
}
/* Earnings Report Chart */

/* Payouts Chart */
var payoutsChartElement = document.getElementById("course-payouts");
var chart2 = null;
var payoutsCategories = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
var payoutsMonthLabels = [];

if (payoutsChartElement !== null) {
    payoutsChartElement.innerHTML = "";

    var payoutsMockPaid = [55, 55, 42, 42, 55, 55, 38, 38, 53, 53, 35, 35];
    var payoutsMockUnpaid = [35, 35, 46, 46, 35, 35, 48, 48, 33, 33, 38, 38];
    var dashboardChartData = window.__DASHBOARD_CHART__ || null;

    if (dashboardChartData) {
        if (Array.isArray(dashboardChartData.approved) && dashboardChartData.approved.length === 12) {
            payoutsMockPaid = dashboardChartData.approved;
        }
        if (Array.isArray(dashboardChartData.pending) && dashboardChartData.pending.length === 12) {
            payoutsMockUnpaid = dashboardChartData.pending;
        }
        if (Array.isArray(dashboardChartData.labels) && dashboardChartData.labels.length === 12) {
            payoutsCategories = dashboardChartData.labels;
        }
        if (Array.isArray(dashboardChartData.month_labels)) {
            payoutsMonthLabels = dashboardChartData.month_labels;
        }
    }

    var payoutsColors = ["rgb(132, 90, 223)", "rgba(230, 83, 60, 0.5)"];

    var options2 = {
        series: [{
            name: 'Paid',
            data: payoutsMockPaid,
            type: 'line',
        }, {
            name: 'UnPaid',
            data: payoutsMockUnpaid,
            type: "line",
        }],
        chart: {
            height: 270,
            toolbar: {
                show: false,
            },
            background: 'none',
            fill: "#fff",
        },
        grid: {
            borderColor: '#f2f6f7',
        },
        colors: payoutsColors,
        background: 'transparent',
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2,
            dashArray: [0, 5],
        },
        legend: {
            show: true,
            position: 'top',
        },
        xaxis: {
            show: false,
            categories: payoutsCategories,
            axisBorder: {
                show: false,
                color: 'rgba(119, 119, 142, 0.05)',
                offsetX: 0,
                offsetY: 0,
            },
            axisTicks: {
                show: false,
                borderType: 'solid',
                color: 'rgba(119, 119, 142, 0.05)',
                width: 6,
                offsetX: 0,
                offsetY: 0
            },
            labels: {
                rotate: -90,
            }
        },
        yaxis: {
            show: false,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            x: {
                formatter: function (value, opts) {
                    var index = opts && typeof opts.dataPointIndex === 'number' ? opts.dataPointIndex : 0;
                    if (payoutsMonthLabels[index]) {
                        return payoutsMonthLabels[index];
                    }
                    return value;
                },
            },
            y: {
                formatter: function (value) {
                    return Math.round(value) + ' รายการ';
                },
            },
        },
    };

    chart2 = new ApexCharts(payoutsChartElement, options2);
    chart2.render();
    window.dashboardPayoutsChart = chart2;
    window.dashboardPayoutsMonthLabels = payoutsMonthLabels;
}

function coursePayouts() {
    if (!chart2) {
        return;
    }

    chart2.updateOptions({
        colors: ["rgb(" + myVarVal + ")", "rgba(230, 83, 60, 0.5)"],
    });
}

window.updateDashboardPayoutsChart = function updateDashboardPayoutsChart(chartData) {
    if (!chart2 || !chartData) {
        return;
    }

    if (Array.isArray(chartData.month_labels)) {
        window.dashboardPayoutsMonthLabels = payoutsMonthLabels = chartData.month_labels;
    }

    chart2.updateOptions({
        xaxis: {
            categories: Array.isArray(chartData.labels) && chartData.labels.length === 12
                ? chartData.labels
                : payoutsCategories,
        },
    });

    chart2.updateSeries([
        {
            name: 'Paid',
            data: chartData.approved || [],
        },
        {
            name: 'UnPaid',
            data: chartData.pending || [],
        },
    ]);
};
/* Payouts Chart */
