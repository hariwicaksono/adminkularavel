import './bootstrap';
import { ref, createApp, watch } from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import '@mdi/font/css/materialdesignicons.css';
import { i18n } from './i18n';
import { initAuth } from './utils/auth';
import helpers from './utils/format';
import eventBus from './eventBus';

const app = createApp(App);

// Gunakan ref agar bisa direaktifkan
const appName = ref('My App');

// Tambahkan ke globalProperties
app.config.globalProperties.$appName = appName;
app.config.globalProperties.$eventBus = eventBus;

// Fetch setting dari API
fetch('/api/settings/app')
  .then(res => res.json())
  .then(data => {
    if (data.site_name) appName.value = data.site_name;

    // Atur router title dinamis
    router.beforeEach((to, from, next) => {
      const key = to.meta.title || 'untitled';
      const translatedTitle = i18n.global.t(key);
      document.title = `${translatedTitle} - ${appName.value}`;
      next();
    });

    app.use(router).use(vuetify).use(i18n).use(initAuth).use(helpers).mount('#app');
  });

// Tangkap perubahan dari komponen lain
eventBus.on('settings-updated', (data) => {
  if (data.site_name) {
    appName.value = data.site_name;

    const route = router.currentRoute.value;
    const titleKey = route.meta.title || 'untitled';
    const translated = i18n.global.t(titleKey);
    document.title = `${translated} - ${appName.value}`;
  }
});

// Update title saat bahasa berubah
watch(() => i18n.global.locale.value, () => {
  const route = router.currentRoute.value;
  const titleKey = route.meta.title || 'untitled';
  const translated = i18n.global.t(titleKey);
  document.title = `${translated} - ${appName.value}`;
});
