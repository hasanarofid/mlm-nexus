<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
  ChevronRight, 
  ArrowLeft, 
  ShoppingBag, 
  Sparkles, 
  CheckCircle2, 
  Phone, 
  ShieldCheck, 
  Gift, 
  Layers 
} from '@lucide/vue';

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  related_products: {
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

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val || 0);
};

const whatsappOrderUrl = () => {
  const phone = props.settings.whatsapp_number || '6280000000000';
  const text = encodeURIComponent(
    `Halo Admin Talenta52, saya ingin menanyakan / memesan produk katalog:\n*${props.product.name}* (${props.product.type.toUpperCase()})\nHarga: ${formatRupiah(props.product.price)}`
  );
  return `https://wa.me/${phone}?text=${text}`;
};
</script>

<template>
  <Head :title="`${product.name} - Katalog TALENTA52`">
    <meta name="description" :content="product.description || `Detail produk paket ${product.name} di TALENTA52`" />
  </Head>

  <div class="min-h-screen bg-[#f8fafc] text-slate-800 font-sans selection:bg-[#04bdb2] selection:text-white flex flex-col justify-between">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div class="h-9 w-auto flex items-center">
            <img src="/images/logo-nexus.png" alt="NEXUS Logo" class="h-8 object-contain" />
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
            :href="product.type === 'po' ? route('admin.purchase-order.index') : route('admin.repeat-order.index')" 
            class="inline-flex items-center px-4 py-2 bg-[#1653a1] hover:bg-[#0b1f3a] text-white text-xs font-extrabold rounded-xl shadow-md transition-all cursor-pointer"
          >
            Beli di Dashboard
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
              class="inline-flex items-center px-4 py-2 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black rounded-xl shadow-md transition-all cursor-pointer"
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
        <span class="text-slate-400">Katalog Produk</span>
        <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
        <span class="text-[#1653a1] font-bold truncate max-w-xs sm:max-w-md">{{ product.name }}</span>
      </nav>

      <!-- Product Main Grid Card -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 md:p-10 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        <!-- Left Image Column -->
        <div class="md:col-span-5 space-y-4">
          <div class="w-full aspect-square rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center p-6 relative group">
            <img 
              v-if="product.image" 
              :src="product.image" 
              :alt="product.name" 
              class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="flex flex-col items-center justify-center text-slate-400 gap-2">
              <ShoppingBag class="w-16 h-16 opacity-40" />
              <span class="text-xs font-bold uppercase">Katalog Produk</span>
            </div>

            <!-- Type badge -->
            <span 
              :class="product.type === 'ro' ? 'bg-[#5c3a21] text-white' : 'bg-[#1653a1] text-white'"
              class="absolute top-4 left-4 px-3 py-1 text-xs font-black uppercase rounded-full shadow-sm"
            >
              PAKET {{ product.type.toUpperCase() }}
            </span>

            <!-- Points badge -->
            <span class="absolute top-4 right-4 px-3 py-1 bg-amber-400 text-slate-950 text-xs font-black rounded-full shadow-sm flex items-center gap-1">
              <Sparkles class="w-3.5 h-3.5" />
              +{{ product.points || 1 }} Poin {{ product.type.toUpperCase() }}
            </span>
          </div>

          <div class="p-4 bg-[#f0f7fb] border border-[#04bdb2]/30 rounded-2xl text-xs space-y-2 text-slate-700">
            <div class="flex items-center gap-2 text-[#1653a1] font-extrabold text-[11px] uppercase tracking-wider">
              <ShieldCheck class="w-4 h-4" />
              Jaminan Kualitas & Keaslian Produk
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
              Produk resmi terdaftar untuk distribusi resmi jaringan kemitraan TALENTA52.
            </p>
          </div>
        </div>

        <!-- Right Specs & Actions Column -->
        <div class="md:col-span-7 space-y-6">
          <div class="space-y-2 border-b border-slate-100 pb-5">
            <span class="text-xs font-extrabold text-[#04bdb2] uppercase tracking-wider">
              Katalog Pilihan Talenta52
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
              {{ product.name }}
            </h1>
            <div class="flex items-baseline gap-3 pt-1">
              <span class="text-3xl font-black text-[#1653a1]">
                {{ formatRupiah(product.price) }}
              </span>
              <span class="text-xs font-semibold text-slate-500">
                / Isi {{ product.quantity || 1 }} Box/Botol
              </span>
            </div>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
              Deskripsi Produk:
            </h3>
            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line font-medium">
              {{ product.description || 'Produk herbal berkualitas tinggi pilihan TALENTA52 untuk menunjang kesehatan optimal dan program kemitraan berkelanjutan.' }}
            </p>
          </div>

          <!-- Benefits Card -->
          <div class="p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-3">
            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
              <Gift class="w-4 h-4 text-emerald-600" />
              Keuntungan Paket Kemitraan:
            </h4>
            <ul class="space-y-2 text-xs font-medium text-slate-700">
              <li class="flex items-start gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                <span>Akumulasi <strong>+{{ product.points || 1 }} Poin {{ product.type.toUpperCase() }}</strong> untuk klaim personal reward uang tunai hingga puluhan juta rupiah.</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                <span>Bonus Sponsor langsung & alokasi multi-tier generasi unilevel otomatis masuk ke saldo Anda.</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                <span>Bebas ongkir atau pengiriman langsung dari pusat sesuai ketentuan sistem.</span>
              </li>
            </ul>
          </div>

          <!-- Call to Actions -->
          <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <Link 
              v-if="user"
              :href="product.type === 'po' ? route('admin.purchase-order.index') : route('admin.repeat-order.index')"
              class="flex-1 py-3.5 px-6 bg-[#1653a1] hover:bg-[#103f80] text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-md text-center transition-all cursor-pointer"
            >
              Beli Voucher Produk di Dashboard
            </Link>
            <Link 
              v-else
              :href="route('register')"
              class="flex-1 py-3.5 px-6 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-md text-center transition-all cursor-pointer"
            >
              Daftar Jadi Mitra Untuk Membeli
            </Link>

            <a 
              :href="whatsappOrderUrl()"
              target="_blank"
              class="py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-2xl shadow-md flex items-center justify-center gap-2 transition-all"
            >
              <Phone class="w-4 h-4" />
              <span>Tanya Admin WA</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="related_products && related_products.length > 0" class="mt-12 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">
            Produk Pilihan Lainnya
          </h3>
          <span class="text-xs font-bold text-slate-500">Paket {{ product.type.toUpperCase() }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <div 
            v-for="rel in related_products" 
            :key="rel.id"
            class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm space-y-3 flex flex-col justify-between hover:shadow-md transition-all group"
          >
            <div class="space-y-3">
              <div class="w-full aspect-square rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden flex items-center justify-center p-3">
                <img v-if="rel.image" :src="rel.image" :alt="rel.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform" />
                <span v-else class="text-2xl">📦</span>
              </div>
              <div class="space-y-1">
                <h4 class="text-sm font-extrabold text-slate-900 truncate">
                  {{ rel.name }}
                </h4>
                <p class="text-xs font-black text-[#1653a1]">
                  {{ formatRupiah(rel.price) }}
                </p>
              </div>
            </div>

            <Link 
              :href="route('catalog.detail', rel.id)"
              class="w-full py-2 bg-slate-100 hover:bg-[#1653a1] hover:text-white text-slate-700 text-xs font-bold rounded-xl text-center transition-all block"
            >
              Lihat Detail
            </Link>
          </div>
        </div>
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
