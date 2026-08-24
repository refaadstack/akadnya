<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    KeyRound,
    LogIn,
    Mail,
} from 'lucide-vue-next';
import PublicNavbar from '@/components/PublicNavbar.vue';

defineOptions({
    // Inertia v3: `layout: undefined` still falls through to the global
    // layout callback; return null explicitly to opt out.
    layout: () => null,
});

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <div class="my-page">
        <Head title="Lupa Password" />

        <PublicNavbar />

        <main
            class="my-container grid min-h-screen items-center gap-10 pt-28 pb-16 lg:grid-cols-[0.95fr_1.05fr]"
        >
            <section class="hidden lg:block">
                <p class="my-label mb-4">Pemulihan Akun</p>
                <h1 class="my-heading max-w-xl text-5xl leading-tight">
                    Lupa password? Tenang, akunmu tetap aman.
                </h1>
                <p class="my-copy mt-5 max-w-lg">
                    Masukkan email yang kamu daftarkan dan kami akan mengirim
                    tautan untuk membuat password baru.
                </p>

                <div class="mt-8 grid max-w-md gap-4">
                    <div class="my-card flex gap-4 p-5">
                        <Clock3
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Link reset dikirim langsung ke email kamu dan hanya
                            berlaku sementara demi keamanan.
                        </p>
                    </div>
                    <div class="my-card flex gap-4 p-5">
                        <LogIn
                            class="mt-1 size-5 shrink-0 text-[var(--my-primary)]"
                        />
                        <p class="text-[var(--my-muted)]">
                            Setelah password baru dibuat, kamu langsung bisa
                            masuk kembali ke Studio.
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
                            <KeyRound class="size-8 text-[var(--my-primary)]" />
                        </div>

                        <h2 class="my-heading mt-5 text-3xl">Lupa password?</h2>
                        <p class="mt-2 text-[var(--my-muted)]">
                            Masukkan email akunmu dan kami akan mengirimkan link
                            untuk reset password.
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

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? 'Mengirim...'
                                    : 'Kirim Link Reset Password'
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
