<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SponsorTreeNode from '@/Components/SponsorTreeNode.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { 
  User, 
  Search, 
  RotateCcw, 
  Users,
  Layers,
  Network,
  Maximize2,
  Minimize2,
  FolderTree
} from '@lucide/vue';

const props = defineProps({
  focus_user: Object,
  tree_data: Object,
  direct_downlines: Array,
  generations: Array,
  all_users: Array,
  is_admin: Boolean
});

const selectedUserSearch = ref(props.focus_user?.id || '');

// Reactive map tracking expanded/collapsed status of tree nodes
const expandedMap = reactive({});

// Toggle single node expand/collapse
const handleToggleNode = (nodeId) => {
  if (expandedMap[nodeId] === undefined) {
    // If not set, default was true (for top levels), so flip to false
    expandedMap[nodeId] = false;
  } else {
    expandedMap[nodeId] = !expandedMap[nodeId];
  }
};

// Expand all nodes recursively
const expandAll = () => {
  const traverse = (node) => {
    if (!node) return;
    expandedMap[node.id] = true;
    if (node.children && node.children.length > 0) {
      node.children.forEach(traverse);
    }
  };
  traverse(props.tree_data);
};

// Collapse all nodes (except root)
const collapseAll = () => {
  const traverse = (node) => {
    if (!node) return;
    expandedMap[node.id] = false;
    if (node.children && node.children.length > 0) {
      node.children.forEach(traverse);
    }
  };
  traverse(props.tree_data);
};

const focusUser = (userId) => {
  if (!userId) return;
  router.get(route('admin.pohon-jaringan'), { focus_id: userId }, { preserveState: true });
};

const resetFocus = () => {
  selectedUserSearch.value = '';
  router.get(route('admin.pohon-jaringan'));
};

const getBadgeColor = (pkg) => {
  const p = (pkg || '').toLowerCase();
  if (p.includes('partner') || p.includes('ultimate') || p.includes('10.500') || p.includes('10500')) return 'bg-amber-100 text-amber-800 border-amber-300';
  if (p.includes('business') || p.includes('pro') || p.includes('4.300') || p.includes('4300')) return 'bg-purple-100 text-purple-800 border-purple-300';
  if (p.includes('affiliate') || p.includes('medium') || p.includes('2.100') || p.includes('2100')) return 'bg-indigo-100 text-indigo-800 border-indigo-300';
  if (p.includes('star') || p.includes('basic') || p.includes('550')) return 'bg-blue-100 text-blue-800 border-blue-300';
  if (p.includes('seller') || p.includes('125')) return 'bg-emerald-100 text-emerald-800 border-emerald-300';
  return 'bg-slate-100 text-slate-700 border-slate-300';
};
</script>

