<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  Plus, 
  FolderPlus, 
  Trash2, 
  X, 
  Save, 
  FileText, 
  Search, 
  ExternalLink, 
  Edit, 
  CheckCircle2, 
  Clock, 
  Layers, 
  Sparkles,
  Tag
} from '@lucide/vue';

const props = defineProps({
  posts: {
    type: Array,
    required: true
  },
  categories: {
    type: Array,
    required: true
  }
});

const isCategoryModalOpen = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('all');
const selectedStatus = ref('all');

// Stats computations
const totalPosts = computed(() => props.posts.length);
const publishedPosts = computed(() => props.posts.filter(p => p.status === 'published').length);
const draftPosts = computed(() => props.posts.filter(p => p.status === 'draft').length);
const totalCategories = computed(() => props.categories.length);

// Filtered posts
const filteredPosts = computed(() => {
  return props.posts.filter(p => {
    const matchSearch = !searchQuery.value || 
      p.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
      p.slug.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchCategory = selectedCategory.value === 'all' || 
      String(p.category_id) === String(selectedCategory.value);
    
    const matchStatus = selectedStatus.value === 'all' || 
      p.status === selectedStatus.value;

    return matchSearch && matchCategory && matchStatus;
  });
});

const categoryForm = useForm({
  name: ''
});

const createCategory = () => {
  categoryForm.post(route('admin.categories.store'), {
    onSuccess: () => {
      isCategoryModalOpen.value = false;
      categoryForm.reset();
    }
  });
};

const deleteCategory = (cat) => {
  if (confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"? Ini akan menghapus semua postingan terkait.`)) {
    router.delete(route('admin.categories.destroy', cat.id));
  }
};

