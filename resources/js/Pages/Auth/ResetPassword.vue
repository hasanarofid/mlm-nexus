<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, Check, ArrowLeft } from '@lucide/vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Atur Ulang Password | Nexus Community" />

        <div class="panel-heading">
            <span class="eyebrow">RESET PASSWORD</span>
            <h2>Atur Password Baru</h2>
            <p>Silakan buat password baru yang aman untuk akun Anda.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Field (Readonly) -->
            <div class="form-group">
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Email Akun</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Mail class="w-4.5 h-4.5 text-slate-400" />
                    </span>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        readonly
                        autocomplete="email"
                        class="w-full bg-slate-50 font-medium text-slate-600"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password Baru -->
            <div class="form-group">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Password Baru</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Lock class="w-4.5 h-4.5 text-slate-400" />
                    </span>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="auth-password-toggle flex items-center justify-center"
                        title="Tampilkan password"
                    >
                        <Eye v-if="!showPassword" class="w-4.5 h-4.5" />
                        <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="form-group">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Konfirmasi Password Baru</label>
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
                        placeholder="Ulangi password baru"
                        class="w-full"
                    />
                    <button
                        type="button"
                        @click="showPasswordConfirm = !showPasswordConfirm"
                        class="auth-password-toggle flex items-center justify-center"
                        title="Tampilkan konfirmasi password"
                    >
                        <Eye v-if="!showPasswordConfirm" class="w-4.5 h-4.5" />
                        <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full mt-4 flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-950 disabled:opacity-50"
            >
                <span v-if="form.processing">Menyimpan Password...</span>
                <span v-else>Simpan Password Baru</span>
                <Check class="w-4 h-4 stroke-[3]" />
            </button>
        </form>

        <div class="mt-6 text-center">
            <Link :href="route('login')" class="inline-flex items-center gap-2 text-xs font-bold text-[#B8922E] hover:text-[#0F172A] transition-colors">
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke halaman login</span>
            </Link>
        </div>
    </GuestLayout>
</template>
