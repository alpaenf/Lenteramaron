<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import axios from 'axios';
import { 
    Search, 
    Sparkles, 
    BookOpen, 
    BookOpenText,
    Bookmark, 
    ExternalLink, 
    HelpCircle, 
    Check, 
    X, 
    Compass, 
    ArrowRight,
    Loader2,
    ShieldCheck,
    FileSpreadsheet,
    Pin,
    Wrench,
    Lightbulb,
    Tag,
    BookMarked,
    CheckCircle2,
    Library,
    MapPin,
    Barcode
} from 'lucide-vue-next';

const props = defineProps({
    initialQuery: {
        type: String,
        default: ''
    },
    initialSourceType: {
        type: String,
        default: ''
    },
    initialYearRange: {
        type: String,
        default: '5_years'
    },
    results: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const query = ref(props.initialQuery);
const sourceType = ref(props.initialSourceType || 'all');
const yearRange = ref(props.initialYearRange || '5_years');
const isLoading = ref(false);
const searchData = ref(props.results && props.results.items ? props.results : null);

// Modal / Drawer States
const selectedItemForExplain = ref(null);
const isExplainModalOpen = ref(false);
const explanationText = ref('');
const isExplainLoading = ref(false);

const isPathModalOpen = ref(false);
const isPathLoading = ref(false);
const researchPathSteps = ref([]);

// Deep Content Analysis Modal State
const isAnalyzeModalOpen = ref(false);
const selectedItemForAnalyze = ref(null);
const analysisData = ref(null);
const isAnalyzeLoading = ref(false);

const openAnalyzeModal = async (item) => {
    selectedItemForAnalyze.value = item;
    isAnalyzeModalOpen.value = true;
    analysisData.value = null;
    isAnalyzeLoading.value = true;

    try {
        const res = await axios.post('/litera/api/analyze', {
            q: query.value,
            item: item
        });
        analysisData.value = res.data.analysis;
    } catch (e) {
        showToast('Gagal melakukan analisis isi.', 'error');
    } finally {
        isAnalyzeLoading.value = false;
    }
};

// Reader Modal State
const isReaderModalOpen = ref(false);
const readerItem = ref(null);

const getGoogleBooksUrl = (item) => {
    if (item.isbn) {
        return `https://books.google.com/books?vid=ISBN${item.isbn}`;
    }
    const q = encodeURIComponent(item.title + (item.author ? ' ' + item.author : ''));
    return `https://books.google.com/books?q=${q}`;
};

const openReaderModal = (item) => {
    readerItem.value = item;
    isReaderModalOpen.value = true;
};

const getReadLabel = (item) => {
    if (item.source_type === 'local') return 'Baca di Google Books';
    if (item.pdf_url) return 'Baca PDF';
    if (item.url) return 'Buka Artikel';
    return null;
};

const canRead = (item) => {
    if (item.source_type === 'local') return true;
    return !!(item.pdf_url || item.url);
};

// Notification alert
const toastMessage = ref('');
const toastType = ref('success');

const showToast = (msg, type = 'success') => {
    toastMessage.value = msg;
    toastType.value = type;
    setTimeout(() => {
        toastMessage.value = '';
    }, 4000);
};

const handleSearch = async () => {
    if (!query.value || query.value.trim().length < 2) return;

    isLoading.value = true;
    try {
        const response = await axios.post('/litera/api/search', {
            q: query.value,
            source_type: sourceType.value === 'all' ? '' : sourceType.value,
            year_range: yearRange.value
        });
        searchData.value = response.data;
    } catch (err) {
        showToast('Gagal melakukan pencarian. Silakan coba lagi.', 'error');
    } finally {
        isLoading.value = false;
    }
};

const setFilter = (type) => {
    sourceType.value = type;
    if (query.value) {
        handleSearch();
    }
};

const openExplainModal = async (item) => {
    selectedItemForExplain.value = item;
    isExplainModalOpen.value = true;
    explanationText.value = item.why_relevant || '';

    if (!explanationText.value) {
        isExplainLoading.value = true;
        try {
            const res = await axios.post('/litera/api/explain', {
                q: query.value,
                item: item
            });
            explanationText.value = res.data.explanation;
        } catch (e) {
            explanationText.value = 'Gagal memuat penjelasan relevansi.';
        } finally {
            isExplainLoading.value = false;
        }
    }
};

const openResearchPathModal = async () => {
    if (!searchData.value || !searchData.value.items || searchData.value.items.length === 0) return;

    isPathModalOpen.value = true;
    isPathLoading.value = true;
    try {
        const res = await axios.post('/litera/api/path', {
            q: query.value,
            sources: searchData.value.items
        });
        researchPathSteps.value = res.data.steps || [];
    } catch (e) {
        showToast('Gagal membuat Research Path.', 'error');
    } finally {
        isPathLoading.value = false;
    }
};

const saveSourceForm = useForm({
    source_type: 'local',
    book_id: null,
    external_source_id: null,
    raw_external: null,
    notes: '',
    status: 'unread'
});

const saveToWorkspace = (item) => {
    if (!isAuthenticated.value) {
        showToast('Silakan login terlebih dahulu untuk menyimpan ke Research Workspace.', 'error');
        return;
    }

    saveSourceForm.reset();
    saveSourceForm.source_type = item.source_type;
    if (item.source_type === 'local') {
        saveSourceForm.book_id = item.db_id;
    } else {
        saveSourceForm.external_source_id = item.db_id || null;
        saveSourceForm.raw_external = item;
    }

    saveSourceForm.post(route('litera.workspace.saved.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Sumber berhasil disimpan ke Research Workspace Anda.');
        },
        onError: (errors) => {
            showToast(errors.message || 'Gagal menyimpan sumber.', 'error');
        }
    });
};

