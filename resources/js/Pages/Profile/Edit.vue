<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    Settings,
    Building2,
    UserCheck,
    CreditCard,
    Plus,
    Trash2,
    Check,
    CheckCircle2,
    AlertCircle,
    Image as ImageIcon,
    Upload,
    MapPin,
    Users,
    Shield,
    HeartHandshake,
} from "@lucide/vue";

const props = defineProps({
    is_admin: Boolean,
    user: Object,
    admin_user: Object,
    company_profile: Object,
    status: String,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Form for Member Profile (Foundation / Yayasan Complete Data)
const ktpPreview = ref(props.user?.ktp_image_url || null);

const memberForm = useForm({
    name: props.user?.name || "",
    username: props.user?.username || "",
    email: props.user?.email || "",
    phone: props.user?.phone || "",
    nik: props.user?.nik || "",
    ktp_image: null,
    gender: props.user?.gender || "Laki-laki",
    birth_place: props.user?.birth_place || "",
    birth_date: props.user?.birth_date || "",
    religion: props.user?.religion || "Islam",
    marital_status: props.user?.marital_status || "Menikah",
    last_education: props.user?.last_education || "SMA / SMK / Sederajat",
    occupation: props.user?.occupation || "",
    address: props.user?.address || "",
    province: props.user?.province || "",
    city: props.user?.city || "",
    district: props.user?.district || "",
    village: props.user?.village || "",
    postal_code: props.user?.postal_code || "",
    beneficiary_name: props.user?.beneficiary_name || "",
    beneficiary_relation: props.user?.beneficiary_relation || "Pasangan (Suami/Istri)",
    beneficiary_phone: props.user?.beneficiary_phone || "",
    bank_name: props.user?.bank_name || "Bank BRI",
    bank_account_number: props.user?.bank_account_number || "",
    bank_account_name: props.user?.bank_account_name || props.user?.name || "",
    password: "",
});

const handleKtpChange = (e) => {
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        memberForm.ktp_image = file;
        ktpPreview.value = URL.createObjectURL(file);
    }
};

const religions = [
    "Islam",
    "Kristen Protestan",
    "Katolik",
    "Hindu",
    "Buddha",
    "Khonghucu",
    "Lainnya",
];

const maritalStatuses = [
    "Belum Menikah",
    "Menikah",
    "Cerai Hidup",
    "Cerai Mati",
];

const educationLevels = [
    "SD / Sederajat",
    "SMP / Sederajat",
    "SMA / SMK / Sederajat",
    "Diploma (D1 - D4)",
    "Sarjana (S1)",
    "Magister (S2)",
    "Doktoral (S3)",
    "Lainnya",
];

const beneficiaryRelations = [
    "Pasangan (Suami/Istri)",
    "Anak Kandung",
    "Orang Tua (Ayah/Ibu)",
    "Saudara Kandung",
    "Keluarga Lainnya",
];

const submitMemberProfile = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            memberForm.password = "";
        },
    };
    if (memberForm.ktp_image instanceof File) {
        options.forceFormData = true;
    }
    memberForm.post(route("profile.update"), options);
};

// Form for Corporate & Admin Profile
const form = useForm({
    company_name: props.company_profile?.name || "PT.Nexus Community Punya Kita",
    company_owner: props.company_profile?.owner || "PT.Nexus Community Punya Kita",
    company_copyright:
        props.company_profile?.copyright ||
        "PT.Nexus Community Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.",
    name: props.admin_user?.name || "President Director (Admin)",
    username: props.admin_user?.username || "admin",
    email: props.admin_user?.email || "admin@nexuscommunity.id",
    phone: props.admin_user?.phone || "081234567890",
    password: "",
    site_logo: null,
});

// Bank & Virtual Wallet Accounts list state
const banksList = ref(props.company_profile?.banks || []);

// Bank & Virtual Wallet add form state
const showAddBank = ref(false);
const accountType = ref("bank"); // 'bank' or 'ewallet'
const selectedProvider = ref("Bank BRI");
const customProvider = ref("");

const bankProviders = [
    "Bank BRI",
    "Bank Mandiri",
    "Bank Central Asia (BCA)",
    "Bank Negara Indonesia (BNI)",
    "Bank Syariah Indonesia (BSI)",
    "CIMB Niaga",
    "Bank Permata",
    "Bank Danamon",
    "Bank Lainnya",
];

