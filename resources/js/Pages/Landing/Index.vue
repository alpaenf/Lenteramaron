<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { 
    Search, BookOpen, PenTool, BookMarked, Users, ArrowRightLeft, 
    RotateCcw, ClipboardList, BarChart3, ArrowRight, Sun, Sparkles, 
    MessageSquare, School, Landmark, Star, Lightbulb, Flag, MapPin, 
    Phone, Mail, FileText, Send, CheckCircle, ChevronDown, Filter, ExternalLink, Compass
} from 'lucide-vue-next';

const props = defineProps({
    books: Object,
    stats: Object,
    categories: Array,
    galleries: Array,
    settings: Object,
    filters: Object,
});

const animatedStats = ref({
    books: 0,
    members: 0,
    visitors: 0,
});

let hasAnimatedStats = false;

const startCountUpAnimation = () => {
    if (hasAnimatedStats) return;
    hasAnimatedStats = true;

    const targetBooks = Number(props.stats?.total_books || 0);
    const targetMembers = Number(props.stats?.total_members || 0);
    const targetVisitors = Number(props.stats?.today_visitors ?? props.stats?.total_visitors ?? 0);

    const duration = 1800; // 1.8 seconds
    const startTime = performance.now();

    const updateCount = (currentTime) => {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 3);

        animatedStats.value.books = Math.floor(easeOut * targetBooks);
        animatedStats.value.members = Math.floor(easeOut * targetMembers);
        animatedStats.value.visitors = Math.floor(easeOut * targetVisitors);

        if (progress < 1) {
            requestAnimationFrame(updateCount);
        } else {
            animatedStats.value.books = targetBooks;
            animatedStats.value.members = targetMembers;
            animatedStats.value.visitors = targetVisitors;
        }
    };

    requestAnimationFrame(updateCount);
};

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-reveal-active');
                if (entry.target.id === 'stats-section') {
                    startCountUpAnimation();
                }
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
        observer.observe(el);
    });

    const statsEl = document.getElementById('stats-section');
    if (statsEl) {
        const rect = statsEl.getBoundingClientRect();
        if (rect.top < window.innerHeight) {
            startCountUpAnimation();
        }
    }
});

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const heroQuery = ref('');

const submitHeroSearch = () => {
    if (!heroQuery.value || heroQuery.value.trim().length < 2) return;
    router.get('/litera/search', { q: heroQuery.value.trim() });
};

const filterBooks = () => {
    const query = {};
    if (search.value && search.value.trim() !== '') {
        query.search = search.value.trim();
    }
    if (categoryId.value && categoryId.value !== '') {
        query.category_id = categoryId.value;
    }

    router.get('/', query, { preserveState: true, preserveScroll: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    categoryId.value = '';
    filterBooks();
};

const guestForm = useForm({
    name: '',
    institution: '',
    purpose: 'Pertanyaan Umum',
    feedback: '',
    note: '',
});

const submitGuestForm = () => {
    guestForm.post('/buku-tamu/simpan', {
        onSuccess: () => {
            guestForm.reset();
        },
    });
};

// Universal helper: resolves the correct public URL for any uploaded asset
const getAssetUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:')) return path;
    const clean = path.replace(/^\/+/, '');
    if (clean.startsWith('uploads/')) return '/' + clean;
    if (clean.startsWith('settings/') || clean.startsWith('books/') || clean.startsWith('galleries/')) {
        return '/uploads/' + clean;
    }
    return '/uploads/settings/' + clean;
};

const seederFallbackImages = {
    'galleries/literasi.jpg': 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80',
    'galleries/perpustakaan.jpg': 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
    'galleries/outing.jpg': 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
    'galleries/lomba.jpg': 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=800&q=80',
};

const getGalleryImageUrl = (galleryItem, defaultCategory = 'Literasi') => {
    if (!galleryItem || !galleryItem.image_path) {
        return getFallbackUrl(defaultCategory);
    }
    const path = galleryItem.image_path;
    if (seederFallbackImages[path]) {
        return seederFallbackImages[path];
    }
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }
    if (path.startsWith('uploads/')) return `/${path}`;
    return `/files-media/${path}`;
};

const getFallbackUrl = (category) => {
    switch (category) {
        case 'Literasi':
            return 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80';
        case 'Perpustakaan':
            return 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80';
        case 'Outing Class':
            return 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80';
        case 'Lomba':
            return 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=800&q=80';
        default:
            return 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80';
    }
};
</script>

