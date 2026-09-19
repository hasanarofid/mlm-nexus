<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  UserPlus, 
  HelpCircle, 
  Check, 
  AlertCircle,
  User,
  Mail,
  Users,
  Copy,
  Building2,
  Upload,
  Trash2
} from '@lucide/vue';

const props = defineProps({
  banks: Array,
  registration_fee: Number,
  users: Array,
  default_sponsor: String,
});

const copySuccessMsg = ref('');
const transferProofPreview = ref(null);

const form = useForm({
  username: '',
  name: '',
  email: '',
  phone: '',
  nik: '',
  password: '',
  sponsor_username: props.default_sponsor || 'admin',
  transfer_proof: null,
});

const handleTransferProofChange = (e) => {
  if (e.target.files && e.target.files[0]) {
    const file = e.target.files[0];
    form.transfer_proof = file;
    if (file.type.startsWith('image/')) {
      transferProofPreview.value = URL.createObjectURL(file);
    } else {
      transferProofPreview.value = null;
    }
  }
};

const removeTransferProof = () => {
  form.transfer_proof = null;
  transferProofPreview.value = null;
};

const copyBankNumber = (accNo) => {
  navigator.clipboard.writeText(accNo);
  copySuccessMsg.value = `Nomor rekening ${accNo} berhasil disalin!`;
  setTimeout(() => {
    copySuccessMsg.value = '';
  }, 3000);
};

const submitForm = () => {
  form.post(route('admin.activation.store'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      removeTransferProof();
      form.sponsor_username = props.default_sponsor || 'admin';
    }
  });
};
</script>

