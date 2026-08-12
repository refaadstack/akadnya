<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Camera,
    Check,
    ChevronDown,
    ChevronUp,
    Eye,
    RefreshCcw,
    ShoppingCart,
    Zap,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface TemplateDetail {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
    price: number;
    original_price?: number | null;
    discount_percent?: number;
    is_free: boolean;
    is_granted: boolean;
    description: string;
    screenshots: string[];
}

interface PreviewData {
    greeting: string;
    groom_name: string;
    bride_name: string;
    groom_father: string;
    groom_mother: string;
    bride_father: string;
    bride_mother: string;
    akad_datetime: string;
    akad_datetime_formatted: string;
    akad_time: string;
    akad_venue: string;
    akad_maps_url: string;
    reception_datetime: string;
    reception_datetime_formatted: string;
    reception_time: string;
    reception_venue: string;
    reception_maps_url: string;
    mappacci_datetime_formatted: string;
    mappacci_time: string;
    mappacci_venue: string;
    love_story: string;
    special_message: string;
    guest_name: string;
    cover_photo_url: string | null;
}

const props = defineProps<{
    template: TemplateDetail;
    preview_defaults: Record<string, string>;
}>();

defineOptions({
    layout: undefined,
});

const STORAGE_KEY = `preview_data_${props.template.slug}`;
const STORAGE_EXPIRY = 24 * 60 * 60 * 1000;

const builtInDefaults = (): PreviewData => ({
    greeting: 'Bismillahirrahmanirrahim',
    groom_name: 'Ahmad Rizki',
    bride_name: 'Sarah Putri',
    groom_father: 'Bapak Hendra Wijaya',
    groom_mother: 'Ibu Dewi Lestari',
    bride_father: 'Bapak Budi Santoso',
    bride_mother: 'Ibu Siti Rahayu',
    akad_datetime: '2026-12-24T09:00',
    akad_datetime_formatted: 'Kamis, 24 Desember 2026',
    akad_time: '09.00 WITA',
    akad_venue: 'Gedung Serbaguna Melati',
    akad_maps_url: 'https://maps.google.com',
    reception_datetime: '2026-12-24T11:00',
    reception_datetime_formatted: 'Kamis, 24 Desember 2026',
    reception_time: '11.00 - 14.00 WITA',
    reception_venue: 'Gedung Serbaguna Melati',
    reception_maps_url: 'https://maps.google.com',
    mappacci_datetime_formatted: 'Rabu, 23 Desember 2026',
    mappacci_time: '19.30 WITA',
    mappacci_venue: 'Kediaman Keluarga',
    love_story:
        'Dari pertemuan keluarga yang sederhana, doa orang tua menuntun kami menuju akad yang penuh restu.',
    special_message:
        'Merupakan kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.',
    guest_name: 'Tamu Undangan',
    cover_photo_url: null,
});

const templateDefaults = (): PreviewData => {
    const builtin = builtInDefaults();
    const serverKeys = Object.keys(props.preview_defaults ?? {});

    for (const key of serverKeys) {
        const value = props.preview_defaults[key];

        if (value !== null && value !== undefined && value !== '') {
            (builtin as unknown as Record<string, string>)[key] = value;
        }
    }

    return builtin;
};

const getDefaultData = (): PreviewData => templateDefaults();

const loadStoredData = (): PreviewData => {
    try {
        const stored = sessionStorage.getItem(STORAGE_KEY);

        if (stored) {
            const { data, timestamp } = JSON.parse(stored);

            if (Date.now() - timestamp < STORAGE_EXPIRY) {
                return { ...templateDefaults(), ...data };
            }
        }
    } catch (e) {
        console.error('Failed to load stored preview data:', e);
    }

    return getDefaultData();
};

const previewData = ref<PreviewData>(loadStoredData());
const coverPhotoFile = ref<File | null>(null);

const encodeJson = (value: unknown): string => {
    const bytes = new TextEncoder().encode(JSON.stringify(value));
    let binary = '';

    for (const byte of bytes) {
        binary += String.fromCharCode(byte);
    }

    return btoa(binary);
};

const buildIframeSrc = (): string =>
    `/templates/${props.template.slug}/render?data=${encodeURIComponent(encodeJson(previewData.value))}&v=${Date.now()}`;

const iframeSrc = ref<string>('');
const previewLoading = ref<boolean>(true);

const persistData = () => {
    sessionStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({ data: previewData.value, timestamp: Date.now() }),
    );
};

let updateTimer: number | undefined;

watch(
    previewData,
    () => {
        try {
            persistData();
            clearTimeout(updateTimer);

            updateTimer = window.setTimeout(() => {
                previewLoading.value = true;
                iframeSrc.value = buildIframeSrc();
            }, 450);
        } catch (e) {
            console.error('Failed to update preview:', e);
        }
    },
    { deep: true },
);

const handleCoverPhotoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        coverPhotoFile.value = file;
        previewData.value.cover_photo_url = URL.createObjectURL(file);
    }
};

const resetData = () => {
    if (window.confirm('Reset semua data ke contoh default template?')) {
        coverPhotoFile.value = null;
        previewData.value = getDefaultData();
    }
};

const showEditForm = ref(true);

// Gallery state
const activeScreenshot = ref(0);
const gallery = computed(() => props.template.screenshots ?? []);
const activeImage = computed(
    () =>
        gallery.value[activeScreenshot.value] ??
        props.template.thumbnail_url ??
        '',
);

// Cart / grant state
const addingId = ref<number | null>(null);
const addedId = ref<number | null>(null);
const activatingId = ref<number | null>(null);

const addToCart = () => {
    if (addingId.value !== null) {
        return;
    }

    addingId.value = props.template.id;

    router.post(
        '/keranjang',
        {
            item_type: 'template',
            item_id: props.template.id,
            preview_data: previewData.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                addedId.value = props.template.id;
                setTimeout(() => {
                    addedId.value = null;
                }, 2500);
            },
            onFinish: () => {
                addingId.value = null;
            },
        },
    );
};

const activateTemplate = () => {
    if (activatingId.value !== null) {
        return;
    }

    activatingId.value = props.template.id;

    router.post(
        `/grants/activate/${props.template.slug}`,
        {},
        {
            onFinish: () => {
                activatingId.value = null;
            },
        },
    );
};

onMounted(() => {
    iframeSrc.value = buildIframeSrc();
});

onUnmounted(() => {
    clearTimeout(updateTimer);

    if (coverPhotoFile.value && previewData.value.cover_photo_url) {
        URL.revokeObjectURL(previewData.value.cover_photo_url);
    }
});
</script>

