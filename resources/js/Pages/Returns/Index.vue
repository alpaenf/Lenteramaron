<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { RotateCcw, Plus, Search, FileSpreadsheet, CheckCircle, AlertCircle, X, Check } from 'lucide-vue-next';

const props = defineProps({
    returns: Object,
    active_borrowings: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const filter = () => {
    router.get('/returns', { search: search.value }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);
const today = new Date().toISOString().substr(0, 10);

const form = useForm({
    borrowing_id: '',
    return_date: today,
    condition: 'Baik',
    note: '',
});

// Searchable Active Borrowings Combobox
const borrowingSearch = ref('');
const isBorrowingDropdownOpen = ref(false);

const filteredActiveBorrowings = computed(() => {
    if (!props.active_borrowings) return [];
    if (!borrowingSearch.value.trim()) return props.active_borrowings.slice(0, 50);
    const q = borrowingSearch.value.toLowerCase();
    return props.active_borrowings.filter(ab => 
        (ab.transaction_no && ab.transaction_no.toLowerCase().includes(q)) ||
        (ab.member?.name && ab.member.name.toLowerCase().includes(q)) ||
        (ab.member?.nis && ab.member.nis.toLowerCase().includes(q)) ||
        (ab.book?.title && ab.book.title.toLowerCase().includes(q))
    ).slice(0, 50);
});

const selectBorrowing = (borrowing) => {
    form.borrowing_id = borrowing.id;
    isBorrowingDropdownOpen.value = false;
};

const openCreateModal = () => {
    form.reset();
    borrowingSearch.value = '';
    isBorrowingDropdownOpen.value = false;
    form.return_date = today;
    isCreateOpen.value = true;
};

const selectedBorrowing = computed(() => {
    return props.active_borrowings.find(b => b.id == form.borrowing_id);
});

const calculatedLateDays = computed(() => {
    if (!selectedBorrowing.value || !form.return_date) return 0;
    const rDate = new Date(form.return_date);
    const dDate = new Date(selectedBorrowing.value.due_date);
    const diffTime = rDate - dDate;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
});

const submitCreate = () => {
    form.post('/returns', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        },
    });
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Sirkulasi Pengembalian Buku</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Proses pengembalian buku dipinjam, hitung keterlambatan, dan pencatatan kondisi fisik buku.</p>
                </div>
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <button @click="openCreateModal" class="col-span-2 sm:col-span-1 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 whitespace-nowrap">
                        <Plus class="w-4 h-4" />
                        <span>Proses Pengembalian</span>
                    </button>
                    <a href="/returns/export-excel" target="_blank" class="px-3.5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- Controls -->
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="relative w-full sm:w-80">
                    <input v-model="search" @keyup.enter="filter" type="text" placeholder="Cari No. Return / Anggota / Judul..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-blue-500 focus:outline-none" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">No. Pengembalian</th>
                                <th class="p-4">Anggota Siswa</th>
                                <th class="p-4">Buku Dikembalikan</th>
                                <th class="p-4">Tgl Kembali</th>
                                <th class="p-4">Kondisi Buku</th>
                                <th class="p-4">Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="r in returns.data" :key="r.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4 font-mono font-bold text-emerald-900">{{ r.return_no }}</td>
                                <td class="p-4 font-bold text-slate-900">
                                    <span>{{ r.member?.name }}</span>
                                    <span class="text-[10px] text-slate-400 block">NIS: {{ r.member?.nis }}</span>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <span class="font-bold text-slate-900 block truncate">{{ r.book?.title }}</span>
                                    <span class="text-[10px] text-slate-400 block">Kode: {{ r.book?.book_code }}</span>
                                </td>
                                <td class="p-4 font-medium text-slate-700">{{ formatDate(r.return_date) }}</td>
                                <td class="p-4">
                                    <span :class="[
                                        r.condition === 'Baik' ? 'bg-emerald-50 text-emerald-700' :
                                        r.condition === 'Rusak Ringan' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700',
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold'
                                    ]">
                                        {{ r.condition }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span :class="[
                                        r.late_days > 0 ? 'text-amber-600 font-bold' : 'text-slate-500',
                                        'text-xs'
                                    ]">
                                        {{ r.late_days > 0 ? `${r.late_days} Hari Terlambat` : 'Tepat Waktu' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Form Pengembalian -->
            <div v-if="isCreateOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">Proses Pengembalian Buku</h3>
                        <button @click="isCreateOpen = false" class="p-1 rounded-lg text-slate-400"><X class="w-5 h-5" /></button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
                        <!-- Searchable Borrowing Picker -->
                        <div class="relative space-y-1">
                            <label class="block font-bold text-slate-700 uppercase">
                                PILIH TRANSAKSI PEMINJAMAN AKTIFF <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="borrowingSearch" 
                                    @focus="isBorrowingDropdownOpen = true"
                                    placeholder="Cari nama siswa, NIS, No TRX, atau judul buku..." 
                                    class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 bg-white" 
                                />
                                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                                <button v-if="form.borrowing_id" type="button" @click="form.borrowing_id = ''; borrowingSearch = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-rose-500">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Live Filter Dropdown List -->
                            <div v-if="isBorrowingDropdownOpen" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <div 
                                    v-for="ab in filteredActiveBorrowings" 
                                    :key="ab.id" 
                                    @click="selectBorrowing(ab)"
                                    class="p-2.5 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition-colors"
                                >
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs">{{ ab.member?.name }} • {{ ab.book?.title }}</span>
                                        <span class="text-[10px] text-slate-500">TRX: {{ ab.transaction_no }} | NIS: {{ ab.member?.nis }}</span>
                                    </div>
                                    <Check v-if="form.borrowing_id == ab.id" class="w-4 h-4 text-emerald-600" />
                                </div>
                                <div v-if="filteredActiveBorrowings.length === 0" class="p-4 text-center text-slate-400 text-xs">
                                    Tidak ada transaksi aktif yang cocok dengan "{{ borrowingSearch }}".
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedBorrowing" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                            <div class="flex justify-between text-slate-900">
                                <div>
                                    <span class="font-bold block">{{ selectedBorrowing.book?.title }}</span>
                                    <span class="text-[11px] text-slate-500">Peminjam: {{ selectedBorrowing.member?.name }} ({{ selectedBorrowing.member?.class_name }})</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between text-[11px]">
                                <span>Tgl Pinjam: {{ selectedBorrowing.borrow_date }}</span>
                                <span class="font-bold text-amber-600">Jatuh Tempo: {{ selectedBorrowing.due_date }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Pengembalian <span class="text-rose-500">*</span></label>
                                <input v-model="form.return_date" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Kondisi Buku <span class="text-rose-500">*</span></label>
                                <select v-model="form.condition" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs">
                                    <option value="Baik">Baik (Bagus)</option>
                                    <option value="Rusak Ringan">Rusak Ringan (Halaman sobek)</option>
                                    <option value="Rusak Berat">Rusak Berat (Cover hilang / basah)</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="selectedBorrowing" class="p-3 bg-amber-50 rounded-2xl border border-amber-100 flex items-center justify-between text-amber-950 text-xs">
                            <span>Perhitungan Keterlambatan:</span>
                            <span class="font-bold text-amber-700">{{ calculatedLateDays }} Hari Terlambat</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Catatan Pengembalian</label>
                            <textarea v-model="form.note" rows="2" placeholder="Catatan kondisi buku atau denda..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs"></textarea>
                        </div>

                        <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center gap-2 text-emerald-900 text-[11px]">
                            <CheckCircle class="w-4 h-4 text-emerald-600 shrink-0" />
                            <span>Stok buku akan otomatis bertambah 1 unit setelah pengembalian diproses.</span>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">Batal</button>
                            <button :disabled="form.processing || !form.borrowing_id" type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold disabled:opacity-50">Simpan Pengembalian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
