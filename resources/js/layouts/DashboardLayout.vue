<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronDown,
    ClipboardCheck,
    Image,
    LayoutDashboard,
    LockKeyhole,
    LogOut,
    Palette,
    PenLine,
    ReceiptText,
    Settings,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Component } from 'vue';

interface NavItem {
    label: string;
    href: string;
    icon: Component;
    requiresInvitation?: boolean;
    requiresFeature?: string;
    locked?: boolean;
}

const showUserMenu = ref(false);
const showMobileMenu = ref(false);

const page = usePage();

const navItems = computed<NavItem[]>(() => {
    const hasInvitation = Boolean(page.props.hasInvitation);
    const features = (page.props.features as string[]) || [];

    const items: NavItem[] = [
        {
            label: 'Dashboard',
            href: '/dashboard',
            icon: LayoutDashboard,
            requiresInvitation: true,
        },
        {
            label: 'Editor',
            href: '/dashboard/editor',
            icon: PenLine,
            requiresInvitation: true,
        },
        {
            label: 'Love Story',
            href: '/dashboard/love-story',
            icon: BookOpen,
            requiresInvitation: true,
        },
        {
            label: 'Kustomisasi',
            href: '/dashboard/customize',
            icon: Palette,
            requiresInvitation: true,
        },
        {
            label: 'Galeri',
            href: '/dashboard/gallery',
            icon: Image,
            requiresInvitation: true,
        },
        {
            label: 'Tamu',
            href: '/dashboard/guests',
            icon: Users,
            requiresInvitation: true,
        },
        {
            label: 'Buku Tamu',
            href: '/dashboard/guest-book',
            icon: ClipboardCheck,
            requiresInvitation: true,
            requiresFeature: 'guest_book',
        },
        {
            label: 'Transaksi',
            href: '/dashboard/transactions',
            icon: ReceiptText,
        },
        {
            label: 'Pengaturan',
            href: '/dashboard/settings',
            icon: Settings,
            requiresInvitation: true,
        },
    ];

    return items.filter((item) => {
        if (item.requiresInvitation && !hasInvitation) {
            return false;
        }

        if (item.requiresFeature && !features.includes(item.requiresFeature)) {
            // Paid venue toolkit: show a locked upsell entry instead of
            // hiding the feature entirely.
            if (item.requiresFeature === 'guest_book' && hasInvitation) {
                item.locked = true;
                item.href = '/produk';
            } else {
                return false;
            }
        }

        return true;
    });
});

