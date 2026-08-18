<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { 
    BookMarked, 
    Plus, 
    Search, 
    FileSpreadsheet, 
    Upload, 
    Edit, 
    Trash2, 
    X, 
    BookOpen,
    Filter,
    Check,
    PenTool,
    Sparkles,
    Loader2
} from 'lucide-vue-next';

const props = defineProps({
    books: Object,
    categories: Array,
    filters: Object,
});

const page = usePage();
const isAdmin = computed(() => {
    const r = (page.props.auth?.user?.role || '').toLowerCase();
    return ['admin', 'pustakawan', 'kepala_sekolah'].includes(r);
});

const isEnriching = ref(false);
const enrichStatus = ref('');

// Read Book Detail Modal State
const selectedReadBook = ref(null);
const isReadModalOpen = ref(false);

const openReadModal = (book) => {
    selectedReadBook.value = book;
    isReadModalOpen.value = true;
};

const handleIsbnEnrich = async () => {
    if (!form.isbn || form.isbn.trim().length < 5) {
        alert('Masukkan nomor ISBN terlebih dahulu.');
        return;
    }

    isEnriching.value = true;
    enrichStatus.value = '';
    try {
        const res = await axios.post('/books/enrich-by-isbn', { isbn: form.isbn });
        if (res.data.success && res.data.data) {
            const d = res.data.data;
            if (d.title) form.title = d.title;
            if (d.author) form.author = d.author;
            if (d.publisher) form.publisher = d.publisher;
            if (d.publication_year) form.year = d.publication_year;
            if (d.description) form.description = d.description;
            if (d.cover_url) coverPreview.value = d.cover_url;
            enrichStatus.value = `✓ Berhasil mengambil data dari ${d.source || 'Open Library'}`;
        }
    } catch (e) {
        enrichStatus.value = '❌ Metadata ISBN tidak ditemukan.';
    } finally {
        isEnriching.value = false;
    }
};

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');

const filter = () => {
    router.get('/books', {
        search: search.value,
        category_id: categoryId.value,
    }, { preserveState: true, replace: true });
};

// Modal State
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isImportOpen = ref(false);
const editingBook = ref(null);

// Create / Edit Form
const form = useForm({
    book_code: '',
    isbn: '',
    title: '',
    author: '',
    publisher: '',
    year: new Date().getFullYear(),
    category_id: '',
    shelf: 'Rak A-01',
    stock: 1,
    description: '',
    cover: null,
});

const coverPreview = ref(null);

const handleCoverChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover = file;
        coverPreview.value = URL.createObjectURL(file);
    }
};

const openCreateModal = () => {
    form.reset();
    coverPreview.value = null;
    isCreateOpen.value = true;
};

const openEditModal = (book) => {
    editingBook.value = book;
    form.book_code = book.book_code;
    form.isbn = book.isbn || '';
    form.title = book.title;
    form.author = book.author;
    form.publisher = book.publisher;
    form.year = book.year;
    form.category_id = book.category_id;
    form.shelf = book.shelf;
    form.stock = book.stock;
    form.description = book.description || '';
    form.cover = null;
    coverPreview.value = book.cover ? (book.cover.startsWith('uploads/') ? `/${book.cover}` : `/files-media/${book.cover}`) : null;
    isEditOpen.value = true;
};

const failedCovers = ref(new Set());

const handleCoverError = (bookId) => {
    failedCovers.value.add(bookId);
};

const submitCreate = () => {
    const formData = new FormData();
    formData.append('book_code', form.book_code);
    formData.append('isbn', form.isbn || '');
    formData.append('title', form.title);
    formData.append('author', form.author);
    formData.append('publisher', form.publisher);
    formData.append('year', form.year);
    formData.append('category_id', form.category_id);
    formData.append('shelf', form.shelf);
    formData.append('stock', form.stock);
    formData.append('description', form.description || '');
    if (form.cover) {
        formData.append('cover', form.cover);
    }

    router.post('/books', formData, {
        forceFormData: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
            coverPreview.value = null;
        },
    });
};

const submitUpdate = () => {
    const formData = new FormData();
    formData.append('book_code', form.book_code);
    formData.append('isbn', form.isbn || '');
    formData.append('title', form.title);
    formData.append('author', form.author);
    formData.append('publisher', form.publisher);
    formData.append('year', form.year);
    formData.append('category_id', form.category_id);
    formData.append('shelf', form.shelf);
    formData.append('stock', form.stock);
    formData.append('description', form.description || '');
    if (form.cover) {
        formData.append('cover', form.cover);
    }

    router.post(`/books/${editingBook.value.id}/update`, formData, {
        forceFormData: true,
        onSuccess: () => {
            isEditOpen.value = false;
            editingBook.value = null;
            form.reset();
            coverPreview.value = null;
        },
    });
};

