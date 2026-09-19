<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ChevronLeft, Layout, Edit, Eye, Plus, Trash2, Layers, Sparkles } from '@lucide/vue';

const props = defineProps({
  page: {
    type: Object,
    required: true
  }
});

const pageForm = useForm({
  title: props.page.title,
  slug: props.page.slug,
  meta_description: props.page.meta_description || '',
  is_active: props.page.is_active
});

const activeSectionId = ref(null);
const sectionForm = useForm({
  title: '',
  content: {},
  is_active: true
});

const selectSection = (section) => {
  activeSectionId.value = section.id;
  sectionForm.title = section.title || '';
  sectionForm.is_active = section.is_active;
  // Deep clone section content
  sectionForm.content = JSON.parse(JSON.stringify(section.content || {}));
};

const updatePage = () => {
  pageForm.put(route('admin.pages.update', props.page.id));
};

const updateSection = (sectionId) => {
  sectionForm.put(route('admin.pages.sections.update', [props.page.id, sectionId]), {
    preserveScroll: true,
    onSuccess: () => {
      // Keep section open
    }
  });
};

// Helpers for features & testimonials list additions/deletions
const addFeatureItem = () => {
  if (!sectionForm.content.items) sectionForm.content.items = [];
  sectionForm.content.items.push({ title: 'Fitur Baru', description: 'Deskripsi fitur...' });
};

const removeFeatureItem = (index) => {
  sectionForm.content.items.splice(index, 1);
};

const addTestimonialItem = () => {
  if (!sectionForm.content.items) sectionForm.content.items = [];
  sectionForm.content.items.push({ name: 'Nama Klien', role: 'Jabatan', comment: 'Komentar testimoni...' });
};

const removeTestimonialItem = (index) => {
  sectionForm.content.items.splice(index, 1);
};
</script>

