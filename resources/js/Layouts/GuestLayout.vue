<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CheckCircle, AlertCircle, Sparkles, BookOpen } from 'lucide-vue-next';

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
                        <img src="/images/logo2.png" alt="Lentera Maron Logo" class="h-8 sm:h-9 w-auto object-contain" />
                    </Link>

                    <!-- Desktop Links -->
                    <div class="hidden md:flex items-center gap-6 font-bold text-sm text-slate-600">
                        <Link href="/litera/search" class="text-blue-600 hover:text-blue-800 transition-colors font-extrabold flex items-center gap-1.5 bg-blue-50 px-3.5 py-1.5 rounded-full border border-blue-200">
                            <Sparkles class="w-3.5 h-3.5 text-blue-600" />
                            <span>AI Research</span>
                        </Link>
                        <Link href="/" class="hover:text-blue-600 transition-colors">Beranda</Link>
                        <a class="hover:text-blue-600 transition-colors" href="/#profil">Profil</a>
                        <a class="hover:text-blue-600 transition-colors" href="/#katalog">Katalog Referensi</a>
                        <a class="hover:text-blue-600 transition-colors" href="/#galeri">Galeri</a>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="hidden md:flex items-center gap-2">
                        <Link href="/litera/workspace" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-full font-bold transition-all text-xs flex items-center gap-1.5 border border-slate-200">
                            <BookOpen class="w-3.5 h-3.5 text-blue-600" />
                            <span>Research Workspace</span>
                        </Link>

                        <Link v-if="page.props.auth?.user" href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full font-bold transition-all shadow-md shadow-blue-500/20 text-xs flex items-center gap-2">
                            <span>Dashboard</span>
                        </Link>
                        <template v-else>
                            <Link href="/login" class="text-slate-700 hover:text-blue-600 font-extrabold text-xs px-3 py-2">
                                <span>Masuk</span>
                            </Link>
                            <Link href="/register" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full font-bold transition-all shadow-md shadow-blue-500/20 text-xs flex items-center gap-1.5">
                                <span>Daftar</span>
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-700 hover:text-blue-600 focus:outline-none rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl flex items-center justify-center">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
                    </button>
                </div>

                <!-- Mobile Menu Dropdown -->
                <div v-if="mobileMenuOpen" class="md:hidden mt-4 pt-4 border-t border-slate-100 space-y-1">
                    <Link href="/litera/search" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-blue-600 hover:bg-blue-50 font-extrabold text-sm transition-colors flex items-center gap-2">
                        <Sparkles class="w-4 h-4 text-blue-600" />
                        <span>AI Research Search</span>
                    </Link>
                    <Link href="/litera/workspace" @click="mobileMenuOpen = false" class="px-4 py-2.5 rounded-xl text-slate-800 hover:bg-slate-100 font-extrabold text-sm transition-colors flex items-center gap-2">
                        <BookOpen class="w-4 h-4 text-blue-600" />
                        <span>Research Workspace</span>
                    </Link>
                    <Link @click="mobileMenuOpen = false" href="/" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Beranda</Link>
                    <a @click="mobileMenuOpen = false" href="/#profil" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Profil</a>
                    <a @click="mobileMenuOpen = false" href="/#katalog" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Katalog Referensi</a>
                    <a @click="mobileMenuOpen = false" href="/#galeri" class="block px-4 py-2.5 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-blue-50 font-bold text-sm transition-colors">Galeri</a>
                    <div class="pt-3 border-t border-slate-100 mt-2 space-y-2">
                        <Link v-if="page.props.auth?.user" href="/dashboard" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-extrabold shadow-md text-sm transition-all">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link href="/login" class="block w-full text-center border border-slate-200 text-slate-700 py-2.5 rounded-2xl font-extrabold text-sm transition-all">
                                <span>Masuk</span>
                            </Link>
                            <Link href="/register" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-2xl font-extrabold shadow-md text-sm transition-all">
                                <span>Daftar Akun Baru</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                    <div class="md:col-span-2 space-y-4">
                        <div class="mb-2 flex items-center gap-2">
                            <img src="/images/logo2.png" alt="LITERA Logo" class="h-10 w-auto object-contain inline-block" />
                            <span class="text-white font-extrabold text-lg tracking-tight">LITERA</span>
                        </div>
                        <p class="max-w-md text-slate-400 text-sm leading-relaxed">
                            LITERA — AI-Powered Research &amp; Library Navigator. Platform navigasi cerdas yang menghubungkan koleksi referensi perpustakaan lokal dengan sumber jurnal ilmiah internasional.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <h4 class="text-white font-bold uppercase tracking-wider text-xs">Fitur Utama</h4>
                        <ul class="space-y-2 text-sm text-slate-400">
                            <li><Link class="hover:text-blue-400 transition-colors" href="/litera/search">AI Research Search</Link></li>
                            <li><Link class="hover:text-blue-400 transition-colors" href="/litera/workspace">Research Workspace</Link></li>
                            <li><a class="hover:text-blue-400 transition-colors" href="#katalog">Katalog Referensi</a></li>
                        </ul>
                    </div>
                    <div class="space-y-3">
                        <h4 class="text-white font-bold uppercase tracking-wider text-xs">Informasi</h4>
                        <ul class="space-y-2 text-sm text-slate-400">
                            <li><a class="hover:text-blue-400 transition-colors" href="#profil">Profil Platform</a></li>
                            <li><a class="hover:text-blue-400 transition-colors" href="#layanan">Layanan AI</a></li>
                            <li><a class="hover:text-blue-400 transition-colors" href="#galeri">Galeri Activities</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-500">© {{ new Date().getFullYear() }} LITERA — AI-Powered Research &amp; Library Navigator. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
