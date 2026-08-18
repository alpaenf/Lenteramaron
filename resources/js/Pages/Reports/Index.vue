<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BarChart3, 
    FileText, 
    Download, 
    Calendar, 
    Search, 
    Bookmark, 
    Folder, 
    BookMarked,
    Sparkles
} from 'lucide-vue-next';

const props = defineProps({
    reportData: Array,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const type = ref(props.filters?.type || 'search_queries');

const filterReport = () => {
    router.get('/reports', {
        start_date: startDate.value,
        end_date: endDate.value,
        type: type.value,
    }, { preserveState: true, replace: true });
};

const downloadPdf = () => {
    window.open(`/reports/export-pdf?start_date=${startDate.value}&end_date=${endDate.value}&type=${type.value}`, '_blank');
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    } catch (e) {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Laporan Analitis Penelitian — LITERA" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Top Header & Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan Analitis Penelitian &amp; Literasi</h1>
                    <p class="text-xs text-slate-500 mt-1">Rekapitulasi pencarian riset, literatur tersimpan, dan katalog referensi dalam format PDF resmi.</p>
                </div>
                <div>
                    <button 
                        @click="downloadPdf" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2"
                    >
                        <Download class="w-4 h-4" />
                        <span>Unduh Laporan PDF</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls Grid -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Laporan</label>
                        <select v-model="type" @change="filterReport" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs bg-white font-medium focus:ring-2 focus:ring-blue-500/20 outline-none">
                            <option value="search_queries">Log Pencarian Riset AI</option>
                            <option value="saved_sources">Sumber Literatur Tersimpan</option>
                            <option value="topics">Proyek &amp; Topik Penelitian</option>
                            <option value="reference_books">Katalog Buku Referensi</option>
                        </select>
                    </div>

                    <div v-if="type !== 'reference_books'">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                        <input v-model="startDate" @change="filterReport" type="date" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 outline-none" />
                    </div>

                    <div v-if="type !== 'reference_books'">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai</label>
                        <input v-model="endDate" @change="filterReport" type="date" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 outline-none" />
                    </div>
                </div>
            </div>

            <!-- Data Table Preview Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6 space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <FileText class="w-5 h-5 text-blue-600" />
                        <span>Pratinjau Data Laporan</span>
                    </h3>
                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Total: {{ (reportData || []).length }} Data</span>
                </div>

                <div class="overflow-x-auto">
                    
                    <!-- Type 1: Search Queries -->
                    <table v-if="type === 'search_queries'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">#</th>
                                <th class="p-3">Kueri Pencarian Riset</th>
                                <th class="p-3">Jumlah Hasil AI</th>
                                <th class="p-3">Waktu Pencarian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in reportData" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 text-slate-400 font-bold">{{ idx + 1 }}</td>
                                <td class="p-3 font-semibold text-slate-900 flex items-center gap-2">
                                    <Search class="w-3.5 h-3.5 text-blue-600 shrink-0" />
                                    <span>"{{ item.query_text }}"</span>
                                </td>
                                <td class="p-3 font-bold text-blue-700">{{ item.results_count }} Hasil</td>
                                <td class="p-3 text-slate-500 font-medium">{{ formatDate(item.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Type 2: Saved Sources -->
                    <table v-else-if="type === 'saved_sources'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">#</th>
                                <th class="p-3">Tipe Sumber</th>
                                <th class="p-3">Judul Literatur</th>
                                <th class="p-3">Status Bacaan</th>
                                <th class="p-3">Tgl Disimpan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in reportData" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 text-slate-400 font-bold">{{ idx + 1 }}</td>
                                <td class="p-3">
                                    <span :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                        item.source_type === 'local' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'
                                    ]">
                                        {{ item.source_type === 'local' ? 'Buku Referensi' : 'Jurnal Eksternal' }}
                                    </span>
                                </td>
                                <td class="p-3 font-bold text-slate-900">
                                    {{ item.source_type === 'local' ? item.book?.title : item.external_source?.title }}
                                </td>
                                <td class="p-3 font-semibold text-slate-700 capitalize">{{ item.reading_status || 'unread' }}</td>
                                <td class="p-3 text-slate-500 font-medium">{{ formatDate(item.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Type 3: Topics -->
                    <table v-else-if="type === 'topics'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">#</th>
                                <th class="p-3">Nama Topik Penelitian</th>
                                <th class="p-3">Pembuat</th>
                                <th class="p-3">Jumlah Sumber</th>
                                <th class="p-3">Tgl Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in reportData" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 text-slate-400 font-bold">{{ idx + 1 }}</td>
                                <td class="p-3 font-bold text-slate-900 flex items-center gap-2">
                                    <Folder class="w-4 h-4 text-blue-600 shrink-0" />
                                    <span>{{ item.title }}</span>
                                </td>
                                <td class="p-3 text-slate-700 font-medium">{{ item.user?.name || 'Pengguna' }}</td>
                                <td class="p-3 font-bold text-emerald-700">{{ item.saved_sources_count || 0 }} Artikel</td>
                                <td class="p-3 text-slate-500 font-medium">{{ formatDate(item.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Type 4: Reference Books -->
                    <table v-else-if="type === 'reference_books'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">#</th>
                                <th class="p-3">Kode Buku</th>
                                <th class="p-3">Judul Buku Referensi</th>
                                <th class="p-3">Pengarang</th>
                                <th class="p-3">Kategori DDC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in reportData" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 text-slate-400 font-bold">{{ idx + 1 }}</td>
                                <td class="p-3 font-mono font-bold text-blue-900">{{ item.book_code }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ item.title }}</td>
                                <td class="p-3 text-slate-700 font-medium">{{ item.author }}</td>
                                <td class="p-3 font-medium text-slate-600">{{ item.category?.name || 'Umum' }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