const handleDeletePost = (post) => {
  if (confirm(`Apakah Anda yakin ingin menghapus postingan "${post.title}"?`)) {
    router.delete(route('admin.posts.destroy', post.id));
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};
</script>

<template>
  <Head title="Kelola Artikel & Berita - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs text-[#a9fff7] font-extrabold uppercase tracking-wider">
            <FileText class="w-3.5 h-3.5" />
            <span>Manajemen Berita & Artikel</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
            Kelola Artikel & Publikasi
          </h1>
          <p class="text-xs md:text-sm text-slate-200 font-medium max-w-xl">
            Tulis, edit, dan kelola berita, artikel edukasi, serta panduan resmi untuk jaringan kemitraan NEXUS COMMUNITY.
          </p>
        </div>

        <div class="flex items-center gap-3 relative z-10 shrink-0">
          <button 
            @click="isCategoryModalOpen = true"
            class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold rounded-2xl backdrop-blur transition-all flex items-center gap-2 cursor-pointer"
          >
            <FolderPlus class="w-4 h-4 text-[#a9fff7]" />
            <span>Kategori Baru</span>
          </button>
          <Link 
            :href="route('admin.posts.create')"
            class="px-5 py-3 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black rounded-2xl shadow-lg shadow-[#04bdb2]/30 transition-all flex items-center gap-2 cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>Tulis Artikel Baru</span>
          </Link>
        </div>
      </div>

      <!-- Stats Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5">
        <!-- Total Posts -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div class="space-y-0.5">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total Post</span>
            <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ totalPosts }}</span>
            <p class="text-[10px] text-slate-500">Artikel & Berita</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-[#1653a1] flex items-center justify-center font-bold">
            <FileText class="w-5 h-5" />
          </div>
        </div>

        <!-- Published -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div class="space-y-0.5">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Diterbitkan</span>
            <span class="text-2xl sm:text-3xl font-black text-emerald-600">{{ publishedPosts }}</span>
            <p class="text-[10px] text-slate-500">Tampil di Publik</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <CheckCircle2 class="w-5 h-5" />
          </div>
        </div>

        <!-- Drafts -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div class="space-y-0.5">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Draf</span>
            <span class="text-2xl sm:text-3xl font-black text-amber-600">{{ draftPosts }}</span>
            <p class="text-[10px] text-slate-500">Belum Terbit</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
            <Clock class="w-5 h-5" />
          </div>
        </div>

        <!-- Total Categories -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
          <div class="space-y-0.5">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Kategori</span>
            <span class="text-2xl sm:text-3xl font-black text-[#04bdb2]">{{ totalCategories }}</span>
            <p class="text-[10px] text-slate-500">Kategori Aktif</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-[#04bdb2]/10 text-[#04bdb2] flex items-center justify-center font-bold">
            <Layers class="w-5 h-5" />
          </div>
        </div>
      </div>

      <!-- Filter and Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Posts Table Column -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4 p-6">
          <!-- Search & Filter Bar -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pb-2 border-b border-slate-100">
            <div class="relative w-full sm:w-72">
              <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Cari judul artikel..." 
                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#04bdb2] transition-colors"
              />
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl w-full sm:w-auto">
              <button 
                @click="selectedStatus = 'all'" 
                :class="selectedStatus === 'all' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                class="px-3 py-1.5 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none"
              >
                Semua
              </button>
              <button 
                @click="selectedStatus = 'published'" 
                :class="selectedStatus === 'published' ? 'bg-white text-emerald-700 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                class="px-3 py-1.5 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none"
              >
                Published
              </button>
              <button 
                @click="selectedStatus = 'draft'" 
                :class="selectedStatus === 'draft' ? 'bg-white text-amber-700 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                class="px-3 py-1.5 text-xs rounded-xl transition-all cursor-pointer flex-1 sm:flex-none"
              >
                Draft
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="border-b border-slate-200/80 bg-slate-50/50 text-[10px] uppercase tracking-wider text-slate-400 font-black">
                  <th class="py-3 px-4">Artikel</th>
                  <th class="py-3 px-4">Kategori</th>
                  <th class="py-3 px-4 text-center">Status</th>
                  <th class="py-3 px-4">Tanggal</th>
                  <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                <tr 
                  v-for="post in filteredPosts" 
                  :key="post.id" 
                  class="hover:bg-slate-50/60 transition-colors group"
                >
                  <!-- Title & Image -->
                  <td class="py-3.5 px-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                        <img v-if="post.image_url" :src="post.image_url" :alt="post.title" class="w-full h-full object-cover" />
                        <FileText v-else class="w-5 h-5 text-slate-400" />
                      </div>
                      <div class="space-y-0.5 min-w-0 max-w-xs">
                        <h4 class="font-extrabold text-slate-900 group-hover:text-[#1653a1] transition-colors line-clamp-1">
                          {{ post.title }}
                        </h4>
                        <p class="text-[10px] text-slate-400 font-mono truncate">
                          /artikel/{{ post.slug }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <!-- Category -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-full text-[10px] font-extrabold">
                      {{ post.category?.name || 'Uncategorized' }}
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="py-3.5 px-4 whitespace-nowrap text-center">
                    <span 
                      :class="post.status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                      class="px-2.5 py-0.5 rounded-full border text-[10px] font-extrabold uppercase"
                    >
                      {{ post.status }}
                    </span>
                  </td>

                  <!-- Date -->
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-medium">
                    {{ formatDate(post.created_at) }}
                  </td>

                  <!-- Actions -->
                  <td class="py-3.5 px-4 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <!-- View on web -->
                      <a 
                        :href="route('posts.detail', post.slug)" 
                        target="_blank"
                        class="p-2 text-slate-400 hover:text-[#1653a1] hover:bg-slate-100 rounded-xl transition-colors"
                        title="Lihat Halaman Publik"
                      >
                        <ExternalLink class="w-4 h-4" />
                      </a>
                      <!-- Edit -->
                      <Link 
                        :href="route('admin.posts.edit', post.id)" 
                        class="p-2 text-slate-400 hover:text-[#04bdb2] hover:bg-[#04bdb2]/10 rounded-xl transition-colors"
                        title="Edit Artikel"
                      >
                        <Edit class="w-4 h-4" />
                      </Link>
                      <!-- Delete -->
                      <button 
                        @click="handleDeletePost(post)" 
                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer"
                        title="Hapus Artikel"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="filteredPosts.length === 0">
                  <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                    Tidak ada artikel yang sesuai dengan filter pencarian.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categories Column -->
        <div class="lg:col-span-4 space-y-4">
          <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center gap-2">
                <Tag class="w-4 h-4 text-[#1653a1]" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                  Kategori Artikel
                </h3>
              </div>
              <button 
                @click="isCategoryModalOpen = true"
                class="text-[11px] font-extrabold text-[#04bdb2] hover:text-[#009c94] flex items-center gap-1 cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                Tambah
              </button>
            </div>

            <!-- Category List -->
            <div class="space-y-2">
              <div 
                v-for="cat in categories" 
                :key="cat.id" 
                class="p-3 bg-slate-50/70 hover:bg-slate-100/70 border border-slate-100 rounded-2xl flex items-center justify-between group transition-colors"
              >
                <div class="space-y-0.5">
                  <p class="text-xs font-extrabold text-slate-800">{{ cat.name }}</p>
                  <p class="text-[10px] text-slate-400 font-medium">{{ cat.posts_count || 0 }} postingan</p>
                </div>

                <button 
                  @click="deleteCategory(cat)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all cursor-pointer"
                  title="Hapus Kategori"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>

              <div v-if="categories.length === 0" class="text-center py-6 text-slate-400 text-xs font-medium">
                Belum ada kategori.
              </div>
            </div>
          </div>

          <!-- Quick Tip Card -->
          <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl text-xs space-y-1.5 text-slate-700">
            <span class="font-extrabold text-[#1653a1] block uppercase text-[10px] tracking-wider">
              💡 Tips Publikasi:
            </span>
            <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
              Artikel dengan status <strong>Published</strong> akan langsung muncul di halaman Beranda dan dapat dibagikan ke calon mitra via WhatsApp / media sosial.
            </p>
          </div>
        </div>
      </div>

      <!-- Create Category Modal -->
      <div v-if="isCategoryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-5 shadow-2xl border border-slate-100 animate-fade-in">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-black text-slate-900">Buat Kategori Baru</h3>
            <button @click="isCategoryModalOpen = false" class="text-slate-400 hover:text-slate-700">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="createCategory" class="space-y-4">
            <div>
              <label for="cat_name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                Nama Kategori
              </label>
              <input 
                id="cat_name"
                v-model="categoryForm.name"
                type="text" 
                required
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#04bdb2]"
                placeholder="Contoh: Info Kemitraan, Tips Sukses"
              />
              <div v-if="categoryForm.errors.name" class="text-xs text-rose-500 font-semibold mt-1">
                {{ categoryForm.errors.name }}
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
              <button 
                type="button" 
                @click="isCategoryModalOpen = false" 
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="categoryForm.processing"
                class="px-5 py-2.5 bg-[#1653a1] hover:bg-[#103f80] text-white font-black text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-1.5"
              >
                <Save class="w-3.5 h-3.5" />
                Simpan Kategori
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
