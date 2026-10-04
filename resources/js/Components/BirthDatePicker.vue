<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: '',
  },
  placeholderDay: {
    type: String,
    default: 'Tgl',
  },
  placeholderMonth: {
    type: String,
    default: 'Bulan',
  },
  placeholderYear: {
    type: String,
    default: 'Tahun',
  },
  theme: {
    type: String,
    default: 'gold', // 'gold' or 'emerald' or 'slate'
  },
});

const emit = defineEmits(['update:modelValue']);

const day = ref('');
const month = ref('');
const year = ref('');

const months = [
  { value: '01', label: 'Januari' },
  { value: '02', label: 'Februari' },
  { value: '03', label: 'Maret' },
  { value: '04', label: 'April' },
  { value: '05', label: 'Mei' },
  { value: '06', label: 'Juni' },
  { value: '07', label: 'Juli' },
  { value: '08', label: 'Agustus' },
  { value: '09', label: 'September' },
  { value: '10', label: 'Oktober' },
  { value: '11', label: 'November' },
  { value: '12', label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const years = computed(() => {
  const list = [];
  for (let y = currentYear; y >= 1930; y--) {
    list.push(String(y));
  }
  return list;
});

const maxDays = computed(() => {
  if (!month.value) return 31;
  const m = parseInt(month.value, 10);
  const y = year.value ? parseInt(year.value, 10) : 2024;
  return new Date(y, m, 0).getDate();
});

const days = computed(() => {
  const count = maxDays.value;
  const list = [];
  for (let d = 1; d <= count; d++) {
    list.push(String(d).padStart(2, '0'));
  }
  return list;
});

const parseValue = (val) => {
  if (!val) {
    day.value = '';
    month.value = '';
    year.value = '';
    return;
  }
  const parts = String(val).trim().split('-');
  if (parts.length === 3) {
    year.value = parts[0] || '';
    month.value = parts[1] ? parts[1].padStart(2, '0') : '';
    day.value = parts[2] ? parts[2].padStart(2, '0') : '';
  }
};

watch(() => props.modelValue, (newVal) => {
  const currentFormatted = (year.value && month.value && day.value) ? `${year.value}-${month.value}-${day.value}` : '';
  if (newVal !== currentFormatted) {
    parseValue(newVal);
  }
}, { immediate: true });

const emitUpdate = () => {
  if (year.value && month.value && day.value) {
    if (parseInt(day.value, 10) > maxDays.value) {
      day.value = String(maxDays.value).padStart(2, '0');
    }
    emit('update:modelValue', `${year.value}-${month.value}-${day.value}`);
  } else {
    emit('update:modelValue', '');
  }
};

watch([day, month, year], () => {
  emitUpdate();
});
</script>

<template>
  <div class="grid grid-cols-12 gap-2 w-full">
    <!-- TANGGAL (3 cols) -->
    <div class="col-span-3 sm:col-span-3">
      <select
        v-model="day"
        :required="required"
        class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-[#D4AF37] focus:bg-white transition-all cursor-pointer"
      >
        <option value="" disabled>{{ placeholderDay }}</option>
        <option v-for="d in days" :key="d" :value="d">
          {{ d }}
        </option>
      </select>
    </div>

    <!-- BULAN (5 cols) -->
    <div class="col-span-5 sm:col-span-5">
      <select
        v-model="month"
        :required="required"
        class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-[#D4AF37] focus:bg-white transition-all cursor-pointer"
      >
        <option value="" disabled>{{ placeholderMonth }}</option>
        <option v-for="m in months" :key="m.value" :value="m.value">
          {{ m.label }}
        </option>
      </select>
    </div>

    <!-- TAHUN (4 cols) -->
    <div class="col-span-4 sm:col-span-4">
      <select
        v-model="year"
        :required="required"
        class="w-full px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-[#D4AF37] focus:bg-white transition-all cursor-pointer"
      >
        <option value="" disabled>{{ placeholderYear }}</option>
        <option v-for="y in years" :key="y" :value="y">
          {{ y }}
        </option>
      </select>
    </div>
  </div>
</template>
