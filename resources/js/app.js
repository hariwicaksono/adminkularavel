import './bootstrap';
import { createApp, watch } from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import '@mdi/font/css/materialdesignicons.css';
import { i18n } from './i18n';
import { initAuth } from './utils/auth';
import helpers from './utils/format'

const app = createApp(App);

// Ambil setting site_name dari API
fetch('/api/settings/app')
  .then(res => res.json())
  .then(data => {
    const appName = data.site_name || 'My App'
    app.config.globalProperties.$appName = appName

    // SET TITLE dengan i18n
    router.beforeEach((to, from, next) => {
      const key = to.meta.title || 'untitled'
      const translatedTitle = i18n.global.t(key)
      document.title = `${translatedTitle} - ${appName}`
      next()
    })

    app.use(router).use(vuetify).use(i18n).use(initAuth).use(helpers).mount('#app')
  })
  .catch(() => {
    const appName = 'My App'
    app.config.globalProperties.$appName = appName

    router.beforeEach((to, from, next) => {
      const key = to.meta.title || 'untitled'
      const translatedTitle = i18n.global.t(key)
      document.title = `${translatedTitle} - ${appName}`
      next()
    })

    app.use(router).use(vuetify).use(i18n).use(initAuth).use(helpers).mount('#app')
  });

watch(() => i18n.global.locale.value, () => {
  const route = router.currentRoute.value
  const titleKey = route.meta.title || 'untitled'
  const translated = i18n.global.t(titleKey)
  document.title = `${translated} - ${app.config.globalProperties.$appName}`
})
