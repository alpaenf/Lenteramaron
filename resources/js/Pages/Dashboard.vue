<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BookOpen, Users, UserCheck, BookMarked, TrendingUp, CheckCircle, Clock, ChevronDown } from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    LineElement, 
    CategoryScale, 
    LinearScale, 
    PointElement
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const props = defineProps({
    stats: Object,
    popular_books: Array,
    charts: Object,
    recent_borrowings: Array,
});

const activityFilter = ref('Semua');
const chartPeriod = ref('daily_7');

// Filtered Recent Borrowings
const filteredActivities = computed(() => {
    if (!props.recent_borrowings) return [];
    if (activityFilter.value === 'Peminjaman') {
        return props.recent_borrowings.filter(b => b.status === 'Dipinjam');
    }
    if (activityFilter.value === 'Pengembalian') {
        return props.recent_borrowings.filter(b => b.status === 'Dikembalikan');
    }
    return props.recent_borrowings;
});

// Line Chart Configuration (Daily / Monthly / Yearly Trends)
const lineChartData = computed(() => {
    let source = props.charts?.daily_7 || props.charts?.monthly || { labels: [], borrowings: [], returns: [] };
    
    if (chartPeriod.value === 'daily_7' && props.charts?.daily_7) {
        source = props.charts.daily_7;
    } else if (chartPeriod.value === 'daily_30' && props.charts?.daily_30) {
        source = props.charts.daily_30;
    } else if (chartPeriod.value === 'monthly' && props.charts?.monthly) {
        source = props.charts.monthly;
    } else if (chartPeriod.value === 'yearly' && props.charts?.yearly) {
        source = props.charts.yearly;
    }

    return {
        labels: source.labels,
        datasets: [
            {
                label: 'Peminjaman Buku',
                borderColor: '#005da7',
                backgroundColor: 'rgba(0, 93, 167, 0.12)',
                data: source.borrowings,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#005da7',
                pointRadius: 4,
            },
            {
                label: 'Pengembalian Buku',
                borderColor: '#006d36',
                backgroundColor: 'rgba(0, 109, 54, 0.12)',
                data: source.returns,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#006d36',
                pointRadius: 4,
            }
        ]
    };
});

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' },
    },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
    }
};

const failedCovers = ref(new Set());

const getAssetUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('uploads/')) return `/${path}`;
    return `/files-media/${path}`;
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