const deleteBook = (book) => {
    if (confirm(`Apakah Anda yakin ingin menghapus buku "${book.title}"?`)) {
        router.delete(`/books/${book.id}`);
    }
};

// Import Form & Fast Client-side CSV Parser
const importForm = useForm({
    file: null,
});

const parsedCsvRows = ref([]);
const isParsingCsv = ref(false);
const isUploadingJson = ref(false);

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    importForm.file = file;
    parsedCsvRows.value = [];

    if (file.name.endsWith('.csv') || file.name.endsWith('.txt') || file.type.includes('csv')) {
        isParsingCsv.value = true;
        const reader = new FileReader();
        reader.onload = (evt) => {
            try {
                const text = evt.target.result;
                const lines = text.split(/\r\n|\n/);
                if (lines.length < 2) {
                    isParsingCsv.value = false;
                    return;
                }

                const parseCsvLine = (line) => {
                    const result = [];
                    let cell = '';
                    let inQuotes = false;
                    for (let i = 0; i < line.length; i++) {
                        const char = line[i];
                        if (char === '"') {
                            inQuotes = !inQuotes;
                        } else if (char === ',' && !inQuotes) {
                            result.push(cell.trim());
                            cell = '';
                        } else {
                            cell += char;
                        }
                    }
                    result.push(cell.trim());
                    return result;
                };

                const headers = parseCsvLine(lines[0]);
                const rows = [];
                const maxRows = Math.min(lines.length, 5001); // Max 5000 data rows

                for (let i = 1; i < maxRows; i++) {
                    if (!lines[i] || !lines[i].trim()) continue;
                    const values = parseCsvLine(lines[i]);
                    const rowObj = {};
                    headers.forEach((h, idx) => {
                        rowObj[h] = values[idx] || '';
                    });
                    rows.push(rowObj);
                }

                parsedCsvRows.value = rows;
            } catch (err) {
                console.error('CSV Parsing Error:', err);
            } finally {
                isParsingCsv.value = false;
            }
        };
        reader.readAsText(file); // Read full file, no byte limit
    }
};

const submitImport = async () => {
    if (parsedCsvRows.value.length > 0) {
        isUploadingJson.value = true;
        try {
            const res = await axios.post('/books/batch-import-json', { rows: parsedCsvRows.value });
            if (res.data.success) {
                alert(res.data.message || 'Berhasil mengimpor data buku.');
                isImportOpen.value = false;
                importForm.reset();
                parsedCsvRows.value = [];
                router.reload({ preserveScroll: true });
            }
        } catch (err) {
            alert('Gagal mengimpor data: ' + (err.response?.data?.message || err.message));
        } finally {
            isUploadingJson.value = false;
        }
        return;
    }

    importForm.post('/books/import-excel', {
        preserveScroll: true,
        onSuccess: () => {
            isImportOpen.value = false;
            importForm.reset();
        },
    });
};

// Live Web Search Import Modal
const isWebSearchOpen = ref(false);
const webSearchQuery = ref('');
const webSearchResults = ref([]);
const isSearchingWeb = ref(false);
const hasSearchedWeb = ref(false);
const importingItemTitle = ref('');

const searchWebBooks = async () => {
    if (!webSearchQuery.value || webSearchQuery.value.trim().length < 2) return;
    isSearchingWeb.value = true;
    hasSearchedWeb.value = true;
    try {
        const res = await axios.post('/books/search-web', { q: webSearchQuery.value });
        webSearchResults.value = res.data.results || [];
    } catch (e) {
        alert('Gagal mencari di web.');
    } finally {
        isSearchingWeb.value = false;
    }
};

