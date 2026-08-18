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
    Bookmark, 
    ExternalLink, 
    HelpCircle, 
    Check, 
    X, 
    Compass, 
    ArrowRight,
    Loader2,
    ShieldCheck,
    FileSpreadsheet
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
                                <button
                                    @click="openExplainModal(item)"
                                    class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-bold"
                                >
                                    <HelpCircle class="w-4 h-4 text-blue-600" />
                                    <span>Mengapa Relevan?</span>
                                </button>

                                <div class="flex items-center gap-2">
                                    <a
                                        v-if="item.url || item.pdf_url"
                                        :href="item.pdf_url || item.url"
                                        target="_blank"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition flex items-center gap-1"
                                    >
                                        <span>Buka Artikel</span>
                                        <ExternalLink class="w-3.5 h-3.5" />
                                    </a>

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

    </component>
</template>
