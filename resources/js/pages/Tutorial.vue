<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

const page = usePage();
const supportEmail = computed(() => page.props.support.email);
const supportWhatsapp = computed(() => page.props.support.whatsapp);

interface TutorialStep {
    title: string;
    detail: string;
}

interface TutorialMenu {
    name: string;
    route: string;
    description: string;
    steps: TutorialStep[];
}

interface TutorialGroup {
    group: string;
    icon: string;
    menus: TutorialMenu[];
}

interface Meta {
    title: string;
    description: string;
    keywords: string;
}

const props = defineProps<{
    groups: TutorialGroup[];
    meta: Meta;
    canRegister?: boolean;
}>();

const activeMenu = ref<string | null>(null);

// Get icon class based on icon name
const getIconClass = (icon: string): string => {
    const icons: Record<string, string> = {
        home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        settings:
            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        cart: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
    };

    return icons[icon] || icons.home;
};

const flatMenus = computed(() => props.groups.flatMap((group) => group.menus));

const menuSectionId = (name: string): string =>
    `menu-${name.toLowerCase().replace(/\s+/g, '-')}`;

// Scroll to the selected menu section
const selectMenu = (name: string) => {
    activeMenu.value = name;
    document
        .getElementById(menuSectionId(name))
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// Highlight active menu while scrolling
const updateActiveMenu = () => {
    let current: string | null = null;

    for (const menu of flatMenus.value) {
        const el = document.getElementById(menuSectionId(menu.name));

        if (el && el.getBoundingClientRect().top <= 160) {
            current = menu.name;
        }
    }

    if (current) {
        activeMenu.value = current;
    }
};

onMounted(() => {
    window.addEventListener('scroll', updateActiveMenu, { passive: true });
    updateActiveMenu();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateActiveMenu);
});
</script>

