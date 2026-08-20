import ApexCharts from 'apexcharts';

const COR_AREA = '#0f766e';
const COR_AREA_FILL = '#14b8a6';

let chartProducao = null;
let chartEvolucao = null;

function readPayloads() {
    const root = document.getElementById('analise-grafica-payloads');
    if (!root) {
        return { producao: null, evolucao: null };
    }

    return {
        producao: JSON.parse(root.dataset.producao || '{}'),
        evolucao: JSON.parse(root.dataset.evolucao || '{}'),
    };
}

function optionsProducao(payload) {
    return {
        chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false },
            parentHeightOffset: 0,
        },
        series: [{
            name: 'Projeto CAD',
            data: payload.totals || [],
        }],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '45%',
                distributed: true,
                borderRadius: 4,
            },
        },
        colors: payload.colors || [],
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: {
            categories: payload.categories || [],
            labels: {
                style: { fontSize: '12px' },
            },
        },
        yaxis: {
            labels: {
                formatter: (value) => Math.round(value).toString(),
            },
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 3,
        },
        tooltip: {
            y: {
                formatter: (value) => `${value} postes`,
            },
        },
    };
}

function optionsEvolucao(payload) {
    return {
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            parentHeightOffset: 0,
            zoom: { enabled: false },
        },
        series: [{
            name: 'Projeto CAD',
            data: payload.totals || [],
        }],
        colors: [COR_AREA],
        fill: {
            type: 'solid',
            opacity: 0.25,
            colors: [COR_AREA_FILL],
        },
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        markers: {
            size: 5,
            colors: [COR_AREA],
            strokeColors: '#ffffff',
            strokeWidth: 2,
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: {
            categories: payload.categories || [],
            title: { text: 'Semana' },
        },
        yaxis: {
            labels: {
                formatter: (value) => Math.round(value).toString(),
            },
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 3,
        },
        tooltip: {
            y: {
                formatter: (value) => `${value} postes`,
            },
        },
    };
}

function syncProducaoVisibility(payload) {
    const chartEl = document.getElementById('chart-producao-colaborador');
    const emptyEl = document.getElementById('chart-producao-colaborador-vazio');
    const hasData = (payload.totals || []).length > 0;

    if (chartEl) {
        chartEl.classList.toggle('hidden', !hasData);
    }
    if (emptyEl) {
        emptyEl.classList.toggle('hidden', hasData);
    }

    return hasData;
}

async function renderOrUpdateProducao(payload) {
    const el = document.getElementById('chart-producao-colaborador');
    if (!el) {
        return;
    }

    const hasData = syncProducaoVisibility(payload);
    if (!hasData) {
        if (chartProducao) {
            chartProducao.destroy();
            chartProducao = null;
        }
        return;
    }

    const options = optionsProducao(payload);

    if (chartProducao) {
        await chartProducao.updateOptions(options, true, true);
        return;
    }

    chartProducao = new ApexCharts(el, options);
    await chartProducao.render();
}

async function renderOrUpdateEvolucao(payload) {
    const el = document.getElementById('chart-evolucao-semanal');
    if (!el) {
        return;
    }

    const options = optionsEvolucao(payload);

    if (chartEvolucao) {
        await chartEvolucao.updateOptions(options, true, true);
        return;
    }

    chartEvolucao = new ApexCharts(el, options);
    await chartEvolucao.render();
}

async function syncCharts() {
    const { producao, evolucao } = readPayloads();
    if (!producao || !evolucao) {
        return;
    }

    await Promise.all([
        renderOrUpdateProducao(producao),
        renderOrUpdateEvolucao(evolucao),
    ]);
}

document.addEventListener('DOMContentLoaded', () => {
    syncCharts();
});

document.addEventListener('livewire:init', () => {
    Livewire.on('graficos-dashboard-atualizados', (event) => {
        const detail = Array.isArray(event) ? event[0] : event;
        const producao = detail?.producao ?? readPayloads().producao;
        const evolucao = detail?.evolucao ?? readPayloads().evolucao;

        if (!producao || !evolucao) {
            return;
        }

        Promise.all([
            renderOrUpdateProducao(producao),
            renderOrUpdateEvolucao(evolucao),
        ]);
    });
});
