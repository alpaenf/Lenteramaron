<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BookMarked, 
    Bookmark, 
    Search, 
    Folder, 
    Sparkles, 
    TrendingUp, 
    ExternalLink, 
    Clock, 
    Check, 
    BookOpen,
    Compass
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    recent_searches: Array,
    recent_saved: Array,
    charts: Object,
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Dashboard Analytics — LITERA" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Executive Research Analytics Summary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Stat 1: Total Reference Books -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <BookMarked class="w-5 h-5 text-blue-600" />
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100">
                            <TrendingUp class="w-3 h-3 text-emerald-600" /> Catalog
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Koleksi Referensi</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">{{ (stats?.total_books || 0).toLocaleString() }}</p>
                </div>

                <!-- Stat 2: Saved Research Sources -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <Bookmark class="w-5 h-5 text-blue-600" />
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100">
                            Workspace
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sumber Tersimpan</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">{{ (stats?.total_saved_sources || 0).toLocaleString() }}</p>
                </div>

                <!-- Stat 3: Total AI Searches Executed -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <Search class="w-5 h-5 text-blue-600" />
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-100">
                            <Sparkles class="w-3 h-3 text-amber-600" /> AI Engine
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pencarian Riset AI</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">{{ (stats?.total_searches || 0).toLocaleString() }}</p>
                </div>

                <!-- Stat 4: Research Topics Created -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <Folder class="w-5 h-5 text-blue-600" />
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100">
                            Proyek Riset
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Topik Penelitian</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">{{ (stats?.total_topics || 0).toLocaleString() }}</p>
                </div>

            </div>

            <!-- Quick AI Search Banner -->
            <div class="bg-gradient-to-r from-blue-700 via-slate-900 to-blue-950 p-6 sm:p-8 rounded-3xl text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="space-y-2 text-center sm:text-left">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-blue-200 border border-white/10">
                        <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                        <span>AI-Powered Discovery Navigator</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black">Mulai Eksplorasi Penelitian Baru</h3>
                    <p class="text-xs text-slate-300 max-w-xl">Cari literatur ilmiah dari jurnal internasional dan hubungkan dengan basis referensi buku lokal secara langsung.</p>
                </div>
                <Link
                    :href="route('litera.search')"
                    class="px-6 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold rounded-2xl text-xs shadow-lg transition flex items-center gap-2 whitespace-nowrap"
                >
                    <Compass class="w-4 h-4 text-blue-200" />
                    <span>Buka AI Research Search</span>
                </Link>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Column: Recent Search Log (7 Cols) -->
                <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Pencarian Riset Terkini</h3>
                            <p class="text-xs text-slate-500">Log pencarian kueri bahasa alami dari pengguna</p>
                        </div>
                        <Link :href="route('litera.search')" class="text-xs font-bold text-blue-600 hover:underline">Cari Baru →</Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table v-if="recent_searches && recent_searches.length > 0" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-slate-500 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider bg-slate-50/50">
                                    <th class="p-3">Kueri Pencarian</th>
                                    <th class="p-3">Jumlah Hasil</th>
                                    <th class="p-3">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <tr v-for="q in recent_searches" :key="q.id" class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3 font-semibold text-slate-900">
                                        <Link :href="route('litera.search', { q: q.query_text })" class="hover:text-blue-600 flex items-center gap-2">
                                            <Search class="w-3.5 h-3.5 text-blue-600 shrink-0" />
                                            <span>"{{ q.query_text }}"</span>
                                        </Link>
                                    </td>
                                    <td class="p-3 font-bold text-blue-700">
                                        {{ q.results_count }} Hasil
                                    </td>
                                    <td class="p-3 text-slate-400 font-medium">
                                        {{ formatDate(q.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-else class="py-12 text-center text-slate-400 text-xs">
                            Belum ada riwayat pencarian riset.
                        </div>
                    </div>
                </div>

                <!-- Right Column: Recent Workspace Bookmarks (5 Cols) -->
                <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Sumber Tersimpan Baru</h3>
                            <p class="text-xs text-slate-500">Bookmark literatur di Research Workspace</p>
                        </div>
                        <Link :href="route('litera.workspace')" class="text-xs font-bold text-blue-600 hover:underline">Buka Workspace →</Link>
                    </div>

                    <div v-if="recent_saved && recent_saved.length > 0" class="space-y-3">
                        <div
                            v-for="saved in recent_saved"
                            :key="saved.id"
                            class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 hover:border-blue-200 transition space-y-1"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span :class="[
                                    'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                    saved.source_type === 'local' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'
                                ]">
                                    {{ saved.source_type === 'local' ? 'Buku Referensi' : 'Jurnal Eksternal' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(saved.created_at) }}</span>
                            </div>

                            <h4 class="font-bold text-xs text-slate-900 line-clamp-1">
                                {{ saved.source_type === 'local' ? saved.book?.title : saved.external_source?.title }}
                            </h4>

                            <p v-if="saved.notes" class="text-[11px] text-slate-600 italic line-clamp-1">
                                "{{ saved.notes }}"
                            </p>
                        </div>
                    </div>

                    <div v-else class="py-12 text-center text-slate-400 text-xs">
                        Belum ada sumber yang disimpan di Research Workspace.
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
