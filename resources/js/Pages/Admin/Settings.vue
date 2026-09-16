<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  Settings as SettingsIcon, 
  Trophy, 
  Check, 
  RotateCcw, 
  CheckCircle2, 
  AlertCircle,
  Plus,
  Trash2,
  Edit2,
  Info
} from '@lucide/vue';

const props = defineProps({
  config: Object,
  rewards: Array,
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
  pin_price: props.config?.pin_price || 200000,
  sponsor_percent: props.config?.sponsor_percent || 100,
  pairing_percent: props.config?.pairing_percent || 50,
  titik_percent: props.config?.titik_percent || 1,
  silver_reward_percent: props.config?.silver_reward_percent || 500,
  gold_reward_percent: props.config?.gold_reward_percent || 2500,
  platinum_reward_percent: props.config?.platinum_reward_percent || 12500,
  diamond_reward_percent: props.config?.diamond_reward_percent || 75000,
  crown_reward_percent: props.config?.crown_reward_percent || 375000,
  business_mode: props.config?.business_mode ?? true,
  min_withdrawal: props.config?.min_withdrawal || 50000,
  max_level_depth: props.config?.max_level_depth || 0,
  allow_sponsor_exceed: props.config?.allow_sponsor_exceed ?? true,
  allow_pairing_exceed: props.config?.allow_pairing_exceed ?? true,
  allow_titik_exceed: props.config?.allow_titik_exceed ?? true,
  allow_reward_exceed: props.config?.allow_reward_exceed ?? true,
});

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const calcBonus = (percent) => {
  return formatRupiah((form.pin_price * (percent || 0)) / 100);
};

const submitSettings = () => {
  form.post(route('admin.settings.update'), {
    preserveScroll: true,
  });
};

const resetDefaults = () => {
  if (confirm('Apakah Anda yakin ingin mengembalikan pengaturan ke default?')) {
    form.pin_price = 200000;
    form.sponsor_percent = 100;
    form.pairing_percent = 50;
    form.titik_percent = 1;
    form.min_withdrawal = 50000;
    form.max_level_depth = 0;
    submitSettings();
  }
};

// Reward Management List & Form
const rewardList = ref(props.rewards || []);
const newReward = useForm({
  name: '',
  pairs: 10,
  bonus_cash: 1000000,
  description: '',
});

const addReward = () => {
  if (!newReward.name || !newReward.pairs) return;
  rewardList.value.push({
    id: Date.now(),
    name: newReward.name,
    pairs: newReward.pairs,
    bonus_cash: newReward.bonus_cash,
    description: newReward.description,
  });
  newReward.reset();
  saveRewards();
};

const removeReward = (idx) => {
  rewardList.value.splice(idx, 1);
  saveRewards();
};

