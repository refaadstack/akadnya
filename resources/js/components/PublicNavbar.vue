<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ShoppingCart, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
    canRegister?: boolean;
    currentPage?: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const cartCount = computed(() => (page.props.cartCount as number) || 0);

const mobileMenuOpen = ref(false);
</script>

<template>
    <nav class="fixed top-3 right-3 left-3 z-50 md:top-5 md:right-5 md:left-5">
        <div
            class="mx-auto flex max-w-6xl items-center justify-between gap-3 rounded-full border border-white/10 bg-[var(--ink)] px-3 py-2 shadow-lg shadow-black/20 md:px-5 md:py-2.5"
        >
            <Link
                href="/"
                class="flex shrink-0 items-center"
                aria-label="Akadnya.com - Beranda"
            >
                <img
                    src="/images/logo.svg"
                    alt="Akadnya.com"
                    class="h-8 w-auto md:h-9"
                />
            </Link>

            <div class="hidden items-center gap-6 md:flex">
                <Link
                    href="/templates"
                    class="text-sm font-semibold transition"
                    :class="
                        currentPage === 'templates'
                            ? 'text-[var(--gold-light)]'
                            : 'text-white/85 hover:text-[var(--gold-light)]'
                    "
                >
                    Koleksi
                </Link>
                <Link
                    href="/produk"
                    class="text-sm font-semibold transition"
                    :class="
                        currentPage === 'products'
                            ? 'text-[var(--gold-light)]'
                            : 'text-white/85 hover:text-[var(--gold-light)]'
                    "
                >
                    Produk
                </Link>
                <Link
                    href="/#how-it-works"
                    class="text-sm font-semibold text-white/85 transition hover:text-[var(--gold-light)]"
                >
                    Cara Pesan
                </Link>
                <Link
                    href="/tutorial"
                    class="text-sm font-semibold transition"
                    :class="
                        currentPage === 'tutorial'
                            ? 'text-[var(--gold-light)]'
                            : 'text-white/85 hover:text-[var(--gold-light)]'
                    "
                >
                    Tutorial
                </Link>
                <Link
                    href="/faq"
                    class="text-sm font-semibold transition"
                    :class="
                        currentPage === 'faq'
                            ? 'text-[var(--gold-light)]'
                            : 'text-white/85 hover:text-[var(--gold-light)]'
                    "
                >
                    FAQ
                </Link>
            </div>

            <div class="hidden items-center gap-2 md:flex">
                <Link
                    href="/keranjang"
                    class="relative inline-flex size-9 items-center justify-center rounded-full text-white/85 transition hover:bg-white/10 hover:text-[var(--gold-light)]"
                    aria-label="Keranjang"
                >
                    <ShoppingCart class="size-5" />
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-0.5 -right-0.5 grid min-w-4.5 place-items-center rounded-full bg-[var(--gold-light)] px-1 text-[0.65rem] font-bold text-[var(--ink)]"
                    >
                        {{ cartCount > 99 ? '99+' : cartCount }}
                    </span>
                </Link>
                <Link
                    v-if="user"
                    href="/dashboard"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold text-white/85 transition hover:bg-white/10 hover:text-[var(--gold-light)]"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold text-white/85 transition hover:bg-white/10 hover:text-[var(--gold-light)]"
                >
                    Masuk
                </Link>
                <Link
                    v-if="canRegister"
                    href="/register"
                    class="rounded-full bg-[var(--gold-light)] px-4 py-1.5 text-sm font-bold text-[var(--ink)] transition hover:bg-white"
                >
                    Mulai Desain
                </Link>
                <Link
                    v-else
                    href="/templates"
                    class="rounded-full bg-[var(--gold-light)] px-4 py-1.5 text-sm font-bold text-[var(--ink)] transition hover:bg-white"
                >
                    Mulai Desain
                </Link>
            </div>

            <button
                class="inline-flex size-9 items-center justify-center rounded-full text-white/85 transition hover:bg-white/10 md:hidden"
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
            class="mx-auto mt-2 max-w-6xl rounded-2xl border border-[var(--my-border)]/60 bg-[var(--my-background)]/95 p-4 shadow-lg shadow-black/20 backdrop-blur-md md:hidden"
        >
            <div class="grid gap-1">
                <Link
                    href="/templates"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Koleksi
                </Link>
                <Link
                    href="/produk"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Produk
                </Link>
                <Link
                    href="/#how-it-works"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Cara Pesan
                </Link>
                <Link
                    href="/tutorial"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Tutorial
                </Link>
                <Link
                    href="/faq"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    FAQ
                </Link>
                <Link
                    href="/keranjang"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    <ShoppingCart class="size-5" />
                    Keranjang
                    <span
                        v-if="cartCount > 0"
                        class="grid min-w-5 place-items-center rounded-full bg-[var(--my-primary)] px-1 text-xs font-bold text-white"
                    >
                        {{ cartCount > 99 ? '99+' : cartCount }}
                    </span>
                </Link>
                <Link
                    v-if="user"
                    href="/dashboard"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="rounded-lg px-3 py-2 font-semibold text-[var(--my-neutral)] hover:bg-[var(--my-primary)]/10"
                    @click="mobileMenuOpen = false"
                >
                    Masuk
                </Link>
                <Link
                    v-if="canRegister"
                    href="/register"
                    class="mt-1 rounded-full bg-[var(--my-primary)] px-4 py-2 text-center font-bold text-white"
                    @click="mobileMenuOpen = false"
                >
                    Mulai Desain
                </Link>
            </div>
        </div>
    </nav>
</template>
