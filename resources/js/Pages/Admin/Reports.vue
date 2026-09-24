<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { 
  FileSpreadsheet, 
  FileText, 
  Users, 
  Award, 
  ArrowUpRight, 
  Wallet,
  Check,
  X,
  Phone,
  ShieldCheck,
  TrendingUp,
  Clock,
  Sparkles
} from '@lucide/vue';

const props = defineProps({
  is_admin: Boolean,
  active_type: String,
  report_data: Array,
  summary: Object,
  current_user: Object,
});

const switchTab = (type) => {
  router.get(
    route('admin.reports.index'),
    { type: type },
    { preserveState: true, preserveScroll: true }
  );
};

const exportExcel = () => {
  window.open(route('admin.reports.export-excel', { type: props.active_type }), '_blank');
};

const exportPdf = () => {
  window.open(route('admin.reports.export-pdf', { type: props.active_type }), '_blank');
};

const approvePencairan = (id) => {
  if (confirm('Apakah Anda yakin ingin menyetujui permohonan penarikan saldo (WD) ini?')) {
    router.post(route('admin.withdrawals.approve', id), {}, {
      preserveScroll: true,
    });
  }
};

const rejectPencairan = (id) => {
  const notes = prompt('Masukkan alasan penolakan penarikan saldo:');
  if (notes !== null) {
    router.post(route('admin.withdrawals.reject', id), { notes }, {
      preserveScroll: true,
    });
  }
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const reportTabs = [
  { type: 'team', label: 'Laporan Team Mitra', icon: Users },
  { type: 'bonus', label: 'Laporan Bonus', icon: Award },
  { type: 'withdrawal', label: 'Laporan Withdrawal', icon: ArrowUpRight },
  { type: 'mutasi', label: 'Laporan Mutasi Saldo', icon: Wallet },
];
</script>

<template>
  <Head title="Menu Laporan - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- 1. TOP HEADER & EXPORT ACTION BUTTONS -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-100 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#faf6eb] border border-[#D4AF37]/30 text-[#b8922e] text-[10px] font-black uppercase tracking-wider mb-2">
            <Sparkles class="w-3 h-3 text-[#D4AF37]" />
            <span>Rekapitulasi Transaksi & Jaringan</span>
          </div>
          <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
            Menu Laporan {{ is_admin ? 'Sistem' : 'Mitra' }}
          </h2>
          <p class="text-xs text-slate-500 font-medium mt-0.5">
            Pantau rincian pendaftaran Team Mitra, akumulasi bonus unilevel, mutasi dompet, dan riwayat penarikan dana (WD).
          </p>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2.5 shrink-0">
          <button 
            @click="exportExcel"
            class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-2xl text-xs font-black shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-[0.98]"
          >
            <FileSpreadsheet class="w-4 h-4 text-emerald-600" />
            <span>Export Excel</span>
          </button>

          <button 
            @click="exportPdf"
            class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 rounded-2xl text-xs font-black shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-[0.98]"
          >
            <FileText class="w-4 h-4 text-rose-600" />
            <span>Download PDF</span>
          </button>
        </div>
      </div>

      <!-- 2. SUMMARY METRICS CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Team Mitra -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-xs flex items-center gap-4 relative overflow-hidden">
          <div class="w-12 h-12 rounded-2xl bg-[#0F172A] text-[#D4AF37] flex items-center justify-center flex-shrink-0 shadow-sm border border-[#D4AF37]/30">
            <Users class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Team Mitra</p>
            <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ summary?.total_team || 0 }} <span class="text-xs font-bold text-slate-400">Mitra</span></h3>
          </div>
        </div>

        <!-- Card 2: Total Bonus Diterima -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-xs flex items-center gap-4 relative overflow-hidden">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 shadow-sm border border-emerald-200">
            <Award class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Bonus Diterima</p>
            <h3 class="text-lg font-black text-emerald-700 tracking-tight">{{ formatRupiah(summary?.total_bonus) }}</h3>
          </div>
        </div>

        <!-- Card 3: Total Withdrawal (WD) -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-xs flex items-center gap-4 relative overflow-hidden">
          <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm border border-blue-200">
            <ArrowUpRight class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Penarikan (WD)</p>
            <h3 class="text-lg font-black text-blue-700 tracking-tight">{{ formatRupiah(summary?.total_withdrawal) }}</h3>
          </div>
        </div>

        <!-- Card 4: Saldo Dompet -->
        <div class="bg-gradient-to-br from-[#0F172A] to-[#1E293B] border border-[#D4AF37]/40 rounded-3xl p-5 shadow-sm flex items-center gap-4 text-white relative overflow-hidden">
          <div class="w-12 h-12 rounded-2xl bg-[#D4AF37]/10 border border-[#D4AF37]/40 text-[#D4AF37] flex items-center justify-center flex-shrink-0 shadow-sm">
            <Wallet class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Saldo Wallet</p>
            <h3 class="text-lg font-black text-[#D4AF37] tracking-tight">{{ formatRupiah(summary?.saldo_wallet) }}</h3>
          </div>
        </div>
      </div>

      <!-- 3. REPORT NAVIGATION TABS -->
      <div class="flex items-center gap-2.5 overflow-x-auto pb-1 max-w-full">
        <button 
          v-for="t in reportTabs" 
          :key="t.type"
          @click="switchTab(t.type)"
          :class="[
            active_type === t.type 
              ? 'bg-[#0F172A] text-[#D4AF37] border border-[#D4AF37]/50 font-black shadow-md' 
              : 'bg-white text-slate-700 hover:bg-slate-50 font-bold border border-slate-200/80',
            'px-5 py-3 text-xs rounded-2xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 active:scale-[0.98]'
          ]"
        >
          <component :is="t.icon" class="w-4 h-4" />
          <span>{{ t.label }}</span>
        </button>
      </div>

      <!-- 4. MAIN TABLE DATA CONTAINER CARD -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm">
        <div class="overflow-x-auto">
          
          <!-- TAB 1: LAPORAN TEAM MITRA -->
          <table v-if="active_type === 'team'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                <th class="py-3.5 px-4 rounded-l-xl">NO</th>
                <th class="py-3.5 px-4">NAMA MITRA</th>
                <th class="py-3.5 px-4">NO. WHATSAPP</th>
                <th class="py-3.5 px-4">TINGKATAN GENERASI</th>
                <th class="py-3.5 px-4">SPONSOR LANGSUNG</th>
                <th class="py-3.5 px-4">MEMBERSHIP</th>
                <th class="py-3.5 px-4 text-right rounded-r-xl">TGL DAFTAR</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="(row, idx) in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 text-slate-400 font-mono text-xs font-bold">{{ idx + 1 }}</td>
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs leading-tight">{{ row.name }}</h4>
                  <p class="text-[11px] text-[#B8922E] font-mono font-bold">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4">
                  <span class="inline-flex items-center gap-1.5 text-slate-700 font-semibold font-mono">
                    <Phone class="w-3.5 h-3.5 text-emerald-600" />
                    <span>{{ row.phone }}</span>
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="[
                      row.gen_level === 1 ? 'bg-[#faf6eb] text-[#B8922E] border-[#D4AF37]/40 font-black' : 'bg-slate-100 text-slate-700 border-slate-200',
                      'px-2.5 py-1 text-[10px] rounded-lg border uppercase tracking-wider inline-block font-bold'
                    ]"
                  >
                    {{ row.generation }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-700">
                  {{ row.sponsor }}
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-1 text-[10px] font-bold bg-slate-900 text-[#D4AF37] border border-slate-800 rounded-lg">
                    {{ row.tier }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">
                  {{ row.created_at }}
                </td>
              </tr>

              <tr v-if="report_data.length === 0">
                <td colspan="7" class="py-14 text-center text-slate-400 text-xs italic space-y-2">
                  <Users class="w-8 h-8 text-slate-300 mx-auto" />
                  <p>Belum ada mitra jaringan yang terdaftar.</p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 2: LAPORAN BONUS -->
          <table v-else-if="active_type === 'bonus'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                <th class="py-3.5 px-4 rounded-l-xl">KODE</th>
                <th class="py-3.5 px-4">PENERIMA</th>
                <th class="py-3.5 px-4">JENIS BONUS</th>
                <th class="py-3.5 px-4">SUMBER MITRA</th>
                <th class="py-3.5 px-4">DESKRIPSI</th>
                <th class="py-3.5 px-4">NOMINAL BONUS</th>
                <th class="py-3.5 px-4 text-right rounded-r-xl">TANGGAL & WAKTU</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-500 text-xs">{{ row.code }}</td>
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="[
                      row.raw_category === 'sponsor' ? 'bg-[#faf6eb] text-[#B8922E] border-[#D4AF37]/40' : 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'px-2.5 py-1 text-[9px] font-black rounded-lg border uppercase tracking-wider'
                    ]"
                  >
                    {{ row.category }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                  {{ row.source }}
                  <span v-if="row.source_name && row.source_name !== '-'" class="block font-sans text-[10px] text-slate-400 font-normal">({{ row.source_name }})</span>
                </td>
                <td class="py-3.5 px-4 text-slate-800 font-medium">{{ row.description }}</td>
                <td class="py-3.5 px-4 font-black text-emerald-600 font-mono text-xs">+{{ formatRupiah(row.amount) }}</td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
              </tr>

              <tr v-if="report_data.length === 0">
                <td colspan="7" class="py-14 text-center text-slate-400 text-xs italic space-y-2">
                  <Award class="w-8 h-8 text-slate-300 mx-auto" />
                  <p>Belum ada riwayat bonus yang tercatat.</p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 3: LAPORAN PENCAIRAN / WITHDRAWAL -->
          <table v-else-if="active_type === 'withdrawal'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                <th class="py-3.5 px-4 rounded-l-xl">ID PENARIKAN</th>
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">REKENING TUJUAN</th>
                <th class="py-3.5 px-4">NOMINAL WD</th>
                <th class="py-3.5 px-4">STATUS</th>
                <th class="py-3.5 px-4">TGL PENGAJUAN</th>
                <th v-if="is_admin" class="py-3.5 px-4 text-center rounded-r-xl">AKSI / APPROVE</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-500">{{ row.code }}</td>
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ row.bank_name }} - <strong class="font-mono text-slate-900">{{ row.bank_account_number }}</strong>
                  <span class="block text-[10px] text-slate-400">a.n {{ row.bank_account_name }}</span>
                </td>
                <td class="py-3.5 px-4 font-black text-slate-900 font-mono text-xs">{{ formatRupiah(row.amount) }}</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="[
                      row.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' :
                      row.status === 'REJECTED' ? 'bg-rose-100 text-rose-800 border-rose-300' :
                      'bg-amber-100 text-amber-800 border-amber-300',
                      'px-2.5 py-1 text-[9px] font-black rounded-lg border uppercase tracking-wider'
                    ]"
                  >
                    {{ row.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
                <td v-if="is_admin" class="py-3.5 px-4 text-center">
                  <div v-if="row.status === 'PENDING'" class="flex items-center justify-center gap-1.5">
                    <button 
                      @click="approvePencairan(row.id)"
                      class="px-3 py-1.5 bg-[#0F172A] hover:bg-slate-800 text-[#D4AF37] font-black text-[10px] rounded-xl shadow-xs border border-[#D4AF37]/30 transition-all cursor-pointer inline-flex items-center gap-1"
                    >
                      <Check class="w-3 h-3 stroke-[3]" />
                      <span>Approve</span>
                    </button>
                    <button 
                      @click="rejectPencairan(row.id)"
                      class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-black text-[10px] rounded-xl shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                    >
                      <X class="w-3 h-3 stroke-[3]" />
                      <span>Tolak</span>
                    </button>
                  </div>
                  <span v-else class="text-slate-400 text-[10px] font-medium italic">Selesai</span>
                </td>
              </tr>

              <tr v-if="report_data.length === 0">
                <td :colspan="is_admin ? 7 : 6" class="py-14 text-center text-slate-400 text-xs italic space-y-2">
                  <ArrowUpRight class="w-8 h-8 text-slate-300 mx-auto" />
                  <p>Belum ada riwayat penarikan saldo (withdrawal).</p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- TAB 4: LAPORAN MUTASI SALDO -->
          <table v-else-if="active_type === 'mutasi'" class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                <th class="py-3.5 px-4 rounded-l-xl">KODE</th>
                <th class="py-3.5 px-4">MEMBER</th>
                <th class="py-3.5 px-4">KATEGORI</th>
                <th class="py-3.5 px-4">DESKRIPSI</th>
                <th class="py-3.5 px-4">TIPE MUTASI</th>
                <th class="py-3.5 px-4">NOMINAL</th>
                <th class="py-3.5 px-4 text-right rounded-r-xl">TANGGAL</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr v-for="row in report_data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-500 text-xs">{{ row.code }}</td>
                <td class="py-3.5 px-4 space-y-0.5">
                  <h4 class="font-extrabold text-slate-900 text-xs">{{ row.name }}</h4>
                  <p class="text-[11px] text-slate-400 font-mono">@{{ row.username }}</p>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-1 text-[9px] font-black bg-slate-100 text-slate-700 border border-slate-200 rounded-lg uppercase tracking-wider">
                    {{ row.category }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800">{{ row.description }}</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="[
                      row.type === 'MASUK' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800',
                      'px-2 py-0.5 text-[9px] font-black rounded uppercase'
                    ]"
                  >
                    {{ row.type }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-black font-mono text-xs" :class="row.type === 'MASUK' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ row.type === 'MASUK' ? '+' : '-' }}{{ formatRupiah(row.amount) }}
                </td>
                <td class="py-3.5 px-4 text-right text-slate-400 font-mono text-xs">{{ row.created_at }}</td>
              </tr>

              <tr v-if="report_data.length === 0">
                <td colspan="7" class="py-14 text-center text-slate-400 text-xs italic space-y-2">
                  <Wallet class="w-8 h-8 text-slate-300 mx-auto" />
                  <p>Belum ada riwayat mutasi saldo.</p>
                </td>
              </tr>
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </AdminLayout>
</template>

