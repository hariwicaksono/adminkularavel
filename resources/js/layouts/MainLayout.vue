<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" :rail="rail" :temporary="display.xs.value" app width="240">
      <v-list dense>
        <v-list-item :prepend-avatar="siteLogo" :title="siteApp">
          <span v-if="loading" class="ml-2">
            <v-progress-circular indeterminate size="20"></v-progress-circular>
          </span>
        </v-list-item>
      </v-list>
      <v-divider></v-divider>

      <v-list nav>
        <!-- Dashboard -->
        <v-list-item to="/" exact router :title="!rail ? 'Dashboard' : ''">
          <template #prepend><v-icon>mdi-view-dashboard</v-icon></template>
          <template v-if="!rail" #title>{{ $t('dashboard') }}</template>
        </v-list-item>

        <!-- Users Group -->
        <v-list-group value="users" prepend-icon="mdi-account-multiple" :sub-title="!rail ? $t('users') : ''"
          v-if="can('user.view')">
          <template #activator="{ props }">
            <v-list-item v-bind="props" :title="t('users')" />
          </template>

          <v-list-item to="/users" router prepend-icon="mdi-account" :title="!rail ? $t('users') : ''"
            v-if="can('user.view')" />
          <v-list-item to="/roles" router prepend-icon="mdi-account-check" :title="!rail ? 'Roles' : ''"
            v-if="can('role.view')" />
          <v-list-item to="/permissions" router prepend-icon="mdi-account-details" :title="!rail ? 'Permissions' : ''"
            v-if="can('permission.view')" />
        </v-list-group>

        <!-- System / Admin Tools Group -->
        <v-list-group value="system" prepend-icon="mdi-cog-box" :sub-title="!rail ? $t('system') : ''"
          v-if="can('log.view') || can('backup.view') || can('setting.update')">
          <template #activator="{ props }">
            <v-list-item v-bind="props" :title="t('system')" />
          </template>

          <v-list-item to="/logs" router prepend-icon="mdi-database-eye" :title="!rail ? $t('log_activity') : ''"
            v-if="can('log.view')" />
          <v-list-item to="/backups" router prepend-icon="mdi-database" :title="!rail ? 'Backup DB' : ''"
            v-if="can('backup.view')" />
          <v-list-item to="/settings" router prepend-icon="mdi-cog" :title="!rail ? $t('settings') : ''"
            v-if="can('setting.update')" />
        </v-list-group>

        <!-- Logout -->
        <v-list-item @click="confirmLogout" :title="!rail ? 'Logout' : ''">
          <template #prepend><v-icon>mdi-logout</v-icon></template>
          <template v-if="!rail" #title>{{ $t('logout') }}</template>
        </v-list-item>
      </v-list>

      <!-- Tombol toggle rail di bawah drawer -->
      <template v-slot:append>
        <v-divider></v-divider>
        <v-list-item @click="toggleRail" class="cursor-pointer">
          <template #prepend>
            <v-icon>{{ rail ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon>
          </template>
          <v-list-item-title>{{ rail ? 'Expand' : 'Collapse' }}</v-list-item-title>
        </v-list-item>
      </template>
    </v-navigation-drawer>

    <v-app-bar app color="primary" dark>
      <v-app-bar-nav-icon @click="toggleDrawer" v-show="!drawer || !display.smAndDown.value" />
      <v-toolbar-title></v-toolbar-title>
      <v-spacer />

      <!-- Notification -->
      <v-menu offset-y location="bottom left">
        <template #activator="{ props }">
          <v-btn icon variant="text" v-bind="props" class="d-flex align-center text-white mr-4" >
            <v-avatar size="32"><v-icon large color="white">mdi-bell</v-icon></v-avatar>
          </v-btn>
        </template>
        <v-card>
          
        </v-card>
      </v-menu>

      <!-- User dropdown -->
      <v-menu offset-y location="bottom left">
        <template #activator="{ props }">
          <v-btn variant="text" v-bind="props" class="d-flex align-center text-white mr-4">
            <v-avatar size="32" class="mr-2"><v-img :src="`https://ui-avatars.com/api/?name=${user?.name}`" /></v-avatar>
            <span class="mr-1">{{ user?.email }}
              <span v-if="loading1" class="ml-2"><v-progress-circular indeterminate
                  size="20"></v-progress-circular></span>
            </span>
            <v-icon size="20">mdi-menu-down</v-icon>
          </v-btn>
        </template>
        <v-card>
          <v-list>
            <v-list-item link to="/profile" prepend-icon="mdi-account">
              <v-list-item-title>{{ $t('my_profile') }}</v-list-item-title>
            </v-list-item>
            <v-list-item link to="/settings" prepend-icon="mdi-cog" v-if="can('setting.update')">
              <v-list-item-title>{{ $t('settings') }}</v-list-item-title>
            </v-list-item>
          </v-list>
          <v-divider />
          <v-list>
            <v-list-item @click="confirmLogout" prepend-icon="mdi-logout">
              <v-list-item-title>{{ $t('logout') }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>

      <!-- Theme + Language -->
      <v-menu offset-y location="bottom left" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn icon variant="text" v-bind="props" class="d-flex align-center text-white mr-5">
            <v-avatar size="32"><v-icon large color="white">mdi-cog</v-icon></v-avatar>
          </v-btn>
        </template>
        <v-card>
          <v-list>
            <v-list-item @click="toggleDark" :prepend-icon="isDark ? 'mdi-weather-night' : 'mdi-white-balance-sunny'">
              <v-list-item-title>{{ isDark ? 'Dark Mode' : 'Light Mode' }}</v-list-item-title>
            </v-list-item>
          </v-list>
          <v-divider />
          <v-card-text>
            <v-select v-model="locale" :items="languages" item-title="label" item-value="code"
              @update:modelValue="changeLang" density="compact" variant="outlined" hide-details label="Bahasa" />
          </v-card-text>
        </v-card>
      </v-menu>
    </v-app-bar>

    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
      <v-footer app padless class="justify-center">
        <v-col class="text-center text-caption py-2" cols="12">
          &copy; {{ new Date().getFullYear() }} <a href="https://itshop.biz.id" target="_blank">
            {{ appCompany }}
          </a>. Software {{ appName }} {{ appVersion }} . All rights reserved
        </v-col>
      </v-footer>

      <v-dialog v-model="dialogLogout" max-width="400">
        <v-card>
            <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('logout') }}</v-card-title>
            <v-card-text>
                {{ $t('confirm_logout') }}?
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="dialogLogout = false">{{ $t('cancel') }}</v-btn>
                <v-btn color="red" variant="flat" @click="logout" :loading="loading2">
                    <v-icon>mdi-logout</v-icon>
                    {{ $t('logout') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    </v-main>
  </v-app>
</template>

<script setup>
import '../../css/admin.css'
import { ref, onMounted, watch } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { initLogout, can } from '@/utils/auth'

const snackbar = useSnackbar()
const router = useRouter()
const drawer = ref(true)
const rail = ref(false)
const isDark = ref(false)
const theme = useTheme()
const { locale, t } = useI18n()
const user = ref(null)
const siteApp = ref(null)
const siteLogo = ref(null)
const loading = ref(false)
const loading1 = ref(false)
const loading2 = ref(false)
const display = useDisplay()
const dialogLogout = ref(false)
const appName = import.meta.env.VITE_APP_NAME
const appCompany = import.meta.env.VITE_APP_COMPANY
const appVersion = 'v' + import.meta.env.VITE_APP_VERSION

const toggleRail = () => {
  rail.value = !rail.value
  localStorage.setItem('rail', JSON.stringify(rail.value))
}

const toggleDrawer = () => {
  drawer.value = !drawer.value
}

const languages = [
  { label: 'ID', code: 'id' },
  { label: 'EN', code: 'en' }
]

const changeLang = (lang) => {
  locale.value = lang
  localStorage.setItem('lang', lang)
}

const toggleDark = () => {
  isDark.value = !isDark.value
  theme.global.name.value = isDark.value ? 'dark' : 'light'
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

onMounted(async () => {
  // Load theme & lang
  const savedTheme = localStorage.getItem('theme')
  const savedLang = localStorage.getItem('lang')
  isDark.value = savedTheme === 'dark'
  theme.global.name.value = savedTheme || 'light'
  if (savedLang) locale.value = savedLang

  // Load rail mode
  const savedRail = localStorage.getItem('rail')
  if (savedRail !== null) {
    rail.value = JSON.parse(savedRail)
  } else {
    rail.value = display.smAndDown.value
  }

  // Set drawer terbuka hanya jika bukan mobile
  drawer.value = !display.smAndDown.value

  // Load site info
  loading.value = true
  try {
    const res = await api.get('/settings/app')
    siteApp.value = res.data.site_name
    siteLogo.value = res.data.site_logo
  } catch (e) {
    console.error('Failed to load settings:', e)
    snackbar.showSnackbar(e || 'Failed to load settings')
  }
  loading.value = false

  // Load user info
  loading1.value = true
  try {
    const res = await api.get('/me')
    user.value = res.data
  } catch (err) {
    console.error('Failed to load user:', err)
  }
  loading1.value = false
})

// Auto rail mode on resize
watch(
  () => display.smAndDown.value,
  (isMobile) => {
    const manualRail = localStorage.getItem('rail')
    if (manualRail === null) {
      rail.value = isMobile
    }
  },
  { immediate: true }
)

function confirmLogout() {
    dialogLogout.value = true
}

const logout = async () => {
  loading2.value = true
  try {
    const response = await api.post('/logout')
    snackbar.showSnackbar(response.data.message, 'success')
    loading2.value = false
  } catch (err) {
    console.warn('Logout failed:', err)
  }
  initLogout()
  router.push('/login')
}
</script>

<style scoped>
/* Pastikan ini mengatasi align left untuk konten item daftar */
.v-list-item .v-list-item__content {
  justify-content: flex-start !important;
  text-align: left !important;
}

/* Jika ikon dan judul juga perlu rata kiri, ini bisa membantu */
.v-list-item__prepend,
.v-list-item__title {
  justify-content: flex-start !important;
  text-align: left !important;
}

/* Khusus untuk v-list-group yang mungkin punya indentasi */
.v-list-group .v-list-item {
  padding-left: 8px !important;
  /* Indentasi standar untuk submenu */
}

.v-navigation-drawer--rail .v-list-group .v-list-item {
  width: 40px !important;
  /* Indentasi standar untuk submenu */
}

.v-navigation-drawer--rail .v-list-group .v-list-item:hover {
  width: 40px !important;
  /* Indentasi standar untuk submenu */
}

.v-navigation-drawer--rail .v-list-group .v-list-item.v-list-item--active {
  width: 40px !important;
  /* Indentasi standar untuk submenu */
}
</style>