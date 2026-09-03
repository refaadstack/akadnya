<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronDown,
    Image,
    LayoutDashboard,
    LogOut,
    Menu,
    Palette,
    Settings,
    Sparkles,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface InvitationOption {
    id: number;
    status: string;
    subdomain: string | null;
    custom_domain: string | null;
    url: string;
    is_active: boolean;
    template: {
        id: number;
        name: string;
        slug: string;
        thumbnail_url: string | null;
    } | null;
}

const page = usePage();

const showUserMenu = ref(false);
const showInvitationSwitcher = ref(false);
const showMobileMenu = ref(false);

const activeInvitation = computed(() => page.props.activeInvitation as any);
const invitationOptions = computed(
    () => (page.props.invitationOptions as InvitationOption[]) || [],
);
const hasInvitation = computed(() => activeInvitation.value !== null);
const isPublished = computed(
    () => activeInvitation.value?.status === 'published',
);

const navItems = computed(() => [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
        show: true,
    },
    {
        label: 'Editor',
        href: '/dashboard/editor',
        icon: BookOpen,
        show: hasInvitation.value,
    },
    {
        label: 'Kustomisasi',
        href: '/dashboard/customize',
        icon: Palette,
        show: hasInvitation.value,
    },
    {
        label: 'Galeri',
        href: '/dashboard/gallery',
        icon: Image,
        show: hasInvitation.value,
    },
    {
        label: 'Tamu',
        href: '/dashboard/guests',
        icon: Users,
        show: hasInvitation.value,
    },
    {
        label: 'Pengaturan',
        href: '/dashboard/settings',
        icon: Settings,
        show: hasInvitation.value,
    },
]);

const filteredNavItems = computed(() =>
    navItems.value.filter((item) => item.show),
);

const isActive = (href: string) => {
    const url = page.url;

    if (href === '/dashboard') {
        return url === '/dashboard';
    }

    return url === href || url.startsWith(href);
};

