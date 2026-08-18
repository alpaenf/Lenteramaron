<script setup>
import { ref } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Users, 
    Plus, 
    Search, 
    Edit, 
    Trash2, 
    X, 
    ShieldCheck, 
    UserCheck, 
    GraduationCap, 
    User as UserIcon,
    KeyRound,
    Mail,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
    availableRoles: Array,
});

const page = usePage();
const authUser = page.props.auth?.user || {};

const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

const filter = () => {
    router.get('/users', {
        search: search.value,
        role: selectedRole.value,
    }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: 'Pustakawan',
    password: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = '';
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post('/users', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        },
    });
};

const submitUpdate = () => {
    form.match ? form.put(`/users/${editingUser.value.id}`, {
        onSuccess: () => {
            isEditOpen.value = false;
            editingUser.value = null;
            form.reset();
        },
    }) : form.post(`/users/${editingUser.value.id}`, {
        _method: 'put',
        onSuccess: () => {
            isEditOpen.value = false;
            editingUser.value = null;
            form.reset();
        },
    });
};

const deleteUser = (user) => {
    if (user.id === authUser.id) {
        alert('Anda tidak dapat menghapus akun Anda sendiri.');
        return;
    }
    if (confirm(`Apakah Anda yakin ingin menghapus akun "${user.name}" (${user.email})?`)) {
        router.delete(`/users/${user.id}`);
    }
};

const getRoleBadgeClass = (role) => {
    const roleLower = (role || '').toLowerCase();
    switch (roleLower) {
        case 'admin':
            return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'pustakawan':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'kepala sekolah':
        case 'kepala_sekolah':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'guru':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Pengguna</h1>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Kelola hak akses pengguna, akun staf perpustakaan, guru, dan pengawas sistem.</p>
                </div>
                <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2 self-start sm:self-auto">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <Users class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengguna</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ stats.total || 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                        <ShieldCheck class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Admin & Pustakawan</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ stats.admin_pustakawan || 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <GraduationCap class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Guru & Kepsek</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ stats.guru_kepsek || 0 }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <UserCheck class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peneliti / User</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ stats.peneliti_user || 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative w-full md:w-80">
                    <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input 
                        v-model="search"
                        @keyup.enter="filter"
                        type="text" 
                        placeholder="Cari nama atau email..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    />
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select 
                        v-model="selectedRole" 
                        @change="filter"
                        class="w-full md:w-48 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 transition"
                    >
                        <option value="">Semua Peran / Role</option>
                        <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
                    </select>

                    <button @click="filter" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-5">Pengguna</th>
                                <th class="py-3.5 px-5">Email</th>
                                <th class="py-3.5 px-5">Peran Sistem</th>
                                <th class="py-3.5 px-5">Tanggal Dibuat</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-black text-xs shadow-xs shrink-0">
                                            {{ getInitials(u.name) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ u.name }}</p>
                                            <span v-if="u.id === authUser.id" class="inline-block text-[10px] text-blue-600 font-extrabold bg-blue-50 px-2 py-0.5 rounded-full mt-0.5">
                                                (Akun Anda)
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-mono text-slate-600">
                                    {{ u.email }}
                                </td>
                                <td class="py-4 px-5">
                                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border', getRoleBadgeClass(u.role)]">
                                        {{ u.role }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-slate-500">
                                    {{ new Date(u.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openEditModal(u)" title="Edit Pengguna" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-600 hover:text-blue-600 transition">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteUser(u)" 
                                            :disabled="u.id === authUser.id" 
                                            title="Hapus Pengguna"
                                            :class="['p-1.5 rounded-lg transition', u.id === authUser.id ? 'opacity-30 cursor-not-allowed text-slate-400' : 'hover:bg-rose-50 text-slate-600 hover:text-rose-600']"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <UserIcon class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="font-bold">Tidak ada data pengguna ditemukan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500 font-medium">
                        Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{ users.total }} pengguna
                    </p>
                    <div class="flex items-center gap-1">
                        <component 
                            v-for="(link, i) in users.links" 
                            :key="i"
                            :is="link.url ? 'Link' : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                                link.active ? 'bg-blue-600 text-white' : link.url ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 pointer-events-none'
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- Modal Create User -->
            <div v-if="isCreateOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-lg font-black text-slate-900">Tambah Pengguna Baru</h3>
                        <button @click="isCreateOpen = false" class="p-1 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" required placeholder="Masukkan nama..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                            <input v-model="form.email" type="email" required placeholder="nama@sekolah.sch.id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran / Role Sistem</label>
                            <select v-model="form.role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500">
                                <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
                            </select>
                            <p v-if="form.errors.role" class="text-rose-500 text-[11px] mt-1">{{ form.errors.role }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi (Password)</label>
                            <input v-model="form.password" type="password" required placeholder="Minimal 8 karakter..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500" />
                            <p v-if="form.errors.password" class="text-rose-500 text-[11px] mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-md shadow-blue-500/20">
                                Simpan Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Edit User -->
            <div v-if="isEditOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-lg font-black text-slate-900">Edit Pengguna</h3>
                        <button @click="isEditOpen = false" class="p-1 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitUpdate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                            <input v-model="form.email" type="email" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500" />
                            <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran / Role Sistem</label>
                            <select v-model="form.role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500">
                                <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
                            </select>
                            <p v-if="form.errors.role" class="text-rose-500 text-[11px] mt-1">{{ form.errors.role }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru (Opsional)</label>
                            <input v-model="form.password" type="password" placeholder="Biarkan kosong jika tidak diubah..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500" />
                            <p v-if="form.errors.password" class="text-rose-500 text-[11px] mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="isEditOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-md shadow-blue-500/20">
                                Perbarui Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