const importWebBook = async (book) => {
    importingItemTitle.value = book.title;
    try {
        const formData = new FormData();
        formData.append('book_code', 'BK-' + Math.random().toString(36).substring(2, 8).toUpperCase());
        formData.append('isbn', book.isbn || '');
        formData.append('title', book.title);
        formData.append('author', book.author || 'Tanpa Pengarang');
        formData.append('publisher', book.publisher || '-');
        formData.append('year', book.publication_year || new Date().getFullYear());
        if (props.categories && props.categories[0]) {
            formData.append('category_id', props.categories[0].id);
        }
        formData.append('shelf', 'Rak Referensi');
        formData.append('stock', 1);
        formData.append('description', book.description || '');
        if (book.cover_url) {
            formData.append('cover_url', book.cover_url);
        }

        router.post('/books', formData, {
            preserveScroll: true,
            onSuccess: () => {
                alert(`✓ Buku "${book.title}" berhasil ditambahkan ke katalog referensi!`);
                isWebSearchOpen.value = false;
            },
            onError: (errors) => {
                alert('Gagal mengimpor buku. ' + Object.values(errors).flat().join(', '));
            }
        });
    } catch (e) {
        alert('Gagal mengimpor buku.');
    } finally {
        importingItemTitle.value = '';
    }
};

// Batch ISBN Import Modal
const isBatchIsbnOpen = ref(false);
const batchIsbnText = ref('');
const isBatchImporting = ref(false);