const getInitials = (name) => {
    if (!name) return 'US';
    const words = name.split(' ');
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Summary Cards Bento Layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Books -->
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/40 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl">library_books</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-secondary bg-secondary/10 px-2.5 py-0.5 rounded-full">
                            <TrendingUp class="w-3 h-3" /> +12%
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">TOTAL BUKU</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight mt-1">{{ (stats.total_books || 0).toLocaleString() }}</p>
                </div>

                <!-- Members -->
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/40 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-secondary-container/40 text-secondary flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl">group</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-secondary bg-secondary/10 px-2.5 py-0.5 rounded-full">
                            <TrendingUp class="w-3 h-3" /> +5%
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">DATA ANGGOTA</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight mt-1">{{ (stats.total_members || 0).toLocaleString() }}</p>
                </div>

                <!-- Visitors -->
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/40 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-tertiary-container/30 text-tertiary flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl">person_pin</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-secondary bg-secondary/10 px-2.5 py-0.5 rounded-full">
                            <TrendingUp class="w-3 h-3" /> +15%
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">PENGUNJUNG HARI INI</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight mt-1">{{ (stats.total_visitors || 0).toLocaleString() }}</p>
                </div>

                <!-- Active Loans -->
                <div class="bg-surface-container-lowest p-5 rounded-2xl border border-outline-variant/40 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-container/20 text-primary flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl">import_contacts</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-secondary bg-secondary/10 px-2.5 py-0.5 rounded-full">
                            <TrendingUp class="w-3 h-3" /> +8%
                        </span>
                    </div>
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">PEMINJAMAN AKTIF</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight mt-1">{{ (stats.total_borrowings || 0).toLocaleString() }}</p>
                </div>
            </div>

            <!-- Main Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Chart Area (Left Column - 8 Cols) -->
                <div class="lg:col-span-8 bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/40 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-on-surface tracking-tight">Tren Sirkulasi &amp; Pengunjung</h3>
                            <p class="text-xs text-on-surface-variant">Statistik peminjaman dan pengembalian perpustakaan</p>
                        </div>
                        <div class="relative inline-block">
                            <select 
                                v-model="chartPeriod" 
                                class="appearance-none bg-surface-container-low hover:bg-surface-container border border-outline-variant/60 rounded-xl text-xs font-bold text-on-surface pl-3.5 pr-9 py-2 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition-all shadow-2xs"
                            >
                                <option value="daily_7" class="bg-surface py-1 text-on-surface">📅 Harian (7 Hari Terakhir)</option>
                                <option value="daily_30" class="bg-surface py-1 text-on-surface">📅 Harian (30 Hari Terakhir)</option>
                                <option value="monthly" class="bg-surface py-1 text-on-surface">📆 Bulanan (6 Bulan Terakhir)</option>
                                <option value="yearly" class="bg-surface py-1 text-on-surface">📊 Tahunan (3 Tahun Terakhir)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant">
                                <ChevronDown class="w-3.5 h-3.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Line Chart Canvas -->
                    <div class="h-72 w-full pt-2">
                        <Line :data="lineChartData" :options="lineChartOptions" />
                    </div>
                </div>

                <!-- Popular Books (Right Column - 4 Cols) -->
                <div class="lg:col-span-4 bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/40 shadow-xs flex flex-col space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-extrabold text-on-surface tracking-tight">Buku Terpopuler</h3>
                        <Link href="/books" class="text-xs font-bold text-primary hover:underline">Lihat Semua</Link>
                    </div>
                    
                    <div v-if="popular_books && popular_books.length > 0" class="space-y-3 overflow-y-auto max-h-[290px] pr-1 flex-1">
                        <div v-for="book in popular_books" :key="book.id" class="flex items-center gap-3 p-2 rounded-xl hover:bg-surface-container-low transition-colors">
                            <div class="w-11 h-14 rounded-lg overflow-hidden shrink-0 shadow-xs border border-outline-variant/30 bg-surface-container flex items-center justify-center">
                                <img 
                                    v-if="book.cover && !failedCovers.has(book.id)" 
                                    :src="getAssetUrl(book.cover)" 
                                    :alt="book.title" 
                                    @error="failedCovers.add(book.id)"
                                    class="w-full h-full object-cover" 
                                />
                                <BookOpen v-else class="w-6 h-6 text-outline" />
                            </div>
                            <div class="flex-grow overflow-hidden">
                                <h4 class="font-bold text-xs text-on-surface truncate">{{ book.title }}</h4>
                                <p class="text-[11px] text-on-surface-variant truncate">{{ book.author }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <div class="h-1.5 flex-grow bg-surface-container-high rounded-full overflow-hidden">
                                        <div class="bg-primary h-full rounded-full" :style="{ width: Math.min(100, (book.borrowings_count || 1) * 10) + '%' }"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-primary shrink-0">{{ book.borrowings_count || 0 }} Peminjam</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex-1 flex items-center justify-center p-6 text-center text-on-surface-variant text-xs">
                        Belum ada data peminjaman buku terpopuler.
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/40 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-on-surface tracking-tight">Aktivitas Terkini</h3>
                        <p class="text-xs text-on-surface-variant">Transaksi peminjaman dan pengembalian buku terbaru</p>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            @click="activityFilter = 'Semua'"
                            :class="[
                                activityFilter === 'Semua' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-medium',
                                'px-3 py-1.5 rounded-xl text-xs transition-colors'
                            ]"
                        >
                            Semua
                        </button>
                        <button 
                            @click="activityFilter = 'Peminjaman'"
                            :class="[
                                activityFilter === 'Peminjaman' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-medium',
                                'px-3 py-1.5 rounded-xl text-xs transition-colors'
                            ]"
                        >
                            Peminjaman
                        </button>
                        <button 
                            @click="activityFilter = 'Pengembalian'"
                            :class="[
                                activityFilter === 'Pengembalian' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-medium',
                                'px-3 py-1.5 rounded-xl text-xs transition-colors'
                            ]"
                        >
                            Pengembalian
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table v-if="filteredActivities.length > 0" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-on-surface-variant border-b border-outline-variant/30 text-xs font-bold uppercase tracking-wider">
                                <th class="pb-3 px-3">Nama Anggota</th>
                                <th class="pb-3 px-3">Judul Buku</th>
                                <th class="pb-3 px-3">Aksi</th>
                                <th class="pb-3 px-3">Waktu</th>
                                <th class="pb-3 px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            <tr v-for="act in filteredActivities" :key="act.id" class="hover:bg-surface-container-low transition-colors">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ getInitials(act.member?.name) }}
                                        </div>
                                        <span class="font-bold text-xs text-on-surface">{{ act.member?.name || 'Siswa' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-xs font-medium text-on-surface">{{ act.book?.title || 'Judul Buku' }}</td>
                                <td class="py-3 px-3">
                                    <span :class="[
                                        act.status === 'Dipinjam' ? 'bg-secondary-container/50 text-on-secondary-container' : 'bg-primary-container/30 text-primary',
                                        'text-[11px] px-2.5 py-1 rounded-full font-bold inline-block'
                                    ]">
                                        {{ act.status === 'Dipinjam' ? 'Pinjam' : 'Kembali' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-xs font-medium text-on-surface-variant">{{ formatDate(act.borrow_date || act.return_date) }}</td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1 text-secondary">
                                        <CheckCircle class="w-3.5 h-3.5" />
                                        <span class="text-xs font-bold">Sukses</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="text-center py-8 text-on-surface-variant text-xs">
                        Belum ada aktivitas transaksi peminjaman atau pengembalian.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
