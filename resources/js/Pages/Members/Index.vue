<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Users, Plus, Search, FileSpreadsheet, Edit, Trash2, X, Phone, UserCheck } from 'lucide-vue-next';

const props = defineProps({
    members: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const className = ref(props.filters.class_name || '');
const status = ref(props.filters.status || '');

const filter = () => {
    router.get('/members', {
        search: search.value,
        class_name: className.value,
        status: status.value,
    }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingMember = ref(null);

const form = useForm({
    nis: '',
    name: '',
    class_name: 'Kelas 5A',
    gender: 'L',
    address: '',
    parent_name: '',
    parent_phone: '',
    status: 'Aktif',
});

const openCreateModal = () => {
    form.reset();
    isCreateOpen.value = true;
};

const openEditModal = (member) => {
    editingMember.value = member;
    form.nis = member.nis;
    form.name = member.name;
    form.class_name = member.class_name;
    form.gender = member.gender;
    form.address = member.address || '';
    form.parent_name = member.parent_name || '';
    form.parent_phone = member.parent_phone || '';
    form.status = member.status;
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post('/members', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        },
    });
};

const submitUpdate = () => {
    form.patch(`/members/${editingMember.value.id}`, {
        onSuccess: () => {
            isEditOpen.value = false;
            editingMember.value = null;
            form.reset();
        },
    });
};

const deleteMember = (member) => {
    if (confirm(`Hapus data anggota siswa "${member.name}"?`)) {
        router.delete(`/members/${member.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Data Anggota Siswa</h1>
                    <p class="text-xs text-slate-500 mt-1">Direktori siswa terdaftar sebagai anggota perpustakaan SD Negeri 02 Maron.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/members/export-excel" target="_blank" class="px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold transition-all flex items-center gap-2">
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                    <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        <span>Tambah Anggota</span>
                    </button>
                </div>
            </div>

            <!-- Controls -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-80">
                    <input v-model="search" @keyup.enter="filter" type="text" placeholder="Cari NIS / Nama Siswa..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select v-model="className" @change="filter" class="py-2 px-3 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="">Semua Kelas</option>
                        <option value="Kelas 1">Kelas 1</option>
                        <option value="Kelas 1A">Kelas 1A</option>
                        <option value="Kelas 1B">Kelas 1B</option>
                        <option value="Kelas 1C">Kelas 1C</option>
                        <option value="Kelas 2">Kelas 2</option>
                        <option value="Kelas 2A">Kelas 2A</option>
                        <option value="Kelas 2B">Kelas 2B</option>
                        <option value="Kelas 2C">Kelas 2C</option>
                        <option value="Kelas 3">Kelas 3</option>
                        <option value="Kelas 3A">Kelas 3A</option>
                        <option value="Kelas 3B">Kelas 3B</option>
                        <option value="Kelas 3C">Kelas 3C</option>
                        <option value="Kelas 4">Kelas 4</option>
                        <option value="Kelas 4A">Kelas 4A</option>
                        <option value="Kelas 4B">Kelas 4B</option>
                        <option value="Kelas 4C">Kelas 4C</option>
                        <option value="Kelas 5">Kelas 5</option>
                        <option value="Kelas 5A">Kelas 5A</option>
                        <option value="Kelas 5B">Kelas 5B</option>
                        <option value="Kelas 5C">Kelas 5C</option>
                        <option value="Kelas 6">Kelas 6</option>
                        <option value="Kelas 6A">Kelas 6A</option>
                        <option value="Kelas 6B">Kelas 6B</option>
                        <option value="Kelas 6C">Kelas 6C</option>
                        <option value="Guru / Staf">Guru / Staf</option>
                    </select>
                    <select v-model="status" @change="filter" class="py-2 px-3 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">NIS</th>
                                <th class="p-4">Nama Lengkap &amp; L/P</th>
                                <th class="p-4">Kelas</th>
                                <th class="p-4">Orang Tua &amp; No. HP</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="member in members.data" :key="member.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4 font-mono font-bold text-blue-900">{{ member.nis }}</td>
                                <td class="p-4 font-bold text-slate-900">
                                    <span>{{ member.name }}</span>
                                    <span class="text-[10px] text-slate-400 block font-normal">Jenis Kelamin: {{ member.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ member.class_name }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <span class="flex items-center gap-1.5 font-medium"><UserCheck class="w-3.5 h-3.5 text-slate-400 shrink-0" /> {{ member.parent_name || '-' }}</span>
                                    <span class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5"><Phone class="w-3 h-3 text-slate-400 shrink-0" /> {{ member.parent_phone || '-' }}</span>
                                </td>
                                <td class="p-4">
                                    <span :class="[
                                        member.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500',
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold'
                                    ]">
                                        {{ member.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(member)" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteMember(member)" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50">
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
                        <h3 class="font-extrabold text-slate-900 text-base">{{ isCreateOpen ? 'Tambah Anggota Siswa' : 'Edit Data Anggota' }}</h3>
                        <button @click="isCreateOpen = false; isEditOpen = false" class="p-1 rounded-lg text-slate-400"><X class="w-5 h-5" /></button>
                    </div>

                    <form @submit.prevent="isCreateOpen ? submitCreate() : submitUpdate()" class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">NIS <span class="text-rose-500">*</span></label>
                                <input v-model="form.nis" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Kelas <span class="text-rose-500">*</span></label>
                                <input 
                                    v-model="form.class_name" 
                                    list="classListOptions" 
                                    type="text" 
                                    required 
                                    placeholder="Ketik atau pilih (misal: Kelas 5C)" 
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white" 
                                />
                                <datalist id="classListOptions">
                                    <option value="Kelas 1"></option>
                                    <option value="Kelas 1A"></option>
                                    <option value="Kelas 1B"></option>
                                    <option value="Kelas 1C"></option>
                                    <option value="Kelas 1D"></option>
                                    <option value="Kelas 2"></option>
                                    <option value="Kelas 2A"></option>
                                    <option value="Kelas 2B"></option>
                                    <option value="Kelas 2C"></option>
                                    <option value="Kelas 2D"></option>
                                    <option value="Kelas 3"></option>
                                    <option value="Kelas 3A"></option>
                                    <option value="Kelas 3B"></option>
                                    <option value="Kelas 3C"></option>
                                    <option value="Kelas 3D"></option>
                                    <option value="Kelas 4"></option>
                                    <option value="Kelas 4A"></option>
                                    <option value="Kelas 4B"></option>
                                    <option value="Kelas 4C"></option>
                                    <option value="Kelas 4D"></option>
                                    <option value="Kelas 5"></option>
                                    <option value="Kelas 5A"></option>
                                    <option value="Kelas 5B"></option>
                                    <option value="Kelas 5C"></option>
                                    <option value="Kelas 5D"></option>
                                    <option value="Kelas 6"></option>
                                    <option value="Kelas 6A"></option>
                                    <option value="Kelas 6B"></option>
                                    <option value="Kelas 6C"></option>
                                    <option value="Kelas 6D"></option>
                                    <option value="Guru / Staf"></option>
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                            <input v-model="form.name" type="text" required class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                <select v-model="form.gender" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Status Keanggotaan</label>
                                <select v-model="form.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Orang Tua / Wali</label>
                                <input v-model="form.parent_name" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">No. HP Orang Tua</label>
                                <input v-model="form.parent_phone" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Alamat</label>
                            <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200"></textarea>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false; isEditOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-md">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
