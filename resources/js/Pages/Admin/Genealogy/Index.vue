<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  User, 
  Search, 
  RotateCcw, 
  ChevronRight,
  ChevronDown,
  Users,
  Layers,
  ArrowRight,
  Folder,
  FolderOpen,
  UserCheck
} from '@lucide/vue';

const props = defineProps({
  focus_user: Object,
  direct_downlines: Array,
  generations: Array,
  all_users: Array
});

const selectedUserSearch = ref(props.focus_user?.id || '');

// Track open/closed state of tree folders
const isRootOpen = ref(true);
const openGenerations = ref({}); // { 1: true, 2: false, ... }
const openSubtrees = ref({});    // { memberId: true, ... }

// Initialize Generasi 1 as open by default
if (props.generations && props.generations.length > 0) {
  openGenerations.value[1] = true;
}

const toggleGenerationFolder = (genNumber) => {
  openGenerations.value[genNumber] = !openGenerations.value[genNumber];
};

const toggleMemberSubtree = (memberId) => {
  openSubtrees.value[memberId] = !openSubtrees.value[memberId];
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
  <Head title="Pohon Jaringan & Team Mitra - TALENTA52" />

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
          <div class="flex items-center gap-2">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
              {{ focus_user?.name }}
            </h2>
            <span class="text-xs font-bold text-slate-400 font-mono">{{ focus_user?.username }}</span>
            <span :class="['px-2.5 py-0.5 text-[10px] font-extrabold rounded-md border uppercase tracking-wider', getBadgeColor(focus_user?.package_name)]">
              Paket {{ focus_user?.package_name }}
            </span>
          </div>
          <p class="text-xs text-slate-500 font-medium pt-0.5">
            Struktur Pohon Jaringan Unilevel Matahari (Generasi 1 s/d Generasi 10)
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

      <!-- Main Content Grid: Tree View (Left - 8 Cols) and Depth Summary (Right - 4 Cols) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: Pohon Jaringan Folder Tree View (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
          <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2">
                <FolderOpen class="w-5 h-5 text-indigo-600" />
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                  POHON JARINGAN MITRA (FOLDER TREE VIEW)
                </h3>
              </div>
              <span class="text-[11px] text-slate-500 font-medium">
                Klik folder untuk membuka/menutup cabang generasi
              </span>
            </div>

            <!-- TREE HIERARCHY STRUCTURE (Gambar 1 Layout) -->
            <div class="space-y-4">
              
              <!-- ROOT FOLDER NODE: Jumlah Mitra -->
              <div class="border-2 border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50 shadow-xs">
                
                <!-- Root Folder Header -->
                <div 
                  @click="isRootOpen = !isRootOpen"
                  class="p-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between cursor-pointer select-none hover:opacity-95 transition-opacity"
                >
                  <div class="flex items-center gap-3">
                    <button class="p-1 text-slate-300 hover:text-white">
                      <ChevronDown v-if="isRootOpen" class="w-5 h-5" />
                      <ChevronRight v-else class="w-5 h-5" />
                    </button>
                    <FolderOpen v-if="isRootOpen" class="w-6 h-6 text-amber-400" />
                    <Folder v-else class="w-6 h-6 text-amber-400" />
                    <div>
                      <h4 class="text-sm font-black tracking-tight text-white flex items-center gap-2">
                        <span>Jumlah Mitra</span>
                        <span class="text-[10px] bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full border border-amber-400/30">
                          Root Tree
                        </span>
                      </h4>
                      <p class="text-[10px] text-slate-300">
                        Mitra Utama: <strong class="text-white">{{ focus_user?.name }}</strong> ({{ focus_user?.username }})
                      </p>
                    </div>
                  </div>

                  <div class="text-right">
                    <span class="text-[10px] text-slate-300 uppercase tracking-wider block font-bold">TOTAL JML MITRA</span>
                    <span class="text-base font-black text-amber-300 font-mono">{{ focus_user?.total_team || 0 }} Member</span>
                  </div>
                </div>

                <!-- Root Content: Sub-folders for Generasi 1 s/d 10 -->
                <div v-if="isRootOpen" class="p-4 space-y-3 pl-6 border-t border-slate-200">
                  
                  <div 
                    v-for="gen in generations" 
                    :key="gen.generation"
                    class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs"
                  >
                    <!-- Generation Folder Header -->
                    <div 
                      @click="toggleGenerationFolder(gen.generation)"
                      class="p-3 bg-slate-100/80 hover:bg-slate-100 flex items-center justify-between cursor-pointer select-none transition-colors border-l-4 border-indigo-500"
                    >
                      <div class="flex items-center gap-2.5">
                        <button class="p-0.5 text-slate-500">
                          <ChevronDown v-if="openGenerations[gen.generation]" class="w-4 h-4" />
                          <ChevronRight v-else class="w-4 h-4" />
                        </button>
                        <FolderOpen v-if="openGenerations[gen.generation]" class="w-4 h-4 text-indigo-600" />
                        <Folder v-else class="w-4 h-4 text-slate-500" />
                        <span class="text-xs font-black text-slate-800">{{ gen.label }}</span>
                      </div>

                      <div class="flex items-center gap-2">
                        <span :class="[gen.count > 0 ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : 'bg-slate-200 text-slate-500 border-slate-300', 'px-2.5 py-0.5 text-xs font-extrabold rounded-full font-mono border']">
                          {{ gen.count }} Mitra
                        </span>
                      </div>
                    </div>

                    <!-- Generation Members List -->
                    <div v-if="openGenerations[gen.generation]" class="p-3 space-y-2 bg-slate-50/50 border-t border-slate-100 pl-6">
                      
                      <div v-if="!gen.members || gen.members.length === 0" class="py-3 text-center text-xs text-slate-400 italic">
                        Belum ada mitra pada {{ gen.label }}.
                      </div>

                      <div 
                        v-for="member in gen.members" 
                        :key="member.id"
                        class="p-3 bg-white border border-slate-200 rounded-xl space-y-2 shadow-2xs hover:border-indigo-300 transition-colors"
                      >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                          <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                              <User class="w-4 h-4" />
                            </div>
                            <div>
                              <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">{{ member.name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ member.username }}</span>
                                <span :class="['px-1.5 py-0.2 text-[8px] font-extrabold rounded border uppercase', getBadgeColor(member.package_name)]">
                                  {{ member.package_name }}
                                </span>
                              </div>
                              <p class="text-[10px] text-slate-500">
                                Sponsor: <strong class="text-slate-700">{{ member.parent_name }}</strong> &bull; Join: {{ member.joined_at }}
                              </p>
                            </div>
                          </div>

                          <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                            <!-- Toggle G2 Subtree Folder -->
                            <button 
                              v-if="member.direct_count > 0"
                              @click="toggleMemberSubtree(member.id)"
                              class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg transition-colors flex items-center gap-1 cursor-pointer"
                            >
                              <FolderOpen class="w-3 h-3 text-amber-600" />
                              <span>G2 ({{ member.direct_count }} Mitra)</span>
                              <ChevronDown v-if="openSubtrees[member.id]" class="w-3 h-3" />
                              <ChevronRight v-else class="w-3 h-3" />
                            </button>

                            <!-- Re-focus Button -->
                            <button 
                              @click="focusUser(member.id)"
                              class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-[10px] font-bold rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer"
                            >
                              <span>Fokus ke Member Ini</span>
                              <ArrowRight class="w-3 h-3" />
                            </button>
                          </div>
                        </div>

                        <!-- SUB-TREE NODE: G2 (Direct Downlines under this member) -->
                        <div v-if="openSubtrees[member.id] && member.children && member.children.length > 0" class="pl-6 pt-2 border-t border-slate-100 space-y-2">
                          <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                            <Folder class="w-3 h-3 text-amber-500" />
                            <span>Daftar Downline Langsung dari {{ member.name }}:</span>
                          </div>

                          <div 
                            v-for="child in member.children" 
                            :key="child.id"
                            class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between text-xs"
                          >
                            <div class="flex items-center gap-2">
                              <UserCheck class="w-3.5 h-3.5 text-emerald-600" />
                              <div>
                                <span class="font-bold text-slate-800">{{ child.name }}</span>
                                <span class="text-[10px] text-slate-400 ml-1.5 font-mono">{{ child.username }}</span>
                              </div>
                            </div>

                            <div class="flex items-center gap-2">
                              <span class="text-[10px] font-bold text-slate-500 font-mono">{{ child.direct_count }} Downline</span>
                              <button 
                                @click="focusUser(child.id)"
                                class="px-2 py-0.5 bg-white border border-slate-200 hover:bg-indigo-600 hover:text-white text-slate-700 text-[9px] font-bold rounded transition-colors"
                              >
                                Lihat
                              </button>
                            </div>
                          </div>
                        </div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </div>

          </div>
        </div>

        <!-- RIGHT: Generation Depth Breakdown Summary (4 Cols) -->
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
                @click="openGenerations[gen.generation] = true"
                class="p-3 bg-slate-50/70 hover:bg-indigo-50/50 border border-slate-100 hover:border-indigo-200 rounded-xl flex items-center justify-between cursor-pointer transition-colors"
              >
                <div class="flex items-center gap-2">
                  <Folder class="w-3.5 h-3.5 text-indigo-500" />
                  <span class="text-xs font-bold text-slate-700">{{ gen.label }}</span>
                </div>
                <span :class="[gen.count > 0 ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : 'bg-slate-100 text-slate-400 border-slate-200', 'px-2.5 py-0.5 text-xs font-extrabold rounded-full font-mono border']">
                  {{ gen.count }} Member
                </span>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </AdminLayout>
</template>
