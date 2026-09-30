<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  Copy, 
  Wallet, 
  TrendingUp, 
  Users, 
  ArrowUpRight, 
  Check, 
  CreditCard, 
  HelpCircle,
  UserPlus,
  KeyRound,
  ShieldCheck,
  User,
  Mail,
  Phone,
  Calendar,
  AlertCircle,
  UploadCloud,
  Lock,
  Eye,
  EyeOff,
  FileText,
  Crown,
  Sparkles,
  AlertTriangle,
  X
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
const isAddMitraModalOpen = ref(false);
const isPriorityModalOpen = ref(false);
const isUpgradeConfirmModalOpen = ref(false);
const isUpgrading = ref(false);
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const ktpPreview = ref(null);
const fileInput = ref(null);
const transferProofPreview = ref(null);
const transferProofFileInput = ref(null);

const handleUpgradePremier = () => {
  if (props.wallet?.is_premier) {
    alert('Akun Anda sudah berstatus PREMIER Member aktif!');
    return;
  }
  const totalDownlines = props.wallet?.total_downlines ?? 0;
  if (totalDownlines < 1000) {
    isPriorityModalOpen.value = true;
  } else {
    isUpgradeConfirmModalOpen.value = true;
  }
};

const executeUpgradePremier = () => {
  isUpgrading.value = true;
  router.post(route('admin.upgrade-premier'), {}, {
    preserveScroll: true,
    onSuccess: () => {
      isUpgradeConfirmModalOpen.value = false;
      isUpgrading.value = false;
    },
    onError: () => {
      isUpgrading.value = false;
    }
  });
};

const bankOptions = [
  'Bank BCA',
  'Bank BRI',
  'Bank BNI',
  'Bank Mandiri',
  'Bank BSI',
  'Bank CIMB Niaga',
  'Bank Permata',
  'Bank Danamon',
  'Bank Tabungan Negara (BTN)',
  'Bank Jago',
  'Seabank',
  'DANA (E-Wallet)',
  'OVO (E-Wallet)',
  'GoPay (E-Wallet)',
  'Lainnya',
];

const addMitraForm = useForm({
  name: '',
  phone: '',
  email: '',
  is_left_handed: 'Tidak',
  beneficiary_name: '',
  beneficiary_birth_date: '',
  beneficiary_relation: '',
  emergency_phone: '',
  bank_name: 'Bank BRI',
  bank_account_number: '',
  bank_account_name: '',
  ktp_image: null,
  transfer_proof: null,
  password: '',
  password_confirmation: '',
  sponsor_username: props.current_user_username || 'admin',
  source: 'dashboard',
});

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 10 MB');
    return;
  }

  addMitraForm.ktp_image = file;
  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (event) => {
      ktpPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    ktpPreview.value = 'document';
  }
};

const removeFile = () => {
  addMitraForm.ktp_image = null;
  ktpPreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const handleTransferProofUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 10 MB');
    return;
  }

  addMitraForm.transfer_proof = file;
  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (event) => {
      transferProofPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    transferProofPreview.value = 'document';
  }
};

const removeTransferProof = () => {
  addMitraForm.transfer_proof = null;
  transferProofPreview.value = null;
  if (transferProofFileInput.value) {
    transferProofFileInput.value.value = '';
  }
};

