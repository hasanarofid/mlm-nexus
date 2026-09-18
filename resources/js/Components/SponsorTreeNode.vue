<script setup>
import { ref, computed } from 'vue';
import { 
  ChevronDown, 
  ChevronRight, 
  User, 
  ArrowRight,
  Sparkles,
  Info
} from '@lucide/vue';

const props = defineProps({
  node: {
    type: Object,
    required: true
  },
  isRoot: {
    type: Boolean,
    default: false
  },
  expandedMap: {
    type: Object,
    default: () => ({})
  },
  depth: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['toggle-node', 'focus-user', 'show-detail']);

const hasChildren = computed(() => {
  return props.node.children && props.node.children.length > 0;
});

const isExpanded = computed(() => {
  if (props.isRoot) return true;
  // If explicitly tracked in expandedMap, use that, otherwise default to true for top 2 levels
  if (props.expandedMap[props.node.id] !== undefined) {
    return props.expandedMap[props.node.id];
  }
  return props.depth <= 2;
});

const toggleExpand = () => {
  if (hasChildren.value) {
    emit('toggle-node', props.node.id);
  }
};

const getGenBadgeClass = (gen) => {
  if (gen === 0) return 'bg-amber-400 text-slate-950 font-black';
  if (gen === 1) return 'bg-emerald-100 text-emerald-800 border-emerald-300 font-extrabold';
  if (gen === 2) return 'bg-blue-100 text-blue-800 border-blue-300 font-extrabold';
  if (gen === 3) return 'bg-indigo-100 text-indigo-800 border-indigo-300 font-extrabold';
  if (gen === 4) return 'bg-purple-100 text-purple-800 border-purple-300 font-extrabold';
  return 'bg-slate-100 text-slate-700 border-slate-300 font-bold';
};
</script>

<template>
  <div class="tree-node-wrapper select-none">
    <!-- Main Node Item -->
    <div class="flex items-center gap-2 group relative">
      
      <!-- Node Pill Card matching exact mockup -->
      <div 
        @click="toggleExpand"
        :class="[
          'inline-flex items-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl border transition-all duration-150',
          isRoot 
            ? 'bg-white text-slate-900 border-slate-300 shadow-md ring-2 ring-amber-400/40' 
            : 'bg-white text-slate-800 border-slate-200 shadow-xs hover:border-indigo-400 hover:shadow-md cursor-pointer'
        ]"
      >
        <!-- Toggle Caret Icon -->
        <button 
          type="button"
          class="p-0.5 text-slate-400 hover:text-slate-700 transition-colors focus:outline-none flex items-center justify-center shrink-0"
          :class="{ 'cursor-pointer': hasChildren, 'opacity-40 cursor-default': !hasChildren }"
        >
          <ChevronDown v-if="hasChildren && isExpanded" class="w-4 h-4 text-slate-500 transition-transform" />
          <ChevronRight v-else-if="hasChildren && !isExpanded" class="w-4 h-4 text-slate-500 transition-transform" />
          <span v-else class="w-2 h-2 rounded-full bg-slate-300 mx-1"></span>
        </button>

        <!-- Member Username & Name -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-xs sm:text-sm font-black tracking-tight text-slate-900">
            {{ node.username || node.name }}
          </span>

          <!-- Mitra Count: (X mitra) -->
          <span class="text-xs font-semibold text-slate-500 font-sans">
            ({{ node.total_downlines ?? 0 }} mitra)
          </span>
        </div>

        <!-- Generation Badge -->
        <span 
          :class="[
            'text-[9px] px-1.5 py-0.5 rounded border uppercase tracking-wider ml-1',
            getGenBadgeClass(node.generation)
          ]"
        >
          {{ isRoot ? 'Root' : `Gen ${node.generation}` }}
        </span>

        <!-- Quick Focus Button (Hidden on root, shown on hover/touch for children) -->
        <button 
          v-if="!isRoot"
          type="button"
          @click.stop="emit('focus-user', node.id)"
          title="Fokus ke jaringan mitra ini"
          class="opacity-0 group-hover:opacity-100 transition-opacity ml-1.5 px-2 py-0.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-600 text-[10px] font-bold rounded-md border border-indigo-200 flex items-center gap-1 cursor-pointer"
        >
          <span>Fokus</span>
          <ArrowRight class="w-2.5 h-2.5" />
        </button>
      </div>

    </div>

    <!-- Recursive Children Subtree with Hierarchical Branch Lines -->
    <div 
      v-if="hasChildren && isExpanded" 
      class="tree-children-container relative pl-6 sm:pl-8 mt-2 space-y-2"
    >
      <!-- Vertical Connecting Line -->
      <div class="tree-vertical-line absolute left-3 sm:left-4 top-0 bottom-4 w-[2px] bg-slate-500/50"></div>

      <!-- Child Nodes Loop -->
      <div 
        v-for="child in node.children" 
        :key="child.id"
        class="tree-item-branch relative"
      >
        <!-- Horizontal Connecting Line to Child Node -->
        <div class="tree-horizontal-line absolute left-[-12px] sm:left-[-16px] top-[18px] w-[12px] sm:w-[16px] h-[2px] bg-slate-500/50"></div>

        <!-- Recursive Component -->
        <SponsorTreeNode 
          :node="child"
          :is-root="false"
          :expanded-map="expandedMap"
          :depth="depth + 1"
          @toggle-node="(id) => emit('toggle-node', id)"
          @focus-user="(id) => emit('focus-user', id)"
          @show-detail="(n) => emit('show-detail', n)"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.tree-node-wrapper {
  position: relative;
}
</style>
