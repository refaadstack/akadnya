<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const support = usePage().props.support;
</script>

<template>
    <footer class="border-t border-white/10 bg-[var(--ink)] py-12">
        <div class="my-container">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <Link
                        href="/"
                        class="font-display text-3xl font-bold text-[var(--gold-light)]"
                    >
                        Akadnya.com
                    </Link>
                    <p
                        class="mt-4 max-w-sm text-sm leading-6 text-[var(--text-light-muted)]"
                    >
                        Undangan digital pernikahan dengan template yang bisa
                        dipreview, dibeli, diisi, lalu dipublish dengan alur
                        yang rapi.
                    </p>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--gold-light)] uppercase"
                    >
                        Produk
                    </h4>
                    <ul
                        class="grid gap-2 text-sm text-[var(--text-light-muted)]"
                    >
                        <li>
                            <Link
                                href="/templates"
                                class="hover:text-[var(--gold-light)]"
                                >Koleksi Template</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/#how-it-works"
                                class="hover:text-[var(--gold-light)]"
                                >Cara Pesan</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/#pricing"
                                class="hover:text-[var(--gold-light)]"
                                >Harga</Link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--gold-light)] uppercase"
                    >
                        Bantuan
                    </h4>
                    <ul
                        class="grid gap-2 text-sm text-[var(--text-light-muted)]"
                    >
                        <li>
                            <Link
                                href="/tutorial"
                                class="hover:text-[var(--gold-light)]"
                                >Tutorial</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/faq"
                                class="hover:text-[var(--gold-light)]"
                                >FAQ</Link
                            >
                        </li>
                        <li>
                            <a
                                :href="`mailto:${support.email}`"
                                class="hover:text-[var(--gold-light)]"
                                >Support</a
                            >
                        </li>
                        <li>
                            <a
                                :href="`https://wa.me/${support.whatsapp}`"
                                target="_blank"
                                class="hover:text-[var(--gold-light)]"
                                >WhatsApp</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4
                        class="mb-4 text-sm font-bold tracking-[0.16em] text-[var(--gold-light)] uppercase"
                    >
                        Legal
                    </h4>
                    <ul
                        class="grid gap-2 text-sm text-[var(--text-light-muted)]"
                    >
                        <li>
                            <Link
                                href="/privacy"
                                class="hover:text-[var(--gold-light)]"
                                >Kebijakan Privasi</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/terms"
                                class="hover:text-[var(--gold-light)]"
                                >Syarat & Ketentuan</Link
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-10 border-t border-white/10 pt-6 text-sm text-[var(--text-light-muted)]"
            >
                © {{ new Date().getFullYear() }} Akadnya.com by Refaadstack.
            </div>
        </div>
    </footer>
</template>
