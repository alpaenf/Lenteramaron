<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
    BookOpen, 
    LayoutDashboard, 
    BookMarked, 
    Users, 
    ClipboardList, 
    ArrowRightLeft, 
    RotateCcw, 
    BarChart3, 
    Image as ImageIcon, 
    Settings, 
    LogOut, 
    Menu, 
    X,
    CheckCircle,
    AlertCircle,
    Bell,
    ExternalLink,
    Shield,
    Clock
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user || {});
const flash = computed(() => page.props.flash || {});

const sidebarOpen = ref(false);
const notificationsOpen = ref(false);
const currentDate = ref('');

const notificationsData = computed(() => page.props.notifications || { items: [], overdue_count: 0, guest_today_count: 0 });
const notificationItems = computed(() => notificationsData.value.items || []);
const hasNotifications = computed(() => notificationItems.value.length > 0);

const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value;
};

const closeNotifications = () => {
    notificationsOpen.value = false;
};

onMounted(() => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    currentDate.value = new Date().toLocaleDateString('id-ID', options);
});

const logout = () => {
    router.post('/logout');
};

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, roles: ['Admin', 'Pustakawan', 'Guru', 'Kepala Sekolah'] },
    { name: 'Master Buku', href: '/books', icon: BookMarked, roles: ['Admin', 'Pustakawan', 'Guru', 'Kepala Sekolah'] },
    { name: 'Data Anggota', href: '/members', icon: Users, roles: ['Admin', 'Pustakawan', 'Guru', 'Kepala Sekolah'] },
    { name: 'Buku Tamu', href: '/guest-books', icon: ClipboardList, roles: ['Admin', 'Pustakawan', 'Guru', 'Kepala Sekolah'] },
    { name: 'Peminjaman', href: '/borrowings', icon: ArrowRightLeft, roles: ['Admin', 'Pustakawan'] },
    { name: 'Pengembalian', href: '/returns', icon: RotateCcw, roles: ['Admin', 'Pustakawan'] },
    { name: 'Laporan & Analytics', href: '/reports', icon: BarChart3, roles: ['Admin', 'Pustakawan', 'Guru', 'Kepala Sekolah'] },
    { name: 'Galeri Kegiatan', href: '/galleries', icon: ImageIcon, roles: ['Admin', 'Pustakawan'] },
    { name: 'Pengaturan Sistem', href: '/settings', icon: Settings, roles: ['Admin'] },
];

const filteredNav = computed(() => {
    if (!user.value.role) return [];
    return navigation.filter(item => item.roles.includes(user.value.role));
});

const isCurrentRoute = (path) => {
    return page.url.startsWith(path);
};

const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
};
</script>