<template>
  <Head title="Pohon Jaringan & Tabel Sponsor - NEXUS COMMUNITY" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Top Search & Focus Control Bar -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search User Select -->
        <div class="relative flex-1 max-w-xl flex items-center gap-2">
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <Search class="w-4 h-4" />
            </span>
            <select
              v-model="selectedUserSearch"
              @change="focusUser(selectedUserSearch)"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-8 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors appearance-none cursor-pointer"
            >
              <option v-for="u in all_users" :key="u.id" :value="u.id">
                {{ u.label }}
              </option>
            </select>
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 text-xs">
              ▼
            </span>
          </div>
        </div>

        <!-- Reset Button -->
        <button 
          @click="resetFocus"
          class="px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0 cursor-pointer"
        >
          <RotateCcw class="w-3.5 h-3.5 text-slate-500" />
          <span>Reset Fokus ke Saya</span>
        </button>
      </div>

      <!-- Member Focus Header Summary Card -->
      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
              {{ focus_user?.name }}
            </h2>
            <span class="text-xs font-bold text-slate-400 font-mono">@{{ focus_user?.username }}</span>
            <span :class="['px-2.5 py-0.5 text-[10px] font-extrabold rounded-md border uppercase tracking-wider', getBadgeColor(focus_user?.package_name)]">
              Paket {{ focus_user?.package_name }}
            </span>
          </div>
          <p class="text-xs text-slate-500 font-medium pt-0.5">
            Struktur Jaringan Sponsor & Multi-Tier Unilevel (Generasi 1 s/d Generasi 10)
          </p>
        </div>

        <!-- Stat Badges -->
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
          <div class="px-5 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
              <Users class="w-5 h-5" />
            </div>
            <div>
              <span class="text-[9px] font-extrabold text-emerald-800 uppercase tracking-wider block">SPONSOR LANGSUNG (G1)</span>
              <span class="text-sm font-black text-emerald-700 font-mono">{{ focus_user?.total_direct || 0 }} Member</span>
            </div>
          </div>

          <div class="px-5 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
              <Layers class="w-5 h-5" />
            </div>
            <div>
              <span class="text-[9px] font-extrabold text-indigo-800 uppercase tracking-wider block">JUMLAH MITRA TIM</span>
              <span class="text-sm font-black text-indigo-700 font-mono">{{ focus_user?.total_team || 0 }} Member</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Layout: Tabel Sponsor (Tree View) + Ringkasan Generasi (Side) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: TABEL SPONSOR (Tree View) - 8 / 12 Cols -->
        <div class="lg:col-span-8 space-y-4">
          <!-- Dark Themed Container matching Mockup Screenshot -->
          <div class="bg-[#182234] border border-slate-700/60 rounded-3xl p-5 sm:p-7 shadow-xl space-y-6 text-white overflow-hidden">
            
            <!-- Card Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700/80 pb-4">
              <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-500/20 text-indigo-400 rounded-xl border border-indigo-500/30">
                  <FolderTree class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-base font-black text-white tracking-tight">
                    Tabel Sponsor
                  </h3>
                  <p class="text-[11px] text-slate-400">
                    Pohon hierarki jaringan mitra sponsor
                  </p>
                </div>
              </div>

              <!-- Quick Expand / Collapse Actions -->
              <div class="flex items-center gap-2 shrink-0">
                <button 
                  type="button"
                  @click="expandAll"
                  class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
                >
                  <Maximize2 class="w-3.5 h-3.5 text-indigo-400" />
                  <span>Buka Semua</span>
                </button>
                <button 
                  type="button"
                  @click="collapseAll"
                  class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
                >
                  <Minimize2 class="w-3.5 h-3.5 text-slate-400" />
                  <span>Tutup Semua</span>
                </button>
              </div>
            </div>

            <!-- SPONSOR TREE VIEW RENDER AREA -->
            <div class="overflow-x-auto pb-4 pt-2 -mx-2 px-2 scrollbar-thin">
              <div v-if="tree_data" class="min-w-max">
                <SponsorTreeNode 
                  :node="tree_data"
                  :is-root="true"
                  :expanded-map="expandedMap"
                  :depth="0"
                  @toggle-node="handleToggleNode"
                  @focus-user="focusUser"
                />
              </div>
              <div v-else class="text-center py-10 text-slate-400 text-xs">
                Data jaringan tidak ditemukan.
              </div>
            </div>

            <!-- Card Footer Note -->
            <div class="pt-3 border-t border-slate-700/60 text-[11px] text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <span>💡 Klik pada nama mitra / panah untuk membuka downline.</span>
              <span class="text-slate-500">Format: <strong>Username (Jumlah Mitra)</strong></span>
            </div>

          </div>
        </div>

        <!-- RIGHT: Ringkasan Kedalaman Generasi (4 / 12 Cols) -->
        <div class="lg:col-span-4 space-y-4">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div class="flex items-center gap-2">
                <Layers class="w-4 h-4 text-indigo-600" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">RINGKASAN KEDALAMAN GENERASI</h3>
              </div>
            </div>

            <div class="space-y-2 max-h-[520px] overflow-y-auto pr-1">
              <div 
                v-for="gen in generations" 
                :key="gen.generation"
                class="p-3 bg-slate-50/70 hover:bg-indigo-50/50 border border-slate-100 hover:border-indigo-200 rounded-xl flex items-center justify-between transition-colors"
              >
                <div class="flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-[10px] flex items-center justify-center border border-indigo-200">
                    G{{ gen.generation }}
                  </span>
                  <span class="text-xs font-bold text-slate-700">{{ gen.label }}</span>
                </div>
                <span :class="[gen.count > 0 ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : 'bg-slate-100 text-slate-400 border-slate-200', 'px-2.5 py-0.5 text-xs font-extrabold rounded-full font-mono border']">
                  {{ gen.count }} Member
                </span>
              </div>
            </div>

            <!-- Info Box -->
            <div class="p-3.5 bg-amber-50/80 border border-amber-200/80 rounded-2xl text-[11px] text-amber-900 space-y-1">
              <span class="font-bold block">📌 Aturan Unilevel Multi-Tier:</span>
              <p class="text-amber-800/90 leading-relaxed">
                Setiap pendaftaran mitra baru memberikan bonus <strong>Rp 7.000</strong> (50% Auto Save & 50% Saldo WD) untuk setiap Upline dari Generasi 1 s/d Generasi 10.
              </p>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
