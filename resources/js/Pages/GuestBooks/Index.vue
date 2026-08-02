<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ClipboardList, Plus, Search, FileSpreadsheet, Printer, Edit, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
    guests: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const date = ref(props.filters.date || '');

const filter = () => {
    router.get('/guest-books', {
        search: search.value,
        date: date.value,
    }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingGuest = ref(null);

const form = useForm({
    name: '',
    institution: '',
    purpose: 'Membaca Buku',
    feedback: '',
    note: '',
    date: new Date().toISOString().substr(0, 10),
    time: new Date().toTimeString().substr(0, 5),
});

const openCreateModal = () => {
    form.reset();
    isCreateOpen.value = true;
};

const openEditModal = (guest) => {
    editingGuest.value = guest;
    form.name = guest.name;
    form.institution = guest.institution;
    form.purpose = guest.purpose;
    form.feedback = guest.feedback || '';
    form.note = guest.note || '';
    form.date = guest.date;
    form.time = guest.time;
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post('/guest-books', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        },
    });
};

const submitUpdate = () => {
    form.patch(`/guest-books/${editingGuest.value.id}`, {
        onSuccess: () => {
            isEditOpen.value = false;
            editingGuest.value = null;
            form.reset();
        },
    });
};

const deleteGuest = (guest) => {
    if (confirm(`Hapus catatan pengunjung "${guest.name}"?`)) {
        router.delete(`/guest-books/${guest.id}`);
    }
};

const printGuestBook = () => {
    window.print();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const cleanStr = String(dateStr).split('T')[0];
    const parts = cleanStr.split('-');
    if (parts.length === 3) {
        const [year, month, day] = parts;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        const mIdx = parseInt(month, 10) - 1;
        if (mIdx >= 0 && mIdx < 12) {
            return `${day} ${months[mIdx]} ${year}`;
        }
    }
    return dateStr;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buku Tamu & Pengunjung</h1>
                    <p class="text-xs text-slate-500 mt-1">Catatan kehadiran siswa, guru, pengawas, dan tamu umum perpustakaan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="printGuestBook" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-bold transition-all flex items-center gap-2">
                        <Printer class="w-4 h-4" />
                        <span>Cetak Buku Tamu</span>
                    </button>
                    <a href="/guest-books/export-excel" target="_blank" class="px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold transition-all flex items-center gap-2">
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                    <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        <span>Catat Kunjungan</span>
                    </button>
                </div>
            </div>

            <!-- Controls -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
                <div class="relative w-full sm:w-80">
                    <input v-model="search" @keyup.enter="filter" type="text" placeholder="Cari Nama / Instansi / Keperluan..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
                <div>
                    <input v-model="date" @change="filter" type="date" class="py-2 px-3 rounded-xl border border-slate-200 text-xs bg-white" />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">No. Pengunjung</th>
                                <th class="p-4">Nama & Instansi</th>
                                <th class="p-4">Keperluan</th>
                                <th class="p-4">Kesan / Pesan</th>
                                <th class="p-4">Tanggal & Jam</th>
                                <th class="p-4 text-right print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="g in guests.data" :key="g.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4 font-mono font-bold text-blue-900">{{ g.visitor_no }}</td>
                                <td class="p-4 font-bold text-slate-900">
                                    <span>{{ g.name }}</span>
                                    <span class="text-[10px] text-slate-500 block font-normal">{{ g.institution }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700">
                                        {{ g.purpose }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600 italic max-w-xs">
                                    <span>"{{ g.feedback || '-' }}"</span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <span class="font-bold text-slate-900">{{ formatDate(g.date) }}</span>
                                    <span class="block text-[10px] text-slate-400">Jam {{ g.time }}</span>
                                </td>
                                <td class="p-4 text-right print:hidden">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(g)" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteGuest(g)" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Create / Edit -->
            <div v-if="isCreateOpen || isEditOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">{{ isCreateOpen ? 'Catat Kunjungan Baru' : 'Edit Catatan Buku Tamu' }}</h3>
                        <button @click="isCreateOpen = false; isEditOpen = false" class="p-1 rounded-lg text-slate-400"><X class="w-5 h-5" /></button>
                    </div>

                    <form @submit.prevent="isCreateOpen ? submitCreate() : submitUpdate()" class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Pengunjung <span class="text-rose-500">*</span></label>
                                <input v-model="form.name" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Instansi / Kelas <span class="text-rose-500">*</span></label>
                                <input v-model="form.institution" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Keperluan <span class="text-rose-500">*</span></label>
                            <input v-model="form.purpose" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal</label>
                                <input v-model="form.date" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Jam</label>
                                <input v-model="form.time" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Kesan & Pesan</label>
                            <textarea v-model="form.feedback" rows="2" class="w-full p-2.5 rounded-xl border border-slate-200"></textarea>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false; isEditOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">Batal</button>
                            <button :disabled="form.processing" type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