const submitBatchIsbn = async () => {
    if (!batchIsbnText.value.trim()) return;
    isBatchImporting.value = true;
    try {
        const res = await axios.post('/books/batch-import-isbn', { isbns: batchIsbnText.value });
        if (res.data.success) {
            alert(res.data.message);
            isBatchIsbnOpen.value = false;
            batchIsbnText.value = '';
            router.reload();
        }
    } catch (e) {
        alert('Gagal memproses impor batch ISBN.');
    } finally {
        isBatchImporting.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Top Header & Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Master Data Buku</h1>
                    <p class="text-xs text-slate-500 mt-1">Kelola katalog koleksi buku, stok exemplar, dan lokasi rak penyimpanan.</p>
                </div>
                <div v-if="isAdmin" class="flex flex-wrap items-center gap-3">
                    <button @click="isWebSearchOpen = true" class="px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-extrabold transition-all flex items-center gap-2">
                        <Sparkles class="w-4 h-4 text-amber-500" />
                        <span>Cari &amp; Tambah dari Web</span>
                    </button>
                    <button @click="isBatchIsbnOpen = true" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-all flex items-center gap-2">
                        <BookMarked class="w-4 h-4 text-blue-600" />
                        <span>Import Batch ISBN</span>
                    </button>
                    <a href="/books/export-excel" target="_blank" class="px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition-all flex items-center gap-2">
                        <FileSpreadsheet class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                    <button @click="isImportOpen = true" class="px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition-all flex items-center gap-2">
                        <Upload class="w-4 h-4" />
                        <span>Import Excel</span>
                    </button>
                    <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        <span>Tambah Manual</span>
                    </button>
                </div>
            </div>

            <!-- Search & Filter Controls -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-80">
                    <input 
                        v-model="search" 
                        @keyup.enter="filter"
                        type="text" 
                        placeholder="Cari Judul / ISBN / Pengarang..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    />
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select v-model="categoryId" @change="filter" class="w-full sm:w-56 py-2 px-3 rounded-xl border border-slate-200 text-xs bg-white focus:border-blue-500">
                        <option value="">Semua Kategori DDC</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.code }} - {{ cat.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Books Data Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">Cover &amp; Kode</th>
                                <th class="p-4">Judul &amp; Pengarang</th>
                                <th class="p-4">Kategori DDC</th>
                                <th class="p-4">Penerbit &amp; Tahun</th>
                                <th class="p-4">Akses Referensi</th>
                                <th v-if="isAdmin" class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="book in books.data" :key="book.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0 flex items-center justify-center text-slate-400 border border-slate-200">
                                            <img 
                                                v-if="book.cover &amp;&amp; !failedCovers.has(book.id)" 
                                                :src="book.cover.startsWith('uploads/') ? `/${book.cover}?t=${book.updated_at || ''}` : `/files-media/${book.cover}?t=${book.updated_at || ''}`" 
                                                @error="handleCoverError(book.id)" 
                                                class="w-full h-full object-cover" 
                                            />
                                            <BookOpen v-else class="w-5 h-5 text-slate-400" />
                                        </div>
                                        <div>
                                            <span class="block font-bold text-slate-900">{{ book.book_code }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">ISBN: {{ book.isbn || '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 max-w-xs">
                                    <span class="block font-bold text-slate-900 text-xs line-clamp-1">{{ book.title }}</span>
                                    <span class="text-[11px] text-slate-500 truncate flex items-center gap-1"><PenTool class="w-3 h-3 text-slate-400 shrink-0" /> {{ book.author }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700">
                                        {{ book.category?.name || '-' }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-600">
                                    <span>{{ book.publisher }}</span>
                                    <span class="block text-[10px] text-slate-400">Tahun {{ book.year }}</span>
                                </td>
                                <td class="p-4">
                                    <button 
                                        @click="openReadModal(book)" 
                                        class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center gap-1.5 border border-blue-200"
                                    >
                                        <BookOpen class="w-3.5 h-3.5 text-blue-600" />
                                        <span>Baca Referensi</span>
                                    </button>
                                </td>
                                <td v-if="isAdmin" class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(book)" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteBook(book)" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!books.data || books.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                    Belum ada data buku tersimpan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Create / Edit Buku -->
            <div v-if="isCreateOpen || isEditOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-6 max-h-[90vh] overflow-y-auto shadow-2xl">
                    <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-lg">
                            {{ isCreateOpen ? 'Tambah Buku Baru' : 'Edit Data Buku' }}
                        </h3>
                        <button @click="isCreateOpen = false; isEditOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="isCreateOpen ? submitCreate() : submitUpdate()" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Buku <span class="text-rose-500">*</span></label>
                                <input v-model="form.book_code" type="text" required placeholder="Contoh: BK-001" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">ISBN</label>
                                <div class="flex gap-2">
                                    <input v-model="form.isbn" type="text" placeholder="Contoh: 9786020332949" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                                    <button
                                        type="button"
                                        @click="handleIsbnEnrich"
                                        :disabled="isEnriching || !form.isbn"
                                        class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs whitespace-nowrap border border-indigo-200 flex items-center gap-1 transition disabled:opacity-50"
                                    >
                                        <Sparkles class="w-3.5 h-3.5 text-indigo-600" />
                                        <span>Auto-Isi</span>
                                    </button>
                                </div>
                                <span v-if="enrichStatus" class="block text-[10px] mt-1 font-medium text-indigo-600">{{ enrichStatus }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Judul Buku <span class="text-rose-500">*</span></label>
                            <input v-model="form.title" type="text" required placeholder="Judul lengkap buku" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Pengarang <span class="text-rose-500">*</span></label>
                                <input v-model="form.author" type="text" required placeholder="Nama pengarang" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Penerbit <span class="text-rose-500">*</span></label>
                                <input v-model="form.publisher" type="text" required placeholder="Nama penerbit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Kategori DDC <span class="text-rose-500">*</span></label>
                                <select v-model="form.category_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.code }} - {{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Tahun Terbit <span class="text-rose-500">*</span></label>
                                <input v-model="form.year" type="number" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Lokasi Rak <span class="text-rose-500">*</span></label>
                                <input v-model="form.shelf" type="text" required placeholder="Rak A-01" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Jumlah Stok <span class="text-rose-500">*</span></label>
                                <input v-model="form.stock" type="number" min="0" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1">Upload Sampul (Cover)</label>
                                <input @change="handleCoverChange" type="file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>
                        </div>

                        <div v-if="coverPreview" class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl">
                            <img :src="coverPreview" class="w-12 h-16 object-cover rounded-lg" />
                            <span class="text-[11px] text-slate-500">Preview Sampul Buku</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Sinopsis</label>
                            <textarea v-model="form.description" rows="3" class="w-full p-3 rounded-xl border border-slate-200 text-xs"></textarea>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false; isEditOpen = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                            <button :disabled="form.processing" type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-bold shadow-md shadow-blue-500/20">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Buku' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Import Excel -->
            <div v-if="isImportOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">Import Data Buku dari Excel</h3>
                        <button @click="isImportOpen = false" class="p-1 rounded-lg text-slate-400">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitImport" class="space-y-4 text-xs">
                        <!-- Format Helper Banner -->
                        <div class="p-4 bg-blue-50/80 border border-blue-100 rounded-2xl text-blue-950 space-y-1">
                            <span class="font-bold">Format Excel / CSV (.xlsx, .xls, .csv):</span>
                            <p class="text-[11px] text-slate-600">Dapat mengunggah file lokal (header Indonesia) atau file dataset Kaggle/Goodreads langsung (header Inggris: <code>title, author, publisher, isbn, year</code>).</p>
                        </div>

                        <!-- Error Alert inside Modal -->
                        <div v-if="importForm.errors.file" class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl font-medium text-xs">
                            ⚠️ {{ importForm.errors.file }}
                        </div>

                        <!-- Processing & Live Percentage Progress Bar -->
                        <div v-if="importForm.processing || isUploadingJson" class="p-5 text-center text-slate-700 space-y-3 bg-blue-50/90 rounded-2xl border border-blue-200">
                            <Loader2 class="w-8 h-8 text-blue-600 animate-spin mx-auto" />
                            <div>
                                <p class="font-extrabold text-sm text-blue-950">
                                    Sedang Memproses &amp; Mengimpor Dataset... 
                                    <span v-if="importForm.progress" class="text-blue-600 font-mono text-xs ml-1">({{ importForm.progress.percentage }}%)</span>
                                    <span v-else-if="parsedCsvRows.length" class="text-blue-600 font-mono text-xs ml-1">({{ parsedCsvRows.length }} Baris)</span>
                                </p>
                                <p class="text-[11px] text-slate-600 mt-0.5 font-medium">Mohon tunggu sebentar, sistem sedang membaca dan menyimpan data langsung ke database.</p>
                            </div>

                            <!-- Animated Live Progress Bar -->
                            <div v-if="importForm.progress" class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden shadow-inner">
                                <div 
                                    class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 shadow-sm" 
                                    :style="{ width: importForm.progress.percentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div v-else class="space-y-2">
                            <label class="block font-bold text-slate-700 uppercase mb-1">Pilih File Dataset (.csv / .xlsx)</label>
                            <input @change="handleFileSelect" type="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white font-bold cursor-pointer" />
                            
                            <div v-if="parsedCsvRows.length > 0" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-[11px] font-medium flex items-center justify-between">
                                <span>✓ Siap mengimpor {{ parsedCsvRows.length }} baris buku secara instan</span>
                                <span class="font-mono text-[10px] bg-emerald-200/60 px-2 py-0.5 rounded-full">JSON Fast Mode</span>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button type="button" :disabled="importForm.processing || isUploadingJson" @click="isImportOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold disabled:opacity-50">Batal</button>
                            <button 
                                :disabled="importForm.processing || isUploadingJson || !importForm.file" 
                                type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-extrabold shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <Loader2 v-if="importForm.processing || isUploadingJson" class="w-4 h-4 text-white animate-spin shrink-0" />
                                <span>{{ (importForm.processing || isUploadingJson) ? `Memproses Impor...` : 'Mulai Import Data' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal 1: Live Web Search Import -->
            <div v-if="isWebSearchOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <Sparkles class="w-5 h-5 text-amber-500" />
                            <h3 class="font-extrabold text-slate-900 text-base">Cari &amp; Tambah Referensi dari Web</h3>
                        </div>
                        <button @click="isWebSearchOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <input 
                                v-model="webSearchQuery" 
                                @keyup.enter="searchWebBooks" 
                                type="text" 
                                placeholder="Ketik judul buku, pengarang, atau topik sains..." 
                                class="flex-grow p-3 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 outline-none"
                            />
                            <button 
                                @click="searchWebBooks" 
                                :disabled="isSearchingWeb"
                                class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-2"
                            >
                                <Search class="w-4 h-4" />
                                <span>{{ isSearchingWeb ? 'Mencari...' : 'Cari' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex-grow overflow-y-auto space-y-3 pr-1 min-h-[200px]">
                        <div v-if="webSearchResults.length > 0" class="space-y-3">
                            <div 
                                v-for="(item, idx) in webSearchResults" 
                                :key="idx" 
                                class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-start gap-3.5 hover:border-blue-200 transition"
                            >
                                <img 
                                    v-if="item.cover_url" 
                                    :src="item.cover_url" 
                                    :alt="item.title" 
                                    class="w-12 h-16 object-cover rounded-lg shrink-0 shadow-xs border border-slate-200"
                                />
                                <div v-else class="w-12 h-16 bg-slate-200 rounded-lg shrink-0 flex items-center justify-center text-slate-400">
                                    <BookOpen class="w-6 h-6" />
                                </div>

                                <div class="flex-grow min-w-0">
                                    <h4 class="font-extrabold text-xs text-slate-900 line-clamp-1">{{ item.title }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Penulis: {{ item.author }} | {{ item.publication_year || '-' }}</p>
                                    <p v-if="item.isbn" class="text-[10px] text-blue-600 font-mono mt-0.5">ISBN: {{ item.isbn }}</p>
                                    <p v-if="item.description" class="text-[11px] text-slate-600 line-clamp-2 mt-1 italic">{{ item.description }}</p>
                                </div>

                                <button 
                                    @click="importWebBook(item)" 
                                    :disabled="importingItemTitle === item.title"
                                    class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-[11px] shrink-0 shadow-xs transition"
                                >
                                    {{ importingItemTitle === item.title ? 'Impor...' : '+ Tambahkan' }}
                                </button>
                            </div>
                        </div>
                        <div v-else-if="isSearchingWeb" class="py-12 text-center text-slate-500 text-xs flex flex-col items-center justify-center gap-2">
                            <Sparkles class="w-6 h-6 text-blue-600 animate-spin" />
                            <span class="font-bold">Sedang mencari buku di internet...</span>
                        </div>
                        <div v-else-if="hasSearchedWeb &amp;&amp; webSearchResults.length === 0" class="py-12 text-center text-slate-500 text-xs">
                            Tidak ditemukan hasil untuk kata kunci "{{ webSearchQuery }}". Silakan coba kata kunci lain.
                        </div>
                        <div v-else class="py-12 text-center text-slate-400 text-xs">
                            Ketikkan kata kunci di atas dan tekan Cari untuk menemukan buku secara otomatis dari internet.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal 2: Batch ISBN Import -->
            <div v-if="isBatchIsbnOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <BookMarked class="w-5 h-5 text-blue-600" />
                            <h3 class="font-extrabold text-slate-900 text-base">Import Batch dari Daftar ISBN</h3>
                        </div>
                        <button @click="isBatchIsbnOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <p class="text-slate-600 font-medium">Paste daftar nomor ISBN (satu nomor per baris). Sistem akan menarik metadata lengkap (Judul, Pengarang, Sampul) secara otomatis dari internet.</p>
                        <textarea 
                            v-model="batchIsbnText" 
                            rows="6" 
                            placeholder="9780131103627&#10;9780262033848&#10;9780596007126" 
                            class="w-full p-3 rounded-2xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-blue-500/20 outline-none"
                        ></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <button type="button" @click="isBatchIsbnOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs">Batal</button>
                        <button 
                            @click="submitBatchIsbn" 
                            :disabled="isBatchImporting"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs"
                        >
                            {{ isBatchImporting ? 'Memproses Impor...' : 'Proses Impor Massal' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal 3: Read Reference Detail Modal -->
            <div v-if="isReadModalOpen && selectedReadBook" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-blue-600" />
                            <h3 class="font-extrabold text-slate-900 text-base">Detail Referensi Literatur</h3>
                        </div>
                        <button @click="isReadModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        <div class="w-24 h-32 bg-slate-100 rounded-xl overflow-hidden shrink-0 border border-slate-200 shadow-xs flex items-center justify-center text-slate-400">
                            <img 
                                v-if="selectedReadBook.cover && !failedCovers.has(selectedReadBook.id)" 
                                :src="selectedReadBook.cover.startsWith('uploads/') ? `/${selectedReadBook.cover}` : `/files-media/${selectedReadBook.cover}`" 
                                class="w-full h-full object-cover" 
                            />
                            <BookOpen v-else class="w-8 h-8 text-slate-400" />
                        </div>
                        <div class="space-y-1.5 min-w-0 flex-grow text-xs">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ selectedReadBook.category?.name || 'Katalog Referensi' }}
                            </span>
                            <h4 class="font-extrabold text-slate-900 text-sm leading-snug">{{ selectedReadBook.title }}</h4>
                            <p class="text-slate-600 font-medium">Penulis: <span class="font-bold text-slate-800">{{ selectedReadBook.author }}</span></p>
                            <p class="text-slate-500">Penerbit: {{ selectedReadBook.publisher }} ({{ selectedReadBook.year }})</p>
                            <p v-if="selectedReadBook.isbn" class="text-blue-600 font-mono text-[11px]">ISBN: {{ selectedReadBook.isbn }}</p>
                        </div>
                    </div>

                    <div v-if="selectedReadBook.description" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1 text-xs">
                        <span class="font-bold text-slate-700">Sinopsis / Ringkasan Referensi:</span>
                        <p class="text-slate-600 leading-relaxed italic">{{ selectedReadBook.description }}</p>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="isReadModalOpen = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs">Tutup</button>
                        <a 
                            :href="`https://www.google.com/search?q=${encodeURIComponent(selectedReadBook.title + ' ' + selectedReadBook.author)}`" 
                            target="_blank" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 flex items-center justify-center gap-2"
                        >
                            <BookOpen class="w-4 h-4" />
                            <span>Buka / Baca Sumber Digital</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
