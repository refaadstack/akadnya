<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Eye, ShoppingCart, Zap } from 'lucide-vue-next';
import { ref } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface Template {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
    price: number;
    original_price?: number | null;
    discount_percent?: number;
    is_free: boolean;
    is_granted: boolean;
}

defineProps<{
    templates: Template[];
}>();

defineOptions({
    layout: undefined,
});

const addingId = ref<number | null>(null);
const addedId = ref<number | null>(null);
const activatingId = ref<number | null>(null);

const activateTemplate = (template: Template) => {
    if (activatingId.value !== null) {
        return;
    }

    activatingId.value = template.id;

    router.post(
        `/grants/activate/${template.slug}`,
        {},
        {
            onFinish: () => {
                activatingId.value = null;
            },
        },
    );
};

const addToCart = (template: Template) => {
    if (addingId.value !== null) {
        return;
    }

    addingId.value = template.id;

    router.post(
        '/keranjang',
        {
            item_type: 'template',
            item_id: template.id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                addedId.value = template.id;
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
</script>

<template>
    <div class="my-page">
        <Head title="Pilih Template" />

        <PublicNavbar current-page="templates" />

        <main class="pt-28">
            <section class="my-container py-14">
                <div class="max-w-3xl">
                    <p class="my-label mb-4">Koleksi Template</p>
                    <h1 class="my-heading text-5xl leading-tight">
                        Pilih gaya undangan yang paling dekat dengan cerita
                        kalian.
                    </h1>
                    <p class="my-copy mt-5">
                        Setiap template bisa kamu lihat dulu dalam tampilan
                        aslinya sebelum checkout. Harga template sudah termasuk
                        semua fitur — akses editor, subdomain gratis, RSVP,
                        galeri, dan publish. Tanpa bundling paket wajib.
                    </p>
                </div>
            </section>

            <section class="my-container pb-20">
                <div
                    v-if="templates.length > 0"
                    class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="template in templates"
                        :key="template.id"
                        class="my-card group overflow-hidden p-3 transition duration-300 hover:-translate-y-1"
                    >
                        <Link
                            :href="`/templates/${template.slug}`"
                            class="relative block overflow-hidden rounded-lg bg-[var(--my-surface-soft)]"
                        >
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

                            <div
                                v-if="template.is_granted"
                                class="absolute top-4 left-4 z-10 flex items-center gap-1.5 rounded-full bg-[var(--my-secondary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-[#5c3a34] uppercase"
                            >
                                <Zap class="size-3.5" />
                                Akses Gratis
                            </div>

                            <div
                                v-if="template.is_free && !template.is_granted"
                                class="absolute top-4 right-4 rounded-full bg-[var(--my-primary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-white uppercase"
                            >
                                Gratis
                            </div>
                            <div
                                v-else-if="
                                    template.discount_percent &&
                                    template.discount_percent > 0
                                "
                                class="absolute top-4 right-4 rounded-full bg-[var(--my-primary)] px-3 py-1 text-xs font-bold tracking-[0.12em] text-white uppercase"
                            >
                                -{{ template.discount_percent }}%
                            </div>
                        </Link>

                        <div class="p-4">
                            <Link
                                :href="`/templates/${template.slug}`"
                                class="group-hover:text-[var(--my-primary)]"
                            >
                                <h3 class="my-heading text-2xl">
                                    {{ template.name }}
                                </h3>
                            </Link>
                            <div
                                class="mt-3 flex items-end justify-between gap-4"
                            >
                                <div class="flex items-end gap-2">
                                    <p
                                        class="text-2xl font-bold text-[var(--my-primary)]"
                                    >
                                        {{
                                            template.is_granted
                                                ? 'Akses Gratis'
                                                : template.is_free
                                                  ? 'Gratis'
                                                  : `Rp ${template.price.toLocaleString('id-ID')}`
                                        }}
                                    </p>
                                    <p
                                        v-if="
                                            !template.is_granted &&
                                            !template.is_free &&
                                            template.original_price &&
                                            template.original_price >
                                                template.price
                                        "
                                        class="text-base font-semibold text-[var(--my-muted)] line-through"
                                    >
                                        Rp
                                        {{
                                            template.original_price.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                    </p>
                                </div>
                                <p
                                    class="text-sm font-semibold tracking-[0.12em] text-[var(--my-muted)] uppercase"
                                >
                                    Premium
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

                            <div class="mt-6 grid gap-3">
                                <a
                                    :href="`/templates/${template.slug}/render`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="my-btn-secondary w-full gap-2"
                                >
                                    <Eye class="size-4" />
                                    Lihat Preview
                                </a>

                                <button
                                    v-if="template.is_granted"
                                    class="my-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                    type="button"
                                    :disabled="activatingId === template.id"
                                    @click="activateTemplate(template)"
                                >
                                    <Zap
                                        v-if="activatingId === template.id"
                                        class="size-4 animate-pulse"
                                    />
                                    <Check v-else class="size-4" />
                                    {{
                                        activatingId === template.id
                                            ? 'Mengaktifkan...'
                                            : 'Aktifkan Sekarang'
                                    }}
                                </button>
                                <button
                                    v-else
                                    class="my-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                    type="button"
                                    :disabled="addingId === template.id"
                                    @click="addToCart(template)"
                                >
                                    <Check
                                        v-if="addedId === template.id"
                                        class="size-4"
                                    />
                                    <ShoppingCart v-else class="size-4" />
                                    {{
                                        addingId === template.id
                                            ? 'Menambahkan...'
                                            : addedId === template.id
                                              ? 'Ditambahkan!'
                                              : 'Tambah ke Keranjang'
                                    }}
                                </button>
                                <Link
                                    v-if="addedId === template.id"
                                    href="/keranjang"
                                    class="my-btn-secondary w-full gap-2"
                                >
                                    Lihat Keranjang
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="my-card py-16 text-center">
                    <p class="my-heading text-3xl">
                        Belum ada template tersedia
                    </p>
                    <p class="my-copy mx-auto mt-3 max-w-md">
                        Koleksi template baru akan segera hadir di sini. Cek
                        kembali dalam beberapa saat ya.
                    </p>
                    <Link href="/" class="my-btn-primary mt-6"
                        >Kembali ke Beranda</Link
                    >
                </div>
            </section>
        </main>

        <PublicFooter />
    </div>
</template>
