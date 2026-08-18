<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { User, Mail, Lock, ShieldCheck, Sparkles, BookOpen } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    role: 'peneliti',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun — LITERA AI Research Navigator" />

    <div class="text-slate-900 min-h-screen flex items-center justify-center p-3 md:p-6 bg-slate-50 relative overflow-hidden font-sans">
        <!-- Background Decoration Glows -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Main Register Card -->
        <main class="w-full max-w-[960px] flex flex-col md:flex-row bg-white rounded-3xl shadow-[0px_4px_30px_rgba(0,0,0,0.08)] overflow-hidden my-auto border border-slate-100">
            <!-- Left Side: Branding Banner -->
            <section class="hidden md:flex md:w-5/12 bg-gradient-to-br from-blue-900 via-slate-900 to-blue-950 text-white relative flex-col items-center justify-center p-8 lg:p-10 overflow-hidden">
                <div class="relative z-10 text-center flex flex-col items-center">
                    <img src="/images/logo2.png" alt="LITERA Logo" class="h-12 w-auto object-contain mb-4" />

                    <h3 class="text-2xl font-black text-white tracking-tight">LITERA</h3>
                    <p class="text-blue-200 text-xs max-w-xs mb-6 leading-relaxed font-medium mt-1">
                        Bergabunglah dengan platform navigasi penelitian ilmiah berbasis kecerdasan buatan.
                    </p>

                    <!-- Feature Bullet Highlights -->
                    <div class="space-y-3 text-left w-full max-w-xs text-xs font-semibold text-blue-100">
                        <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
                            <Sparkles class="w-4 h-4 text-amber-400 shrink-0" />
                            <span>Akses AI Research Search</span>
                        </div>
                        <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
                            <BookOpen class="w-4 h-4 text-blue-400 shrink-0" />
                            <span>Personal Research Workspace</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right Side: Registration Form -->
            <section class="w-full md:w-7/12 flex flex-col justify-center px-6 py-8 md:px-10 md:py-10 bg-white">
                <!-- Mobile Header -->
                <div class="flex items-center gap-3 mb-6 md:hidden">
                    <img src="/images/logo2.png" alt="LITERA Logo" class="h-9 w-auto object-contain" />
                </div>

                <div class="mb-6 text-left">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 mb-1 tracking-tight">Daftar Akun LITERA</h2>
                    <p class="text-xs text-slate-500 font-medium">Lengkapi formulir di bawah untuk membuat ruang kerja penelitian Anda.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block ml-1" for="name">
                            NAMA LENGKAP
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <User class="w-4 h-4" />
                            </div>
                            <input 
                                id="name" 
                                v-model="form.name" 
                                type="text" 
                                required 
                                autofocus 
                                placeholder="Dr. Ahmad Subagyo / Budi Santoso" 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:border-blue-600 focus:bg-white focus:outline-none transition-all"
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-[11px] font-semibold text-rose-600 ml-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block ml-1" for="email">
                            ALAMAT EMAIL
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <Mail class="w-4 h-4" />
                            </div>
                            <input 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                required 
                                placeholder="peneliti@universitas.ac.id" 
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:border-blue-600 focus:bg-white focus:outline-none transition-all"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-[11px] font-semibold text-rose-600 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Role Selection -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block ml-1">
                            PERAN PENGGUNA
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label :class="[
                                'p-3 rounded-xl border cursor-pointer transition-all flex items-center gap-2.5 text-xs font-extrabold',
                                form.role === 'peneliti' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-2 ring-blue-500/20' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                            ]">
                                <input type="radio" v-model="form.role" value="peneliti" class="sr-only" />
                                <Sparkles class="w-4 h-4 text-blue-600" />
                                <span>Peneliti / Pengajar</span>
                            </label>

                            <label :class="[
                                'p-3 rounded-xl border cursor-pointer transition-all flex items-center gap-2.5 text-xs font-extrabold',
                                form.role === 'user' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-2 ring-blue-500/20' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                            ]">
                                <input type="radio" v-model="form.role" value="user" class="sr-only" />
                                <BookOpen class="w-4 h-4 text-blue-600" />
                                <span>Pelajar / Umum</span>
                            </label>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block ml-1" for="password">
                                KATA SANDI
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <Lock class="w-4 h-4" />
                                </div>
                                <input 
                                    id="password" 
                                    v-model="form.password" 
                                    type="password" 
                                    required 
                                    placeholder="••••••••" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:border-blue-600 focus:bg-white focus:outline-none transition-all"
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-[11px] font-semibold text-rose-600 ml-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block ml-1" for="password_confirmation">
                                KONFIRMASI SANDI
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <ShieldCheck class="w-4 h-4" />
                                </div>
                                <input 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation" 
                                    type="password" 
                                    required 
                                    placeholder="••••••••" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:border-blue-600 focus:bg-white focus:outline-none transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Login Link -->
                    <div class="pt-2 space-y-3">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2"
                        >
                            <span>{{ form.processing ? 'Mendaftarkan Akun...' : 'Daftar Akun Sekarang' }}</span>
                        </button>

                        <div class="text-center text-xs text-slate-500">
                            Sudah memiliki akun? 
                            <Link href="/login" class="text-blue-600 hover:underline font-extrabold ml-1">Masuk di sini</Link>
                        </div>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
