<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    User, Mail, Phone, Calendar, Users, AlertCircle, 
    CreditCard, UploadCloud, Lock, Eye, EyeOff, ArrowRight, 
    Check, FileText, X, KeyRound
} from '@lucide/vue';

const props = defineProps({
    referral_code: {
        type: String,
        default: '',
    },
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const ktpPreview = ref(null);
const fileInput = ref(null);

const form = useForm({
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
    referral: props.referral_code || (typeof window !== 'undefined' ? (new URLSearchParams(window.location.search).get('sponsor') || new URLSearchParams(window.location.search).get('ref') || new URLSearchParams(window.location.search).get('referral') || new URLSearchParams(window.location.search).get('reff') || '') : ''),
    terms: true,
});

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

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran file maksimal adalah 10 MB');
        return;
    }

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

const submit = () => {
    form.post(route('register'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Form Pendaftaran | Nexus Community" />

        <div class="panel-heading">
            <span class="eyebrow">FORMULIR PENDAFTARAN</span>
            <h2>Daftar Akun Baru</h2>
            <p>Silakan lengkapi formulir pendaftaran anggota di bawah ini dengan data yang benar.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            
            <!-- SECTION 1: DATA PRIBADI -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
                <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
                    <User class="w-3.5 h-3.5" />
                    <span>1. Data Pribadi & Kontak</span>
                </div>

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <User class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Jawaban Anda"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <!-- Whatsapp -->
                <div class="form-group">
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">
                        Whatsapp <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Phone class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="phone"
                            type="tel"
                            v-model="form.phone"
                            required
                            placeholder="Jawaban Anda (Contoh: 081234567890)"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.phone" />
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                        Email <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Mail class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="email"
                            placeholder="Jawaban Anda (nama@email.com)"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- Dominan Tangan Kiri (Kidal) -->
                <div class="form-group pt-1">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Apakah Anda dominan tangan kiri (Kidal) ? <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label 
                            :class="[
                                'flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-200',
                                form.is_left_handed === 'Iya' 
                                    ? 'border-[#D4AF37] bg-[#D4AF37]/10 text-slate-900 font-bold shadow-sm' 
                                    : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'
                            ]"
                        >
                            <input
                                type="radio"
                                name="is_left_handed"
                                value="Iya"
                                v-model="form.is_left_handed"
                                class="w-4 h-4 text-[#D4AF37] focus:ring-[#D4AF37] accent-[#0F172A]"
                            />
                            <span class="text-xs">Iya</span>
                        </label>

                        <label 
                            :class="[
                                'flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-200',
                                form.is_left_handed === 'Tidak' 
                                    ? 'border-[#D4AF37] bg-[#D4AF37]/10 text-slate-900 font-bold shadow-sm' 
                                    : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'
                            ]"
                        >
                            <input
                                type="radio"
                                name="is_left_handed"
                                value="Tidak"
                                v-model="form.is_left_handed"
                                class="w-4 h-4 text-[#D4AF37] focus:ring-[#D4AF37] accent-[#0F172A]"
                            />
                            <span class="text-xs">Tidak</span>
                        </label>
                    </div>
                    <InputError class="mt-1" :message="form.errors.is_left_handed" />
                </div>
            </div>

            <!-- SECTION 2: DATA AHLI WARIS & KONTAK DARURAT -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
                <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
                    <Users class="w-3.5 h-3.5" />
                    <span>2. Data Ahli Waris & Kontak Darurat</span>
                </div>

                <!-- Nama Ahli Waris -->
                <div class="form-group">
                    <label for="beneficiary_name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Ahli Waris: <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <User class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="beneficiary_name"
                            type="text"
                            v-model="form.beneficiary_name"
                            required
                            placeholder="Jawaban Anda"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.beneficiary_name" />
                </div>

                <!-- Tanggal Lahir Ahli Waris -->
                <div class="form-group">
                    <label for="beneficiary_birth_date" class="block text-xs font-bold text-slate-700 mb-1">
                        Tanggal Lahir Ahli Waris: <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Calendar class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="beneficiary_birth_date"
                            type="date"
                            v-model="form.beneficiary_birth_date"
                            required
                            class="w-full text-sm bg-transparent"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.beneficiary_birth_date" />
                </div>

                <!-- Hubungan dengan Ahli Waris -->
                <div class="form-group">
                    <label for="beneficiary_relation" class="block text-xs font-bold text-slate-700 mb-1">
                        Hubungan dengan Ahli Waris: <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Users class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="beneficiary_relation"
                            type="text"
                            v-model="form.beneficiary_relation"
                            required
                            placeholder="Jawaban Anda (Contoh: Anak / Pasangan / Orang Tua / Saudara)"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.beneficiary_relation" />
                </div>

                <!-- Nomor Whatsapp Kontak Darurat -->
                <div class="form-group">
                    <label for="emergency_phone" class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor Whatsapp Kontak Darurat: <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <AlertCircle class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="emergency_phone"
                            type="tel"
                            v-model="form.emergency_phone"
                            required
                            placeholder="Jawaban Anda"
                            class="w-full text-sm"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors.emergency_phone" />
                </div>
            </div>

            <!-- SECTION 3: REKENING & DOKUMEN IDENTITAS -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
                <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
                    <CreditCard class="w-3.5 h-3.5" />
                    <span>3. Rekening Bonus & Verifikasi KTP</span>
                </div>

                <!-- Bank & Nomor Rekening -->
                <div class="form-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bank & Nomor Rekening (untuk bonus referral) <span class="text-rose-500">*</span>
                    </label>
                    
                    <div class="space-y-2">
                        <!-- Select Bank -->
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon">
                                <CreditCard class="w-4.5 h-4.5 text-slate-400" />
                            </span>
                            <select
                                v-model="form.bank_name"
                                required
                                class="w-full text-xs font-semibold text-slate-800"
                            >
                                <option v-for="b in bankOptions" :key="b" :value="b">{{ b }}</option>
                            </select>
                        </div>

                        <!-- No Rekening -->
                        <div class="auth-input-wrap">
                            <input
                                type="text"
                                v-model="form.bank_account_number"
                                required
                                placeholder="Nomor Rekening / No. E-Wallet"
                                class="w-full text-sm"
                            />
                        </div>

                        <!-- Atas Nama -->
                        <div class="auth-input-wrap">
                            <input
                                type="text"
                                v-model="form.bank_account_name"
                                placeholder="Nama Pemilik Rekening (opsional jika sama dengan nama pendaftar)"
                                class="w-full text-sm"
                            />
                        </div>
                    </div>
                    <InputError class="mt-1" :message="form.errors.bank_name || form.errors.bank_account_number" />
                </div>

                <!-- ID KTP Upload -->
                <div class="form-group pt-1">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700">
                            ID KTP <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Maks 10 MB (JPG, PNG, PDF)</span>
                    </div>

                    <div 
                        v-if="!form.ktp_image"
                        @click="$refs.fileInput.click()"
                        class="border-2 border-dashed border-slate-300 hover:border-[#D4AF37] rounded-xl p-4 text-center cursor-pointer transition-colors bg-white group flex flex-col items-center justify-center gap-2"
                    >
                        <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#D4AF37]/10 flex items-center justify-center text-slate-500 group-hover:text-[#D4AF37] transition-colors">
                            <UploadCloud class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-bold text-slate-700 group-hover:text-slate-900">
                            Tambahkan file
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Klik untuk memilih file foto KTP Anda
                        </div>
                    </div>

                    <!-- File Attached Preview -->
                    <div v-else class="p-3 bg-white border border-[#D4AF37]/40 rounded-xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div v-if="ktpPreview && ktpPreview !== 'document'" class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                                <img :src="ktpPreview" alt="KTP Preview" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <FileText class="w-5 h-5" />
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-slate-800 truncate max-w-[180px] sm:max-w-xs">
                                    {{ form.ktp_image.name }}
                                </p>
                                <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                                    <Check class="w-3 h-3" /> Siap diunggah ({{ (form.ktp_image.size / 1024 / 1024).toFixed(2) }} MB)
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="removeFile"
                            class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors"
                            title="Hapus file"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        @change="handleFileUpload"
                        class="hidden"
                    />
                    <InputError class="mt-1" :message="form.errors.ktp_image" />
                </div>
            </div>

            <!-- SECTION 4: KEAMANAN PASSWORD -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
                <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
                    <Lock class="w-3.5 h-3.5" />
                    <span>4. Keamanan Akun Login</span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Lock class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="w-full text-sm"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="auth-password-toggle flex items-center justify-center"
                        >
                            <Eye v-if="!showPassword" class="w-4.5 h-4.5" />
                            <EyeOff v-else class="w-4.5 h-4.5" />
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <!-- Konfirmasi Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                        Konfirmasi Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <Lock class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="password_confirmation"
                            :type="showPasswordConfirm ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi password Anda"
                            class="w-full text-sm"
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirm = !showPasswordConfirm"
                            class="auth-password-toggle flex items-center justify-center"
                        >
                            <Eye v-if="!showPasswordConfirm" class="w-4.5 h-4.5" />
                            <EyeOff v-else class="w-4.5 h-4.5" />
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- SECTION 5: KODE REFERRAL / SPONSOR -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-3.5">
                <div class="text-[11px] font-black tracking-wider text-[#D4AF37] uppercase flex items-center gap-1.5 border-b border-slate-200/60 pb-2">
                    <KeyRound class="w-3.5 h-3.5" />
                    <span>5. Kode Referral / Sponsor</span>
                </div>

                <!-- Input Kode Referral -->
                <div class="form-group">
                    <label for="referral" class="block text-xs font-bold text-slate-700 mb-1">
                        Kode Referral / Username Sponsor <span class="text-rose-500">*</span>
                    </label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon">
                            <KeyRound class="w-4.5 h-4.5 text-slate-400" />
                        </span>
                        <input
                            id="referral"
                            type="text"
                            v-model="form.referral"
                            required
                            placeholder="Masukkan kode referral / username sponsor"
                            class="w-full text-sm font-semibold text-slate-800"
                        />
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Wajib diisi dengan username atau kode referral sponsor yang mengundang Anda.</p>
                    <InputError class="mt-1" :message="form.errors.referral" />
                </div>
            </div>

            <!-- Terms -->
            <label class="flex items-start cursor-pointer text-xs text-slate-600 my-2 pt-1">
                <input
                    type="checkbox"
                    v-model="form.terms"
                    required
                    class="mt-0.5 rounded border-slate-300 text-[#D4AF37] focus:ring-[#D4AF37] accent-[#0F172A]"
                />
                <span class="ms-2 leading-tight">Saya menyatakan data yang saya isi adalah benar dan menyetujui syarat & ketentuan yang berlaku.</span>
            </label>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-white disabled:opacity-50 mt-4 py-3"
            >
                <span v-if="form.processing">Memproses Pendaftaran...</span>
                <span v-else>Daftar Sekarang</span>
                <ArrowRight class="w-4 h-4" />
            </button>
        </form>

        <div class="mt-5 text-center text-xs text-slate-500 font-medium">
            Sudah punya akun?
            <Link :href="route('login')" class="ms-1 font-bold text-[#B8922E] hover:text-[#0F172A] transition-colors">
                Masuk ke Akun
            </Link>
        </div>
    </GuestLayout>
</template>

