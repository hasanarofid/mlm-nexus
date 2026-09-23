<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Save, 
  ChevronLeft, 
  Image as ImageIcon, 
  Bold, 
  Italic, 
  Heading2, 
  Heading3, 
  List, 
  Quote, 
  Link as LinkIcon, 
  Eye, 
  PenTool, 
  Sparkles,
  Layers,
  X,
  CheckCircle2,
  Calendar
} from '@lucide/vue';

const props = defineProps({
  post: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    required: true
  }
});

const isEdit = !!props.post;

const form = useForm({
  _method: isEdit ? 'PUT' : 'POST',
  category_id: props.post?.category_id || (props.categories.length > 0 ? props.categories[0].id : ''),
  title: props.post?.title || '',
  slug: props.post?.slug || '',
  content: props.post?.content || '',
  image: null,
  status: props.post?.status || 'published',
  is_featured: props.post?.is_featured ?? false
});

const imagePreview = ref(props.post?.image_url || null);
const isDragging = ref(false);
const activeTab = ref('editor'); // 'editor' | 'preview'
const textareaRef = ref(null);

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  form.image = null;
  imagePreview.value = null;
};

// Toolbar formatting helpers
const insertFormat = (prefix, suffix = '') => {
  const textarea = textareaRef.value;
  if (!textarea) return;

  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const text = form.content;
  const selected = text.substring(start, end);

  form.content = text.substring(0, start) + prefix + selected + suffix + text.substring(end);

  // Restore cursor position
  setTimeout(() => {
    textarea.focus();
    textarea.setSelectionRange(start + prefix.length, end + prefix.length);
  }, 50);
};

