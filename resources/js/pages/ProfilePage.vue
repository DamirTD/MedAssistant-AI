<script setup>
import { onMounted, ref } from 'vue';
import { getProfile, updateEmail, updatePassword } from '../services/api';

const loading = ref(true);
const errorText = ref('');
const successText = ref('');
const profile = ref(null);

const currentPasswordForEmail = ref('');
const newEmail = ref('');
const currentPasswordForPassword = ref('');
const newPassword = ref('');
const newPasswordConfirmation = ref('');
const submittingEmail = ref(false);
const submittingPassword = ref(false);

async function loadProfile() {
    loading.value = true;
    errorText.value = '';
    try {
        profile.value = await getProfile();
        newEmail.value = profile.value.user.email;
    } catch (error) {
        errorText.value = error.message || 'Не удалось загрузить профиль.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadProfile);

async function submitEmail() {
    successText.value = '';
    errorText.value = '';
    submittingEmail.value = true;
    try {
        await updateEmail({
            current_password: currentPasswordForEmail.value,
            new_email: newEmail.value,
        });
        currentPasswordForEmail.value = '';
        successText.value = 'Email успешно обновлен.';
        await loadProfile();
    } catch (error) {
        errorText.value = error.message || 'Не удалось обновить email.';
    } finally {
        submittingEmail.value = false;
    }
}

async function submitPassword() {
    successText.value = '';
    errorText.value = '';
    submittingPassword.value = true;
    try {
        await updatePassword({
            current_password: currentPasswordForPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: newPasswordConfirmation.value,
        });
        currentPasswordForPassword.value = '';
        newPassword.value = '';
        newPasswordConfirmation.value = '';
        successText.value = 'Пароль успешно обновлен.';
    } catch (error) {
        errorText.value = error.message || 'Не удалось обновить пароль.';
    } finally {
        submittingPassword.value = false;
    }
}
</script>

<template>
    <main class="page-shell app">
        <section class="card">
            <h2>Профиль пользователя</h2>
            <p v-if="loading" class="card-note">Загружаем профиль...</p>
            <p v-else-if="errorText" class="error">{{ errorText }}</p>
            <template v-else>
                <p v-if="successText" class="profile-success">{{ successText }}</p>
                <p v-if="errorText" class="error">{{ errorText }}</p>

                <div class="profile-grid">
                    <article class="result-card">
                        <p class="card-label">Имя</p>
                        <h3 class="card-title">{{ profile.user.name }}</h3>
                        <p class="card-note">Почта: {{ profile.user.email }}</p>
                        <p class="card-note">Регистрация: {{ new Date(profile.user.created_at).toLocaleDateString() }}</p>
                    </article>
                    <article class="result-card">
                        <p class="card-label">Аналитика</p>
                        <h3 class="card-title">{{ profile.stats.analyses_count }}</h3>
                        <p class="card-note">Последний диагноз: {{ profile.stats.last_diagnosis || 'пока нет' }}</p>
                    </article>
                </div>

                <div class="profile-grid">
                    <article class="result-card">
                        <p class="card-label">Изменить email</p>
                        <form class="auth-form" @submit.prevent="submitEmail">
                            <label>Новый email</label>
                            <input v-model="newEmail" type="email" required />
                            <label>Текущий пароль</label>
                            <input v-model="currentPasswordForEmail" type="password" required />
                            <button type="submit" class="primary-btn" :disabled="submittingEmail">
                                {{ submittingEmail ? 'Сохраняем...' : 'Обновить email' }}
                            </button>
                        </form>
                    </article>

                    <article class="result-card">
                        <p class="card-label">Изменить пароль</p>
                        <form class="auth-form" @submit.prevent="submitPassword">
                            <label>Текущий пароль</label>
                            <input v-model="currentPasswordForPassword" type="password" required />
                            <label>Новый пароль</label>
                            <input v-model="newPassword" type="password" minlength="8" required />
                            <label>Подтверждение нового пароля</label>
                            <input v-model="newPasswordConfirmation" type="password" minlength="8" required />
                            <button type="submit" class="primary-btn" :disabled="submittingPassword">
                                {{ submittingPassword ? 'Сохраняем...' : 'Обновить пароль' }}
                            </button>
                        </form>
                    </article>
                </div>
            </template>
        </section>
    </main>
</template>
