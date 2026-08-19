<script setup>
import { ref, computed } from 'vue';
import { X, ExternalLink, Download, Maximize2, Minimize2, BookOpen, FileText } from 'lucide-vue-next';

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

const embedUrl = computed(() => {
    if (!props.pdfUrl) return '';
    const url = props.pdfUrl.trim();
    
    // Local storage PDF or direct .pdf URL
    if (url.startsWith('/') || url.startsWith('blob:') || url.endsWith('.pdf')) {
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
    <div v-if="show" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 animate-in fade-in duration-200">
        <div 
            :class="[
                'bg-slate-900 text-white rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-800 transition-all duration-300',
                isFullscreen ? 'fixed inset-2 z-[110] rounded-2xl' : 'w-full max-w-5xl h-[88vh]'
            ]"
        >
            <!-- Header Bar -->
            <div class="px-6 py-4 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2.5 bg-blue-600/20 text-blue-400 rounded-xl border border-blue-500/20 shrink-0">
                        <BookOpen class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-sm sm:text-base text-slate-100 truncate leading-tight">
                            {{ title || 'Pembaca Dokumen LITERA' }}
                        </h3>
                        <p class="text-xs text-slate-400 truncate mt-0.5 font-medium">
                            {{ author ? 'Oleh: ' + author : 'Pembaca Digital Built-in' }}
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
                        class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-slate-700"
                    >
                        <ExternalLink class="w-4 h-4" />
                        <span class="hidden sm:inline">Tab Baru</span>
                    </a>

                    <!-- Download PDF -->
                    <a 
                        v-if="pdfUrl" 
                        :href="pdfUrl" 
                        download
                        target="_blank" 
                        title="Unduh Berkas PDF" 
                        class="p-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-500/20"
                    >
                        <Download class="w-4 h-4" />
                        <span class="hidden sm:inline">Unduh</span>
                    </a>

                    <!-- Fullscreen Toggle -->
                    <button 
                        @click="toggleFullscreen" 
                        title="Toggle Layar Penuh" 
                        class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl transition border border-slate-700"
                    >
                        <component :is="isFullscreen ? Minimize2 : Maximize2" class="w-4 h-4" />
                    </button>

                    <!-- Close Modal -->
                    <button 
                        @click="close" 
                        title="Tutup Pembaca" 
                        class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white rounded-xl transition border border-rose-500/20 ml-1"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- PDF Viewer Iframe Body -->
            <div class="relative flex-1 bg-slate-950 overflow-hidden flex items-center justify-center">
                <iframe 
                    v-if="pdfUrl"
                    :src="embedUrl" 
                    class="w-full h-full border-0 bg-slate-950" 
                    title="PDF Reader"
                    allowfullscreen
                ></iframe>

                <div v-else class="text-center p-8 text-slate-400 max-w-sm space-y-3">
                    <FileText class="w-12 h-12 mx-auto opacity-40 text-blue-400" />
                    <p class="font-bold text-sm text-slate-200">Berkas PDF Tidak Tersedia</p>
                    <p class="text-xs leading-relaxed">Pratinjau digital tidak tersedia secara langsung untuk sumber ini.</p>
                </div>
            </div>

            <!-- Footer Bar -->
            <div class="px-6 py-2.5 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>LITERA Built-in PDF Reader Engine</span>
                </div>
                <span>Tekan <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-700 rounded text-[10px] text-slate-300">ESC</kbd> atau tombol Tutup untuk kembali</span>
            </div>
        </div>
    </div>
</template>