<template>
    <div class="bg-surface font-sans text-on-surface min-h-screen flex selection:bg-primary selection:text-on-primary">
        <!-- Toast Notification Flash -->
        <div v-if="flash.success" class="fixed top-5 right-5 z-[60] flex items-center gap-3 bg-secondary text-on-secondary px-5 py-3.5 rounded-xl shadow-xl animate-bounce">
            <CheckCircle class="w-5 h-5 shrink-0" />
            <span class="font-medium text-sm">{{ flash.success }}</span>
        </div>
        <div v-if="flash.error" class="fixed top-5 right-5 z-[60] flex items-center gap-3 bg-error text-on-error px-5 py-3.5 rounded-xl shadow-xl">
            <AlertCircle class="w-5 h-5 shrink-0" />
            <span class="font-medium text-sm">{{ flash.error }}</span>
        </div>

        <!-- Desktop Sidebar Navigation -->
        <aside class="hidden md:flex flex-col h-screen w-64 bg-surface-container-lowest border-r border-outline-variant/40 p-4 shrink-0 fixed inset-y-0 z-30">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 px-3 py-3 border-b border-outline-variant/30 mb-3">
                <Link href="/" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                    <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-9 w-auto object-contain" />
                </Link>
            </div>

            <!-- Profile Info Badge -->
            <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/40 flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-lg bg-secondary text-on-secondary font-bold text-xs flex items-center justify-center shrink-0">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'A' }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-on-surface truncate">{{ user.name }}</p>
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container">
                        {{ user.role || 'Petugas' }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-grow space-y-1.5 overflow-y-auto pr-1">
                <Link 
                    v-for="item in filteredNav" 
                    :key="item.name" 
                    :href="item.href"
                    :class="[
                        isCurrentRoute(item.href)
                            ? 'bg-primary text-on-primary font-bold shadow-md shadow-primary/20'
                            : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary font-medium',
                        'flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-all duration-200'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Sidebar Footer Buttons -->
            <div class="pt-3 border-t border-outline-variant/30 space-y-2 mt-auto">
                <Link href="/" target="_blank" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-outline-variant/60 text-on-surface-variant hover:bg-surface-container-low text-xs font-bold transition-colors">
                    <ExternalLink class="w-3.5 h-3.5" />
                    <span>Lihat Web Publik</span>
                </Link>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-error-container/60 hover:bg-error-container text-on-error-container text-xs font-bold transition-colors">
                    <LogOut class="w-3.5 h-3.5" />
                    <span>Keluar Akun</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow md:pl-64 flex flex-col min-h-screen overflow-hidden relative">
            <!-- Top Header -->
            <header class="w-full px-3.5 sm:px-8 py-3.5 flex justify-between items-center bg-surface-container-lowest/95 backdrop-blur-md border-b border-outline-variant/30 shadow-xs z-10 sticky top-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 pr-2">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-xl text-on-surface-variant hover:bg-surface-container-low shrink-0">
                        <Menu class="w-5 h-5" />
                    </button>
                    <div class="min-w-0 overflow-hidden">
                        <h2 class="text-sm sm:text-lg text-on-surface font-extrabold tracking-tight truncate">
                            {{ getGreeting() }}, {{ user.name }}!
                        </h2>
                        <p class="text-on-surface-variant text-[10px] sm:text-xs mt-0.5 truncate">{{ currentDate }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <Link href="/" target="_blank" title="Lihat Website Publik" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface text-xs font-bold transition-colors border border-outline-variant/40">
                        <ExternalLink class="w-3.5 h-3.5" />
                        <span>Web Publik</span>
                    </Link>

                    <!-- Notification Button & Dropdown -->
                    <div class="relative">
                        <button 
                            @click="toggleNotifications" 
                            class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-surface-container-low transition-colors relative text-on-surface-variant border border-outline-variant/40" 
                            title="Notifikasi Sistem"
                        >
                            <Bell class="w-4 h-4" />
                            <span v-if="hasNotifications" class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full animate-ping"></span>
                            <span v-if="hasNotifications" class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
                        </button>

                        <!-- Backdrop -->
                        <div v-if="notificationsOpen" @click="closeNotifications" class="fixed inset-0 z-40"></div>

                        <!-- Dropdown Panel -->
                        <div v-if="notificationsOpen" class="absolute right-0 mt-2.5 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200/80 z-50 overflow-hidden transform transition-all duration-200">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Bell class="w-4 h-4 text-primary" />
                                    <span class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Notifikasi Sistem</span>
                                </div>
                                <span v-if="hasNotifications" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-extrabold text-[10px]">
                                    {{ notificationItems.length }} Baru
                                </span>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <Link 
                                    v-for="item in notificationItems" 
                                    :key="item.id" 
                                    :href="item.url" 
                                    @click="closeNotifications"
                                    class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition-colors block text-left"
                                >
                                    <div :class="[
                                        'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs',
                                        item.type === 'warning' ? 'bg-amber-100 text-amber-700' :
                                        item.type === 'error' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700'
                                    ]">
                                        <AlertCircle v-if="item.type === 'error'" class="w-4 h-4" />
                                        <Clock v-else-if="item.type === 'warning'" class="w-4 h-4" />
                                        <CheckCircle v-else class="w-4 h-4" />
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <span class="font-extrabold text-xs text-slate-900 truncate">{{ item.title }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 shrink-0">{{ item.time }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ item.message }}</p>
                                    </div>
                                </Link>

                                <div v-if="!hasNotifications" class="p-8 text-center text-slate-400">
                                    <Bell class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                    <p class="text-xs font-medium">Tidak ada notifikasi baru saat ini.</p>
                                </div>
                            </div>

                            <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
                                <Link href="/reports" @click="closeNotifications" class="text-xs font-extrabold text-primary hover:underline">
                                    Lihat Seluruh Laporan Sistem →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-outline-variant/40"></div>

                    <button @click="logout" class="flex items-center gap-1.5 px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl hover:bg-error-container/60 hover:text-error transition-colors text-on-surface-variant text-xs font-bold border border-outline-variant/40">
                        <LogOut class="w-4 h-4" />
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </header>

            <!-- Mobile Drawer Overlay -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-inverse-surface/50 z-40 md:hidden"></div>
            <div v-if="sidebarOpen" class="fixed inset-y-0 left-0 w-72 bg-surface-container-lowest z-50 md:hidden flex flex-col p-4 shadow-2xl">
                <div class="flex justify-between items-center pb-4 border-b border-outline-variant/30">
                    <Link href="/" class="flex items-center gap-2">
                        <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-9 w-auto object-contain" />
                    </Link>
                    <button @click="sidebarOpen = false" class="p-1.5 rounded-xl hover:bg-surface-container-low text-on-surface-variant">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <nav class="flex-grow space-y-1.5 overflow-y-auto py-4">
                    <Link 
                        v-for="item in filteredNav" 
                        :key="item.name" 
                        :href="item.href"
                        @click="sidebarOpen = false"
                        :class="[
                            isCurrentRoute(item.href)
                                ? 'bg-primary text-on-primary font-bold shadow-md'
                                : 'text-on-surface-variant hover:bg-surface-container-low font-medium',
                            'flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-all'
                        ]"
                    >
                        <component :is="item.icon" class="w-5 h-5 shrink-0" />
                        <span>{{ item.name }}</span>
                    </Link>
                </nav>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-error-container/60 text-on-error-container text-sm font-bold">
                    <LogOut class="w-4 h-4" />
                    <span>Keluar</span>
                </button>
            </div>

            <!-- Page Content Scrollable Area -->
            <div class="flex-grow overflow-y-auto p-4 sm:p-8">
                <slot />
            </div>
        </main>
    </div>
</template>
