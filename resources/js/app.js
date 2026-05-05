import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const auth = useAuthStore();

auth.initAuth().finally(() => {
    app.use(router).mount('#app');
});
