<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const email = ref('');
const password = ref('');
const errorText = ref('');
const loading = ref(false);

async function submit() {
    errorText.value = '';
    loading.value = true;
    try {
        await auth.loginUser({
            email: email.value,
            password: password.value,
        });
        router.push(String(route.query.redirect || '/portal'));
    } catch (error) {
        errorText.value = error.message || 'Не удалось выполнить вход.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="page-shell app">
        <section class="card auth-card">
            <h2>Вход</h2>
            <p class="card-note">Войдите, чтобы сохранять диагнозы в историю.</p>
            <p v-if="errorText" class="error">{{ errorText }}</p>
            <form class="auth-form" @submit.prevent="submit">
                <label>Email</label>
                <input v-model="email" type="email" required />
                <label>Пароль</label>
                <input v-model="password" type="password" required />
                <button type="submit" class="primary-btn" :disabled="loading">
                    {{ loading ? 'Входим...' : 'Войти' }}
                </button>
            </form>
        </section>
    </main>
</template>
