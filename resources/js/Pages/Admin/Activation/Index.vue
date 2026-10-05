<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import BirthDatePicker from '@/Components/BirthDatePicker.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  UserPlus, 
  Check, 
  AlertCircle,
  User,
  Mail,
  Phone,
  Calendar,
  Users,
  CreditCard,
  UploadCloud,
  Lock,
  Eye,
  EyeOff,
  FileText,
  X,
  KeyRound
} from '@lucide/vue';

const props = defineProps({
  is_admin: Boolean,
  current_user_name: String,
  users: Array,
  default_sponsor: String,
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const ktpPreview = ref(null);
const fileInput = ref(null);
const transferProofPreview = ref(null);
const transferProofFileInput = ref(null);

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

const form = useForm({
  name: '',
  phone: '',
  email: '',
  is_left_handed: 'Tidak',
  beneficiary_name: '',
  beneficiary_birth_date: '',
  beneficiary_relation: '',
  emergency_phone: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  ktp_image: null,
  transfer_proof: null,
  password: '',
  password_confirmation: '',
  sponsor_username: props.default_sponsor || 'admin',
});

const compressImage = (file, maxWidth = 1600, maxHeight = 1600, quality = 0.82) => {
  return new Promise((resolve) => {
    if (!file || !file.type.startsWith('image/')) {
      resolve(file);
      return;
    }

    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e) => {
      const img = new Image();
      img.src = e.target.result;
      img.onload = () => {
        let width = img.width;
        let height = img.height;

        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            width = maxHeight;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        canvas.toBlob(
          (blob) => {
            if (!blob) {
              resolve(file);
              return;
            }
            const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
              type: 'image/jpeg',
              lastModified: Date.now(),
            });
            resolve(compressedFile);
          },
          'image/jpeg',
          quality
        );
      };
      img.onerror = () => resolve(file);
    };
    reader.onerror = () => resolve(file);
  });
};

const handleFileUpload = async (e) => {
  const rawFile = e.target.files[0];
  if (!rawFile) return;

  if (rawFile.size > 15 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 15 MB');
    return;
  }

  const file = await compressImage(rawFile);
  form.ktp_image = file;
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
  form.ktp_image = null;
  ktpPreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const handleTransferProofUpload = async (e) => {
  const rawFile = e.target.files[0];
  if (!rawFile) return;

  if (rawFile.size > 15 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 15 MB');
    return;
  }

  const file = await compressImage(rawFile);
  form.transfer_proof = file;
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
  form.transfer_proof = null;
  transferProofPreview.value = null;
  if (transferProofFileInput.value) {
    transferProofFileInput.value.value = '';
  }
};

const submitForm = () => {
  if (!form.transfer_proof) {
    form.setError('transfer_proof', 'Bukti transfer pembayaran wajib diunggah.');
    return;
  }
  form.post(route('admin.activation.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      form.is_left_handed = 'Tidak';
      form.bank_name = 'Bank BRI';
      form.sponsor_username = props.default_sponsor || 'admin';
      ktpPreview.value = null;
      transferProofPreview.value = null;
    }
  });
};
</script>

