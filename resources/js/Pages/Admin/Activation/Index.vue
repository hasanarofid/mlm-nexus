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
  CreditCard
} from '@lucide/vue';

const props = defineProps({
  users: Array,
  default_sponsor: String,
});

const form = useForm({
  username: '',
  name: '',
  email: '',
  phone: '',
  nik: '',
  bank_name: 'Bank BRI',
  bank_account_number: '',
  bank_account_name: '',
  password: '',
  sponsor_username: props.default_sponsor || 'admin',
});

const submitForm = () => {
  form.post(route('admin.activation.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      form.bank_name = 'Bank BRI';
      form.sponsor_username = props.default_sponsor || 'admin';
    }
  });
};
</script>

<template>
  <Head title="Aktivasi & Pendaftaran Member Baru - TALENTA52" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Main Card Container -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-emerald-600" />
              <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Registrasi & Pendaftaran Mitra Baru</h2>
            </div>
            <p class="text-xs text-slate-500">Daftarkan anggota/mitra baru ke dalam jaringan Anda beserta data rekening bank untuk penyaluran pembayaran WD & bonus.</p>
          </div>
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

          <!-- 8. Rekening Bank Mitra Baru (Untuk Pencairan Saldo & Bonus) -->
          <div class="p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-2xl space-y-3">
            <div class="flex items-center justify-between border-b border-emerald-200/70 pb-2">
              <div class="flex items-center gap-2">
                <CreditCard class="w-4 h-4 text-emerald-700" />
                <h3 class="text-xs font-extrabold text-emerald-950 uppercase tracking-tight">Data Rekening Bank Mitra (Penerima WD / Bonus)</h3>
              </div>
              <span class="text-[9px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded">
                Untuk Pembayaran WD
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Bank Name -->
              <div class="space-y-1">
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">
                  NAMA BANK / E-WALLET <span class="text-rose-500">*</span>
                </label>
                <select 
                  v-model="form.bank_name"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 transition-all"
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
              <div class="space-y-1">
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">
                  NO REKENING <span class="text-rose-500">*</span>
                </label>
                <input 
                  v-model="form.bank_account_number"
                  type="text"
                  placeholder="cth: 1234567890"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500 transition-all"
                />
              </div>

              <!-- Nama Pemilik Rekening -->
              <div class="space-y-1">
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">
                  ATAS NAMA (A.N)
                </label>
                <input 
                  v-model="form.bank_account_name"
                  type="text"
                  :placeholder="form.name || 'Sesuai KTP Mitra'"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 transition-all"
                />
              </div>
            </div>
            <p class="text-[10px] text-emerald-800/80 font-medium">
              💡 Rekening ini digunakan admin untuk menyalurkan pembayaran pencairan saldo (WD) & bonus mitra sesuai nama lengkap dan NIK-nya.
            </p>
          </div>

          <!-- 9. Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black rounded-2xl text-xs flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all cursor-pointer disabled:opacity-50"
            >
              <UserPlus class="w-4 h-4 stroke-[2.5]" />
              <span>Daftarkan Mitra Sekarang</span>
            </button>
          </div>

        </form>

      </div>

    </div>
  </AdminLayout>
</template>
