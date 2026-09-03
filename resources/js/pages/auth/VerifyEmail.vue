<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, MailCheck } from 'lucide-vue-next';
import PublicNavbar from '@/components/PublicNavbar.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    // Inertia v3: `layout: undefined` still falls through to the global
    // layout callback; return null explicitly to opt out.
    layout: () => null,
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <div class="my-page">
        <Head title="Verifikasi Email" />

        <PublicNavbar />

        <main
            class="my-container flex min-h-screen items-center justify-center px-4 pt-28 pb-16"
        >
            <section class="w-full max-w-md">
                <div class="my-card p-7 md:p-8">
                    <div class="mb-8 text-center">
                        <Link
                            href="/"
                            class="font-display text-4xl font-bold text-[var(--my-primary)]"
                            >Akadnya.com</Link
                        >

                        <div
                            class="mx-auto mt-6 flex size-16 items-center justify-center rounded-full bg-[var(--my-primary)]/10"
                        >
                            <MailCheck
                                class="size-8 text-[var(--my-primary)]"
                            />
                        </div>

                        <h2 class="my-heading mt-5 text-3xl">
                            Verifikasi email kamu
                        </h2>
                        <p class="mt-3 text-[var(--my-muted)]">
                            Kami sudah mengirim link verifikasi ke alamat email
                            kamu. Klik tombol di email untuk mengaktifkan akun.
                        </p>
                    </div>

                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mb-6 flex gap-3 rounded-lg border border-[var(--my-primary)]/25 bg-[var(--my-primary)]/10 px-4 py-3 text-sm text-[var(--my-neutral)]"
                    >
                        <CheckCircle2
                            class="mt-0.5 size-4 shrink-0 text-[var(--my-primary)]"
                        />
                        <span
                            >Link verifikasi baru sudah dikirim ke email
                            kamu.</span
                        >
                    </div>

                    <Form
                        v-bind="send.form()"
                        class="grid gap-4"
                        v-slot="{ processing }"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                processing
                                    ? 'Mengirim...'
                                    : 'Kirim Ulang Email Verifikasi'
                            }}
                        </button>
                    </Form>

                    <p class="mt-4 text-center text-sm text-[var(--my-muted)]">
                        Tidak menemukan emailnya? Cek folder spam atau promosi.
                    </p>
                </div>

                <div class="mt-6 text-center">
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="text-sm font-bold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]"
                    >
                        Keluar
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>
