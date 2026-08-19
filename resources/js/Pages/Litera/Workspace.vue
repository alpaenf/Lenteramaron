<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Search, 
    Plus, 
    X, 
    Edit3, 
    ExternalLink, 
    Folder, 
    Bookmark, 
    BookOpen, 
    Check, 
    Trash2, 
    FileText 
} from 'lucide-vue-next';
import PdfViewerModal from '@/Components/PdfViewerModal.vue';

const isPdfReaderOpen = ref(false);
const pdfReaderItem = ref({ title: '', author: '', pdfUrl: '', sourceType: 'external' });

const openPdfReader = (item) => {
    const title = item.book?.title || item.external_source?.title || 'Dokumen Riset';
    const author = item.book?.author || (item.external_source?.authors || []).join(', ') || '';
    const pdfUrl = item.external_source?.pdf_url || item.external_source?.url || item.book?.file_path || '';
    const sourceType = item.source_type || 'external';

    pdfReaderItem.value = { title, author, pdfUrl, sourceType };
    isPdfReaderOpen.value = true;
};

const props = defineProps({
    topics: {
        type: Array,
        default: () => []
    },
    savedSources: {
        type: Array,
        default: () => []
    }
});

const selectedTopicId = ref(null);
const selectedStatus = ref('all');
const searchQuery = ref('');

// Modals
const isCreateTopicModalOpen = ref(false);
const topicForm = useForm({
    title: '',
    description: ''
});

const submitTopic = () => {
    topicForm.post(route('litera.workspace.topics.store'), {
        onSuccess: () => {
            isCreateTopicModalOpen.value = false;
            topicForm.reset();
        }
    });
};

const deleteTopic = (topicId) => {
    if (confirm('Apakah Anda yakin ingin menghapus topik penelitian ini?')) {
        useForm({}).delete(route('litera.workspace.topics.destroy', topicId));
    }
};

const deleteSavedSource = (savedId) => {
    if (confirm('Hapus sumber ini dari Research Workspace Anda?')) {
        useForm({}).delete(route('litera.workspace.saved.destroy', savedId));
    }
};

// Edit Notes & Status Modal
const selectedSavedSource = ref(null);
const isEditModalOpen = ref(false);
const editForm = useForm({
    research_topic_id: null,
    status: 'unread',
    notes: ''
});

const openEditModal = (item) => {
    selectedSavedSource.value = item;
    editForm.research_topic_id = item.research_topic_id || null;
    editForm.status = item.status || 'unread';
    editForm.notes = item.notes || '';
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedSavedSource.value) return;

    editForm.patch(route('litera.workspace.saved.update', selectedSavedSource.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
        }
    });
};

const filteredSavedSources = computed(() => {
    return props.savedSources.filter(item => {
        // Filter by topic
        if (selectedTopicId.value && item.research_topic_id !== selectedTopicId.value) {
            return false;
        }
        // Filter by status
        if (selectedStatus.value !== 'all' && item.status !== selectedStatus.value) {
            return false;
        }
        // Filter by search query
        if (searchQuery.value) {
            const title = item.source_type === 'local' ? (item.book?.title || '') : (item.external_source?.title || '');
            const notes = item.notes || '';
            const q = searchQuery.value.toLowerCase();
            return title.toLowerCase().includes(q) || notes.toLowerCase().includes(q);
        }
        return true;
    });
});
</script>

