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
  HelpCircle,
  UserPlus,
  KeyRound,
  ShieldCheck,
  User,
  Upload,
  Image as ImageIcon,
  Trash2,
  Building2
} from '@lucide/vue';

const props = defineProps({
  is_admin: Boolean,
  current_user_username: String,
  vouchers: Array,
  banks: Array,
  registration_fee: Number,
  all_sponsors: Array,
  referral_links: Object,
  wallet: Object,
  premi_info: Object
});

const copySuccessMsg = ref('');
const isPremiModalOpen = ref(false);
const isAddMitraModalOpen = ref(false);

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

const addMitraForm = useForm({
  username: '',
  name: '',
  email: '',
  phone: '',
  nik: '',
  bank_name: 'Bank BRI',
  bank_account_number: '',
  bank_account_name: '',
  sponsor_username: props.current_user_username || 'admin',
  password: '',
  source: 'dashboard',
});

const submitAddMitra = () => {
  addMitraForm.post(route('admin.activation.store'), {
    preserveScroll: true,
    onSuccess: () => {
      isAddMitraModalOpen.value = false;
      addMitraForm.reset();
      addMitraForm.bank_name = 'Bank BRI';
      addMitraForm.sponsor_username = props.current_user_username || 'admin';
      addMitraForm.password = '';
      addMitraForm.source = 'dashboard';
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

const copyBankNumber = (accNo) => {
  navigator.clipboard.writeText(accNo);
  copySuccessMsg.value = `Nomor rekening ${accNo} berhasil disalin!`;
  setTimeout(() => {
    copySuccessMsg.value = '';
  }, 3000);
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Dashboard Member Area - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Copy Link Toast Alert Banner -->
      <div v-if="copySuccessMsg" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 rounded-xl text-xs font-semibold flex items-center gap-2 animate-bounce">
        <Check class="w-4 h-4 text-emerald-500" />
        <span>{{ copySuccessMsg }}</span>
      </div>

      <!-- 1. Link Referral & Quick Add Mitra Banner Card -->
      <div class="bg-gradient-to-r from-[#fdfbf7] via-[#faf6eb] to-[#f7f3e8] border border-[#D4AF37]/40 rounded-3xl p-5 md:p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-[#D4AF37]/15 text-[#B8922E] rounded-2xl shrink-0 hidden sm:block">
            <span class="text-xl font-bold">🔗</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold text-[#0F172A] tracking-tight">Komunitas Mitra</h3>
              <span class="px-2 py-0.5 text-[9px] font-bold bg-[#D4AF37]/20 text-[#B8922E] border border-[#D4AF37]/30 rounded-md">Pendaftaran Mitra</span>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">Daftarkan mitra baru secara langsung dari dashboard atau bagikan link referral Anda.</p>
          </div>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
          <button 
            @click="isAddMitraModalOpen = true"
            class="px-4 py-2.5 bg-[#0F172A] hover:bg-[#1E293B] text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-black rounded-xl transition-all flex items-center gap-2 cursor-pointer shadow-md hover:shadow-lg"
          >
            <UserPlus class="w-4 h-4 stroke-[2.5]" />
            <span>+ Tambah Mitra Baru</span>
          </button>

          <button 
            @click="copyToClipboard(referral_links?.default || referral_links?.url, 'Referral')"
            class="px-4 py-2.5 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] text-slate-950 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Link Referral</span>
          </button>
        </div>
      </div>

      <!-- 2. Main Dashboard Cards Grid (TOTAL SALDO MITRA & RINGKASAN JARINGAN) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Total Saldo Card -->
        <div class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] text-white rounded-3xl p-6 relative overflow-hidden shadow-xl space-y-4 border border-[#D4AF37]/40 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#D4AF37] flex items-center gap-1.5">
                <Wallet class="w-3.5 h-3.5 text-[#D4AF37]" />
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
              <span class="font-bold text-[#D4AF37]">Terverifikasi</span>
            </div>
            <Link 
              :href="route('admin.withdrawals.index')" 
              class="px-4 py-2 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] text-slate-950 text-xs font-black rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <span>Penarikan (WD)</span>
              <ArrowUpRight class="w-4 h-4" />
            </Link>
          </div>
        </div>

        <!-- Network Summary Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-[#0F172A]" />
              <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Ringkasan Mitra Saya</h3>
            </div>
            <Link :href="route('admin.pohon-jaringan')" class="text-xs font-bold text-[#B8922E] hover:text-[#0F172A] hover:underline flex items-center gap-1 transition-colors">
              <span>Lihat Tree Network</span>
              <ArrowUpRight class="w-3.5 h-3.5" />
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div class="p-4 rounded-2xl border border-slate-100 bg-[#fdfbf7]">
              <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Mitra Direct (Gen 1)</p>
              <h4 class="text-2xl font-black text-[#0F172A] mt-1">{{ wallet?.direct_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-[#faf6eb]">
              <p class="text-[10px] font-extrabold text-[#B8922E] uppercase tracking-wider">Total Team (Gen 1-10)</p>
              <h4 class="text-2xl font-black text-[#B8922E] mt-1">{{ wallet?.total_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Modal Form Tambah Mitra Baru (Quick Add Mitra from Dashboard) -->
    <div v-if="isAddMitraModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
      <div class="bg-white rounded-3xl p-6 md:p-7 max-w-lg w-full shadow-2xl space-y-5 border border-slate-100 my-8">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
              <UserPlus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Tambah Mitra Baru</h3>
              <p class="text-[11px] text-slate-500 font-medium">Registrasi langsung anggota/mitra ke jaringan Anda.</p>
            </div>
          </div>
          <button @click="isAddMitraModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
        </div>

        <!-- Add Mitra Form -->
        <form @submit.prevent="submitAddMitra" class="space-y-4">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            
            <!-- Username Mitra -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                USERNAME MITRA <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-xs">@</span>
                <input 
                  v-model="addMitraForm.username"
                  type="text"
                  required
                  placeholder="cth: andipratama"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-7 pr-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
                />
              </div>
              <p v-if="addMitraForm.errors.username" class="text-[10px] text-rose-500 font-bold mt-1">{{ addMitraForm.errors.username }}</p>
            </div>

            <!-- Nama Lengkap -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                NAMA LENGKAP <span class="text-rose-500">*</span>
              </label>
              <input 
                v-model="addMitraForm.name"
                type="text"
                required
                placeholder="cth: Andi Pratama"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="addMitraForm.errors.name" class="text-[10px] text-rose-500 font-bold mt-1">{{ addMitraForm.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                ALAMAT EMAIL <span class="text-rose-500">*</span>
              </label>
              <input 
                v-model="addMitraForm.email"
                type="email"
                required
                placeholder="cth: andi@gmail.com"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="addMitraForm.errors.email" class="text-[10px] text-rose-500 font-bold mt-1">{{ addMitraForm.errors.email }}</p>
            </div>

            <!-- No HP / WA -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                NO HP / WHATSAPP
              </label>
              <input 
                v-model="addMitraForm.phone"
                type="text"
                placeholder="cth: 081234567890"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
            </div>

            <!-- NIK (Opsional) -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                NIK (KTP - OPSIONAL)
              </label>
              <input 
                v-model="addMitraForm.nik"
                type="text"
                maxlength="20"
                placeholder="cth: 3201234567890001"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
            </div>

            <!-- Password Awal -->
            <div>
              <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                PASSWORD AWAL
              </label>
              <input 
                v-model="addMitraForm.password"
                type="text"
                placeholder="Default: password"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
            </div>

          </div>

          <!-- Sponsor Selection -->
          <div class="p-3.5 bg-[#faf6eb] border border-[#D4AF37]/40 rounded-2xl space-y-1.5">
            <label class="block text-[10px] font-extrabold text-[#0F172A] uppercase tracking-wider">
              SPONSOR LANGSUNG
            </label>
            <div v-if="is_admin && all_sponsors && all_sponsors.length > 0">
              <select 
                v-model="addMitraForm.sponsor_username"
                class="w-full bg-white border border-[#D4AF37]/40 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#D4AF37]"
              >
                <option 
                  v-for="s in all_sponsors" 
                  :key="s.username" 
                  :value="s.username"
                >
                  {{ s.label }}
                </option>
              </select>
            </div>
            <div v-else class="flex items-center justify-between bg-white px-3 py-2 border border-[#D4AF37]/30 rounded-xl">
              <span class="text-xs font-extrabold text-[#0F172A]">@{{ addMitraForm.sponsor_username }}</span>
              <span class="text-[10px] font-bold text-[#B8922E] bg-[#faf6eb] px-2 py-0.5 rounded border border-[#D4AF37]/30">Sponsor Anda</span>
            </div>
            <p class="text-[10px] text-slate-600 font-medium">Mitra baru akan otomatis terhubung di bawah sponsor langsung ini.</p>
          </div>

          <!-- Data Rekening Bank Mitra Baru -->
          <div class="p-3.5 bg-[#fdfbf7] border border-[#D4AF37]/40 rounded-2xl space-y-3">
            <div class="flex items-center justify-between border-b border-[#D4AF37]/30 pb-2">
              <div class="flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-[#B8922E]" />
                <h4 class="text-[11px] font-extrabold text-[#0F172A] uppercase tracking-tight">Data Rekening Bank Mitra (Penerima WD / Bonus)</h4>
              </div>
              <span class="text-[9px] font-bold text-[#B8922E] bg-[#faf6eb] px-2 py-0.5 rounded border border-[#D4AF37]/30">
                Untuk Pencairan
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <!-- Bank Name -->
              <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                  NAMA BANK / E-WALLET <span class="text-rose-500">*</span>
                </label>
                <select 
                  v-model="addMitraForm.bank_name"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-[#D4AF37] transition-all"
                >
                  <option value="Bank BRI">Bank BRI</option>
                  <option value="Bank Mandiri">Bank Mandiri</option>
                  <option value="Bank Central Asia (BCA)">BCA</option>
                  <option value="Bank Negara Indonesia (BNI)">BNI</option>
                  <option value="Bank Syariah Indonesia (BSI)">BSI</option>
                  <option value="CIMB Niaga">CIMB Niaga</option>
                  <option value="Bank Permata">Permata</option>
                  <option value="Bank Danamon">Danamon</option>
                  <option value="DANA">DANA (E-Wallet)</option>
                  <option value="OVO">OVO (E-Wallet)</option>
                  <option value="GoPay">GoPay (E-Wallet)</option>
                  <option value="Bank Lainnya">Bank Lainnya</option>
                </select>
              </div>

              <!-- Nomor Rekening -->
              <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                  NO REKENING <span class="text-rose-500">*</span>
                </label>
                <input 
                  v-model="addMitraForm.bank_account_number"
                  type="text"
                  placeholder="cth: 1234567890"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-[#D4AF37] transition-all"
                />
              </div>

              <!-- Nama Pemilik Rekening -->
              <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">
                  ATAS NAMA (A.N)
                </label>
                <input 
                  v-model="addMitraForm.bank_account_name"
                  type="text"
                  :placeholder="addMitraForm.name || 'Sesuai KTP Mitra'"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-[#D4AF37] transition-all"
                />
              </div>
            </div>
            <p class="text-[10px] text-slate-600 font-medium">
              💡 Rekening ini digunakan admin untuk menyalurkan pembayaran pencairan saldo (WD) & bonus mitra sesuai nama lengkap dan NIK-nya.
            </p>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button 
              type="button" 
              @click="isAddMitraModalOpen = false" 
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="addMitraForm.processing"
              class="px-6 py-2.5 bg-[#0F172A] hover:bg-[#1E293B] text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-black rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
            >
              <Check class="w-4 h-4 stroke-[3]" />
              <span>Daftarkan Mitra Sekarang</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </AdminLayout>
</template>