<template>
  <Head :title="`Edit Halaman: ${page.title} - TALENTA52`" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      <!-- Header Banner -->
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link 
            :href="route('admin.pages.index')" 
            class="p-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-2xl text-slate-600 hover:text-slate-900 transition-colors shadow-xs"
          >
            <ChevronLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ page.title }}</h1>
              <span 
                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border"
                :class="page.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
              >
                {{ page.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola parameter SEO dan tata letak bagian (section) konten.</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- SEO Details Panel (Left) -->
        <div class="lg:col-span-4 space-y-6">
          <form @submit.prevent="updatePage" class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight pb-3 border-b border-slate-100 flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-[#1653a1]" />
              Metadata SEO Halaman
            </h3>
            
            <div class="space-y-1">
              <label for="title" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Judul Halaman</label>
              <input 
                id="title"
                v-model="pageForm.title"
                type="text" 
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
              />
            </div>

            <div class="space-y-1">
              <label for="slug" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Slug (URL)</label>
              <input 
                id="slug"
                v-model="pageForm.slug"
                type="text" 
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
              />
            </div>

            <div class="space-y-1">
              <label for="meta_description" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Meta Description SEO</label>
              <textarea 
                id="meta_description"
                v-model="pageForm.meta_description"
                rows="3"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] resize-none"
              ></textarea>
            </div>

            <div class="flex items-center gap-2 py-1">
              <input 
                id="page_active"
                v-model="pageForm.is_active"
                type="checkbox" 
                class="w-4 h-4 rounded text-[#04bdb2] focus:ring-[#04bdb2] border-slate-300"
              />
              <label for="page_active" class="text-xs font-bold text-slate-700 cursor-pointer">Aktifkan Halaman Ini</label>
            </div>

            <button 
              type="submit" 
              :disabled="pageForm.processing"
              class="w-full py-2.5 px-4 bg-[#1653a1] hover:bg-[#103f80] disabled:opacity-50 text-xs font-black uppercase tracking-wider text-white rounded-xl shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
            >
              <Save class="w-4 h-4" />
              <span>Simpan Metadata</span>
            </button>
          </form>

          <!-- List of Sections -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight pb-3 border-b border-slate-100 flex items-center gap-2">
              <Layers class="w-4 h-4 text-[#04bdb2]" />
              Daftar Bagian (Sections)
            </h3>
            
            <div class="space-y-2">
              <button 
                v-for="section in page.sections" 
                :key="section.id"
                type="button"
                @click="selectSection(section)"
                :class="[
                  activeSectionId === section.id 
                    ? 'bg-[#1653a1]/10 border-[#1653a1]/40 text-[#1653a1] font-black' 
                    : 'bg-slate-50/80 border-slate-200/80 text-slate-700 hover:bg-slate-100 font-bold',
                  'w-full text-left px-4 py-3 rounded-2xl border flex items-center justify-between transition-all cursor-pointer'
                ]"
              >
                <div class="flex items-center gap-2">
                  <Layout class="w-4 h-4" />
                  <span class="text-xs">{{ section.title }}</span>
                </div>
                <span class="text-[9px] px-2 py-0.5 bg-slate-200/60 text-slate-600 rounded-md uppercase font-mono">
                  {{ section.key }}
                </span>
              </button>
              <div v-if="page.sections.length === 0" class="text-center py-6 text-slate-400 text-xs font-medium">
                Belum ada section di halaman ini.
              </div>
            </div>
          </div>
        </div>

        <!-- Section Content Editor Panel (Right) -->
        <div class="lg:col-span-8">
          <div v-if="activeSectionId" class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-sm space-y-6 p-6 md:p-8">
            <!-- Header Editor -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div>
                <h3 class="text-base font-black text-slate-900">Edit Bagian Konten</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sesuaikan teks headline, poin keunggulan, atau testimoni.</p>
              </div>
              <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-extrabold uppercase" v-if="sectionForm.is_active">
                Aktif
              </span>
            </div>

            <!-- Content Form Editor -->
            <form @submit.prevent="updateSection(activeSectionId)" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                  <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Judul Bagian (Internal)</label>
                  <input 
                    v-model="sectionForm.title"
                    type="text" 
                    required
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                  />
                </div>
                <div class="flex items-center gap-2 md:pt-6">
                  <input 
                    id="section_active"
                    v-model="sectionForm.is_active"
                    type="checkbox" 
                    class="w-4 h-4 rounded text-[#04bdb2] focus:ring-[#04bdb2] border-slate-300"
                  />
                  <label for="section_active" class="text-xs font-bold text-slate-700 cursor-pointer">Tampilkan Bagian Ini di Website</label>
                </div>
              </div>

              <!-- Dynamic form fields depending on the key -->
              <div class="border-t border-slate-100 pt-6 space-y-4">
                <!-- Hero Section Editor -->
                <div v-if="page.sections.find(s => s.id === activeSectionId)?.key === 'hero'" class="space-y-4">
                  <div class="space-y-1">
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Headline Utama</label>
                    <input 
                      v-model="sectionForm.content.headline"
                      type="text" 
                      class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                    />
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Sub-headline</label>
                    <textarea 
                      v-model="sectionForm.content.subheadline"
                      rows="3"
                      class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] resize-none"
                    ></textarea>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                      <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Teks Tombol (CTA)</label>
                      <input 
                        v-model="sectionForm.content.cta_text"
                        type="text" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                      />
                    </div>
                    <div class="space-y-1">
                      <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Link Tombol (URL CTA)</label>
                      <input 
                        v-model="sectionForm.content.cta_url"
                        type="text" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                      />
                    </div>
                  </div>
                </div>

                <!-- Features Section Editor -->
                <div v-else-if="page.sections.find(s => s.id === activeSectionId)?.key === 'features'" class="space-y-4">
                  <div class="space-y-1">
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Judul Blok Keunggulan</label>
                    <input 
                      v-model="sectionForm.content.title"
                      type="text" 
                      class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                    />
                  </div>

                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Item Keunggulan</label>
                      <button 
                        type="button" 
                        @click="addFeatureItem"
                        class="text-xs font-extrabold text-[#04bdb2] hover:text-[#009c94] flex items-center gap-1 cursor-pointer"
                      >
                        <Plus class="w-3.5 h-3.5" /> Tambah Item
                      </button>
                    </div>

                    <div 
                      v-for="(item, idx) in sectionForm.content.items" 
                      :key="idx"
                      class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 relative group"
                    >
                      <button 
                        type="button" 
                        @click="removeFeatureItem(idx)"
                        class="absolute top-3 right-3 p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                        title="Hapus item"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pr-8">
                        <div class="space-y-1">
                          <label class="text-[10px] font-extrabold text-slate-500 uppercase block">Nama Keunggulan</label>
                          <input 
                            v-model="item.title"
                            type="text" 
                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#04bdb2]"
                          />
                        </div>
                        <div class="space-y-1">
                          <label class="text-[10px] font-extrabold text-slate-500 uppercase block">Keterangan / Deskripsi</label>
                          <input 
                            v-model="item.description"
                            type="text" 
                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-[#04bdb2]"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Testimonials Section Editor -->
                <div v-else-if="page.sections.find(s => s.id === activeSectionId)?.key === 'testimonials'" class="space-y-4">
                  <div class="space-y-1">
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Judul Blok Testimoni</label>
                    <input 
                      v-model="sectionForm.content.title"
                      type="text" 
                      class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                    />
                  </div>

                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Item Testimoni</label>
                      <button 
                        type="button" 
                        @click="addTestimonialItem"
                        class="text-xs font-extrabold text-[#04bdb2] hover:text-[#009c94] flex items-center gap-1 cursor-pointer"
                      >
                        <Plus class="w-3.5 h-3.5" /> Tambah Testimoni
                      </button>
                    </div>

                    <div 
                      v-for="(item, idx) in sectionForm.content.items" 
                      :key="idx"
                      class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 relative group"
                    >
                      <button 
                        type="button" 
                        @click="removeTestimonialItem(idx)"
                        class="absolute top-3 right-3 p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                        title="Hapus item"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pr-8">
                        <div class="space-y-1">
                          <label class="text-[10px] font-extrabold text-slate-500 uppercase block">Nama Pengirim</label>
                          <input 
                            v-model="item.name"
                            type="text" 
                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#04bdb2]"
                          />
                        </div>
                        <div class="space-y-1">
                          <label class="text-[10px] font-extrabold text-slate-500 uppercase block">Profesi / Domisili</label>
                          <input 
                            v-model="item.role"
                            type="text" 
                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-[#04bdb2]"
                          />
                        </div>
                      </div>
                      <div class="space-y-1">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase block">Ulasan / Komentar Testimoni</label>
                        <textarea 
                          v-model="item.comment"
                          rows="2"
                          class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-[#04bdb2] resize-none"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Generic JSON Editor -->
                <div v-else class="space-y-2">
                  <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Parameter JSON Data Konten</label>
                  <textarea 
                    :value="JSON.stringify(sectionForm.content, null, 2)"
                    @input="e => {
                      try {
                        sectionForm.content = JSON.parse(e.target.value);
                      } catch (err) {}
                    }"
                    rows="8"
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                  ></textarea>
                </div>
              </div>

              <!-- Action Footer -->
              <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button 
                  type="submit" 
                  :disabled="sectionForm.processing"
                  class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-1.5"
                >
                  <Save class="w-4 h-4" />
                  <span>Simpan Bagian Ini</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Empty Editor State -->
          <div v-else class="h-full min-h-[300px] bg-white border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center p-12 text-center text-slate-400">
            <Layout class="w-12 h-12 mb-3 text-slate-300" />
            <h3 class="text-sm font-bold text-slate-700">Pilih Bagian Halaman</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-xs">Silakan pilih salah satu bagian di menu sebelah kiri untuk mengedit isinya secara visual.</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