<template>
    <Head title="Research Workspace — LITERA" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight flex items-center gap-2">
                        <span class="bg-blue-600 text-white px-2.5 py-1 rounded-lg text-lg font-black tracking-wider shadow-sm">LITERA</span>
                        <span>Research Workspace</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Kelola perpustakaan pribadi, catatan penelitian, dan status bacaan Anda</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="isCreateTopicModalOpen = true"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Topik Baru</span>
                    </button>
                    <Link :href="route('litera.search')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                        <Search class="w-4 h-4 text-slate-500" />
                        <span>Cari Literatur</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Filter Controls & Stats Bar -->
                <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    
                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari dalam tersimpan..."
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 text-slate-800 rounded-xl border-slate-200 text-xs focus:ring-blue-500 focus:border-blue-500"
                        />
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    </div>

                    <!-- Topic Filter Pills -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <button
                            @click="selectedTopicId = null"
                            :class="['px-3 py-1.5 rounded-xl font-bold transition', selectedTopicId === null ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
                        >
                            Semua Topik ({{ savedSources.length }})
                        </button>

                        <div v-for="t in topics" :key="t.id" class="inline-flex items-center gap-1 bg-slate-100 rounded-xl px-3 py-1 text-xs">
                            <button
                                @click="selectedTopicId = t.id"
                                :class="['font-bold transition', selectedTopicId === t.id ? 'text-blue-600 font-extrabold' : 'text-slate-700 hover:text-blue-600']"
                            >
                                {{ t.title }} ({{ t.saved_sources_count || 0 }})
                            </button>
                            <button @click="deleteTopic(t.id)" class="text-slate-400 hover:text-rose-600 ml-1 p-0.5">
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                        <button
                            @click="selectedStatus = 'all'"
                            :class="['px-2.5 py-1 rounded-lg transition font-bold', selectedStatus === 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600']"
                        >
                            Semua Status
                        </button>
                        <button
                            @click="selectedStatus = 'unread'"
                            :class="['px-2.5 py-1 rounded-lg transition font-bold', selectedStatus === 'unread' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600']"
                        >
                            Belum Dibaca
                        </button>
                        <button
                            @click="selectedStatus = 'reading'"
                            :class="['px-2.5 py-1 rounded-lg transition font-bold', selectedStatus === 'reading' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600']"
                        >
                            Sedang Dibaca
                        </button>
                        <button
                            @click="selectedStatus = 'completed'"
                            :class="['px-2.5 py-1 rounded-lg transition font-bold', selectedStatus === 'completed' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600']"
                        >
                            Selesai
                        </button>
                    </div>

                </div>

                <!-- Saved Items List -->
                <div v-if="filteredSavedSources.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div
                        v-for="item in filteredSavedSources"
                        :key="item.id"
                        class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition space-y-4 flex flex-col justify-between"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span :class="[
                                    'px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px]',
                                    item.source_type === 'local' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'
                                ]">
                                    {{ item.source_type === 'local' ? 'Buku Lokal Perpustakaan' : 'Jurnal Eksternal' }}
                                </span>

                                <!-- Status Badge -->
                                <span :class="[
                                    'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase flex items-center gap-1',
                                    item.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : (item.status === 'reading' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')
                                ]">
                                    <Check v-if="item.status === 'completed'" class="w-3 h-3 text-emerald-700" />
                                    <BookOpen v-else-if="item.status === 'reading'" class="w-3 h-3 text-amber-700" />
                                    <Bookmark v-else class="w-3 h-3 text-slate-500" />
                                    <span>{{ item.status === 'completed' ? 'Selesai' : (item.status === 'reading' ? 'Sedang Dibaca' : 'Belum Dibaca') }}</span>
                                </span>
                            </div>

                            <!-- Source Title & Info -->
                            <h4 class="font-bold text-slate-900 text-base leading-snug">
                                {{ item.source_type === 'local' ? item.book?.title : item.external_source?.title }}
                            </h4>

                            <div class="text-xs text-slate-500 font-medium space-y-0.5">
                                <p v-if="item.source_type === 'local'"><strong>Penulis:</strong> {{ item.book?.author }} | <strong>Rak:</strong> {{ item.book?.rack?.name || '-' }}</p>
                                <p v-else><strong>Penulis:</strong> {{ (item.external_source?.authors || []).join(', ') }} ({{ item.external_source?.publication_year || 'N/A' }})</p>
                                <p v-if="item.research_topic"><strong>Topik:</strong> <span class="text-blue-600 font-bold">{{ item.research_topic.title }}</span></p>
                            </div>

                            <!-- User Notes -->
                            <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 text-xs text-slate-700 space-y-1">
                                <span class="font-bold text-blue-900 text-[11px] uppercase tracking-wider block">Catatan Pribadi:</span>
                                <p class="italic text-slate-600 leading-relaxed">{{ item.notes || 'Belum ada catatan.' }}</p>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                            <button
                                @click="openEditModal(item)"
                                class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                                <span>Edit Status & Catatan</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    v-if="item.external_source?.pdf_url || item.external_source?.url || item.book?.file_path"
                                    @click="openPdfReader(item)"
                                    class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 shadow-xs transition"
                                >
                                    <BookOpen class="w-3.5 h-3.5" />
                                    <span>Baca PDF</span>
                                </button>

                                <a
                                    v-if="item.external_source?.url || item.external_source?.pdf_url"
                                    :href="item.external_source.pdf_url || item.external_source.url"
                                    target="_blank"
                                    class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold flex items-center gap-1"
                                >
                                    <span>Buka Artikel</span>
                                    <ExternalLink class="w-3 h-3" />
                                </a>

                                <button
                                    @click="deleteSavedSource(item.id)"
                                    class="text-rose-600 hover:text-rose-800 font-bold text-xs px-2 py-1 flex items-center gap-1"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200 space-y-3">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto">
                        <Folder class="w-8 h-8 text-blue-600" />
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg">Research Workspace Masih Kosong</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">Anda belum menyimpan sumber artikel atau buku apapun. Gunakan pencarian LITERA untuk menemukan dan menyimpan literatur.</p>
                    <Link :href="route('litera.search')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md hover:bg-blue-700">
                        <Search class="w-4 h-4" />
                        <span>Cari Literatur Sekarang</span>
                    </Link>
                </div>

            </div>
        </div>

        <!-- Create Topic Modal -->
        <div v-if="isCreateTopicModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-lg">Buat Topik Penelitian Baru</h3>
                    <button @click="isCreateTopicModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitTopic" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Topik *</label>
                        <input
                            v-model="topicForm.title"
                            type="text"
                            required
                            placeholder="Contoh: Dampak AI Terhadap Pembelajaran"
                            class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Ringkas</label>
                        <textarea
                            v-model="topicForm.description"
                            rows="3"
                            placeholder="Catatan mengenai fokus pengkajian topik ini..."
                            class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="isCreateTopicModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" :disabled="topicForm.processing" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-blue-700">
                            Simpan Topik
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Notes & Status Modal -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-lg">Edit Catatan & Status Bacaan</h3>
                    <button @click="isEditModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompokkan ke Topik</label>
                        <select v-model="editForm.research_topic_id" class="w-full text-xs rounded-xl border-slate-200">
                            <option :value="null">-- Tanpa Topik --</option>
                            <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.title }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Bacaan *</label>
                        <select v-model="editForm.status" class="w-full text-xs rounded-xl border-slate-200 font-bold">
                            <option value="unread">Belum Dibaca</option>
                            <option value="reading">Sedang Dibaca</option>
                            <option value="completed">Selesai Dibaca</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pribadi</label>
                        <textarea
                            v-model="editForm.notes"
                            rows="4"
                            placeholder="Tuliskan ide utama, ringkasan, atau kutipan penting dari sumber ini..."
                            class="w-full text-xs rounded-xl border-slate-200 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold">
                            Batal
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-blue-700">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Embedded In-App PDF Reader Modal -->
        <PdfViewerModal
            :show="isPdfReaderOpen"
            :title="pdfReaderItem.title"
            :author="pdfReaderItem.author"
            :pdfUrl="pdfReaderItem.pdfUrl"
            :sourceType="pdfReaderItem.sourceType"
            @close="isPdfReaderOpen = false"
        />

    </AuthenticatedLayout>
</template>
