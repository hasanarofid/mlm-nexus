<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  Copy, 
  Wallet, 
  TrendingUp, 
  Users, 
  ArrowUpRight, 
  Check, 
  PiggyBank,
  CreditCard,
  Send,
  HelpCircle
} from '@lucide/vue';

const props = defineProps({
  referral_links: Object,
  wallet: Object,
  premi_info: Object
});

const copySuccessMsg = ref('');
const isPremiModalOpen = ref(false);

const premiForm = useForm({
  amount: 10000,
});

const submitPremi = () => {
  premiForm.post(route('admin.pay-premi'), {
    onSuccess: () => {
      isPremiModalOpen.value = false;
      premiForm.reset();
    }
  });
};

const copyToClipboard = (text, type) => {
  navigator.clipboard.writeText(text);
  copySuccessMsg.value = `Link Referral ${type} berhasil disalin!`;
  setTimeout(() => {
    copySuccessMsg.value = '';
  }, 3000);
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Dashboard Member Area - TALENTA52" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Copy Link Toast Alert Banner -->
      <div v-if="copySuccessMsg" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 rounded-xl text-xs font-semibold flex items-center gap-2 animate-bounce">
        <Check class="w-4 h-4 text-emerald-500" />
        <span>{{ copySuccessMsg }}</span>
      </div>

      <!-- 1. Link Referral Banner Card -->
      <div class="bg-gradient-to-r from-[#f0f7fb] to-[#e6f9f8] border border-[#04bdb2]/30 rounded-3xl p-5 md:p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-[#04bdb2]/10 text-[#009c94] rounded-2xl shrink-0 hidden sm:block">
            <span class="text-xl font-bold">🔗</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold text-[#1653a1] tracking-tight">Link Referral Anda</h3>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-[#04bdb2]/20 text-[#009c94] rounded-md">Referral Kemitraan</span>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Bagikan link ini untuk mendaftarkan mitra baru secara langsung ke jaringan Unilevel Matahari Anda.</p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button 
            @click="copyToClipboard(referral_links?.default || referral_links?.url, 'Referral')"
            class="px-4 py-2 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:opacity-95 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Link Referral</span>
          </button>
        </div>
      </div>

      <!-- 2. Main Saldo Breakdown Grid (TOTAL SALDO, AUTO SAVE, SALDO WD) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Total Saldo Card (Combined 50% AutoSave + 50% Saldo WD) -->
        <div class="bg-gradient-to-br from-[#0b1f3a] via-[#103f80] to-[#1653a1] text-white rounded-3xl p-6 relative overflow-hidden shadow-lg space-y-4 border border-[#04bdb2]/30 lg:col-span-1 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#a9fff7] flex items-center gap-1.5">
                <Wallet class="w-3.5 h-3.5 text-[#04bdb2]" />
                TOTAL SALDO MITRA
              </span>
            </div>

            <div class="mt-2">
              <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight">{{ formatRupiah(wallet?.total_saldo ?? 0) }}</h2>
            </div>
          </div>

          <div class="pt-3 border-t border-white/10 flex items-center justify-between gap-2">
            <div class="text-[11px]">
              <span class="text-slate-300 block font-medium">Status Wallet</span>
              <span class="font-bold text-[#a9fff7]">Terverifikasi</span>
            </div>
            <Link 
              :href="route('admin.withdrawals.index')" 
              class="px-3.5 py-1.5 bg-[#04bdb2] hover:bg-[#009c94] text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <span>Penarikan (WD)</span>
              <ArrowUpRight class="w-4 h-4" />
            </Link>
          </div>
        </div>

        <!-- 50% Auto Save Card -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
              <PiggyBank class="w-4 h-4 text-emerald-500" />
              AUTO SAVE (50%)
            </span>
            <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 text-emerald-600 rounded-md">Alokasi 50%</span>
          </div>

          <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ formatRupiah(wallet?.auto_save_saldo ?? 0) }}</h2>
            <p class="text-xs text-slate-500 mt-1 font-medium">Diakumulasikan dari 50% bonus rekrut mitra 10 generasi (Rp 3.500/mitra).</p>
          </div>

          <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500 font-medium">
            Tersimpan otomatis untuk Tabungan Masa Depan Mitra.
          </div>
        </div>

        <!-- 50% Saldo WD Card -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
              <CreditCard class="w-4 h-4 text-[#1653a1]" />
              SALDO WD (50%)
            </span>
            <span class="px-2 py-0.5 text-[9px] font-bold bg-blue-50 text-[#1653a1] rounded-md">Bisa Ditarik</span>
          </div>

          <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ formatRupiah(wallet?.saldo_wd ?? 0) }}</h2>
            <p class="text-xs text-slate-500 mt-1 font-medium">Dapat ditarik ke rekening bank (Min. WD Rp 50.000, admin Rp 10.000).</p>
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[11px] text-slate-500 font-medium">Siap ditarik kapan saja</span>
            <Link 
              :href="route('admin.withdrawals.index')" 
              class="text-xs font-extrabold text-[#1653a1] hover:underline flex items-center gap-1"
            >
              <span>Tarik Saldo</span>
              <ArrowUpRight class="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>

      </div>

      <!-- 3. Network Metrics & Monthly Premi Card -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Network Summary -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-[#1653a1]" />
              <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Ringkasan Jaringan Matahari</h3>
            </div>
            <Link :href="route('admin.pohon-jaringan')" class="text-xs font-bold text-[#1653a1] hover:underline flex items-center gap-1">
              <span>Lihat Tree Network</span>
              <ArrowUpRight class="w-3.5 h-3.5" />
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60">
              <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Mitra Direct (Gen 1)</p>
              <h4 class="text-2xl font-black text-[#1653a1] mt-1">{{ wallet?.direct_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60">
              <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Team (Gen 1-10)</p>
              <h4 class="text-2xl font-black text-[#009c94] mt-1">{{ wallet?.total_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60">
              <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Bonus Generasi</p>
              <h4 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(wallet?.bonus_generasi ?? 0) }}</h4>
            </div>
          </div>
        </div>

        <!-- Monthly Premi Action Box -->
        <div class="bg-gradient-to-br from-[#e6f9f8] to-[#f0f7fb] border border-[#04bdb2]/30 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center gap-2">
              <div class="p-2 bg-[#04bdb2]/20 text-[#009c94] rounded-xl">
                <CreditCard class="w-4 h-4" />
              </div>
              <h3 class="text-sm font-extrabold text-[#1653a1] tracking-tight">Bayar Premi Bulanan</h3>
            </div>
            <p class="text-xs text-slate-600 mt-2 font-medium">
              Setoran Premi bulanan (min. Rp 10.000) masuk <strong>100% Full</strong> ke Total Saldo mitra.
            </p>
          </div>

          <button 
            @click="isPremiModalOpen = true" 
            class="w-full py-3 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:opacity-95 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <Send class="w-4 h-4" />
            <span>Bayar Premi (Min. Rp 10.000)</span>
          </button>
        </div>

      </div>

    </div>

    <!-- Modal Form Premi Bulanan -->
    <div v-if="isPremiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
      <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-extrabold text-slate-900">Setor Premi Bulanan</h3>
          <button @click="isPremiModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <p class="text-xs text-slate-500 font-medium">
          Setoran premi bulanan akan langsung menambahkan nominal setoran 100% full ke Saldo WD Anda.
        </p>

        <form @submit.prevent="submitPremi" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Premi (Rp)</label>
            <input 
              v-model="premiForm.amount" 
              type="number" 
              min="10000" 
              step="5000" 
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-[#04bdb2] focus:outline-none" 
              placeholder="10000"
              required 
            />
            <span class="text-[10px] text-slate-400 mt-1 block">Minimal nominal pembayaran Rp 10.000</span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button 
              type="button" 
              @click="isPremiModalOpen = false" 
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="premiForm.processing"
              class="px-5 py-2 bg-gradient-to-r from-[#1653a1] to-[#04bdb2] hover:opacity-95 text-white text-xs font-bold rounded-xl shadow-md cursor-pointer disabled:opacity-50"
            >
              Confirm & Bayar 100% Full
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
