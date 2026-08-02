<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowRightLeft, Plus, Search, FileSpreadsheet, Trash2, X, AlertTriangle, Calendar, Clock, User, BookOpen, Check } from 'lucide-vue-next';

const props = defineProps({
    borrowings: Object,
    members: Array,
    books: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const filter = () => {
    router.get('/borrowings', {
        search: search.value,
        status: status.value,
    }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);

const today = new Date().toISOString().substr(0, 10);
const defaultDue = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10);

const form = useForm({
    member_id: '',
    book_id: '',
    borrow_date: today,
    due_date: defaultDue,
    notes: '',
});

// Searchable Member Picker State
const memberSearch = ref('');
const isMemberDropdownOpen = ref(false);

const filteredMembers = computed(() => {
    if (!props.members) return [];
    if (!memberSearch.value.trim()) return props.members.slice(0, 50);
    const q = memberSearch.value.toLowerCase();
    return props.members.filter(m => 
        (m.name && m.name.toLowerCase().includes(q)) ||
        (m.nis && m.nis.toLowerCase().includes(q)) ||
        (m.class_name && m.class_name.toLowerCase().includes(q))
    ).slice(0, 50);
});

const selectedMember = computed(() => {
    return props.members.find(m => m.id == form.member_id);
});

const selectMember = (member) => {
    form.member_id = member.id;
    isMemberDropdownOpen.value = false;
};

// Searchable Book Picker State
const bookSearch = ref('');
const isBookDropdownOpen = ref(false);

const filteredBooks = computed(() => {
    if (!props.books) return [];
    if (!bookSearch.value.trim()) return props.books.slice(0, 50);
    const q = bookSearch.value.toLowerCase();
    return props.books.filter(b => 
        (b.title && b.title.toLowerCase().includes(q)) ||
        (b.book_code && b.book_code.toLowerCase().includes(q)) ||
        (b.author && b.author.toLowerCase().includes(q))
    ).slice(0, 50);
});

const selectedBook = computed(() => {
    return props.books.find(b => b.id == form.book_id);
});

const selectBook = (book) => {
    form.book_id = book.id;
    isBookDropdownOpen.value = false;
};

const openCreateModal = () => {
    form.reset();
    memberSearch.value = '';
    bookSearch.value = '';
    isMemberDropdownOpen.value = false;
    isBookDropdownOpen.value = false;
    form.borrow_date = today;
    form.due_date = defaultDue;
    isCreateOpen.value = true;
};

const submitCreate = () => {
    form.post('/borrowings', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        },
    });
};

