<template>
    <v-container fluid>
        <h1 class="mb-3">News / Informasi</h1>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <v-btn color="primary" @click="openForm()"><v-icon>mdi-plus</v-icon> {{ $t('add') }}</v-btn>
            </v-card-title>

            <v-card-subtitle>
                <v-row class="mt-2">
                    <v-col cols="12" sm="6">
                        <v-text-field v-model="filters.search" label="Cari Judul / Konten"
                            append-inner-icon="mdi-magnify" clearable @keyup.enter="loadData"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-select v-model="filters.jenis" :items="dataJenis" item-title="text" item-value="value"
                            label="Filter Jenis" clearable></v-select>
                    </v-col>
                    <v-col cols="12" sm="2">
                        <v-btn @click="loadData" class="mt-2">Filter</v-btn>
                    </v-col>
                </v-row>
            </v-card-subtitle>

            <v-data-table-server :headers="headers" :items="items" :items-length="totalItems" :loading="loading"
                v-model:options="options" @update:options="loadData">
                <template #item.jenis="{ item }">
                    <v-chip color="info">{{ getJenisText(item.jenis) }}</v-chip>
                </template>

                <template #item.status="{ item }">
                    <v-switch v-model="item.status" :true-value="1" :false-value="0" color="green" density="compact"
                        hide-details @update:model-value="toggleStatus(item)" :loading="loading1">
                        <template #label>
                            <span class="text-caption">
                                {{ item.status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </template>
                    </v-switch>
                </template>

                <template #item.actions="{ item }">
                    <v-btn icon variant="text" @click="openForm(item)" class="mr-2"><v-icon
                            color="primary">mdi-pencil</v-icon></v-btn>
                    <v-btn icon variant="text" @click="confirmDelete(item)"><v-icon
                            color="red">mdi-delete</v-icon></v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- Dialog Add/Edit -->
        <v-dialog v-model="dialog" scrollable max-width="900px">
            <v-card>
                <v-card-title class="d-flex justify-space-between align-center">
                    {{ editedItem.id ? 'Edit' : 'Tambah' }} News
                    <v-btn variant="text" icon="mdi-close" @click="dialog = false"></v-btn>
                </v-card-title>

                <v-card-text>
                    <v-text-field v-model="editedItem.judul" label="Judul" :error-messages="errors.judul"
                        class="mb-2"></v-text-field>

                    <v-select v-model="editedItem.jenis" :items="dataJenis" item-title="text" item-value="value"
                        label="Jenis" :error-messages="errors.jenis" class="mb-2"></v-select>

                    <v-textarea v-model="editedItem.konten" label="Konten" rows="4" :error-messages="errors.konten"
                        class="mb-2"></v-textarea>

                    <v-text-field v-model="editedItem.tanggal" label="Tanggal" type="date"
                        :error-messages="errors.tanggal" class="mb-2"></v-text-field>

                    <v-switch v-model="editedItem.status" label="Aktif" color="success"></v-switch>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="dialog = false">{{ $t('cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" @click="saveItem"
                        :loading="loading1"><v-icon>mdi-content-save</v-icon>
                        {{ $t('save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Konfirmasi Delete -->
        <v-dialog v-model="dialogDelete.visible" max-width="400">
            <v-card>
                <v-card-title class="text-h5">{{ $t('confirmation') }} {{ $t('delete') }}</v-card-title>
                <v-card-text>
                    {{ $t('confirm_delete') }} "<strong>{{ dialogDelete.item?.judul }}</strong>"?
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialogDelete.visible = false">{{ $t('cancel') }}</v-btn>
                    <v-btn color="red" variant="flat" @click="deleteItem">
                        <v-icon>mdi-delete</v-icon>
                        {{ $t('delete') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import api from "@/axios";
import { useSnackbar } from '@/stores/snackbar'

const dataJenis = [
    { text: "News Ticker", value: "1" },
    { text: "Informasi", value: "2" },
    { text: "Masjid", value: "3" },
];

const headers = [
    { title: "ID", key: "id", width: 70 },
    { title: "Judul", key: "judul", width: 250 },
    { title: "Jenis", key: "jenis", width: 130 },
    { title: "Tanggal", key: "tanggal", width: 130 },
    { title: "Status", key: "status", width: 100 },
    { title: "Aksi", key: "actions", sortable: false, width: 130 },
];

const items = ref([]);
const totalItems = ref(0);
const loading = ref(false);
const loading1 = ref(false);
const errors = ref({})
const snackbar = useSnackbar()

const filters = ref({
    jenis: "",
    search: "",
});

const options = ref({
    page: 1,
    itemsPerPage: 10,
    sortBy: [{ key: 'created_at', order: 'desc' }], // Ini adalah format yang diharapkan oleh v-data-table-server
});

const dialog = ref(false);
const editedItem = reactive({
    id: null,
    judul: "",
    jenis: "",
    konten: "",
    tanggal: "",
    status: true,
});

const loadData = async () => {
    loading.value = true;
    const sortBy = options.value.sortBy.length > 0 ? options.value.sortBy[0].key : 'created_at'
    const sortOrder = options.value.sortBy.length > 0 ? options.value.sortBy[0].order : 'desc'

    try {
        const { data } = await api.get("/news", {
            params: {
                page: options.value.page,
                itemsPerPage: options.value.itemsPerPage,
                sortBy: sortBy, // Kirim key kolom
                sortDesc: sortOrder === 'desc' ? 'true' : 'false', // Kirim 'true' atau 'false'
                search: filters.value.search,
                jenis: filters.value.jenis,
            },
        });
        items.value = data.data;
        totalItems.value = data.total;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const openForm = (item = null) => {
    if (item) {
        Object.assign(editedItem, {
            ...item,
            jenis: String(item.jenis),  // ← konversi agar cocok dengan value number di dataJenis
            status: item.status === 1 || item.status === true, // pastikan boolean
        });
    } else {
        Object.assign(editedItem, {
            id: null,
            judul: "",
            jenis: null,
            konten: "",
            tanggal: "",
            status: true,
        });
    }

    errors.value = {};
    dialog.value = true;
};

const saveItem = async () => {
    loading1.value = true;
    try {
        if (editedItem.id) {
            await api.put(`/news/${editedItem.id}`, editedItem);
        } else {
            await api.post("/news", editedItem);
        }
        dialog.value = false;
        await loadData();
    } catch (error) {
        console.error(error);
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        }
        snackbar.showSnackbar(error.response.data.message)
        loading1.value = false;
    } finally {
        loading1.value = false;
    }
};

const toggleStatus = async (item) => {
    loading1.value = true;
    try {
        const res = await api.put(`/news/${item.id}/toggle-status`, {
            status: item.status
        })
        snackbar.showSnackbar(res.data.message)
    } catch (error) {
        // rollback kalau gagal
        item.status = item.status ? 0 : 1
        console.error(error)
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        }
        snackbar.showSnackbar(error.response.data.message)
        loading1.value = false;
    } finally {
        loading1.value = false;
    }
}

// Modal Konfirmasi Delete
const dialogDelete = reactive({
    visible: false,
    item: null,
})

const confirmDelete = (item) => {
    dialogDelete.item = item
    dialogDelete.visible = true
}

const deleteItem = async () => {
    try {
        await api.delete(`/news/${dialogDelete.item.id}`);
        dialogDelete.visible = false
        await loadData();
    } catch (error) {
        console.error(error);
    }
};

const getJenisText = (val) => {
    const found = dataJenis.find((j) => j.value == val);
    return found ? found.text : "-";
};

onMounted(loadData);
</script>
