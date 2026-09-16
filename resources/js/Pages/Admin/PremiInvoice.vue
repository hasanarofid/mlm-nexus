<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, Copy, Info, Check, Send, AlertCircle } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
  amount: Number,
  unique_code: Number,
  total_transfer: Number,
  invoice_number: String,
  banks: Array,
  whatsapp_admin: String
});

const copySuccessMsg = ref('');

const copyToClipboard = (text, type) => {
  navigator.clipboard.writeText(text);
  copySuccessMsg.value = `${type} berhasil disalin!`;
  setTimeout(() => {
    copySuccessMsg.value = '';
  }, 3000);
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const sendWhatsAppConfirmation = () => {
  const text = `Halo Admin, saya ingin konfirmasi pembayaran Premi Bulanan.%0A%0A*No Invoice:* ${props.invoice_number}%0A*Nominal Premi:* ${formatRupiah(props.amount)}%0A*Kode Unik:* ${props.unique_code}%0A*Total Transfer:* ${formatRupiah(props.total_transfer)}%0A%0ASaya akan melampirkan bukti transfer di bawah ini.`;
  window.open(`https://wa.me/${props.whatsapp_admin}?text=${text}`, '_blank');
};
</script>

<template>
  <Head title="Invoice Pembayaran Premi - TALENTA52" />

  <AdminLayout>
    <div class="max-w-3xl mx-auto space-y-6 animate-fade-in pb-12">
      <!-- Alert Copy Success -->
      <div v-if="copySuccessMsg" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 rounded-xl text-xs font-semibold flex items-center gap-2 animate-bounce">
        <Check class="w-4 h-4 text-emerald-500" />
        <span>{{ copySuccessMsg }}</span>
      </div>

      <div class="bg-white border border-slate-100 shadow-sm rounded-3xl overflow-hidden">
        <!-- Header Invoice -->
        <div class="bg-gradient-to-r from-[#1653a1] to-[#04bdb2] p-6 text-center space-y-2 relative overflow-hidden">
          <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
          <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
          
          <h2 class="text-white text-lg font-black tracking-tight relative z-10">INVOICE PEMBAYARAN PREMI</h2>
          <p class="text-[#a9fff7] text-xs font-medium relative z-10">No. Tagihan: <strong>{{ invoice_number }}</strong></p>
        </div>

        <div class="p-6 md:p-8 space-y-8">
          
          <!-- Jumlah Transfer -->
          <div class="text-center space-y-2">
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Total Yang Harus Ditransfer</p>
            <div class="flex items-center justify-center gap-3">
              <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter">{{ formatRupiah(total_transfer) }}</h1>
              <button @click="copyToClipboard(total_transfer, 'Nominal Transfer')" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl transition-colors tooltip" title="Copy Nominal">
                <Copy class="w-5 h-5" />
              </button>
            </div>
            <div class="flex items-center justify-center gap-2 text-xs font-bold text-slate-600 bg-amber-50 text-amber-700 py-2 px-4 rounded-xl inline-flex mt-2">
              <AlertCircle class="w-4 h-4" />
              <span>Pastikan transfer sesuai hingga 3 digit terakhir!</span>
            </div>
          </div>

          <!-- Rincian -->
          <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
            <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-widest mb-4 border-b border-slate-200 pb-2">Rincian Tagihan</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Nominal Premi</span>
                <span class="font-bold text-slate-800">{{ formatRupiah(amount) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Kode Unik <span class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded ml-1">Untuk verifikasi otomatis</span></span>
                <span class="font-bold text-emerald-600">+ {{ unique_code }}</span>
              </div>
              <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                <span class="text-slate-700 font-black">Total Keseluruhan</span>
                <span class="font-black text-[#1653a1] text-lg">{{ formatRupiah(total_transfer) }}</span>
              </div>
            </div>
          </div>

          <!-- Bank Rekening -->
          <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
              <CreditCard class="w-5 h-5 text-[#04bdb2]" />
              Silakan Transfer ke Rekening Berikut:
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(bank, index) in banks" :key="index" class="bg-white border-2 border-slate-100 hover:border-[#04bdb2] transition-colors rounded-2xl p-5 relative overflow-hidden group">
                <div class="absolute right-0 top-0 bottom-0 w-2 bg-slate-100 group-hover:bg-[#04bdb2] transition-colors"></div>
                
                <h4 class="text-sm font-black text-[#1653a1] mb-1">{{ bank.bank_name }}</h4>
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-2xl font-black text-slate-800 tracking-wider font-mono">{{ bank.bank_account_number }}</span>
                  <button @click="copyToClipboard(bank.bank_account_number, 'Nomor Rekening')" class="text-slate-400 hover:text-[#04bdb2]">
                    <Copy class="w-4 h-4" />
                  </button>
                </div>
                <p class="text-xs text-slate-500 font-medium">a.n <strong class="text-slate-800">{{ bank.bank_account_name }}</strong></p>
              </div>
            </div>
            <p v-if="!banks || banks.length === 0" class="text-sm text-red-500 font-bold bg-red-50 p-4 rounded-xl">
              Belum ada rekening tujuan yang diatur. Silakan hubungi admin.
            </p>
          </div>

          <!-- Konfirmasi Action -->
          <div class="pt-6 border-t border-slate-100 space-y-4">
            <div class="p-4 bg-blue-50 text-blue-800 rounded-2xl text-xs font-medium flex items-start gap-3">
              <Info class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
              <p>Setelah melakukan transfer, silakan konfirmasi ke Admin dengan menekan tombol di bawah ini sambil melampirkan foto/screenshot bukti transfer.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
              <button @click="sendWhatsAppConfirmation" class="flex-1 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-2xl shadow flex items-center justify-center gap-2 transition-colors">
                <Send class="w-4 h-4" />
                <span>Konfirmasi via WhatsApp</span>
              </button>
              
              <Link :href="route('admin.dashboard')" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-2xl transition-colors text-center flex-none">
                Kembali ke Dashboard
              </Link>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>