const ewalletProviders = [
    "DANA (E-Wallet)",
    "OVO (E-Wallet)",
    "GoPay (E-Wallet)",
    "ShopeePay (E-Wallet)",
    "LinkAja (E-Wallet)",
    "QRIS / Virtual Account",
    "Virtual Wallet Lainnya",
];

const newBank = useForm({
    type: "bank",
    bank_name: "Bank BRI",
    account_number: "",
    account_name: "",
});

const handleLogoChange = (e) => {
    if (e.target.files.length > 0) {
        form.site_logo = e.target.files[0];
    }
};

const submitProfile = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.password = "";
        },
    };
    if (form.site_logo instanceof File) {
        options.forceFormData = true;
    }
    form.post(route("profile.update"), options);
};

const addBank = () => {
    let providerName = selectedProvider.value;
    if (
        selectedProvider.value.includes("Lainnya") &&
        customProvider.value.trim()
    ) {
        providerName = customProvider.value.trim();
    }

    if (!newBank.account_number || !newBank.account_name || !providerName)
        return;

    banksList.value.push({
        type: accountType.value,
        bank_name: providerName,
        account_number: newBank.account_number,
        account_name: newBank.account_name,
    });

    newBank.reset();
    customProvider.value = "";
    showAddBank.value = false;
    saveBanks();
};

const removeBank = (index) => {
    banksList.value.splice(index, 1);
    saveBanks();
};

