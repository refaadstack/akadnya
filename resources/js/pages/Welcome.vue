<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarCheck,
    Eye,
    Gift,
    MessageCircle,
    Music,
    Send,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface FeaturedTemplate {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
    price: number;
    is_free: boolean;
}

const props = defineProps<{
    canRegister: boolean;
    startingTemplate: { name: string; price: number } | null;
    featuredTemplates: FeaturedTemplate[];
}>();

const isLocal = import.meta.env.DEV;

const formattedPrice = computed(() => {
    if (!props.startingTemplate) {
        return 'Rp 0';
    }

    return `Rp ${props.startingTemplate.price.toLocaleString('id-ID')}`;
});

const features = [
    {
        icon: Eye,
        title: 'Lihat dulu, baru putuskan',
        text: 'Kamu bisa cek tampilan asli setiap template sebelum checkout. Yang kamu lihat adalah yang tamu akan buka.',
    },
    {
        icon: CalendarCheck,
        title: 'Kustomisasi sebebasmu',
        text: 'Isi nama, foto, detail acara, daftar tamu, RSVP, hingga amplop digital dengan santai setelah checkout.',
    },
    {
        icon: Music,
        title: 'Detail yang terasa hidup',
        text: 'Musik pembuka, galeri foto, hitung mundur hari-H, dan amplop digital sudah ada, tinggal kamu isi.',
    },
    {
        icon: Send,
        title: 'Satu link untuk semua tamu',
        text: 'Setelah publish, link personal undanganmu siap dibagikan lewat WhatsApp, Instagram, atau media sosial.',
    },
];

const steps = [
    'Temukan template yang punya jiwa yang sama dengan acaramu.',
    'Preview tampilan aslinya dulu sebelum memutuskan.',
    'Checkout, lalu isi nama, foto, detail acara, dan tamu dengan santai.',
    'Publish, salin link, dan bagikan ke seluruh tamu lewat WhatsApp.',
];
</script>

