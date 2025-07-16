<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="text-h5">
            <h2>Halo, {{ user?.name }} 
              <span v-if="loading" class="ml-2"><v-progress-circular indeterminate size="20"></v-progress-circular></span>  
            </h2>
            Selamat Datang di Dashboard 🎉
          </v-card-title>
          <v-card-text>
            Ini adalah halaman dashboard utama aplikasi Anda. Di sini Anda bisa menampilkan statistik, grafik, atau menu
            cepat.
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Informasi 1</v-card-title>
          <v-card-text>
            Konten informasi 1.
            <MyChart />
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Informasi 2</v-card-title>
          <v-card-text>
            Konten informasi 2.
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/axios'
import MyChart from '@/components/MyChart.vue'

const user = ref(null)
const loading = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    const res = await api.get('/me')
    user.value = res.data
  } catch (err) {
    console.error('Gagal ambil user:', err)
  }
  loading.value = false
})
</script>
