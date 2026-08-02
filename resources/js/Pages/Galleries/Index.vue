<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Image as ImageIcon, Plus, Trash2, Edit, X } from 'lucide-vue-next';

const props = defineProps({
    galleries: Object,
    filters: Object,
});

const category = ref(props.filters.category || '');

const filter = () => {
    router.get('/galleries', { category: category.value }, { preserveState: true, replace: true });
};

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingGallery = ref(null);

const form = useForm({
    title: '',
    category: 'Literasi',
    image: null,
    description: '',
});

const imagePreview = ref(null);

const seederFallbackImages = {
    'galleries/literasi.jpg': 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80',
    'galleries/perpustakaan.jpg': 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
    'galleries/outing.jpg': 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
    'galleries/lomba.jpg': 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=800&q=80',
};

const getImageUrl = (path) => {
    if (!path) return getFallbackImage('Literasi');
    if (seederFallbackImages[path]) return seederFallbackImages[path];
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('uploads/')) return `/${path}`;
    return `/files-media/${path}`;
};

const getFallbackImage = (categoryName) => {
    switch (categoryName) {
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

const handleImgError = (e, categoryName) => {
    // Prevent infinite loop: only switch to fallback if not already showing one
    if (!e.target.src.includes('unsplash.com')) {
        e.target.src = getFallbackImage(categoryName);
    }
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const openCreateModal = () => {
    form.reset();
    imagePreview.value = null;
    isCreateOpen.value = true;
};

const openEditModal = (gal) => {
    editingGallery.value = gal;
    form.title = gal.title;
    form.category = gal.category;
    form.description = gal.description || '';
    form.image = null;
    imagePreview.value = gal.image_path ? getImageUrl(gal.image_path) : null;
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post('/galleries', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
            imagePreview.value = null;
        },
    });
};

const submitUpdate = () => {
    const formData = new FormData();
    formData.append('_method', 'put');
    formData.append('title', form.title);
    formData.append('category', form.category);
    formData.append('description', form.description || '');
    if (form.image) {
        formData.append('image', form.image);
    }

    router.post(`/galleries/${editingGallery.value.id}`, formData, {
        forceFormData: true,
        onSuccess: () => {
            isEditOpen.value = false;
            editingGallery.value = null;
            form.reset();
            imagePreview.value = null;
        },
    });
};

const deleteGallery = (gal) => {
    if (confirm(`Hapus foto galeri "${gal.title}"?`)) {
        router.delete(`/galleries/${gal.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Galeri Kegiatan Literasi</h1>
                    <p class="text-xs text-slate-500 mt-1">Dokumentasi foto kegiatan membaca, perpustakaan, outing class, dan lomba.</p>
                </div>
                <div>
                    <button @click="openCreateModal" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        <span>Tambah Foto Galeri</span>
                    </button>
                </div>
            </div>

            <!-- Category Filter Pills -->
            <div class="flex flex-wrap items-center gap-2">
                <button 
                    v-for="cat in ['', 'Literasi', 'Perpustakaan', 'Outing Class', 'Lomba']" 
                    :key="cat"
                    @click="category = cat; filter()"
                    :class="[
                        category === cat ? 'bg-blue-600 text-white font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 font-medium',
                        'px-4 py-2 rounded-xl text-xs border border-slate-200/80 transition-all'
                    ]"
                >
                    {{ cat === '' ? 'Semua Kategori' : cat }}
                </button>
            </div>

            <!-- Gallery Cards Grid -->
            <div v-if="galleries.data && galleries.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="gal in galleries.data" :key="gal.id" class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group">
                    <div class="aspect-[4/3] bg-slate-100 relative overflow-hidden">
                        <img 
                            :src="getImageUrl(gal.image_path)" 
                            :alt="gal.title" 
                            @error="handleImgError($event, gal.category)"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                        />
                        
                        <span class="absolute top-3 left-3 bg-slate-900/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-full backdrop-blur-md">
                            {{ gal.category }}
                        </span>

                        <!-- Action Buttons: Edit & Delete -->
                        <div class="absolute top-3 right-3 flex items-center gap-1.5 opacity-90 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEditModal(gal)" class="p-1.5 bg-blue-600/90 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md" title="Edit Foto / Details">
                                <Edit class="w-3.5 h-3.5" />
                            </button>
                            <button @click="deleteGallery(gal)" class="p-1.5 bg-rose-600/90 text-white rounded-lg hover:bg-rose-700 transition-colors shadow-md" title="Hapus Foto">
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs line-clamp-1 hover:text-blue-600 transition-colors">{{ gal.title }}</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-2 mt-1">{{ gal.description || 'Tidak ada deskripsi.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-2xl p-12 text-center text-slate-500 border border-slate-200/80">
                <ImageIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                <p class="font-medium text-sm">Belum ada foto galeri kegiatan untuk kategori ini.</p>
            </div>

            <!-- Modal Create / Edit -->
            <div v-if="isCreateOpen || isEditOpen" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-slate-900 text-base">{{ isCreateOpen ? 'Tambah Foto Kegiatan' : 'Edit Foto / Detail Galeri' }}</h3>
                        <button @click="isCreateOpen = false; isEditOpen = false" class="p-1 rounded-lg text-slate-400"><X class="w-5 h-5" /></button>
                    </div>

                    <form @submit.prevent="isCreateOpen ? submitCreate() : submitUpdate()" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Judul Kegiatan <span class="text-rose-500">*</span></label>
                            <input v-model="form.title" type="text" required placeholder="Contoh: Pekan Gerakan Literasi 2026" class="w-full px-3 py-2 rounded-xl border border-slate-200" />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Kategori Kegiatan <span class="text-rose-500">*</span></label>
                            <select v-model="form.category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                                <option value="Literasi">Literasi</option>
                                <option value="Perpustakaan">Perpustakaan</option>
                                <option value="Outing Class">Outing Class</option>
                                <option value="Lomba">Lomba</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">
                                {{ isCreateOpen ? 'Upload File Gambar *' : 'Ganti Gambar (Opsional)' }}
                            </label>
                            <input 
                                @change="handleImageChange" 
                                type="file" 
                                :required="isCreateOpen" 
                                accept="image/*" 
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700" 
                            />
                            <p class="text-[10px] text-slate-400 mt-1">Sistem otomatis mengompres &amp; mengoptimalkan ukuran foto.</p>
                        </div>

                        <div v-if="imagePreview" class="rounded-xl overflow-hidden h-36 bg-slate-100 border border-slate-200 relative group">
                            <img :src="imagePreview" class="w-full h-full object-cover" @error="handleImgError($event, form.category)" />
                            <span class="absolute bottom-2 right-2 bg-slate-900/70 text-white text-[10px] px-2 py-0.5 rounded-md backdrop-blur-sm">Pratinjau Foto</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Singkat</label>
                            <textarea v-model="form.description" rows="2" placeholder="Tuliskan catatan singkat kegiatan..." class="w-full p-2.5 rounded-xl border border-slate-200"></textarea>
                        </div>

                        <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="isCreateOpen = false; isEditOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">Batal</button>
                            <button :disabled="form.processing" type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-md">
                                {{ form.processing ? 'Menyimpan...' : (isCreateOpen ? 'Simpan Foto' : 'Perbarui Foto') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