const submit = () => {
  if (isEdit) {
    form.post(route('admin.posts.update', props.post.id), {
      forceFormData: true,
      preserveScroll: true
    });
  } else {
    form.post(route('admin.posts.store'), {
      forceFormData: true
    });
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Artikel - NEXUS COMMUNITY' : 'Tulis Artikel Baru - NEXUS COMMUNITY'" />

  <AdminLayout>
    <div class="space-y-6 max-w-6xl mx-auto">
      <!-- Header Banner -->
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link 
            :href="route('admin.posts.index')" 
            class="p-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-2xl text-slate-600 hover:text-slate-900 transition-colors shadow-xs"
          >
            <ChevronLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">
              {{ isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru' }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              {{ isEdit ? 'Perbarui isi berita atau artikel edukasi NEXUS COMMUNITY.' : 'Buat publikasi berita atau panduan resmi untuk jaringan kemitraan.' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button 
            type="button"
            @click="submit"
            :disabled="form.processing"
            class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] disabled:opacity-50 text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>{{ isEdit ? 'Simpan Perubahan' : 'Publikasikan' }}</span>
          </button>
        </div>
      </div>

      <!-- Main Form Grid -->
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Content Column (Left) -->
        <div class="lg:col-span-8 space-y-6">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <!-- Title -->
            <div class="space-y-1.5">
              <label for="title" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                Judul Artikel / Berita
              </label>
              <input 
                id="title"
                v-model="form.title"
                type="text" 
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] transition-all"
                placeholder="Contoh: Strategi Sukses Meraih Personal Poin Reward NEXUS COMMUNITY"
                @input="!isEdit && (form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''))"
              />
              <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold">{{ form.errors.title }}</div>
            </div>

            <!-- Slug -->
            <div class="space-y-1.5">
              <label for="slug" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                Slug URL
              </label>
              <div class="flex items-center bg-slate-50 border border-slate-200 rounded-2xl px-3 py-1 text-xs text-slate-500 font-mono">
                <span class="text-slate-400 select-none">/artikel/</span>
                <input 
                  id="slug"
                  v-model="form.slug"
                  type="text" 
                  required
                  class="w-full bg-transparent border-none py-2 px-1 text-xs font-bold text-slate-800 focus:outline-none"
                  placeholder="slug-url-artikel"
                />
              </div>
              <div v-if="form.errors.slug" class="text-xs text-rose-500 font-semibold">{{ form.errors.slug }}</div>
            </div>

            <!-- Content Area with Tabs & Rich Toolbar -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label for="content" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                  Konten / Isi Artikel
                </label>

                <!-- Tab Switcher: Editor vs Live Preview -->
                <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl">
                  <button 
                    type="button"
                    @click="activeTab = 'editor'"
                    :class="activeTab === 'editor' ? 'bg-white text-slate-900 font-extrabold shadow-2xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                    class="px-3 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                  >
                    <PenTool class="w-3.5 h-3.5" />
                    Editor
                  </button>
                  <button 
                    type="button"
                    @click="activeTab = 'preview'"
                    :class="activeTab === 'preview' ? 'bg-white text-[#1653a1] font-extrabold shadow-2xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                    class="px-3 py-1 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    Pratinjau Live
                  </button>
                </div>
              </div>

              <!-- Toolbar (Only in Editor Mode) -->
              <div v-if="activeTab === 'editor'" class="flex items-center gap-1 p-1.5 bg-slate-50 border border-slate-200 rounded-xl flex-wrap">
                <button 
                  type="button" 
                  @click="insertFormat('**', '**')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Tebal (Bold)"
                >
                  <Bold class="w-3.5 h-3.5" />
                </button>
                <button 
                  type="button" 
                  @click="insertFormat('*', '*')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Miring (Italic)"
                >
                  <Italic class="w-3.5 h-3.5" />
                </button>
                <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
                <button 
                  type="button" 
                  @click="insertFormat('## ')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Heading 2"
                >
                  <Heading2 class="w-3.5 h-3.5" />
                </button>
                <button 
                  type="button" 
                  @click="insertFormat('### ')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Heading 3"
                >
                  <Heading3 class="w-3.5 h-3.5" />
                </button>
                <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
                <button 
                  type="button" 
                  @click="insertFormat('\n- ')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Daftar List"
                >
                  <List class="w-3.5 h-3.5" />
                </button>
                <button 
                  type="button" 
                  @click="insertFormat('\n> ')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Kutipan (Quote)"
                >
                  <Quote class="w-3.5 h-3.5" />
                </button>
                <button 
                  type="button" 
                  @click="insertFormat('[Teks Link](', ')')" 
                  class="p-1.5 hover:bg-slate-200 rounded text-slate-700 transition-colors"
                  title="Sisipkan Tautan (Link)"
                >
                  <LinkIcon class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Editor Textarea -->
              <div v-if="activeTab === 'editor'">
                <textarea 
                  id="content"
                  ref="textareaRef"
                  v-model="form.content"
                  rows="14"
                  required
                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] transition-all resize-y font-mono leading-relaxed"
                  placeholder="Tuliskan naskah artikel, pengumuman, atau konten di sini. Gunakan tombol toolbar di atas untuk format teks..."
                ></textarea>
              </div>

              <!-- Live Preview Tab -->
              <div v-else class="p-6 bg-slate-50/70 border border-slate-200 rounded-2xl min-h-[350px] space-y-4">
                <div class="border-b border-slate-200 pb-3">
                  <span class="text-[10px] font-extrabold text-[#04bdb2] uppercase tracking-wider block">
                    {{ categories.find(c => c.id === form.category_id)?.name || 'Kategori' }}
                  </span>
                  <h2 class="text-xl font-black text-slate-900 mt-1">
                    {{ form.title || 'Judul Artikel Anda Akan Tampil di Sini' }}
                  </h2>
                </div>

                <div v-if="imagePreview" class="rounded-xl overflow-hidden max-h-60 bg-white border border-slate-200 flex items-center justify-center">
                  <img :src="imagePreview" alt="Cover Preview" class="w-full h-full object-cover max-h-60" />
                </div>

                <div class="text-sm text-slate-700 leading-relaxed space-y-3">
                  <div 
                    v-for="(paragraph, pIdx) in (form.content || 'Tuliskan isi konten pada tab Editor untuk melihat pratinjau.').split('\n\n')" 
                    :key="pIdx"
                  >
                    <h3 v-if="paragraph.startsWith('### ')" class="text-base font-black text-slate-900 pt-2">
                      {{ paragraph.replace('### ', '') }}
                    </h3>
                    <h2 v-else-if="paragraph.startsWith('## ')" class="text-lg font-black text-slate-900 pt-2 border-b border-slate-200 pb-1">
                      {{ paragraph.replace('## ', '') }}
                    </h2>
                    <blockquote v-else-if="paragraph.startsWith('> ')" class="p-3 bg-white border-l-4 border-[#04bdb2] rounded-r-xl text-slate-600 italic">
                      {{ paragraph.replace('> ', '') }}
                    </blockquote>
                    <ul v-else-if="paragraph.includes('\n- ') || paragraph.startsWith('- ')" class="space-y-1 list-disc list-inside bg-white p-3 rounded-xl border border-slate-200">
                      <li v-for="(item, iIdx) in paragraph.split('\n').filter(l => l.trim().startsWith('-'))" :key="iIdx" class="text-xs font-medium text-slate-700">
                        {{ item.replace(/^-\s*/, '') }}
                      </li>
                    </ul>
                    <p v-else class="whitespace-pre-line">
                      {{ paragraph }}
                    </p>
                  </div>
                </div>
              </div>

              <div v-if="form.errors.content" class="text-xs text-rose-500 font-semibold">{{ form.errors.content }}</div>
            </div>
          </div>
        </div>

        <!-- Sidebar Settings Column (Right) -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Publication Settings Card -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-5">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight border-b border-slate-100 pb-3 flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-[#1653a1]" />
              Pengaturan Publikasi
            </h3>

            <!-- Category -->
            <div class="space-y-1.5">
              <label for="category_id" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                Pilih Kategori
              </label>
              <select 
                id="category_id"
                v-model="form.category_id"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] transition-all cursor-pointer"
              >
                <option value="" disabled>Pilih Kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <div v-if="form.errors.category_id" class="text-xs text-rose-500 font-semibold">{{ form.errors.category_id }}</div>
            </div>

            <!-- Status -->
            <div class="space-y-1.5">
              <label for="status" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                Status Publikasi
              </label>
              <select 
                id="status"
                v-model="form.status"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2] transition-all cursor-pointer"
              >
                <option value="published">Published (Tampil di Beranda & Publik)</option>
                <option value="draft">Draft (Simpan Internal)</option>
              </select>
            </div>

            <!-- Featured Toggle -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
              <div>
                <label for="is_featured" class="text-xs font-bold text-slate-800 block cursor-pointer">Rekomendasikan Artikel</label>
                <p class="text-[10px] text-slate-400">Tampilkan di posisi teratas</p>
              </div>
              <input 
                id="is_featured"
                v-model="form.is_featured"
                type="checkbox" 
                class="w-4 h-4 rounded text-[#04bdb2] focus:ring-[#04bdb2] border-slate-300"
              />
            </div>
          </div>

          <!-- Cover Image Upload Card -->
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight border-b border-slate-100 pb-3 flex items-center gap-2">
              <ImageIcon class="w-4 h-4 text-emerald-600" />
              Gambar Cover
            </h3>

            <!-- Upload Area -->
            <div 
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                isDragging ? 'border-[#04bdb2] bg-[#04bdb2]/5' : 'border-slate-200 bg-slate-50 hover:border-slate-300',
                'border-2 border-dashed rounded-2xl p-5 flex flex-col items-center justify-center text-center cursor-pointer transition-all relative min-h-[160px]'
              ]"
            >
              <input 
                id="image"
                type="file" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                accept="image/*"
                @change="handleImageChange"
              />

              <!-- Preview -->
              <div v-if="imagePreview" class="relative group w-full rounded-xl overflow-hidden">
                <img 
                  :src="imagePreview" 
                  alt="Post Cover Preview" 
                  class="w-full h-36 object-cover rounded-xl"
                />
                <button 
                  type="button" 
                  @click.stop="removeImage"
                  class="absolute top-2 right-2 p-1.5 bg-rose-600 text-white rounded-lg shadow-md hover:bg-rose-700 transition-colors"
                  title="Hapus gambar"
                >
                  <X class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Upload Placeholder -->
              <div v-else class="flex flex-col items-center gap-2">
                <div class="p-3 bg-[#1653a1]/10 rounded-2xl text-[#1653a1]">
                  <ImageIcon class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-700">Pilih / Seret Cover di Sini</p>
                  <p class="text-[10px] text-slate-400 mt-0.5">PNG, JPG, WEBP (Maks 2 MB)</p>
                </div>
              </div>
            </div>

            <div v-if="form.errors.image" class="text-xs text-rose-500 font-semibold mt-1">{{ form.errors.image }}</div>
          </div>

          <!-- Quick Action Card -->
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-3 px-4 bg-[#1653a1] hover:bg-[#103f80] disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <Save class="w-4 h-4" />
              <span>{{ isEdit ? 'Simpan Perubahan' : 'Publikasikan Sekarang' }}</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