<template>
    <GuestLayout>
        <!-- Hero Section -->
        <section class="relative min-h-[85vh] flex items-center overflow-hidden px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-blue-50/60 via-slate-50 to-white pt-12 pb-16 lg:pt-14 lg:pb-20">
            <!-- Background Decorative Soft Glowing Orbs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 right-10 w-80 h-80 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative z-10">
                <!-- Hero Left Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left relative reveal-on-scroll">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-100/80 text-blue-800 text-xs font-extrabold uppercase tracking-wider shadow-xs border border-blue-200/80">
                            LITERA — AI RESEARCH &amp; KNOWLEDGE NAVIGATOR
                            <Sparkles class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                        </span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.15] tracking-tight">
                        From Library Knowledge <br/>
                        <span class="text-blue-600 relative inline-block">
                            to Research Discovery
                            <svg class="absolute -bottom-3 left-0 w-full" viewBox="0 0 240 12" fill="none">
                                <path d="M3 9 C 70 2, 170 2, 237 9" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <Sparkles class="w-7 h-7 text-amber-400 inline-block ml-2 align-middle" />
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        Platform navigasi cerdas yang menghubungkan koleksi referensi perpustakaan lokal dengan sumber jurnal ilmiah internasional. Dipandu AI untuk menemukan, memahami, dan menavigasi alur membaca secara terstruktur.
                    </p>

                    <!-- Hero AI Search Input Box -->
                    <form @submit.prevent="submitHeroSearch" class="mt-4 flex flex-col sm:flex-row items-center gap-2 bg-white p-2 rounded-2xl shadow-xl border border-slate-200/80">
                        <div class="relative w-full">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <Search class="w-5 h-5" />
                            </span>
                            <input
                                v-model="heroQuery"
                                type="text"
                                placeholder="Ketik topik penelitian (misal: Generative AI, Literasi Digital)..."
                                class="w-full pl-11 pr-4 py-3 bg-transparent text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-0 border-0 font-medium"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="!heroQuery.trim()"
                            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                        >
                            <Sparkles class="w-4 h-4 text-amber-300" />
                            <span>Cari &amp; Navigasi Riset</span>
                        </button>
                    </form>

                    <!-- Quick Sample Tags -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1 text-xs text-slate-500 font-medium">
                        <span class="text-slate-400 font-semibold">Contoh Topik:</span>
                        <button @click="heroQuery = 'Kecerdasan Buatan dalam Pendidikan'; submitHeroSearch()" class="hover:text-blue-600 underline hover:no-underline">AI Pendidikan</button>
                        <span>•</span>
                        <button @click="heroQuery = 'Metodologi Penelitian'; submitHeroSearch()" class="hover:text-blue-600 underline hover:no-underline">Metodologi Penelitian</button>
                        <span>•</span>
                        <button @click="heroQuery = 'Data Science & Machine Learning'; submitHeroSearch()" class="hover:text-blue-600 underline hover:no-underline">Data Science</button>
                    </div>

                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start pt-3">
                        <Link href="/litera/workspace" class="border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 px-6 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all shadow-xs">
                            <BookOpen class="w-4 h-4 text-blue-600" />
                            <span>Buka Research Workspace</span>
                        </Link>
                    </div>
                </div>

                <!-- Hero Right Illustration -->
                <div class="lg:col-span-5 relative reveal-on-scroll reveal-delay-2 flex justify-center items-center">
                    <div class="relative w-full max-w-lg mx-auto">
                        <img class="w-full h-auto object-contain drop-shadow-2xl transform hover:scale-[1.03] transition-transform duration-500" 
                             alt="LITERA AI Research Navigator Platform" 
                             :src="settings.hero_banner_path ? '/files-media/' + settings.hero_banner_path : '/images/hero1.png'"/>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profil Perpustakaan -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white relative overflow-hidden" id="profil">
            <div class="max-w-7xl mx-auto relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="w-full lg:w-1/2 reveal-on-scroll">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <div class="h-52 rounded-3xl bg-cover bg-center shadow-lg border-2 border-white transform hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${getAssetUrl(settings.profile_photo_1) || 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                                <div class="h-64 rounded-3xl bg-cover bg-center shadow-lg border-2 border-white transform hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${getAssetUrl(settings.profile_photo_2) || 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            </div>
                            <div class="pt-10 space-y-4">
                                <div class="h-64 rounded-3xl bg-cover bg-center shadow-lg border-2 border-white transform hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${getAssetUrl(settings.profile_photo_3) || 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                                <div class="h-52 rounded-3xl bg-cover bg-center shadow-lg border-2 border-white transform hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${getAssetUrl(settings.profile_photo_4) || 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full lg:w-1/2 space-y-6 reveal-on-scroll reveal-delay-2">
                        <div>
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-extrabold uppercase tracking-wider mb-2">
                                <School class="w-4 h-4 text-blue-600" />
                                <span>PROFIL PLATFORM</span>
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight inline-block relative">
                            Platform Navigator <br/>
                            <span class="text-blue-600 relative inline-block">
                                Pengetahuan &amp; Riset
                                <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 240 10" fill="none">
                                    <path d="M2 7 C 70 2, 170 2, 238 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </h2>
                        <p class="text-slate-600 leading-relaxed text-base font-medium">
                            LITERA dikembangkan untuk menjembatani jurang antara pengetahuan dasar yang ada pada koleksi lokal dengan perkembangan riset &amp; sains terbaru di seluruh dunia.
                        </p>
                        <p class="text-slate-600 leading-relaxed text-base font-medium">
                            Dengan mengintegrasikan kecerdasan AI berstandar ilmiah dan indeks literatur global, LITERA membantu pengguna berpindah dari pemahaman konsep awal menuju eksplorasi *research gap* terkini.
                        </p>
                        <div class="grid grid-cols-2 gap-5 pt-2">
                            <div class="p-6 bg-[#f4f8fc] rounded-3xl border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-2xl mb-3">
                                    <Landmark class="w-6 h-6 text-blue-600" />
                                </div>
                                <div class="font-extrabold text-base text-slate-900">100% Grounded AI</div>
                                <div class="text-xs text-slate-500 font-medium mt-1">Penjelasan berbasis data empiris tanpa manipulasi LLM.</div>
                            </div>
                            <div class="p-6 bg-[#f4f8fc] rounded-3xl border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-2xl mb-3">
                                    <Star class="w-6 h-6 text-amber-500 fill-amber-500" />
                                </div>
                                <div class="font-extrabold text-base text-slate-900">Open Access</div>
                                <div class="text-xs text-slate-500 font-medium mt-1">Akses mudah ke jutaan paper jurnal internasional.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Visi & Misi -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#f4f8fc]">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-2xl mx-auto mb-14 space-y-2 reveal-on-scroll">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight inline-block relative">
                        Visi &amp; Misi LITERA
                        <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 10" fill="none">
                            <path d="M2 7 C 60 2, 140 2, 198 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </h2>
                    <p class="text-slate-600 text-base font-medium">Mendorong keberlanjutan eksplorasi sains dan literasi ilmiah.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-8 lg:p-10 bg-gradient-to-br from-blue-700 to-slate-900 rounded-3xl text-white shadow-xl relative overflow-hidden group reveal-on-scroll hover:-translate-y-1 transition-transform duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-4 backdrop-blur-md">
                            <Lightbulb class="w-7 h-7 text-amber-300" />
                        </div>
                        <h3 class="text-2xl font-black mb-4 flex items-center gap-3">
                            <span>Visi LITERA</span>
                        </h3>
                        <p class="text-lg leading-relaxed font-medium text-blue-50">
                            Menjadi navigator pengetahuan cerdas berstandar ilmiah yang membantu setiap peneliti, guru, dan pelajar berpindah dari fondasi teori awal menuju inovasi sains mutakhir.
                        </p>
                    </div>
                    <div class="p-8 lg:p-10 bg-white rounded-3xl border-2 border-blue-100 shadow-xl relative overflow-hidden group reveal-on-scroll reveal-delay-2 hover:-translate-y-1 transition-transform duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                            <Flag class="w-7 h-7 text-blue-600" />
                        </div>
                        <h3 class="text-2xl font-black text-blue-600 mb-4 flex items-center gap-3">
                            <span>Misi Utama</span>
                        </h3>
                        <ul class="space-y-3 text-slate-600 text-base font-medium relative z-10">
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Menyediakan pencarian literatur semantik tanpa ketergantungan pada exact keyword.</span>
                            </li>
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Memberikan sintesis penjelasan relevansi AI yang jujur tanpa manipulasi data.</span>
                            </li>
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Menyusun 5 tahap alur eksplorasi membaca (Research Path) secara otomatis.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Layanan Kami Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#f4f8fc]/60 relative overflow-hidden" id="layanan">
            <div class="max-w-7xl mx-auto relative z-10">
                <div class="text-center max-w-xl mx-auto mb-12 space-y-2 reveal-on-scroll">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight inline-block relative">
                        Layanan Kami
                        <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 160 10" fill="none">
                            <path d="M2 7 C 40 2, 120 2, 158 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </h2>
                </div>

                <!-- 5 Service Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    <!-- Card 1: Katalog Buku -->
                    <a href="#katalog" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-1">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#0066cc] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <BookMarked class="w-7 h-7" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Katalog Buku</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Temukan buku favoritmu dengan mudah.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-blue-50 group-hover:bg-[#0066cc] text-[#0066cc] group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </a>

                    <!-- Card 2: AI Research Search -->
                    <Link :href="route('litera.search')" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-2">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <Sparkles class="w-7 h-7 text-blue-600" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">AI Research Search</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Cari literatur ilmiah &amp; koleksi referensi berbasis bahasa alami.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 3: Research Path Navigation -->
                    <Link :href="route('litera.search')" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-3">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <Compass class="w-7 h-7 text-emerald-600" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Research Path</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Alur eksplorasi urutan membaca dari dasar hingga studi terbaru.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 group-hover:bg-emerald-600 text-emerald-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 4: Research Workspace -->
                    <Link :href="route('litera.workspace')" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-4">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <BookOpen class="w-7 h-7 text-purple-600" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Research Workspace</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Simpan sumber riset, kelola topik, dan buat catatan pribadi.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-purple-50 group-hover:bg-purple-600 text-purple-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </Link>

                    <!-- Card 5: Laporan & Analytics -->
                    <a href="#katalog" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-5">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <BarChart3 class="w-7 h-7 text-amber-600" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Katalog Referensi</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Koleksi referensi buku acuan berlisensi.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-amber-50 group-hover:bg-amber-600 text-amber-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Statistik -->
        <section id="stats-section" class="py-16 bg-[#0066cc] text-white relative overflow-hidden reveal-on-scroll">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ animatedStats.books }}+</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Koleksi Buku</div>
                    </div>
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ animatedStats.members }}+</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Anggota Aktif</div>
                    </div>
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ animatedStats.visitors }}+</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Total Pengunjung</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Katalog Buku Section (Interactive Catalog) -->
        <section id="katalog" class="py-20 px-4 sm:px-6 lg:px-8 bg-white border-y border-slate-100">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 reveal-on-scroll">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-[#0066cc] text-xs font-extrabold uppercase tracking-wider mb-2">
                            <BookMarked class="w-3.5 h-3.5 text-[#0066cc]" />
                            <span>KATALOG PUBLIK</span>
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight inline-block relative">
                            Cari &amp; Telusuri Buku
                            <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 220 10" fill="none">
                                <path d="M2 7 C 70 2, 150 2, 218 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </h2>
                        <p class="text-slate-500 text-base mt-2 font-medium">Cari judul buku favoritmu atau filter berdasarkan kategori DDC.</p>
                    </div>

                    <!-- Search Filter Form -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-72">
                            <input 
                                v-model="search" 
                                @keyup.enter="filterBooks"
                                type="text" 
                                placeholder="Cari Judul / Pengarang / ISBN..." 
                                class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200 bg-[#f4f8fc] text-xs font-medium focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                            />
                            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
                        </div>
                        <div class="relative inline-flex items-center w-full sm:w-auto">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <Filter class="w-4 h-4" />
                            </div>
                            <select 
                                v-model="categoryId" 
                                @change="filterBooks"
                                class="w-full appearance-none py-3 pl-10 pr-9 rounded-2xl border border-slate-200 bg-[#f4f8fc] text-xs font-bold text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none cursor-pointer transition-all shadow-2xs"
                            >
                                <option value="" class="bg-white py-1">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id" class="bg-white py-1">{{ cat.code }} - {{ cat.name }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                <ChevronDown class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book Cards Grid -->
                <div v-if="books.data && books.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6 reveal-on-scroll reveal-delay-2">
                    <div v-for="book in books.data" :key="book.id" class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col group">
                        <div class="aspect-[3/4] bg-slate-100 relative overflow-hidden flex items-center justify-center text-slate-400">
                            <img v-if="book.cover" :src="getAssetUrl(book.cover)" :alt="book.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <BookOpen v-else class="w-12 h-12 text-slate-300" />
                            <span :class="[
                                book.stock > 0 ? 'bg-emerald-600' : 'bg-amber-600',
                                'absolute top-3 right-3 text-[10px] font-extrabold text-white px-2.5 py-1 rounded-full shadow-md'
                            ]">
                                {{ book.stock > 0 ? 'Tersedia' : 'Sedang Dipinjam' }}
                            </span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <span class="text-[10px] font-bold text-[#0066cc] bg-blue-50 px-2 py-0.5 rounded-md inline-block mb-1">
                                    {{ book.category?.name || 'Umum' }}
                                </span>
                                <h4 class="font-extrabold text-slate-900 text-xs line-clamp-2 leading-snug group-hover:text-[#0066cc] transition-colors">
                                    {{ book.title }}
                                </h4>
                            </div>
                            <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                                <p class="truncate flex items-center gap-1 font-medium"><PenTool class="w-3 h-3 text-slate-400 shrink-0" /> {{ book.author }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5 font-medium">Koleksi Terverifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-[#f4f8fc] rounded-3xl p-12 text-center text-slate-500 border border-slate-100 my-4">
                    <BookOpen class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="font-bold text-base text-slate-700">Tidak ada buku yang sesuai dengan pencarian / kategori Anda.</p>
                    <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau tampilkan kembali semua buku.</p>
                    <button 
                        @click="resetFilter" 
                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer"
                    >
                        <RotateCcw class="w-3.5 h-3.5 text-white" />
                        Tampilkan Semua Buku
                    </button>
                </div>
            </div>
        </section>

        <!-- Galeri (Bento Grid) -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#f4f8fc]" id="galeri">
            <div class="max-w-7xl mx-auto">
                <div class="text-center max-w-2xl mx-auto mb-14 space-y-2 reveal-on-scroll">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight inline-block relative">
                        Galeri Kegiatan
                        <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 10" fill="none">
                            <path d="M2 7 C 60 2, 140 2, 198 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </h2>
                    <p class="text-slate-600 text-base font-medium">Momen seru saat belajar dan bermain di perpustakaan.</p>
                </div>
                <div class="bento-grid">
                    <!-- Item 1 (2x2 span) -->
                    <div class="md:col-span-2 md:row-span-2 rounded-3xl relative group overflow-hidden min-h-[280px] shadow-xl border-2 border-white bg-slate-200 reveal-on-scroll reveal-delay-1">
                        <img 
                            :src="getGalleryImageUrl(galleries && galleries[0], 'Literasi')" 
                            :alt="galleries && galleries[0] ? galleries[0].title : 'Literasi Pagi Bersama'" 
                            @error="(e) => { if (!e.target.src.includes('unsplash.com')) e.target.src = getFallbackUrl('Literasi') }"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-6">
                            <div>
                                <span class="text-[10px] font-bold text-white bg-[#0066cc] px-3 py-1 rounded-full inline-block mb-2 shadow-sm">
                                    {{ galleries && galleries[0] ? galleries[0].category : 'Literasi' }}
                                </span>
                                <h3 class="text-white font-black text-lg sm:text-xl drop-shadow-md">
                                    {{ galleries && galleries[0] ? galleries[0].title : 'Literasi Pagi Bersama' }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 (1x1 span) -->
                    <div class="md:col-span-1 md:row-span-1 rounded-3xl relative group overflow-hidden min-h-[180px] shadow-lg border-2 border-white bg-slate-200 reveal-on-scroll reveal-delay-2">
                        <img 
                            :src="getGalleryImageUrl(galleries && galleries[1], 'Perpustakaan')" 
                            :alt="galleries && galleries[1] ? galleries[1].title : 'Sudut Baca'" 
                            @error="(e) => { if (!e.target.src.includes('unsplash.com')) e.target.src = getFallbackUrl('Perpustakaan') }"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-5">
                            <div>
                                <span class="text-[10px] font-bold text-white bg-emerald-600 px-2.5 py-0.5 rounded-full inline-block mb-1 shadow-sm">
                                    {{ galleries && galleries[1] ? galleries[1].category : 'Perpustakaan' }}
                                </span>
                                <h3 class="text-white font-extrabold text-sm drop-shadow-md line-clamp-2">
                                    {{ galleries && galleries[1] ? galleries[1].title : 'Sudut Baca Ramah Anak' }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 (1x2 span) -->
                    <div class="md:col-span-1 md:row-span-2 rounded-3xl relative group overflow-hidden min-h-[280px] shadow-xl border-2 border-white bg-slate-200 reveal-on-scroll reveal-delay-3">
                        <img 
                            :src="getGalleryImageUrl(galleries && galleries[2], 'Outing Class')" 
                            :alt="galleries && galleries[2] ? galleries[2].title : 'Outing Class'" 
                            @error="(e) => { if (!e.target.src.includes('unsplash.com')) e.target.src = getFallbackUrl('Outing Class') }"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-6">
                            <div>
                                <span class="text-[10px] font-bold text-white bg-purple-600 px-3 py-1 rounded-full inline-block mb-2 shadow-sm">
                                    {{ galleries && galleries[2] ? galleries[2].category : 'Outing Class' }}
                                </span>
                                <h3 class="text-white font-extrabold text-base drop-shadow-md line-clamp-3">
                                    {{ galleries && galleries[2] ? galleries[2].title : 'Outing Class Wisata Literasi' }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4 (1x1 span) -->
                    <div class="md:col-span-1 md:row-span-1 rounded-3xl relative group overflow-hidden min-h-[180px] shadow-lg border-2 border-white bg-slate-200 reveal-on-scroll reveal-delay-4">
                        <img 
                            :src="getGalleryImageUrl(galleries && galleries[3], 'Lomba')" 
                            :alt="galleries && galleries[3] ? galleries[3].title : 'Lomba'" 
                            @error="(e) => { if (!e.target.src.includes('unsplash.com')) e.target.src = getFallbackUrl('Lomba') }"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-5">
                            <div>
                                <span class="text-[10px] font-bold text-white bg-amber-600 px-2.5 py-0.5 rounded-full inline-block mb-1 shadow-sm">
                                    {{ galleries && galleries[3] ? galleries[3].category : 'Lomba' }}
                                </span>
                                <h3 class="text-white font-extrabold text-sm drop-shadow-md line-clamp-2">
                                    {{ galleries && galleries[3] ? galleries[3].title : 'Juara 1 Lomba Bercerita' }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kontak & Map & Buku Tamu -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white" id="kontak">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="space-y-8 reveal-on-scroll">
                        <div class="space-y-2">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-[#0066cc] text-xs font-extrabold uppercase tracking-wider">
                                <MapPin class="w-3.5 h-3.5 text-[#0066cc]" />
                                <span>LOKASI &amp; KONTAK</span>
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight block relative">
                                Hubungi Kami
                                <svg class="w-48 h-3 -mt-1" viewBox="0 0 200 10" fill="none">
                                    <path d="M2 7 C 60 2, 140 2, 198 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                            </h2>
                            <p class="text-slate-600 text-base font-medium">Kami siap melayani kebutuhan informasi dan literasi Anda. Silakan hubungi kami atau kunjungi langsung perpustakaan kami.</p>
                        </div>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-[#f4f8fc] border border-slate-100 shadow-xs hover:shadow-md transition-all duration-300">
                                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-[#0066cc] flex items-center justify-center shrink-0 font-bold">
                                    <MapPin class="w-5 h-5 text-[#0066cc]" />
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm text-slate-900">Alamat Lengkap</div>
                                    <div class="text-slate-600 text-xs mt-0.5 font-medium">{{ settings.school_address || 'Desa Maron, Kec. Garung, Kab. Wonosobo, Jawa Tengah' }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-[#f4f8fc] border border-slate-100 shadow-xs hover:shadow-md transition-all duration-300">
                                <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
                                    <Phone class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm text-slate-900">Telepon / WhatsApp</div>
                                    <div class="text-slate-600 text-xs mt-0.5 font-medium">{{ settings.school_phone || '(0335) 1234567' }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-[#f4f8fc] border border-slate-100 shadow-xs hover:shadow-md transition-all duration-300">
                                <div class="w-11 h-11 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 font-bold">
                                    <Mail class="w-5 h-5 text-purple-600" />
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm text-slate-900">Email Resmi</div>
                                    <div class="text-slate-600 text-xs mt-0.5 font-medium">{{ settings.school_email || 'sdn02maron@gmail.com' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2">
                            <div class="w-full h-64 rounded-3xl border-4 border-white shadow-xl overflow-hidden relative group">
                                <iframe 
                                    :src="settings.gmaps_embed_url || 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6751394476323!2d109.92206757357137!3d-7.277756071516778!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e700b18b719df35%3A0x753393990615190a!2sSD%20N%202%20MARON!5e0!3m2!1sid!2sid!4v1785695520988!5m2!1sid!2sid'"
                                    class="w-full h-full border-0"
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                                <a 
                                    :href="settings.gmaps_url || 'https://maps.app.goo.gl/AvLEeA6feNjgzVZVA'" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/80 text-slate-800 text-xs font-extrabold shadow-lg hover:bg-white hover:text-blue-600 transition-all duration-200">
                                    <MapPin class="w-3.5 h-3.5 text-red-500 shrink-0" />
                                    <span>Buka di Google Maps</span>
                                    <ExternalLink class="w-3 h-3 text-slate-400 shrink-0 ml-0.5" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Digital Guest Form -->
                    <!-- LITERA Research Discovery Feature Card -->
                    <div class="bg-gradient-to-br from-blue-900 via-slate-900 to-blue-950 p-8 lg:p-10 rounded-3xl shadow-xl text-white space-y-6 reveal-on-scroll reveal-delay-2 relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="space-y-2 relative z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-blue-200 border border-white/10">
                                <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                                <span>AI RESEARCH NAVIGATOR</span>
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                From Library Knowledge to Research Discovery
                            </h3>
                            <p class="text-xs text-slate-300 font-medium leading-relaxed">
                                LITERA menghubungkan referensi perpustakaan lokal dengan sumber jurnal ilmiah internasional untuk membantu Anda menavigasi pengetahuan secara terstruktur.
                            </p>
                        </div>

                        <div class="space-y-3 relative z-10 text-xs">
                            <div class="flex items-start gap-3 bg-white/5 p-3 rounded-2xl border border-white/10">
                                <div class="w-7 h-7 rounded-lg bg-blue-600/30 text-blue-300 flex items-center justify-center shrink-0 font-bold">1</div>
                                <div>
                                    <h4 class="font-bold text-white">Find (Temukan)</h4>
                                    <p class="text-slate-300 text-[11px]">Cari literatur berbasis makna dan sinonim akademik tanpa tergantung exact keyword.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 bg-white/5 p-3 rounded-2xl border border-white/10">
                                <div class="w-7 h-7 rounded-lg bg-blue-600/30 text-blue-300 flex items-center justify-center shrink-0 font-bold">2</div>
                                <div>
                                    <h4 class="font-bold text-white">Understand (Pahami)</h4>
                                    <p class="text-slate-300 text-[11px]">Penjelasan relevansi AI ("Mengapa Relevan?") secara instan dan jujur tanpa fakta buatan.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 bg-white/5 p-3 rounded-2xl border border-white/10">
                                <div class="w-7 h-7 rounded-lg bg-blue-600/30 text-blue-300 flex items-center justify-center shrink-0 font-bold">3</div>
                                <div>
                                    <h4 class="font-bold text-white">Navigate (Navigasi)</h4>
                                    <p class="text-slate-300 text-[11px]">Rekomendasi 5 tahap alur eksplorasi urutan membaca dari dasar hingga riset terbaru.</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 relative z-10">
                            <Link :href="route('litera.search')" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3.5 rounded-2xl font-extrabold text-xs transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                                <Compass class="w-4 h-4 text-blue-200" />
                                <span>Mulai Cari &amp; Navigasi Riset</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
/* Bento Grid Styling */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1.25rem;
}

@media (min-width: 768px) {
    .bento-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-template-rows: repeat(2, minmax(0, 1fr));
    }
}

/* Cute Scroll Reveal Animations */
.reveal-on-scroll {
    opacity: 0;
    transform: translateY(35px) scale(0.97);
    transition: opacity 0.75s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.75s cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: opacity, transform;
}

.reveal-on-scroll.animate-reveal-active {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Stagger Delays */
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }
.reveal-delay-5 { transition-delay: 0.5s; }

/* Cute Floating Animations */
@keyframes floatWobble {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(3deg); }
}

@keyframes pulseGlow {
    0%, 100% { transform: scale(1); opacity: 0.85; }
    50% { transform: scale(1.06); opacity: 1; }
}

.animate-float-wobble {
    animation: floatWobble 4s ease-in-out infinite;
}

.animate-pulse-glow {
    animation: pulseGlow 3s ease-in-out infinite;
}
</style>
