<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, X, Save, Layers, Edit, Trash2, Globe, Sparkles } from '@lucide/vue';

const props = defineProps({
  pages: {
    type: Array,
    required: true
  }
});

const isModalOpen = ref(false);

const form = useForm({
  title: '',
  slug: '',
  meta_description: ''
});

const createPage = () => {
  form.post(route('admin.pages.store'), {
    onSuccess: () => {
      isModalOpen.value = false;
      form.reset();
    }
  });
};

const handleDelete = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus halaman "${item.title}"?`)) {
    router.delete(route('admin.pages.destroy', item.id));
  }
};
</script>

<template>
  <Head title="Kelola Halaman CMS - TALENTA52" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <Layers class="w-3.5 h-3.5" />
            <span>Manajemen Halaman Statis & Dinamis</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
            Kelola Halaman Web
          </h1>
          <p class="text-xs md:text-sm text-slate-200 font-medium max-w-xl">
            Atur struktur layout landing page, banner hero, blok keunggulan, dan testimoni website TALENTA52.
          </p>
        </div>

        <div class="relative z-10 shrink-0">
          <button 
            @click="isModalOpen = true"
            class="px-5 py-3 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black rounded-2xl shadow-lg shadow-[#04bdb2]/30 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>Buat Halaman Baru</span>
          </button>
        </div>
      </div>

      <!-- Pages Grid / Table Card -->
      <div class="bg-white rounded-3xl border border-slate-200/80 p-6 md:p-8 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <h3 class="text-base font-black text-slate-900">
            Daftar Halaman ({{ pages.length }})
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 bg-slate-50/50 text-[10px] uppercase tracking-wider text-slate-400 font-black">
                <th class="py-3 px-4">Nama Halaman</th>
                <th class="py-3 px-4">Slug / URL</th>
                <th class="py-3 px-4 text-center">Jumlah Section</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
              <tr 
                v-for="page in pages" 
                :key="page.id"
                class="hover:bg-slate-50/60 transition-colors"
              >
                <td class="py-3.5 px-4">
                  <div class="space-y-0.5">
                    <span class="font-extrabold text-slate-900 text-sm block">{{ page.title }}</span>
                    <p class="text-[10px] text-slate-400 font-normal line-clamp-1">{{ page.meta_description || 'Tidak ada meta deskripsi' }}</p>
                  </div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-500">
                  /{{ page.slug === 'home' ? '' : page.slug }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2.5 py-0.5 bg-indigo-50 text-[#1653a1] font-extrabold rounded-md border border-indigo-100 text-[10px]">
                    {{ page.sections_count || 0 }} Bagian
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span 
                    :class="page.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
                    class="px-2.5 py-0.5 rounded-full border text-[10px] font-extrabold uppercase"
                  >
                    {{ page.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <Link 
                      :href="route('admin.pages.edit', page.id)" 
                      class="px-3 py-1.5 bg-[#1653a1]/10 hover:bg-[#1653a1] text-[#1653a1] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                    >
                      <Edit class="w-3.5 h-3.5" />
                      <span>Edit Konten</span>
                    </Link>
                    <button 
                      v-if="page.slug !== 'home'"
                      @click="handleDelete(page)"
                      class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer"
                      title="Hapus Halaman"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create Page Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 space-y-6 shadow-2xl border border-slate-100 animate-fade-in">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-black text-slate-900">Buat Halaman Baru</h3>
            <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-700">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="createPage" class="space-y-4">
            <div>
              <label for="title" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                Judul Halaman
              </label>
              <input 
                id="title"
                v-model="form.title"
                type="text" 
                required
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                placeholder="Contoh: Tentang Kami, Program Sosial"
                @input="form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
              />
              <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold mt-1">{{ form.errors.title }}</div>
            </div>

            <div>
              <label for="slug" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                Slug (URL)
              </label>
              <input 
                id="slug"
                v-model="form.slug"
                type="text" 
                required
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                placeholder="tentang-kami"
              />
              <div v-if="form.errors.slug" class="text-xs text-rose-500 font-semibold mt-1">{{ form.errors.slug }}</div>
            </div>

            <div>
              <label for="meta_description" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                Meta Description SEO
              </label>
              <textarea 
                id="meta_description"
                v-model="form.meta_description"
                rows="3"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] resize-none"
                placeholder="Tulis deskripsi ringkas untuk pencarian Google..."
              ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
              <button 
                type="button" 
                @click="isModalOpen = false" 
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="form.processing"
                class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] text-white font-black text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-1.5"
              >
                <Save class="w-3.5 h-3.5" />
                Simpan Halaman
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
