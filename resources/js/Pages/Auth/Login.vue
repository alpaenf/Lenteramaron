<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk — LITERA AI Research Navigator" />

    <div class="text-on-background min-h-screen flex items-center justify-center p-3 md:p-6 bg-slate-50 relative overflow-hidden">
        <!-- Background Decoration Glows -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Main Login Container Card -->
        <main class="w-full max-w-[960px] flex flex-col md:flex-row bg-white rounded-3xl shadow-[0px_4px_30px_rgba(0,0,0,0.08)] overflow-hidden my-auto border border-slate-100">
            <!-- Left Side: Illustration & Branding -->
            <section class="hidden md:flex md:w-1/2 bg-gradient-to-br from-blue-900 via-slate-900 to-blue-950 text-white relative flex-col items-center justify-center p-8 lg:p-10 overflow-hidden">
                <div class="relative z-10 text-center flex flex-col items-center">
                    <img src="/images/logo.png" alt="LITERA Logo" class="h-12 w-auto object-contain mb-4" />

                    <h3 class="text-xl font-black text-white">LITERA</h3>
                    <p class="text-blue-200 text-xs max-w-xs mb-6 leading-relaxed font-medium mt-1">
                        AI-Powered Research &amp; Library Navigator. Menghubungkan koleksi referensi dengan sumber ilmiah internasional.
                    </p>

                    <!-- Featured Image Card -->
                    <div class="w-full max-w-xs rounded-2xl overflow-hidden shadow-lg border-2 border-white/20 transform -rotate-1 hover:rotate-0 transition-transform duration-500 bg-white/5">
                        <img src="/images/hero1.png" alt="LITERA Platform" class="w-full h-auto object-contain" />
                    </div>
                </div>
            </section>

            <!-- Right Side: Login Form -->
            <section class="w-full md:w-1/2 flex flex-col justify-center px-6 py-8 md:px-10 md:py-10 bg-white">
                <!-- Mobile Header Only -->
                <div class="flex items-center gap-3 mb-6 md:hidden">
                    <img src="/images/logo.png" alt="LITERA Logo" class="h-9 w-auto object-contain" />
                </div>

                <div class="mb-6 text-left">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 mb-1 tracking-tight">Selamat Datang di LITERA</h2>
                    <p class="text-xs text-slate-500 font-medium">Silakan masuk ke akun Anda untuk mengelola referensi dan analitis riset.</p>
                </div>

                <!-- Status Flash Message -->
                <div v-if="status" class="mb-4 p-3 rounded-xl bg-secondary/10 text-secondary text-xs font-medium">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email / Username Field -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block ml-1" for="email">
                            USERNAME ATAU EMAIL
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors text-xl">person</span>
                            </div>
                            <input 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                required 
                                autofocus 
                                autocomplete="username" 
                                placeholder="admin.pustaka@sekolah.id" 
                                class="w-full pl-11 pr-4 py-3 bg-surface-container-low border-2 border-transparent rounded-xl text-xs font-medium focus:border-primary focus:bg-white focus:outline-none transition-all duration-200"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-[11px] font-semibold text-error mt-0.5 ml-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block ml-1" for="password">
                            KATA SANDI
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors text-xl">lock</span>
                            </div>
                            <input 
                                id="password" 
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                autocomplete="current-password" 
                                placeholder="••••••••" 
                                class="w-full pl-11 pr-11 py-3 bg-surface-container-low border-2 border-transparent rounded-xl text-xs font-medium focus:border-primary focus:bg-white focus:outline-none transition-all duration-200"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-outline hover:text-primary transition-colors focus:outline-none"
                            >
                                <span class="material-symbols-outlined text-xl">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-[11px] font-semibold text-error mt-0.5 ml-1">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Utilities: Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" v-model="form.remember" class="peer sr-only" />
                                <div class="w-4 h-4 bg-surface-container-high border-2 border-outline-variant rounded peer-checked:bg-secondary peer-checked:border-secondary transition-all"></div>
                                <span class="material-symbols-outlined absolute text-white opacity-0 peer-checked:opacity-100 text-[10px] left-0.5 transition-opacity">check</span>
                            </div>
                            <span class="text-xs font-medium text-on-surface-variant group-hover:text-on-surface transition-colors">Ingat Saya</span>
                        </label>
                        
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-[11px] font-bold text-primary hover:text-primary-container transition-colors uppercase tracking-wider">
                            LUPA KATA SANDI?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        :disabled="form.processing" 
                        type="submit" 
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-500/20 active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 text-xs mt-2"
                    >
                        <span>{{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}</span>
                    </button>
                </form>

                <!-- Register & Help Support -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col items-center gap-3">
                    <p class="text-xs text-slate-500 text-center">
                        Belum memiliki akun? 
                        <Link href="/register" class="text-blue-600 font-extrabold hover:underline ml-1">Daftar Akun Baru</Link>
                    </p>
                    <p class="text-[11px] text-slate-400 text-center">
                        <Link href="/" class="hover:text-blue-600 font-bold transition-colors">Kembali ke Beranda Utama</Link>
                    </p>
                </div>
            </section>
        </main>
    </div>
</template>
