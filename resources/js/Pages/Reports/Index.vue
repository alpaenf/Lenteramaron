<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BarChart3, FileText, Download, Calendar, Filter } from 'lucide-vue-next';

const props = defineProps({
    reportData: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const type = ref(props.filters.type);

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
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan & Pelaporan Rekapitulasi</h1>
                    <p class="text-xs text-slate-500 mt-1">Cetak dan unduh laporan resmi perpustakaan format PDF (Siap Cetak) dan Excel.</p>
                </div>
                <div>
                    <button @click="downloadPdf" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Download class="w-4 h-4" />
                        <span>Unduh Laporan PDF</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Laporan</label>
                        <select v-model="type" @change="filterReport" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs bg-white">
                            <option value="borrowings">Laporan Peminjaman Buku</option>
                            <option value="returns">Laporan Pengembalian Buku</option>
                            <option value="guest_books">Laporan Pengunjung / Buku Tamu</option>
                            <option value="popular_books">Laporan Buku Terpopuler</option>
                        </select>
                    </div>

                    <div v-if="type !== 'popular_books'">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                        <input v-model="startDate" @change="filterReport" type="date" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs" />
                    </div>

                    <div v-if="type !== 'popular_books'">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai</label>
                        <input v-model="endDate" @change="filterReport" type="date" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs" />
                    </div>
                </div>
            </div>

            <!-- Data Table Preview -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6 space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <FileText class="w-5 h-5 text-blue-600" />
                        <span>Pratinjau Data Laporan</span>
                    </h3>
                    <span class="text-xs font-semibold text-slate-500">Total: {{ reportData.length }} Data</span>
                </div>

                <div class="overflow-x-auto">
                    <!-- Borrowings Preview -->
                    <table v-if="type === 'borrowings'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">No Transaksi</th>
                                <th class="p-3">Nama Anggota</th>
                                <th class="p-3">Judul Buku</th>
                                <th class="p-3">Tgl Pinjam</th>
                                <th class="p-3">Jatuh Tempo</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in reportData" :key="item.id">
                                <td class="p-3 font-mono font-bold text-blue-900">{{ item.transaction_no }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ item.member?.name }} ({{ item.member?.class_name }})</td>
                                <td class="p-3 text-slate-800">{{ item.book?.title }}</td>
                                <td class="p-3 font-medium text-slate-700">{{ formatDate(item.borrow_date) }}</td>
                                <td class="p-3 text-amber-700 font-bold">{{ formatDate(item.due_date) }}</td>
                                <td class="p-3"><span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full font-bold text-[10px]">{{ item.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Returns Preview -->
                    <table v-else-if="type === 'returns'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">No Return</th>
                                <th class="p-3">Nama Anggota</th>
                                <th class="p-3">Judul Buku</th>
                                <th class="p-3">Tgl Kembali</th>
                                <th class="p-3">Kondisi</th>
                                <th class="p-3">Terlambat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in reportData" :key="item.id">
                                <td class="p-3 font-mono font-bold text-emerald-900">{{ item.return_no }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ item.member?.name }}</td>
                                <td class="p-3 text-slate-800">{{ item.book?.title }}</td>
                                <td class="p-3 font-medium text-slate-700">{{ formatDate(item.return_date) }}</td>
                                <td class="p-3 font-bold text-emerald-700">{{ item.condition }}</td>
                                <td class="p-3">{{ item.late_days }} hari</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Guest Books Preview -->
                    <table v-else-if="type === 'guest_books'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">No Pengunjung</th>
                                <th class="p-3">Nama</th>
                                <th class="p-3">Instansi / Kelas</th>
                                <th class="p-3">Keperluan</th>
                                <th class="p-3">Tanggal & Jam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in reportData" :key="item.id">
                                <td class="p-3 font-mono font-bold text-blue-900">{{ item.visitor_no }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ item.name }}</td>
                                <td class="p-3">{{ item.institution }}</td>
                                <td class="p-3">{{ item.purpose }}</td>
                                <td class="p-3 font-medium text-slate-700">{{ formatDate(item.date) }} <span class="text-[10px] text-slate-400">Jam {{ item.time }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Popular Books Preview -->
                    <table v-else-if="type === 'popular_books'" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="p-3">Peringkat</th>
                                <th class="p-3">Kode Buku</th>
                                <th class="p-3">Judul Buku</th>
                                <th class="p-3">Pengarang</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Frekuensi Dipinjam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in reportData" :key="item.id">
                                <td class="p-3 font-bold text-slate-900">#{{ idx + 1 }}</td>
                                <td class="p-3 font-mono font-bold text-blue-900">{{ item.book_code }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ item.title }}</td>
                                <td class="p-3 text-slate-600">{{ item.author }}</td>
                                <td class="p-3">{{ item.category?.name || '-' }}</td>
                                <td class="p-3 font-bold text-amber-600">{{ item.borrowings_count }} kali</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
