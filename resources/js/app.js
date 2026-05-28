
import Alpine from 'alpinejs';

window.Alpine = Alpine;

let apexChartsLoader;

const loadApexCharts = async () => {
    apexChartsLoader ??= import('apexcharts').then((module) => module.default);

    return apexChartsLoader;
};

const rupiahFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const parseChartPayload = (element) => {
    try {
        return JSON.parse(element.dataset.chartPayload || '{}');
    } catch {
        return {};
    }
};

const hasChartData = (payload) => {
    if (Array.isArray(payload.series)) {
        return payload.series.some((series) => {
            if (typeof series === 'number') {
                return series > 0;
            }

            return Array.isArray(series.data) && series.data.some((value) => Number(value) > 0);
        });
    }

    return false;
};

const tooltipXFormatter = (payload) => (value, options) => {
    const index = options?.dataPointIndex;

    return payload.tooltipLabels?.[index] || value;
};

const baseChartOptions = {
    chart: {
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 500,
        },
    },
    dataLabels: { enabled: false },
    legend: { show: false },
    tooltip: {
        theme: 'light',
        y: {
            formatter: (value) => rupiahFormatter.format(Number(value || 0)),
        },
    },
    noData: {
        text: 'No data yet',
        align: 'center',
        verticalAlign: 'middle',
        style: {
            color: '#72777E',
            fontSize: '13px',
            fontFamily: 'Inter, sans-serif',
        },
    },
};

const chartOptions = {
    donut: (payload) => ({
        ...baseChartOptions,
        chart: {
            ...baseChartOptions.chart,
            type: 'donut',
            height: 160,
            sparkline: { enabled: true },
        },
        labels: payload.labels || [],
        series: payload.series || [],
        colors: payload.colors?.length ? payload.colors : ['#0D8B7D', '#093C5D', '#3B7597', '#6FD1D7'],
        stroke: {
            width: 4,
            colors: ['#FFFFFF'],
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: { show: false },
                },
            },
        },
        tooltip: {
            ...baseChartOptions.tooltip,
            y: {
                formatter: (value) => rupiahFormatter.format(Number(value || 0)),
            },
        },
    }),
    line: (payload) => {
        const labels = Array.isArray(payload.labels) ? payload.labels : [];
        const labelCount = labels.length;
        const showMarkers = labelCount <= 12;

        return ({
        ...baseChartOptions,
        chart: {
            ...baseChartOptions.chart,
            type: 'area',
            height: 192,
            parentHeightOffset: 0,
            dropShadow: {
                enabled: true,
                top: 4,
                blur: 8,
                color: '#0D8B7D',
                opacity: 0.3,
            },
        },
        series: payload.series || [],
        colors: ['#007A53'],
        stroke: {
            curve: 'smooth',
            width: 5,
            lineCap: 'round',
        },
        grid: {
            borderColor: '#CFE0E5',
            strokeDashArray: 6,
            padding: { top: 14, right: 12, bottom: 8, left: 8 },
        },
        markers: {
            size: showMarkers ? 4 : 0,
            colors: ['#FFFFFF'],
            strokeColors: '#007A53',
            strokeWidth: 3,
            hover: { size: 6 },
        },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                show: false,
            },
        },
        tooltip: {
            ...baseChartOptions.tooltip,
            x: {
                formatter: tooltipXFormatter(payload),
            },
        },
        yaxis: {
            labels: { show: false },
            min: 0,
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.36,
                opacityTo: 0.04,
                stops: [0, 78, 100],
            },
        },
        });
    },
    bar: (payload) => {
        const labels = Array.isArray(payload.labels) ? payload.labels : [];
        const labelStep = labels.length > 14 ? Math.ceil(labels.length / 7) : 1;
        const displayLabels = labels.map((label, index) => (index % labelStep === 0 ? label : ''));

        return ({
        ...baseChartOptions,
        chart: {
            ...baseChartOptions.chart,
            type: 'bar',
            height: 208,
            parentHeightOffset: 0,
        },
        series: payload.series || [],
        colors: ['#007A53', '#3B7597'],
        plotOptions: {
            bar: {
                borderRadius: 7,
                borderRadiusApplication: 'end',
                columnWidth: '48%',
            },
        },
        grid: {
            borderColor: '#DCE8EB',
            strokeDashArray: 6,
            padding: { top: 8, right: 4, bottom: 0, left: 4 },
        },
        xaxis: {
            categories: displayLabels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    colors: '#72777E',
                    fontSize: '11px',
                    fontWeight: 700,
                },
            },
        },
        tooltip: {
            ...baseChartOptions.tooltip,
            x: {
                formatter: tooltipXFormatter(payload),
            },
        },
        yaxis: {
            labels: { show: false },
        },
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '12px',
            fontWeight: 700,
            labels: { colors: '#3C4A42' },
            markers: { size: 5, strokeWidth: 0 },
        },
        });
    },
};

const initializeApexCharts = async () => {
    const elements = document.querySelectorAll('[data-chart-engine="ApexCharts"]');

    if (elements.length === 0) {
        return;
    }

    const ApexCharts = await loadApexCharts();

    elements.forEach((element) => {
        if (element.dataset.chartInitialized === 'true') {
            return;
        }

        const payload = parseChartPayload(element);
        const type = element.dataset.chartType;
        const factory = chartOptions[type];

        if (!factory) {
            return;
        }

        if (!hasChartData(payload)) {
            element.innerHTML = '<div class="flex h-full min-h-40 items-center justify-center text-sm font-semibold text-[#72777E]">No chart data yet</div>';
            element.dataset.chartInitialized = 'true';
            return;
        }

        element.innerHTML = '';

        const chart = new ApexCharts(element, factory(payload));
        chart.render();
        element.dataset.chartInitialized = 'true';
    });
};

document.addEventListener('DOMContentLoaded', initializeApexCharts);

Alpine.start();