const filteredItems = computed(() => {
    if (!searchData.value || !searchData.value.items) return [];
    if (sourceType.value === 'all') return searchData.value.items;
    return searchData.value.items.filter(i => i.source_type === sourceType.value);
});
</script>

<template>
    <Head title="AI Research & Library Navigator — LITERA" />

    <component :is="isAuthenticated ? AuthenticatedLayout : GuestLayout">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight flex items-center gap-2">
                        <span class="bg-blue-600 text-white px-2.5 py-1 rounded-lg text-lg font-black tracking-wider shadow-sm">LITERA</span>
                        <span>AI Research Navigator</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">From Library Knowledge to Research Discovery</p>
                </div>

                <div v-if="isAuthenticated" class="flex items-center gap-2">
                    <Link :href="route('litera.workspace')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl font-bold text-xs transition border border-blue-200">
                        <Bookmark class="w-4 h-4 text-blue-600" />
                        <span>Research Workspace</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Toast Notification -->
                <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="toastMessage" :class="[
                        'p-4 rounded-xl shadow-lg flex items-center justify-between text-sm font-medium text-white',
                        toastType === 'error' ? 'bg-rose-500' : 'bg-emerald-500'
                    ]">
                        <span>{{ toastMessage }}</span>
                        <button @click="toastMessage = ''" class="ml-4 p-1 hover:bg-white/20 rounded-lg">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </transition>

                <!-- Search Hero Box with Blue Theme -->
                <div class="bg-gradient-to-br from-blue-700 via-slate-900 to-blue-950 rounded-3xl p-6 sm:p-10 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute left-1/3 -bottom-10 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl mx-auto text-center space-y-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-200 border border-blue-400/30">
                            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                            <span>AI Knowledge Discovery Engine</span>
                        </span>
                        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Apa yang ingin Anda teliti hari ini?</h1>
                        <p class="text-slate-300 text-xs sm:text-sm">Tulis topik penelitian dalam bahasa alami. LITERA akan menghubungkan koleksi perpustakaan lokal dengan sumber ilmiah internasional.</p>

                        <!-- Search Form -->
                        <form @submit.prevent="handleSearch" class="mt-6 flex flex-col sm:flex-row items-center gap-2 bg-white/10 p-2 rounded-2xl backdrop-blur-md border border-white/10 shadow-inner">
                            <div class="relative w-full">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <Search class="w-5 h-5" />
                                </span>
                                <input
                                    v-model="query"
                                    type="text"
                                    placeholder="Contoh: Pengaruh Generative AI terhadap pembelajaran..."
                                    class="w-full pl-11 pr-4 py-3 bg-transparent text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-0 border-0"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="isLoading || !query.trim()"
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2 whitespace-nowrap"
                            >
                                <Loader2 v-if="isLoading" class="animate-spin h-4 w-4 text-white" />
                                <span>Cari & Navigasi</span>
                            </button>
                        </form>

                        <!-- Quick Tags -->
                        <div class="flex flex-wrap justify-center items-center gap-2 pt-2 text-xs text-slate-300">
                            <span class="text-slate-400 font-medium">Contoh Topik:</span>
                            <button @click="query = 'Kecerdasan Buatan dalam Pendidikan'; handleSearch()" class="hover:text-white underline hover:no-underline">AI Pendidikan</button>
                            <span class="text-slate-500">•</span>
                            <button @click="query = 'Metode Pembelajaran Literasi Membaca'; handleSearch()" class="hover:text-white underline hover:no-underline">Literasi Membaca</button>
                            <span class="text-slate-500">•</span>
                            <button @click="query = 'Manajemen Perpustakaan Digital'; handleSearch()" class="hover:text-white underline hover:no-underline">Perpustakaan Digital</button>
                        </div>
                    </div>
                </div>

                <!-- Results & Navigation Area -->
                <div v-if="searchData" class="space-y-6">

                    <!-- Result Header Bar & Filter Tabs -->
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-lg">Hasil Penemuan Literatur</h3>
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-full text-xs font-bold">{{ searchData.total_results || 0 }} Sumber</span>
                            </div>
                            <p v-if="searchData.intent" class="text-xs text-blue-600 font-medium mt-1">
                                Intent: {{ searchData.intent }}
                            </p>
                        </div>

                        <!-- Filters (Source & Year) -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                                <button
                                    @click="setFilter('all')"
                                    :class="['px-3 py-1.5 rounded-lg transition font-bold', sourceType === 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900']"
                                >
                                    Semua Sumber
                                </button>
                                <button
                                    @click="setFilter('local')"
                                    :class="['px-3 py-1.5 rounded-lg transition font-bold', sourceType === 'local' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900']"
                                >
                                    Perpustakaan (Lokal)
                                </button>
                                <button
                                    @click="setFilter('external')"
                                    :class="['px-3 py-1.5 rounded-lg transition font-bold', sourceType === 'external' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900']"
                                >
                                    Jurnal (Eksternal)
                                </button>
                            </div>

                            <!-- Year Range Filter (Academic Standard: 5 Years) -->
                            <div class="flex items-center gap-1.5 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100 text-xs font-bold text-slate-700">
                                <span class="text-blue-600 font-extrabold text-[11px]">Tahun Terbit:</span>
                                <select
                                    v-model="yearRange"
                                    @change="handleSearch"
                                    class="bg-transparent border-0 text-xs font-extrabold text-blue-900 focus:ring-0 py-0 pl-1 pr-6 cursor-pointer"
                                >
                                    <option value="5_years">5 Tahun Terakhir (Standar Akademik)</option>
                                    <option value="3_years">3 Tahun Terakhir (Terbaru)</option>
                                    <option value="10_years">10 Tahun Terakhir</option>
                                    <option value="all">Semua Tahun</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action: Generate Research Path -->
                        <button
                            @click="openResearchPathModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition"
                        >
                            <Compass class="w-4 h-4 text-blue-200" />
                            <span>Jalur Eksplorasi (Research Path)</span>
                        </button>
                    </div>

                    <!-- Loading Skeleton -->
                    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="n in 4" :key="n" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 animate-pulse space-y-3">
                            <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                            <div class="h-6 bg-slate-200 rounded w-3/4"></div>
                            <div class="h-12 bg-slate-100 rounded w-full"></div>
                        </div>
                    </div>

                    <!-- Source Cards Grid -->
                    <div v-else-if="filteredItems.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between space-y-4 relative group"
                        >
                            <div class="space-y-2">
                                <!-- Top Badges -->
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span :class="[
                                            'px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px]',
                                            item.source_type === 'local' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-blue-100 text-blue-800 border border-blue-200'
                                        ]">
                                            {{ item.source_type === 'local' ? 'Koleksi Perpustakaan' : 'Jurnal Eksternal (' + (item.source_provider || 'OpenAlex') + ')' }}
                                        </span>

                                        <span v-if="item.open_access" class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-semibold border border-amber-200">
                                            Open Access
                                        </span>
                                    </div>

                                    <!-- Relevance Score Badge -->
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-extrabold border border-blue-100">
                                        <span>Relevansi {{ item.relevance_percent }}%</span>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h4 class="font-bold text-slate-900 text-base leading-snug group-hover:text-blue-600 transition">
                                    {{ item.title }}
                                </h4>

                                <!-- Meta Info -->
                                <div class="text-xs text-slate-500 font-medium space-y-1">
                                    <p v-if="item.author"><strong>Penulis:</strong> {{ item.author }}</p>
                                    <p v-if="item.publisher_or_journal"><strong>Penerbit/Jurnal:</strong> {{ item.publisher_or_journal }} ({{ item.publication_year || 'N/A' }})</p>
                                    <p v-if="item.source_type === 'local'">
                                        <strong>Status Koleksi:</strong> 
                                        <span :class="item.stock > 0 ? 'text-emerald-700 font-bold' : 'text-amber-700 font-bold'">
                                            {{ item.stock > 0 ? 'Tersedia di Katalog (Siap Dipinjam)' : 'Sedang Dipinjam' }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Abstract Snippet -->
                                <p v-if="item.abstract" class="text-xs text-slate-600 line-clamp-3 bg-slate-50 p-3 rounded-xl border border-slate-100 italic">
                                    "{{ item.abstract }}"
                                </p>
                            </div>

                            <!-- Card Action Footer -->
                            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="openExplainModal(item)"
                                        class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-bold"
                                    >
                                        <HelpCircle class="w-4 h-4 text-blue-600" />
                                        <span>Mengapa Relevan?</span>
                                    </button>

                                    <button
                                        @click="openAnalyzeModal(item)"
                                        class="inline-flex items-center gap-1.5 text-purple-600 hover:text-purple-800 font-bold"
                                    >
                                        <Sparkles class="w-4 h-4 text-purple-600" />
                                        <span>Bedah Isi AI</span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Smart Read Button -->
                                    <button
                                        v-if="canRead(item)"
                                        @click="openReaderModal(item)"
                                        class="px-3 py-1.5 font-bold rounded-lg shadow-sm transition flex items-center gap-1 text-white"
                                        :class="item.source_type === 'local' ? 'bg-emerald-600 hover:bg-emerald-700' : (item.pdf_url ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-slate-500 hover:bg-slate-600')"
                                    >
                                        <BookOpenText class="w-3.5 h-3.5" />
                                        <span>{{ getReadLabel(item) }}</span>
                                    </button>

                                    <button
                                        @click="saveToWorkspace(item)"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition flex items-center gap-1"
                                    >
                                        <Bookmark class="w-3.5 h-3.5" />
                                        <span>Simpan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Results State -->
                    <div v-else class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200 space-y-3">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto">
                            <BookOpen class="w-8 h-8 text-blue-600" />
                        </div>
                        <h4 class="font-bold text-slate-800 text-lg">Tidak ada sumber yang cocok</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">Coba gunakan kata kunci pencarian yang lebih umum atau ubah filter tipe sumber.</p>
                    </div>

                </div>

            </div>
        </div>

        <!-- Relevance Explanation Modal -->
        <div v-if="isExplainModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <Sparkles class="w-4 h-4 text-blue-600" />
                        <span>Penjelasan Relevansi AI</span>
                    </h3>
                    <button @click="isExplainModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="selectedItemForExplain" class="space-y-3 text-xs">
                    <p class="font-semibold text-slate-800 text-sm">{{ selectedItemForExplain.title }}</p>

                    <div v-if="isExplainLoading" class="p-6 text-center text-slate-500 space-y-2">
                        <Loader2 class="animate-spin h-6 w-6 text-blue-600 mx-auto" />
                        <p class="font-medium">Menganalisis keterkaitan sumber...</p>
                    </div>

                    <div v-else class="bg-blue-50/70 text-blue-950 p-4 rounded-2xl border border-blue-100 leading-relaxed font-medium">
                        {{ explanationText }}
                    </div>
                </div>

                <div class="pt-2 text-right">
                    <button @click="isExplainModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Research Path Timeline Modal -->
        <div v-if="isPathModalOpen" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-slate-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Alur Pembelajaran</span>
                        <h3 class="font-extrabold text-slate-900 text-xl mt-1">Jalur Eksplorasi Penelitian (Research Path)</h3>
                    </div>
                    <button @click="isPathModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="isPathLoading" class="py-12 text-center text-slate-500 space-y-2">
                    <Loader2 class="animate-spin h-8 w-8 text-blue-600 mx-auto" />
                    <p class="font-medium text-xs">Menyusun rekomendasi alur urutan membaca...</p>
                </div>

                <div v-else class="relative border-l-2 border-blue-200 ml-4 space-y-6">
                    <div v-for="step in researchPathSteps" :key="step.step" class="relative pl-6">
                        <span class="absolute -left-3 top-0 w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-md">
                            {{ step.step }}
                        </span>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-1">
                            <h4 class="font-bold text-slate-900 text-sm">{{ step.title }}</h4>
                            <p class="text-xs text-slate-600">{{ step.description }}</p>
                            <div v-if="step.recommended_source_title" class="mt-2 pt-2 border-t border-slate-200 text-[11px] text-blue-700 font-semibold flex items-center gap-1">
                                <span>Rekomendasi Bacaan:</span>
                                <span class="italic font-normal text-slate-800">"{{ step.recommended_source_title }}"</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button @click="isPathModalOpen = false" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700 shadow-md">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
        <!-- ============================================ -->
        <!-- READER MODAL                                -->
        <!-- ============================================ -->
        <div v-if="isReaderModalOpen && readerItem" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white rounded-3xl w-full max-w-5xl shadow-2xl border border-slate-100 flex flex-col" style="height: 92vh;">
                <!-- Header -->
                <div class="flex items-start justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span :class="[
                                'px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px]',
                                readerItem.source_type === 'local' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200'
                            ]">
                                {{ readerItem.source_type === 'local' ? 'Koleksi Perpustakaan' : 'Jurnal Eksternal' }}
                            </span>
                            <span v-if="readerItem.pdf_url" class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-semibold border border-amber-200">PDF Open Access</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base leading-snug line-clamp-2">{{ readerItem.title }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5" v-if="readerItem.author">{{ readerItem.author }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Open in new tab -->
                        <a
                            :href="readerItem.source_type === 'local' ? getGoogleBooksUrl(readerItem) : (readerItem.pdf_url || readerItem.url)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition"
                        >
                            <ExternalLink class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Buka Tab Baru</span>
                        </a>
                        <button @click="isReaderModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 transition">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Reader Body -->
                <div class="flex-1 overflow-hidden rounded-b-3xl bg-slate-100">
                    <!-- Local Book: Digital Catalog Card UI (Fixes Google Books iFrame SameOrigin block) -->
                    <template v-if="readerItem.source_type === 'local'">
                        <div class="h-full overflow-y-auto p-6 sm:p-8 space-y-6 bg-slate-50">
                            <!-- Physical Availability & Location Banner -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                                            <Library class="w-5 h-5 text-emerald-700" />
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Koleksi Fisik Perpustakaan</span>
                                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">Informasi Lokasi & Stok Katalog</h4>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-xs font-black shadow-xs',
                                            readerItem.stock > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200'
                                        ]">
                                            {{ readerItem.stock > 0 ? '✓ Tersedia (' + readerItem.stock + ' Eksemplar)' : '⚠️ Sedang Dipinjam' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/70 space-y-1">
                                        <span class="text-slate-400 font-bold uppercase text-[10px] flex items-center gap-1">
                                            <MapPin class="w-3.5 h-3.5 text-blue-600" />
                                            <span>Lokasi Rak</span>
                                        </span>
                                        <p class="font-extrabold text-slate-900 text-sm">{{ readerItem.rack_name || '-' }}</p>
                                    </div>

                                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/70 space-y-1">
                                        <span class="text-slate-400 font-bold uppercase text-[10px] flex items-center gap-1">
                                            <Barcode class="w-3.5 h-3.5 text-purple-600" />
                                            <span>Nomor ISBN</span>
                                        </span>
                                        <p class="font-extrabold text-slate-900 text-sm">{{ readerItem.isbn || '-' }}</p>
                                    </div>

                                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/70 space-y-1">
                                        <span class="text-slate-400 font-bold uppercase text-[10px] flex items-center gap-1">
                                            <BookOpen class="w-3.5 h-3.5 text-emerald-600" />
                                            <span>Penerbit & Tahun</span>
                                        </span>
                                        <p class="font-extrabold text-slate-900 text-sm">{{ readerItem.publisher_or_journal || '-' }} ({{ readerItem.publication_year || 'N/A' }})</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Abstract / Description -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                                <h5 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                    <BookOpenText class="w-4 h-4 text-blue-600" />
                                    <span>Ringkasan & Sinopsis Buku</span>
                                </h5>
                                <p class="text-xs text-slate-700 leading-relaxed font-medium">
                                    {{ readerItem.abstract || 'Belum ada ringkasan tertulis untuk buku ini. Silakan gunakan fitur Bedah Isi AI untuk menghasilkan analisis otomatis.' }}
                                </p>
                            </div>

                            <!-- External Links & Actions -->
                            <div class="bg-blue-50/70 p-6 rounded-2xl border border-blue-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="space-y-1 text-center sm:text-left">
                                    <h5 class="font-extrabold text-blue-900 text-xs">Ingin Membaca Preview Digital?</h5>
                                    <p class="text-[11px] text-blue-700 font-medium">Buka pratinjau cuplikan halaman resmi di Google Books melalui tab baru.</p>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <button
                                        @click="openAnalyzeModal(readerItem); isReaderModalOpen = false;"
                                        class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-1.5"
                                    >
                                        <Sparkles class="w-4 h-4 text-white" />
                                        <span>Bedah Isi AI</span>
                                    </button>

                                    <a
                                        :href="getGoogleBooksUrl(readerItem)"
                                        target="_blank"
                                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-2"
                                    >
                                        <ExternalLink class="w-4 h-4" />
                                        <span>Buka Google Books Preview</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- External with PDF URL: PDF viewer -->
                    <template v-else-if="readerItem.pdf_url">
                        <iframe
                            :src="`https://docs.google.com/gview?url=${encodeURIComponent(readerItem.pdf_url)}&embedded=true`"
                            class="w-full h-full rounded-b-3xl"
                            frameborder="0"
                            allowfullscreen
                            title="PDF Reader"
                        />
                    </template>

                    <!-- External with URL only: landing page embed -->
                    <template v-else-if="readerItem.url">
                        <div class="flex flex-col items-center justify-center h-full space-y-6 p-8">
                            <div class="w-20 h-20 rounded-full bg-indigo-50 flex items-center justify-center">
                                <BookOpenText class="w-10 h-10 text-indigo-500" />
                            </div>
                            <div class="text-center space-y-2">
                                <h4 class="font-bold text-slate-800 text-lg">Baca di Sumber Asli</h4>
                                <p class="text-sm text-slate-500 max-w-sm">Artikel ini tidak memiliki PDF terbuka. Klik tombol di bawah untuk membacanya langsung di situs jurnal asli.</p>
                            </div>
                            <a
                                :href="readerItem.url"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg transition text-sm"
                            >
                                <ExternalLink class="w-4 h-4" />
                                Buka di Sumber Asli
                            </a>
                            <p class="text-xs text-slate-400">{{ readerItem.url }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- DEEP CONTENT ANALYSIS MODAL                 -->
        <!-- ============================================ -->
        <div v-if="isAnalyzeModalOpen" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-purple-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                            <Sparkles class="w-4 h-4 text-purple-600" />
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">AI Deep Synthesizer</span>
                            <h3 class="font-bold text-slate-900 text-base leading-tight mt-0.5">Bedah Isi & Analisis Akademis</h3>
                        </div>
                    </div>
                    <button @click="isAnalyzeModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 transition">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="selectedItemForAnalyze" class="space-y-4">
                    <!-- Title Header -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70">
                        <h4 class="font-bold text-slate-900 text-sm leading-snug">{{ selectedItemForAnalyze.title }}</h4>
                        <p class="text-xs text-slate-500 mt-1 font-medium" v-if="selectedItemForAnalyze.author">Penulis: {{ selectedItemForAnalyze.author }}</p>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isAnalyzeLoading" class="py-12 text-center text-slate-500 space-y-3">
                        <Loader2 class="animate-spin h-8 w-8 text-purple-600 mx-auto" />
                        <p class="font-medium text-xs text-purple-900">Sedang membedah struktur isi literatur dengan Groq AI...</p>
                    </div>

                    <!-- Analysis Result Sections -->
                    <div v-else-if="analysisData" class="space-y-4 text-xs">
                        <!-- Recommended Chapter Tag -->
                        <div v-if="analysisData.rekomendasi_bab" class="flex items-center gap-2 bg-purple-100/80 px-3.5 py-2 rounded-xl text-purple-900 border border-purple-200">
                            <BookMarked class="w-4 h-4 text-purple-700 shrink-0" />
                            <span class="font-extrabold text-xs">Rekomendasi Sitasi Skripsi/Makalah:</span>
                            <span class="bg-white px-2.5 py-0.5 rounded-md font-black text-purple-800 text-[11px] border border-purple-200">{{ analysisData.rekomendasi_bab }}</span>
                        </div>

                        <!-- 1. Fokus Utama -->
                        <div class="bg-purple-50/70 p-4 rounded-2xl border border-purple-100 space-y-1.5">
                            <h5 class="font-extrabold text-purple-900 text-xs flex items-center gap-2">
                                <Pin class="w-4 h-4 text-purple-600 shrink-0" />
                                <span>Fokus & Ruang Lingkup Utama</span>
                            </h5>
                            <p class="text-slate-700 font-medium leading-relaxed">{{ analysisData.fokus_utama }}</p>
                        </div>

                        <!-- 2. Metodologi & Pendekatan -->
                        <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-100 space-y-1.5">
                            <h5 class="font-extrabold text-blue-900 text-xs flex items-center gap-2">
                                <Wrench class="w-4 h-4 text-blue-600 shrink-0" />
                                <span>Metodologi & Kerangka Pendekatan</span>
                            </h5>
                            <p class="text-slate-700 font-medium leading-relaxed">{{ analysisData.metodologi_pendekatan }}</p>
                        </div>

                        <!-- 3. Temuan Kunci & Kontribusi -->
                        <div class="bg-emerald-50/70 p-4 rounded-2xl border border-emerald-100 space-y-2">
                            <h5 class="font-extrabold text-emerald-900 text-xs flex items-center gap-2">
                                <Lightbulb class="w-4 h-4 text-emerald-600 shrink-0" />
                                <span>Temuan Kunci & Kontribusi Pengetahuan</span>
                            </h5>
                            <div v-if="Array.isArray(analysisData.temuan_kontribusi)" class="space-y-1.5 pl-1">
                                <div v-for="(item, idx) in analysisData.temuan_kontribusi" :key="idx" class="flex items-start gap-2 text-slate-700 font-medium leading-relaxed">
                                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                    <span>{{ item }}</span>
                                </div>
                            </div>
                            <p v-else class="text-slate-700 font-medium leading-relaxed whitespace-pre-line">{{ analysisData.temuan_kontribusi }}</p>
                        </div>

                        <!-- 4. Kata Kunci & Konsep -->
                        <div v-if="analysisData.kata_kunci_konsep && analysisData.kata_kunci_konsep.length > 0" class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2">
                            <h5 class="font-extrabold text-slate-800 text-xs flex items-center gap-2">
                                <Tag class="w-4 h-4 text-slate-600 shrink-0" />
                                <span>Konsep & Istilah Utama</span>
                            </h5>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="(kw, idx) in analysisData.kata_kunci_konsep" :key="idx" class="bg-white text-slate-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200 text-[11px]">
                                    #{{ kw }}
                                </span>
                            </div>
                        </div>

                        <!-- 5. Implikasi Riset & Petunjuk Integrasi -->
                        <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-100 space-y-1.5">
                            <h5 class="font-extrabold text-amber-900 text-xs flex items-center gap-2">
                                <Search class="w-4 h-4 text-amber-600 shrink-0" />
                                <span>Petunjuk Integrasi & Potensi Riset Lanjutan</span>
                            </h5>
                            <p class="text-slate-700 font-medium leading-relaxed">{{ analysisData.implikasi_riset }}</p>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 text-right">
                    <button @click="isAnalyzeModalOpen = false" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                        Tutup Bedah AI
                    </button>
                </div>
            </div>
        </div>

    </component>
</template>