<template>
    <div class="my-page">
        <Head title="MyAkad - Undangan Digital Pernikahan" />

        <PublicNavbar :can-register="canRegister" current-page="home" />

        <main>
            <section class="min-h-[860px] px-0 pt-24 md:pt-20">
                <div
                    class="my-container grid min-h-[760px] items-center gap-12 py-16 lg:grid-cols-2"
                >
                    <div>
                        <p class="my-label mb-5">Undangan Digital Premium</p>
                        <h1
                            class="my-heading max-w-2xl text-5xl leading-[0.98] md:text-6xl"
                        >
                            Abadikan Momen
                            <br />
                            <span class="my-heading-accent">Terindah Kamu</span>
                        </h1>
                        <p class="my-copy mt-6 max-w-xl">
                            Pilih template dengan karakter budaya yang paling
                            kamu suka, isi detail acara, lalu bagikan linknya ke
                            semua tamu. Siap dalam hitungan menit, tanpa perlu
                            skill desain.
                        </p>
                        <div class="mt-9 flex flex-wrap gap-4">
                            <Link href="/templates" class="my-btn-primary px-9">
                                Pilih Template Undanganku
                            </Link>
                            <a
                                href="#how-it-works"
                                class="my-btn-secondary px-9"
                            >
                                Cara Pesan
                            </a>
                        </div>
                        <p
                            class="mt-5 text-sm font-semibold text-[var(--my-muted)]"
                        >
                            Dipercaya pasangan yang ingin undangan digital
                            terasa personal, rapi, dan mudah dibagikan.
                        </p>
                    </div>

                    <div class="flex justify-center lg:justify-end">
                        <div class="relative w-full max-w-[470px]">
                            <img
                                class="aspect-square w-full rounded-[96px_40px_96px_40px] object-cover shadow-[0_28px_70px_rgb(51_51_51_/_16%)]"
                                src="https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=1000&q=85"
                                alt="Detail undangan pernikahan dengan bunga putih dan dedaunan sage"
                            />
                            <div
                                class="absolute -bottom-5 left-8 rounded-xl border border-[var(--my-border)] bg-white/84 px-5 py-4 shadow-lg backdrop-blur-md"
                            >
                                <p class="my-label text-[0.66rem]">
                                    Preview Asli
                                </p>
                                <p
                                    class="font-display mt-1 text-xl font-semibold text-[var(--my-neutral)]"
                                >
                                    Lihat dulu sebelum checkout
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="templates" class="bg-white/40 py-20">
                <div class="my-container">
                    <div
                        class="mb-12 flex flex-col justify-between gap-5 md:flex-row md:items-end"
                    >
                        <div>
                            <p class="my-label mb-3">
                                Koleksi Nusantara & Global
                            </p>
                            <h2 class="my-heading text-4xl">
                                Template yang punya karakter sendiri
                            </h2>
                        </div>
                        <Link
                            href="/templates"
                            class="my-btn-secondary w-fit px-7"
                            >Lihat semua</Link
                        >
                    </div>

                    <div
                        v-if="featuredTemplates.length > 0"
                        class="grid gap-6 md:grid-cols-3"
                    >
                        <article
                            v-for="template in featuredTemplates"
                            :key="template.id"
                            class="my-card group overflow-hidden p-3"
                        >
                            <div class="overflow-hidden rounded-lg">
                                <img
                                    v-if="template.thumbnail_url"
                                    :src="template.thumbnail_url"
                                    :alt="template.name"
                                    class="aspect-[3/4] w-full object-cover transition duration-700 group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="grid aspect-[3/4] place-items-center bg-[var(--my-tertiary)]/35"
                                >
                                    <span
                                        class="font-display text-6xl text-[var(--my-primary)]"
                                        >My</span
                                    >
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="my-heading text-2xl">
                                    {{ template.name }}
                                </h3>
                                <div
                                    class="mt-3 flex items-end justify-between gap-4"
                                >
                                    <p
                                        class="text-2xl font-bold text-[var(--my-primary)]"
                                    >
                                        {{
                                            template.is_free
                                                ? 'Gratis'
                                                : `Rp ${template.price.toLocaleString('id-ID')}`
                                        }}
                                    </p>
                                    <p
                                        class="text-sm font-semibold tracking-[0.12em] text-[var(--my-muted)] uppercase"
                                    >
                                        Premium
                                    </p>
                                </div>
                                <p
                                    class="mt-3 text-sm leading-6 text-[var(--my-neutral)]"
                                >
                                    Preview tampilan asli template sebelum
                                    checkout, lalu lanjutkan ke editor untuk
                                    mengisi detail undangan.
                                </p>
                                <a
                                    :href="`/templates/${template.slug}/render`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="my-btn-secondary mt-5 w-full"
                                >
                                    Preview Template
                                </a>
                            </div>
                        </article>
                    </div>

                    <div v-else class="my-card py-14 text-center">
                        <p class="my-heading text-3xl">
                            Template segera tersedia
                        </p>
                        <p class="my-copy mx-auto mt-3 max-w-md">
                            Koleksi template akan tampil otomatis setelah
                            diaktifkan dari database.
                        </p>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="py-20">
                <div
                    class="my-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start"
                >
                    <div>
                        <p class="my-label mb-3">Cara Pesan</p>
                        <h2 class="my-heading text-4xl">
                            Alurnya sederhana, hasilnya tetap premium.
                        </h2>
                        <p class="my-copy mt-5">
                            Di MyAkad, kamu bisa preview tampilan asli setiap
                            template sebelum memutuskan beli. Setelah itu
                            tinggal isi data, publish, dan bagikan link
                            undanganmu.
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div
                            v-for="(step, index) in steps"
                            :key="step"
                            class="my-card flex gap-5 p-5"
                        >
                            <span
                                class="font-display flex size-11 shrink-0 items-center justify-center rounded-full bg-[var(--my-primary)] text-xl font-bold text-white"
                            >
                                {{ index + 1 }}
                            </span>
                            <p
                                class="text-lg leading-7 text-[var(--my-neutral)]"
                            >
                                {{ step }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="bg-[var(--my-surface-soft)]/70 py-20">
                <div class="my-container">
                    <div class="mb-12 text-center">
                        <p class="my-label mb-3">Fitur Utama</p>
                        <h2 class="my-heading text-4xl">
                            Semua kebutuhan undangan digital
                        </h2>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <article
                            v-for="feature in features"
                            :key="feature.title"
                            class="my-card p-6"
                        >
                            <component
                                :is="feature.icon"
                                class="mb-5 size-9 text-[var(--my-primary)]"
                            />
                            <h3
                                class="text-xl font-bold text-[var(--my-neutral)]"
                            >
                                {{ feature.title }}
                            </h3>
                            <p class="mt-3 leading-6 text-[var(--my-muted)]">
                                {{ feature.text }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pricing" class="py-20">
                <div class="my-container">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="my-label mb-3">Harga</p>
                        <h2 class="my-heading text-4xl">
                            Mulai dari
                            {{
                                startingTemplate
                                    ? formattedPrice
                                    : 'paket aktif'
                            }}
                        </h2>
                        <p class="my-copy mt-4">
                            Setara harga beberapa lembar undangan cetak, tapi
                            bisa dibagikan ke banyak tamu sekaligus dan
                            diperbarui kapanpun ada perubahan info.
                        </p>
                    </div>

                    <div class="my-card mx-auto mt-10 max-w-md p-7">
                        <div class="text-center">
                            <h3 class="my-heading text-3xl">
                                {{
                                    startingTemplate?.name ?? 'Template MyAkad'
                                }}
                            </h3>
                            <p
                                class="mt-3 text-4xl font-bold text-[var(--my-primary)]"
                            >
                                {{
                                    startingTemplate
                                        ? `${formattedPrice} sekali bayar`
                                        : 'Tersedia di koleksi'
                                }}
                            </p>
                            <p class="mt-3 text-[var(--my-muted)]">
                                {{
                                    startingTemplate
                                        ? 'Satu template, semua fitur — editor, subdomain, RSVP, galeri, dan publish.'
                                        : 'Pilih template yang paling dekat dengan cerita kalian.'
                                }}
                            </p>
                        </div>
                        <ul class="mt-7 grid gap-3 text-[var(--my-muted)]">
                            <li class="flex gap-3">
                                <Gift
                                    class="mt-0.5 size-5 text-[var(--my-primary)]"
                                />
                                Template premium siap pakai
                            </li>
                            <li class="flex gap-3">
                                <CalendarCheck
                                    class="mt-0.5 size-5 text-[var(--my-primary)]"
                                />
                                RSVP, galeri, dan data acara
                            </li>
                            <li class="flex gap-3">
                                <MessageCircle
                                    class="mt-0.5 size-5 text-[var(--my-primary)]"
                                />
                                Link undangan siap dibagikan
                            </li>
                        </ul>
                        <Link
                            href="/templates"
                            class="my-btn-primary mt-8 w-full"
                            >Pilih Template Undanganku</Link
                        >
                    </div>
                </div>
            </section>
        </main>

        <PublicFooter />

        <Link
            v-if="isLocal"
            href="/dev/payment-simulator"
            class="fixed right-6 bottom-6 z-50 rounded-full bg-[var(--my-neutral)] px-4 py-3 text-sm font-bold text-white shadow-2xl transition hover:bg-[var(--my-primary)]"
            title="Payment Simulator (Dev Tool)"
        >
            Payment Simulator
        </Link>
    </div>
</template>
