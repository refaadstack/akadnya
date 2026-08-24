<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, LockKeyhole, Mail } from 'lucide-vue-next';
import PublicNavbar from '@/components/PublicNavbar.vue';

defineOptions({
    // Inertia v3: `layout: undefined` still falls through to the global
    // layout callback; return null explicitly to opt out.
    layout: () => null,
});

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <div class="my-page">
        <Head title="Masuk" />

        <PublicNavbar />

        <main
            class="my-container grid min-h-screen items-center gap-10 pt-28 pb-16 lg:grid-cols-[0.95fr_1.05fr]"
        >
            <section class="hidden lg:block">
                <p class="my-label mb-4">Masuk ke Studio</p>
                <h1 class="my-heading max-w-xl text-5xl leading-tight">
                    Lanjutkan undangan yang sudah kamu mulai.
                </h1>
                <p class="my-copy mt-5 max-w-lg">
                    Kelola template, isi detail acara, pantau RSVP, dan publish
                    undangan dari satu ruang kerja yang rapi.
                </p>

                <div class="mt-8 grid max-w-md gap-4">
                    <div class="my-card flex gap-4 p-5">
                        <CheckCircle2
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Data undangan, galeri, tamu, dan pengaturan
                            tersimpan aman di akunmu.
                        </p>
                    </div>
                    <div class="my-card flex gap-4 p-5">
                        <CheckCircle2
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Preview hasil undangan kapan pun sebelum dibagikan
                            ke tamu.
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
                        <h2 class="my-heading mt-6 text-3xl">
                            Selamat datang kembali
                        </h2>
                        <p class="mt-2 text-[var(--my-muted)]">
                            Masuk untuk melanjutkan undangan digitalmu.
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-6 flex gap-3 rounded-lg border border-[var(--my-primary)]/25 bg-[var(--my-primary)]/10 px-4 py-3 text-sm text-[var(--my-neutral)]"
                    >
                        <CheckCircle2
                            class="mt-0.5 size-4 shrink-0 text-[var(--my-primary)]"
                        />
                        <span>{{ status }}</span>
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
                                    required
                                    autofocus
                                    autocomplete="username"
                                    class="my-input min-h-12 pr-4 pl-11"
                                    :class="{
                                        'border-red-500': form.errors.email,
                                    }"
                                    placeholder="nama@email.com"
                                />
                            </div>
                            <p
                                v-if="form.errors.email"
                                class="mt-2 text-sm font-semibold text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="mb-2 block text-sm font-bold text-[var(--my-neutral)]"
                                >Password</label
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
                                    autocomplete="current-password"
                                    class="my-input min-h-12 pr-4 pl-11"
                                    :class="{
                                        'border-red-500': form.errors.password,
                                    }"
                                    placeholder="Password akun"
                                />
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="mt-2 text-sm font-semibold text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <label
                                class="flex items-center gap-2 text-sm font-semibold text-[var(--my-muted)]"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="size-4 rounded border-[var(--my-border)] text-[var(--my-primary)] focus:ring-[var(--my-primary)]"
                                />
                                Ingat saya
                            </label>

                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm font-bold text-[var(--my-primary)]"
                            >
                                Lupa password?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </button>
                    </form>

                    <p class="mt-6 text-center text-[var(--my-muted)]">
                        Belum punya akun?
                        <Link
                            href="/register"
                            class="font-bold text-[var(--my-primary)]"
                            >Daftar sekarang</Link
                        >
                    </p>
                </div>

                <div class="mt-6 text-center">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 text-sm font-bold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke beranda
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>
