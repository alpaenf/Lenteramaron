<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { 
    Search, BookOpen, PenTool, BookMarked, Users, ArrowRightLeft, 
    RotateCcw, ClipboardList, BarChart3, ArrowRight, Sun, Sparkles, 
    MessageSquare, School, Landmark, Star, Lightbulb, Flag, MapPin, 
    Phone, Mail, FileText, Send, CheckCircle, ChevronDown, Filter
} from 'lucide-vue-next';

const props = defineProps({
    books: Object,
    stats: Object,
    categories: Array,
    galleries: Array,
    settings: Object,
    filters: Object,
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-reveal-active');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
        observer.observe(el);
    });
});

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');

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
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('uploads/')) return `/${path}`;
    return `/files-media/${path}`;
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
        <section class="relative min-h-[82vh] flex items-center overflow-hidden px-4 sm:px-6 lg:px-8 bg-[#f4f8fc] pt-12 pb-16 lg:pt-14 lg:pb-20">
            <!-- Background Decorative Ornaments -->
            <div class="absolute top-10 left-10 text-amber-400 animate-float-wobble pointer-events-none">
                <svg class="w-16 h-16" viewBox="0 0 100 100" fill="none">
                    <circle cx="50" cy="50" r="24" fill="#FACC15"/>
                    <path d="M50 10 V 2, M50 90 V 98, M10 50 H 2, M90 50 H 98, M22 22 L 16 16, M78 78 L 84 84, M22 78 L 16 84, M78 22 L 84 16" stroke="#FACC15" stroke-width="6" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="absolute top-14 left-1/3 text-blue-300 pointer-events-none opacity-80 animate-float-wobble" style="animation-delay: 1.5s;">
                <svg class="w-10 h-10 -rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </div>

            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative z-10">
                <!-- Hero Left Content -->
                <div class="lg:col-span-6 space-y-6 text-center lg:text-left relative reveal-on-scroll">
                    <div>
                        <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#8df3a4] text-[#0d5926] text-xs font-bold uppercase tracking-wider shadow-xs">
                            SELAMAT DATANG DI SD NEGERI 02 MARON
                            <Sparkles class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                        </span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.15] tracking-tight">
                        Membaca Adalah <br/>
                        <span class="text-[#0054a6] relative inline-block">
                            Jendela Dunia
                            <svg class="absolute -bottom-3 left-0 w-full" viewBox="0 0 240 12" fill="none">
                                <path d="M3 9 C 70 2, 170 2, 237 9" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <Sparkles class="w-7 h-7 text-amber-400 inline-block ml-2 align-middle" />
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        Nyalakan cahaya ilmu melalui koleksi buku yang inspiratif dan ruang baca yang ceria. Perpustakaan ramah anak untuk generasi masa depan yang gemilang.
                    </p>

                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start pt-2">
                        <a href="#katalog" class="bg-[#0066cc] hover:bg-[#0052a3] text-white px-8 py-3.5 rounded-2xl font-bold text-sm shadow-xl shadow-blue-500/25 hover:scale-105 transition-all duration-200 flex items-center gap-2.5">
                            <BookOpen class="w-4 h-4" />
                            <span>Jelajahi Buku</span>
                        </a>
                        <a href="#profil" class="border-2 border-[#0066cc] text-[#0066cc] bg-white/80 hover:bg-blue-50/50 px-8 py-3.5 rounded-2xl font-bold text-sm flex items-center gap-2.5 transition-all duration-200 shadow-sm">
                            <Users class="w-4 h-4" />
                            <span>Tentang Kami</span>
                        </a>
                    </div>
                </div>

                <!-- Hero Right Illustration with Organic Blob Curve -->
                <div class="lg:col-span-6 relative reveal-on-scroll reveal-delay-2">
                    <div class="relative w-full max-w-xl mx-auto">
                        <!-- Curved Image Container -->
                        <div class="rounded-[40px] lg:rounded-[55px] overflow-hidden border-4 border-white shadow-2xl shadow-blue-900/10 bg-white aspect-[4/3]">
                            <img class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700" 
                                 alt="Perpustakaan Lentera Maron SD Negeri 02 Maron" 
                                 :src="settings.hero_banner_path ? '/files-media/' + settings.hero_banner_path : '/images/hero.png'"/>
                        </div>

                        <!-- Floating Chat Widget (Bottom Right) -->
                        <div class="absolute -bottom-6 -right-2 sm:right-4 bg-white/95 backdrop-blur-md p-4 rounded-3xl shadow-2xl border border-slate-100 flex items-center gap-3 z-20 max-w-xs animate-float-wobble">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 font-bold">
                                <BookOpen class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-xs font-extrabold text-slate-900 flex items-center gap-1">
                                    <span>Halo Sahabat Literasi!</span>
                                </p>
                                <p class="text-[10px] text-slate-500 font-medium">Butuh bantuan? Yuk, tanya pustakawan!</p>
                                <a href="#buku-tamu" class="mt-1.5 inline-block bg-[#0066cc] hover:bg-[#0052a3] text-white text-[10px] font-bold px-3.5 py-1.5 rounded-full shadow-md shadow-blue-500/20 transition-all">
                                    Chat Sekarang
                                </a>
                            </div>
                        </div>
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
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-[#0066cc] text-xs font-extrabold uppercase tracking-wider mb-2">
                                <School class="w-4 h-4 text-[#0066cc]" />
                                <span>PROFIL PERPUSTAKAAN</span>
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight inline-block relative">
                            Pusat Literasi <br/>
                            <span class="text-[#0054a6] relative inline-block">
                                SD Negeri 02 Maron
                                <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 240 10" fill="none">
                                    <path d="M2 7 C 70 2, 170 2, 238 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </h2>
                        <p class="text-slate-600 leading-relaxed text-base font-medium">
                            Lentera Maron didirikan sebagai jantung dari proses pembelajaran di SD Negeri 02 Maron. Sejak awal, visi kami adalah menciptakan ruang di mana setiap anak merasa bersemangat untuk menggali pengetahuan melalui literasi.
                        </p>
                        <p class="text-slate-600 leading-relaxed text-base font-medium">
                            Dengan ribuan koleksi buku pilihan, dari fiksi hingga referensi sains, kami berkomitmen untuk mendampingi siswa-siswi dalam perjalanan intelektual mereka. Perpustakaan kami bukan sekadar tempat menyimpan buku, melainkan ruang kreasi dan eksplorasi.
                        </p>
                        <div class="grid grid-cols-2 gap-5 pt-2">
                            <div class="p-6 bg-[#f4f8fc] rounded-3xl border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-[#0066cc] flex items-center justify-center font-bold text-2xl mb-3">
                                    <Landmark class="w-6 h-6 text-[#0066cc]" />
                                </div>
                                <div class="font-extrabold text-base text-slate-900">Didirikan 2010</div>
                                <div class="text-xs text-slate-500 font-medium mt-1">Dedikasi satu dekade lebih untuk literasi.</div>
                            </div>
                            <div class="p-6 bg-[#f4f8fc] rounded-3xl border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-2xl mb-3">
                                    <Star class="w-6 h-6 text-amber-500 fill-amber-500" />
                                </div>
                                <div class="font-extrabold text-base text-slate-900">Akreditasi A</div>
                                <div class="text-xs text-slate-500 font-medium mt-1">Standar pelayanan perpustakaan nasional.</div>
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
                        Visi &amp; Misi Kami
                        <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 10" fill="none">
                            <path d="M2 7 C 60 2, 140 2, 198 7" stroke="#facc15" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </h2>
                    <p class="text-slate-600 text-base font-medium">Membangun fondasi masa depan melalui budaya baca yang ceria.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="p-8 lg:p-10 bg-gradient-to-br from-[#0066cc] to-[#004080] rounded-3xl text-white shadow-xl relative overflow-hidden group reveal-on-scroll hover:-translate-y-1 transition-transform duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-4 backdrop-blur-md">
                            <Lightbulb class="w-7 h-7 text-amber-300" />
                        </div>
                        <h3 class="text-2xl font-black mb-4 flex items-center gap-3">
                            <span>Visi Perpustakaan</span>
                        </h3>
                        <p class="text-lg leading-relaxed font-medium text-blue-50">
                            {{ settings.vision || 'Menjadi pusat sumber belajar yang unggul dalam membentuk generasi pembelajar sepanjang hayat, cerdas, berkarakter, dan berwawasan luas melalui penguatan budaya literasi.' }}
                        </p>
                    </div>
                    <div class="p-8 lg:p-10 bg-white rounded-3xl border-2 border-blue-100 shadow-xl relative overflow-hidden group reveal-on-scroll reveal-delay-2 hover:-translate-y-1 transition-transform duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                            <Flag class="w-7 h-7 text-[#0066cc]" />
                        </div>
                        <h3 class="text-2xl font-black text-[#0066cc] mb-4 flex items-center gap-3">
                            <span>Misi Utama</span>
                        </h3>
                        <ul v-if="!settings.mission" class="space-y-3 text-slate-600 text-base font-medium relative z-10">
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Menyediakan koleksi pustaka yang relevan, inspiratif, dan mutakhir.</span>
                            </li>
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Memberikan pelayanan prima yang ramah anak dan edukatif.</span>
                            </li>
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Mengembangkan inovasi program literasi berbasis digital.</span>
                            </li>
                            <li class="flex gap-3 items-start">
                                <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Menyelenggarakan kegiatan kreatif yang menumbuhkan minat baca.</span>
                            </li>
                        </ul>
                        <div v-else class="text-slate-600 text-base relative z-10 whitespace-pre-line leading-relaxed font-medium">
                            {{ settings.mission }}
                        </div>
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

                    <!-- Card 2: Peminjaman -->
                    <a href="#katalog" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-2">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <ArrowRightLeft class="w-7 h-7" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Peminjaman</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Pinjam buku untuk menambah pengetahuanmu.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 group-hover:bg-emerald-600 text-emerald-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </a>

                    <!-- Card 3: Pengembalian -->
                    <a href="#profil" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-3">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <RotateCcw class="w-7 h-7" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Pengembalian</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Kembalikan buku tepat waktu agar tidak denda.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-purple-50 group-hover:bg-purple-600 text-purple-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </a>

                    <!-- Card 4: Buku Tamu -->
                    <a href="#buku-tamu" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-4">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <ClipboardList class="w-7 h-7" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Buku Tamu</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Tinggalkan pesan dan kesan untuk perpustakaan.</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <div class="w-8 h-8 rounded-full bg-rose-50 group-hover:bg-rose-500 text-rose-500 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <ArrowRight class="w-4 h-4" />
                            </div>
                        </div>
                    </a>

                    <!-- Card 5: Laporan & Statistik -->
                    <a href="#profil" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll reveal-delay-5">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                <BarChart3 class="w-7 h-7" />
                            </div>
                            <h3 class="font-extrabold text-base text-slate-900 mb-1.5">Laporan &amp; Statistik</h3>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Lihat laporan dan statistik perpustakaan.</p>
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
        <section class="py-16 bg-[#0066cc] text-white relative overflow-hidden reveal-on-scroll">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ stats.total_books ? (stats.total_books + '+') : '5240+' }}</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Koleksi Buku</div>
                    </div>
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ stats.total_members ? (stats.total_members + '+') : '380+' }}</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Anggota Aktif</div>
                    </div>
                    <div class="space-y-2 transform hover:scale-105 transition-transform duration-300">
                        <div class="text-4xl sm:text-5xl font-black tracking-tight">{{ stats.total_visitors ? (stats.total_visitors + '+') : '125+' }}</div>
                        <div class="text-xs font-extrabold uppercase tracking-widest text-blue-100">Pengunjung Harian</div>
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
                                book.stock > 0 ? 'bg-emerald-500' : 'bg-rose-500',
                                'absolute top-3 right-3 text-[10px] font-extrabold text-white px-2.5 py-1 rounded-full shadow-md'
                            ]">
                                {{ book.stock > 0 ? `Stok: ${book.stock}` : 'Stok Habis' }}
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
                                <p class="text-[10px] text-slate-400 mt-0.5 font-mono">Rak: {{ book.shelf }}</p>
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
                                    <div class="text-slate-600 text-xs mt-0.5 font-medium">{{ settings.school_address || 'Jl. Raya Maron No. 02, Kec. Maron, Kab. Probolinggo' }}</div>
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
                            <div class="w-full h-64 rounded-3xl border-4 border-white shadow-xl overflow-hidden relative">
                                <iframe 
                                    :src="settings.gmaps_embed_url || 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15809.123456789!2d113.3150000!3d-7.8500000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd70123456789%3A0x123456789!2sSD%20Negeri%2002%20Maron!5e0!3m2!1sid!2sid!4v1700000000000'"
                                    class="w-full h-full border-0"
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Digital Guest Form -->
                    <div id="buku-tamu" class="bg-[#f4f8fc] p-8 lg:p-10 rounded-3xl shadow-xl border border-slate-100 reveal-on-scroll reveal-delay-2">
                        <div class="mb-6">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-100 text-[#0066cc] text-xs font-extrabold uppercase tracking-wider mb-2">
                                <FileText class="w-3.5 h-3.5 text-[#0066cc]" />
                                <span>KUNJUNGAN PERPUSTAKAAN</span>
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                                Buku Tamu Digital
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-1">Isi formulir ini saat Anda berkunjung ke perpustakaan.</p>
                        </div>

                        <form @submit.prevent="submitGuestForm" class="space-y-4">
                            <div class="space-y-1">
                                <label class="font-extrabold text-xs text-slate-700 uppercase">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input v-model="guestForm.name" required class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none text-xs font-medium" placeholder="Masukkan nama lengkap Anda" type="text"/>
                            </div>
                            <div class="space-y-1">
                                <label class="font-extrabold text-xs text-slate-700 uppercase">Email / No. WA / Instansi / Kelas <span class="text-rose-500">*</span></label>
                                <input v-model="guestForm.institution" required class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none text-xs font-medium" placeholder="SDN 02 Maron / Kelas 5B / Umum" type="text"/>
                            </div>
                            <div class="space-y-1">
                                <label class="font-extrabold text-xs text-slate-700 uppercase">Subjek / Keperluan <span class="text-rose-500">*</span></label>
                                <select v-model="guestForm.purpose" required class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none text-xs font-bold text-slate-700">
                                    <option value="Pertanyaan Umum">Pertanyaan Umum</option>
                                    <option value="Membaca Buku">Membaca Buku</option>
                                    <option value="Meminjam Buku">Meminjam Buku</option>
                                    <option value="Donasi Buku">Donasi Buku</option>
                                    <option value="Kerjasama Literasi">Kerjasama Literasi</option>
                                    <option value="Keluhan Pelayanan">Keluhan Pelayanan</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="font-extrabold text-xs text-slate-700 uppercase">Pesan &amp; Kesan</label>
                                <textarea v-model="guestForm.feedback" class="w-full p-3.5 rounded-2xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none text-xs font-medium" placeholder="Tuliskan kesan atau saran Anda..." rows="3"></textarea>
                            </div>
                            <button :disabled="guestForm.processing" class="w-full bg-[#0066cc] hover:bg-[#0052a3] text-white py-3.5 rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 mt-2" type="submit">
                                <Send class="w-4 h-4" />
                                <span>{{ guestForm.processing ? 'Mengirim...' : 'Kirim Pesan Sekarang' }}</span>
                            </button>
                        </form>
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
