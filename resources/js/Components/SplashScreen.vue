<script setup>
import { ref, onMounted, watch } from 'vue';
import { Sparkles, Search, BookOpen, ChevronRight, ChevronLeft, X, ArrowRight, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const isVisible = ref(false);
const currentSlide = ref(0);

const slides = [
    {
        tag: 'LITERA NAVIGATOR • 1/3',
        title: 'Selamat Datang di LITERA',
        subtitle: 'Platform Navigasi Pengetahuan & Riset Ilmiah',
        description: 'LITERA menjembatani koleksi buku perpustakaan lokal dengan jutaan jurnal ilmiah internasional terkini.',
        image: '/images/splash1.png',
        badge: '100% Grounded AI',
    },
    {
        tag: 'AI DEEP ANALYSIS • 2/3',
        title: 'Penelusuran & Bedah Isi AI',
        subtitle: 'Indeks Literatur Global & Ringkasan Presisi',
        description: 'Cari artikel ilmiah dari OpenAlex & Semantic Scholar, lalu bedah fokus, metodologi, dan temuan kunci secara instan.',
        image: '/images/splash2.png',
        badge: 'OpenAlex & Scholar',
    },
    {
        tag: 'RESEARCH WORKSPACE • 3/3',
        title: 'Ruang Kerja Digital Anda',
        subtitle: 'Katalog Perpustakaan & Ruang Simpan Sitasi',
        description: 'Simpan referensi pilihan ke Research Workspace pribadi Anda dan nikmati pemutar pembaca digital built-in.',
        image: '/images/splash3.png',
        badge: 'Personal Workspace',
    },
];

const checkFirstVisit = () => {
    if (props.show) {
        isVisible.value = true;
        return;
    }
    const hasSeen = sessionStorage.getItem('litera_splash_seen');
    if (!hasSeen) {
        setTimeout(() => {
            isVisible.value = true;
        }, 500);
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        currentSlide.value = 0;
        isVisible.value = true;
    }
});

const nextSlide = () => {
    if (currentSlide.value < slides.length - 1) {
        currentSlide.value++;
    } else {
        closeSplash();
    }
};

const prevSlide = () => {
    if (currentSlide.value > 0) {
        currentSlide.value--;
    }
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

const closeSplash = () => {
    isVisible.value = false;
    sessionStorage.setItem('litera_splash_seen', 'true');
    emit('close');
};

onMounted(() => {
    checkFirstVisit();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-950/70 backdrop-blur-md font-sans selection:bg-blue-600 selection:text-white"
            >
                <!-- Splash Modal Container -->
                <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[92vh]">
                    <!-- Top Bar Header with Close/Skip -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/80 backdrop-blur-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                            <span class="text-[11px] font-extrabold tracking-wider text-blue-700 uppercase">
                                {{ slides[currentSlide].tag }}
                            </span>
                        </div>
                        <button
                            @click="closeSplash"
                            class="text-xs font-bold text-slate-400 hover:text-slate-700 px-3 py-1.5 rounded-xl hover:bg-slate-200/60 transition-colors flex items-center gap-1 cursor-pointer"
                        >
                            <span>Lewati</span>
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Slide Content Body -->
                    <div class="p-6 sm:p-8 flex-grow overflow-y-auto space-y-6 flex flex-col justify-between">
                        <!-- Image Container (Uncropped Full View) -->
                        <div class="relative w-full rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 p-2 flex items-center justify-center min-h-[220px] sm:min-h-[280px]">
                            <Transition
                                mode="out-in"
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 translate-x-4"
                                enter-to-class="opacity-100 translate-x-0"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 translate-x-0"
                                leave-to-class="opacity-0 -translate-x-4"
                            >
                                <img
                                    :key="currentSlide"
                                    :src="slides[currentSlide].image"
                                    :alt="slides[currentSlide].title"
                                    class="w-full h-56 sm:h-72 object-contain rounded-xl drop-shadow-md transform hover:scale-[1.02] transition-transform duration-500"
                                />
                            </Transition>

                            <!-- Floating Badge -->
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full border border-slate-200 shadow-sm text-[10px] font-extrabold text-blue-700 flex items-center gap-1.5">
                                <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                                <span>{{ slides[currentSlide].badge }}</span>
                            </div>
                        </div>

                        <!-- Text Info Block -->
                        <div class="space-y-2 text-center sm:text-left">
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                                {{ slides[currentSlide].title }}
                            </h3>
                            <p class="text-xs sm:text-sm font-bold text-blue-600">
                                {{ slides[currentSlide].subtitle }}
                            </p>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                                {{ slides[currentSlide].description }}
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar Navigation Controls -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <!-- Step Dots -->
                        <div class="flex items-center gap-2">
                            <button
                                v-for="(slide, idx) in slides"
                                :key="idx"
                                @click="goToSlide(idx)"
                                :class="[
                                    'h-2.5 rounded-full transition-all duration-300 cursor-pointer',
                                    currentSlide === idx ? 'w-8 bg-blue-600 shadow-sm shadow-blue-500/30' : 'w-2.5 bg-slate-300 hover:bg-slate-400'
                                ]"
                                :title="`Slide ${idx + 1}`"
                            ></button>
                        </div>

                        <!-- Next / Finish Buttons -->
                        <div class="flex items-center gap-3">
                            <button
                                v-if="currentSlide > 0"
                                @click="prevSlide"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-all flex items-center gap-1 cursor-pointer"
                            >
                                <ChevronLeft class="w-4 h-4" />
                                <span>Kembali</span>
                            </button>

                            <button
                                @click="nextSlide"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-500/20 transition-all flex items-center gap-2 cursor-pointer"
                            >
                                <span v-if="currentSlide === slides.length - 1">Mulai Eksplorasi LITERA</span>
                                <span v-else>Lanjut</span>
                                <CheckCircle2 v-if="currentSlide === slides.length - 1" class="w-4 h-4" />
                                <ChevronRight v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