<template>
  <Head title="Aktivasi & Pendaftaran Member Baru - TALENTA52" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Copy Link Toast Alert Banner -->
      <div v-if="copySuccessMsg" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 rounded-xl text-xs font-semibold flex items-center gap-2 animate-bounce">
        <Check class="w-4 h-4 text-emerald-500" />
        <span>{{ copySuccessMsg }}</span>
      </div>

      <!-- Main Card Container -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-emerald-600" />
              <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Registrasi & Pendaftaran Mitra Baru</h2>
            </div>
            <p class="text-xs text-slate-500">Daftarkan mitra baru ke jaringan Anda melalui transfer pembayaran ke rekening resmi yayasan.</p>
          </div>
        </div>

        <!-- 1. Informasi Rekening Bank Tujuan Transfer -->
        <div class="p-4 bg-gradient-to-br from-emerald-50/80 to-teal-50/80 border border-emerald-200/90 rounded-2xl space-y-3 shadow-2xs">
          <div class="flex items-center justify-between border-b border-emerald-200/70 pb-2.5">
            <div class="flex items-center gap-2">
              <Building2 class="w-4 h-4 text-emerald-700" />
              <h3 class="text-xs font-extrabold text-emerald-950 uppercase tracking-tight">Rekening Tujuan Transfer Pendaftaran</h3>
            </div>
            <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-emerald-600 text-white rounded-md shadow-2xs">
              Biaya Pendaftaran: Rp 100.000
            </span>
          </div>

          <div v-if="banks && banks.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div 
              v-for="(b, idx) in banks" 
              :key="idx"
              class="p-3 bg-white border border-emerald-100 rounded-xl flex items-center justify-between gap-3 shadow-2xs"
            >
              <div class="space-y-0.5 min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="text-[10px] font-black text-emerald-800 uppercase px-1.5 py-0.5 bg-emerald-100/70 rounded">
                    {{ b.bank_name }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-sm font-black text-slate-900 font-mono tracking-wide">
                    {{ b.bank_account_number || b.account_number }}
                  </span>
                  <span class="text-[11px] text-slate-500 font-medium truncate">
                    a.n {{ b.bank_account_name || b.account_name }}
                  </span>
                </div>
              </div>

              <button 
                type="button"
                @click="copyBankNumber(b.bank_account_number || b.account_number)"
                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-lg transition-colors shrink-0 flex items-center gap-1 cursor-pointer"
                title="Salin Nomor Rekening"
              >
                <Copy class="w-3 h-3" />
                <span>Salin</span>
              </button>
            </div>
          </div>

          <p class="text-[11px] text-emerald-800/90 font-medium leading-relaxed">
            💡 Silakan lakukan transfer biaya pendaftaran <strong>Rp 100.000</strong> ke nomor rekening resmi di atas, kemudian isi data mitra dan lampirkan bukti transfer pada formulir di bawah ini.
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6 pt-2">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- 1. Username Downline -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                USERNAME MITRA <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">@</span>
                <input 
                  v-model="form.username"
                  type="text"
                  required
                  placeholder="cth: budisantoso"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
                />
              </div>
              <p class="text-[10px] text-slate-400">Hanya huruf, angka, dan underscore. Otomatis menjadi lowercase.</p>
              <p v-if="form.errors.username" class="text-xs text-rose-500 font-medium">{{ form.errors.username }}</p>
            </div>

            <!-- 2. Nama Lengkap -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                NAMA LENGKAP <span class="text-rose-500">*</span>
              </label>
              <input 
                v-model="form.name"
                type="text"
                required
                placeholder="cth: Budi Santoso"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="form.errors.name" class="text-xs text-rose-500 font-medium">{{ form.errors.name }}</p>
            </div>

            <!-- 3. Alamat Email -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                ALAMAT EMAIL <span class="text-rose-500">*</span>
              </label>
              <input 
                v-model="form.email"
                type="email"
                required
                placeholder="cth: budi@gmail.com"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="form.errors.email" class="text-xs text-rose-500 font-medium">{{ form.errors.email }}</p>
            </div>

            <!-- 4. No WhatsApp / HP -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                NO HP / WHATSAPP
              </label>
              <input 
                v-model="form.phone"
                type="text"
                placeholder="cth: 081234567890"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="form.errors.phone" class="text-xs text-rose-500 font-medium">{{ form.errors.phone }}</p>
            </div>

            <!-- 5. NIK KTP (Opsional) -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                NIK (KTP - OPSIONAL)
              </label>
              <input 
                v-model="form.nik"
                type="text"
                maxlength="20"
                placeholder="cth: 3201234567890001"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-mono font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p v-if="form.errors.nik" class="text-xs text-rose-500 font-medium">{{ form.errors.nik }}</p>
            </div>

            <!-- 6. Password Awal -->
            <div class="space-y-1.5">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                PASSWORD AWAL
              </label>
              <input 
                v-model="form.password"
                type="text"
                placeholder="Default: password"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
              />
              <p class="text-[10px] text-slate-400">Jika dikosongkan, default password adalah 'password'.</p>
            </div>

            <!-- 7. Username Sponsor -->
            <div class="space-y-1.5 md:col-span-2">
              <div class="flex items-center gap-1">
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                  USERNAME SPONSOR LANGSUNG <span class="text-rose-500">*</span>
                </label>
                <HelpCircle class="w-3.5 h-3.5 text-slate-400 cursor-help" />
              </div>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">@</span>
                <input 
                  v-model="form.sponsor_username"
                  type="text"
                  required
                  placeholder="admin"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all"
                />
              </div>
              <p class="text-[10px] text-slate-400">Mitra baru akan otomatis terhubung di bawah sponsor ini.</p>
              <p v-if="form.errors.sponsor_username" class="text-xs text-rose-500 font-medium">{{ form.errors.sponsor_username }}</p>
            </div>

          </div>

          <!-- 8. Upload Bukti Transfer -->
          <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <Upload class="w-4 h-4 text-slate-600" />
                <span>UPLOAD BUKTI TRANSFER PEMBAYARAN</span>
              </label>
              <span v-if="transferProofPreview" class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                File Terpilih
              </span>
            </div>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3.5">
              <!-- Preview Thumbnail if Image -->
              <div 
                v-if="transferProofPreview" 
                class="w-24 h-20 rounded-xl bg-slate-200 border border-slate-300 overflow-hidden relative group shrink-0"
              >
                <img :src="transferProofPreview" alt="Bukti Transfer" class="w-full h-full object-cover" />
                <button 
                  type="button" 
                  @click="removeTransferProof"
                  class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-xs font-bold"
                >
                  <Trash2 class="w-5 h-5" />
                </button>
              </div>

              <div class="flex-1 w-full space-y-1">
                <input 
                  type="file" 
                  @change="handleTransferProofChange" 
                  accept="image/*,application/pdf"
                  class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer bg-white border border-slate-200 rounded-xl p-1.5"
                />
                <p class="text-[10px] text-slate-400 font-medium">Unggah foto struk/screenshot transfer (JPG, PNG, WEBP, atau PDF, maks. 5MB).</p>
              </div>
            </div>
            <p v-if="form.errors.transfer_proof" class="text-xs text-rose-500 font-medium">{{ form.errors.transfer_proof }}</p>
          </div>

          <!-- 9. Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black rounded-2xl text-xs flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all cursor-pointer disabled:opacity-50"
            >
              <UserPlus class="w-4 h-4 stroke-[2.5]" />
              <span>Daftarkan & Aktifkan Member Sekarang</span>
            </button>
          </div>

        </form>

      </div>

    </div>
  </AdminLayout>
</template>
