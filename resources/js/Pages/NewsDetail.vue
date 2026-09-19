<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
  Calendar, 
  Clock, 
  Tag, 
  ChevronRight, 
  Share2, 
  ArrowLeft, 
  Layers, 
  Phone, 
  Sparkles, 
  CheckCircle2, 
  ShoppingBag,
  ExternalLink,
  Copy,
  Check
} from '@lucide/vue';

const props = defineProps({
  post: {
    type: Object,
    required: true
  },
  related_posts: {
    type: Array,
    default: () => []
  },
  featured_products: {
    type: Array,
    default: () => []
  },
  settings: {
    type: Object,
    default: () => ({})
  }
});

const pageData = usePage();
const user = pageData.props.auth?.user;

const copied = ref(false);

const copyLink = () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2500);
  }
};

const shareWhatsapp = () => {
  const text = encodeURIComponent(`${props.post.title}\n\nBaca selengkapnya di Talenta52:\n${window.location.href}`);
  window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
};

const shareTelegram = () => {
  const url = encodeURIComponent(window.location.href);
  const text = encodeURIComponent(props.post.title);
  window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val || 0);
};

// Simple reading time estimator
const readingTime = Math.max(1, Math.ceil((props.post.content || '').split(' ').length / 180));
</script>

<template>
  <Head :title="`${post.title} - TALENTA52`">
    <meta name="description" :content="post.content?.substring(0, 160)" />
  </Head>

  <div class="min-h-screen bg-[#f8fafc] text-slate-800 font-sans selection:bg-[#04bdb2] selection:text-white flex flex-col justify-between">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div class="h-9 w-auto flex items-center">
            <img src="/logo-x-seller.png" alt="TALENTA52 Logo" class="h-8 object-contain" />
          </div>
          <div class="flex flex-col">
            <span class="font-black text-lg tracking-tight text-[#0b1f3a] group-hover:text-[#1653a1] transition-colors">
              TALENTA52
            </span>
            <span class="text-[9px] font-extrabold uppercase tracking-widest text-[#04bdb2] -mt-1">
              Saling Bantu • Manfaat Bersama
            </span>
          </div>
        </Link>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <Link 
            href="/"
            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-[#1653a1] hover:bg-slate-100 rounded-xl transition-all"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            Beranda
          </Link>

          <Link 
            v-if="user" 
            :href="route('admin.dashboard')" 
            class="inline-flex items-center px-4 py-2 bg-[#1653a1] hover:bg-[#0b1f3a] text-white text-xs font-extrabold rounded-xl shadow-md shadow-[#1653a1]/20 transition-all cursor-pointer"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link 
              :href="route('login')" 
              class="px-4 py-2 text-xs font-bold text-slate-700 hover:text-[#1653a1] transition-colors"
            >
              Masuk
            </Link>
            <Link 
              :href="route('register')" 
              class="inline-flex items-center px-4 py-2 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black rounded-xl shadow-md shadow-[#04bdb2]/20 transition-all cursor-pointer"
            >
              Daftar Mitra
            </Link>
          </template>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-12 w-full">
      <!-- Breadcrumbs -->
      <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6 flex-wrap">
        <Link href="/" class="hover:text-[#1653a1] transition-colors">Beranda</Link>
        <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
        <span class="text-slate-400">Artikel & Berita</span>
        <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
        <span class="text-[#1653a1] font-bold truncate max-w-xs sm:max-w-md">{{ post.title }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Main Article Column -->
        <article class="lg:col-span-8 bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 md:p-10 shadow-sm space-y-6">
          <!-- Article Header -->
          <div class="space-y-4 border-b border-slate-100 pb-6">
            <div class="flex items-center gap-2.5 flex-wrap">
              <span class="px-3 py-1 bg-[#1653a1]/10 border border-[#1653a1]/20 text-[#1653a1] text-xs font-black rounded-full uppercase tracking-wider">
                {{ post.category?.name || 'Berita & Informasi' }}
              </span>
              <span v-if="post.status === 'draft'" class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md">
                Pratinjau Draft
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight leading-tight">
              {{ post.title }}
            </h1>

            <div class="flex items-center gap-4 text-xs font-semibold text-slate-500 flex-wrap">
              <div class="flex items-center gap-1.5">
                <Calendar class="w-4 h-4 text-slate-400" />
                <span>{{ formatDate(post.created_at) }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <Clock class="w-4 h-4 text-slate-400" />
                <span>{{ readingTime }} Menit Baca</span>
              </div>
              <div class="flex items-center gap-1.5 text-emerald-600 font-bold">
                <Sparkles class="w-3.5 h-3.5" />
                <span>Official Talenta52</span>
              </div>
            </div>
          </div>

          <!-- Featured Cover Image -->
          <div v-if="post.image_url" class="rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 max-h-[440px] flex items-center justify-center">
            <img 
              :src="post.image_url" 
              :alt="post.title" 
              class="w-full h-full object-cover max-h-[440px] hover:scale-101 transition-transform duration-300"
            />
          </div>

          <!-- Article Content Body -->
          <div class="article-prose text-slate-700 leading-relaxed text-sm sm:text-base space-y-4 pt-2">
            <!-- Render paragraphs cleanly -->
            <div 
              v-for="(paragraph, pIdx) in post.content.split('\n\n')" 
              :key="pIdx"
              class="leading-relaxed"
            >
              <!-- If paragraph starts with heading markdown -->
              <h3 v-if="paragraph.startsWith('### ')" class="text-lg font-black text-slate-900 pt-3 pb-1">
                {{ paragraph.replace('### ', '') }}
              </h3>
              <h2 v-else-if="paragraph.startsWith('## ')" class="text-xl font-black text-slate-900 pt-4 pb-1 border-b border-slate-100">
                {{ paragraph.replace('## ', '') }}
              </h2>
              <blockquote v-else-if="paragraph.startsWith('> ')" class="p-4 bg-[#f0f7fb] border-l-4 border-[#04bdb2] rounded-r-2xl text-slate-700 font-medium italic">
                {{ paragraph.replace('> ', '') }}
              </blockquote>
              <ul v-else-if="paragraph.includes('\n- ') || paragraph.startsWith('- ')" class="space-y-2 list-disc list-inside bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                <li v-for="(item, iIdx) in paragraph.split('\n').filter(l => l.trim().startsWith('-'))" :key="iIdx" class="text-sm font-medium text-slate-700">
                  {{ item.replace(/^-\s*/, '') }}
                </li>
              </ul>
              <p v-else class="text-slate-700 leading-relaxed whitespace-pre-line font-normal">
                {{ paragraph }}
              </p>
            </div>
          </div>

          <!-- Share Section -->
          <div class="border-t border-slate-100 pt-6 space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 block">
              Bagikan Informasi Ini:
            </span>
            <div class="flex items-center gap-2.5 flex-wrap">
              <button 
                @click="shareWhatsapp" 
                class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <Phone class="w-3.5 h-3.5" />
                WhatsApp
              </button>
              <button 
                @click="shareTelegram" 
                class="px-4 py-2 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <Share2 class="w-3.5 h-3.5" />
                Telegram
              </button>
              <button 
                @click="copyLink" 
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-600" />
                <Copy v-else class="w-3.5 h-3.5" />
                {{ copied ? 'Tautan Disalin!' : 'Salin Tautan' }}
              </button>
            </div>
          </div>
        </article>

        <!-- Right Sidebar -->
        <aside class="lg:col-span-4 space-y-6">
          <!-- CTA Card Talenta52 -->
          <div class="bg-gradient-to-br from-[#0b1f3a] via-[#103f80] to-[#1653a1] rounded-3xl p-6 text-white shadow-lg space-y-4 relative overflow-hidden">
            <div class="space-y-2 relative z-10">
              <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-[10px] font-black uppercase tracking-wider text-[#a9fff7]">
                Program Saling Bantu
              </span>
              <h3 class="text-xl font-black tracking-tight leading-snug">
                Mulai Sukses Finansial Bersama TALENTA52
              </h3>
              <p class="text-xs text-slate-200 leading-relaxed font-medium">
                Nikmati bagi hasil 10 Generasi, Auto Save, Saldo WD, Poin RO & Personal Reward PO hingga Rp 150.000.000.
              </p>
            </div>

            <div class="pt-2 relative z-10">
              <Link 
                :href="route('register')"
                class="w-full py-3 px-4 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <span>Daftar Jadi Mitra Sekarang</span>
                <ChevronRight class="w-4 h-4" />
              </Link>
            </div>
          </div>

          <!-- Related Posts Card -->
          <div v-if="related_posts && related_posts.length > 0" class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide border-b border-slate-100 pb-3 flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-[#1653a1]" />
              Artikel Terkait
            </h3>

            <div class="space-y-4 divide-y divide-slate-100">
              <div 
                v-for="rel in related_posts" 
                :key="rel.id" 
                class="pt-3 first:pt-0 group"
              >
                <Link :href="route('posts.detail', rel.slug)" class="block space-y-1">
                  <span class="text-[10px] font-extrabold text-[#04bdb2] uppercase tracking-wider">
                    {{ rel.category?.name || 'Berita' }}
                  </span>
                  <h4 class="text-xs font-bold text-slate-800 group-hover:text-[#1653a1] transition-colors line-clamp-2 leading-snug">
                    {{ rel.title }}
                  </h4>
                  <p class="text-[10px] text-slate-400 font-medium">
                    {{ formatDate(rel.created_at) }}
                  </p>
                </Link>
              </div>
            </div>
          </div>

          <!-- Featured Products (Katalog) Card -->
          <div v-if="featured_products && featured_products.length > 0" class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide border-b border-slate-100 pb-3 flex items-center gap-2">
              <ShoppingBag class="w-4 h-4 text-emerald-600" />
              Katalog Pilihan
            </h3>

            <div class="space-y-3">
              <div 
                v-for="prod in featured_products" 
                :key="prod.id"
                class="p-3 bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/60 rounded-2xl flex items-center gap-3 transition-colors"
              >
                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 p-1">
                  <img v-if="prod.image" :src="prod.image" :alt="prod.name" class="w-full h-full object-contain" />
                  <span v-else class="text-xs">📦</span>
                </div>
                <div class="flex-1 min-w-0">
                  <Link :href="route('catalog.detail', prod.id)" class="text-xs font-extrabold text-slate-900 hover:text-[#1653a1] truncate block">
                    {{ prod.name }}
                  </Link>
                  <p class="text-[11px] font-black text-[#1653a1]">
                    {{ formatRupiah(prod.price) }}
                  </p>
                </div>
                <span :class="prod.type === 'ro' ? 'bg-[#5c3a21] text-white' : 'bg-[#1653a1] text-white'" class="px-2 py-0.5 text-[9px] font-black uppercase rounded-md shrink-0">
                  {{ prod.type }}
                </span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-8 text-center text-xs text-slate-500 mt-12">
      <div class="max-w-7xl mx-auto px-4 space-y-2">
        <p class="font-bold text-slate-700">
          PT. TALENTA52 PUNYA KITA &copy; {{ new Date().getFullYear() }} — Saling Bantu, Manfaat Bersama.
        </p>
        <p class="text-[11px] text-slate-400">
          Domain Resmi: <a href="https://talenta52.com" class="text-[#1653a1] font-bold">https://talenta52.com</a>
        </p>
      </div>
    </footer>
  </div>
</template>
