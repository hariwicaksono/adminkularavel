<template>
  <v-container fluid>
    <!-- Header -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-expand-transition>
            <v-card-text>
              <h5 class="text-h5 font-weight-bold d-flex justify-space-between align-center">
                {{ $t('introduction') }}
                <v-btn icon @click="expanded = !expanded" variant="text" color="primary">
                  <v-icon>{{ expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                </v-btn>
              </h5>
              <div v-show="expanded">
                <p class="text-h6 font-weight-medium mb-3">{{ $t('about_application') }}</p>
                <p class="text-subtitle-1">
                  Aplikasi Web dibuat menggunakan Laravel 12 dan Vue.js 3. Tech stack yang
                  digunakan: PHP 8.1, MySQL, Laravel {{ version }} REST API, VITE, Vue.js v3, Vuetify.js v3, JWT-Auth,
                  Axios, Material Design Icons, Laravel-dompdf, Maatwebsite/excel dan Chart.js.
                </p>
                <p>
                  Aplikasi Web adalah Produk dari ITSHOP Purwokerto yaitu milik dari yang terdaftar di AHU Online
                  dari KEMENKUMHAM RI dan memiliki Legalitas NOMOR INDUK BERUSAHA.
                </p>
                <p>
                  Kunjungi Link Toko Online Official kami:
                </p>
                <ul class="ml-5">
                  <li><a href="https://itshop.biz.id" target="_blank">www.itshop.biz.id</a></li>
                  <li><a href="https://tokopedia.com/itshoppwt" target="_blank">Tokopedia.com/itshoppwt</a></li>
                  <li><a href="https://shopee.co.id/itshoppwt" target="_blank">Shopee.co.id/itshoppwt</a></li>
                  <li><a href="https://toco.id/store/itshop-purwokerto"
                      target="_blank">Toco.id/store/itshop-purwokerto</a></li>
                </ul>
              </div>
            </v-card-text>
          </v-expand-transition>
        </v-card>
      </v-col>
      <v-col cols="12">
        <v-card class="pa-10">
          <v-row align="center" justify="space-between">
            <div>
              <div class="text-h5 font-weight-bold">
                👋 Halo, {{ user?.name }}
                <v-progress-circular v-if="loading" indeterminate size="20" color="primary" class="ml-2" />
              </div>
              <div class="text-subtitle-1 text-grey-darken-1 mt-1">
                {{ $t('welcome_dashboard') }} 🌞
              </div>
            </div>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Summary Cards -->
    <v-row class="mt-6" dense>
      <v-col cols="12" sm="6" md="3" v-for="(item, index) in summaryCards" :key="index">
        <v-card class="pa-4" color="surface" rounded="lg">
          <v-icon size="36" class="mb-2" color="primary">{{ item.icon }}</v-icon>
          <div class="text-h6 font-weight-bold">{{ item.title }}</div>
          <div class="text-caption text-grey-darken-1">{{ item.subtitle }}</div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-6" dense v-if="can('backup.view')">
      <v-col cols="12">
        <v-card>
          <v-card-text>
            <v-row align="center" justify="space-between" no-gutters>
              <!-- Informasi Kiri -->
              <v-col cols="12" md="10">
                <div v-if="loading1">
                  <v-progress-linear indeterminate color="primary" rounded height="4" />
                </div>
                <div v-else>
                  <v-alert :type="backupToday ? 'success' : 'error'" variant="outlined" border="start" class="mt-2"
                    density="compact">
                    {{ backupToday
                      ? "Great, you've done a database backup today. ✅"
                      : "It looks like you haven't done a database backup today. ❌" }}
                  </v-alert>
                </div>
              </v-col>

              <!-- Tombol Backup Kanan -->
              <v-col cols="12" md="2" class="d-flex justify-end align-center mt-3 mt-md-0">
                <v-btn v-if="!backupToday && can('backup.create')" color="primary" @click="doBackup" :loading="doingBackup"
                  prepend-icon="mdi-database-arrow-up">
                  Backup Now
                </v-btn>
                <v-btn v-else color="primary" prepend-icon="mdi-database-check" link to="/backups">
                  See Backups
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts -->
    <v-row class="mt-6">
      <v-col cols="12" md="6">
        <v-card class="pa-4" rounded="lg">
          <v-card-title class="text-h6 mb-2">📈 Chart Line</v-card-title>
          <v-card-subtitle class="text-grey text-caption mb-3"></v-card-subtitle>
          <MyChart />
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card class="pa-4" rounded="lg">
          <v-card-title class="text-h6 mb-2">📊 Chart Bar</v-card-title>
          <v-card-subtitle class="text-grey text-caption mb-3"></v-card-subtitle>
          <MyChart2 />
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/axios'
import { can } from '@/utils/auth'
import MyChart from '@/components/MyChart.vue'
import MyChart2 from '@/components/MyChart2.vue'

const user = ref(null)
const loading = ref(false)
const loading1 = ref(false)
const version = ref('')
const expanded = ref(false) // default tertutup, bisa juga true jika mau expanded awalnya
const backupToday = ref(false)
const doingBackup = ref(false)

const summaryCards = [
  {
    title: 'Rp12.500.000',
    subtitle: 'Total Penjualan',
    icon: 'mdi-cash-multiple',
  },
  {
    title: '320',
    subtitle: 'Pengunjung Hari Ini',
    icon: 'mdi-account-group',
  },
  {
    title: '8',
    subtitle: 'Pesanan Baru',
    icon: 'mdi-cart-plus',
  },
  {
    title: '15',
    subtitle: 'Users',
    icon: 'mdi-account',
  },
]

const fetchMe = async () => {
  loading.value = true
  try {
    const res = await api.get('/me')
    user.value = res.data
  } catch (err) {
    console.error('Failed to load user: ', err)
  } finally {
    loading.value = false
  }
}

const fetchVersion = async () => {
  try {
    const res = await api.get('/laravel-version')
    version.value = res.data
  } catch (err) {
    console.error('Failed to load version: ', err)
  }
}

const checkBackup = async () => {
  loading1.value = true
  try {
    const res = await api.get('/backups/check-today')
    backupToday.value = res.data.status === true
  } catch (error) {
    console.error('Failed to check backup: ', error)
  } finally {
    loading1.value = false
  }
}

const doBackup = async () => {
  doingBackup.value = true
  try {
    const res = await api.post('/backups')
    if (res.data.success) {
      backupToday.value = true
    }
    checkBackup()
  } catch (error) {
    console.error('Failed to Backup: ', error)
  } finally {
    doingBackup.value = false
  }
}

onMounted(async () => {
  fetchMe()
  fetchVersion()
  await checkBackup()
})
</script>
