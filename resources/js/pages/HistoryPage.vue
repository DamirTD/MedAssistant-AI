<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getHistory } from '../services/api';

const router = useRouter();
const loading = ref(true);
const errorText = ref('');
const history = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);

async function loadHistory(page = 1) {
    loading.value = true;
    errorText.value = '';
    try {
        const response = await getHistory(page);
        history.value = response.data || [];
        currentPage.value = Number(response.current_page || page);
        lastPage.value = Number(response.last_page || 1);
    } catch (error) {
        errorText.value = error.message || 'Не удалось загрузить историю.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => loadHistory(1));

function diagnosisOf(item) {
    return item?.response_payload?.diagnosis || 'Без диагноза';
}

function payload(item) {
    return item?.response_payload || {};
}

function urgencyText(item) {
    return payload(item).urgency || 'не указана';
}

function confidenceText(item) {
    return payload(item).confidence || 'не указана';
}

function severityText(item) {
    return payload(item).severity || 'не указана';
}

function goToAnalyze() {
    router.push('/analyze');
}
</script>

<template>
    <main class="page-shell app">
        <section class="card">
            <h2>История запросов</h2>
            <p class="card-note">Здесь сохраняется только ключевая информация по анализам.</p>
            <div class="portal-actions">
                <button type="button" class="ghost-btn" @click="goToAnalyze">Начать новый анализ</button>
                <button type="button" class="ghost-btn" @click="loadHistory(currentPage)">Обновить список</button>
            </div>

            <p v-if="loading" class="card-note">Загружаем историю...</p>
            <p v-else-if="errorText" class="error">{{ errorText }}</p>
            <p v-else-if="!history.length" class="card-note">Пока нет сохраненных анализов.</p>

            <div v-else class="history-list">
                <article v-for="item in history" :key="item.id" class="result-card">
                    <p class="card-label">Запрос #{{ item.id }}</p>
                    <h3 class="card-title">{{ diagnosisOf(item) }}</h3>
                    <p class="card-note">{{ payload(item).about || item.description || 'Описание не указано' }}</p>

                    <div class="history-meta-row">
                        <span class="history-chip">Дата: {{ new Date(item.created_at).toLocaleString() }}</span>
                        <span class="history-chip">Срочность: {{ urgencyText(item) }}</span>
                        <span class="history-chip">Уверенность: {{ confidenceText(item) }}</span>
                        <span class="history-chip">Тяжесть: {{ severityText(item) }}</span>
                    </div>
                </article>
            </div>
            <div v-if="!loading && lastPage > 1" class="history-pagination">
                <button type="button" class="ghost-btn" :disabled="currentPage <= 1" @click="loadHistory(currentPage - 1)">
                    Назад
                </button>
                <span>Страница {{ currentPage }} из {{ lastPage }}</span>
                <button type="button" class="ghost-btn" :disabled="currentPage >= lastPage" @click="loadHistory(currentPage + 1)">
                    Далее
                </button>
            </div>
        </section>
    </main>
</template>