const selectInvitation = (invitationId: number) => {
    router.post(
        `/dashboard/invitations/${invitationId}/select`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showInvitationSwitcher.value = false;
            },
        },
    );
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="my-page min-h-screen">
        <nav
            class="sticky top-0 z-40 border-b border-[var(--my-border)]/70 bg-[var(--my-background)]/88 backdrop-blur-md"
        >
            <div class="my-container">
                <div class="flex min-h-16 items-center justify-between gap-4">
                    <Link
                        href="/dashboard"
                        class="font-display text-2xl leading-none font-bold text-[var(--my-primary)]"
                    >
                        Akadnya.com
                    </Link>

                    <div class="hidden items-center gap-5 lg:flex">
                        <Link
                            v-for="item in filteredNavItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-1.5 text-sm font-semibold transition"
                            :class="
                                isActive(item.href)
                                    ? 'text-[var(--my-primary)]'
                                    : 'text-[var(--my-muted)] hover:text-[var(--my-primary)]'
                            "
                        >
                            <component :is="item.icon" class="size-4" />
                            {{ item.label }}
                        </Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            v-if="hasInvitation"
                            class="relative hidden md:block"
                        >
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-lg border border-[var(--my-border)] bg-white/60 px-3 py-1.5 text-sm font-semibold text-[var(--my-neutral)] transition hover:border-[var(--my-primary)]"
                                @click="
                                    showInvitationSwitcher =
                                        !showInvitationSwitcher
                                "
                            >
                                <Sparkles
                                    class="size-3.5 text-[var(--my-primary)]"
                                />
                                <span class="max-w-[120px] truncate">{{
                                    activeInvitation?.template?.name ||
                                    'Undangan'
                                }}</span>
                                <span
                                    class="rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                                    :class="
                                        isPublished
                                            ? 'bg-[var(--my-primary)]/12 text-[var(--my-primary)]'
                                            : 'bg-[var(--my-secondary)]/20 text-[#8b5b52]'
                                    "
                                >
                                    {{ isPublished ? 'Published' : 'Draft' }}
                                </span>
                                <ChevronDown
                                    class="size-3.5 text-[var(--my-muted)]"
                                />
                            </button>

                            <div
                                v-show="showInvitationSwitcher"
                                class="absolute top-full right-0 mt-2 w-72 overflow-hidden rounded-lg border border-[var(--my-border)] bg-white shadow-lg"
                                @click="showInvitationSwitcher = false"
                            >
                                <div
                                    class="border-b border-[var(--my-border)] px-4 py-3"
                                >
                                    <p
                                        class="text-xs font-bold tracking-wider text-[var(--my-primary)] uppercase"
                                    >
                                        Pilih Undangan Aktif
                                    </p>
                                </div>
                                <div class="max-h-80 overflow-y-auto py-2">
                                    <button
                                        v-for="option in invitationOptions"
                                        :key="option.id"
                                        type="button"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-[var(--my-surface-soft)]"
                                        :class="
                                            option.is_active
                                                ? 'bg-[var(--my-primary)]/5'
                                                : ''
                                        "
                                        @click="selectInvitation(option.id)"
                                    >
                                        <div
                                            class="grid h-10 w-8 shrink-0 place-items-center overflow-hidden rounded bg-[var(--my-surface-soft)]"
                                        >
                                            <img
                                                v-if="
                                                    option.template
                                                        ?.thumbnail_url
                                                "
                                                :src="
                                                    option.template
                                                        .thumbnail_url
                                                "
                                                :alt="option.template.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <span
                                                v-else
                                                class="text-xs font-bold text-[var(--my-primary)]"
                                                >My</span
                                            >
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-bold text-[var(--my-neutral)]"
                                            >
                                                {{
                                                    option.template?.name ||
                                                    'Template'
                                                }}
                                            </p>
                                            <p
                                                class="truncate text-xs text-[var(--my-muted)]"
                                            >
                                                {{ option.subdomain }}
                                            </p>
                                        </div>
                                        <span
                                            v-if="option.is_active"
                                            class="rounded-full bg-[var(--my-primary)]/12 px-2 py-0.5 text-[10px] font-bold text-[var(--my-primary)]"
                                        >
                                            Aktif
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <button
                                type="button"
                                class="flex items-center gap-2"
                                @click="showUserMenu = !showUserMenu"
                            >
                                <span
                                    class="grid size-9 place-items-center rounded-lg bg-[var(--my-primary)] text-sm font-bold text-white"
                                >
                                    {{
                                        $page.props.auth?.user?.name
                                            ?.charAt(0)
                                            .toUpperCase() || 'U'
                                    }}
                                </span>
                                <ChevronDown
                                    class="hidden size-3.5 text-[var(--my-muted)] md:block"
                                />
                            </button>

                            <div
                                v-show="showUserMenu"
                                class="my-card absolute right-0 mt-2 w-56 overflow-hidden py-2"
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
                                    <p
                                        class="truncate text-xs text-[var(--my-muted)]"
                                    >
                                        {{ $page.props.auth?.user?.email }}
                                    </p>
                                </div>
                                <Link
                                    href="/settings/profile"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]"
                                >
                                    <Users class="size-4" />
                                    Profil
                                </Link>
                                <Link
                                    href="/settings/security"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]"
                                >
                                    <Settings class="size-4" />
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

                        <button
                            type="button"
                            class="grid size-9 place-items-center rounded-lg text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)] lg:hidden"
                            @click="showMobileMenu = !showMobileMenu"
                        >
                            <X v-if="showMobileMenu" class="size-5" />
                            <Menu v-else class="size-5" />
                        </button>
                    </div>
                </div>

                <div
                    v-if="showMobileMenu"
                    class="border-t border-[var(--my-border)]/70 py-3 lg:hidden"
                >
                    <div class="flex flex-col gap-1">
                        <Link
                            v-for="item in filteredNavItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition"
                            :class="
                                isActive(item.href)
                                    ? 'bg-[var(--my-primary)]/8 text-[var(--my-primary)]'
                                    : 'text-[var(--my-muted)] hover:bg-[var(--my-surface-soft)]'
                            "
                            @click="showMobileMenu = false"
                        >
                            <component :is="item.icon" class="size-4" />
                            {{ item.label }}
                        </Link>
                    </div>

                    <div
                        v-if="hasInvitation && invitationOptions.length > 1"
                        class="mt-3 border-t border-[var(--my-border)]/70 pt-3"
                    >
                        <p
                            class="mb-2 px-3 text-xs font-bold tracking-wider text-[var(--my-primary)] uppercase"
                        >
                            Pilih Undangan
                        </p>
                        <button
                            v-for="option in invitationOptions"
                            :key="option.id"
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition hover:bg-[var(--my-surface-soft)]"
                            :class="
                                option.is_active
                                    ? 'bg-[var(--my-primary)]/5'
                                    : ''
                            "
                            @click="
                                selectInvitation(option.id);
                                showMobileMenu = false;
                            "
                        >
                            <div
                                class="grid h-8 w-6 shrink-0 place-items-center overflow-hidden rounded bg-[var(--my-surface-soft)]"
                            >
                                <img
                                    v-if="option.template?.thumbnail_url"
                                    :src="option.template.thumbnail_url"
                                    :alt="option.template.name"
                                    class="h-full w-full object-cover"
                                />
                                <span
                                    v-else
                                    class="text-[10px] font-bold text-[var(--my-primary)]"
                                    >My</span
                                >
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-bold text-[var(--my-neutral)]"
                                >
                                    {{ option.template?.name || 'Template' }}
                                </p>
                                <p
                                    class="truncate text-xs text-[var(--my-muted)]"
                                >
                                    {{ option.subdomain }}
                                </p>
                            </div>
                            <span
                                v-if="option.is_active"
                                class="rounded-full bg-[var(--my-primary)]/12 px-2 py-0.5 text-[10px] font-bold text-[var(--my-primary)]"
                            >
                                Aktif
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>
