<script setup>
import { ref, computed } from 'vue';
import { X, ExternalLink, Download, Maximize2, Minimize2, BookOpen, FileText, Globe } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    title: String,
    author: String,
    pdfUrl: String,
    sourceType: {
        type: String,
        default: 'external'
    }
});

const emit = defineEmits(['close']);

const isFullscreen = ref(false);

const isGoogleSearchUrl = computed(() => {
    if (!props.pdfUrl) return false;
    return props.pdfUrl.includes('google.com/search');
});

const isDirectPdfUrl = computed(() => {
    if (!props.pdfUrl) return false;
    const url = props.pdfUrl.trim().toLowerCase();
    return url.startsWith('/') || url.startsWith('blob:') || url.endsWith('.pdf') || url.includes('.pdf?');
});

const embedUrl = computed(() => {
    if (!props.pdfUrl || isGoogleSearchUrl.value) return '';
    const url = props.pdfUrl.trim();
    
    // Direct PDF files (local uploads or direct .pdf links)
    if (isDirectPdfUrl.value) {
        return url;
    }
    
    // Google Docs Viewer fallback for external online PDFs
    return `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
});

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

const close = () => {
    isFullscreen.value = false;
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4 animate-in fade-in duration-200">
        <div 
            :class="[
                'bg-white text-slate-900 rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-200/80 transition-all duration-300',
                isFullscreen ? 'fixed inset-2 z-[110] rounded-2xl' : 'w-full max-w-5xl h-[88vh]'
            ]"
        >
            <!-- Header Bar -->
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl border border-blue-200/60 shrink-0">
                        <BookOpen class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 truncate leading-tight">
                            {{ title || 'Pembaca Dokumen LITERA' }}
                        </h3>
                        <p class="text-xs text-slate-500 truncate mt-0.5 font-medium">
                            {{ author ? 'Penulis: ' + author : 'Pembaca Digital Built-in' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Open in New Tab -->
                    <a 
                        v-if="pdfUrl" 
                        :href="pdfUrl" 
                        target="_blank" 
                        title="Buka di Tab Baru" 
                        class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-slate-200"
                    >
                        <ExternalLink class="w-4 h-4" />
                        <span class="hidden sm:inline">Buka Tab Baru</span>
                    </a>

                    <!-- Download PDF -->
                    <a 
                        v-if="pdfUrl && isDirectPdfUrl" 
                        :href="pdfUrl" 
                        download
                        target="_blank" 
                        title="Unduh Berkas PDF" 
                        class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-500/20"
                    >
                        <Download class="w-4 h-4" />
                        <span class="hidden sm:inline">Unduh PDF</span>
                    </a>

                    <!-- Fullscreen Toggle -->
                    <button 
                        @click="toggleFullscreen" 
                        title="Toggle Layar Penuh" 
                        class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition border border-slate-200"
                    >
                        <component :is="isFullscreen ? Minimize2 : Maximize2" class="w-4 h-4" />
                    </button>

                    <!-- Close Modal -->
                    <button 
                        @click="close" 
                        title="Tutup Pembaca" 
                        class="p-2 bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white rounded-xl transition border border-rose-200 ml-1"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Main Reader Content Area -->
            <div class="relative flex-1 bg-slate-100/70 overflow-hidden flex items-center justify-center p-4">
                <!-- Case A: Valid PDF Embed -->
                <iframe 
                    v-if="pdfUrl && !isGoogleSearchUrl"
                    :src="embedUrl" 
                    class="w-full h-full border-0 bg-white rounded-xl shadow-xs" 
                    title="PDF Reader"
                    allowfullscreen
                ></iframe>

                <!-- Case B: Non-PDF or External Web Search Link Fallback Card -->
                <div v-else class="text-center p-8 max-w-md w-full bg-white rounded-3xl border border-slate-200/80 shadow-xl space-y-5 animate-in fade-in duration-300">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto border border-blue-200/60">
                        <BookOpen class="w-8 h-8" />
                    </div>

                    <div class="space-y-2">
                        <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                            Koleksi Referensi Buku Fisik
                        </span>
                        <h4 class="font-extrabold text-base text-slate-900 leading-snug">{{ title }}</h4>
                        <p class="text-xs text-slate-500 font-medium">Oleh: {{ author || '-' }}</p>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-200/60 italic">
                        Buku ini belum memiliki berkas E-Book PDF digital terlampir. Anda dapat membaca fisik buku ini di rak perpustakaan atau menelusuri pustaka ilmiah eksternal di tab baru.
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a 
                            v-if="pdfUrl"
                            :href="pdfUrl" 
                            target="_blank" 
                            class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 transition"
                        >
                            <Globe class="w-4 h-4" />
                            <span>Telusuri Sumber Web di Tab Baru</span>
                        </a>
                        <button 
                            @click="close"
                            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Bar -->
            <div class="px-6 py-2.5 bg-white border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium">LITERA Built-in PDF Reader Engine</span>
                </div>
                <span>Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-600 font-mono">ESC</kbd> atau tombol Tutup untuk kembali</span>
            </div>
        </div>
    </div>
</template>