<template>
  <Head title="Pendaftaran Member Baru - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6 max-w-4xl mx-auto">
      
      <!-- Main Card Container -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
          <div class="space-y-1">
            <div class="flex items-center gap-2.5">
              <div class="p-2.5 bg-[#D4AF37]/15 text-[#B8922E] rounded-2xl">
                <UserPlus class="w-5 h-5" />
              </div>
              <h2 class="text-lg font-black text-slate-900 tracking-tight">Form Pendaftaran Member Baru</h2>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-5 pt-1">
          
          <!-- SECTION 1: DATA PRIBADI -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 space-y-4">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <User class="w-3.5 h-3.5" />
              <span>1. Data Pribadi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Nama Lengkap -->
              <div class="form-group sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><User class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="form.name" required placeholder="Nama Lengkap Member" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.name" />
              </div>

              <!-- Whatsapp -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Whatsapp <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Phone class="w-4 h-4 text-slate-400" /></span>
                  <input type="tel" v-model="form.phone" required placeholder="Contoh: 081234567890" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.phone" />
              </div>

              <!-- Email -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Email <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Mail class="w-4 h-4 text-slate-400" /></span>
                  <input type="email" v-model="form.email" required placeholder="nama@email.com" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
              </div>

              <!-- Kidal -->
              <div class="form-group sm:col-span-2 pt-1">
                <label class="block text-xs font-bold text-slate-700 mb-2">
                  Apakah dominan tangan kiri (Kidal) ? <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <label :class="['flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all', form.is_left_handed === 'Iya' ? 'border-[#D4AF37] bg-[#D4AF37]/10 font-bold text-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700']">
                    <input type="radio" value="Iya" v-model="form.is_left_handed" class="w-4 h-4 accent-[#0F172A]" />
                    <span class="text-xs">Iya</span>
                  </label>
                  <label :class="['flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all', form.is_left_handed === 'Tidak' ? 'border-[#D4AF37] bg-[#D4AF37]/10 font-bold text-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700']">
                    <input type="radio" value="Tidak" v-model="form.is_left_handed" class="w-4 h-4 accent-[#0F172A]" />
                    <span class="text-xs">Tidak</span>
                  </label>
                </div>
                <InputError class="mt-1" :message="form.errors.is_left_handed" />
              </div>
            </div>
          </div>

          <!-- SECTION 2: DATA AHLI WARIS & KONTAK DARURAT -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 space-y-4">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <Users class="w-3.5 h-3.5" />
              <span>2. Data Ahli Waris & Kontak Darurat</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Nama Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Nama Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><User class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="form.beneficiary_name" required placeholder="Nama Lengkap Ahli Waris" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.beneficiary_name" />
              </div>

              <!-- Tanggal Lahir Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Tanggal Lahir Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <BirthDatePicker
                  v-model="form.beneficiary_birth_date"
                  :required="true"
                />
                <InputError class="mt-1" :message="form.errors.beneficiary_birth_date" />
              </div>

              <!-- Hubungan dengan Ahli Waris -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Hubungan dengan Ahli Waris <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Users class="w-4 h-4 text-slate-400" /></span>
                  <input type="text" v-model="form.beneficiary_relation" required placeholder="Contoh: Anak / Pasangan / Orang Tua" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.beneficiary_relation" />
              </div>

              <!-- Nomor Whatsapp Kontak Darurat -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Whatsapp Kontak Darurat <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><AlertCircle class="w-4 h-4 text-slate-400" /></span>
                  <input type="tel" v-model="form.emergency_phone" required placeholder="Nomor Kontak Darurat" class="w-full text-sm" />
                </div>
                <InputError class="mt-1" :message="form.errors.emergency_phone" />
              </div>
            </div>
          </div>

          <!-- SECTION 3: IDENTITAS -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 space-y-5">
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
                v-if="!form.ktp_image"
                @click="$refs.fileInput.click()"
                class="border-2 border-dashed border-slate-300 hover:border-[#D4AF37] rounded-xl p-5 text-center cursor-pointer transition-colors bg-white group flex flex-col items-center justify-center gap-2"
              >
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#D4AF37]/10 flex items-center justify-center text-slate-500 group-hover:text-[#D4AF37] transition-colors">
                  <UploadCloud class="w-5 h-5" />
                </div>
                <div class="text-xs font-bold text-slate-700 group-hover:text-slate-900">Tambahkan file KTP</div>
                <div class="text-[11px] text-slate-400">Klik untuk memilih file foto KTP member</div>
              </div>

              <!-- Preview KTP -->
              <div v-else class="p-3.5 bg-white border border-[#D4AF37]/40 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                  <div v-if="ktpPreview && ktpPreview !== 'document'" class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                    <img :src="ktpPreview" alt="KTP Preview" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <FileText class="w-5 h-5" />
                  </div>
                  <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate max-w-[220px]">{{ form.ktp_image.name }}</p>
                    <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                      <Check class="w-3 h-3" /> Siap diunggah ({{ (form.ktp_image.size / 1024 / 1024).toFixed(2) }} MB)
                    </p>
                  </div>
                </div>
                <button type="button" @click="removeFile" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleFileUpload" class="hidden" />
              <InputError class="mt-1" :message="form.errors.ktp_image" />
            </div>

            <!-- Bukti Transfer Upload -->
            <div class="form-group pt-3 border-t border-slate-200/60">
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-bold text-slate-700">
                  Bukti Transfer <span class="text-rose-500">*</span>
                </label>
                <span class="text-[10px] text-slate-400 font-medium">Maks 10 MB (JPG, PNG, PDF)</span>
              </div>

              <div class="mb-3 p-4 sm:p-5 bg-gradient-to-r from-[#fffbeb] via-[#faf6eb] to-[#fffbeb] border-2 border-[#D4AF37] rounded-2xl shadow-sm space-y-2.5">
                <div class="flex items-center justify-between gap-2 border-b border-[#D4AF37]/30 pb-2">
                  <span class="text-xs sm:text-base font-black text-slate-950 uppercase tracking-tight">Rekening Perusahaan:</span>
                  <span class="text-sm sm:text-lg font-black text-[#B8922E] font-mono whitespace-nowrap shrink-0">Rp 500.000</span>
                </div>
                <div class="text-sm sm:text-base text-slate-900 font-black flex flex-wrap items-center gap-x-2.5 gap-y-1.5 pt-0.5">
                  <span class="font-black text-slate-900">Bank BCA:</span>
                  <span class="font-mono font-black text-base sm:text-lg text-slate-950 px-2.5 py-1 bg-white border-2 border-[#D4AF37]/70 rounded-lg shadow-2xs tracking-wider">172-666-2020</span>
                  <span class="text-slate-700 font-bold text-xs sm:text-sm">a/n</span>
                  <strong class="font-black text-slate-950 uppercase tracking-wide text-sm sm:text-base">PT. NEXUS KOMUNITAS BERSAMA</strong>
                </div>
              </div>

              <div 
                v-if="!form.transfer_proof"
                @click="$refs.transferProofFileInput.click()"
                class="border-2 border-dashed border-slate-300 hover:border-[#D4AF37] rounded-xl p-5 text-center cursor-pointer transition-colors bg-white group flex flex-col items-center justify-center gap-2"
              >
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#D4AF37]/10 flex items-center justify-center text-slate-500 group-hover:text-[#D4AF37] transition-colors">
                  <CreditCard class="w-5 h-5" />
                </div>
                <div class="text-xs font-bold text-slate-700 group-hover:text-slate-900">Tambahkan Bukti Transfer</div>
                <div class="text-[11px] text-slate-400">Klik untuk memilih foto bukti transfer pembayaran</div>
              </div>

              <!-- Preview Bukti Transfer -->
              <div v-else class="p-3.5 bg-white border border-[#D4AF37]/40 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                  <div v-if="transferProofPreview && transferProofPreview !== 'document'" class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                    <img :src="transferProofPreview" alt="Transfer Proof Preview" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <CreditCard class="w-5 h-5" />
                  </div>
                  <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate max-w-[220px]">{{ form.transfer_proof.name }}</p>
                    <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                      <Check class="w-3 h-3" /> Bukti transfer siap diunggah ({{ (form.transfer_proof.size / 1024 / 1024).toFixed(2) }} MB)
                    </p>
                  </div>
                </div>
                <button type="button" @click="removeTransferProof" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <input ref="transferProofFileInput" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="handleTransferProofUpload" class="hidden" />
              <InputError class="mt-1" :message="form.errors.transfer_proof" />
            </div>
          </div>

          <!-- SECTION 4: KEAMANAN -->
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 space-y-4">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
              <Lock class="w-3.5 h-3.5" />
              <span>4. Keamanan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Password -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Password <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Lock class="w-4 h-4 text-slate-400" /></span>
                  <input :type="showPassword ? 'text' : 'password'" v-model="form.password" required placeholder="Minimal 8 karakter" class="w-full text-sm" />
                  <button type="button" @click="showPassword = !showPassword" class="auth-password-toggle flex items-center justify-center">
                    <Eye v-if="!showPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <InputError class="mt-1" :message="form.errors.password" />
              </div>

              <!-- Konfirmasi Password -->
              <div class="form-group">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Konfirmasi Password <span class="text-rose-500">*</span>
                </label>
                <div class="auth-input-wrap">
                  <span class="auth-input-icon"><Lock class="w-4 h-4 text-slate-400" /></span>
                  <input :type="showPasswordConfirm ? 'text' : 'password'" v-model="form.password_confirmation" required placeholder="Ulangi password" class="w-full text-sm" />
                  <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="auth-password-toggle flex items-center justify-center">
                    <Eye v-if="!showPasswordConfirm" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
              </div>
            </div>
          </div>

          <!-- SECTION 5: DIREFERENSI OLEH -->
          <div class="p-4 bg-[#faf6eb] border border-[#D4AF37]/40 rounded-2xl space-y-2">
            <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-[#D4AF37]/30 pb-2">
              <KeyRound class="w-3.5 h-3.5" />
              <span>Direferensi oleh:</span>
            </div>
            <div v-if="is_admin && users && users.length > 0">
              <select v-model="form.sponsor_username" class="w-full bg-white border border-[#D4AF37]/40 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#D4AF37]">
                <option v-for="u in users" :key="u.username" :value="u.username">{{ u.label }}</option>
              </select>
            </div>
            <div v-else class="flex items-center justify-between bg-white px-3.5 py-2.5 border border-[#D4AF37]/30 rounded-xl">
              <span class="text-xs font-extrabold text-[#0F172A]">@{{ form.sponsor_username }} <span v-if="current_user_name" class="font-bold text-slate-600">({{ current_user_name }})</span></span>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="auth-primary-btn w-full flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-950 py-3.5 rounded-2xl shadow-md transition-all cursor-pointer disabled:opacity-50"
            >
              <span v-if="form.processing">Memproses Pendaftaran...</span>
              <span v-else>Daftarkan Member Sekarang</span>
              <Check class="w-4 h-4 stroke-[3]" />
            </button>
          </div>

        </form>

      </div>

    </div>
  </AdminLayout>
</template>
