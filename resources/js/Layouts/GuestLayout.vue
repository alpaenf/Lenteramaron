<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';

const page = usePage();
const settings = computed(() => page.props.settings || page.props.app_settings || {});
const flash = computed(() => page.props.flash || {});

const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-surface text-on-surface font-sans selection:bg-primary selection:text-on-primary">
        <!-- Toast Notification Flash -->
        <div v-if="flash.success" class="fixed top-24 right-5 z-[60] flex items-center gap-3 bg-secondary text-on-secondary px-5 py-3.5 rounded-xl shadow-xl animate-bounce">
            <CheckCircle class="w-5 h-5 shrink-0" />
            <span class="font-medium text-sm">{{ flash.success }}</span>
        </div>
        <div v-if="flash.error" class="fixed top-24 right-5 z-[60] flex items-center gap-3 bg-error text-on-error px-5 py-3.5 rounded-xl shadow-xl">
            <AlertCircle class="w-5 h-5 shrink-0" />
            <span class="font-medium text-sm">{{ flash.error }}</span>
        </div>

        <!-- Floating Capsule Glassmorphic Header -->
        <header class="fixed top-4 left-4 right-4 z-50 max-w-7xl mx-auto">
            <nav class="w-full bg-white/80 backdrop-blur-xl border border-white/60 rounded-full shadow-lg shadow-black/5 px-6 py-2.5 transition-all duration-300">
                <div class="flex justify-between items-center h-11">
                    <!-- Brand Logo -->
                    <Link href="/" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-8 sm:h-9 w-auto object-contain" />
                    </Link>

                    <!-- Desktop Links -->
                    <div class="hidden md:flex items-center gap-7 font-bold text-sm text-slate-600">
                        <a class="hover:text-blue-600 transition-colors" href="#">Beranda</a>
                        <a class="hover:text-blue-600 transition-colors" href="#profil">Profil</a>
                        <a class="hover:text-blue-600 transition-colors" href="#layanan">Layanan</a>
                        <a class="hover:text-blue-600 transition-colors" href="#katalog">Katalog</a>
                        <a class="hover:text-blue-600 transition-colors" href="#galeri">Galeri</a>
                        <a class="hover:text-blue-600 transition-colors" href="#buku-tamu">Buku Tamu</a>
                    </div>

                    <!-- CTA Button -->
                    <div class="hidden md:flex items-center">
                        <Link v-if="page.props.auth?.user" href="/dashboard" class="bg-[#0066cc] hover:bg-[#0052a3] text-white px-6 py-2 rounded-full font-bold transition-all shadow-md shadow-blue-500/20 text-xs flex items-center gap-2">
                            <span>Dashboard</span>
                        </Link>
                        <Link v-else href="/login" class="bg-[#0066cc] hover:bg-[#0052a3] text-white px-6 py-2 rounded-full font-bold transition-all shadow-md shadow-blue-500/20 text-xs flex items-center gap-2">
                            <span>Login Staf</span>
                            <span class="material-symbols-outlined text-sm">person</span>
                        </Link>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-1.5 text-primary focus:outline-none rounded-full hover:bg-primary/10">
                        <span class="material-symbols-outlined text-2xl flex items-center justify-center">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
                    </button>
                </div>

                <!-- Mobile Menu Dropdown -->
                <div v-if="mobileMenuOpen" class="md:hidden mt-3 pt-3 border-t border-outline-variant/30 space-y-2 pb-2">
                    <a @click="mobileMenuOpen = false" href="#profil" class="block text-on-surface-variant hover:text-primary py-1 font-medium text-sm">Profil</a>
                    <a @click="mobileMenuOpen = false" href="#layanan" class="block text-on-surface-variant hover:text-primary py-1 font-medium text-sm">Layanan</a>
                    <a @click="mobileMenuOpen = false" href="#katalog" class="block text-on-surface-variant hover:text-primary py-1 font-medium text-sm">Katalog</a>
                    <a @click="mobileMenuOpen = false" href="#galeri" class="block text-on-surface-variant hover:text-primary py-1 font-medium text-sm">Galeri</a>
                    <a @click="mobileMenuOpen = false" href="#buku-tamu" class="block text-on-surface-variant hover:text-primary py-1 font-medium text-sm">Buku Tamu</a>
                    <div class="pt-2">
                        <Link v-if="page.props.auth?.user" href="/dashboard" class="block w-full text-center bg-primary text-on-primary py-2.5 rounded-full font-bold shadow-md text-sm">
                            Dashboard
                        </Link>
                        <Link v-else href="/login" class="block w-full text-center bg-primary text-on-primary py-2.5 rounded-full font-bold shadow-md text-sm">
                            Login Staf
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="pt-20 flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-inverse-surface text-on-surface-variant py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                    <div class="md:col-span-2 space-y-4">
                        <div class="mb-2">
                            <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-10 w-auto object-contain bg-white/90 p-1.5 rounded-xl shadow-md inline-block" />
                        </div>
                        <p class="max-w-md text-outline-variant text-sm leading-relaxed">
                            Perpustakaan resmi SD Negeri 02 Maron. Berkomitmen menyediakan akses literasi yang inklusif, modern, dan menginspirasi bagi seluruh siswa-siswi.
                        </p>
                        <div class="flex gap-3 pt-2">
                            <a class="w-10 h-10 rounded-full bg-surface-variant/10 flex items-center justify-center hover:bg-primary transition-all text-white" href="#"><span class="material-symbols-outlined">qr_code_2</span></a>
                            <a class="w-10 h-10 rounded-full bg-surface-variant/10 flex items-center justify-center hover:bg-primary transition-all text-white" href="#"><span class="material-symbols-outlined">photo_camera</span></a>
                            <a class="w-10 h-10 rounded-full bg-surface-variant/10 flex items-center justify-center hover:bg-primary transition-all text-white" href="#"><span class="material-symbols-outlined">video_library</span></a>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h4 class="text-white font-bold uppercase tracking-wider text-xs">Tautan Cepat</h4>
                        <ul class="space-y-2 text-sm text-outline-variant">
                            <li><a class="hover:text-primary-fixed transition-colors" href="#">Beranda</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#profil">Profil</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#layanan">Layanan</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#galeri">Galeri</a></li>
                        </ul>
                    </div>
                    <div class="space-y-3">
                        <h4 class="text-white font-bold uppercase tracking-wider text-xs">Informasi</h4>
                        <ul class="space-y-2 text-sm text-outline-variant">
                            <li><a class="hover:text-primary-fixed transition-colors" href="#katalog">Katalog Online</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#layanan">Jadwal Operasional</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#profil">Aturan Perpustakaan</a></li>
                            <li><a class="hover:text-primary-fixed transition-colors" href="#kontak">Staf Perpustakaan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pt-8 border-t border-outline/20 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-outline-variant">© {{ new Date().getFullYear() }} Lentera Maron - SD Negeri 02 Maron. All rights reserved.</p>
                    <div class="flex gap-6 text-xs text-outline-variant">
                        <a class="hover:text-white" href="#">Kebijakan Privasi</a>
                        <a class="hover:text-white" href="#">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