const submitAddMitra = () => {
  addMitraForm.post(route('admin.activation.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      isAddMitraModalOpen.value = false;
      addMitraForm.reset();
      addMitraForm.is_left_handed = 'Tidak';
      addMitraForm.bank_name = 'Bank BRI';
      addMitraForm.sponsor_username = props.current_user_username || 'admin';
      addMitraForm.source = 'dashboard';
      ktpPreview.value = null;
      transferProofPreview.value = null;
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

      <!-- Sponsor Block Notification Banner (>= 1000 Downlines Not Premier) -->
      <div v-if="wallet?.can_sponsor === false" class="p-4 bg-amber-500/10 border-2 border-amber-500/40 rounded-3xl text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-amber-500/20 text-amber-600 rounded-2xl shrink-0">
            <AlertTriangle class="w-6 h-6 stroke-[2.5]" />
          </div>
          <div>
            <h4 class="text-sm font-black text-slate-900 tracking-tight">Wajib Upgrade ke Premier Member</h4>
            <p class="text-xs text-slate-700 font-medium mt-0.5">{{ wallet?.sponsor_block_message || 'Total jaringan Anda telah mencapai 1.000 member. Wajib upgrade ke Premier agar dapat mendaftarkan member baru dan menerima bonus jaringan.' }}</p>
          </div>
        </div>
        <button 
          @click="handleUpgradePremier"
          class="shrink-0 px-4 py-2.5 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] text-slate-950 font-black text-xs rounded-xl shadow transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <Crown class="w-4 h-4" />
          <span>Upgrade Premier Sekarang</span>
        </button>
      </div>

      <!-- 1. Pendaftaran Member Banner Card -->
      <div class="bg-gradient-to-r from-[#fdfbf7] via-[#faf6eb] to-[#f7f3e8] border border-[#D4AF37]/40 rounded-3xl p-5 md:p-6 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="p-3 bg-[#D4AF37]/15 text-[#B8922E] rounded-2xl shrink-0 hidden sm:block">
            <span class="text-xl font-bold">🔗</span>
          </div>
          <div>
            <h3 class="text-sm font-extrabold text-[#0F172A] tracking-tight">Pendaftaran Member</h3>
            <p class="text-xs text-slate-600 mt-1 font-medium">Daftarkan member baru secara langsung dari dashboard.</p>
          </div>
        </div>

        <div class="flex items-center flex-wrap gap-2.5 shrink-0">
          <button
            @click="copyToClipboard(referral_links?.default || referral_links?.url, 'Referral')"
            class="px-4 py-2.5 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] text-slate-950 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
          >
            <Copy class="w-3.5 h-3.5" />
            <span>Copy Link Referral</span>
          </button>
          
          <button 
            @click="isAddMitraModalOpen = true"
            class="px-4 py-2.5 bg-[#0F172A] hover:bg-[#1E293B] text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-black rounded-xl transition-all flex items-center gap-2 cursor-pointer shadow-md hover:shadow-lg"
          >
            <UserPlus class="w-4 h-4 stroke-[2.5]" />
            <span>+ Tambah Member</span>
          </button>
        </div>
      </div>

      <!-- 2. Main Dashboard Cards Grid (TOTAL SALDO MEMBER & TEAM MEMBER) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Total Saldo Card -->
        <div class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] text-white rounded-3xl p-6 relative overflow-hidden shadow-xl space-y-4 border border-[#D4AF37]/40 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#D4AF37] flex items-center gap-1.5">
                <Wallet class="w-3.5 h-3.5 text-[#D4AF37]" />
                SALDO TERSEDIA SAAT INI
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
              <span>Penarikan</span>
              <ArrowUpRight class="w-4 h-4" />
            </Link>
          </div>
        </div>

        <!-- Network Summary Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-[#0F172A]" />
              <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Team Member</h3>
            </div>
            <Link :href="route('admin.pohon-jaringan')" class="text-xs font-bold text-[#B8922E] hover:text-[#0F172A] hover:underline flex items-center gap-1 transition-colors">
              <span>Rincian Team</span>
              <ArrowUpRight class="w-3.5 h-3.5" />
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div class="p-4 rounded-2xl border border-slate-100 bg-[#fdfbf7]">
              <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Member Direct (Gen 1)</p>
              <h4 class="text-2xl font-black text-[#0F172A] mt-1">{{ wallet?.direct_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-[#faf6eb]">
              <p class="text-[10px] font-extrabold text-[#B8922E] uppercase tracking-wider">Total Team (Gen 1-10)</p>
              <h4 class="text-2xl font-black text-[#B8922E] mt-1">{{ wallet?.total_downlines ?? 0 }} <span class="text-xs font-normal text-slate-500">Orang</span></h4>
            </div>
          </div>
        </div>

      </div>

      <!-- 3. Upgrade ke PREMIER Card Banner (Purple Luxury Theme) -->
      <div class="bg-gradient-to-r from-[#2a0845] via-[#4b126d] to-[#6b1187] border border-purple-400/40 rounded-3xl p-5 md:p-6 shadow-xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5 text-white">
        <!-- Ambient lighting decoration -->
        <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-purple-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute left-1/3 -top-12 w-36 h-36 bg-amber-400/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
          <div class="w-12 h-12 rounded-2xl bg-white/10 border border-purple-300/30 text-amber-300 flex items-center justify-center shrink-0 shadow-inner">
            <Crown class="w-6 h-6 text-amber-300" />
          </div>
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <h3 class="text-base sm:text-lg font-black text-white tracking-tight">
                {{ wallet?.is_premier ? 'Akun PREMIER Member' : 'Upgrade ke PREMIER' }}
              </h3>
              <span v-if="wallet?.is_premier" class="px-2 py-0.5 text-[9px] font-black bg-amber-400/30 text-amber-300 border border-amber-400/60 rounded-md uppercase tracking-wider">
                👑 PREMIER VIP
              </span>
              <span v-else class="px-2 py-0.5 text-[9px] font-black bg-amber-400/20 text-amber-300 border border-amber-400/40 rounded-md uppercase tracking-wider">
                {{ (wallet?.total_downlines ?? 0) >= 1000 ? 'Syarat Terpenuhi' : (wallet?.total_downlines ?? 0) + ' / 1000 Member' }}
              </span>
            </div>
            <p v-if="wallet?.is_premier" class="text-xs sm:text-sm text-purple-100 font-medium">
              Status Premier aktif hingga {{ wallet?.premier_expires_at || '1 Tahun' }}. Anda bebas mendaftarkan member baru dan menerima bonus penuh.
            </p>
          </div>
        </div>

        <div class="relative z-10 shrink-0">
          <button 
            type="button"
            @click="handleUpgradePremier"
            :class="wallet?.is_premier ? 'bg-white/20 text-purple-200 border border-white/20 cursor-default' : 'bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] active:scale-95 text-slate-950 shadow-lg cursor-pointer'"
            class="w-full sm:w-auto px-6 py-3 text-xs font-black rounded-xl transition-all flex items-center justify-center gap-2"
          >
            <span>{{ wallet?.is_premier ? 'Status Premier Aktif' : 'Upgrade Sekarang' }}</span>
            <Sparkles v-if="!wallet?.is_premier" class="w-4 h-4 text-slate-950" />
            <Check v-else class="w-4 h-4 text-amber-300" />
          </button>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah Member Baru (Quick Add Member from Dashboard) -->
    <div v-if="isAddMitraModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
      <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl space-y-5 border border-slate-100 my-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="p-2.5 bg-[#D4AF37]/15 text-[#B8922E] rounded-2xl">
              <UserPlus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Tambah Member Baru</h3>
              <p class="text-xs text-slate-500 font-medium">Registrasi langsung member ke jaringan Anda.</p>
            </div>
          </div>
          <button @click="isAddMitraModalOpen = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Add Mitra Form -->
        <form @submit.prevent="submitAddMitra" class="space-y-4">
          
          <!-- SECTION 1: DATA PRIBADI & KONTAK -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <User class="w-3.5 h-3.5" />
              <span>1. Data Pribadi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Nama Lengkap -->
              <div class="form-group sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><User class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="addMitraForm.name" required placeholder="Nama Lengkap Member" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.name" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.name }}</p>
              </div>

              <!-- Whatsapp -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Whatsapp <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Phone class="w-4 h-4 text-slate-400" /></span>
                  <input type="tel" v-model="addMitraForm.phone" required placeholder="Contoh: 081234567890" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.phone" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.phone }}</p>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Email <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Mail class="w-4 h-4 text-slate-400" /></span>
                  <input type="email" v-model="addMitraForm.email" required placeholder="nama@email.com" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.email" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.email }}</p>
              </div>

              <!-- Kidal -->
              <div class="form-group sm:col-span-2 pt-1">
                <label class="block text-xs font-bold text-slate-700 mb-2">
                  Apakah dominan tangan kiri (Kidal) ? <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <label :class="['flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all', addMitraForm.is_left_handed === 'Iya' ? 'border-[#D4AF37] bg-[#D4AF37]/10 font-bold text-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700']">
                    <input type="radio" value="Iya" v-model="addMitraForm.is_left_handed" class="w-4 h-4 accent-[#0F172A]" />
                    <span class="text-xs">Iya</span>
                  </label>
                  <label :class="['flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all', addMitraForm.is_left_handed === 'Tidak' ? 'border-[#D4AF37] bg-[#D4AF37]/10 font-bold text-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700']">
                    <input type="radio" value="Tidak" v-model="addMitraForm.is_left_handed" class="w-4 h-4 accent-[#0F172A]" />
                    <span class="text-xs">Tidak</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 2: DATA AHLI WARIS & KONTAK DARURAT -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <Users class="w-3.5 h-3.5" />
              <span>2. Data Ahli Waris & Kontak Darurat</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Nama Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Nama Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><User class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="addMitraForm.beneficiary_name" required placeholder="Nama Lengkap Ahli Waris" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.beneficiary_name" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.beneficiary_name }}</p>
              </div>

              <!-- Tanggal Lahir Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Tanggal Lahir Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Calendar class="w-4 h-4 text-slate-400" /></span>
                  <input type="date" v-model="addMitraForm.beneficiary_birth_date" required class="w-full text-sm bg-transparent" />
                </div>
                <p v-if="addMitraForm.errors.beneficiary_birth_date" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.beneficiary_birth_date }}</p>
              </div>

              <!-- Hubungan dengan Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Hubungan dengan Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Users class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="addMitraForm.beneficiary_relation" required placeholder="Contoh: Anak / Pasangan / Orang Tua" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.beneficiary_relation" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.beneficiary_relation }}</p>
              </div>

              <!-- Nomor Whatsapp Kontak Darurat -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Whatsapp Kontak Darurat <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><AlertCircle class="w-4 h-4 text-slate-400" /></span>
                  <input type="tel" v-model="addMitraForm.emergency_phone" required placeholder="Nomor Kontak Darurat" class="w-full text-sm" />
                </div>
                <p v-if="addMitraForm.errors.emergency_phone" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.emergency_phone }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 3: IDENTITAS -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-4">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <FileText class="w-3.5 h-3.5" />
              <span>3. Identitas</span>
            </div>

            <!-- ID KTP Upload -->
            <div class="form-group pt-1">
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-bold text-slate-700">ID KTP <span class="text-rose-500">*</span></label>
                <span class="text-[10px] text-slate-400 font-medium">Maks 10 MB (JPG, PNG, PDF)</span>
              </div>

              <div 
                v-if="!addMitraForm.ktp_image"
                @click="$refs.fileInput.click()"
                class="border-2 border-dashed border-slate-300 hover:border-[#D4AF37] rounded-xl p-4 text-center cursor-pointer transition-colors bg-white group flex flex-col items-center justify-center gap-2"
              >
                <div class="w-9 h-9 rounded-full bg-slate-100 group-hover:bg-[#D4AF37]/10 flex items-center justify-center text-slate-500 group-hover:text-[#D4AF37] transition-colors">
                  <UploadCloud class="w-4.5 h-4.5" />
                </div>
                <div class="text-xs font-bold text-slate-700 group-hover:text-slate-900">Tambahkan file KTP</div>
                <div class="text-[11px] text-slate-400">Klik untuk memilih file foto KTP member</div>
              </div>

              <!-- Preview KTP -->
              <div v-else class="p-3 bg-white border border-[#D4AF37]/40 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                  <div v-if="ktpPreview && ktpPreview !== 'document'" class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                    <img :src="ktpPreview" alt="KTP Preview" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <FileText class="w-5 h-5" />
                  </div>
                  <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate max-w-[180px]">{{ addMitraForm.ktp_image.name }}</p>
                    <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                      <Check class="w-3 h-3" /> Siap diunggah ({{ (addMitraForm.ktp_image.size / 1024 / 1024).toFixed(2) }} MB)
                    </p>
                  </div>
                </div>
                <button type="button" @click="removeFile" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleFileUpload" class="hidden" />
              <p v-if="addMitraForm.errors.ktp_image" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.ktp_image }}</p>
            </div>

            <!-- Bukti Transfer Upload -->
            <div class="form-group pt-2 border-t border-slate-200/60">
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-bold text-slate-700">Bukti Transfer</label>
                <span class="text-[10px] text-slate-400 font-medium">Maks 10 MB (JPG, PNG, PDF)</span>
              </div>

              <!-- Company Bank Details Box -->
              <div class="mb-3 p-4 bg-gradient-to-r from-[#fffbeb] via-[#faf6eb] to-[#fffbeb] border-2 border-[#D4AF37]/60 rounded-2xl shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs sm:text-sm font-black text-slate-950 uppercase tracking-tight">Rekening Perusahaan:</span>
                  <span class="text-sm sm:text-base font-black text-[#B8922E] font-mono">Rp 500.000</span>
                </div>
                <div class="text-xs sm:text-sm text-slate-900 font-bold flex flex-wrap items-center gap-x-2 gap-y-1">
                  <span class="font-extrabold text-slate-800">Bank BCA:</span>
                  <span class="font-mono font-black text-sm sm:text-base text-slate-950 px-2 py-0.5 bg-white border border-[#D4AF37]/50 rounded-md shadow-2xs tracking-wider">172-666-2020</span>
                  <span class="text-slate-700 font-semibold">a/n</span>
                  <strong class="font-black text-slate-950 uppercase tracking-wide">PT. NEXUS KOMUNITAS BERSAMA</strong>
                </div>
              </div>

              <div 
                v-if="!addMitraForm.transfer_proof"
                @click="$refs.transferProofFileInput.click()"
                class="border-2 border-dashed border-slate-300 hover:border-[#D4AF37] rounded-xl p-4 text-center cursor-pointer transition-colors bg-white group flex flex-col items-center justify-center gap-2"
              >
                <div class="w-9 h-9 rounded-full bg-slate-100 group-hover:bg-[#D4AF37]/10 flex items-center justify-center text-slate-500 group-hover:text-[#D4AF37] transition-colors">
                  <CreditCard class="w-4.5 h-4.5" />
                </div>
                <div class="text-xs font-bold text-slate-700 group-hover:text-slate-900">Tambahkan Bukti Transfer</div>
                <div class="text-[11px] text-slate-400">Klik untuk memilih foto bukti transfer pembayaran</div>
              </div>

              <!-- Preview Bukti Transfer -->
              <div v-else class="p-3 bg-white border border-[#D4AF37]/40 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                  <div v-if="transferProofPreview && transferProofPreview !== 'document'" class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                    <img :src="transferProofPreview" alt="Transfer Proof Preview" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <CreditCard class="w-5 h-5" />
                  </div>
                  <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate max-w-[180px]">{{ addMitraForm.transfer_proof.name }}</p>
                    <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                      <Check class="w-3 h-3" /> Bukti transfer siap diunggah ({{ (addMitraForm.transfer_proof.size / 1024 / 1024).toFixed(2) }} MB)
                    </p>
                  </div>
                </div>
                <button type="button" @click="removeTransferProof" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <input ref="transferProofFileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleTransferProofUpload" class="hidden" />
              <p v-if="addMitraForm.errors.transfer_proof" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.transfer_proof }}</p>
            </div>
          </div>

          <!-- SECTION 4: KEAMANAN -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <Lock class="w-3.5 h-3.5" />
              <span>4. Keamanan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Password -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Password <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Lock class="w-4 h-4 text-slate-400" /></span>
                  <input :type="showPassword ? 'text' : 'password'" v-model="addMitraForm.password" required placeholder="Minimal 8 karakter" class="w-full text-sm" />
                  <button type="button" @click="showPassword = !showPassword" class="auth-password-toggle flex items-center justify-center">
                    <Eye v-if="!showPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <p v-if="addMitraForm.errors.password" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.password }}</p>
              </div>

              <!-- Konfirmasi Password -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Konfirmasi Password <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Lock class="w-4 h-4 text-slate-400" /></span>
                  <input :type="showPasswordConfirm ? 'text' : 'password'" v-model="addMitraForm.password_confirmation" required placeholder="Ulangi password" class="w-full text-sm" />
                  <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="auth-password-toggle flex items-center justify-center">
                    <Eye v-if="!showPasswordConfirm" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <p v-if="addMitraForm.errors.password_confirmation" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.password_confirmation }}</p>
              </div>
            </div>
          </div>

          <!-- SECTION 5: DIREFERENSI OLEH -->
          <div class="p-4 bg-[#faf6eb] border border-[#D4AF37]/40 rounded-2xl space-y-2">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-[#D4AF37]/30 pb-2">
              <KeyRound class="w-3.5 h-3.5" />
              <span>Direferensi oleh:</span>
            </div>
            <div v-if="is_admin && all_sponsors && all_sponsors.length > 0">
              <select v-model="addMitraForm.sponsor_username" class="w-full bg-white border border-[#D4AF37]/40 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#D4AF37]">
                <option v-for="s in all_sponsors" :key="s.username" :value="s.username">{{ s.label }}</option>
              </select>
            </div>
            <div v-else class="flex items-center justify-between bg-white px-3.5 py-2.5 border border-[#D4AF37]/30 rounded-xl">
              <span class="text-xs font-extrabold text-[#0F172A]">@{{ addMitraForm.sponsor_username }}</span>
              <span class="text-[10px] font-bold text-[#B8922E] bg-[#faf6eb] px-2 py-0.5 rounded border border-[#D4AF37]/30">Sponsor Anda</span>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="isAddMitraModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="addMitraForm.processing" class="auth-primary-btn !w-auto !h-auto px-6 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl shadow-md cursor-pointer disabled:opacity-50 flex items-center gap-2">
              <span v-if="addMitraForm.processing">Mendaftarkan...</span>
              <span v-else>Daftarkan Member Sekarang</span>
              <Check class="w-4 h-4 stroke-[3]" />
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- Modal Warning: Upgrade PREMIER (Total Downline < 1000) -->
    <div v-if="isPriorityModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs animate-fade-in">
      <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl space-y-5 border border-slate-100 text-center relative overflow-hidden animate-scale-in">
        
        <!-- Close Button -->
        <button 
          @click="isPriorityModalOpen = false" 
          class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <!-- Warning Icon -->
        <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-500 shadow-xs">
          <AlertTriangle class="w-7 h-7 stroke-[2.5]" />
        </div>

        <!-- Text Content -->
        <div class="space-y-2">
          <h3 class="text-lg font-black text-slate-900 tracking-tight">
            Peringatan Upgrade PREMIER
          </h3>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
            Untuk upgrade ke PREMIER, Anda harus memiliki 1000 member di team anda. Fitur ini akan terbuka otomatis jika level tim member Anda sudah mencapai 1000
          </p>
        </div>

        <!-- Progress Box -->
        <div class="p-4 bg-purple-50/70 border border-purple-100 rounded-2xl space-y-2 text-left">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-purple-900">Total Member Tim Anda:</span>
            <span class="font-black text-purple-700 font-mono text-sm">{{ wallet?.total_downlines ?? 0 }} / 1000 Member</span>
          </div>
          <!-- Progress Bar -->
          <div class="w-full bg-purple-200/60 rounded-full h-2.5 overflow-hidden">
            <div 
              class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" 
              :style="{ width: Math.min(100, Math.round(((wallet?.total_downlines ?? 0) / 1000) * 100)) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Action Button -->
        <button 
          type="button"
          @click="isPriorityModalOpen = false" 
          class="w-full py-3 bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md cursor-pointer"
        >
          Tutup
        </button>

      </div>
    </div>

    <!-- Modal Confirmation: Upgrade PREMIER (Rp 5.000.000 via Wallet) -->
    <div v-if="isUpgradeConfirmModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs animate-fade-in">
      <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl space-y-5 border border-slate-100 text-center relative overflow-hidden animate-scale-in">
        
        <!-- Close Button -->
        <button 
          @click="isUpgradeConfirmModalOpen = false" 
          class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>

        <!-- Crown Icon -->
        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#D4AF37]/15 border border-[#D4AF37]/30 flex items-center justify-center text-[#B8922E] shadow-sm">
          <Crown class="w-8 h-8 stroke-[2.5]" />
        </div>

        <!-- Text Content -->
        <div class="space-y-2">
          <h3 class="text-lg font-black text-slate-900 tracking-tight">
            Konfirmasi Upgrade PREMIER
          </h3>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
            Selamat! Anda telah mencapai 1.000 member jaringan. Lanjutkan upgrade ke Premier untuk membuka hak pendaftaran tanpa batas dan bonus 10 generasi.
          </p>
        </div>

        <!-- Details Box -->
        <div class="p-4 bg-[#faf6eb] border border-[#D4AF37]/30 rounded-2xl space-y-3 text-left">
          <div class="flex items-center justify-between text-xs pb-2 border-b border-[#D4AF37]/20">
            <span class="text-slate-600 font-medium">Biaya Upgrade (1 Tahun):</span>
            <span class="font-black text-slate-900 font-mono text-sm">Rp 5.000.000</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-600 font-medium">Saldo Wallet Anda:</span>
            <span class="font-black font-mono text-sm" :class="(wallet?.saldo ?? 0) >= 5000000 ? 'text-emerald-600' : 'text-rose-600'">
              {{ formatRupiah(wallet?.saldo ?? 0) }}
            </span>
          </div>
        </div>

        <p v-if="(wallet?.saldo ?? 0) < 5000000" class="text-xs text-rose-500 font-semibold">
          Saldo wallet tidak mencukupi untuk upgrade. Silakan kumpulkan saldo atau hubungi admin.
        </p>

        <!-- Action Buttons -->
        <div class="flex gap-2">
          <button 
            type="button"
            @click="isUpgradeConfirmModalOpen = false" 
            class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button"
            @click="executeUpgradePremier" 
            :disabled="isUpgrading || (wallet?.saldo ?? 0) < 5000000"
            class="flex-1 py-3 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] disabled:opacity-50 text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <span v-if="isUpgrading">Memproses...</span>
            <span v-else>Bayar & Upgrade</span>
          </button>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>
