<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const isAuthed = computed(() => auth.isAuthenticated());
const showWorkspaceLayout = computed(() => isAuthed.value && route.meta.requiresAuth);
const currentTitle = computed(() => String(route.meta.title || 'Раздел'));

const navItems = [
    { to: '/portal', label: 'Портал' },
    { to: '/analyze', label: 'Начать анализ' },
    { to: '/history', label: 'Мои анализы' },
    { to: '/profile', label: 'Профиль' },
];

async function logout() {
    await auth.logoutUser();
    router.push('/login');
}
</script>

<template>
    <div v-if="showWorkspaceLayout" class="workspace-layout">
        <aside class="workspace-sidebar">
            <p class="workspace-brand">MedAssistant AI</p>
            <nav class="workspace-nav">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    class="workspace-link"
                    :class="{ active: route.path === item.to }"
                >
                    {{ item.label }}
                </RouterLink>
            </nav>
            <button type="button" class="workspace-logout" @click="logout">Выйти</button>
        </aside>

        <div class="workspace-content">
            <header class="workspace-header">
                <h1>{{ currentTitle }}</h1>
                <p v-if="auth.state.user" class="workspace-user">{{ auth.state.user.name }}</p>
            </header>
            <router-view />
        </div>
    </div>

    <router-view v-else />
</template>
