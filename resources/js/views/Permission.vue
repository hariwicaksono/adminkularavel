<template>
  <v-container fluid>
    <h1 class="mb-3">{{ $t('permission') }}</h1>
    <v-row>
      <v-col cols="12" sm="6">
        <v-text-field v-model="newPermission" label="Permission baru" />
      </v-col>
      <v-col cols="12" sm="3">
        <v-btn @click="createPermission" color="primary" :loading="loading1">Tambah</v-btn>
      </v-col>
    </v-row>

    <v-data-table :headers="headers" :items="permissions" class="elevation-1" :loading="loading">
      <template #item.updated_at="{ item }">
        {{ $t('created') }}: {{ $helpers.formatDate(item.updated_at) }}<br />
        {{ $t('updated') }}: {{ $helpers.formatDate(item.updated_at) }}
      </template>
      <template #item.actions="{ item }">
        <v-btn icon variant="text" @click="deletePermission(item.id)">
          <v-icon color="red">mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { useI18n } from 'vue-i18n'

const loading = ref(false)
const loading1 = ref(false)
const snackbar = useSnackbar()
const { t } = useI18n();

const permissions = ref([])
const newPermission = ref('')
const headers = [
  { title: 'Permission', key: 'name' },
  { title: t('date'), key: 'updated_at' },
  { title: 'Aksi', key: 'actions', sortable: false }
]

const fetchPermissions = async () => {
  loading.value = true
  const res = await api.get('/permissions')
  permissions.value = res.data
  loading.value = false
}

const createPermission = async () => {
  if (!newPermission.value) return
  loading1.value = true
  const res = await api.post('/permissions', { name: newPermission.value })
  newPermission.value = ''
  loading1.value = false
  snackbar.showSnackbar(res.data.message)
  fetchPermissions()
}

const deletePermission = async (id) => {
  if (confirm('Are you sure you want to delete this permission?')) {
    const res = await api.delete(`/permissions/${id}`)
    snackbar.showSnackbar(res.data.message)
    fetchPermissions()
  }
}

onMounted(fetchPermissions)
</script>