const isActive = (href: string): boolean => {
    if (href === '/dashboard') {
        return window.location.pathname === '/dashboard';
    }

    return window.location.pathname.startsWith(href);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[var(--my-background)] pt-16 md:pt-20">
        <!-- Floating Navigation -->
        <nav
            class="fixed top-3 right-3 left-3 z-40 md:top-5 md:right-5 md:left-5"
        >
            <div
                class="mx-auto flex max-w-6xl items-center justify-between gap-3 rounded-full border border-[var(--my-border)]/60 bg-[var(--my-background)]/95 px-3 py-2 shadow-lg shadow-black/5 md:px-5 md:py-2.5"
            >
                <!-- Logo -->
                <Link
                    href="/dashboard"
                    class="flex shrink-0 items-center"
                    aria-label="Akadnya.com - Dashboard"
                >
                    <span
                        class="font-display text-xl leading-none font-bold text-[var(--my-primary)] md:text-2xl"
                    >
                        Akadnya<span class="text-[var(--my-secondary)]"
                            >.com</span
                        >
                    </span>
                </Link>

                <!-- Desktop Nav -->
                <div class="hidden items-center gap-1 lg:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="relative rounded-full px-4 py-1.5 text-sm font-semibold transition"
                        :class="
                            isActive(item.href)
                                ? 'bg-[var(--my-primary)]/10 text-[var(--my-primary)]'
                                : 'text-[var(--my-muted)] hover:text-[var(--my-primary)]'
                        "
                    >
                        {{ item.label }}
                        <LockKeyhole
                            v-if="item.locked"
                            class="ml-1 inline size-3"
                        />
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-full text-[var(--my-neutral)] transition hover:bg-[var(--my-primary)]/10 lg:hidden"
                    aria-label="Buka menu"
                    @click="showMobileMenu = !showMobileMenu"
                >
                    <svg
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="!showMobileMenu"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- User Menu (Desktop) -->
                <div class="relative hidden lg:block">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 transition hover:bg-[var(--my-primary)]/10"
                        @click="showUserMenu = !showUserMenu"
                    >
                        <span
                            class="grid size-8 place-items-center rounded-full bg-[var(--my-primary)] text-sm font-bold text-white"
                        >
                            {{
                                $page.props.auth?.user?.name
                                    ?.charAt(0)
                                    .toUpperCase() || 'U'
                            }}
                        </span>
                        <ChevronDown class="size-4 text-[var(--my-muted)]" />
                    </button>

                    <div
                        v-show="showUserMenu"
                        class="my-card absolute right-0 mt-3 w-56 overflow-hidden rounded-2xl py-2"
                        @click="showUserMenu = false"
                    >
                        <div
                            class="border-b border-[var(--my-border)] px-4 py-3"
                        >
                            <p
                                class="truncate text-sm font-bold text-[var(--my-neutral)]"
                            >
                                {{ $page.props.auth?.user?.name }}
                            </p>
                            <p class="truncate text-xs text-[var(--my-muted)]">
                                {{ $page.props.auth?.user?.email }}
                            </p>
                        </div>
                        <Link
                            href="/settings/profile"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]"
                        >
                            <svg
                                class="size-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            Profil
                        </Link>
                        <Link
                            href="/settings/security"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]"
                        >
                            <LockKeyhole class="size-4" />
                            Keamanan
                        </Link>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 border-t border-[var(--my-border)] px-4 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50"
                            @click="logout"
                        >
                            <LogOut class="size-4" />
                            Keluar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div
                v-if="showMobileMenu"
                class="mx-auto mt-2 max-w-6xl rounded-2xl border border-[var(--my-border)]/60 bg-[var(--my-background)]/95 p-4 shadow-lg shadow-black/5 lg:hidden"
            >
                <div class="grid gap-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition"
                        :class="
                            isActive(item.href)
                                ? 'bg-[var(--my-primary)]/10 text-[var(--my-primary)]'
                                : 'text-[var(--my-muted)] hover:bg-[var(--my-surface-soft)] hover:text-[var(--my-primary)]'
                        "
                        @click="showMobileMenu = false"
                    >
                        <component :is="item.icon" class="size-5" />
                        {{ item.label }}
                        <LockKeyhole
                            v-if="item.locked"
                            class="ml-auto size-4"
                        />
                    </Link>
                </div>
                <div class="mt-4 border-t border-[var(--my-border)] pt-4">
                    <div class="mb-3 px-3">
                        <p class="text-sm font-bold text-[var(--my-neutral)]">
                            {{ $page.props.auth?.user?.name }}
                        </p>
                        <p class="text-xs text-[var(--my-muted)]">
                            {{ $page.props.auth?.user?.email }}
                        </p>
                    </div>
                    <Link
                        href="/settings/profile"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]"
                        @click="showMobileMenu = false"
                    >
                        <svg
                            class="size-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                        Profil
                    </Link>
                    <Link
                        href="/settings/security"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]"
                        @click="showMobileMenu = false"
                    >
                        <LockKeyhole class="size-4" />
                        Keamanan
                    </Link>
                    <button
                        type="button"
                        class="mt-1 flex w-full items-center gap-3 rounded-lg bg-red-50 px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-100"
                        @click="logout"
                    >
                        <LogOut class="size-4" />
                        Keluar
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
