<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, ArrowRight, Wallet, Users, ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
    allNotifications: {
        type: Array,
        required: true
    }
});

const getIcon = (type) => {
    switch(type) {
        case 'bonus': return Wallet;
        case 'network': return Users;
        case 'finance': return ArrowUpRight;
        default: return Bell;
    }
};

const getIconColor = (type) => {
    switch(type) {
        case 'bonus': return 'text-emerald-500 bg-emerald-100';
        case 'network': return 'text-indigo-500 bg-indigo-100';
        case 'finance': return 'text-amber-500 bg-amber-100';
        default: return 'text-slate-500 bg-slate-100';
    }
};
</script>

<template>
    <Head title="Semua Notifikasi Aktivitas" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Semua Notifikasi</h1>
                    <p class="text-sm text-slate-500 mt-1">Daftar lengkap laporan aktivitas, bonus, dan jaringan Anda.</p>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="bg-white border border-slate-200/90 rounded-3xl shadow-sm overflow-hidden">
                <div v-if="allNotifications.length > 0" class="divide-y divide-slate-100">
                    <div 
                        v-for="item in allNotifications" 
                        :key="item.id"
                        class="p-5 hover:bg-slate-50/80 transition-colors flex items-start gap-4"
                    >
                        <!-- Icon -->
                        <div 
                            class="w-12 h-12 rounded-full shrink-0 flex items-center justify-center shadow-inner"
                            :class="getIconColor(item.type)"
                        >
                            <component :is="getIcon(item.type)" class="w-5 h-5" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-bold text-slate-800 truncate pr-4">{{ item.title }}</h4>
                                <span class="text-xs font-semibold text-slate-400 whitespace-nowrap">{{ item.time }}</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                {{ item.message }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-20 px-4 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-4 shadow-inner">
                        <Bell class="w-8 h-8" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Belum ada notifikasi</h3>
                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Semua aktivitas bonus, laporan keuangan, dan jaringan Anda akan muncul di sini.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