const bankForm = useForm({ banks: [] });
const saveBanks = () => {
    bankForm.banks = banksList.value;
    bankForm.post(route("profile.update-banks"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head
        :title="
            is_admin
                ? 'Pengaturan Profile Instansi & Administrator - NEXUS COMMUNITY'
                : 'Pengaturan Profile Member - NEXUS COMMUNITY'
        "
    />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Flash Alert Notifications -->
            <div
                v-if="flashSuccess"
                class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in"
            >
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                    <span>{{ flashSuccess }}</span>
                </div>
            </div>

            <div
                v-if="flashError"
                class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm animate-fade-in"
            >
                <div class="flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
                    <span>{{ flashError }}</span>
                </div>
            </div>

            <!-- MEMBER PROFILE EDIT CARD (When is_admin is false) -->
            <div
                v-if="!is_admin"
                class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-8"
            >
                <!-- Header -->
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-6"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0 mt-0.5"
                        >
                            <UserCheck class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2
                                    class="text-lg md:text-xl font-black text-slate-900 tracking-tight"
                                >
                                    Data Diri Anggota NEXUS COMMUNITY & Rekening Bank
                                </h2>
                                <span
                                    class="px-2.5 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200 uppercase tracking-wider"
                                >
                                    Member NEXUS COMMUNITY
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-1">
                                Lengkapi identitas kependudukan, alamat domisili, data ahli waris, serta rekening bank untuk tertib administrasi NEXUS COMMUNITY & pencairan saldo (WD).
                            </p>
                        </div>
                    </div>

                    <!-- Notice Badge -->
                    <div
                        class="px-4 py-2.5 bg-amber-50 border border-amber-200/80 rounded-2xl text-[11px] text-amber-900 font-semibold flex items-center gap-2"
                    >
                        <Shield class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Data tersimpan aman & terlindungi untuk legalitas keanggotaan NEXUS COMMUNITY.</span>
                    </div>
                </div>

                <form @submit.prevent="submitMemberProfile" class="space-y-8">
                    <!-- SECTION 1: IDENTITAS KEPENDUDUKAN & PRIBADI -->
                    <div
                        class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 md:p-6 space-y-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-200/70 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <UserCheck class="w-4 h-4 text-emerald-600" />
                                <h3
                                    class="text-xs font-black text-slate-900 uppercase tracking-tight"
                                >
                                    1. INFORMASI IDENTITAS KEPENDUDUKAN & PRIBADI
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">
                                Sesuai KTP / KK
                            </span>
                        </div>

                        <!-- KTP PHOTO UPLOAD BOX -->
                        <div
                            class="p-4 bg-white border border-slate-200 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-2xs"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-32 h-20 rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shrink-0 relative group"
                                >
                                    <img
                                        v-if="ktpPreview"
                                        :src="ktpPreview"
                                        alt="Foto KTP Anggota"
                                        class="w-full h-full object-cover rounded-lg"
                                    />
                                    <div
                                        v-else
                                        class="flex flex-col items-center justify-center text-slate-400 text-center p-1"
                                    >
                                        <ImageIcon class="w-6 h-6 mb-1 text-slate-300" />
                                        <span class="text-[9px] font-bold">FOTO KTP</span>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-extrabold text-slate-900">
                                            Upload Foto KTP Asli
                                        </h4>
                                        <span
                                            v-if="ktpPreview"
                                            class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-800 rounded-md border border-emerald-200 flex items-center gap-1"
                                        >
                                            <Check class="w-3 h-3 text-emerald-600 stroke-[3]" /> KTP Terpasang
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 py-0.5 text-[9px] font-extrabold bg-amber-100 text-amber-800 rounded-md border border-amber-200"
                                        >
                                            Belum Diunggah
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-medium">
                                        Unggah foto KTP asli yang jelas & terbaca (Maks. 5MB: JPG, PNG, WEBP).
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="inline-flex items-center px-4 py-2 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] text-xs font-bold rounded-xl shadow-xs border border-[#D4AF37]/30 cursor-pointer transition-colors"
                                >
                                    <Upload class="w-3.5 h-3.5 mr-1.5" />
                                    <span>{{ ktpPreview ? 'Ganti Foto KTP' : 'Pilih Foto KTP' }}</span>
                                    <input
                                        type="file"
                                        @change="handleKtpChange"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>
                                <p
                                    v-if="memberForm.errors.ktp_image"
                                    class="text-[10px] text-rose-600 font-bold mt-1"
                                >
                                    {{ memberForm.errors.ktp_image }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                        >
                            <!-- NIK -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NIK (KTP - 16 DIGIT) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="memberForm.nik"
                                    type="text"
                                    maxlength="20"
                                    placeholder="cth: 3201234567890001"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-mono font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                                <p
                                    v-if="memberForm.errors.nik"
                                    class="text-[10px] text-rose-600 font-bold mt-1"
                                >
                                    {{ memberForm.errors.nik }}
                                </p>
                            </div>

                            <!-- NAMA LENGKAP -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NAMA LENGKAP (SESUAI KTP) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="memberForm.name"
                                    type="text"
                                    required
                                    placeholder="Masukkan nama lengkap Anda"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                                <p
                                    v-if="memberForm.errors.name"
                                    class="text-[10px] text-rose-600 font-bold mt-1"
                                >
                                    {{ memberForm.errors.name }}
                                </p>
                            </div>

                            <!-- USERNAME -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    USERNAME (ID LOGIN) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="memberForm.username"
                                    type="text"
                                    required
                                    placeholder="Username akun"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                                <p
                                    v-if="memberForm.errors.username"
                                    class="text-[10px] text-rose-600 font-bold mt-1"
                                >
                                    {{ memberForm.errors.username }}
                                </p>
                            </div>

                            <!-- EMAIL -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    ALAMAT EMAIL <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="memberForm.email"
                                    type="email"
                                    required
                                    placeholder="alamat.email@contoh.com"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                                <p
                                    v-if="memberForm.errors.email"
                                    class="text-[10px] text-rose-600 font-bold mt-1"
                                >
                                    {{ memberForm.errors.email }}
                                </p>
                            </div>

                            <!-- NO HP / WA -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NO HP / WHATSAPP AKTIF
                                </label>
                                <input
                                    v-model="memberForm.phone"
                                    type="text"
                                    placeholder="cth: 081234567890"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>

                            <!-- JENIS KELAMIN -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    JENIS KELAMIN
                                </label>
                                <select
                                    v-model="memberForm.gender"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>

                            <!-- TEMPAT LAHIR -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    TEMPAT LAHIR
                                </label>
                                <input
                                    v-model="memberForm.birth_place"
                                    type="text"
                                    placeholder="cth: Jakarta / Surabaya"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>

                            <!-- TANGGAL LAHIR -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    TANGGAL LAHIR
                                </label>
                                <input
                                    v-model="memberForm.birth_date"
                                    type="date"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>

                            <!-- AGAMA -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    AGAMA
                                </label>
                                <select
                                    v-model="memberForm.religion"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                    <option
                                        v-for="rel in religions"
                                        :key="rel"
                                        :value="rel"
                                    >
                                        {{ rel }}
                                    </option>
                                </select>
                            </div>

                            <!-- STATUS PERNIKAHAN -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    STATUS PERNIKAHAN
                                </label>
                                <select
                                    v-model="memberForm.marital_status"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                    <option
                                        v-for="ms in maritalStatuses"
                                        :key="ms"
                                        :value="ms"
                                    >
                                        {{ ms }}
                                    </option>
                                </select>
                            </div>

                            <!-- PENDIDIKAN TERAKHIR -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    PENDIDIKAN TERAKHIR
                                </label>
                                <select
                                    v-model="memberForm.last_education"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                    <option
                                        v-for="edu in educationLevels"
                                        :key="edu"
                                        :value="edu"
                                    >
                                        {{ edu }}
                                    </option>
                                </select>
                            </div>

                            <!-- PEKERJAAN -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    PEKERJAAN / PROFESI
                                </label>
                                <input
                                    v-model="memberForm.occupation"
                                    type="text"
                                    placeholder="cth: Wiraswasta / Karyawan / PNS"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: ALAMAT DOMISILI LENGKAP -->
                    <div
                        class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 md:p-6 space-y-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-200/70 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <MapPin class="w-4 h-4 text-emerald-600" />
                                <h3
                                    class="text-xs font-black text-slate-900 uppercase tracking-tight"
                                >
                                    2. ALAMAT DOMISILI LENGKAP (TEMPAT TINGGAL)
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">
                                Wilayah Domisili
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- ALAMAT JALAN / RT RW -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    ALAMAT LENGKAP (JALAN, NO. RUMAH, RT/RW, DUSUN/KOMPLEK)
                                </label>
                                <textarea
                                    v-model="memberForm.address"
                                    rows="2"
                                    placeholder="cth: Jl. Merdeka No. 45 RT 02 / RW 05, Kelurahan Mulyaharja"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                                ></textarea>
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                            >
                                <!-- PROVINSI -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                    >
                                        PROVINSI
                                    </label>
                                    <input
                                        v-model="memberForm.province"
                                        type="text"
                                        placeholder="cth: Jawa Barat"
                                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                    />
                                </div>

                                <!-- KOTA / KABUPATEN -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                    >
                                        KOTA / KABUPATEN
                                    </label>
                                    <input
                                        v-model="memberForm.city"
                                        type="text"
                                        placeholder="cth: Kota Bogor"
                                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                    />
                                </div>

                                <!-- KECAMATAN -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                    >
                                        KECAMATAN
                                    </label>
                                    <input
                                        v-model="memberForm.district"
                                        type="text"
                                        placeholder="cth: Bogor Selatan"
                                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                    />
                                </div>

                                <!-- KELURAHAN / DESA -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                    >
                                        KELURAHAN / DESA
                                    </label>
                                    <input
                                        v-model="memberForm.village"
                                        type="text"
                                        placeholder="cth: Mulyaharja"
                                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                    />
                                </div>

                                <!-- KODE POS -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                    >
                                        KODE POS
                                    </label>
                                    <input
                                        v-model="memberForm.postal_code"
                                        type="text"
                                        placeholder="cth: 16135"
                                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-mono font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: DATA AHLI WARIS / KONTAK DARURAT YAYASAN -->
                    <div
                        class="bg-indigo-50/40 border border-indigo-200/70 rounded-2xl p-5 md:p-6 space-y-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-indigo-200/70 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <Users class="w-4 h-4 text-indigo-600" />
                                <h3
                                    class="text-xs font-black text-indigo-950 uppercase tracking-tight"
                                >
                                    3. DATA AHLI WARIS & KONTAK DARURAT (NEXUS COMMUNITY)
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-indigo-500">
                                Penerima Manfaat / Santunan
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- NAMA AHLI WARIS -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NAMA LENGKAP AHLI WARIS
                                </label>
                                <input
                                    v-model="memberForm.beneficiary_name"
                                    type="text"
                                    placeholder="cth: Siti Aminah"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-indigo-500 transition-colors"
                                />
                            </div>

                            <!-- HUBUNGAN AHLI WARIS -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    HUBUNGAN KELUARGA
                                </label>
                                <select
                                    v-model="memberForm.beneficiary_relation"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-indigo-500 transition-colors"
                                >
                                    <option
                                        v-for="rel in beneficiaryRelations"
                                        :key="rel"
                                        :value="rel"
                                    >
                                        {{ rel }}
                                    </option>
                                </select>
                            </div>

                            <!-- NO HP AHLI WARIS -->
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NO HP / WA AHLI WARIS
                                </label>
                                <input
                                    v-model="memberForm.beneficiary_phone"
                                    type="text"
                                    placeholder="cth: 081987654321"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-indigo-500 transition-colors"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: REKENING BANK & VIRTUAL WALLET PENARIKAN SALDO -->
                    <div
                        class="bg-emerald-50/50 border border-emerald-200/80 rounded-2xl p-5 md:p-6 space-y-5"
                    >
                        <div
                            class="flex items-center justify-between border-b border-emerald-200/70 pb-3"
                        >
                            <div class="flex items-center gap-2">
                                <CreditCard class="w-4 h-4 text-emerald-600" />
                                <h3
                                    class="text-xs font-black text-emerald-950 uppercase tracking-tight"
                                >
                                    4. INFORMASI REKENING BANK & VIRTUAL WALLET (PENARIKAN SALDO / WD)
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600">
                                Rekening Penerima WD
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NAMA BANK / PROVIDER E-WALLET
                                </label>
                                <input
                                    v-model="memberForm.bank_name"
                                    type="text"
                                    placeholder="cth: Bank Mandiri / BRI / DANA"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    NOMOR REKENING / NO. HP E-WALLET
                                </label>
                                <input
                                    v-model="memberForm.bank_account_number"
                                    type="text"
                                    placeholder="cth: 1234567890"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-mono font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                                >
                                    ATAS NAMA (PEMILIK REKENING)
                                </label>
                                <input
                                    v-model="memberForm.bank_account_name"
                                    type="text"
                                    placeholder="cth: Nama Anda"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: UBAH PASSWORD LOGIN -->
                    <div
                        class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 md:p-6 space-y-4"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-200/70 pb-3"
                        >
                            <Settings class="w-4 h-4 text-slate-600" />
                            <h3
                                    class="text-xs font-black text-slate-900 uppercase tracking-tight"
                            >
                                5. UBAH PASSWORD LOGIN (OPSIONAL)
                            </h3>
                        </div>

                        <div class="max-w-md">
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"
                            >
                                PASSWORD BARU
                            </label>
                            <input
                                v-model="memberForm.password"
                                type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah password"
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500 transition-colors"
                            />
                            <p
                                v-if="memberForm.errors.password"
                                class="text-xs text-rose-600 font-bold mt-1"
                            >
                                {{ memberForm.errors.password }}
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Submit Button -->
                    <div class="flex items-center justify-between flex-wrap gap-4 pt-2">
                        <span class="text-xs text-slate-400 font-medium italic">
                            * Pastikan seluruh data diri Anda telah benar sebelum menekan tombol simpan.
                        </span>
                        <button
                            type="submit"
                            :disabled="memberForm.processing"
                            class="px-8 py-3.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] hover:opacity-95 active:scale-[0.99] text-slate-950 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <Check class="w-4 h-4 stroke-[3]" />
                            <span>Simpan Data Profil NEXUS COMMUNITY</span>
                        </button>
                    </div>
                </form>

                <!-- TABEL DATA DIRI & STATUS KEANGGOTAAN NEXUS COMMUNITY (TABLE SUMMARY) -->
                <div class="border-t border-slate-100 pt-8 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <HeartHandshake class="w-5 h-5 text-emerald-600" />
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                TABEL RINGKASAN DATA ANGGOTA NEXUS COMMUNITY TERDAFTAR
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-slate-400">
                            ID Member #{{ user?.id }}
                        </span>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-2xl bg-white shadow-2xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <tbody>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-500 w-1/4">Foto KTP</td>
                                    <td class="py-3 px-4 w-1/4">
                                        <div v-if="user?.ktp_image_url" class="flex items-center gap-2">
                                            <a :href="user.ktp_image_url" target="_blank" class="inline-block">
                                                <img :src="user.ktp_image_url" alt="KTP" class="w-16 h-10 object-cover rounded border border-slate-200 hover:scale-105 transition-transform" />
                                            </a>
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Terunggah</span>
                                        </div>
                                        <span v-else class="text-slate-400 italic">Belum diunggah</span>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-500 w-1/4">Nama Anggota</td>
                                    <td class="py-3 px-4 font-bold text-slate-900 w-1/4">{{ user?.name || '-' }}</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-bold text-slate-500">NIK (KTP)</td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ user?.nik || '-' }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-500">No. WhatsApp / HP</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ user?.phone || '-' }}</td>
                                </tr>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-500">Username & Email</td>
                                    <td class="py-3 px-4 text-slate-800">{{ user?.username }} / {{ user?.email }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-500">Jenis Kelamin</td>
                                    <td class="py-3 px-4 text-slate-800">{{ user?.gender || '-' }}</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-bold text-slate-500">Tempat & Tanggal Lahir</td>
                                    <td class="py-3 px-4 text-slate-800">
                                        {{ user?.birth_place ? user.birth_place + ', ' : '' }}{{ user?.birth_date || '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-500">Agama / Pernikahan</td>
                                    <td class="py-3 px-4 text-slate-800">{{ user?.religion || '-' }} / {{ user?.marital_status || '-' }}</td>
                                </tr>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-500">Pendidikan & Pekerjaan</td>
                                    <td class="py-3 px-4 text-slate-800" colspan="3">{{ user?.last_education || '-' }} - {{ user?.occupation || '-' }}</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-bold text-slate-500">Alamat Domisili</td>
                                    <td class="py-3 px-4 text-slate-800" colspan="3">
                                        {{ user?.address || '-' }}
                                        <span v-if="user?.village || user?.district || user?.city || user?.province || user?.postal_code" class="text-slate-500 text-[11px] block mt-0.5">
                                            Kel. {{ user?.village || '-' }}, Kec. {{ user?.district || '-' }}, {{ user?.city || '-' }}, {{ user?.province || '-' }} {{ user?.postal_code ? '(' + user.postal_code + ')' : '' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-bold text-indigo-900 bg-indigo-50/30">Ahli Waris / Hubungan</td>
                                    <td class="py-3 px-4 font-bold text-indigo-950 bg-indigo-50/30">
                                        {{ user?.beneficiary_name || '-' }} ({{ user?.beneficiary_relation || '-' }})
                                    </td>
                                    <td class="py-3 px-4 font-bold text-indigo-900 bg-indigo-50/30">Kontak Ahli Waris</td>
                                    <td class="py-3 px-4 font-bold text-indigo-950 bg-indigo-50/30">{{ user?.beneficiary_phone || '-' }}</td>
                                </tr>
                                <tr class="bg-emerald-50/30">
                                    <td class="py-3 px-4 font-bold text-emerald-900">Rekening Pencairan (WD)</td>
                                    <td class="py-3 px-4 font-bold text-emerald-950" colspan="3">
                                        {{ user?.bank_name || '-' }} - No. Rek: <span class="font-mono">{{ user?.bank_account_number || '-' }}</span> (a.n {{ user?.bank_account_name || '-' }})
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ADMIN CORPORATE PROFILE EDIT CARD (When is_admin is true) -->
            <div
                v-else
                class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6"
            >
                <!-- Header -->
                <div
                    class="flex items-start gap-3 border-b border-slate-100 pb-5"
                >
                    <div
                        class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0 mt-0.5"
                    >
                        <Settings class="w-6 h-6" />
                    </div>
                    <div>
                        <h2
                            class="text-lg md:text-xl font-black text-slate-900 tracking-tight"
                        >
                            Pengaturan Profile Instansi & Administrator Utama
                        </h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Kelola Identitas korporat perusahaan, nomor rekening
                            bank admin utama untuk transfer penarikan, serta
                            kredensial akun login admin.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitProfile" class="space-y-6">
                    <!-- SECTION 1: LOGOS & AVATARS (Matching Mockup Gray Card) -->
                    <div
                        class="bg-slate-50/80 border border-slate-100 rounded-2xl p-5 grid grid-cols-1 md:grid-cols-2 gap-6"
                    >
                        <!-- Company Logo -->
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold shrink-0 shadow-xs"
                            >
                                <img
                                    v-if="company_profile?.logo_url"
                                    :src="company_profile.logo_url"
                                    class="max-h-12 max-w-12 object-contain"
                                />
                                <span v-else>LOGO</span>
                            </div>
                            <div class="space-y-1.5">
                                <h4
                                    class="text-xs font-extrabold text-slate-900"
                                >
                                    Logo Perusahaan
                                </h4>
                                <p
                                    class="text-[10px] text-slate-400 font-medium"
                                >
                                    Tampil di header utama navigasi.
                                </p>
                                <label
                                    class="inline-flex items-center px-3 py-1.5 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] text-[11px] font-bold rounded-xl shadow-xs border border-[#D4AF37]/30 cursor-pointer transition-colors"
                                >
                                    <Upload class="w-3.5 h-3.5 mr-1.5" />
                                    <span>Pilih File Logo</span>
                                    <input
                                        type="file"
                                        @change="handleLogoChange"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>
                            </div>
                        </div>

                        <!-- Admin Profile Picture -->
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 rounded-full bg-[#faf6eb] text-[#B8922E] border-2 border-[#D4AF37]/40 flex items-center justify-center text-xl font-extrabold shrink-0 shadow-xs"
                            >
                                A
                            </div>
                            <div class="space-y-1.5">
                                <h4
                                    class="text-xs font-extrabold text-slate-900"
                                >
                                    Foto Profil Admin
                                </h4>
                                <p
                                    class="text-[10px] text-slate-400 font-medium"
                                >
                                    Foto avatar administrator utama.
                                </p>
                                <button
                                    type="button"
                                    class="px-3 py-1.5 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] text-[11px] font-bold rounded-xl shadow-xs border border-[#D4AF37]/30 cursor-pointer transition-colors inline-flex items-center"
                                >
                                    <span>Pilih Avatar</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: COMPANY IDENTITIES (Inputs Grid) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                            >
                                NAMA PERUSAHAAN / PLATFORM
                            </label>
                            <input
                                v-model="form.company_name"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                            >
                                NAMA PEMILIK / OWNER UTAMA
                            </label>
                            <input
                                v-model="form.company_owner"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                            >
                                TEKS KETERANGAN HAK CIPTA / FOOTER
                            </label>
                            <input
                                v-model="form.company_copyright"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-[#D4AF37]"
                            />
                        </div>
                    </div>

                    <!-- SECTION 3: COMPANY BANK ACCOUNTS & VIRTUAL WALLET -->
                    <div
                        class="bg-[#faf6eb]/50 border border-[#D4AF37]/30 rounded-2xl p-5 space-y-4"
                    >
                        <div
                            class="flex items-center justify-between flex-wrap gap-2"
                        >
                            <div class="flex items-center gap-2">
                                <CreditCard class="w-4 h-4 text-[#B8922E]" />
                                <h3
                                    class="text-xs font-black text-slate-900 uppercase tracking-tight"
                                >
                                    DAFTAR REKENING BANK & VIRTUAL WALLET /
                                    E-WALLET PERUSAHAAN (ADMIN)
                                </h3>
                            </div>

                            <button
                                type="button"
                                @click="showAddBank = !showAddBank"
                                class="px-3.5 py-1.5 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] text-[11px] font-bold rounded-xl shadow-xs border border-[#D4AF37]/30 transition-colors flex items-center gap-1.5 cursor-pointer"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Tambah Rekening / Virtual Wallet</span>
                            </button>
                        </div>

                        <!-- Add Bank / Virtual Wallet Form Dropdown -->
                        <div
                            v-if="showAddBank"
                            class="p-4 bg-white border border-[#D4AF37]/30 rounded-2xl space-y-4 shadow-sm"
                        >
                            <!-- Type Switcher -->
                            <div
                                class="flex items-center gap-2 border-b border-slate-100 pb-3"
                            >
                                <span
                                    class="text-xs font-bold text-slate-500 mr-2"
                                    >Pilih Jenis:</span
                                >
                                <button
                                    type="button"
                                    @click="
                                        accountType = 'bank';
                                        selectedProvider = bankProviders[0];
                                    "
                                    :class="[
                                        accountType === 'bank'
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                        'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
                                    ]"
                                >
                                    🏦 Rekening Bank
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        accountType = 'ewallet';
                                        selectedProvider = ewalletProviders[0];
                                    "
                                    :class="[
                                        accountType === 'ewallet'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                        'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
                                    ]"
                                >
                                    📲 Virtual Wallet / E-Wallet
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Provider Select -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                    >
                                        {{
                                            accountType === "bank"
                                                ? "PILIH BANK"
                                                : "PILIH PROVIDER E-WALLET"
                                        }}
                                    </label>
                                    <select
                                        v-model="selectedProvider"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-emerald-500"
                                    >
                                        <option
                                            v-for="p in accountType === 'bank'
                                                ? bankProviders
                                                : ewalletProviders"
                                            :key="p"
                                            :value="p"
                                        >
                                            {{ p }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Custom Provider Name (if Lainnya) -->
                                <div
                                    v-if="selectedProvider.includes('Lainnya')"
                                >
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                    >
                                        NAMA
                                        {{
                                            accountType === "bank"
                                                ? "BANK"
                                                : "E-WALLET"
                                        }}
                                        KUSTOM
                                    </label>
                                    <input
                                        v-model="customProvider"
                                        placeholder="Masukkan nama bank/e-wallet"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-emerald-500"
                                    />
                                </div>

                                <!-- Account Number / E-Wallet ID -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                    >
                                        {{
                                            accountType === "bank"
                                                ? "NOMOR REKENING"
                                                : "NO. HP / ID VIRTUAL WALLET"
                                        }}
                                    </label>
                                    <input
                                        v-model="newBank.account_number"
                                        :placeholder="
                                            accountType === 'bank'
                                                ? 'cth: 806401000095564'
                                                : 'cth: 081234567890'
                                        "
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-emerald-500"
                                    />
                                </div>

                                <!-- Owner Name (a.n) -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                    >
                                        NAMA PEMILIK / ATAS NAMA (A.N)
                                    </label>
                                    <input
                                        v-model="newBank.account_name"
                                        placeholder="cth: PT.Nexus Community Punya Kita"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-emerald-500"
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-1">
                                <button
                                    type="button"
                                    @click="showAddBank = false"
                                    class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="addBank"
                                    type="button"
                                    class="px-4 py-1.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl shadow-xs cursor-pointer"
                                >
                                    Simpan Rekening / E-Wallet
                                </button>
                            </div>
                        </div>

                        <!-- Bank & Virtual Wallet List -->
                        <div
                            v-if="banksList.length === 0"
                            class="text-center py-6 text-xs text-slate-400 italic"
                        >
                            Belum ada rekening bank atau virtual wallet yang
                            tersimpan. Gunakan tombol + Tambah Rekening /
                            Virtual Wallet.
                        </div>

                        <div
                            v-else
                            class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                        >
                            <div
                                v-for="(b, idx) in banksList"
                                :key="idx"
                                class="p-3.5 bg-white border border-emerald-200/80 rounded-2xl flex items-center justify-between shadow-2xs"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                b.type === 'ewallet' ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('wallet') ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('dana') ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('ovo') ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('gopay') ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('shopee') ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes('qris')
                                                    ? 'bg-indigo-100 text-indigo-700 border-indigo-200'
                                                    : 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'px-2 py-0.5 text-[9px] font-extrabold rounded-md border uppercase tracking-wider',
                                            ]"
                                        >
                                            {{
                                                b.type === "ewallet" ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("wallet") ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("dana") ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("ovo") ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("gopay") ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("shopee") ||
                                                b.bank_name
                                                    .toLowerCase()
                                                    .includes("qris")
                                                    ? "VIRTUAL WALLET"
                                                    : "BANK"
                                            }}
                                        </span>
                                        <h4
                                            class="text-xs font-black text-slate-900"
                                        >
                                            {{ b.bank_name }}
                                        </h4>
                                    </div>
                                    <p
                                        class="text-[11px] text-slate-700 font-mono"
                                    >
                                        <strong class="text-slate-900">{{
                                            b.account_number
                                        }}</strong>
                                        <span class="text-slate-500">a.n</span>
                                        {{ b.account_name }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="removeBank(idx)"
                                    class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer shrink-0"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: ADMIN LOGIN CREDENTIALS (Matching Mockup Box) -->
                    <div
                        class="bg-slate-50/50 border border-slate-100 rounded-2xl p-5 space-y-4"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-200/60 pb-3"
                        >
                            <UserCheck class="w-4 h-4 text-slate-600" />
                            <h3
                                class="text-xs font-black text-slate-900 uppercase tracking-tight"
                            >
                                DETAIL AKUN KREDENSIAL ADMINISTRATOR UTAMA
                                (UNTUK LOGIN)
                            </h3>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                >
                                    USERNAME ADMIN
                                </label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                >
                                    NAMA LENGKAP ADMIN
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                >
                                    ALAMAT EMAIL
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                >
                                    NO HP / WHATSAPP
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                                >
                                    PASSWORD BARU ADMIN
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Password Baru"
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-500"
                                />
                                <p
                                    v-if="form.errors.password"
                                    class="text-xs text-rose-600 font-bold mt-1"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Submit Button -->
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3.5 bg-gradient-to-r from-[#B8922E] via-[#D4AF37] to-[#F3E5AB] hover:opacity-95 active:scale-[0.99] text-slate-950 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <Check class="w-4 h-4 stroke-[3]" />
                            <span>Simpan Profil & Identitas Perusahaan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
