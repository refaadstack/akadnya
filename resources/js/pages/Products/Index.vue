<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, ShoppingBag } from 'lucide-vue-next';
import { ref } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface Product {
    id: number;
    type: string;
    slug: string;
    name: string;
    description: string;
    price: number;
    is_recurring?: boolean;
    recurring_interval?: string;
}

defineProps<{
    products: Product[];
}>();

defineOptions({
    layout: undefined,
});

const addingToCart = ref<number | null>(null);

const addToCart = (product: Product) => {
    addingToCart.value = product.id;

    setTimeout(() => {
        router.visit(`/checkout?product=${product.slug}`);
    }, 300);
};
</script>

<template>
    <div class="my-page">
        <Head title="Produk" />

        <PublicNavbar current-page="products" />

        <main class="pt-28">
            <section class="my-container py-14">
                <div class="max-w-3xl">
                    <div class="my-label mb-4">Produk & Tambahan</div>
                    <h1 class="my-heading text-5xl leading-tight">
                        Lengkapi undanganmu dengan tambahan, beli satuan.
                    </h1>
                    <p class="my-copy mt-5">
                        Template undangan sudah termasuk semua fitur. Di sini
                        kamu bisa menambahkan layanan pendukung seperti buku
                        tamu, custom domain, atau bantuan setup — beli satuan
                        kapan pun kamu butuh.
                    </p>
                </div>
            </section>

            <section class="my-container pb-20">
                <template v-if="products.length > 0">
                    <h2 class="my-heading mb-6 text-3xl">Tambahan</h2>
                    <div
                        class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="product in products"
                            :key="product.id"
                            class="my-card p-6 transition duration-300 hover:-translate-y-1"
                        >
                            <h3 class="my-heading text-2xl">
                                {{ product.name }}
                            </h3>
                            <p class="mt-2 text-sm text-[var(--my-muted)]">
                                {{ product.description }}
                            </p>
                            <p
                                class="mt-5 text-3xl font-bold text-[var(--my-primary)]"
                            >
                                Rp {{ product.price.toLocaleString('id-ID') }}
                                <span
                                    v-if="product.is_recurring"
                                    class="text-sm font-semibold text-[var(--my-muted)]"
                                >
                                    /{{
                                        product.recurring_interval === 'monthly'
                                            ? 'bulan'
                                            : 'tahun'
                                    }}
                                </span>
                                <span
                                    v-else
                                    class="text-sm font-semibold text-[var(--my-muted)]"
                                    >sekali bayar</span
                                >
                            </p>
                            <button
                                class="my-btn-primary mt-6 w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
                                type="button"
                                :disabled="addingToCart === product.id"
                                @click="addToCart(product)"
                            >
                                <ShoppingBag class="size-4" />
                                {{
                                    addingToCart === product.id
                                        ? 'Memproses...'
                                        : 'Beli Produk Ini'
                                }}
                            </button>
                        </article>
                    </div>
                </template>

                <div
                    v-if="products.length === 0"
                    class="my-card py-16 text-center"
                >
                    <p class="my-heading text-3xl">Belum ada produk tersedia</p>
                    <p class="my-copy mx-auto mt-3 max-w-md">
                        Produk akan muncul di sini setelah dikelola dari admin.
                    </p>
                    <Link href="/templates" class="my-btn-primary mt-6"
                        >Lihat Koleksi Template</Link
                    >
                </div>

                <div class="my-card mt-14 p-6">
                    <div
                        class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
                    >
                        <div>
                            <h3 class="my-heading text-2xl">
                                Belum punya template?
                            </h3>
                            <p class="mt-1 text-sm text-[var(--my-muted)]">
                                Template undangan bisa dibeli satuan dan sudah
                                termasuk semua fitur editor, subdomain, dan
                                publish.
                            </p>
                        </div>
                        <Link
                            href="/templates"
                            class="my-btn-secondary shrink-0 gap-2"
                        >
                            <Check class="size-4" />
                            Lihat Template
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <PublicFooter />
    </div>
</template>
