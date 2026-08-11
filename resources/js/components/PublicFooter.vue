<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const support = usePage().props.support;
</script>

<template>
    <footer
        class="border-t border-[var(--my-border)] bg-[var(--my-surface-soft)]/80 py-12"
    >
        <div class="my-container">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <Link
                        href="/"
                        class="font-display text-3xl font-bold text-[var(--my-primary)]"
                    >
                        MyAkad
                    </Link>
                    <p
                        class="mt-4 max-w-sm text-sm leading-6 text-[var(--my-muted)]"
                    >
                        Undangan digital pernikahan dengan template yang bisa
                        dipreview, dibeli, diisi, lalu dipublish dengan alur
                        yang rapi.
                    </p>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--my-primary)] uppercase"
                    >
                        Produk
                    </h4>
                    <ul class="grid gap-2 text-sm text-[var(--my-muted)]">
                        <li>
                            <Link
                                href="/templates"
                                class="hover:text-[var(--my-primary)]"
                                >Koleksi Template</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/#how-it-works"
                                class="hover:text-[var(--my-primary)]"
                                >Cara Pesan</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/#pricing"
                                class="hover:text-[var(--my-primary)]"
                                >Harga</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--my-primary)] uppercase"
                    >
                        Bantuan
                    </h4>
                    <ul class="grid gap-2 text-sm text-[var(--my-muted)]">
                        <li>
                            <Link
                                href="/faq"
                                class="hover:text-[var(--my-primary)]"
                                >FAQ</Link
                            >
                        </li>
                        <li>
                            <a
                                :href="`mailto:${support.email}`"
                                class="hover:text-[var(--my-primary)]"
                                >Support</a
                            >
                        </li>
                        <li>
                            <a
                                :href="`https://wa.me/${support.whatsapp}`"
                                target="_blank"
                                class="hover:text-[var(--my-primary)]"
                                >WhatsApp</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--my-primary)] uppercase"
                    >
                        Legal
                    </h4>
                    <ul class="grid gap-2 text-sm text-[var(--my-muted)]">
                        <li>
                            <Link
                                href="/privacy"
                                class="hover:text-[var(--my-primary)]"
                                >Kebijakan Privasi</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/terms"
                                class="hover:text-[var(--my-primary)]"
                                >Syarat & Ketentuan</Link
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-10 border-t border-[var(--my-border)] pt-6 text-sm text-[var(--my-muted)]"
            >
                © {{ new Date().getFullYear() }} MyAkad by Refaadstack.
            </div>
        </div>
    </footer>
</template>
