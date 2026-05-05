import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from './pages/LandingPage.vue';
import AnalyzePage from './pages/AnalyzePage.vue';
import LoginPage from './pages/LoginPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import ProfilePage from './pages/ProfilePage.vue';
import HistoryPage from './pages/HistoryPage.vue';
import PortalPage from './pages/PortalPage.vue';
import { useAuthStore } from './stores/auth';

const routes = [
    { path: '/', name: 'landing', component: LandingPage, meta: { title: 'Главная' } },
    { path: '/analyze', name: 'analyze', component: AnalyzePage, meta: { title: 'Анализ', requiresAuth: true } },
    { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true, title: 'Вход' } },
    { path: '/register', name: 'register', component: RegisterPage, meta: { guestOnly: true, title: 'Регистрация' } },
    { path: '/portal', name: 'portal', component: PortalPage, meta: { requiresAuth: true, title: 'Портал' } },
    { path: '/profile', name: 'profile', component: ProfilePage, meta: { requiresAuth: true, title: 'Профиль' } },
    { path: '/history', name: 'history', component: HistoryPage, meta: { requiresAuth: true, title: 'Мои анализы' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const auth = useAuthStore();
    const isAuthed = auth.isAuthenticated();

    if (to.meta.requiresAuth && !isAuthed) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && isAuthed) {
        return { name: 'portal' };
    }

    return true;
});

export default router;
