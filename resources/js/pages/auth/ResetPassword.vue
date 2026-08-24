<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    LockKeyhole,
    Mail,
    ShieldCheck,
} from 'lucide-vue-next';
import PublicNavbar from '@/components/PublicNavbar.vue';

defineOptions({
    layout: undefined,
});

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <div class="my-page">
        <Head title="Reset Password" />

        <PublicNavbar />

        <main
            class="my-container grid min-h-screen items-center gap-10 pt-28 pb-16 lg:grid-cols-[0.95fr_1.05fr]"
        >
            <section class="hidden lg:block">
                <p class="my-label mb-4">Password Baru</p>
                <h1 class="my-heading max-w-xl text-5xl leading-tight">
                    Amankan kembali akunmu dengan password baru.
                </h1>
                <p class="my-copy mt-5 max-w-lg">
                    Buat password yang kuat dan belum pernah kamu pakai di
                    tempat lain agar akun undanganmu tetap terlindungi.
                </p>

                <div class="mt-8 grid max-w-md gap-4">
                    <div class="my-card flex gap-4 p-5">
                        <ShieldCheck
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Kombinasikan huruf, angka, dan simbol agar
                            passwordmu semakin kuat.
                        </p>
                    </div>
                    <div class="my-card flex gap-4 p-5">
                        <CheckCircle2
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Password baru langsung berlaku setelah disimpan,
                            tanpa perlu konfigurasi tambahan.
                        </p>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-md">
                <div class="my-card p-7 md:p-8">
                    <div class="mb-8 text-center">
                        <Link
                            href="/"
                            class="font-display text-4xl font-bold text-[var(--my-primary)]"
                            >MyAkad</Link
                        >

                        <div
                            class="mx-auto mt-6 flex size-16 items-center justify-center rounded-full bg-[var(--my-primary)]/10"
                        >
                            <LockKeyhole
                                class="size-8 text-[var(--my-primary)]"
                            />
                        </div>

                        <h2 class="my-heading mt-5 text-3xl">Reset password</h2>
                        <p class="mt-2 text-[var(--my-muted)]">
                            Buat password baru untuk menggantikan yang lama.
                        </p>
                    </div>

                    <form class="grid gap-5" @submit.prevent="submit">
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-bold text-[var(--my-neutral)]"
                                >Email</label
                            >
                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]"
                                />
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    readonly
                                    autocomplete="username"
                                    class="my-input min-h-12 pr-4 pl-11 opacity-70"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="mb-2 block text-sm font-bold text-[var(--my-neutral)]"
                                >Password Baru</label
                            >
                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]"
                                />
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autofocus
                                    autocomplete="new-password"
                                    class="my-input min-h-12 pr-4 pl-11"
                                    :class="{
                                        'border-red-500': form.errors.password,
                                    }"
                                    placeholder="Minimal 8 karakter"
                                />
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="mt-2 text-sm font-semibold text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="mb-2 block text-sm font-bold text-[var(--my-neutral)]"
                                >Konfirmasi Password Baru</label
                            >
                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]"
                                />
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    class="my-input min-h-12 pr-4 pl-11"
                                    :class="{
                                        'border-red-500':
                                            form.errors.password_confirmation,
                                    }"
                                    placeholder="Ulangi password baru"
                                />
                            </div>
                            <p
                                v-if="form.errors.password_confirmation"
                                class="mt-2 text-sm font-semibold text-red-600"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? 'Memproses...'
                                    : 'Simpan Password Baru'
                            }}
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 text-sm font-bold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke halaman login
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>
