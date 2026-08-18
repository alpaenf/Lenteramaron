<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Settings, Save, School, MapPin, Mail, Phone, ExternalLink, Image } from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
});

const preview1 = ref(null);
const preview2 = ref(null);
const preview3 = ref(null);
const preview4 = ref(null);

const form = useForm({
    school_name: props.settings.school_name || 'SD Negeri 02 Maron',
    library_name: props.settings.library_name || 'LENTERA MARON',
    school_address: props.settings.school_address || '',
    school_email: props.settings.school_email || '',
    school_phone: props.settings.school_phone || '',
    headmaster_name: props.settings.headmaster_name || '',
    librarian_name: props.settings.librarian_name || '',
    vision: props.settings.vision || '',
    mission: props.settings.mission || '',
    gmaps_embed_url: props.settings.gmaps_embed_url || '',
    spreadsheet_url: props.settings.spreadsheet_url || '',
    profile_photo_1: null,
    profile_photo_2: null,
    profile_photo_3: null,
    profile_photo_4: null,
});

const getAssetUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    if (path.startsWith('/')) return path;
    if (path.startsWith('uploads/')) return '/' + path;
    return '/files-media/' + path;
};

const handleFileChange = (e, field, setPreview) => {
    const file = e.target.files[0];
    if (file) {
        form[field] = file;
        setPreview(URL.createObjectURL(file));
    }
};

const submit = () => {
    form.post('/settings', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            preview1.value = null;
            preview2.value = null;
            preview3.value = null;
            preview4.value = null;
            form.profile_photo_1 = null;
            form.profile_photo_2 = null;
            form.profile_photo_3 = null;
            form.profile_photo_4 = null;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-4xl space-y-6">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem &amp; Profil Sekolah</h1>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi identitas perpustakaan, foto profil, tautan Google Maps, dan Google Spreadsheet.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-6 text-xs">
                <!-- Identitas Sekolah & Perpustakaan -->
                <div class="space-y-4">
                    <h3 class="font-extrabold text-slate-900 text-sm border-b border-slate-100 pb-2 flex items-center gap-2">
                        <School class="w-4 h-4 text-blue-600" />
                        <span>Identitas Perpustakaan &amp; Sekolah</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nama Perpustakaan <span class="text-rose-500">*</span></label>
                            <input v-model="form.library_name" type="text" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nama Sekolah <span class="text-rose-500">*</span></label>
                            <input v-model="form.school_name" type="text" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kepala Sekolah</label>
                            <input v-model="form.headmaster_name" type="text" placeholder="Drs. H. Mulyono, M.Pd" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kepala Perpustakaan</label>
                            <input v-model="form.librarian_name" type="text" placeholder="Siti Pustakawan, S.IP" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                        </div>
                    </div>
                </div>

                <!-- Foto Profil Perpustakaan (4 Foto Grid) -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm border-b border-slate-100 pb-2 flex items-center gap-2">
                        <Image class="w-4 h-4 text-blue-600" />
                        <span>Foto Profil Perpustakaan (4 Foto Grid Landing Page)</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Foto 1 -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                            <label class="block font-bold text-slate-700 uppercase text-[10px]">Foto 1 (Kiri Atas)</label>
                            <div class="h-28 rounded-xl bg-cover bg-center border border-slate-200" :style="{ backgroundImage: `url('${preview1 || getAssetUrl(props.settings.profile_photo_1) || 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            <input type="file" @change="(e) => handleFileChange(e, 'profile_photo_1', (url) => preview1 = url)" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>

                        <!-- Foto 2 -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                            <label class="block font-bold text-slate-700 uppercase text-[10px]">Foto 2 (Kiri Bawah)</label>
                            <div class="h-28 rounded-xl bg-cover bg-center border border-slate-200" :style="{ backgroundImage: `url('${preview2 || getAssetUrl(props.settings.profile_photo_2) || 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            <input type="file" @change="(e) => handleFileChange(e, 'profile_photo_2', (url) => preview2 = url)" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>

                        <!-- Foto 3 -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                            <label class="block font-bold text-slate-700 uppercase text-[10px]">Foto 3 (Kanan Atas)</label>
                            <div class="h-28 rounded-xl bg-cover bg-center border border-slate-200" :style="{ backgroundImage: `url('${preview3 || getAssetUrl(props.settings.profile_photo_3) || 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            <input type="file" @change="(e) => handleFileChange(e, 'profile_photo_3', (url) => preview3 = url)" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>

                        <!-- Foto 4 -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                            <label class="block font-bold text-slate-700 uppercase text-[10px]">Foto 4 (Kanan Bawah)</label>
                            <div class="h-28 rounded-xl bg-cover bg-center border border-slate-200" :style="{ backgroundImage: `url('${preview4 || getAssetUrl(props.settings.profile_photo_4) || 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=800&q=80'}')` }"></div>
                            <input type="file" @change="(e) => handleFileChange(e, 'profile_photo_4', (url) => preview4 = url)" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100" />
                        </div>
                    </div>
                </div>

                <!-- Visi Misi -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm border-b border-slate-100 pb-2">Visi &amp; Misi Perpustakaan</h3>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Visi Perpustakaan</label>
                        <textarea v-model="form.vision" rows="2" class="w-full p-3 rounded-xl border border-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Misi Perpustakaan (Pisahkan dengan Enter)</label>
                        <textarea v-model="form.mission" rows="4" class="w-full p-3 rounded-xl border border-slate-200 font-sans"></textarea>
                    </div>
                </div>

                <!-- Kontak & Integrasi Google -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="font-extrabold text-slate-900 text-sm border-b border-slate-100 pb-2 flex items-center gap-2">
                        <MapPin class="w-4 h-4 text-blue-600" />
                        <span>Kontak &amp; Integrasi Eksternal</span>
                    </h3>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Sekolah Lengkap</label>
                        <textarea v-model="form.school_address" rows="2" class="w-full p-3 rounded-xl border border-slate-200"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Email Resmi / Tujuan Notifikasi Gmail Admin</label>
                            <input v-model="form.school_email" type="email" placeholder="sdn02maron@gmail.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                            <span class="text-[10px] text-slate-400 mt-1 block">Notifikasi Buku Tamu dari pengunjung akan otomatis dikirimkan ke Gmail ini.</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nomor Telepon / WA</label>
                            <input v-model="form.school_phone" type="text" placeholder="0812-3456-7890" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">URL Embed Google Maps (iframe src)</label>
                        <input v-model="form.gmaps_embed_url" type="text" placeholder="https://www.google.com/maps/embed?..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-[11px]" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700 uppercase">Link Google Spreadsheet Dokumentasi</label>
                            <a v-if="form.spreadsheet_url" :href="form.spreadsheet_url" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1">
                                <span>Buka Spreadsheet</span>
                                <ExternalLink class="w-3 h-3" />
                            </a>
                        </div>
                        <input v-model="form.spreadsheet_url" type="url" placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-[11px]" />
                        <span class="text-[10px] text-slate-400 mt-1 block">Tautkan link Google Sheet untuk dokumentasi arsip digital perpustakaan.</span>
                    </div>
                </div>

                <div class="pt-4 flex justify-end border-t border-slate-100">
                    <button :disabled="form.processing" type="submit" class="px-7 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
