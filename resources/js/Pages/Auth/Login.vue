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
    <Head title="Login | Lentera Maron SDN 02 Maron" />

    <div class="text-on-background min-h-screen flex items-center justify-center p-3 md:p-6 bg-surface relative overflow-hidden">
        <!-- Background Decoration Glows -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-secondary/15 rounded-full blur-3xl"></div>
        </div>

        <!-- Main Login Container Card -->
        <main class="w-full max-w-[960px] flex flex-col md:flex-row bg-white rounded-3xl shadow-[0px_4px_30px_rgba(0,0,0,0.08)] overflow-hidden my-auto">
            <!-- Left Side: Illustration & Branding -->
            <section class="hidden md:flex md:w-1/2 bg-surface-container-low relative flex-col items-center justify-center p-8 lg:p-10 overflow-hidden border-r border-outline-variant/30">
                <div class="relative z-10 text-center flex flex-col items-center">
                    <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-12 w-auto object-contain mb-4" />

                    <p class="text-on-surface-variant text-xs lg:text-sm max-w-xs mb-6 leading-relaxed">
                        Jendela dunia bagi siswa-siswi cerdas masa depan. Mari mulai petualangan membaca hari ini!
                    </p>

                    <!-- Featured Library Image Card -->
                    <div class="w-full max-w-xs aspect-video rounded-2xl overflow-hidden shadow-lg border-2 border-white transform -rotate-1 hover:rotate-0 transition-transform duration-500">
                        <img src="/images/hero.png" alt="SDN 02 Maron Library" class="w-full h-full object-cover" />
                    </div>
                </div>

                <!-- Bottom Accent -->
                <div class="absolute bottom-6 left-6 flex items-center gap-1.5 opacity-70">
                    <span class="material-symbols-outlined text-secondary text-sm">verified</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-secondary">Sistem Lentera Maron</span>
                </div>
            </section>

            <!-- Right Side: Login Form -->
            <section class="w-full md:w-1/2 flex flex-col justify-center px-6 py-8 md:px-10 md:py-10 bg-white">
                <!-- Mobile Header Only -->
                <div class="flex items-center gap-3 mb-6 md:hidden">
                    <img src="/images/logo.png" alt="Lentera Maron Logo" class="h-9 w-auto object-contain" />
                </div>

                <div class="mb-6 text-left">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-on-surface mb-1 tracking-tight">Selamat Datang Kembali!</h2>
                    <p class="text-xs text-on-surface-variant">Silakan masuk untuk mengelola katalog buku dan melayani siswa.</p>
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
                        class="w-full py-3.5 bg-primary text-white rounded-xl font-bold shadow-md shadow-primary/20 hover:bg-primary-container active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 text-sm mt-2"
                    >
                        <span>{{ form.processing ? 'Memproses...' : 'Masuk ke Panel Admin' }}</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                <!-- Help Support -->
                <div class="mt-6 pt-4 border-t border-surface-container flex flex-col items-center gap-3">
                    <p class="text-xs text-on-surface-variant text-center">
                        Bukan Pustakawan? <Link href="/" class="text-secondary font-bold hover:underline ml-1">Kembali ke Beranda Utama</Link>
                    </p>
                    <div class="flex gap-5 text-[11px] font-bold text-on-surface-variant">
                        <Link href="/#buku-tamu" class="flex items-center gap-1 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-sm">help</span>
                            <span class="tracking-wider">BANTUAN</span>
                        </Link>
                        <span class="flex items-center gap-1 text-outline">
                            <span class="material-symbols-outlined text-sm">language</span>
                            <span class="tracking-wider">INDONESIA</span>
                        </span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