const deleteBorrowing = (borrowing) => {
    if (confirm(`Hapus transaksi peminjaman ${borrowing.transaction_no}? (Stok buku akan dikembalikan jika belum dikembalikan)`)) {
        router.delete(`/borrowings/${borrowing.id}`);
    }
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
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sirkulasi Peminjaman Buku</h1>
                    <p class="text-xs text-slate-500 mt-1">Transaksi peminjaman koleksi buku oleh anggota siswa perpustakaan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/borrowings/export-excel" target="_blank" class="px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold transition-all flex items-center gap-2">
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                    <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        <span>Catat Peminjaman Baru</span>
                    </button>
                </div>
            </div>

            <!-- Controls -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-80">
                    <input v-model="search" @keyup.enter="filter" type="text" placeholder="Cari No. TRX / Nama / Judul Buku..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
                <div>
                    <select v-model="status" @change="filter" class="py-2 px-3 rounded-xl border border-slate-200 text-xs bg-white">
                        <option value="">Semua Status</option>
                        <option value="Dipinjam">Dipinjam</option>
                        <option value="Dikembalikan">Dikembalikan</option>
                        <option value="Terlambat">Terlambat</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">No Transaksi</th>
                                <th class="p-4">Anggota Siswa</th>
                                <th class="p-4">Buku Dipinjam</th>
                                <th class="p-4">Tgl Pinjam &amp; Jatuh Tempo</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="b in borrowings.data" :key="b.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4 font-mono font-bold text-blue-900">{{ b.transaction_no }}</td>
                                <td class="p-4 font-bold text-slate-900">
                                    <span>{{ b.member?.name }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">NIS: {{ b.member?.nis }} ({{ b.member?.class_name }})</span>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <span class="font-bold text-slate-900 block truncate">{{ b.book?.title }}</span>
                                    <span class="text-[10px] text-slate-400 block">Kode: {{ b.book?.book_code }} | Rak: {{ b.book?.shelf }}</span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <span class="flex items-center gap-1.5 font-medium"><Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" /> Pinjam: {{ formatDate(b.borrow_date) }}</span>
                                    <span class="flex items-center gap-1.5 text-[10px] text-amber-600 font-bold mt-0.5"><Clock class="w-3 h-3 text-amber-600 shrink-0" /> Harus Kembali: {{ formatDate(b.due_date) }}</span>
                                </td>
                                <td class="p-4">
                                    <span :class="[
                                        b.status === 'Dipinjam' ? 'bg-blue-50 text-blue-700' : 
                                        b.status === 'Dikembalikan' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700',
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold'
                                    ]">
                                        {{ b.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="deleteBorrowing(b)" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50" title="Hapus Transaksi">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Form Peminjaman -->
            <div v-if="isCreateOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">Peminjaman Buku Baru</h3>
                        <button @click="isCreateOpen = false" class="p-1 rounded-lg text-slate-400"><X class="w-5 h-5" /></button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
                        <!-- Searchable Member Picker (Live Filter 1000+ Siswa) -->
                        <div class="relative space-y-1">
                            <label class="block font-bold text-slate-700 uppercase">
                                PILIH ANGGOTA SISWA <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="memberSearch" 
                                    @focus="isMemberDropdownOpen = true"
                                    placeholder="Cari nama siswa, NIS, atau kelas..." 
                                    class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-blue-600 focus:ring-2 focus:ring-blue-100 bg-white" 
                                />
                                <User class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                                <button v-if="form.member_id" type="button" @click="form.member_id = ''; memberSearch = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-rose-500">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Selected Member Summary -->
                            <div v-if="selectedMember" class="p-2.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                                        {{ selectedMember.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-blue-950 block">{{ selectedMember.name }}</span>
                                        <span class="text-[10px] text-blue-700">NIS: {{ selectedMember.nis }} | {{ selectedMember.class_name }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-600 text-white rounded-full">Terpilih</span>
                            </div>

                            <!-- Live Filter Dropdown List -->
                            <div v-if="isMemberDropdownOpen" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <div 
                                    v-for="m in filteredMembers" 
                                    :key="m.id" 
                                    @click="selectMember(m)"
                                    class="p-2.5 hover:bg-blue-50 cursor-pointer flex items-center justify-between transition-colors"
                                >
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs">{{ m.name }}</span>
                                        <span class="text-[10px] text-slate-500">NIS: {{ m.nis }} • {{ m.class_name }}</span>
                                    </div>
                                    <Check v-if="form.member_id == m.id" class="w-4 h-4 text-blue-600" />
                                </div>
                                <div v-if="filteredMembers.length === 0" class="p-4 text-center text-slate-400 text-xs">
                                    Siswa tidak ditemukan untuk pencarian "{{ memberSearch }}".
                                </div>
                            </div>
                        </div>

                        <!-- Searchable Book Picker (Live Filter 2500+ Buku) -->
                        <div class="relative space-y-1">
                            <label class="block font-bold text-slate-700 uppercase">
                                PILIH BUKU DIPINJAM <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="relative">
                                <input 
                                    type="text" 
                                    v-model="bookSearch" 
                                    @focus="isBookDropdownOpen = true"
                                    placeholder="Cari judul buku, kode, atau pengarang..." 
                                    class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-blue-600 focus:ring-2 focus:ring-blue-100 bg-white" 
                                />
                                <BookOpen class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                                <button v-if="form.book_id" type="button" @click="form.book_id = ''; bookSearch = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-rose-500">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Selected Book Summary -->
                            <div v-if="selectedBook" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-emerald-950 block">{{ selectedBook.title }}</span>
                                    <span class="text-[10px] text-emerald-700">Kode: {{ selectedBook.book_code }} | Rak: {{ selectedBook.shelf }}</span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-600 text-white rounded-full">Stok: {{ selectedBook.stock }}</span>
                            </div>

                            <!-- Live Filter Dropdown List -->
                            <div v-if="isBookDropdownOpen" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <div 
                                    v-for="bk in filteredBooks" 
                                    :key="bk.id" 
                                    @click="selectBook(bk)"
                                    class="p-2.5 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition-colors"
                                >
                                    <div>
                                        <span class="font-bold text-slate-900 block text-xs">{{ bk.title }}</span>
                                        <span class="text-[10px] text-slate-500">Kode: {{ bk.book_code }} • {{ bk.author }}</span>
                                    </div>
                                    <span :class="[bk.stock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700', 'text-[10px] font-bold px-2 py-0.5 rounded-md']">
                                        Stok: {{ bk.stock }}
                                    </span>
                                </div>
                                <div v-if="filteredBooks.length === 0" class="p-4 text-center text-slate-400 text-xs">
                                    Buku tidak ditemukan untuk pencarian "{{ bookSearch }}".
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Pinjam <span class="text-rose-500">*</span></label>
                                <input v-model="form.borrow_date" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Harus Kembali <span class="text-rose-500">*</span></label>
                                <input v-model="form.due_date" type="date" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                            <textarea v-model="form.notes" rows="2" placeholder="Catatan transaksi (opsional)..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs"></textarea>
                        </div>

                        <div class="p-3 bg-amber-50 rounded-2xl border border-amber-100 flex items-center gap-2 text-amber-900 text-[11px]">
                            <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
                            <span>Stok buku akan otomatis berkurang 1 unit setelah transaksi ini disimpan.</span>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">Batal</button>
                            <button :disabled="form.processing || !form.member_id || !form.book_id" type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-md disabled:opacity-50">Simpan Peminjaman</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
