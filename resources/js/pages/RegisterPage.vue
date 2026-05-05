<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const errorText = ref('');
const loading = ref(false);

async function submit() {
    errorText.value = '';
    loading.value = true;
    try {
        await auth.registerUser({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        router.push('/portal');
    } catch (error) {
        errorText.value = error.message || 'Не удалось выполнить регистрацию.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="page-shell app">
        <section class="card auth-card">
            <h2>Регистрация</h2>
            <p v-if="errorText" class="error">{{ errorText }}</p>
            <form class="auth-form" @submit.prevent="submit">
                <label>Имя</label>
                <input v-model="name" type="text" required />
                <label>Email</label>
                <input v-model="email" type="email" required />
                <label>Пароль</label>
                <input v-model="password" type="password" required minlength="8" />
                <label>Повторите пароль</label>
                <input v-model="passwordConfirmation" type="password" required minlength="8" />
                <button type="submit" class="primary-btn" :disabled="loading">
                    {{ loading ? 'Создаем...' : 'Создать аккаунт' }}
                </button>
            </form>
        </section>
    </main>
</template>
