<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarCheck,
    Check,
    Eye,
    Gift,
    Layers,
    MessageCircle,
    Music,
    QrCode,
    Send,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface FeaturedTemplate {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
    price: number;
    original_price?: number | null;
    discount_percent?: number;
    is_free: boolean;
}

const props = defineProps<{
    canRegister: boolean;
    startingTemplate: {
        name: string;
        price: number;
        original_price?: number | null;
        discount_percent?: number;
    } | null;
    featuredTemplates: FeaturedTemplate[];
    guestBook: {
        name: string;
        price: number;
        original_price?: number | null;
        discount_percent?: number;
        url: string;
        demo_qr_svg: string | null;
    } | null;
}>();

const isLocal = import.meta.env.DEV;

const formattedPrice = computed(() => {
    if (!props.startingTemplate) {
        return 'Rp 0';
    }

    return `Rp ${props.startingTemplate.price.toLocaleString('id-ID')}`;
});

const guestBookPrice = computed(() => {
    if (!props.guestBook) {
        return '';
    }

    return `Rp ${props.guestBook.price.toLocaleString('id-ID')}`;
});

const features = [
    {
        icon: Eye,
        title: 'Lihat dulu, baru putuskan',
        text: 'Kamu bisa cek tampilan asli setiap template sebelum checkout. Yang kamu lihat adalah yang tamu akan buka.',
    },
    {
        icon: QrCode,
        title: 'Buku tamu digital',
        text: 'Check-in tamu saat hari-H cukup dengan scan barcode QR. Pantau kehadiran, cek souvenir, dan undi pemenang dalam satu tempat.',
    },
    {
        icon: Layers,
        title: 'Bisa lebih dari satu undangan',
        text: 'Kelola beberapa template sekaligus dalam satu akun. Setiap undangan punya konten, tamu, dan link-nya sendiri, tinggal pilih yang aktif.',
    },
    {
        icon: Users,
        title: 'Tamu punya link personal',
        text: 'Import daftar tamu, bagikan link khusus per tamu, dan kirim undangan langsung lewat WhatsApp. Nama tamu ikut tampil di undangan.',
    },
    {
        icon: CalendarCheck,
        title: 'RSVP & konfirmasi hadir',
        text: 'Tamu konfirmasi kehadiran dengan santai. Kamu bisa lihat siapa yang hadir, jumlah orang, dan pesan mereka sekaligus.',
    },
    {
        icon: Music,
        title: 'Detail yang terasa hidup',
        text: 'Love story, galeri foto, hitung mundur hari-H, musik pembuka, dan kustomisasi section sudah ada lalu diisi dengan leluasa.',
    },
    {
        icon: Wallet,
        title: 'Amplop digital',
        text: 'Buat tamu mengirim hadiah ke rekening bank, QRIS, maupun e-wallet — lengkap dengan alamat kado fisik bila perlu.',
    },
    {
        icon: Send,
        title: 'Satu link untuk semua tamu',
        text: 'Setelah publish, link undanganmu siap dibagikan lewat WhatsApp, Instagram, atau media sosial. Lengkap dengan subdomain sendiri.',
    },
];

const steps = [
    'Temukan template yang terasa paling dekat dengan acaramu.',
    'Preview tampilan aslinya dulu sebelum memutuskan.',
    'Checkout, lalu isi nama, foto, detail acara, tamu, dan RSVP dengan santai.',
    'Untuk undangan kedua, cukup tambah template baru lalu kelola semuanya di satu dashboard.',
];

const marqueeItems = [
    'Undangan Digital',
    'Check-in QR Tamu',
    'Galeri & Love Story',
    'Musik Pembuka',
    'Sekali Bayar, Seumur Hidup',
    'Template Nusantara',
];

const revealObserver = ref<IntersectionObserver | null>(null);

onMounted(() => {
    if (!('IntersectionObserver' in window)) {
        document
            .querySelectorAll('.my-reveal')
            .forEach((el) => el.classList.add('my-revealed'));

        return;
    }

    revealObserver.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('my-revealed');
                    revealObserver.value?.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -48px 0px', threshold: 0.08 },
    );

    document
        .querySelectorAll('.my-reveal')
        .forEach((el) => revealObserver.value?.observe(el));
});

onBeforeUnmount(() => {
    revealObserver.value?.disconnect();
});
</script>

