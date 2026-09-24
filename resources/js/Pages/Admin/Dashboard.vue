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
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const ktpPreview = ref(null);
const fileInput = ref(null);

const handleUpgradePrioritas = () => {
  const totalDownlines = props.wallet?.total_downlines ?? 0;
  if (totalDownlines < 1000) {
    isPriorityModalOpen.value = true;
  } else {
    alert('Selamat! Anda memenuhi syarat untuk upgrade ke Prioritas. Tim kami akan memproses pengajuan Anda.');
  }
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

      <!-- 3. Upgrade ke Prioritas Card Banner (Purple Luxury Theme) -->
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
              <h3 class="text-base sm:text-lg font-black text-white tracking-tight">Upgrade ke Prioritas</h3>
              <span class="px-2 py-0.5 text-[9px] font-black bg-amber-400/20 text-amber-300 border border-amber-400/40 rounded-md uppercase tracking-wider">Eksklusif</span>
            </div>
            <p class="text-xs sm:text-sm text-purple-100 font-medium">
              Dapatkan bonus lebih dan raih kesuksesan bersama Nexus Community
            </p>
          </div>
        </div>

        <div class="relative z-10 shrink-0">
          <button 
            type="button"
            @click="handleUpgradePrioritas"
            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-[#D4AF37] to-[#B8922E] hover:from-[#E5C07B] hover:to-[#D4AF37] active:scale-95 text-slate-950 text-xs font-black rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>Upgrade Sekarang</span>
            <Sparkles class="w-4 h-4 text-slate-950" />
          </button>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah Mitra Baru (Quick Add Mitra from Dashboard) -->
    <div v-if="isAddMitraModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in overflow-y-auto">
      <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl space-y-5 border border-slate-100 my-8 max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="p-2.5 bg-[#D4AF37]/15 text-[#B8922E] rounded-2xl">
              <UserPlus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Tambah Mitra Baru</h3>
              <p class="text-xs text-slate-500 font-medium">Registrasi langsung anggota/mitra ke jaringan Anda.</p>
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
              <span>1. Data Pribadi & Kontak</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Nama Lengkap -->
              <div class="form-group sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><User class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="addMitraForm.name" required placeholder="Nama Lengkap Mitra" class="w-full text-sm" />
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

          <!-- SECTION 3: REKENING & DOKUMEN IDENTITAS -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <CreditCard class="w-3.5 h-3.5" />
              <span>3. Rekening Bonus & Verifikasi KTP</span>
            </div>

            <!-- Bank & No Rekening -->
            <div class="form-group space-y-2">
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Bank & Nomor Rekening (untuk bonus referral) <span class="text-rose-500">*</span>
              </label>

              <!-- Select Bank -->
              <div class="auth-input-wrap">
                <span class="auth-input-icon"><CreditCard class="w-4 h-4 text-slate-400" /></span>
                <select v-model="addMitraForm.bank_name" required class="w-full text-xs font-semibold text-slate-800">
                  <option v-for="b in bankOptions" :key="b" :value="b">{{ b }}</option>
                </select>
              </div>

              <!-- No Rekening -->
              <div class="auth-input-wrap">
                <input type="text" v-model="addMitraForm.bank_account_number" required placeholder="Nomor Rekening / No. E-Wallet" class="w-full text-sm" />
              </div>

              <!-- Atas Nama -->
              <div class="auth-input-wrap">
                <input type="text" v-model="addMitraForm.bank_account_name" :placeholder="addMitraForm.name || 'Nama Pemilik Rekening (opsional jika sama)'" class="w-full text-sm" />
              </div>
              <p v-if="addMitraForm.errors.bank_name || addMitraForm.errors.bank_account_number" class="text-xs text-rose-500 font-medium mt-1">
                {{ addMitraForm.errors.bank_name || addMitraForm.errors.bank_account_number }}
              </p>
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
                <div class="text-[11px] text-slate-400">Klik untuk memilih file foto KTP mitra</div>
              </div>

              <!-- Preview -->
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
                <button type="button" @click="removeFile" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleFileUpload" class="hidden" />
              <p v-if="addMitraForm.errors.ktp_image" class="text-xs text-rose-500 font-medium mt-1">{{ addMitraForm.errors.ktp_image }}</p>
            </div>
          </div>

          <!-- SECTION 4: KEAMANAN AKUN LOGIN -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <Lock class="w-3.5 h-3.5" />
              <span>4. Keamanan Akun Login</span>
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

          <!-- SECTION 5: SPONSOR LANGSUNG -->
          <div class="p-4 bg-[#faf6eb] border border-[#D4AF37]/40 rounded-2xl space-y-2">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-[#D4AF37]/30 pb-2">
              <KeyRound class="w-3.5 h-3.5" />
              <span>5. Sponsor Langsung</span>
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
            <p class="text-[10px] text-slate-600 font-medium">Mitra baru akan otomatis terhubung di bawah sponsor langsung ini.</p>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="isAddMitraModalOpen = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="addMitraForm.processing" class="auth-primary-btn !w-auto !h-auto px-6 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl shadow-md cursor-pointer disabled:opacity-50 flex items-center gap-2">
              <span v-if="addMitraForm.processing">Mendaftarkan...</span>
              <span v-else>Daftarkan Mitra Sekarang</span>
              <Check class="w-4 h-4 stroke-[3]" />
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- Modal Warning: Upgrade Prioritas (Total Downline < 1000) -->
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
            Peringatan Upgrade Prioritas
          </h3>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
            Untuk upgrade ke prioritas, Anda harus memiliki 1000 mitra di team anda. Fitur ini akan terbuka otomatis jika level tim mitra Anda sudah mencapai 1000
          </p>
        </div>

        <!-- Progress Box -->
        <div class="p-4 bg-purple-50/70 border border-purple-100 rounded-2xl space-y-2 text-left">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-purple-900">Total Mitra Tim Anda:</span>
            <span class="font-black text-purple-700 font-mono text-sm">{{ wallet?.total_downlines ?? 0 }} / 1000 Mitra</span>
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
  </AdminLayout>
</template>
