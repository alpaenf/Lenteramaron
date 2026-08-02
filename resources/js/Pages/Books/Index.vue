<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    PenTool
} from 'lucide-vue-next';

const props = defineProps({
    books: Object,
    categories: Array,
    filters: Object,
});

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
            if (editingBook.value) {
                failedCovers.value.delete(editingBook.value.id);
            }
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

// Import Form
const importForm = useForm({
    file: null,
});

const submitImport = () => {
    importForm.post('/books/import-excel', {
        onSuccess: () => {
            isImportOpen.value = false;
            importForm.reset();
        },
    });
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
                <div class="flex flex-wrap items-center gap-3">
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
                        <span>Tambah Buku</span>
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
                                <th class="p-4">Cover & Kode</th>
                                <th class="p-4">Judul & Pengarang</th>
                                <th class="p-4">Kategori DDC</th>
                                <th class="p-4">Penerbit & Tahun</th>
                                <th class="p-4">Rak & Stok</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="book in books.data" :key="book.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0 flex items-center justify-center text-slate-400 border border-slate-200">
                                            <img 
                                                v-if="book.cover && !failedCovers.has(book.id)" 
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
                                    <span class="block font-semibold text-slate-800">{{ book.shelf }}</span>
                                    <span :class="[
                                        book.stock > 0 ? 'text-emerald-600' : 'text-rose-500',
                                        'text-[11px] font-bold'
                                    ]">
                                        Stok: {{ book.stock }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
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
                                <input v-model="form.isbn" type="text" placeholder="Contoh: 978-602-1234-01-1" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-blue-500" />
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
                        <div class="p-4 bg-blue-50 border border-blue-100 rounded-2xl text-blue-950 space-y-1">
                            <span class="font-bold">Format Excel (.xlsx / .csv):</span>
                            <p class="text-[11px] text-slate-600">Pastikan baris pertama berisi kolom header: <code>kode_buku, judul_buku, pengarang, penerbit, tahun, kategori, rak, stok</code>.</p>
                        </div>

                        <div>
                            <input @change="e => importForm.file = e.target.files[0]" type="file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white font-bold" />
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button type="button" @click="isImportOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold">Batal</button>
                            <button :disabled="importForm.processing" type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold">
                                {{ importForm.processing ? 'Mengunggah...' : 'Import Data' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
