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
            <nav :class="[mobileMenuOpen ? 'rounded-3xl p-5' : 'rounded-full px-6 py-2.5', 'w-full bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-xl shadow-slate-900/5 transition-all duration-300']">
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
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-700 hover:text-blue-600 focus:outline-none rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl flex items-center justify-center">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
                    </button>
                </div>

                <!-- Mobile Menu Dropdown -->
                <div v-if="mobileMenuOpen" class="md:hidden mt-4 pt-4 border-t border-slate-100 space-y-1">
                    <a @click="mobileMenuOpen = false" href="#" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Beranda</a>
                    <a @click="mobileMenuOpen = false" href="#profil" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Profil</a>
                    <a @click="mobileMenuOpen = false" href="#layanan" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Layanan</a>
                    <a @click="mobileMenuOpen = false" href="#katalog" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Katalog</a>
                    <a @click="mobileMenuOpen = false" href="#galeri" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Galeri</a>
                    <a @click="mobileMenuOpen = false" href="#buku-tamu" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Buku Tamu</a>
                    <div class="pt-3 border-t border-slate-100 mt-2">
                        <Link v-if="page.props.auth?.user" href="/dashboard" class="block w-full text-center bg-[#0066cc] hover:bg-[#0052a3] text-white py-3 rounded-2xl font-extrabold shadow-md text-sm transition-all">
                            Dashboard
                        </Link>
                        <Link v-else href="/login" class="block w-full text-center bg-[#0066cc] hover:bg-[#0052a3] text-white py-3 rounded-2xl font-extrabold shadow-md text-sm transition-all flex items-center justify-center gap-2">
                            <span>Login Staf</span>
                            <span class="material-symbols-outlined text-base">person</span>
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
                            <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-12 w-auto object-contain inline-block" />
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
