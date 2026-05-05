<script setup>
import * as echarts from 'echarts';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getPortal } from '../services/api';

const router = useRouter();
const loading = ref(true);
const errorText = ref('');
const portal = ref(null);
const byDayChartEl = ref(null);
const urgencyChartEl = ref(null);
const diagnosesChartEl = ref(null);

let byDayChart = null;
let urgencyChart = null;
let diagnosesChart = null;

function renderEmptyLineChart(instance, title) {
    instance.setOption({
        title: { text: title, left: 'center', textStyle: { fontSize: 14 } },
        tooltip: { trigger: 'axis' },
        grid: { left: 40, right: 20, top: 40, bottom: 30 },
        xAxis: { type: 'category', data: ['Нет данных'] },
        yAxis: { type: 'value', min: 0, max: 1 },
        series: [{ type: 'line', data: [0], smooth: true, areaStyle: {} }],
    });
}

function renderEmptyPieChart(instance, title) {
    instance.setOption({
        title: { text: title, left: 'center', textStyle: { fontSize: 14 } },
        tooltip: { trigger: 'item' },
        legend: { bottom: 0 },
        series: [{
            type: 'pie',
            radius: ['45%', '72%'],
            data: [{ name: 'Нет данных', value: 1 }],
            label: { formatter: 'Нет данных' },
            itemStyle: { color: '#d6dbe8' },
        }],
    });
}

function renderEmptyBarChart(instance, title) {
    instance.setOption({
        title: { text: title, left: 'center', textStyle: { fontSize: 14 } },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: 120, right: 20, top: 40, bottom: 20 },
        xAxis: { type: 'value', min: 0, max: 1 },
        yAxis: { type: 'category', data: ['Нет данных'] },
        series: [{ type: 'bar', data: [0], barMaxWidth: 24 }],
    });
}

function renderCharts() {
    if (!portal.value) return;
    if (!byDayChartEl.value || !urgencyChartEl.value || !diagnosesChartEl.value) return;

    if (!byDayChart) byDayChart = echarts.init(byDayChartEl.value);
    if (!urgencyChart) urgencyChart = echarts.init(urgencyChartEl.value);
    if (!diagnosesChart) diagnosesChart = echarts.init(diagnosesChartEl.value);

    const requestsByModel = portal.value.chart_series?.requests_by_model || [];
    const modelShare = portal.value.chart_series?.model_share || [];
    const byDayRaw = portal.value.chart_series?.requests_by_model_by_day || [];

    if (!requestsByModel.length) {
        renderEmptyBarChart(byDayChart, 'Запросы по ИИ-моделям');
    } else {
        byDayChart.setOption({
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            grid: { left: 60, right: 20, top: 24, bottom: 30 },
            xAxis: { type: 'value', minInterval: 1 },
            yAxis: { type: 'category', data: requestsByModel.map((item) => item.model) },
            series: [{
                data: requestsByModel.map((item) => item.count),
                type: 'bar',
                barMaxWidth: 24,
            }],
        });
    }

    if (!modelShare.length) {
        renderEmptyPieChart(urgencyChart, 'Доля запросов по моделям');
    } else {
        urgencyChart.setOption({
            tooltip: { trigger: 'item' },
            legend: { bottom: 0 },
            series: [{
                type: 'pie',
                radius: ['45%', '72%'],
                data: modelShare.map((item) => ({
                    name: item.model,
                    value: item.count,
                })),
            }],
        });
    }

    if (!byDayRaw.length) {
        renderEmptyLineChart(diagnosesChart, 'Динамика запросов по моделям');
    } else {
        const days = byDayRaw.map((item) => item.day);
        const modelSet = new Set();
        byDayRaw.forEach((entry) => {
            (entry.models || []).forEach((modelRow) => modelSet.add(modelRow.model));
        });
        const models = Array.from(modelSet);

        const series = models.map((model) => ({
            name: model,
            type: 'line',
            smooth: true,
            data: byDayRaw.map((entry) => {
                const row = (entry.models || []).find((m) => m.model === model);
                return row ? row.count : 0;
            }),
        }));

        diagnosesChart.setOption({
            tooltip: { trigger: 'axis' },
            legend: { bottom: 0 },
            grid: { left: 50, right: 20, top: 20, bottom: 50 },
            xAxis: { type: 'category', data: days },
            yAxis: { type: 'value', minInterval: 1 },
            series,
        });
    }
}

function handleResize() {
    byDayChart?.resize();
    urgencyChart?.resize();
    diagnosesChart?.resize();
}

onMounted(async () => {
    try {
        portal.value = await getPortal();
        loading.value = false;
        await nextTick();
        renderCharts();
        window.addEventListener('resize', handleResize);
    } catch (error) {
        errorText.value = error.message || 'Не удалось загрузить портал.';
    } finally {
        if (loading.value) {
            loading.value = false;
        }
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
    byDayChart?.dispose();
    urgencyChart?.dispose();
    diagnosesChart?.dispose();
});

function go(route) {
    router.push(route);
}
</script>

<template>
    <main class="page-shell app">
        <section class="card">
            <h2>Портал пользователя</h2>
            <p class="card-note">Быстрый доступ к основным разделам.</p>

            <p v-if="loading" class="card-note">Загружаем данные...</p>
            <p v-else-if="errorText" class="error">{{ errorText }}</p>

            <template v-else>
                <div class="portal-actions">
                    <button
                        v-for="action in portal.quick_actions"
                        :key="action.id"
                        type="button"
                        class="primary-btn portal-action-btn"
                        @click="go(action.route)"
                    >
                        {{ action.title }}
                    </button>
                </div>

                <div class="profile-grid">
                    <article class="result-card">
                        <p class="card-label">Всего анализов</p>
                        <h3 class="card-title">{{ portal.stats.analyses_count }}</h3>
                        <p class="card-note">Последний диагноз: {{ portal.stats.last_diagnosis || 'пока нет' }}</p>
                    </article>
                    <article class="result-card">
                        <p class="card-label">Последний анализ</p>
                        <h3 class="card-title">{{ portal.stats.last_analysis_at ? new Date(portal.stats.last_analysis_at).toLocaleString() : 'нет данных' }}</h3>
                        <p class="card-note">Откройте «Мои анализы», чтобы посмотреть детали.</p>
                    </article>
                </div>

                <div class="portal-charts-grid">
                    <article class="result-card">
                        <p class="card-label">Запросы по ИИ-моделям</p>
                        <div ref="byDayChartEl" class="portal-chart"></div>
                    </article>
                    <article class="result-card">
                        <p class="card-label">Доля запросов по моделям</p>
                        <div ref="urgencyChartEl" class="portal-chart"></div>
                    </article>
                    <article class="result-card portal-chart-wide">
                        <p class="card-label">Динамика запросов по моделям</p>
                        <div ref="diagnosesChartEl" class="portal-chart"></div>
                    </article>
                </div>
            </template>
        </section>
    </main>
</template>