<template>
    <div>
        <Head :title="meta.title">
            <meta name="description" :content="meta.description" />
            <meta name="keywords" :content="meta.keywords" />

            <!-- Open Graph / Facebook -->
            <meta property="og:type" content="website" />
            <meta property="og:title" :content="meta.title" />
            <meta property="og:description" :content="meta.description" />

            <!-- Twitter -->
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:title" :content="meta.title" />
            <meta property="twitter:description" :content="meta.description" />
        </Head>

        <div class="my-page">
            <!-- Navbar -->
            <PublicNavbar :can-register="canRegister" current-page="tutorial" />

            <!-- Hero Section -->
            <section class="pt-32 pb-16 md:pb-24">
                <div class="my-container">
                    <div class="mx-auto max-w-4xl text-center">
                        <p class="my-label mb-4">Panduan Penggunaan</p>
                        <h1 class="my-heading mb-6 text-4xl md:text-5xl">
                            Tutorial Menggunakan
                            <span class="my-heading-accent">Seluruh Menu</span>
                        </h1>
                        <p class="my-copy mb-8">
                            Panduan langkah demi langkah untuk setiap menu di
                            MyAkad — dari menjelajah template hingga
                            mempublikasikan undangan.
                        </p>

                        <!-- Quick jump to menus -->
                        <div
                            class="mx-auto flex max-w-3xl flex-wrap items-center justify-center gap-3"
                        >
                            <button
                                v-for="menu in flatMenus"
                                :key="menu.name"
                                type="button"
                                @click="selectMenu(menu.name)"
                                class="rounded-full border border-[var(--my-border)] bg-white/60 px-4 py-2 text-sm font-semibold text-[var(--my-neutral)] transition hover:border-[var(--my-primary)] hover:text-[var(--my-primary)]"
                            >
                                {{ menu.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tutorial Content -->
            <section class="pb-20">
                <div class="my-container">
                    <div class="grid gap-10 lg:grid-cols-[280px_1fr]">
                        <!-- Sidebar -->
                        <aside class="lg:sticky lg:top-24 lg:self-start">
                            <nav
                                class="my-card max-h-[70vh] overflow-y-auto p-4 lg:max-h-[calc(100vh-8rem)]"
                            >
                                <div
                                    v-for="group in props.groups"
                                    :key="group.group"
                                    class="mb-6 last:mb-0"
                                >
                                    <p
                                        class="mb-2 flex items-center gap-2 px-2 text-xs font-bold tracking-[0.16em] text-[var(--my-primary)] uppercase"
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
                                                :d="getIconClass(group.icon)"
                                            />
                                        </svg>
                                        {{ group.group }}
                                    </p>
                                    <ul class="grid gap-1">
                                        <li
                                            v-for="menu in group.menus"
                                            :key="menu.name"
                                        >
                                            <button
                                                type="button"
                                                @click="selectMenu(menu.name)"
                                                class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition"
                                                :class="
                                                    activeMenu === menu.name
                                                        ? 'bg-[var(--my-primary)]/10 text-[var(--my-primary)]'
                                                        : 'text-[var(--my-muted)] hover:bg-[var(--my-surface-soft)] hover:text-[var(--my-neutral)]'
                                                "
                                            >
                                                {{ menu.name }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </aside>

                        <!-- Content -->
                        <div class="min-w-0">
                            <div
                                v-for="group in props.groups"
                                :key="group.group"
                                class="mb-14 last:mb-0"
                            >
                                <div class="mb-6 flex items-center gap-3">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-xl bg-[var(--my-primary)]"
                                    >
                                        <svg
                                            class="size-5 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                :d="getIconClass(group.icon)"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="my-heading text-2xl">
                                            {{ group.group }}
                                        </h2>
                                        <p class="text-[var(--my-muted)]">
                                            {{ group.menus.length }}
                                            menu
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-6">
                                    <article
                                        v-for="menu in group.menus"
                                        :id="menuSectionId(menu.name)"
                                        :key="menu.name"
                                        class="my-card scroll-mt-32 overflow-hidden"
                                    >
                                        <header
                                            class="border-b border-[var(--my-border)] bg-[var(--my-surface-soft)]/50 px-6 py-5"
                                        >
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-3"
                                            >
                                                <h3
                                                    class="flex items-center gap-2 text-lg font-bold text-[var(--my-neutral)]"
                                                >
                                                    <span
                                                        class="grid size-7 place-items-center rounded-lg bg-[var(--my-primary)] text-sm font-bold text-white"
                                                    >
                                                        {{
                                                            flatMenus.indexOf(
                                                                menu,
                                                            ) + 1
                                                        }}
                                                    </span>
                                                    {{ menu.name }}
                                                </h3>
                                                <Link
                                                    :href="menu.route"
                                                    class="rounded-full border border-[var(--my-primary)]/40 px-3 py-1 text-xs font-semibold text-[var(--my-primary)] transition hover:bg-[var(--my-primary)]/10"
                                                >
                                                    Buka menu
                                                </Link>
                                            </div>
                                            <p
                                                class="mt-2 text-sm leading-6 text-[var(--my-muted)]"
                                            >
                                                {{ menu.description }}
                                            </p>
                                        </header>

                                        <ol
                                            class="divide-y divide-[var(--my-border)]"
                                        >
                                            <li
                                                v-for="(
                                                    step, stepIndex
                                                ) in menu.steps"
                                                :key="stepIndex"
                                                class="flex gap-4 px-6 py-5"
                                            >
                                                <span
                                                    class="grid size-8 shrink-0 place-items-center rounded-full border-2 border-[var(--my-primary)] text-sm font-bold text-[var(--my-primary)]"
                                                >
                                                    {{ stepIndex + 1 }}
                                                </span>
                                                <div>
                                                    <h4
                                                        class="mb-1 font-semibold text-[var(--my-neutral)]"
                                                    >
                                                        {{ step.title }}
                                                    </h4>
                                                    <p
                                                        class="text-sm leading-6 text-[var(--my-muted)]"
                                                    >
                                                        {{ step.detail }}
                                                    </p>
                                                </div>
                                            </li>
                                        </ol>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="bg-[var(--my-primary)] py-16">
                <div class="my-container text-center">
                    <h2 class="my-heading mb-4 text-3xl text-white md:text-4xl">
                        Siap Membuat Undangan?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-xl text-white/80">
                        Mulai desain undangan digital Anda sekarang — pilih
                        template, isi konten, dan bagikan ke tamu.
                    </p>
                    <div
                        class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                    >
                        <Link
                            :href="canRegister ? '/register' : '/templates'"
                            class="inline-flex items-center rounded-lg bg-white px-8 py-4 font-semibold text-[var(--my-primary)] transition hover:shadow-xl"
                        >
                            Mulai Desain
                        </Link>
                        <a
                            :href="`mailto:${supportEmail}`"
                            class="inline-flex items-center rounded-lg bg-[var(--my-neutral)] px-8 py-4 font-semibold text-white transition hover:shadow-xl"
                        >
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            Email Support
                        </a>
                        <a
                            :href="`https://wa.me/${supportWhatsapp}`"
                            target="_blank"
                            class="inline-flex items-center rounded-lg bg-[var(--my-neutral)] px-8 py-4 font-semibold text-white transition hover:shadow-xl"
                        >
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                                />
                            </svg>
                            Tanya via WhatsApp
                        </a>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <PublicFooter />
        </div>
    </div>
</template>