<template>
    <div class="my-page">
        <Head :title="`${template.name} — Detail Template`" />

        <PublicNavbar current-page="templates" />

        <main class="pt-28">
            <section class="my-container py-10">
                <Link
                    href="/templates"
                    class="my-btn-secondary mb-8 gap-2 px-4 py-2"
                >
                    <ArrowLeft class="size-4" />
                    Kembali ke Katalog
                </Link>

                <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                    <!-- Gallery -->
                    <div>
                        <div
                            class="grid min-h-[420px] place-items-center overflow-hidden rounded-xl bg-[var(--my-surface-soft)] p-6"
                        >
                            <img
                                v-if="activeImage"
                                :src="activeImage"
                                :alt="`Tampilan template ${template.name}`"
                                class="max-h-[540px] w-auto object-contain shadow-lg"
                            />
                        </div>

                        <div
                            v-if="gallery.length > 1"
                            class="mt-4 grid grid-cols-3 gap-3"
                        >
                            <button
                                v-for="(shot, index) in gallery"
                                :key="shot"
                                type="button"
                                class="overflow-hidden rounded-lg border-2 transition"
                                :class="
                                    index === activeScreenshot
                                        ? 'border-[var(--my-primary)]'
                                        : 'border-transparent hover:border-[var(--my-border)]'
                                "
                                @click="activeScreenshot = index"
                            >
                                <img
                                    :src="shot"
                                    :alt="`Screenshot ${template.name}`"
                                    class="h-28 w-full object-cover"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Info + CTA -->
                    <div>
                        <p class="my-label mb-3">Katalog Template</p>
                        <h1 class="my-heading text-5xl leading-tight">
                            {{ template.name }}
                        </h1>

                        <p
                            v-if="template.description"
                            class="my-copy mt-5 max-w-xl"
                        >
                            {{ template.description }}
                        </p>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span
                                v-if="template.is_granted"
                                class="flex items-center gap-1.5 rounded-full bg-[var(--my-secondary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-[#5c3a34] uppercase"
                            >
                                <Zap class="size-3.5" />
                                Akses Gratis
                            </span>
                            <span
                                v-else-if="template.is_free"
                                class="rounded-full bg-[var(--my-primary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-white uppercase"
                            >
                                Gratis
                            </span>
                            <span
                                v-else-if="
                                    template.discount_percent &&
                                    template.discount_percent > 0
                                "
                                class="rounded-full bg-[var(--my-primary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-white uppercase"
                            >
                                -{{ template.discount_percent }}%
                            </span>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap items-end gap-x-4 gap-y-2"
                        >
                            <p
                                v-if="!template.is_granted"
                                class="text-3xl font-bold text-[var(--my-primary)]"
                            >
                                {{
                                    template.is_free
                                        ? 'Gratis'
                                        : `Rp ${template.price.toLocaleString('id-ID')}`
                                }}
                            </p>
                            <p
                                v-if="
                                    !template.is_granted &&
                                    !template.is_free &&
                                    template.original_price &&
                                    template.original_price > template.price
                                "
                                class="text-lg font-semibold text-[var(--my-muted)] line-through"
                            >
                                Rp
                                {{
                                    template.original_price.toLocaleString(
                                        'id-ID',
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="mt-5 grid gap-2 text-sm text-[var(--my-muted)]"
                        >
                            <p class="flex items-center gap-2">
                                <Check
                                    class="size-4 text-[var(--my-primary)]"
                                />
                                Sudah termasuk akses editor & publish
                            </p>
                            <p class="flex items-center gap-2">
                                <Check
                                    class="size-4 text-[var(--my-primary)]"
                                />
                                Subdomain gratis, RSVP, dan galeri
                            </p>
                            <p class="flex items-center gap-2">
                                <Check
                                    class="size-4 text-[var(--my-primary)]"
                                />
                                Tanpa bundling paket — bayar sekali
                            </p>
                        </div>

                        <div class="mt-7 grid gap-3 sm:max-w-sm">
                            <button
                                v-if="template.is_granted"
                                class="my-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                type="button"
                                :disabled="activatingId !== null"
                                @click="activateTemplate"
                            >
                                <Zap
                                    v-if="activatingId !== null"
                                    class="size-4 animate-pulse"
                                />
                                <Check v-else class="size-4" />
                                {{
                                    activatingId !== null
                                        ? 'Mengaktifkan...'
                                        : 'Aktifkan Sekarang'
                                }}
                            </button>
                            <button
                                v-else
                                class="my-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                type="button"
                                :disabled="addingId !== null"
                                @click="addToCart"
                            >
                                <Check v-if="addedId !== null" class="size-4" />
                                <ShoppingCart v-else class="size-4" />
                                {{
                                    addingId !== null
                                        ? 'Menambahkan...'
                                        : addedId !== null
                                          ? 'Ditambahkan!'
                                          : 'Tambah ke Keranjang'
                                }}
                            </button>
                            <Link
                                v-if="addedId !== null"
                                href="/keranjang"
                                class="my-btn-secondary w-full gap-2"
                            >
                                Lihat Keranjang
                            </Link>
                            <a
                                :href="`/templates/${template.slug}/render`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="my-btn-secondary w-full gap-2"
                            >
                                <Eye class="size-4" />
                                Buka Preview Penuh
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Test with own text -->
            <section class="my-container pb-20">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="my-label mb-3">Coba Gratis</p>
                        <h2 class="my-heading text-4xl">
                            Test dengan teks kalian sendiri
                        </h2>
                        <p class="my-copy mt-3 max-w-2xl">
                            Isi nama, tanggal, dan tempat — preview di samping
                            langsung berubah. Tanpa login, tanpa bayar.
                        </p>
                    </div>
                    <button
                        class="my-btn-secondary gap-2"
                        type="button"
                        @click="showEditForm = !showEditForm"
                    >
                        <ChevronUp v-if="showEditForm" class="size-4" />
                        <ChevronDown v-else class="size-4" />
                        {{ showEditForm ? 'Tutup Form' : 'Buka Form' }}
                    </button>
                </div>

                <div class="mt-8 grid gap-8 lg:grid-cols-2">
                    <!-- Form -->
                    <div v-show="showEditForm" class="my-card p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="my-heading text-2xl">Data Undangan</h3>
                            <button
                                class="my-btn-secondary gap-2 px-4 py-2 text-sm"
                                type="button"
                                @click="resetData"
                            >
                                <RefreshCcw class="size-4" />
                                Reset
                            </button>
                        </div>

                        <div class="mt-6 space-y-8">
                            <div class="space-y-4">
                                <h4
                                    class="border-b pb-2 text-sm font-bold tracking-[0.14em] text-[var(--my-primary)] uppercase"
                                >
                                    Mempelai
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Nama Mempelai Pria</label
                                        >
                                        <input
                                            v-model="previewData.groom_name"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Nama Mempelai Wanita</label
                                        >
                                        <input
                                            v-model="previewData.bride_name"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Ayah Mempelai Pria</label
                                        >
                                        <input
                                            v-model="previewData.groom_father"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Ibu Mempelai Pria</label
                                        >
                                        <input
                                            v-model="previewData.groom_mother"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Ayah Mempelai Wanita</label
                                        >
                                        <input
                                            v-model="previewData.bride_father"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Ibu Mempelai Wanita</label
                                        >
                                        <input
                                            v-model="previewData.bride_mother"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4
                                    class="border-b pb-2 text-sm font-bold tracking-[0.14em] text-[var(--my-primary)] uppercase"
                                >
                                    Akad Nikah
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tanggal Akad (display)</label
                                        >
                                        <input
                                            v-model="
                                                previewData.akad_datetime_formatted
                                            "
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Waktu Akad</label
                                        >
                                        <input
                                            v-model="previewData.akad_time"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tempat Akad</label
                                        >
                                        <input
                                            v-model="previewData.akad_venue"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Maps Akad (URL)</label
                                        >
                                        <input
                                            v-model="previewData.akad_maps_url"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4
                                    class="border-b pb-2 text-sm font-bold tracking-[0.14em] text-[var(--my-primary)] uppercase"
                                >
                                    Resepsi
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tanggal Resepsi (display)</label
                                        >
                                        <input
                                            v-model="
                                                previewData.reception_datetime_formatted
                                            "
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Waktu Resepsi</label
                                        >
                                        <input
                                            v-model="previewData.reception_time"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tempat Resepsi</label
                                        >
                                        <input
                                            v-model="
                                                previewData.reception_venue
                                            "
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Maps Resepsi (URL)</label
                                        >
                                        <input
                                            v-model="
                                                previewData.reception_maps_url
                                            "
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4
                                    class="border-b pb-2 text-sm font-bold tracking-[0.14em] text-[var(--my-primary)] uppercase"
                                >
                                    Acara Adat (opsional)
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tanggal Adat (display)</label
                                        >
                                        <input
                                            v-model="
                                                previewData.mappacci_datetime_formatted
                                            "
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Waktu Adat</label
                                        >
                                        <input
                                            v-model="previewData.mappacci_time"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Tempat Acara Adat</label
                                        >
                                        <input
                                            v-model="previewData.mappacci_venue"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4
                                    class="border-b pb-2 text-sm font-bold tracking-[0.14em] text-[var(--my-primary)] uppercase"
                                >
                                    Teks & Foto
                                </h4>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Salam Pembuka</label
                                        >
                                        <input
                                            v-model="previewData.greeting"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Nama Tamu</label
                                        >
                                        <input
                                            v-model="previewData.guest_name"
                                            type="text"
                                            class="my-input px-3 py-2"
                                        />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Cerita Cinta</label
                                        >
                                        <textarea
                                            v-model="previewData.love_story"
                                            rows="3"
                                            class="my-input px-3 py-2"
                                        ></textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Pesan Khusus</label
                                        >
                                        <textarea
                                            v-model="
                                                previewData.special_message
                                            "
                                            rows="2"
                                            class="my-input px-3 py-2"
                                        ></textarea>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label
                                            class="mb-1 block text-sm font-medium text-[var(--my-muted)]"
                                            >Foto Cover</label
                                        >
                                        <div class="flex items-center gap-3">
                                            <label
                                                class="my-btn-secondary cursor-pointer gap-2 px-4 py-2 text-sm"
                                            >
                                                <Camera class="size-4" />
                                                Pilih Foto
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    class="hidden"
                                                    @change="
                                                        handleCoverPhotoUpload
                                                    "
                                                />
                                            </label>
                                            <span
                                                class="text-xs text-[var(--my-muted)]"
                                            >
                                                {{
                                                    coverPhotoFile
                                                        ? coverPhotoFile.name
                                                        : 'Pakai foto contoh template'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live preview -->
                    <div class="my-card overflow-hidden p-0">
                        <div
                            class="flex items-center justify-between border-b px-5 py-4"
                        >
                            <div>
                                <h3 class="my-heading text-xl">
                                    Preview Langsung
                                </h3>
                                <p class="text-xs text-[var(--my-muted)]">
                                    Perubahan tampil otomatis beberapa detik
                                    setelah mengetik
                                </p>
                            </div>
                            <a
                                :href="`/templates/${template.slug}/render?data=${iframeSrc.split('?data=')[1] ?? ''}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="my-btn-secondary gap-2 px-4 py-2 text-sm"
                            >
                                <Eye class="size-4" />
                                Tab Baru
                            </a>
                        </div>

                        <div class="relative min-h-[640px] bg-black/90">
                            <div
                                v-if="previewLoading"
                                class="absolute inset-0 z-10 grid place-items-center text-white/70"
                            >
                                <div class="text-center">
                                    <div
                                        class="mx-auto size-8 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                    ></div>
                                    <p class="mt-3 text-sm">
                                        Memuat preview...
                                    </p>
                                </div>
                            </div>
                            <iframe
                                :src="iframeSrc"
                                title="Preview undangan"
                                class="h-[720px] w-full border-0"
                                @load="previewLoading = false"
                            ></iframe>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <PublicFooter />
    </div>
</template>