const rewardForm = useForm({ rewards: [] });
const saveRewards = () => {
  rewardForm.rewards = rewardList.value;
  rewardForm.post(route('admin.settings.rewards'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Pengaturan Sistem & Konfigurasi Bonus - TALENTA52" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Flash Notifications -->
      <div v-if="flashSuccess" class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
          <span>{{ flashSuccess }}</span>
        </div>
      </div>

      <div v-if="flashError" class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
          <span>{{ flashError }}</span>
        </div>
      </div>

      <!-- MAIN CONTAINER 1: KONFIGURASI BONUS (White Card matching Image 2) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex items-start gap-3 border-b border-slate-100 pb-5">
          <div class="p-2.5 bg-amber-50 text-amber-600 rounded-2xl shrink-0 mt-0.5">
            <SettingsIcon class="w-6 h-6" />
          </div>
          <div>
            <h2 class="text-lg md:text-xl font-black text-slate-900 tracking-tight">
              Konfigurasi Persentase Bonus & Biaya Pendaftaran
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              Sebagai Admin Utama, Anda dapat menyesuaikan persentase distribusi bonus, biaya pendaftaran (pembelian PIN), serta nilai apresiasi reward jaringan secara instan.
            </p>
          </div>
        </div>

        <form @submit.prevent="submitSettings" class="space-y-6">
          
          <!-- SECTION 1: PENGATURAN HARGA PIN DI ADMIN -->
          <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              1. PENGATURAN HARGA PIN DI ADMIN
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
              <div class="md:col-span-5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                  HARGA PER PIN (VP/USD/RP)
                </label>
                <input 
                  v-model="form.pin_price"
                  type="number"
                  required
                  step="1000"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-black focus:outline-none focus:border-amber-500"
                />
                <p class="text-[10px] text-slate-400 mt-1">Digunakan sebagai acuan kalkulasi persentase seluruh bonus. Default Rp 200.000.</p>
              </div>

              <div class="md:col-span-7 p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl text-xs text-blue-900 flex items-start gap-2">
                <Info class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                <span class="text-[11px] leading-relaxed">Semua komisi dan reward dihitung dalam bentuk persentase dasar (%) dikalikan dengan Biaya Pendaftaran PIN yang diaktifkan ini secara otomatis.</span>
              </div>
            </div>

            <!-- Bonus 3 Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
              <!-- Bonus Sponsor -->
              <div class="p-4 bg-white border border-slate-200/80 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black text-slate-900">BONUS SPONSOR</span>
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-700 rounded uppercase">SPONSOR</span>
                </div>
                <div>
                  <label class="text-[10px] text-slate-400 block mb-0.5">PERSENTASE (%)</label>
                  <input v-model="form.sponsor_percent" type="number" step="0.1" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" />
                </div>
                <div class="text-[11px] text-slate-500 flex justify-between pt-1">
                  <span>Nilai Cair:</span>
                  <strong class="text-emerald-600 font-mono">{{ calcBonus(form.sponsor_percent) }}</strong>
                </div>
              </div>

              <!-- Bonus Pasangan -->
              <div class="p-4 bg-white border border-slate-200/80 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black text-slate-900">BONUS PASANGAN</span>
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-indigo-100 text-indigo-700 rounded uppercase">PASANGAN</span>
                </div>
                <div>
                  <label class="text-[10px] text-slate-400 block mb-0.5">PERSENTASE (%)</label>
                  <input v-model="form.pairing_percent" type="number" step="0.1" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" />
                </div>
                <div class="text-[11px] text-slate-500 flex justify-between pt-1">
                  <span>Nilai Cair:</span>
                  <strong class="text-indigo-600 font-mono">{{ calcBonus(form.pairing_percent) }}</strong>
                </div>
              </div>

              <!-- Bonus Titik -->
              <div class="p-4 bg-white border border-slate-200/80 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black text-slate-900">BONUS TITIK (MAKS 10 LEVEL)</span>
                  <span class="px-2 py-0.5 text-[9px] font-extrabold bg-emerald-100 text-emerald-700 rounded uppercase">TITIK</span>
                </div>
                <div>
                  <label class="text-[10px] text-slate-400 block mb-0.5">PERSENTASE (%)</label>
                  <input v-model="form.titik_percent" type="number" step="0.1" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" />
                </div>
                <div class="text-[11px] text-slate-500 flex justify-between pt-1">
                  <span>Nilai Cair:</span>
                  <strong class="text-emerald-600 font-mono">{{ calcBonus(form.titik_percent) }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 2: PERSENTASE BONUS REWARD KESEIMBANGAN KAKI -->
          <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-amber-500"></span>
              2. PERSENTASE BONUS REWARD KESEIMBANGAN KAKI
            </h3>
            <p class="text-[11px] text-slate-500">Terima persentase (%) dari pendaftaran yang dialokasikan sebagai akumulasi bonus reward saat member mencapai keseimbangan jumlah kaki.</p>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
              <div class="p-3 bg-white border border-slate-200/80 rounded-xl space-y-1">
                <span class="text-[10px] font-extrabold text-slate-400 block">SILVER (10/10)</span>
                <input v-model="form.silver_reward_percent" type="number" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded text-xs font-bold" />
                <span class="text-[10px] text-emerald-600 font-bold font-mono block pt-1">{{ calcBonus(form.silver_reward_percent) }}</span>
              </div>

              <div class="p-3 bg-white border border-slate-200/80 rounded-xl space-y-1">
                <span class="text-[10px] font-extrabold text-slate-400 block">GOLD (50/50)</span>
                <input v-model="form.gold_reward_percent" type="number" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded text-xs font-bold" />
                <span class="text-[10px] text-emerald-600 font-bold font-mono block pt-1">{{ calcBonus(form.gold_reward_percent) }}</span>
              </div>

              <div class="p-3 bg-white border border-slate-200/80 rounded-xl space-y-1">
                <span class="text-[10px] font-extrabold text-slate-400 block">PLATINUM (250/250)</span>
                <input v-model="form.platinum_reward_percent" type="number" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded text-xs font-bold" />
                <span class="text-[10px] text-emerald-600 font-bold font-mono block pt-1">{{ calcBonus(form.platinum_reward_percent) }}</span>
              </div>

              <div class="p-3 bg-white border border-slate-200/80 rounded-xl space-y-1">
                <span class="text-[10px] font-extrabold text-slate-400 block">DIAMOND (1000/1000)</span>
                <input v-model="form.diamond_reward_percent" type="number" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded text-xs font-bold" />
                <span class="text-[10px] text-emerald-600 font-bold font-mono block pt-1">{{ calcBonus(form.diamond_reward_percent) }}</span>
              </div>

              <div class="p-3 bg-white border border-slate-200/80 rounded-xl space-y-1">
                <span class="text-[10px] font-extrabold text-slate-400 block">CROWN (5000/5000)</span>
                <input v-model="form.crown_reward_percent" type="number" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded text-xs font-bold" />
                <span class="text-[10px] text-emerald-600 font-bold font-mono block pt-1">{{ calcBonus(form.crown_reward_percent) }}</span>
              </div>
            </div>
          </div>

          <!-- SECTION 3: MODE BISNIS & MINIMAL PENARIKAN -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Business Mode -->
            <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-3">
              <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                3. PENGATURAN MODE BISNIS
              </h3>
              <div class="flex items-center gap-3 pt-1">
                <input v-model="form.business_mode" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer" />
                <span class="text-xs font-extrabold text-slate-800">Mode: Murni Jual PIN / Voucher (Member bisa beli PIN langsung)</span>
              </div>
            </div>

            <!-- Min Withdrawal -->
            <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-3">
              <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                4. PENGATURAN MINIMAL PENARIKAN
              </h3>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">MINIMAL PENARIKAN (RUPIAH)</label>
                <input v-model="form.min_withdrawal" type="number" step="5000" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold" />
              </div>
            </div>
          </div>

          <!-- SECTION 5: BATASAN KEDALAMAN LEVEL JARINGAN -->
          <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-4">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
              5. BATASAN KEDALAMAN LEVEL JARINGAN
            </h3>
            <p class="text-[11px] text-slate-500">Ukur maksimal kedalaman level jaringan untuk pembagian bonus. Jika diset "0", artinya tanpa batas kedalaman (unlimited).</p>

            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">MAKSIMAL LEVEL JARINGAN (0 = TANPA BATAS)</label>
                <input v-model="form.max_level_depth" type="number" min="0" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold" />
              </div>

              <div class="p-4 bg-white border border-slate-200/80 rounded-xl space-y-2 text-xs font-semibold text-slate-700">
                <span class="text-[10px] text-slate-400 font-bold block uppercase mb-1">Pengecualian Batasan Level:</span>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.allow_sponsor_exceed" type="checkbox" class="w-4 h-4 text-amber-600 rounded" />
                  <span>Tetap berikan Bonus Sponsor</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.allow_pairing_exceed" type="checkbox" class="w-4 h-4 text-amber-600 rounded" />
                  <span>Tetap berikan Bonus Pasangan (Pairing)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.allow_titik_exceed" type="checkbox" class="w-4 h-4 text-amber-600 rounded" />
                  <span>Tetap berikan Bonus Titik</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.allow_reward_exceed" type="checkbox" class="w-4 h-4 text-amber-600 rounded" />
                  <span>Tetap hitung poin untuk Bonus Reward</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" @click="resetDefaults" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center gap-1.5">
              <RotateCcw class="w-3.5 h-3.5" />
              <span>Reset ke Default</span>
            </button>

            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50">
              <Check class="w-4 h-4 stroke-[3]" />
              <span>Simpan Pengaturan</span>
            </button>
          </div>

        </form>

      </div>

      <!-- MAIN CONTAINER 2: DAFTAR & PENGATURAN REWARD JARINGAN (Matching Image 2 Bottom) -->
      <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
          <div class="p-2 bg-amber-50 text-amber-600 rounded-xl shrink-0 mt-0.5">
            <Trophy class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-base font-black text-slate-900 tracking-tight">Daftar & Pengaturan Reward Jaringan</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              Tambahkan, ubah, atau hapus kriteria pencapaian reward keseimbangan jaringan kaki kiri & kanan secara dinamis. Semua perubahan langsung berlaku real-time.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          
          <!-- Active Reward Cards Grid (8 Cols) -->
          <div class="lg:col-span-8 space-y-3">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">REWARD JARINGAN AKTIF ({{ rewardList.length }})</span>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="(r, idx) in rewardList" :key="r.id || idx" class="p-4 bg-slate-50/70 border border-slate-100 rounded-2xl flex items-center justify-between">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <Trophy class="w-4 h-4 text-amber-500" />
                    <h4 class="text-xs font-black text-slate-900">{{ r.name }}</h4>
                  </div>
                  <p class="text-[10px] text-slate-500 font-medium">Target: <strong class="text-slate-800 font-mono">{{ r.pairs }} / {{ r.pairs }} Pasang</strong></p>
                  <p class="text-[11px] text-slate-700 font-bold pt-1">{{ r.description }} | <span class="text-emerald-600 font-mono">{{ formatRupiah(r.bonus_cash) }}</span></p>
                </div>
                <button type="button" @click="removeReward(idx)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg cursor-pointer">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Add Reward Form (4 Cols) -->
          <div class="lg:col-span-4 bg-slate-50/70 border border-slate-100 rounded-2xl p-5 space-y-4">
            <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-1.5">
              <Plus class="w-4 h-4 text-amber-600" />
              <span>TAMBAH PAKET REWARD BARU</span>
            </h4>

            <div class="space-y-3 text-xs font-medium">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NAMA REWARD MILESTONE</label>
                <input v-model="newReward.name" placeholder="e.g. Silver Reward" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl" />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">SYARAT KESEIMBANGAN KAKI (PASANG)</label>
                <input v-model="newReward.pairs" type="number" placeholder="10" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl" />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NOMINAL BONUS TUNAI (RP)</label>
                <input v-model="newReward.bonus_cash" type="number" placeholder="1000000" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold" />
              </div>

              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">DESKRIPSI / HADIAH PRODUK</label>
                <input v-model="newReward.description" placeholder="e.g. HP Android / Rp 1 Juta" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl" />
              </div>

              <button type="button" @click="addReward" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                <Check class="w-4 h-4" />
                <span>Tambah</span>
              </button>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AdminLayout>
</template>