<template>
    <div class="my-page">
        <Head title="Akadnya.com - Undangan Digital Pernikahan" />

        <PublicNavbar :can-register="canRegister" current-page="home" />

        <main>
            <section class="bg-[var(--ink)] px-0 pt-24 md:pt-20">
                <div
                    class="my-container grid min-h-[720px] items-center gap-14 py-16 lg:grid-cols-[1.05fr_0.95fr]"
                >
                    <div class="my-reveal">
                        <p
                            class="mb-5 text-[0.78rem] font-bold tracking-[0.18em] text-[var(--gold-light)] uppercase"
                        >
                            Undangan Digital Premium
                        </p>
                        <h1
                            class="my-heading max-w-xl text-5xl leading-[1.02] text-[var(--text-light)] md:text-[3.6rem]"
                        >
                            Undangan digital dengan karakter,
                            <span class="text-[var(--gold-light)]"
                                >bukan template pasaran.</span
                            >
                        </h1>
                        <p
                            class="my-copy mt-7 max-w-lg text-[var(--text-light)]"
                        >
                            Pilih template dengan karakter budaya yang kamu
                            suka, isi detail acara, bagikan linknya ke semua
                            tamu.
                        </p>
                        <div class="mt-10 flex flex-wrap gap-4">
                            <Link
                                href="/templates"
                                class="inline-flex items-center justify-center rounded-lg bg-[var(--gold-light)] px-9 py-3 text-sm font-bold text-[var(--ink)] transition hover:bg-[var(--ink-2)] hover:text-[var(--text-light)]"
                            >
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
                            class="mt-6 text-sm font-semibold text-[var(--text-light-muted)]"
                        >
                            Preview asli tiap template sebelum checkout — yang
                            kamu lihat, yang dibuka tamu.
                        </p>
                    </div>

                    <div
                        class="my-reveal flex justify-center lg:justify-end"
                        style="--reveal-delay: 120ms"
                    >
                        <div class="relative w-full max-w-[440px]">
                            <img
                                class="aspect-[4/5] w-full rounded-[24px_96px_24px_96px] object-cover shadow-[0_28px_70px_rgb(0_0_0_/_40%)]"
                                src="https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=1000&q=85"
                                alt="Detail undangan pernikahan dengan bunga putih dan dedaunan sage"
                            />
                            <div
                                class="absolute top-6 -left-4 rounded-lg border border-white/15 bg-[var(--ink)]/90 px-4 py-3 shadow-lg backdrop-blur-md md:-left-8"
                            >
                                <p
                                    class="my-label text-[0.66rem] text-[var(--gold-light)]"
                                >
                                    Mulai dari
                                </p>
                                <p
                                    class="font-display mt-0.5 text-2xl font-bold text-[var(--text-light)]"
                                >
                                    {{ formattedPrice }}
                                </p>
                                <p
                                    class="text-xs font-semibold text-[var(--text-light-muted)]"
                                >
                                    sekali bayar
                                </p>
                            </div>
                            <div
                                class="absolute right-6 -bottom-5 flex items-center gap-3 rounded-lg border border-white/15 bg-[var(--ink)]/90 px-4 py-3 shadow-lg backdrop-blur-md"
                            >
                                <QrCode
                                    class="size-6 shrink-0 text-[var(--gold-light)]"
                                />
                                <div>
                                    <p
                                        class="my-label text-[0.66rem] text-[var(--gold-light)]"
                                    >
                                        Check-in Tamu
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-bold text-[var(--text-light)]"
                                    >
                                        Scan QR saat hari-H
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div
                class="my-marquee border-y border-[var(--my-primary)]/30 bg-[var(--my-primary)] py-4"
                aria-hidden="true"
            >
                <div class="my-marquee-track">
                    <div
                        v-for="copy in [0, 1]"
                        :key="copy"
                        class="my-marquee-content"
                    >
                        <span
                            v-for="item in marqueeItems"
                            :key="`${copy}-${item}`"
                            class="my-marquee-item"
                        >
                            {{ item }}
                            <span class="my-marquee-star">✦</span>
                        </span>
                    </div>
                </div>
            </div>

            <section id="templates" class="bg-white/45 py-24">
                <div class="my-container">
                    <div class="my-reveal mb-14 max-w-2xl">
                        <h2 class="my-heading text-4xl md:text-5xl">
                            Template yang tidak terlihat seperti orang lain
                        </h2>
                        <p class="my-copy mt-5 max-w-xl">
                            Dua belas karakter, dari Sunda sampai Bali, siap
                            diisi dengan cerita kalian — lengkap dengan section
                            yang bisa dipilih mana yang mau ditampilkan.
                        </p>
                    </div>

                    <div
                        v-if="featuredTemplates.length > 0"
                        class="grid gap-6 md:grid-cols-3"
                    >
                        <article
                            v-for="(template, index) in featuredTemplates"
                            :key="template.id"
                            class="my-card group overflow-hidden p-0 transition duration-300 hover:-translate-y-1.5"
                            :class="
                                index === 0
                                    ? 'border-[var(--my-primary)]/55 bg-white/88'
                                    : ''
                            "
                        >
                            <div class="overflow-hidden">
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
                            <div class="p-5">
                                <div
                                    class="flex items-baseline justify-between gap-3"
                                >
                                    <h3 class="my-heading text-xl">
                                        {{ template.name }}
                                    </h3>
                                    <div
                                        class="flex shrink-0 items-baseline gap-1.5"
                                    >
                                        <p
                                            class="text-lg font-bold text-[var(--my-primary)]"
                                        >
                                            {{
                                                template.is_free
                                                    ? 'Gratis'
                                                    : `Rp ${template.price.toLocaleString('id-ID')}`
                                            }}
                                        </p>
                                        <p
                                            v-if="
                                                !template.is_free &&
                                                template.original_price &&
                                                template.original_price >
                                                    template.price
                                            "
                                            class="text-xs font-semibold text-[var(--my-muted)] line-through"
                                        >
                                            Rp
                                            {{
                                                template.original_price.toLocaleString(
                                                    'id-ID',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <a
                                    :href="`/templates/${template.slug}/render`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-[var(--my-primary)] underline underline-offset-4 transition hover:text-[var(--my-neutral)]"
                                >
                                    Preview tampilan asli
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    </div>

                    <div v-else class="my-card py-14 text-center">
                        <p class="my-heading text-3xl">
                            Template segera tersedia
                        </p>
                        <p class="my-copy mx-auto mt-3 max-w-md">
                            Koleksi template baru akan segera hadir. Cek kembali
                            dalam beberapa saat ya.
                        </p>
                    </div>
                </div>
            </section>

            <section
                id="guest-book"
                class="border-y border-[var(--my-border)]/50 bg-[var(--my-surface-soft)]/55 py-24"
            >
                <div
                    class="my-container grid items-center gap-14 lg:grid-cols-[0.9fr_1.1fr]"
                >
                    <div class="my-reveal order-2 lg:order-1">
                        <div
                            class="relative mx-auto max-w-[300px] rounded-2xl border border-dashed border-[var(--my-primary)]/45 bg-white p-7 text-center shadow-[var(--my-shadow)]"
                        >
                            <p class="my-label mb-4">Contoh Kode Tamu</p>
                            <div
                                v-if="guestBook?.demo_qr_svg"
                                class="guest-qr-svg mx-auto max-w-[220px] rounded-lg bg-white"
                                v-html="guestBook.demo_qr_svg"
                            ></div>
                            <p
                                class="mt-4 font-mono text-xs tracking-widest text-[var(--my-muted)]"
                            >
                                Akadnya.com-DEMO-0001
                            </p>
                            <p
                                class="mt-3 text-xs leading-5 text-[var(--my-muted)]"
                            >
                                QR asli dibuat per tamu — tiap tamu punya kode
                                uniknya sendiri.
                            </p>
                        </div>
                    </div>

                    <div class="my-reveal order-1 lg:order-2">
                        <p class="my-label mb-5">Fitur Buku Tamu</p>
                        <h2 class="my-heading text-4xl md:text-5xl">
                            Check-in tamu tanpa
                            <span class="my-heading-accent"
                                >buku tamu fisik.</span
                            >
                        </h2>
                        <p class="my-copy mt-5 max-w-lg">
                            Ganti meja buku tamu dengan barcode yang bisa
                            dipindai. Kamu tahu siapa yang sudah hadir, tanpa
                            menebak tulisan tangan.
                        </p>
                        <ul class="mt-8 grid gap-4 text-[var(--my-neutral)]">
                            <li
                                v-for="point in [
                                    'Tamu scan QR saat tiba di venue, kehadiran tercatat otomatis',
                                    'Catat souvenir yang sudah diambil per tamu',
                                    'Undi pemenang langsung dari daftar tamu yang hadir',
                                ]"
                                :key="point"
                                class="flex gap-3"
                            >
                                <Check
                                    class="mt-0.5 size-5 shrink-0 text-[var(--my-primary)]"
                                />
                                {{ point }}
                            </li>
                        </ul>
                        <div
                            class="mt-9 flex flex-wrap items-center gap-5 border-t border-[var(--my-border)]/60 pt-6"
                        >
                            <p
                                class="text-3xl font-bold text-[var(--my-primary)]"
                            >
                                {{ guestBookPrice }}
                                <span
                                    v-if="
                                        guestBook?.original_price &&
                                        guestBook.original_price >
                                            guestBook.price
                                    "
                                    class="text-lg font-semibold text-[var(--my-muted)] line-through"
                                >
                                    Rp
                                    {{
                                        guestBook.original_price.toLocaleString(
                                            'id-ID',
                                        )
                                    }}
                                </span>
                                <span
                                    class="align-middle text-sm font-semibold text-[var(--my-muted)]"
                                    >sekali bayar</span
                                >
                            </p>
                            <a
                                v-if="guestBook"
                                :href="guestBook.url"
                                class="my-btn-primary px-8"
                            >
                                Lihat Paket Buku Tamu
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="py-24">
                <div class="my-container grid gap-12 lg:grid-cols-[1fr_1.2fr]">
                    <div class="my-reveal lg:sticky lg:top-28 lg:self-start">
                        <h2 class="my-heading text-4xl md:text-5xl">
                            Empat langkah,
                            <span class="my-heading-accent"
                                >satu undangan.</span
                            >
                        </h2>
                        <p class="my-copy mt-5 max-w-md">
                            Tidak ada desain yang perlu kamu kuasai. Preview
                            dulu, bayar sekali, sisanya tinggal diisi.
                        </p>
                        <Link
                            href="/templates"
                            class="my-btn-secondary mt-8 px-8"
                        >
                            Lihat Koleksi Template
                        </Link>
                    </div>

                    <ol class="grid gap-0">
                        <li
                            v-for="(step, index) in steps"
                            :key="step"
                            class="my-reveal flex gap-6 border-t border-[var(--my-border)]/60 py-7 first:border-t-0"
                            :style="{ '--reveal-delay': `${index * 80}ms` }"
                        >
                            <span
                                class="font-display shrink-0 text-6xl leading-none font-bold text-[var(--my-tertiary)] italic"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>
                            <p
                                class="self-center text-lg leading-7 text-[var(--my-neutral)]"
                            >
                                {{ step }}
                            </p>
                        </li>
                    </ol>
                </div>
            </section>

            <section id="features" class="bg-[var(--my-surface-soft)]/55 py-24">
                <div class="my-container">
                    <div class="my-reveal mb-14 max-w-2xl">
                        <h2 class="my-heading text-4xl md:text-5xl">
                            Semua kebutuhan undangan digital
                        </h2>
                        <p class="my-copy mt-5 max-w-xl">
                            Delapan hal yang biasanya ditanyakan — sudah ada
                            sejak awal, tinggal kamu pakai.
                        </p>
                    </div>
                    <div
                        class="grid gap-x-14 border-t border-[var(--my-border)]/60 md:grid-cols-2"
                    >
                        <article
                            v-for="(feature, index) in features"
                            :key="feature.title"
                            class="my-reveal border-b border-[var(--my-border)]/60 py-7"
                            :class="index % 2 === 1 ? 'md:border-b-0' : ''"
                            :style="{
                                '--reveal-delay': `${(index % 2) * 90}ms`,
                            }"
                        >
                            <div class="flex items-center gap-4">
                                <component
                                    :is="feature.icon"
                                    class="size-6 shrink-0 text-[var(--my-primary)]"
                                />
                                <h3 class="my-heading text-2xl">
                                    {{ feature.title }}
                                </h3>
                            </div>
                            <p
                                class="mt-3 pl-10 leading-6 text-[var(--my-muted)]"
                            >
                                {{ feature.text }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pricing" class="py-24">
                <div
                    class="my-container grid items-center gap-14 lg:grid-cols-[1.1fr_0.9fr]"
                >
                    <div class="my-reveal">
                        <h2 class="my-heading text-4xl md:text-5xl">
                            Satu harga,
                            <span class="my-heading-accent">semua fitur.</span>
                        </h2>
                        <p class="my-copy mt-5 max-w-md">
                            Setara beberapa lembar undangan cetak — tanpa biaya
                            cetak, tanpa revisi ulang, tanpa hitungan undangan
                            tercecer.
                        </p>
                        <ul
                            class="mt-9 grid gap-0 border-t border-[var(--my-border)]/60 text-[var(--my-muted)]"
                        >
                            <li
                                v-for="(item, index) in [
                                    ['Gift', 'Template premium siap pakai'],
                                    [
                                        'CalendarCheck',
                                        'RSVP, galeri, dan data acara',
                                    ],
                                    [
                                        'QrCode',
                                        'Buku tamu digital & scan QR di venue',
                                    ],
                                    [
                                        'Layers',
                                        'Bisa kelola lebih dari satu undangan',
                                    ],
                                    [
                                        'MessageCircle',
                                        'Link undangan siap dibagikan',
                                    ],
                                ]"
                                :key="index"
                                class="flex items-center gap-4 border-b border-[var(--my-border)]/60 py-4"
                            >
                                <component
                                    :is="
                                        {
                                            Gift,
                                            CalendarCheck,
                                            QrCode,
                                            Layers,
                                            MessageCircle,
                                        }[item[0]]
                                    "
                                    class="size-5 shrink-0 text-[var(--my-primary)]"
                                />
                                <span class="text-[15px] font-semibold">
                                    {{ item[1] }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="my-reveal rounded-2xl border border-[var(--my-primary)]/35 bg-white/85 p-8 shadow-[var(--my-shadow)]"
                        style="--reveal-delay: 120ms"
                    >
                        <div class="flex items-baseline justify-between gap-4">
                            <h3 class="my-heading text-2xl">
                                {{
                                    startingTemplate?.name ??
                                    'Template Akadnya.com'
                                }}
                            </h3>
                            <span
                                v-if="
                                    startingTemplate?.discount_percent &&
                                    startingTemplate.discount_percent > 0
                                "
                                class="rounded-[4px] bg-[var(--my-secondary)] px-2 py-0.5 text-xs font-bold text-[#5c3a34]"
                                >-{{ startingTemplate.discount_percent }}%</span
                            >
                        </div>
                        <p
                            class="mt-5 text-4xl font-bold text-[var(--my-primary)]"
                        >
                            {{
                                startingTemplate
                                    ? `${formattedPrice}`
                                    : 'Lihat koleksi'
                            }}
                            <span
                                class="text-lg font-semibold text-[var(--my-muted)]"
                                >sekali bayar</span
                            >
                        </p>
                        <p
                            v-if="
                                startingTemplate?.original_price &&
                                startingTemplate.original_price >
                                    startingTemplate.price
                            "
                            class="mt-1 text-lg font-semibold text-[var(--my-muted)] line-through"
                        >
                            Rp
                            {{
                                startingTemplate.original_price.toLocaleString(
                                    'id-ID',
                                )
                            }}
                        </p>
                        <p class="mt-4 leading-6 text-[var(--my-muted)]">
                            Satu template, semua fitur — editor, subdomain,
                            RSVP, galeri, buku tamu digital, dan publish.
                        </p>
                        <Link
                            href="/templates"
                            class="my-btn-primary mt-8 w-full"
                        >
                            Lihat Semua Template
                        </Link>
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

<style scoped>
.my-reveal {
    opacity: 0;
    transform: translateY(26px);
    transition:
        opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1) var(--reveal-delay, 0ms),
        transform 0.7s cubic-bezier(0.16, 1, 0.3, 1) var(--reveal-delay, 0ms);
}

.my-reveal.my-revealed {
    opacity: 1;
    transform: none;
}

/* The QR SVG is injected via v-html, so it lacks the scoped data
   attribute — target it with :deep() or the rule never matches. */
.guest-qr-svg :deep(svg) {
    width: 100%;
    height: auto;
    display: block;
}

@media (prefers-reduced-motion: reduce) {
    .my-reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
