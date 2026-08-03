<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    canRegister?: boolean;
    currentPage?: string;
}>();

const mobileMenuOpen = ref(false);
</script>

<template>
    <nav
        class="fixed top-0 right-0 left-0 z-50 border-b border-[var(--my-border)]/60 bg-[var(--my-background)]/86 backdrop-blur-md"
    >
        <div class="my-container">
            <div class="flex min-h-16 items-center justify-between">
                <Link
                    href="/"
                    class="font-display text-3xl leading-none font-bold text-[var(--my-primary)]"
                >
                    MyAkad
                </Link>

                <div class="hidden items-center gap-8 md:flex">
                    <Link
                        href="/templates"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'templates'
                                ? 'border-b-2 border-[var(--my-primary)] text-[var(--my-primary)]'
                                : 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                        "
                    >
                        Koleksi
                    </Link>
                    <Link
                        href="/produk"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'products'
                                ? 'border-b-2 border-[var(--my-primary)] text-[var(--my-primary)]'
                                : 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                        "
                    >
                        Produk
                    </Link>
                    <Link
                        href="/#how-it-works"
                        class="text-sm font-semibold text-[var(--my-neutral)] transition hover:text-[var(--my-primary)]"
                    >
                        Cara Pesan
                    </Link>
                    <Link
                        href="/faq"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'faq'
                                ? 'border-b-2 border-[var(--my-primary)] text-[var(--my-primary)]'
                                : 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                        "
                    >
                        FAQ
                    </Link>
                </div>

                <div class="hidden items-center gap-3 md:flex">
                    <Link
                        href="/login"
                        class="px-3 py-2 text-sm font-semibold text-[var(--my-neutral)] transition hover:text-[var(--my-primary)]"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="my-btn-primary min-h-10 px-5"
                    >
                        Mulai Desain
                    </Link>
                    <Link
                        v-else
                        href="/templates"
                        class="my-btn-primary min-h-10 px-5"
                    >
                        Mulai Desain
                    </Link>
                </div>

                <button
                    class="inline-flex size-10 items-center justify-center rounded-lg border border-[var(--my-border)] text-[var(--my-neutral)] md:hidden"
                    type="button"
                    aria-label="Buka menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <Menu v-if="!mobileMenuOpen" class="size-5" />
                    <X v-else class="size-5" />
                </button>
            </div>

            <div
                v-if="mobileMenuOpen"
                class="grid gap-3 border-t border-[var(--my-border)]/70 py-4 md:hidden"
            >
                <Link
                    href="/templates"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Koleksi
                </Link>
                <Link
                    href="/produk"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Produk
                </Link>
                <Link
                    href="/#how-it-works"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Cara Pesan
                </Link>
                <Link
                    href="/faq"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    FAQ
                </Link>
                <Link
                    href="/login"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Masuk
                </Link>
                <Link
                    v-if="canRegister"
                    href="/register"
                    class="my-btn-primary mt-2"
                    @click="mobileMenuOpen = false"
                >
                    Mulai Desain
                </Link>
            </div>
        </div>
    </nav>
</template>
