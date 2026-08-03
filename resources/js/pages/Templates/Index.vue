<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Eye, ShoppingBag } from 'lucide-vue-next';
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
}

defineProps<{
    templates: Template[];
}>();

defineOptions({
    layout: undefined,
});

const addingToCart = ref<number | null>(null);

const addToCart = (template: Template) => {
    addingToCart.value = template.id;

    sessionStorage.setItem(
        'selected_template',
        JSON.stringify({
            id: template.id,
            slug: template.slug,
            name: template.name,
            price: template.price,
            is_free: template.is_free,
        }),
    );

    setTimeout(() => {
        router.visit(`/checkout?template=${template.slug}`);
    }, 300);
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
                        <div
                            class="relative overflow-hidden rounded-lg bg-[var(--my-surface-soft)]"
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
                                v-if="template.is_free"
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
                        </div>

                        <div class="p-4">
                            <h3 class="my-heading text-2xl">
                                {{ template.name }}
                            </h3>
                            <div
                                class="mt-3 flex items-end justify-between gap-4"
                            >
                                <div class="flex items-end gap-2">
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
                                        v-if="
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
                                    class="my-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                    type="button"
                                    :disabled="addingToCart === template.id"
                                    @click="addToCart(template)"
                                >
                                    <ShoppingBag class="size-4" />
                                    {{
                                        addingToCart === template.id
                                            ? 'Memproses...'
                                            : 'Beli Template Ini'
                                    }}
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="my-card py-16 text-center">
                    <p class="my-heading text-3xl">
                        Belum ada template tersedia
                    </p>
                    <p class="my-copy mx-auto mt-3 max-w-md">
                        Template akan muncul di sini setelah diupload dari
                        admin.
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
