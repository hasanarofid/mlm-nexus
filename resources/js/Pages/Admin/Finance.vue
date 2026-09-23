<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
  Wallet, 
  TrendingUp, 
  ArrowRightLeft, 
  CheckCircle2, 
  AlertCircle, 
  Send, 
  ShieldCheck, 
  CreditCard,
  Building2,
  FileText,
  Eye,
  X,
  Check,
  Ban
} from '@lucide/vue';

const props = defineProps({
  wallet: Object,
  transactions: Array,
  premi_requests: Array,
  is_admin: Boolean,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Modal state for Premi Detail & Bukti Transfer
const selectedPremi = ref(null);
const isModalOpen = ref(false);

const openDetailModal = (item) => {
  selectedPremi.value = item;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedPremi.value = null;
};

const approvePremi = (id) => {
  router.post(route('admin.finance.approve-premi', id), {}, {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  });
};

const rejectPremi = (id) => {
  router.post(route('admin.finance.reject-premi', id), {}, {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  });
};

// Form Cashout Bonus
const cashoutForm = useForm({});
const submitCashout = () => {
  cashoutForm.post(route('admin.finance.cashout'), {
    preserveScroll: true,
  });
};

// Form Generate / Create Saldo Wallet Admin & Member
const generateForm = useForm({
  username: '',
  amount: '',
});
const submitGenerateSaldo = () => {
  generateForm.post(route('admin.finance.generate-saldo'), {
    preserveScroll: true,
    onSuccess: () => generateForm.reset(),
  });
};

// Form Transfer Saldo ke Member
const transferForm = useForm({
  recipient_username: '',
  amount: '',
  security_pin: '123456',
});
const submitTransfer = () => {
  transferForm.post(route('admin.finance.transfer'), {
    preserveScroll: true,
    onSuccess: () => {
      transferForm.recipient_username = '';
      transferForm.amount = '';
    },
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
  <Head title="Keuangan & Mutasi Saldo - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Flash Alert Notifications -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
          <span>{{ flashSuccess }}</span>
        </div>
      </div>

      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
          <span>{{ flashError }}</span>
        </div>
      </div>

      <!-- Main Layout Grid (Left: Balance & Forms, Right: History & Premi Requests) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: Wallet Cards & Action Forms (6 Cols) -->
        <div class="lg:col-span-6 space-y-6">
          
          <!-- 1. SALDO E-WALLET AKTIF CARD (Dark Midnight Card matching Mockup) -->
          <div class="bg-[#0d131d] text-white rounded-3xl p-6 shadow-xl relative overflow-hidden border border-slate-800 flex items-center justify-between gap-4">
            <div class="space-y-2">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400 flex items-center gap-1.5">
                SALDO E-WALLET AKTIF
              </span>
              <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                {{ formatRupiah(wallet?.saldo ?? 0) }}
              </h2>
              <p class="text-[11px] text-slate-400 font-medium">
                Batas penarikan harian maksimum: {{ formatRupiah(wallet?.max_daily_withdrawal || 50000000) }}
              </p>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
              <Wallet class="w-7 h-7" />
            </div>
          </div>

          <!-- 2. TOTAL BONUS CAIR CARD (Forest Green Card matching Mockup) -->
          <div class="bg-[#064e3b] text-white rounded-3xl p-6 shadow-xl relative overflow-hidden border border-emerald-800/60 flex items-center justify-between gap-4">
            <div class="space-y-2">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-200">
                TOTAL BONUS CAIR
              </span>
              <h2 class="text-3xl font-black text-white tracking-tight">
                {{ formatRupiah(wallet?.total_bonus_cair || 0) }}
              </h2>
              <div class="pt-1">
                <button 
                  @click="submitCashout"
                  :disabled="cashoutForm.processing"
                  class="px-4 py-2 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] text-xs font-black rounded-xl shadow-md border border-[#D4AF37]/30 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                >
                  <span>Cairkan ke E-Wallet</span>
                </button>
              </div>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-white/10 text-emerald-200 flex items-center justify-center shrink-0">
              <TrendingUp class="w-7 h-7" />
            </div>
          </div>

          <!-- 3. GENERATE / CREATE SALDO WALLET CARD (Only visible if admin) -->
          <div v-if="is_admin" class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl shrink-0">
                <CreditCard class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">GENERATE / CREATE SALDO WALLET</h3>
                <p class="text-xs text-slate-500 mt-0.5">Isi atau tambahkan saldo wallet member / admin setelah konfirmasi transfer.</p>
              </div>
            </div>

            <form @submit.prevent="submitGenerateSaldo" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  TARGET USERNAME (KOSONGKAN JIKA UNTUK ADMIN)
                </label>
                <input 
                  v-model="generateForm.username"
                  type="text"
                  placeholder="cth: budisantoso"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  NOMINAL SALDO (RP)
                </label>
                <input 
                  v-model.number="generateForm.amount"
                  type="number"
                  required
                  min="1000"
                  step="1000"
                  placeholder="cth: 2600000"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:border-emerald-500"
                />
              </div>

              <button 
                type="submit"
                :disabled="generateForm.processing"
                class="w-full py-3.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] hover:opacity-95 text-slate-950 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <span>Generate Saldo Wallet</span>
              </button>
            </form>
          </div>

          <!-- 4. KIRIM SALDO KE MEMBER CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-3">
              <div class="p-2.5 bg-[#faf6eb] text-[#B8922E] rounded-xl shrink-0">
                <ArrowRightLeft class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">KIRIM SALDO KE MEMBER</h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                  Transfer saldo secara instan ke sesama member di jaringan tanpa biaya admin.
                </p>
              </div>
            </div>

            <form @submit.prevent="submitTransfer" class="space-y-3 pt-1">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  USERNAME PENERIMA
                </label>
                <input 
                  v-model="transferForm.recipient_username"
                  type="text"
                  required
                  placeholder="@ cth: siti"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#D4AF37]"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  JUMLAH SALDO (RP)
                </label>
                <input 
                  v-model="transferForm.amount"
                  type="number"
                  required
                  min="1000"
                  step="1000"
                  placeholder="cth: 50000"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#D4AF37]"
                />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  PIN KEAMANAN AKUN
                </label>
                <input 
                  v-model="transferForm.security_pin"
                  type="password"
                  required
                  placeholder="default: 123456 / 111111"
                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#D4AF37]"
                />
              </div>

              <button 
                type="submit"
                :disabled="transferForm.processing"
                class="w-full py-3.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] hover:opacity-95 text-slate-950 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Send class="w-4 h-4 stroke-[2.5]" />
                <span>Kirim Saldo Sekarang</span>
              </button>
            </form>
          </div>

        </div>

        <!-- RIGHT COLUMN: Financial History & Premi Requests (6 Cols) -->
        <div class="lg:col-span-6 space-y-6">
          
          <!-- 5. PERMINTAAN TOPUP PREMI & BUKTI TRANSFER CARD (Matching Video Demo) -->
          <div v-if="premi_requests && premi_requests.length > 0" class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <FileText class="w-4 h-4 text-emerald-600" />
                KONFIRMASI PEMBAYARAN PREMI BULANAN
              </h3>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-amber-100 text-amber-800 rounded-full font-mono">
                {{ premi_requests.filter(r => r.status === 'pending').length }} Pending
              </span>
            </div>

            <div class="space-y-3">
              <div 
                v-for="item in premi_requests" 
                :key="item.id"
                class="p-4 bg-slate-50/80 border border-slate-100 rounded-2xl flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors"
              >
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span 
                      :class="[
                        item.status === 'approved' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 
                        item.status === 'rejected' ? 'bg-rose-100 text-rose-800 border-rose-300' : 
                        'bg-amber-100 text-amber-800 border-amber-300',
                        'px-2 py-0.5 text-[9px] font-extrabold rounded border uppercase tracking-wider'
                      ]"
                    >
                      TOPUP {{ item.status === 'approved' ? 'SUCCESS' : item.status === 'rejected' ? 'REJECTED' : 'PENDING' }}
                    </span>
                    <span class="text-xs font-bold text-slate-800">Topup Saldo Premi</span>
                  </div>
                  <p class="text-[10px] text-slate-400 font-medium">
                    {{ item.created_at }} &bull; <span class="text-slate-600 font-semibold">{{ item.user_name }}</span> ({{ item.user_username }})
                  </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                  <span class="font-black text-slate-900 text-xs font-mono">{{ item.formatted_amount }}</span>
                  <button 
                    @click="openDetailModal(item)"
                    class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 cursor-pointer"
                  >
                    <Eye class="w-3.5 h-3.5 text-slate-500" />
                    <span>Lihat Detail</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 6. RIWAYAT KEUANGAN & PENCAIRAN BONUS CARD -->
          <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">
                RIWAYAT KEUANGAN & PENCAIRAN BONUS
              </h3>
              <span class="px-2.5 py-1 text-[10px] font-extrabold bg-slate-100 text-slate-600 rounded-full">
                {{ transactions.length }} Catatan
              </span>
            </div>

            <!-- Mutation History List -->
            <div class="space-y-3">
              <div 
                v-for="item in transactions" 
                :key="item.id" 
                class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <span 
                    :class="[
                      item.type === 'KELUAR' ? 'bg-rose-100 text-rose-600 border-rose-200' : 'bg-emerald-100 text-emerald-600 border-emerald-200',
                      'px-2 py-1 text-[9px] font-extrabold rounded-md border uppercase tracking-wider shrink-0'
                    ]"
                  >
                    {{ item.type }}
                  </span>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ item.description }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ item.created_at }}</p>
                  </div>
                </div>

                <div class="shrink-0 text-right">
                  <span 
                    :class="[
                      item.is_income ? 'text-emerald-600' : 'text-rose-600',
                      'font-black text-xs font-mono tracking-tight block'
                    ]"
                  >
                    {{ item.amount }}
                  </span>
                </div>
              </div>

              <div v-if="transactions.length === 0" class="py-12 text-center text-slate-400 text-xs italic">
                Belum ada riwayat transaksi keuangan.
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- DETAIL TOPUP / PREMI MODAL (Matching Video Frame 7) -->
      <div v-if="isModalOpen && selectedPremi" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in">
        <div class="bg-[#0f172a] text-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl border border-slate-700 animate-scale-up">
          
          <!-- Modal Header -->
          <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div>
              <h3 class="text-base font-extrabold text-white tracking-tight">Detail Topup Saldo</h3>
              <p class="text-[11px] text-slate-400 font-mono">ID: {{ selectedPremi.invoice_number }}</p>
            </div>
            <button @click="closeModal" class="p-2 text-slate-400 hover:text-white rounded-xl bg-slate-800/80 cursor-pointer">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-4 text-xs">
            <div class="bg-slate-800/50 rounded-2xl p-4 space-y-2 border border-slate-700/50">
              <div class="flex justify-between items-center">
                <span class="text-slate-400 font-medium uppercase text-[10px] tracking-wider">TANGGAL</span>
                <span class="font-semibold text-slate-200 font-mono">{{ selectedPremi.created_at }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-400 font-medium uppercase text-[10px] tracking-wider">MITRA / USER</span>
                <span class="font-bold text-white">{{ selectedPremi.user_name }} <span class="text-slate-400 font-mono font-normal">({{ selectedPremi.user_username }})</span></span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-400 font-medium uppercase text-[10px] tracking-wider">NOMINAL</span>
                <span class="font-black text-emerald-400 text-sm font-mono">{{ selectedPremi.formatted_amount }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-400 font-medium uppercase text-[10px] tracking-wider">STATUS</span>
                <span 
                  :class="[
                    selectedPremi.status === 'approved' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 
                    selectedPremi.status === 'rejected' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : 
                    'bg-amber-500/20 text-amber-300 border-amber-500/40',
                    'px-2.5 py-0.5 text-[9px] font-extrabold rounded border uppercase tracking-wider font-mono'
                  ]"
                >
                  {{ selectedPremi.status === 'approved' ? 'SUCCESS' : selectedPremi.status === 'rejected' ? 'REJECTED' : 'PENDING' }}
                </span>
              </div>
            </div>

            <!-- Bukti Transfer Image -->
            <div class="space-y-2">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">BUKTI TRANSFER:</span>
              <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 flex items-center justify-center">
                <img 
                  v-if="selectedPremi.proof_of_transfer" 
                  :src="selectedPremi.proof_of_transfer" 
                  alt="Bukti Transfer Mitra" 
                  class="max-h-72 object-contain rounded-xl shadow-md border border-slate-800"
                />
                <div v-else class="py-12 text-center text-slate-500 italic">
                  Belum ada foto bukti transfer yang diunggah mitra.
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer Actions -->
          <div class="p-6 bg-slate-900 border-t border-slate-800 flex items-center justify-between gap-3">
            <button 
              @click="closeModal" 
              class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer"
            >
              Tutup
            </button>

            <div v-if="is_admin && selectedPremi.status === 'pending'" class="flex items-center gap-2">
              <button 
                @click="rejectPremi(selectedPremi.id)" 
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
              >
                <Ban class="w-3.5 h-3.5" />
                <span>Tolak</span>
              </button>

              <button 
                @click="approvePremi(selectedPremi.id)" 
                class="px-5 py-2.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] hover:opacity-95 text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-md"
              >
                <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                <span>Setujui (Tambah Saldo)</span>
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </AdminLayout>
</template>
