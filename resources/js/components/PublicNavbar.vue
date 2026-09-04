<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ShoppingCart, X } from 'lucide-vue-next';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';

defineProps<{
    canRegister?: boolean;
    currentPage?: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const cartCount = computed(() => (page.props.cartCount as number) || 0);

const mobileMenuOpen = ref(false);

const scrolled = ref(false);
const onScroll = () => {
    scrolled.value = window.scrollY > 40;
};
onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>

<template>
    <nav
        class="fixed top-0 right-0 left-0 z-50 transition-colors duration-300"
        :class="
            scrolled
                ? 'border-b border-[var(--my-border)]/60 bg-[var(--my-background)]/86 backdrop-blur-md'
                : 'border-b border-transparent bg-transparent'
        "
    >
        <div class="my-container">
            <div class="flex min-h-16 items-center justify-between">
                <Link
                    href="/"
                    class="flex items-center"
                    aria-label="Akadnya.com - Beranda"
                >
                    <img
                        v-if="!scrolled"
                        src="/images/logo.svg"
                        alt="Akadnya.com"
                        class="h-9 w-auto"
                    />
                    <span
                        v-else
                        class="font-display text-2xl leading-none font-bold text-[var(--my-primary)]"
                    >
                        Akadnya<span class="text-[var(--my-secondary)]">.com</span>
                    </span>
                </Link>

                <div class="hidden items-center gap-8 md:flex">
                    <Link
                        href="/templates"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'templates'
                                ? 'border-b-2 border-[var(--gold-light)] text-[var(--gold-light)]'
                                : scrolled
                                  ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                  : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Koleksi
                    </Link>
                    <Link
                        href="/produk"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'products'
                                ? 'border-b-2 border-[var(--gold-light)] text-[var(--gold-light)]'
                                : scrolled
                                  ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                  : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Produk
                    </Link>
                    <Link
                        href="/#how-it-works"
                        class="text-sm font-semibold transition"
                        :class="
                            scrolled
                                ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Cara Pesan
                    </Link>
                    <Link
                        href="/tutorial"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'tutorial'
                                ? 'border-b-2 border-[var(--gold-light)] text-[var(--gold-light)]'
                                : scrolled
                                  ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                  : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Tutorial
                    </Link>
                    <Link
                        href="/faq"
                        class="text-sm font-semibold transition"
                        :class="
                            currentPage === 'faq'
                                ? 'border-b-2 border-[var(--gold-light)] text-[var(--gold-light)]'
                                : scrolled
                                  ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                  : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        FAQ
                    </Link>
                </div>

                <div class="hidden items-center gap-3 md:flex">
                    <Link
                        href="/keranjang"
                        class="relative inline-flex size-10 items-center justify-center rounded-lg border transition"
                        :class="
                            scrolled
                                ? 'border-[var(--my-border)] text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                : 'border-white/20 text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                        aria-label="Keranjang"
                    >
                        <ShoppingCart class="size-5" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-1.5 -right-1.5 grid min-w-5 place-items-center rounded-full bg-[var(--gold-light)] px-1 text-[0.65rem] font-bold text-[var(--ink)]"
                        >
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>
                    <Link
                        v-if="user"
                        href="/dashboard"
                        class="px-3 py-2 text-sm font-semibold transition"
                        :class="
                            scrolled
                                ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="px-3 py-2 text-sm font-semibold transition"
                        :class="
                            scrolled
                                ? 'text-[var(--my-neutral)] hover:text-[var(--my-primary)]'
                                : 'text-[var(--text-light)] hover:text-[var(--gold-light)]'
                        "
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="min-h-10 px-5"
                        :class="
                            scrolled
                                ? 'my-btn-primary'
                                : 'inline-flex items-center justify-center rounded-lg bg-[var(--gold-light)] px-5 text-sm font-bold text-[var(--ink)] transition hover:bg-[var(--ink-2)] hover:text-[var(--text-light)]'
                        "
                    >
                        Mulai Desain
                    </Link>
                    <Link
                        v-else
                        href="/templates"
                        class="min-h-10 px-5"
                        :class="
                            scrolled
                                ? 'my-btn-primary'
                                : 'inline-flex items-center justify-center rounded-lg bg-[var(--gold-light)] px-5 text-sm font-bold text-[var(--ink)] transition hover:bg-[var(--ink-2)] hover:text-[var(--text-light)]'
                        "
                    >
                        Mulai Desain
                    </Link>
                </div>

                <button
                    class="inline-flex size-10 items-center justify-center rounded-lg border transition md:hidden"
                    :class="
                        scrolled
                            ? 'border-[var(--my-border)] text-[var(--my-neutral)]'
                            : 'border-white/20 text-[var(--text-light)]'
                    "
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
                class="grid gap-3 border-t border-[var(--my-border)]/70 bg-[var(--my-background)] py-4 md:hidden"
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
                    href="/tutorial"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Tutorial
                </Link>
                <Link
                    href="/faq"
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    FAQ
                </Link>
                <Link
                    href="/keranjang"
                    class="flex items-center gap-2 py-2 font-semibold text-[var(--my-neutral)]"
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
                    class="py-2 font-semibold text-[var(--my-neutral)]"
                    @click="mobileMenuOpen = false"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
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
